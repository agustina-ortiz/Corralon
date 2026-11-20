<?php

namespace App\Http\Controllers\Api\Intranet;

use App\Actions\Intranet\CambiarEstadoUsuario;
use App\Actions\Intranet\VincularDniUsuario;
use App\Actions\Usuarios\GuardarUsuario;
use App\Http\Controllers\Controller;
use App\Models\Corralon;
use App\Models\Deposito;
use App\Models\Rol;
use App\Models\User;
use App\Models\UsuarioPermiso;
use App\Support\ActorIntranet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * API de usuarios para la intranet municipal (guía en docs/api-intranet.md; contrato en
 * intranet/docs/integracion.md §5). La protege `ClaveServicioIntranet`: sin Sanctum ni
 * sesión.
 *
 * El alta delega en `GuardarUsuario` —la misma lógica y las mismas reglas que el modal
 * de Usuarios— con los mismos nombres de campo. Desde acá **nunca se crea un
 * Administrador** ni se activa, desactiva o vincula uno (decisión conservadora: el
 * Administrador del corralón ve y edita todo). Todo lo que escribe queda en la
 * Actividad con el actor de la intranet.
 */
class UsuarioController extends Controller
{
    private const NIVELES = ['ver', 'editar'];

    /** Lo que necesita la intranet para armar el modal de alta (la forma está en docs/api-intranet.md). */
    public function opciones(): JsonResponse
    {
        $niveles = collect(UsuarioPermiso::NIVELES_ACCESO)
            ->map(fn (string $etiqueta, string $valor) => ['valor' => $valor, 'etiqueta' => $etiqueta])
            ->values();

        $roles = Rol::where('nombre', '!=', 'Administrador')->orderBy('nombre')->get()
            ->map(fn (Rol $r) => ['id' => $r->id, 'nombre' => $r->nombre, 'descripcion' => $r->descripcion]);

        $modulosGlobales = collect(array_merge(UsuarioPermiso::MODULOS_MIXTOS, UsuarioPermiso::MODULOS_GLOBALES))
            ->map(fn (string $m) => [
                'clave' => $m,
                'etiqueta' => UsuarioPermiso::MODULOS[$m].(UsuarioPermiso::esModuloMixto($m) ? ' (todos)' : ''),
                'mixto' => UsuarioPermiso::esModuloMixto($m),
            ]);

        $modulosPorCorralon = collect(UsuarioPermiso::MODULOS_POR_UBICACION)
            ->map(fn (string $m) => ['clave' => $m, 'etiqueta' => UsuarioPermiso::MODULOS[$m], 'mixto' => UsuarioPermiso::esModuloMixto($m)]);

        $depositos = Deposito::orderBy('deposito')->get(['id', 'deposito', 'id_corralon'])->groupBy('id_corralon');
        $corralones = Corralon::orderBy('descripcion')->get(['id', 'descripcion'])
            ->map(fn (Corralon $c) => [
                'id' => $c->id,
                'nombre' => $c->descripcion,
                'depositos' => ($depositos[$c->id] ?? collect())->map(fn (Deposito $d) => ['id' => $d->id, 'nombre' => $d->deposito])->values(),
            ]);

        return response()->json([
            'campos' => [
                ['nombre' => 'name', 'etiqueta' => 'Nombre', 'tipo' => 'texto', 'obligatorio' => true, 'max' => 255],
                ['nombre' => 'email', 'etiqueta' => 'Email', 'tipo' => 'email', 'obligatorio' => true, 'max' => 255],
                ['nombre' => 'password', 'etiqueta' => 'Contraseña', 'tipo' => 'clave', 'obligatorio' => true, 'min' => 8],
                ['nombre' => 'password_confirmation', 'etiqueta' => 'Confirmar Contraseña', 'tipo' => 'clave', 'obligatorio' => true],
                ['nombre' => 'id_rol', 'etiqueta' => 'Rol', 'tipo' => 'seleccion', 'obligatorio' => true, 'opciones' => $roles->values()],
                ['nombre' => 'permisos_globales', 'etiqueta' => 'Módulos Globales', 'tipo' => 'permisos_globales', 'obligatorio' => false,
                    'ayuda' => 'No dependen de un corralón. "Vehículos (todos)" da acceso a todos los vehículos.',
                    'modulos' => $modulosGlobales->values()],
                ['nombre' => 'corralones_seleccionados', 'etiqueta' => 'Corralones', 'tipo' => 'corralones', 'obligatorio' => false,
                    'ayuda' => 'Corralones a los que tiene acceso.'],
                ['nombre' => 'permisos_por_corralon', 'etiqueta' => 'Permisos por Corralón', 'tipo' => 'permisos_por_corralon', 'obligatorio' => false,
                    'ayuda' => 'Módulos a los que puede acceder en cada corralón elegido.',
                    'modulos' => $modulosPorCorralon->values()],
                ['nombre' => 'depositos_especificos', 'etiqueta' => 'Depósitos específicos', 'tipo' => 'depositos_especificos', 'obligatorio' => false,
                    'ayuda' => 'Opcional: limita un módulo de un corralón a ciertos depósitos. Sin depósitos, son todos los del corralón.'],
            ],
            'niveles' => $niveles,
            'corralones' => $corralones->values(),
        ]);
    }

