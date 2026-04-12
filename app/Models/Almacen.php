<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    use HasFactory;
    protected $table = 'almacenes';

    protected $fillable = [
        'codigo', 'nombre', 'descripcion', 'direccion', 'estado'
    ];

    protected $casts = [
        'estado' => 'boolean'
    ];

   public function inventarios()
    {
        return $this->hasMany(InventarioAlmacen::class, 'almacen_id');
    }
}
