<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_persona' => 'required|string',
            'razon_social' => 'required|max:80',
            'direccion' => 'required|max:80',
            'telefono' => 'required|max:20',
            'documento_id' => 'required|integer|exists:documentos,id',
            'numero_documento' => 'required|max:20|unique:personas,numero_documento',
            'grupo_cliente_id' => 'required|integer|exists:grupos_clientes,id',
        ];
    }
}

