<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Ordre important pour les clés étrangères
        $this->call([
            UserSeeder::class,
            SpaceSeeder::class,
            PricingSeeder::class, // NOUVEAU
            GallerySeeder::class, // NOUVEAU
            // SettingSeeder::class,
        ]);
    }
}