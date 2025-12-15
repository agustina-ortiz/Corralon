<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CategoriaInsumo;

class AbmCategoriasInsumos extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;

    public $categoria_id;
    public $nombre;

    protected $rules = [
        'nombre' => 'required|string|max:100',
    ];

    public function render()
    {
        $categorias = CategoriaInsumo::when($this->search, function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%');
            })
            ->orderBy('nombre')
            ->paginate(10);

        return view('livewire.abm-categorias-insumos', [
            'categorias' => $categorias
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
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editar($id)
    {
        $categoria = CategoriaInsumo::findOrFail($id);

        $this->categoria_id = $categoria->id;
        $this->nombre = $categoria->nombre;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function guardar()
    {
        $this->validate();

        CategoriaInsumo::updateOrCreate(
            ['id' => $this->categoria_id],
            ['nombre' => $this->nombre,]
        );

        session()->flash(
            'message',
            $this->editMode
                ? 'Categoría actualizada correctamente.'
                : 'Categoría creada correctamente.'
        );

        $this->cerrarModal();
    }

    public function eliminar($id)
    {
        CategoriaInsumo::findOrFail($id)->delete();
        session()->flash('message', 'Categoría eliminada correctamente.');
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->categoria_id = null;
        $this->nombre = '';
        $this->resetErrorBag();
    }
}
