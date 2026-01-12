<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Insumo;
use App\Models\Maquinaria;
use App\Models\Vehiculo;
use App\Models\Evento;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();

        // ============= TOTALES GENERALES =============
        
        // Total Insumos (filtrado por permisos)
        $totalInsumos = Insumo::when(!$user->acceso_todos_corralones, function($query) use ($user) {
            $query->whereHas('deposito', function($q) use ($user) {
                $q->whereIn('id_corralon', $user->corralones_permitidos ?? []);
            });
        })->count();

        // Total Maquinaria (filtrado por permisos)
        $totalMaquinaria = Maquinaria::when(!$user->acceso_todos_corralones, function($query) use ($user) {
            $query->whereHas('deposito', function($q) use ($user) {
                $q->whereIn('id_corralon', $user->corralones_permitidos ?? []);
            });
        })->count();

        // Total Vehículos (filtrado por permisos)
        $totalVehiculos = Vehiculo::when(!$user->acceso_todos_corralones, function($query) use ($user) {
            $query->whereHas('deposito', function($q) use ($user) {
                $q->whereIn('id_corralon', $user->corralones_permitidos ?? []);
            });
        })->count();

        // ============= ESTADÍSTICAS DE ALERTA =============
        
        // Insumos con stock bajo el mínimo
        $insumosBajoMinimo = Insumo::with(['categoriaInsumo', 'deposito.corralon'])
            ->whereColumn('stock_actual', '<', 'stock_minimo')
            ->when(!$user->acceso_todos_corralones, function($query) use ($user) {
                $query->whereHas('deposito', function($q) use ($user) {
                    $q->whereIn('id_corralon', $user->corralones_permitidos ?? []);
                });
            })
            ->orderBy('stock_actual', 'asc')
            ->get();

        // Maquinaria no disponible (estado != 'Disponible')
        $maquinariaNoDisponible = Maquinaria::with(['categoriaMaquinaria', 'deposito.corralon'])
            ->where('estado', '!=', 'Disponible')
            ->when(!$user->acceso_todos_corralones, function($query) use ($user) {
                $query->whereHas('deposito', function($q) use ($user) {
                    $q->whereIn('id_corralon', $user->corralones_permitidos ?? []);
                });
            })
            ->orderBy('estado', 'asc')
            ->get();

        // Vehículos en uso
        $vehiculosEnUso = Vehiculo::with(['deposito.corralon'])
            ->where('estado', 'en_uso')
            ->when(!$user->acceso_todos_corralones, function($query) use ($user) {
                $query->whereHas('deposito', function($q) use ($user) {
                    $q->whereIn('id_corralon', $user->corralones_permitidos ?? []);
                });
            })
            ->orderBy('nro_movil', 'asc')
            ->get();

        // Próximos eventos (ordenados por fecha)
        $proximosEventos = Evento::where('fecha', '>=', now())
            ->orderBy('fecha', 'asc')
            ->get();

        // ============= CONTADORES PARA BADGES =============
        $countInsumosBajoMinimo = Insumo::whereColumn('stock_actual', '<', 'stock_minimo')
            ->when(!$user->acceso_todos_corralones, function($query) use ($user) {
                $query->whereHas('deposito', function($q) use ($user) {
                    $q->whereIn('id_corralon', $user->corralones_permitidos ?? []);
                });
            })
            ->count();

        $countMaquinariaNoDisponible = Maquinaria::where('estado', '!=', 'Disponible')
            ->when(!$user->acceso_todos_corralones, function($query) use ($user) {
                $query->whereHas('deposito', function($q) use ($user) {
                    $q->whereIn('id_corralon', $user->corralones_permitidos ?? []);
                });
            })
            ->count();

        $countVehiculosEnUso = Vehiculo::where('estado', 'en_uso')
            ->when(!$user->acceso_todos_corralones, function($query) use ($user) {
                $query->whereHas('deposito', function($q) use ($user) {
                    $q->whereIn('id_corralon', $user->corralones_permitidos ?? []);
                });
            })
            ->count();

        $countProximosEventos = Evento::where('fecha', '>=', now())->count();

        return view('livewire.dashboard', [
            // Totales
            'totalInsumos' => $totalInsumos,
            'totalMaquinaria' => $totalMaquinaria,
            'totalVehiculos' => $totalVehiculos,
            
            // Estadísticas detalladas
            'insumosBajoMinimo' => $insumosBajoMinimo,
            'maquinariaNoDisponible' => $maquinariaNoDisponible,
            'vehiculosEnUso' => $vehiculosEnUso,
            'proximosEventos' => $proximosEventos,
            
            // Contadores
            'countInsumosBajoMinimo' => $countInsumosBajoMinimo,
            'countMaquinariaNoDisponible' => $countMaquinariaNoDisponible,
            'countVehiculosEnUso' => $countVehiculosEnUso,
            'countProximosEventos' => $countProximosEventos,
        ]);
    }
}