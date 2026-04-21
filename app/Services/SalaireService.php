<?php
// app/Services/SalaireService.php — VERSION AMÉLIORÉE

namespace App\Services;

use App\Models\Employe;
use App\Models\Salaire;
use App\Models\Prime;
use App\Models\HeureSupplementaire;
use App\Models\Paiement;
use App\Models\AlerteRh;
use App\Models\Parametre;

class SalaireService
{
    public function __construct(private PresenceService $presenceService) {}

    // ──────────────────────────────────────────────────────────────────
    // Calculer tous les salaires d'un mois avec primes + heures sup
    // ──────────────────────────────────────────────────────────────────
    public function calculerMois(int $mois, int $annee): int
    {
        $employes    = Employe::actifs()->get();
        $joursOuvres = $this->presenceService->compterJoursOuvres($mois, $annee);
        $heuresParJour = (float) Parametre::valeur('heures_par_jour', 8);

        $pctAbsence = (float) Parametre::valeur('penalite_absence_pct', 5) / 100;
        $pctRetard  = (float) Parametre::valeur('penalite_retard_pct', 2) / 100;
        $seuilRetards = (int) Parametre::valeur('seuil_penalite_retards', 3);

        $count = 0;

        foreach ($employes as $employe) {
            $rapport = collect($this->presenceService->genererRapportMensuel($mois, $annee))
                ->firstWhere('employe.id', $employe->id);

            if (!$rapport) continue;

            $joursTravailles = $rapport['jours_travailles'];
            $nbAbsences      = $rapport['absences'];
            $nbRetards       = $rapport['retards'];

            // Taux horaire de base
            $heuresMensuelles = $joursOuvres * $heuresParJour;
            $tauxHoraire = $heuresMensuelles > 0
                ? round($employe->salaire_base / $heuresMensuelles, 4)
                : 0;

            // Heures travaillées réelles (depuis les présences)
            $heuresTravaillees = $this->calculerHeuresTravaillees($employe->id, $mois, $annee);

            // Heures supplémentaires approuvées
            $heuresSup = HeureSupplementaire::where('employe_id', $employe->id)
                ->duMois($mois, $annee)
                ->approuvees()
                ->sum('nb_heures');

            $montantHeuresSup = HeureSupplementaire::where('employe_id', $employe->id)
                ->duMois($mois, $annee)
                ->approuvees()
                ->sum('montant');

            // Recalculer montant heures sup si pas défini
            if ($montantHeuresSup == 0 && $heuresSup > 0) {
                $tauxMajoration = (float) Parametre::valeur('taux_majoration_heures_sup', 1.25);
                $montantHeuresSup = round($tauxHoraire * $heuresSup * $tauxMajoration, 2);
            }

            // Primes du mois
            $totalPrimes = (float) Prime::where('employe_id', $employe->id)
                ->duMois($mois, $annee)
                ->sum('montant');

            // Pénalité absences
            $salaireJournalier = $joursOuvres > 0 ? $employe->salaire_base / $joursOuvres : 0;
            $penaliteAbsence   = $nbAbsences * $salaireJournalier * $pctAbsence;

            // Pénalité retards (seulement au-delà du seuil)
            $retardsFactures  = max(0, $nbRetards - $seuilRetards);
            $penaliteRetard   = $retardsFactures * $salaireJournalier * $pctRetard;

            $totalPenalites = $penaliteAbsence + $penaliteRetard;

            $salaireNet = max(
                0,
                $employe->salaire_base
                    + $totalPrimes
                    + $montantHeuresSup
                    - $totalPenalites
            );

            $tauxPresence = $joursOuvres > 0
                ? round($joursTravailles / $joursOuvres * 100, 2)
                : 0;

            $salaire = Salaire::updateOrCreate(
                ['employe_id' => $employe->id, 'mois' => $mois, 'annee' => $annee],
                [
                    'salaire_brut'             => $employe->salaire_base,
                    'total_primes'             => round($totalPrimes, 2),
                    'total_heures_sup'         => round($montantHeuresSup, 2),
                    'jours_travailles'         => $joursTravailles,
                    'jours_ouvres'             => $joursOuvres,
                    'nb_absences'              => $nbAbsences,
                    'nb_retards'               => $nbRetards,
                    'heures_travaillees'       => round($heuresTravaillees, 2),
                    'heures_supplementaires'   => round($heuresSup, 2),
                    'taux_horaire'             => $tauxHoraire,
                    'penalites_absences'       => round($penaliteAbsence, 2),
                    'penalites_retards'        => round($penaliteRetard, 2),
                    'total_penalites'          => round($totalPenalites, 2),
                    'salaire_net'              => round($salaireNet, 2),
                    'taux_presence'            => $tauxPresence,
                ]
            );

            // Lier les primes et heures sup à ce salaire
            Prime::where('employe_id', $employe->id)
                ->duMois($mois, $annee)
                ->update(['salaire_id' => $salaire->id]);

            // Alerte si retards fréquents
            if ($nbRetards >= $seuilRetards) {
                AlerteRh::creer(
                    'retards_frequents',
                    "Retards fréquents — {$employe->nom_complet}",
                    "{$nbRetards} retards enregistrés en " . now()->locale('fr')->isoFormat('MMMM YYYY'),
                    $employe->id,
                    $nbRetards >= $seuilRetards * 2 ? 'haute' : 'normale'
                );
            }

            $count++;
        }

        return $count;
    }

