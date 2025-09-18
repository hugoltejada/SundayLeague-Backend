<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PhoneRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // phone
            'device_id'          => ['required', 'string', 'max:255'],
            'email'              => ['nullable', 'email', 'max:255'],
            'platform'           => ['nullable', 'string', 'max:50'],
            'notification_token' => ['nullable', 'string', 'max:1024'],
            'player_id'          => ['nullable', 'integer', 'exists:players,id'],

            // player
            'name'        => ['required', 'string', 'max:255'],
            'age'         => ['nullable', 'integer'],
            'position'    => ['nullable', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:255'],
        ];
    }
}
