<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class UpdateAlmacenRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    
    public function rules(): array
    {
        return [
            'codigo' => 'required|unique:almacenes,codigo,' . $this->almacen->id,
            'nombre' => 'required|unique:almacenes,nombre,' . $this->almacen->id,
            'descripcion' => 'nullable|max:255',
            'direccion' => 'nullable|max:255',
            'estado' => 'boolean',
        ];
    }
}
