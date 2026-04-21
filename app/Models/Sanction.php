<?php
// app/Models/Sanction.php
// Commande : php artisan make:model Sanction

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sanction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employe_id',
        'type',
        'motif',
        'description',
        'date_debut',
        'date_fin',
        'montant_retenu',
        'absences_ids',
        'statut',
        'prononcee_par',
        'signe_employe',
        'date_signature',
        'document_path',
    ];

    protected $casts = [
        'date_debut'     => 'date',
        'date_fin'       => 'date',
        'date_signature' => 'date',
        'montant_retenu' => 'decimal:2',
        'signe_employe'  => 'boolean',
        'absences_ids'   => 'array',   // JSON auto-décodé
    ];

    // ── Relations ─────────────────────────────────────────────────────
    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function prononcePar()
    {
        return $this->belongsTo(User::class, 'prononcee_par');
    }

    // ── Accesseurs ────────────────────────────────────────────────────

    /**
     * Libellé lisible du type de sanction
     */
    public function getTypeLibelleAttribute(): string
    {
        return match ($this->type) {
            'avertissement_verbal' => 'Avertissement verbal',
            'avertissement_ecrit'  => 'Avertissement écrit',
            'mise_a_pied'          => 'Mise à pied',
            'retenue_salaire'      => 'Retenue sur salaire',
            'licenciement'         => 'Licenciement',
            default                => 'Autre',
        };
    }

    /**
     * Durée en jours (pour les mises à pied)
     */
    public function getDureeJoursAttribute(): ?int
    {
        if (!$this->date_fin) return null;
        return $this->date_debut->diffInDays($this->date_fin) + 1;
    }

    /**
     * La sanction est-elle encore active ?
     */
    public function getEstActiveAttribute(): bool
    {
        return $this->statut === 'en_cours';
    }

    // ── Scopes ────────────────────────────────────────────────────────
    public function scopeEnCours($query)
    {
        return $query->where('statut', 'en_cours');
    }

    public function scopePourEmploye($query, int $employeId)
    {
        return $query->where('employe_id', $employeId);
    }
}
