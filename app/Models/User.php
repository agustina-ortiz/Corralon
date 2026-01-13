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

    // Permisos para Insumos
    public function puedeCrearInsumos(): bool
    {
        return $this->rol && $this->rol->puedeCrear();
    }

    public function puedeEditarInsumos(): bool
    {
        return $this->rol && $this->rol->puedeEditar();
    }

    public function puedeEliminarInsumos(): bool
    {
        return $this->rol && $this->rol->puedeEliminar();
    }

    // Permisos para Transferencias y Movimientos de Insumos
    public function puedeCrearMovimientosInsumos(): bool
    {
        return $this->rol && $this->rol->puedeCrear();
    }

    public function puedeCrearTransferenciasInsumos(): bool
    {
        return $this->rol && $this->rol->puedeCrear();
    }

    // Permisos para Vehículos
    public function puedeCrearVehiculos(): bool
    {
        return $this->rol && $this->rol->puedeCrear();
    }

    public function puedeEditarVehiculos(): bool
    {
        return $this->rol && $this->rol->puedeEditar();
    }

    public function puedeEliminarVehiculos(): bool
    {
        return $this->rol && $this->rol->puedeEliminar();
    }

    // Permisos para Categorías de Insumos
    public function puedeCrearCategoriasInsumos(): bool
    {
        return $this->rol && $this->rol->puedeCrear();
    }

    public function puedeEditarCategoriasInsumos(): bool
    {
        return $this->rol && $this->rol->puedeEditar();
    }

    public function puedeEliminarCategoriasInsumos(): bool
    {
        return $this->rol && $this->rol->puedeEliminar();
    }

    // Permisos para Maquinarias
    public function puedeCrearMaquinarias(): bool
    {
        return $this->rol && $this->rol->puedeCrear();
    }

    public function puedeEditarMaquinarias(): bool
    {
        return $this->rol && $this->rol->puedeEditar();
    }

    public function puedeEliminarMaquinarias(): bool
    {
        return $this->rol && $this->rol->puedeEliminar();
    }

    // Permisos para Categorías de Maquinarias
    public function puedeCrearCategoriasMaquinarias(): bool
    {
        return $this->rol && $this->rol->puedeCrear();
    }

    public function puedeEditarCategoriasMaquinarias(): bool
    {
        return $this->rol && $this->rol->puedeEditar();
    }

    public function puedeEliminarCategoriasMaquinarias(): bool
    {
        return $this->rol && $this->rol->puedeEliminar();
    }

    // Permisos para Depósitos
    public function puedeCrearDepositos(): bool
    {
        return $this->rol && $this->rol->puedeCrear();
    }

    public function puedeEditarDepositos(): bool
    {
        return $this->rol && $this->rol->puedeEditar();
    }

    public function puedeEliminarDepositos(): bool
    {
        return $this->rol && $this->rol->puedeEliminar();
    }
}