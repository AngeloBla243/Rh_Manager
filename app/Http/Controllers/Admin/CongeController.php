<?php
// app/Http/Controllers/Admin/CongeController.php
// Commande : php artisan make:controller Admin/CongeController --resource

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conge;
use App\Models\Employe;
use App\Models\JourFerie;
use App\Models\Absence;
use App\Models\Parametre;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CongeController extends Controller
{
    // ──────────────────────────────────────────────────────────────────
    // GET /admin/conges
    // Liste les congés avec filtres
    // ──────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $statut    = $request->statut;
        $employeId = $request->employe_id;
        $annee     = $request->annee ?? now()->year;

        $conges = Conge::with('employe')
            ->whereYear('date_debut', $annee)
            ->when($statut,    fn($q) => $q->where('statut', $statut))
            ->when($employeId, fn($q) => $q->where('employe_id', $employeId))
            ->orderByDesc('date_debut')
            ->get();

        $employes    = Employe::where('statut', 'actif')->orderBy('nom')->get();
        $joursFeries = JourFerie::whereYear('date', $annee)->orderBy('date')->get();

        return view('admin.conges.index', compact(
            'conges',
            'employes',
            'joursFeries',
            'statut',
            'annee'
        ));
    }

    // ──────────────────────────────────────────────────────────────────
    // GET /admin/conges/create
    // Formulaire de création
    // ──────────────────────────────────────────────────────────────────
    public function create()
    {
        $employes = Employe::where('statut', 'actif')->orderBy('nom')->get();
        return view('admin.conges.create', compact('employes'));
    }

    // ──────────────────────────────────────────────────────────────────
    // POST /admin/conges
    // Enregistrer une nouvelle demande de congé
    // ──────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employe_id' => 'required|exists:employes,id',
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after_or_equal:date_debut',
            'type'       => 'required|in:annuel,maladie,maternite,sans_solde,exceptionnel',
            'motif'      => 'nullable|string|max:500',
            'statut'     => 'nullable|in:en_attente,approuve',
            'document'   => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        // Calculer le nombre de jours ouvrés (sans week-ends ni fériés)
        $validated['nombre_jours'] = $this->compterJoursOuvresConge(
            $validated['date_debut'],
            $validated['date_fin']
        );
        $validated['statut'] = $validated['statut'] ?? 'en_attente';

        if ($request->hasFile('document')) {
            $validated['document'] = $request->file('document')
                ->store('conges/documents', 'local');
        }

        $conge = Conge::create($validated);

        // Si approuvé directement, créer les absences de type "congé"
        if ($conge->statut === 'approuve') {
            $this->creerAbsencesConge($conge);
        }

        return redirect()
            ->route('admin.conges.index')
            ->with('success', "Congé de {$conge->employe->nom_complet} enregistré ({$conge->nombre_jours} jours ouvrés).");
    }

    // ──────────────────────────────────────────────────────────────────
    // GET /admin/conges/{conge}
    // Détail d'un congé
    // ──────────────────────────────────────────────────────────────────
    public function show(Conge $conge)
    {
        $conge->load('employe');
        return view('admin.conges.show', compact('conge'));
    }

    // ──────────────────────────────────────────────────────────────────
    // PUT /admin/conges/{conge}
    // Modifier un congé (dates, motif)
    // ──────────────────────────────────────────────────────────────────
    public function update(Request $request, Conge $conge)
    {
        // Interdire la modification si déjà approuvé
        if ($conge->statut === 'approuve') {
            return back()->with('error', 'Un congé approuvé ne peut plus être modifié. Refusez-le d\'abord.');
        }

        $validated = $request->validate([
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after_or_equal:date_debut',
            'type'       => 'required|in:annuel,maladie,maternite,sans_solde,exceptionnel',
            'motif'      => 'nullable|string|max:500',
        ]);

        $validated['nombre_jours'] = $this->compterJoursOuvresConge(
            $validated['date_debut'],
            $validated['date_fin']
        );

        $conge->update($validated);

        return back()->with('success', 'Congé mis à jour.');
    }

    // ──────────────────────────────────────────────────────────────────
    // DELETE /admin/conges/{conge}
    // Annuler/supprimer un congé
    // ──────────────────────────────────────────────────────────────────
    public function destroy(Conge $conge)
    {
        // Supprimer les absences "congé" liées
        Absence::where('employe_id', $conge->employe_id)
            ->whereBetween('date', [$conge->date_debut, $conge->date_fin])
            ->where('type', 'conge')
            ->delete();

        $conge->update(['statut' => 'annule']);

        return back()->with('success', 'Congé annulé.');
    }

    // ──────────────────────────────────────────────────────────────────
    // POST /admin/conges/{conge}/approuver
    // Approuver une demande de congé
    // ──────────────────────────────────────────────────────────────────
    public function approuver(Request $request, Conge $conge)
    {
        if ($conge->statut !== 'en_attente') {
            return back()->with('error', 'Ce congé a déjà été traité.');
        }

        $conge->update([
            'statut'              => 'approuve',
            'commentaire_admin'   => $request->commentaire,
        ]);

        // Créer les entrées d'absences de type "congé" dans la table absences
        // (pour que les salaires soient calculés correctement = 0 pénalité)
        $this->creerAbsencesConge($conge);

        return back()->with('success', "Congé de {$conge->employe->nom_complet} approuvé.");
    }

    // ──────────────────────────────────────────────────────────────────
    // POST /admin/conges/{conge}/refuser
    // Refuser une demande de congé
    // ──────────────────────────────────────────────────────────────────
    public function refuser(Request $request, Conge $conge)
    {
        $request->validate([
            'commentaire' => 'nullable|string|max:500',
        ]);

        $conge->update([
            'statut'            => 'refuse',
            'commentaire_admin' => $request->commentaire ?? 'Demande refusée.',
        ]);

        return back()->with('success', "Congé de {$conge->employe->nom_complet} refusé.");
    }

    // ──────────────────────────────────────────────────────────────────
    // MÉTHODES PRIVÉES
    // ──────────────────────────────────────────────────────────────────

    /**
     * Compter les jours ouvrés entre deux dates (sans week-ends ni fériés)
     */
    private function compterJoursOuvresConge(string $debut, string $fin): int
    {
        $start   = Carbon::parse($debut);
        $end     = Carbon::parse($fin);
        $feries  = JourFerie::whereBetween('date', [$start, $end])
            ->pluck('date')
            ->map(fn($d) => (string) $d);
        $count   = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if (!$current->isWeekend() && !$feries->contains($current->toDateString())) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }

    /**
     * Créer une absence de type "conge" pour chaque jour ouvré du congé.
     * Pénalité = 0 (congé approuvé = absence légale)
     */
    private function creerAbsencesConge(Conge $conge): void
    {
        $current = Carbon::parse($conge->date_debut);
        $fin     = Carbon::parse($conge->date_fin);
        $feries  = JourFerie::whereBetween('date', [$current, $fin])
            ->pluck('date')
            ->map(fn($d) => (string) $d);

        while ($current->lte($fin)) {
            if (!$current->isWeekend() && !$feries->contains($current->toDateString())) {
                Absence::updateOrCreate(
                    ['employe_id' => $conge->employe_id, 'date' => $current->toDateString()],
                    [
                        'type'       => 'conge',
                        'motif'      => "Congé approuvé ({$conge->type})",
                        'penalite'   => 0,
                        'approuvee'  => true,
                        'approuvee_par' => auth()->id(),
                    ]
                );
            }
            $current->addDay();
        }
    }
}
