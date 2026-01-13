<?php

namespace App\Livewire;

use App\Models\Evento;
use Livewire\Component;
use Livewire\WithPagination;

class AbmEventos extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    
    // Campos del formulario
    public $evento_id;
    public $evento;
    public $fecha;
    public $ubicacion;
    public $secretaria;

    protected $rules = [
        'evento' => 'required|string|max:200',
        'fecha' => 'required|date',
        'ubicacion' => 'required|string|max:200',
        'secretaria' => 'nullable|string|max:200',
    ];

    protected $messages = [
        'evento.required' => 'El nombre del evento es obligatorio',
        'evento.max' => 'El nombre del evento no puede exceder 200 caracteres',
        'fecha.required' => 'La fecha es obligatoria',
        'fecha.date' => 'Debe ingresar una fecha válida',
        'ubicacion.required' => 'La ubicación es obligatoria',
        'ubicacion.max' => 'La ubicación no puede exceder 200 caracteres',
        'secretaria.max' => 'La secretaría no puede exceder 200 caracteres',
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
        
        $eventos = Evento::when($this->search, function($query) {
                $query->where('evento', 'like', '%' . $this->search . '%')
                      ->orWhere('ubicacion', 'like', '%' . $this->search . '%')
                      ->orWhere('secretaria', 'like', '%' . $this->search . '%');
            })
            ->orderBy('fecha', 'asc')
            ->paginate(10);

        return view('livewire.abm-eventos', [
            'eventos' => $eventos,
            // Pasar permisos a la vista
            'puedeCrear' => $user->puedeCrearEventos(),
            'puedeEditar' => $user->puedeEditarEventos(),
            'puedeEliminar' => $user->puedeEliminarEventos(),
        ])->layout('layouts.app', [
            'header' => 'ABM Eventos'
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function crear()
    {
        // Verificar permiso de creación por rol
        if (!auth()->user()->puedeCrearEventos()) {
            session()->flash('error', 'No tienes permisos para crear eventos.');
            return;
        }
        
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editar($id)
    {
        // Verificar permiso de edición por rol
        if (!auth()->user()->puedeEditarEventos()) {
            session()->flash('error', 'No tienes permisos para editar eventos.');
            return;
        }
        
        $evento = Evento::findOrFail($id);
        
        $this->evento_id = $evento->id;
        $this->evento = $evento->evento;
        $this->fecha = $evento->fecha->format('Y-m-d');
        $this->ubicacion = $evento->ubicacion;
        $this->secretaria = $evento->secretaria;
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function guardar()
    {
        // Verificar permisos por rol
        if (!$this->editMode && !auth()->user()->puedeCrearEventos()) {
            session()->flash('error', 'No tienes permisos para crear eventos.');
            $this->showModal = false;
            return;
        }

        if ($this->editMode && !auth()->user()->puedeEditarEventos()) {
            session()->flash('error', 'No tienes permisos para editar eventos.');
            $this->showModal = false;
            return;
        }
        
        $this->validate();

        if ($this->editMode) {
            $evento = Evento::findOrFail($this->evento_id);
            $evento->update([
                'evento' => $this->evento,
                'fecha' => $this->fecha,
                'ubicacion' => $this->ubicacion,
                'secretaria' => $this->secretaria,
            ]);
            session()->flash('message', 'Evento actualizado correctamente.');
        } else {
            Evento::create([
                'evento' => $this->evento,
                'fecha' => $this->fecha,
                'ubicacion' => $this->ubicacion,
                'secretaria' => $this->secretaria,
            ]);
            session()->flash('message', 'Evento creado correctamente.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function eliminar($id)
    {
        // Verificar permiso de eliminación por rol
        if (!auth()->user()->puedeEliminarEventos()) {
            session()->flash('error', 'No tienes permisos para eliminar eventos.');
            return;
        }
        
        $evento = Evento::findOrFail($id);
        
        // Aquí puedes agregar validaciones adicionales si el evento tiene relaciones
        // Por ejemplo: if ($evento->algunaRelacion()->count() > 0) { ... }
        
        $evento->delete();
        session()->flash('message', 'Evento eliminado correctamente.');
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->evento_id = null;
        $this->evento = '';
        $this->fecha = '';
        $this->ubicacion = '';
        $this->secretaria = '';
        $this->resetErrorBag();
    }
}