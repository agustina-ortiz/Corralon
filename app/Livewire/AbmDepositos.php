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

    public function render()
    {
        $depositos = Deposito::with('corralon')
            ->when($this->search, function($query) {
                $query->where('deposito', 'like', '%' . $this->search . '%')
                      ->orWhere('sector', 'like', '%' . $this->search . '%');
            })
            ->orderBy('deposito')
            ->paginate(10);

        $corralones = Corralon::orderBy('descripcion')->get();

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
        $this->showModal = true;
    }

    public function editar($id)
    {
        $deposito = Deposito::findOrFail($id);
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

        if ($this->editMode) {
            $deposito = Deposito::findOrFail($this->deposito_id);
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
        Deposito::findOrFail($id)->delete();
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