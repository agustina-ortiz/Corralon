<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Traits\FiltraPorPermisos;

class Vehiculo extends Model
{
    use FiltraPorPermisos;

    const MODULO_PERMISO = 'vehiculos';

    /**
     * Acceso a vehículos POR SECRETARÍA (no por depósito propio).
     *
     * Los vehículos no usan `id_deposito` para el control de acceso: se rige por
     * `id_secretaria` a través del pivote `depositos_secretarias`. Un usuario ve un
     * vehículo si la secretaría del vehículo está vinculada a alguno de los depósitos
     * a los que tiene acceso en el módulo `vehiculos`. El administrador ve todo.
     *
     * Sobrescribe el scope homónimo del trait FiltraPorPermisos (que filtra por
     * `id_deposito`) únicamente para este modelo; Insumo/Maquinaria no se ven afectados.
     */
    public function scopePorCorralonesPermitidos(Builder $query)
    {
        $user = auth()->user();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->esAdministrador()) {
            return $query;
        }

        $depositosPermitidos = $user->getDepositosPermitidosParaModulo('vehiculos');

        if (empty($depositosPermitidos)) {
            return $query->whereRaw('1 = 0');
        }

        $secretariasPermitidas = DB::table('depositos_secretarias')
            ->whereIn('id_deposito', $depositosPermitidos)
            ->pluck('id_secretaria')
            ->unique()
            ->all();

        if (empty($secretariasPermitidas)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('id_secretaria', $secretariasPermitidas);
    }

     protected $table = 'vehiculos';
    
    protected $fillable = [
        'id_tipo_vehiculo',
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
        'area',
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

    public function tipoVehiculo(): BelongsTo
    {
        return $this->belongsTo(TipoVehiculo::class, 'id_tipo_vehiculo');
    }

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'id_deposito');
    }

    public function secretaria(): BelongsTo
    {
        return $this->belongsTo(Secretaria::class, 'id_secretaria');
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
