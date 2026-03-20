<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Chofer;
use App\Models\Vehiculo;
use App\Models\Secretaria;

class AbmChoferes extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;

    // Campos del formulario
    public $choferId;
    public $nombre;
    public $dni;
    public $numero_empleado;
    public $licencia;
    public $tipo_licencia;
    public $vencimiento_licencia;
    public $domicilio;
    public $secretaria_id;
    public $area;
    public $vehiculosSeleccionados = [];

    protected function rules()
    {
        return [
            'nombre'            => 'required|string|max:255',
            'dni'               => 'required|string|max:50|unique:choferes,dni' . ($this->editMode ? ',' . $this->choferId : ''),
            'numero_empleado'   => 'required|string|max:50|unique:choferes,numero_empleado' . ($this->editMode ? ',' . $this->choferId : ''),
            'licencia'          => 'nullable|string|max:100',
            'tipo_licencia'     => 'nullable|string|max:255',
            'vencimiento_licencia' => 'nullable|date',
            'domicilio'         => 'nullable|string|max:255',
            'secretaria_id'     => 'nullable|exists:secretarias,id',
            'area'              => 'nullable|string|max:100',
            'vehiculosSeleccionados' => 'array',
            'vehiculosSeleccionados.*' => 'exists:vehiculos,id',
        ];
    }

    protected $messages = [
        'nombre.required'           => 'El nombre es obligatorio.',
        'dni.required'              => 'El DNI es obligatorio.',
        'dni.unique'                => 'Este DNI ya está registrado.',
        'numero_empleado.required'  => 'El número de empleado es obligatorio.',
        'numero_empleado.unique'    => 'Este número de empleado ya está registrado.',
        'vencimiento_licencia.date' => 'La fecha de vencimiento no es válida.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();

        $choferes = Chofer::where(function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('dni', 'like', '%' . $this->search . '%')
                      ->orWhere('numero_empleado', 'like', '%' . $this->search . '%')
                      ->orWhere('area', 'like', '%' . $this->search . '%');
            })
            ->with('vehiculos')
            ->orderBy('nombre')
            ->paginate(10);

        $vehiculos = Vehiculo::orderBy('nro_movil')->get();
        $secretarias = Secretaria::orderBy('secretaria')->get();

        return view('livewire.abm-choferes', [
            'choferes'    => $choferes,
            'vehiculos'   => $vehiculos,
            'secretarias' => $secretarias,
            'puedeCrear'   => $user->puedeCrearChoferes(),
            'puedeEditar'  => $user->puedeEditarChoferes(),
            'puedeEliminar' => $user->puedeEliminarChoferes(),
        ])->layout('layouts.app', ['header' => 'Choferes']);
    }

    public function crear()
    {
        if (!auth()->user()->puedeCrearChoferes()) {
            session()->flash('error', 'No tienes permisos para crear choferes.');
            return;
        }

        $this->resetForm();
        $this->showModal = true;
    }

    public function editar($id)
    {
        if (!auth()->user()->puedeEditarChoferes()) {
            session()->flash('error', 'No tienes permisos para editar choferes.');
            return;
        }

        $chofer = Chofer::with('vehiculos')->findOrFail($id);

        $this->choferId             = $chofer->id;
        $this->nombre               = $chofer->nombre;
        $this->dni                  = $chofer->dni;
        $this->numero_empleado      = $chofer->numero_empleado;
        $this->licencia             = $chofer->licencia;
        $this->tipo_licencia        = $chofer->tipo_licencia;
        $this->vencimiento_licencia = $chofer->vencimiento_licencia?->format('Y-m-d');
        $this->domicilio            = $chofer->domicilio;
        $this->secretaria_id        = $chofer->secretaria_id;
        $this->area                 = $chofer->area;
        $this->vehiculosSeleccionados = $chofer->vehiculos->pluck('id')->toArray();

        $this->editMode  = true;
        $this->showModal = true;
    }

    public function guardar()
    {
        if (!$this->editMode && !auth()->user()->puedeCrearChoferes()) {
            session()->flash('error', 'No tienes permisos para crear choferes.');
            $this->cerrarModal();
            return;
        }

        if ($this->editMode && !auth()->user()->puedeEditarChoferes()) {
            session()->flash('error', 'No tienes permisos para editar choferes.');
            $this->cerrarModal();
            return;
        }

        $this->validate();

        $data = [
            'nombre'               => $this->nombre,
            'dni'                  => $this->dni,
            'numero_empleado'      => $this->numero_empleado,
            'licencia'             => $this->licencia ?: null,
            'tipo_licencia'        => $this->tipo_licencia ?: null,
            'vencimiento_licencia' => $this->vencimiento_licencia ?: null,
            'domicilio'            => $this->domicilio ?: null,
            'secretaria_id'        => $this->secretaria_id ?: null,
            'area'                 => $this->area ?: null,
        ];

        if ($this->editMode) {
            $chofer = Chofer::findOrFail($this->choferId);
            $chofer->update($data);
            $chofer->vehiculos()->sync($this->vehiculosSeleccionados);
            session()->flash('message', 'Chofer actualizado correctamente.');
        } else {
            $chofer = Chofer::create($data);
            $chofer->vehiculos()->sync($this->vehiculosSeleccionados);
            session()->flash('message', 'Chofer creado correctamente.');
        }

        $this->cerrarModal();
    }

    public function eliminar($id)
    {
        if (!auth()->user()->puedeEliminarChoferes()) {
            session()->flash('error', 'No tienes permisos para eliminar choferes.');
            return;
        }

        try {
            Chofer::findOrFail($id)->delete();
            session()->flash('message', 'Chofer eliminado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'No se pudo eliminar el chofer.');
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
            'choferId', 'nombre', 'dni', 'numero_empleado', 'licencia',
            'tipo_licencia', 'vencimiento_licencia', 'domicilio',
            'secretaria_id', 'area', 'vehiculosSeleccionados', 'editMode',
        ]);
        $this->resetValidation();
    }
}
