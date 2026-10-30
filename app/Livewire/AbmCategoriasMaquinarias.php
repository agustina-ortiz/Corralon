<?php

namespace App\Livewire;

use App\Livewire\Traits\ConfirmaDescarte;
use App\Livewire\Traits\EnviaToasts;
use App\Models\CategoriaMaquinaria;
use App\Livewire\Traits\OrdenaPorColumna;
use Livewire\Component;
use Livewire\WithPagination;

class AbmCategoriasMaquinarias extends Component
{
    use WithPagination;
    use OrdenaPorColumna;
    use EnviaToasts;
    use ConfirmaDescarte;

    public $search = '';
    public $showModal = false;

    // Registro a eliminar (abre el modal de confirmación)
    public $eliminarId = null;
    public $editMode = false;
    
    // Campos del formulario
    public $categoria_id;
    public $nombre;
    public $descripcion;

    protected $rules = [
        'nombre' => 'required|string|max:100',
        'descripcion' => 'nullable|string',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre de la categoría es obligatorio.',
        'nombre.max' => 'El nombre de la categoría no puede exceder los 100 caracteres.',
        'descripcion.string' => 'La descripción debe ser una cadena de texto.',
    ];

    public function mount()
    {
        // Verificar que el usuario esté autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }
    }

    protected function columnasOrdenables(): array
    {
        return [
            'id' => 'id',
            'nombre' => 'nombre',
            'descripcion' => 'descripcion',
        ];
    }

    protected function ordenInicial(): array
    {
        return ['nombre', 'asc'];
    }

    public function render()
    {
        $user = auth()->user();
        
        $categorias = CategoriaMaquinaria::query()
            ->when($this->search, function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            });
        $categorias = $this->aplicarOrden($categorias)->paginate(10);

        return view('livewire.abm-categorias-maquinarias', [
            'categorias' => $categorias,
            // Pasar permisos a la vista
            'puedeCrear' => $user->puedeCrearCategoriasMaquinarias(),
            'puedeEditar' => $user->puedeEditarCategoriasMaquinarias(),
            'puedeEliminar' => $user->puedeEliminarCategoriasMaquinarias(),
        ])->layout('layouts.app', [
            'header' => 'ABM Categorías de Maquinarias'
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function crear()
    {
        // Verificar permiso de creación por rol
        if (!auth()->user()->puedeCrearCategoriasMaquinarias()) {
            $this->toastError('No tienes permisos para crear categorías de maquinarias.');
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
        if (!auth()->user()->puedeEditarCategoriasMaquinarias()) {
            $this->toastError('No tienes permisos para editar categorías de maquinarias.');
            return;
        }
        
        $categoria = CategoriaMaquinaria::findOrFail($id);
        $this->categoria_id = $categoria->id;
        $this->nombre = $categoria->nombre;
        $this->descripcion = $categoria->descripcion;
        
        $this->editMode = true;
        $this->showModal = true;
        $this->marcarFormularioLimpio();
    }

    public function guardar()
    {
        // Verificar permisos por rol
        if (!$this->editMode && !auth()->user()->puedeCrearCategoriasMaquinarias()) {
            $this->toastError('No tienes permisos para crear categorías de maquinarias.');
            $this->showModal = false;
            return;
        }

        if ($this->editMode && !auth()->user()->puedeEditarCategoriasMaquinarias()) {
            $this->toastError('No tienes permisos para editar categorías de maquinarias.');
            $this->showModal = false;
            return;
        }
        
        $this->validate();

        if ($this->editMode) {
            $categoria = CategoriaMaquinaria::findOrFail($this->categoria_id);
            $categoria->update([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
            ]);
            $this->toastExito('Categoría actualizada correctamente.');
        } else {
            CategoriaMaquinaria::create([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
            ]);
            $this->toastExito('Categoría creada correctamente.');
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
        if (!auth()->user()->puedeEliminarCategoriasMaquinarias()) {
            $this->toastError('No tienes permisos para eliminar categorías de maquinarias.');
            return;
        }
        
        try {
            CategoriaMaquinaria::findOrFail($id)->delete();
            $this->toastExito('Categoría eliminada correctamente.');
        } catch (\Exception $e) {
            $this->toastError('No se puede eliminar la categoría porque tiene maquinarias asociadas.');
        }
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function camposFormulario(): array
    {
        return ['nombre', 'descripcion'];
    }

    public function cerrarFormulario(): void
    {
        $this->cerrarModal();
    }

    private function resetForm()
    {
        $this->categoria_id = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->resetErrorBag();
    }
}