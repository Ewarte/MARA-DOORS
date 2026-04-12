<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{

    public function run(): void
    {
        $permisos = [

            //Tipo de Unidad
            'ver-tipounidad',
            'crear-tipounidad',
            'editar-tipounidad',

            //Categorías
            'ver-categoria',
            'crear-categoria',
            'editar-categoria',

            //Almacen
            'ver-almacen',
            'crear-almacen',
            'editar-almacen',
            'dar-de-baja-almacen',

            //Marca
            'ver-marca',
            'crear-marca',
            'editar-marca',

            //Roles
            'ver-role',
            'crear-role',
            'editar-role',
            'eliminar-role',

            //Traslado
            'ver-traslado',
            'crear-traslado',
            'editar-traslado',
            'eliminar-traslado',
            'update-estadoTraslado',

            //Cliente
            'ver-cliente',
            'crear-cliente',
            'editar-cliente',
            'eliminar-cliente',

            //Proveedor
            'ver-proveedor',
            'crear-proveedor',
            'editar-proveedor',
            'dar-de-baja-proveedor',
            
            //Compra
            'ver-compra',
            'crear-compra',
            'editar-compra',
            'mostrar-compra',
            'eliminar-compra',
           
            //Producto
            'ver-producto',
            'crear-producto',
            'editar-producto',
            'update-estado-producto',
            'ajustar-stock',
            'eliminar-producto',
            'ver-historial-stock',

            //Venta
            'ver-venta',
            'crear-venta',
            'editar-venta',
            'mostrar-venta',
            'eliminar-venta',

            //Cotización
            'ver-cotizacion',
            'crear-cotizacion',
            'editar-cotizacion',
            'mostrar-cotizacion',
            'eliminar-cotizacion',

            //Ver GrupoCliente
            'ver-grupocliente',
            'crear-grupocliente',
            'editar-grupocliente',
            'eliminar-grupocliente',

            //User
            'ver-user',
            'crear-user',
            'editar-user',
            'eliminar-user',

            //Perfil
            'ver-perfil',
            'editar-perfil',
            // Panel
            'ver-panel',

        ];

        foreach ($permisos as $permiso) {
            Permission::create(['name' => $permiso]);
        }
    }
}
