<?php

namespace App\Livewire;

use App\Models\Deposito;
use App\Models\Corralon;
use Livewire\Component;
use Livewire\WithPagination;

class AbmDepositos extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    
    // Campos del formulario
    public $deposito_id;
    public $deposito;
    public $id_corralon;

    protected $rules = [
        'deposito' => 'required|string|max:100',
        'id_corralon' => 'required|exists:corralones,id',
    ];

    protected $messages = [
        'deposito.required' => 'El nombre del depósito es obligatorio',
        'id_corralon.required' => 'Debe seleccionar un corralón',
        'id_corralon.exists' => 'El corralón seleccionado no es válido',
    ];

    public function mount()
    {
        // Verificar que el usuario esté autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }
    }

    public function render()
    {
        $user = auth()->user();
        
        $depositos = Deposito::with('corralon')
            ->porCorralonesPermitidos()
            ->when($this->search, function($query) {
                $query->where('deposito', 'like', '%' . $this->search . '%')
                      ->orWhere('id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('id')
            ->paginate(10);

        // Filtrar corralones por permisos
        $corralonesQuery = Corralon::orderBy('descripcion');
        if (!$user->acceso_todos_corralones) {
            $corralonesQuery->whereIn('id', $user->corralones_permitidos ?? []);
        }
        $corralones = $corralonesQuery->get();

        return view('livewire.abm-depositos', [
            'depositos' => $depositos,
            'corralones' => $corralones,
            // Pasar permisos a la vista
            'puedeCrear' => $user->puedeCrearDepositos(),
            'puedeEditar' => $user->puedeEditarDepositos(),
            'puedeEliminar' => $user->puedeEliminarDepositos(),
        ])->layout('layouts.app', [
            'header' => 'ABM Depósitos'
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function crear()
    {
        // Verificar permiso de creación por rol
        if (!auth()->user()->puedeCrearDepositos()) {
            session()->flash('error', 'No tienes permisos para crear depósitos.');
            return;
        }
        
        $this->resetForm();
        $this->editMode = false;
        
        // Si el usuario solo tiene acceso a un corralón, preseleccionarlo
        $user = auth()->user();
        if (!$user->acceso_todos_corralones && count($user->corralones_permitidos ?? []) === 1) {
            $this->id_corralon = $user->corralones_permitidos[0];
        }
        
        $this->showModal = true;
    }

    public function editar($id)
    {
        // Verificar permiso de edición por rol
        if (!auth()->user()->puedeEditarDepositos()) {
            session()->flash('error', 'No tienes permisos para editar depósitos.');
            return;
        }
        
        $deposito = Deposito::findOrFail($id);
        
        // Verificar que el usuario tenga acceso a este depósito por corralón
        $user = auth()->user();
        if (!$user->acceso_todos_corralones) {
            if (!in_array($deposito->id_corralon, $user->corralones_permitidos ?? [])) {
                session()->flash('error', 'No tienes permisos para editar depósitos de este corralón.');
                return;
            }
        }
        
        $this->deposito_id = $deposito->id;
        $this->deposito = $deposito->deposito;
        $this->id_corralon = $deposito->id_corralon;
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function guardar()
    {
        // Verificar permisos por rol
        if (!$this->editMode && !auth()->user()->puedeCrearDepositos()) {
            session()->flash('error', 'No tienes permisos para crear depósitos.');
            $this->showModal = false;
            return;
        }

        if ($this->editMode && !auth()->user()->puedeEditarDepositos()) {
            session()->flash('error', 'No tienes permisos para editar depósitos.');
            $this->showModal = false;
            return;
        }
        
        $this->validate();
        
        $user = auth()->user();
        
        // Verificar que el corralón seleccionado esté permitido
        if (!$user->acceso_todos_corralones) {
            if (!in_array($this->id_corralon, $user->corralones_permitidos ?? [])) {
                session()->flash('error', 'No tienes permisos para usar ese corralón.');
                return;
            }
        }

        if ($this->editMode) {
            $deposito = Deposito::findOrFail($this->deposito_id);
            
            // Verificar acceso al depósito original por corralón
            if (!$user->acceso_todos_corralones) {
                if (!in_array($deposito->id_corralon, $user->corralones_permitidos ?? [])) {
                    session()->flash('error', 'No tienes permisos para editar depósitos de este corralón.');
                    return;
                }
            }
            
            $deposito->update([
                'deposito' => $this->deposito,
                'id_corralon' => $this->id_corralon,
            ]);
            session()->flash('message', 'Depósito actualizado correctamente.');
        } else {
            Deposito::create([
                'deposito' => $this->deposito,
                'id_corralon' => $this->id_corralon,
            ]);
            session()->flash('message', 'Depósito creado correctamente.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function eliminar($id)
    {
        // Verificar permiso de eliminación por rol
        if (!auth()->user()->puedeEliminarDepositos()) {
            session()->flash('error', 'No tienes permisos para eliminar depósitos.');
            return;
        }
        
        $deposito = Deposito::findOrFail($id);
        
        // Verificar que el usuario tenga acceso a este depósito por corralón
        $user = auth()->user();
        if (!$user->acceso_todos_corralones) {
            if (!in_array($deposito->id_corralon, $user->corralones_permitidos ?? [])) {
                session()->flash('error', 'No tienes permisos para eliminar depósitos de este corralón.');
                return;
            }
        }
        
        // Verificar que no tenga elementos asignados
        if ($deposito->insumos()->count() > 0) {
            session()->flash('error', 'No se puede eliminar el depósito porque tiene insumos asignados.');
            return;
        }
        
        if ($deposito->maquinarias()->count() > 0) {
            session()->flash('error', 'No se puede eliminar el depósito porque tiene maquinarias asignadas.');
            return;
        }
        
        if ($deposito->vehiculos()->count() > 0) {
            session()->flash('error', 'No se puede eliminar el depósito porque tiene vehículos asignados.');
            return;
        }
        
        try {
            $deposito->delete();
            session()->flash('message', 'Depósito eliminado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'No se puede eliminar el depósito porque tiene movimientos asociados.');
        }
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->deposito_id = null;
        $this->deposito = '';
        $this->id_corralon = '';
        $this->resetErrorBag();
    }
}