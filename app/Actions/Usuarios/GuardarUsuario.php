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
            $usuario->permisos()->delete();

            if (! $esAdmin) {
                $permisosACrear = [];

                foreach ($permisosGlobales as $modulo => $nivel) {
                    $permisosACrear[] = $this->fila($usuario, null, null, $modulo, $nivel);
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
                            $permisosACrear[] = $this->fila($usuario, (int) $corralonId, null, $modulo, $nivel);
                        } else {
                            foreach ($depositosEsp as $depositoId) {
                                $permisosACrear[] = $this->fila($usuario, (int) $corralonId, (int) $depositoId, $modulo, $nivel);
                            }
                        }
                    }
                }

                if (! empty($permisosACrear)) {
                    DB::table('usuario_permisos')->insert($permisosACrear);
                }
            }

            return $usuario;
        });
    }

    private function fila(User $usuario, ?int $corralonId, ?int $depositoId, string $modulo, string $nivel): array
    {
        return [
            'id_usuario' => $usuario->id,
            'id_corralon' => $corralonId,
            'id_deposito' => $depositoId,
            'modulo' => $modulo,
            'nivel_acceso' => $nivel,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
