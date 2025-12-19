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
    public $sector;
    public $deposito;
    public $id_corralon;

    protected $rules = [
        'sector' => 'required|string|max:100',
        'deposito' => 'required|string|max:100',
        'id_corralon' => 'required|exists:corralones,id',
    ];

    protected $messages = [
        'sector.required' => 'El sector es obligatorio',
        'deposito.required' => 'El nombre del depósito es obligatorio',
        'id_corralon.required' => 'Debe seleccionar un corralón',
        'id_corralon.exists' => 'El corralón seleccionado no es válido',
    ];

    public function render()
    {
        $user = auth()->user(); // ← AGREGAR
        
        $depositos = Deposito::with('corralon')
            ->porCorralonesPermitidos() // ← AGREGAR EL SCOPE
            ->when($this->search, function($query) {
                $query->where('deposito', 'like', '%' . $this->search . '%')
                      ->orWhere('sector', 'like', '%' . $this->search . '%');
            })
            ->orderBy('sector')
            ->paginate(10);

        // Filtrar corralones por permisos
        $corralonesQuery = Corralon::orderBy('descripcion');
        if (!$user->acceso_todos_corralones) {
            $corralonesQuery->whereIn('id', $user->corralones_permitidos ?? []);
        }
        $corralones = $corralonesQuery->get();

        return view('livewire.abm-depositos', [
            'depositos' => $depositos,
            'corralones' => $corralones
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
        $deposito = Deposito::findOrFail($id);
        
        // Verificar que el usuario tenga acceso a este depósito
        $user = auth()->user();
        if (!$user->acceso_todos_corralones) {
            if (!in_array($deposito->id_corralon, $user->corralones_permitidos ?? [])) {
                session()->flash('error', 'No tienes permisos para editar este depósito.');
                return;
            }
        }
        
        $this->deposito_id = $deposito->id;
        $this->sector = $deposito->sector;
        $this->deposito = $deposito->deposito;
        $this->id_corralon = $deposito->id_corralon;
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function guardar()
    {
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
            
            // Verificar acceso al depósito original
            if (!$user->acceso_todos_corralones) {
                if (!in_array($deposito->id_corralon, $user->corralones_permitidos ?? [])) {
                    session()->flash('error', 'No tienes permisos para editar este depósito.');
                    return;
                }
            }
            
            $deposito->update([
                'sector' => $this->sector,
                'deposito' => $this->deposito,
                'id_corralon' => $this->id_corralon,
            ]);
            session()->flash('message', 'Depósito actualizado correctamente.');
        } else {
            Deposito::create([
                'sector' => $this->sector,
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
        $deposito = Deposito::findOrFail($id);
        
        // Verificar que el usuario tenga acceso a este depósito
        $user = auth()->user();
        if (!$user->acceso_todos_corralones) {
            if (!in_array($deposito->id_corralon, $user->corralones_permitidos ?? [])) {
                session()->flash('error', 'No tienes permisos para eliminar este depósito.');
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
        
        $deposito->delete();
        session()->flash('message', 'Depósito eliminado correctamente.');
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->deposito_id = null;
        $this->sector = '';
        $this->deposito = '';
        $this->id_corralon = '';
        $this->resetErrorBag();
    }
}