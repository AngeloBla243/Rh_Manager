<?php

namespace App\Http\Controllers\Pointage;

use App\Http\Controllers\Controller;
use App\Services\PresenceService;
use App\Services\BiometriqueService;
use App\Models\Employe;
use Illuminate\Http\Request;

class PointageController extends Controller
{
    public function __construct(
        private PresenceService   $presenceService,
        private BiometriqueService $biometriqueService
    ) {}

    // Page publique de pointage (pas de auth middleware)
    public function index()
    {
        return view('pointage.index');
    }

    // Pointage par empreinte digitale (appelé par l'appareil ou le JS)
    public function pointageEmpreinte(Request $request)
    {
        $request->validate([
            'empreinte_id' => 'required|string',
            'type'         => 'required|in:entree,sortie',
        ]);

        $employe = Employe::where('empreinte_id', $request->empreinte_id)
            ->where('statut', 'actif')
            ->first();

        if (!$employe) {
            return response()->json([
                'succes'  => false,
                'message' => 'Empreinte non reconnue.',
            ], 404);
        }

        $presence = $this->presenceService->enregistrerPointage(
            $employe->id,
            $request->type,
            now()->format('H:i'),
            now()->toDateString(),
            'biometrique'
        );

        $message = $request->type === 'entree'
            ? "Bienvenue, {$employe->prenom} !"
            : "Au revoir, {$employe->prenom} !";

        return response()->json([
            'succes'  => true,
            'message' => $message,
            'employe' => $employe->nom_complet,
            'heure'   => now()->format('H:i'),
            'retard'  => $presence->est_retard,
        ]);
    }

    // Vérifier si l'appareil est connecté
    public function statutAppareil()
    {
        $connecte = $this->biometriqueService->verifierConnexion();

        return response()->json([
            'connecte' => $connecte,
            'message'  => $connecte ? 'Appareil connecté' : 'Appareil non disponible',
        ]);
    }
}
