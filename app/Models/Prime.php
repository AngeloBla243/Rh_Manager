<?php
// app/Models/Prime.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prime extends Model
{
    protected $fillable = [
        'employe_id',
        'salaire_id',
        'mois',
        'annee',
        'type',
        'libelle',
        'montant',
        'imposable',
        'note'
    ];
    protected $casts = ['montant' => 'decimal:2', 'imposable' => 'boolean'];

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }
    public function salaire()
    {
        return $this->belongsTo(Salaire::class);
    }

    public function getTypeLibelleAttribute(): string
    {
        return match ($this->type) {
            'performance'   => 'Prime de performance',
            'anciennete'    => 'Prime d\'ancienneté',
            'transport'     => 'Indemnité de transport',
            'logement'      => 'Indemnité de logement',
            'repas'         => 'Indemnité de repas',
            'exceptionnelle' => 'Prime exceptionnelle',
            default         => 'Autre prime',
        };
    }

    public function scopeDuMois($q, $m, $a)
    {
        return $q->where('mois', $m)->where('annee', $a);
    }
    public function scopePourEmploye($q, $id)
    {
        return $q->where('employe_id', $id);
    }
}
