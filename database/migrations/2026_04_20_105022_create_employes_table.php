<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employes', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->unique();       // EMP-001
            $table->string('nom');
            $table->string('postnom')->nullable();
            $table->string('prenom');
            $table->date('date_naissance');
            $table->string('fonction');
            $table->integer('annee_engagement');
            $table->string('photo')->nullable();         // chemin fichier image
            $table->string('empreinte_id')->nullable();  // ID stocké dans l'appareil
            $table->decimal('salaire_base', 10, 2);
            $table->enum('statut', ['actif', 'suspendu', 'retraite'])->default('actif');
            $table->timestamps();
            $table->softDeletes();                       // suppression douce
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employes');
    }
};
