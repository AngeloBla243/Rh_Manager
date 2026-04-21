<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use App\Models\Employe;
use App\Services\PresenceService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PresenceController extends Controller
{
    public function __construct(private PresenceService $service) {}

    public function index(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();

        $presences = Presence::with('employe')
            ->whereDate('date', $date)
            ->orderBy('heure_entree')
            ->get();

        $employes = Employe::where('statut', 'actif')->orderBy('nom')->get();

        $stats = [
            'presents' => $presences->where('est_valide', true)->count(),
            'retards'  => $presences->where('est_retard', true)->count(),
            'absents'  => $employes->count() - $presences->count(),
        ];

        return view('admin.presences.index', compact('presences', 'employes', 'date', 'stats'));
    }

    // Pointage manuel par l'admin
    public function pointageManuel(Request $request)
    {
        $request->validate([
            'employe_id' => 'required|exists:employes,id',
            'type'       => 'required|in:entree,sortie',
            'heure'      => 'required|date_format:H:i',
            'date'       => 'required|date',
        ]);

        $this->service->enregistrerPointage(
            $request->employe_id,
            $request->type,
            $request->heure,
            $request->date,
            'manuel'
        );

        return back()->with('success', 'Pointage enregistré.');
    }

    // Rapport mensuel
    public function rapport(Request $request)
    {
        $mois  = $request->mois  ?? now()->month;
        $annee = $request->annee ?? now()->year;

        $rapport = $this->service->genererRapportMensuel($mois, $annee);

        return view('admin.presences.rapport', compact('rapport', 'mois', 'annee'));
    }
}
