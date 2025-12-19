<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FiltraPorCorralonViaDeposito
{
    /**
     * Scope para filtrar por corralones permitidos a través de la relación con depósito
     * USO: Para modelos como Insumo que están relacionados con Deposito
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

        // Filtrar insumos que están en depósitos de corralones permitidos
        return $query->whereHas('deposito', function ($q) use ($corralonesIds) {
            $q->whereIn('id_corralon', $corralonesIds);
        });
    }
}