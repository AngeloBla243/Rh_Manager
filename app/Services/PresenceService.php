<?php

namespace App\Services;

use App\Models\Employe;
use App\Models\Presence;
use App\Models\JourFerie;
use App\Models\Parametre;
use Carbon\Carbon;

class PresenceService
{
    public function enregistrerPointage(
        int    $employeId,
        string $type,           // 'entree' ou 'sortie'
        string $heure,          // format HH:MM
        string $date,
        string $mode = 'biometrique'
    ): Presence {

        // Trouver ou créer la fiche du jour
        $presence = Presence::firstOrCreate(
            ['employe_id' => $employeId, 'date' => $date],
            ['mode_pointage' => $mode]
        );

        if ($type === 'entree' && !$presence->heure_entree) {
            $presence->heure_entree = $heure . ':00';

            // Vérifier retard
            $limite = Parametre::valeur('heure_limite_retard', '08:30');
            $entreeCarbon = Carbon::createFromFormat('H:i', $heure);
            $limiteCarbon = Carbon::createFromFormat('H:i', $limite);

            if ($entreeCarbon->gt($limiteCarbon)) {
                $presence->est_retard     = true;
                $presence->minutes_retard = $entreeCarbon->diffInMinutes($limiteCarbon);
            }
        }

        if ($type === 'sortie' && !$presence->heure_sortie) {
            $presence->heure_sortie = $heure . ':00';
        }

        // Valider si on a entrée ET sortie
        $presence->est_valide = !empty($presence->heure_entree) && !empty($presence->heure_sortie);
        $presence->save();

        return $presence;
    }

    public function genererRapportMensuel(int $mois, int $annee): array
    {
        $employes    = Employe::where('statut', 'actif')->get();
        $joursOuvres = $this->compterJoursOuvres($mois, $annee);

        $rapport = [];
        foreach ($employes as $employe) {
            $presences = Presence::where('employe_id', $employe->id)
                ->whereMonth('date', $mois)
                ->whereYear('date', $annee)
                ->get();

            $rapport[] = [
                'employe'         => $employe,
                'jours_travailles' => $presences->where('est_valide', true)->count(),
                'jours_ouvres'    => $joursOuvres,
                'retards'         => $presences->where('est_retard', true)->count(),
                'absences'        => $joursOuvres - $presences->where('est_valide', true)->count(),
                'taux_presence'   => $joursOuvres > 0
                    ? round(($presences->where('est_valide', true)->count() / $joursOuvres) * 100, 2)
                    : 0,
            ];
        }

        return $rapport;
    }

    public function compterJoursOuvres(int $mois, int $annee): int
    {
        $debut  = Carbon::createFromDate($annee, $mois, 1);
        $fin    = $debut->copy()->endOfMonth();
        $feries = JourFerie::whereBetween('date', [$debut, $fin])->pluck('date');

        $count = 0;
        while ($debut->lte($fin)) {
            if (!$debut->isWeekend() && !$feries->contains($debut->toDateString())) {
                $count++;
            }
            $debut->addDay();
        }
        return $count;
    }
}
