<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\FiltraPorPermisos;

class Maquinaria extends Model
{
    use FiltraPorPermisos;

    const MODULO_PERMISO = 'maquinarias';
    protected $fillable = [
        'maquinaria',
        'id_categoria_maquinaria',
        'estado',
        'id_deposito',
        'cantidad',
    ];

    protected $casts = [
        'estado' => 'string',
        'cantidad' => 'integer',
    ];

    protected $appends = ['cantidad_disponible'];

    public function categoriaMaquinaria(): BelongsTo
    {
        return $this->belongsTo(CategoriaMaquinaria::class, 'id_categoria_maquinaria');
    }

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'id_deposito');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoMaquinaria::class, 'id_maquinaria');
    }

    // Calcula la cantidad disponible en el depósito propio de la maquinaria
    public function getCantidadDisponibleAttribute()
    {
        $entradas = MovimientoMaquinaria::where('id_maquinaria', $this->id)
            ->where('id_deposito_entrada', $this->id_deposito)
            ->whereHas('tipoMovimiento', fn($q) => $q->whereIn('tipo_movimiento', TipoMovimiento::NOMBRES_ENTRADA))
            ->sum('cantidad');

        $salidas = MovimientoMaquinaria::where('id_maquinaria', $this->id)
            ->where('id_deposito_entrada', $this->id_deposito)
            ->whereHas('tipoMovimiento', fn($q) => $q->whereIn('tipo_movimiento', TipoMovimiento::NOMBRES_SALIDA))
            ->sum('cantidad');

        return max(0, $entradas - $salidas);
    }

    // Calcula la cantidad disponible en un depósito específico
    public function getCantidadEnDeposito($id_deposito)
    {
        $entradas = $this->movimientos()
            ->where('id_deposito_entrada', $id_deposito)
            ->whereHas('tipoMovimiento', fn($q) => $q->whereIn('tipo_movimiento', TipoMovimiento::NOMBRES_ENTRADA))
            ->sum('cantidad');

        $salidas = $this->movimientos()
            ->where('id_deposito_entrada', $id_deposito)
            ->whereHas('tipoMovimiento', fn($q) => $q->whereIn('tipo_movimiento', TipoMovimiento::NOMBRES_SALIDA))
            ->sum('cantidad');

        return max(0, $entradas - $salidas);
    }

    public function getCantidadTotalDisponible()
    {
        $user = auth()->user();
        $depositosAccesibles = $user->getDepositosPermitidosParaModulo('maquinarias');

        $total = 0;
        foreach ($depositosAccesibles as $depositoId) {
            $total += $this->getCantidadEnDeposito($depositoId);
        }

        return $total;
    }

}