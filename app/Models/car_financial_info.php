<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class car_financial_info extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'price',
        'currency',
        'negotiable',
        'discount_percentage',
        'discount_amount'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'negotiable' => 'boolean'
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
