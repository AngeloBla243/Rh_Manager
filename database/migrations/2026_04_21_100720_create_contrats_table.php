<?php
// database/migrations/xxxx_create_contrats_table.php
// Commande : php artisan make:migration create_contrats_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employe_id')
                ->constrained('employes')
                ->onDelete('cascade');

            // Type de contrat
            $table->enum('type', [
                'CDI',       // Contrat à Durée Indéterminée
                'CDD',       // Contrat à Durée Déterminée
                'Stage',     // Convention de stage
                'Interim',   // Travail temporaire
                'Freelance', // Prestataire indépendant
                'Autre',
            ]);

            // Période
            $table->date('date_debut');
            $table->date('date_fin')->nullable(); // null = CDI ou durée indéterminée

            // Poste et rémunération
            $table->string('poste')->nullable();        // peut différer de employe.fonction
            $table->decimal('salaire_contractuel', 10, 2)->nullable();

            // Statut du contrat
            $table->enum('statut', [
                'actif',
                'suspendu',   // mis en pause temporairement
                'expire',     // arrivé à terme (CDD terminé)
                'resilie',    // rupture anticipée
                'renouvele',  // remplacé par un nouveau contrat
            ])->default('actif');

            // Informations complémentaires
            $table->text('description')->nullable();       // clauses spéciales, notes
            $table->string('numero_contrat')->nullable();  // référence interne
            $table->string('document_path')->nullable();   // scan du contrat signé

            // Renouvellement (CDD → CDD)
            $table->foreignId('renouvelle_depuis')
                ->nullable()
                ->constrained('contrats')
                ->nullOnDelete();

            // Période d'essai
            $table->boolean('periode_essai')->default(false);
            $table->date('fin_periode_essai')->nullable();

            // Qui a créé l'enregistrement
            $table->foreignId('cree_par')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};
