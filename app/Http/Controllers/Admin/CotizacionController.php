<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cotizacion;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Proveedor;
use App\Models\Almacen;
use App\Models\Comprobante;
use App\Services\CotizacionService;
use App\Traits\FilterByAlmacen;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CotizacionController extends Controller
{
    use FilterByAlmacen;
    protected $cotizacionService;

    public function __construct(CotizacionService $cotizacionService)
    {
        $this->cotizacionService = $cotizacionService;
        $this->middleware('permission:ver-cotizacion|crear-cotizacion|mostrar-cotizacion|eliminar-cotizacion', ['only' => ['index', 'show']]);
        $this->middleware('permission:crear-cotizacion', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-cotizacion', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-cotizacion', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $busqueda = $request->get('busqueda');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $perPage = $request->get('per_page', 10);
        $sort = $request->get('sort', 'fecha_hora');
        $direction = $request->get('direction', 'desc');

        $query = Cotizacion::with(['cliente.persona', 'proveedor.persona', 'user', 'almacen']);

        $query = $this->filterByUserAlmacen($query);

        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('numero_cotizacion', 'like', "%{$busqueda}%")
                    ->orWhereHas('cliente.persona', function ($pq) use ($busqueda) {
                        $pq->where('razon_social', 'like', "%{$busqueda}%");
                    })
                    ->orWhereHas('proveedor.persona', function ($pq) use ($busqueda) {
                        $pq->where('razon_social', 'like', "%{$busqueda}%");
                    });
            });
        }

        if ($dateFrom) $query->whereDate('fecha_hora', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('fecha_hora', '<=', $dateTo);

        $cotizaciones = $query->orderBy($sort, $direction)->paginate($perPage);

        if ($request->ajax()) {
            return view('admin.cotizacion.index', compact('cotizaciones', 'busqueda', 'dateFrom', 'dateTo', 'perPage', 'sort', 'direction'));
        }

        return view('admin.cotizacion.index', compact('cotizaciones', 'busqueda', 'dateFrom', 'dateTo', 'perPage', 'sort', 'direction'));
    }

    public function create()
    {
        $productos = Producto::where('estado', 1)->get(['id', 'codigo', 'nombre', 'precio_venta', 'precio_compra']);
        $clientes = Cliente::with('persona')->get();
        $proveedores = Proveedor::with('persona')->get();
        
        $userAlmacenId = auth()->user()->almacen_id;
        $almacenes = $userAlmacenId ? Almacen::where('id', $userAlmacenId)->get() : Almacen::where('estado', 1)->get();
        
        $nextCotizacionNumber = $this->getNextNumero();

        return view('admin.cotizacion.create', compact('productos', 'clientes', 'proveedores', 'almacenes', 'nextCotizacionNumber'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'fecha_hora' => ['nullable', 'date'],
                'numero_cotizacion' => ['required', 'string', 'max:50'],
                'almacen_id' => ['required', 'integer'],
                'cliente_id' => ['nullable', 'integer'],
                'proveedor_id' => ['nullable', 'integer'],
                'vencimiento' => ['nullable', 'date'],
                'nota_personal' => ['nullable', 'string'],
                'nota_cliente' => ['nullable', 'string'],
                'arrayidproducto' => ['required', 'array', 'min:1'],
                'arraycantidad' => ['required', 'array', 'min:1'],
                'arraypreciounitario' => ['required', 'array', 'min:1'],
                'arraydescuento' => ['nullable', 'array'],
            ]);

            if (!empty($validated['cliente_id']) && !empty($validated['proveedor_id'])) {
                return back()->withInput()->with('error', 'Seleccione solo Cliente o Proveedor (no ambos).');
            }

            $this->cotizacionService->crearCotizacion($request->all(), auth()->id());
            return redirect()->route('cotizaciones.index')->with('success', 'Cotización creada exitosamente');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show(Cotizacion $cotizacion)
    {
        $cotizacion->load(['cliente.persona', 'proveedor.persona', 'user', 'almacen', 'detalles.producto']);
        
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.cotizacion.show-modal', compact('cotizacion'))->render(),
                'cotizacion' => $cotizacion,
                'pdf_url' => route('cotizaciones.pdf', ['cotizacion' => $cotizacion->id])
            ]);
        }

        return view('admin.cotizacion.show', compact('cotizacion'));
    }

    public function edit(Cotizacion $cotizacion)
    {
        $productos = Producto::where('estado', 1)->get();
        $clientes = Cliente::with('persona')->get();
        $proveedores = Proveedor::with('persona')->get();
        $userAlmacenId = auth()->user()->almacen_id;
        $almacenes = $userAlmacenId ? Almacen::where('id', $userAlmacenId)->get() : Almacen::where('estado', 1)->get();

        return view('admin.cotizacion.edit', compact('cotizacion', 'productos', 'clientes', 'proveedores', 'almacenes'));
    }

    public function update(Request $request, Cotizacion $cotizacion)
    {
        try {
            $validated = $request->validate([
                'fecha_hora' => ['nullable', 'date'],
                'numero_cotizacion' => ['required', 'string', 'max:50'],
                'almacen_id' => ['required', 'integer'],
                'cliente_id' => ['nullable', 'integer'],
                'proveedor_id' => ['nullable', 'integer'],
                'vencimiento' => ['nullable', 'date'],
                'nota_personal' => ['nullable', 'string'],
                'nota_cliente' => ['nullable', 'string'],
                'arrayidproducto' => ['required', 'array', 'min:1'],
                'arraycantidad' => ['required', 'array', 'min:1'],
                'arraypreciounitario' => ['required', 'array', 'min:1'],
                'arraydescuento' => ['nullable', 'array'],
            ]);

            if (!empty($validated['cliente_id']) && !empty($validated['proveedor_id'])) {
                return back()->withInput()->with('error', 'Seleccione solo Cliente o Proveedor (no ambos).');
            }

            $this->cotizacionService->actualizarCotizacion($cotizacion, $request->all());
            return redirect()->route('cotizaciones.index')->with('success', 'Cotización actualizada exitosamente');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy(Cotizacion $cotizacion)
    {
        try {
            $cotizacion->detalles()->delete();
            $cotizacion->delete();
            return redirect()->route('cotizaciones.index')->with('success', 'Cotización eliminada');
        } catch (Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function convertirVenta(Request $request, Cotizacion $cotizacion)
    {
        try {
            $this->cotizacionService->convertirAVenta($cotizacion, $request->all());
            return response()->json(['success' => true, 'message' => 'Convertido a venta exitosamente']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function convertirCompra(Request $request, Cotizacion $cotizacion)
    {
        try {
            $this->cotizacionService->convertirACompra($cotizacion, $request->all());
            return response()->json(['success' => true, 'message' => 'Convertido a compra exitosamente']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function generarPdf(Cotizacion $cotizacion)
    {
        $cotizacion->load(['cliente.persona', 'proveedor.persona', 'user', 'almacen', 'detalles.producto']);
        $pdf = Pdf::loadView('admin.cotizacion.pdf', compact('cotizacion'));
        return $pdf->stream("COT-{$cotizacion->numero_cotizacion}.pdf");
    }

    private function getNextNumero()
    {
        $last = Cotizacion::latest()->first();
        $next = $last ? (int)$last->numero_cotizacion + 1 : 1;
        return str_pad($next, 8, '0', STR_PAD_LEFT);
    }
}
