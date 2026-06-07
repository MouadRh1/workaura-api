<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    const ROLES = [
        'admin' => 'Administrateur',
        'user' => 'Utilisateur'
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isUser()
    {
        return $this->role === 'user';
    }

    public function getRoleLabelAttribute()
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar && file_exists(public_path($this->avatar))) {
            return asset($this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=F4620A&color=fff';
    }
}