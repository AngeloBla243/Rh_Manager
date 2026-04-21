<?php
// app/Http/Controllers/Admin/StatistiqueController.php
// Commande : php artisan make:controller Admin/StatistiqueController

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salaire;
use App\Models\Absence;
use App\Models\Employe;
use App\Models\Presence;
use App\Services\PresenceService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StatistiqueController extends Controller
{
    public function __construct(private PresenceService $presenceService) {}

    // ──────────────────────────────────────────────────────────────────
    // GET /admin/statistiques
    // ──────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $annee = $request->annee ?? now()->year;

        // ── Taux de présence par mois (12 mois de l'année) ────────────
        $presencesParMois = [];
        for ($m = 1; $m <= 12; $m++) {
            $s = Salaire::whereMonth('mois', $m)->where('annee', $annee);
            $count = $s->count();
            $presencesParMois[] = $count > 0
                ? round($s->avg('taux_presence'), 1)
                : 0;
        }

        // ── Dépenses salariales par mois ───────────────────────────────
        $depensesParMois = [];
        for ($m = 1; $m <= 12; $m++) {
            $depensesParMois[] = (float) Salaire::where('mois', $m)
                ->where('annee', $annee)
                ->sum('salaire_net');
        }

        // ── Stats globales de l'année ──────────────────────────────────
        $tauxAnnuel = Salaire::where('annee', $annee)->avg('taux_presence');

        $absencesAnnee = Absence::whereYear('date', $annee)->count();

        $absencesNonJustifiees = Absence::whereYear('date', $annee)
            ->where('type', 'non_justifiee')->count();

        $depensesAnnee = (float) Salaire::where('annee', $annee)->sum('salaire_net');

        $penalitesAnnee = (float) Salaire::where('annee', $annee)->sum('total_penalites');

        // ── Taux par employé sur toute l'année ────────────────────────
        $employes = Employe::where('statut', 'actif')->get();

        $tauxParEmploye = $employes->map(function ($e) use ($annee) {
            $salaires = Salaire::where('employe_id', $e->id)
                ->where('annee', $annee)
                ->get();

            return [
                'employe'          => $e,
                'taux'             => $salaires->count() > 0
                    ? round($salaires->avg('taux_presence'), 1)
                    : 0,
                'jours_travailles' => $salaires->sum('jours_travailles'),
                'jours_ouvres'     => $salaires->sum('jours_ouvres'),
                'nb_absences'      => $salaires->sum('nb_absences'),
                'nb_retards'       => $salaires->sum('nb_retards'),
                'salaire_annuel'   => $salaires->sum('salaire_net'),
            ];
        })->sortByDesc('taux')->values();

        return view('admin.statistiques.index', compact(
            'annee',
            'presencesParMois',
            'depensesParMois',
            'tauxAnnuel',
            'absencesAnnee',
            'absencesNonJustifiees',
            'depensesAnnee',
            'penalitesAnnee',
            'tauxParEmploye'
        ));
    }
}
