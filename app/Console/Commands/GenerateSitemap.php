<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Space;
use App\Models\Gallery;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap';

    public function handle()
    {
        $sitemap = Sitemap::create();
        
        // Pages statiques
        $sitemap->add(Url::create('/')->setPriority(1.0));
        $sitemap->add(Url::create('/espaces')->setPriority(0.9));
        $sitemap->add(Url::create('/galerie')->setPriority(0.8));
        $sitemap->add(Url::create('/contact')->setPriority(0.7));
        
        // Pages dynamiques - Espaces
        foreach (Space::where('is_active', true)->get() as $space) {
            $sitemap->add(Url::create("/espaces/{$space->slug}")
                ->setLastModificationDate($space->updated_at)
                ->setPriority(0.8));
        }
        
        // Pages dynamiques - Galerie
        foreach (Gallery::where('is_active', true)->get() as $item) {
            $sitemap->add(Url::create("/galerie/{$item->slug}")
                ->setLastModificationDate($item->updated_at)
                ->setPriority(0.6));
        }
        
        $sitemap->writeToFile(public_path('sitemap.xml'));
        
        $this->info('Sitemap generated successfully!');
    }
}