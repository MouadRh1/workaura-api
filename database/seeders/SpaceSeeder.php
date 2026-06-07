<?php

namespace Database\Seeders;

use App\Models\Space;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SpaceSeeder extends Seeder
{
    public function run(): void
    {
        $spaces = [
            // =============================================
            // ESPACES OUVERTS (Coworking)
            // =============================================
            [
                'name' => 'Espace Ouvert',
                'slug' => 'espace-ouvert',
                'type' => 'coworking',
                'capacity' => 'Flexible',
                'price' => 60, // Prix par défaut (prix journalier)
                'description' => 'Espace de coworking ouvert et lumineux, idéal pour le travail collaboratif et le networking. Parfait pour les freelances et les petites équipes.',
                'amenities' => json_encode([
                    'WiFi Fibre Haut Débit',
                    'Café et thé à volonté',
                    'Espace détente',
                    'Imprimante/scanner',
                    'Casiers sécurisés',
                    'Climatisation réversible',
                    'Accès 24/7'
                ]),
                'featured_image' => '/uploads/spaces/espace-ouvert.jpg',
                'status' => 'available',
                'sort_order' => 1,
                'is_active' => true
            ],

            // =============================================
            // GRANDS BUREAUX
            // =============================================
            [
                'name' => 'Grand Bureau',
                'slug' => 'grand-bureau',
                'type' => 'private',
                'capacity' => '4-6 personnes',
                'price' => 400, // Prix par défaut (prix journalier)
                'description' => 'Grand bureau spacieux, parfait pour les équipes de 4 à 6 personnes. Idéal pour les startups et les petites entreprises.',
                'amenities' => json_encode([
                    'WiFi Fibre Haut Débit',
                    'Écran 4K 55"',
                    'Tableau blanc interactif',
                    'Climatisation réversible',
                    'Chaises ergonomiques',
                    'Casier sécurisé',
                    'Accès 24/7'
                ]),
                'featured_image' => '/uploads/spaces/grand-bureau.jpg',
                'status' => 'available',
                'sort_order' => 2,
                'is_active' => true
            ],

            // =============================================
            // BUREAUX MOYENS
            // =============================================
            [
                'name' => 'Bureau Moyen',
                'slug' => 'bureau-moyen',
                'type' => 'private',
                'capacity' => '2-4 personnes',
                'price' => 300, // Prix par défaut (prix journalier)
                'description' => 'Bureau moyen confortable, adapté aux petites équipes de 2 à 4 personnes. Cadre professionnel et calme.',
                'amenities' => json_encode([
                    'WiFi Fibre Haut Débit',
                    'Écran 32"',
                    'Tableau blanc',
                    'Climatisation',
                    'Chaises ergonomiques',
                    'Casier sécurisé',
                    'Accès 24/7'
                ]),
                'featured_image' => '/uploads/spaces/bureau-moyen.jpg',
                'status' => 'available',
                'sort_order' => 3,
                'is_active' => true
            ],

            // =============================================
            // PETITS BUREAUX
            // =============================================
            [
                'name' => 'Petit Bureau',
                'slug' => 'petit-bureau',
                'type' => 'private',
                'capacity' => '1-2 personnes',
                'price' => 200, // Prix par défaut (prix journalier)
                'description' => 'Petit bureau intime et fonctionnel, parfait pour les freelances et les indépendants. Calme et propice à la concentration.',
                'amenities' => json_encode([
                    'WiFi Fibre Haut Débit',
                    'Bureau ajustable',
                    'Chaise ergonomique',
                    'Climatisation',
                    'Casier sécurisé',
                    'Accès 24/7'
                ]),
                'featured_image' => '/uploads/spaces/petit-bureau.jpg',
                'status' => 'available',
                'sort_order' => 4,
                'is_active' => true
            ],

            // =============================================
            // GRANDES SALLES DE RÉUNION
            // =============================================
            [
                'name' => 'Grande Salle de Réunion',
                'slug' => 'grande-salle-reunion',
                'type' => 'meeting',
                'capacity' => '12-20 personnes',
                'price' => 600, // Prix par défaut (prix journalier)
                'description' => 'Grande salle de réunion équipée pour accueillir jusqu\'à 20 personnes. Idéale pour les séminaires, formations et grandes réunions.',
                'amenities' => json_encode([
                    'WiFi Fibre Haut Débit',
                    'Écran géant 85" 4K',
                    'Système audio professionnel',
                    'Visioconférence 4K',
                    'Tableau blanc interactif',
                    'Climatisation réversible',
                    'Service café/thé inclus'
                ]),
                'featured_image' => '/uploads/spaces/grande-salle-reunion.jpg',
                'status' => 'available',
                'sort_order' => 5,
                'is_active' => true
            ],

            // =============================================
            // PETITES SALLES DE RÉUNION
            // =============================================
            [
                'name' => 'Petite Salle de Réunion',
                'slug' => 'petite-salle-reunion',
                'type' => 'meeting',
                'capacity' => '4-8 personnes',
                'price' => 450, // Prix par défaut (prix journalier)
                'description' => 'Petite salle de réunion intime, parfaite pour les réunions d\'équipe, les entretiens clients et les brainstorming.',
                'amenities' => json_encode([
                    'WiFi Fibre Haut Débit',
                    'Écran 55" 4K',
                    'Système audio',
                    'Visioconférence',
                    'Tableau blanc',
                    'Climatisation',
                    'Service café/thé'
                ]),
                'featured_image' => '/uploads/spaces/petite-salle-reunion.jpg',
                'status' => 'available',
                'sort_order' => 6,
                'is_active' => true
            ],
        ];

        foreach ($spaces as $space) {
            Space::updateOrCreate(
                ['slug' => $space['slug']],
                $space
            );
        }
    }
}