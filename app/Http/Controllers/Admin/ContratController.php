<?php
// app/Http/Controllers/Admin/ContratController.php
// Commande : php artisan make:controller Admin/ContratController --resource

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contrat;
use App\Models\Employe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ContratController extends Controller
{
    // ──────────────────────────────────────────────────────────────────
    // GET /admin/contrats
    // ──────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $type      = $request->type;
        $statut    = $request->statut;
        $employeId = $request->employe_id;
        $alerte    = $request->alerte; // 'expirant' pour filtrer les contrats proches de la fin

        $contrats = Contrat::with('employe')
            ->when($type,      fn($q) => $q->where('type', $type))
            ->when($statut,    fn($q) => $q->where('statut', $statut))
            ->when($employeId, fn($q) => $q->where('employe_id', $employeId))
            ->when($alerte === 'expirant', fn($q) => $q->expirantDans(30))
            ->orderByDesc('date_debut')
            ->paginate(20);

        $employes = Employe::actifs()->orderBy('nom')->get();

        // Alertes : contrats expirant dans 30 jours
        $expirantBientot = Contrat::with('employe')->expirantDans(30)->get();
        // Contrats arrivés à terme non mis à jour
        $aMettreAJour = Contrat::with('employe')->aMettreAJour()->get();

        $stats = [
            'total'           => Contrat::actifs()->count(),
            'cdi'             => Contrat::actifs()->deType('CDI')->count(),
            'cdd'             => Contrat::actifs()->deType('CDD')->count(),
            'stages'          => Contrat::actifs()->deType('Stage')->count(),
            'expirant_bientot' => $expirantBientot->count(),
        ];

        return view('admin.contrats.index', compact(
            'contrats',
            'employes',
            'stats',
            'expirantBientot',
            'aMettreAJour',
            'type',
            'statut'
        ));
    }

    // ──────────────────────────────────────────────────────────────────
    // GET /admin/contrats/create
    // ──────────────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        $employes   = Employe::actifs()->orderBy('nom')->get();
        $employeId  = $request->employe_id; // pré-sélection depuis la fiche employé
        return view('admin.contrats.create', compact('employes', 'employeId'));
    }

    // ──────────────────────────────────────────────────────────────────
    // POST /admin/contrats
    // ──────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employe_id'          => 'required|exists:employes,id',
            'type'                => 'required|in:CDI,CDD,Stage,Interim,Freelance,Autre',
            'date_debut'          => 'required|date',
            'date_fin'            => 'nullable|date|after:date_debut',
            'poste'               => 'nullable|string|max:100',
            'salaire_contractuel' => 'nullable|numeric|min:0',
            'statut'              => 'required|in:actif,suspendu,expire,resilie,renouvele',
            'description'         => 'nullable|string',
            'numero_contrat'      => 'nullable|string|max:50',
            'periode_essai'       => 'nullable|boolean',
            'fin_periode_essai'   => 'nullable|date|after:date_debut',
            'document'            => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
            'renouvelle_depuis'   => 'nullable|exists:contrats,id',
        ]);

        $validated['cree_par'] = auth()->id();

        if ($request->hasFile('document')) {
            $validated['document_path'] = $request->file('document')
                ->store("employes/{$validated['employe_id']}/contrats", 'local');
        }

        // Si c'est un renouvellement, marquer l'ancien comme "renouvelé"
        if (!empty($validated['renouvelle_depuis'])) {
            Contrat::where('id', $validated['renouvelle_depuis'])
                ->update(['statut' => 'renouvele']);
        }

        $contrat = Contrat::create($validated);

        return redirect()
            ->route('admin.contrats.show', $contrat)
            ->with('success', "Contrat {$contrat->type} créé pour {$contrat->employe->nom_complet}.");
    }

    // ──────────────────────────────────────────────────────────────────
    // GET /admin/contrats/{contrat}
    // ──────────────────────────────────────────────────────────────────
    public function show(Contrat $contrat)
    {
        $contrat->load('employe', 'renouvellDepuis', 'renouvellements.employe');
        return view('admin.contrats.show', compact('contrat'));
    }

    // ──────────────────────────────────────────────────────────────────
    // GET /admin/contrats/{contrat}/edit
    // ──────────────────────────────────────────────────────────────────
    public function edit(Contrat $contrat)
    {
        $employes = Employe::actifs()->orderBy('nom')->get();
        return view('admin.contrats.edit', compact('contrat', 'employes'));
    }

    // ──────────────────────────────────────────────────────────────────
    // PUT /admin/contrats/{contrat}
    // ──────────────────────────────────────────────────────────────────
    public function update(Request $request, Contrat $contrat)
    {
        $validated = $request->validate([
            'type'                => 'required|in:CDI,CDD,Stage,Interim,Freelance,Autre',
            'date_debut'          => 'required|date',
            'date_fin'            => 'nullable|date|after:date_debut',
            'poste'               => 'nullable|string|max:100',
            'salaire_contractuel' => 'nullable|numeric|min:0',
            'statut'              => 'required|in:actif,suspendu,expire,resilie,renouvele',
            'description'         => 'nullable|string',
            'numero_contrat'      => 'nullable|string|max:50',
            'periode_essai'       => 'nullable|boolean',
            'fin_periode_essai'   => 'nullable|date',
            'document'            => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        if ($request->hasFile('document')) {
            if ($contrat->document_path) {
                Storage::disk('local')->delete($contrat->document_path);
            }
            $validated['document_path'] = $request->file('document')
                ->store("employes/{$contrat->employe_id}/contrats", 'local');
        }

        $contrat->update($validated);

        return redirect()
            ->route('admin.contrats.show', $contrat)
            ->with('success', 'Contrat mis à jour.');
    }

    // ──────────────────────────────────────────────────────────────────
    // DELETE /admin/contrats/{contrat}
    // ──────────────────────────────────────────────────────────────────
    public function destroy(Contrat $contrat)
    {
        if ($contrat->document_path) {
            Storage::disk('local')->delete($contrat->document_path);
        }
        $employe = $contrat->employe;
        $contrat->delete();

        return redirect()
            ->route('admin.contrats.index')
            ->with('success', "Contrat supprimé.");
    }

    // ──────────────────────────────────────────────────────────────────
    // POST /admin/contrats/{contrat}/resilier
    // Résilier un contrat avec motif
    // ──────────────────────────────────────────────────────────────────
    public function resilier(Request $request, Contrat $contrat)
    {
        $request->validate([
            'motif_resiliation' => 'required|string|max:500',
            'date_resiliation'  => 'required|date',
        ]);

        $contrat->update([
            'statut'      => 'resilie',
            'date_fin'    => $request->date_resiliation,
            'description' => ($contrat->description ? $contrat->description . "\n\n" : '')
                . "Résilié le {$request->date_resiliation} — {$request->motif_resiliation}",
        ]);

        return back()->with('success', 'Contrat résilié.');
    }

    // ──────────────────────────────────────────────────────────────────
    // POST /admin/contrats/{contrat}/renouveler
    // Renouveler un CDD
    // ──────────────────────────────────────────────────────────────────
    public function renouveler(Request $request, Contrat $contrat)
    {
        $request->validate([
            'nouvelle_date_fin' => 'required|date|after:' . ($contrat->date_fin ?? now()->toDateString()),
        ]);

        // Marquer l'ancien comme renouvelé
        $contrat->update(['statut' => 'renouvele']);

        // Créer le nouveau contrat
        $nouveau = Contrat::create([
            'employe_id'        => $contrat->employe_id,
            'type'              => $contrat->type,
            'date_debut'        => $contrat->date_fin
                ? $contrat->date_fin->addDay()->toDateString()
                : now()->toDateString(),
            'date_fin'          => $request->nouvelle_date_fin,
            'poste'             => $contrat->poste,
            'salaire_contractuel' => $contrat->salaire_contractuel,
            'statut'            => 'actif',
            'renouvelle_depuis' => $contrat->id,
            'cree_par'          => auth()->id(),
        ]);

        return redirect()
            ->route('admin.contrats.show', $nouveau)
            ->with('success', 'Contrat renouvelé avec succès.');
    }

    // ──────────────────────────────────────────────────────────────────
    // GET /admin/contrats/{contrat}/download
    // ──────────────────────────────────────────────────────────────────
    public function download(Contrat $contrat)
    {
        if (!$contrat->document_path || !Storage::disk('local')->exists($contrat->document_path)) {
            abort(404, 'Document introuvable.');
        }

        return Storage::disk('local')->download(
            $contrat->document_path,
            "contrat-{$contrat->employe->matricule}-{$contrat->type}.pdf"
        );
    }

    // ──────────────────────────────────────────────────────────────────
    // POST /admin/contrats/mettre-a-jour-expiration
    // Marque automatiquement comme "expiré" les contrats arrivés à terme
    // ──────────────────────────────────────────────────────────────────
    public function mettreAJourExpiration()
    {
        $count = Contrat::aMettreAJour()->update(['statut' => 'expire']);

        return back()->with('success', "{$count} contrat(s) marqué(s) comme expiré(s).");
    }
}
