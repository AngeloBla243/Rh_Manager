<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('biometrique:synchroniser')->everyFiveMinutes();

// Vérifier les alertes chaque matin à 8h05
Schedule::call(function () {
    app(\App\Services\AlerteService::class)->verifierToutes();
})->dailyAt('08:05');

// Mettre à jour les contrats expirés chaque nuit
Schedule::call(function () {
    \App\Models\Contrat::aMettreAJour()->update(['statut' => 'expire']);
})->daily();
