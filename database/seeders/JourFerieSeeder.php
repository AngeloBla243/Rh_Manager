<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JourFerie;

class JourFerieSeeder extends Seeder
{
    public function run(): void
    {
        $annee = now()->year;
        $jours = [
            ['date' => "{$annee}-01-01", 'libelle' => 'Jour de l\'an'],
            ['date' => "{$annee}-01-17", 'libelle' => 'Journée Lumumba'],
            ['date' => "{$annee}-05-01", 'libelle' => 'Fête du Travail'],
            ['date' => "{$annee}-05-17", 'libelle' => 'Journée Nationale'],
            ['date' => "{$annee}-06-30", 'libelle' => 'Indépendance'],
            ['date' => "{$annee}-08-01", 'libelle' => 'Fête des Parents'],
            ['date' => "{$annee}-12-25", 'libelle' => 'Noël'],
        ];

        foreach ($jours as $j) {
            JourFerie::updateOrCreate(['date' => $j['date']], $j);
        }
    }
}
