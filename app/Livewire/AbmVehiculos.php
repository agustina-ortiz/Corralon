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
            'vehiculo' => 'required|string|max:255',
            'marca' => 'required|string|max:255',
            'nro_motor' => 'nullable|string|max:255',
            'nro_chasis' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'patente' => 'nullable|string|max:20',
            'tipo_combustible' => 'nullable|in:nafta,diesel,gas',
            'vencimiento_oblea' => $this->tipo_combustible === 'gas' ? 'required|date' : 'nullable|date',
            'nro_poliza' => 'nullable|string|max:255',
            'vencimiento_poliza' => 'nullable|date',
            'vencimiento_vtv' => 'nullable|date',
            'estado' => 'required|in:disponible,en_uso,mantenimiento,fuera_de_servicio',
            'id_deposito' => 'required|exists:depositos,id',
            'nuevo_documento' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'nueva_descripcion' => 'required_with:nuevo_documento|string|max:255',
        ];
    }

    protected $messages = [
        'vehiculo.required' => 'El nombre del vehículo es obligatorio',
        'marca.required' => 'La marca es obligatoria',
        'estado.required' => 'El estado es obligatorio',
        'id_deposito.required' => 'El depósito es obligatorio',
        'id_deposito.exists' => 'El depósito seleccionado no existe',
        'vencimiento_oblea.required' => 'El vencimiento de oblea es obligatorio para vehículos a gas',
        'vencimiento_oblea.date' => 'Ingrese una fecha válida',
        'vencimiento_poliza.date' => 'Ingrese una fecha válida',
        'vencimiento_vtv.date' => 'Ingrese una fecha válida',
        'nuevo_documento.mimes' => 'El documento debe ser PDF, JPG, JPEG o PNG',
        'nuevo_documento.max' => 'El documento no debe superar 10MB',
        'nueva_descripcion.required_with' => 'La descripción es obligatoria al cargar un documento',
    ];

    public function updatedTipoCombustible()
    {
        if ($this->tipo_combustible !== 'gas') {
            $this->vencimiento_oblea = null;
        }
    }

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
        $user = auth()->user();

        $vehiculos = Vehiculo::with(['deposito'])
            ->withCount('documentos')
            ->porCorralonesPermitidos()
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

        $depositosQuery = Deposito::with('corralon')->orderBy('deposito');
        if (!$user->acceso_todos_corralones) {
            $depositosQuery->whereIn('id_corralon', $user->corralones_permitidos ?? []);
        }
        
        $depositos = $depositosQuery->get();
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

    public function abrirModalDocumentos($vehiculoId)
    {
        $vehiculo = Vehiculo::with('documentos')->findOrFail($vehiculoId);

        $user = auth()->user();
        if (!$user->acceso_todos_corralones) {
            $corralonId = $vehiculo->deposito->id_corralon;
            if (!in_array($corralonId, $user->corralones_permitidos ?? [])) {
                session()->flash('error', 'No tienes permisos para ver los documentos de este vehículo.');
                return;
            }
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
        $this->resetForm();
        $this->showModal = true;
    }

    public function editar($id)
    {
        $vehiculo = Vehiculo::findOrFail($id);

        $user = auth()->user();
        if (!$user->acceso_todos_corralones) {
            $corralonId = $vehiculo->deposito->id_corralon;
            if (!in_array($corralonId, $user->corralones_permitidos ?? [])) {
                session()->flash('error', 'No tienes permisos para editar este vehículo.');
                return;
            }
        }
            
        $this->vehiculoId = $vehiculo->id;
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
        $this->validate();

        $user = auth()->user();
    
        if (!$user->acceso_todos_corralones) {
            $deposito = Deposito::find($this->id_deposito);
            if (!in_array($deposito->id_corralon, $user->corralones_permitidos ?? [])) {
                session()->flash('error', 'No tienes permisos para usar ese depósito.');
                return;
            }
        }

        $data = [
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
            'id_secretaria' => 'sec',
            'estado' => $this->estado,
            'id_deposito' => $this->id_deposito,
        ];

        if ($this->editMode) {
            $vehiculo = Vehiculo::findOrFail($this->vehiculoId);

            if (!$user->acceso_todos_corralones) {
                $corralonId = $vehiculo->deposito->id_corralon;
                if (!in_array($corralonId, $user->corralones_permitidos ?? [])) {
                    session()->flash('error', 'No tienes permisos para editar este vehículo.');
                    return;
                }
            }

            $vehiculo->update($data);
            session()->flash('message', 'Vehículo actualizado exitosamente.');
        } else {
            Vehiculo::create($data);
            session()->flash('message', 'Vehículo creado exitosamente.');
        }

        $this->cerrarModal();
    }

    public function agregarDocumento()
    {
        $this->validate([
            'nuevo_documento' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'nueva_descripcion' => 'required|string|max:255',
        ]);

        if (!$this->vehiculoId) {
            session()->flash('error', 'Error al identificar el vehículo.');
            return;
        }

        $vehiculo = Vehiculo::findOrFail($this->vehiculoId);

        $user = auth()->user();
        if (!$user->acceso_todos_corralones) {
            $corralonId = $vehiculo->deposito->id_corralon;
            if (!in_array($corralonId, $user->corralones_permitidos ?? [])) {
                session()->flash('error', 'No tienes permisos para modificar este vehículo.');
                return;
            }
        }

        $archivo = $this->nuevo_documento->store('vehiculos/documentos', 'public');

        DocumentoVehiculo::create([
            'id_vehiculo' => $this->vehiculoId,
            'descripcion' => $this->nueva_descripcion,
            'archivo' => $archivo,
        ]);

        $this->nuevo_documento = null;
        $this->nueva_descripcion = '';

        // Recargar documentos
        $this->documentos_existentes = DocumentoVehiculo::where('id_vehiculo', $this->vehiculoId)->get()->toArray();

        session()->flash('message', 'Documento agregado exitosamente.');
    }

    public function eliminarDocumento($documentoId)
    {
        $documento = DocumentoVehiculo::findOrFail($documentoId);
        
        $user = auth()->user();
        if (!$user->acceso_todos_corralones) {
            $corralonId = $documento->vehiculo->deposito->id_corralon;
            if (!in_array($corralonId, $user->corralones_permitidos ?? [])) {
                session()->flash('error', 'No tienes permisos para modificar este vehículo.');
                return;
            }
        }

        Storage::disk('public')->delete($documento->archivo);
        $documento->delete();

        // Recargar documentos
        $this->documentos_existentes = DocumentoVehiculo::where('id_vehiculo', $this->vehiculoId)->get()->toArray();

        session()->flash('message', 'Documento eliminado exitosamente.');
    }

    public function eliminar($id)
    {
        $vehiculo = Vehiculo::with('documentos')->findOrFail($id);
        
        $user = auth()->user();
        if (!$user->acceso_todos_corralones) {
            $corralonId = $vehiculo->deposito->id_corralon;
            if (!in_array($corralonId, $user->corralones_permitidos ?? [])) {
                session()->flash('error', 'No tienes permisos para eliminar este vehículo.');
                return;
            }
        }

        // Eliminar todos los documentos asociados
        foreach ($vehiculo->documentos as $documento) {
            Storage::disk('public')->delete($documento->archivo);
            $documento->delete();
        }
        
        $vehiculo->delete();
        session()->flash('message', 'Vehículo eliminado correctamente.');
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