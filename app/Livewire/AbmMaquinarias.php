<?php

namespace App\Livewire;

use App\Models\Maquinaria;
use App\Models\CategoriaMaquinaria;
use App\Models\Deposito;
use Livewire\Component;
use Livewire\WithPagination;

class AbmMaquinarias extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $showFilters = false;
    
    // Filtros
    public $filtro_categoria = '';
    public $filtro_estado = '';
    public $filtro_deposito = '';
    
    // Campos del formulario
    public $maquinaria_id;
    public $maquinaria;
    public $id_categoria_maquinaria;
    public $estado;
    public $id_deposito;

    protected $rules = [
        'maquinaria' => 'required|string|max:100',
        'id_categoria_maquinaria' => 'required|exists:categoria_maquinarias,id',
        'estado' => 'required|in:disponible,no disponible',
        'id_deposito' => 'required|exists:depositos,id',
    ];

    public function render()
    {
        $maquinarias = Maquinaria::with(['categoriaMaquinaria', 'deposito'])
            ->when($this->search, function($query) {
                $query->where('maquinaria', 'like', '%' . $this->search . '%');
            })
            ->when($this->filtro_categoria, function($query) {
                $query->where('id_categoria_maquinaria', $this->filtro_categoria);
            })
            ->when($this->filtro_estado, function($query) {
                $query->where('estado', $this->filtro_estado);
            })
            ->when($this->filtro_deposito, function($query) {
                $query->where('id_deposito', $this->filtro_deposito);
            })
            ->orderBy('maquinaria')
            ->paginate(10);

        $categorias = CategoriaMaquinaria::orderBy('nombre')->get();
        $depositos = Deposito::orderBy('deposito')->get();

        return view('livewire.abm-maquinarias', [
            'maquinarias' => $maquinarias,
            'categorias' => $categorias,
            'depositos' => $depositos
        ])->layout('layouts.app', [
            'header' => 'ABM Maquinarias'
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

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function updatingFiltroDeposito()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->filtro_categoria = '';
        $this->filtro_estado = '';
        $this->filtro_deposito = '';
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
        $maquinaria = Maquinaria::findOrFail($id);
        $this->maquinaria_id = $maquinaria->id;
        $this->maquinaria = $maquinaria->maquinaria;
        $this->id_categoria_maquinaria = $maquinaria->id_categoria_maquinaria;
        $this->estado = $maquinaria->estado;
        $this->id_deposito = $maquinaria->id_deposito;
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->editMode) {
            $maquinaria = Maquinaria::findOrFail($this->maquinaria_id);
            $maquinaria->update([
                'maquinaria' => $this->maquinaria,
                'id_categoria_maquinaria' => $this->id_categoria_maquinaria,
                'estado' => $this->estado,
                'id_deposito' => $this->id_deposito,
            ]);
            session()->flash('message', 'Maquinaria actualizada correctamente.');
        } else {
            Maquinaria::create([
                'maquinaria' => $this->maquinaria,
                'id_categoria_maquinaria' => $this->id_categoria_maquinaria,
                'estado' => $this->estado,
                'id_deposito' => $this->id_deposito,
            ]);
            session()->flash('message', 'Maquinaria creada correctamente.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function eliminar($id)
    {
        Maquinaria::findOrFail($id)->delete();
        session()->flash('message', 'Maquinaria eliminada correctamente.');
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->maquinaria_id = null;
        $this->maquinaria = '';
        $this->id_categoria_maquinaria = '';
        $this->estado = 'disponible';
        $this->id_deposito = '';
        $this->resetErrorBag();
    }
}