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

    private function filtrarPorDepositos($query, $user, string $modulo)
    {
        if ($user->esAdministrador()) return $query;
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

        $vtvProximasVencer    = collect();
        $countVtvProximasVencer = 0;
        if (in_array('vtv_vencer', $widgetsActivos)) {
            $fechaLimite = Carbon::now()->addDays(30)->endOfDay();
            $query = Vehiculo::with(['deposito.corralon'])
                ->whereNotNull('vencimiento_vtv')
                ->where('vencimiento_vtv', '<=', $fechaLimite);
            $vtvProximasVencer = $this->filtrarPorDepositos($query, $user, 'vehiculos')
                ->orderBy('vencimiento_vtv', 'asc')
                ->get();
            $countVtvProximasVencer = $vtvProximasVencer->count();
        }

        $vehiculosEnUso    = collect();
        $countVehiculosEnUso = 0;
        if (in_array('vehiculos_en_uso', $widgetsActivos)) {
            $query = Vehiculo::with(['deposito.corralon'])
                ->where('estado', 'en_uso');
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
            'vtvProximasVencer'    => $vtvProximasVencer,
            'vehiculosEnUso'       => $vehiculosEnUso,
            'proximosEventos'      => $proximosEventos,
            'countInsumosBajoMinimo'    => $countInsumosBajoMinimo,
            'countVtvProximasVencer'    => $countVtvProximasVencer,
            'countVehiculosEnUso'       => $countVehiculosEnUso,
            'countProximosEventosWidget' => $countProximosEventosWidget,
            'opcionesCards'   => $opcionesCards,
            'opcionesWidgets' => $opcionesWidgets,
        ]);
    }
}
