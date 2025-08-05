<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'first_name'      => $this->first_name,
            'last_name'       => $this->last_name,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'user_name'       => $this->user_name,
            'profile_picture' => $this->profile_picture ? asset($this->profile_picture) : null,
            // 'role_id'         => $this->role_id
        ];
    }
}
