<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PhoneRegisterEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:255'],
            'email'              => ['required', 'email', 'unique:phones,email'],
            'password'           => ['required', 'string', 'min:6'],
            'device_id'          => ['nullable', 'string', 'max:255'],
            'platform'           => ['nullable', 'string', 'max:50'],
            'notification_token' => ['nullable', 'string', 'max:1024'],
        ];
    }
}
