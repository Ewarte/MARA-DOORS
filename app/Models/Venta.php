<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    use HasFactory;
    protected $table = 'ventas';

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
        'estado_pago', 
        'estado_entrega', 
        'estado', 
        'cliente_id', 
        'grupo_cliente_id', 
        'almacen_id', 
        'user_id', 
        'comprobante_id',
        'nota_personal',
        'nota_cliente'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comprobante()
    {
        return $this->belongsTo(Comprobante::class, 'comprobante_id');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(VentaPago::class, 'venta_id');
    }

    public function getSaldoAttribute(): float
    {
        $total = (float) ($this->total ?? 0);
        $pagado = $this->relationLoaded('pagos')
            ? (float) $this->pagos->sum('monto')
            : (float) ($this->monto_pagado ?? 0);
        return max(0.0, $total - $pagado);
    }
}
