<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingOption extends Model
{
    protected $fillable = [
        'space_id', 'duration_type', 'price', 'duration_hours'
    ];

    const DURATION_TYPES = [
        'hourly' => 'À l\'heure',
        '2_hours' => '2 heures',
        'half_day' => 'Demi-journée',
        'daily' => 'Journée',
        'weekly' => 'Semaine',
        'monthly' => 'Mois',
        'yearly' => 'Année'
    ];

    public function space()
    {
        return $this->belongsTo(Space::class);
    }
}