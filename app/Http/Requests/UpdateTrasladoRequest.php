<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTrasladoRequest extends FormRequest
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
            'origen_almacen_id' => 'required|exists:almacenes,id',
            'destino_almacen_id' => 'required|exists:almacenes,id|different:origen_almacen_id',
            'costo_envio' => 'required|numeric|min:0',
            'arrayidproducto' => 'required|array|min:1',
            'arrayidproducto.*' => 'required|exists:productos,id',
            'arraycantidad' => 'required|array|min:1',
            'arraycantidad.*' => 'required|numeric|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'origen_almacen_id.required' => 'El almacén origen es requerido',
            'origen_almacen_id.exists' => 'El almacén origen seleccionado no existe',
            'destino_almacen_id.required' => 'El almacén destino es requerido',
            'destino_almacen_id.exists' => 'El almacén destino seleccionado no existe',
            'destino_almacen_id.different' => 'El almacén origen y destino no pueden ser iguales',
            'fecha_hora.required' => 'La fecha y hora es requerida',
            'fecha_hora.date_format' => 'El formato de fecha debe ser válido',
            'costo_envio.required' => 'El costo de envío es requerido',
            'costo_envio.numeric' => 'El costo de envío debe ser un número',
            'costo_envio.min' => 'El costo de envío no puede ser negativo',
            'arrayidproducto.required' => 'Debe agregar al menos un producto',
            'arrayidproducto.min' => 'Debe agregar al menos un producto',
            'arrayidproducto.*.required' => 'Todos los productos son requeridos',
            'arrayidproducto.*.exists' => 'Uno de los productos seleccionados no existe',
            'arraycantidad.required' => 'Las cantidades son requeridas',
            'arraycantidad.min' => 'Debe agregar al menos una cantidad',
            'arraycantidad.*.required' => 'Todas las cantidades son requeridas',
            'arraycantidad.*.numeric' => 'Las cantidades deben ser números',
            'arraycantidad.*.min' => 'La cantidad debe ser al menos 1',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'arraycantidad' => array_map('floatval', $this->arraycantidad ?? []),
        ]);
    }
}
