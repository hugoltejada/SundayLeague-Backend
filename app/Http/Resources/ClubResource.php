<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClubResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'stadium' => $this->stadium,
            'location' => $this->location,
            'invitation_code' => $this->invitation_code,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'default_schedules' => $this->default_schedules,
            'match_duration' => $this->match_duration,
            'president_id' => $this->president_id,
            'president' => $this->whenLoaded('president', function () {
                return new PlayerResource($this->president);
            }),
            'players' => PlayerResource::collection($this->whenLoaded('players')),
        ];
    }
}
