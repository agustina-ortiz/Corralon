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

    public function render()
    {
        $insumos = Insumo::with(['categoriaInsumo', 'deposito'])
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
        $depositos = Deposito::orderBy('deposito')->get();
        $unidades = Insumo::select('unidad')->distinct()->orderBy('unidad')->pluck('unidad');

        return view('livewire.abm-insumos', [
            'insumos' => $insumos,
            'categorias' => $categorias,
            'depositos' => $depositos,
            'unidades' => $unidades
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
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editar($id)
    {
        $insumo = Insumo::findOrFail($id);
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
        $this->validate();

        if ($this->editMode) {
            $insumo = Insumo::findOrFail($this->insumo_id);
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
        Insumo::findOrFail($id)->delete();
        session()->flash('message', 'Insumo eliminado correctamente.');
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