<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Producto extends Model
{
    use HasFactory;
    protected $table = 'productos';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'precio_compra',
        'precio_venta',
        'estado',
        'marca_id',
        'categoria_id',
        'tipounidad_id'
    ];

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function tipounidad()
    {
        return $this->belongsTo(TipoUnidad::class, 'tipounidad_id');
    }

    public function inventarios()
    {
        return $this->hasMany(InventarioAlmacen::class, 'producto_id');
    }

    public function compras()
    {
        return $this->belongsToMany(Compra::class, 'detalle_compras')
                    ->withPivot('cantidad', 'precio_compra', 'precio_venta');
    }

    public function detalleCompras()
    {
        return $this->hasMany(DetalleCompra::class, 'producto_id');
    }

    public function detalleVentas()
    {
        return $this->hasMany(DetalleVenta::class, 'producto_id');
    }

    public function detalleTraslados()
    {
        return $this->hasMany(DetalleTraslado::class, 'producto_id');
    }

    public function almacenes()
    {
        return $this->belongsToMany(Almacen::class, 'inventario_almacenes')
                    ->withPivot('stock')
                    ->withTimestamps();
    }
    
    // Helper para obtener stock total
    public function getStockTotalAttribute()
    {
        return $this->inventarios()->sum('stock');
    }
}
