<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'showroom_id',
        'available_status',
        'is_rentable',
        'rental_cost_per_hour'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function showroom()
    {
        return $this->belongsTo(Showroom::class);
    }

    public function generalInfo()
    {
        return $this->hasOne(car_general_info::class);
    }

    public function technicalSpecs()
    {
        return $this->hasOne(car_technical_spec::class);
    }

    public function financialInfo()
    {
        return $this->hasOne(car_financial_info::class);
    }

    public function images()
    {
        return $this->hasMany(car_images::class);
    }

    public function mainImage()
    {
        return $this->hasOne(car_images::class)->where('is_main', true);
    }

    public function rates()
    {
        return $this->hasMany(car_rate::class);
    }

    public function favorites()
    {
        return $this->hasMany(car_favorite::class);
    }

    public function reservations()
    {
        return $this->hasMany(reservation::class);
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function auction()
    {
        return $this->hasOne(Auction::class);
    }

    // حساب متوسط التقييم
    public function getAverageRatingAttribute()
    {
        return $this->rates()->avg('rate') ?? 0;
    }

    // الحصول على السعر بعد الخصم
    public function getFinalPriceAttribute()
    {
        $financial = $this->financialInfo;
        if (!$financial) return 0;

        $price = $financial->price;

        if ($financial->discount_amount) {
            return $price - $financial->discount_amount;
        }

        if ($financial->discount_percentage) {
            return $price - ($price * $financial->discount_percentage / 100);
        }

        return $price;
    }
}
