<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Ordre important :
     * 1. Utilisateur admin (dépendance de tout le reste)
     * 2. Paramètres (utilisés dans les calculs)
     * 3. Jours fériés (utilisés dans les calculs de présences)
     * 4. Employés (+ leurs contrats)
     * 5. Présences (dépend des employés et jours fériés)
     * 6. Absences (générées depuis les présences manquantes)
     * 7. Salaires (calculés depuis présences + absences)
     * 8. Congés (données de démo)
     * 9. Sanctions (données de démo)
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            ParametreSeeder::class,
            JourFerieSeeder::class,
            EmployeSeeder::class,
            PresenceSeeder::class,
            AbsenceSeeder::class,
            SalaireSeeder::class,
            CongeSeeder::class,
            SanctionSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ Base de données initialisée avec succès !');
        $this->command->info('   Login : admin@rh-manager.com');
        $this->command->info('   Mot de passe : Admin@2025!');
        $this->command->info('   URL : http://127.0.0.1:8000/admin');
    }
}
