<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Models\Categoria;
use Exception;
use Illuminate\Support\Facades\DB;

class CategoriaController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:ver-categoria', ['only' => ['index']]);
        $this->middleware('permission:crear-categoria', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-categoria', ['only' => ['edit', 'update']]);
    }

    public function index()
    {
        $categorias = Categoria::orderBy('nombre')->get();
        return view('admin.categoria.index', compact('categorias'));
    }

    public function create()
    {
        return view('admin.categoria.create');
    }

    public function store(StoreCategoriaRequest $request)
    {

        Categoria::create($request->validated());

        return redirect()
            ->route('categorias.index')
            ->with('success', 'Categoría registrada correctamente');
    }

    public function edit(Categoria $categoria)
    {
        return view('admin.categoria.edit', ['categoria' => $categoria]);
    }

   
    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {

        $categoria->update($request->validated());

        return redirect()
            ->route('categorias.index')
            ->with('success', 'Categoría actualizada correctamente');
    }

}



