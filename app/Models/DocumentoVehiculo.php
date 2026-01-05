<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoVehiculo extends Model
{
    protected $table = 'documentos_vehiculos';

    protected $fillable = [
        'id_vehiculo',
        'descripcion',
        'archivo',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'id_vehiculo');
    }
}
