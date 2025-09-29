<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'phone_id' => $this->phone_id,
            'name' => $this->name,
            'age' => $this->age,
            'position' => $this->position,
            'nationality' => $this->nationality,
            'description' => $this->description,
            'height' => $this->height,
            'weight' => $this->weight,
            'strong_foot' => $this->strong_foot,
            'avatar' => $this->avatar,
            'pivot' => $this->when($this->pivot, function () {
                return [
                    'is_active' => (bool)($this->pivot->is_active ?? false),
                ];
            }),
            'phone' => $this->whenLoaded('phone', function () {
                return new PhoneResource($this->phone);
            }),
        ];
    }
}
