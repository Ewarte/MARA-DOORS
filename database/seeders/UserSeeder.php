<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Almacen;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{

    public function run(): void
    {
        // Obtener almacén CENTRAL
        $almacenCentral = Almacen::where('codigo', 'CENTRAL')->first();

        $user = User::create([
            'name' => 'Administrador',
            'email' => 'administrador@gmail.com',
            'almacen_id' => null,  // Admin sin almacén específico, ve TODO
            'password' => bcrypt('12345678')
        ]);

        //Usuario administrador
        $rol = Role::firstOrCreate(['name' => 'ADMINISTRADOR']);
        $permisos = Permission::pluck('id','id')->all();
        $rol->syncPermissions($permisos);
        $user->assignRole('ADMINISTRADOR');

        // Crear rol de Trabajador Almacén con permisos limitados
        $rolTrabajador = Role::firstOrCreate(['name' => 'Trabajador Almacén']);
        $permisosTrabajador = Permission::whereIn('name', [
            'ver-traslado', 'crear-traslado', 'editar-traslado', 'eliminar-traslado',
            'ver-venta', 'crear-venta', 'editar-venta', 'eliminar-venta','update-estadoTraslado',
            'ver-compra', 'crear-compra', 'editar-compra', 'eliminar-compra',
            'ver-producto', 'crear-producto', 'editar-producto', 'eliminar-producto',
        ])->pluck('id','id')->all();
        $rolTrabajador->syncPermissions($permisosTrabajador);
    }
}
