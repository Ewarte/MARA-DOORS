<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrasladoRequest;
use App\Http\Requests\UpdateTrasladoRequest;
use App\Models\Almacen;
use App\Models\InventarioAlmacen;
use App\Models\Producto;
use App\Models\Traslado;
use App\Traits\FilterByAlmacen;
use App\Exports\TrasladosExport;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class TrasladoController extends Controller
{
    use FilterByAlmacen;

    function __construct()
    {
        $this->middleware('permission:ver-traslado', ['only' => ['index']]);
        $this->middleware('permission:crear-traslado', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-traslado', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-traslado', ['only' => ['destroy']]);
        $this->middleware('permission:update-estadoTraslado', ['only' => ['toggleEstado']]);
    }

    public function index(Request $request)
    {
        $busqueda = $request->get('busqueda');
        $perPage  = $request->get('per_page', 10);

        // Validar perPage
        if (!in_array($perPage, [5,10,15,20,25])) {
            $perPage = 10;
        }

        // Construir query
        $query = Traslado::with([
            'origenAlmacen',
            'destinoAlmacen',
            'user',
            'detalles.producto'
        ]);

        // Filtrar por almacen del usuario
        $query = $this->filterByUserAlmacen($query, 'origen_almacen_id');

        if ($busqueda) {
            $query->where(function($q) use ($busqueda) {
                $q->where('id', 'like', "%{$busqueda}%")
                ->orWhereHas('origenAlmacen', function($qa) use ($busqueda) {
                    $qa->where('nombre', 'like', "%{$busqueda}%");
                })
                ->orWhereHas('destinoAlmacen', function($qa) use ($busqueda) {
                    $qa->where('nombre', 'like', "%{$busqueda}%");
                })
                ->orWhereHas('user', function($qa) use ($busqueda) {
                    $qa->where('name', 'like', "%{$busqueda}%");
                });
            });
        }

        // Ordenar y paginar
        $traslados = $query->orderBy('fecha_hora', 'desc')->paginate($perPage);

        // Traer almacenes activos (opcional, para filtros)
        $almacenes = Almacen::where('estado', true)->get();

        if ($request->ajax()) {
            return view('admin.traslado.index', compact(
                'traslados',
                'busqueda',
                'perPage',
                'almacenes'
            ));
        }

        return view('admin.traslado.index', compact(
            'traslados',
            'busqueda',
            'perPage',
            'almacenes'
        ));
    }

    public function create()
    {
        $user = auth()->user();
        
        // Si es trabajador (tiene almacen), mostrar solo su almacen como origen
        if ($user->almacen_id) {
            $almacenes = Almacen::where('id', $user->almacen_id)->get();
            $almacenesDestino = $this->getDestinationAlmacens();
        } else {
            // Si es admin, mostrar todos los almacenes
            $almacenes = Almacen::all();
            $almacenesDestino = Almacen::where('estado', true)->get();
        }

        $productos = Producto::with('inventarios')
            ->where('estado', 1)
            ->get();

        return view('admin.traslado.create', compact('almacenes', 'almacenesDestino', 'productos'));
    }



    public function store(StoreTrasladoRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = auth()->user();

            // Validar que si es trabajador, solo puede crear desde su almacen
            if ($user->almacen_id && $request->origen_almacen_id != $user->almacen_id) {
                return back()->withErrors(['error' => 'No puede crear traslados desde otros almacenes.'])->withInput();
            }

            // Validar que no envíe a su mismo almacen
            if ($request->origen_almacen_id == $request->destino_almacen_id) {
                return back()->withErrors(['error' => 'El almacen de origen y destino no pueden ser iguales.'])->withInput();
            }

            $traslado = \App\Models\Traslado::create([
                'origen_almacen_id' => $request->origen_almacen_id,
                'destino_almacen_id' => $request->destino_almacen_id,
                'fecha_hora' => $request->fecha_hora,
                'costo_envio' => $request->costo_envio,
                'user_id' => auth()->id(),
                'estado' => 1,
            ]);

            foreach ($request->arrayidproducto as $index => $productoId) {
                $traslado->detalles()->create([
                    'producto_id' => $productoId,
                    'cantidad' => $request->arraycantidad[$index],
                ]);
            }

            DB::commit();
            return redirect()->route('traslados.index')->with('success', 'Traslado creado correctamente.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error al crear el traslado: '.$e->getMessage()])->withInput();
        }
    }

    public function toggleEstado(Request $request, Traslado $traslado)
    {
        $request->validate([
            'estado' => 'required|in:1,2,3',
        ]);

        DB::beginTransaction();

        try {
            $estadoAnterior = $traslado->estado;
            $estadoNuevo = $request->estado;

            // Solo procesar inventario si cambia a COMPLETADO (estado 2)
            if ($estadoAnterior != 2 && $estadoNuevo == 2) {
                $this->procesarInventarioTraslado($traslado);
            }

            $traslado->estado = $estadoNuevo;
            $traslado->save();

            DB::commit();

            return redirect()->back()->with('success', 'Traslado marcado como completado correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al actualizar el estado: ' . $e->getMessage());
        }
    }

    /**
     * Procesar el traslado de inventario cuando se marca como completado
     */
    protected function procesarInventarioTraslado(Traslado $traslado)
    {
        $traslado->load('detalles');

        foreach ($traslado->detalles as $detalle) {
            // Restar del almacen origen
            $inventarioOrigen = InventarioAlmacen::where('producto_id', $detalle->producto_id)
                ->where('almacen_id', $traslado->origen_almacen_id)
                ->first();

            if ($inventarioOrigen) {
                $inventarioOrigen->decrement('stock', $detalle->cantidad);
            }

            // Sumar al almacen destino
            $inventarioDestino = InventarioAlmacen::firstOrCreate(
                [
                    'producto_id' => $detalle->producto_id,
                    'almacen_id' => $traslado->destino_almacen_id
                ],
                ['stock' => 0]
            );

            $inventarioDestino->increment('stock', $detalle->cantidad);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Traslado $traslado)
    {
        $user = auth()->user();

        // Validar que el usuario pueda editar este traslado
        if (!$this->canAccessAlmacen($traslado->origen_almacen_id)) {
            return redirect()->route('traslados.index')
                ->with('error', 'No tienes permiso para editar este traslado');
        }

        // Solo permitir editar si está en estado Pendiente (1)
        if ($traslado->estado != 1) {
            return redirect()->route('traslados.index')
                ->with('error', 'Solo puedes editar traslados en estado Pendiente');
        }

        $traslado->load(['detalles.producto.inventarios']);
        
        // Si es trabajador, mostrar solo su almacen como origen
        if ($user->almacen_id) {
            $almacenes = Almacen::where('id', $user->almacen_id)->get();
            $almacenesDestino = $this->getDestinationAlmacens();
        } else {
            $almacenes = Almacen::all();
            $almacenesDestino = Almacen::where('estado', true)->get();
        }
        
        $productos = Producto::with('inventarios')
            ->where('estado', 1)
            ->get();

        return view('admin.traslado.edit', compact('traslado', 'almacenes', 'almacenesDestino', 'productos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTrasladoRequest $request, Traslado $traslado)
    {
        // Validar que el usuario pueda editar este traslado
        if (!$this->canAccessAlmacen($traslado->origen_almacen_id)) {
            return redirect()->route('traslados.index')
                ->with('error', 'No tienes permiso para editar este traslado');
        }

        // Solo permitir editar si está en estado Pendiente (1)
        if ($traslado->estado != 1) {
            return redirect()->route('traslados.index')
                ->with('error', 'Solo puedes editar traslados en estado Pendiente');
        }

        DB::beginTransaction();

        try {
            // Actualizar datos del traslado
            $traslado->update([
                'origen_almacen_id' => $request->origen_almacen_id,
                'destino_almacen_id' => $request->destino_almacen_id,
                'fecha_hora' => $request->fecha_hora,
                'costo_envio' => $request->costo_envio,
            ]);

            // Eliminar detalles anteriores
            $traslado->detalles()->delete();

            // Crear nuevos detalles
            foreach ($request->arrayidproducto as $index => $productoId) {
                $traslado->detalles()->create([
                    'producto_id' => $productoId,
                    'cantidad' => $request->arraycantidad[$index],
                ]);
            }

            DB::commit();
            return redirect()->route('traslados.index')->with('success', 'Traslado actualizado correctamente.');

        } catch (Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error al actualizar el traslado: '.$e->getMessage()])->withInput();
        }
    }

    public function destroy(Traslado $traslado)
    {
        // Validar que el usuario pueda eliminar este traslado
        if (!$this->canAccessAlmacen($traslado->origen_almacen_id)) {
            return redirect()->route('traslados.index')
                ->with('error', 'No tienes permiso para eliminar este traslado');
        }

        DB::beginTransaction();

        try {
            // Eliminar detalles primero (por integridad referencial)
            $traslado->detalles()->delete();

            // Eliminar el traslado
            $traslado->delete();

            DB::commit();
            
            return redirect()->route('traslados.index')->with('success', 'Traslado eliminado correctamente.');

        } catch (Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al eliminar el traslado: ' . $e->getMessage());
        }
    }

    /**
     * Exportar traslados a Excel
     */
    public function exportarExcel(Request $request)
    {
        try {
            $trasladoIds = $request->input('traslado_ids', []);
            // Convertir strings a booleanos
            $includeDetalles = filter_var($request->input('includeDetalles', true), FILTER_VALIDATE_BOOLEAN);
            $includeCosto = filter_var($request->input('includeCosto', true), FILTER_VALIDATE_BOOLEAN);
            $includeUsuario = filter_var($request->input('includeUsuario', true), FILTER_VALIDATE_BOOLEAN);

            // Si hay IDs seleccionados, usar esos; si no, exportar todos
            if (!empty($trasladoIds)) {
                $query = Traslado::with(['origenAlmacen', 'destinoAlmacen', 'user', 'detalles.producto'])
                    ->whereIn('id', $trasladoIds);
            } else {
                $query = Traslado::with(['origenAlmacen', 'destinoAlmacen', 'user', 'detalles.producto']);
            }

            // Filtrar por almacen del usuario
            $query = $this->filterByUserAlmacen($query, 'origen_almacen_id');

            $traslados = $query->orderBy('fecha_hora', 'desc')->get();

            if ($traslados->isEmpty()) {
                return back()->with('warning', 'No hay traslados para exportar');
            }

            $filename = 'traslados_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            
            return Excel::download(
                new TrasladosExport($traslados, $includeDetalles, $includeCosto, $includeUsuario),
                $filename
            );

        } catch (\Exception $e) {
            return back()->with('error', 'Error al exportar traslados: ' . $e->getMessage());
        }
    }

    /**
     * Exportar traslados a PDF
     */
    public function exportarPdf(Request $request)
    {
        $trasladoIds = $request->input('traslado_ids', []);
        // Convertir strings a booleanos
        $includeDetalles = filter_var($request->input('includeDetalles', true), FILTER_VALIDATE_BOOLEAN);
        $includeCosto = filter_var($request->input('includeCosto', true), FILTER_VALIDATE_BOOLEAN);
        $includeUsuario = filter_var($request->input('includeUsuario', true), FILTER_VALIDATE_BOOLEAN);

        // Si hay IDs seleccionados, usar esos; si no, exportar todos
        if (!empty($trasladoIds)) {
            $query = Traslado::with(['origenAlmacen', 'destinoAlmacen', 'user', 'detalles.producto'])
                ->whereIn('id', $trasladoIds);
        } else {
            $query = Traslado::with(['origenAlmacen', 'destinoAlmacen', 'user', 'detalles.producto']);
        }

        // Filtrar por almacen del usuario
        $query = $this->filterByUserAlmacen($query, 'origen_almacen_id');

        $traslados = $query->orderBy('fecha_hora', 'desc')->get();

        if ($traslados->isEmpty()) {
            return back()->with('warning', 'No hay traslados para exportar');
        }

        $pdf = Pdf::loadview('admin.traslado.pdf', compact('traslados', 'includeDetalles', 'includeCosto', 'includeUsuario'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('traslados_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Obtener detalles de un traslado (para cargar en modal)
     */
    public function getDetalles(Traslado $traslado)
    {
        // Validar que el usuario pueda ver este traslado
        if (!$this->canAccessAlmacen($traslado->origen_almacen_id)) {
            return response()->json(['error' => 'No tienes permiso para ver este traslado'], 403);
        }

        $traslado->load(['detalles.producto', 'origenAlmacen', 'destinoAlmacen', 'user']);

        return response()->json([
            'traslado' => [
                'id' => $traslado->id,
                'fecha_hora' => $traslado->fecha_hora->format('d/m/Y H:i:s'),
                'origen' => $traslado->origenAlmacen?->nombre ?? 'N/A',
                'destino' => $traslado->destinoAlmacen?->nombre ?? 'N/A',
                'usuario' => $traslado->user?->name ?? 'N/A',
                'costo_envio' => number_format($traslado->costo_envio, 2),
                'estado' => $traslado->estado,
                'estado_text' => match($traslado->estado) {
                    1 => 'Pendiente',
                    2 => 'Completado',
                    3 => 'Cancelado',
                    default => 'Desconocido',
                },
                'estado_color' => match($traslado->estado) {
                    1 => 'warning',
                    2 => 'success',
                    3 => 'danger',
                    default => 'secondary',
                },
            ],
            'detalles' => $traslado->detalles->map(function($detalle) {
                return [
                    'producto_nombre' => $detalle->producto?->nombre ?? 'Producto eliminado',
                    'producto_codigo' => $detalle->producto?->codigo ?? null,
                    'cantidad' => $detalle->cantidad,
                ];
            }),
            'total_cantidad' => $traslado->detalles->sum('cantidad'),
        ]);
    }

}