<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\SalaireService;
use Carbon\Carbon;

class SalaireSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(SalaireService::class);
        $count   = 0;

        // Calculer les salaires des 2 derniers mois
        for ($i = 1; $i >= 0; $i--) {
            $date  = Carbon::now()->subMonths($i);
            $added = $service->calculerMois($date->month, $date->year);
            $count += $added;
        }

        $this->command->info("✓ {$count} fiche(s) de salaire calculée(s).");
    }
}
