<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Cliente;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Traslado;
use App\Models\Almacen;
use App\Models\GrupoCliente;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $periodo = $request->get('periodo', 'anual');

        // 1. Obtener métricas de flujo de caja según período
        $flujoCaja = $this->getCashFlowMetrics($periodo);

        // 2. Obtener Top Productos (Basado en los más vendidos)
        $topProductos = $this->getTopProducts($periodo);

        // 3. Obtener Métricas Generales (Cards superiores)
        $metricasGenerales = $this->getGeneralMetrics();

        // 4. Productos bajo stock
        $productosBajoStock = $this->getLowStockProducts();

        return view('admin.panel.index', [
            // Datos para Gráficos
            'mesesVentas'     => $flujoCaja['ventas'],
            'mesesCompras'    => $flujoCaja['compras'],
            'labelsMeses'     => $flujoCaja['labels'],

            // Totales del período
            'totalVentas'     => $flujoCaja['total_ventas'],
            'totalCompras'    => $flujoCaja['total_compras'],
            'balanceNeto'     => $flujoCaja['balance_neto'],

            // Período actual
            'periodoActual'   => $periodo,

            // Datos para Top Productos
            'nombresProductos'    => $topProductos['nombres'],
            'cantidadesProductos' => $topProductos['cantidades'],

            // Resto de datos
            'metricas'           => $metricasGenerales,
            'productosBajoStock' => $productosBajoStock,
        ]);
    }

    private function getCashFlowMetrics(string $periodo): array
    {
        $data = [
            'ventas'        => [],
            'compras'       => [],
            'labels'        => [],
            'total_ventas'  => 0,
            'total_compras' => 0,
        ];

        if ($periodo === 'semanal') {
            // Últimos 7 días
            for ($i = 6; $i >= 0; $i--) {
                $day = Carbon::now()->subDays($i);

                $venta = (float) Venta::where('estado', 1)
                    ->whereDate('fecha_hora', $day->toDateString())
                    ->sum('total');

                $compra = (float) Compra::where('estado', 1)
                    ->whereDate('fecha_hora', $day->toDateString())
                    ->sum('total');

                $data['ventas'][]  = $venta;
                $data['compras'][] = $compra;
                $data['labels'][]  = ucfirst($day->locale('es')->translatedFormat('D d/M'));
                $data['total_ventas']  += $venta;
                $data['total_compras'] += $compra;
            }

        } elseif ($periodo === 'mensual') {
            // Semanas del mes actual
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth   = Carbon::now()->endOfMonth();
            $week = 1;
            $current = $startOfMonth->copy();

            while ($current->lte($endOfMonth)) {
                $weekStart = $current->copy();
                $weekEnd   = $current->copy()->endOfWeek(Carbon::SUNDAY);
                if ($weekEnd->gt($endOfMonth)) {
                    $weekEnd = $endOfMonth->copy();
                }

                $venta = (float) Venta::where('estado', 1)
                    ->whereBetween('fecha_hora', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                    ->sum('total');

                $compra = (float) Compra::where('estado', 1)
                    ->whereBetween('fecha_hora', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                    ->sum('total');

                $data['ventas'][]  = $venta;
                $data['compras'][] = $compra;
                $data['labels'][]  = 'Sem ' . $week;
                $data['total_ventas']  += $venta;
                $data['total_compras'] += $compra;

                $current = $weekEnd->copy()->addDay()->startOfDay();
                $week++;
            }

        } else {
            // Anual: los 12 meses del año actual
            $currentYear = Carbon::now()->year;

            $ventasPorMes = Venta::whereYear('fecha_hora', $currentYear)
                ->where('estado', 1)
                ->select(
                    DB::raw('MONTH(fecha_hora) as mes'),
                    DB::raw('SUM(total) as total')
                )
                ->groupBy('mes')
                ->pluck('total', 'mes')
                ->toArray();

            $comprasPorMes = Compra::whereYear('fecha_hora', $currentYear)
                ->where('estado', 1)
                ->select(
                    DB::raw('MONTH(fecha_hora) as mes'),
                    DB::raw('SUM(total) as total')
                )
                ->groupBy('mes')
                ->pluck('total', 'mes')
                ->toArray();

            for ($i = 1; $i <= 12; $i++) {
                $venta  = (float)($ventasPorMes[$i]  ?? 0);
                $compra = (float)($comprasPorMes[$i] ?? 0);

                $data['ventas'][]  = $venta;
                $data['compras'][] = $compra;
                $data['labels'][]  = ucfirst(Carbon::create()->month($i)->locale('es')->translatedFormat('F'));
                $data['total_ventas']  += $venta;
                $data['total_compras'] += $compra;
            }
        }

        $data['balance_neto'] = $data['total_ventas'] - $data['total_compras'];

        return $data;
    }

    private function getTopProducts(string $periodo): array
    {
        $query = DB::table('detalle_ventas')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->where('ventas.estado', 1);

        if ($periodo === 'semanal') {
            $query->where('ventas.fecha_hora', '>=', Carbon::now()->subDays(7));
        } elseif ($periodo === 'mensual') {
            $query->whereYear('ventas.fecha_hora', Carbon::now()->year)
                  ->whereMonth('ventas.fecha_hora', Carbon::now()->month);
        } else {
            $query->whereYear('ventas.fecha_hora', Carbon::now()->year);
        }

        $productos = $query
            ->select('productos.nombre', DB::raw('SUM(detalle_ventas.cantidad) as total_vendido'))
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('total_vendido')
            ->take(5)
            ->get();

        return [
            'nombres'    => $productos->pluck('nombre')->toArray(),
            'cantidades' => $productos->pluck('total_vendido')->toArray(),
        ];
    }

    private function getLowStockProducts()
    {
        return Producto::select(
                'productos.nombre',
                'productos.precio_compra',
                'productos.precio_venta',
                DB::raw('SUM(inventario_almacenes.stock) as total_stock')
            )
            ->leftJoin('inventario_almacenes', 'productos.id', '=', 'inventario_almacenes.producto_id')
            ->groupBy('productos.id', 'productos.nombre', 'productos.precio_compra', 'productos.precio_venta')
            ->havingRaw('COALESCE(SUM(inventario_almacenes.stock), 0) < 10')
            ->orderBy('total_stock', 'asc')
            ->take(10)
            ->get();
    }

    private function getGeneralMetrics(): array
    {
        $today = Carbon::today();

        return [
            'totalAlmacenes'      => Almacen::count(),
            'totalCategorias'     => Categoria::count(),
            'totalClientes'       => Cliente::count(),
            'totalCompras'        => Compra::where('estado', 1)->count(),
            'totalVentas'         => Venta::where('estado', 1)->count(),
            'totalGrupoClientes'  => GrupoCliente::count(),
            'totalMarcas'         => Marca::count(),
            'totalProductos'      => Producto::where('estado', 1)->count(),
            'totalProveedores'    => Proveedor::count(),
            'totalTraslados'      => Traslado::count(),
            'totalUsuarios'       => User::count(),

            // METRICAS DEL DIA
            'ventasHoy' => Venta::where('estado', 1)
                ->whereDate('fecha_hora', $today)
                ->sum('total'),

            'comprasHoy' => Compra::where('estado', 1)
                ->whereDate('fecha_hora', $today)
                ->sum('total'),
        ];
    }
}
