<?php
// app/Services/AlerteService.php

namespace App\Services;

use App\Models\AlerteRh;
use App\Models\Employe;
use App\Models\Contrat;
use App\Models\DocumentEmploye;
use App\Models\Absence;
use App\Models\Presence;
use App\Models\Parametre;
use Carbon\Carbon;

class AlerteService
{
    // ──────────────────────────────────────────────────────────────────
    // Vérifier toutes les alertes (lancé par le scheduler chaque matin)
    // ──────────────────────────────────────────────────────────────────
    public function verifierToutes(): array
    {
        $resultats = [];
        $resultats['absences']        = $this->verifierAbsences();
        $resultats['retards']         = $this->verifierRetardsFrequents();
        $resultats['fins_contrat']    = $this->verifierFinsContrat();
        $resultats['docs_expiration'] = $this->verifierDocumentsExpiration();
        return $resultats;
    }

    // ──────────────────────────────────────────────────────────────────
    // Alertes absence du jour
    // ──────────────────────────────────────────────────────────────────
    public function verifierAbsences(): int
    {
        $employes   = Employe::actifs()->get();
        $today      = now()->toDateString();
        $alertes    = 0;

        // Ne pas envoyer le week-end
        if (now()->isWeekend()) return 0;

        foreach ($employes as $employe) {
            $aPointe = Presence::where('employe_id', $employe->id)
                ->whereDate('date', $today)
                ->exists();

            if (!$aPointe) {
                // Vérifier qu'on n'a pas déjà une alerte aujourd'hui
                $dejaAlertee = AlerteRh::where('employe_id', $employe->id)
                    ->where('type', 'absence')
                    ->whereDate('created_at', $today)
                    ->exists();

                if (!$dejaAlertee) {
                    AlerteRh::creer(
                        'absence',
                        "Absence non signalée — {$employe->nom_complet}",
                        "{$employe->nom_complet} n'a pas pointé ce {now()->locale('fr')->isoFormat('dddd D MMMM')}.",
                        $employe->id,
                        'normale'
                    );
                    $alertes++;
                }
            }
        }

        return $alertes;
    }

    // ──────────────────────────────────────────────────────────────────
    // Alertes retards fréquents (sur les 30 derniers jours)
    // ──────────────────────────────────────────────────────────────────
    public function verifierRetardsFrequents(): int
    {
        $seuil   = (int) Parametre::valeur('seuil_alerte_retards', 3);
        $employes = Employe::actifs()->get();
        $alertes  = 0;

        foreach ($employes as $employe) {
            $nbRetards = Presence::where('employe_id', $employe->id)
                ->where('est_retard', true)
                ->whereBetween('date', [now()->subDays(30)->toDateString(), now()->toDateString()])
                ->count();

            if ($nbRetards >= $seuil) {
                $dejaAlertee = AlerteRh::where('employe_id', $employe->id)
                    ->where('type', 'retards_frequents')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->exists();

                if (!$dejaAlertee) {
                    AlerteRh::creer(
                        'retards_frequents',
                        "Retards fréquents — {$employe->nom_complet}",
                        "{$nbRetards} retard(s) sur les 30 derniers jours (seuil : {$seuil}).",
                        $employe->id,
                        $nbRetards >= $seuil * 2 ? 'haute' : 'normale',
                        ['nb_retards' => $nbRetards, 'seuil' => $seuil]
                    );
                    $alertes++;
                }
            }
        }

        return $alertes;
    }

    // ──────────────────────────────────────────────────────────────────
    // Alertes fin de contrat (CDD/Stage/Interim)
    // ──────────────────────────────────────────────────────────────────
    public function verifierFinsContrat(): int
    {
        $joursAlerte = (int) Parametre::valeur('alerte_fin_contrat_jours', 30);
        $contrats    = Contrat::with('employe')
            ->expirantDans($joursAlerte)
            ->get();
        $alertes = 0;

        foreach ($contrats as $contrat) {
            $joursRestants = $contrat->jours_restants;
            $priorite = $joursRestants <= 7 ? 'critique' : ($joursRestants <= 14 ? 'haute' : 'normale');

            $dejaAlertee = AlerteRh::where('employe_id', $contrat->employe_id)
                ->where('type', 'fin_contrat')
                ->where('created_at', '>=', now()->subDays(7))
                ->exists();

            if (!$dejaAlertee) {
                AlerteRh::creer(
                    'fin_contrat',
                    "Fin de contrat dans {$joursRestants} jours — {$contrat->employe->nom_complet}",
                    "Le contrat {$contrat->type} de {$contrat->employe->nom_complet} expire le {$contrat->date_fin->format('d/m/Y')}.",
                    $contrat->employe_id,
                    $priorite,
                    ['contrat_id' => $contrat->id, 'jours_restants' => $joursRestants]
                );
                $alertes++;
            }
        }

        return $alertes;
    }

    // ──────────────────────────────────────────────────────────────────
    // Alertes documents expirant bientôt
    // ──────────────────────────────────────────────────────────────────
    public function verifierDocumentsExpiration(): int
    {
        $docs    = DocumentEmploye::with('employe')->expirantDans(30)->get();
        $alertes = 0;

        foreach ($docs as $doc) {
            $dejaAlertee = AlerteRh::where('employe_id', $doc->employe_id)
                ->where('type', 'expiration_document')
                ->where('created_at', '>=', now()->subDays(7))
                ->whereJsonContains('meta->document_id', $doc->id)
                ->exists();

            if (!$dejaAlertee) {
                AlerteRh::creer(
                    'expiration_document',
                    "Document expirant — {$doc->employe->nom_complet}",
                    "Le document « {$doc->type_document} » expire dans {$doc->jours_avant_expiration} jours.",
                    $doc->employe_id,
                    $doc->jours_avant_expiration <= 7 ? 'haute' : 'normale',
                    ['document_id' => $doc->id]
                );
                $alertes++;
            }
        }

        return $alertes;
    }

    // ──────────────────────────────────────────────────────────────────
    // Compter les alertes non lues
    // ──────────────────────────────────────────────────────────────────
    public function compterNonLues(): int
    {
        return AlerteRh::nonLues()->count();
    }
}
