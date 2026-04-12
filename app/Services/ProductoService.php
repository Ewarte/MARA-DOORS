<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\InventarioAlmacen;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductoService
{
    /**
     * Ajustar stock de un producto en un almacén específico
     * @param string $tipo 'sumar', 'restar', 'fijar'
     */
    public function ajustarStock(
    int $productoId,
    int $almacenId,
    float $cantidad,
    int $userId,
    string $tipo = 'fijar',
    string $motivo = 'Ajuste manual'
) {
    return DB::transaction(function () use ($productoId, $almacenId, $cantidad, $userId, $tipo, $motivo) {

        $inventario = InventarioAlmacen::where('producto_id', $productoId)
            ->where('almacen_id', $almacenId)
            ->lockForUpdate()
            ->first();

        $cantidadAnterior = $inventario ? $inventario->stock : 0;
        $nuevaCantidad = $cantidadAnterior;

        switch ($tipo) {
            case 'sumar':
                $nuevaCantidad = $cantidadAnterior + $cantidad;
                break;

            case 'restar':
                    if ($cantidad > $cantidadAnterior) {
                        throw ValidationException::withMessages([
                            'cantidad' => "No se puede restar {$cantidad}. Stock disponible: {$cantidadAnterior}."
                        ]);
                    }
                    $nuevaCantidad = $cantidadAnterior - $cantidad;
                    break;

            case 'fijar':
                if ($cantidad < 0) {
                    throw new \Exception("El stock no puede ser negativo.");
                }
                $nuevaCantidad = $cantidad;
                break;
        }

        // 🔒 SEGURIDAD EXTRA (por si acaso)
        if ($nuevaCantidad < 0) {
            throw new \Exception("El stock resultante no puede ser negativo.");
        }

        $inventario = InventarioAlmacen::updateOrCreate(
            [
                'producto_id' => $productoId,
                'almacen_id' => $almacenId
            ],
            [
                'stock' => $nuevaCantidad
            ]
        );

        \App\Models\AjusteStock::create([
            'producto_id'       => $productoId,
            'almacen_id'        => $almacenId,
            'user_id'           => $userId,
            'cantidad_anterior' => $cantidadAnterior,
            'cantidad_nueva'    => $nuevaCantidad,
            'motivo'            => $motivo
        ]);

        return $inventario;
    });
}


    /**
     * Eliminar producto completamente
     */
    public function eliminarProducto(Producto $producto)
    {
        return DB::transaction(function () use ($producto) {
            // Eliminar inventarios relacionados
            $producto->inventarios()->delete();

            // Eliminar producto
            return $producto->delete();
        });
    }
    // Otros métodos relacionados con productos pueden ir aquí
    /**
     * metodo para evitar borrar productos que estan en uso de una orden de compra o venta
     * metodo para
     */
}
