<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents_employe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes')->onDelete('cascade');
            $table->string('type_document');   // configurable par l'admin
            $table->string('nom_fichier');
            $table->string('chemin_fichier');
            $table->string('extension');
            $table->unsignedBigInteger('taille_octets');
            $table->date('date_expiration')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents_employe');
    }
};
