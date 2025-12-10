<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehiculo extends Model
{
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
    ];

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'id_deposito');
    }
}
