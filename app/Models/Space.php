<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Space extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'capacity',
        'price',
        'description',
        'amenities',
        'featured_image',
        'status',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'amenities' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    const TYPES = [
        'private' => 'Bureau Privé',
        'coworking' => 'Coworking',
        'meeting' => 'Salle de Réunion'
    ];

    const STATUSES = [
        'available' => 'Disponible',
        'occupied' => 'Occupé',
        'maintenance' => 'Maintenance'
    ];

    public function images()
    {
        return $this->hasMany(SpaceImage::class)->orderBy('sort_order');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getTypeLabelAttribute()
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getFeaturedImageUrlAttribute()
    {
        if ($this->featured_image && file_exists(public_path($this->featured_image))) {
            return asset($this->featured_image);
        }
        return asset('images/placeholder-space.jpg');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}