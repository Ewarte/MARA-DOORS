<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAlmacenRequest;
use App\Http\Requests\UpdateAlmacenRequest;
use App\Models\Almacen;
use App\Models\Producto;
use App\Models\InventarioAlmacen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlmacenController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-almacen', ['only' => ['index']]);
        $this->middleware('permission:crear-almacen', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-almacen', ['only' => ['edit', 'update']]);
        $this->middleware('permission:dar-de-baja-almacen', ['only' => ['destroy']]);
    }

    public function index()
    {
        $almacenes = Almacen::orderBy('nombre')->get();
        return view('admin.almacen.index', compact('almacenes'));
    }

    public function create()
    {
        return view('admin.almacen.create');
    }

    public function store(StoreAlmacenRequest $request)
    {
        DB::beginTransaction();

        try {
            // Crear el almacén
            $almacen = Almacen::create($request->validated());

            // Obtener todos los productos activos
            $productos = Producto::where('estado', 1)->get();

            // Crear registros en inventario_almacen para cada producto con stock 0
            foreach ($productos as $producto) {
                InventarioAlmacen::create([
                    'almacen_id' => $almacen->id,
                    'producto_id' => $producto->id,
                    'stock' => 0,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('almacenes.index')
                ->with('success', 'Almacén registrado correctamente con todos los productos integrados');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error al crear el almacén: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit(Almacen $almacen)
    {
        return view('admin.almacen.edit', compact('almacen'));
    }



    public function update(UpdateAlmacenRequest $request, Almacen $almacen)
    {

        $almacen->update($request->validated());

        return redirect()
            ->route('almacenes.index')
            ->with('success', 'Almacén actualizado correctamente');
    }

    public function updateEstado(Almacen $almacen)
    {
        $almacen->estado = ! $almacen->estado;
        $almacen->save();

        $mensaje = $almacen->estado
            ? 'Almacén activado correctamente'
            : 'Almacén desactivado correctamente';

        return redirect()->route('almacenes.index')->with('success', $mensaje);
    }


}