    // ──────────────────────────────────────────────────────────────────
    // Enregistrer un paiement
    // ──────────────────────────────────────────────────────────────────
    public function enregistrerPaiement(Salaire $salaire, array $data): Paiement
    {
        $paiement = Paiement::create([
            'salaire_id'    => $salaire->id,
            'employe_id'    => $salaire->employe_id,
            'montant'       => $data['montant'] ?? $salaire->salaire_net,
            'mode'          => $data['mode'],
            'date_paiement' => $data['date_paiement'] ?? now()->toDateString(),
            'reference'     => $data['reference'] ?? null,
            'banque'        => $data['banque'] ?? null,
            'statut'        => 'effectue',
            'note'          => $data['note'] ?? null,
            'effectue_par'  => auth()->id(),
        ]);

        $salaire->update([
            'statut_paiement' => 'paye',
            'date_paiement'   => $paiement->date_paiement,
        ]);

        // Alerte de confirmation de paiement
        AlerteRh::creer(
            'paiement_salaire',
            "Salaire payé — {$salaire->employe->nom_complet}",
            "Paiement de {$salaire->salaire_net} $ effectué via {$paiement->mode_libelle}.",
            $salaire->employe_id,
            'basse'
        );

        return $paiement;
    }

    // ──────────────────────────────────────────────────────────────────
    // Calculer les heures travaillées réelles du mois
    // ──────────────────────────────────────────────────────────────────
    public function calculerHeuresTravaillees(int $employeId, int $mois, int $annee): float
    {
        $presences = \App\Models\Presence::where('employe_id', $employeId)
            ->whereMonth('date', $mois)
            ->whereYear('date', $annee)
            ->where('est_valide', true)
            ->whereNotNull('heure_entree')
            ->whereNotNull('heure_sortie')
            ->get();

        $totalMinutes = 0;
        foreach ($presences as $p) {
            $entree = \Carbon\Carbon::createFromFormat('H:i:s', $p->heure_entree);
            $sortie = \Carbon\Carbon::createFromFormat('H:i:s', $p->heure_sortie);
            $totalMinutes += max(0, $entree->diffInMinutes($sortie));
        }

        return round($totalMinutes / 60, 2);
    }

    // ──────────────────────────────────────────────────────────────────
    // Statistiques globales pour le rapport
    // ──────────────────────────────────────────────────────────────────
    public function statsGlobales(int $annee): array
    {
        $salaires = Salaire::where('annee', $annee)->get();

        $depensesParMois = [];
        for ($m = 1; $m <= 12; $m++) {
            $moisData = $salaires->where('mois', $m);
            $depensesParMois[] = [
                'mois'         => $m,
                'brut'         => $moisData->sum('salaire_brut'),
                'primes'       => $moisData->sum('total_primes'),
                'heures_sup'   => $moisData->sum('total_heures_sup'),
                'penalites'    => $moisData->sum('total_penalites'),
                'net'          => $moisData->sum('salaire_net'),
                'taux_moyen'   => $moisData->avg('taux_presence') ?? 0,
            ];
        }

        return [
            'total_annuel'        => $salaires->sum('salaire_net'),
            'total_primes'        => $salaires->sum('total_primes'),
            'total_heures_sup'    => $salaires->sum('total_heures_sup'),
            'total_penalites'     => $salaires->sum('total_penalites'),
            'taux_presence_moyen' => round($salaires->avg('taux_presence') ?? 0, 1),
            'depenses_par_mois'   => $depensesParMois,
        ];
    }
}
