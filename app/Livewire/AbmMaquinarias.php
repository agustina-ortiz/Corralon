<?php

namespace App\Livewire;

use App\Models\Maquinaria;
use App\Models\CategoriaMaquinaria;
use App\Models\Deposito;
use App\Models\MovimientoMaquinaria; 
use App\Models\TipoMovimiento;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Auth; 

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
    public $cantidad;

    protected function rules()
    {
        $rules = [
            'maquinaria' => 'required|string|max:100',
            'id_categoria_maquinaria' => 'required|exists:categoria_maquinarias,id',
            'estado' => 'required|in:disponible,no disponible',
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

        // Filtrar depósitos
        $depositosQuery = Deposito::with('corralon')->orderBy('deposito');
        if (!$user->acceso_todos_corralones) {
            $depositosQuery->whereIn('id_corralon', $user->corralones_permitidos ?? []);
        }
        
        $depositos = $depositosQuery->get();
        $categorias = CategoriaMaquinaria::orderBy('nombre')->get();

        return view('livewire.abm-maquinarias', [
            'maquinarias' => $maquinarias,
            'categorias' => $categorias,
            'depositos' => $depositos,
            // Pasar permisos a la vista
            'puedeCrear' => $user->puedeCrearMaquinarias(),
            'puedeEditar' => $user->puedeEditarMaquinarias(),
            'puedeEliminar' => $user->puedeEliminarMaquinarias(),
        ])->layout('layouts.app', [
            'header' => 'ABM Maquinarias'
        ]);
    }

    private function getMaquinariasDistribuidas()
    {
        $user = auth()->user();

        // Obtener todas las maquinarias con sus movimientos
        $maquinariasQuery = Maquinaria::with(['categoriaMaquinaria', 'deposito', 'movimientos.tipoMovimiento'])
            ->porCorralonesPermitidos()
            ->when($this->search, function($query) {
                $query->where('maquinaria', 'like', '%' . $this->search . '%');
            })
            ->when($this->filtro_categoria, function($query) {
                $query->where('id_categoria_maquinaria', $this->filtro_categoria);
            })
            ->orderBy('maquinaria')
            ->get();

        // ✅ Expandir cada maquinaria en múltiples filas según su distribución
        $maquinariasExpandidas = collect();

        foreach ($maquinariasQuery as $maquinaria) {
            // Obtener depósitos accesibles
            $depositosAccesibles = $user->acceso_todos_corralones 
                ? Deposito::pluck('id')->toArray()
                : Deposito::whereIn('id_corralon', $user->corralones_permitidos ?? [])->pluck('id')->toArray();

            // Calcular cantidad por depósito
            foreach ($depositosAccesibles as $depositoId) {
                $cantidadEnDeposito = $maquinaria->getCantidadEnDeposito($depositoId);

                // Solo mostrar si hay cantidad en este depósito O si es el depósito original
                // (para mostrar depósitos en 0 también)
                $mostrarFila = $cantidadEnDeposito > 0 || $maquinaria->id_deposito == $depositoId;
                
                if ($mostrarFila) {
                    // ✅ Estado calculado POR DEPÓSITO
                    $estadoFila = $cantidadEnDeposito > 0 ? 'disponible' : 'no disponible';

                    // Aplicar filtros
                    if ($this->filtro_deposito && $depositoId != $this->filtro_deposito) {
                        continue;
                    }

                    if ($this->filtro_estado && $estadoFila != $this->filtro_estado) {
                        continue;
                    }

                    // Crear objeto virtual para esta fila
                    $fila = (object)[
                        'id' => $maquinaria->id,
                        'maquinaria' => $maquinaria->maquinaria,
                        'id_categoria_maquinaria' => $maquinaria->id_categoria_maquinaria,
                        'categoriaMaquinaria' => $maquinaria->categoriaMaquinaria,
                        'estado' => $maquinaria->estado,
                        'cantidad' => $maquinaria->cantidad,
                        'cantidad_disponible' => $cantidadEnDeposito,
                        'id_deposito' => $depositoId,
                        'deposito' => Deposito::with('corralon')->find($depositoId),
                        'row_id' => $maquinaria->id . '_' . $depositoId,
                    ];

                    $maquinariasExpandidas->push($fila);
                }
            }
        }

        // ✅ Paginar manualmente
        $perPage = 10;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;

        $items = $maquinariasExpandidas->slice($offset, $perPage)->values();
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $maquinariasExpandidas->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
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
        // Verificar permiso de creación por rol
        if (!auth()->user()->puedeCrearMaquinarias()) {
            session()->flash('error', 'No tienes permisos para crear maquinarias.');
            return;
        }
        
        $this->resetForm();
        $this->editMode = false;
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

        // Verificar que el usuario tenga acceso a esta maquinaria por corralón
        $user = auth()->user();
        if (!$user->acceso_todos_corralones) {
            $corralonId = $maquinaria->deposito->id_corralon;
            if (!in_array($corralonId, $user->corralones_permitidos ?? [])) {
                session()->flash('error', 'No tienes permisos para editar maquinarias de este corralón.');
                return;
            }
        }

        $this->maquinaria_id = $maquinaria->id;
        $this->maquinaria = $maquinaria->maquinaria;
        $this->id_categoria_maquinaria = $maquinaria->id_categoria_maquinaria;
        $this->estado = $maquinaria->estado;
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
    
        // Verificar que el depósito seleccionado pertenezca a un corralón permitido
        if (!$user->acceso_todos_corralones) {
            $deposito = Deposito::find($this->id_deposito);
            if (!in_array($deposito->id_corralon, $user->corralones_permitidos ?? [])) {
                session()->flash('error', 'No tienes permisos para usar ese depósito.');
                return;
            }
        }

        if ($this->editMode) {
            $maquinaria = Maquinaria::findOrFail($this->maquinaria_id);

            // Verificar acceso a la maquinaria original por corralón
            if (!$user->acceso_todos_corralones) {
                $corralonId = $maquinaria->deposito->id_corralon;
                if (!in_array($corralonId, $user->corralones_permitidos ?? [])) {
                    session()->flash('error', 'No tienes permisos para editar maquinarias de este corralón.');
                    return;
                }
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

                // ✅ Crear tipo de movimiento si no existe
                $tipoMovimiento = TipoMovimiento::firstOrCreate([
                    'tipo_movimiento' => 'Inventario Inicial Maquinaria',
                    'tipo' => 'I'
                ]);

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
        
        // Verificar que el usuario tenga acceso a esta maquinaria por corralón
        $user = auth()->user();
        if (!$user->acceso_todos_corralones) {
            $corralonId = $maquinaria->deposito->id_corralon;
            if (!in_array($corralonId, $user->corralones_permitidos ?? [])) {
                session()->flash('error', 'No tienes permisos para eliminar maquinarias de este corralón.');
                return;
            }
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
        $this->id_deposito = '';
        $this->cantidad = 1;
        $this->resetErrorBag();
    }
}