<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FiltraPorPermisosCorralon
{
    /**
     * Scope para filtrar por permisos del usuario a nivel corralón
     * Para modelos que tienen id_corralon directamente (Deposito)
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

        $corralonesPermitidos = $user->getCorralonesParaModulo($modulo);

        if (empty($corralonesPermitidos)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('id_corralon', $corralonesPermitidos);
    }

    /**
     * Scope retrocompatible
     */
    public function scopePorCorralonesPermitidos(Builder $query)
    {
        $modulo = defined(static::class . '::MODULO_PERMISO') ? static::MODULO_PERMISO : null;

        if ($modulo) {
            return $this->scopePorPermisosDeModulo($query, $modulo);
        }

        $user = auth()->user();
        if (!$user) return $query->whereRaw('1 = 0');
        if ($user->esAdministrador()) return $query;

        $corralonesIds = $user->getCorralonesPermitidosIds();
        if (empty($corralonesIds)) return $query->whereRaw('1 = 0');

        return $query->whereIn('id_corralon', $corralonesIds);
    }
}
