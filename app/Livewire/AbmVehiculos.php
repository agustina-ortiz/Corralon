<?php

namespace App\Livewire;

use App\Livewire\Traits\ConfirmaDescarte;
use App\Livewire\Traits\EnviaToasts;
use App\Livewire\Traits\OrdenaPorColumna;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Vehiculo;
use App\Models\Deposito;
use App\Models\DocumentoVehiculo;
use App\Models\Secretaria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;

class AbmVehiculos extends Component
{
    use WithPagination;
    use OrdenaPorColumna;
    use WithFileUploads;
    use EnviaToasts;
    use ConfirmaDescarte;

    public $search = '';
    public $showModal = false;

    // Registro a eliminar (abre el modal de confirmación)
    public $eliminarId = null;
    public $editMode = false;
    public $showFilters = false;
    public $showModalDocumentos = false;

    // Documento a eliminar (abre el modal de confirmación)
    public $eliminarDocumentoId = null;

    // Filtros
    public $filtro_marca_modelo = '';
    public $filtro_estado = '';
    public $filtro_secretaria = '';

    // Alerta del dashboard (clave de Vehiculo::ALERTAS), llega como /vehiculos?alerta=...
    #[Url(as: 'alerta', except: '')]
    public $filtro_alerta = '';

    // Campos del formulario
    public $nro_patrimonio;
    public $vehiculoId;
    public $vehiculo;
    public $marca_modelo;
    public $nro_motor;
    public $nro_chasis;
    public $anio;
    public $patente;
    public $tipo_combustible;
    public $vencimiento_oblea;
    public $nro_poliza;
    public $vencimiento_poliza;
    public $vencimiento_vtv;
    public $origen;
    public $jurisdiccion_procedencia;
    public $nro_telepase;
    public $estado;
    public $id_deposito;
    public $id_secretaria;

    // Documentos
    public $nuevo_documento;
    public $nueva_descripcion;
    public $documentos_existentes = [];
    public $vehiculoSeleccionado;

    protected function rules()
    {
        return [
            'nro_patrimonio' => [
                'required',
                'string',
                'max:50',
                $this->editMode
                    ? 'unique:vehiculos,nro_patrimonio,' . $this->vehiculoId
                    : 'unique:vehiculos,nro_patrimonio'
            ],
            'vehiculo' => 'required|string|max:255',
            'marca_modelo' => 'required|string|max:255',
            'nro_motor' => 'required|string|max:255',
            'nro_chasis' => 'required|string|max:255',
            'anio' => 'nullable|string|max:4',
            'patente' => 'required|string|max:20',
            'tipo_combustible' => 'required|in:nafta,diesel,gas',
            'vencimiento_oblea' => $this->tipo_combustible === 'gas' ? 'required|date' : 'nullable|date',
            'nro_poliza' => 'required|string|max:255',
            'vencimiento_poliza' => 'nullable|date',
            'vencimiento_vtv' => 'nullable|date',
            'origen' => 'nullable|string|max:255',
            'jurisdiccion_procedencia' => 'nullable|string|max:255',
            'nro_telepase' => 'nullable|string|max:255',
            'estado' => 'required|in:EN USO,BAJA,MANTENIMIENTO',
            'id_deposito' => 'nullable|exists:depositos,id',
            'id_secretaria' => 'nullable|exists:secretarias,id',
        ];
    }

