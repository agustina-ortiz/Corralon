<?php
// app/Livewire/TransferenciasInsumos.php

namespace App\Livewire;

use App\Models\Insumo;
use App\Models\Deposito;
use App\Models\MovimientoInsumo;
use App\Models\MovimientoEncabezado;
use App\Models\TipoMovimiento;
use App\Models\CategoriaInsumo;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransferenciasInsumos extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $showFilters = false;
    
    // Filtros
    public $filtro_fecha_desde = '';
    public $filtro_fecha_hasta = '';
    public $filtro_deposito_origen = '';
    public $filtro_deposito_destino = '';
    public $filtro_usuario = '';
    public $filtro_insumo = '';
    public $filtro_categoria = '';
    
    // ✅ NUEVO: Tipo de operación
    public $tipo_operacion = 'transferencia'; // 'transferencia' o 'carga_inicial'
    
    // Campos del formulario
    public $id_deposito_destino;
    public $observaciones = '';
    
    // Búsqueda de insumos
    public $search_insumo = '';
    public $mostrar_lista = false;
    
    // Lista de insumos seleccionados para transferir
    public $insumos_a_transferir = [];
    
    // Para expandir/colapsar transferencias agrupadas
    public $transferencias_expandidas = [];
    
    // Datos auxiliares
    public $depositos_disponibles = [];
    public $id_tipo_transferencia;

    protected function rules()
    {
        $rules = [
            'insumos_a_transferir' => 'required|array|min:1',
            'insumos_a_transferir.*.id_insumo' => 'required|exists:insumos,id',
            'insumos_a_transferir.*.cantidad' => 'required|numeric|min:0.01',
            'observaciones' => 'nullable|string|max:500',
        ];

        // ✅ Solo requerir depósito destino si es transferencia
        if ($this->tipo_operacion === 'transferencia') {
            $rules['id_deposito_destino'] = 'required|exists:depositos,id';
        }

        return $rules;
    }

    protected $messages = [
        'insumos_a_transferir.required' => 'Debe agregar al menos un insumo.',
        'insumos_a_transferir.min' => 'Debe agregar al menos un insumo.',
        'insumos_a_transferir.*.cantidad.required' => 'La cantidad es obligatoria.',
        'insumos_a_transferir.*.cantidad.min' => 'La cantidad debe ser mayor a 0.',
        'id_deposito_destino.required' => 'Debe seleccionar un depósito destino.',
    ];

    public function mount()
    {
        // Buscar o crear el tipo de movimiento "Transferencia"
        $this->id_tipo_transferencia = TipoMovimiento::firstOrCreate(
            ['tipo_movimiento' => 'Transferencia', 'tipo' => 'I']
        )->id;
    }

    /**
     * Obtiene los IDs de depósitos accesibles por el usuario actual
     */
    private function getDepositosAccesibles()
    {
        $user = Auth::user();
        
        if ($user->acceso_todos_corralones) {
            return Deposito::pluck('id')->toArray();
        }

        return Deposito::whereIn('id_corralon', $user->corralones_permitidos ?? [])
            ->pluck('id')
            ->toArray();
    }

    public function render()
    {
        $depositosAccesibles = $this->getDepositosAccesibles();

        // Obtener transferencias agrupadas con filtros
        $transferencias = MovimientoEncabezado::with([
                'movimientos.insumo.categoriaInsumo',
                'movimientos.insumo.deposito',
                'depositoOrigen',
                'depositoDestino',
                'usuario'
            ])
            ->whereHas('depositoOrigen', function($query) use ($depositosAccesibles) {
                $query->whereIn('id', $depositosAccesibles);
            })
            ->when($this->search, function($query) {
                $query->whereHas('movimientos.insumo', function($q) {
                    $q->where('insumo', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filtro_fecha_desde, function($query) {
                $query->whereDate('fecha', '>=', $this->filtro_fecha_desde);
            })
            ->when($this->filtro_fecha_hasta, function($query) {
                $query->whereDate('fecha', '<=', $this->filtro_fecha_hasta);
            })
            ->when($this->filtro_deposito_origen, function($query) {
                $query->where('id_deposito_origen', $this->filtro_deposito_origen);
            })
            ->when($this->filtro_deposito_destino, function($query) {
                $query->where('id_deposito_destino', $this->filtro_deposito_destino);
            })
            ->when($this->filtro_usuario, function($query) {
                $query->where('id_usuario', $this->filtro_usuario);
            })
            ->when($this->filtro_insumo, function($query) {
                $query->whereHas('movimientos.insumo', function($q) {
                    $q->where('insumo', 'like', '%' . $this->filtro_insumo . '%');
                });
            })
            ->when($this->filtro_categoria, function($query) {
                $query->whereHas('movimientos.insumo', function($q) {
                    $q->where('id_categoria', $this->filtro_categoria);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Insumos filtrados para el listado
        $insumos_filtrados = collect();
        
        if ($this->mostrar_lista || $this->search_insumo) {
            $ids_agregados = collect($this->insumos_a_transferir)->pluck('id_insumo')->toArray();
            
            $query = Insumo::with(['categoriaInsumo', 'deposito'])
                ->whereIn('id_deposito', $depositosAccesibles)
                ->whereNotIn('id', $ids_agregados);
            
            // ✅ Filtrar según el tipo de operación
            if ($this->tipo_operacion === 'carga_inicial') {
                $query->where('stock_actual', 0);
            } else {
                $query->where('stock_actual', '>', 0);
            }
            
            $insumos_filtrados = $query->when($this->search_insumo, function($query) {
                    $query->where(function($q) {
                        $q->where('insumo', 'like', '%' . $this->search_insumo . '%')
                          ->orWhereHas('categoriaInsumo', function($cat) {
                              $cat->where('nombre', 'like', '%' . $this->search_insumo . '%');
                          })
                          ->orWhereHas('deposito', function($dep) {
                              $dep->where('deposito', 'like', '%' . $this->search_insumo . '%');
                          });
                    });
                })
                ->orderBy('insumo')
                ->limit(50)
                ->get();
        }

        $depositos = Deposito::whereIn('id', $depositosAccesibles)
            ->orderBy('deposito')
            ->get();
            
        $categorias = CategoriaInsumo::orderBy('nombre')->get();
        $usuarios = User::orderBy('name')->get();

        return view('livewire.transferencias-insumos', [
            'transferencias' => $transferencias,
            'insumos_filtrados' => $insumos_filtrados,
            'depositos' => $depositos,
            'categorias' => $categorias,
            'usuarios' => $usuarios,
        ])->layout('layouts.app', [
            'header' => 'Transferencias de Insumos'
        ]);
    }

    // ✅ NUEVO: Cambiar tipo de operación
    public function cambiarTipoOperacion($tipo)
    {
        $this->tipo_operacion = $tipo;
        $this->insumos_a_transferir = [];
        $this->id_deposito_destino = '';
        $this->depositos_disponibles = [];
        $this->search_insumo = '';
        $this->mostrar_lista = false;
    }

    public function toggleTransferencia($id)
    {
        if (in_array($id, $this->transferencias_expandidas)) {
            $this->transferencias_expandidas = array_diff($this->transferencias_expandidas, [$id]);
        } else {
            $this->transferencias_expandidas[] = $id;
        }
    }

    // ... (rest of updatingFiltro methods remain the same)

    public function limpiarFiltros()
    {
        $this->filtro_fecha_desde = '';
        $this->filtro_fecha_hasta = '';
        $this->filtro_deposito_origen = '';
        $this->filtro_deposito_destino = '';
        $this->filtro_usuario = '';
        $this->filtro_insumo = '';
        $this->filtro_categoria = '';
        $this->resetPage();
    }

    public function updatedSearchInsumo()
    {
        $this->mostrar_lista = true;
    }

    public function agregarInsumo($insumoId)
    {
        $depositosAccesibles = $this->getDepositosAccesibles();
        
        $insumo = Insumo::with(['categoriaInsumo', 'deposito'])
            ->whereIn('id_deposito', $depositosAccesibles)
            ->find($insumoId);
        
        if (!$insumo) {
            session()->flash('error', 'No tiene acceso a este insumo.');
            return;
        }
        
        $this->insumos_a_transferir[] = [
            'id_insumo' => $insumo->id,
            'insumo' => $insumo->insumo,
            'categoria' => $insumo->categoriaInsumo->nombre,
            'deposito_origen' => $insumo->deposito->deposito,
            'id_deposito_origen' => $insumo->id_deposito,
            'stock_actual' => $insumo->stock_actual,
            'stock_minimo' => $insumo->stock_minimo,
            'id_categoria' => $insumo->id_categoria,
            'unidad' => $insumo->unidad,
            'cantidad' => '',
        ];
        
        // Si es transferencia y es el primer insumo, cargar depósitos disponibles
        if ($this->tipo_operacion === 'transferencia' && count($this->insumos_a_transferir) == 1) {
            $this->cargarDepositosDisponibles($insumo->id_deposito);
        }
        
        $this->search_insumo = '';
        $this->mostrar_lista = false;
    }

    public function eliminarInsumo($index)
    {
        unset($this->insumos_a_transferir[$index]);
        $this->insumos_a_transferir = array_values($this->insumos_a_transferir);
        
        if (count($this->insumos_a_transferir) == 0) {
            $this->depositos_disponibles = [];
            $this->id_deposito_destino = '';
        }
    }

    public function cargarDepositosDisponibles($id_deposito_origen)
    {
        $depositosAccesibles = $this->getDepositosAccesibles();
        
        $this->depositos_disponibles = Deposito::whereIn('id', $depositosAccesibles)
            ->where('id', '!=', $id_deposito_origen)
            ->orderBy('deposito')
            ->get();
    }

    public function mostrarLista()
    {
        $this->mostrar_lista = true;
    }

    public function crear()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function guardar()
    {
        $this->validate();

        // ✅ Lógica diferente según el tipo de operación
        if ($this->tipo_operacion === 'carga_inicial') {
            return $this->guardarCargaInicial();
        } else {
            return $this->guardarTransferencia();
        }
    }

    // ✅ NUEVO: Guardar carga inicial
    private function guardarCargaInicial()
    {
        $depositosAccesibles = $this->getDepositosAccesibles();

        try {
            DB::beginTransaction();

            $tipoInventarioInicial = TipoMovimiento::firstOrCreate([
                'tipo_movimiento' => 'Inventario Inicial',
                'tipo' => 'I'
            ]);

            $items_procesados = [];

            foreach ($this->insumos_a_transferir as $item) {
                $insumo = Insumo::whereIn('id_deposito', $depositosAccesibles)
                    ->findOrFail($item['id_insumo']);

                // Crear movimiento de inventario inicial
                MovimientoInsumo::create([
                    'id_insumo' => $insumo->id,
                    'id_tipo_movimiento' => $tipoInventarioInicial->id,
                    'cantidad' => $item['cantidad'],
                    'fecha' => now(),
                    'id_usuario' => Auth::id(),
                    'id_deposito_entrada' => $insumo->id_deposito,
                    'id_referencia' => 0,
                    'tipo_referencia' => 'inventario',
                ]);

                // Sincronizar stock
                $insumo->sincronizarStock();

                $items_procesados[] = "{$item['cantidad']} {$item['unidad']} de {$item['insumo']} en {$item['deposito_origen']}";
            }

            DB::commit();

            $mensaje = "Carga inicial realizada exitosamente: " . implode(', ', $items_procesados);
            session()->flash('message', $mensaje);
            $this->showModal = false;
            $this->resetForm();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al realizar la carga inicial: ' . $e->getMessage());
        }
    }

    // Transferencia normal (código existente)
    private function guardarTransferencia()
    {
        $depositosAccesibles = $this->getDepositosAccesibles();

        // Validaciones adicionales
        $errores = [];
        
        foreach ($this->insumos_a_transferir as $index => $item) {
            $insumo = Insumo::whereIn('id_deposito', $depositosAccesibles)->find($item['id_insumo']);
            
            if (!$insumo) {
                $errores[] = "No tiene acceso al insumo: {$item['insumo']}";
                continue;
            }
            
            if ($item['cantidad'] > $insumo->stock_actual) {
                $errores[] = "{$item['insumo']}: La cantidad ({$item['cantidad']}) no puede ser mayor al stock disponible (" . number_format($insumo->stock_actual, 2) . ").";
            }
            
            if ($insumo->id_deposito == $this->id_deposito_destino) {
                $errores[] = "{$item['insumo']}: No puede transferir al mismo depósito de origen.";
            }
        }

        if (!in_array($this->id_deposito_destino, $depositosAccesibles)) {
            $errores[] = "No tiene acceso al depósito destino seleccionado.";
        }
        
        if (count($errores) > 0) {
            session()->flash('error', implode('<br>', $errores));
            return;
        }

        try {
            DB::beginTransaction();

            $primer_insumo = Insumo::whereIn('id_deposito', $depositosAccesibles)
                ->find($this->insumos_a_transferir[0]['id_insumo']);
            
            if (!$primer_insumo) {
                throw new \Exception('No tiene acceso a los insumos seleccionados.');
            }
            
            $encabezado = MovimientoEncabezado::create([
                'fecha' => now(),
                'id_deposito_origen' => $primer_insumo->id_deposito,
                'id_deposito_destino' => $this->id_deposito_destino,
                'observaciones' => $this->observaciones,
                'id_usuario' => Auth::id(),
            ]);

            $items_procesados = [];

            foreach ($this->insumos_a_transferir as $item) {
                $insumo_origen = Insumo::whereIn('id_deposito', $depositosAccesibles)
                    ->findOrFail($item['id_insumo']);

                $insumo_destino = Insumo::firstOrCreate(
                    [
                        'insumo' => $insumo_origen->insumo,
                        'id_categoria' => $insumo_origen->id_categoria,
                        'unidad' => $insumo_origen->unidad,
                        'id_deposito' => $this->id_deposito_destino,
                    ],
                    [
                        'stock_actual' => 0,
                        'stock_minimo' => $insumo_origen->stock_minimo,
                    ]
                );

                MovimientoInsumo::create([
                    'id_movimiento_encabezado' => $encabezado->id,
                    'id_insumo' => $insumo_origen->id,
                    'id_tipo_movimiento' => $this->id_tipo_transferencia,
                    'cantidad' => $item['cantidad'],
                    'fecha' => now(),
                    'id_usuario' => Auth::id(),
                    'id_deposito_entrada' => $this->id_deposito_destino,
                    'id_referencia' => $insumo_destino->id,
                    'tipo_referencia' => 'transferencia',
                ]);

                MovimientoInsumo::create([
                    'id_movimiento_encabezado' => $encabezado->id,
                    'id_insumo' => $insumo_destino->id,
                    'id_tipo_movimiento' => $this->id_tipo_transferencia,
                    'cantidad' => $item['cantidad'],
                    'fecha' => now(),
                    'id_usuario' => Auth::id(),
                    'id_deposito_entrada' => $this->id_deposito_destino,
                    'id_referencia' => $insumo_origen->id,
                    'tipo_referencia' => 'transferencia',
                ]);

                $insumo_origen->sincronizarStock();
                $insumo_destino->sincronizarStock();

                $items_procesados[] = "{$item['cantidad']} {$item['unidad']} de {$item['insumo']}";
            }

            DB::commit();

            $deposito_origen = $encabezado->depositoOrigen;
            $deposito_destino = $encabezado->depositoDestino;
            
            $mensaje = "Transferencia realizada exitosamente: " . implode(', ', $items_procesados) . " desde {$deposito_origen->deposito} hacia {$deposito_destino->deposito}";
            session()->flash('message', $mensaje);
            $this->showModal = false;
            $this->resetForm();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al realizar la transferencia: ' . $e->getMessage());
        }
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->tipo_operacion = 'transferencia'; // ✅ Reset al valor por defecto
        $this->insumos_a_transferir = [];
        $this->id_deposito_destino = '';
        $this->observaciones = '';
        $this->search_insumo = '';
        $this->mostrar_lista = false;
        $this->depositos_disponibles = [];
        $this->resetErrorBag();
    }
}