<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity;

/**
 * Un registro de la Actividad (tabla `activity_log` de spatie/laravel-activitylog).
 *
 * Es el modelo del paquete con una sola diferencia: si no puede grabarse (la tabla
 * todavía no existe porque falta correr la migración, la base rechaza algo), lo manda
 * a `report()` y sigue. La auditoría nunca frena el guardado del usuario o del
 * permiso que la provocó.
 */
class Actividad extends Activity
{
    public function save(array $options = []): bool
    {
        try {
            return parent::save($options);
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}
