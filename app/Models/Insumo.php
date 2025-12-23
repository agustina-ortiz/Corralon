<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\FiltraPorCorralonViaDeposito;

class Insumo extends Model
{
    use FiltraPorCorralonViaDeposito;

    protected $table = 'insumos';

    protected $fillable = [
        'insumo',
        'id_categoria',
        'unidad',
        'stock_actual',
        'stock_minimo',
        'id_deposito',
        'id_corralon',
    ];

    protected $appends = ['stock_calculado'];

    public function getNombreAttribute()
    {
        return $this->insumo;
    }

    public function categoriaInsumo(): BelongsTo
    {
        return $this->belongsTo(CategoriaInsumo::class, 'id_categoria');
    }

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'id_deposito');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInsumo::class, 'id_insumo');
    }

    public function getCorralon()
    {
        return $this->deposito?->corralon;
    }

    public function getCorralonNombreAttribute()
    {
        return $this->deposito?->corralon?->descripcion ?? 'Sin corralón';
    }

    /**
     * Calcula el stock actual basado en los movimientos
     * Para transferencias:
     * - Si id_deposito_entrada == id_deposito del insumo → ENTRADA (suma)
     * - Si id_deposito_entrada != id_deposito del insumo → SALIDA (resta)
     */
    public function calcularStockActual(): float
    {
        $movimientos = $this->movimientos()->with('tipoMovimiento')->get();
        
        $stock = 0;
        
        foreach ($movimientos as $movimiento) {
            if (!$movimiento->tipoMovimiento) {
                continue;
            }
            
            // Verificar que el tipo de movimiento sea para insumos
            if ($movimiento->tipoMovimiento->tipo !== 'I') {
                continue;
            }
            
            // Para transferencias, determinar si es entrada o salida según el depósito
            if (strtolower($movimiento->tipoMovimiento->tipo_movimiento) === 'transferencia') {
                // ✅ CLAVE: Si el depósito de entrada coincide con el depósito del insumo = ENTRADA
                if ($movimiento->id_deposito_entrada == $this->id_deposito) {
                    $stock += $movimiento->cantidad; // SUMA (entrada)
                } else {
                    // ✅ Es una salida desde este depósito
                    $stock -= $movimiento->cantidad; // RESTA (salida)
                }
            }
            // Para otros tipos de movimiento, usar la lógica por nombre
            elseif ($this->esEntradaPorNombre($movimiento->tipoMovimiento->tipo_movimiento)) {
                $stock += $movimiento->cantidad;
            } elseif ($this->esSalidaPorNombre($movimiento->tipoMovimiento->tipo_movimiento)) {
                $stock -= $movimiento->cantidad;
            }
        }
        
        return $stock;
    }

    /**
     * Determina si un tipo de movimiento es entrada por su nombre
     */
    private function esEntradaPorNombre(string $tipoMovimiento): bool
    {
        $nombre = strtolower($tipoMovimiento);
        
        $palabrasEntrada = [
            'entrada',
            'ingreso',
            'compra',
            'recepción',
            'recepcion',
            'devolución',
            'devolucion',
            'ajuste positivo',
            'inventario inicial', // ✅ Agregado
            'inventario',         // ✅ Agregado
            'inicial',            // ✅ Agregado
        ];

        foreach ($palabrasEntrada as $palabra) {
            if (str_contains($nombre, $palabra)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determina si un tipo de movimiento es salida por su nombre
     */
    private function esSalidaPorNombre(string $tipoMovimiento): bool
    {
        $nombre = strtolower($tipoMovimiento);
        
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

    /**
     * Atributo accessor para obtener el stock calculado
     */
    public function getStockCalculadoAttribute(): float
    {
        return $this->calcularStockActual();
    }

    /**
     * Sincroniza el stock_actual con el valor calculado
     */
    public function sincronizarStock()
    {
        // Calcular el stock basado en los movimientos
        $entradas = MovimientoInsumo::where('id_insumo', $this->id)
            ->whereHas('tipoMovimiento', function($q) {
                $q->where('tipo', 'I'); // Ingresos
            })
            ->sum('cantidad');
        
        $salidas = MovimientoInsumo::where('id_insumo', $this->id)
            ->whereHas('tipoMovimiento', function($q) {
                $q->where('tipo', 'E'); // Egresos
            })
            ->sum('cantidad');
        
        $this->stock_actual = $entradas - $salidas;
        $this->save();
        
        return $this->stock_actual;
    }

    /**
     * Verifica si el stock está bajo el mínimo
     */
    public function stockBajoMinimo(): bool
    {
        return $this->stock_actual < $this->stock_minimo;
    }

    /**
     * Obtiene la diferencia entre stock actual y mínimo
     */
    public function getDiferenciaStockAttribute(): float
    {
        return $this->stock_actual - $this->stock_minimo;
    }

    /**
     * Scope para insumos con stock bajo
     */
    public function scopeConStockBajo($query)
    {
        return $query->whereColumn('stock_actual', '<', 'stock_minimo');
    }

    /**
     * Obtiene el detalle de movimientos para auditoría
     */
    public function getDetalleMovimientos(): array
    {
        $movimientos = $this->movimientos()->with(['tipoMovimiento', 'depositoEntrada'])->get();
        
        $entradas = 0;
        $salidas = 0;
        $detalles = [];
        
        foreach ($movimientos as $movimiento) {
            if (!$movimiento->tipoMovimiento || $movimiento->tipoMovimiento->tipo !== 'I') {
                continue;
            }
            
            $esEntrada = false;
            $esSalida = false;
            
            if (strtolower($movimiento->tipoMovimiento->tipo_movimiento) === 'transferencia') {
                if ($movimiento->id_deposito_entrada == $this->id_deposito) {
                    $esEntrada = true;
                    $entradas += $movimiento->cantidad;
                } else {
                    $esSalida = true;
                    $salidas += $movimiento->cantidad;
                }
            } elseif ($this->esEntradaPorNombre($movimiento->tipoMovimiento->tipo_movimiento)) {
                $esEntrada = true;
                $entradas += $movimiento->cantidad;
            } elseif ($this->esSalidaPorNombre($movimiento->tipoMovimiento->tipo_movimiento)) {
                $esSalida = true;
                $salidas += $movimiento->cantidad;
            }
            
            $detalles[] = [
                'id' => $movimiento->id,
                'fecha' => $movimiento->fecha->format('Y-m-d'),
                'tipo' => $movimiento->tipoMovimiento->tipo_movimiento,
                'cantidad' => $movimiento->cantidad,
                'deposito_entrada' => $movimiento->depositoEntrada?->deposito ?? 'N/A',
                'deposito_actual' => $this->deposito->deposito,
                'es_entrada' => $esEntrada,
                'es_salida' => $esSalida,
                'operacion' => $esEntrada ? '+' : ($esSalida ? '-' : '?'),
            ];
        }
        
        return [
            'insumo' => $this->insumo,
            'deposito_actual' => $this->deposito->deposito,
            'entradas' => $entradas,
            'salidas' => $salidas,
            'stock_calculado' => $entradas - $salidas,
            'stock_actual' => $this->stock_actual,
            'diferencia' => ($entradas - $salidas) - $this->stock_actual,
            'movimientos' => $detalles,
        ];
    }
}