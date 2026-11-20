<?php

namespace App\Actions\Intranet;

use App\Actions\Usuarios\GuardarUsuario;
use App\Models\User;
use App\Support\ActorIntranet;
use Illuminate\Validation\ValidationException;

/**
 * Activa o desactiva un usuario desde la intranet: lo dispara la baja del empleado en
 * la intranet (`PATCH /api/intranet/v1/usuarios/{dni}/estado`).
 *
 * Desde la intranet no se activa ni desactiva a un Administrador (422 en `activo`), y
 * se sostiene la guarda de GuardarUsuario (siempre queda un Administrador activo).
 * Desactivarlo le cierra las sesiones (User::booted). Pedir el estado que ya tiene no
 * cambia nada ni deja Actividad.
 */
class CambiarEstadoUsuario
{
    /** @throws ValidationException */
    public function ejecutar(User $usuario, bool $activo, ActorIntranet $actor): User
    {
        if ($usuario->esAdministrador()) {
            throw ValidationException::withMessages(['activo' => __('intranet.errores.administrador_estado')]);
        }

        if ($usuario->activo === $activo) {
            return $usuario;
        }

        GuardarUsuario::validarQueQuedeAdministrador($usuario, false, $activo, null);

        $actor->registrando(function () use ($usuario, $activo) {
            $usuario->activo = $activo;
            $usuario->save();
        });

        return $usuario;
    }
}
