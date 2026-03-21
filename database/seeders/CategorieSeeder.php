<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'nom' => 'Alimentation',
                'description' => 'Produits alimentaires et denrées périssables',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nom' => 'Boissons',
                'description' => 'Eaux, sodas, jus et autres boissons',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nom' => 'Hygiène',
                'description' => 'Produits d\'hygiène et de soin',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nom' => 'Électroménager',
                'description' => 'Appareils électroménagers et électroniques',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nom' => 'Vêtements',
                'description' => 'Habillement et accessoires',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}