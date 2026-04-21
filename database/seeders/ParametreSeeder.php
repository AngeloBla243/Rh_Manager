<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Parametre;

class ParametreSeeder extends Seeder
{
    public function run(): void
    {
        $parametres = [
            ['cle' => 'heure_arrivee',           'valeur' => '08:00', 'description' => 'Heure d\'arrivée normale'],
            ['cle' => 'heure_limite_retard',      'valeur' => '08:30', 'description' => 'Limite sans retard'],
            ['cle' => 'heure_sortie',             'valeur' => '17:00', 'description' => 'Heure de sortie'],
            ['cle' => 'penalite_absence_pct',     'valeur' => '5',     'description' => '% pénalité absence non justifiée'],
            ['cle' => 'penalite_retard_pct',      'valeur' => '2',     'description' => '% pénalité par retard'],
            ['cle' => 'conge_jours_par_an',       'valeur' => '21',    'description' => 'Jours de congé annuels'],
            ['cle' => 'types_documents',          'valeur' => json_encode([
                'Contrat de travail',
                'Pièce d\'identité',
                'Diplôme',
                'Attestation médicale',
            ]), 'description' => 'Types de documents requis'],
            ['cle' => 'nom_entreprise',           'valeur' => 'Mon Entreprise SARL', 'description' => 'Nom de l\'entreprise'],
            ['cle' => 'adresse_entreprise',       'valeur' => 'Kinshasa, RDC',       'description' => 'Adresse'],
        ];

        foreach ($parametres as $p) {
            Parametre::updateOrCreate(['cle' => $p['cle']], $p);
        }
    }
}
