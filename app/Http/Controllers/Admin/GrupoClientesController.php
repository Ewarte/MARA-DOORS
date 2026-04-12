<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGrupoClienteRequest;
use App\Http\Requests\UpdateGrupoClienteRequest;
use App\Models\GrupoCliente;

class GrupoClientesController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-grupocliente', ['only' => ['index']]);
        $this->middleware('permission:crear-grupocliente', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-grupocliente', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-grupocliente', ['only' => ['destroy']]);
    }

    public function index()
    {
        $grupoclientes = GrupoCliente::orderBy('nombre')->get();
        return view('admin.grupocliente.index', compact('grupoclientes'));
    }

    public function create()
    {
        return view('admin.grupocliente.create');
    }

    public function store(StoreGrupoClienteRequest $request)
    {
        GrupoCliente::create($request->validated());

        return redirect()
            ->route('grupoclientes.index')
            ->with('success', 'Grupo de clientes registrado correctamente');
    }

    public function edit(GrupoCliente $grupocliente)
    {
        return view('admin.grupocliente.edit', compact('grupocliente'));
    }

    public function update(UpdateGrupoClienteRequest $request, GrupoCliente $grupocliente)
    {
        $grupocliente->update($request->validated());

        return redirect()
            ->route('grupoclientes.index')
            ->with('success', 'Grupo de clientes actualizado correctamente');
    }

    public function destroy(GrupoCliente $grupocliente)
    {
        // Validar si tiene clientes relacionados
        if ($grupocliente->clientes()->exists()) {
            return redirect()
                ->route('grupoclientes.index')
                ->with('error', 'No se puede eliminar el grupo porque tiene clientes asociados.');
        }

        $grupocliente->delete();

        return redirect()
            ->route('grupoclientes.index')
            ->with('success', 'Grupo eliminado correctamente.');
    }

}
