<?php

namespace App\Support;

use Spatie\Activitylog\ActivityLogStatus;

/**
 * Corre un proceso con el registro de actividad apagado y lo vuelve a su estado al
 * terminar, aunque falle (kit, spec 38): los seeders, el recálculo de stocks y el
 * rehash de claves no son acciones de nadie.
 *
 * `php artisan db:seed` ya lo apaga solo (AppServiceProvider, CommandStarting); esto
 * cubre a los seeders y comandos llamados de cualquier otra forma, como los tests o
 * `Artisan::call()`.
 */
final class SinActividad
{
    public static function correr(callable $proceso): mixed
    {
        $estado = app(ActivityLogStatus::class);
        $estabaApagado = $estado->disabled();
        $estado->disable();

        try {
            return $proceso();
        } finally {
            if (! $estabaApagado) {
                $estado->enable();
            }
        }
    }
}
