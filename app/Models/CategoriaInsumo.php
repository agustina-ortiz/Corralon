<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CategoriaInsumo extends Model
{
    protected $table = 'categorias_insumos';
    
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function insumos(): HasMany
    {
        return $this->hasMany(Insumo::class, 'id_categoria');
    }

    public function tiposMovimientos(): BelongsToMany
    {
        return $this->belongsToMany(
            TipoMovimiento::class,
            'categoria_insumo_movimiento',
            'id_categoria',
            'id_movimiento'
        );
    }
}