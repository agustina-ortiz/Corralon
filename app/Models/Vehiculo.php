<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\FiltraPorPermisos;

class Vehiculo extends Model
{
    use FiltraPorPermisos;

    const MODULO_PERMISO = 'vehiculos';

     protected $table = 'vehiculos';
    
    protected $fillable = [
        'nro_patrimonio',
        'vehiculo',
        'marca_modelo',
        'nro_motor',
        'nro_chasis',
        'anio',
        'patente',
        'tipo_combustible',
        'vencimiento_oblea',
        'nro_poliza',
        'vencimiento_poliza',
        'vencimiento_vtv',
        'origen',
        'jurisdiccion_procedencia',
        'nro_telepase',
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

    public function choferes()
    {
        return $this->belongsToMany(Chofer::class, 'choferes_vehiculos', 'vehiculo_id', 'chofer_id');
    }
}
