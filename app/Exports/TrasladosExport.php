<?php

namespace App\Exports;

use App\Models\Traslado;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TrasladosExport implements FromCollection, WithHeadings, WithMapping
{
    protected $traslados;
    protected $includeDetalles;
    protected $includeCosto;
    protected $includeUsuario;

    public function __construct($traslados, $includeDetalles = true, $includeCosto = true, $includeUsuario = true)
    {
        $this->traslados = $traslados;
        $this->includeDetalles = $includeDetalles;
        $this->includeCosto = $includeCosto;
        $this->includeUsuario = $includeUsuario;
    }

    public function collection()
    {
        return $this->traslados;
    }

    public function headings(): array
    {
        $headers = [
            'ID',
            'Fecha',
            'Origen',
            'Destino',
        ];

        if ($this->includeUsuario) {
            $headers[] = 'Usuario';
        }

        if ($this->includeCosto) {
            $headers[] = 'Costo Envío';
        }

        $headers[] = 'Estado';

        if ($this->includeDetalles) {
            $headers[] = 'Productos';
        }

        return $headers;
    }

    public function map($traslado): array
    {
        $estadoMap = [1 => 'Pendiente', 2 => 'Completado', 3 => 'Cancelado'];

        $row = [
            $traslado->id,
            $traslado->fecha_hora->format('d/m/Y H:i'),
            $traslado->origenAlmacen?->nombre ?? 'N/A',
            $traslado->destinoAlmacen?->nombre ?? 'N/A',
        ];

        if ($this->includeUsuario) {
            $row[] = $traslado->user?->name ?? 'N/A';
        }

        if ($this->includeCosto) {
            $row[] = number_format($traslado->costo_envio, 2, '.', '');
        }

        $row[] = $estadoMap[$traslado->estado] ?? 'Desconocido';

        if ($this->includeDetalles) {
            $productos = $traslado->detalles->map(function ($d) {
                return ($d->producto?->nombre ?? 'Producto eliminado') . ' (x' . $d->cantidad . ')';
            })->implode(', ');
            $row[] = $productos;
        }

        return $row;
    }
}

