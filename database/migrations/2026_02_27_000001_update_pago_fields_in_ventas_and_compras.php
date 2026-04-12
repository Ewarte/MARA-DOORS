<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (!Schema::hasColumn('ventas', 'metodo_pago')) {
                $table->enum('metodo_pago', ['efectivo', 'debito', 'qr', 'deposito'])->default('efectivo')->after('total');
            }
            if (!Schema::hasColumn('ventas', 'monto_pagado')) {
                $table->decimal('monto_pagado', 10, 2)->unsigned()->default(0.00)->after('metodo_pago');
            }
        });

        Schema::table('compras', function (Blueprint $table) {
            if (!Schema::hasColumn('compras', 'metodo_pago')) {
                $table->enum('metodo_pago', ['efectivo', 'debito', 'qr', 'deposito'])->default('efectivo')->after('total');
            }
            if (!Schema::hasColumn('compras', 'monto_pagado')) {
                $table->decimal('monto_pagado', 10, 2)->unsigned()->default(0.00)->after('metodo_pago');
            }
        });

        // Expandir enum estado_pago (se usa en controladores/vistas para cancelado/anulado y pagos parciales)
        DB::statement("ALTER TABLE `ventas` MODIFY `estado_pago` ENUM('pendiente','parcial','pagado','cancelado','anulado') NOT NULL DEFAULT 'pendiente'");
        DB::statement("ALTER TABLE `compras` MODIFY `estado_pago` ENUM('pendiente','parcial','pagado','cancelado','anulado') NOT NULL DEFAULT 'pendiente'");

        // Backfill monto_pagado segun estado actual
        DB::statement("UPDATE `ventas` SET `monto_pagado` = `total` WHERE `estado_pago` = 'pagado' AND (`monto_pagado` IS NULL OR `monto_pagado` = 0)");
        DB::statement("UPDATE `compras` SET `monto_pagado` = `total` WHERE `estado_pago` = 'pagado' AND (`monto_pagado` IS NULL OR `monto_pagado` = 0)");
    }

    public function down(): void
    {
        // Revertir enum a version anterior (sin parcial/cancelado/anulado)
        DB::statement("ALTER TABLE `ventas` MODIFY `estado_pago` ENUM('pagado','pendiente') NOT NULL DEFAULT 'pendiente'");
        DB::statement("ALTER TABLE `compras` MODIFY `estado_pago` ENUM('pagado','pendiente') NOT NULL DEFAULT 'pendiente'");

        Schema::table('ventas', function (Blueprint $table) {
            if (Schema::hasColumn('ventas', 'monto_pagado')) $table->dropColumn('monto_pagado');
            if (Schema::hasColumn('ventas', 'metodo_pago')) $table->dropColumn('metodo_pago');
        });

        Schema::table('compras', function (Blueprint $table) {
            if (Schema::hasColumn('compras', 'monto_pagado')) $table->dropColumn('monto_pagado');
            if (Schema::hasColumn('compras', 'metodo_pago')) $table->dropColumn('metodo_pago');
        });
    }
};

