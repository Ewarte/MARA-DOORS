<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\InventarioAlmacen;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CompraService
{
    /**
     * Procesa la entrada de stock al confirmar una compra.
     */
    public function procesarEntradaStock(Compra $compra)
    {
        return DB::transaction(function () use ($compra) {
            $almacenId = $compra->almacen_id;
            $compra->load('detalles.producto.tipounidad');

            foreach ($compra->detalles as $detalle) {
                if (!$detalle->producto) continue;

                $inventario = InventarioAlmacen::where('producto_id', $detalle->producto_id)
                    ->where('almacen_id', $almacenId)
                    ->lockForUpdate()
                    ->first();

                if (!$inventario) {
                    $inventario = InventarioAlmacen::create([
                        'producto_id' => $detalle->producto_id,
                        'almacen_id' => $almacenId,
                        'stock' => 0
                    ]);
                }

                $detalle->producto->update([
                    'precio_compra' => $detalle->precio_compra,
                    'precio_venta' => $detalle->precio_venta
                ]);

                if ($detalle->producto->tipounidad && !$detalle->producto->tipounidad->maneja_stock) {
                    continue;
                }

                $inventario->stock += $detalle->cantidad;
                $inventario->save();
            }
        });
    }

    /**
     * Revierte el stock (al eliminar o editar compra).
     */
    public function revertirStock(Compra $compra)
    {
        return DB::transaction(function () use ($compra) {
            $almacenId = $compra->almacen_id;
            if (!$almacenId) return;

            $compra->loadMissing('detalles.producto.tipounidad');

            foreach ($compra->detalles as $detalle) {
                if (!$detalle->producto) continue;

                $inventario = InventarioAlmacen::where('producto_id', $detalle->producto_id)
                    ->where('almacen_id', $almacenId)
                    ->lockForUpdate()
                    ->first();

                if ($detalle->producto->tipounidad && !$detalle->producto->tipounidad->maneja_stock) {
                    continue;
                }

                if ($inventario) {
                    $inventario->stock -= $detalle->cantidad;
                    $inventario->save();
                }
            }
        });
    }

    /**
     * Valida de forma inteligente si el cambio de cantidades es posible
     * sin dejar stock negativo, considerando la diferencia entre lo viejo y lo nuevo.
     */
    public function validarEdicionDinamica(Compra $compra, array $nuevosDetalles)
    {
        $compra->load('detalles.producto.tipounidad');
        $almacenId = $compra->almacen_id;

        // Mapear cantidades antiguas para comparar fácilmente
        $cantidadesAntiguas = $compra->detalles->pluck('cantidad', 'producto_id')->toArray();

        foreach ($nuevosDetalles as $detalle) {
            $productoId = $detalle['producto_id'];
            $nuevaCant = (float) $detalle['cantidad'];
            $viejaCant = (float) ($cantidadesAntiguas[$productoId] ?? 0);

            // Si la nueva cantidad es mayor o igual, es un incremento o no hay cambio: SIEMPRE SEGURO
            if ($nuevaCant >= $viejaCant) {
                continue;
            }

            // Si es una reducción, la diferencia debe estar disponible en el stock actual
            $reduccion = $viejaCant - $nuevaCant;

            $inventario = InventarioAlmacen::where('producto_id', $productoId)
                ->where('almacen_id', $almacenId)
                ->first();

            $stockActual = $inventario ? (float)$inventario->stock : 0.0;

            if ($stockActual < $reduccion) {
                $nombre = \App\Models\Producto::find($productoId)->nombre ?? 'Producto';
                throw new Exception("No puedes reducir la cantidad de '{$nombre}' a {$nuevaCant} porque ya has vendido parte. El stock actual ({$stockActual}) no alcanza para retirar las " . number_format($reduccion, 2) . " unidades que intentas quitar.");
            }
        }
    }

    /**
     * Método heredado para cuando se ELIMINA la compra (reversión total)
     */
    public function validarReversion(Compra $compra)
    {
        $compra->load('detalles.producto.tipounidad');
        $almacenId = $compra->almacen_id;

        foreach ($compra->detalles as $detalle) {
            if (!$detalle->producto || ($detalle->producto->tipounidad && !$detalle->producto->tipounidad->maneja_stock)) {
                continue;
            }

            $inventario = InventarioAlmacen::where('producto_id', $detalle->producto_id)
                ->where('almacen_id', $almacenId)
                ->first();

            $stockActual = $inventario ? (float)$inventario->stock : 0.0;

            if ($stockActual < $detalle->cantidad) {
                throw new Exception("No se puede eliminar la compra: El producto '{$detalle->producto->nombre}' ya no tiene stock suficiente (Stock: {$stockActual}, Necesario: {$detalle->cantidad}).");
            }
        }
    }

    /**
     * Centraliza la creación de una compra y sus detalles.
     */
    public function crearCompra(array $data, $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $compra = Compra::create([
                'fecha_hora' => $data['fecha_hora'] ?? now(),
                'numero_comprobante' => $data['numero_comprobante'],
                'total' => 0,
                'metodo_pago' => $data['metodo_pago'] ?? 'efectivo',
                'monto_pagado' => 0,
                'costo_transporte' => $data['costo_transporte'] ?? 0,
                'nota_personal' => $data['nota_personal'] ?? null,
                'estado_pago' => $data['estado_pago'] ?? 'pendiente',
                'estado_entrega' => $data['estado_entrega'] ?? 'por_entregar',
                'comprobante_id' => $data['comprobante_id'],
                'proveedor_id' => $data['proveedor_id'],
                'almacen_id' => $data['almacen_id'],
                'user_id' => $userId,
            ]);

            $total = 0;
            foreach ($data['arrayidproducto'] as $index => $productoId) {
                $cantidad = $data['arraycantidad'][$index];
                $precioCompra = $data['arraypreciocompra'][$index];
                $precioVenta = $data['arrayprecioventa'][$index];

                $compra->detalles()->create([
                    'producto_id' => $productoId,
                    'cantidad' => $cantidad,
                    'precio_compra' => $precioCompra,
                    'precio_venta' => $precioVenta,
                ]);

                $total += ($cantidad * $precioCompra);
            }

            $montoPagado = (float)($data['monto_pagado'] ?? 0);
            if (($data['estado_pago'] ?? '') === 'pagado') {
                $montoPagado = $total;
            }
            $montoPagado = max(0.0, min($montoPagado, (float) $total));
            $estadoPago = $montoPagado >= (float) $total ? 'pagado' : ($montoPagado > 0 ? 'parcial' : 'pendiente');

            $compra->update([
                'total' => $total,
                'monto_pagado' => $montoPagado,
                'estado_pago' => $estadoPago,
            ]);

            $this->procesarEntradaStock($compra);

            return $compra;
        });
    }
}
