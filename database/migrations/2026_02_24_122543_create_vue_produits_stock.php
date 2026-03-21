<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Supprimer la vue si elle existe déjà
        DB::statement("DROP VIEW IF EXISTS vue_produits_stock");
        
        // Créer la vue
        DB::statement("
            CREATE VIEW vue_produits_stock AS
            SELECT 
                p.id,
                p.nom,
                p.description,
                p.prix,
                c.nom as categorie_nom,
                c.id as categorie_id,
                u.nom as unite_nom,
                u.symbole as unite_symbole,
                COUNT(DISTINCT pp.id) as nombre_photos,
                COUNT(DISTINCT a.id) as nombre_approvisionnements
            FROM produits p
            LEFT JOIN categories c ON p.categorie_id = c.id
            LEFT JOIN unites u ON p.unite_id = u.id
            LEFT JOIN photoproduits pp ON p.id = pp.produit_id
            LEFT JOIN approvisionnements a ON a.id IN (
                SELECT id FROM approvisionnements WHERE produit_id = p.id
            )
            GROUP BY p.id, p.nom, p.description, p.prix, c.nom, c.id, u.nom, u.symbole
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS vue_produits_stock");
    }
};