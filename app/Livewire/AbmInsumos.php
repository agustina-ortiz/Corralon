<?php

namespace App\Livewire;

use App\Models\Insumo;
use App\Models\CategoriaInsumo;
use Livewire\Component;
use Livewire\WithPagination;

class AbmInsumos extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    
    // Campos del formulario
    public $insumo_id;
    public $nombre;
    public $descripcion;
    public $unidad_medida;
    public $stock_minimo;
    public $stock_actual;
    public $precio_unitario;
    public $categoria_insumo_id;

    protected $rules = [
        'nombre' => 'required|string|max:100',
        'descripcion' => 'nullable|string',
        'unidad_medida' => 'required|string|max:20',
        'stock_minimo' => 'required|numeric|min:0',
        'stock_actual' => 'required|numeric|min:0',
        'precio_unitario' => 'required|numeric|min:0',
        'categoria_insumo_id' => 'required|exists:categorias_insumo,id',
    ];

    public function render()
    {
        $insumos = Insumo::with('categoriaInsumo')
            ->when($this->search, function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            })
            ->orderBy('nombre')
            ->paginate(10);

        $categorias = CategoriaInsumo::orderBy('nombre')->get();

        return view('livewire.abm-insumos', [
            'insumos' => $insumos,
            'categorias' => $categorias
        ])->layout('layouts.app', [
            'header' => 'ABM Insumos'
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
        $this->showModal = true;
    }

    public function editar($id)
    {
        $insumo = Insumo::findOrFail($id);
        $this->insumo_id = $insumo->id;
        $this->nombre = $insumo->nombre;
        $this->descripcion = $insumo->descripcion;
        $this->unidad_medida = $insumo->unidad_medida;
        $this->stock_minimo = $insumo->stock_minimo;
        $this->stock_actual = $insumo->stock_actual;
        $this->precio_unitario = $insumo->precio_unitario;
        $this->categoria_insumo_id = $insumo->categoria_insumo_id;
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->editMode) {
            $insumo = Insumo::findOrFail($this->insumo_id);
            $insumo->update([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'unidad_medida' => $this->unidad_medida,
                'stock_minimo' => $this->stock_minimo,
                'stock_actual' => $this->stock_actual,
                'precio_unitario' => $this->precio_unitario,
                'categoria_insumo_id' => $this->categoria_insumo_id,
            ]);
            session()->flash('message', 'Insumo actualizado correctamente.');
        } else {
            Insumo::create([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'unidad_medida' => $this->unidad_medida,
                'stock_minimo' => $this->stock_minimo,
                'stock_actual' => $this->stock_actual,
                'precio_unitario' => $this->precio_unitario,
                'categoria_insumo_id' => $this->categoria_insumo_id,
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
        $this->nombre = '';
        $this->descripcion = '';
        $this->unidad_medida = '';
        $this->stock_minimo = '';
        $this->stock_actual = '';
        $this->precio_unitario = '';
        $this->categoria_insumo_id = '';
        $this->resetErrorBag();
    }
}