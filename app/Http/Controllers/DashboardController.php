<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Maquinaria;
use App\Models\Empleado;
use App\Models\Vehiculo;

class DashboardController extends Controller
{
    public function index()
    {
        // Contadores simples
        $totalInsumos = Insumo::count();
        $totalMaquinaria = Maquinaria::count();
        $totalEmpleados = Empleado::count();
        $totalVehiculos = Vehiculo::count();

        return view('dashboard', compact(
            'totalInsumos',
            'totalMaquinaria',
            'totalEmpleados',
            'totalVehiculos'
        ));
    }
}