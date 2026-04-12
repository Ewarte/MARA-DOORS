<?php

namespace Database\Seeders;

use App\Models\Comprobante;
use Illuminate\Database\Seeder;

class ComprobanteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Comprobante::insert([
            [
                'tipo_comprobante' => 'Factura',
            ],
            [
                'tipo_comprobante' => 'Boleta',
            ],
        ]);
    }
    // comando para hacer la semilla
    // php artisan db:seed --class=ComprobanteSeeder
}
