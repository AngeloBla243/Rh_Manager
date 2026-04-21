<?php
// ════════════════════════════════════════════════════════════════════
// app/Models/Paiement.php
// ════════════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = [
        'salaire_id',
        'employe_id',
        'montant',
        'mode',
        'date_paiement',
        'reference',
        'banque',
        'statut',
        'note',
        'effectue_par'
    ];
    protected $casts = [
        'date_paiement' => 'date',
        'montant' => 'decimal:2',
    ];

    public function salaire()
    {
        return $this->belongsTo(Salaire::class);
    }
    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }
    public function effectuePar()
    {
        return $this->belongsTo(User::class, 'effectue_par');
    }

    public function getModeLibelleAttribute(): string
    {
        return match ($this->mode) {
            'virement' => 'Virement bancaire',
            'especes' => 'Espèces',
            'cheque' => 'Chèque',
            'mobile_money' => 'Mobile Money',
            default => '—',
        };
    }

    public function scopeEffectues($q)
    {
        return $q->where('statut', 'effectue');
    }
    public function scopePourEmploye($q, $id)
    {
        return $q->where('employe_id', $id);
    }
}
