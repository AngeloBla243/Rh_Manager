<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Service de connexion à l'appareil biométrique.
 *
 * Compatible avec les appareils ZKTeco (les plus courants en Afrique).
 * Communication via socket TCP ou port série.
 * Protocole : ZKFP (ZKFinger Protocol) ou SDK ZKTeco.
 */
class BiometriqueService
{
    private ?object $connexion = null;
    private string  $ip;
    private int     $port;

    public function __construct()
    {
        $this->ip   = config('app.biometric_ip',   env('BIOMETRIC_IP', '192.168.1.200'));
        $this->port = (int) config('app.biometric_port', env('BIOMETRIC_TCP_PORT', 4370));
    }

    // -------------------------------------------------------------------------
    // CONNEXION TCP (ZKTeco ADMS / ZKFinger Protocol)
    // -------------------------------------------------------------------------

    public function connecter(): bool
    {
        try {
            $this->connexion = fsockopen($this->ip, $this->port, $errno, $errstr, 5);

            if (!$this->connexion) {
                Log::warning("Biométrique : connexion échouée — {$errstr} ({$errno})");
                return false;
            }

            stream_set_timeout($this->connexion, 10);
            Log::info("Biométrique : connexion établie sur {$this->ip}:{$this->port}");
            return true;
        } catch (\Exception $e) {
            Log::error("Biométrique exception : " . $e->getMessage());
            return false;
        }
    }

    public function deconnecter(): void
    {
        if ($this->connexion) {
            fclose($this->connexion);
            $this->connexion = null;
        }
    }

    public function verifierConnexion(): bool
    {
        $result = $this->connecter();
        $this->deconnecter();
        return $result;
    }

    // -------------------------------------------------------------------------
    // RÉCUPÉRER LES LOGS DE POINTAGE DEPUIS L'APPAREIL
    // Chaque enregistrement contient : ID utilisateur, heure, type (entree/sortie)
    // -------------------------------------------------------------------------

    public function lireLogsPointage(): array
    {
        if (!$this->connecter()) {
            return [];
        }

        $logs = [];

        try {
            // Commande ZKTeco pour récupérer les logs d'assiduité
            // La commande exacte dépend du modèle ; voici le protocole standard
            $commande = $this->construireCommande(0x0D, '');
            fwrite($this->connexion, $commande);

            $reponse = '';
            while (!feof($this->connexion)) {
                $chunk = fread($this->connexion, 4096);
                if ($chunk === false || strlen($chunk) === 0) break;
                $reponse .= $chunk;
            }

            $logs = $this->parserLogsPointage($reponse);
        } catch (\Exception $e) {
            Log::error("Lecture logs biométrique : " . $e->getMessage());
        }

        $this->deconnecter();
        return $logs;
    }

    // -------------------------------------------------------------------------
    // ENREGISTRER UNE EMPREINTE SUR L'APPAREIL
    // -------------------------------------------------------------------------

    public function enregistrerEmpreinte(int $userId, string $nom): bool
    {
        if (!$this->connecter()) {
            return false;
        }

        try {
            // Envoyer la commande d'enrôlement
            $data = pack('n', $userId) . $nom . "\x00";
            $commande = $this->construireCommande(0x64, $data);
            fwrite($this->connexion, $commande);

            $reponse = fread($this->connexion, 512);
            $succes  = $this->parserReponseSucces($reponse);

            $this->deconnecter();
            return $succes;
        } catch (\Exception $e) {
            Log::error("Enrôlement empreinte : " . $e->getMessage());
            $this->deconnecter();
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // SUPPRIMER UN UTILISATEUR DE L'APPAREIL
    // -------------------------------------------------------------------------

    public function supprimerUtilisateur(int $userId): bool
    {
        if (!$this->connecter()) {
            return false;
        }

        try {
            $data = pack('n', $userId);
            $commande = $this->construireCommande(0x52, $data);
            fwrite($this->connexion, $commande);

            $reponse = fread($this->connexion, 512);
            $succes  = $this->parserReponseSucces($reponse);

            $this->deconnecter();
            return $succes;
        } catch (\Exception $e) {
            $this->deconnecter();
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // MÉTHODES PRIVÉES — PROTOCOLE ZKFinger
    // -------------------------------------------------------------------------

    private function construireCommande(int $codeCmd, string $donnees): string
    {
        $longueur = strlen($donnees);
        $entete   = "\x50\x50\x82\x7D";      // Magic header ZKTeco
        $cmd      = pack('n', $codeCmd);
        $len      = pack('N', $longueur);
        $checksum = $this->calculerChecksum($cmd . $len . $donnees);

        return $entete . $cmd . $len . $checksum . $donnees;
    }

    private function calculerChecksum(string $data): string
    {
        $sum = 0;
        for ($i = 0; $i < strlen($data); $i++) {
            $sum += ord($data[$i]);
        }
        return pack('n', $sum & 0xFFFF);
    }

    private function parserLogsPointage(string $donneesBrutes): array
    {
        // Format de chaque enregistrement : 40 octets
        // Octets 0-3  : ID utilisateur (int 32bits)
        // Octets 4-9  : Timestamp (6 octets : aaaa mm jj HH MM SS)
        // Octet  10   : Type (0=entrée, 1=sortie)
        $logs = [];
        $entreeSize = 40;
        $offset = 0;

        while ($offset + $entreeSize <= strlen($donneesBrutes)) {
            $enregistrement = substr($donneesBrutes, $offset, $entreeSize);

            $userId = unpack('N', substr($enregistrement, 0, 4))[1];
            $annee  = ord($enregistrement[4]) * 256 + ord($enregistrement[5]);
            $mois   = ord($enregistrement[6]);
            $jour   = ord($enregistrement[7]);
            $heure  = ord($enregistrement[8]);
            $minute = ord($enregistrement[9]);
            $type   = ord($enregistrement[10]) === 0 ? 'entree' : 'sortie';

            if ($userId > 0) {
                $logs[] = [
                    'user_id' => $userId,
                    'date'    => sprintf('%04d-%02d-%02d', $annee, $mois, $jour),
                    'heure'   => sprintf('%02d:%02d', $heure, $minute),
                    'type'    => $type,
                ];
            }

            $offset += $entreeSize;
        }

        return $logs;
    }

    private function parserReponseSucces(string $reponse): bool
    {
        if (strlen($reponse) < 6) return false;
        $codeRetour = unpack('n', substr($reponse, 4, 2))[1];
        return $codeRetour === 0x00;
    }
}
