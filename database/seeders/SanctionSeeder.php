<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sanction;
use App\Models\Employe;
use Carbon\Carbon;

class SanctionSeeder extends Seeder
{
    public function run(): void
    {
        $employes = Employe::where('statut', 'actif')->get();
        if ($employes->isEmpty()) return;

        Sanction::create([
            'employe_id'  => $employes->first()->id,
            'type'        => 'avertissement_ecrit',
            'motif'       => '3 absences non justifiées consécutives en janvier',
            'description' => 'Malgré les avertissements verbaux précédents, l\'employé a continué à s\'absenter.',
            'date_debut'  => Carbon::now()->subDays(20)->toDateString(),
            'statut'      => 'executee',
            'signe_employe' => true,
            'date_signature' => Carbon::now()->subDays(18)->toDateString(),
        ]);

        $this->command->info('✓ 1 sanction de démonstration créée.');
    }
}
