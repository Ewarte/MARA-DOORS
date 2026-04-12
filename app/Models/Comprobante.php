<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    use HasFactory;

    protected $table = 'comprobantes';
    public $timestamps = true;

    protected $fillable = ['tipo_comprobante'];

    public function compra()
    {
        return $this->hasMany(Compra::class, 'comprobante_id');
    }
}