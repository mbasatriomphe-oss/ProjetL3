<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProduitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('produits')->insert([
            [
                'nom' => 'Riz parfumé',
                'description' => 'Riz long grain 5kg',
                'prix' => 4500,
                'categorie_id' => 1, // Alimentation
                'unite_id' => 1,      // kg
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nom' => 'Huile d\'olive',
                'description' => 'Huile d\'olive extra vierge 1L',
                'prix' => 8000,
                'categorie_id' => 1, // Alimentation
                'unite_id' => 2,      // L
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nom' => 'Savon liquide',
                'description' => 'Savon pour les mains 500ml',
                'prix' => 2500,
                'categorie_id' => 3, // Hygiène
                'unite_id' => 2,      // L
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nom' => 'Cahier 100 pages',
                'description' => 'Cahier format A4',
                'prix' => 1200,
                'categorie_id' => 5, // Vêtements (ou à créer catégorie "Fournitures")
                'unite_id' => 3,      // pc
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nom' => 'Eau minérale',
                'description' => 'Pack de 12 bouteilles 1.5L',
                'prix' => 6000,
                'categorie_id' => 2, // Boissons
                'unite_id' => 5,      // bt
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nom' => 'Couscous',
                'description' => 'Couscous de blé 1kg',
                'prix' => 1800,
                'categorie_id' => 1, // Alimentation
                'unite_id' => 1,      // kg
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nom' => 'Lait concentré',
                'description' => 'Boîte de lait concentré sucré',
                'prix' => 2500,
                'categorie_id' => 1, // Alimentation
                'unite_id' => 5,      // bt
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nom' => 'Dentifrice',
                'description' => 'Dentifrice au fluor 100ml',
                'prix' => 1500,
                'categorie_id' => 3, // Hygiène
                'unite_id' => 3,      // pc
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}