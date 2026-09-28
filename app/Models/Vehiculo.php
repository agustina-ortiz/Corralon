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

    /** Días de anticipación para alertar un vencimiento (VTV, póliza, oblea). */
    const DIAS_ALERTA_VENCIMIENTO = 30;

    /**
     * Alertas de vehículos (dashboard + filtro `?alerta=` de /vehiculos).
     * Todas excluyen los vehículos en BAJA. Grupos:
     *  - vencimientos: fecha vencida o dentro de DIAS_ALERTA_VENCIMIENTO
     *  - datos: campos sin cargar (NULL o vacío)
     *  - operativos: situaciones de la flota a revisar
     */
    const ALERTAS = [
        'vtv_vencer'          => ['grupo' => 'vencimientos', 'label' => 'VTV vencida o por vencer',        'campo' => 'vencimiento_vtv'],
        'poliza_vencer'       => ['grupo' => 'vencimientos', 'label' => 'Póliza vencida o por vencer',     'campo' => 'vencimiento_poliza'],
        'oblea_vencer'        => ['grupo' => 'vencimientos', 'label' => 'Oblea GNC vencida o por vencer',  'campo' => 'vencimiento_oblea'],

        'sin_poliza'          => ['grupo' => 'datos', 'label' => 'Sin N° de póliza',              'campo' => 'nro_poliza'],
        'sin_venc_poliza'     => ['grupo' => 'datos', 'label' => 'Sin vencimiento de póliza',     'campo' => 'vencimiento_poliza'],
        'sin_venc_vtv'        => ['grupo' => 'datos', 'label' => 'Sin vencimiento de VTV',        'campo' => 'vencimiento_vtv'],
        'sin_venc_oblea'      => ['grupo' => 'datos', 'label' => 'A gas sin vencimiento de oblea', 'campo' => 'vencimiento_oblea'],
        'sin_patente'         => ['grupo' => 'datos', 'label' => 'Sin patente',                   'campo' => 'patente'],
        'sin_patrimonio'      => ['grupo' => 'datos', 'label' => 'Sin N° de patrimonio',          'campo' => 'nro_patrimonio'],
        'sin_secretaria'      => ['grupo' => 'datos', 'label' => 'Sin secretaría',                'campo' => 'id_secretaria'],
        'sin_combustible'     => ['grupo' => 'datos', 'label' => 'Sin tipo de combustible',       'campo' => 'tipo_combustible'],
        'sin_anio'            => ['grupo' => 'datos', 'label' => 'Sin año',                       'campo' => 'anio'],
        'sin_chasis'          => ['grupo' => 'datos', 'label' => 'Sin N° de chasis',              'campo' => 'nro_chasis'],
        'sin_motor'           => ['grupo' => 'datos', 'label' => 'Sin N° de motor',               'campo' => 'nro_motor'],

        'en_mantenimiento'    => ['grupo' => 'operativos', 'label' => 'En mantenimiento'],
        'sin_chofer'          => ['grupo' => 'operativos', 'label' => 'En uso sin chofer asignado'],
        'patente_duplicada'   => ['grupo' => 'operativos', 'label' => 'Patente repetida en otro vehículo'],
    ];

    /** Valores de `patente` que indican que el vehículo no lleva patente (no cuentan como duplicado). */
    const PATENTES_NO_POSEE = ['no posee'];

    /** Columnas de fecha: "sin dato" es solo NULL (comparar una fecha con '' falla en modo estricto). */
    const CAMPOS_FECHA = ['vencimiento_vtv', 'vencimiento_poliza', 'vencimiento_oblea'];

    /** Excluye los vehículos dados de baja. */
    public function scopeNoDadosDeBaja(Builder $query)
    {
        return $query->where(fn($q) => $q->whereNull('estado')->orWhere('estado', '<>', 'BAJA'));
    }

    /** Vehículos (no dados de baja) que disparan la alerta `$clave` de ALERTAS. */
    public function scopeConAlerta(Builder $query, string $clave)
    {
        $alerta = self::ALERTAS[$clave] ?? null;
        if (!$alerta) {
            return $query->whereRaw('1 = 0');
        }

        $query->noDadosDeBaja();

        switch ($alerta['grupo']) {
            case 'vencimientos':
                if ($clave === 'oblea_vencer') {
                    $query->where('tipo_combustible', 'gas');
                }
                return $query->whereNotNull($alerta['campo'])
                    ->where($alerta['campo'], '<=', now()->addDays(self::DIAS_ALERTA_VENCIMIENTO)->toDateString());

            case 'datos':
                if ($clave === 'sin_venc_oblea') {
                    $query->where('tipo_combustible', 'gas');
                }
                return $query->sinDato($alerta['campo']);
        }

        return match ($clave) {
            'en_mantenimiento'  => $query->where('estado', 'MANTENIMIENTO'),
            'sin_chofer'        => $query->where('estado', 'EN USO')->doesntHave('choferes'),
            'patente_duplicada' => $query->whereIn('patente', function ($sub) {
                $sub->select('patente')->from('vehiculos')
                    ->where('patente', '<>', '')
                    ->whereNotIn('patente', self::PATENTES_NO_POSEE)
                    ->where(fn($q) => $q->whereNull('estado')->orWhere('estado', '<>', 'BAJA'))
                    ->groupBy('patente')
                    ->havingRaw('COUNT(*) > 1');
            }),
        };
    }

    /** Campo sin cargar: NULL, o vacío/espacios en columnas de texto. */
    public function scopeSinDato(Builder $query, string $campo)
    {
        if (in_array($campo, self::CAMPOS_FECHA) || str_starts_with($campo, 'id_')) {
            return $query->whereNull($campo);
        }

        return $query->where(fn($q) => $q->whereNull($campo)->orWhereRaw("TRIM(`{$campo}`) = ''"));
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
