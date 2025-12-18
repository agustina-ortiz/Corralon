<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInsumo extends Model
{
    protected $table = 'movimiento_insumos';
    
    protected $fillable = [
        'id_movimiento_encabezado',
        'id_insumo',
        'id_tipo_movimiento',
        'cantidad',
        'fecha',
        'fecha_devolucion',
        'id_usuario',
        'id_deposito_entrada',
        'id_referencia',
        'tipo_referencia',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_devolucion' => 'date',
    ];

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class, 'id_insumo');
    }

    public function tipoMovimiento(): BelongsTo
    {
        return $this->belongsTo(TipoMovimiento::class, 'id_tipo_movimiento');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function depositoEntrada(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'id_deposito_entrada');
    }

    public function encabezado(): BelongsTo
    {
        return $this->belongsTo(MovimientoEncabezado::class, 'id_movimiento_encabezado');
    }
}
