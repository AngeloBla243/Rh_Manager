<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Services\EmployeService;
use Illuminate\Http\Request;

class EmployeController extends Controller
{
    public function __construct(private EmployeService $service) {}

    public function index(Request $request)
    {
        $employes = Employe::query()
            ->when(
                $request->search,
                fn($q) =>
                $q->where('nom', 'like', "%{$request->search}%")
                    ->orWhere('prenom', 'like', "%{$request->search}%")
            )
            ->when(
                $request->fonction,
                fn($q) =>
                $q->where('fonction', $request->fonction)
            )
            ->orderBy('nom')
            ->paginate(15);

        return view('admin.employes.index', compact('employes'));
    }

    public function create()
    {
        return view('admin.employes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'             => 'required|string|max:100',
            'postnom'         => 'nullable|string|max:100',
            'prenom'          => 'required|string|max:100',
            'date_naissance'  => 'required|date|before:today',
            'fonction'        => 'required|string|max:100',
            'annee_engagement' => 'required|integer|min:1990|max:' . date('Y'),
            'salaire_base'    => 'required|numeric|min:0',
            'photo'           => 'nullable|image|max:2048',
        ]);

        $employe = $this->service->creer($validated, $request->file('photo'));

        return redirect()
            ->route('admin.employes.show', $employe)
            ->with('success', "Employé {$employe->nom_complet} créé avec succès.");
    }

    public function show(Employe $employe)
    {
        $employe->load(['presences', 'absences', 'salaires', 'documents', 'conges']);
        return view('admin.employes.show', compact('employe'));
    }

    public function edit(Employe $employe)
    {
        return view('admin.employes.edit', compact('employe'));
    }

    public function update(Request $request, Employe $employe)
    {
        $validated = $request->validate([
            'nom'             => 'required|string|max:100',
            'postnom'         => 'nullable|string|max:100',
            'prenom'          => 'required|string|max:100',
            'date_naissance'  => 'required|date',
            'fonction'        => 'required|string|max:100',
            'annee_engagement' => 'required|integer',
            'salaire_base'    => 'required|numeric|min:0',
            'photo'           => 'nullable|image|max:2048',
        ]);

        $this->service->modifier($employe, $validated, $request->file('photo'));

        return redirect()
            ->route('admin.employes.show', $employe)
            ->with('success', 'Informations mises à jour.');
    }

    public function destroy(Employe $employe)
    {
        $employe->delete();
        return redirect()
            ->route('admin.employes.index')
            ->with('success', 'Employé archivé.');
    }

    // Enregistrer document
    public function ajouterDocument(Request $request, Employe $employe)
    {
        $request->validate([
            'type_document' => 'required|string',
            'fichier'       => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $this->service->ajouterDocument($employe, $request);

        return back()->with('success', 'Document ajouté.');
    }
}
