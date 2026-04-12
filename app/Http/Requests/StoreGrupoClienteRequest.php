<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGrupoClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255|unique:grupos_clientes,nombre',
            'descripcion' => 'nullable|string',
            'descuento_global' => 'nullable|numeric|min:0|max:100',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'estado' => 1 // 🔥 Siempre activo al crear
        ]);
    }
}
