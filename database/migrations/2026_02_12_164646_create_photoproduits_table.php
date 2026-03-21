<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('photoproduits', function (Blueprint $table) {
            $table->id();
            $table->string('nom_du_fichier');
            $table->unsignedBigInteger('produit_id');      // CORRIGÉ : "cunsigned" -> "unsigned"
            $table->foreign('produit_id')->references('id')->on('produits');  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photoproduits');
    }
};
