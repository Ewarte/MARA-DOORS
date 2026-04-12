<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoUnidad;

class TipoUnidadSeeder extends Seeder
{
    public function run()
    {
        $tipos = [
            [
                'nombre' => 'Unidad',
                'descripcion' => 'Productos que se venden por unidad (piezas)',
                'maneja_stock' => true
            ],
            [
                'nombre' => 'Metro',
                'descripcion' => 'Productos que se venden por metro (cables, perfiles)',
                'maneja_stock' => true
            ],
            [
                'nombre' => 'Docena',
                'descripcion' => 'Conjunto de 12 unidades',
                'maneja_stock' => true
            ],
            [
                'nombre' => 'Servicio',
                'descripcion' => 'Servicios intangibles (cortes, instalación, fletes)',
                'maneja_stock' => false
            ],
            [
                'nombre' => 'Juego/Kit',
                'descripcion' => 'Conjunto de piezas que forman un item',
                'maneja_stock' => true
            ]
        ];

        foreach ($tipos as $tipo) {
            TipoUnidad::create($tipo);
        }
    }
}
