<?php

namespace App\Services;

use App\Models\Salaire;
use App\Models\Employe;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    // Fiche de paie individuelle mensuelle
    public function fichePaieIndividuelle(Salaire $salaire): \Illuminate\Http\Response
    {
        $salaire->load('employe');

        $pdf = Pdf::loadView('pdf.fiche-paie-individuelle', [
            'salaire' => $salaire,
            'employe' => $salaire->employe,
        ])->setPaper('a4', 'portrait');

        $nomFichier = "fiche-paie-{$salaire->employe->matricule}-{$salaire->mois}-{$salaire->annee}.pdf";

        return $pdf->download($nomFichier);
    }

    // Fiches collectives — tous les employés d'un mois
    public function fichesPaieCollectives(int $mois, int $annee): \Illuminate\Http\Response
    {
        $salaires = Salaire::with('employe')
            ->where('mois', $mois)
            ->where('annee', $annee)
            ->orderBy('employe_id')
            ->get();

        $pdf = Pdf::loadView('pdf.fiches-collectives', [
            'salaires' => $salaires,
            'mois'     => $mois,
            'annee'    => $annee,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("fiches-paie-collectives-{$mois}-{$annee}.pdf");
    }

    // Carte de service employé
    public function carteService(Employe $employe): \Illuminate\Http\Response
    {
        $pdf = Pdf::loadView('pdf.carte-service', [
            'employe' => $employe,
            'annee'   => now()->year,
        ])->setPaper([0, 0, 242, 153], 'landscape'); // Format carte bancaire en points

        return $pdf->download("carte-{$employe->matricule}.pdf");
    }

    // Rapport de présences mensuel
    public function rapportPresences(array $rapport, int $mois, int $annee): \Illuminate\Http\Response
    {
        $pdf = Pdf::loadView('pdf.rapport-presences', compact('rapport', 'mois', 'annee'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("rapport-presences-{$mois}-{$annee}.pdf");
    }
}
