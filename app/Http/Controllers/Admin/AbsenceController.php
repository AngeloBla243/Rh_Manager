<?php
// app/Http/Controllers/Admin/AbsenceController.php
// Commande : php artisan make:controller Admin/AbsenceController --resource

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Employe;
use App\Models\Parametre;
use App\Services\AbsenceService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsenceController extends Controller
{
    public function __construct(private AbsenceService $service) {}

    // ──────────────────────────────────────────────────────────────────
    // GET /admin/absences
    // Liste les absences avec filtres mois/année/employé
    // ──────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $mois    = $request->mois  ?? now()->month;
        $annee   = $request->annee ?? now()->year;
        $employe = $request->employe_id;

        $absences = Absence::with('employe')
            ->whereMonth('date', $mois)
            ->whereYear('date', $annee)
            ->when($employe, fn($q) => $q->where('employe_id', $employe))
            ->orderByDesc('date')
            ->get();

        $employes = Employe::where('statut', 'actif')->orderBy('nom')->get();

        // Stats rapides
        $stats = [
            'total'          => $absences->count(),
            'justifiees'     => $absences->where('type', 'justifiee')->count(),
            'non_justifiees' => $absences->where('type', 'non_justifiee')->count(),
            'total_penalites' => $absences->sum('penalite'),
        ];

        return view('admin.absences.index', compact(
            'absences',
            'employes',
            'stats',
            'mois',
            'annee'
        ));
    }

    // ──────────────────────────────────────────────────────────────────
    // POST /admin/absences
    // Enregistrer manuellement une absence
    // ──────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employe_id' => 'required|exists:employes,id',
            'date'       => 'required|date|before_or_equal:today',
            'type'       => 'required|in:justifiee,non_justifiee,conge,ferie',
            'motif'      => 'nullable|string|max:255',
        ]);

        // Calculer la pénalité automatiquement si non justifiée
        $penalite = 0;
        if ($validated['type'] === 'non_justifiee') {
            $employe   = Employe::findOrFail($validated['employe_id']);
            $penalite  = $this->service->calculerPenaliteAbsence($employe);
        }

        Absence::create([...$validated, 'penalite' => $penalite]);

        return back()->with('success', 'Absence enregistrée.');
    }

    // ──────────────────────────────────────────────────────────────────
    // PUT /admin/absences/{absence}
    // Modifier une absence (type, motif)
    // ──────────────────────────────────────────────────────────────────
    public function update(Request $request, Absence $absence)
    {
        $validated = $request->validate([
            'type'  => 'required|in:justifiee,non_justifiee,conge,ferie',
            'motif' => 'nullable|string|max:255',
        ]);

        // Si on passe à "justifiée", annuler la pénalité
        if ($validated['type'] === 'justifiee') {
            $validated['penalite'] = 0;
            $validated['approuvee'] = true;
            $validated['approuvee_par'] = auth()->id();
        }

        $absence->update($validated);

        return back()->with('success', 'Absence mise à jour.');
    }

    // ──────────────────────────────────────────────────────────────────
    // DELETE /admin/absences/{absence}
    // Supprimer une absence (ex: enregistrée par erreur)
    // ──────────────────────────────────────────────────────────────────
    public function destroy(Absence $absence)
    {
        $absence->delete();
        return back()->with('success', 'Absence supprimée.');
    }

    // ──────────────────────────────────────────────────────────────────
    // POST /admin/absences/{absence}/justifier
    // Justifier une absence non justifiée avec document
    // ──────────────────────────────────────────────────────────────────
    public function justifier(Request $request, Absence $absence)
    {
        $request->validate([
            'motif'                 => 'required|string|max:255',
            'document_justificatif' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $cheminDoc = null;
        if ($request->hasFile('document_justificatif')) {
            $cheminDoc = $request->file('document_justificatif')
                ->store("absences/{$absence->employe_id}/justificatifs", 'local');
        }

        $absence->update([
            'type'                  => 'justifiee',
            'motif'                 => $request->motif,
            'document_justificatif' => $cheminDoc,
            'penalite'              => 0,          // annulation de la pénalité
            'approuvee'             => true,
            'approuvee_par'         => auth()->id(),
        ]);

        return back()->with('success', 'Absence justifiée — pénalité annulée.');
    }

    // ──────────────────────────────────────────────────────────────────
    // POST /admin/absences/generer-depuis-presences
    // Génère automatiquement les absences à partir des présences
    // manquantes pour un mois donné (tâche admin)
    // ──────────────────────────────────────────────────────────────────
    public function genererDepuisPresences(Request $request)
    {
        $request->validate([
            'mois'  => 'required|integer|between:1,12',
            'annee' => 'required|integer|min:2020',
        ]);

        $count = $this->service->genererAbsencesManquantes(
            $request->mois,
            $request->annee
        );

        return back()->with('success', "{$count} absence(s) générée(s) automatiquement.");
    }
}
