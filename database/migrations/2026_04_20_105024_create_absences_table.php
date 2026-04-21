<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes')->onDelete('cascade');
            $table->date('date');
            $table->enum('type', ['justifiee', 'non_justifiee', 'conge', 'ferie']);
            $table->string('motif')->nullable();
            $table->string('document_justificatif')->nullable();  // chemin fichier
            $table->decimal('penalite', 8, 2)->default(0);        // montant déduit
            $table->boolean('approuvee')->default(false);
            $table->foreignId('approuvee_par')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
