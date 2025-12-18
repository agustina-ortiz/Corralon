<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Vehiculo;
use App\Models\Deposito;

class AbmVehiculos extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $showFilters = false;
    
    // Filtros
    public $filtro_marca = '';
    public $filtro_modelo = '';
    public $filtro_estado = '';
    public $filtro_deposito = '';
    
    // Campos del formulario
    public $vehiculoId;
    public $vehiculo;
    public $marca;
    public $nro_motor;
    public $nro_chasis;
    public $modelo;
    public $patente;
    public $estado;
    public $id_deposito;

    protected $rules = [
        'vehiculo' => 'required|string|max:255',
        'marca' => 'required|string|max:255',
        'nro_motor' => 'nullable|string|max:255',
        'nro_chasis' => 'nullable|string|max:255',
        'modelo' => 'nullable|string|max:255',
        'patente' => 'nullable|string|max:20',
        'estado' => 'required|in:disponible,en_uso,mantenimiento,fuera_de_servicio',
        'id_deposito' => 'required|exists:depositos,id',
    ];

    protected $messages = [
        'vehiculo.required' => 'El nombre del vehículo es obligatorio',
        'marca.required' => 'La marca es obligatoria',
        'estado.required' => 'El estado es obligatorio',
        'id_deposito.required' => 'El depósito es obligatorio',
        'id_deposito.exists' => 'El depósito seleccionado no existe',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroMarca()
    {
        $this->resetPage();
    }

    public function updatingFiltroModelo()
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
        $this->filtro_marca = '';
        $this->filtro_modelo = '';
        $this->filtro_estado = '';
        $this->filtro_deposito = '';
        $this->resetPage();
    }

    public function render()
    {
        $vehiculos = Vehiculo::with('deposito')
            ->where(function($query) {
                $query->where('vehiculo', 'like', '%' . $this->search . '%')
                      ->orWhere('marca', 'like', '%' . $this->search . '%')
                      ->orWhere('patente', 'like', '%' . $this->search . '%')
                      ->orWhere('modelo', 'like', '%' . $this->search . '%');
            })
            ->when($this->filtro_marca, function($query) {
                $query->where('marca', $this->filtro_marca);
            })
            ->when($this->filtro_modelo, function($query) {
                $query->where('modelo', $this->filtro_modelo);
            })
            ->when($this->filtro_estado, function($query) {
                $query->where('estado', $this->filtro_estado);
            })
            ->when($this->filtro_deposito, function($query) {
                $query->where('id_deposito', $this->filtro_deposito);
            })
            ->orderBy('vehiculo')
            ->paginate(10);

        $depositos = Deposito::orderBy('deposito')->get();
        $marcas = Vehiculo::select('marca')->distinct()->whereNotNull('marca')->orderBy('marca')->pluck('marca');
        $modelos = Vehiculo::select('modelo')->distinct()->whereNotNull('modelo')->orderBy('modelo')->pluck('modelo');

        return view('livewire.abm-vehiculos', [
            'vehiculos' => $vehiculos,
            'depositos' => $depositos,
            'marcas' => $marcas,
            'modelos' => $modelos
        ])->layout('layouts.app', [
            'header' => 'ABM Vehículos'
        ]);
    }

    public function crear()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function editar($id)
    {
        $vehiculo = Vehiculo::findOrFail($id);
        
        $this->vehiculoId = $vehiculo->id;
        $this->vehiculo = $vehiculo->vehiculo;
        $this->marca = $vehiculo->marca;
        $this->nro_motor = $vehiculo->nro_motor;
        $this->nro_chasis = $vehiculo->nro_chasis;
        $this->modelo = $vehiculo->modelo;
        $this->patente = $vehiculo->patente;
        $this->estado = $vehiculo->estado;
        $this->id_deposito = $vehiculo->id_deposito;
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->editMode) {
            $vehiculo = Vehiculo::findOrFail($this->vehiculoId);
            $vehiculo->update([
                'vehiculo' => $this->vehiculo,
                'marca' => $this->marca,
                'nro_motor' => $this->nro_motor,
                'nro_chasis' => $this->nro_chasis,
                'modelo' => $this->modelo,
                'patente' => $this->patente,
                'id_secretaria' => 'sec',
                'estado' => $this->estado,
                'id_deposito' => $this->id_deposito,
            ]);

            session()->flash('message', 'Vehículo actualizado exitosamente.');
        } else {
            Vehiculo::create([
                'vehiculo' => $this->vehiculo,
                'marca' => $this->marca,
                'nro_motor' => $this->nro_motor,
                'nro_chasis' => $this->nro_chasis,
                'modelo' => $this->modelo,
                'patente' => $this->patente,
                'id_secretaria' => 'sec',
                'estado' => $this->estado,
                'id_deposito' => $this->id_deposito,
            ]);

            session()->flash('message', 'Vehículo creado exitosamente.');
        }

        $this->cerrarModal();
    }

    public function eliminar($id)
    {
        try {
            $vehiculo = Vehiculo::findOrFail($id);
            $vehiculo->delete();
            
            session()->flash('message', 'Vehículo eliminado exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'No se pudo eliminar el vehículo. Puede estar asociado a otros registros.');
        }
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'vehiculoId',
            'vehiculo',
            'marca',
            'nro_motor',
            'nro_chasis',
            'modelo',
            'patente',
            'estado',
            'id_deposito'
        ]);
        $this->editMode = false;
        $this->resetErrorBag();
    }
}