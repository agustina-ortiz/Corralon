<?php

namespace App\Actions\Usuarios;

use App\Models\Rol;
use App\Models\User;
use App\Models\UsuarioPermiso;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Alta y edición de un usuario con su rol, sus corralones y su matriz de permisos.
 *
 * Es la lógica que tenía AbmUsuarios::guardar(), sin cambios: la usan la pantalla
 * Usuarios y la API de la intranet (/api/intranet/v1). Quién puede guardar lo decide
 * el que llama (la pantalla revisa los permisos del usuario de la sesión; la API, la
 * clave de servicio).
 *
 * - **Administrador**: acceso a todos los corralones, `corralones_permitidos` nulo y
 *   sin filas en `usuario_permisos` (se borran las que tuviera).
 * - **Otro rol**: `corralones_permitidos` = los corralones elegidos (enteros) y la
 *   matriz se reemplaza entera: permisos globales (sin corralón), y por corralón,
 *   una fila para todo el corralón o una por cada depósito específico. Un módulo
 *   mixto (vehículos) con permiso global no graba filas por corralón.
 */
class GuardarUsuario
{
    /**
     * Reglas del formulario, con los nombres de campo del modal.
     *
     * @return array<string, string>
     */
    public static function reglas(?int $usuarioId, ?string $password): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$usuarioId,
            'id_rol' => 'required|exists:roles,id',
        ];

        if ($usuarioId === null) {
            $rules['password'] = 'required|string|min:8|confirmed';
        } elseif ($password) {
            $rules['password'] = 'string|min:8|confirmed';
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public static function mensajes(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'Debe ser un email válido',
            'email.unique' => 'Este email ya está registrado',
            'id_rol.required' => 'Debe seleccionar un rol',
            'id_rol.exists' => 'El rol seleccionado no es válido',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
        ];
    }

    /**
     * Guarda el usuario (ya validado). Todo o nada: si falla algo, no queda ni el
     * usuario ni sus permisos, y la excepción sube al que llama.
     *
     * @param  array{name: string, email: string, id_rol: int|string, password?: ?string,
     *               corralones_seleccionados?: array, permisos_globales?: array,
     *               permisos_por_corralon?: array, depositos_especificos?: array}  $datos
     */
    public function ejecutar(?User $usuario, array $datos): User
    {
        $corralonesSeleccionados = $datos['corralones_seleccionados'] ?? [];
        $permisosGlobales = $datos['permisos_globales'] ?? [];
        $permisosPorCorralon = $datos['permisos_por_corralon'] ?? [];
        $depositosEspecificos = $datos['depositos_especificos'] ?? [];

        $rol = Rol::find($datos['id_rol']);
        $esAdmin = $rol && $rol->nombre === 'Administrador';

        return DB::transaction(function () use ($usuario, $datos, $esAdmin, $corralonesSeleccionados, $permisosGlobales, $permisosPorCorralon, $depositosEspecificos) {
            $data = [
                'name' => $datos['name'],
                'email' => $datos['email'],
                'id_rol' => $datos['id_rol'],
                'acceso_todos_corralones' => $esAdmin,
                'corralones_permitidos' => $esAdmin ? null : array_map('intval', $corralonesSeleccionados),
            ];

            if ($datos['password'] ?? null) {
                $data['password'] = Hash::make($datos['password']);
            }

            if ($usuario === null) {
                $usuario = User::create($data);
            } else {
                $usuario->update($data);
            }

            // La matriz se reemplaza entera (un administrador queda sin filas).
            $this->sincronizarPermisos($usuario, $esAdmin ? [] : $this->permisosDeseados(
                $permisosGlobales, $permisosPorCorralon, $depositosEspecificos
            ));

            return $usuario;
        });
    }

    /**
     * Las filas que tiene que quedar en `usuario_permisos`, como [corralón, depósito,
     * módulo, nivel], en el orden de siempre.
     */
    private function permisosDeseados(array $permisosGlobales, array $permisosPorCorralon, array $depositosEspecificos): array
    {
        $filas = [];

        foreach ($permisosGlobales as $modulo => $nivel) {
            $filas[] = [null, null, $modulo, $nivel];
        }

        foreach ($permisosPorCorralon as $corralonId => $modulos) {
            foreach ($modulos as $modulo => $nivel) {
                // Módulo mixto con permiso global: ya ve todo, el permiso por corralón sobra
                if (UsuarioPermiso::esModuloMixto($modulo) && isset($permisosGlobales[$modulo])) {
                    continue;
                }

                $depositosEsp = $depositosEspecificos[$corralonId][$modulo] ?? [];

                if (empty($depositosEsp)) {
                    // Acceso a todos los depósitos del corralón
                    $filas[] = [(int) $corralonId, null, $modulo, $nivel];
                } else {
                    foreach ($depositosEsp as $depositoId) {
                        $filas[] = [(int) $corralonId, (int) $depositoId, $modulo, $nivel];
                    }
                }
            }
        }

        return $filas;
    }

    /**
     * Deja en `usuario_permisos` exactamente las filas pedidas. Va por el modelo (no con
     * un insert masivo) para que cada alta, cambio de nivel o baja quede en la
     * Actividad: lo que no cambia no se toca, así editar un usuario sin tocar sus
     * permisos no deja ruido. El resultado es el mismo que borrar todo y volver a
     * insertar.
     */
    private function sincronizarPermisos(User $usuario, array $filas): void
    {
        $clave = fn ($corralon, $deposito, $modulo) => ($corralon ?? '-').'|'.($deposito ?? '-').'|'.$modulo;

        $existentes = [];
        foreach ($usuario->permisos()->orderBy('id')->get() as $permiso) {
            $k = $clave($permiso->id_corralon, $permiso->id_deposito, $permiso->modulo);
            if (isset($existentes[$k])) {
                $permiso->delete();  // fila repetida: queda una sola
            } else {
                $existentes[$k] = $permiso;
            }
        }

        $pedidas = [];
        foreach ($filas as [$corralon, $deposito, $modulo, $nivel]) {
            $pedidas[$clave($corralon, $deposito, $modulo)] = [$corralon, $deposito, $modulo, $nivel];
        }

        foreach (array_diff_key($existentes, $pedidas) as $sobrante) {
            $sobrante->delete();
        }

        foreach ($pedidas as $k => [$corralon, $deposito, $modulo, $nivel]) {
            if (isset($existentes[$k])) {
                $existentes[$k]->update(['nivel_acceso' => $nivel]);
            } else {
                UsuarioPermiso::create([
                    'id_usuario' => $usuario->id,
                    'id_corralon' => $corralon,
                    'id_deposito' => $deposito,
                    'modulo' => $modulo,
                    'nivel_acceso' => $nivel,
                ]);
            }
        }

        $usuario->limpiarCachePermisos();
        $usuario->unsetRelation('permisos');
    }
}
