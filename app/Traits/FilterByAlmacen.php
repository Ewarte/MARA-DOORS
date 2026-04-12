<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FilterByAlmacen
{
    /**
     * Filtrar query por almacén del usuario autenticado
     * Si el usuario es admin (sin almacén_id), devuelve la query sin filtrar
     * Si el usuario tiene almacén_id, filtra solo los registros de su almacén
     */
    public function filterByUserAlmacen(Builder $query, $almacenColumn = 'almacen_id'): Builder
    {
        $user = auth()->user();
        
        // Si el usuario es admin (sin almacén asignado), devolver todos
        if (!$user->almacen_id) {
            return $query;
        }
        
        // Si el usuario tiene almacén, filtrar por su almacén
        return $query->where($almacenColumn, $user->almacen_id);
    }

    /**
     * Verificar si el usuario puede acceder a un recurso específico
     * Retorna true si es admin o si el almacén del recurso es el suyo
     */
    public function canAccessAlmacen($almacenId): bool
    {
        $user = auth()->user();
        
        // Admin puede acceder a todo
        if (!$user->almacen_id) {
            return true;
        }
        
        // Trabajador solo puede acceder a su almacén
        return $user->almacen_id == $almacenId;
    }

    /**
     * Obtener almacenes disponibles para enviar traslados
     * Si es admin, devuelve todos. Si es trabajador, devuelve todos excepto el suyo
     */
    public function getDestinationAlmacens()
    {
        $user = auth()->user();
        $query = \App\Models\Almacen::where('estado', true);
        
        // Si el usuario tiene almacén (trabajador), excluir el suyo como destino
        if ($user->almacen_id) {
            $query->where('id', '!=', $user->almacen_id);
        }
        
        return $query->get();
    }
}
