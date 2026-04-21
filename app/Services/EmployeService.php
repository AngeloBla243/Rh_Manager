<?php

namespace App\Services;

use App\Models\Employe;
use App\Models\DocumentEmploye;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class EmployeService
{
    public function creer(array $donnees, ?UploadedFile $photo): Employe
    {
        // Générer le matricule automatiquement
        $dernierNum = Employe::withTrashed()->count() + 1;
        $donnees['matricule'] = 'EMP-' . str_pad($dernierNum, 3, '0', STR_PAD_LEFT);

        // Sauvegarder la photo
        if ($photo) {
            $donnees['photo'] = $photo->store('employes/photos', 'public');
        }

        return Employe::create($donnees);
    }

    public function modifier(Employe $employe, array $donnees, ?UploadedFile $photo): Employe
    {
        if ($photo) {
            // Supprimer l'ancienne photo
            if ($employe->photo) {
                Storage::disk('public')->delete($employe->photo);
            }
            $donnees['photo'] = $photo->store('employes/photos', 'public');
        }

        $employe->update($donnees);
        return $employe;
    }

    public function ajouterDocument(Employe $employe, Request $request): DocumentEmploye
    {
        $fichier = $request->file('fichier');
        $chemin  = $fichier->store("employes/{$employe->id}/documents", 'local');

        return $employe->documents()->create([
            'type_document'   => $request->type_document,
            'nom_fichier'     => $fichier->getClientOriginalName(),
            'chemin_fichier'  => $chemin,
            'extension'       => $fichier->getClientOriginalExtension(),
            'taille_octets'   => $fichier->getSize(),
        ]);
    }

    public function enregistrerEmpreinte(Employe $employe, string $empreinteId): void
    {
        $employe->update(['empreinte_id' => $empreinteId]);
    }
}
