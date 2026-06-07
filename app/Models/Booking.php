<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'space_id',
        'booking_date',
        'end_date',
        'start_time',
        'end_time',
        'duration_type',
        'status',
        'payment_status',
        'total_amount',
        'unit_price',
        'notes',
        'guest_name',
        'guest_email',
        'guest_phone',
        'guest_token'
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'booking_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'total_amount' => 'decimal:2',
        'unit_price' => 'decimal:2'
    ];

    /**
     * Les types de durée disponibles.
     */
    const DURATION_TYPES = [
        'hourly' => 'À l\'heure',
        '2_hours' => '2 heures',
        'half_day' => 'Demi-journée',
        'daily' => 'Journée',
        'weekly' => 'Semaine',
        'monthly' => 'Mois',
        'yearly' => 'Année'
    ];

    /**
     * Les statuts de réservation.
     */
    const STATUSES = [
        'pending' => 'En attente',
        'confirmed' => 'Confirmée',
        'cancelled' => 'Annulée'
    ];

    /**
     * Les statuts de paiement.
     */
    const PAYMENT_STATUSES = [
        'pending' => 'En attente',
        'paid' => 'Payé',
        'failed' => 'Échoué'
    ];

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    /**
     * Accesseurs
     */
    public function getDurationTypeLabelAttribute()
    {
        return self::DURATION_TYPES[$this->duration_type] ?? $this->duration_type;
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getPaymentStatusLabelAttribute()
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? $this->payment_status;
    }

    public function getDurationInHoursAttribute()
    {
        if ($this->start_time && $this->end_time) {
            return Carbon::parse($this->start_time)->diffInHours(Carbon::parse($this->end_time));
        }
        return null;
    }

    public function getDurationInDaysAttribute()
    {
        if ($this->booking_date && $this->end_date) {
            return Carbon::parse($this->booking_date)->diffInDays(Carbon::parse($this->end_date)) + 1;
        }
        return 1;
    }

    public function getCustomerNameAttribute()
    {
        return $this->guest_name ?? $this->user?->name;
    }

    public function getCustomerEmailAttribute()
    {
        return $this->guest_email ?? $this->user?->email;
    }

    public function getCustomerPhoneAttribute()
    {
        return $this->guest_phone ?? $this->user?->phone;
    }

    /**
     * Vérifier si le créneau est disponible (pour les réservations courtes)
     */
    public static function isAvailable($spaceId, $date, $startTime, $endTime)
    {
        return !self::where('space_id', $spaceId)
            ->where('booking_date', $date)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            })
            ->exists();
    }

    /**
     * Vérifier la disponibilité pour une période longue
     */
    public static function isAvailableForPeriod($spaceId, $startDate, $endDate)
    {
        return !self::where('space_id', $spaceId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('booking_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('booking_date', '<=', $startDate)
                          ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();
    }

    /**
     * Vérifier si la réservation peut être annulée
     */
    public function isCancellable()
    {
        return $this->status === 'pending';
    }

    /**
     * Vérifier si la réservation peut être confirmée
     */
    public function isConfirmable()
    {
        return $this->status === 'pending';
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhere('guest_email', function ($sub) use ($userId) {
                  $user = User::find($userId);
                  if ($user) {
                      $sub->select('email')->from('users')->where('id', $userId);
                  }
              });
        });
    }

    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('booking_date', $date);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('booking_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate]);
        });
    }

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            // Si pas de token généré, en créer un
            if (!$booking->guest_token && !$booking->user_id) {
                $booking->guest_token = \Illuminate\Support\Str::random(64);
            }
            
            // Si pas de statut défini, mettre en attente
            if (!$booking->status) {
                $booking->status = 'pending';
            }
            
            // Si pas de statut de paiement, mettre en attente
            if (!$booking->payment_status) {
                $booking->payment_status = 'pending';
            }
        });
    }
}