<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'id_rol');
    }

    // Métodos helper para verificar permisos
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
}