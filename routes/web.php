<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Livewire\AbmInsumos;
use App\Livewire\AbmCategoriasInsumos;
use App\Livewire\AbmMaquinarias;
use App\Livewire\AbmCategoriasMaquinarias;
use App\Livewire\AbmDepositos;
use App\Livewire\AbmEmpleados;
use App\Livewire\AbmVehiculos;

Route::view('/', 'welcome');

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

Route::get('/empleados', AbmEmpleados::class)
    ->middleware(['auth'])
    ->name('empleados');

Route::get('/vehiculos', AbmVehiculos::class)
    ->middleware(['auth'])
    ->name('vehiculos');


require __DIR__.'/auth.php';
