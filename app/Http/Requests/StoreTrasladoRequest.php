<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrasladoRequest extends FormRequest
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
        return [
            'origen_almacen_id' => 'nullable|exists:almacenes,id',
            'destino_almacen_id' => 'nullable|exists:almacenes,id',
            'costo_envio' => 'required|numeric|min:0',
            'user_id' => 'required|exists:users,id',
            'arrayidproducto' => 'required|array|min:1',
            'arrayidproducto.*' => 'required|exists:productos,id',
            'arraycantidad' => 'required|array|min:1',
            'arraycantidad.*' => 'required|numeric|min:0.0001',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_hora.required' => 'La fecha y hora es requerida',
            'fecha_hora.date_format' => 'El formato de fecha debe ser YYYY-MM-DD HH:MM',
            'origen_almacen_id.exists' => 'El almacén origen seleccionado no existe',
            'destino_almacen_id.exists' => 'El almacén destino seleccionado no existe',
            'costo_envio.required' => 'El costo de envío es requerido',
            'costo_envio.numeric' => 'El costo de envío debe ser un número',
            'costo_envio.min' => 'El costo de envío no puede ser negativo',
            'user_id.required' => 'El usuario es requerido',
            'user_id.exists' => 'El usuario seleccionado no existe',
            'arrayidproducto.required' => 'Debe agregar al menos un producto',
            'arrayidproducto.min' => 'Debe agregar al menos un producto',
            'arrayidproducto.*.exists' => 'Uno de los productos seleccionados no existe',
            'arraycantidad.required' => 'Las cantidades son requeridas',
            'arraycantidad.*.numeric' => 'Las cantidades deben ser números',
            'arraycantidad.*.min' => 'La cantidad debe ser al menos 0.0001',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'arraycantidad' => array_map('floatval', $this->arraycantidad ?? []),
            'user_id' => auth()->id(),
        ]);
    }
}
