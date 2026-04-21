<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\Presence;
use App\Models\Salaire;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $mois  = request('mois', now()->month);
        $annee = request('annee', now()->year);
        $today = Carbon::today();

        // Stats employés
        $totalEmployes    = Employe::where('statut', 'actif')->count();
        $presentsAujourdhui = Presence::whereDate('date', $today)
            ->where('est_valide', true)->count();
        $absentsAujourdhui = $totalEmployes - $presentsAujourdhui;

        // Masse salariale du mois
        $masseSalariale = Salaire::where('mois', $mois)
            ->where('annee', $annee)
            ->sum('salaire_net');

        // Données pour le graphique (6 derniers mois)
        $graphPresences = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $joursOuvres = $this->joursOuvresCount($date->month, $date->year);
            $presences   = Presence::whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->where('est_valide', true)->count();
            $attendu = $joursOuvres * $totalEmployes;
            $graphPresences[] = [
                'mois'  => $date->locale('fr')->monthName,
                'taux'  => $attendu > 0 ? round(($presences / $attendu) * 100, 1) : 0,
            ];
        }

        return view('admin.dashboard', compact(
            'totalEmployes',
            'presentsAujourdhui',
            'absentsAujourdhui',
            'masseSalariale',
            'graphPresences',
            'mois',
            'annee'
        ));
    }

    private function joursOuvresCount(int $mois, int $annee): int
    {
        $debut = Carbon::createFromDate($annee, $mois, 1);
        $fin   = $debut->copy()->endOfMonth();
        $count = 0;
        while ($debut->lte($fin)) {
            if (!$debut->isWeekend()) $count++;
            $debut->addDay();
        }
        return $count;
    }
}
