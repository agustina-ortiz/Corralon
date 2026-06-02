<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComprobanteMovimientoMaquinaria extends Model
{
    protected $table = 'comprobantes_movimiento_maquinaria';

    protected $fillable = [
        'id_movimiento_maquinaria',
        'archivo',
        'nombre_original',
        'tipo_mime',
    ];

    public function movimientoMaquinaria(): BelongsTo
    {
        return $this->belongsTo(MovimientoMaquinaria::class, 'id_movimiento_maquinaria');
    }
}
