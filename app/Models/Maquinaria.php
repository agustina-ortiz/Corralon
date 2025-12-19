<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\FiltraPorCorralonViaDeposito;

class Maquinaria extends Model
{
    use FiltraPorCorralonViaDeposito;

    protected $table = 'maquinarias';
    
    protected $fillable = [
        'maquinaria',
        'id_categoria_maquinaria',
        'estado',
        'id_deposito',
        'id_corralon',
    ];

    public function getNombreAttribute()
    {
        return $this->maquinaria;
    }

    public function categoriaMaquinaria(): BelongsTo
    {
        return $this->belongsTo(CategoriaMaquinaria::class, 'id_categoria_maquinaria');
    }

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'id_deposito');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoMaquinaria::class, 'id_maquinaria');
    }
}