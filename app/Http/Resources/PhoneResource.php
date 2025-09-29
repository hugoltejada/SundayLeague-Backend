<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhoneResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'platform' => $this->platform,
            'notification_token' => $this->notification_token,
            // Campos sensibles omitidos: password, auth_code, tokens internos
            'auth' => $this->auth,
            'authorized_at' => $this->authorized_at,
        ];
    }
}
