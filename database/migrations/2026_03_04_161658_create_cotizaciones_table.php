<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_hora');
            $table->string('numero_cotizacion');
            $table->decimal('total', 10, 2)->unsigned();
            $table->enum('estado', ['pendiente', 'venta_realizada', 'compra_realizada', 'anulado'])->default('pendiente');
            
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->onDelete('set null');
            $table->foreignId('almacen_id')->constrained('almacenes')->onDelete('restrict');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->date('vencimiento')->nullable();
            $table->text('nota_personal')->nullable();
            $table->text('nota_cliente')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
