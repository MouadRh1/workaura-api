<?php

namespace Database\Seeders;

use App\Models\Space;
use App\Models\PricingOption;
use Illuminate\Database\Seeder;

class PricingSeeder extends Seeder
{
    public function run(): void
    {
        // Mapping des espaces avec leurs prix selon le fichier Excel
        $pricingData = [
            'Espace Ouvert' => [
                'hourly' => 15,
                'half_day' => 35,
                'daily' => 60,
                'weekly' => 300,
                'monthly' => 1250,
                'yearly' => 13750,
            ],
            'Grand Bureau' => [
                'hourly' => 80,
                'half_day' => 200,
                'daily' => 400,
                'weekly' => 1500,
                'monthly' => 3500,
                'yearly' => 38500, // 3500 * 11
            ],
            'Bureau Moyen' => [
                'hourly' => 50,
                'half_day' => 150,
                'daily' => 300,
                'weekly' => 1200,
                'monthly' => 3000,
                'yearly' => 33000, // 3000 * 11
            ],
            'Petit Bureau' => [
                'hourly' => 30,
                'half_day' => 100,
                'daily' => 200,
                'weekly' => 1000,
                'monthly' => 2500,
                'yearly' => 27500, // 2500 * 11
            ],
            'Grande Salle de Réunion' => [
                'hourly' => 150,
                '2_hours' => 300,
                'half_day' => 350,
                'daily' => 600,
            ],
            'Petite Salle de Réunion' => [
                'hourly' => 100,
                '2_hours' => 200,
                'half_day' => 250,
                'daily' => 450,
            ],
        ];

        // Durées en heures pour chaque type
        $durationHours = [
            'hourly' => 1,
            '2_hours' => 2,
            'half_day' => 4,
            'daily' => 8,
            'weekly' => 40,
            'monthly' => 160,
            'yearly' => 1920,
        ];

        foreach ($pricingData as $spaceName => $options) {
            $space = Space::where('name', $spaceName)->first();
            
            if ($space) {
                foreach ($options as $type => $price) {
                    // Vérifier si l'option existe déjà
                    $existing = PricingOption::where('space_id', $space->id)
                        ->where('duration_type', $type)
                        ->first();
                    
                    if (!$existing) {
                        PricingOption::create([
                            'space_id' => $space->id,
                            'duration_type' => $type,
                            'price' => $price,
                            'duration_hours' => $durationHours[$type] ?? null,
                        ]);
                    }
                }
            }
        }
    }
}