    public function ver(int $dni): JsonResponse
    {
        $usuario = $this->buscar($dni);

        return response()->json([
            'id' => $usuario->id,
            'dni' => $usuario->dni,
            'nombre' => $usuario->name,
            'email' => $usuario->email,
            'activo' => $usuario->activo,
        ]);
    }

    public function crear(Request $request, GuardarUsuario $guardarUsuario): JsonResponse
    {
        $actor = $this->actor($request);

        $reglas = GuardarUsuario::reglas(null, null);
        unset($reglas['activo']);  // un alta desde la intranet nace activa
        // El DNI es obligatorio: es lo que vincula a la persona con la intranet.
        $reglas['dni'] = 'required|'.$reglas['dni'];
        $reglas += [
            'corralones_seleccionados' => 'nullable|array',
            'corralones_seleccionados.*' => 'integer',
            'permisos_globales' => 'nullable|array',
            'permisos_globales.*' => 'in:ver,editar',
            'permisos_por_corralon' => 'nullable|array',
            'permisos_por_corralon.*' => 'array',
            'permisos_por_corralon.*.*' => 'in:ver,editar',
            'depositos_especificos' => 'nullable|array',
            'depositos_especificos.*' => 'array',
            'depositos_especificos.*.*' => 'array',
            'depositos_especificos.*.*.*' => 'integer',
        ];

        $datos = Validator::make($request->all(), $reglas, GuardarUsuario::mensajes() + [
            'dni.required' => __('intranet.errores.dni_obligatorio'),
            'permisos_globales.*.in' => __('intranet.errores.nivel_desconocido'),
            'permisos_por_corralon.*.*.in' => __('intranet.errores.nivel_desconocido'),
        ], __('intranet.atributos'))->validate();

        if (Rol::whereKey($datos['id_rol'])->value('nombre') === 'Administrador') {
            throw ValidationException::withMessages(['id_rol' => __('intranet.errores.administrador_alta')]);
        }

        $matriz = $this->matriz($datos);

        $usuario = $actor->registrando(fn () => $guardarUsuario->ejecutar(null, [
            'name' => $datos['name'],
            'email' => $datos['email'],
            'id_rol' => $datos['id_rol'],
            'password' => $datos['password'],
            'dni' => $datos['dni'],
            'activo' => true,
        ] + $matriz));

        return response()->json(['id' => $usuario->id], 201);
    }

    public function vincular(Request $request, int $dni, VincularDniUsuario $vincular): JsonResponse
    {
        $actor = $this->actor($request);
        $this->validarDni($dni);

        $datos = Validator::make($request->all(), ['email' => ['required', 'string', 'email', 'max:255']], [], __('intranet.atributos'))->validate();

        $usuario = User::where('email', $datos['email'])->first() ?? $this->noExiste();
        $vincular->ejecutar($usuario, $dni, $actor);

        return response()->json(['id' => $usuario->id]);
    }

    public function estado(Request $request, int $dni, CambiarEstadoUsuario $cambiarEstado): JsonResponse
    {
        $actor = $this->actor($request);

        $datos = Validator::make($request->all(), ['activo' => ['required', 'boolean']], [], __('intranet.atributos'))->validate();

        $usuario = $this->buscar($dni);
        $cambiarEstado->ejecutar($usuario, (bool) $datos['activo'], $actor);

        return response()->json(['id' => $usuario->id, 'activo' => $usuario->activo]);
    }

