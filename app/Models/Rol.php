<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'descripcion',
        'lInsumosABM',
        'lMaquinariasABM',
        'lVehiculosABM',
        'lCategoriasInsumosABM',
        'lCategoriasMaquinariasABM',
        'lDepositosABM',
        'lEventosABM',
        'lEmpleadosABM',
        'lUsuariosABM',
        'lMovimientosInsumos',
        'lMovimientosMaquinarias',
    ];

    protected $casts = [
        'lInsumosABM' => 'boolean',
        'lMaquinariasABM' => 'boolean',
        'lVehiculosABM' => 'boolean',
        'lCategoriasInsumosABM' => 'boolean',
        'lCategoriasMaquinariasABM' => 'boolean',
        'lDepositosABM' => 'boolean',
        'lEventosABM' => 'boolean',
        'lEmpleadosABM' => 'boolean',
        'lUsuariosABM' => 'boolean',
        'lMovimientosInsumos' => 'boolean',
        'lMovimientosMaquinarias' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'id_rol');
    }

    // Métodos helper para verificar permisos (mantener compatibilidad)
    public function puedeCrear(): bool
    {
        return $this->nombre === 'Administrador';
    }

    public function puedeEditar(): bool
    {
        return $this->nombre === 'Administrador';
    }

    public function puedeEliminar(): bool
    {
        return $this->nombre === 'Administrador';
    }

    // Métodos helper específicos por módulo
    public function puedeGestionarInsumos(): bool
    {
        return $this->lInsumosABM;
    }

    public function puedeGestionarMaquinarias(): bool
    {
        return $this->lMaquinariasABM;
    }

    public function puedeGestionarVehiculos(): bool
    {
        return $this->lVehiculosABM;
    }

    public function puedeGestionarCategoriasInsumos(): bool
    {
        return $this->lCategoriasInsumosABM;
    }

    public function puedeGestionarCategoriasMaquinarias(): bool
    {
        return $this->lCategoriasMaquinariasABM;
    }

    public function puedeGestionarDepositos(): bool
    {
        return $this->lDepositosABM;
    }

    public function puedeGestionarEventos(): bool
    {
        return $this->lEventosABM;
    }

    public function puedeGestionarEmpleados(): bool
    {
        return $this->lEmpleadosABM;
    }

    public function puedeGestionarUsuarios(): bool
    {
        return $this->lUsuariosABM;
    }

    public function puedeGestionarMovimientosInsumos(): bool
    {
        return $this->lMovimientosInsumos;
    }

    public function puedeGestionarMovimientosMaquinarias(): bool
    {
        return $this->lMovimientosMaquinarias;
    }

    // Método para verificar si tiene algún permiso ABM
    public function tieneAlgunPermisoABM(): bool
    {
        return $this->lInsumosABM 
            || $this->lMaquinariasABM 
            || $this->lVehiculosABM 
            || $this->lCategoriasInsumosABM 
            || $this->lCategoriasMaquinariasABM 
            || $this->lDepositosABM 
            || $this->lEventosABM 
            || $this->lEmpleadosABM
            || $this->lUsuariosABM;
    }
}