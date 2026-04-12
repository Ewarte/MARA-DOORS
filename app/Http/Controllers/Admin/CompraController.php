<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompraRequest;
use App\Http\Requests\UpdateCompraRequest;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Comprobante;
use App\Models\DetalleVenta;
use App\Models\Proveedor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\FilterByAlmacen;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\URL;

class CompraController extends Controller
{
    use FilterByAlmacen;

    public function generarPdf($id, Request $request)
    {

        $compra = Compra::with([
            'comprobante',
            'proveedor.persona',
            'detalles.producto',
            'almacen',
            'user'
        ])->findOrFail($id);

        $pdf = Pdf::loadview('admin.compra.pdf', compact('compra'))
            ->setPaper('a4', 'portrait');

        $fileName = "COMPRA-{$compra->numero_comprobante}-{$compra->proveedor->persona->razon_social}.pdf";

        if ($request->has('print')) {
            return $pdf->stream($fileName);
        }

        return $pdf->download($fileName);
    }

    public function facturaPublica(Compra $compra, Request $request)
    {
        $compra->load([
            'comprobante',
            'proveedor.persona',
            'detalles.producto',
            'almacen',
            'user'
        ]);

        $pdf = Pdf::loadview('admin.compra.pdf', compact('compra'))
            ->setPaper('a4', 'portrait');

        $fileName = "COMPRA-{$compra->numero_comprobante}.pdf";

        if ($request->boolean('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }

    protected $compraService;

    function __construct(\App\Services\CompraService $compraService)
    {
        $this->compraService = $compraService;
        $this->middleware('permission:ver-compra|crear-compra|mostrar-compra|eliminar-compra', ['only' => ['index']]);
        $this->middleware('permission:crear-compra', ['only' => ['create', 'store']]);
        $this->middleware('permission:mostrar-compra', ['only' => ['show']]);
        $this->middleware('permission:editar-compra', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-compra', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $busqueda = $request->get('busqueda');
        $metodoPago = $request->get('metodo_pago');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $perPage  = $request->get('per_page', 10);
        $sort     = $request->get('sort', 'fecha_hora'); // Por defecto fecha más reciente
        $direction = $request->get('direction', 'desc'); // Descendente por defecto

        // Validar per_page y direction
        if (!in_array($perPage, [5, 10, 15, 20, 25])) $perPage = 10;
        if (!in_array($direction, ['asc', 'desc'])) $direction = 'desc';

        $query = Compra::with(['comprobante', 'proveedor.persona', 'user'])
            ->where('estado', 1);

        // Filtrar por almacén del usuario
        $query = $this->filterByUserAlmacen($query);

        // Búsqueda
        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('numero_comprobante', 'like', "%{$busqueda}%")
                    ->orWhereHas('proveedor.persona', function ($pq) use ($busqueda) {
                        $pq->where('razon_social', 'like', "%{$busqueda}%")
                            ->orWhere('tipo_persona', 'like', "%{$busqueda}%");
                    })
                    ->orWhereHas('comprobante', function ($cq) use ($busqueda) {
                        $cq->where('tipo_comprobante', 'like', "%{$busqueda}%");
                    });
            });
        }

        if ($metodoPago) {
            $query->where('metodo_pago', $metodoPago);
        }
        if ($dateFrom) {
            $query->whereDate('fecha_hora', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('fecha_hora', '<=', $dateTo);
        }

        // Ordenamiento
        switch ($sort) {
            case 'proveedor':
                $query->join('proveedores', 'compras.proveedor_id', '=', 'proveedores.id')
                    ->join('personas', 'proveedores.persona_id', '=', 'personas.id')
                    ->select('compras.*') // Evitar colisión de IDs
                    ->orderBy('personas.razon_social', $direction);
                break;
            case 'numero_comprobante':
                $query->orderBy('numero_comprobante', $direction);
                break;
            case 'total':
                $query->orderBy('total', $direction);
                break;
            case 'fecha_hora':
                $query->orderBy('fecha_hora', $direction);
                break;
            default:
                $query->latest('fecha_hora');
                break;
        }

        $compras = $query->paginate($perPage);

        // Estadísticas para el footer - Filtradas por almacén
        $statsQuery = Compra::where('estado', 1);
        $statsQuery = $this->filterByUserAlmacen($statsQuery);
        if ($metodoPago) $statsQuery->where('metodo_pago', $metodoPago);
        if ($dateFrom) $statsQuery->whereDate('fecha_hora', '>=', $dateFrom);
        if ($dateTo) $statsQuery->whereDate('fecha_hora', '<=', $dateTo);

        $totalComprasMonto = (clone $statsQuery)->sum('total');
        $totalComprasCount = (clone $statsQuery)->count();
        $comprasHoy = (clone $statsQuery)
            ->whereDate('fecha_hora', today())
            ->count();

        if ($request->ajax()) {
            return view('admin.compra.index', compact(
                'compras',
                'busqueda',
                'metodoPago',
                'dateFrom',
                'dateTo',
                'perPage',
                'sort',
                'direction',
                'totalComprasMonto',
                'totalComprasCount',
                'comprasHoy'
            ));
        }

        return view('admin.compra.index', compact(
            'compras',
            'busqueda',
            'metodoPago',
            'dateFrom',
            'dateTo',
            'perPage',
            'sort',
            'direction',
            'totalComprasMonto',
            'totalComprasCount',
            'comprasHoy'
        ));
    }


    public function create()
    {
        $proveedores = Proveedor::with('persona')->get();
        $comprobantes = Comprobante::all();

        // Filtrar almacenes disponibles para el usuario
        $userAlmacenId = auth()->user()->almacen_id;
        if ($userAlmacenId) {
            $almacenes = \App\Models\Almacen::where('estado', 1)->where('id', $userAlmacenId)->get();
        } else {
            $almacenes = \App\Models\Almacen::where('estado', 1)->get();
        }
        $productos = Producto::where('estado', 1)->get();

        // Generar número de comprobante automático
        $ultimaCompra = Compra::latest('id')->first();
        $nextNumber = $ultimaCompra ? (intval($ultimaCompra->numero_comprobante) + 1) : 1;
        $nextComprobanteNumber = str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

        return view('admin.compra.create', compact('proveedores', 'comprobantes', 'almacenes', 'productos', 'nextComprobanteNumber'));
    }

    public function store(StoreCompraRequest $request)
    {
        try {
            // Validar acceso al almacén
            if (!$this->canAccessAlmacen($request->almacen_id)) {
                return redirect()->back()->withInput()->with('error', 'No tiene permiso para realizar compras en este almacén.');
            }

            DB::beginTransaction();

            $numeroComprobante = $request->numero_comprobante;
            if (empty($numeroComprobante)) {
                $ultimaCompra = Compra::latest('id')->first();
                $nextNumber = $ultimaCompra ? (intval($ultimaCompra->numero_comprobante) + 1) : 1;
                $numeroComprobante = str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
            }

            $compra = Compra::create([
                'fecha_hora' => now(),
                'numero_comprobante' => $numeroComprobante,
                'total' => 0, // Se calculará abajo
                'metodo_pago' => $request->input('metodo_pago', 'efectivo'),
                'monto_pagado' => 0,
                'costo_transporte' => $request->costo_transporte ?? 0,
                'nota_personal' => $request->nota_personal,
                'estado_pago' => $request->estado_pago ?? 'pendiente',
                'estado_entrega' => $request->estado_entrega ?? 'por_entregar',
                'comprobante_id' => $request->comprobante_id,
                'proveedor_id' => $request->proveedor_id,
                'almacen_id' => $request->almacen_id,
                'user_id' => auth()->id(),
            ]);

            $total = 0;
            foreach ($request->arrayidproducto as $index => $productoId) {
                $cantidad = $request->arraycantidad[$index];
                $precioCompra = $request->arraypreciocompra[$index];
                $precioVenta = $request->arrayprecioventa[$index];

                $compra->detalles()->create([
                    'producto_id' => $productoId,
                    'cantidad' => $cantidad,
                    'precio_compra' => $precioCompra,
                    'precio_venta' => $precioVenta,
                ]);

                $total += ($cantidad * $precioCompra);
            }

            $montoPagado = (float) $request->input('monto_pagado', 0);
            if ($request->input('estado_pago') === 'pagado') {
                $montoPagado = $total;
            }
            $montoPagado = max(0.0, min($montoPagado, (float) $total));
            $estadoPago = $montoPagado >= (float) $total ? 'pagado' : ($montoPagado > 0 ? 'parcial' : 'pendiente');

            $compra->update([
                'total' => $total,
                'monto_pagado' => $montoPagado,
                'estado_pago' => $estadoPago,
            ]);

            // Procesar stock
            $this->compraService->procesarEntradaStock($compra);

            DB::commit();
            return redirect()->route('compras.index')->with('success', 'Compra registrada exitosamente.');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al registrar: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    // En CompraController.php
    public function show(Compra $compra)
    {
        if (!$this->canAccessAlmacen($compra->almacen_id)) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Acceso denegado'], 403);
            }
            return redirect()->route('compras.index')->with('error', 'No tiene acceso a esta compra.');
        }

        $compra->load(['comprobante', 'proveedor.persona', 'detalles.producto', 'almacen', 'user']);

        if (request()->ajax() || request()->wantsJson()) {
            $telefono = optional(optional($compra->proveedor)->persona)->telefono;
            $pdfUrl = route('compras.pdf', ['id' => $compra->id]);
            $facturaUrl = URL::temporarySignedRoute(
                'facturas.compras',
                now()->addDays(30),
                ['compra' => $compra->id]
            );

            return response()->json([
                'success' => true,
                'telefono' => $telefono,
                'pdf_url' => $pdfUrl,
                'factura_url' => $facturaUrl,
                'html' => view('admin.compra.show-modal', compact('compra'))->render()
            ]);
        }

        return view('admin.compra.show', compact('compra'));
    }

    public function edit(Compra $compra)
    {
        $compra->load(['detalles.producto', 'proveedor.persona']);

        if (!$this->canAccessAlmacen($compra->almacen_id)) {
            return redirect()->route('compras.index')->with('error', 'No tiene acceso a esta compra.');
        }

        $productosInfo = [];
        foreach ($compra->detalles as $detalle) {
            $producto = $detalle->producto;
            // Calculamos cuánto se ha vendido de este producto en total
            //$vendido = \App\Models\DetalleVenta::where('producto_id', $producto->id)->sum('cantidad');
            if ($producto) {
                $vendido = DetalleVenta::where('producto_id', $producto->id)->sum('cantidad');
            } else {
                $vendido = 0;
            }

            $productosInfo[$producto->id] = [
                'vendido' => $vendido,
                'minimo_permitido' => $vendido,
                'cantidad_original' => $detalle->cantidad
            ];
        }

        $proveedores = Proveedor::with('persona')->get();
        $comprobantes = Comprobante::all();

        // Filtrar almacenes disponibles para el usuario
        $userAlmacenId = auth()->user()->almacen_id;
        if ($userAlmacenId) {
            $almacenes = \App\Models\Almacen::where('estado', 1)->where('id', $userAlmacenId)->get();
        } else {
            $almacenes = \App\Models\Almacen::where('estado', 1)->get();
        }

        // Cargamos los productos con el stock total para mostrar en el select
        $productos = Producto::withSum('inventarios as stock', 'stock')
            ->where('estado', 1)
            ->get();

        return view('admin.compra.edit', compact('compra', 'proveedores', 'comprobantes', 'almacenes', 'productos', 'productosInfo'));
    }

    public function update(UpdateCompraRequest $request, Compra $compra)
    {
        try {
            // Validar acceso al almacén original y al nuevo
            if (!$this->canAccessAlmacen($compra->almacen_id) || !$this->canAccessAlmacen($request->almacen_id)) {
                return redirect()->back()->withInput()->with('error', 'Acceso denegado al almacén.');
            }

            DB::beginTransaction();

            // 1. Validar de forma inteligente: Si aumentas stock (de 5 a 6), no dar error aunque tengas 0.
            $nuevosDetalles = [];
            foreach ($request->arrayidproducto as $index => $pid) {
                if ($pid) {
                    $nuevosDetalles[] = [
                        'producto_id' => $pid,
                        'cantidad' => $request->arraycantidad[$index] ?? 0
                    ];
                }
            }
            $this->compraService->validarEdicionDinamica($compra, $nuevosDetalles);

            // 2. Revertir stock actual
            $this->compraService->revertirStock($compra);

            // 3. Borrar detalles viejos
            $compra->detalles()->delete();

            // 4. Actualizar datos básicos
            $compra->update([
                'fecha_hora' => $request->fecha_hora ?? $compra->fecha_hora,
                'numero_comprobante' => $request->numero_comprobante ?? $compra->numero_comprobante,
                'total' => 0,
                'metodo_pago' => $request->input('metodo_pago', $compra->metodo_pago ?? 'efectivo'),
                'costo_transporte' => $request->costo_transporte ?? 0,
                'nota_personal' => $request->nota_personal,
                'estado_pago' => $request->estado_pago ?? $compra->estado_pago,
                'estado_entrega' => $request->estado_entrega ?? $compra->estado_entrega,
                'comprobante_id' => $request->comprobante_id,
                'proveedor_id' => $request->proveedor_id,
                'almacen_id' => $request->almacen_id,
            ]);

            // 5. Crear nuevos detalles
            $total = 0;
            $arrayIds = $request->arrayidproducto ?? [];
            $arrayCantidades = $request->arraycantidad ?? [];
            $arrayPreciosCompra = $request->arraypreciocompra ?? [];
            $arrayPreciosVenta = $request->arrayprecioventa ?? [];

            foreach ($arrayIds as $index => $productoId) {
                if (empty($productoId)) continue;

                $cantidad = $arrayCantidades[$index] ?? 0;
                $precioCompra = $arrayPreciosCompra[$index] ?? 0;
                $precioVenta = $arrayPreciosVenta[$index] ?? 0;

                $compra->detalles()->create([
                    'producto_id' => $productoId,
                    'cantidad' => $cantidad,
                    'precio_compra' => $precioCompra,
                    'precio_venta' => $precioVenta,
                ]);

                $total += ($cantidad * $precioCompra);
            }

            $montoPagado = (float) $request->input('monto_pagado', $compra->monto_pagado ?? 0);
            if ($request->input('estado_pago') === 'pagado') {
                $montoPagado = $total;
            } elseif ($request->input('estado_pago') === 'pendiente') {
                $montoPagado = 0;
            }
            $montoPagado = max(0.0, min($montoPagado, (float) $total));
            $estadoPago = $montoPagado >= (float) $total ? 'pagado' : ($montoPagado > 0 ? 'parcial' : 'pendiente');

            $compra->update([
                'total' => $total,
                'monto_pagado' => $montoPagado,
                'estado_pago' => $estadoPago,
            ]);

            // 6. Procesar entrada de stock nuevo
            // IMPORTANTE: Recargar la relación detalles porque tiene en caché los viejos (que ya se borraron)
            $compra->refresh();
            $this->compraService->procesarEntradaStock($compra);

            DB::commit();
            return redirect()->route('compras.index')->with('success', 'Compra actualizada exitosamente.');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function destroy(Compra $compra)
    {
        try {
            if (!$this->canAccessAlmacen($compra->almacen_id)) {
                return redirect()->route('compras.index')->with('error', 'No tiene permiso para eliminar compras en este almacén.');
            }

            DB::beginTransaction();

            // Validar si se puede revertir
            $this->compraService->validarReversion($compra);

            // Revertir stock
            $this->compraService->revertirStock($compra);

            // Borrar detalles y compra
            $compra->detalles()->delete();
            $compra->delete();

            DB::commit();
            return redirect()->route('compras.index')->with('success', 'Compra eliminada y stock revertido.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('compras.index')->with('error', 'No se pudo eliminar: ' . $e->getMessage());
        }
    }

    public function actualizarEstadoPago(Request $request, string $id)
    {
        try {
            $compra = Compra::findOrFail($id);

            if (!$this->canAccessAlmacen($compra->almacen_id)) {
                return response()->json(['success' => false, 'message' => 'Acceso denegado'], 403);
            }

            $estadoAnterior = $compra->estado_pago;
            $estadoNuevo = $request->input('estado_pago', 'pagado');

            DB::beginTransaction();

            // Si se cancela/anula y no estaba cancelada antes -> Revertir stock (quitar lo que entre)
            if (in_array($estadoNuevo, ['cancelado', 'anulado']) && !in_array($estadoAnterior, ['cancelado', 'anulado'])) {
                // Validar si se puede revertir (si hay stock suficiente para quitar)
                $this->compraService->validarReversion($compra);
                $this->compraService->revertirStock($compra);
            }
            // Si se recupera de cancelado -> Procesar entrada stock nuevamente
            elseif (!in_array($estadoNuevo, ['cancelado', 'anulado']) && in_array($estadoAnterior, ['cancelado', 'anulado'])) {
                $this->compraService->procesarEntradaStock($compra);
            }

            $updates = ['estado_pago' => $estadoNuevo];

            if ($request->filled('metodo_pago')) {
                $updates['metodo_pago'] = $request->input('metodo_pago');
            }

            if (in_array($estadoNuevo, ['cancelado', 'anulado'])) {
                $updates['monto_pagado'] = 0;
            } elseif ($estadoNuevo === 'pagado') {
                $updates['monto_pagado'] = (float) $compra->total;
            } elseif ($estadoNuevo === 'pendiente') {
                $updates['monto_pagado'] = 0;
            } elseif ($estadoNuevo === 'parcial') {
                $montoPagado = (float) $request->input('monto_pagado', $compra->monto_pagado ?? 0);
                $updates['monto_pagado'] = max(0.0, min($montoPagado, (float) $compra->total));
            }

            $compra->update($updates);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Estado de pago actualizado correctamente',
                'compra' => $compra
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado de pago: ' . $e->getMessage()
            ], 400);
        }
    }

    public function actualizarEstadoEntrega(Request $request, string $id)
    {
        try {
            $compra = Compra::findOrFail($id);

            if (!$this->canAccessAlmacen($compra->almacen_id)) {
                return response()->json(['success' => false, 'message' => 'Acceso denegado'], 403);
            }

            $estado = $request->input('estado_entrega', 'entregado');

            $compra->update(['estado_entrega' => $estado]);

            return response()->json([
                'success' => true,
                'message' => 'Estado de entrega actualizado correctamente',
                'compra' => $compra
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado de entrega: ' . $e->getMessage()
            ], 400);
        }
    }
}

