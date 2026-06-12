<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadoMunicipal extends Model
{
    protected $connection = 'munimer_inasi';
    protected $table = 'in_maestro';
    protected $primaryKey = 'LEGAJO';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'FECING' => 'date',
        'FECNAC' => 'date',
        'FECHABAJ' => 'date',
    ];

    /**
     * Depto de los empleados del corralón (Municipalidad de Mercedes)
     */
    public const DEPTO_CORRALON = 36;

    /**
     * Scope para filtrar solo empleados activos (sin fecha de baja)
     */
    public function scopeActivos($query)
    {
        return $query->whereNull('FECHABAJ');
    }

    /**
     * Scope para filtrar por departamento (columna DEPTO)
     */
    public function scopePorDepto($query, int $depto)
    {
        return $query->where('DEPTO', $depto);
    }

    /**
     * Nombre completo formateado (capitalizado)
     */
    public function getNombreFormateadoAttribute(): string
    {
        return mb_convert_case(trim($this->NOMBRE), MB_CASE_TITLE, 'UTF-8');
    }
}
