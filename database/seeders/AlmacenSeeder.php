<?php

namespace Database\Seeders;

use App\Models\Almacen;
use Illuminate\Database\Seeder;

class AlmacenSeeder extends Seeder
{
    
    public function run(): void
    {
        Almacen::create([
            'codigo' => 'CENTRAL',
            'nombre' => 'Almacén Central',
            'descripcion' => 'Almacén central principal',
            'direccion' => 'Dirección Central',
            'estado' => true
        ]);
    }
}
