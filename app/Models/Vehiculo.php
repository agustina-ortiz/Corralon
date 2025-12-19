<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\FiltraPorCorralonViaDeposito;


class Vehiculo extends Model
{
    use FiltraPorCorralonViaDeposito;

     protected $table = 'vehiculos';
    
    protected $fillable = [
        'vehiculo',
        'marca',
        'nro_motor',
        'nro_chasis',
        'modelo',
        'patente',
        'id_secretaria',
        'estado',
        'id_deposito',
        'id_corralon',
    ];

    public function getNombreAttribute()
    {
        return $this->vehiculo;
    }

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'id_deposito');
    }
}
