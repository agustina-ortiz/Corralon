<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoMovimiento extends Model
{
    protected $table = 'tipo_movimientos';

    protected $fillable = [
        'tipo_movimiento',
        'tipo',
    ];

    // Tipos de movimiento considerados entradas (aumentan stock)
    const NOMBRES_ENTRADA = [
        'Ajuste Positivo',
        'Carga de Stock',
        'Carga de Stock Maquinaria',
        'Devolución',
        'Inventario Inicial',
        'Inventario Inicial Maquinaria',
        'Transferencia Entrada',
        'Transferencia Entrada Maquinaria',
    ];

    // Tipos de movimiento considerados salidas (reducen stock)
    const NOMBRES_SALIDA = [
        'Ajuste Negativo',
        'Asignación Maquinaria',
        'Mantenimiento Maquinaria',
        'Transferencia Salida',
        'Transferencia Salida Maquinaria',
    ];

    /**
     * Determina si aplica a Insumos (tipo I o IM)
     */
    public function esParaInsumos(): bool
    {
        return in_array($this->tipo, ['I', 'IM']);
    }

    /**
     * Determina si aplica a Maquinarias (tipo M o IM)
     */
    public function esParaMaquinaria(): bool
    {
        return in_array($this->tipo, ['M', 'IM']);
    }

    /**
     * Determina si este tipo representa una entrada de stock
     */
    public function esEntrada(): bool
    {
        return in_array($this->tipo_movimiento, self::NOMBRES_ENTRADA);
    }

    /**
     * Determina si este tipo representa una salida de stock
     */
    public function esSalida(): bool
    {
        return in_array($this->tipo_movimiento, self::NOMBRES_SALIDA);
    }

    /**
     * Scope para filtrar solo entradas
     */
    public function scopeEntradas($query)
    {
        return $query->whereIn('tipo_movimiento', self::NOMBRES_ENTRADA);
    }

    /**
     * Scope para filtrar solo salidas
     */
    public function scopeSalidas($query)
    {
        return $query->whereIn('tipo_movimiento', self::NOMBRES_SALIDA);
    }
}