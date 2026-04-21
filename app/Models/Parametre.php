<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Parametre extends Model
{
    protected $fillable = ['cle', 'valeur', 'description'];

    // Récupérer une valeur facilement depuis n'importe où
    public static function valeur(string $cle, mixed $defaut = null): mixed
    {
        return Cache::remember("param_{$cle}", 3600, function () use ($cle, $defaut) {
            $param = static::where('cle', $cle)->first();
            return $param ? $param->valeur : $defaut;
        });
    }

    // Mettre à jour et invalider le cache
    public static function definir(string $cle, mixed $valeur): void
    {
        static::updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
        Cache::forget("param_{$cle}");
    }
}
