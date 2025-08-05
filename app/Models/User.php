<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'profile_picture',
        'role_id',
        'user_name',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function car()
    {
        return $this->hasMany(related: Car::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function profileVerification()
    {
        return $this->hasOne(Verification::class)->where('type', 'USER')
            ->orWhere('type', 'showroom');
    }
    public function showrooms()
    {
        return $this->hasMany(Showroom::class);
    }
    protected static function booted()
    {
        static::deleting(function ($user) {
            if ($user->profile_picture && Storage::exists(str_replace('storage/', '', $user->profile_picture))) {
                Storage::delete(str_replace('storage/', '', $user->profile_picture));
            }
        });
    }
}
