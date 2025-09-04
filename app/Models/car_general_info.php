<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class car_general_info extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'name',
        'brand',
        'model',
        'gear_box',
        'year',
        'fuel_type',
        'body_type',
        'vin',
        'condition',
        'color'
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
