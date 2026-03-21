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
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('description');
            $table->integer('prix');
            $table->unsignedBigInteger('categorie_id');  // CORRIGÉ : "cunsigned" -> "unsigned"
            $table->foreign('categorie_id')->references('id')->on('categories');
            $table->unsignedBigInteger('unite_id');      // CORRIGÉ : "cunsigned" -> "unsigned"
            $table->foreign('unite_id')->references('id')->on('unites');  // Note: 'unit_id' mais colonne 'unite_id'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};