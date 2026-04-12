<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoCliente extends Model
{
    use HasFactory;

    protected $table = 'grupos_clientes';

    protected $fillable = ['nombre','descripcion','descuento_global','estado'];
    
    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'grupo_cliente_id');
    }
}
