<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoEncabezado extends Model
{
    protected $table = 'movimiento_encabezados';

    protected $fillable = [
        'fecha',
        'id_deposito_origen',
        'id_deposito_destino',
        'observaciones',
        'id_usuario',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInsumo::class, 'id_movimiento_encabezado');
    }

    public function depositoOrigen(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'id_deposito_origen');
    }

    public function depositoDestino(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'id_deposito_destino');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /**
     * Obtiene solo los movimientos de entrada (cantidad positiva en depósito destino)
     * Para transferencias, son los movimientos donde id_deposito_entrada coincide con el depósito del insumo
     */
    public function movimientosEntrada()
    {
        return $this->movimientos()
            ->with(['insumo.categoriaInsumo', 'tipoMovimiento'])
            ->where('id_deposito_entrada', $this->id_deposito_destino);
    }

    /**
     * Obtiene solo los movimientos de salida (desde depósito origen)
     */
    public function movimientosSalida()
    {
        return $this->movimientos()
            ->whereHas('insumo', function($query) {
                $query->where('id_deposito', $this->id_deposito_origen);
            });
    }

    /**
     * Obtiene los insumos únicos transferidos (sin duplicar entrada/salida)
     */
    public function getInsumosTransferidosAttribute()
    {
        return $this->movimientosEntrada()
            ->with('insumo.categoriaInsumo')
            ->get();
    }

    /**
     * Cuenta la cantidad de insumos únicos transferidos
     */
    public function getCantidadInsumosAttribute()
    {
        return $this->movimientosEntrada()->count();
    }

    /**
     * Obtiene el resumen de la transferencia
     */
    public function getResumenTransferenciaAttribute()
    {
        $movimientos = $this->movimientosEntrada;
        
        return $movimientos->map(function($mov) {
            return sprintf(
                '%s %s de %s',
                number_format($mov->cantidad, 2),
                $mov->insumo->unidad,
                $mov->insumo->insumo
            );
        })->join(', ');
    }
}