<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpaceImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'space_id',
        'image_path',
        'is_primary',
        'sort_order'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer'
    ];

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function getImageUrlAttribute()
    {
        if ($this->image_path && file_exists(public_path($this->image_path))) {
            return asset($this->image_path);
        }
        return asset('images/placeholder.jpg');
    }
}