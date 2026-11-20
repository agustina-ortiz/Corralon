<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

/**
 * Receptor del SSO por ticket de la intranet (contrato en intranet/docs/integracion.md
 * §1 y §5): `GET /sso/ingresar?ticket=…`.
 *
 * Canjea el ticket contra la intranet (`POST {INTRANET_URL}/api/sso/v1/canjes`, con la
 * clave de servicio, 5 s de tope) y entra con el usuario que tenga ese DNI, si está
 * activo.
 *
 * Cualquier falla (sin ticket, red, 422, DNI desconocido, inactivo) vuelve al login con
 * un único mensaje, sin decir cuál fue. Si había alguien con sesión, esa sesión se
 * cierra antes de validar: en una PC compartida, un ticket fallido no deja adentro al
 * usuario anterior. El corralón no tiene "Uso del panel": el ingreso no se registra
 * aparte.
 */
class IngresoSso extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $usuario = $this->usuarioDelTicket($request->query('ticket'));

        if ($usuario === null) {
            return redirect()->route('login')->with('error', __('intranet.sso_rechazado'));
        }

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function usuarioDelTicket(mixed $ticket): ?User
    {
        $url = config('intranet.url');
        $clave = config('intranet.clave_servicio');

        if (! is_string($ticket) || $ticket === '' || strlen($ticket) > 255 || $url === '' || $clave === '') {
            return null;
        }

        try {
            $respuesta = Http::timeout(5)
                ->acceptJson()
                ->withToken($clave)
                ->post($url.'/api/sso/v1/canjes', ['ticket' => $ticket]);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }

        $dni = filter_var($respuesta->json('dni'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($respuesta->status() !== 200 || $dni === false) {
            return null;
        }

        return User::query()
            ->where('dni', $dni)
            ->where('activo', true)
            ->first();
    }
}
