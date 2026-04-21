<?php
// app/Http/Controllers/Admin/NotificationController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlerteRh;
use App\Services\AlerteService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private AlerteService $alerteService) {}

    public function index(Request $request)
    {
        $query = AlerteRh::with('employe')->recentes();

        if ($request->type)     $query->where('type', $request->type);
        if ($request->priorite) $query->where('priorite', $request->priorite);
        if ($request->lue === '0') $query->nonLues();

        $alertes  = $query->paginate(25);
        $nonLues  = AlerteRh::nonLues()->count();

        return view('admin.notifications.index', compact('alertes', 'nonLues'));
    }

    public function lire(AlerteRh $alerte)
    {
        $alerte->marquerLue();
        return back()->with('success', 'Alerte marquée comme lue.');
    }

    public function toutLire()
    {
        AlerteRh::nonLues()->update(['lue' => true, 'lue_at' => now()]);
        return back()->with('success', 'Toutes les alertes ont été marquées comme lues.');
    }

    public function destroy(AlerteRh $alerte)
    {
        $alerte->delete();
        return back()->with('success', 'Notification supprimée.');
    }

    public function generer()
    {
        $resultats = $this->alerteService->verifierToutes();
        $total = array_sum($resultats);
        return back()->with('success', "{$total} alerte(s) générée(s) : absences ({$resultats['absences']}), retards ({$resultats['retards']}), contrats ({$resultats['fins_contrat']}), documents ({$resultats['docs_expiration']}).");
    }
}
