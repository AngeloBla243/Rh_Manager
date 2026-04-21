<?php
// app/Http/Controllers/Admin/JourFerieController.php
// Commande : php artisan make:controller Admin/JourFerieController --resource

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JourFerie;
use Illuminate\Http\Request;
use Carbon\Carbon;

class JourFerieController extends Controller
{
    // ──────────────────────────────────────────────────────────────────
    // GET /admin/jours-feries
    // Liste tous les jours fériés de l'année en cours (ou filtrée)
    // ──────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $annee = $request->annee ?? now()->year;

        $joursFeries = JourFerie::whereYear('date', $annee)
            ->orderBy('date')
            ->get();

        // Années disponibles pour le filtre
        $annees = JourFerie::selectRaw('YEAR(date) as annee')
            ->groupBy('annee')
            ->orderByDesc('annee')
            ->pluck('annee');

        return view('admin.jours-feries.index', compact('joursFeries', 'annee', 'annees'));
    }

    // ──────────────────────────────────────────────────────────────────
    // POST /admin/jours-feries
    // Créer un jour férié (depuis la modal dans la vue Congés)
    // ──────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date'      => 'required|date',
            'libelle'   => 'required|string|max:150',
            'recurrent' => 'nullable|boolean',
        ]);

        // Vérifier qu'il n'existe pas déjà pour cette date
        if (JourFerie::where('date', $validated['date'])->exists()) {
            return back()
                ->with('error', 'Un jour férié existe déjà pour cette date.')
                ->withInput();
        }

        $jourFerie = JourFerie::create([
            'date'      => $validated['date'],
            'libelle'   => $validated['libelle'],
            'recurrent' => $request->boolean('recurrent', true),
        ]);

        // Si récurrent, proposer de l'ajouter aux années suivantes (optionnel)
        // On le fait automatiquement pour l'année +1 si le flag est coché
        if ($jourFerie->recurrent) {
            $dateAnneeProchaine = Carbon::parse($jourFerie->date)->addYear();
            if (!JourFerie::where('date', $dateAnneeProchaine)->exists()) {
                JourFerie::create([
                    'date'      => $dateAnneeProchaine,
                    'libelle'   => $jourFerie->libelle,
                    'recurrent' => true,
                ]);
            }
        }

        return back()->with('success', "Jour férié « {$jourFerie->libelle} » ajouté.");
    }

    // ──────────────────────────────────────────────────────────────────
    // PUT /admin/jours-feries/{jourFerie}
    // Modifier un jour férié
    // ──────────────────────────────────────────────────────────────────
    public function update(Request $request, JourFerie $jourFerie)
    {
        $validated = $request->validate([
            'date'      => 'required|date',
            'libelle'   => 'required|string|max:150',
            'recurrent' => 'nullable|boolean',
        ]);

        $jourFerie->update([
            'date'      => $validated['date'],
            'libelle'   => $validated['libelle'],
            'recurrent' => $request->boolean('recurrent'),
        ]);

        return back()->with('success', 'Jour férié mis à jour.');
    }

    // ──────────────────────────────────────────────────────────────────
    // DELETE /admin/jours-feries/{jourFerie}
    // Supprimer un jour férié
    // ──────────────────────────────────────────────────────────────────
    public function destroy(JourFerie $jourFerie)
    {
        $libelle = $jourFerie->libelle;
        $jourFerie->delete();

        return back()->with('success', "Jour férié « {$libelle} » supprimé.");
    }

    // ──────────────────────────────────────────────────────────────────
    // POST /admin/jours-feries/dupliquer-annee
    // Duplique tous les jours fériés récurrents vers l'année suivante
    // ──────────────────────────────────────────────────────────────────
    public function dupliquerAnnee(Request $request)
    {
        $request->validate(['annee_source' => 'required|integer|min:2020']);

        $anneeSource  = $request->annee_source;
        $anneeCible   = $anneeSource + 1;
        $feries       = JourFerie::whereYear('date', $anneeSource)
            ->where('recurrent', true)
            ->get();

        $crees = 0;
        foreach ($feries as $f) {
            $nouvelle = Carbon::parse($f->date)->addYear();
            if (!JourFerie::where('date', $nouvelle->toDateString())->exists()) {
                JourFerie::create([
                    'date'      => $nouvelle->toDateString(),
                    'libelle'   => $f->libelle,
                    'recurrent' => true,
                ]);
                $crees++;
            }
        }

        return back()->with('success', "{$crees} jour(s) férié(s) dupliqué(s) vers {$anneeCible}.");
    }
}
