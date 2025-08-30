<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'showroom_id',
        'starting_price',
        'current_price',
        'winner_id',
        'status',
        'start_time',
        'end_time',
    ];

    protected $dates = ['start_time', 'end_time'];


    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function showroom()
    {
        return $this->belongsTo(Showroom::class);
    }
    public function bids()
    {
        return $this->hasMany(Bid::class);
    }
}
