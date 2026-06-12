<?php

namespace Database\Seeders;

use App\Models\Space;
use App\Models\PricingOption;
use Illuminate\Database\Seeder;

class PricingSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * IMPORTANT : Les salles de réunion et de formation (Grande/Petite)
         * ont leurs prix gérés directement dans le frontend (ROOM_PRICES).
         * Le seeder ne peuple QUE les espaces dont les prix viennent de la BDD.
         *
         * Espaces concernés ici :
         *   - Espace Ouvert
         *   - Grand Bureau
         *   - Bureau Moyen
         *   - Petit Bureau
         *
         * Le backend expose ces prix via GET /api/spaces/{id}/pricing.
         * Les salles (meeting / formation) n'ont PAS de PricingOption en BDD —
         * leurs prix sont calculés côté client selon la taille choisie.
         */
        $pricingData = [
            'Espace Ouvert' => [
                'hourly'   => 15,
                'half_day' => 35,
                'daily'    => 60,
                'weekly'   => 300,
                'monthly'  => 1250,
                'yearly'   => 13750,
            ],
            'Grand Bureau' => [
                'hourly'   => 80,
                'half_day' => 200,
                'daily'    => 400,
                'weekly'   => 1500,
                'monthly'  => 3500,
                'yearly'   => 38500,
            ],
            'Bureau Moyen' => [
                'hourly'   => 50,
                'half_day' => 150,
                'daily'    => 300,
                'weekly'   => 1200,
                'monthly'  => 3000,
                'yearly'   => 33000,
            ],
            'Petit Bureau' => [
                'hourly'   => 30,
                'half_day' => 100,
                'daily'    => 200,
                'weekly'   => 1000,
                'monthly'  => 2500,
                'yearly'   => 27500,
            ],
        ];

        $durationHours = [
            'hourly'   => 1,
            '2_hours'  => 2,
            'half_day' => 4,
            'daily'    => 8,
            'weekly'   => 40,
            'monthly'  => 160,
            'yearly'   => 1920,
        ];

        foreach ($pricingData as $spaceName => $options) {
            $space = Space::where('name', $spaceName)->first();

            if (! $space) {
                $this->command->warn("Espace introuvable : {$spaceName}");
                continue;
            }

            foreach ($options as $type => $price) {
                PricingOption::updateOrCreate(
                    [
                        'space_id'      => $space->id,
                        'duration_type' => $type,
                    ],
                    [
                        'price'          => $price,
                        'duration_hours' => $durationHours[$type] ?? null,
                    ]
                );
            }

            $this->command->info("✓ Tarifs mis à jour : {$spaceName}");
        }
    }
}
