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

        if ($user->esAdministrador()) {
            $totalInsumos = Insumo::count();
            $totalMaquinaria = Maquinaria::count();
            $totalVehiculos = Vehiculo::count();
        } else {
            $depositosInsumos = $user->getDepositosPermitidosParaModulo('insumos');
            $depositosMaquinarias = $user->getDepositosPermitidosParaModulo('maquinarias');
            $depositosVehiculos = $user->getDepositosPermitidosParaModulo('vehiculos');

            $totalInsumos = !empty($depositosInsumos) ? Insumo::whereIn('id_deposito', $depositosInsumos)->count() : 0;
            $totalMaquinaria = !empty($depositosMaquinarias) ? Maquinaria::whereIn('id_deposito', $depositosMaquinarias)->count() : 0;
            $totalVehiculos = !empty($depositosVehiculos) ? Vehiculo::whereIn('id_deposito', $depositosVehiculos)->count() : 0;
        }

        return view('dashboard', compact(
            'totalInsumos',
            'totalMaquinaria',
            'totalVehiculos'
        ));
    }
}
