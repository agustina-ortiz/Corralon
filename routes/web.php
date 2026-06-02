<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Livewire\AbmInsumos;
use App\Livewire\AbmCategoriasInsumos;
use App\Livewire\AbmMaquinarias;
use App\Livewire\AbmCategoriasMaquinarias;
use App\Livewire\AbmDepositos;
use App\Livewire\TransferenciasInsumos;
use App\Livewire\AbmChoferes;
use App\Livewire\AbmEmpleados;
use App\Livewire\AbmVehiculos;
use App\Livewire\AbmEventos;
use App\Livewire\AbmSecretarias;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/insumos', AbmInsumos::class)
    ->middleware(['auth'])
    ->name('insumos');

Route::get('/maquinarias', AbmMaquinarias::class)
    ->middleware(['auth'])
    ->name('maquinarias');

Route::get('/categorias-insumos', AbmCategoriasInsumos::class)
    ->middleware(['auth'])
    ->name('categorias-insumos');

Route::get('/categorias-maquinarias', AbmCategoriasMaquinarias::class)
    ->middleware(['auth'])
    ->name('categorias-maquinarias');

Route::get('/depositos', AbmDepositos::class)
    ->middleware(['auth'])
    ->name('depositos');

Route::get('/eventos', AbmEventos::class)
    ->middleware(['auth'])
    ->name('eventos');

Route::get('/transferencias-insumos', TransferenciasInsumos::class)
    ->middleware(['auth'])
    ->name('transferencias-insumos');

Route::get('/transferencias-maquinarias', \App\Livewire\TransferenciasMaquinarias::class)
    ->middleware(['auth'])
    ->name('transferencias-maquinarias');

Route::get('/choferes', AbmChoferes::class)
    ->middleware(['auth'])
    ->name('choferes');

Route::get('/empleados', AbmEmpleados::class)
    ->middleware(['auth'])
    ->name('empleados');

Route::get('/vehiculos', AbmVehiculos::class)
    ->middleware(['auth'])
    ->name('vehiculos');

Route::get('/secretarias', AbmSecretarias::class)
    ->middleware(['auth'])
    ->name('secretarias');

Route::get('/usuarios', \App\Livewire\AbmUsuarios::class)
    ->middleware(['auth'])
    ->name('usuarios');

Route::get('/comprobantes/{comprobante}/ver', function (App\Models\ComprobanteMovimiento $comprobante) {
    $path = storage_path('app/private/comprobantes/' . $comprobante->archivo);
    return response()->file($path, ['Content-Type' => $comprobante->tipo_mime]);
})->middleware(['auth'])->name('comprobantes.ver');

Route::get('/comprobantes/{comprobante}/descargar', function (App\Models\ComprobanteMovimiento $comprobante) {
    return response()->download(
        storage_path('app/private/comprobantes/' . $comprobante->archivo),
        $comprobante->nombre_original
    );
})->middleware(['auth'])->name('comprobantes.descargar');

Route::get('/comprobantes-maquinaria/{comprobante}/ver', function (App\Models\ComprobanteMovimientoMaquinaria $comprobante) {
    $path = storage_path('app/private/comprobantes-maquinaria/' . $comprobante->archivo);
    return response()->file($path, ['Content-Type' => $comprobante->tipo_mime]);
})->middleware(['auth'])->name('comprobantes-maquinaria.ver');

Route::get('/comprobantes-maquinaria/{comprobante}/descargar', function (App\Models\ComprobanteMovimientoMaquinaria $comprobante) {
    return response()->download(
        storage_path('app/private/comprobantes-maquinaria/' . $comprobante->archivo),
        $comprobante->nombre_original
    );
})->middleware(['auth'])->name('comprobantes-maquinaria.descargar');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    return redirect('/login');
})->name('logout');


require __DIR__.'/auth.php';
