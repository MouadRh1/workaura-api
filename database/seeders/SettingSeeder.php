<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Informations générales
            [
                'key' => 'site_name',
                'value' => 'WORKAURA',
                'type' => 'text',
                'group' => 'general'
            ],
            [
                'key' => 'site_description',
                'value' => 'Espace de coworking premium à Témara. Bureaux privés, espaces coworking & salles de réunion.',
                'type' => 'textarea',
                'group' => 'general'
            ],
            [
                'key' => 'site_logo',
                'value' => '/images/logo.png',
                'type' => 'image',
                'group' => 'general'
            ],
            [
                'key' => 'site_favicon',
                'value' => '/images/favicon.ico',
                'type' => 'image',
                'group' => 'general'
            ],
            
            // Contact
            [
                'key' => 'contact_email',
                'value' => 'contact@workaura.com',
                'type' => 'email',
                'group' => 'contact'
            ],
            [
                'key' => 'contact_phone',
                'value' => '+212 5XX XXX XXX',
                'type' => 'text',
                'group' => 'contact'
            ],
            [
                'key' => 'contact_address',
                'value' => 'Centre Témara, Maroc',
                'type' => 'textarea',
                'group' => 'contact'
            ],
            [
                'key' => 'contact_map_url',
                'value' => 'https://maps.google.com/?q=Témara+Maroc',
                'type' => 'url',
                'group' => 'contact'
            ],
            
            // Horaires
            [
                'key' => 'opening_hours_weekday',
                'value' => '09:00 - 20:00',
                'type' => 'text',
                'group' => 'hours'
            ],
            [
                'key' => 'opening_hours_saturday',
                'value' => '10:00 - 18:00',
                'type' => 'text',
                'group' => 'hours'
            ],
            [
                'key' => 'opening_hours_sunday',
                'value' => 'Fermé',
                'type' => 'text',
                'group' => 'hours'
            ],
            
            // Réseaux sociaux
            [
                'key' => 'social_facebook',
                'value' => 'https://facebook.com/workaura',
                'type' => 'url',
                'group' => 'social'
            ],
            [
                'key' => 'social_instagram',
                'value' => 'https://instagram.com/workaura',
                'type' => 'url',
                'group' => 'social'
            ],
            [
                'key' => 'social_linkedin',
                'value' => 'https://linkedin.com/company/workaura',
                'type' => 'url',
                'group' => 'social'
            ],
            [
                'key' => 'social_twitter',
                'value' => 'https://twitter.com/workaura',
                'type' => 'url',
                'group' => 'social'
            ],
            
            // SEO
            [
                'key' => 'seo_title',
                'value' => 'Workaura - Espace de Coworking à Témara',
                'type' => 'text',
                'group' => 'seo'
            ],
            [
                'key' => 'seo_description',
                'value' => 'Découvrez Workaura, l\'espace de coworking premium à Témara. Bureaux privés, espaces partagés et salles de réunion pour entrepreneurs et freelances.',
                'type' => 'textarea',
                'group' => 'seo'
            ],
            [
                'key' => 'seo_keywords',
                'value' => 'coworking, Témara, bureau, espace de travail, freelance, entrepreneur',
                'type' => 'text',
                'group' => 'seo'
            ],
            [
                'key' => 'seo_og_image',
                'value' => '/images/og-image.jpg',
                'type' => 'image',
                'group' => 'seo'
            ],
            
            // Réservations
            [
                'key' => 'booking_start_hour',
                'value' => '10:00',
                'type' => 'time',
                'group' => 'booking'
            ],
            [
                'key' => 'booking_end_hour',
                'value' => '22:00',
                'type' => 'time',
                'group' => 'booking'
            ],
            [
                'key' => 'booking_max_days_advance',
                'value' => '30',
                'type' => 'number',
                'group' => 'booking'
            ],
            [
                'key' => 'booking_min_duration_hours',
                'value' => '1',
                'type' => 'number',
                'group' => 'booking'
            ],
            [
                'key' => 'booking_cancellation_deadline_hours',
                'value' => '24',
                'type' => 'number',
                'group' => 'booking'
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}