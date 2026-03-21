<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FournisseurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('fournisseurs')->insert([
            [
                'nom' => 'Distribex SA',
                'contact' => '+221 77 123 45 67',
                'adress' => '15 Rue du Commerce, Dakar'
            ],
            [
                'nom' => 'Senegal Import',
                'contact' => '+221 76 234 56 78',
                'adress' => 'Zone Industrielle, Rufisque'
            ],
            [
                'nom' => 'AfriMarket',
                'contact' => '+221 70 345 67 89',
                'adress' => '12 Avenue Léopold Sédar Senghor, Thiès'
            ],
            [
                'nom' => 'Global Distribution',
                'contact' => '+221 77 456 78 90',
                'adress' => '45 Boulevard du Centenaire, Dakar'
            ],
            [
                'nom' => 'ProxiFour',
                'contact' => '+221 76 567 89 01',
                'adress' => '8 Rue de Saint-Louis, Saint-Louis'
            ]
        ]);
    }
}