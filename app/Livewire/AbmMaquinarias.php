<?php

namespace App\Livewire;

use App\Models\Maquinaria;
use App\Models\CategoriaMaquinaria;
use App\Models\Deposito;
use App\Models\MovimientoMaquinaria; // ✅ Agregar
use App\Models\TipoMovimiento; // ✅ Agregar
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB; // ✅ Agregar
use Illuminate\Support\Facades\Auth; // ✅ Agregar

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
        'cantidad.required' => 'La cantidad es obligatoria.',
        'cantidad.min' => 'La cantidad debe ser al menos 1.',
    ];

    public function render()
    {
        $user = auth()->user();

        $maquinarias = Maquinaria::with(['categoriaMaquinaria', 'deposito', 'movimientos.tipoMovimiento']) // ✅ Agregar eager loading
            ->porCorralonesPermitidos()
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

        $user = auth()->user();
        if (!$user->acceso_todos_corralones) {
            $corralonId = $maquinaria->deposito->id_corralon;
            if (!in_array($corralonId, $user->corralones_permitidos ?? [])) {
                session()->flash('error', 'No tienes permisos para editar esta maquinaria.');
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
        $this->validate();

        $user = auth()->user();
    
        if (!$user->acceso_todos_corralones) {
            $deposito = Deposito::find($this->id_deposito);
            if (!in_array($deposito->id_corralon, $user->corralones_permitidos ?? [])) {
                session()->flash('error', 'No tienes permisos para usar ese depósito.');
                return;
            }
        }

        if ($this->editMode) {
            $maquinaria = Maquinaria::findOrFail($this->maquinaria_id);

            if (!$user->acceso_todos_corralones) {
                $corralonId = $maquinaria->deposito->id_corralon;
                if (!in_array($corralonId, $user->corralones_permitidos ?? [])) {
                    session()->flash('error', 'No tienes permisos para editar esta maquinaria.');
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
        $maquinaria = Maquinaria::findOrFail($id);
        
        $user = auth()->user();
        if (!$user->acceso_todos_corralones) {
            $corralonId = $maquinaria->deposito->id_corralon;
            if (!in_array($corralonId, $user->corralones_permitidos ?? [])) {
                session()->flash('error', 'No tienes permisos para eliminar esta maquinaria.');
                return;
            }
        }
        
        $maquinaria->delete();
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
        $this->cantidad = 1;
        $this->resetErrorBag();
    }
}