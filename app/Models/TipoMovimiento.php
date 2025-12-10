<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TipoMovimiento extends Model
{
    protected $table = 'tipo_movimientos';
    
    protected $fillable = [
        'tipo_movimiento',
        'tipo',
    ];

    public function movimientosInsumos(): HasMany
    {
        return $this->hasMany(MovimientoInsumo::class, 'id_tipo_movimiento');
    }

    public function movimientosMaquinaria(): HasMany
    {
        return $this->hasMany(MovimientoMaquinaria::class, 'id_tipo_movimiento');
    }

    public function categoriasInsumos(): BelongsToMany
    {
        return $this->belongsToMany(
            CategoriaInsumo::class,
            'categoria_insumo_movimiento',
            'id_movimiento',
            'id_categoria'
        );
    }
}
