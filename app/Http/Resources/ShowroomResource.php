<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowroomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'location' => $this->location,
            'phone'    => $this->phone,
            'logo_url' => $this->logo ? asset('storage/' . $this->logo) : null,
            // 'user_id'  => $this->user_id,
            // 'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
