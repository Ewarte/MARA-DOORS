<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoriaRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    
    public function rules(): array
    {
        return [
             'nombre' => 'required|unique:categorias,nombre',
            'descripcion' => 'nullable|max:255'
        ];
    }
}
