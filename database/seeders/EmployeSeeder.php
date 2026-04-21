<?php
// database/seeders/EmployeSeeder.php
// Commande : php artisan make:seeder EmployeSeeder

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employe;
use App\Models\Contrat;

class EmployeSeeder extends Seeder
{
    public function run(): void
    {
        $employes = [
            [
                'matricule'        => 'EMP-001',
                'nom'              => 'Mbeki',
                'postnom'          => 'Jean',
                'prenom'           => 'Claude',
                'date_naissance'   => '1985-03-15',
                'fonction'         => 'Directeur des Ressources Humaines',
                'annee_engagement' => 2018,
                'salaire_base'     => 2400.00,
                'statut'           => 'actif',
                'contrat' => [
                    'type'        => 'CDI',
                    'date_debut'  => '2018-01-15',
                    'date_fin'    => null,
                    'statut'      => 'actif',
                ],
            ],
            [
                'matricule'        => 'EMP-002',
                'nom'              => 'Kabila',
                'postnom'          => 'Marie',
                'prenom'           => 'Solange',
                'date_naissance'   => '1992-07-22',
                'fonction'         => 'Comptable principale',
                'annee_engagement' => 2019,
                'salaire_base'     => 1800.00,
                'statut'           => 'actif',
                'contrat' => [
                    'type'        => 'CDI',
                    'date_debut'  => '2019-03-01',
                    'date_fin'    => null,
                    'statut'      => 'actif',
                ],
            ],
            [
                'matricule'        => 'EMP-003',
                'nom'              => 'Tshisekedi',
                'postnom'          => 'Paul',
                'prenom'           => 'Eric',
                'date_naissance'   => '1988-11-08',
                'fonction'         => 'Ingénieur informatique',
                'annee_engagement' => 2015,
                'salaire_base'     => 2100.00,
                'statut'           => 'actif',
                'contrat' => [
                    'type'        => 'CDI',
                    'date_debut'  => '2015-06-01',
                    'date_fin'    => null,
                    'statut'      => 'actif',
                ],
            ],
            [
                'matricule'        => 'EMP-004',
                'nom'              => 'Lumumba',
                'postnom'          => 'Grace',
                'prenom'           => 'Aline',
                'date_naissance'   => '1995-04-12',
                'fonction'         => 'Assistante administrative',
                'annee_engagement' => 2021,
                'salaire_base'     => 1400.00,
                'statut'           => 'actif',
                'contrat' => [
                    'type'        => 'CDI',
                    'date_debut'  => '2021-02-15',
                    'date_fin'    => null,
                    'statut'      => 'actif',
                ],
            ],
            [
                'matricule'        => 'EMP-005',
                'nom'              => 'Mobutu',
                'postnom'          => 'Felix',
                'prenom'           => 'Joseph',
                'date_naissance'   => '1980-09-30',
                'fonction'         => 'Technicien de maintenance',
                'annee_engagement' => 2012,
                'salaire_base'     => 1650.00,
                'statut'           => 'actif',
                'contrat' => [
                    'type'        => 'CDI',
                    'date_debut'  => '2012-09-01',
                    'date_fin'    => null,
                    'statut'      => 'actif',
                ],
            ],
            [
                'matricule'        => 'EMP-006',
                'nom'              => 'Kasavubu',
                'postnom'          => 'Sarah',
                'prenom'           => 'Diane',
                'date_naissance'   => '1991-06-18',
                'fonction'         => 'Secrétaire de direction',
                'annee_engagement' => 2020,
                'salaire_base'     => 1350.00,
                'statut'           => 'actif',
                'contrat' => [
                    'type'        => 'CDI',
                    'date_debut'  => '2020-07-01',
                    'date_fin'    => null,
                    'statut'      => 'actif',
                ],
            ],
            [
                'matricule'        => 'EMP-007',
                'nom'              => 'Nguesso',
                'postnom'          => 'Patrick',
                'prenom'           => 'Alain',
                'date_naissance'   => '1998-02-14',
                'fonction'         => 'Stagiaire développement',
                'annee_engagement' => 2025,
                'salaire_base'     => 600.00,
                'statut'           => 'actif',
                'contrat' => [
                    'type'        => 'Stage',
                    'date_debut'  => '2025-01-06',
                    'date_fin'    => '2025-06-30',
                    'statut'      => 'actif',
                    'description' => 'Stage de fin d\'études — 6 mois',
                ],
            ],
            [
                'matricule'        => 'EMP-008',
                'nom'              => 'Bemba',
                'postnom'          => 'Christine',
                'prenom'           => 'Rose',
                'date_naissance'   => '1990-12-05',
                'fonction'         => 'Chargée de communication',
                'annee_engagement' => 2023,
                'salaire_base'     => 1500.00,
                'statut'           => 'actif',
                'contrat' => [
                    'type'        => 'CDD',
                    'date_debut'  => '2023-09-01',
                    'date_fin'    => '2025-08-31',
                    'statut'      => 'actif',
                    'description' => 'CDD renouvelable 2 ans',
                ],
            ],
        ];

        foreach ($employes as $data) {
            $contratData = $data['contrat'];
            unset($data['contrat']);

            $employe = Employe::updateOrCreate(
                ['matricule' => $data['matricule']],
                $data
            );

            // Créer le contrat associé si la table existe
            if (class_exists(Contrat::class)) {
                Contrat::updateOrCreate(
                    ['employe_id' => $employe->id, 'type' => $contratData['type'], 'date_debut' => $contratData['date_debut']],
                    array_merge($contratData, ['employe_id' => $employe->id])
                );
            }
        }

        $this->command->info('✓ ' . count($employes) . ' employés créés avec leurs contrats.');
    }
}
