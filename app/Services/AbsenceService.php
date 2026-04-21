<?php
// app/Services/AbsenceService.php

namespace App\Services;

use App\Models\Absence;
use App\Models\Employe;
use App\Models\JourFerie;
use App\Models\Parametre;
use App\Models\Sanction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AbsenceService
{
    public function __construct(private PresenceService $presenceService) {}

    // ──────────────────────────────────────────────────────────────────
    // Calculer la pénalité financière d'une absence non justifiée
    // = salaire_journalier × % pénalité configuré
    // ──────────────────────────────────────────────────────────────────
    public function calculerPenaliteAbsence(Employe $employe, ?int $mois = null, ?int $annee = null): float
    {
        $mois  = $mois  ?? now()->month;
        $annee = $annee ?? now()->year;

        $joursOuvres = $this->presenceService->compterJoursOuvres($mois, $annee);
        if ($joursOuvres === 0) return 0;

        $salaireJournalier = $employe->salaire_base / $joursOuvres;
        $pct               = (float) Parametre::valeur('penalite_absence_pct', 5) / 100;

        return round($salaireJournalier * $pct, 2);
    }

    // ──────────────────────────────────────────────────────────────────
    // Calculer la pénalité financière d'un retard
    // = salaire_journalier × % pénalité retard
    // ──────────────────────────────────────────────────────────────────
    public function calculerPenaliteRetard(Employe $employe, ?int $mois = null, ?int $annee = null): float
    {
        $mois  = $mois  ?? now()->month;
        $annee = $annee ?? now()->year;

        $joursOuvres = $this->presenceService->compterJoursOuvres($mois, $annee);
        if ($joursOuvres === 0) return 0;

        $salaireJournalier = $employe->salaire_base / $joursOuvres;
        $pct               = (float) Parametre::valeur('penalite_retard_pct', 2) / 100;

        return round($salaireJournalier * $pct, 2);
    }

    // ──────────────────────────────────────────────────────────────────
    // Générer automatiquement les absences pour un mois entier.
    // Parcourt chaque jour ouvré du mois : si un employé actif n'a pas
    // de présence validée ET pas d'absence déjà enregistrée, on en crée une.
    // ──────────────────────────────────────────────────────────────────
    public function genererAbsencesManquantes(int $mois, int $annee): int
    {
        $employes   = Employe::where('statut', 'actif')->get();
        $debut      = Carbon::createFromDate($annee, $mois, 1);
        $fin        = $debut->copy()->endOfMonth();
        $feries     = JourFerie::whereBetween('date', [$debut, $fin])->pluck('date')->map(fn($d) => (string) $d);

        $count = 0;

        $current = $debut->copy();
        while ($current->lte($fin)) {
            // Ignorer week-ends et jours fériés
            if ($current->isWeekend() || $feries->contains($current->toDateString())) {
                $current->addDay();
                continue;
            }

            foreach ($employes as $employe) {
                $dejaDans = Absence::where('employe_id', $employe->id)
                    ->where('date', $current->toDateString())
                    ->exists();

                $aPointe = \App\Models\Presence::where('employe_id', $employe->id)
                    ->where('date', $current->toDateString())
                    ->where('est_valide', true)
                    ->exists();

                if (!$aPointe && !$dejaDans) {
                    $penalite = $this->calculerPenaliteAbsence($employe, $mois, $annee);

                    Absence::create([
                        'employe_id' => $employe->id,
                        'date'       => $current->toDateString(),
                        'type'       => 'non_justifiee',
                        'penalite'   => $penalite,
                    ]);

                    $count++;
                }
            }

            $current->addDay();
        }

        // Vérifier si des sanctions automatiques doivent être déclenchées
        $this->verifierSanctionsAutomatiques($mois, $annee);

        Log::info("AbsenceService : {$count} absence(s) générée(s) pour {$mois}/{$annee}");

        return $count;
    }

    // ──────────────────────────────────────────────────────────────────
    // Déclenchement automatique des sanctions disciplinaires.
    // Ex : si un employé dépasse N absences NJ consécutives → avertissement écrit
    // ──────────────────────────────────────────────────────────────────
    public function verifierSanctionsAutomatiques(int $mois, int $annee): void
    {
        $seuilAvertissement = (int) Parametre::valeur('seuil_avertissement_absences', 3);
        $employes = Employe::where('statut', 'actif')->get();

        foreach ($employes as $employe) {
            $absencesNJ = Absence::where('employe_id', $employe->id)
                ->whereMonth('date', $mois)
                ->whereYear('date', $annee)
                ->where('type', 'non_justifiee')
                ->count();

            if ($absencesNJ >= $seuilAvertissement) {
                // Vérifier qu'une sanction pour ce mois n'existe pas déjà
                $dejaSanctionne = Sanction::where('employe_id', $employe->id)
                    ->whereMonth('date_debut', $mois)
                    ->whereYear('date_debut', $annee)
                    ->where('type', 'avertissement_ecrit')
                    ->exists();

                if (!$dejaSanctionne) {
                    Sanction::create([
                        'employe_id'    => $employe->id,
                        'type'          => 'avertissement_ecrit',
                        'motif'         => "{$absencesNJ} absences non justifiées en " . now()->locale('fr')->isoFormat('MMMM YYYY'),
                        'date_debut'    => now()->toDateString(),
                        'statut'        => 'en_cours',
                        'prononcee_par' => null,  // automatique
                    ]);

                    Log::info("Sanction auto : {$employe->nom_complet} — {$absencesNJ} absences NJ");
                }
            }
        }
    }
}
