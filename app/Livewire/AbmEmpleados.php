<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Empleado;

class AbmEmpleados extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    
    // Campos del formulario
    public $empleadoId;
    public $nombre;
    public $apellido;
    public $legajo;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'apellido' => 'required|string|max:255',
        'legajo' => 'required|string|max:50|unique:empleados,legajo',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio',
        'apellido.required' => 'El apellido es obligatorio',
        'legajo.required' => 'El legajo es obligatorio',
        'legajo.unique' => 'Este legajo ya está registrado',
    ];

    public function mount()
    {
        // Verificar que el usuario esté autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();

        $empleados = Empleado::where(function($query) {
            $query->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('apellido', 'like', '%' . $this->search . '%')
                  ->orWhere('legajo', 'like', '%' . $this->search . '%');
        })
        ->orderBy('apellido')
        ->orderBy('nombre')
        ->paginate(10);

        return view('livewire.abm-empleados', [
            'empleados' => $empleados,
            // Pasar permisos a la vista
            'puedeCrear' => $user->puedeCrearEmpleados(),
            'puedeEditar' => $user->puedeEditarEmpleados(),
            'puedeEliminar' => $user->puedeEliminarEmpleados(),
        ])->layout('layouts.app', [
            'header' => 'Empleados'
        ]);
    }

    public function crear()
    {
        // Verificar permiso de creación
        if (!auth()->user()->puedeCrearEmpleados()) {
            session()->flash('error', 'No tienes permisos para crear empleados.');
            return;
        }

        $this->resetForm();
        $this->showModal = true;
    }

    public function editar($id)
    {
        // Verificar permiso de edición
        if (!auth()->user()->puedeEditarEmpleados()) {
            session()->flash('error', 'No tienes permisos para editar empleados.');
            return;
        }

        $empleado = Empleado::findOrFail($id);
        
        $this->empleadoId = $empleado->id;
        $this->nombre = $empleado->nombre;
        $this->apellido = $empleado->apellido;
        $this->legajo = $empleado->legajo;
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function guardar()
    {
        // Verificar permisos antes de guardar
        if (!$this->editMode && !auth()->user()->puedeCrearEmpleados()) {
            session()->flash('error', 'No tienes permisos para crear empleados.');
            $this->cerrarModal();
            return;
        }

        if ($this->editMode && !auth()->user()->puedeEditarEmpleados()) {
            session()->flash('error', 'No tienes permisos para editar empleados.');
            $this->cerrarModal();
            return;
        }

        if ($this->editMode) {
            $this->rules['legajo'] = 'required|string|max:50|unique:empleados,legajo,' . $this->empleadoId;
        }

        $this->validate();

        if ($this->editMode) {
            $empleado = Empleado::findOrFail($this->empleadoId);
            $empleado->update([
                'nombre' => $this->nombre,
                'apellido' => $this->apellido,
                'legajo' => $this->legajo,
            ]);
            
            session()->flash('message', 'Empleado actualizado correctamente.');
        } else {
            Empleado::create([
                'nombre' => $this->nombre,
                'apellido' => $this->apellido,
                'legajo' => $this->legajo,
            ]);
            
            session()->flash('message', 'Empleado creado correctamente.');
        }

        $this->cerrarModal();
    }

    public function eliminar($id)
    {
        // Verificar permiso de eliminación
        if (!auth()->user()->puedeEliminarEmpleados()) {
            session()->flash('error', 'No tienes permisos para eliminar empleados.');
            return;
        }

        try {
            $empleado = Empleado::findOrFail($id);
            $empleado->delete();
            
            session()->flash('message', 'Empleado eliminado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'No se pudo eliminar el empleado. Puede estar siendo utilizado en otros registros.');
        }
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['empleadoId', 'nombre', 'apellido', 'legajo', 'editMode']);
        $this->resetValidation();
    }
}