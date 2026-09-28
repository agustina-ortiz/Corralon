<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Insumo;
use App\Models\Maquinaria;
use App\Models\Vehiculo;
use App\Models\Evento;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Dashboard extends Component
{
    public bool $modalPersonalizar = false;
    public array $seleccionCards   = [];
    public array $seleccionWidgets = [];

    public function abrirModalPersonalizar(): void
    {
        $user = Auth::user();

        $this->seleccionCards   = $user->dashboardActivosPara('cards');
        $this->seleccionWidgets = $user->dashboardActivosPara('widgets');

        $this->modalPersonalizar = true;
    }

    public function guardarPreferencias(): void
    {
        $user = Auth::user();
        $user->dashboard_widgets = [
            'cards'   => $this->seleccionCards,
            'widgets' => $this->seleccionWidgets,
            // Claves existentes al guardar: las que se agreguen después se activan solas
            'conocidos' => [
                'cards'   => array_keys(config('dashboard.cards')),
                'widgets' => array_keys(config('dashboard.widgets')),
            ],
        ];
        $user->save();

        $this->modalPersonalizar = false;
    }

    private function filtrarPorDepositos($query, $user, string $modulo)
    {
        if ($user->esAdministrador()) return $query;
        // Vehículos: acceso global o por depósito/secretaría (no solo id_deposito)
        if ($modulo === 'vehiculos') return $query->porCorralonesPermitidos();
        $depositos = $user->getDepositosPermitidosParaModulo($modulo);
        return $query->whereIn('id_deposito', $depositos);
    }

    public function render()
    {
        $user = Auth::user();

        $cardsActivas   = $user->dashboardActivosPara('cards');
        $widgetsActivos = $user->dashboardActivosPara('widgets');

        // ============= TOTALES GENERALES =============

        $totalInsumos = in_array('card_insumos', $cardsActivas)
            ? $this->filtrarPorDepositos(Insumo::query(), $user, 'insumos')->count()
            : null;

        $totalMaquinaria = in_array('card_maquinaria', $cardsActivas)
            ? $this->filtrarPorDepositos(Maquinaria::query(), $user, 'maquinarias')->count()
            : null;

        $totalVehiculos = in_array('card_vehiculos', $cardsActivas)
            ? $this->filtrarPorDepositos(Vehiculo::query(), $user, 'vehiculos')->count()
            : null;

        $countProximosEventos = in_array('card_eventos', $cardsActivas)
            ? Evento::where('fecha', '>=', now())->count()
            : null;

        // ============= WIDGETS DE ALERTA =============

        $insumosBajoMinimo    = collect();
        $countInsumosBajoMinimo = 0;
        if (in_array('stock_bajo', $widgetsActivos)) {
            $query = Insumo::with(['categoriaInsumo', 'deposito.corralon'])
                ->whereColumn('stock_actual', '<', 'stock_minimo');
            $insumosBajoMinimo = $this->filtrarPorDepositos($query, $user, 'insumos')
                ->orderBy('stock_actual', 'asc')
                ->get();
            $countInsumosBajoMinimo = $insumosBajoMinimo->count();
        }

        // Vencimientos de vehículos (VTV / póliza / oblea): widget => [alerta de vencimiento, alerta de fecha faltante]
        $widgetsVencimiento = [
            'vtv_vencer'    => ['alerta' => 'vtv_vencer',    'sin_fecha' => 'sin_venc_vtv',    'titulo' => 'VTVs Próximas a Vencer',       'documento' => 'VTV'],
            'poliza_vencer' => ['alerta' => 'poliza_vencer', 'sin_fecha' => 'sin_venc_poliza', 'titulo' => 'Pólizas Próximas a Vencer',    'documento' => 'póliza'],
            'oblea_vencer'  => ['alerta' => 'oblea_vencer',  'sin_fecha' => 'sin_venc_oblea',  'titulo' => 'Obleas GNC Próximas a Vencer', 'documento' => 'oblea GNC'],
        ];
        $vencimientos = [];
        foreach ($widgetsVencimiento as $key => $def) {
            if (!in_array($key, $widgetsActivos)) continue;

            $campo = Vehiculo::ALERTAS[$def['alerta']]['campo'];
            $vehiculos = $this->filtrarPorDepositos(Vehiculo::with('secretaria')->conAlerta($def['alerta']), $user, 'vehiculos')
                ->orderBy($campo, 'asc')
                ->get();

            $vencimientos[$key] = $def + [
                'campo'     => $campo,
                'vehiculos' => $vehiculos,
                'vencidos'  => $vehiculos->filter(fn($v) => $v->$campo->lt(Carbon::today()))->count(),
                'cantSinFecha' => $this->filtrarPorDepositos(Vehiculo::conAlerta($def['sin_fecha']), $user, 'vehiculos')->count(),
            ];
        }

        // Contadores por alerta (datos incompletos / a revisar); se ocultan los que dan 0
        $contarAlertas = function (string $grupo) use ($user) {
            return collect(Vehiculo::ALERTAS)
                ->filter(fn($a) => $a['grupo'] === $grupo)
                ->map(fn($a, $clave) => $a + [
                    'clave'    => $clave,
                    'cantidad' => $this->filtrarPorDepositos(Vehiculo::conAlerta($clave), $user, 'vehiculos')->count(),
                ])
                ->filter(fn($a) => $a['cantidad'] > 0)
                ->values();
        };
        $vehiculosDatos   = in_array('vehiculos_datos', $widgetsActivos)   ? $contarAlertas('datos')      : collect();
        $vehiculosRevisar = in_array('vehiculos_revisar', $widgetsActivos) ? $contarAlertas('operativos') : collect();
        $totalVehiculosActivos = in_array('vehiculos_datos', $widgetsActivos)
            ? $this->filtrarPorDepositos(Vehiculo::noDadosDeBaja(), $user, 'vehiculos')->count()
            : 0;

        $vehiculosEnUso    = collect();
        $countVehiculosEnUso = 0;
        if (in_array('vehiculos_en_uso', $widgetsActivos)) {
            $query = Vehiculo::with(['secretaria'])
                ->where('estado', 'EN USO');
            $vehiculosEnUso = $this->filtrarPorDepositos($query, $user, 'vehiculos')
                ->orderBy('nro_patrimonio', 'asc')
                ->get();
            $countVehiculosEnUso = $vehiculosEnUso->count();
        }

        $proximosEventos    = collect();
        $countProximosEventosWidget = 0;
        if (in_array('proximos_eventos', $widgetsActivos)) {
            $proximosEventos = Evento::where('fecha', '>=', now())
                ->orderBy('fecha', 'asc')
                ->get();
            $countProximosEventosWidget = $proximosEventos->count();
        }

        // Opciones del modal filtradas por permiso de modulo
        $opcionesCards = array_filter(
            config('dashboard.cards'),
            fn($c) => $user->tieneAccesoAModulo($c['permiso_modulo'])
        );
        $opcionesWidgets = array_filter(
            config('dashboard.widgets'),
            fn($w) => $user->tieneAccesoAModulo($w['permiso_modulo'])
        );

        return view('livewire.dashboard', [
            'cardsActivas'   => $cardsActivas,
            'widgetsActivos' => $widgetsActivos,
            'totalInsumos'    => $totalInsumos,
            'totalMaquinaria' => $totalMaquinaria,
            'totalVehiculos'  => $totalVehiculos,
            'countProximosEventos' => $countProximosEventos,
            'insumosBajoMinimo'    => $insumosBajoMinimo,
            'vencimientos'         => $vencimientos,
            'vehiculosDatos'       => $vehiculosDatos,
            'vehiculosRevisar'     => $vehiculosRevisar,
            'totalVehiculosActivos' => $totalVehiculosActivos,
            'vehiculosEnUso'       => $vehiculosEnUso,
            'proximosEventos'      => $proximosEventos,
            'countInsumosBajoMinimo'    => $countInsumosBajoMinimo,
            'countVehiculosEnUso'       => $countVehiculosEnUso,
            'countProximosEventosWidget' => $countProximosEventosWidget,
            'opcionesCards'   => $opcionesCards,
            'opcionesWidgets' => $opcionesWidgets,
        ]);
    }
}
