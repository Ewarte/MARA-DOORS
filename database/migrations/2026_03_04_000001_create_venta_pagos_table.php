<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venta_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->enum('metodo_pago', ['efectivo', 'debito', 'qr', 'deposito', 'otro'])->default('efectivo');
            $table->decimal('monto', 10, 2)->unsigned();
            $table->timestamps();

            $table->index(['venta_id', 'metodo_pago']);
        });

        // Permitir marcar ventas "mixto" a nivel resumen
        DB::statement("ALTER TABLE `ventas` MODIFY `metodo_pago` ENUM('efectivo','debito','qr','deposito','mixto') NOT NULL DEFAULT 'efectivo'");

        // Backfill: si ya existe monto_pagado/metodo_pago en ventas, crear un pago base
        $rows = DB::table('ventas')
            ->select('id', 'total', 'estado_pago', 'metodo_pago', 'monto_pagado')
            ->get();

        foreach ($rows as $row) {
            $monto = (float) ($row->monto_pagado ?? 0);
            if ($monto <= 0 && $row->estado_pago === 'pagado') {
                $monto = (float) $row->total;
            }
            if ($monto <= 0) continue;

            DB::table('venta_pagos')->insert([
                'venta_id' => $row->id,
                'metodo_pago' => $row->metodo_pago ?: 'efectivo',
                'monto' => $monto,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_pagos');
        DB::statement("ALTER TABLE `ventas` MODIFY `metodo_pago` ENUM('efectivo','debito','qr','deposito') NOT NULL DEFAULT 'efectivo'");
    }
};

