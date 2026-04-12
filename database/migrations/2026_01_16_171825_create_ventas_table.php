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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_hora');
            $table->string('numero_comprobante');
            $table->decimal('total', 10, 2)->unsigned();
            $table->enum('estado_pago', ['pagado', 'pendiente'])->default('pendiente');
            $table->enum('estado_entrega', ['entregado', 'por_entregar'])->default('por_entregar');
            $table->tinyInteger('estado')->default(1);
            
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->foreignId('grupo_cliente_id')->nullable()->constrained('grupos_clientes')->onDelete('set null');
            $table->foreignId('almacen_id')->constrained('almacenes')->onDelete('restrict');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('comprobante_id')->nullable()->constrained('comprobantes')->onDelete('set null');
            
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
        Schema::dropIfExists('ventas');
    }
};
