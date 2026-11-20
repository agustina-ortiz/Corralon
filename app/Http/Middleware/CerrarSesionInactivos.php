<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Un usuario desactivado (`users.activo = false`) no sigue adentro: si todavía tiene
 * una sesión abierta (o entra por "Recordarme"), en su próximo pedido se le cierra y
 * vuelve al login. Va en el grupo `web`, así cubre también los pedidos de Livewire.
 */
class CerrarSesionInactivos
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = Auth::guard('web')->user();

        if ($usuario !== null && $usuario->activo === false) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                abort(401);
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
