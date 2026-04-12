<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Datos generales
            'fecha_hora' => 'required|date|before_or_equal:now',
            'numero_comprobante' => 'nullable|string|max:255|unique:ventas,numero_comprobante',
            'total' => 'required|numeric|min:0.01',
            // Pagos (pueden ser mixtos)
            'pagos_metodo' => 'nullable|array',
            'pagos_metodo.*' => 'required_with:pagos_monto.*|in:efectivo,debito,qr,deposito,otro',
            'pagos_monto' => 'nullable|array',
            'pagos_monto.*' => 'required_with:pagos_metodo.*|numeric|min:0|max:999999999',

            // Compatibilidad con flujo anterior
            'metodo_pago' => 'nullable|in:efectivo,debito,qr,deposito,otro',
            'monto_pagado' => 'nullable|numeric|min:0|max:999999999',
            'cliente_id' => 'required|exists:clientes,id',
            'user_id' => 'required|exists:users,id',
            'comprobante_id' => 'required|exists:comprobantes,id',
            'almacen_id' => 'required|exists:almacenes,id',
            
            // Arrays de productos
            'arrayidproducto' => 'required|array|min:1',
            'arrayidproducto.*' => 'required|integer|exists:productos,id',
            'arraycantidad' => 'required|array|min:1|size:' . count($this->arrayidproducto ?? []),
            'arraycantidad.*' => 'required|numeric|min:0.001|max:999999',
            'arrayprecioventa' => 'required|array|min:1|size:' . count($this->arrayidproducto ?? []),
            'arrayprecioventa.*' => 'required|numeric|min:0.01|max:999999',
            'arraydescuento' => 'nullable|array|size:' . count($this->arrayidproducto ?? []),
            'arraydescuento.*' => 'nullable|numeric|min:0|max:999999',
        ];
    }

    public function messages(): array
    {
        return [
            // Generales
            'fecha_hora.required' => 'La fecha y hora son obligatorias',
            'fecha_hora.before_or_equal' => 'La fecha no puede ser futura',
            'numero_comprobante.unique' => 'Este número de comprobante ya existe',
            'total.min' => 'El total debe ser mayor a 0',
            'cliente_id.required' => 'Debe seleccionar un cliente',
            'cliente_id.exists' => 'El cliente seleccionado no existe',
            'almacen_id.required' => 'Debe seleccionar una sucursal/almacén',
            'almacen_id.exists' => 'La sucursal seleccionada no existe',
            'comprobante_id.required' => 'Debe seleccionar un tipo de comprobante',
            
            // Productos
            'arrayidproducto.required' => 'Debe agregar al menos un producto a la venta',
            'arrayidproducto.min' => 'Debe agregar al menos un producto',
            'arrayidproducto.*.exists' => 'Uno de los productos seleccionados no existe',
            
            // Cantidades
            'arraycantidad.required' => 'Las cantidades son obligatorias',
            'arraycantidad.size' => 'El número de cantidades no coincide con los productos',
            'arraycantidad.*.min' => 'La cantidad mínima es 0.001',
            'arraycantidad.*.max' => 'La cantidad es demasiado grande',
            
            // Precios
            'arrayprecioventa.required' => 'Los precios de venta son obligatorios',
            'arrayprecioventa.size' => 'El número de precios no coincide con los productos',
            'arrayprecioventa.*.min' => 'El precio de venta mínimo es 0.01',
            'arrayprecioventa.*.max' => 'El precio es demasiado grande',
            
            // Descuentos
            'arraydescuento.*.min' => 'El descuento no puede ser negativo',
            'arraydescuento.*.max' => 'El descuento es demasiado grande',
        ];
    }

    protected function prepareForValidation()
    {
        // Convertir valores a números y limpiar
        $this->merge([
            'arraycantidad' => array_map(fn($v) => floatval($v ?? 0), $this->arraycantidad ?? []),
            'arrayprecioventa' => array_map(fn($v) => floatval($v ?? 0), $this->arrayprecioventa ?? []),
            'arraydescuento' => array_map(fn($v) => floatval($v ?? 0), $this->arraydescuento ?? []),
            'total' => floatval($this->total ?? 0),
            'monto_pagado' => floatval($this->monto_pagado ?? 0),
            'pagos_monto' => array_map(fn($v) => floatval($v ?? 0), $this->pagos_monto ?? []),
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar que el descuento no sea mayor al subtotal
            if ($this->arrayidproducto) {
                foreach ($this->arrayidproducto as $index => $productoId) {
                    $cantidad = $this->arraycantidad[$index] ?? 0;
                    $precio = $this->arrayprecioventa[$index] ?? 0;
                    $descuento = $this->arraydescuento[$index] ?? 0;
                    
                    $subtotal = $cantidad * $precio;
                    
                    if ($descuento > $subtotal) {
                        $validator->errors()->add(
                            "arraydescuento.{$index}", 
                            "El descuento no puede ser mayor al subtotal del producto"
                        );
                    }
                }
            }
        });
    }
}
