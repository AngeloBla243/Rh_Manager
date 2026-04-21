<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes')->onDelete('cascade');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->integer('nombre_jours');
            $table->enum('type', ['annuel', 'maladie', 'maternite', 'sans_solde', 'exceptionnel']);
            $table->string('motif')->nullable();
            $table->enum('statut', ['en_attente', 'approuve', 'refuse', 'annule'])->default('en_attente');
            $table->string('document')->nullable();
            $table->text('commentaire_admin')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conges');
    }
};
