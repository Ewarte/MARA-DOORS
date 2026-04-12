<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCaracteristicaRequest;
use App\Http\Requests\UpdateMarcaRequest;
use App\Models\Marca;
use Illuminate\Support\Facades\DB;

class MarcaController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-marca', ['only' => ['index']]);
        $this->middleware('permission:crear-marca', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-marca', ['only' => ['edit', 'update']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $marcas = Marca::orderBy('nombre')->get();
        return view('admin.marca.index', compact('marcas'));
    }

    public function create()
    {
        return view('admin.marca.create');
    }

    public function store(StoreCaracteristicaRequest $request)
    {
        Marca::create($request->validated());

        return redirect()
            ->route('marcas.index')
            ->with('success', 'Marca registrada correctamente');
    }
    
    public function edit(Marca $marca)
    {
        return view('admin.marca.edit',compact('marca'));
    }

    
    public function update(UpdateMarcaRequest $request, Marca $marca)
    {
        $marca->update($request->validated());

        return redirect()
            ->route('marcas.index')
            ->with('success', 'Marca actualizada correctamente');
    }

}



