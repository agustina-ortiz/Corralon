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
     * Acceso a vehículos (módulo mixto `vehiculos`):
     *
     * - Admin o permiso GLOBAL (usuario_permisos con id_corralon NULL) => todos los
     *   vehículos, incluidos los que no tienen secretaría.
     * - Permiso por corralón/depósito => ver scopeEnDepositos().
     *
     * Sobrescribe el scope homónimo del trait FiltraPorPermisos únicamente para
     * este modelo; Insumo/Maquinaria no se ven afectados.
     */
    public function scopePorCorralonesPermitidos(Builder $query)
    {
        $user = auth()->user();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->tieneAccesoGlobalAModulo(self::MODULO_PERMISO)) {
            return $query;
        }

        return $query->enDepositos($user->getDepositosPermitidosParaModulo(self::MODULO_PERMISO));
    }

    /**
     * Vehículos que corresponden a un conjunto de depósitos: los que tienen
     * `id_deposito` en ese conjunto, o cuya `id_secretaria` está vinculada a
     * alguno de esos depósitos por el pivote `depositos_secretarias`.
     */
    public function scopeEnDepositos(Builder $query, array $depositos)
    {
        if (empty($depositos)) {
            return $query->whereRaw('1 = 0');
        }

        $secretarias = DB::table('depositos_secretarias')
            ->whereIn('id_deposito', $depositos)
            ->pluck('id_secretaria')
            ->unique()
            ->all();

        return $query->where(function ($q) use ($depositos, $secretarias) {
            $q->whereIn('id_deposito', $depositos);
            if (!empty($secretarias)) {
                $q->orWhereIn('id_secretaria', $secretarias);
            }
        });
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
