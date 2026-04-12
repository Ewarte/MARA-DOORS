<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleTraslado extends Model
{
    use HasFactory;

    protected $table = 'detalle_traslados';
    public $incrementing = false; // PK compuesta
    public $timestamps = true;

    protected $fillable = ['producto_id', 'traslado_id', 'cantidad'];

    public function traslado()
    {
        return $this->belongsTo(Traslado::class, 'traslado_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
