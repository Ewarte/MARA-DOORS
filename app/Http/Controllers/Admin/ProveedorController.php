<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePersonaRequest;
use App\Http\Requests\UpdateProveedoreRequest;
use App\Models\Documento;
use App\Models\Persona;
use App\Models\Proveedor;
use Exception;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-proveedor|crear-proveedor|editar-proveedor|dar-de-baja-proveedor', ['only' => ['index']]);
        $this->middleware('permission:crear-proveedor', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-proveedor', ['only' => ['edit', 'update']]);
        $this->middleware('permission:dar-de-baja-proveedor', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request)
    {
        try {
            $busqueda = $request->get('busqueda');
            $perPage  = $request->get('per_page', 10);

            if (!in_array($perPage, [5, 10, 15, 20, 25])) $perPage = 10;

            $query = Proveedor::with('persona.documento');

            if ($busqueda) {
                $query->whereHas('persona', function ($q) use ($busqueda) {
                    $q->where('razon_social', 'like', "%{$busqueda}%")
                        ->orWhere('direccion', 'like', "%{$busqueda}%")
                        ->orWhere('telefono', 'like', "%{$busqueda}%")
                        ->orWhere('email', 'like', "%{$busqueda}%")
                        ->orWhere('numero_documento', 'like', "%{$busqueda}%")
                        ->orWhere('tipo_persona', 'like', "%{$busqueda}%");
                })->orWhereHas('persona.documento', function ($q) use ($busqueda) {
                    $q->where('tipo_documento', 'like', "%{$busqueda}%");
                });
            }

            $proveedores = $query->paginate($perPage);

            $totalProveedores = Proveedor::count();
            $proveedoresActivos = Proveedor::whereHas('persona', fn($q) => $q->where('estado', 1))->count();
            $proveedoresInactivos = $totalProveedores - $proveedoresActivos;

            if ($request->ajax()) {
                return view('admin.proveedor.index', compact(
                    'proveedores',
                    'busqueda',
                    'perPage',
                    'totalProveedores',
                    'proveedoresActivos',
                    'proveedoresInactivos'
                ));
            }

            return view('admin.proveedor.index', compact(
                'proveedores',
                'busqueda',
                'perPage',
                'totalProveedores',
                'proveedoresActivos',
                'proveedoresInactivos'
            ));
        } catch (Exception $e) {
            return redirect()->route('proveedores.index')->with('error', 'Error al mostrar proveedores');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $documentos = Documento::all();
            return view('admin.proveedor.create', compact('documentos'));
        } catch (Exception $e) {
            return redirect()->route('proveedores.index')->with('error', 'Error al abrir el formulario de creacion');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePersonaRequest $request)
    {
        try {
            DB::beginTransaction();
            $persona = Persona::create($request->validated());
            $persona->proveedor()->create([
                'persona_id' => $persona->id
            ]);
            DB::commit();
            return redirect()->route('proveedores.index')->with('success', 'Proveedor registrado correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al registrar el proveedor: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $proveedor = Proveedor::with('persona.documento')->find($id);
            return view('admin.proveedor.show', compact('proveedor'));
        } catch (Exception $e) {
            return redirect()->route('proveedores.index')->with('error', 'Error al mostrar el proveedor');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Proveedor $proveedore)
    {
        try {
            $proveedore->load('persona.documento');
            $documentos = Documento::all();
            return view('admin.proveedor.edit', compact('proveedore', 'documentos'));
        } catch (Exception $e) {
            return redirect()->route('proveedores.index')->with('error', 'Error al mostrar el proveedor para editar');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProveedoreRequest $request, Proveedor $proveedore)
    {
        try {
            DB::beginTransaction();

            Persona::where('id', $proveedore->persona->id)
                ->update($request->validated());

            DB::commit();
            return redirect()->route('proveedores.index')->with('success', 'Proveedor editado correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al editar el proveedor: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $message = '';
            $persona = Persona::find($id);
            if ($persona->estado == 1) {
                Persona::where('id', $persona->id)
                    ->update([
                        'estado' => 0
                    ]);
                $message = 'Proveedor desactivado';
            } else {
                Persona::where('id', $persona->id)
                    ->update([
                        'estado' => 1
                    ]);
                $message = 'Proveedor restaurado';
            }

            return redirect()->route('proveedores.index')->with('success', $message);
        } catch (Exception $e) {
            return redirect()->route('proveedores.index')->with('error', 'Error al cambiar el estado del proveedor');
        }
    }
}



