<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;
    protected $table = 'compras';

    protected $casts = [
        'total' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'fecha_hora' => 'datetime',
    ];

    protected $fillable = [
        'fecha_hora',
        'numero_comprobante',
        'total',
        'metodo_pago',
        'monto_pagado',
        'costo_transporte',
        'nota_personal',
        'estado_pago',
        'estado_entrega',
        'estado',
        'comprobante_id',
        'proveedor_id',
        'almacen_id',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class, 'compra_id');
    }

    public function comprobante()
    {
        return $this->belongsTo(Comprobante::class, 'comprobante_id');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    public function getSaldoAttribute(): float
    {
        $total = (float) ($this->total ?? 0);
        $pagado = (float) ($this->monto_pagado ?? 0);
        return max(0.0, $total - $pagado);
    }
}
