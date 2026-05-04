<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FiltraPorPermisos
{
    /**
     * Scope para filtrar por permisos del usuario (corralón + depósito)
     * Para modelos que tienen id_deposito directamente (Insumo, Maquinaria, Vehiculo)
     */
    public function scopePorPermisosDeModulo(Builder $query, string $modulo)
    {
        $user = auth()->user();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->esAdministrador()) {
            return $query;
        }

        $depositosPermitidos = $user->getDepositosPermitidosParaModulo($modulo);

        if (empty($depositosPermitidos)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('id_deposito', $depositosPermitidos);
    }

    /**
     * Scope retrocompatible: filtra por corralones permitidos (ahora derivados de permisos)
     * Usa el módulo definido en la constante MODULO_PERMISO del modelo
     */
    public function scopePorCorralonesPermitidos(Builder $query)
    {
        $modulo = defined(static::class . '::MODULO_PERMISO') ? static::MODULO_PERMISO : null;

        if ($modulo) {
            return $this->scopePorPermisosDeModulo($query, $modulo);
        }

        // Fallback: filtrar por cualquier depósito al que tenga acceso
        $user = auth()->user();

        if (!$user) return $query->whereRaw('1 = 0');
        if ($user->esAdministrador()) return $query;

        $depositosPermitidos = $this->getDepositosTotalesUsuario($user);
        if (empty($depositosPermitidos)) return $query->whereRaw('1 = 0');

        return $query->whereIn('id_deposito', $depositosPermitidos);
    }

    private function getDepositosTotalesUsuario($user): array
    {
        $corralonIds = $user->getCorralonesPermitidosIds();
        if (empty($corralonIds)) return [];
        return \App\Models\Deposito::whereIn('id_corralon', $corralonIds)->pluck('id')->toArray();
    }
}
