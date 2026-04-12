<?php

namespace App\Exports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductosExport implements FromCollection, WithHeadings, WithMapping
{
    protected $productos;
    protected $includePrices;
    protected $includeStock;
    protected $includeAllDetails;
    protected $almacenes;

    public function __construct($productos, $includePrices = true, $includeStock = true, $includeAllDetails = true, $almacenes = [])
    {
        $this->productos = $productos;
        $this->includePrices = $includePrices;
        $this->includeStock = $includeStock;
        $this->includeAllDetails = $includeAllDetails;
        $this->almacenes = $almacenes ?: [];
    }

    public function collection()
    {
        return $this->productos;
    }

    public function headings(): array
    {
        $headers = [
            'ID',
            'Código',
            'Nombre',
        ];

        if ($this->includeAllDetails) {
            $headers[] = 'Categoría';
            $headers[] = 'Marca';
            $headers[] = 'Unidad';
            $headers[] = 'Descripcion';
        }

        if ($this->includePrices) {
            $headers[] = 'Precio Compra';
            $headers[] = 'Precio Venta';
        }

        if ($this->includeStock) {
            $headers[] = 'Stock Total';

            foreach ($this->almacenes as $almacen) {
                $headers[] = 'Stock - ' . ($almacen->nombre ?? ('Almacen ' . $almacen->id));
            }
        }

        $headers[] = 'Estado';

        return $headers;
    }

    public function map($producto): array
    {
        $row = [
            $producto->id,
            $producto->codigo,
            $producto->nombre,
        ];

        if ($this->includeAllDetails) {
            $row[] = $producto->categoria ? $producto->categoria->nombre : 'N/A';
            $row[] = $producto->marca ? $producto->marca->nombre : 'N/A';
            $row[] = $producto->tipounidad ? $producto->tipounidad->nombre : 'N/A';
            $row[] = $producto->descripcion ?? '';
        }

        if ($this->includePrices) {
            $row[] = $producto->precio_compra;
            $row[] = $producto->precio_venta;
        }

        if ($this->includeStock) {
            $row[] = $producto->stock_total ?? 0;

            $inventarios = $producto->inventarios ?? collect();
            $invByAlmacen = method_exists($inventarios, 'keyBy') ? $inventarios->keyBy('almacen_id') : [];

            foreach ($this->almacenes as $almacen) {
                $inv = $invByAlmacen[$almacen->id] ?? null;
                $row[] = $inv ? ($inv->stock ?? 0) : 0;
            }
        }

        $row[] = $producto->estado ? 'Activo' : 'Inactivo';

        return $row;
    }
}
