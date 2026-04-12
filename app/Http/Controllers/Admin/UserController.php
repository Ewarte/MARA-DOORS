<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Models\Almacen;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-user|crear-user|editar-user|eliminar-user', ['only' => ['index']]);
        $this->middleware('permission:crear-user', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-user', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-user', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return view('admin.user.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        $almacenes = Almacen::where('estado', true)->get();
        return view('admin.user.create', compact('roles', 'almacenes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            DB::beginTransaction();

            //Preparar datos del usuario
            $userData = $request->validated();
            $userData['password'] = Hash::make($userData['password']);
            
            //Crear usuario
            $user = User::create($userData);

            //Asignar su rol (obtenido directamente del request)
            $user->assignRole($request->input('role'));

            DB::commit();
            return redirect()->route('users.index')->with('success', 'Usuario registrado correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al crear usuario: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error al registrar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $almacenes = Almacen::where('estado', true)->get();
        return view('admin.user.edit', compact('user', 'roles', 'almacenes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            DB::beginTransaction();

            $userData = $request->validated();
            
            //Manejar password
            if (!empty($userData['password'])) {
                $userData['password'] = Hash::make($userData['password']);
            } else {
                unset($userData['password']);
            }

            $user->update($userData);

            //Actualizar rol (obtenido directamente del request)
            $user->syncRoles([$request->input('role')]);

            DB::commit();
            return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar usuario: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error al actualizar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        //Eliminar rol
        $rolUser = $user->getRoleNames()->first();
        $user->removeRole($rolUser);

        //Eliminar usuario
        $user->delete();

        return redirect()->route('users.index')->with('success','Usuario eliminado');
    }
}