<?php

namespace Database\Seeders;

use App\Models\Gallery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GallerySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer le dossier s'il n'existe pas
        if (!file_exists(public_path('uploads/gallery'))) {
            mkdir(public_path('uploads/gallery'), 0777, true);
        }

        $galleries = [
            // =============================================
            // ESPACES
            // =============================================
            [
                'title' => 'Espace Coworking Lumineux',
                'description' => 'Notre espace de coworking principal avec lumière naturelle abondante, idéal pour travailler dans les meilleures conditions.',
                'category' => 'space',
                'sort_order' => 1,
                'image_url' => 'https://images.unsplash.com/photo-1497366216548-37526070297c'
            ],
            [
                'title' => 'Bureau Privé Premium',
                'description' => 'Bureau privé équipé avec écran 4K, chaise ergonomique et espace de rangement sécurisé.',
                'category' => 'space',
                'sort_order' => 2,
                'image_url' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36'
            ],
            [
                'title' => 'Salle de Réunion Executive',
                'description' => 'Salle de réunion équipée avec vidéoconférence, tableau blanc interactif et système audio professionnel.',
                'category' => 'space',
                'sort_order' => 3,
                'image_url' => 'https://images.unsplash.com/photo-1517502884422-41eaead166d4'
            ],
            [
                'title' => 'Espace Détente',
                'description' => 'Espace de détente avec canapés confortables, café et thé à volonté pour vos pauses.',
                'category' => 'space',
                'sort_order' => 4,
                'image_url' => 'https://images.unsplash.com/photo-1556761175-4b46a572b786'
            ],
            [
                'title' => 'Terrasse Extérieure',
                'description' => 'Terrasse avec vue panoramique, parfaite pour les pauses déjeuner et le networking informel.',
                'category' => 'space',
                'sort_order' => 5,
                'image_url' => 'https://images.unsplash.com/photo-1556761175-4b46a572b786'
            ],
            [
                'title' => 'Zone de Créativité',
                'description' => 'Espace dédié à la créativité avec tableaux blancs, post-it et mobilier modulable.',
                'category' => 'space',
                'sort_order' => 6,
                'image_url' => 'https://images.unsplash.com/photo-1556761175-4b46a572b786'
            ],

            // =============================================
            // ÉVÉNEMENTS
            // =============================================
            [
                'title' => 'Meetup Entrepreneurs',
                'description' => 'Notre dernier meetup mensuel avec plus de 50 entrepreneurs locaux. Networking, partage d\'expériences et collaboration.',
                'category' => 'event',
                'sort_order' => 7,
                'image_url' => 'https://images.unsplash.com/photo-1556761175-4b46a572b786'
            ],
            [
                'title' => 'Atelier Formation',
                'description' => 'Atelier de formation sur le marketing digital animé par des experts du secteur.',
                'category' => 'event',
                'sort_order' => 8,
                'image_url' => 'https://images.unsplash.com/photo-1556761175-4b46a572b786'
            ],
            [
                'title' => 'Cocktail de Bienvenue',
                'description' => 'Cocktail d\'accueil pour nos nouveaux membres, l\'occasion de rencontrer la communauté.',
                'category' => 'event',
                'sort_order' => 9,
                'image_url' => 'https://images.unsplash.com/photo-1556761175-4b46a572b786'
            ],
            [
                'title' => 'Conférence Tech',
                'description' => 'Conférence sur les dernières innovations technologiques avec des intervenants de renom.',
                'category' => 'event',
                'sort_order' => 10,
                'image_url' => 'https://images.unsplash.com/photo-1556761175-4b46a572b786'
            ],

            // =============================================
            // COMMUNAUTÉ
            // =============================================
            [
                'title' => 'Notre Communauté',
                'description' => 'Une communauté dynamique d\'entrepreneurs, freelances et créatifs qui collaborer et s\'entraident.',
                'category' => 'community',
                'sort_order' => 11,
                'image_url' => 'https://images.unsplash.com/photo-1556761175-4b46a572b786'
            ],
            [
                'title' => 'Collaboration',
                'description' => 'Nos membres en pleine collaboration sur un projet commun.',
                'category' => 'community',
                'sort_order' => 12,
                'image_url' => 'https://images.unsplash.com/photo-1556761175-4b46a572b786'
            ],
            [
                'title' => 'Happy Hour',
                'description' => 'Notre happy hour mensuelle pour célébrer les succès de la communauté.',
                'category' => 'community',
                'sort_order' => 13,
                'image_url' => 'https://images.unsplash.com/photo-1556761175-4b46a572b786'
            ],
            [
                'title' => 'Coworking Ensemble',
                'description' => 'L\'ambiance unique de notre espace où productivité rime avec convivialité.',
                'category' => 'community',
                'sort_order' => 14,
                'image_url' => 'https://images.unsplash.com/photo-1556761175-4b46a572b786'
            ],
        ];

        foreach ($galleries as $gallery) {
            try {
                // Télécharger l'image depuis l'URL
                $imageContent = file_get_contents($gallery['image_url']);
                
                if ($imageContent === false) {
                    $this->command->error("Impossible de télécharger l'image pour: " . $gallery['title']);
                    continue;
                }
                
                // Générer un nom unique pour l'image
                $extension = pathinfo(parse_url($gallery['image_url'], PHP_URL_PATH), PATHINFO_EXTENSION);
                if (!$extension) {
                    $extension = 'jpg';
                }
                
                $imageName = time() . '_' . uniqid() . '_' . Str::slug($gallery['title']) . '.' . $extension;
                $imagePath = public_path('uploads/gallery/' . $imageName);
                
                // Sauvegarder l'image
                file_put_contents($imagePath, $imageContent);
                
                // Créer l'entrée en base de données
                Gallery::create([
                    'title' => $gallery['title'],
                    'slug' => Str::slug($gallery['title']) . '-' . time() . '-' . uniqid(),
                    'description' => $gallery['description'],
                    'image_path' => '/uploads/gallery/' . $imageName,
                    'category' => $gallery['category'],
                    'sort_order' => $gallery['sort_order'],
                    'is_active' => true
                ]);
                
                $this->command->info("Image ajoutée: " . $gallery['title']);
                
                // Petite pause pour éviter les doublons de timestamp
                sleep(1);
                
            } catch (\Exception $e) {
                $this->command->error("Erreur pour " . $gallery['title'] . ": " . $e->getMessage());
            }
        }
    }
}