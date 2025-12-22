<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInsumo extends Model
{
    protected $table = 'movimiento_insumos';

    protected $fillable = [
        'id_insumo',
        'id_movimiento_encabezado',
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

    /**
     * Relación polimórfica con la referencia
     */
    public function referencia()
    {
        $modelMap = [
            'empleado' => Personal::class,
            'maquina' => Maquinaria::class,
            'evento' => Evento::class,
            'secretaria' => Secretaria::class,
        ];

        $model = $modelMap[$this->tipo_referencia] ?? null;
        
        if ($model && class_exists($model)) {
            return $this->belongsTo($model, 'id_referencia');
        }
        
        return null;
    }

    /**
     * Verifica si este movimiento es una entrada para un depósito específico
     * Para transferencias, verifica si el depósito_entrada coincide
     */
    public function esEntradaPara(int $idDeposito): bool
    {
        if (strtolower($this->tipoMovimiento?->tipo_movimiento ?? '') === 'transferencia') {
            return $this->id_deposito_entrada == $idDeposito;
        }
        
        // Para otros tipos, usar lógica por nombre
        return $this->tipoMovimiento?->esEntrada() ?? false;
    }

    /**
     * Verifica si este movimiento es una salida desde un depósito específico
     */
    public function esSalidaDe(int $idDeposito): bool
    {
        if (strtolower($this->tipoMovimiento?->tipo_movimiento ?? '') === 'transferencia') {
            return $this->id_deposito_entrada != $idDeposito;
        }
        
        // Para otros tipos, usar lógica por nombre
        return $this->tipoMovimiento?->esSalida() ?? false;
    }
}