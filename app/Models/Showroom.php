<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Showroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'location',
        'logo',
        'phone',
    ];
    public function showroomVerification()
    {
        return $this->hasOne(Verification::class)->where('type', 'Showroom');
    }

    // العلاقات
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cars()
    {
        return $this->hasMany(Car::class);
    }

    // public function rates()
    // {
    //     return $this->hasMany(ShowroomRate::class);
    // }

    // public function notes()
    // {
    //     return $this->hasMany(ShowroomNote::class);
    // }

    // public function favorites()
    // {
    //     return $this->belongsToMany(User::class, 'showroom_favorite')
    //         ->withTimestamps();
    // }

    // public function auctions()
    // {
    //     return $this->hasMany(Auction::class);
    // }

    // public function rentals()
    // {
    //     return $this->hasMany(Rental::class);
    // }
}
