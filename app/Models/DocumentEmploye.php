<?php
// ════════════════════════════════════════════════════════════════════
// app/Models/DocumentEmploye.php
// Commande : php artisan make:model DocumentEmploye
// ════════════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class DocumentEmploye extends Model
{
    use HasFactory;

    protected $table = 'documents_employe';

    protected $fillable = [
        'employe_id',
        'type_document',
        'nom_fichier',
        'chemin_fichier',
        'extension',
        'taille_octets',
        'date_expiration',
    ];

    protected $casts = [
        'date_expiration' => 'date',
        'taille_octets'   => 'integer',
    ];

    // ── Relations ─────────────────────────────────────────────────────

    /**
     * L'employé propriétaire du document
     */
    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    // ── Accesseurs ────────────────────────────────────────────────────

    /**
     * Taille formatée : "1.4 Mo", "230 Ko", "512 o"
     */
    public function getTailleFormateeAttribute(): string
    {
        $octets = $this->taille_octets;
        if ($octets >= 1_048_576) return round($octets / 1_048_576, 1) . ' Mo';
        if ($octets >= 1_024)     return round($octets / 1_024, 1) . ' Ko';
        return $octets . ' o';
    }

    /**
     * Extension en majuscules pour l'affichage : "PDF", "PNG"
     */
    public function getExtensionMajAttribute(): string
    {
        return strtoupper($this->extension);
    }

    /**
     * Le document est-il un fichier image ?
     */
    public function getEstImageAttribute(): bool
    {
        return in_array(strtolower($this->extension), ['jpg', 'jpeg', 'png', 'webp', 'gif']);
    }

    /**
     * Le document est-il un PDF ?
     */
    public function getEstPdfAttribute(): bool
    {
        return strtolower($this->extension) === 'pdf';
    }

    /**
     * Le document peut-il être prévisualisé dans le navigateur ?
     * (PDF ou image)
     */
    public function getEstPrevisualisableAttribute(): bool
    {
        return $this->est_pdf || $this->est_image;
    }

    /**
     * Le document est-il expiré ?
     */
    public function getEstExpireAttribute(): bool
    {
        return $this->date_expiration && $this->date_expiration->lt(now());
    }

    /**
     * Le document expire-t-il dans moins de 30 jours ?
     */
    public function getExpireBientotAttribute(): bool
    {
        return $this->date_expiration
            && !$this->est_expire
            && $this->date_expiration->lt(now()->addDays(30));
    }

    /**
     * Nombre de jours avant expiration (négatif si déjà expiré)
     */
    public function getJoursAvantExpirationAttribute(): ?int
    {
        if (!$this->date_expiration) return null;
        return (int) now()->startOfDay()->diffInDays($this->date_expiration->startOfDay(), false);
    }

    /**
     * Le fichier existe-t-il réellement sur le disque ?
     */
    public function getExisteSurDisqueAttribute(): bool
    {
        return Storage::disk('local')->exists($this->chemin_fichier);
    }

    /**
     * Icône emoji selon le type de fichier
     */
    public function getIconeAttribute(): string
    {
        return match (true) {
            $this->est_pdf   => '📄',
            $this->est_image => '🖼️',
            in_array($this->extension, ['doc', 'docx']) => '📝',
            in_array($this->extension, ['xls', 'xlsx']) => '📊',
            default          => '📁',
        };
    }

    // ── Scopes ────────────────────────────────────────────────────────

    /**
     * Documents d'un type précis
     */
    public function scopeDeType($query, string $type)
    {
        return $query->where('type_document', $type);
    }

    /**
     * Documents expirés
     */
    public function scopeExpires($query)
    {
        return $query->whereNotNull('date_expiration')
            ->where('date_expiration', '<', now()->toDateString());
    }

    /**
     * Documents expirant dans les N prochains jours
     */
    public function scopeExpirantDans($query, int $jours = 30)
    {
        return $query->whereNotNull('date_expiration')
            ->where('date_expiration', '>=', now()->toDateString())
            ->where('date_expiration', '<=', now()->addDays($jours)->toDateString());
    }

    // ── Méthodes ─────────────────────────────────────────────────────

    /**
     * Supprimer le fichier physique et l'enregistrement en BDD
     */
    public function supprimerComplet(): bool
    {
        if (Storage::disk('local')->exists($this->chemin_fichier)) {
            Storage::disk('local')->delete($this->chemin_fichier);
        }
        return $this->delete();
    }

    /**
     * Contenu brut du fichier (pour streaming)
     */
    public function contenu(): ?string
    {
        if (!$this->existe_sur_disque) return null;
        return Storage::disk('local')->get($this->chemin_fichier);
    }

    /**
     * Type MIME pour le Content-Type HTTP
     */
    public function mimeType(): string
    {
        return match (strtolower($this->extension)) {
            'pdf'       => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png'       => 'image/png',
            'webp'      => 'image/webp',
            'doc'       => 'application/msword',
            'docx'      => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'       => 'application/vnd.ms-excel',
            'xlsx'      => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default     => 'application/octet-stream',
        };
    }
}
