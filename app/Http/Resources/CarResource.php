<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'available_status' => $this->available_status,
            'general_info' => [
                'name' => $this->generalInfo->name ?? null,
                'brand' => $this->generalInfo->brand ?? null,
                'model' => $this->generalInfo->model ?? null,
                'gear_box' => $this->generalInfo->gear_box ?? null,
                'year' => $this->generalInfo->year ?? null,
                'fuel_type' => $this->generalInfo->fuel_type ?? null,
                'body_type' => $this->generalInfo->body_type ?? null,
            ],
            'financial_info' => [
                'price' => $this->financialInfo->price ?? null,
                'currency' => $this->financialInfo->currency ?? null,
                'negotiable' => $this->financialInfo->negotiable ?? null,
                'discount_percentage' => $this->financialInfo->discount_percentage ?? null,
                'discount_amount' => $this->financialInfo->discount_amount ?? null,
            ],
            'technical_specs' => [
                'horse_power' => $this->technicalSpecs->horse_power ?? null,
                'engine_type' => $this->technicalSpecs->engine_type ?? null,
                'cylinders' => $this->technicalSpecs->cylinders ?? null,
            ],
            'images' => $this->images->map(function ($image) {
                return [
                    'url' => asset($image->image_path),
                    'is_main' => $image->is_main,
                ];
            }),
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
        ];
    }
}
