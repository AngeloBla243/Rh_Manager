<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salaire;
use App\Models\Employe;
use App\Services\SalaireService;
use Illuminate\Http\Request;

class SalaireController extends Controller
{
    public function __construct(private SalaireService $service) {}

    public function index(Request $request)
    {
        $mois  = $request->mois  ?? now()->month;
        $annee = $request->annee ?? now()->year;

        $salaires = Salaire::with('employe')
            ->where('mois', $mois)
            ->where('annee', $annee)
            ->get();

        $totalNet  = $salaires->sum('salaire_net');
        $totalBrut = $salaires->sum('salaire_brut');
        $penalites = $salaires->sum('total_penalites');

        return view(
            'admin.salaires.index',
            compact('salaires', 'totalNet', 'totalBrut', 'penalites', 'mois', 'annee')
        );
    }

    // Calculer tous les salaires d'un mois
    public function calculer(Request $request)
    {
        $request->validate([
            'mois'  => 'required|integer|between:1,12',
            'annee' => 'required|integer|min:2020',
        ]);

        $count = $this->service->calculerMois($request->mois, $request->annee);

        return back()->with('success', "{$count} fiches de salaire calculées.");
    }

    // Marquer comme payé
    public function marquerPaye(Salaire $salaire)
    {
        $salaire->update([
            'statut_paiement' => 'paye',
            'date_paiement'   => now(),
        ]);

        return back()->with('success', 'Salaire marqué comme payé.');
    }
}
