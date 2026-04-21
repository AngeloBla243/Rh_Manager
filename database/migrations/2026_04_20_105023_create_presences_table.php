<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes')->onDelete('cascade');
            $table->date('date');
            $table->time('heure_entree')->nullable();
            $table->time('heure_sortie')->nullable();
            $table->enum('mode_pointage', ['biometrique', 'manuel'])->default('biometrique');
            $table->boolean('est_retard')->default(false);
            $table->integer('minutes_retard')->default(0);
            $table->boolean('est_valide')->default(false);  // true si entrée + sortie
            $table->string('remarque')->nullable();
            $table->timestamps();

            // Un employé ne peut avoir qu'une présence par jour
            $table->unique(['employe_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};
