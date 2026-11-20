<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Puerta de la API `/api/intranet/v1` (contrato en intranet/docs/integracion.md §5).
 * Es entre servidores: sin Sanctum ni sesión.
 *
 * - IP fuera de `config('intranet.ips')` → 403. Lista vacía = no se controla IP (solo
 *   local y tests). Va primero: desde afuera no se puede ni probar claves.
 * - Sin `Authorization: Bearer`, o con una clave distinta de la de servicio → 401
 *   `{"message": "No autorizado."}`. Se compara con `hash_equals`. Sin clave
 *   configurada, rechaza todo.
 */
class ClaveServicioIntranet
{
    public function handle(Request $request, Closure $next): Response
    {
        $ips = (array) config('intranet.ips', []);

        if ($ips !== [] && ! in_array($request->ip(), $ips, true)) {
            return response()->json(['message' => __('intranet.api.ip_no_permitida')], 403);
        }

        $clave = (string) config('intranet.clave_servicio');
        $enviada = (string) $request->bearerToken();

        if ($clave === '' || $enviada === '' || ! hash_equals($clave, $enviada)) {
            return response()->json(['message' => __('intranet.api.no_autorizado')], 401);
        }

        return $next($request);
    }
}
