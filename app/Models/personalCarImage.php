<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalCarImage extends Model
{
    protected $fillable = ['personal_car_id', 'image_path', 'is_main'];

    public function personalCar(): BelongsTo
    {
        return $this->belongsTo(PersonalCar::class);
    }
}
