<?php

namespace App\Livewire;

use App\Models\Insumo;
use App\Models\CategoriaInsumo;
use App\Models\Corralon;
use App\Models\Deposito;
use App\Models\TipoMovimiento;
use App\Models\MovimientoInsumo;
use App\Exports\InsumosExport;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AbmInsumos extends Component
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
    public $filtro_unidad = '';
    public $filtro_deposito = '';
    public $filtro_stock_bajo = false;
    
    // Campos del formulario
    public $insumo_id;
    public $insumo;
    public $id_categoria;
    public $unidad;
    public $stock_inicial;
    public $stock_minimo;
    public $id_deposito;

    protected function rules()
    {
        $rules = [
            'insumo' => 'required|string|max:100',
            'id_categoria' => 'required|exists:categorias_insumos,id',
            'unidad' => 'required|in:UNIDAD,LITROS,TAMBOR,METRO,ROLLO X 100 MT,PAQUETE,BOLSA,BALDE',
            'stock_minimo' => 'required|numeric|min:0',
            'id_deposito' => 'required|exists:depositos,id',
        ];

        // ✅ Solo validar stock_inicial en modo creación
        if (!$this->editMode) {
            $rules['stock_inicial'] = 'nullable|numeric|min:0';
        }

        return $rules;
    }

    protected $messages = [
        'insumo.required' => 'El nombre del insumo es obligatorio',
        'insumo.max' => 'El nombre del insumo no puede exceder los 100 caracteres',
        'id_categoria.required' => 'La categoría es obligatoria',
        'id_categoria.exists' => 'La categoría seleccionada no existe',
        'unidad.required' => 'La unidad es obligatoria',
        'unidad.in' => 'La unidad seleccionada no es válida',
        'stock_inicial.numeric' => 'El stock inicial debe ser un número',
        'stock_inicial.min' => 'El stock inicial no puede ser negativo',
        'stock_minimo.required' => 'El stock mínimo es obligatorio',
        'stock_minimo.numeric' => 'El stock mínimo debe ser un número',
        'stock_minimo.min' => 'El stock mínimo no puede ser negativo',
        'id_deposito.required' => 'El depósito es obligatorio',
        'id_deposito.exists' => 'El depósito seleccionado no existe',
    ];

    public function mount()
    {
        // Verificar que el usuario esté autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $this->limpiarFiltros();
    }

    /**
     * Query base de insumos con todos los filtros y permisos aplicados.
     * Reutilizado por el listado, las estadísticas y la exportación para
     * garantizar que los tres reflejen exactamente lo mismo.
     */
    private function baseQuery()
    {
        return Insumo::porCorralonesPermitidos()
            ->when($this->search, function($query) {
                $query->where('insumo', 'like', '%' . $this->search . '%');
            })
            ->when($this->filtro_categoria, function($query) {
                $query->where('id_categoria', $this->filtro_categoria);
            })
            ->when($this->filtro_unidad, function($query) {
                $query->where('unidad', $this->filtro_unidad);
            })
            ->when($this->filtro_corralon, function($query) {
                $query->whereHas('deposito', function($q) {
                    $q->where('id_corralon', $this->filtro_corralon);
                });
            })
            ->when($this->filtro_deposito, function($query) {
                $query->where('id_deposito', $this->filtro_deposito);
            })
            ->when($this->filtro_stock_bajo, function($query) {
                $query->whereColumn('stock_actual', '<', 'stock_minimo');
            });
    }

    public function render()
    {
        $user = auth()->user();

        $insumos = $this->baseQuery()
            ->with(['categoriaInsumo', 'deposito.corralon'])
            ->orderBy('insumo')
            ->paginate(10);

        $estadisticas = $this->showEstadisticas ? $this->calcularEstadisticas() : null;

        $categorias = CategoriaInsumo::orderBy('nombre')->get();
        
        // Filtrar depósitos por permisos del módulo insumos
        $depositosPermitidos = $user->getDepositosPermitidosParaModulo('insumos');
        $depositos = Deposito::with('corralon')
            ->when(!$user->esAdministrador(), fn($q) => $q->whereIn('id', $depositosPermitidos))
            ->when($this->filtro_corralon, fn($q) => $q->where('id_corralon', $this->filtro_corralon))
            ->orderBy('deposito')
            ->get();

        // Corralones permitidos para el filtro
        $corralonesPermitidos = $user->getCorralonesParaModulo('insumos');
        $corralones = Corralon::when(!$user->esAdministrador(), fn($q) => $q->whereIn('id', $corralonesPermitidos))
            ->orderBy('descripcion')
            ->get();
        
        // Filtrar unidades de insumos visibles
        $unidades = Insumo::select('unidad')
            ->porCorralonesPermitidos()
            ->distinct()
            ->whereNotNull('unidad')
            ->orderBy('unidad')
            ->pluck('unidad');

        return view('livewire.abm-insumos', [
            'insumos' => $insumos,
            'estadisticas' => $estadisticas,
            'categorias' => $categorias,
            'corralones' => $corralones,
            'depositos' => $depositos,
            'unidades' => $unidades,
            // Pasar permisos a la vista
            'puedeCrear' => $user->puedeCrearInsumos(),
            'puedeEditar' => $user->puedeEditarInsumos(),
            'puedeEliminar' => $user->puedeEliminarInsumos(),
        ])->layout('layouts.app', [
            'header' => 'ABM Insumos'
        ]);
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

    public function updatingFiltroUnidad()
    {
        $this->resetPage();
    }

    public function updatingFiltroDeposito()
    {
        $this->resetPage();
    }

    public function updatingFiltroStockBajo()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->filtro_corralon = '';
        $this->filtro_categoria = '';
        $this->filtro_unidad = '';
        $this->filtro_deposito = '';
        $this->filtro_stock_bajo = false;
        $this->resetPage();
    }

    /**
     * Calcula los indicadores del modal de estadísticas sobre el query filtrado.
     */
    private function calcularEstadisticas(): array
    {
        $insumos = $this->baseQuery()->with(['categoriaInsumo', 'deposito.corralon'])->get();

        $bajoMinimo = $insumos->filter(fn($i) => (float) $i->stock_actual > 0 && (float) $i->stock_actual < (float) $i->stock_minimo);
        $sinStock = $insumos->filter(fn($i) => (float) $i->stock_actual <= 0);

        // Distribución por categoría: cantidad de insumos por categoría
        $porCategoria = $insumos->groupBy(fn($i) => $i->categoriaInsumo->nombre ?? 'Sin categoría')
            ->map(fn($g) => $g->count())
            ->sortDesc();

        // Stock por depósito (suma de stock_actual)
        $porDeposito = $insumos->groupBy(fn($i) => $i->deposito->deposito ?? 'Sin depósito')
            ->map(fn($g) => $g->sum(fn($i) => (float) $i->stock_actual))
            ->sortDesc();

        return [
            'total' => $insumos->count(),
            'stock_total' => $insumos->sum(fn($i) => (float) $i->stock_actual),
            'bajo_minimo' => $bajoMinimo->count(),
            'sin_stock' => $sinStock->count(),
            'ok' => $insumos->count() - $bajoMinimo->count() - $sinStock->count(),
            'por_categoria' => $porCategoria,
            'por_deposito' => $porDeposito,
            'top_bajo_minimo' => $bajoMinimo->sortBy(fn($i) => (float) $i->stock_actual)->take(10),
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
        if ($this->filtro_categoria) $partes[] = 'Categoría: ' . (CategoriaInsumo::find($this->filtro_categoria)->nombre ?? '');
        if ($this->filtro_corralon) $partes[] = 'Corralón: ' . (Corralon::find($this->filtro_corralon)->descripcion ?? '');
        if ($this->filtro_deposito) $partes[] = 'Depósito: ' . (Deposito::find($this->filtro_deposito)->deposito ?? '');
        if ($this->filtro_unidad) $partes[] = "Unidad: {$this->filtro_unidad}";
        if ($this->filtro_stock_bajo) $partes[] = 'Solo stock bajo mínimo';
        return implode(' · ', $partes);
    }

    public function exportarExcel()
    {
        $nombre = 'insumos_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new InsumosExport($this->baseQuery()), $nombre);
    }

    public function exportarPdf()
    {
        // Tope de seguridad: dompdf carga todo en memoria y se cae con datasets muy grandes.
        $total = $this->baseQuery()->count();
        if ($total > 2000) {
            session()->flash('error', "El listado tiene {$total} insumos, demasiados para un PDF. Acotá los filtros o usá la exportación a Excel.");
            return;
        }

        // Subir límites solo durante la generación (dompdf es intensivo en memoria/tiempo).
        @ini_set('memory_limit', '1024M');
        @set_time_limit(300);

        $insumos = $this->baseQuery()->with(['categoriaInsumo', 'deposito.corralon'])->orderBy('insumo')->get();
        $pdf = Pdf::loadView('exports.insumos-pdf', [
            'insumos' => $insumos,
            'filtros' => $this->descripcionFiltros(),
        ])->setPaper('a4', 'landscape');

        $path = storage_path('app/insumos_' . uniqid() . '.pdf');
        $pdf->save($path);

        return response()->download($path, 'insumos_' . now()->format('Ymd_His') . '.pdf')
            ->deleteFileAfterSend(true);
    }

    public function crear()
    {
        // Verificar permiso de creación por rol
        if (!auth()->user()->puedeCrearInsumos()) {
            session()->flash('error', 'No tienes permisos para crear insumos.');
            return;
        }
        
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editar($id)
    {
        // Verificar permiso de edición por rol
        if (!auth()->user()->puedeEditarInsumos()) {
            session()->flash('error', 'No tienes permisos para editar insumos.');
            return;
        }
        
        $insumo = Insumo::findOrFail($id);
        
        // Verificar que el usuario tenga acceso a este insumo por depósito/corralón
        $user = auth()->user();
        $depositosPermitidos = $user->getDepositosPermitidosParaModulo('insumos');
        if (!$user->esAdministrador() && !in_array($insumo->id_deposito, $depositosPermitidos)) {
            session()->flash('error', 'No tienes permisos para editar insumos de este depósito.');
            return;
        }

        $this->insumo_id = $insumo->id;
        $this->insumo = $insumo->insumo;
        $this->id_categoria = $insumo->id_categoria;
        $this->unidad = $insumo->unidad;
        $this->stock_minimo = $insumo->stock_minimo;
        $this->id_deposito = $insumo->id_deposito;
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function guardar()
    {
        // Verificar permisos por rol
        if (!$this->editMode && !auth()->user()->puedeCrearInsumos()) {
            session()->flash('error', 'No tienes permisos para crear insumos.');
            $this->showModal = false;
            return;
        }

        if ($this->editMode && !auth()->user()->puedeEditarInsumos()) {
            session()->flash('error', 'No tienes permisos para editar insumos.');
            $this->showModal = false;
            return;
        }
        
        $this->validate();
        
        $user = auth()->user();
        $depositosPermitidos = $user->getDepositosPermitidosParaModulo('insumos');

        // Verificar que el depósito seleccionado esté permitido
        if (!$user->esAdministrador() && !in_array((int)$this->id_deposito, $depositosPermitidos)) {
            session()->flash('error', 'No tienes permisos para usar ese depósito.');
            return;
        }

        if ($this->editMode) {
            $insumo = Insumo::findOrFail($this->insumo_id);

            // Verificar acceso al insumo original por depósito
            if (!$user->esAdministrador() && !in_array($insumo->id_deposito, $depositosPermitidos)) {
                session()->flash('error', 'No tienes permisos para editar insumos de este depósito.');
                return;
            }
            
            $insumo->update([
                'insumo' => $this->insumo,
                'id_categoria' => $this->id_categoria,
                'unidad' => $this->unidad,
                'stock_minimo' => $this->stock_minimo,
                'id_deposito' => $this->id_deposito,
            ]);
            
            // Recalcular el stock después de actualizar
            $insumo->sincronizarStock();
            
            session()->flash('message', 'Insumo actualizado correctamente.');
        } else {
            // CREAR INSUMO NUEVO
            $insumo = Insumo::create([
                'insumo' => $this->insumo,
                'id_categoria' => $this->id_categoria,
                'unidad' => $this->unidad,
                'stock_actual' => $this->stock_inicial,
                'stock_minimo' => $this->stock_minimo,
                'id_deposito' => $this->id_deposito,
            ]);
            
            // Si hay stock inicial, crear movimiento de inventario inicial
            if ($this->stock_inicial && $this->stock_inicial > 0) {
                $tipoInventarioInicial = TipoMovimiento::where('tipo_movimiento', 'Inventario Inicial')->first();
                
                MovimientoInsumo::create([
                    'id_insumo' => $insumo->id,
                    'id_tipo_movimiento' => $tipoInventarioInicial->id,
                    'cantidad' => $this->stock_inicial,
                    'fecha' => now(),
                    'id_usuario' => Auth::id(),
                    'id_deposito_entrada' => $this->id_deposito,
                    'id_referencia' => 0,
                    'tipo_referencia' => 'inventario',
                ]);
                
                // Sincronizar el stock
                $insumo->sincronizarStock();
                
                session()->flash('message', "Insumo creado correctamente con stock inicial de {$this->stock_inicial} {$this->unidad}.");
            } else {
                session()->flash('message', 'Insumo creado correctamente. El stock inicial es 0. Registra movimientos de entrada para aumentar el stock.');
            }
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function eliminar($id)
    {
        // Verificar permiso de eliminación por rol
        if (!auth()->user()->puedeEliminarInsumos()) {
            session()->flash('error', 'No tienes permisos para eliminar insumos.');
            return;
        }
        
        $insumo = Insumo::findOrFail($id);
        
        // Verificar que el usuario tenga acceso a este insumo por depósito
        $user = auth()->user();
        $depositosPermitidos = $user->getDepositosPermitidosParaModulo('insumos');
        if (!$user->esAdministrador() && !in_array($insumo->id_deposito, $depositosPermitidos)) {
            session()->flash('error', 'No tienes permisos para eliminar insumos de este depósito.');
            return;
        }
        
        try {
            $insumo->delete();
            session()->flash('message', 'Insumo eliminado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'No se puede eliminar el insumo porque tiene movimientos asociados.');
        }
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->insumo_id = null;
        $this->insumo = '';
        $this->id_categoria = '';
        $this->unidad = '';
        $this->stock_inicial = ''; // ✅ RESETEAR
        $this->stock_minimo = '';
        $this->id_deposito = '';
        $this->resetErrorBag();
    }
}