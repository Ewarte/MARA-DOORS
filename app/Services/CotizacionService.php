<?php

namespace App\Services;

use App\Models\Cotizacion;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\InventarioAlmacen;
use Illuminate\Support\Facades\DB;
use Exception;

class CotizacionService
{
    public function crearCotizacion(array $data, $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $cotizacion = Cotizacion::create([
                'fecha_hora' => $data['fecha_hora'] ?? now(),
                'numero_cotizacion' => $data['numero_cotizacion'],
                'total' => 0,
                'estado' => 'pendiente',
                'cliente_id' => $data['cliente_id'] ?? null,
                'proveedor_id' => $data['proveedor_id'] ?? null,
                'almacen_id' => $data['almacen_id'],
                'user_id' => $userId,
                'vencimiento' => $data['vencimiento'] ?? null,
                'nota_personal' => $data['nota_personal'] ?? null,
                'nota_cliente' => $data['nota_cliente'] ?? null,
            ]);

            $total = 0;
            if (isset($data['arrayidproducto'])) {
                foreach ($data['arrayidproducto'] as $index => $productoId) {
                    $cantidad = floatval($data['arraycantidad'][$index] ?? 0);
                    $precioUnitario = floatval($data['arraypreciounitario'][$index] ?? 0);
                    $descuento = floatval($data['arraydescuento'][$index] ?? 0);

                    if (empty($productoId) || $cantidad <= 0) continue;

                    $cotizacion->detalles()->create([
                        'producto_id' => $productoId,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precioUnitario,
                        'descuento' => $descuento
                    ]);

                    $total += ($cantidad * $precioUnitario) - $descuento;
                }
            }

            $cotizacion->update(['total' => $total]);

            return $cotizacion;
        });
    }

    public function actualizarCotizacion(Cotizacion $cotizacion, array $data)
    {
        return DB::transaction(function () use ($cotizacion, $data) {
            $cotizacion->update([
                'fecha_hora' => $data['fecha_hora'] ?? $cotizacion->fecha_hora,
                'numero_cotizacion' => $data['numero_cotizacion'] ?? $cotizacion->numero_cotizacion,
                'cliente_id' => $data['cliente_id'] ?? null,
                'proveedor_id' => $data['proveedor_id'] ?? null,
                'almacen_id' => $data['almacen_id'],
                'vencimiento' => $data['vencimiento'] ?? null,
                'nota_personal' => $data['nota_personal'] ?? null,
                'nota_cliente' => $data['nota_cliente'] ?? null,
            ]);

            $cotizacion->detalles()->delete();

            $total = 0;
            if (isset($data['arrayidproducto'])) {
                foreach ($data['arrayidproducto'] as $index => $productoId) {
                    $cantidad = floatval($data['arraycantidad'][$index] ?? 0);
                    $precioUnitario = floatval($data['arraypreciounitario'][$index] ?? 0);
                    $descuento = floatval($data['arraydescuento'][$index] ?? 0);

                    if (empty($productoId) || $cantidad <= 0) continue;

                    $cotizacion->detalles()->create([
                        'producto_id' => $productoId,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precioUnitario,
                        'descuento' => $descuento
                    ]);

                    $total += ($cantidad * $precioUnitario) - $descuento;
                }
            }

            $cotizacion->update(['total' => $total]);

            return $cotizacion;
        });
    }

    public function convertirAVenta(Cotizacion $cotizacion, array $extraData = [])
    {
        return DB::transaction(function () use ($cotizacion, $extraData) {
            if ($cotizacion->estado !== 'pendiente') {
                throw new Exception("Esta cotización ya fue procesada o anulada.");
            }

            // Mapear datos para VentaService
            $dataVenta = [
                'fecha_hora' => now(),
                'numero_comprobante' => $extraData['numero_comprobante'] ?? $this->generarNumeroComprobante(Venta::class),
                'cliente_id' => $cotizacion->cliente_id ?? $extraData['cliente_id'] ?? null,
                'almacen_id' => $cotizacion->almacen_id,
                'comprobante_id' => $extraData['comprobante_id'] ?? 1, // Default or selected
                'metodo_pago' => $extraData['metodo_pago'] ?? 'efectivo',
                'estado_pago' => $extraData['estado_pago'] ?? 'pendiente',
                'monto_pagado' => $extraData['monto_pagado'] ?? 0,
                'nota_personal' => $cotizacion->nota_personal,
                'nota_cliente' => $cotizacion->nota_cliente,
                'arrayidproducto' => $cotizacion->detalles->pluck('producto_id')->toArray(),
                'arraycantidad' => $cotizacion->detalles->pluck('cantidad')->toArray(),
                'arrayprecioventa' => $cotizacion->detalles->pluck('precio_unitario')->toArray(),
                'arraydescuento' => $cotizacion->detalles->pluck('descuento')->toArray(),
            ];

            if (empty($dataVenta['cliente_id'])) {
                throw new Exception("Debe asignar un cliente para convertir a venta.");
            }

            $ventaService = app(VentaService::class);
            $venta = $ventaService->crearVenta($dataVenta, auth()->id());

            $cotizacion->update(['estado' => 'venta_realizada']);

            return $venta;
        });
    }

    public function convertirACompra(Cotizacion $cotizacion, array $extraData = [])
    {
        return DB::transaction(function () use ($cotizacion, $extraData) {
            if ($cotizacion->estado !== 'pendiente') {
                throw new Exception("Esta cotización ya fue procesada o anulada.");
            }

            // Mapear datos para CompraService
            $dataCompra = [
                'fecha_hora' => now(),
                'numero_comprobante' => $extraData['numero_comprobante'] ?? $this->generarNumeroComprobante(Compra::class),
                'proveedor_id' => $cotizacion->proveedor_id ?? $extraData['proveedor_id'] ?? null,
                'almacen_id' => $cotizacion->almacen_id,
                'comprobante_id' => $extraData['comprobante_id'] ?? 1,
                'metodo_pago' => $extraData['metodo_pago'] ?? 'efectivo',
                'estado_pago' => $extraData['estado_pago'] ?? 'pendiente',
                'monto_pagado' => $extraData['monto_pagado'] ?? 0,
                'nota_personal' => $cotizacion->nota_personal,
                'arrayidproducto' => $cotizacion->detalles->pluck('producto_id')->toArray(),
                'arraycantidad' => $cotizacion->detalles->pluck('cantidad')->toArray(),
                'arraypreciocompra' => $cotizacion->detalles->pluck('precio_unitario')->toArray(),
                'arrayprecioventa' => $cotizacion->detalles->map(function($d) {
                    return $d->producto->precio_venta;
                })->toArray(),
            ];

            if (empty($dataCompra['proveedor_id'])) {
                throw new Exception("Debe asignar un proveedor para convertir a compra.");
            }

            $compraService = app(CompraService::class);
            $compra = $compraService->crearCompra($dataCompra, auth()->id());

            $cotizacion->update(['estado' => 'compra_realizada']);

            return $compra;
        });
    }

    private function generarNumeroComprobante($modelClass)
    {
        $last = $modelClass::latest()->first();
        $nextNumber = $last ? (int)$last->numero_comprobante + 1 : 1;
        return str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
    }
}
