<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;
use App\Models\Persona;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        // Crear o traer la persona
        $persona = Persona::firstOrCreate(
            [
                'numero_documento' => '0',
                'documento_id'     => 1,
            ],
            [
                'razon_social' => 'Cliente General',
                'direccion'    => 'Sin dirección',
                'telefono'     => '00000000',
                'tipo_persona' => 'natural',
                'estado'       => true,
            ]
        );

        // Crear o traer el cliente asociado
        Cliente::firstOrCreate(
            [
                'persona_id' => $persona->id,
            ],
            [
                'grupo_cliente_id' => 1,
            ]
        );
    }
}
