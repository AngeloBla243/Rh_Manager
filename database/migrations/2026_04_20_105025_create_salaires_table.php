<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes')->onDelete('cascade');
            $table->integer('mois');         // 1 à 12
            $table->integer('annee');
            $table->decimal('salaire_brut', 10, 2);
            $table->integer('jours_travailles');
            $table->integer('jours_ouvres');      // jours ouvrables du mois
            $table->integer('nb_absences')->default(0);
            $table->integer('nb_retards')->default(0);
            $table->decimal('penalites_absences', 8, 2)->default(0);
            $table->decimal('penalites_retards', 8, 2)->default(0);
            $table->decimal('total_penalites', 8, 2)->default(0);
            $table->decimal('salaire_net', 10, 2);
            $table->decimal('taux_presence', 5, 2)->default(0);  // pourcentage
            $table->enum('statut_paiement', ['en_attente', 'paye', 'annule'])->default('en_attente');
            $table->date('date_paiement')->nullable();
            $table->timestamps();

            $table->unique(['employe_id', 'mois', 'annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salaires');
    }
};
