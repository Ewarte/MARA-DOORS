<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAlmacenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return True;
    }

    
    public function rules(): array
    {
        return [
            'codigo' => 'required|unique:almacenes,codigo',
            'nombre' => 'required|unique:almacenes,nombre',
            'descripcion' => 'nullable|max:255',
            'direccion' => 'nullable|max:255',
            'estado' => 'boolean',
        ];
    }
}
