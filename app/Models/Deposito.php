<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\FiltraPorPermisosCorralon;

class Deposito extends Model
{
    use FiltraPorPermisosCorralon;

    const MODULO_PERMISO = 'depositos';

    protected $table = 'depositos';
    
    protected $fillable = [
        'sector',
        'deposito',
        'id_corralon',
    ];

    public function getNombreAttribute()
    {
        return $this->sector . ' - ' . $this->deposito;
    }

    public function insumos(): HasMany
    {
        return $this->hasMany(Insumo::class, 'id_deposito');
    }

    public function maquinarias(): HasMany
    {
        return $this->hasMany(Maquinaria::class, 'id_deposito');
    }

    public function corralon(): BelongsTo
    {
        return $this->belongsTo(Corralon::class, 'id_corralon');
    }

    /**
     * Secretarías a las que está asignado el depósito (pivote muchos-a-muchos).
     */
    public function secretarias(): BelongsToMany
    {
        return $this->belongsToMany(
            Secretaria::class,
            'depositos_secretarias',
            'id_deposito',
            'id_secretaria'
        )->withTimestamps();
    }

    public function cuadrillas(): HasMany
    {
        return $this->hasMany(Cuadrilla::class, 'id_deposito');
    }

    public function vehiculos(): HasMany
    {
        return $this->hasMany(Vehiculo::class, 'id_deposito');
    }

    public function movimientosInsumosEntrada(): HasMany
    {
        return $this->hasMany(MovimientoInsumo::class, 'id_deposito_entrada');
    }

    public function movimientosInsumosSalida(): HasMany
    {
        return $this->hasMany(MovimientoInsumo::class, 'id_deposito_salida');
    }

    public function movimientosMaquinariaEntrada(): HasMany
    {
        return $this->hasMany(MovimientoMaquinaria::class, 'id_deposito_entrada');
    }

    public function movimientosMaquinariaSalida(): HasMany
    {
        return $this->hasMany(MovimientoMaquinaria::class, 'id_deposito_salida');
    }
}
