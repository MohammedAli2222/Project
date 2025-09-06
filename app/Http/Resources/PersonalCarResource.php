<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PersonalCarResource extends JsonResource
{
    public function toArray($request)
    {
        $mainImage = $this->images->where('is_main', true)->first()
            ?? $this->images->first();

        return [
            // المعلومات الأساسية من جدول personal_cars
            'id' => $this->id,
            'user_id' => $this->user_id,
            'condition' => $this->condition,
            'vin' => $this->vin,
            'available_status' => $this->available_status,
            'is_rentable' => (bool) $this->is_rentable,
            'rental_cost_per_hour' => $this->rental_cost_per_hour,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),

            // المعلومات من جدول personal_car_infos
            'name' => $this->info->name ?? null,
            'brand' => $this->info->brand ?? null,
            'model' => $this->info->model ?? null,
            'gear_box' => $this->info->gear_box ?? null,
            'year' => $this->info->year ?? null,
            'fuel_type' => $this->info->fuel_type ?? null,
            'body_type' => $this->info->body_type ?? null,
            'color' => $this->info->color ?? null,
            'engine_type' => $this->info->engine_type ?? null,
            'cylinders' => $this->info->cylinders ?? null,
            'horse_power' => $this->info->horse_power ?? null,
            'price' => (float) ($this->info->price ?? 0),
            'currency' => $this->info->currency ?? 'SYP',
            'negotiable' => (bool) ($this->info->negotiable ?? false),
            'discount_percentage' => $this->info->discount_percentage ? (float) $this->info->discount_percentage : null,
            'discount_amount' => $this->info->discount_amount ? (float) $this->info->discount_amount : null,
            'final_price' => $this->calculateFinalPrice(),

            // الصور
            'images' => $this->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_url' => asset($image->image_path),
                    'is_main' => (bool) $image->is_main,
                    'full_path' => $image->image_path,
                ];
            }),

            // الصورة الرئيسية
            'main_image' => $mainImage ? [
                'id' => $mainImage->id,
                'image_url' => asset($mainImage->image_path),
                'is_main' => true,
                'full_path' => $mainImage->image_path,
            ] : null,
        ];
    }

    /**
     * Calculate final price with discount
     */
    private function calculateFinalPrice(): float
    {
        if (!$this->info) {
            return 0;
        }

        $price = (float) $this->info->price;

        if ($this->info->discount_percentage) {
            $discount = $price * ($this->info->discount_percentage / 100);
            return round($price - $discount, 2);
        }

        if ($this->info->discount_amount) {
            return round($price - $this->info->discount_amount, 2);
        }

        return $price;
    }
}
