<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTipoUnidadRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|max:60|unique:tipo_unidades,nombre,' . $this->tipounidad->id,
            'descripcion' => 'nullable|max:255',
        ];
    }

}
