<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class car_technical_spec extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'horse_power',
        'engine_type',
        'cylinders'
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
