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
        $this->middleware('permission:ver-producto', ['only' => ['index', 'getDetalle']]);
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
        $estado = $request->get('estado', 'all');
        $categoriaId = $request->get('categoria_id', 'all');
        $stock = $request->get('stock', 'all');
        $almacenId = $request->get('almacen_id', 'all');

        // Validar per_page y direction
        $perPageValue = $perPage === 'all' ? 'all' : (in_array((int)$perPage, [5, 10, 15, 20, 25, 50, 100]) ? (int)$perPage : 10);
        if (!in_array($direction, ['asc', 'desc'])) $direction = 'asc';

        $query = $this->buildProductQuery($request);
        $query = $this->applySorting($query, $request);

        if ($perPageValue === 'all') {
            // Aumentar límite de tiempo y memoria para consultas grandes
            set_time_limit(120);
            ini_set('memory_limit', '256M');
            $allItems = $query->get();
            $total = $allItems->count();
            // Envolver en un paginador manual para que la vista funcione igual
            $productos = new \Illuminate\Pagination\LengthAwarePaginator(
                $allItems,
                $total,
                max($total, 1),
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $productos = $query->paginate($perPageValue);
        }

        // Estadísticas para el footer (con respecto al almacén si está seleccionado)
        if ($almacenId !== 'all' && is_numeric($almacenId)) {
            $totalStockGlobal = DB::table('inventario_almacenes')->where('almacen_id', $almacenId)->sum('stock');
            
            $bajoStockCount = Producto::join('inventario_almacenes', 'productos.id', '=', 'inventario_almacenes.producto_id')
                ->where('inventario_almacenes.almacen_id', $almacenId)
                ->select('productos.id')
                ->groupBy('productos.id')
                ->havingRaw('SUM(inventario_almacenes.stock) <= 10')
                ->get()
                ->count();
        } else {
            $totalStockGlobal = DB::table('inventario_almacenes')->sum('stock');
            
            $bajoStockCount = Producto::join('inventario_almacenes', 'productos.id', '=', 'inventario_almacenes.producto_id')
                ->select('productos.id')
                ->groupBy('productos.id')
                ->havingRaw('SUM(inventario_almacenes.stock) <= 10')
                ->get()
                ->count();
        }
        
        $productosActivos = Producto::where('estado', 1)->count();
        $almacenes = Almacen::where('estado', true)->get();
        $categorias = Categoria::orderBy('nombre')->get();

        $viewData = compact(
            'productos', 'busqueda', 'perPage', 'almacenes', 'categorias',
            'sort', 'direction', 'estado', 'categoriaId', 'stock', 'almacenId',
            'totalStockGlobal', 'productosActivos', 'bajoStockCount'
        );

        if ($request->ajax()) {
            return view('admin.producto.index', $viewData);
        }

        return view('admin.producto.index', $viewData);
    }

    private function buildProductQuery(Request $request)
    {
        $busqueda = $request->get('busqueda');
        $estado = $request->get('estado', 'all');
        $categoriaId = $request->get('categoria_id', 'all');
        $stock = $request->get('stock', 'all');
        $almacenId = $request->get('almacen_id', 'all');

        if ($almacenId !== 'all' && is_numeric($almacenId)) {
            $query = Producto::with([
                'marca:id,nombre',
                'categoria:id,nombre',
                'tipounidad:id,nombre',
                'inventarios' => function($q) use ($almacenId) {
                    $q->select('id', 'producto_id', 'almacen_id', 'stock')
                      ->where('almacen_id', $almacenId)
                      ->with('almacen:id,nombre');
                },
            ]);
            $query->withSum(['inventarios as stock_total' => function($q) use ($almacenId) {
                $q->where('almacen_id', $almacenId);
            }], 'stock');
        } else {
            $query = Producto::with([
                'marca:id,nombre',
                'categoria:id,nombre',
                'tipounidad:id,nombre',
                'inventarios' => function($q) {
                    $q->select('id', 'producto_id', 'almacen_id', 'stock')
                      ->with('almacen:id,nombre');
                },
            ]);
            $query->withSum('inventarios as stock_total', 'stock');
        }

        // Búsqueda
        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('codigo', 'like', "%{$busqueda}%")
                  ->orWhere('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('descripcion', 'like', "%{$busqueda}%");
            });
        }

        if ($estado === 'active') {
            $query->where('estado', 1);
        } elseif ($estado === 'inactive') {
            $query->where('estado', 0);
        }

        if ($categoriaId !== 'all' && is_numeric($categoriaId)) {
            $query->where('categoria_id', $categoriaId);
        }

        if ($almacenId !== 'all' && is_numeric($almacenId)) {
            $query->whereHas('inventarios', function($q) use ($almacenId) {
                $q->where('almacen_id', $almacenId);
            });

            if ($stock === 'low') {
                $query->whereRaw('(SELECT COALESCE(SUM(stock), 0) FROM inventario_almacenes ia WHERE ia.producto_id = productos.id AND ia.almacen_id = ?) <= 10', [$almacenId]);
            } elseif ($stock === 'normal') {
                $query->whereRaw('(SELECT COALESCE(SUM(stock), 0) FROM inventario_almacenes ia WHERE ia.producto_id = productos.id AND ia.almacen_id = ?) > 10', [$almacenId]);
            }
        } else {
            if ($stock === 'low') {
                $query->whereRaw('(SELECT COALESCE(SUM(stock), 0) FROM inventario_almacenes ia WHERE ia.producto_id = productos.id) <= 10');
            } elseif ($stock === 'normal') {
                $query->whereRaw('(SELECT COALESCE(SUM(stock), 0) FROM inventario_almacenes ia WHERE ia.producto_id = productos.id) > 10');
            }
        }

        return $query;
    }

    private function applySorting($query, Request $request)
    {
        $sort = $request->get('sort', 'nombre');
        $direction = $request->get('direction', 'asc');
        
        if (!in_array($direction, ['asc', 'desc'])) $direction = 'asc';

        switch ($sort) {
            case 'categoria':
                $query->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
                      ->select('productos.*')
                      ->orderBy('categorias.nombre', $direction);
                break;
            case 'stock_total':
                $query->orderBy('stock_total', $direction);
                break;
            case 'precio_venta':
            case 'nombre':
            case 'estado':
                $query->orderBy('productos.' . $sort, $direction);
                break;
            default:
                $query->latest('productos.created_at');
                break;
        }

        return $query;
    }
 
    public function getDetalle($id)
    {
        $producto = Producto::with([
            'marca:id,nombre',
            'categoria:id,nombre',
            'tipounidad:id,nombre',
            'inventarios' => function($q) {
                $q->select('id', 'producto_id', 'almacen_id', 'stock')
                  ->with('almacen:id,nombre');
            },
        ])->findOrFail($id);

        return response()->json([
            'id'            => $producto->id,
            'nombre'        => $producto->nombre,
            'codigo'        => $producto->codigo,
            'descripcion'   => $producto->descripcion,
            'precio_venta'  => $producto->precio_venta,
            'precio_compra' => $producto->precio_compra,
            'categoria'     => $producto->categoria?->nombre,
            'marca'         => $producto->marca?->nombre,
            'tipounidad'    => $producto->tipounidad?->nombre,
            'estado'        => $producto->estado,
            'inventarios'   => $producto->inventarios->map(fn($inv) => [
                'almacen' => $inv->almacen?->nombre,
                'stock'   => $inv->stock,
            ]),
        ]);
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

        $perPageValue = $perPage === 'all' ? 1000000 : (in_array((int)$perPage, [5, 10, 15, 20, 25]) ? (int)$perPage : 10);
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

        $ajustes = $query->paginate($perPageValue);

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

            $almacenId = $request->input('almacen_id', 'all');

            $almacenes = $includeStock
                ? ($almacenId !== 'all' && is_numeric($almacenId)
                    ? Almacen::where('id', $almacenId)->get(['id', 'nombre'])
                    : Almacen::where('estado', true)->orderBy('nombre')->get(['id', 'nombre']))
                : collect();
            
            if (!empty($productIds)) {
                $query = Producto::with([
                        'marca',
                        'categoria',
                        'tipounidad',
                        'inventarios:id,producto_id,almacen_id,stock',
                    ]);
                if ($almacenId !== 'all' && is_numeric($almacenId)) {
                    $query->withSum(['inventarios as stock_total' => function($q) use ($almacenId) {
                        $q->where('almacen_id', $almacenId);
                    }], 'stock');
                } else {
                    $query->withSum('inventarios as stock_total', 'stock');
                }
                $productos = $query->whereIn('id', $productIds)->get();
            } else {
                $query = $this->buildProductQuery($request);
                $query = $this->applySorting($query, $request);
                $productos = $query->get();
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
            // Aumentar límites para el generador de PDF
            set_time_limit(180);
            ini_set('memory_limit', '512M');

            $productIds = $request->input('product_ids', []);
            $includePrices      = filter_var($request->input('includePrices', true),      FILTER_VALIDATE_BOOLEAN);
            $includeStock       = filter_var($request->input('includeStock', true),       FILTER_VALIDATE_BOOLEAN);
            $includeAllDetails  = filter_var($request->input('includeAllDetails', true),  FILTER_VALIDATE_BOOLEAN);
            $almacenId          = $request->input('almacen_id', 'all');

            // Almacenes a mostrar en columnas de stock
            $almacenes = $includeStock
                ? ($almacenId !== 'all' && is_numeric($almacenId)
                    ? Almacen::where('id', $almacenId)->get(['id', 'nombre'])
                    : Almacen::where('estado', true)->orderBy('nombre')->get(['id', 'nombre']))
                : collect();

            // Construir query optimizada (solo columnas necesarias para PDF)
            if (!empty($productIds)) {
                $query = Producto::select('id', 'codigo', 'nombre', 'precio_compra', 'precio_venta',
                                         'categoria_id', 'marca_id', 'tipounidad_id', 'estado');
                if ($includeAllDetails) {
                    $query->with([
                        'categoria:id,nombre',
                        'marca:id,nombre',
                        'tipounidad:id,nombre',
                    ]);
                }
                if ($includeStock) {
                    if ($almacenId !== 'all' && is_numeric($almacenId)) {
                        $query->with(['inventarios' => fn($q) => $q
                            ->select('id', 'producto_id', 'almacen_id', 'stock')
                            ->where('almacen_id', $almacenId)]);
                        $query->withSum(['inventarios as stock_total' => fn($q) => $q->where('almacen_id', $almacenId)], 'stock');
                    } else {
                        $query->with(['inventarios' => fn($q) => $q->select('id', 'producto_id', 'almacen_id', 'stock')]);
                        $query->withSum('inventarios as stock_total', 'stock');
                    }
                }
                $productos = $query->whereIn('id', $productIds)->get();
            } else {
                $query = $this->buildProductQuery($request);
                $query = $this->applySorting($query, $request);

                // Seguridad: limitar PDF a 500 filas para evitar agotamiento de memoria
                $totalCount = $query->count();
                if ($totalCount > 500) {
                    return back()->with('error',
                        "El reporte PDF está limitado a 500 registros para evitar problemas de rendimiento. " .
                        "Actualmente hay {$totalCount} registros. Usa los filtros para reducir el resultado, o selecciona productos específicos para exportar."
                    );
                }

                $productos = $query->get();
            }

            $pdf = Pdf::loadView('admin.producto.pdf', compact(
                'productos', 'almacenes', 'includePrices', 'includeStock', 'includeAllDetails'
            ))->setPaper('a4', 'landscape');

            $pdf->getDomPDF()->set_option('isPhpEnabled', false);
            $pdf->getDomPDF()->set_option('isRemoteEnabled', false);

            return $pdf->download('reporte-productos_' . now()->format('Y-m-d_H-i-s') . '.pdf');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al exportar PDF: ' . $e->getMessage());
        }
    }
}



