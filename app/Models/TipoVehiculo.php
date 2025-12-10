<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoVehiculo extends Model
{
    protected $table = 'tipos_vehiculos';
    
    protected $fillable = [
        'descripcion',
    ];

    public function vehiculos(): HasMany
    {
        return $this->hasMany(Vehiculo::class, 'id_tipo_vehiculo');
    }
}
