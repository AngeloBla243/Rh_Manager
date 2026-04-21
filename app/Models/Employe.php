<?php
// ════════════════════════════════════════════════════════════════════
// app/Models/Employe.php  — VERSION COMPLÈTE
// Commande : php artisan make:model Employe
// ════════════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Employe extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'matricule',
        'nom',
        'postnom',
        'prenom',
        'date_naissance',
        'fonction',
        'annee_engagement',
        'photo',
        'empreinte_id',
        'salaire_base',
        'statut',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'salaire_base'   => 'decimal:2',
    ];

    // ════════════════════════════════════════════════════════════════
    // RELATIONS
    // ════════════════════════════════════════════════════════════════

    /**
     * Toutes les présences de l'employé
     */
    public function presences()
    {
        return $this->hasMany(Presence::class);
    }

    /**
     * Toutes les absences de l'employé
     */
    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    /**
     * Toutes les fiches de salaire de l'employé
     */
    public function salaires()
    {
        return $this->hasMany(Salaire::class);
    }

    /**
     * Tous les congés de l'employé
     */
    public function conges()
    {
        return $this->hasMany(Conge::class);
    }

    /**
     * Tous les documents administratifs de l'employé
     */
    public function documents()
    {
        return $this->hasMany(DocumentEmploye::class);
    }

    /**
     * Toutes les sanctions disciplinaires de l'employé
     */
    public function sanctions()
    {
        return $this->hasMany(Sanction::class);
    }

    // ════════════════════════════════════════════════════════════════
    // ACCESSEURS
    // ════════════════════════════════════════════════════════════════

    /**
     * Nom complet : "MBEKI Jean Claude"
     */
    public function getNomCompletAttribute(): string
    {
        return trim("{$this->nom} {$this->postnom} {$this->prenom}");
    }

    /**
     * Initiales pour l'avatar : "MJ"
     */
    public function getInitialesAttribute(): string
    {
        return strtoupper(
            mb_substr($this->nom, 0, 1) . mb_substr($this->prenom, 0, 1)
        );
    }

    /**
     * Ancienneté en années
     */
    public function getAncienneteAttribute(): int
    {
        return now()->year - $this->annee_engagement;
    }

    /**
     * URL publique de la photo (ou avatar par défaut)
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->photo)) {
            return asset("storage/{$this->photo}");
        }
        return asset('images/default-avatar.png');
    }

    /**
     * Âge de l'employé
     */
    public function getAgeAttribute(): int
    {
        return $this->date_naissance->age;
    }

    /**
     * L'employé a-t-il une empreinte enregistrée ?
     */
    public function getAEmpreinteAttribute(): bool
    {
        return !empty($this->empreinte_id);
    }

    /**
     * Libellé du statut
     */
    public function getStatutLibelleAttribute(): string
    {
        return match ($this->statut) {
            'actif'   => 'Actif',
            'suspendu' => 'Suspendu',
            'retraite' => 'Retraité',
            default   => 'Inconnu',
        };
    }

    // ════════════════════════════════════════════════════════════════
    // MÉTHODES MÉTIER
    // ════════════════════════════════════════════════════════════════

    /**
     * Récupérer la fiche de salaire d'un mois/année donné
     */
    public function salaireDuMois(int $mois, int $annee): ?Salaire
    {
        return $this->salaires()
            ->where('mois', $mois)
            ->where('annee', $annee)
            ->first();
    }

    /**
     * Taux de présence pour un mois/année donné
     */
    public function tauxPresence(int $mois, int $annee): float
    {
        $salaire = $this->salaireDuMois($mois, $annee);
        return $salaire ? (float) $salaire->taux_presence : 0.0;
    }

    /**
     * L'employé est-il présent aujourd'hui ?
     */
    public function estPresentAujourdhui(): bool
    {
        return $this->presences()
            ->whereDate('date', today())
            ->where('est_valide', true)
            ->exists();
    }

    /**
     * L'employé est-il en congé à une date donnée ?
     */
    public function estEnConge(?string $date = null): bool
    {
        $date = $date ?? today()->toDateString();
        return Conge::employeEnConge($this->id, $date);
    }

    /**
     * Solde de congés annuels restants
     */
    public function soldeConges(int $annee = null): int
    {
        return Conge::soldeRestant($this->id, $annee ?? now()->year);
    }

    /**
     * Nombre d'absences non justifiées ce mois
     */
    public function absencesNonJustifieesduMois(int $mois = null, int $annee = null): int
    {
        return $this->absences()
            ->whereMonth('date', $mois ?? now()->month)
            ->whereYear('date',  $annee ?? now()->year)
            ->where('type', 'non_justifiee')
            ->count();
    }

    /**
     * Salaire journalier de base (utile pour calculs de pénalités)
     */
    public function salaireJournalier(int $mois = null, int $annee = null): float
    {
        $mois  = $mois  ?? now()->month;
        $annee = $annee ?? now()->year;

        // Compter les jours ouvrés du mois via un calcul direct
        $debut  = Carbon::createFromDate($annee, $mois, 1);
        $fin    = $debut->copy()->endOfMonth();
        $feries = JourFerie::datesDeLAnnee($annee);

        $joursOuvres = 0;
        $current = $debut->copy();
        while ($current->lte($fin)) {
            if (!$current->isWeekend() && !$feries->contains($current->toDateString())) {
                $joursOuvres++;
            }
            $current->addDay();
        }

        return $joursOuvres > 0 ? round($this->salaire_base / $joursOuvres, 4) : 0;
    }

    /**
     * Documents manquants par rapport à la configuration
     * (types requis mais non encore uploadés)
     */
    public function documentsManquants(): array
    {
        $typesRequis = json_decode(Parametre::valeur('types_documents', '[]'), true);
        $typesPresents = $this->documents()->pluck('type_document')->toArray();

        return array_values(array_diff($typesRequis, $typesPresents));
    }

    // ════════════════════════════════════════════════════════════════
    // SCOPES
    // ════════════════════════════════════════════════════════════════

    /**
     * Uniquement les employés actifs
     */
    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }

    /**
     * Recherche par nom, prénom ou matricule
     */
    public function scopeRecherche($query, string $terme)
    {
        return $query->where(function ($q) use ($terme) {
            $q->where('nom',       'like', "%{$terme}%")
                ->orWhere('prenom',  'like', "%{$terme}%")
                ->orWhere('postnom', 'like', "%{$terme}%")
                ->orWhere('matricule', 'like', "%{$terme}%");
        });
    }

    /**
     * Filtrer par fonction
     */
    public function scopeDeFonction($query, string $fonction)
    {
        return $query->where('fonction', $fonction);
    }

    /**
     * Employés ayant une empreinte enregistrée
     */
    public function scopeAvecEmpreinte($query)
    {
        return $query->whereNotNull('empreinte_id');
    }

    public function contrats()
    {
        return $this->hasMany(Contrat::class);
    }

    // Contrat actif actuel
    public function contratActif(): ?Contrat
    {
        return $this->contrats()->where('statut', 'actif')->latest('date_debut')->first();
    }
}
