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

    protected $listeners = ['refreshComponent' => '$refresh'];

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
    public $filtro_tipo_movimiento = '';
    
    // Pasos del modal
    public $paso_actual = 1; // 1: Seleccionar insumo, 2: Seleccionar tipo, 3: Completar datos
    
    // Insumo seleccionado
    public $insumo_seleccionado = null;
    public $insumo_id = null;
    
    // Tipo de movimiento seleccionado
    public $tipo_movimiento = ''; // 'carga', 'transferencia', 'ajuste_positivo', 'ajuste_negativo'
    
    // Campos del formulario
    public $cantidad = '';
    public $id_deposito_destino = '';
    public $observaciones = '';
    
    // Búsqueda de insumos
    public $search_insumo = '';
    public $mostrar_lista = false;
    
    // Para expandir/colapsar movimientos
    public $movimientos_expandidos = [];
    
    // Datos auxiliares
    public $depositos_disponibles = [];
    public $tipos_movimiento_disponibles = [];

    protected function rules()
    {
        $rules = [
            'cantidad' => 'required|numeric|min:0.01',
            'observaciones' => 'nullable|string|max:500',
        ];

        if ($this->tipo_movimiento === 'transferencia') {
            $rules['id_deposito_destino'] = 'required|exists:depositos,id|different:insumo_seleccionado.id_deposito';
        }

        return $rules;
    }

    protected $messages = [
        'cantidad.required' => 'La cantidad es obligatoria.',
        'cantidad.min' => 'La cantidad debe ser mayor a 0.',
        'id_deposito_destino.required' => 'Debe seleccionar un depósito destino.',
        'id_deposito_destino.different' => 'El depósito destino debe ser diferente al de origen.',
    ];

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

    /**
     * Determina qué tipos de movimiento están disponibles para el insumo
     */
    private function calcularTiposDisponibles()
    {
        if (!$this->insumo_seleccionado) {
            return [];
        }

        $tipos = [
            [
                'key' => 'carga',
                'nombre' => 'Carga de Stock',
                'descripcion' => 'Agregar stock al inventario actual',
                'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6',
                'color' => 'green',
                'disponible' => true
            ],
            [
                'key' => 'ajuste_positivo',
                'nombre' => 'Ajuste Positivo',
                'descripcion' => 'Corrección por diferencia de inventario (suma)',
                'icon' => 'M12 4v16m8-8H4',
                'color' => 'blue',
                'disponible' => true
            ],
        ];

        // Solo si hay stock disponible
        if ($this->insumo_seleccionado->stock_actual > 0) {
            $tipos[] = [
                'key' => 'transferencia',
                'nombre' => 'Transferencia',
                'descripcion' => 'Mover stock a otro depósito',
                'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
                'color' => 'purple',
                'disponible' => true
            ];
            
            $tipos[] = [
                'key' => 'ajuste_negativo',
                'nombre' => 'Ajuste Negativo',
                'descripcion' => 'Corrección por diferencia de inventario (resta)',
                'icon' => 'M20 12H4',
                'color' => 'red',
                'disponible' => true
            ];
        }

        return $tipos;
    }

    public function render()
    {
        $depositosAccesibles = $this->getDepositosAccesibles();

        // Obtener movimientos con filtros
        $movimientos = MovimientoInsumo::with([
                'insumo.categoriaInsumo',
                'insumo.deposito',
                'tipoMovimiento',
                'usuario'
            ])
            ->whereHas('insumo', function($query) use ($depositosAccesibles) {
                $query->whereIn('id_deposito', $depositosAccesibles);
            })
            ->when($this->search, function($query) {
                $query->whereHas('insumo', function($q) {
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
                $query->whereHas('insumo', function($q) {
                    $q->where('id_deposito', $this->filtro_deposito_origen);
                });
            })
            ->when($this->filtro_usuario, function($query) {
                $query->where('id_usuario', $this->filtro_usuario);
            })
            ->when($this->filtro_insumo, function($query) {
                $query->whereHas('insumo', function($q) {
                    $q->where('insumo', 'like', '%' . $this->filtro_insumo . '%');
                });
            })
            ->when($this->filtro_categoria, function($query) {
                $query->whereHas('insumo', function($q) {
                    $q->where('id_categoria', $this->filtro_categoria);
                });
            })
            ->when($this->filtro_tipo_movimiento, function($query) {
                $query->where('id_tipo_movimiento', $this->filtro_tipo_movimiento);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Insumos filtrados para el listado (paso 1)
        $insumos_filtrados = collect();
        
        if ($this->paso_actual === 1 && ($this->mostrar_lista || $this->search_insumo)) {
            // ✅ Query fresca SIN cache
            $insumos_filtrados = Insumo::with(['categoriaInsumo', 'deposito'])
                ->whereIn('id_deposito', $depositosAccesibles)
                ->when($this->search_insumo, function($query) {
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
                ->get(['id', 'insumo', 'id_categoria', 'id_deposito', 'stock_actual', 'stock_minimo', 'unidad']); // ✅ Especificar columnas para evitar cache
        }

        $depositos = Deposito::whereIn('id', $depositosAccesibles)
            ->orderBy('deposito')
            ->get();
            
        $categorias = CategoriaInsumo::orderBy('nombre')->get();
        $usuarios = User::orderBy('name')->get();
        $tipos_movimiento = TipoMovimiento::orderBy('tipo_movimiento')->get();

        return view('livewire.transferencias-insumos', [
            'movimientos' => $movimientos,
            'insumos_filtrados' => $insumos_filtrados,
            'depositos' => $depositos,
            'categorias' => $categorias,
            'usuarios' => $usuarios,
            'tipos_movimiento' => $tipos_movimiento,
        ])->layout('layouts.app', [
            'header' => 'Movimientos de Insumos'
        ]);
    }

    public function toggleMovimiento($id)
    {
        if (in_array($id, $this->movimientos_expandidos)) {
            $this->movimientos_expandidos = array_diff($this->movimientos_expandidos, [$id]);
        } else {
            $this->movimientos_expandidos[] = $id;
        }
    }

    public function limpiarFiltros()
    {
        $this->filtro_fecha_desde = '';
        $this->filtro_fecha_hasta = '';
        $this->filtro_deposito_origen = '';
        $this->filtro_deposito_destino = '';
        $this->filtro_usuario = '';
        $this->filtro_insumo = '';
        $this->filtro_categoria = '';
        $this->filtro_tipo_movimiento = '';
        $this->resetPage();
    }

    public function updatedSearchInsumo()
    {
        $this->mostrar_lista = true;
    }

    public function mostrarLista()
    {
        $this->mostrar_lista = true;
    }

    public function crear()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->paso_actual = 1;
    }

    // PASO 1: Seleccionar insumo
    public function seleccionarInsumo($insumoId)
    {
        $depositosAccesibles = $this->getDepositosAccesibles();
        
        // ✅ SIEMPRE recargar desde la base de datos para tener datos actualizados
        $this->insumo_seleccionado = Insumo::with(['categoriaInsumo', 'deposito'])
            ->whereIn('id_deposito', $depositosAccesibles)
            ->find($insumoId);
        
        if (!$this->insumo_seleccionado) {
            session()->flash('error', 'No tiene acceso a este insumo.');
            return;
        }
        
        // ✅ Refrescar el stock desde la BD
        $this->insumo_seleccionado->refresh();
        
        $this->insumo_id = $insumoId;
        $this->tipos_movimiento_disponibles = $this->calcularTiposDisponibles();
        $this->paso_actual = 2;
        $this->search_insumo = '';
        $this->mostrar_lista = false;
    }

    // PASO 2: Seleccionar tipo de movimiento
    public function seleccionarTipoMovimiento($tipo)
    {
        $this->tipo_movimiento = $tipo;
        
        // Si es transferencia, cargar depósitos disponibles
        if ($tipo === 'transferencia') {
            $depositosAccesibles = $this->getDepositosAccesibles();
            $this->depositos_disponibles = Deposito::whereIn('id', $depositosAccesibles)
                ->where('id', '!=', $this->insumo_seleccionado->id_deposito)
                ->orderBy('deposito')
                ->get();
        }
        
        $this->paso_actual = 3;
    }

    // Volver al paso anterior
    public function volverPaso()
    {
        if ($this->paso_actual > 1) {
            $this->paso_actual--;
            
            if ($this->paso_actual === 1) {
                $this->insumo_seleccionado = null;
                $this->tipo_movimiento = '';
                $this->tipos_movimiento_disponibles = [];
            } elseif ($this->paso_actual === 2) {
                // ✅ Refrescar el insumo cuando volvemos al paso 2
                if ($this->insumo_id) {
                    $this->insumo_seleccionado = Insumo::with(['categoriaInsumo', 'deposito'])
                        ->find($this->insumo_id);
                    $this->tipos_movimiento_disponibles = $this->calcularTiposDisponibles();
                }
                $this->tipo_movimiento = '';
                $this->cantidad = '';
                $this->id_deposito_destino = '';
            }
        }
    }

    // PASO 3: Guardar movimiento
    public function guardar()
    {
        try {
            $this->validate();

            switch ($this->tipo_movimiento) {
                case 'carga':
                    return $this->guardarCarga();
                case 'transferencia':
                    return $this->guardarTransferencia();
                case 'ajuste_positivo':
                    return $this->guardarAjustePositivo();
                case 'ajuste_negativo':
                    return $this->guardarAjusteNegativo();
                default:
                    session()->flash('error', 'Tipo de movimiento no válido.');
                    $this->cerrarModal();
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Dejar que Livewire maneje los errores de validación normalmente
            throw $e;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al procesar el movimiento: ' . $e->getMessage());
            $this->cerrarModal();
            \Log::error('Error en guardar movimiento: ' . $e->getMessage());
        }
    }

    private function guardarCarga()
    {
        try {
            DB::beginTransaction();

            $tipoMovimiento = TipoMovimiento::firstOrCreate([
                'tipo_movimiento' => 'Carga de Stock',
                'tipo' => 'I'
            ]);

            $insumo = Insumo::find($this->insumo_id);
            
            if (!$insumo) {
                throw new \Exception('No se encontró el insumo seleccionado.');
            }

            MovimientoInsumo::create([
                'id_insumo' => $insumo->id,
                'id_tipo_movimiento' => $tipoMovimiento->id,
                'cantidad' => $this->cantidad,
                'fecha' => now(),
                'fecha_devolucion' => null,
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $insumo->id_deposito,
                'id_referencia' => 0,
                'tipo_referencia' => 'inventario',
            ]);

            $insumo->sincronizarStock();

            DB::commit();

            $mensaje = "Carga realizada exitosamente: {$this->cantidad} {$insumo->unidad} de {$insumo->insumo}";
            session()->flash('message', $mensaje);
            
            // ✅ Limpiar cache de Eloquent
            \Illuminate\Support\Facades\Cache::flush();
            
            $this->cerrarModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al realizar la carga: ' . $e->getMessage());
            $this->cerrarModal();
            \Log::error('Error en guardarCarga: ' . $e->getMessage());
        }
    }

    private function guardarTransferencia()
    {
        try {
            $insumo = Insumo::find($this->insumo_id);
            
            if (!$insumo) {
                throw new \Exception('No se encontró el insumo seleccionado.');
            }

            if ($this->cantidad > $insumo->stock_actual) {
                session()->flash('error', 'La cantidad excede el stock disponible.');
                $this->cerrarModal();
                return;
            }

            DB::beginTransaction();

            // ✅ Tipo de movimiento SALIDA para el origen
            $tipoMovimientoSalida = TipoMovimiento::firstOrCreate([
                'tipo_movimiento' => 'Transferencia Salida',
                'tipo' => 'E' // ✅ SALIDA
            ]);

            // ✅ Tipo de movimiento ENTRADA para el destino
            $tipoMovimientoEntrada = TipoMovimiento::firstOrCreate([
                'tipo_movimiento' => 'Transferencia Entrada',
                'tipo' => 'I' // ✅ ENTRADA
            ]);

            // Crear o encontrar insumo en destino
            $insumo_destino = Insumo::firstOrCreate(
                [
                    'insumo' => $insumo->insumo,
                    'id_categoria' => $insumo->id_categoria,
                    'unidad' => $insumo->unidad,
                    'id_deposito' => $this->id_deposito_destino,
                ],
                [
                    'stock_actual' => 0,
                    'stock_minimo' => $insumo->stock_minimo,
                ]
            );

            // Crear encabezado de movimiento
            $encabezado = MovimientoEncabezado::create([
                'fecha' => now(),
                'id_deposito_origen' => $insumo->id_deposito,
                'id_deposito_destino' => $this->id_deposito_destino,
                'observaciones' => $this->observaciones,
                'id_usuario' => Auth::id(),
            ]);

            // ✅ Movimiento de SALIDA (origen) - tipo 'E'
            MovimientoInsumo::create([
                'id_insumo' => $insumo->id,
                'id_movimiento_encabezado' => $encabezado->id,
                'id_tipo_movimiento' => $tipoMovimientoSalida->id,
                'cantidad' => $this->cantidad,
                'fecha' => now(),
                'fecha_devolucion' => null,
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $insumo->id_deposito, // ✅ Poner el depósito de origen
                'id_referencia' => $insumo_destino->id,
                'tipo_referencia' => 'transferencia',
            ]);

            // ✅ Movimiento de ENTRADA (destino) - tipo 'I'
            MovimientoInsumo::create([
                'id_insumo' => $insumo_destino->id,
                'id_movimiento_encabezado' => $encabezado->id,
                'id_tipo_movimiento' => $tipoMovimientoEntrada->id,
                'cantidad' => $this->cantidad,
                'fecha' => now(),
                'fecha_devolucion' => null,
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $this->id_deposito_destino,
                'id_referencia' => $insumo->id,
                'tipo_referencia' => 'transferencia',
            ]);

            $insumo->sincronizarStock();
            $insumo_destino->sincronizarStock();

            DB::commit();

            $deposito_destino = Deposito::find($this->id_deposito_destino);
            $mensaje = "Transferencia realizada exitosamente: {$this->cantidad} {$insumo->unidad} de {$insumo->insumo} desde {$insumo->deposito->deposito} hacia {$deposito_destino->deposito}";
            session()->flash('message', $mensaje);
            $this->cerrarModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al realizar la transferencia: ' . $e->getMessage());
            $this->cerrarModal();
            \Log::error('Error en guardarTransferencia: ' . $e->getMessage());
        }
    }

    private function guardarAjustePositivo()
    {
        try {
            DB::beginTransaction();

            $tipoMovimiento = TipoMovimiento::firstOrCreate([
                'tipo_movimiento' => 'Ajuste Positivo',
                'tipo' => 'I'
            ]);

            $insumo = Insumo::find($this->insumo_id);
            
            if (!$insumo) {
                throw new \Exception('No se encontró el insumo seleccionado.');
            }

            MovimientoInsumo::create([
                'id_insumo' => $insumo->id,
                'id_tipo_movimiento' => $tipoMovimiento->id,
                'cantidad' => $this->cantidad,
                'fecha' => now(),
                'fecha_devolucion' => null,
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $insumo->id_deposito,
                'id_referencia' => 0,
                'tipo_referencia' => 'inventario', // ✅ Usar 'inventario' para ajustes
            ]);

            $insumo->sincronizarStock();

            DB::commit();

            $mensaje = "Ajuste positivo realizado exitosamente: +{$this->cantidad} {$insumo->unidad} de {$insumo->insumo}";
            session()->flash('message', $mensaje);
            $this->cerrarModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al realizar el ajuste: ' . $e->getMessage());
            $this->cerrarModal();
            \Log::error('Error en guardarAjustePositivo: ' . $e->getMessage());
        }
    }

    private function guardarAjusteNegativo()
    {
        try {
            $insumo = Insumo::find($this->insumo_id);
            
            if (!$insumo) {
                throw new \Exception('No se encontró el insumo seleccionado.');
            }

            if ($this->cantidad > $insumo->stock_actual) {
                session()->flash('error', 'La cantidad excede el stock disponible.');
                $this->cerrarModal();
                return;
            }

            DB::beginTransaction();

            $tipoMovimiento = TipoMovimiento::firstOrCreate([
                'tipo_movimiento' => 'Ajuste Negativo',
                'tipo' => 'E'
            ]);

            MovimientoInsumo::create([
                'id_insumo' => $insumo->id,
                'id_tipo_movimiento' => $tipoMovimiento->id,
                'cantidad' => $this->cantidad,
                'fecha' => now(),
                'fecha_devolucion' => null,
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $insumo->id_deposito,
                'id_referencia' => 0,
                'tipo_referencia' => 'inventario', // ✅ Usar 'inventario' para ajustes
            ]);

            $insumo->sincronizarStock();

            DB::commit();

            $mensaje = "Ajuste negativo realizado exitosamente: -{$this->cantidad} {$insumo->unidad} de {$insumo->insumo}";
            session()->flash('message', $mensaje);
            $this->cerrarModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al realizar el ajuste: ' . $e->getMessage());
            $this->cerrarModal();
            \Log::error('Error en guardarAjusteNegativo: ' . $e->getMessage());
        }
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetForm();
        
        // ✅ Forzar actualización del componente
        $this->dispatch('refreshComponent');
    }

    private function resetForm()
    {
        $this->paso_actual = 1;
        $this->insumo_seleccionado = null;
        $this->insumo_id = null;
        $this->tipo_movimiento = '';
        $this->cantidad = '';
        $this->id_deposito_destino = '';
        $this->observaciones = '';
        $this->search_insumo = '';
        $this->mostrar_lista = false;
        $this->depositos_disponibles = [];
        $this->tipos_movimiento_disponibles = [];
        $this->resetErrorBag();
    }
}