<?php

namespace Database\Seeders;

use App\Models\Marca;
use Illuminate\Database\Seeder;

class MarcaSeeder extends Seeder
{

    public function run(): void
    {
        Marca::create([
            'nombre' => 'Marca General',
            'descripcion' => 'Marca por defecto para productos sin marca asignada'
        ]);
    }
}