    protected $messages = [
        'nro_patrimonio.required' => 'El número de patrimonio es obligatorio',
        'nro_patrimonio.unique' => 'Este número de patrimonio ya está en uso',
        'vehiculo.required' => 'El nombre del vehículo es obligatorio',
        'marca_modelo.required' => 'La marca/modelo es obligatoria',
        'patente.required' => 'La patente es obligatoria',
        'tipo_combustible.required' => 'El tipo de combustible es obligatorio',
        'nro_motor.required' => 'El número de motor es obligatorio',
        'nro_chasis.required' => 'El número de chasis es obligatorio',
        'estado.required' => 'El estado es obligatorio',
        'nro_póliza.required' => 'El número de poliza es obligatorio',
        'id_deposito.required' => 'El depósito es obligatorio',
        'id_deposito.exists' => 'El depósito seleccionado no existe',
        'vencimiento_oblea.required' => 'El vencimiento de oblea es obligatorio para vehículos a gas',
        'vencimiento_oblea.date' => 'Ingrese una fecha válida',
        'vencimiento_póliza.date' => 'Ingrese una fecha válida',
        'vencimiento_vtv.date' => 'Ingrese una fecha válida',
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
    public function updatingFiltroMarcaModelo() { $this->resetPage(); }
    public function updatingFiltroEstado() { $this->resetPage(); }
    public function updatingFiltroSecretaria() { $this->resetPage(); }

    public function quitarAlerta()
    {
        $this->filtro_alerta = '';
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->filtro_marca_modelo = '';
        $this->filtro_estado = '';
        $this->filtro_secretaria = '';
        $this->resetPage();
    }

    protected function columnasOrdenables(): array
    {
        return [
            'patrimonio' => 'nro_patrimonio',
            'vehiculo' => 'vehiculo',
            'patente' => 'patente',
            'estado' => 'estado',
            'secretaria' => fn (Builder $q, string $dir) => $q->orderBy(
                Secretaria::select('secretaria')->whereColumn('secretarias.id', 'vehiculos.id_secretaria'), $dir
            ),
            'vtv' => 'vencimiento_vtv',
            'documentos' => 'documentos_count',
        ];
    }

    protected function ordenInicial(): array
    {
        return ['vehiculo', 'asc'];
    }

    public function render()
    {
        $user = auth()->user();

        $vehiculos = $this->aplicarOrden(Vehiculo::with(['deposito', 'secretaria'])
            ->withCount('documentos')
            ->porCorralonesPermitidos()
            ->where(function($query) {
                $query->where('nro_patrimonio', 'like', '%' . $this->search . '%')
                      ->orWhere('vehiculo', 'like', '%' . $this->search . '%')
                      ->orWhere('marca_modelo', 'like', '%' . $this->search . '%')
                      ->orWhere('patente', 'like', '%' . $this->search . '%');
            })
            ->when($this->filtro_marca_modelo, fn($q) => $q->where('marca_modelo', $this->filtro_marca_modelo))
            ->when($this->filtro_estado, fn($q) => $q->where('estado', $this->filtro_estado))
            ->when($this->filtro_secretaria, fn($q) => $q->where('id_secretaria', $this->filtro_secretaria))
            ->when(isset(Vehiculo::ALERTAS[$this->filtro_alerta]), fn($q) => $q->conAlerta($this->filtro_alerta))
        )->paginate(10);

        $depositosPermitidos = $user->getDepositosPermitidosParaModulo('vehiculos');
        $depositos = Deposito::with('corralon')
            ->when(!$user->esAdministrador(), fn($q) => $q->whereIn('id', $depositosPermitidos))
            ->orderBy('deposito')
            ->get();

        $marcasModelos = Vehiculo::select('marca_modelo')->distinct()->whereNotNull('marca_modelo')->orderBy('marca_modelo')->pluck('marca_modelo');

        $secretarias = Secretaria::orderBy('secretaria')->get();

        return view('livewire.abm-vehiculos', [
            'vehiculos' => $vehiculos,
            'depositos' => $depositos,
            'marcasModelos' => $marcasModelos,
            'secretarias' => $secretarias,
            'alertaActiva' => Vehiculo::ALERTAS[$this->filtro_alerta] ?? null,
            'puedeCrear' => $user->puedeCrearVehiculos(),
            'puedeEditar' => $user->puedeEditarVehiculos(),
            'puedeEliminar' => $user->puedeEliminarVehiculos(),
        ])->layout('layouts.app', [
            'header' => 'ABM Vehiculos'
        ]);
    }

    private function verificarAccesoVehiculo($vehiculo): bool
    {
        // Mismo criterio que el listado (global, depósito o secretaría vía pivote)
        return Vehiculo::whereKey($vehiculo->id)->porCorralonesPermitidos()->exists();
    }

    public function abrirModalDocumentos($vehiculoId)
    {
        $vehiculo = Vehiculo::with('documentos')->findOrFail($vehiculoId);

        if (!$this->verificarAccesoVehiculo($vehiculo)) {
            $this->toastError('No tienes permisos para ver los documentos de este vehiculo.');
            return;
        }

        $this->vehiculoId = $vehiculoId;
        $this->vehiculoSeleccionado = $vehiculo;
        $this->documentos_existentes = $vehiculo->documentos->toArray();
        $this->showModalDocumentos = true;
        $this->marcarFormularioLimpio();
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
            $this->toastError('No tienes permisos para crear vehiculos.');
            return;
        }

        $this->resetForm();
        $this->showModal = true;
        $this->marcarFormularioLimpio();
    }

