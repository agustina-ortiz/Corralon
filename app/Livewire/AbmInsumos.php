<?php

namespace App\Livewire;

use App\Models\Insumo;
use App\Models\CategoriaInsumo;
use App\Models\Deposito;
use Livewire\Component;
use Livewire\WithPagination;

class AbmInsumos extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $showFilters = false;
    
    // Filtros
    public $filtro_categoria = '';
    public $filtro_unidad = '';
    public $filtro_deposito = '';
    public $filtro_stock_bajo = false;
    
    // Campos del formulario
    public $insumo_id;
    public $insumo;
    public $id_categoria;
    public $unidad;
    public $stock_actual;
    public $stock_minimo;
    public $id_deposito;

    protected $rules = [
        'insumo' => 'required|string|max:100',
        'id_categoria' => 'required|exists:categorias_insumos,id',
        'unidad' => 'required|string|max:20',
        'stock_actual' => 'required|numeric|min:0',
        'stock_minimo' => 'required|numeric|min:0',
        'id_deposito' => 'required|exists:depositos,id',
    ];

    public function mount()
    {
        // Verificar que el usuario esté autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $this->limpiarFiltros();
    }

    public function render()
    {
        $user = auth()->user();
        
        $insumos = Insumo::with(['categoriaInsumo', 'deposito.corralon'])
            ->porCorralonesPermitidos()
            ->when($this->search, function($query) {
                $query->where('insumo', 'like', '%' . $this->search . '%');
            })
            ->when($this->filtro_categoria, function($query) {
                $query->where('id_categoria', $this->filtro_categoria);
            })
            ->when($this->filtro_unidad, function($query) {
                $query->where('unidad', $this->filtro_unidad);
            })
            ->when($this->filtro_deposito, function($query) {
                $query->where('id_deposito', $this->filtro_deposito);
            })
            ->when($this->filtro_stock_bajo, function($query) {
                $query->whereColumn('stock_actual', '<', 'stock_minimo');
            })
            ->orderBy('insumo')
            ->paginate(10);

        $categorias = CategoriaInsumo::orderBy('nombre')->get();
        
        // Filtrar depósitos por corralones permitidos
        $depositosQuery = Deposito::with('corralon')->orderBy('deposito');
        if (!$user->acceso_todos_corralones) {
            $depositosQuery->whereIn('id_corralon', $user->corralones_permitidos ?? []);
        }
        $depositos = $depositosQuery->get();
        
        // Filtrar unidades de insumos visibles
        $unidades = Insumo::select('unidad')
            ->porCorralonesPermitidos()
            ->distinct()
            ->whereNotNull('unidad')
            ->orderBy('unidad')
            ->pluck('unidad');

        return view('livewire.abm-insumos', [
            'insumos' => $insumos,
            'categorias' => $categorias,
            'depositos' => $depositos,
            'unidades' => $unidades,
            // Pasar permisos a la vista
            'puedeCrear' => $user->puedeCrearInsumos(),
            'puedeEditar' => $user->puedeEditarInsumos(),
            'puedeEliminar' => $user->puedeEliminarInsumos(),
        ])->layout('layouts.app', [
            'header' => 'ABM Insumos'
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroCategoria()
    {
        $this->resetPage();
    }

    public function updatingFiltroUnidad()
    {
        $this->resetPage();
    }

    public function updatingFiltroDeposito()
    {
        $this->resetPage();
    }

    public function updatingFiltroStockBajo()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->filtro_categoria = '';
        $this->filtro_unidad = '';
        $this->filtro_deposito = '';
        $this->filtro_stock_bajo = false;
        $this->resetPage();
    }

    public function crear()
    {
        // Verificar permiso de creación por rol
        if (!auth()->user()->puedeCrearInsumos()) {
            session()->flash('error', 'No tienes permisos para crear insumos.');
            return;
        }
        
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editar($id)
    {
        // Verificar permiso de edición por rol
        if (!auth()->user()->puedeEditarInsumos()) {
            session()->flash('error', 'No tienes permisos para editar insumos.');
            return;
        }
        
        $insumo = Insumo::findOrFail($id);
        
        // Verificar que el usuario tenga acceso a este insumo por corralón
        $user = auth()->user();
        if (!$user->acceso_todos_corralones) {
            $corralonId = $insumo->deposito->id_corralon;
            if (!in_array($corralonId, $user->corralones_permitidos ?? [])) {
                session()->flash('error', 'No tienes permisos para editar insumos de este corralón.');
                return;
            }
        }
        
        $this->insumo_id = $insumo->id;
        $this->insumo = $insumo->insumo;
        $this->id_categoria = $insumo->id_categoria;
        $this->unidad = $insumo->unidad;
        $this->stock_actual = $insumo->stock_actual;
        $this->stock_minimo = $insumo->stock_minimo;
        $this->id_deposito = $insumo->id_deposito;
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function guardar()
    {
        // Verificar permisos por rol
        if (!$this->editMode && !auth()->user()->puedeCrearInsumos()) {
            session()->flash('error', 'No tienes permisos para crear insumos.');
            $this->showModal = false;
            return;
        }

        if ($this->editMode && !auth()->user()->puedeEditarInsumos()) {
            session()->flash('error', 'No tienes permisos para editar insumos.');
            $this->showModal = false;
            return;
        }
        
        $this->validate();
        
        $user = auth()->user();
        
        // Verificar que el depósito seleccionado pertenezca a un corralón permitido
        if (!$user->acceso_todos_corralones) {
            $deposito = Deposito::find($this->id_deposito);
            if (!in_array($deposito->id_corralon, $user->corralones_permitidos ?? [])) {
                session()->flash('error', 'No tienes permisos para usar ese depósito.');
                return;
            }
        }

        if ($this->editMode) {
            $insumo = Insumo::findOrFail($this->insumo_id);
            
            // Verificar acceso al insumo original por corralón
            if (!$user->acceso_todos_corralones) {
                $corralonId = $insumo->deposito->id_corralon;
                if (!in_array($corralonId, $user->corralones_permitidos ?? [])) {
                    session()->flash('error', 'No tienes permisos para editar insumos de este corralón.');
                    return;
                }
            }
            
            $insumo->update([
                'insumo' => $this->insumo,
                'id_categoria' => $this->id_categoria,
                'unidad' => $this->unidad,
                'stock_actual' => $this->stock_actual,
                'stock_minimo' => $this->stock_minimo,
                'id_deposito' => $this->id_deposito,
            ]);
            session()->flash('message', 'Insumo actualizado correctamente.');
        } else {
            Insumo::create([
                'insumo' => $this->insumo,
                'id_categoria' => $this->id_categoria,
                'unidad' => $this->unidad,
                'stock_actual' => $this->stock_actual,
                'stock_minimo' => $this->stock_minimo,
                'id_deposito' => $this->id_deposito,
            ]);
            session()->flash('message', 'Insumo creado correctamente.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function eliminar($id)
    {
        // Verificar permiso de eliminación por rol
        if (!auth()->user()->puedeEliminarInsumos()) {
            session()->flash('error', 'No tienes permisos para eliminar insumos.');
            return;
        }
        
        $insumo = Insumo::findOrFail($id);
        
        // Verificar que el usuario tenga acceso a este insumo por corralón
        $user = auth()->user();
        if (!$user->acceso_todos_corralones) {
            $corralonId = $insumo->deposito->id_corralon;
            if (!in_array($corralonId, $user->corralones_permitidos ?? [])) {
                session()->flash('error', 'No tienes permisos para eliminar insumos de este corralón.');
                return;
            }
        }
        
        try {
            $insumo->delete();
            session()->flash('message', 'Insumo eliminado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'No se puede eliminar el insumo porque tiene movimientos asociados.');
        }
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->insumo_id = null;
        $this->insumo = '';
        $this->id_categoria = '';
        $this->unidad = '';
        $this->stock_actual = '';
        $this->stock_minimo = '';
        $this->id_deposito = '';
        $this->resetErrorBag();
    }
}