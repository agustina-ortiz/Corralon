<?php

use App\Http\Controllers\Api\Intranet\UsuarioController;
use App\Http\Middleware\ClaveServicioIntranet;
use Illuminate\Support\Facades\Route;

/*
 * API de usuarios para la intranet municipal (guía en docs/api-intranet.md; contrato
 * en intranet/docs/integracion.md §5). **Sin Sanctum**: es entre servidores, con la
 * clave de servicio y la lista de IPs del .env (config/intranet.php).
 */
Route::prefix('intranet/v1')->name('api.intranet.v1.')
    ->middleware([ClaveServicioIntranet::class, 'throttle:60,1'])
    ->group(function () {
        Route::get('alta/opciones', [UsuarioController::class, 'opciones'])->name('alta.opciones');
        Route::post('usuarios', [UsuarioController::class, 'crear'])->name('usuarios.crear');
        Route::get('usuarios/{dni}', [UsuarioController::class, 'ver'])->where('dni', '[0-9]{1,9}')->name('usuarios.ver');
        Route::patch('usuarios/{dni}/vinculo', [UsuarioController::class, 'vincular'])->where('dni', '[0-9]{1,9}')->name('usuarios.vinculo');
        Route::patch('usuarios/{dni}/estado', [UsuarioController::class, 'estado'])->where('dni', '[0-9]{1,9}')->name('usuarios.estado');
    });
