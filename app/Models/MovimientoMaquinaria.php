<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoMaquinaria extends Model
{
    protected $table = 'movimiento_maquinarias';
    
    protected $fillable = [
        'id_maquinaria',
        'cantidad',
        'id_tipo_movimiento',
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
        'cantidad' => 'integer',
    ];

    protected $appends = ['cantidad_historica'];

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

    public function depositoEntrada(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'id_deposito_entrada');
    }

    // ✅ Accessor corregido - calcula cantidad en el depósito específico
    public function getCantidadHistoricaAttribute()
    {
        $deposito = $this->id_deposito_entrada;
        
        // Sumar entradas en ESTE DEPÓSITO hasta ESTE movimiento
        $entradas = MovimientoMaquinaria::where('id_maquinaria', $this->id_maquinaria)
            ->where('id_deposito_entrada', $deposito)
            ->where('id', '<=', $this->id)
            ->whereHas('tipoMovimiento', fn($q) => $q->where('tipo', 'I'))
            ->sum('cantidad');
        
        // Sumar salidas desde ESTE DEPÓSITO hasta ESTE movimiento
        $salidas = MovimientoMaquinaria::where('id_maquinaria', $this->id_maquinaria)
            ->where('id_deposito_entrada', $deposito)
            ->where('id', '<=', $this->id)
            ->whereHas('tipoMovimiento', fn($q) => $q->where('tipo', 'E'))
            ->sum('cantidad');
        
        // Calcular solo basado en movimientos (no sumar cantidad inicial)
        return max(0, $entradas - $salidas);
    }
}