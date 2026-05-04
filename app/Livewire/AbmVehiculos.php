<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Vehiculo;
use App\Models\Deposito;
use App\Models\DocumentoVehiculo;
use Illuminate\Support\Facades\Storage;

class AbmVehiculos extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $showFilters = false;
    public $showModalDocumentos = false;

    // Filtros
    public $filtro_marca = '';
    public $filtro_modelo = '';
    public $filtro_estado = '';
    public $filtro_deposito = '';

    // Campos del formulario
    public $nro_movil;
    public $vehiculoId;
    public $vehiculo;
    public $marca;
    public $nro_motor;
    public $nro_chasis;
    public $modelo;
    public $patente;
    public $tipo_combustible;
    public $vencimiento_oblea;
    public $nro_poliza;
    public $vencimiento_poliza;
    public $vencimiento_vtv;
    public $estado;
    public $id_deposito;

    // Documentos
    public $nuevo_documento;
    public $nueva_descripcion;
    public $documentos_existentes = [];
    public $vehiculoSeleccionado;

    protected function rules()
    {
        return [
            'nro_movil' => [
                'required',
                'string',
                'max:50',
                $this->editMode
                    ? 'unique:vehiculos,nro_movil,' . $this->vehiculoId
                    : 'unique:vehiculos,nro_movil'
            ],
            'vehiculo' => 'required|string|max:255',
            'marca' => 'required|string|max:255',
            'nro_motor' => 'required|string|max:255',
            'nro_chasis' => 'required|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'patente' => 'required|string|max:20',
            'tipo_combustible' => 'required|in:nafta,diesel,gas',
            'vencimiento_oblea' => $this->tipo_combustible === 'gas' ? 'required|date' : 'nullable|date',
            'nro_poliza' => 'required|string|max:255',
            'vencimiento_poliza' => 'nullable|date',
            'vencimiento_vtv' => 'nullable|date',
            'estado' => 'required|in:disponible,en_uso,mantenimiento,fuera_de_servicio',
            'id_deposito' => 'nullable|exists:depositos,id',
        ];
    }

    protected $messages = [
        'nro_movil.required' => 'El numero de movil es obligatorio',
        'nro_movil.unique' => 'Este numero de movil ya esta en uso',
        'vehiculo.required' => 'El nombre del vehiculo es obligatorio',
        'marca.required' => 'La marca es obligatoria',
        'patente.required' => 'La patente es obligatoria',
        'tipo_combustible.required' => 'El tipo de combustible es obligatorio',
        'nro_motor.required' => 'El numero de motor es obligatorio',
        'nro_chasis.required' => 'El numero de chasis es obligatorio',
        'estado.required' => 'El estado es obligatorio',
        'nro_poliza.required' => 'El numero de poliza es obligatorio',
        'id_deposito.required' => 'El deposito es obligatorio',
        'id_deposito.exists' => 'El deposito seleccionado no existe',
        'vencimiento_oblea.required' => 'El vencimiento de oblea es obligatorio para vehiculos a gas',
        'vencimiento_oblea.date' => 'Ingrese una fecha valida',
        'vencimiento_poliza.date' => 'Ingrese una fecha valida',
        'vencimiento_vtv.date' => 'Ingrese una fecha valida',
    ];

    public function mount()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
    }

    public function updatedTipoCombustible()
    {
        if ($this->tipo_combustible !== 'gas') {
            $this->vencimiento_oblea = null;
        }
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFiltroMarca() { $this->resetPage(); }
    public function updatingFiltroModelo() { $this->resetPage(); }
    public function updatingFiltroEstado() { $this->resetPage(); }
    public function updatingFiltroDeposito() { $this->resetPage(); }

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
        $user = auth()->user();

        $vehiculos = Vehiculo::with(['deposito'])
            ->withCount('documentos')
            ->porCorralonesPermitidos()
            ->where(function($query) {
                $query->where('nro_movil', 'like', '%' . $this->search . '%')
                      ->orWhere('vehiculo', 'like', '%' . $this->search . '%')
                      ->orWhere('marca', 'like', '%' . $this->search . '%')
                      ->orWhere('patente', 'like', '%' . $this->search . '%')
                      ->orWhere('modelo', 'like', '%' . $this->search . '%');
            })
            ->when($this->filtro_marca, fn($q) => $q->where('marca', $this->filtro_marca))
            ->when($this->filtro_modelo, fn($q) => $q->where('modelo', $this->filtro_modelo))
            ->when($this->filtro_estado, fn($q) => $q->where('estado', $this->filtro_estado))
            ->when($this->filtro_deposito, fn($q) => $q->where('id_deposito', $this->filtro_deposito))
            ->orderBy('vehiculo')
            ->paginate(10);

        $depositosPermitidos = $user->getDepositosPermitidosParaModulo('vehiculos');
        $depositos = Deposito::with('corralon')
            ->when(!$user->esAdministrador(), fn($q) => $q->whereIn('id', $depositosPermitidos))
            ->orderBy('deposito')
            ->get();

        $marcas = Vehiculo::select('marca')->distinct()->whereNotNull('marca')->orderBy('marca')->pluck('marca');
        $modelos = Vehiculo::select('modelo')->distinct()->whereNotNull('modelo')->orderBy('modelo')->pluck('modelo');

        return view('livewire.abm-vehiculos', [
            'vehiculos' => $vehiculos,
            'depositos' => $depositos,
            'marcas' => $marcas,
            'modelos' => $modelos,
            'puedeCrear' => $user->puedeCrearVehiculos(),
            'puedeEditar' => $user->puedeEditarVehiculos(),
            'puedeEliminar' => $user->puedeEliminarVehiculos(),
        ])->layout('layouts.app', [
            'header' => 'ABM Vehiculos'
        ]);
    }

    private function verificarAccesoVehiculo($vehiculo): bool
    {
        $user = auth()->user();
        if ($user->esAdministrador()) return true;
        $depositosPermitidos = $user->getDepositosPermitidosParaModulo('vehiculos');
        return in_array($vehiculo->id_deposito, $depositosPermitidos);
    }

    public function abrirModalDocumentos($vehiculoId)
    {
        $vehiculo = Vehiculo::with('documentos')->findOrFail($vehiculoId);

        if (!$this->verificarAccesoVehiculo($vehiculo)) {
            session()->flash('error', 'No tienes permisos para ver los documentos de este vehiculo.');
            return;
        }

        $this->vehiculoId = $vehiculoId;
        $this->vehiculoSeleccionado = $vehiculo;
        $this->documentos_existentes = $vehiculo->documentos->toArray();
        $this->showModalDocumentos = true;
    }

    public function cerrarModalDocumentos()
    {
        $this->showModalDocumentos = false;
        $this->reset(['vehiculoId', 'vehiculoSeleccionado', 'documentos_existentes', 'nuevo_documento', 'nueva_descripcion']);
        $this->resetErrorBag();
    }

    public function crear()
    {
        if (!auth()->user()->puedeCrearVehiculos()) {
            session()->flash('error', 'No tienes permisos para crear vehiculos.');
            return;
        }

        $this->resetForm();
        $this->showModal = true;
    }

    public function editar($id)
    {
        if (!auth()->user()->puedeEditarVehiculos()) {
            session()->flash('error', 'No tienes permisos para editar vehiculos.');
            return;
        }

        $vehiculo = Vehiculo::findOrFail($id);

        if (!$this->verificarAccesoVehiculo($vehiculo)) {
            session()->flash('error', 'No tienes permisos para editar este vehiculo.');
            return;
        }

        $this->vehiculoId = $vehiculo->id;
        $this->nro_movil = $vehiculo->nro_movil;
        $this->vehiculo = $vehiculo->vehiculo;
        $this->marca = $vehiculo->marca;
        $this->nro_motor = $vehiculo->nro_motor;
        $this->nro_chasis = $vehiculo->nro_chasis;
        $this->modelo = $vehiculo->modelo;
        $this->patente = $vehiculo->patente;
        $this->tipo_combustible = $vehiculo->tipo_combustible;
        $this->vencimiento_oblea = $vehiculo->vencimiento_oblea;
        $this->nro_poliza = $vehiculo->nro_poliza;
        $this->vencimiento_poliza = $vehiculo->vencimiento_poliza;
        $this->vencimiento_vtv = $vehiculo->vencimiento_vtv;
        $this->estado = $vehiculo->estado;
        $this->id_deposito = $vehiculo->id_deposito;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function guardar()
    {
        if (!$this->editMode && !auth()->user()->puedeCrearVehiculos()) {
            session()->flash('error', 'No tienes permisos para crear vehiculos.');
            $this->showModal = false;
            return;
        }

        if ($this->editMode && !auth()->user()->puedeEditarVehiculos()) {
            session()->flash('error', 'No tienes permisos para editar vehiculos.');
            $this->showModal = false;
            return;
        }

        $this->validate();

        $user = auth()->user();
        $depositosPermitidos = $user->getDepositosPermitidosParaModulo('vehiculos');

        if (!$user->esAdministrador() && !in_array((int)$this->id_deposito, $depositosPermitidos)) {
            session()->flash('error', 'No tienes permisos para usar ese deposito.');
            return;
        }

        $data = [
            'nro_movil' => $this->nro_movil,
            'vehiculo' => $this->vehiculo,
            'marca' => $this->marca,
            'nro_motor' => $this->nro_motor,
            'nro_chasis' => $this->nro_chasis,
            'modelo' => $this->modelo,
            'patente' => $this->patente,
            'tipo_combustible' => $this->tipo_combustible,
            'vencimiento_oblea' => $this->tipo_combustible === 'gas' ? $this->vencimiento_oblea : null,
            'nro_poliza' => $this->nro_poliza,
            'vencimiento_poliza' => $this->vencimiento_poliza,
            'vencimiento_vtv' => $this->vencimiento_vtv,
            'id_secretaria' => null,
            'estado' => $this->estado,
            'id_deposito' => $this->id_deposito,
        ];

        if ($this->editMode) {
            $vehiculo = Vehiculo::findOrFail($this->vehiculoId);

            if (!$this->verificarAccesoVehiculo($vehiculo)) {
                session()->flash('error', 'No tienes permisos para editar este vehiculo.');
                return;
            }

            $vehiculo->update($data);
            session()->flash('message', 'Vehiculo actualizado exitosamente.');
        } else {
            Vehiculo::create($data);
            session()->flash('message', 'Vehiculo creado exitosamente.');
        }

        $this->cerrarModal();
    }

    public function agregarDocumento()
    {
        if (!auth()->user()->puedeEditarVehiculos()) {
            session()->flash('error', 'No tienes permisos para agregar documentos.');
            return;
        }

        $this->validate([
            'nuevo_documento' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'nueva_descripcion' => 'required|string|max:255',
        ]);

        if (!$this->vehiculoId) {
            session()->flash('error', 'Error al identificar el vehiculo.');
            return;
        }

        $vehiculo = Vehiculo::findOrFail($this->vehiculoId);

        if (!$this->verificarAccesoVehiculo($vehiculo)) {
            session()->flash('error', 'No tienes permisos para modificar este vehiculo.');
            return;
        }

        $archivo = $this->nuevo_documento->store('vehiculos/documentos', 'public');

        DocumentoVehiculo::create([
            'id_vehiculo' => $this->vehiculoId,
            'descripcion' => $this->nueva_descripcion,
            'archivo' => $archivo,
        ]);

        $this->nuevo_documento = null;
        $this->nueva_descripcion = '';
        $this->documentos_existentes = DocumentoVehiculo::where('id_vehiculo', $this->vehiculoId)->get()->toArray();

        session()->flash('message', 'Documento agregado exitosamente.');
    }

    public function eliminarDocumento($documentoId)
    {
        if (!auth()->user()->puedeEditarVehiculos()) {
            session()->flash('error', 'No tienes permisos para eliminar documentos.');
            return;
        }

        $documento = DocumentoVehiculo::findOrFail($documentoId);
        $vehiculo = Vehiculo::findOrFail($documento->id_vehiculo);

        if (!$this->verificarAccesoVehiculo($vehiculo)) {
            session()->flash('error', 'No tienes permisos para modificar este vehiculo.');
            return;
        }

        Storage::disk('public')->delete($documento->archivo);
        $documento->delete();

        $this->documentos_existentes = DocumentoVehiculo::where('id_vehiculo', $this->vehiculoId)->get()->toArray();

        session()->flash('message', 'Documento eliminado exitosamente.');
    }

    public function eliminar($id)
    {
        if (!auth()->user()->puedeEliminarVehiculos()) {
            session()->flash('error', 'No tienes permisos para eliminar vehiculos.');
            return;
        }

        $vehiculo = Vehiculo::with('documentos')->findOrFail($id);

        if (!$this->verificarAccesoVehiculo($vehiculo)) {
            session()->flash('error', 'No tienes permisos para eliminar este vehiculo.');
            return;
        }

        foreach ($vehiculo->documentos as $documento) {
            Storage::disk('public')->delete($documento->archivo);
            $documento->delete();
        }

        $vehiculo->delete();
        session()->flash('message', 'Vehiculo eliminado correctamente.');
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'nro_movil',
            'vehiculoId',
            'vehiculo',
            'marca',
            'nro_motor',
            'nro_chasis',
            'modelo',
            'patente',
            'tipo_combustible',
            'vencimiento_oblea',
            'nro_poliza',
            'vencimiento_poliza',
            'vencimiento_vtv',
            'estado',
            'id_deposito'
        ]);
        $this->editMode = false;
        $this->resetErrorBag();
    }
}
