<?php
// database/migrations/xxxx_create_paie_details_table.php
// php artisan make:migration create_paie_details_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Primes et bonus ──────────────────────────────────────────
        Schema::create('primes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes')->onDelete('cascade');
            $table->foreignId('salaire_id')->nullable()->constrained('salaires')->nullOnDelete();
            $table->integer('mois');
            $table->integer('annee');
            $table->enum('type', ['performance', 'anciennete', 'transport', 'logement', 'repas', 'exceptionnelle', 'autre']);
            $table->string('libelle');
            $table->decimal('montant', 10, 2);
            $table->boolean('imposable')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // ── Heures supplémentaires ───────────────────────────────────
        Schema::create('heures_supplementaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes')->onDelete('cascade');
            $table->foreignId('presence_id')->nullable()->constrained('presences')->nullOnDelete();
            $table->date('date');
            $table->decimal('nb_heures', 5, 2);         // ex: 2.50 = 2h30
            $table->decimal('taux_majoration', 5, 2)->default(1.25); // 125% par défaut
            $table->decimal('montant', 10, 2)->default(0); // calculé automatiquement
            $table->enum('statut', ['en_attente', 'approuvee', 'refusee', 'payee'])->default('en_attente');
            $table->string('motif')->nullable();
            $table->foreignId('approuvee_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── Mise à jour de la table salaires avec nouveaux champs ────
        Schema::table('salaires', function (Blueprint $table) {
            $table->decimal('total_primes', 10, 2)->default(0)->after('salaire_brut');
            $table->decimal('total_heures_sup', 10, 2)->default(0)->after('total_primes');
            $table->decimal('heures_travaillees', 8, 2)->default(0)->after('nb_retards');
            $table->decimal('heures_supplementaires', 6, 2)->default(0)->after('heures_travaillees');
            $table->decimal('taux_horaire', 8, 4)->default(0)->after('heures_supplementaires');
        });

        // ── Historique des paiements ─────────────────────────────────
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salaire_id')->constrained('salaires')->onDelete('cascade');
            $table->foreignId('employe_id')->constrained('employes')->onDelete('cascade');
            $table->decimal('montant', 10, 2);
            $table->enum('mode', ['virement', 'especes', 'cheque', 'mobile_money']);
            $table->date('date_paiement');
            $table->string('reference')->nullable();
            $table->string('banque')->nullable();
            $table->enum('statut', ['effectue', 'en_attente', 'rejete'])->default('effectue');
            $table->text('note')->nullable();
            $table->foreignId('effectue_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── Notifications ────────────────────────────────────────────
        Schema::create('alertes_rh', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->nullable()->constrained('employes')->nullOnDelete();
            $table->enum('type', [
                'absence',
                'retards_frequents',
                'paiement_salaire',
                'fin_contrat',
                'expiration_document',
                'absence_injustifiee',
                'sanctions_auto',
                'periode_essai'
            ]);
            $table->string('titre');
            $table->text('message');
            $table->enum('priorite', ['basse', 'normale', 'haute', 'critique'])->default('normale');
            $table->boolean('lue')->default(false);
            $table->timestamp('lue_at')->nullable();
            $table->json('meta')->nullable(); // données supplémentaires
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertes_rh');
        Schema::dropIfExists('paiements');
        Schema::table('salaires', function (Blueprint $table) {
            $table->dropColumn(['total_primes', 'total_heures_sup', 'heures_travaillees', 'heures_supplementaires', 'taux_horaire']);
        });
        Schema::dropIfExists('heures_supplementaires');
        Schema::dropIfExists('primes');
    }
};
