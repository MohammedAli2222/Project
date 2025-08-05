<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class car_images extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'image_path',
        'is_main'
    ];

    protected $casts = [
        'is_main' => 'boolean'
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }
}
