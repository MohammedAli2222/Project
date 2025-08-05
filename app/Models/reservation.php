<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'car_id',
        'showroom_id',
        'reservation_date',
        'deposit_amount',
        'status'
    ];

    protected $casts = [
        'reservation_date' => 'datetime',
        'deposit_amount' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function showroom()
    {
        return $this->belongsTo(Showroom::class);
    }
}