    public function editar($id)
    {
        if (!auth()->user()->puedeEditarVehiculos()) {
            $this->toastError('No tienes permisos para editar vehiculos.');
            return;
        }

        $vehiculo = Vehiculo::findOrFail($id);

        if (!$this->verificarAccesoVehiculo($vehiculo)) {
            $this->toastError('No tienes permisos para editar este vehiculo.');
            return;
        }

        $this->vehiculoId = $vehiculo->id;
        $this->nro_patrimonio = $vehiculo->nro_patrimonio;
        $this->vehiculo = $vehiculo->vehiculo;
        $this->marca_modelo = $vehiculo->marca_modelo;
        $this->nro_motor = $vehiculo->nro_motor;
        $this->nro_chasis = $vehiculo->nro_chasis;
        $this->anio = $vehiculo->anio;
        $this->patente = $vehiculo->patente;
        $this->tipo_combustible = $vehiculo->tipo_combustible;
        $this->vencimiento_oblea = $vehiculo->vencimiento_oblea;
        $this->nro_poliza = $vehiculo->nro_poliza;
        $this->vencimiento_poliza = $vehiculo->vencimiento_poliza;
        $this->vencimiento_vtv = $vehiculo->vencimiento_vtv;
        $this->origen = $vehiculo->origen;
        $this->jurisdiccion_procedencia = $vehiculo->jurisdiccion_procedencia;
        $this->nro_telepase = $vehiculo->nro_telepase;
        $this->estado = $vehiculo->estado;
        $this->id_deposito = $vehiculo->id_deposito;
        $this->id_secretaria = $vehiculo->id_secretaria;

        $this->editMode = true;
        $this->showModal = true;
        $this->marcarFormularioLimpio();
    }

    public function guardar()
    {
        if (!$this->editMode && !auth()->user()->puedeCrearVehiculos()) {
            $this->toastError('No tienes permisos para crear vehiculos.');
            $this->showModal = false;
            return;
        }

        if ($this->editMode && !auth()->user()->puedeEditarVehiculos()) {
            $this->toastError('No tienes permisos para editar vehiculos.');
            $this->showModal = false;
            return;
        }

        $this->validate();

        $user = auth()->user();
        $depositosPermitidos = $user->getDepositosPermitidosParaModulo('vehiculos');

        // El depósito es opcional: solo se valida si se eligió uno
        if ($this->id_deposito && !$user->esAdministrador() && !in_array((int)$this->id_deposito, $depositosPermitidos)) {
            $this->toastError('No tienes permisos para usar ese deposito.');
            return;
        }

        $data = [
            'nro_patrimonio' => $this->nro_patrimonio,
            'vehiculo' => $this->vehiculo,
            'marca_modelo' => $this->marca_modelo,
            'nro_motor' => $this->nro_motor,
            'nro_chasis' => $this->nro_chasis,
            'anio' => $this->anio,
            'patente' => $this->patente,
            'tipo_combustible' => $this->tipo_combustible,
            'vencimiento_oblea' => $this->tipo_combustible === 'gas' ? $this->vencimiento_oblea : null,
            'nro_poliza' => $this->nro_poliza,
            'vencimiento_poliza' => $this->vencimiento_poliza,
            'vencimiento_vtv' => $this->vencimiento_vtv,
            'origen' => $this->origen,
            'jurisdiccion_procedencia' => $this->jurisdiccion_procedencia,
            'nro_telepase' => $this->nro_telepase,
            'id_secretaria' => $this->id_secretaria ?: null,
            'estado' => $this->estado,
            'id_deposito' => $this->id_deposito,
        ];

        if ($this->editMode) {
            $vehiculo = Vehiculo::findOrFail($this->vehiculoId);

            if (!$this->verificarAccesoVehiculo($vehiculo)) {
                $this->toastError('No tienes permisos para editar este vehiculo.');
                return;
            }

            $vehiculo->update($data);
            $this->toastExito('Vehiculo actualizado exitosamente.');
        } else {
            Vehiculo::create($data);
            $this->toastExito('Vehiculo creado exitosamente.');
        }

        $this->cerrarModal();
    }

    public function agregarDocumento()
    {
        if (!auth()->user()->puedeEditarVehiculos()) {
            $this->toastError('No tienes permisos para agregar documentos.');
            return;
        }

        $this->validate([
            'nuevo_documento' => ['required', 'file', 'max:10240', new \App\Rules\ArchivoSeguro()],
            'nueva_descripcion' => 'required|string|max:255',
        ]);

        if (!$this->vehiculoId) {
            $this->toastError('Error al identificar el vehiculo.');
            return;
        }

        $vehiculo = Vehiculo::findOrFail($this->vehiculoId);

        if (!$this->verificarAccesoVehiculo($vehiculo)) {
            $this->toastError('No tienes permisos para modificar este vehiculo.');
            return;
        }

        $ruta = DocumentoVehiculo::rutaParaArchivo($vehiculo, $this->nueva_descripcion, $this->nuevo_documento->extension());
        $archivo = $this->nuevo_documento->storeAs(dirname($ruta), basename($ruta), DocumentoVehiculo::DISCO);

        if (!$archivo) {
            $this->toastError('No se pudo guardar el archivo del documento.');
            return;
        }

        DocumentoVehiculo::create([
            'id_vehiculo' => $this->vehiculoId,
            'descripcion' => $this->nueva_descripcion,
            'archivo' => $archivo,
        ]);

        $this->nuevo_documento = null;
        $this->nueva_descripcion = '';
        $this->documentos_existentes = DocumentoVehiculo::where('id_vehiculo', $this->vehiculoId)->get()->toArray();
        $this->marcarFormularioLimpio();

        $this->toastExito('Documento agregado exitosamente.');
    }

