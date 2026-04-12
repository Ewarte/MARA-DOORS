<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{ 
    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_hora');
            $table->string('numero_comprobante');
            $table->decimal('total', 10, 2)->unsigned(); // UNSIGNED requested
            $table->decimal('costo_transporte', 10, 2)->default(0.00);
            $table->text('nota_personal')->nullable();
            $table->enum('estado_pago', ['pagado', 'pendiente'])->default('pendiente');
            $table->enum('estado_entrega', ['entregado', 'por_entregar'])->default('por_entregar');
            $table->tinyInteger('estado')->default(1);
            
            $table->foreignId('comprobante_id')->nullable()->constrained('comprobantes')->onDelete('set null');
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->onDelete('set null');
            $table->foreignId('almacen_id')->constrained('almacenes')->onDelete('restrict');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('compras');
    }
};
