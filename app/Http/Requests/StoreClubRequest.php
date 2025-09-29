<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClubRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Si en algún momento quieres permisos, aquí los gestionas
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255|unique:clubs,name',
            'stadium'      => 'nullable|string|max:255',
            'schedule'     => 'nullable|string|max:255',
            'location'     => 'nullable|string|max:255',
            'description'  => 'nullable|string|max:500',
            'president_id' => 'nullable|exists:players,id',

            'image_url'    => [
                'nullable',
                'string',
                'url',
                'max:500',
            ],
        ];
    }
}
