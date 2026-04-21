<?php
// ════════════════════════════════════════════════════════════════════
// app/Models/Conge.php
// Commande : php artisan make:model Conge
// ════════════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Conge extends Model
{
    use HasFactory;

    protected $table = 'conges';

    protected $fillable = [
        'employe_id',
        'date_debut',
        'date_fin',
        'nombre_jours',
        'type',
        'motif',
        'statut',
        'document',
        'commentaire_admin',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
    ];

    // ── Relations ─────────────────────────────────────────────────────

    /**
     * L'employé qui bénéficie du congé
     */
    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    // ── Accesseurs ────────────────────────────────────────────────────

    /**
     * Libellé lisible du type de congé
     */
    public function getTypeLibelleAttribute(): string
    {
        return match ($this->type) {
            'annuel'       => 'Congé annuel',
            'maladie'      => 'Congé maladie',
            'maternite'    => 'Congé maternité',
            'sans_solde'   => 'Congé sans solde',
            'exceptionnel' => 'Congé exceptionnel',
            default        => 'Congé',
        };
    }

    /**
     * Libellé lisible du statut
     */
    public function getStatutLibelleAttribute(): string
    {
        return match ($this->statut) {
            'en_attente' => 'En attente',
            'approuve'   => 'Approuvé',
            'refuse'     => 'Refusé',
            'annule'     => 'Annulé',
            default      => 'Inconnu',
        };
    }

    /**
     * Couleur du badge de statut
     */
    public function getStatutCouleurAttribute(): string
    {
        return match ($this->statut) {
            'approuve'   => 'green',
            'refuse'     => 'red',
            'annule'     => 'red',
            'en_attente' => 'amber',
            default      => 'gray',
        };
    }

    /**
     * Période formatée : "21 avr. → 25 avr. 2025"
     */
    public function getPeriodeAttribute(): string
    {
        $debut = $this->date_debut->locale('fr')->isoFormat('D MMM');
        $fin   = $this->date_fin->locale('fr')->isoFormat('D MMM YYYY');
        return "{$debut} → {$fin}";
    }

    /**
     * Le congé est-il en cours aujourd'hui ?
     */
    public function getEstEnCoursAttribute(): bool
    {
        return $this->statut === 'approuve'
            && now()->between($this->date_debut, $this->date_fin);
    }

    /**
     * Le congé est-il passé ?
     */
    public function getEstPasseAttribute(): bool
    {
        return $this->date_fin->lt(now());
    }

    /**
     * Le congé est-il à venir ?
     */
    public function getEstAVenirAttribute(): bool
    {
        return $this->date_debut->gt(now());
    }

    /**
     * Le congé est-il approuvé et futur ou en cours ?
     */
    public function getEstActifAttribute(): bool
    {
        return $this->statut === 'approuve' && !$this->est_passe;
    }

    /**
     * A-t-il un document joint ?
     */
    public function getADocumentAttribute(): bool
    {
        return !empty($this->document);
    }

    // ── Scopes ────────────────────────────────────────────────────────

    /**
     * Congés en attente de traitement
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    /**
     * Congés approuvés
     */
    public function scopeApprouves($query)
    {
        return $query->where('statut', 'approuve');
    }

    /**
     * Congés actifs aujourd'hui (en cours)
     */
    public function scopeActifsAujourdhui($query)
    {
        return $query->where('statut', 'approuve')
            ->where('date_debut', '<=', now()->toDateString())
            ->where('date_fin',   '>=', now()->toDateString());
    }

    /**
     * Congés d'un employé précis
     */
    public function scopePourEmploye($query, int $employeId)
    {
        return $query->where('employe_id', $employeId);
    }

    /**
     * Congés d'une année donnée
     */
    public function scopeDeAnnee($query, int $annee)
    {
        return $query->whereYear('date_debut', $annee);
    }

    // ── Méthodes statiques ────────────────────────────────────────────

    /**
     * Vérifier si un employé a un congé approuvé à une date donnée
     */
    public static function employeEnConge(int $employeId, string $date): bool
    {
        return static::where('employe_id', $employeId)
            ->where('statut', 'approuve')
            ->where('date_debut', '<=', $date)
            ->where('date_fin',   '>=', $date)
            ->exists();
    }

    /**
     * Solde de congés restants pour un employé dans l'année
     * (jours accordés - jours pris approuvés)
     */
    public static function soldeRestant(int $employeId, int $annee): int
    {
        $accordes = (int) \App\Models\Parametre::valeur('conge_jours_par_an', 21);

        $pris = static::where('employe_id', $employeId)
            ->where('statut', 'approuve')
            ->where('type', 'annuel')
            ->whereYear('date_debut', $annee)
            ->sum('nombre_jours');

        return max(0, $accordes - $pris);
    }
}
