<?php
// database/seeders/PresenceSeeder.php
// Génère des présences réalistes pour les 2 derniers mois

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employe;
use App\Models\Presence;
use App\Models\JourFerie;
use Carbon\Carbon;

class PresenceSeeder extends Seeder
{
    public function run(): void
    {
        $employes = Employe::where('statut', 'actif')->get();
        $count    = 0;

        // Générer des présences pour les 2 derniers mois
        for ($moisOffset = 1; $moisOffset >= 0; $moisOffset--) {
            $debut  = Carbon::now()->subMonths($moisOffset)->startOfMonth();
            $fin    = $moisOffset === 0
                ? Carbon::now()->subDays(1)   // mois courant : jusqu'à hier
                : Carbon::now()->subMonths($moisOffset)->endOfMonth();

            $feries = JourFerie::whereBetween('date', [$debut, $fin])
                ->pluck('date')->map(fn($d) => (string)$d);

            $current = $debut->copy();

            while ($current->lte($fin)) {
                if ($current->isWeekend() || $feries->contains($current->toDateString())) {
                    $current->addDay();
                    continue;
                }

                foreach ($employes as $employe) {
                    // 85% de chance d'être présent
                    $present = rand(1, 100) <= 85;
                    if (!$present) {
                        $current->addDay();
                        continue;
                    }

                    // Heure d'entrée entre 07h45 et 09h00
                    // 20% de chance d'être en retard (après 08h30)
                    $retard = rand(1, 100) <= 20;
                    $heureEntree = $retard
                        ? sprintf('%02d:%02d:00', 8, rand(31, 59))
                        : sprintf('%02d:%02d:00', rand(7, 8) === 7 ? 7 : 8, $retard ? rand(31, 59) : rand(0, 29));

                    $heureEntreeCarbon = Carbon::createFromFormat('H:i:s', $heureEntree);
                    $limite = Carbon::createFromFormat('H:i', '08:30');

                    $estRetard = $heureEntreeCarbon->gt($limite);
                    $minutesRetard = $estRetard ? $heureEntreeCarbon->diffInMinutes($limite) : 0;

                    // Heure de sortie entre 16h45 et 17h30
                    $heureSortie = sprintf('%02d:%02d:00', 17, rand(0, 30));

                    Presence::updateOrCreate(
                        ['employe_id' => $employe->id, 'date' => $current->toDateString()],
                        [
                            'heure_entree'   => $heureEntree,
                            'heure_sortie'   => $heureSortie,
                            'mode_pointage'  => rand(0, 1) ? 'biometrique' : 'manuel',
                            'est_retard'     => $estRetard,
                            'minutes_retard' => $minutesRetard,
                            'est_valide'     => true,
                        ]
                    );
                    $count++;
                }

                $current->addDay();
            }
        }

        $this->command->info("✓ {$count} présences générées.");
    }
}
