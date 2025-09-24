<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PhoneVerifyGoogleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_token'           => 'required|string',
            'platform'           => 'nullable|string|max:50',
            'notification_token' => 'nullable|string|max:1024',
        ];
    }

    public function messages(): array
    {
        return [
            'id_token.required' => 'Google ID Token es obligatorio.',
        ];
    }
}
