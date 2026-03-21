<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UniteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('unites')->insert([
            [
                'nom' => 'Kilogramme',
                'description' => 'Unité de mesure de masse',
                'symbole' => 'kg',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nom' => 'Litre',
                'description' => 'Unité de mesure de volume',
                'symbole' => 'L',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nom' => 'Pièce',
                'description' => 'Unité de mesure pour les articles à l\'unité',
                'symbole' => 'pc',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nom' => 'Mètre',
                'description' => 'Unité de mesure de longueur',
                'symbole' => 'm',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nom' => 'Boîte',
                'description' => 'Conditionnement en boîte',
                'symbole' => 'bt',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}