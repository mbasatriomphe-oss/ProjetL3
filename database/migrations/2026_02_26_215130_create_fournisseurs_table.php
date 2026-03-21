<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // Dans votre fichier de migration
public function up(): void
{
    Schema::create('fournisseurs', function (Blueprint $table) {
        $table->id();
        $table->string('nom', 90);
        $table->string('adresse', 63);
        $table->string('ville', 50);   // 'v' minuscule
        $table->string('pays', 50);    // 'p' minuscule
        $table->string('contact', 50); // 'c' minuscule
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fournisseurs');
    }
};
