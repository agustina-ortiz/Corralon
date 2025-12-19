<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FiltraPorCorralon
{
    /**
     * Scope para filtrar por corralones permitidos del usuario
     * USO: Para modelos que tienen id_corralon directamente (Maquinaria, Vehiculo, etc)
     */
    public function scopePorCorralonesPermitidos(Builder $query)
    {
        $user = auth()->user();
        
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->acceso_todos_corralones) {
            return $query;
        }

        $corralonesIds = $user->corralones_permitidos ?? [];
        
        if (empty($corralonesIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('id_corralon', $corralonesIds);
    }
}