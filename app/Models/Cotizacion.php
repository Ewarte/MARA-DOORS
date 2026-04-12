<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    use HasFactory;
    protected $table = 'cotizaciones';

    protected $casts = [
        'total' => 'decimal:2',
        'fecha_hora' => 'datetime',
        'vencimiento' => 'date',
    ];

    protected $fillable = [
        'fecha_hora',
        'numero_cotizacion',
        'total',
        'estado',
        'cliente_id',
        'proveedor_id',
        'almacen_id',
        'user_id',
        'vencimiento',
        'nota_personal',
        'nota_cliente'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCotizacion::class, 'cotizacion_id');
    }
}
