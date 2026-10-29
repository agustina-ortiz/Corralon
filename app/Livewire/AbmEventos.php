<?php

namespace App\Livewire;

use App\Livewire\Traits\ConfirmaDescarte;
use App\Livewire\Traits\EnviaToasts;
use App\Models\Evento;
use App\Models\Secretaria;
use Livewire\Component;
use Livewire\WithPagination;

class AbmEventos extends Component
{
    use WithPagination;
    use EnviaToasts;
    use ConfirmaDescarte;

    public $search = '';
    public $showModal = false;

    // Registro a eliminar (abre el modal de confirmación)
    public $eliminarId = null;
    public $editMode = false;
    
    // Campos del formulario
    public $evento_id;
    public $evento;
    public $fecha;
    public $ubicacion;
    public $secretaria_id;
    public $evento_anual = false;

    protected $rules = [
        'evento' => 'required|string|max:200',
        'fecha' => 'required|date',
        'ubicacion' => 'required|string|max:200',
        'secretaria_id' => 'nullable|exists:secretarias,id',
        'evento_anual' => 'boolean',
    ];

    protected $messages = [
        'evento.required' => 'El nombre del evento es obligatorio',
        'evento.max' => 'El nombre del evento no puede exceder 200 caracteres',
        'fecha.required' => 'La fecha es obligatoria',
        'fecha.date' => 'Debe ingresar una fecha válida',
        'ubicacion.required' => 'La ubicación es obligatoria',
        'ubicacion.max' => 'La ubicación no puede exceder 200 caracteres',
        'secretaria_id.exists' => 'La secretaría seleccionada no es válida',
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
        
        $eventos = Evento::with('secretaria')
            ->when($this->search, function($query) {
                $query->where('evento', 'like', '%' . $this->search . '%')
                      ->orWhere('ubicacion', 'like', '%' . $this->search . '%')
                      ->orWhereHas('secretaria', function($q) {
                          $q->where('secretaria', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy('fecha', 'asc')
            ->paginate(10);

        $secretarias = Secretaria::orderBy('secretaria')->get();

        return view('livewire.abm-eventos', [
            'eventos' => $eventos,
            'secretarias' => $secretarias,
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
            $this->toastError('No tienes permisos para crear eventos.');
            return;
        }
        
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
        $this->marcarFormularioLimpio();
    }

    public function editar($id)
    {
        // Verificar permiso de edición por rol
        if (!auth()->user()->puedeEditarEventos()) {
            $this->toastError('No tienes permisos para editar eventos.');
            return;
        }
        
        $evento = Evento::findOrFail($id);
        
        $this->evento_id = $evento->id;
        $this->evento = $evento->evento;
        $this->fecha = $evento->fecha->format('Y-m-d');
        $this->ubicacion = $evento->ubicacion;
        $this->secretaria_id = $evento->secretaria_id;
        $this->evento_anual = $evento->evento_anual;
        
        $this->editMode = true;
        $this->showModal = true;
        $this->marcarFormularioLimpio();
    }

    public function guardar()
    {
        // Verificar permisos por rol
        if (!$this->editMode && !auth()->user()->puedeCrearEventos()) {
            $this->toastError('No tienes permisos para crear eventos.');
            $this->showModal = false;
            return;
        }

        if ($this->editMode && !auth()->user()->puedeEditarEventos()) {
            $this->toastError('No tienes permisos para editar eventos.');
            $this->showModal = false;
            return;
        }
        
        $this->validate();

        $data = [
            'evento' => $this->evento,
            'fecha' => $this->fecha,
            'ubicacion' => $this->ubicacion,
            'secretaria_id' => $this->secretaria_id,
            'evento_anual' => $this->evento_anual,
        ];

        if ($this->editMode) {
            $evento = Evento::findOrFail($this->evento_id);
            $evento->update($data);
            $this->toastExito('Evento actualizado correctamente.');
        } else {
            Evento::create($data);
            $this->toastExito('Evento creado correctamente.');
        }

        $this->showModal = false;
        $this->resetForm();
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

        // Verificar permiso de eliminación por rol
        if (!auth()->user()->puedeEliminarEventos()) {
            $this->toastError('No tienes permisos para eliminar eventos.');
            return;
        }
        
        try {
            Evento::findOrFail($id)->delete();
            $this->toastExito('Evento eliminado correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->toastError('No se puede eliminar el evento porque tiene movimientos o asignaciones asociadas.');
        } catch (\Exception $e) {
            $this->toastError('No se pudo eliminar el evento.');
        }
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function camposFormulario(): array
    {
        return ['evento', 'fecha', 'ubicacion', 'secretaria_id', 'evento_anual'];
    }

    public function cerrarFormulario(): void
    {
        $this->cerrarModal();
    }

    private function resetForm()
    {
        $this->evento_id = null;
        $this->evento = '';
        $this->fecha = '';
        $this->ubicacion = '';
        $this->secretaria_id = null;
        $this->evento_anual = false;
        $this->resetErrorBag();
    }
}