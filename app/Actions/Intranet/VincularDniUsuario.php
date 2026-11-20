<?php

namespace App\Actions\Intranet;

use App\Models\User;
use App\Support\ActorIntranet;
use Illuminate\Validation\ValidationException;

/**
 * Le carga el DNI a un usuario que ya existe: lo pide la intranet para vincular a los
 * empleados que ya tienen usuario en el corralón
 * (`PATCH /api/intranet/v1/usuarios/{dni}/vinculo`, buscado por email).
 *
 * Desde la intranet no se toca a un Administrador (422 en `email`: su DNI se carga a
 * mano desde Usuarios). El usuario no puede tener ya otro DNI (422 en `email`) y el DNI
 * no puede estar en otro usuario (422 en `dni`). Volver a vincular el mismo DNI no
 * cambia nada ni deja Actividad.
 */
class VincularDniUsuario
{
    /** @throws ValidationException */
    public function ejecutar(User $usuario, int $dni, ActorIntranet $actor): User
    {
        if ($usuario->esAdministrador()) {
            $this->rechazar('email', __('intranet.errores.administrador_vinculo'));
        }
        if ($usuario->dni !== null && $usuario->dni !== $dni) {
            $this->rechazar('email', __('intranet.errores.email_con_otro_dni', ['dni' => $usuario->dni]));
        }
        if (User::where('dni', $dni)->whereKeyNot($usuario->id)->exists()) {
            $this->rechazar('dni', __('intranet.errores.dni_en_otro_usuario'));
        }

        if ($usuario->dni === $dni) {
            return $usuario;
        }

        $actor->registrando(function () use ($usuario, $dni) {
            $usuario->dni = $dni;
            $usuario->save();
        });

        return $usuario;
    }

    private function rechazar(string $campo, string $mensaje): never
    {
        throw ValidationException::withMessages([$campo => $mensaje]);
    }
}
