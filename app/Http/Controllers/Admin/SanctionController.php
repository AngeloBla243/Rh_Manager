<?php
// app/Http/Controllers/Admin/SanctionController.php
// Commande : php artisan make:controller Admin/SanctionController --resource

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sanction;
use App\Models\Employe;
use Illuminate\Http\Request;

class SanctionController extends Controller
{
    // ──────────────────────────────────────────────────────────────────
    // GET /admin/sanctions
    // Liste toutes les sanctions avec filtres
    // ──────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $statut    = $request->statut;
        $type      = $request->type;
        $employeId = $request->employe_id;
        $annee     = $request->annee ?? now()->year;

        $sanctions = Sanction::with('employe', 'prononcePar')
            ->whereYear('date_debut', $annee)
            ->when($statut,    fn($q) => $q->where('statut', $statut))
            ->when($type,      fn($q) => $q->where('type', $type))
            ->when($employeId, fn($q) => $q->where('employe_id', $employeId))
            ->orderByDesc('date_debut')
            ->paginate(20);

        $employes = Employe::where('statut', 'actif')->orderBy('nom')->get();

        $stats = [
            'total'       => Sanction::whereYear('date_debut', $annee)->count(),
            'en_cours'    => Sanction::whereYear('date_debut', $annee)->where('statut', 'en_cours')->count(),
            'avertissements' => Sanction::whereYear('date_debut', $annee)
                ->whereIn('type', ['avertissement_verbal', 'avertissement_ecrit'])->count(),
            'mises_a_pied'   => Sanction::whereYear('date_debut', $annee)->where('type', 'mise_a_pied')->count(),
        ];

        return view('admin.sanctions.index', compact('sanctions', 'employes', 'stats', 'annee', 'statut', 'type'));
    }

    // ──────────────────────────────────────────────────────────────────
    // POST /admin/sanctions
    // Prononcer une nouvelle sanction
    // ──────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employe_id'     => 'required|exists:employes,id',
            'type'           => 'required|in:avertissement_verbal,avertissement_ecrit,mise_a_pied,retenue_salaire,licenciement,autre',
            'motif'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'date_debut'     => 'required|date',
            'date_fin'       => 'nullable|date|after_or_equal:date_debut',
            'montant_retenu' => 'nullable|numeric|min:0',
            'document'       => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        $validated['prononcee_par'] = auth()->id();

        if ($request->hasFile('document')) {
            $validated['document_path'] = $request->file('document')
                ->store('sanctions/documents', 'local');
        }

        $sanction = Sanction::create($validated);

        return back()->with('success', "Sanction « {$sanction->type_libelle} » prononcée contre {$sanction->employe->nom_complet}.");
    }

    // ──────────────────────────────────────────────────────────────────
    // POST /admin/sanctions/{sanction}/lever
    // Lever / annuler une sanction
    // ──────────────────────────────────────────────────────────────────
    public function lever(Request $request, Sanction $sanction)
    {
        $request->validate(['raison' => 'nullable|string|max:255']);

        $sanction->update([
            'statut'      => 'levee',
            'description' => ($sanction->description ? $sanction->description . "\n" : '')
                . "Levée le " . now()->format('d/m/Y')
                . ($request->raison ? " — " . $request->raison : ''),
        ]);

        return back()->with('success', 'Sanction levée.');
    }

    // ──────────────────────────────────────────────────────────────────
    // POST /admin/sanctions/{sanction}/signer
    // Marquer la sanction comme signée par l'employé
    // ──────────────────────────────────────────────────────────────────
    public function signer(Sanction $sanction)
    {
        $sanction->update([
            'signe_employe'  => true,
            'date_signature' => now()->toDateString(),
            'statut'         => 'executee',
        ]);

        return back()->with('success', 'Sanction marquée comme signée et exécutée.');
    }

    // ──────────────────────────────────────────────────────────────────
    // DELETE /admin/sanctions/{sanction}
    // Supprimer une sanction (soft delete)
    // ──────────────────────────────────────────────────────────────────
    public function destroy(Sanction $sanction)
    {
        $sanction->delete();
        return back()->with('success', 'Sanction archivée.');
    }
}
