<?php

namespace App\Exports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ProductosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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

    public function styles(Worksheet $sheet)
    {
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();

        // 1. Estilo para la cabecera (Navy Blue #2C3E50 y texto blanco negrita)
        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2C3E50'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Ajustar altura de la cabecera
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Estilos para todas las celdas de datos
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2E8F0'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ];
        
        $sheet->getStyle("A2:{$highestColumn}{$highestRow}")->applyFromArray($styleArray);

        // Zebra striping y altura de filas para filas de datos
        for ($row = 2; $row <= $highestRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(22);
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->getFill()->applyFromArray([
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8FAFC'],
                ]);
            }
        }

        // Formatear columnas según el tipo
        $headings = $this->headings();
        foreach ($headings as $index => $heading) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
            if (str_contains(strtolower($heading), 'precio')) {
                // Formato de moneda Bs
                $sheet->getStyle("{$colLetter}2:{$colLetter}{$highestRow}")
                    ->getNumberFormat()
                    ->setFormatCode('[$Bs-40A] #,##0.00'); // Símbolo boliviano
                $sheet->getStyle("{$colLetter}2:{$colLetter}{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            } elseif (str_contains(strtolower($heading), 'stock') || $heading === 'ID') {
                $sheet->getStyle("{$colLetter}2:{$colLetter}{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            } elseif ($heading === 'Estado') {
                $sheet->getStyle("{$colLetter}2:{$colLetter}{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }
    }
}
