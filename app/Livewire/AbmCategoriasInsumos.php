<?php

namespace App\Livewire;

use App\Livewire\Traits\ConfirmaDescarte;
use App\Livewire\Traits\EnviaToasts;
use App\Livewire\Traits\OrdenaPorColumna;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CategoriaInsumo;

class AbmCategoriasInsumos extends Component
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
        return ['id', 'asc'];
    }

    public function render()
    {
        $user = auth()->user();
        
        $categorias = $this->aplicarOrden(CategoriaInsumo::when($this->search, function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            })
        )->paginate(10);

        return view('livewire.abm-categorias-insumos', [
            'categorias' => $categorias,
            // Pasar permisos a la vista
            'puedeCrear' => $user->puedeCrearCategoriasInsumos(),
            'puedeEditar' => $user->puedeEditarCategoriasInsumos(),
            'puedeEliminar' => $user->puedeEliminarCategoriasInsumos(),
        ])->layout('layouts.app', [
            'header' => 'ABM Categorías de Insumos'
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function crear()
    {
        // Verificar permiso de creación por rol
        if (!auth()->user()->puedeCrearCategoriasInsumos()) {
            $this->toastError('No tienes permisos para crear categorías de insumos.');
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
        if (!auth()->user()->puedeEditarCategoriasInsumos()) {
            $this->toastError('No tienes permisos para editar categorías de insumos.');
            return;
        }
        
        $categoria = CategoriaInsumo::findOrFail($id);

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
        if (!$this->editMode && !auth()->user()->puedeCrearCategoriasInsumos()) {
            $this->toastError('No tienes permisos para crear categorías de insumos.');
            $this->showModal = false;
            return;
        }

        if ($this->editMode && !auth()->user()->puedeEditarCategoriasInsumos()) {
            $this->toastError('No tienes permisos para editar categorías de insumos.');
            $this->showModal = false;
            return;
        }
        
        $this->validate();

        CategoriaInsumo::updateOrCreate(
            ['id' => $this->categoria_id],
            [
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
            ]
        );

        $this->toastExito($this->editMode
                ? 'Categoría actualizada correctamente.'
                : 'Categoría creada correctamente.'
        );

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

        // Verificar permiso de eliminación por rol
        if (!auth()->user()->puedeEliminarCategoriasInsumos()) {
            $this->toastError('No tienes permisos para eliminar categorías de insumos.');
            return;
        }
        
        try {
            CategoriaInsumo::findOrFail($id)->delete();
            $this->toastExito('Categoría eliminada correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->toastError('No se puede eliminar la categoría porque tiene insumos o movimientos asociados.');
        } catch (\Exception $e) {
            $this->toastError('No se pudo eliminar la categoría.');
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