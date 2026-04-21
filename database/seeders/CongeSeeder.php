<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Conge;
use App\Models\Employe;
use Carbon\Carbon;

class CongeSeeder extends Seeder
{
    public function run(): void
    {
        $employes = Employe::where('statut', 'actif')->get();

        $conges = [
            [
                'employe_index' => 0,
                'date_debut'    => Carbon::now()->addDays(5)->toDateString(),
                'date_fin'      => Carbon::now()->addDays(9)->toDateString(),
                'type'          => 'annuel',
                'statut'        => 'approuve',
                'motif'         => 'Congé annuel planifié',
                'nombre_jours'  => 5,
            ],
            [
                'employe_index' => 1,
                'date_debut'    => Carbon::now()->addDays(15)->toDateString(),
                'date_fin'      => Carbon::now()->addDays(19)->toDateString(),
                'type'          => 'annuel',
                'statut'        => 'en_attente',
                'motif'         => 'Vacances familiales',
                'nombre_jours'  => 5,
            ],
            [
                'employe_index' => 2,
                'date_debut'    => Carbon::now()->subDays(10)->toDateString(),
                'date_fin'      => Carbon::now()->subDays(7)->toDateString(),
                'type'          => 'maladie',
                'statut'        => 'approuve',
                'motif'         => 'Hospitalisation',
                'nombre_jours'  => 4,
            ],
        ];

        foreach ($conges as $data) {
            $employe = $employes->get($data['employe_index']);
            if (!$employe) continue;

            unset($data['employe_index']);
            Conge::create(array_merge($data, ['employe_id' => $employe->id]));
        }

        $this->command->info('✓ ' . count($conges) . ' congés créés.');
    }
}
