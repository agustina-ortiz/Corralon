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
}