<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // Solo la API para la intranet (/api/intranet/v1), sin Sanctum.
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Un usuario desactivado no sigue adentro con una sesión vieja.
        $middleware->web(append: [
            \App\Http\Middleware\CerrarSesionInactivos::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // La API responde siempre JSON (422 con errors, 404, 429), aunque falte el Accept.
        $exceptions->shouldRenderJsonWhen(fn (\Illuminate\Http\Request $request) => $request->is('api/*') || $request->expectsJson());
    })->create();
