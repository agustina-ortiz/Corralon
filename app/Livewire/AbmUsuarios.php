<?php

namespace App\Livewire;

use App\Actions\Usuarios\GuardarUsuario;
use App\Livewire\Traits\ConfirmaDescarte;
use App\Livewire\Traits\EnviaToasts;
use App\Models\User;
use App\Models\Corralon;
use App\Models\Deposito;
use App\Models\Rol;
use App\Models\UsuarioPermiso;
use App\Livewire\Traits\OrdenaPorColumna;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class AbmUsuarios extends Component
{
    use WithPagination;
    use OrdenaPorColumna;
    use EnviaToasts;
    use ConfirmaDescarte;

    // Busqueda y filtros
    public $busqueda = '';
    public $filtro_acceso = '';
    public $filtro_corralon = '';
    public $filtro_rol = '';
    public $mostrarFiltros = false;

    // Modal
    public $modalAbierto = false;

    // Registro a eliminar (abre el modal de confirmación)
    public $eliminarId = null;
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



    protected function rules()
    {
        return GuardarUsuario::reglas($this->modo === 'crear' ? null : $this->usuario_id, $this->password);
    }

    protected function messages()
    {
        return GuardarUsuario::mensajes();
    }

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
        return $this->aplicarOrden(User::with(['rol', 'permisos'])
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
        )->paginate(10);
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
            $this->toastError('No tienes permisos para crear usuarios.');
            return;
        }

        if ($modo === 'editar' && !auth()->user()->puedeEditarUsuarios()) {
            $this->toastError('No tienes permisos para editar usuarios.');
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
        $this->marcarFormularioLimpio();
    }

    private function cargarPermisosUsuario(User $usuario)
    {
        $this->permisos_por_corralon = [];
        $this->permisos_globales = [];
        $this->corralones_seleccionados = [];
        $this->depositos_especificos = [];

        foreach ($usuario->permisos as $permiso) {
            if (UsuarioPermiso::esPermisoGlobal($permiso->modulo, $permiso->id_corralon)) {
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

    protected function camposFormulario(): array
    {
        return ['name', 'email', 'password', 'password_confirmation', 'id_rol', 'permisos_por_corralon', 'permisos_globales', 'corralones_seleccionados', 'depositos_especificos'];
    }

    public function cerrarFormulario(): void
    {
        $this->cerrarModal();
    }

    public function guardar()
    {
        if ($this->modo === 'crear' && !auth()->user()->puedeCrearUsuarios()) {
            $this->toastError('No tienes permisos para crear usuarios.');
            $this->cerrarModal();
            return;
        }

        if ($this->modo === 'editar' && !auth()->user()->puedeEditarUsuarios()) {
            $this->toastError('No tienes permisos para editar usuarios.');
            $this->cerrarModal();
            return;
        }

        $this->validate();

        try {
            app(GuardarUsuario::class)->ejecutar(
                $this->modo === 'crear' ? null : User::findOrFail($this->usuario_id),
                [
                    'name' => $this->name,
                    'email' => $this->email,
                    'id_rol' => $this->id_rol,
                    'password' => $this->password,
                    'corralones_seleccionados' => $this->corralones_seleccionados,
                    'permisos_globales' => $this->permisos_globales,
                    'permisos_por_corralon' => $this->permisos_por_corralon,
                    'depositos_especificos' => $this->depositos_especificos,
                ],
            );
            $this->toastExito($this->modo === 'crear' ? 'Usuario creado exitosamente' : 'Usuario actualizado exitosamente');
        } catch (\Exception $e) {
            $this->toastError('Error al guardar: ' . $e->getMessage());
        }

        $this->cerrarModal();
    }

    public function confirmarEliminar($id)
    {
        $this->eliminarId = $id;
    }

    public function cancelarEliminar()
    {
        $this->eliminarId = null;
    }

    public function eliminar()
    {
        $id = $this->eliminarId;
        $this->eliminarId = null;

        if (!auth()->user()->puedeEliminarUsuarios()) {
            $this->toastError('No tienes permisos para eliminar usuarios.');
            return;
        }

        $usuario = User::findOrFail($id);

        if ($usuario->id === auth()->id()) {
            $this->toastError('No puedes eliminar tu propio usuario');
            return;
        }

        try {
            $usuario->delete(); // cascade elimina permisos
            $this->toastExito('Usuario eliminado exitosamente');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->toastError('No se puede eliminar el usuario porque tiene movimientos asociados.');
        } catch (\Exception $e) {
            $this->toastError('No se pudo eliminar el usuario.');
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
            if (UsuarioPermiso::esPermisoGlobal($p->modulo, $p->id_corralon)) {
                $resumen['globales'][$p->modulo] = $p->nivel_acceso;
            } else {
                $resumen['corralones'][$p->id_corralon][$p->modulo] = $p->nivel_acceso;
            }
        }

        return $resumen;
    }

    protected function columnasOrdenables(): array
    {
        return [
            'nombre' => 'name',
            'email' => 'email',
            'rol' => fn (Builder $q, string $dir) => $q->orderBy(
                Rol::select('nombre')->whereColumn('roles.id', 'users.id_rol'), $dir
            ),
        ];
    }

    protected function ordenInicial(): array
    {
        return ['nombre', 'asc'];
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.abm-usuarios', [
            'usuarios' => $this->users,
            'corralones' => $this->corralones,
            'roles' => $this->roles,
            'modulosPorUbicacion' => UsuarioPermiso::MODULOS_POR_UBICACION,
            'modulosGlobales' => array_merge(UsuarioPermiso::MODULOS_MIXTOS, UsuarioPermiso::MODULOS_GLOBALES),
            'modulosMixtos' => UsuarioPermiso::MODULOS_MIXTOS,
            'todosLosModulos' => UsuarioPermiso::MODULOS,
            'puedeCrear' => $user->puedeCrearUsuarios(),
            'puedeEditar' => $user->puedeEditarUsuarios(),
            'puedeEliminar' => $user->puedeEliminarUsuarios(),
        ])->layout('layouts.app', [
            'header' => 'ABM Usuarios'
        ]);
    }
}
