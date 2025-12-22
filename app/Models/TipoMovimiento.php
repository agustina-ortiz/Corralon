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

    /**
     * Determina si este tipo de movimiento es para insumos
     */
    public function esParaInsumos(): bool
    {
        return $this->tipo === 'I';
    }

    /**
     * Determina si este tipo de movimiento es para maquinaria
     */
    public function esParaMaquinaria(): bool
    {
        return $this->tipo === 'M';
    }

    /**
     * Determina si este tipo de movimiento es una transferencia
     */
    public function esTransferencia(): bool
    {
        return strtolower($this->tipo_movimiento) === 'transferencia';
    }

    /**
     * Determina si este tipo de movimiento es una entrada/ingreso
     * (Para tipos que no son transferencia)
     */
    public function esEntrada(): bool
    {
        if ($this->esTransferencia()) {
            // Las transferencias se determinan por contexto
            return false;
        }
        
        $nombre = strtolower($this->tipo_movimiento);
        
        $palabrasEntrada = [
            'entrada',
            'ingreso',
            'compra',
            'recepción',
            'recepcion',
            'devolución',
            'devolucion',
            'ajuste positivo',
            'inventario inicial',
        ];

        foreach ($palabrasEntrada as $palabra) {
            if (str_contains($nombre, $palabra)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determina si este tipo de movimiento es una salida
     * (Para tipos que no son transferencia)
     */
    public function esSalida(): bool
    {
        if ($this->esTransferencia()) {
            // Las transferencias se determinan por contexto
            return false;
        }
        
        $nombre = strtolower($this->tipo_movimiento);
        
        $palabrasSalida = [
            'salida',
            'egreso',
            'uso',
            'consumo',
            'préstamo',
            'prestamo',
            'ajuste negativo',
            'baja',
        ];

        foreach ($palabrasSalida as $palabra) {
            if (str_contains($nombre, $palabra)) {
                return true;
            }
        }

        return false;
    }
}