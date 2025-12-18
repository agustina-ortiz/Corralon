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
}