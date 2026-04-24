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
        ];
        $user->save();

        $this->modalPersonalizar = false;
    }

    public function render()
    {
        $user = Auth::user();

        $cardsActivas   = $user->dashboardActivosPara('cards');
        $widgetsActivos = $user->dashboardActivosPara('widgets');

        // ============= TOTALES GENERALES =============

        $totalInsumos = in_array('card_insumos', $cardsActivas)
            ? Insumo::when(!$user->acceso_todos_corralones, function ($query) use ($user) {
                $query->whereHas('deposito', fn($q) => $q->whereIn('id_corralon', $user->corralones_permitidos ?? []));
            })->count()
            : null;

        $totalMaquinaria = in_array('card_maquinaria', $cardsActivas)
            ? Maquinaria::when(!$user->acceso_todos_corralones, function ($query) use ($user) {
                $query->whereHas('deposito', fn($q) => $q->whereIn('id_corralon', $user->corralones_permitidos ?? []));
            })->count()
            : null;

        $totalVehiculos = in_array('card_vehiculos', $cardsActivas)
            ? Vehiculo::when(!$user->acceso_todos_corralones, function ($query) use ($user) {
                $query->whereHas('deposito', fn($q) => $q->whereIn('id_corralon', $user->corralones_permitidos ?? []));
            })->count()
            : null;

        $countProximosEventos = in_array('card_eventos', $cardsActivas)
            ? Evento::where('fecha', '>=', now())->count()
            : null;

        // ============= WIDGETS DE ALERTA =============

        $insumosBajoMinimo    = collect();
        $countInsumosBajoMinimo = 0;
        if (in_array('stock_bajo', $widgetsActivos)) {
            $insumosBajoMinimo = Insumo::with(['categoriaInsumo', 'deposito.corralon'])
                ->whereColumn('stock_actual', '<', 'stock_minimo')
                ->when(!$user->acceso_todos_corralones, function ($query) use ($user) {
                    $query->whereHas('deposito', fn($q) => $q->whereIn('id_corralon', $user->corralones_permitidos ?? []));
                })
                ->orderBy('stock_actual', 'asc')
                ->get();
            $countInsumosBajoMinimo = $insumosBajoMinimo->count();
        }

        $vtvProximasVencer    = collect();
        $countVtvProximasVencer = 0;
        if (in_array('vtv_vencer', $widgetsActivos)) {
            $fechaLimite = Carbon::now()->addDays(30)->endOfDay();
            $vtvProximasVencer = Vehiculo::with(['deposito.corralon'])
                ->whereNotNull('vencimiento_vtv')
                ->where('vencimiento_vtv', '<=', $fechaLimite)
                ->when(!$user->acceso_todos_corralones, function ($query) use ($user) {
                    $query->whereHas('deposito', fn($q) => $q->whereIn('id_corralon', $user->corralones_permitidos ?? []));
                })
                ->orderBy('vencimiento_vtv', 'asc')
                ->get();
            $countVtvProximasVencer = $vtvProximasVencer->count();
        }

        $vehiculosEnUso    = collect();
        $countVehiculosEnUso = 0;
        if (in_array('vehiculos_en_uso', $widgetsActivos)) {
            $vehiculosEnUso = Vehiculo::with(['deposito.corralon'])
                ->where('estado', 'en_uso')
                ->when(!$user->acceso_todos_corralones, function ($query) use ($user) {
                    $query->whereHas('deposito', fn($q) => $q->whereIn('id_corralon', $user->corralones_permitidos ?? []));
                })
                ->orderBy('nro_movil', 'asc')
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

        // Opciones del modal filtradas por permiso
        $opcionesCards   = array_filter(
            config('dashboard.cards'),
            fn($c) => $user->rol?->{$c['permiso']}
        );
        $opcionesWidgets = array_filter(
            config('dashboard.widgets'),
            fn($w) => $user->rol?->{$w['permiso']}
        );

        return view('livewire.dashboard', [
            // Activos
            'cardsActivas'   => $cardsActivas,
            'widgetsActivos' => $widgetsActivos,

            // Totales
            'totalInsumos'    => $totalInsumos,
            'totalMaquinaria' => $totalMaquinaria,
            'totalVehiculos'  => $totalVehiculos,
            'countProximosEventos' => $countProximosEventos,

            // Widgets detallados
            'insumosBajoMinimo'    => $insumosBajoMinimo,
            'vtvProximasVencer'    => $vtvProximasVencer,
            'vehiculosEnUso'       => $vehiculosEnUso,
            'proximosEventos'      => $proximosEventos,

            // Contadores de badges
            'countInsumosBajoMinimo'    => $countInsumosBajoMinimo,
            'countVtvProximasVencer'    => $countVtvProximasVencer,
            'countVehiculosEnUso'       => $countVehiculosEnUso,
            'countProximosEventosWidget' => $countProximosEventosWidget,

            // Opciones para el modal
            'opcionesCards'   => $opcionesCards,
            'opcionesWidgets' => $opcionesWidgets,
        ]);
    }
}
