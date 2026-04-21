<?php
// app/Http/Controllers/Admin/ParametreController.php
// Commande : php artisan make:controller Admin/ParametreController

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parametre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class ParametreController extends Controller
{
    // ──────────────────────────────────────────────────────────────────
    // GET /admin/parametres
    // ──────────────────────────────────────────────────────────────────
    public function index()
    {
        // Tous les paramètres en un tableau clé => valeur
        $params = Parametre::all()->pluck('valeur', 'cle');

        return view('admin.parametres.index', compact('params'));
    }

    // ──────────────────────────────────────────────────────────────────
    // POST /admin/parametres
    // Sauvegarder tous les paramètres du formulaire
    // ──────────────────────────────────────────────────────────────────
    public function update(Request $request)
    {
        $request->validate([
            'heure_arrivee'         => 'required|date_format:H:i',
            'heure_limite_retard'   => 'required|date_format:H:i',
            'heure_sortie'          => 'required|date_format:H:i',
            'conge_jours_par_an'    => 'required|integer|min:1|max:60',
            'penalite_absence_pct'  => 'required|integer|min:0|max:100',
            'penalite_retard_pct'   => 'required|integer|min:0|max:100',
            'nom_entreprise'        => 'nullable|string|max:200',
            'adresse_entreprise'    => 'nullable|string|max:300',
            'telephone_entreprise'  => 'nullable|string|max:30',
            'biometric_ip'          => 'nullable|ip',
            'biometric_port'        => 'nullable|integer|min:1|max:65535',
            'types_documents'       => 'nullable|array',
            'types_documents.*'     => 'string|max:100',
            // Mot de passe
            'current_password'      => 'nullable|string',
            'new_password'          => 'nullable|string|min:8|confirmed',
        ]);

        // Champs à sauvegarder directement
        $cles = [
            'heure_arrivee',
            'heure_limite_retard',
            'heure_sortie',
            'conge_jours_par_an',
            'penalite_absence_pct',
            'penalite_retard_pct',
            'nom_entreprise',
            'adresse_entreprise',
            'telephone_entreprise',
            'biometric_ip',
            'biometric_port',
        ];

        foreach ($cles as $cle) {
            if ($request->has($cle)) {
                Parametre::definir($cle, $request->input($cle));
            }
        }

        // Types de documents (JSON)
        if ($request->has('types_documents')) {
            $types = array_values(array_filter($request->types_documents));
            Parametre::definir('types_documents', json_encode($types));
        }

        // Logo de l'entreprise
        if ($request->hasFile('logo')) {
            $request->validate(['logo' => 'image|max:2048']);
            $chemin = $request->file('logo')->store('entreprise', 'public');
            Parametre::definir('logo_entreprise', $chemin);
        }

        // Changement de mot de passe
        if ($request->filled('current_password') && $request->filled('new_password')) {
            $user = auth()->user();
            if (!Hash::check($request->current_password, $user->password)) {
                return back()
                    ->withErrors(['current_password' => 'Mot de passe actuel incorrect.'])
                    ->with('error', 'Mot de passe actuel incorrect.');
            }
            $user->update(['password' => Hash::make($request->new_password)]);
        }

        // Vider tout le cache des paramètres
        Cache::flush();

        return back()->with('success', 'Paramètres sauvegardés avec succès.');
    }
}
