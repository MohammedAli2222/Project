<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'car_id',
        'carable_type',
        'user_id',
        'showroom_id',
        'reservation_date',
        'deposit_amount',
        'status',
        'expires_at',
    ];

    public function carable()
    {
        return $this->morphTo('carable', 'carable_type', 'car_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function showroom()
    {
        return $this->belongsTo(Showroom::class);
    }
}
