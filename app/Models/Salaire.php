<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salaire extends Model
{
    protected $fillable = [
        'employe_id',
        'mois',
        'annee',
        'salaire_brut',
        'jours_travailles',
        'jours_ouvres',
        'nb_absences',
        'nb_retards',
        'penalites_absences',
        'penalites_retards',
        'total_penalites',
        'salaire_net',
        'taux_presence',
        'statut_paiement',
        'date_paiement',
    ];

    protected $casts = [
        'salaire_brut'        => 'decimal:2',
        'penalites_absences'  => 'decimal:2',
        'penalites_retards'   => 'decimal:2',
        'total_penalites'     => 'decimal:2',
        'salaire_net'         => 'decimal:2',
        'taux_presence'       => 'decimal:2',
        'date_paiement'       => 'date',
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function getMoisLibelleAttribute(): string
    {
        $mois = [
            1 => 'Janvier',
            2 => 'Février',
            3 => 'Mars',
            4 => 'Avril',
            5 => 'Mai',
            6 => 'Juin',
            7 => 'Juillet',
            8 => 'Août',
            9 => 'Septembre',
            10 => 'Octobre',
            11 => 'Novembre',
            12 => 'Décembre'
        ];
        return $mois[$this->mois] ?? '';
    }
}
