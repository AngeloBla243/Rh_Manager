<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employe;
use App\Models\Absence;
use App\Models\Presence;
use App\Models\JourFerie;
use App\Services\AbsenceService;
use App\Services\PresenceService;
use Carbon\Carbon;

class AbsenceSeeder extends Seeder
{
    public function run(): void
    {
        $presenceService = app(PresenceService::class);
        $absenceService  = app(AbsenceService::class);

        // Générer les absences manquantes des 2 derniers mois
        $count = 0;
        for ($i = 1; $i >= 0; $i--) {
            $date  = Carbon::now()->subMonths($i);
            $added = $absenceService->genererAbsencesManquantes($date->month, $date->year);
            $count += $added;
        }

        // Quelques absences justifiées manuelles pour les données de démo
        $employes = Employe::where('statut', 'actif')->take(3)->get();
        foreach ($employes as $employe) {
            $dateJustif = Carbon::now()->subDays(rand(5, 20))->toDateString();
            Absence::updateOrCreate(
                ['employe_id' => $employe->id, 'date' => $dateJustif],
                [
                    'type'       => 'justifiee',
                    'motif'      => 'Rendez-vous médical',
                    'penalite'   => 0,
                    'approuvee'  => true,
                ]
            );
        }

        $this->command->info("✓ {$count} absence(s) générée(s) automatiquement.");
    }
}
