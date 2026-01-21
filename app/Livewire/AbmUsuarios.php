<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Corralon;
use App\Models\Rol;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;

class AbmUsuarios extends Component
{
    use WithPagination;

    // Búsqueda y filtros
    public $busqueda = '';
    public $filtro_acceso = ''; // 'todos', 'limitado', ''
    public $filtro_corralon = '';
    public $filtro_rol = '';
    public $mostrarFiltros = false;

    // Modal
    public $modalAbierto = false;
    public $modo = 'crear'; // 'crear' o 'editar'

    // Campos del formulario
    public $usuario_id;
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $id_rol;
    public $acceso_todos_corralones = false;
    public $corralones_seleccionados = [];

    // Ordenamiento
    public $orden_campo = 'name';
    public $orden_direccion = 'asc';

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->usuario_id,
            'id_rol' => 'required|exists:roles,id',
            'acceso_todos_corralones' => 'boolean',
        ];

        if ($this->modo === 'crear') {
            $rules['password'] = 'required|string|min:8|confirmed';
        } elseif ($this->password) {
            $rules['password'] = 'string|min:8|confirmed';
        }

        if (!$this->acceso_todos_corralones) {
            $rules['corralones_seleccionados'] = 'required|array|min:1';
        }

        return $rules;
    }

    protected $messages = [
        'name.required' => 'El nombre es obligatorio',
        'email.required' => 'El email es obligatorio',
        'email.email' => 'Debe ser un email válido',
        'email.unique' => 'Este email ya está registrado',
        'id_rol.required' => 'Debe seleccionar un rol',
        'id_rol.exists' => 'El rol seleccionado no es válido',
        'password.required' => 'La contraseña es obligatoria',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres',
        'password.confirmed' => 'Las contraseñas no coinciden',
        'corralones_seleccionados.required' => 'Debe seleccionar al menos un corralón',
        'corralones_seleccionados.min' => 'Debe seleccionar al menos un corralón',
    ];

    public function mount()
    {
        // Verificar que el usuario esté autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }
    }

    public function updatingBusqueda()
    {
        $this->resetPage();
    }

    public function updatingFiltroAcceso()
    {
        $this->resetPage();
    }

    public function updatingFiltroCorralon()
    {
        $this->resetPage();
    }

    public function updatingFiltroRol()
    {
        $this->resetPage();
    }

    public function getUsersProperty()
    {
        return User::with('rol')
            ->when($this->busqueda, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->busqueda . '%')
                      ->orWhere('email', 'like', '%' . $this->busqueda . '%');
                });
            })
            ->when($this->filtro_acceso === 'todos', function ($query) {
                $query->where('acceso_todos_corralones', true);
            })
            ->when($this->filtro_acceso === 'limitado', function ($query) {
                $query->where('acceso_todos_corralones', false);
            })
            ->when($this->filtro_corralon, function ($query) {
                $corralon_id = $this->filtro_corralon;
                $query->where(function ($q) use ($corralon_id) {
                    // Usuarios con acceso a todos los corralones
                    $q->where('acceso_todos_corralones', true)
                      // O usuarios con este corralón específico en su array de permisos
                      ->orWhereRaw('JSON_CONTAINS(corralones_permitidos, ?)', ['"' . $corralon_id . '"']);
                });
            })
            ->when($this->filtro_rol, function ($query) {
                $query->where('id_rol', $this->filtro_rol);
            })
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

    public function ordenarPor($campo)
    {
        if ($this->orden_campo === $campo) {
            $this->orden_direccion = $this->orden_direccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->orden_campo = $campo;
            $this->orden_direccion = 'asc';
        }
    }

    public function abrirModal($modo = 'crear', $id = null)
    {
        // Verificar permisos
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
            $usuario = User::findOrFail($id);
            $this->usuario_id = $usuario->id;
            $this->name = $usuario->name;
            $this->email = $usuario->email;
            $this->id_rol = $usuario->id_rol;
            $this->acceso_todos_corralones = $usuario->acceso_todos_corralones;
            $this->corralones_seleccionados = $usuario->corralones_permitidos ?? [];
        }

        $this->modalAbierto = true;
    }

    public function cerrarModal()
    {
        $this->modalAbierto = false;
        $this->resetearFormulario();
    }

    public function guardar()
    {
        // Verificar permisos antes de guardar
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

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'id_rol' => $this->id_rol,
            'acceso_todos_corralones' => $this->acceso_todos_corralones,
            'corralones_permitidos' => $this->acceso_todos_corralones ? null : $this->corralones_seleccionados,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->modo === 'crear') {
            User::create($data);
            session()->flash('mensaje', 'Usuario creado exitosamente');
        } else {
            $usuario = User::findOrFail($this->usuario_id);
            $usuario->update($data);
            session()->flash('mensaje', 'Usuario actualizado exitosamente');
        }

        $this->cerrarModal();
    }

    public function eliminar($id)
    {
        // Verificar permiso de eliminación
        if (!auth()->user()->puedeEliminarUsuarios()) {
            session()->flash('error', 'No tienes permisos para eliminar usuarios.');
            return;
        }

        $usuario = User::findOrFail($id);
        
        // No permitir eliminar al usuario actual
        if ($usuario->id === auth()->id()) {
            session()->flash('error', 'No puedes eliminar tu propio usuario');
            return;
        }

        $usuario->delete();
        session()->flash('mensaje', 'Usuario eliminado exitosamente');
    }

    private function resetearFormulario()
    {
        $this->usuario_id = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->id_rol = '';
        $this->acceso_todos_corralones = false;
        $this->corralones_seleccionados = [];
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

    public function render()
    {
        $user = auth()->user();

        return view('livewire.abm-usuarios', [
            'usuarios' => $this->users,
            'corralones' => $this->corralones,
            'roles' => $this->roles,
            // Pasar permisos a la vista
            'puedeCrear' => $user->puedeCrearUsuarios(),
            'puedeEditar' => $user->puedeEditarUsuarios(),
            'puedeEliminar' => $user->puedeEliminarUsuarios(),
        ])->layout('layouts.app', [
            'header' => 'ABM Usuarios'
        ]);
    }
}