<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('razon_social', 80);
            $table->string('direccion', 80);
            $table->string('telefono', 20)->nullable();
            $table->string('tipo_persona', 20);
            $table->tinyInteger('estado')->default(1);
            $table->foreignId('documento_id')->constrained('documentos')->onDelete('restrict');
            $table->string('numero_documento', 20);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('personas');
    }
};
