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
        'nro_movil',
        'vehiculo',
        'marca',
        'nro_motor',
        'nro_chasis',
        'modelo',
        'patente',
        'tipo_combustible',
        'vencimiento_oblea',
        'nro_poliza',
        'vencimiento_poliza',
        'vencimiento_vtv',
        'id_secretaria',
        'estado',
        'id_deposito',
    ];

    protected $casts = [
        'vencimiento_oblea' => 'date',
        'vencimiento_poliza' => 'date',
        'vencimiento_vtv' => 'date',
    ];

    public function getNombreAttribute()
    {
        return $this->vehiculo;
    }

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'id_deposito');
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoVehiculo::class, 'id_vehiculo');
    }
}
