<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Insumo extends Model
{
    protected $table = 'insumos';

    protected $fillable = [
        'insumo',
        'id_categoria',
        'unidad',
        'stock_actual',
        'stock_minimo',
        'id_deposito',
    ];

    public function categoriaInsumo(): BelongsTo
    {
        return $this->belongsTo(CategoriaInsumo::class, 'id_categoria');
    }

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'id_deposito');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInsumo::class, 'id_insumo');
    }
}