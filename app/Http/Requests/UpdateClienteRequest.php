<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClienteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $cliente = $this->route('cliente');
        return [
            'razon_social' => 'required|max:80',
            'direccion' => 'required|max:80',
            'telefono' => 'required|max:20',
            'documento_id' => 'required|integer|exists:documentos,id',
            'numero_documento' => 'required|max:20|unique:personas,numero_documento,'.$cliente->persona->id,
            'grupo_cliente_id' => 'required|integer|exists:grupos_clientes,id',
            'estado' => 'required|boolean',
        ];
    }
}
