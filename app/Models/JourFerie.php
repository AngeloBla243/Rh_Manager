<?php
// ════════════════════════════════════════════════════════════════════
// app/Models/JourFerie.php
// Commande : php artisan make:model JourFerie
// ════════════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class JourFerie extends Model
{
    use HasFactory;

    protected $table = 'jours_feries';

    protected $fillable = [
        'date',
        'libelle',
        'recurrent',
    ];

    protected $casts = [
        'date'      => 'date',
        'recurrent' => 'boolean',
    ];

    // ── Accesseurs ────────────────────────────────────────────────────

    /**
     * Date formatée en français : "1er janvier 2025"
     */
    public function getDateFormateeAttribute(): string
    {
        return $this->date->locale('fr')->isoFormat('D MMMM YYYY');
    }

    /**
     * Date courte : "01 Jan"
     */
    public function getDateCourteAttribute(): string
    {
        return $this->date->format('d M');
    }

    /**
     * Le jour férié tombe-t-il cette semaine ?
     */
    public function getEstCetteSemaineAttribute(): bool
    {
        return $this->date->isCurrentWeek();
    }

    /**
     * Nombre de jours restants avant ce jour férié
     */
    public function getJoursRestantsAttribute(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->date->startOfDay(), false);
    }

    // ── Scopes ────────────────────────────────────────────────────────

    /**
     * Filtrer par année
     */
    public function scopeDeAnnee($query, int $annee)
    {
        return $query->whereYear('date', $annee);
    }

    /**
     * Uniquement les jours fériés récurrents
     */
    public function scopeRecurrents($query)
    {
        return $query->where('recurrent', true);
    }

    /**
     * Jours fériés à venir (à partir d'aujourd'hui)
     */
    public function scopeAvenir($query)
    {
        return $query->where('date', '>=', now()->toDateString());
    }

    // ── Méthodes statiques utilitaires ───────────────────────────────

    /**
     * Vérifier si une date donnée est un jour férié
     */
    public static function estFerie(string $date): bool
    {
        return static::where('date', $date)->exists();
    }

    /**
     * Récupérer toutes les dates fériées d'une année (pour calculs)
     */
    public static function datesDeLAnnee(int $annee): \Illuminate\Support\Collection
    {
        return static::whereYear('date', $annee)
            ->pluck('date')
            ->map(fn($d) => (string) $d);
    }
}
