<?php

namespace Database\Seeders;

use App\Models\GrupoCliente;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GrupoClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grupoClientes = [
            [
                'nombre' => 'General',
                'descripcion' => 'Clientes estándar sin descuentos especiales. Aplicable a ventas minoristas o clientes ocasionales.',
                'descuento_global' => 0.00
            ],
            [
                'nombre' => 'Facturadores',
                'descripcion' => 'Clientes frecuentes que realizan compras al por mayor y requieren facturación continua. Acceden a un descuento preferencial.',
                'descuento_global' => 10.00
            ],
            [
                'nombre' => 'Ferreterias',
                'descripcion' => 'Ferreterías asociadas que compran productos para reventa. Reciben un descuento especial por volumen.',
                'descuento_global' => 5.00
            ]
        ];

        foreach ($grupoClientes as $grupo) {
            GrupoCliente::create($grupo);
        }
    }
}

