<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoMaquinaria extends Model
{
    protected $table = 'movimiento_maquinarias';
    
    protected $fillable = [
        'id_maquinaria',
        'id_tipo_movimiento',
        'fecha',
        'fecha_devolucion',
        'id_usuario',
        'id_referencia',
        'tipo_referencia',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_devolucion' => 'date',
    ];

    public function maquinaria(): BelongsTo
    {
        return $this->belongsTo(Maquinaria::class, 'id_maquinaria');
    }

    public function tipoMovimiento(): BelongsTo
    {
        return $this->belongsTo(TipoMovimiento::class, 'id_tipo_movimiento');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
