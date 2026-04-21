<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    protected $fillable = [
        'employe_id',
        'date',
        'heure_entree',
        'heure_sortie',
        'mode_pointage',
        'est_retard',
        'minutes_retard',
        'est_valide',
        'remarque',
    ];

    protected $casts = [
        'date'       => 'date',
        'est_retard' => 'boolean',
        'est_valide' => 'boolean',
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    // Calculer automatiquement le statut après pointage
    public function valider(): void
    {
        $this->est_valide = $this->heure_entree && $this->heure_sortie;

        if ($this->heure_entree) {
            $limite = Parametre::valeur('heure_limite_retard', '08:30');
            $entree = \Carbon\Carbon::createFromFormat('H:i:s', $this->heure_entree);
            $limiteCarbon = \Carbon\Carbon::createFromFormat('H:i', $limite);

            if ($entree->gt($limiteCarbon)) {
                $this->est_retard = true;
                $this->minutes_retard = $entree->diffInMinutes($limiteCarbon);
            }
        }

        $this->save();
    }
}
