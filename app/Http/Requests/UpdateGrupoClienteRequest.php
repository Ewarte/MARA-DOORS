<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGrupoClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255|unique:grupos_clientes,nombre,' . $this->grupocliente->id,
            'descripcion' => 'nullable|string',
            'descuento_global' => 'nullable|numeric|min:0|max:100',
            'estado' => 'required|boolean',
        ];
    }
}
