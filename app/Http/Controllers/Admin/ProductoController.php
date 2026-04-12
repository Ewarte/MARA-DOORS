<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\InventarioAlmacen;
use App\Models\Marca;
use App\Models\TipoUnidad;
use App\Models\Producto;
use App\Services\ProductoService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductosExport;
use Barryvdh\DomPDF\Facade\Pdf;

class ProductoController extends Controller
{
    protected $productoService;

    function __construct(ProductoService $productoService)
    {
        $this->productoService = $productoService;
        $this->middleware('permission:ver-producto', ['only' => ['index']]);
        $this->middleware('permission:crear-producto', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-producto', ['only' => ['edit', 'update']]); 
        $this->middleware('permission:eliminar-producto', ['only' => ['destroy']]);
        $this->middleware('permission:update-estado-producto', ['only' => ['updateEstado']]);
        $this->middleware('permission:ajustar-stock', ['only' => ['ajusteCantidad', 'updateCantidad']]);
    }
    
    public function index(Request $request)
    {
        $busqueda = $request->get('busqueda');
        $perPage  = $request->get('per_page', 10);
        $sort      = $request->get('sort', 'nombre');
        $direction = $request->get('direction', 'asc');

        // Validar per_page y direction
        if (!in_array($perPage, [5, 10, 15, 20, 25])) $perPage = 10;
        if (!in_array($direction, ['asc', 'desc'])) $direction = 'asc';

        $query = Producto::with([
            'marca',
            'categoria',
            'tipounidad',
            'inventarios.almacen'
        ])->withSum('inventarios as stock_total', 'stock');

        // Búsqueda
        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('codigo', 'like', "%{$busqueda}%")
                  ->orWhere('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('descripcion', 'like', "%{$busqueda}%");
            });
        }

        // Ordenamiento
        switch ($sort) {
            case 'categoria':
                $query->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
                      ->select('productos.*') // Evitar colisión de IDs
                      ->orderBy('categorias.nombre', $direction);
                break;
            case 'stock_total':
                $query->orderBy('stock_total', $direction);
                break;
            case 'precio_venta':
            case 'nombre':
            case 'estado':
                $query->orderBy($sort, $direction);
                break;
            default:
                $query->latest();
                break;
        }

        $productos = $query->paginate($perPage);

        // Estadísticas para el footer (Totales globales de toda la tabla productos)
        $totalStockGlobal = DB::table('inventario_almacenes')->sum('stock');
        $productosActivos = Producto::where('estado', 1)->count();
        
        // Conteo de bajo stock global (suma de inventarios por producto <= 10)
        $bajoStockCount = Producto::join('inventario_almacenes', 'productos.id', '=', 'inventario_almacenes.producto_id')
            ->select('productos.id')
            ->groupBy('productos.id')
            ->havingRaw('SUM(inventario_almacenes.stock) <= 10')
            ->get()
            ->count();

        $almacenes = Almacen::where('estado', true)->get();

        if ($request->ajax()) {
            return view('admin.producto.index', compact(
                'productos', 'busqueda', 'perPage', 'almacenes', 
                'sort', 'direction', 'totalStockGlobal', 'productosActivos', 'bajoStockCount'
            ));
        }

