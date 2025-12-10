<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empleado extends Model
{
    protected $table = 'empleados';

    protected $fillable = [
        'nombre',
        'apellido',
        'legajo',
    ];

    public function movimientosInsumos(): HasMany
    {
        return $this->hasMany(MovimientoInsumo::class, 'id_empleado');
    }

    public function movimientosMaquinaria(): HasMany
    {
        return $this->hasMany(MovimientoMaquinaria::class, 'id_empleado');
    }
}
