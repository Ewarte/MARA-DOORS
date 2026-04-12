<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $routeCompra = $this->route('compra') ?? $this->route('id');
        $compraId = is_object($routeCompra) ? $routeCompra->id : $routeCompra;

        return [
            'numero_comprobante' => 'nullable|string|max:255|unique:compras,numero_comprobante,' . $compraId,
            'total' => 'required|numeric|min:0.01',
            'metodo_pago' => 'nullable|in:efectivo,debito,qr,deposito',
            'monto_pagado' => 'nullable|numeric|min:0|max:999999999',
            'proveedor_id' => 'required|exists:proveedores,id',
            'comprobante_id' => 'required|exists:comprobantes,id',
            'almacen_id' => 'required|exists:almacenes,id',

            'arrayidproducto' => 'required|array|min:1',
            'arrayidproducto.*' => 'required|integer|exists:productos,id',
            'arraycantidad' => 'required|array',
            'arraycantidad.*' => 'required|numeric|min:0.001',
            'arraypreciocompra' => 'required|array',
            'arraypreciocompra.*' => 'required|numeric|min:0.01',
            'arrayprecioventa' => 'required|array',
            'arrayprecioventa.*' => 'required|numeric|min:0.01',
        ];
    }

    public function messages(): array
    {
        return [
            'numero_comprobante.unique' => 'Este número de comprobante ya existe',
            'total.min' => 'El total debe ser mayor a 0',
            'proveedor_id.required' => 'Debe seleccionar un proveedor',
            'arrayidproducto.required' => 'Debe agregar al menos un producto',
            'arraycantidad.*.min' => 'La cantidad mínima es 0.001',
            'arraypreciocompra.*.min' => 'El precio de compra debe ser mayor a 0',
            'arrayprecioventa.*.min' => 'El precio de venta debe ser mayor a 0',
        ];
    }

    protected function prepareForValidation()
    {
        // Normalizar arrays para evitar keys faltantes
        $arrayidproducto = array_values($this->arrayidproducto ?? []);
        $rawCantidades = $this->arraycantidad ?? [];
        $rawPreciosCompra = $this->arraypreciocompra ?? [];
        $rawPreciosVenta = $this->arrayprecioventa ?? [];

        $filteredProductIds = [];
        $cantidades = [];
        $preciosCompra = [];
        $preciosVenta = [];

        foreach ($arrayidproducto as $idx => $pid) {
            if (empty($pid)) continue;

            $filteredProductIds[] = (int)$pid;
            $cantidades[] = floatval($rawCantidades[$idx] ?? 0);
            $preciosCompra[] = floatval($rawPreciosCompra[$idx] ?? 0);
            $preciosVenta[] = floatval($rawPreciosVenta[$idx] ?? 0);
        }

        $this->merge([
            'arrayidproducto' => $filteredProductIds,
            'arraycantidad' => $cantidades,
            'arraypreciocompra' => $preciosCompra,
            'arrayprecioventa' => $preciosVenta,
            'total' => floatval($this->total ?? 0),
            'monto_pagado' => floatval($this->monto_pagado ?? 0),
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validación de seguridad simple sin alterar índices
            $ids = $this->input('arrayidproducto', []);
            $preciosCompra = $this->input('arraypreciocompra', []);
            $preciosVenta = $this->input('arrayprecioventa', []);

            foreach ($ids as $index => $id) {
                $compra = $preciosCompra[$index] ?? 0;
                $venta = $preciosVenta[$index] ?? 0;

                if ($venta < $compra) {
                    $validator->errors()->add(
                        "arrayprecioventa.$index",
                        "El precio de venta no puede ser menor al de compra"
                    );
                }
            }
        });
    }
}
