<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AjusteStock extends Model
{
    use HasFactory;

    protected $table = 'ajustes_stock';

    protected $fillable = [
        'producto_id',
        'almacen_id',
        'user_id',
        'cantidad_anterior',
        'cantidad_nueva',
        'motivo'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
