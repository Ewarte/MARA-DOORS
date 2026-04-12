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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 1. Obtener métricas de flujo de caja (Ventas vs Compras)
        $flujoCaja = $this->getCashFlowMetrics();

        // 2. Obtener Top Productos (Basado en los más vendidos, no en stock)
        $topProductos = $this->getTopProducts();

        // 3. Obtener Métricas Generales (Cards superiores)
        $metricasGenerales = $this->getGeneralMetrics();

        // 4. Productos bajo stock
        $productosBajoStock = $this->getLowStockProducts();

        return view('admin.panel.index', [
            // Datos para Gráficos
            'mesesVentas'     => $flujoCaja['ventas'],
            'mesesCompras'    => $flujoCaja['compras'],
            'labelsMeses'     => $flujoCaja['labels'],

            // Totales Anuales
            'totalVentas'     => $flujoCaja['total_anual_ventas'],
            'totalCompras'    => $flujoCaja['total_anual_compras'],
            'balanceNeto'     => $flujoCaja['balance_neto'],

            // Datos para Top Productos
            'nombresProductos'    => $topProductos['nombres'],
            'cantidadesProductos' => $topProductos['cantidades'],

            // Resto de datos
            'metricas'           => $metricasGenerales,
            'productosBajoStock' => $productosBajoStock,
        ]);
    }

    private function getCashFlowMetrics(): array
    {
        $currentYear = Carbon::now()->year;

        // Ventas por mes (Usando campo total de la tabla ventas para mayor precision)
        $ventasPorMes = Venta::whereYear('fecha_hora', $currentYear)
            ->where('estado', 1)
            ->select(
                DB::raw('MONTH(fecha_hora) as mes'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        // Compras por mes
        $comprasPorMes = Compra::whereYear('fecha_hora', $currentYear)
            ->where('estado', 1)
            ->select(
                DB::raw('MONTH(fecha_hora) as mes'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        $data = [
            'ventas' => [],
            'compras' => [],
            'labels' => [],
            'total_anual_ventas' => 0,
            'total_anual_compras' => 0,
        ];

        for ($i = 1; $i <= 12; $i++) {
            $ventaMes  = (float)($ventasPorMes[$i] ?? 0);
            $compraMes = (float)($comprasPorMes[$i] ?? 0);

            $data['ventas'][] = $ventaMes;
            $data['compras'][] = $compraMes;
            $data['labels'][] = ucfirst(Carbon::create()->month($i)->locale('es')->translatedFormat('F'));

            $data['total_anual_ventas'] += $ventaMes;
            $data['total_anual_compras'] += $compraMes;
        }

        $data['balance_neto'] = $data['total_anual_ventas'] - $data['total_anual_compras'];

        return $data;
    }

    private function getTopProducts(): array
    {
        // Productos mas vendidos (Top 5)
        $productos = DB::table('detalle_ventas')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->where('ventas.estado', 1)
            ->select('productos.nombre', DB::raw('SUM(detalle_ventas.cantidad) as total_vendido'))
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('total_vendido')
            ->take(5)
            ->get();

        return [
            'nombres' => $productos->pluck('nombre')->toArray(),
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

            // METRICAS DEL DIA (Basadas en el campo 'total' ya calculado)
            'ventasHoy' => Venta::where('estado', 1)
                ->whereDate('fecha_hora', $today)
                ->sum('total'),

            'comprasHoy' => Compra::where('estado', 1)
                ->whereDate('fecha_hora', $today)
                ->sum('total'),
        ];
    }
}