    // ----- Apoyo -------------------------------------------------------------

    /** El administrador de la intranet que hace el pedido. Sin un actor válido, 422 en `actor`. */
    private function actor(Request $request): ActorIntranet
    {
        $validador = Validator::make($request->all(), [
            'actor' => ['required', 'array'],
            'actor.dni' => ['required', 'integer', 'min:1', 'max:99999999'],
            'actor.nombre' => ['required', 'string', 'max:255'],
        ], [], __('intranet.atributos'));

        if ($validador->fails()) {
            throw ValidationException::withMessages(['actor' => $validador->errors()->first()]);
        }

        return new ActorIntranet((int) $request->input('actor.dni'), trim((string) $request->input('actor.nombre')));
    }

    /**
     * La matriz de permisos con la forma que usa la pantalla, revisada contra lo que el
     * corralón tiene: un módulo, corralón o depósito que no existe es un error (la
     * intranet tiene una lista vieja), no se ignora. Los corralones con permisos se
     * suman solos a `corralones_seleccionados`.
     */
    private function matriz(array $datos): array
    {
        $globales = $datos['permisos_globales'] ?? [];
        $porCorralon = $datos['permisos_por_corralon'] ?? [];
        $depositos = $datos['depositos_especificos'] ?? [];

        $modulosGlobales = array_merge(UsuarioPermiso::MODULOS_MIXTOS, UsuarioPermiso::MODULOS_GLOBALES);
        if ($desconocidos = array_diff(array_keys($globales), $modulosGlobales)) {
            $this->rechazar('permisos_globales', __('intranet.errores.modulo_desconocido', ['modulos' => implode(', ', $desconocidos)]));
        }

        $corralones = array_values(array_unique(array_map('intval', array_merge(
            $datos['corralones_seleccionados'] ?? [], array_keys($porCorralon), array_keys($depositos)
        ))));
        if (count($corralones) !== Corralon::whereIn('id', $corralones)->count()) {
            $this->rechazar('corralones_seleccionados', __('intranet.errores.corralon_desconocido'));
        }

        foreach ($porCorralon as $corralonId => $modulos) {
            if ($desconocidos = array_diff(array_keys($modulos), UsuarioPermiso::MODULOS_POR_UBICACION)) {
                $this->rechazar("permisos_por_corralon.{$corralonId}", __('intranet.errores.modulo_desconocido', ['modulos' => implode(', ', $desconocidos)]));
            }
        }

        foreach ($depositos as $corralonId => $modulos) {
            foreach ($modulos as $modulo => $ids) {
                if (! isset($porCorralon[$corralonId][$modulo])) {
                    $this->rechazar("depositos_especificos.{$corralonId}.{$modulo}", __('intranet.errores.modulo_desconocido', ['modulos' => $modulo]));
                }
                $ids = array_values(array_unique(array_map('intval', $ids)));
                if (count($ids) !== Deposito::whereIn('id', $ids)->where('id_corralon', (int) $corralonId)->count()) {
                    $this->rechazar("depositos_especificos.{$corralonId}.{$modulo}", __('intranet.errores.deposito_desconocido'));
                }
                $depositos[$corralonId][$modulo] = $ids;
            }
        }

        return [
            'corralones_seleccionados' => $corralones,
            'permisos_globales' => $globales,
            'permisos_por_corralon' => $porCorralon,
            'depositos_especificos' => $depositos,
        ];
    }

    private function buscar(int $dni): User
    {
        return User::where('dni', $dni)->first() ?? $this->noExiste();
    }

    private function validarDni(int $dni): void
    {
        if ($dni < 1 || $dni > 99999999) {
            $this->rechazar('dni', __('validation.between.numeric', ['attribute' => 'el DNI', 'min' => 1, 'max' => 99999999]));
        }
    }

    private function rechazar(string $campo, string $mensaje): never
    {
        throw ValidationException::withMessages([$campo => $mensaje]);
    }

    private function noExiste(): never
    {
        abort(response()->json(['message' => __('intranet.api.no_existe')], 404));
    }
}
