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

    // ✅ Accessor actualizado - calcula solo para el depósito actual
    public function getCantidadDisponibleAttribute()
    {
        // Sumar entradas en el depósito ACTUAL
        $entradas = MovimientoMaquinaria::where('id_maquinaria', $this->id)
            ->where('id_deposito_entrada', $this->id_deposito)
            ->whereHas('tipoMovimiento', fn($q) => $q->where('tipo', 'I'))
            ->sum('cantidad');
        
        // Sumar salidas desde el depósito ACTUAL
        $salidas = MovimientoMaquinaria::where('id_maquinaria', $this->id)
            ->where('id_deposito_entrada', $this->id_deposito)
            ->whereHas('tipoMovimiento', fn($q) => $q->where('tipo', 'E'))
            ->sum('cantidad');
        
        return max(0, $entradas - $salidas);
    }

    // ✅ Método para obtener cantidad en un depósito específico
    public function getCantidadEnDeposito($id_deposito)
    {
        // Verificar si hay algún movimiento de "Inventario Inicial" para este depósito
        $tieneInventarioInicial = $this->movimientos()
            ->where('id_deposito_entrada', $id_deposito)
            ->whereHas('tipoMovimiento', function($q) {
                $q->where('tipo_movimiento', 'Inventario Inicial Maquinaria')
                ->orWhere('tipo_movimiento', 'Carga Inicial Maquinaria');
            })
            ->exists();
        
        // Entradas a este depósito (incluyendo inventario inicial si existe)
        $entradas = $this->movimientos()
            ->where('id_deposito_entrada', $id_deposito)
            ->whereHas('tipoMovimiento', fn($q) => $q->where('tipo', 'I'))
            ->sum('cantidad');
        
        // Salidas desde este depósito
        $salidas = $this->movimientos()
            ->where('id_deposito_entrada', $id_deposito)
            ->whereHas('tipoMovimiento', fn($q) => $q->where('tipo', 'E'))
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