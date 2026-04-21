<?php
// ════════════════════════════════════════════════════════════════════
// app/Models/AlerteRh.php
// ════════════════════════════════════════════════════════════════════

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlerteRh extends Model
{
    protected $table = 'alertes_rh';
    protected $fillable = [
        'employe_id',
        'type',
        'titre',
        'message',
        'priorite',
        'lue',
        'lue_at',
        'meta'
    ];
    protected $casts = [
        'lue'    => 'boolean',
        'lue_at' => 'datetime',
        'meta'   => 'array',
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function getPrioriteClasseAttribute(): string
    {
        return match ($this->priorite) {
            'critique' => 'badge-red',
            'haute'    => 'badge-amber',
            'normale'  => 'badge-blue',
            default    => 'badge-gray',
        };
    }

    public function getPrioriteIconAttribute(): string
    {
        return match ($this->priorite) {
            'critique' => '🔴',
            'haute'    => '🟠',
            'normale'  => '🔵',
            default    => '⚪',
        };
    }

    public function marquerLue(): void
    {
        $this->update(['lue' => true, 'lue_at' => now()]);
    }

    public function scopeNonLues($q)
    {
        return $q->where('lue', false);
    }
    public function scopeCritiques($q)
    {
        return $q->where('priorite', 'critique');
    }
    public function scopeRecentes($q)
    {
        return $q->orderByDesc('created_at');
    }

    /** Créer une alerte rapidement */
    public static function creer(string $type, string $titre, string $message, ?int $employeId = null, string $priorite = 'normale', array $meta = []): static
    {
        return static::create([
            'employe_id' => $employeId,
            'type'       => $type,
            'titre'      => $titre,
            'message'    => $message,
            'priorite'   => $priorite,
            'meta'       => $meta ?: null,
        ]);
    }
}
