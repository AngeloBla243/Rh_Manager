<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BiometriqueService;
use App\Services\PresenceService;
use App\Models\Employe;

class SynchroniserPointages extends Command
{
    protected $signature   = 'biometrique:synchroniser';
    protected $description = 'Récupère les pointages depuis l\'appareil biométrique';

    public function handle(BiometriqueService $bio, PresenceService $presenceService): int
    {
        $this->info('Connexion à l\'appareil biométrique...');

        if (!$bio->verifierConnexion()) {
            $this->error('Impossible de se connecter à l\'appareil.');
            return Command::FAILURE;
        }

        $this->info('Lecture des logs de pointage...');
        $logs = $bio->lireLogsPointage();

        if (empty($logs)) {
            $this->warn('Aucun log trouvé.');
            return Command::SUCCESS;
        }

        $this->info(count($logs) . ' enregistrements trouvés.');
        $bar = $this->output->createProgressBar(count($logs));

        $traites = 0;
        foreach ($logs as $log) {
            $employe = Employe::where('empreinte_id', $log['user_id'])->first();

            if ($employe) {
                $presenceService->enregistrerPointage(
                    $employe->id,
                    $log['type'],
                    $log['heure'],
                    $log['date'],
                    'biometrique'
                );
                $traites++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("{$traites} pointages synchronisés.");

        return Command::SUCCESS;
    }
}
