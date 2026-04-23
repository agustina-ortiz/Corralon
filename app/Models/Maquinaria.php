<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Maquinaria extends Model
{
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

    public function scopePorCorralonesPermitidos($query)
    {
        $user = auth()->user();
        
        if ($user->acceso_todos_corralones) {
            return $query;
        }

        return $query->whereHas('deposito', function($q) use ($user) {
            $q->whereIn('id_corralon', $user->corralones_permitidos ?? []);
        });
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
        
        $depositosAccesibles = $user->acceso_todos_corralones 
            ? \App\Models\Deposito::pluck('id')->toArray()
            : \App\Models\Deposito::whereIn('id_corralon', $user->corralones_permitidos ?? [])->pluck('id')->toArray();
        
        $total = 0;
        foreach ($depositosAccesibles as $depositoId) {
            $total += $this->getCantidadEnDeposito($depositoId);
        }
        
        return $total;
    }

}