    public function quitarNuevoDocumento()
    {
        $this->nuevo_documento = null;
    }

    public function confirmarEliminarDocumento($documentoId)
    {
        $this->eliminarDocumentoId = $documentoId;
    }

    public function cancelarEliminarDocumento()
    {
        $this->eliminarDocumentoId = null;
    }

    public function eliminarDocumento()
    {
        $documentoId = $this->eliminarDocumentoId;
        $this->eliminarDocumentoId = null;

        if (!auth()->user()->puedeEditarVehiculos()) {
            $this->toastError('No tienes permisos para eliminar documentos.');
            return;
        }

        $documento = DocumentoVehiculo::findOrFail($documentoId);
        $vehiculo = Vehiculo::findOrFail($documento->id_vehiculo);

        if (!$this->verificarAccesoVehiculo($vehiculo)) {
            $this->toastError('No tienes permisos para modificar este vehiculo.');
            return;
        }

        Storage::disk(DocumentoVehiculo::DISCO)->delete($documento->archivo);
        $documento->delete();

        $this->documentos_existentes = DocumentoVehiculo::where('id_vehiculo', $this->vehiculoId)->get()->toArray();

        $this->toastExito('Documento eliminado exitosamente.');
    }

    public function confirmarEliminar($id)
    {
        $this->eliminarId = $id;
    }

    public function cancelarEliminar()
    {
        $this->eliminarId = null;
    }

    public function eliminar()
    {
        $id = $this->eliminarId;
        $this->eliminarId = null;

        if (!auth()->user()->puedeEliminarVehiculos()) {
            $this->toastError('No tienes permisos para eliminar vehiculos.');
            return;
        }

        $vehiculo = Vehiculo::with('documentos')->findOrFail($id);

        if (!$this->verificarAccesoVehiculo($vehiculo)) {
            $this->toastError('No tienes permisos para eliminar este vehiculo.');
            return;
        }

        try {
            // Filas en transacción: si el vehículo no se puede borrar (FK), los documentos quedan intactos
            DB::transaction(function () use ($vehiculo) {
                DocumentoVehiculo::where('id_vehiculo', $vehiculo->id)->delete();
                $vehiculo->delete();
            });

            // Los archivos se borran recién cuando el vehículo ya no existe
            foreach ($vehiculo->documentos as $documento) {
                Storage::disk(DocumentoVehiculo::DISCO)->delete($documento->archivo);
            }
            Storage::disk(DocumentoVehiculo::DISCO)->deleteDirectory(DocumentoVehiculo::DIRECTORIO . '/' . $vehiculo->id);

            $this->toastExito('Vehiculo eliminado correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->toastError('No se puede eliminar el vehículo porque tiene choferes, movimientos o asignaciones asociadas.');
        } catch (\Exception $e) {
            $this->toastError('No se pudo eliminar el vehículo.');
        }
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function camposFormulario(): array
    {
        // El modal de Documentos tiene su propio formulario (documento a agregar)
        if ($this->showModalDocumentos) {
            return ['nuevo_documento', 'nueva_descripcion'];
        }

        return ['nro_patrimonio', 'vehiculo', 'marca_modelo', 'nro_motor', 'nro_chasis', 'anio', 'patente', 'tipo_combustible', 'vencimiento_oblea', 'nro_poliza', 'vencimiento_poliza', 'vencimiento_vtv', 'origen', 'jurisdiccion_procedencia', 'nro_telepase', 'estado', 'id_deposito', 'id_secretaria'];
    }

    public function cerrarFormulario(): void
    {
        if ($this->showModalDocumentos) {
            $this->cerrarModalDocumentos();

            return;
        }

        $this->cerrarModal();
    }

    private function resetForm()
    {
        $this->reset([
            'nro_patrimonio',
            'vehiculoId',
            'vehiculo',
            'marca_modelo',
            'nro_motor',
            'nro_chasis',
            'anio',
            'patente',
            'tipo_combustible',
            'vencimiento_oblea',
            'nro_poliza',
            'vencimiento_poliza',
            'vencimiento_vtv',
            'origen',
            'jurisdiccion_procedencia',
            'nro_telepase',
            'estado',
            'id_deposito',
            'id_secretaria'
        ]);
        $this->editMode = false;
        $this->resetErrorBag();
    }
}
