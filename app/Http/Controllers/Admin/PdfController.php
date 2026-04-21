<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salaire;
use App\Models\Employe;
use App\Services\PdfService;
use App\Services\PresenceService;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function __construct(
        private PdfService      $pdfService,
        private PresenceService $presenceService
    ) {}

    // ──────────────────────────────────────────────────────────────────
    // GET /admin/fiches
    // Page d'accueil des fiches de paie
    // ──────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $mois   = $request->mois  ?? now()->month;
        $annee  = $request->annee ?? now()->year;

        $employes  = Employe::where('statut', 'actif')->orderBy('nom')->get();
        $fonctions = Employe::where('statut', 'actif')->distinct()->pluck('fonction');

        $salaires = Salaire::with('employe')
            ->orderByDesc('annee')->orderByDesc('mois')
            ->paginate(25);

        // Données salaire du mois courant pour chaque employé (pour l'affichage JS)
        $salaireData = Salaire::where('mois', $mois)->where('annee', $annee)
            ->get()
            ->keyBy('employe_id')
            ->map(fn($s) => [
                'net'  => number_format($s->salaire_net, 2, ',', ' '),
                'taux' => $s->taux_presence,
                'id'   => $s->id,
            ]);

        return view('admin.fiches.index', compact(
            'employes',
            'fonctions',
            'salaires',
            'salaireData',
            'mois',
            'annee'
        ));
    }

    // ──────────────────────────────────────────────────────────────────
    // GET /admin/pdf/fiche-individuelle/{salaire}
    // Générer et télécharger la fiche de paie individuelle en PDF
    // ──────────────────────────────────────────────────────────────────
    public function ficheIndividuelle(Salaire $salaire)
    {
        return $this->pdfService->fichePaieIndividuelle($salaire);
    }

    // ──────────────────────────────────────────────────────────────────
    // GET /admin/pdf/fiches-collectives
    // Toutes les fiches d'un mois en un seul PDF
    // ──────────────────────────────────────────────────────────────────
    public function fichesCollectives(Request $request)
    {
        $mois  = $request->mois  ?? now()->month;
        $annee = $request->annee ?? now()->year;

        return $this->pdfService->fichesPaieCollectives($mois, $annee);
    }

    // ──────────────────────────────────────────────────────────────────
    // GET /admin/pdf/carte-service/{employe}
    // Générer la carte de service d'un employé (format bancaire)
    // ──────────────────────────────────────────────────────────────────
    public function carteService(Employe $employe)
    {
        return $this->pdfService->carteService($employe);
    }

    // ──────────────────────────────────────────────────────────────────
    // GET /admin/pdf/rapport-presences
    // Rapport mensuel de présences en PDF
    // ──────────────────────────────────────────────────────────────────
    public function rapportPresences(Request $request)
    {
        $mois  = $request->mois  ?? now()->month;
        $annee = $request->annee ?? now()->year;

        $rapport = $this->presenceService->genererRapportMensuel($mois, $annee);

        return $this->pdfService->rapportPresences($rapport, $mois, $annee);
    }
}
