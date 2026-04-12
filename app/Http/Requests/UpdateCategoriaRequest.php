<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoriaRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        
        return [
            'nombre' => 'required|max:60|unique:categorias,nombre,'.$this->categoria->id,
            'descripcion' => 'nullable|max:255'
        ];
    }
}
