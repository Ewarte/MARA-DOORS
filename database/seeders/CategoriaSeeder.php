<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{

    public function run(): void
    {
        Categoria::create([
            'nombre' => 'Categoría General',
            'descripcion' => 'Categoría por defecto para productos sin categoría asignada'
        ]);
    }
}
