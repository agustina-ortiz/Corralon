<?php

namespace App\Livewire;

use App\Models\Insumo;
use App\Models\Maquinaria;
use App\Models\Vehiculo;
use App\Models\Chofer;
use App\Models\Evento;
use App\Models\Corralon;
use App\Models\Deposito;
use App\Models\MovimientoInsumo;
use App\Models\TipoMovimiento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Estadisticas extends Component
{
    // Filtros globales de la solapa
    public $filtro_corralon = '';
    public $filtro_deposito = '';
    public $fecha_desde = '';
    public $fecha_hasta = '';

    // Modal "Personalizar"
    public bool $modalPersonalizar = false;
    public array $seleccionWidgets = [];

    public function updatingFiltroCorralon(): void
    {
        $this->filtro_deposito = '';
    }

    public function limpiarFiltros(): void
    {
        $this->filtro_corralon = '';
        $this->filtro_deposito = '';
        $this->fecha_desde = '';
        $this->fecha_hasta = '';
    }

    public function abrirModalPersonalizar(): void
    {
        $this->seleccionWidgets = Auth::user()->estadisticasActivas();
        $this->modalPersonalizar = true;
    }

    public function guardarPreferencias(): void
    {
        $user = Auth::user();
        $user->estadisticas_widgets = array_values($this->seleccionWidgets);
        $user->save();
        $this->modalPersonalizar = false;
    }

    // ================================================================
    // HELPERS DE PERMISOS + FILTROS
    // ================================================================

    /**
     * IDs de depósitos a considerar para un módulo, combinando los permisos
     * del usuario con los filtros de corralón/depósito seleccionados.
     */
    private function depositosConstraint(string $modulo): array
    {
        $user = Auth::user();
        $permitidos = $user->getDepositosPermitidosParaModulo($modulo); // admin => todos

        if ($this->filtro_deposito) {
            return in_array((int) $this->filtro_deposito, $permitidos)
                ? [(int) $this->filtro_deposito]
                : [];
        }

        if ($this->filtro_corralon) {
            $delCorralon = Deposito::where('id_corralon', $this->filtro_corralon)->pluck('id')->toArray();
            return array_values(array_intersect($permitidos, $delCorralon));
        }

        return $permitidos;
    }

    private function rangoFechas(): array
    {
        return [
            $this->fecha_desde ? Carbon::parse($this->fecha_desde)->startOfDay() : null,
            $this->fecha_hasta ? Carbon::parse($this->fecha_hasta)->endOfDay() : null,
        ];
    }

    // ================================================================
    // CÁLCULO DE WIDGETS
    // ================================================================

    /**
     * Devuelve el payload de un widget: ['tipo','titulo','data','decimales'].
     */
    private function calcular(string $key, array $cfg, &$movsCache): array
    {
        $payload = [
            'tipo'      => $cfg['chart'],
            'titulo'    => $cfg['label'],
            'decimales' => 0,
            'data'      => [],
        ];

        switch ($key) {

            // ---------- INSUMOS ----------
            case 'ins_categoria':
                $insumos = Insumo::with('categoriaInsumo')
                    ->whereIn('id_deposito', $this->depositosConstraint('insumos'))->get();
                $payload['data'] = $insumos
                    ->groupBy(fn($i) => $i->categoriaInsumo->nombre ?? 'Sin categoría')
                    ->map(fn($g, $l) => ['label' => $l, 'value' => $g->count()])
                    ->sortByDesc('value')->values()->all();
                break;

            case 'ins_estado_stock':
                $insumos = Insumo::whereIn('id_deposito', $this->depositosConstraint('insumos'))->get();
                $sin = $insumos->filter(fn($i) => (float) $i->stock_actual <= 0)->count();
                $bajo = $insumos->filter(fn($i) => (float) $i->stock_actual > 0 && (float) $i->stock_actual < (float) $i->stock_minimo)->count();
                $ok = $insumos->count() - $sin - $bajo;
                $payload['data'] = [
                    ['label' => 'OK',           'value' => $ok],
                    ['label' => 'Bajo mínimo',  'value' => $bajo],
                    ['label' => 'Sin stock',    'value' => $sin],
                ];
                break;

            case 'ins_top_stock':
                $payload['decimales'] = 2;
                $payload['data'] = Insumo::whereIn('id_deposito', $this->depositosConstraint('insumos'))
                    ->orderByDesc('stock_actual')->take(10)->get()
                    ->map(fn($i) => ['label' => $i->insumo, 'value' => (float) $i->stock_actual])
                    ->all();
                break;

            case 'ins_unidad':
                $insumos = Insumo::whereIn('id_deposito', $this->depositosConstraint('insumos'))->get();
                $payload['data'] = $insumos
                    ->groupBy(fn($i) => $i->unidad ?: 'Sin unidad')
                    ->map(fn($g, $l) => ['label' => $l, 'value' => $g->count()])
                    ->sortByDesc('value')->values()->all();
                break;

            case 'ins_stock_deposito':
                $payload['decimales'] = 2;
                $insumos = Insumo::with('deposito')
                    ->whereIn('id_deposito', $this->depositosConstraint('insumos'))->get();
                $payload['data'] = $insumos
                    ->groupBy(fn($i) => $i->deposito->deposito ?? 'Sin depósito')
                    ->map(fn($g, $l) => ['label' => $l, 'value' => $g->sum(fn($i) => (float) $i->stock_actual)])
                    ->sortByDesc('value')->values()->all();
                break;

            // ---------- MAQUINARIAS ----------
            case 'maq_categoria':
                $maqs = Maquinaria::with('categoriaMaquinaria')
                    ->whereIn('id_deposito', $this->depositosConstraint('maquinarias'))->get();
                $payload['data'] = $maqs
                    ->groupBy(fn($m) => $m->categoriaMaquinaria->nombre ?? 'Sin categoría')
                    ->map(fn($g, $l) => ['label' => $l, 'value' => $g->count()])
                    ->sortByDesc('value')->values()->all();
                break;

            case 'maq_disponibilidad':
                $maqs = Maquinaria::whereIn('id_deposito', $this->depositosConstraint('maquinarias'))->get();
                $total = (int) $maqs->sum('cantidad');
                $disp = (int) $maqs->sum(fn($m) => (int) $m->cantidad_disponible);
                $asig = max(0, $total - $disp);
                $payload['data'] = [
                    ['label' => 'Disponibles',        'value' => $disp],
                    ['label' => 'Asignadas / en uso', 'value' => $asig],
                ];
                break;

            case 'maq_deposito':
                $maqs = Maquinaria::with('deposito')
                    ->whereIn('id_deposito', $this->depositosConstraint('maquinarias'))->get();
                $payload['data'] = $maqs
                    ->groupBy(fn($m) => $m->deposito->deposito ?? 'Sin depósito')
                    ->map(fn($g, $l) => ['label' => $l, 'value' => (int) $g->sum('cantidad')])
                    ->sortByDesc('value')->values()->all();
                break;

            // ---------- VEHÍCULOS ----------
            case 'veh_estado':
                $vehs = $this->vehiculosFiltrados();
                $payload['data'] = $vehs
                    ->groupBy(fn($v) => $v->estado ?: 'Sin estado')
                    ->map(fn($g, $l) => ['label' => $l, 'value' => $g->count()])
                    ->sortByDesc('value')->values()->all();
                break;

            case 'veh_secretaria':
                $vehs = $this->vehiculosFiltrados()->load('secretaria');
                $payload['data'] = $vehs
                    ->groupBy(fn($v) => $v->secretaria->secretaria ?? 'Sin secretaría')
                    ->map(fn($g, $l) => ['label' => $l, 'value' => $g->count()])
                    ->sortByDesc('value')->values()->all();
                break;

            case 'veh_combustible':
                $vehs = $this->vehiculosFiltrados();
                $payload['data'] = $vehs
                    ->groupBy(fn($v) => $v->tipo_combustible ?: 'Sin dato')
                    ->map(fn($g, $l) => ['label' => $l, 'value' => $g->count()])
                    ->sortByDesc('value')->values()->all();
                break;

            case 'veh_vtv':
                $vehs = $this->vehiculosFiltrados();
                $hoy = Carbon::today();
                $limite = Carbon::today()->addDays(30);
                $sinDato = $vehs->filter(fn($v) => !$v->vencimiento_vtv)->count();
                $vencida = $vehs->filter(fn($v) => $v->vencimiento_vtv && $v->vencimiento_vtv->lt($hoy))->count();
                $porVencer = $vehs->filter(fn($v) => $v->vencimiento_vtv && $v->vencimiento_vtv->gte($hoy) && $v->vencimiento_vtv->lte($limite))->count();
                $alDia = $vehs->count() - $sinDato - $vencida - $porVencer;
                $payload['data'] = [
                    ['label' => 'Al día',      'value' => $alDia],
                    ['label' => 'Por vencer',  'value' => $porVencer],
                    ['label' => 'Vencida',     'value' => $vencida],
                    ['label' => 'Sin dato',    'value' => $sinDato],
                ];
                break;

            // ---------- MOVIMIENTOS ----------
            case 'mov_entrada_salida':
                $movs = $this->movimientos($movsCache);
                [$desde, $hasta] = $this->rangoFechas();
                $fin = $hasta ? $hasta->copy()->startOfMonth() : Carbon::now()->startOfMonth();
                $ini = $desde ? $desde->copy()->startOfMonth() : $fin->copy()->subMonths(5);
                // Limitar a un máximo razonable de barras
                if ($ini->diffInMonths($fin) > 23) $ini = $fin->copy()->subMonths(23);
                $meses = [];
                $cursor = $ini->copy();
                while ($cursor->lte($fin)) {
                    $meses[$cursor->format('Y-m')] = ['label' => $cursor->locale('es')->isoFormat('MMM YY'), 'entradas' => 0, 'salidas' => 0];
                    $cursor->addMonth();
                }
                foreach ($movs as $m) {
                    if (!$m->fecha) continue;
                    $k = $m->fecha->format('Y-m');
                    if (!isset($meses[$k])) continue;
                    $nombre = $m->tipoMovimiento->tipo_movimiento ?? '';
                    if (in_array($nombre, TipoMovimiento::NOMBRES_ENTRADA)) {
                        $meses[$k]['entradas'] += (float) $m->cantidad;
                    } elseif (in_array($nombre, TipoMovimiento::NOMBRES_SALIDA)) {
                        $meses[$k]['salidas'] += (float) $m->cantidad;
                    }
                }
                $payload['data'] = array_values($meses);
                break;

            case 'mov_tipo':
                $movs = $this->movimientos($movsCache);
                $payload['data'] = $movs
                    ->groupBy(fn($m) => $m->tipoMovimiento->tipo_movimiento ?? 'Sin tipo')
                    ->map(fn($g, $l) => ['label' => $l, 'value' => $g->count()])
                    ->sortByDesc('value')->values()->all();
                break;

            case 'mov_top_insumos':
                $movs = $this->movimientos($movsCache);
                $payload['data'] = $movs
                    ->groupBy(fn($m) => $m->insumo->insumo ?? 'Sin insumo')
                    ->map(fn($g, $l) => ['label' => $l, 'value' => (float) $g->sum('cantidad')])
                    ->sortByDesc('value')->take(10)->values()->all();
                break;

            case 'mov_destino':
                $movs = $this->movimientos($movsCache);
                $etiquetas = [
                    'vehiculo'   => 'Vehículos',
                    'evento'     => 'Eventos',
                    'empleado'   => 'Empleados',
                    'secretaria' => 'Secretarías',
                ];
                $payload['data'] = $movs
                    ->filter(fn($m) => isset($etiquetas[$m->tipo_referencia]))
                    ->groupBy('tipo_referencia')
                    ->map(fn($g, $l) => ['label' => $etiquetas[$l], 'value' => $g->count()])
                    ->sortByDesc('value')->values()->all();
                break;

            case 'mov_usuario':
                $movs = $this->movimientos($movsCache);
                $payload['data'] = $movs
                    ->groupBy(fn($m) => $m->usuario->name ?? 'Sin usuario')
                    ->map(fn($g, $l) => ['label' => $l, 'value' => $g->count()])
                    ->sortByDesc('value')->take(10)->values()->all();
                break;

            // ---------- CHOFERES / EVENTOS ----------
            case 'cho_licencias':
                $limite = Carbon::today()->addDays(60);
                $payload['data'] = Chofer::with('secretaria')
                    ->whereNotNull('vencimiento_licencia')
                    ->where('vencimiento_licencia', '<=', $limite)
                    ->orderBy('vencimiento_licencia')
                    ->get();
                break;

            case 'eve_por_mes':
                [$desde, $hasta] = $this->rangoFechas();
                $eventos = Evento::query()
                    ->when($desde, fn($q) => $q->whereDate('fecha', '>=', $desde))
                    ->when($hasta, fn($q) => $q->whereDate('fecha', '<=', $hasta))
                    ->get();
                $payload['data'] = $eventos
                    ->groupBy(fn($e) => $e->fecha->format('Y-m'))
                    ->map(fn($g, $k) => [
                        'label' => Carbon::createFromFormat('Y-m', $k)->locale('es')->isoFormat('MMM YY'),
                        'value' => $g->count(),
                        'orden' => $k,
                    ])
                    ->sortBy('orden')->take(-12)->values()
                    ->map(fn($x) => ['label' => $x['label'], 'value' => $x['value']])
                    ->all();
                break;
        }

        return $payload;
    }

    /**
     * Vehículos filtrados por acceso POR SECRETARÍA (pivote depositos_secretarias)
     * + filtros de corralón/depósito. El acceso de vehículos no usa id_deposito.
     * Admin sin filtros de ubicación => todos los vehículos.
     */
    private function vehiculosFiltrados()
    {
        $user = Auth::user();
        $query = Vehiculo::query();

        if (!$user->esAdministrador() || $this->filtro_corralon || $this->filtro_deposito) {
            $secretarias = DB::table('depositos_secretarias')
                ->whereIn('id_deposito', $this->depositosConstraint('vehiculos'))
                ->pluck('id_secretaria')->unique()->all();
            $query->whereIn('id_secretaria', $secretarias);
        }

        return $query->get();
    }

    /**
     * Colección de movimientos de insumos (con relaciones) filtrada por
     * permisos + corralón/depósito + rango de fechas. Cacheada por render.
     */
    private function movimientos(&$cache)
    {
        if ($cache !== null) return $cache;

        $insumoIds = Insumo::whereIn('id_deposito', $this->depositosConstraint('movimientos_insumos'))->pluck('id');
        [$desde, $hasta] = $this->rangoFechas();

        $cache = MovimientoInsumo::with(['tipoMovimiento', 'insumo:id,insumo', 'usuario:id,name'])
            ->whereIn('id_insumo', $insumoIds)
            ->when($desde, fn($q) => $q->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn($q) => $q->whereDate('fecha', '<=', $hasta))
            ->get();

        return $cache;
    }

    public function render()
    {
        $user = Auth::user();

        $activos = $user->estadisticasActivas();
        $config  = config('estadisticas.widgets', []);
        $grupos  = config('estadisticas.grupos', []);

        // Cache de movimientos compartido entre los widgets de movimientos
        $movsCache = null;

        $widgets = [];
        foreach ($activos as $key) {
            if (!isset($config[$key])) continue;
            $widgets[$key] = $this->calcular($key, $config[$key], $movsCache);
        }

        // Opciones del modal (filtradas por permiso), agrupadas
        $opcionesPorGrupo = [];
        foreach ($config as $key => $cfg) {
            if (!$user->tieneAccesoAModulo($cfg['permiso'])) continue;
            $opcionesPorGrupo[$cfg['grupo']][$key] = $cfg;
        }

        // Selects de filtros: corralones y depósitos accesibles al usuario
        $corralonesPermitidos = $user->getCorralonesPermitidosIds();
        $corralones = Corralon::when(!$user->esAdministrador(), fn($q) => $q->whereIn('id', $corralonesPermitidos))
            ->orderBy('descripcion')->get();

        $depositos = Deposito::with('corralon')
            ->when(!$user->esAdministrador(), fn($q) => $q->whereIn('id', $this->depositosAccesibles()))
            ->when($this->filtro_corralon, fn($q) => $q->where('id_corralon', $this->filtro_corralon))
            ->orderBy('deposito')->get();

        return view('livewire.estadisticas', [
            'widgets'          => $widgets,
            'config'           => $config,
            'grupos'           => $grupos,
            'opcionesPorGrupo' => $opcionesPorGrupo,
            'corralones'       => $corralones,
            'depositos'        => $depositos,
            'hayWidgets'       => count($widgets) > 0,
            'sinOpciones'      => count($opcionesPorGrupo) === 0,
        ])->layout('layouts.app', ['header' => 'Estadísticas']);
    }

    /** Unión de depósitos accesibles del usuario en los módulos con ubicación. */
    private function depositosAccesibles(): array
    {
        $modulos = ['insumos', 'maquinarias', 'vehiculos', 'movimientos_insumos', 'depositos'];
        $ids = [];
        foreach ($modulos as $m) {
            $ids = array_merge($ids, Auth::user()->getDepositosPermitidosParaModulo($m));
        }
        return array_values(array_unique($ids));
    }
}
