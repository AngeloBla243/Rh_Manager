<?php
// app/Models/Contrat.php
// Commande : php artisan make:model Contrat

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Contrat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employe_id',
        'type',
        'date_debut',
        'date_fin',
        'poste',
        'salaire_contractuel',
        'statut',
        'description',
        'numero_contrat',
        'document_path',
        'renouvelle_depuis',
        'periode_essai',
        'fin_periode_essai',
        'cree_par',
    ];

    protected $casts = [
        'date_debut'       => 'date',
        'date_fin'         => 'date',
        'fin_periode_essai' => 'date',
        'salaire_contractuel' => 'decimal:2',
        'periode_essai'    => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────────────

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function creePar()
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    /** Contrat d'origine en cas de renouvellement */
    public function renouvellDepuis()
    {
        return $this->belongsTo(Contrat::class, 'renouvelle_depuis');
    }

    /** Contrats qui ont été renouvelés à partir de celui-ci */
    public function renouvellements()
    {
        return $this->hasMany(Contrat::class, 'renouvelle_depuis');
    }

    // ── Accesseurs ────────────────────────────────────────────────────

    public function getTypeLibelleAttribute(): string
    {
        return match ($this->type) {
            'CDI'      => 'Contrat à Durée Indéterminée',
            'CDD'      => 'Contrat à Durée Déterminée',
            'Stage'    => 'Convention de stage',
            'Interim'  => 'Mission d\'intérim',
            'Freelance' => 'Prestataire indépendant',
            default    => 'Autre',
        };
    }

    public function getStatutLibelleAttribute(): string
    {
        return match ($this->statut) {
            'actif'     => 'En vigueur',
            'suspendu'  => 'Suspendu',
            'expire'    => 'Expiré',
            'resilie'   => 'Résilié',
            'renouvele' => 'Renouvelé',
            default     => 'Inconnu',
        };
    }

    public function getStatutCouleurAttribute(): string
    {
        return match ($this->statut) {
            'actif'     => 'green',
            'suspendu'  => 'amber',
            'expire',
            'resilie'   => 'red',
            'renouvele' => 'gray',
            default     => 'gray',
        };
    }

    /** Durée du contrat en jours (null si CDI sans fin) */
    public function getDureeJoursAttribute(): ?int
    {
        if (!$this->date_fin) return null;
        return $this->date_debut->diffInDays($this->date_fin);
    }

    /** Durée formatée : "2 ans", "6 mois", "45 jours" */
    public function getDureeFormateeAttribute(): string
    {
        if (!$this->date_fin) return 'Indéterminée (CDI)';
        $jours = $this->duree_jours;
        if ($jours >= 365) return round($jours / 365, 1) . ' an(s)';
        if ($jours >= 30)  return round($jours / 30) . ' mois';
        return $jours . ' jours';
    }

    /** Jours restants avant la fin du contrat */
    public function getJoursRestantsAttribute(): ?int
    {
        if (!$this->date_fin) return null;
        return (int) now()->startOfDay()->diffInDays($this->date_fin->startOfDay(), false);
    }

    /** Le contrat expire-t-il dans moins de 30 jours ? */
    public function getExpireBientotAttribute(): bool
    {
        return $this->date_fin
            && $this->statut === 'actif'
            && $this->jours_restants !== null
            && $this->jours_restants <= 30
            && $this->jours_restants >= 0;
    }

    /** Le contrat est-il arrivé à terme ? */
    public function getEstExpireAttribute(): bool
    {
        return $this->date_fin && $this->date_fin->lt(now()) && $this->statut === 'actif';
    }

    /** Est-on encore en période d'essai ? */
    public function getEnPeriodeEssaiAttribute(): bool
    {
        return $this->periode_essai
            && $this->fin_periode_essai
            && $this->fin_periode_essai->gte(now());
    }

    public function getADocumentAttribute(): bool
    {
        return !empty($this->document_path);
    }

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeExpirantDans($query, int $jours = 30)
    {
        return $query->where('statut', 'actif')
            ->whereNotNull('date_fin')
            ->whereBetween('date_fin', [now(), now()->addDays($jours)]);
    }

    public function scopeDeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopePourEmploye($query, int $employeId)
    {
        return $query->where('employe_id', $employeId);
    }

    /** Contrats arrivés à terme mais pas encore marqués expirés */
    public function scopeAMettreAJour($query)
    {
        return $query->where('statut', 'actif')
            ->whereNotNull('date_fin')
            ->where('date_fin', '<', now()->toDateString());
    }
}
