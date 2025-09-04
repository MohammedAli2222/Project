<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalCarInfo extends Model
{
    protected $fillable = [
        'personal_car_id',
        'name',
        'brand',
        'model',
        'gear_box',
        'year',
        'fuel_type',
        'body_type',
        'color',
        'engine_type',
        'cylinders',
        'horse_power',
        'price',
        'currency',
        'negotiable',
        'discount_percentage',
        'discount_amount'
    ];

    public function personalCar(): BelongsTo
    {
        return $this->belongsTo(PersonalCar::class);
    }
}
