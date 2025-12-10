<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deposito extends Model
{
    protected $table = 'depositos';
    
    protected $fillable = [
        'sector',
        'deposito',
        'id_corralon',
    ];

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
