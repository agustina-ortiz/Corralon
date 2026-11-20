<?php

namespace App\Support;

use App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

/**
 * Traduce los registros de la Actividad (`activity_log`) a algo que se lea (kit, spec
 * 38): tipo de registro en castellano, nombre y contexto ("Permiso de Juan Pérez"),
 * los cambios campo por campo con el valor de antes y el de después, y quién lo hizo.
 *
 * El nombre y el contexto se guardan en el momento de la acción (`description` y
 * `contexto`, desde RegistraActividad::tapActivity): sirven para buscar y siguen
 * valiendo cuando el registro ya se borró.
 *
 * Los valores salen de los `casts` de cada modelo (sí/no, fechas, listas) y las claves
 * foráneas se nombran con el registro al que apuntan. Los textos, de
 * lang/es/actividad.php. Por ahora solo se auditan usuarios y sus permisos.
 */
class ActividadLegible
{
    /** Grupos del filtro "Tipo de registro", en orden. */
    public const GRUPOS = ['usuarios'];

    /** Clase del modelo => grupo. */
    public const TIPOS = [
        Models\User::class => 'usuarios',
        Models\UsuarioPermiso::class => 'usuarios',
    ];

    /** Clave foránea => modelo al que apunta (para nombrar el registro, no el id). */
    private const REFERENCIAS = [
        'id_rol' => Models\Rol::class,
        'id_corralon' => Models\Corralon::class,
        'id_deposito' => Models\Deposito::class,
        'id_usuario' => Models\User::class,
    ];

    // ── Nombre, contexto y tipo ─────────────────────────────────────

    /** Tipo de registro en castellano ("Usuario", "Permiso de usuario"). */
    public static function etiqueta(?string $clase): string
    {
        if ($clase && array_key_exists($clase, self::TIPOS)) {
            return __('actividad.tipos.'.class_basename($clase));
        }

        return $clase ? Str::headline(class_basename($clase)) : __('actividad.registro');
    }

    /** Clases de un grupo del filtro. */
    public static function clasesDelGrupo(string $grupo): array
    {
        return array_keys(array_filter(self::TIPOS, fn ($g) => $g === $grupo));
    }

    /** El mejor nombre disponible para un registro. */
    public static function nombre(Model $m): string
    {
        $nombre = match (true) {
            $m instanceof Models\UsuarioPermiso => __('actividad.nombres.permiso', [
                'modulo' => Models\UsuarioPermiso::MODULOS[$m->modulo] ?? (string) $m->modulo,
            ]),
            default => null,
        };

        if (filled($nombre)) {
            return Str::limit($nombre, 150);
        }

        foreach (['name', 'nombre', 'descripcion'] as $campo) {
            $valor = $m->getAttribute($campo);
            if (is_scalar($valor) && filled($valor)) {
                return Str::limit((string) $valor, 150);
            }
        }

        return $m->getKey() ? '#'.$m->getKey() : __('actividad.sin_nombre');
    }

    /** Dónde está el registro ("Juan Pérez · Corralón Centro"), o null si no hace falta. */
    public static function contexto(Model $m): ?string
    {
        $contexto = match (true) {
            $m instanceof Models\UsuarioPermiso => implode(' · ', array_filter([
                self::nombreDe(Models\User::class, $m->id_usuario),
                $m->id_corralon ? self::nombreDe(Models\Corralon::class, $m->id_corralon) : __('actividad.global'),
                $m->id_deposito ? self::nombreDe(Models\Deposito::class, $m->id_deposito) : null,
            ])),
            $m instanceof Models\User => $m->email,
            default => null,
        };

        return filled($contexto) ? Str::limit($contexto, 480) : null;
    }

    /**
     * Quién lo hizo cuando no fue un usuario del corralón: lo hecho desde la intranet va
     * sin causer y con el actor en `properties` → "Intranet · Pérez, Ana (DNI 20123456)".
     * Null si no es de la intranet.
     */
    public static function autorExterno(Activity $a): ?string
    {
        $propiedades = collect($a->properties);

        if ($propiedades->get('origen') !== 'intranet') {
            return null;
        }

        return __('actividad.intranet', [
            'nombre' => (string) $propiedades->get('actor_nombre'),
            'dni' => (string) $propiedades->get('actor_dni'),
        ]);
    }

    // ── Cambios ────────────────────────────────────────────────────

    private static function clase(Activity $a): ?string
    {
        return $a->subject_type && class_exists($a->subject_type) ? $a->subject_type : null;
    }