        return view('admin.producto.index', compact(
            'productos', 'busqueda', 'perPage', 'almacenes', 
            'sort', 'direction', 'totalStockGlobal', 'productosActivos', 'bajoStockCount'
        ));
    }
 
    public function create()
    {
        $marcas = Marca::all();
        $tipounidades = TipoUnidad::all();
        $categorias = Categoria::all();

        return view('admin.producto.create', compact('marcas', 'tipounidades', 'categorias'));
    }

    public function store(StoreProductoRequest $request)
    {
        try {
            DB::beginTransaction();

            // 1ï¸âƒ£ Crear producto
            $producto = Producto::create([
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'precio_compra' => $request->precio_compra,
                'precio_venta' => $request->precio_venta,
                'marca_id' => $request->marca_id,
                'tipounidad_id' => $request->tipounidad_id,
                'categoria_id' => $request->categoria_id,
                'estado' => true,
            ]);

            // 2ï¸âƒ£ Obtener TODOS los almacenes activos
            $almacenes = Almacen::where('estado', true)->get();

            // 3ï¸âƒ£ Crear inventario con stock = 0
            foreach ($almacenes as $almacen) {
                $producto->inventarios()->create([
                    'almacen_id' => $almacen->id,
                    'stock' => 0,
                ]);
            }

            DB::commit();

            return redirect()->route('productos.index')
                ->with('success', 'Producto creado y agregado a todos los almacenes.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Error al crear producto: ' . $e->getMessage());
        }
    }

    public function edit(Producto $producto)
    {
        $marcas = Marca::all();
        $tipounidades = TipoUnidad::all();
        $categorias = Categoria::all();

        return view('admin.producto.edit', compact(
            'producto',
            'marcas',
            'tipounidades',
            'categorias'
        ));
    }

    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        try {
            $producto->update([
                'nombre'         => $request->nombre,
                'descripcion'    => $request->descripcion,
                'precio_compra'  => $request->precio_compra,
                'precio_venta'   => $request->precio_venta,
                'marca_id'       => $request->marca_id,
                'tipounidad_id'  => $request->tipounidad_id,
                'categoria_id'   => $request->categoria_id,
            ]);

            return redirect()->route('productos.index')
                ->with('success', 'Producto actualizado correctamente.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }
 
    public function updateEstado(Producto $producto)
    {
        $producto->estado = !$producto->estado;
        $producto->save();

        return redirect()->route('productos.index')
            ->with('success', $producto->estado
                ? 'Producto activado correctamente.'
                : 'Producto desactivado correctamente.');
    }


    public function destroy(Request $request, Producto $producto)
    {
        $accion = $request->get('accion', 'eliminar');

        try {
            if ($accion === 'inactivar') {
                $producto->estado = 0;
                $producto->save();
                return redirect()->route('productos.index')
                    ->with('success', 'Producto puesto en inactivo correctamente.');
            }

            if ($accion === 'activar') {
                $producto->estado = 1;
                $producto->save();
                return redirect()->route('productos.index')
                    ->with('success', 'Producto activado correctamente.');
            }

            $this->productoService->eliminarProducto($producto);
            return redirect()->route('productos.index')
                ->with('success', 'Producto eliminado completamente del sistema.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar la solicitud: ' . $e->getMessage());
        }
    }

    public function ajusteCantidad(Producto $producto)
    {
        $almacenes = Almacen::where('estado', true)->get();
        return view('admin.producto.ajuste_cantidad', compact('producto', 'almacenes'));
    }

    public function updateCantidad(Request $request, Producto $producto)
    {
        $request->validate([
            'almacen_id' => 'required|exists:almacenes,id',
            'cantidad' => 'required|numeric|min:0',
            'tipo_ajuste' => 'required|in:sumar,restar,fijar'
        ]);

        try {
            $this->productoService->ajustarStock(
                $producto->id, 
                $request->almacen_id, 
                $request->cantidad, 
                auth()->id(),
                $request->tipo_ajuste,
                $request->motivo ?? 'Ajuste manual'
            );
            return redirect()->route('productos.index')
                ->with('success', 'Stock ajustado correctamente.');
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Error al ajustar stock: ' . $e->getMessage());
        }
    }
    public function historialAjustes(Request $request)
    {
        $busqueda = $request->get('busqueda');
        $perPage  = $request->get('per_page', 10);
        $sort      = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        if (!in_array($perPage, [5, 10, 15, 20, 25])) $perPage = 10;
        if (!in_array($direction, ['asc', 'desc'])) $direction = 'desc';

        $query = \App\Models\AjusteStock::with(['producto', 'almacen', 'user']);

        // Búsqueda por producto
        if ($busqueda) {
            $query->whereHas('producto', function($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('codigo', 'like', "%{$busqueda}%");
            });
        }

        // Ordenamiento
        switch ($sort) {
            case 'producto':
                $query->join('productos', 'ajuste_stocks.producto_id', '=', 'productos.id')
                      ->select('ajuste_stocks.*')
                      ->orderBy('productos.nombre', $direction);
                break;
            case 'usuario':
                $query->join('users', 'ajuste_stocks.user_id', '=', 'users.id')
                      ->select('ajuste_stocks.*')
                      ->orderBy('users.name', $direction);
                break;
            case 'almacen':
                $query->join('almacenes', 'ajuste_stocks.almacen_id', '=', 'almacenes.id')
                      ->select('ajuste_stocks.*')
                      ->orderBy('almacenes.nombre', $direction);
                break;
            default:
                $query->orderBy($sort, $direction);
                break;
        }

        $ajustes = $query->paginate($perPage);

        // Estadísticas para el footer
        $totalAjustes = \App\Models\AjusteStock::count();
        $ajustesPositivos = \App\Models\AjusteStock::whereRaw('cantidad_nueva > cantidad_anterior')->count();
        $ajustesNegativos = \App\Models\AjusteStock::whereRaw('cantidad_nueva < cantidad_anterior')->count();

        if ($request->ajax()) {
            return view('admin.producto.historial_ajustes', compact(
                'ajustes', 'busqueda', 'perPage', 'sort', 'direction', 
                'totalAjustes', 'ajustesPositivos', 'ajustesNegativos'
            ));
        }

        return view('admin.producto.historial_ajustes', compact(
            'ajustes', 'busqueda', 'perPage', 'sort', 'direction', 
            'totalAjustes', 'ajustesPositivos', 'ajustesNegativos'
        ));
    }

    public function createAjuste()
    {
        $productos = Producto::where('estado', true)->get();
        $almacenes = Almacen::where('estado', true)->get();
        return view('admin.producto.create_ajuste', compact('productos', 'almacenes'));
    }
    public function storeAjuste(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'almacen_id' => 'required|exists:almacenes,id',
            'cantidad' => 'required|numeric|min:0',
            'tipo_ajuste' => 'required|in:sumar,restar,fijar'
        ]);

        try {
            $this->productoService->ajustarStock(
                $request->producto_id, 
                $request->almacen_id, 
                $request->cantidad, 
                auth()->id(),
                $request->tipo_ajuste,
                $request->motivo ?? 'Ajuste manual desde menú'
            );
            return redirect()->route('productos.historialAjustes')
                ->with('success', 'Stock ajustado correctamente.');
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Error al ajustar stock: ' . $e->getMessage());
        }
    }
    public function checkStock(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'almacen_id' => 'required|exists:almacenes,id',
        ]);

        $inventario = InventarioAlmacen::where('producto_id', $request->producto_id)
            ->where('almacen_id', $request->almacen_id)
            ->first();

        return response()->json([
            'stock' => $inventario ? $inventario->stock : 0
        ]);
    }
    public function exportExcel(Request $request)
    {
        try {
            $productIds = $request->input('product_ids', []);
            
            // Convertir strings a booleanos
            $includePrices = filter_var($request->input('includePrices', true), FILTER_VALIDATE_BOOLEAN);
            $includeStock = filter_var($request->input('includeStock', true), FILTER_VALIDATE_BOOLEAN);
            $includeAllDetails = filter_var($request->input('includeAllDetails', true), FILTER_VALIDATE_BOOLEAN);

            $almacenes = $includeStock
                ? Almacen::where('estado', true)->orderBy('nombre')->get(['id', 'nombre'])
                : collect();
            
            if (!empty($productIds)) {
                $productos = Producto::with([
                        'marca',
                        'categoria',
                        'tipounidad',
                        'inventarios:id,producto_id,almacen_id,stock',
                    ])
                    ->withSum('inventarios as stock_total', 'stock')
                    ->whereIn('id', $productIds)
                    ->get();
            } else {
                $productos = Producto::with([
                        'marca',
                        'categoria',
                        'tipounidad',
                        'inventarios:id,producto_id,almacen_id,stock',
                    ])
                    ->withSum('inventarios as stock_total', 'stock')
                    ->get();
            }
            $filename = 'productos_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            return Excel::download(
                new ProductosExport($productos, $includePrices, $includeStock, $includeAllDetails, $almacenes),
                $filename
            );

        } catch (\Exception $e) {
            return back()->with('error', 'Error al exportar productos: ' . $e->getMessage());
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $productIds = $request->input('product_ids', []);
            
            // Convertir strings a booleanos
            $includePrices = filter_var($request->input('includePrices', true), FILTER_VALIDATE_BOOLEAN);
            $includeStock = filter_var($request->input('includeStock', true), FILTER_VALIDATE_BOOLEAN);
            $includeAllDetails = filter_var($request->input('includeAllDetails', true), FILTER_VALIDATE_BOOLEAN);

            $almacenes = $includeStock
                ? Almacen::where('estado', true)->orderBy('nombre')->get(['id', 'nombre'])
                : collect();
            
            if (!empty($productIds)) {
                $productos = Producto::with([
                        'marca',
                        'categoria',
                        'tipounidad',
                        'inventarios:id,producto_id,almacen_id,stock',
                    ])
                    ->withSum('inventarios as stock_total', 'stock')
                    ->whereIn('id', $productIds)
                    ->get();
            } else {
                $productos = Producto::with([
                        'marca',
                        'categoria',
                        'tipounidad',
                        'inventarios:id,producto_id,almacen_id,stock',
                    ])
                    ->withSum('inventarios as stock_total', 'stock')
                    ->get();
            }

            $pdf = Pdf::loadview('admin.producto.pdf', compact('productos', 'almacenes', 'includePrices', 'includeStock', 'includeAllDetails'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('reporte-productos_'.now()->format('Y-m-d_H-i-s').'.pdf');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al exportar PDF: ' . $e->getMessage());
        }
    }
}



