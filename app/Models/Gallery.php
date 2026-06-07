<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'image_path',
        'category',
        'sort_order',
        'is_active'
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Les catégories disponibles pour la galerie.
     */
    const CATEGORIES = [
        'space' => 'Espace',
        'event' => 'Événement',
        'community' => 'Communauté'
    ];

    /**
     * Obtenir l'URL complète de l'image.
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if ($this->image_path && file_exists(public_path($this->image_path))) {
            return asset($this->image_path);
        }
        return asset('images/placeholder.jpg');
    }

    /**
     * Obtenir la catégorie en français.
     *
     * @return string
     */
    public function getCategoryLabelAttribute()
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    /**
     * Scope pour les images actives.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope par catégorie.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope ordonné.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
    }
}