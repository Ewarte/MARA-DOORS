<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaPago extends Model
{
    use HasFactory;

    protected $table = 'venta_pagos';

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    protected $fillable = [
        'venta_id',
        'metodo_pago',
        'monto',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}

