<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;
    protected $table = 'documentos';

    protected $fillable = ['tipo_documento'];
    public $timestamps = false; 

    public function personas()
    {
        return $this->hasMany(Persona::class, 'documento_id');
    }
}
