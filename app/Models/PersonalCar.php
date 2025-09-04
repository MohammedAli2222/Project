<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalCar extends Model
{
    protected $fillable = [
        'user_id',
        'condition',
        'vin',
        'available_status',
        'is_rentable',
        'rental_cost_per_hour',
    ];

    public function info(): HasOne
    {
        return $this->hasOne(PersonalCarInfo::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PersonalCarImage::class);
    }

    public function mainImage()
    {
        return $this->hasOne(PersonalCarImage::class)->where('is_main', true);
    }
}
