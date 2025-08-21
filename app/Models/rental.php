<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class rental extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'car_id',
        'showroom_id',
        'start_date',
        'end_date',
        'rental_cost_per_hour',
        'total_cost',
        'status'
    ];

    const STATUS_PENDING   = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_ACTIVE    = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'rental_cost_per_hour' => 'decimal:2',
        'total_cost' => 'decimal:2'
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
    public function getDurationInHoursAttribute()
    {
        return $this->start_date->diffInHours($this->end_date);
    }
}
