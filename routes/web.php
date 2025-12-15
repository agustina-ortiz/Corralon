<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Livewire\AbmInsumos;
use App\Livewire\AbmCategoriasInsumos;

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

Route::get('/categorias-insumos', AbmCategoriasInsumos::class)
    ->middleware('auth')
    ->name('categorias-insumos');

require __DIR__.'/auth.php';
