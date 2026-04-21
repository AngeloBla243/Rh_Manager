<?php
// app/Http/Controllers/Admin/DocumentEmployeController.php
// Commande : php artisan make:controller Admin/DocumentEmployeController

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\DocumentEmploye;
use App\Models\Parametre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentEmployeController extends Controller
{
    // ──────────────────────────────────────────────────────────────────
    // GET /admin/employes/{employe}/documents
    // Liste les documents d'un employé (utilisé en JSON pour l'API)
    // ──────────────────────────────────────────────────────────────────
    public function index(Employe $employe): \Illuminate\Http\JsonResponse
    {
        $documents = $employe->documents()->orderByDesc('created_at')->get()->map(fn($d) => [
            'id'            => $d->id,
            'type'          => $d->type_document,
            'nom'           => $d->nom_fichier,
            'extension'     => strtoupper($d->extension),
            'taille'        => $this->formatTaille($d->taille_octets),
            'date'          => $d->created_at->format('d/m/Y'),
            'expiration'    => $d->date_expiration?->format('d/m/Y'),
            'expiredBientot' => $d->date_expiration && $d->date_expiration->lt(now()->addDays(30)),
            'download_url'  => route('admin.employes.documents.download', $d),
        ]);

        return response()->json($documents);
    }

    // ──────────────────────────────────────────────────────────────────
    // POST /admin/employes/{employe}/documents
    // Ajouter un document à un employé
    // ──────────────────────────────────────────────────────────────────
    public function store(Request $request, Employe $employe)
    {
        $typesConfig = json_decode(Parametre::valeur('types_documents', '[]'), true);
        $typesValides = array_merge($typesConfig, ['autre']);

        $request->validate([
            'type_document'   => 'required|string|max:100',
            'fichier'         => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
            'date_expiration' => 'nullable|date|after:today',
        ]);

        $fichier = $request->file('fichier');
        $chemin  = $fichier->store(
            "employes/{$employe->id}/documents",
            'local'                                // stockage privé (non public)
        );

        $document = $employe->documents()->create([
            'type_document'   => $request->type_document,
            'nom_fichier'     => $fichier->getClientOriginalName(),
            'chemin_fichier'  => $chemin,
            'extension'       => strtolower($fichier->getClientOriginalExtension()),
            'taille_octets'   => $fichier->getSize(),
            'date_expiration' => $request->date_expiration,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Document ajouté.',
                'document' => $document,
            ]);
        }

        return back()->with('success', "Document « {$document->type_document} » ajouté.");
    }

    // ──────────────────────────────────────────────────────────────────
    // GET /admin/employes/documents/{document}/download
    // Télécharger un document (stocké en zone privée)
    // ──────────────────────────────────────────────────────────────────
    public function download(DocumentEmploye $document): StreamedResponse
    {
        // Vérifier que le fichier existe
        if (!Storage::disk('local')->exists($document->chemin_fichier)) {
            abort(404, 'Fichier introuvable sur le serveur.');
        }

        return Storage::disk('local')->download(
            $document->chemin_fichier,
            $document->nom_fichier         // nom original au téléchargement
        );
    }

    // ──────────────────────────────────────────────────────────────────
    // GET /admin/employes/documents/{document}/preview
    // Prévisualiser un fichier image ou PDF dans le navigateur
    // ──────────────────────────────────────────────────────────────────
    public function preview(DocumentEmploye $document)
    {
        if (!Storage::disk('local')->exists($document->chemin_fichier)) {
            abort(404);
        }

        $mimeTypes = [
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'webp' => 'image/webp',
        ];

        $mime = $mimeTypes[$document->extension] ?? 'application/octet-stream';

        return response()->streamDownload(
            function () use ($document) {
                echo Storage::disk('local')->get($document->chemin_fichier);
            },
            $document->nom_fichier,
            ['Content-Type' => $mime, 'Content-Disposition' => 'inline']
        );
    }

    // ──────────────────────────────────────────────────────────────────
    // PUT /admin/employes/documents/{document}
    // Mettre à jour les métadonnées d'un document (type, date expiration)
    // ──────────────────────────────────────────────────────────────────
    public function update(Request $request, DocumentEmploye $document)
    {
        $request->validate([
            'type_document'   => 'required|string|max:100',
            'date_expiration' => 'nullable|date',
        ]);

        $document->update([
            'type_document'   => $request->type_document,
            'date_expiration' => $request->date_expiration,
        ]);

        return back()->with('success', 'Document mis à jour.');
    }

    // ──────────────────────────────────────────────────────────────────
    // DELETE /admin/employes/documents/{document}
    // Supprimer un document (fichier + enregistrement BDD)
    // ──────────────────────────────────────────────────────────────────
    public function destroy(DocumentEmploye $document)
    {
        // Supprimer le fichier physique
        if (Storage::disk('local')->exists($document->chemin_fichier)) {
            Storage::disk('local')->delete($document->chemin_fichier);
        }

        $nom = $document->type_document;
        $document->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Document supprimé.']);
        }

        return back()->with('success', "Document « {$nom} » supprimé.");
    }

    // ──────────────────────────────────────────────────────────────────
    // MÉTHODE PRIVÉE — Formatage de la taille
    // ──────────────────────────────────────────────────────────────────
    private function formatTaille(int $octets): string
    {
        if ($octets >= 1_048_576) return round($octets / 1_048_576, 1) . ' Mo';
        if ($octets >= 1_024)     return round($octets / 1_024, 1) . ' Ko';
        return $octets . ' o';
    }
}
