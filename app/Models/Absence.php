<?php
// ════════════════════════════════════════════════════════════════════
// app/Models/Absence.php
// Commande : php artisan make:model Absence
// ════════════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Absence extends Model
{
    use HasFactory;

    protected $fillable = [
        'employe_id',
        'date',
        'type',
        'motif',
        'document_justificatif',
        'penalite',
        'approuvee',
        'approuvee_par',
    ];

    protected $casts = [
        'date'      => 'date',
        'penalite'  => 'decimal:2',
        'approuvee' => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────────────

    /**
     * L'employé concerné par l'absence
     */
    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    /**
     * L'admin qui a approuvé/justifié l'absence
     */
    public function approuveePar()
    {
        return $this->belongsTo(User::class, 'approuvee_par');
    }

    // ── Accesseurs ────────────────────────────────────────────────────

    /**
     * Libellé lisible du type d'absence
     */
    public function getTypeLibelleAttribute(): string
    {
        return match ($this->type) {
            'justifiee'     => 'Justifiée',
            'non_justifiee' => 'Non justifiée',
            'conge'         => 'Congé',
            'ferie'         => 'Jour férié',
            default         => 'Inconnue',
        };
    }

    /**
     * Couleur CSS associée au type (pour les badges dans les vues)
     */
    public function getTypeCouleurAttribute(): string
    {
        return match ($this->type) {
            'justifiee'     => 'amber',
            'non_justifiee' => 'red',
            'conge'         => 'blue',
            'ferie'         => 'gray',
            default         => 'gray',
        };
    }

    /**
     * L'absence a-t-elle un document justificatif attaché ?
     */
    public function getADocumentAttribute(): bool
    {
        return !empty($this->document_justificatif);
    }

    /**
     * L'absence génère-t-elle une pénalité financière ?
     */
    public function getAPenaliteAttribute(): bool
    {
        return $this->penalite > 0;
    }

    // ── Scopes ────────────────────────────────────────────────────────

    /**
     * Absences du mois donné
     */
    public function scopeDuMois($query, int $mois, int $annee)
    {
        return $query->whereMonth('date', $mois)->whereYear('date', $annee);
    }

    /**
     * Uniquement les absences non justifiées
     */
    public function scopeNonJustifiees($query)
    {
        return $query->where('type', 'non_justifiee');
    }

    /**
     * Uniquement les absences justifiées
     */
    public function scopeJustifiees($query)
    {
        return $query->where('type', 'justifiee');
    }

    /**
     * Absences d'un employé précis
     */
    public function scopePourEmploye($query, int $employeId)
    {
        return $query->where('employe_id', $employeId);
    }

    /**
     * Absences non encore approuvées
     */
    public function scopeEnAttente($query)
    {
        return $query->where('approuvee', false)
            ->whereIn('type', ['non_justifiee', 'justifiee']);
    }

    // ── Méthodes statiques ────────────────────────────────────────────

    /**
     * Total des pénalités pour un employé sur un mois
     */
    public static function totalPenalites(int $employeId, int $mois, int $annee): float
    {
        return (float) static::where('employe_id', $employeId)
            ->whereMonth('date', $mois)
            ->whereYear('date', $annee)
            ->sum('penalite');
    }

    /**
     * Nombre d'absences non justifiées consécutives (pour déclencher sanction auto)
     */
    public static function consecutivesNonJustifiees(int $employeId): int
    {
        $absences = static::where('employe_id', $employeId)
            ->where('type', 'non_justifiee')
            ->orderByDesc('date')
            ->get();

        $count = 0;
        $precedente = null;

        foreach ($absences as $abs) {
            if ($precedente === null) {
                $count = 1;
            } elseif ($precedente->date->diffInDays($abs->date) <= 3) {
                // Tolérance week-end (vendredi → lundi = 3 jours)
                $count++;
            } else {
                break; // série interrompue
            }
            $precedente = $abs;
        }

        return $count;
    }
}
