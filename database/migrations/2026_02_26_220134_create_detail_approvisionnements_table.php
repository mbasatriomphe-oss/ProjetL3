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
        Schema::create('detail_approvisionnements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approv_id');      
            $table->foreign('approv_id')->references('id')->on('approvisionnements');
            $table->unsignedBigInteger('produit_id');      // CORRIGÉ : "cunsigned" -> "unsigned"
            $table->foreign('produit_id')->references('id')->on('produits');
            $table->integer('quantite');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_approvisionnements');
    }
};
