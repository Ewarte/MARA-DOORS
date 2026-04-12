<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompraRequest extends FormRequest
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
            'numero_comprobante' => 'nullable|string|max:255|unique:compras,numero_comprobante',
            'total' => 'required|numeric|min:0.01',
            'metodo_pago' => 'nullable|in:efectivo,debito,qr,deposito',
            'monto_pagado' => 'nullable|numeric|min:0|max:999999999',
            'proveedor_id' => 'required|exists:proveedores,id',
            'user_id' => 'required|exists:users,id',
            'comprobante_id' => 'required|exists:comprobantes,id',
            'almacen_id' => 'required|exists:almacenes,id',
            
            // Arrays de productos
            'arrayidproducto' => 'required|array|min:1',
            'arrayidproducto.*' => 'required|integer|exists:productos,id',
            'arraycantidad' => 'required|array|min:1|size:' . count($this->arrayidproducto ?? []),
            'arraycantidad.*' => 'required|numeric|min:0.001|max:999999',
            'arraypreciocompra' => 'required|array|min:1|size:' . count($this->arrayidproducto ?? []),
            'arraypreciocompra.*' => 'required|numeric|min:0.01|max:999999',
            'arrayprecioventa' => 'required|array|min:1|size:' . count($this->arrayidproducto ?? []),
            'arrayprecioventa.*' => 'required|numeric|min:0.01|max:999999',
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
            'proveedor_id.required' => 'Debe seleccionar un proveedor',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe',
            'almacen_id.required' => 'Debe seleccionar una sucursal/almacén',
            'almacen_id.exists' => 'La sucursal seleccionada no existe',
            'comprobante_id.required' => 'Debe seleccionar un tipo de comprobante',
            
            // Productos
            'arrayidproducto.required' => 'Debe agregar al menos un producto a la compra',
            'arrayidproducto.min' => 'Debe agregar al menos un producto',
            'arrayidproducto.*.exists' => 'Uno de los productos seleccionados no existe',
            
            // Cantidades
            'arraycantidad.required' => 'Las cantidades son obligatorias',
            'arraycantidad.size' => 'El número de cantidades no coincide con los productos',
            'arraycantidad.*.min' => 'La cantidad mínima es 0.001',
            'arraycantidad.*.max' => 'La cantidad es demasiado grande',
            
            // Precios de compra
            'arraypreciocompra.required' => 'Los precios de compra son obligatorios',
            'arraypreciocompra.size' => 'El número de precios de compra no coincide con los productos',
            'arraypreciocompra.*.min' => 'El precio de compra mínimo es 0.01',
            'arraypreciocompra.*.max' => 'El precio de compra es demasiado grande',
            
            // Precios de venta
            'arrayprecioventa.required' => 'Los precios de venta son obligatorios',
            'arrayprecioventa.size' => 'El número de precios de venta no coincide con los productos',
            'arrayprecioventa.*.min' => 'El precio de venta mínimo es 0.01',
            'arrayprecioventa.*.max' => 'El precio de venta es demasiado grande',
        ];
    }

    protected function prepareForValidation()
    {
        // Convertir valores a números y limpiar
        $this->merge([
            'arraycantidad' => array_map(fn($v) => floatval($v ?? 0), $this->arraycantidad ?? []),
            'arraypreciocompra' => array_map(fn($v) => floatval($v ?? 0), $this->arraypreciocompra ?? []),
            'arrayprecioventa' => array_map(fn($v) => floatval($v ?? 0), $this->arrayprecioventa ?? []),
            'total' => floatval($this->total ?? 0),
            'monto_pagado' => floatval($this->monto_pagado ?? 0),
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar que el precio de venta sea mayor al precio de compra
            if ($this->arrayidproducto) {
                foreach ($this->arrayidproducto as $index => $productoId) {
                    $precioCompra = $this->arraypreciocompra[$index] ?? 0;
                    $precioVenta = $this->arrayprecioventa[$index] ?? 0;
                    
                    if ($precioVenta < $precioCompra) {
                        $validator->errors()->add(
                            "arrayprecioventa.{$index}", 
                            "El precio de venta debe ser mayor o igual al precio de compra"
                        );
                    }
                }
            }
        });
    }
}