    /**
     * Campos de la actividad con su valor legible:
     * [['campo', 'antes' => valor|null, 'despues' => valor|null], …].
     * En una edición trae antes y después; en un alta solo después; en una baja solo antes.
     */
    public static function cambios(Activity $a): array
    {
        $nuevos = (array) ($a->properties['attributes'] ?? []);
        $viejos = (array) ($a->properties['old'] ?? []);
        $modelo = ($clase = self::clase($a)) ? new $clase : null;

        $claves = match ($a->event) {
            'updated' => array_keys($nuevos),
            'created' => array_keys(array_filter($nuevos, fn ($v) => self::tieneValor($v))),
            'deleted' => array_keys(array_filter($viejos ?: $nuevos, fn ($v) => self::tieneValor($v))),
            default => [],
        };
        if ($a->event === 'deleted' && ! $viejos) {
            $viejos = $nuevos;  // spatie guarda la baja en "attributes"
            $nuevos = [];
        }

        return array_map(fn ($campo) => [
            'campo' => self::campo($campo),
            'antes' => $a->event === 'created' ? null : self::valor($campo, $viejos[$campo] ?? null, $modelo),
            'despues' => $a->event === 'deleted' ? null : self::valor($campo, $nuevos[$campo] ?? null, $modelo),
        ], $claves);
    }

    /** Resumen de una línea de una edición: "Cambió: rol y corralones". */
    public static function resumen(Activity $a): ?string
    {
        if ($a->event !== 'updated') {
            return null;
        }

        $claves = array_keys((array) ($a->properties['attributes'] ?? []));
        $campos = array_values(array_unique(array_map(fn ($c) => Str::lower(self::campo($c)), $claves)));

        return $campos ? __('actividad.resumen', ['campos' => self::enumerar($campos)]) : null;
    }

    public static function campo(string $campo): string
    {
        $clave = 'actividad.campos.'.$campo;

        return __($clave) !== $clave ? __($clave) : Str::ucfirst(str_replace('_', ' ', $campo));
    }

    /**
     * Valor legible de un campo: ['tipo' => vacio|texto, 'texto' => …], según el cast
     * del modelo, si es una clave foránea o un campo con su lista de etiquetas.
     */
    public static function valor(string $campo, mixed $valor, ?Model $modelo = null): array
    {
        $cast = $modelo?->getCasts()[$campo] ?? null;

        if ($cast === 'boolean') {
            return ['tipo' => 'texto', 'texto' => __(self::esVerdadero($valor) ? 'actividad.si' : 'actividad.no')];
        }

        if (! self::tieneValor($valor)) {
            return ['tipo' => 'vacio', 'texto' => __('actividad.vacio')];
        }

        if ($campo === 'modulo' && is_string($valor)) {
            return ['tipo' => 'texto', 'texto' => Models\UsuarioPermiso::MODULOS[$valor] ?? $valor];
        }

        if ($campo === 'nivel_acceso' && is_string($valor)) {
            return ['tipo' => 'texto', 'texto' => Models\UsuarioPermiso::NIVELES_ACCESO[$valor] ?? $valor];
        }

        if ($campo === 'corralones_permitidos') {
            $ids = is_array($valor) ? $valor : (json_decode((string) $valor, true) ?: []);

            return ['tipo' => 'texto', 'texto' => collect($ids)
                ->map(fn ($id) => self::nombreDe(Models\Corralon::class, $id) ?? __('actividad.eliminado', ['id' => $id]))
                ->implode(', ') ?: __('actividad.vacio')];
        }

        if (isset(self::REFERENCIAS[$campo]) && is_numeric($valor)) {
            $nombre = self::nombreDe(self::REFERENCIAS[$campo], (int) $valor);

            return ['tipo' => 'texto', 'texto' => $nombre ?? __('actividad.eliminado', ['id' => $valor])];
        }

        if (is_array($valor)) {
            return ['tipo' => 'texto', 'texto' => self::lista($valor)];
        }

        $texto = (string) $valor;

        if (in_array($cast, ['date', 'datetime', 'immutable_date', 'immutable_datetime'], true)) {
            try {
                $fecha = Carbon::parse($texto)->setTimezone(config('app.timezone'));

                return ['tipo' => 'texto', 'texto' => $fecha->format($fecha->format('H:i') === '00:00' ? 'd/m/Y' : 'd/m/Y H:i')];
            } catch (\Throwable) {
                // No era una fecha: sigue como texto
            }
        }

        return ['tipo' => 'texto', 'texto' => $texto];
    }

    // ── Auxiliares ─────────────────────────────────────────────────

    /** Nombre del registro con ese id, o null si no existe. */
    private static function nombreDe(string $clase, mixed $id): ?string
    {
        if (! $id) {
            return null;
        }

        $registro = $clase::query()->find($id);

        return $registro ? self::nombre($registro) : null;
    }

    private static function tieneValor(mixed $v): bool
    {
        return ! ($v === null || $v === '' || $v === []);
    }

    private static function esVerdadero(mixed $v): bool
    {
        return in_array($v, [true, 1, '1', 'true'], true);
    }

    private static function enumerar(array $partes): string
    {
        return count($partes) > 1
            ? implode(', ', array_slice($partes, 0, -1)).' '.__('actividad.y').' '.end($partes)
            : ($partes[0] ?? '');
    }

    private static function lista(array $valor): string
    {
        return collect($valor)
            ->filter(fn ($v) => self::tieneValor($v))
            ->map(fn ($v, $k) => (is_int($k) ? '' : self::campo((string) $k).': ').(is_array($v) ? self::lista($v) : (is_bool($v) ? __($v ? 'actividad.si' : 'actividad.no') : $v)))
            ->implode(' · ');
    }
}
