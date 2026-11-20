<?php

namespace App\Support;

use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Registra en el log de actividad (`activity_log`) los eventos created / updated /
 * deleted del modelo, con el usuario que los provocó (causer = el de la sesión; sin
 * sesión, "Sistema"; desde la API de la intranet, sin causer y con el actor en
 * `properties`). Kit, spec 38: un modelo que interesa auditar solo hace
 * `use RegistraActividad;`.
 *
 * Por ahora lo usan User y UsuarioPermiso (lo que toca la integración con la intranet).
 *
 * Un modelo puede sumar campos que no son `$fillable` (`$actividadAdemas`) o sacar
 * alguno (`$actividadSinRegistrar`). Lo que se escribe sin eventos de modelo (un
 * `update()` masivo, un `insert`) no queda registrado.
 *
 * La auditoría nunca frena un guardado: el nombre y el contexto van en un `try`, y el
 * modelo `App\Models\Actividad` (config/activitylog.php) traga y reporta cualquier
 * error al grabar el registro.
 */
trait RegistraActividad
{
    use LogsActivity;

    /** Ruido o datos sensibles: nunca van al registro. */
    private const ACTIVIDAD_SIEMPRE_FUERA = [
        'password', 'remember_token', 'created_at', 'updated_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnly($this->actividadAdemas ?? [])
            ->logExcept(array_merge(self::ACTIVIDAD_SIEMPRE_FUERA, $this->actividadSinRegistrar ?? []))
            ->logOnlyDirty()                // en una edición, solo lo que cambió
            ->dontSubmitEmptyLogs()         // una edición sin cambios no deja nada
            ->useLogName('sistema');
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        try {
            // Lo hecho desde la intranet: sin causer local, con el actor en properties.
            if ($actor = ActorIntranet::actual()) {
                $activity->causer()->dissociate();
                $activity->properties = collect($activity->properties)->merge($actor->propiedades());
            }

            $activity->description = ActividadLegible::nombre($this);
            $activity->contexto = ActividadLegible::contexto($this);
        } catch (\Throwable $e) {
            report($e);  // la auditoría nunca frena un guardado
        }
    }
}
