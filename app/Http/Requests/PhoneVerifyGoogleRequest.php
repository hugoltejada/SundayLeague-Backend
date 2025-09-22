<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PhoneVerifyGoogleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 👈 en producción puedes poner lógica extra si hace falta
    }

    public function rules(): array
    {
        return [
            'id_token'           => ['required', 'string'],        // obligatorio
            'device_id'          => ['nullable', 'string', 'max:255'],
            'platform'           => ['nullable', 'string', 'max:50'],
            'notification_token' => ['nullable', 'string', 'max:1024'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_token.required' => 'El token de Google es obligatorio.',
            'id_token.string'   => 'El token debe ser una cadena válida.',
            'device_id.string'  => 'El device_id debe ser texto.',
            'platform.string'   => 'El platform debe ser texto.',
        ];
    }
}
