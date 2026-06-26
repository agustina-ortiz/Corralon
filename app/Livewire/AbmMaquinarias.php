<?php

namespace App\Livewire;

use App\Models\Maquinaria;
use App\Models\CategoriaMaquinaria;
use App\Models\Corralon;
use App\Models\Deposito;
use App\Models\MovimientoMaquinaria;
use App\Models\TipoMovimiento;
use App\Models\User;
use App\Exports\MaquinariasExport;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AbmMaquinarias extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $showFilters = false;
    public $showEstadisticas = false;
    
    // Filtros
    public $filtro_corralon = '';
    public $filtro_categoria = '';
    public $filtro_estado = '';
    public $filtro_deposito = '';
    
    // Campos del formulario
    public $maquinaria_id;
    public $maquinaria;
    public $id_categoria_maquinaria;
    public $estado;
    public $id_corralon = '';
    public $id_deposito;
    public $cantidad;

    protected function rules()
    {
        $rules = [
            'maquinaria' => 'required|string|max:100',
            'id_categoria_maquinaria' => 'required|exists:categoria_maquinarias,id',
            'estado' => 'required|in:disponible,no disponible',
            'id_corralon' => 'required|exists:corralones,id',
            'id_deposito' => 'required|exists:depositos,id',
        ];

        if (!$this->editMode) {
            $rules['cantidad'] = 'required|integer|min:1';
        }

        return $rules;
    }

    protected $messages = [
        'maquinaria.required' => 'El nombre de la maquinaria es obligatorio.',
        'maquinaria.max' => 'El nombre de la maquinaria no puede exceder los 100 caracteres.',
        'id_categoria_maquinaria.required' => 'La categoría es obligatoria.',
        'id_categoria_maquinaria.exists' => 'La categoría seleccionada no existe.',
        'estado.required' => 'El estado es obligatorio.',
        'id_corralon.required' => 'El corralón es obligatorio.',
        'id_corralon.exists' => 'El corralón seleccionado no existe.',
        'id_deposito.required' => 'El depósito es obligatorio.',
        'cantidad.required' => 'La cantidad es obligatoria.',
        'cantidad.min' => 'La cantidad debe ser al menos 1.',
    ];

    public function mount()
    {
        // Verificar que el usuario esté autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $this->limpiarFiltros();
    }

    public function render()
    {
        $user = auth()->user();

        // ✅ Obtener distribución de maquinarias por depósito
        $maquinarias = $this->getMaquinariasDistribuidas();

        // Filtrar depósitos por permisos del módulo maquinarias
        $depositosPermitidos = $user->getDepositosPermitidosParaModulo('maquinarias');
        $depositos = Deposito::with('corralon')
            ->when(!$user->esAdministrador(), fn($q) => $q->whereIn('id', $depositosPermitidos))
            ->when($this->filtro_corralon, fn($q) => $q->where('id_corralon', $this->filtro_corralon))
            ->orderBy('deposito')
            ->get();

        // Corralones permitidos para el modal
        $corralonesPermitidos = $user->getCorralonesParaModulo('maquinarias');
        $corralones = Corralon::when(!$user->esAdministrador(), fn($q) => $q->whereIn('id', $corralonesPermitidos))
            ->orderBy('descripcion')
            ->get();

        // Depósitos del modal filtrados por corralón seleccionado + permisos
        $depositosModalQuery = Deposito::orderBy('deposito');
        if ($this->id_corralon) {
            $depositosModalQuery->where('id_corralon', $this->id_corralon);
            if (!$user->esAdministrador()) {
                $depositosModalQuery->whereIn('id', $depositosPermitidos);
            }
        } else {
            $depositosModalQuery->whereRaw('1 = 0');
        }
        $depositosModal = $depositosModalQuery->get();

        $categorias = CategoriaMaquinaria::orderBy('nombre')->get();

        $estadisticas = $this->showEstadisticas ? $this->calcularEstadisticas() : null;

        return view('livewire.abm-maquinarias', [
            'maquinarias' => $maquinarias,
            'estadisticas' => $estadisticas,
            'categorias' => $categorias,
            'depositos' => $depositos,
            'corralones' => $corralones,
            'depositosModal' => $depositosModal,
            // Pasar permisos a la vista
            'puedeCrear' => $user->puedeCrearMaquinarias(),
            'puedeEditar' => $user->puedeEditarMaquinarias(),
            'puedeEliminar' => $user->puedeEliminarMaquinarias(),
        ])->layout('layouts.app', [
            'header' => 'ABM Maquinarias'
        ]);
    }

    public function updatedIdCorralon()
    {
        $this->id_deposito = '';
    }

    /**
     * Query base de maquinarias con todos los filtros y permisos aplicados.
     * Reutilizado por el listado, las estadísticas y la exportación.
     */
    private function baseQuery()
    {
        return Maquinaria::porCorralonesPermitidos()
            ->when($this->search, function($query) {
                $query->where('maquinaria', 'like', '%' . $this->search . '%');
            })
            ->when($this->filtro_categoria, function($query) {
                $query->where('id_categoria_maquinaria', $this->filtro_categoria);
            })
            ->when($this->filtro_corralon, function($query) {
                $query->whereHas('deposito', function($q) {
                    $q->where('id_corralon', $this->filtro_corralon);
                });
            })
            ->when($this->filtro_deposito, function($query) {
                $query->where('id_deposito', $this->filtro_deposito);
            })
            ->when($this->filtro_estado, function($query) {
                // Filtrar por estado basado en cantidad disponible
                if ($this->filtro_estado === 'disponible') {
                    $query->where('cantidad', '>', 0);
                } else {
                    $query->where('cantidad', '<=', 0);
                }
            });
    }

    private function getMaquinariasDistribuidas()
    {
        return $this->baseQuery()
            ->with(['categoriaMaquinaria', 'deposito.corralon', 'movimientos.tipoMovimiento'])
            ->orderBy('maquinaria')
            ->paginate(15);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroCorralon()
    {
        $this->filtro_deposito = '';
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
        $this->filtro_corralon = '';
        $this->filtro_categoria = '';
        $this->filtro_estado = '';
        $this->filtro_deposito = '';
        $this->resetPage();
    }

    /**
     * Calcula los indicadores del modal de estadísticas sobre el query filtrado.
     */
    private function calcularEstadisticas(): array
    {
        $maquinarias = $this->baseQuery()->with(['categoriaMaquinaria', 'deposito.corralon'])->get();

        $disponibles = $maquinarias->filter(fn($m) => (int) $m->cantidad_disponible > 0);
        $noDisponibles = $maquinarias->filter(fn($m) => (int) $m->cantidad_disponible <= 0);

        // Distribución por categoría (cantidad de maquinarias)
        $porCategoria = $maquinarias->groupBy(fn($m) => $m->categoriaMaquinaria->nombre ?? 'Sin categoría')
            ->map(fn($g) => $g->count())
            ->sortDesc();

        // Unidades totales por depósito
        $porDeposito = $maquinarias->groupBy(fn($m) => $m->deposito->deposito ?? 'Sin depósito')
            ->map(fn($g) => $g->sum(fn($m) => (int) $m->cantidad))
            ->sortDesc();

        return [
            'total' => $maquinarias->count(),
            'unidades_total' => $maquinarias->sum(fn($m) => (int) $m->cantidad),
            'unidades_disponibles' => $maquinarias->sum(fn($m) => (int) $m->cantidad_disponible),
            'disponibles' => $disponibles->count(),
            'no_disponibles' => $noDisponibles->count(),
            'por_categoria' => $porCategoria,
            'por_deposito' => $porDeposito,
        ];
    }

    public function toggleEstadisticas()
    {
        $this->showEstadisticas = !$this->showEstadisticas;
    }

    /**
     * Descripción legible de los filtros activos (para encabezados de export).
     */
    private function descripcionFiltros(): string
    {
        $partes = [];
        if ($this->search) $partes[] = "Búsqueda: {$this->search}";
        if ($this->filtro_categoria) $partes[] = 'Categoría: ' . (CategoriaMaquinaria::find($this->filtro_categoria)->nombre ?? '');
        if ($this->filtro_corralon) $partes[] = 'Corralón: ' . (Corralon::find($this->filtro_corralon)->descripcion ?? '');
        if ($this->filtro_deposito) $partes[] = 'Depósito: ' . (Deposito::find($this->filtro_deposito)->deposito ?? '');
        if ($this->filtro_estado) $partes[] = 'Estado: ' . ($this->filtro_estado === 'disponible' ? 'Disponible' : 'No disponible');
        return implode(' · ', $partes);
    }

    public function exportarExcel()
    {
        $nombre = 'maquinarias_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new MaquinariasExport($this->baseQuery()), $nombre);
    }

    public function exportarPdf()
    {
        // Tope de seguridad: dompdf carga todo en memoria y se cae con datasets muy grandes.
        $total = $this->baseQuery()->count();
        if ($total > 2000) {
            session()->flash('error', "El listado tiene {$total} maquinarias, demasiadas para un PDF. Acotá los filtros o usá la exportación a Excel.");
            return;
        }

        // Subir límites solo durante la generación (dompdf es intensivo en memoria/tiempo).
        @ini_set('memory_limit', '1024M');
        @set_time_limit(300);

        $maquinarias = $this->baseQuery()->with(['categoriaMaquinaria', 'deposito.corralon'])->orderBy('maquinaria')->get();
        $pdf = Pdf::loadView('exports.maquinarias-pdf', [
            'maquinarias' => $maquinarias,
            'filtros' => $this->descripcionFiltros(),
        ])->setPaper('a4', 'landscape');

        $path = storage_path('app/maquinarias_' . uniqid() . '.pdf');
        $pdf->save($path);

        return response()->download($path, 'maquinarias_' . now()->format('Ymd_His') . '.pdf')
            ->deleteFileAfterSend(true);
    }

    public function crear()
    {
        // Verificar permiso de creación por rol
        if (!auth()->user()->puedeCrearMaquinarias()) {
            session()->flash('error', 'No tienes permisos para crear maquinarias.');
            return;
        }
        
        $this->resetForm();
        $this->editMode = false;

        // Si el usuario solo tiene acceso a un corralón, preseleccionarlo
        $user = auth()->user();
        $corralonesModulo = $user->getCorralonesParaModulo('maquinarias');
        if (!$user->esAdministrador() && count($corralonesModulo) === 1) {
            $this->id_corralon = $corralonesModulo[0];
        }

        $this->showModal = true;
    }

    public function editar($id)
    {
        // Verificar permiso de edición por rol
        if (!auth()->user()->puedeEditarMaquinarias()) {
            session()->flash('error', 'No tienes permisos para editar maquinarias.');
            return;
        }
        
        $maquinaria = Maquinaria::findOrFail($id);

        // Verificar que el usuario tenga acceso a esta maquinaria por depósito
        $user = auth()->user();
        $depositosPermitidos = $user->getDepositosPermitidosParaModulo('maquinarias');
        if (!$user->esAdministrador() && !in_array($maquinaria->id_deposito, $depositosPermitidos)) {
            session()->flash('error', 'No tienes permisos para editar maquinarias de este depósito.');
            return;
        }

        $this->maquinaria_id = $maquinaria->id;
        $this->maquinaria = $maquinaria->maquinaria;
        $this->id_categoria_maquinaria = $maquinaria->id_categoria_maquinaria;
        $this->estado = $maquinaria->estado;
        $this->id_corralon = $maquinaria->deposito->id_corralon;
        $this->id_deposito = $maquinaria->id_deposito;
        $this->cantidad = $maquinaria->cantidad;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function guardar()
    {
        // Verificar permisos por rol
        if (!$this->editMode && !auth()->user()->puedeCrearMaquinarias()) {
            session()->flash('error', 'No tienes permisos para crear maquinarias.');
            $this->showModal = false;
            return;
        }

        if ($this->editMode && !auth()->user()->puedeEditarMaquinarias()) {
            session()->flash('error', 'No tienes permisos para editar maquinarias.');
            $this->showModal = false;
            return;
        }
        
        $this->validate();

        $user = auth()->user();
    
        // Verificar que el depósito seleccionado esté permitido
        $depositosPermitidos = $user->getDepositosPermitidosParaModulo('maquinarias');
        if (!$user->esAdministrador() && !in_array((int)$this->id_deposito, $depositosPermitidos)) {
            session()->flash('error', 'No tienes permisos para usar ese depósito.');
            return;
        }

        if ($this->editMode) {
            $maquinaria = Maquinaria::findOrFail($this->maquinaria_id);

            // Verificar acceso a la maquinaria original por depósito
            if (!$user->esAdministrador() && !in_array($maquinaria->id_deposito, $depositosPermitidos)) {
                session()->flash('error', 'No tienes permisos para editar maquinarias de este depósito.');
                return;
            }

            $maquinaria->update([
                'maquinaria' => $this->maquinaria,
                'id_categoria_maquinaria' => $this->id_categoria_maquinaria,
                'estado' => $this->estado,
                'id_deposito' => $this->id_deposito,
            ]);
            
            session()->flash('message', 'Maquinaria actualizada correctamente.');
        } else {
            // ✅ Crear maquinaria con movimiento inicial
            try {
                DB::beginTransaction();

                // Crear la maquinaria
                $maquinaria = Maquinaria::create([
                    'maquinaria' => $this->maquinaria,
                    'id_categoria_maquinaria' => $this->id_categoria_maquinaria,
                    'estado' => $this->estado,
                    'id_deposito' => $this->id_deposito,
                    'cantidad' => $this->cantidad,
                ]);

                // ✅ Buscar tipo de movimiento existente (Inventario Inicial Maquinaria, tipo M)
                $tipoMovimiento = TipoMovimiento::where('tipo_movimiento', 'Inventario Inicial Maquinaria')->firstOrFail();

                // ✅ Crear movimiento de entrada inicial
                MovimientoMaquinaria::create([
                    'id_maquinaria' => $maquinaria->id,
                    'cantidad' => $this->cantidad,
                    'id_tipo_movimiento' => $tipoMovimiento->id,
                    'fecha' => now(),
                    'fecha_devolucion' => null,
                    'id_usuario' => Auth::id(),
                    'id_deposito_entrada' => $this->id_deposito,
                    'id_referencia' => 0,
                    'tipo_referencia' => 'deposito',
                ]);

                DB::commit();
                
                session()->flash('message', 'Maquinaria creada correctamente con inventario inicial de ' . $this->cantidad . ' unidades.');
                
            } catch (\Exception $e) {
                DB::rollBack();
                session()->flash('error', 'Error al crear la maquinaria: ' . $e->getMessage());
                \Log::error('Error al crear maquinaria: ' . $e->getMessage());
            }
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function eliminar($id)
    {
        // Verificar permiso de eliminación por rol
        if (!auth()->user()->puedeEliminarMaquinarias()) {
            session()->flash('error', 'No tienes permisos para eliminar maquinarias.');
            return;
        }
        
        $maquinaria = Maquinaria::findOrFail($id);
        
        // Verificar que el usuario tenga acceso a esta maquinaria por depósito
        $user = auth()->user();
        $depositosPermitidos = $user->getDepositosPermitidosParaModulo('maquinarias');
        if (!$user->esAdministrador() && !in_array($maquinaria->id_deposito, $depositosPermitidos)) {
            session()->flash('error', 'No tienes permisos para eliminar maquinarias de este depósito.');
            return;
        }
        
        try {
            $maquinaria->delete();
            session()->flash('message', 'Maquinaria eliminada correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'No se puede eliminar la maquinaria porque tiene movimientos asociados.');
        }
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
        $this->id_corralon = '';
        $this->id_deposito = '';
        $this->cantidad = 1;
        $this->resetErrorBag();
    }
}