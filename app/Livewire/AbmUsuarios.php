<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Corralon;
use App\Models\Deposito;
use App\Models\Rol;
use App\Models\UsuarioPermiso;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AbmUsuarios extends Component
{
    use WithPagination;

    // Busqueda y filtros
    public $busqueda = '';
    public $filtro_acceso = '';
    public $filtro_corralon = '';
    public $filtro_rol = '';
    public $mostrarFiltros = false;

    // Modal
    public $modalAbierto = false;
    public $modo = 'crear';

    // Campos del formulario
    public $usuario_id;
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $id_rol;

    // Permisos granulares
    // Estructura: ['corralon_id' => ['modulo' => 'nivel_acceso', ...], ...]
    public $permisos_por_corralon = [];
    // Permisos globales: ['modulo' => 'nivel_acceso', ...]
    public $permisos_globales = [];
    // Corralones seleccionados para asignar permisos
    public $corralones_seleccionados = [];
    // Depositos especificos por corralon+modulo (opcional)
    // Estructura: ['corralon_id' => ['modulo' => [deposito_ids], ...], ...]
    public $depositos_especificos = [];

    // Ordenamiento
    public $orden_campo = 'name';
    public $orden_direccion = 'asc';

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->usuario_id,
            'id_rol' => 'required|exists:roles,id',
        ];

        if ($this->modo === 'crear') {
            $rules['password'] = 'required|string|min:8|confirmed';
        } elseif ($this->password) {
            $rules['password'] = 'string|min:8|confirmed';
        }

        return $rules;
    }

    protected $messages = [
        'name.required' => 'El nombre es obligatorio',
        'email.required' => 'El email es obligatorio',
        'email.email' => 'Debe ser un email valido',
        'email.unique' => 'Este email ya esta registrado',
        'id_rol.required' => 'Debe seleccionar un rol',
        'id_rol.exists' => 'El rol seleccionado no es valido',
        'password.required' => 'La contrasena es obligatoria',
        'password.min' => 'La contrasena debe tener al menos 8 caracteres',
        'password.confirmed' => 'Las contrasenas no coinciden',
    ];

    public function mount()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
    }

    public function updatingBusqueda() { $this->resetPage(); }
    public function updatingFiltroAcceso() { $this->resetPage(); }
    public function updatingFiltroCorralon() { $this->resetPage(); }
    public function updatingFiltroRol() { $this->resetPage(); }

    public function getUsersProperty()
    {
        return User::with(['rol', 'permisos'])
            ->when($this->busqueda, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->busqueda . '%')
                      ->orWhere('email', 'like', '%' . $this->busqueda . '%');
                });
            })
            ->when($this->filtro_acceso === 'admin', function ($query) {
                $query->whereHas('rol', fn($q) => $q->where('nombre', 'Administrador'));
            })
            ->when($this->filtro_acceso === 'limitado', function ($query) {
                $query->whereHas('rol', fn($q) => $q->where('nombre', '!=', 'Administrador'));
            })
            ->when($this->filtro_corralon, function ($query) {
                $corralonId = $this->filtro_corralon;
                $query->where(function ($q) use ($corralonId) {
                    $q->whereHas('rol', fn($r) => $r->where('nombre', 'Administrador'))
                      ->orWhereHas('permisos', fn($p) => $p->where('id_corralon', $corralonId));
                });
            })
            ->when($this->filtro_rol, fn($q) => $q->where('id_rol', $this->filtro_rol))
            ->orderBy($this->orden_campo, $this->orden_direccion)
            ->paginate(10);
    }

    public function getCorralonesProperty()
    {
        return Corralon::orderBy('descripcion')->get();
    }

    public function getRolesProperty()
    {
        return Rol::orderBy('nombre')->get();
    }

    public function getDepositosPorCorralonProperty()
    {
        $resultado = [];
        foreach ($this->corralones_seleccionados as $corralonId) {
            $resultado[$corralonId] = Deposito::where('id_corralon', $corralonId)
                ->orderBy('deposito')
                ->get();
        }
        return $resultado;
    }

    public function ordenarPor($campo)
    {
        if ($this->orden_campo === $campo) {
            $this->orden_direccion = $this->orden_direccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->orden_campo = $campo;
            $this->orden_direccion = 'asc';
        }
    }

    public function toggleCorralon($corralonId)
    {
        $corralonId = (string) $corralonId;
        if (in_array($corralonId, $this->corralones_seleccionados)) {
            $this->corralones_seleccionados = array_values(array_diff($this->corralones_seleccionados, [$corralonId]));
            unset($this->permisos_por_corralon[$corralonId]);
            unset($this->depositos_especificos[$corralonId]);
        } else {
            $this->corralones_seleccionados[] = $corralonId;
            // Inicializar permisos vacios para este corralon
            $this->permisos_por_corralon[$corralonId] = [];
        }
    }

    public function toggleModuloCorralon($corralonId, $modulo, $nivel)
    {
        $corralonId = (string) $corralonId;
        if (isset($this->permisos_por_corralon[$corralonId][$modulo]) && $this->permisos_por_corralon[$corralonId][$modulo] === $nivel) {
            // Desactivar
            unset($this->permisos_por_corralon[$corralonId][$modulo]);
        } else {
            $this->permisos_por_corralon[$corralonId][$modulo] = $nivel;
        }
    }

    public function toggleModuloGlobal($modulo, $nivel)
    {
        if (isset($this->permisos_globales[$modulo]) && $this->permisos_globales[$modulo] === $nivel) {
            unset($this->permisos_globales[$modulo]);
        } else {
            $this->permisos_globales[$modulo] = $nivel;
        }
    }

    public function abrirModal($modo = 'crear', $id = null)
    {
        if ($modo === 'crear' && !auth()->user()->puedeCrearUsuarios()) {
            session()->flash('error', 'No tienes permisos para crear usuarios.');
            return;
        }

        if ($modo === 'editar' && !auth()->user()->puedeEditarUsuarios()) {
            session()->flash('error', 'No tienes permisos para editar usuarios.');
            return;
        }

        $this->resetearFormulario();
        $this->modo = $modo;

        if ($modo === 'editar' && $id) {
            $usuario = User::with('permisos')->findOrFail($id);
            $this->usuario_id = $usuario->id;
            $this->name = $usuario->name;
            $this->email = $usuario->email;
            $this->id_rol = $usuario->id_rol;

            // Cargar permisos existentes
            $this->cargarPermisosUsuario($usuario);
        }

        $this->modalAbierto = true;
    }

    private function cargarPermisosUsuario(User $usuario)
    {
        $this->permisos_por_corralon = [];
        $this->permisos_globales = [];
        $this->corralones_seleccionados = [];
        $this->depositos_especificos = [];

        foreach ($usuario->permisos as $permiso) {
            if (UsuarioPermiso::esModuloGlobal($permiso->modulo)) {
                $this->permisos_globales[$permiso->modulo] = $permiso->nivel_acceso;
            } elseif ($permiso->id_corralon) {
                $corralonId = (string) $permiso->id_corralon;

                if (!in_array($corralonId, $this->corralones_seleccionados)) {
                    $this->corralones_seleccionados[] = $corralonId;
                }

                $this->permisos_por_corralon[$corralonId][$permiso->modulo] = $permiso->nivel_acceso;

                // Si tiene deposito especifico
                if ($permiso->id_deposito) {
                    $this->depositos_especificos[$corralonId][$permiso->modulo][] = (string) $permiso->id_deposito;
                }
            }
        }
    }

    public function cerrarModal()
    {
        $this->modalAbierto = false;
        $this->resetearFormulario();
    }

    public function guardar()
    {
        if ($this->modo === 'crear' && !auth()->user()->puedeCrearUsuarios()) {
            session()->flash('error', 'No tienes permisos para crear usuarios.');
            $this->cerrarModal();
            return;
        }

        if ($this->modo === 'editar' && !auth()->user()->puedeEditarUsuarios()) {
            session()->flash('error', 'No tienes permisos para editar usuarios.');
            $this->cerrarModal();
            return;
        }

        $this->validate();

        $rol = Rol::find($this->id_rol);
        $esAdmin = $rol && $rol->nombre === 'Administrador';

        DB::beginTransaction();
        try {
            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'id_rol' => $this->id_rol,
                'acceso_todos_corralones' => $esAdmin,
                'corralones_permitidos' => $esAdmin ? null : array_map('intval', $this->corralones_seleccionados),
            ];

            if ($this->password) {
                $data['password'] = Hash::make($this->password);
            }

            if ($this->modo === 'crear') {
                $usuario = User::create($data);
            } else {
                $usuario = User::findOrFail($this->usuario_id);
                $usuario->update($data);
            }

            // Guardar permisos (solo si no es admin)
            // Eliminar permisos existentes
            $usuario->permisos()->delete();

            if (!$esAdmin) {
                $permisosACrear = [];

                // Permisos globales
                foreach ($this->permisos_globales as $modulo => $nivel) {
                    $permisosACrear[] = [
                        'id_usuario' => $usuario->id,
                        'id_corralon' => null,
                        'id_deposito' => null,
                        'modulo' => $modulo,
                        'nivel_acceso' => $nivel,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Permisos por corralon
                foreach ($this->permisos_por_corralon as $corralonId => $modulos) {
                    foreach ($modulos as $modulo => $nivel) {
                        // Verificar si hay depositos especificos
                        $depositosEsp = $this->depositos_especificos[$corralonId][$modulo] ?? [];

                        if (empty($depositosEsp)) {
                            // Acceso a todos los depositos del corralon
                            $permisosACrear[] = [
                                'id_usuario' => $usuario->id,
                                'id_corralon' => (int) $corralonId,
                                'id_deposito' => null,
                                'modulo' => $modulo,
                                'nivel_acceso' => $nivel,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        } else {
                            // Acceso solo a depositos especificos
                            foreach ($depositosEsp as $depositoId) {
                                $permisosACrear[] = [
                                    'id_usuario' => $usuario->id,
                                    'id_corralon' => (int) $corralonId,
                                    'id_deposito' => (int) $depositoId,
                                    'modulo' => $modulo,
                                    'nivel_acceso' => $nivel,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }
                    }
                }

                if (!empty($permisosACrear)) {
                    DB::table('usuario_permisos')->insert($permisosACrear);
                }
            }

            DB::commit();
            session()->flash('mensaje', $this->modo === 'crear' ? 'Usuario creado exitosamente' : 'Usuario actualizado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }

        $this->cerrarModal();
    }

    public function eliminar($id)
    {
        if (!auth()->user()->puedeEliminarUsuarios()) {
            session()->flash('error', 'No tienes permisos para eliminar usuarios.');
            return;
        }

        $usuario = User::findOrFail($id);

        if ($usuario->id === auth()->id()) {
            session()->flash('error', 'No puedes eliminar tu propio usuario');
            return;
        }

        try {
            $usuario->delete(); // cascade elimina permisos
            session()->flash('mensaje', 'Usuario eliminado exitosamente');
        } catch (\Illuminate\Database\QueryException $e) {
            session()->flash('error', 'No se puede eliminar el usuario porque tiene movimientos asociados.');
        } catch (\Exception $e) {
            session()->flash('error', 'No se pudo eliminar el usuario.');
        }
    }

    private function resetearFormulario()
    {
        $this->usuario_id = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->id_rol = '';
        $this->permisos_por_corralon = [];
        $this->permisos_globales = [];
        $this->corralones_seleccionados = [];
        $this->depositos_especificos = [];
        $this->resetValidation();
    }

    public function resetearFiltros()
    {
        $this->busqueda = '';
        $this->filtro_acceso = '';
        $this->filtro_corralon = '';
        $this->filtro_rol = '';
        $this->resetPage();
    }

    /**
     * Retorna los corralones con sus permisos para mostrar en la tabla
     */
    public function getPermisosResumen($usuario): array
    {
        if ($usuario->esAdministrador()) {
            return ['tipo' => 'admin'];
        }

        $resumen = [];
        foreach ($usuario->permisos as $p) {
            if (UsuarioPermiso::esModuloGlobal($p->modulo)) {
                $resumen['globales'][$p->modulo] = $p->nivel_acceso;
            } else {
                $resumen['corralones'][$p->id_corralon][$p->modulo] = $p->nivel_acceso;
            }
        }

        return $resumen;
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.abm-usuarios', [
            'usuarios' => $this->users,
            'corralones' => $this->corralones,
            'roles' => $this->roles,
            'modulosPorUbicacion' => UsuarioPermiso::MODULOS_POR_UBICACION,
            'modulosGlobales' => UsuarioPermiso::MODULOS_GLOBALES,
            'todosLosModulos' => UsuarioPermiso::MODULOS,
            'puedeCrear' => $user->puedeCrearUsuarios(),
            'puedeEditar' => $user->puedeEditarUsuarios(),
            'puedeEliminar' => $user->puedeEliminarUsuarios(),
        ])->layout('layouts.app', [
            'header' => 'ABM Usuarios'
        ]);
    }
}
