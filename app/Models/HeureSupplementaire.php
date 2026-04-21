<?php
// ════════════════════════════════════════════════════════════════════
// app/Models/HeureSupplementaire.php
// ════════════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeureSupplementaire extends Model
{
    protected $table = 'heures_supplementaires';
    protected $fillable = [
        'employe_id',
        'presence_id',
        'date',
        'nb_heures',
        'taux_majoration',
        'montant',
        'statut',
        'motif',
        'approuvee_par'
    ];
    protected $casts = [
        'date' => 'date',
        'nb_heures' => 'decimal:2',
        'taux_majoration' => 'decimal:2',
        'montant' => 'decimal:2',
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }
    public function presence()
    {
        return $this->belongsTo(Presence::class);
    }
    public function approuveePar()
    {
        return $this->belongsTo(User::class, 'approuvee_par');
    }

    public function getStatutLibelleAttribute(): string
    {
        return match ($this->statut) {
            'en_attente' => 'En attente',
            'approuvee' => 'Approuvée',
            'refusee' => 'Refusée',
            'payee' => 'Payée',
            default => '—',
        };
    }

    /** Calculer le montant : taux_horaire × heures × majoration */
    public function calculerMontant(float $tauxHoraire): float
    {
        return round($tauxHoraire * $this->nb_heures * $this->taux_majoration, 2);
    }

    public function scopeApprouvees($q)
    {
        return $q->where('statut', 'approuvee');
    }
    public function scopePayees($q)
    {
        return $q->where('statut', 'payee');
    }
    public function scopeDuMois($q, $m, $a)
    {
        return $q->whereMonth('date', $m)->whereYear('date', $a);
    }
}
