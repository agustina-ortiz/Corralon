<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComprobanteMovimiento extends Model
{
    protected $table = 'comprobantes_movimiento';

    protected $fillable = [
        'id_movimiento_insumo',
        'archivo',
        'nombre_original',
        'tipo_mime',
    ];

    public function movimientoInsumo(): BelongsTo
    {
        return $this->belongsTo(MovimientoInsumo::class, 'id_movimiento_insumo');
    }
}
