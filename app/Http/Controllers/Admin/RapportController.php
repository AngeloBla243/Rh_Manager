<?php

// ════════════════════════════════════════════════════════════════════
// app/Http/Controllers/Admin/RapportController.php
// ════════════════════════════════════════════════════════════════════

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SalaireService;
use App\Services\PresenceService;
use App\Models\Salaire;
use App\Models\Absence;
use Illuminate\Http\Request;

class RapportController extends Controller
{
    public function __construct(
        private SalaireService  $salaireService,
        private PresenceService $presenceService
    ) {}

    public function index(Request $request)
    {
        $annee = $request->annee ?? now()->year;

        $stats          = $this->salaireService->statsGlobales($annee);
        $tauxAnnuel     = round($stats['taux_presence_moyen'], 1);
        $absencesAnnee  = Absence::whereYear('date', $annee)->count();

        // Taux par employé
        $employes = \App\Models\Employe::actifs()->get();
        $tauxParEmploye = $employes->map(function ($e) use ($annee) {
            $s = Salaire::where('employe_id', $e->id)->where('annee', $annee)->get();
            return [
                'employe'          => $e,
                'taux'             => $s->count() ? round($s->avg('taux_presence'), 1) : 0,
                'jours_travailles' => $s->sum('jours_travailles'),
                'nb_absences'      => $s->sum('nb_absences'),
                'nb_retards'       => $s->sum('nb_retards'),
                'salaire_annuel'   => $s->sum('salaire_net'),
            ];
        })->sortByDesc('taux')->values();

        return view('admin.rapports.index', compact(
            'annee',
            'stats',
            'tauxAnnuel',
            'absencesAnnee',
            'tauxParEmploye'
        ));
    }
}
