<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Traslado extends Model
{
    use HasFactory;
    protected $table = 'traslados';
    protected $fillable = ['fecha_hora', 'costo_envio', 'user_id', 'estado', 'origen_almacen_id', 'destino_almacen_id'];

    protected $casts = ['fecha_hora' => 'datetime',];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function origenAlmacen()
    {
        return $this->belongsTo(Almacen::class, 'origen_almacen_id');
    }

    public function destinoAlmacen()
    {
        return $this->belongsTo(Almacen::class, 'destino_almacen_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleTraslado::class, 'traslado_id');
    }
}
