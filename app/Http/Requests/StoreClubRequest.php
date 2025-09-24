<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClubRequest extends FormRequest
{
    public function authorize(): bool
    {
        // 👈 si en algún momento quieres permisos, aquí los gestionas
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255|unique:clubs,name',
            'stadium'     => 'nullable|string|max:255',
            'schedule'    => 'nullable|string|max:255',
            'location'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'president_id' => 'nullable|exists:players,id',
        ];
    }
}
