<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PhoneCheckRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'device_id' => ['required','string','max:255'],
            'auth_code' => ['required','string','size:4'],
        ];
    }
}
