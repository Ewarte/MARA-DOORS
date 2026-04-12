<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{

    use HasFactory;
    protected $table = 'clientes';

    protected $fillable = ['persona_id', 'grupo_cliente_id'];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function grupo()
    {
        return $this->belongsTo(GrupoCliente::class, 'grupo_cliente_id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'cliente_id');
    }
}
