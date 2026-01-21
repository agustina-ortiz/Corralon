<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'corralones_permitidos',
        'acceso_todos_corralones',
        'id_rol',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'corralones_permitidos' => 'array',
        'acceso_todos_corralones' => 'boolean',
    ];

    /**
     * Verifica si el usuario tiene acceso a un corralón específico
     */
    public function tieneAccesoACorralon($corralonId): bool
    {
        if ($this->acceso_todos_corralones) {
            return true;
        }

        return in_array($corralonId, $this->corralones_permitidos ?? []);
    }

    /**
     * Obtiene los IDs de corralones a los que el usuario tiene acceso
     */
    public function getCorralonesPermitidosIds(): array
    {
        if ($this->acceso_todos_corralones) {
            return \App\Models\Corralon::pluck('id')->toArray();
        }

        return $this->corralones_permitidos ?? [];
    }

    /**
     * Relación con los corralones permitidos
     */
    public function corralones()
    {
        if ($this->acceso_todos_corralones) {
            return \App\Models\Corralon::all();
        }

        return \App\Models\Corralon::whereIn('id', $this->corralones_permitidos ?? [])->get();
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    // Métodos helper para verificar permisos
    public function esAdministrador(): bool
    {
        return $this->rol && $this->rol->nombre === 'Administrador';
    }

    // ========== PERMISOS PARA INSUMOS ==========
    public function puedeCrearInsumos(): bool
    {
        return $this->rol && $this->rol->lInsumosABM;
    }

    public function puedeEditarInsumos(): bool
    {
        return $this->rol && $this->rol->lInsumosABM;
    }

    public function puedeEliminarInsumos(): bool
    {
        return $this->rol && $this->rol->lInsumosABM;
    }

    // ========== PERMISOS PARA MAQUINARIAS ==========
    public function puedeCrearMaquinarias(): bool
    {
        return $this->rol && $this->rol->lMaquinariasABM;
    }

    public function puedeEditarMaquinarias(): bool
    {
        return $this->rol && $this->rol->lMaquinariasABM;
    }

    public function puedeEliminarMaquinarias(): bool
    {
        return $this->rol && $this->rol->lMaquinariasABM;
    }

    // ========== PERMISOS PARA VEHÍCULOS ==========
    public function puedeCrearVehiculos(): bool
    {
        return $this->rol && $this->rol->lVehiculosABM;
    }

    public function puedeEditarVehiculos(): bool
    {
        return $this->rol && $this->rol->lVehiculosABM;
    }

    public function puedeEliminarVehiculos(): bool
    {
        return $this->rol && $this->rol->lVehiculosABM;
    }

    // ========== PERMISOS PARA CATEGORÍAS DE INSUMOS ==========
    public function puedeCrearCategoriasInsumos(): bool
    {
        return $this->rol && $this->rol->lCategoriasInsumosABM;
    }

    public function puedeEditarCategoriasInsumos(): bool
    {
        return $this->rol && $this->rol->lCategoriasInsumosABM;
    }

    public function puedeEliminarCategoriasInsumos(): bool
    {
        return $this->rol && $this->rol->lCategoriasInsumosABM;
    }

    // ========== PERMISOS PARA CATEGORÍAS DE MAQUINARIAS ==========
    public function puedeCrearCategoriasMaquinarias(): bool
    {
        return $this->rol && $this->rol->lCategoriasMaquinariasABM;
    }

    public function puedeEditarCategoriasMaquinarias(): bool
    {
        return $this->rol && $this->rol->lCategoriasMaquinariasABM;
    }

    public function puedeEliminarCategoriasMaquinarias(): bool
    {
        return $this->rol && $this->rol->lCategoriasMaquinariasABM;
    }

    // ========== PERMISOS PARA DEPÓSITOS ==========
    public function puedeCrearDepositos(): bool
    {
        return $this->rol && $this->rol->lDepositosABM;
    }

    public function puedeEditarDepositos(): bool
    {
        return $this->rol && $this->rol->lDepositosABM;
    }

    public function puedeEliminarDepositos(): bool
    {
        return $this->rol && $this->rol->lDepositosABM;
    }

    // ========== PERMISOS PARA EVENTOS ==========
    public function puedeCrearEventos(): bool
    {
        return $this->rol && $this->rol->lEventosABM;
    }

    public function puedeEditarEventos(): bool
    {
        return $this->rol && $this->rol->lEventosABM;
    }

    public function puedeEliminarEventos(): bool
    {
        return $this->rol && $this->rol->lEventosABM;
    }

    // ========== PERMISOS PARA EMPLEADOS ==========
    public function puedeCrearEmpleados(): bool
    {
        return $this->rol && $this->rol->lEmpleadosABM;
    }

    public function puedeEditarEmpleados(): bool
    {
        return $this->rol && $this->rol->lEmpleadosABM;
    }

    public function puedeEliminarEmpleados(): bool
    {
        return $this->rol && $this->rol->lEmpleadosABM;
    }

    // ========== PERMISOS PARA USUARIOS ==========
    public function puedeCrearUsuarios(): bool
    {
        return $this->rol && $this->rol->lUsuariosABM;
    }

    public function puedeEditarUsuarios(): bool
    {
        return $this->rol && $this->rol->lUsuariosABM;
    }

    public function puedeEliminarUsuarios(): bool
    {
        return $this->rol && $this->rol->lUsuariosABM;
    }

    // ========== PERMISOS PARA MOVIMIENTOS DE INSUMOS ==========
    public function puedeCrearMovimientosInsumos(): bool
    {
        return $this->rol && $this->rol->lMovimientosInsumos;
    }

    public function puedeCrearTransferenciasInsumos(): bool
    {
        return $this->rol && $this->rol->lMovimientosInsumos;
    }

    // ========== PERMISOS PARA MOVIMIENTOS DE MAQUINARIAS ==========
    public function puedeCrearMovimientosMaquinarias(): bool
    {
        return $this->rol && $this->rol->lMovimientosMaquinarias;
    }

    public function puedeEditarMovimientosMaquinarias(): bool
    {
        return $this->rol && $this->rol->lMovimientosMaquinarias;
    }

    public function puedeEliminarMovimientosMaquinarias(): bool
    {
        return $this->rol && $this->rol->lMovimientosMaquinarias;
    }
}