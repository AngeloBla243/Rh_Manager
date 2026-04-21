<?php
// database/migrations/xxxx_xx_xx_create_sanctions_table.php
// Commande : php artisan make:migration create_sanctions_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Une sanction est une mesure disciplinaire prononcée contre un employé.
     * Elle peut être déclenchée manuellement par l'admin ou automatiquement
     * quand le nombre d'absences non justifiées dépasse le seuil configuré.
     *
     * Elle est distincte de la "pénalité financière" (calculée dans la table absences).
     * La sanction, elle, a une portée disciplinaire : avertissement, mise à pied, etc.
     */
    public function up(): void
    {
        Schema::create('sanctions', function (Blueprint $table) {
            $table->id();

            // Employé concerné
            $table->foreignId('employe_id')
                ->constrained('employes')
                ->onDelete('cascade');

            // Type de sanction
            $table->enum('type', [
                'avertissement_verbal',
                'avertissement_ecrit',
                'mise_a_pied',        // suspension temporaire avec ou sans solde
                'retenue_salaire',    // déduction financière supplémentaire
                'licenciement',
                'autre',
            ]);

            // Motif principal
            $table->string('motif');  // ex: "3 absences non justifiées consécutives"

            // Description libre
            $table->text('description')->nullable();

            // Période de la sanction (pour les mises à pied)
            $table->date('date_debut');
            $table->date('date_fin')->nullable();  // null = sanction sans durée définie

            // Impact financier (en $) si retenue_salaire
            $table->decimal('montant_retenu', 8, 2)->default(0);

            // Lien vers les absences déclencheuses (JSON array d'IDs)
            $table->json('absences_ids')->nullable();

            // Statut de la sanction
            $table->enum('statut', [
                'en_cours',
                'levee',       // la sanction a été levée / annulée
                'executee',    // pleinement appliquée
            ])->default('en_cours');

            // L'admin qui a prononcé la sanction
            $table->foreignId('prononcee_par')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Accusé de réception signé par l'employé
            $table->boolean('signe_employe')->default(false);
            $table->date('date_signature')->nullable();

            // Document PDF de la sanction scanné / généré
            $table->string('document_path')->nullable();

            $table->timestamps();
            $table->softDeletes();  // permet d'archiver sans supprimer
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sanctions');
    }
};
