<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Maquinaria;
use App\Models\Empleado;
use App\Models\Vehiculo;
use App\Models\Deposito;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Obtener los IDs de corralones a los que el usuario tiene acceso
        $corralonesAccesibles = $user->getCorralonesPermitidosIds();
        
        // Si el usuario tiene acceso a todos los corralones
        $mostrarTodos = $user->acceso_todos_corralones;
        
        if ($mostrarTodos) {
            // Mostrar todos los datos del sistema
            $totalInsumos = Insumo::count();
            $totalMaquinaria = Maquinaria::count();
            $totalVehiculos = Vehiculo::count();
        } else {
            // Obtener los IDs de depósitos que pertenecen a los corralones accesibles
            $depositosAccesibles = Deposito::whereIn('id_corralon', $corralonesAccesibles)
                ->pluck('id');
            
            // Total Insumos - filtrar por depósitos de los corralones accesibles
            $totalInsumos = Insumo::whereIn('id_deposito', $depositosAccesibles)->count();
            
            // Total Maquinaria - filtrar directamente por corralón
            $totalMaquinaria = Maquinaria::whereIn('id_deposito', $depositosAccesibles)->count();
            
            // Total Vehículos - filtrar directamente por corralón
            $totalVehiculos = Vehiculo::whereIn('id_deposito', $depositosAccesibles)->count();
        }
        
        return view('dashboard', compact(
            'totalInsumos',
            'totalMaquinaria',
            'totalVehiculos'
        ));
    }
}