<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\ProductosExport;
use App\Exports\UniversalExport;
use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\TipoUnidad;
use App\Models\Traslado;
use App\Models\User;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class ExportController extends Controller
{
    protected function buildClientesExportConfig(Request $request): array
    {
        $includeContact = filter_var($request->input('includeContact', true), FILTER_VALIDATE_BOOLEAN);
        $includeDocument = filter_var($request->input('includeDocument', true), FILTER_VALIDATE_BOOLEAN);
        $includeAll = filter_var($request->input('includeAll', true), FILTER_VALIDATE_BOOLEAN);

        if ($includeAll) {
            $includeContact = true;
            $includeDocument = true;
        }

        $headings = ['ID', 'Razón Social', 'Grupo', 'Tipo'];
        $mapping = [
            'id',
            'persona.razon_social',
            function ($item) {
                return $item->grupo->nombre ?? 'N/A';
            },
            function ($item) {
                $tipo = $item->persona->tipo_persona ?? null;
                if ($tipo === 'natural') return 'Natural';
                if ($tipo === 'juridica') return 'Jurídica';
                return $tipo ?: 'N/A';
            },
        ];

        if ($includeDocument) {
            $headings[] = 'Tipo Doc.';
            $headings[] = 'Número Doc.';
            $mapping[] = 'persona.documento.tipo_documento';
            $mapping[] = 'persona.numero_documento';
        }

        if ($includeContact) {
            $headings[] = 'Dirección';
            $headings[] = 'Teléfono';
            $mapping[] = 'persona.direccion';
            $mapping[] = 'persona.telefono';
        }

        if ($includeAll) {
            $headings[] = 'Descuento Grupo (%)';
            $mapping[] = function ($item) {
                return $item->grupo->descuento_global ?? 0;
            };
        }

        $headings[] = 'Estado';
        $mapping[] = function ($item) {
            return ($item->persona && $item->persona->estado) ? 'Activo' : 'Inactivo';
        };

        return [
            'model' => Cliente::class,
            'relations' => ['persona.documento', 'grupo'],
            'title' => 'Reporte de Clientes',
            'headings' => $headings,
            'mapping' => $mapping,
        ];
    }

    protected function getModuleConfig($module)
    {
        $configs = [
            'almacenes' => [
                'model' => Almacen::class,
                'title' => 'Reporte de Almacenes',
                'headings' => ['ID', 'Nombre', 'Dirección', 'Descripción', 'Estado'],
                'mapping' => ['id', 'nombre', 'direccion', 'descripcion', function($item) { return $item->estado ? 'Activo' : 'Inactivo'; }]
            ],
            'categorias' => [
                'model' => Categoria::class,
                'title' => 'Reporte de Categorías',
                'headings' => ['ID', 'Nombre', 'Descripción', 'Estado'],
                'mapping' => ['id', 'nombre', 'descripcion', function($item) { return $item->estado ? 'Activo' : 'Inactivo'; }]
            ],
            'clientes' => [
                'model' => Cliente::class,
                'relations' => ['persona.documento', 'grupo'],
                'title' => 'Reporte de Clientes',
                'headings' => ['ID', 'Razón Social', 'Grupo', 'Tipo', 'Tipo Doc.', 'Número Doc.', 'Dirección', 'Teléfono', 'Descuento Grupo (%)', 'Estado'],
                'mapping' => [
                    'id', 
                    'persona.razon_social',
                    function($item) { return $item->grupo->nombre ?? 'N/A'; },
                    function($item) {
                        $tipo = $item->persona->tipo_persona ?? null;
                        if ($tipo === 'natural') return 'Natural';
                        if ($tipo === 'juridica') return 'Jurídica';
                        return $tipo ?: 'N/A';
                    },
                    'persona.documento.tipo_documento', 
                    'persona.numero_documento', 
                    'persona.direccion', 
                    'persona.telefono', 
                    function($item) { return $item->grupo->descuento_global ?? 0; },
                    function($item) { return $item->persona->estado ? 'Activo' : 'Inactivo'; }
                ]
            ],
            'compras' => [
                'model' => Compra::class,
                'relations' => ['proveedor.persona', 'comprobante'],
                'title' => 'Reporte de Compras',
                'headings' => ['ID', 'Fecha', 'Comprobante', 'Número', 'Proveedor', 'Total', 'Estado'],
                'mapping' => [
                    'id', 
                    function($item) { return \Carbon\Carbon::parse($item->fecha_hora)->format('d/m/Y H:i'); },
                    'comprobante.tipo_comprobante', 
                    'numero_comprobante', 
                    'proveedor.persona.razon_social', 
                    'total', 
                    function($item) { return $item->estado ? 'Activo' : 'Anulado'; }
                ]
            ],
            'marcas' => [
                'model' => Marca::class,
                'title' => 'Reporte de Marcas',
                'headings' => ['ID', 'Nombre', 'Descripción', 'Estado'],
                'mapping' => ['id', 'nombre', 'descripcion', function($item) { return $item->estado ? 'Activo' : 'Inactivo'; }]
            ],
            'proveedores' => [
                'model' => Proveedor::class,
                'relations' => ['persona.documento'],
                'title' => 'Reporte de Proveedores',
                'headings' => ['ID', 'Razón Social', 'Tipo Doc.', 'Número Doc.', 'Dirección', 'Teléfono', 'Email', 'Estado'],
                'mapping' => [
                    'id', 
                    'persona.razon_social', 
                    'persona.documento.tipo_documento', 
                    'persona.numero_documento', 
                    'persona.direccion', 
                    'persona.telefono', 
                    'persona.email', 
                    function($item) { return $item->persona->estado ? 'Activo' : 'Inactivo'; }
                ]
            ],
            'roles' => [
                'model' => Role::class, // Spatie
                'title' => 'Reporte de Roles',
                'headings' => ['ID', 'Nombre', 'Guard Name'],
                'mapping' => ['id', 'name', 'guard_name']
            ],
            'tipounidades' => [
                'model' => TipoUnidad::class,
                'title' => 'Reporte de Tipos de Unidad',
                'headings' => ['ID', 'Nombre', 'Abreviatura', 'Estado'],
                'mapping' => ['id', 'nombre', 'abreviatura', function($item) { return $item->estado ? 'Activo' : 'Inactivo'; }]
            ],
            'traslados' => [
                'model' => Traslado::class,
                'relations' => ['origenAlmacen', 'destinoAlmacen', 'user'],
                'title' => 'Reporte de Traslados',
                'headings' => ['ID', 'Fecha', 'Origen', 'Destino', 'Usuario', 'Estado'],
                'mapping' => [
                    'id', 
                    function($item) { return \Carbon\Carbon::parse($item->fecha_hora)->format('d/m/Y H:i'); },
                    'origenAlmacen.nombre', 
                    'destinoAlmacen.nombre', 
                    'user.name', 
                    function($item) { 
                        return match($item->estado) {
                            1 => 'Pendiente',
                            2 => 'Completado',
                            3 => 'Cancelado',
                            default => 'Desconocido'
                        };
                    }
                ]
            ],
            'users' => [
                'model' => User::class,
                'relations' => ['roles'],
                'title' => 'Reporte de Usuarios',
                'headings' => ['ID', 'Nombre', 'Email', 'Roles'],
                'mapping' => [
                    'id', 
                    'name', 
                    'email', 
                    function($item) { return $item->getRoleNames()->implode(', '); }
                ]
            ],
            'ventas' => [
                'model' => Venta::class,
                'relations' => ['cliente.persona', 'comprobante'],
                'title' => 'Reporte de Ventas',
                'headings' => ['ID', 'Fecha', 'Comprobante', 'Numero', 'Cliente', 'Total', 'Estado Pago', 'Estado Entrega', 'Estado'],
                'mapping' => [
                    'id', 
                    function($item) { return \Carbon\Carbon::parse($item->fecha_hora)->format('d/m/Y H:i'); },
                    'comprobante.tipo_comprobante', 
                    'numero_comprobante', 
                    function($item) { return $item->cliente->persona->razon_social ?? 'Cliente General'; },
                    'total', 
                    function($item) { return $item->estado_pago == 1 ? 'Pagado' : 'Pendiente'; },
                    function($item) { return $item->estado_entrega == 1 ? 'Entregado' : 'Pendiente'; },
                    function($item) { return $item->estado ? 'Activo' : 'Anulado'; }
                ]
            ],
        ];

        return $configs[$module] ?? null;
    }

    public function exportExcel(Request $request, $module)
    {
        // 1. Caso especial: Productos (usa su propia clase compleja)
        if ($module === 'productos') {
            return app(ProductoController::class)->exportExcel($request);
        }

        $config = $module === 'clientes'
            ? $this->buildClientesExportConfig($request)
            : $this->getModuleConfig($module);
        if (!$config) {
            return back()->with('error', 'Modulo de exportación no válido.');
        }

        try {
            $ids = $request->input('ids', []);
            $query = $config['model']::query();

            if (!empty($config['relations'])) {
                $query->with($config['relations']);
            }

            if (!empty($ids)) {
                $query->whereIn('id', $ids);
            }

            $collection = $query->get()->map(function($item) use ($config) {
                // Pre-process mapping here if needed, or pass mapping to export class
                return $item; 
            });

            // Usar UniversalExport
            // Necesitamos pasar la colección y la configuración de mapeo
            // El mapeo se ejecuta dentro de UniversalExport::map()
            

            return Excel::download(
                new UniversalExport($collection, $config['headings'], $config['mapping']),
                $module . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
            );

        } catch (\Exception $e) {
            return back()->with('error', 'Error al exportar Excel: ' . $e->getMessage());
        }
    }

    public function exportPdf(Request $request, $module)
    {
        // 1. Caso especial: Productos
        if ($module === 'productos') {
            return app(ProductoController::class)->exportPdf($request);
        }

        $config = $module === 'clientes'
            ? $this->buildClientesExportConfig($request)
            : $this->getModuleConfig($module);
        if (!$config) {
            return back()->with('error', 'Modulo de exportación no válido.');
        }

        try {
            $ids = $request->input('ids', []);
            $query = $config['model']::query();

            if (!empty($config['relations'])) {
                $query->with($config['relations']);
            }

            if (!empty($ids)) {
                $query->whereIn('id', $ids);
            }

            $data = $query->get();

            // Preparar datos para la vista (ejecutar mapping)
            $rows = $data->map(function($item) use ($config) {
                $row = [];
                foreach ($config['mapping'] as $field) {
                    if (is_callable($field)) {
                        $row[] = $field($item);
                    } else {
                        $row[] = data_get($item, $field) ?? 'N/A';
                    }
                }
                return $row;
            });

            $pdf = Pdf::loadview('admin.exports.universal_pdf', [
                'title' => $config['title'],
                'headings' => $config['headings'],
                'rows' => $rows,
                'count' => $data->count(),
                'date' => now()->format('d/m/Y H:i')
            ])->setPaper('a4', 'landscape');

            return $pdf->download($module . '_reporte_' . now()->format('Y-m-d_H-i-s') . '.pdf');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al exportar PDF: ' . $e->getMessage());
        }
    }
}



