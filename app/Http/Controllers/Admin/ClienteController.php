<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\GrupoCliente;
use App\Models\Persona;
use Exception;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-cliente|crear-cliente|editar-cliente|eliminar-cliente', ['only' => ['index']]);
        $this->middleware('permission:crear-cliente', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-cliente', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-cliente', ['only' => ['destroy', 'changeState']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $busqueda = trim((string) $request->get('busqueda', ''));
        $perPage  = $request->get('per_page', 10);
        $sort = $request->get('sort', 'razon_social');
        $direction = $request->get('direction', 'asc');

        if (!in_array($perPage, [5, 10, 15, 20, 25])) $perPage = 10;
        if (!in_array($direction, ['asc', 'desc'])) $direction = 'asc';
        if (!in_array($sort, ['id', 'razon_social', 'grupo', 'telefono', 'documento', 'tipo_persona', 'estado'])) {
            $sort = 'razon_social';
        }

        $query = Cliente::query()
            ->with(['persona.documento', 'grupo'])
            ->join('personas', 'clientes.persona_id', '=', 'personas.id')
            ->leftJoin('documentos', 'personas.documento_id', '=', 'documentos.id')
            ->leftJoin('grupos_clientes', 'clientes.grupo_cliente_id', '=', 'grupos_clientes.id')
            ->select('clientes.*');

        if ($busqueda !== '') {
            $query->where(function ($q) use ($busqueda) {
                $q->where('personas.razon_social', 'like', "%{$busqueda}%")
                    ->orWhere('personas.direccion', 'like', "%{$busqueda}%")
                    ->orWhere('personas.telefono', 'like', "%{$busqueda}%")
                    ->orWhere('personas.numero_documento', 'like', "%{$busqueda}%")
                    ->orWhere('personas.tipo_persona', 'like', "%{$busqueda}%")
                    ->orWhere('documentos.tipo_documento', 'like', "%{$busqueda}%")
                    ->orWhere('grupos_clientes.nombre', 'like', "%{$busqueda}%")
                    ->orWhere('grupos_clientes.descripcion', 'like', "%{$busqueda}%");

                if (is_numeric($busqueda)) {
                    $q->orWhere('clientes.id', (int) $busqueda);
                }
            });
        }

        switch ($sort) {
            case 'id':
                $query->orderBy('clientes.id', $direction);
                break;
            case 'razon_social':
                $query->orderBy('personas.razon_social', $direction);
                break;
            case 'grupo':
                $query->orderBy('grupos_clientes.nombre', $direction);
                break;
            case 'telefono':
                $query->orderBy('personas.telefono', $direction);
                break;
            case 'documento':
                $query->orderBy('personas.numero_documento', $direction);
                break;
            case 'tipo_persona':
                $query->orderBy('personas.tipo_persona', $direction);
                break;
            case 'estado':
                $query->orderBy('personas.estado', $direction);
                break;
        }

        $clientes = $query->paginate($perPage);

        $totalClientes = Cliente::count();
        $clientesActivos = Cliente::whereHas('persona', fn($q) => $q->where('estado', 1))->count();
        $clientesInactivos = $totalClientes - $clientesActivos;

        if ($request->ajax()) {
            return view('admin.cliente.index', compact(
                'clientes',
                'busqueda',
                'perPage',
                'sort',
                'direction',
                'totalClientes',
                'clientesActivos',
                'clientesInactivos'
            ));
        }

        return view('admin.cliente.index', compact(
            'clientes',
            'busqueda',
            'perPage',
            'sort',
            'direction',
            'totalClientes',
            'clientesActivos',
            'clientesInactivos'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            DB::beginTransaction();
            $documentos = Documento::all();
            $grupos = GrupoCliente::where('estado', 1)->orderBy('nombre')->get();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
        return view('admin.cliente.create', compact('documentos', 'grupos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClienteRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $grupoClienteId = $validated['grupo_cliente_id'];
            unset($validated['grupo_cliente_id']);

            $persona = Persona::create($validated);
            $persona->cliente()->create([
                'persona_id' => $persona->id,
                'grupo_cliente_id' => $grupoClienteId,
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }

        return redirect()->route('clientes.index')->with('success', 'Cliente registrado');
    }

    public function edit(Cliente $cliente)
    {
        $cliente->load(['persona.documento', 'grupo']);
        $documentos = Documento::all();
        $grupos = GrupoCliente::where('estado', 1)->orderBy('nombre')->get();
        return view('admin.cliente.edit', compact('cliente', 'documentos', 'grupos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $grupoClienteId = $validated['grupo_cliente_id'];
            unset($validated['grupo_cliente_id']);

            Persona::where('id', $cliente->persona->id)->update($validated);
            $cliente->update(['grupo_cliente_id' => $grupoClienteId]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }

        return redirect()->route('clientes.index')->with('success', 'Cliente editado');
    }

    /**
     * Remove the specified resource from storage.
     */
    //funcion para eliminar cliente de la base de datos
    public function destroy(string $id)
    {
        $persona = Persona::find($id);
        $persona->cliente()->delete();
        $persona->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado');
    }
    // funcion para cambiar estado de cliente activo o inactivo
    public function changeState(string $id)
    {
        $persona = Persona::find($id);
        if ($persona->estado == 1) {
            Persona::where('id', $persona->id)
                ->update([
                    'estado' => 0
                ]);
            $message = 'Cliente desactivado';
        } else {
            Persona::where('id', $persona->id)
                ->update([
                    'estado' => 1
                ]);
            $message = 'Cliente activado';
        }

        return redirect()->route('clientes.index')->with('success', $message);
    }
}



