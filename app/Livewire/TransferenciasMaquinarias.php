<?php
// app/Livewire/TransferenciasMaquinarias.php

namespace App\Livewire;

use App\Models\Maquinaria;
use App\Models\Deposito;
use App\Models\MovimientoMaquinaria;
use App\Models\MovimientoEncabezado;
use App\Models\TipoMovimiento;
use App\Models\CategoriaMaquinaria;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransferenciasMaquinarias extends Component
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
    public $filtro_maquinaria = '';
    public $filtro_categoria = '';
    public $filtro_tipo_movimiento = '';
    
    // Pasos del modal
    public $paso_actual = 1; // 1: Seleccionar maquinaria, 2: Seleccionar tipo, 3: Completar datos
    
    // Maquinaria seleccionada
    public $maquinaria_seleccionada = null;
    public $maquinaria_id = null;
    
    // Tipo de movimiento seleccionado
    public $tipo_movimiento = ''; // 'asignacion', 'transferencia', 'devolucion', 'mantenimiento'
    
    // Campos del formulario
    public $id_deposito_destino = '';
    public $id_deposito_origen = '';
    public $observaciones = '';
    public $fecha_devolucion_esperada = '';
    
    // Búsqueda de maquinarias
    public $search_maquinaria = '';
    public $mostrar_lista = false;
    
    // Para expandir/colapsar movimientos
    public $movimientos_expandidos = [];
    
    // Datos auxiliares
    public $depositos_disponibles = [];
    public $tipos_movimiento_disponibles = [];

    public $cantidad_a_transferir = 1;
    public $cantidad_a_cargar = 1;  
    public $cantidad_a_asignar = 1; 

    protected function rules()
    {
        $rules = [
            'observaciones' => 'nullable|string|max:500',
        ];

        if ($this->tipo_movimiento === 'transferencia') {
            $rules['id_deposito_destino'] = 'required|exists:depositos,id';

            $cantidadDisponible = $this->maquinaria_seleccionada 
                ? $this->maquinaria_seleccionada->cantidad_disponible
                : 1;

            $rules['cantidad_a_transferir'] = [
                'required',
                'integer',
                'min:1',
                'max:' . $cantidadDisponible
            ];
        }

        // ✅ NUEVO: Validación para carga de stock
        if ($this->tipo_movimiento === 'carga_stock') {
            $rules['id_deposito_origen'] = 'required|exists:depositos,id';
            $rules['cantidad_a_cargar'] = [
                'required',
                'integer',
                'min:1',
                'max:1000' // Límite razonable
            ];
        }

        // Validación para asignaciones
        if ($this->tipo_movimiento === 'asignacion') {
            $rules['id_deposito_origen'] = 'required|exists:depositos,id';
            
            $cantidadDisponible = $this->maquinaria_seleccionada 
                ? $this->maquinaria_seleccionada->getCantidadEnDeposito($this->id_deposito_origen)
                : 1;

            $rules['cantidad_a_asignar'] = [
                'required',
                'integer',
                'min:1',
                'max:' . $cantidadDisponible
            ];
            
            $rules['fecha_devolucion_esperada'] = 'required|date|after:today';
        }

        if (in_array($this->tipo_movimiento, ['devolucion'])) {
            $rules['fecha_devolucion_esperada'] = 'nullable|date|after:today';
        }

        return $rules;
    }

    protected $messages = [
        'id_deposito_destino.required' => 'Debe seleccionar un depósito destino.',
        'id_deposito_destino.different' => 'El depósito destino debe ser diferente al de origen.',
        'id_deposito_origen.required' => 'Debe seleccionar un depósito de origen.',
        'fecha_devolucion_esperada.required' => 'Debe ingresar una fecha de devolución esperada.',
        'fecha_devolucion_esperada.after' => 'La fecha de devolución debe ser posterior a hoy.',
        'cantidad_a_transferir.required' => 'Debe ingresar la cantidad a transferir.',
        'cantidad_a_transferir.min' => 'La cantidad debe ser al menos 1.',
        'cantidad_a_transferir.max' => 'No hay suficiente cantidad disponible en el depósito origen.',
        'cantidad_a_asignar.required' => 'Debe ingresar la cantidad a asignar.',
        'cantidad_a_asignar.min' => 'La cantidad debe ser al menos 1.',
        'cantidad_a_asignar.max' => 'No hay suficiente cantidad disponible en el depósito seleccionado.',
        'cantidad_a_cargar.required' => 'Debe ingresar la cantidad a cargar.',
        'cantidad_a_cargar.min' => 'La cantidad debe ser al menos 1.',
        'cantidad_a_cargar.max' => 'La cantidad excede el límite permitido.',
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
     * Determina qué tipos de movimiento están disponibles para la maquinaria
     */
    private function calcularTiposDisponibles()
    {
        if (!$this->maquinaria_seleccionada) {
            return [];
        }

        // ✅ Verificar si hay stock en ALGÚN depósito
        $tieneStock = $this->maquinaria_seleccionada->getCantidadTotalDisponible() > 0;

        $tipos = [];

        // ✅ NUEVO: Carga de Stock - SIEMPRE disponible
        $tipos[] = [
            'key' => 'carga_stock',
            'nombre' => 'Carga de Stock',
            'descripcion' => 'Agregar unidades al inventario',
            'icon' => 'M12 4v16m8-8H4',
            'color' => 'indigo',
            'disponible' => true
        ];

        // Asignación - solo si hay stock
        if ($tieneStock) {
            $tipos[] = [
                'key' => 'asignacion',
                'nombre' => 'Asignación',
                'descripcion' => 'Asignar maquinaria a un empleado o evento',
                'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                'color' => 'blue',
                'disponible' => true
            ];
        }

        // Mantenimiento - solo si hay stock
        if ($tieneStock) {
            $tipos[] = [
                'key' => 'mantenimiento',
                'nombre' => 'Enviar a Mantenimiento',
                'descripcion' => 'Marcar maquinaria como en mantenimiento',
                'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                'color' => 'orange',
                'disponible' => true
            ];
        }

        // Transferencia - solo si hay stock
        if ($tieneStock) {
            $tipos[] = [
                'key' => 'transferencia',
                'nombre' => 'Transferencia',
                'descripcion' => 'Mover maquinaria a otro depósito',
                'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
                'color' => 'purple',
                'disponible' => true
            ];
        }

        // Devolución: siempre disponible (puede haber unidades prestadas aunque stock sea 0)
        $tipos[] = [
            'key' => 'devolucion',
            'nombre' => 'Devolución',
            'descripcion' => 'Devolver maquinaria al depósito',
            'icon' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6',
            'color' => 'green',
            'disponible' => true
        ];

        return collect($tipos)->where('disponible', true)->values()->toArray();
    }

    public function render()
    {
        $depositosAccesibles = $this->getDepositosAccesibles();

        // Obtener movimientos con filtros
        $movimientos = MovimientoMaquinaria::with([
                'maquinaria.categoriaMaquinaria',
                'maquinaria.deposito',
                'maquinaria.movimientos.tipoMovimiento',
                'depositoEntrada',
                'tipoMovimiento',
                'usuario'
        ])
        ->whereHas('maquinaria', function($query) use ($depositosAccesibles) {
            $query->whereIn('id_deposito', $depositosAccesibles);
        })
        ->when($this->search, function($query) {
            $query->whereHas('maquinaria', function($q) {
                $q->where('maquinaria', 'like', '%' . $this->search . '%');
            });
        })
        ->when($this->filtro_fecha_desde, function($query) {
            $query->whereDate('fecha', '>=', $this->filtro_fecha_desde);
        })
        ->when($this->filtro_fecha_hasta, function($query) {
            $query->whereDate('fecha', '<=', $this->filtro_fecha_hasta);
        })
        ->when($this->filtro_deposito_origen, function($query) {
            $query->where('id_deposito_entrada', $this->filtro_deposito_origen);
        })
        ->when($this->filtro_usuario, function($query) {
            $query->where('id_usuario', $this->filtro_usuario);
        })
        ->when($this->filtro_maquinaria, function($query) {
            $query->whereHas('maquinaria', function($q) {
                $q->where('maquinaria', 'like', '%' . $this->filtro_maquinaria . '%');
            });
        })
        ->when($this->filtro_categoria, function($query) {
            $query->whereHas('maquinaria', function($q) {
                $q->where('id_categoria_maquinaria', $this->filtro_categoria);
            });
        })
        ->when($this->filtro_tipo_movimiento, function($query) {
            $query->where('id_tipo_movimiento', $this->filtro_tipo_movimiento);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(15);

        $movimientos->getCollection()->transform(function ($movimiento) use ($depositosAccesibles) {
            // ✅ Calcular cantidad histórica en el momento del movimiento
            $entradas = MovimientoMaquinaria::where('id_maquinaria', $movimiento->id_maquinaria)
                ->where('created_at', '<=', $movimiento->created_at)
                ->whereHas('tipoMovimiento', fn($q) => $q->where('tipo', 'I'))
                ->sum('cantidad');
            
            $salidas = MovimientoMaquinaria::where('id_maquinaria', $movimiento->id_maquinaria)
                ->where('created_at', '<=', $movimiento->created_at)
                ->whereHas('tipoMovimiento', fn($q) => $q->where('tipo', 'E'))
                ->sum('cantidad');
            
            $movimiento->cantidad_historica = max(0, $entradas - $salidas);
            
            // ✅ Calcular estado ACTUAL basado en stock total disponible
            $stockTotal = 0;
            foreach ($depositosAccesibles as $depositoId) {
                $stockTotal += $movimiento->maquinaria->getCantidadEnDeposito($depositoId);
            }
            
            $movimiento->estado_calculado = $stockTotal > 0 ? 'disponible' : 'no disponible';
            
            return $movimiento;
        });

        // Maquinarias filtradas para el listado (paso 1)
        $maquinarias_filtradas = collect();
        
        if ($this->paso_actual === 1 && ($this->mostrar_lista || $this->search_maquinaria)) {
            $maquinarias_filtradas = Maquinaria::with([
                'categoriaMaquinaria', 
                'deposito',
                'movimientos.tipoMovimiento'
            ])
            ->whereIn('id_deposito', $depositosAccesibles)
            ->when($this->search_maquinaria, function($query) {
                $query->where(function($q) {
                    $q->where('maquinaria', 'like', '%' . $this->search_maquinaria . '%')
                        ->orWhereHas('categoriaMaquinaria', function($cat) {
                            $cat->where('nombre', 'like', '%' . $this->search_maquinaria . '%');
                        })
                        ->orWhereHas('deposito', function($dep) {
                            $dep->where('deposito', 'like', '%' . $this->search_maquinaria . '%');
                        });
                });
            })
            ->orderBy('maquinaria')
            ->limit(50)
            ->get()
            ->map(function($maquinaria) use ($depositosAccesibles) {
                // ✅ Calcular cantidad total disponible en todos los depósitos
                $cantidadTotal = 0;
                foreach ($depositosAccesibles as $depositoId) {
                    $cantidadTotal += $maquinaria->getCantidadEnDeposito($depositoId);
                }
                
                // ✅ Agregar la cantidad calculada como atributo
                $maquinaria->cantidad_disponible = $cantidadTotal;
                
                return $maquinaria;
            });
        }

        $depositos = Deposito::whereIn('id', $depositosAccesibles)
            ->orderBy('deposito')
            ->get();
            
        $categorias = CategoriaMaquinaria::orderBy('nombre')->get();
        $usuarios = User::orderBy('name')->get();
        $tipos_movimiento = TipoMovimiento::orderBy('tipo_movimiento')->get();

        return view('livewire.transferencias-maquinarias', [
            'movimientos' => $movimientos,
            'maquinarias_filtradas' => $maquinarias_filtradas,
            'depositos' => $depositos,
            'categorias' => $categorias,
            'usuarios' => $usuarios,
            'tipos_movimiento' => $tipos_movimiento,
        ])->layout('layouts.app', [
            'header' => 'Movimientos de Maquinarias'
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
        $this->filtro_maquinaria = '';
        $this->filtro_categoria = '';
        $this->filtro_tipo_movimiento = '';
        $this->resetPage();
    }

    public function updatedSearchMaquinaria()
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

   public function incrementarCantidad()
    {
        if ($this->tipo_movimiento === 'transferencia') {
            // ✅ Usar el depósito de la maquinaria seleccionada, no id_deposito_origen
            $maxCantidad = $this->maquinaria_seleccionada 
                ? $this->maquinaria_seleccionada->cantidad_disponible
                : 1;
            
            if ($this->cantidad_a_transferir < $maxCantidad) {
                $this->cantidad_a_transferir++;
            }
        } elseif ($this->tipo_movimiento === 'asignacion') {
            $maxCantidad = $this->maquinaria_seleccionada && $this->id_deposito_origen
                ? $this->maquinaria_seleccionada->getCantidadEnDeposito($this->id_deposito_origen)
                : 1;
            
            if ($this->cantidad_a_asignar < $maxCantidad) {
                $this->cantidad_a_asignar++;
            }
        } elseif ($this->tipo_movimiento === 'carga_stock') {
            if ($this->cantidad_a_cargar < 1000) {
                $this->cantidad_a_cargar++;
            }
        }
    }

    public function decrementarCantidad()
    {
        if ($this->tipo_movimiento === 'transferencia') {
            if ($this->cantidad_a_transferir > 1) {
                $this->cantidad_a_transferir--;
            }
        } elseif ($this->tipo_movimiento === 'asignacion') {
            if ($this->cantidad_a_asignar > 1) {
                $this->cantidad_a_asignar--;
            }
        } elseif ($this->tipo_movimiento === 'carga_stock') {
            if ($this->cantidad_a_cargar > 1) {
                $this->cantidad_a_cargar--;
            }
        }
    }

    // PASO 1: Seleccionar maquinaria
    public function seleccionarMaquinaria($maquinariaId)
    {
        $depositosAccesibles = $this->getDepositosAccesibles();
        
        $this->maquinaria_seleccionada = Maquinaria::with(['categoriaMaquinaria', 'deposito'])
            ->whereIn('id_deposito', $depositosAccesibles)
            ->find($maquinariaId);
        
        if (!$this->maquinaria_seleccionada) {
            session()->flash('error', 'No tiene acceso a esta maquinaria.');
            return;
        }
        
        $this->maquinaria_id = $maquinariaId;
        $this->tipos_movimiento_disponibles = $this->calcularTiposDisponibles();
        $this->paso_actual = 2;
        $this->search_maquinaria = '';
        $this->mostrar_lista = false;
    }

    // PASO 2: Seleccionar tipo de movimiento
    public function seleccionarTipoMovimiento($tipo)
    {
        $this->tipo_movimiento = $tipo;
        $depositosAccesibles = $this->getDepositosAccesibles();
        
        // Para transferencia
        if ($tipo === 'transferencia') {
            $depositosConStock = [];
            foreach ($depositosAccesibles as $depId) {
                if ($this->maquinaria_seleccionada->getCantidadEnDeposito($depId) > 0) {
                    $depositosConStock[] = $depId;
                }
            }
            
            // ✅ Si solo hay un depósito con stock, seleccionarlo automáticamente
            if (count($depositosConStock) === 1) {
                $this->id_deposito_origen = $depositosConStock[0];
            }
            
            // ✅ CORRECCIÓN: Para depósito DESTINO, mostrar TODOS excepto el origen
            // No usar whereNotIn con $depositosConStock
            $this->depositos_disponibles = Deposito::whereIn('id', $depositosAccesibles)
                ->orderBy('deposito')
                ->get();

            $cantidadDisponible = $this->id_deposito_origen 
                ? $this->maquinaria_seleccionada->getCantidadEnDeposito($this->id_deposito_origen)
                : $this->maquinaria_seleccionada->cantidad_disponible;

            $this->cantidad_a_transferir = min(1, $cantidadDisponible);
        }
        
        // Para asignación
        if ($tipo === 'asignacion') {
            $depositosConStock = [];
            foreach ($depositosAccesibles as $depId) {
                if ($this->maquinaria_seleccionada->getCantidadEnDeposito($depId) > 0) {
                    $depositosConStock[] = $depId;
                }
            }
            
            $this->depositos_disponibles = Deposito::whereIn('id', $depositosConStock)
                ->orderBy('deposito')
                ->get();
            
            if (count($depositosConStock) === 1) {
                $this->id_deposito_origen = $depositosConStock[0];
                $this->cantidad_a_asignar = min(1, $this->maquinaria_seleccionada->getCantidadEnDeposito($this->id_deposito_origen));
            } else {
                $this->cantidad_a_asignar = 1;
            }
        }
        
        // ✅ Para carga de stock
        if ($tipo === 'carga_stock') {
            // Cargar TODOS los depósitos accesibles
            $this->depositos_disponibles = Deposito::whereIn('id', $depositosAccesibles)
                ->orderBy('deposito')
                ->get();
            
            $this->cantidad_a_cargar = 1;
        }
        
        $this->paso_actual = 3;
    }

    public function updatedIdDepositoOrigen()
    {
        if ($this->tipo_movimiento === 'asignacion' && $this->maquinaria_seleccionada && $this->id_deposito_origen) {
            $cantidadDisponible = $this->maquinaria_seleccionada->getCantidadEnDeposito($this->id_deposito_origen);
            $this->cantidad_a_asignar = min(1, $cantidadDisponible);
        }
    }

    // Volver al paso anterior
    public function volverPaso()
    {
        if ($this->paso_actual > 1) {
            $this->paso_actual--;
            
            if ($this->paso_actual === 1) {
                $this->maquinaria_seleccionada = null;
                $this->tipo_movimiento = '';
                $this->tipos_movimiento_disponibles = [];
            } elseif ($this->paso_actual === 2) {
                if ($this->maquinaria_id) {
                    $this->maquinaria_seleccionada = Maquinaria::with(['categoriaMaquinaria', 'deposito'])
                        ->find($this->maquinaria_id);
                    $this->tipos_movimiento_disponibles = $this->calcularTiposDisponibles();
                }
                $this->tipo_movimiento = '';
                $this->id_deposito_destino = '';
                $this->fecha_devolucion_esperada = '';
            }
        }
    }

    // PASO 3: Guardar movimiento
    public function guardar()
    {
        try {
            $this->validate();

            switch ($this->tipo_movimiento) {
                case 'carga_stock':
                    return $this->guardarCargaStock();
                case 'asignacion':
                    return $this->guardarAsignacion();
                case 'transferencia':
                    return $this->guardarTransferencia();
                case 'devolucion':
                    return $this->guardarDevolucion();
                case 'mantenimiento':
                    return $this->guardarMantenimiento();
                default:
                    session()->flash('error', 'Tipo de movimiento no válido.');
                    $this->cerrarModal();
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al procesar el movimiento: ' . $e->getMessage());
            $this->cerrarModal();
            \Log::error('Error en guardar movimiento: ' . $e->getMessage());
        }
    }

    private function guardarCargaStock()
    {
        try {
            DB::beginTransaction();

            $tipoMovimiento = TipoMovimiento::firstOrCreate([
                'tipo_movimiento' => 'Carga de Stock Maquinaria',
                'tipo' => 'I'
            ]);

            $maquinaria = Maquinaria::find($this->maquinaria_id);
            
            if (!$maquinaria) {
                throw new \Exception('La maquinaria no se encontró.');
            }

            // ✅ Crear movimiento de entrada
            MovimientoMaquinaria::create([
                'id_maquinaria' => $maquinaria->id,
                'cantidad' => $this->cantidad_a_cargar,
                'id_tipo_movimiento' => $tipoMovimiento->id,
                'fecha' => now(),
                'fecha_devolucion' => null,
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $this->id_deposito_origen,
                'id_referencia' => 0,
                'tipo_referencia' => 'deposito',
            ]);

            // ✅ NUEVO: Actualizar el campo cantidad
            $this->actualizarCantidadMaquinaria($maquinaria->id);

            DB::commit();
            
            $deposito = Deposito::find($this->id_deposito_origen);
            $mensaje = "Carga de stock realizada exitosamente: {$this->cantidad_a_cargar} " . 
                    ($this->cantidad_a_cargar == 1 ? 'unidad' : 'unidades') . 
                    " de {$maquinaria->maquinaria} en {$deposito->deposito}";
            
            session()->flash('message', $mensaje);
            \Illuminate\Support\Facades\Cache::flush();
            $this->cerrarModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al realizar la carga de stock: ' . $e->getMessage());
            $this->cerrarModal();
            \Log::error('Error en guardarCargaStock: ' . $e->getMessage());
        }
    }

    private function guardarAsignacion()
    {
        try {
            DB::beginTransaction();

            $tipoMovimiento = TipoMovimiento::firstOrCreate([
                'tipo_movimiento' => 'Asignación Maquinaria',
                'tipo' => 'E'
            ]);

            $maquinaria = Maquinaria::find($this->maquinaria_id);
            
            if (!$maquinaria) {
                throw new \Exception('La maquinaria no se encontró.');
            }

            $stockDisponible = $maquinaria->getCantidadEnDeposito($this->id_deposito_origen);
            
            if ($stockDisponible < $this->cantidad_a_asignar) {
                throw new \Exception("Solo hay {$stockDisponible} unidades disponibles en el depósito seleccionado.");
            }

            MovimientoMaquinaria::create([
                'id_maquinaria' => $maquinaria->id,
                'cantidad' => $this->cantidad_a_asignar,
                'id_tipo_movimiento' => $tipoMovimiento->id,
                'fecha' => now(),
                'fecha_devolucion' => $this->fecha_devolucion_esperada,
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $this->id_deposito_origen,
                'id_referencia' => 0,
                'tipo_referencia' => 'empleado',
            ]);

            // ✅ NUEVO: Actualizar el campo cantidad
            $this->actualizarCantidadMaquinaria($maquinaria->id);

            DB::commit();
            
            $deposito = Deposito::find($this->id_deposito_origen);
            $mensaje = "Asignación realizada exitosamente: {$this->cantidad_a_asignar} " . 
                    ($this->cantidad_a_asignar == 1 ? 'unidad' : 'unidades') . 
                    " de {$maquinaria->maquinaria} desde {$deposito->deposito}";
            
            session()->flash('message', $mensaje);
            \Illuminate\Support\Facades\Cache::flush();
            $this->cerrarModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al realizar la asignación: ' . $e->getMessage());
            $this->cerrarModal();
            \Log::error('Error en guardarAsignacion: ' . $e->getMessage());
        }
    }

    private function guardarTransferencia()
    {
        try {
            $maquinaria = Maquinaria::find($this->maquinaria_id);
            
            if (!$maquinaria) {
                throw new \Exception('No se encontró la maquinaria seleccionada.');
            }

            $deposito_origen_id = $maquinaria->id_deposito;
            $deposito_destino_id = $this->id_deposito_destino;

            if ($deposito_origen_id == $deposito_destino_id) {
                throw new \Exception('El depósito destino debe ser diferente al depósito origen.');
            }

            $cantidadDisponibleOrigen = $maquinaria->getCantidadEnDeposito($deposito_origen_id);
            
            if ($this->cantidad_a_transferir > $cantidadDisponibleOrigen) {
                throw new \Exception("Solo hay {$cantidadDisponibleOrigen} unidades disponibles en el depósito origen.");
            }

            DB::beginTransaction();

            $maquinariaOrigen = Maquinaria::where('maquinaria', $maquinaria->maquinaria)
                ->where('id_deposito', $deposito_origen_id)
                ->where('id_categoria_maquinaria', $maquinaria->id_categoria_maquinaria)
                ->first();

            if (!$maquinariaOrigen) {
                throw new \Exception('No se encontró el registro de origen en la base de datos.');
            }

            $cantidadActualOrigen = $maquinariaOrigen->getCantidadEnDeposito($deposito_origen_id);
            
            if ($this->cantidad_a_transferir > $cantidadActualOrigen) {
                throw new \Exception("Error de consistencia: solo hay {$cantidadActualOrigen} unidades disponibles.");
            }

            $maquinariaDestino = Maquinaria::where('maquinaria', $maquinaria->maquinaria)
                ->where('id_deposito', $deposito_destino_id)
                ->where('id_categoria_maquinaria', $maquinaria->id_categoria_maquinaria)
                ->first();

            $esNuevoRegistro = false;

            if (!$maquinariaDestino) {
                $maquinariaDestino = Maquinaria::create([
                    'maquinaria' => $maquinaria->maquinaria,
                    'id_categoria_maquinaria' => $maquinaria->id_categoria_maquinaria,
                    'id_deposito' => $deposito_destino_id,
                    'estado' => 'disponible',
                    'cantidad' => 0, // Se actualizará después del movimiento
                    'descripcion' => $maquinaria->descripcion,
                    'modelo' => $maquinaria->modelo,
                    'numero_serie' => null,
                    'anio_fabricacion' => $maquinaria->anio_fabricacion,
                ]);

                $esNuevoRegistro = true;
            }

            $tipoMovimientoSalida = TipoMovimiento::firstOrCreate([
                'tipo_movimiento' => 'Transferencia Salida Maquinaria',
                'tipo' => 'E'
            ]);

            $tipoMovimientoEntrada = TipoMovimiento::firstOrCreate([
                'tipo_movimiento' => 'Transferencia Entrada Maquinaria',
                'tipo' => 'I'
            ]);

            if ($esNuevoRegistro) {
                $tipoCargaInicial = TipoMovimiento::firstOrCreate([
                    'tipo_movimiento' => 'Carga Inicial Maquinaria',
                    'tipo' => 'I'
                ]);

                MovimientoMaquinaria::create([
                    'id_maquinaria' => $maquinariaDestino->id,
                    'cantidad' => $this->cantidad_a_transferir,
                    'id_tipo_movimiento' => $tipoCargaInicial->id,
                    'fecha' => now(),
                    'id_usuario' => Auth::id(),
                    'id_deposito_entrada' => $deposito_destino_id,
                    'id_referencia' => $maquinariaOrigen->id,
                    'tipo_referencia' => 'deposito',
                ]);
            } else {
                MovimientoMaquinaria::create([
                    'id_maquinaria' => $maquinariaDestino->id,
                    'cantidad' => $this->cantidad_a_transferir,
                    'id_tipo_movimiento' => $tipoMovimientoEntrada->id,
                    'fecha' => now(),
                    'id_usuario' => Auth::id(),
                    'id_deposito_entrada' => $deposito_destino_id,
                    'id_referencia' => $maquinariaOrigen->id,
                    'tipo_referencia' => 'deposito',
                ]);
            }

            MovimientoMaquinaria::create([
                'id_maquinaria' => $maquinariaOrigen->id,
                'cantidad' => $this->cantidad_a_transferir,
                'id_tipo_movimiento' => $tipoMovimientoSalida->id,
                'fecha' => now(),
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $deposito_origen_id,
                'id_referencia' => $maquinariaDestino->id,
                'tipo_referencia' => 'deposito',
            ]);

            // ✅ NUEVO: Actualizar cantidades de AMBAS maquinarias
            $this->actualizarCantidadMaquinaria($maquinariaOrigen->id);
            $this->actualizarCantidadMaquinaria($maquinariaDestino->id);

            DB::commit();

            $deposito_origen = Deposito::find($deposito_origen_id);
            $deposito_destino = Deposito::find($deposito_destino_id);
            
            $mensaje = "Transferencia realizada exitosamente: {$this->cantidad_a_transferir} " . 
                    ($this->cantidad_a_transferir == 1 ? 'unidad' : 'unidades') . 
                    " de {$maquinaria->maquinaria} desde {$deposito_origen->deposito} hacia {$deposito_destino->deposito}";
            
            session()->flash('message', $mensaje);
            \Illuminate\Support\Facades\Cache::flush();
            $this->cerrarModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al realizar la transferencia: ' . $e->getMessage());
            $this->cerrarModal();
            \Log::error('Error en guardarTransferencia: ' . $e->getMessage());
        }
    }

    private function guardarDevolucion()
    {
        try {
            DB::beginTransaction();

            $maquinaria = Maquinaria::find($this->maquinaria_id);
            
            if (!$maquinaria) {
                throw new \Exception('La maquinaria no se encontró.');
            }

            $ultimoMovimiento = MovimientoMaquinaria::where('id_maquinaria', $maquinaria->id)
                ->whereHas('tipoMovimiento', fn($q) => $q->where('tipo', 'E'))
                ->latest('fecha')
                ->first();

            if ($ultimoMovimiento && $ultimoMovimiento->tipo_referencia === 'mantenimiento') {
                $tipoMovimiento = TipoMovimiento::firstOrCreate([
                    'tipo_movimiento' => 'Retorno de Mantenimiento Maquinaria',
                    'tipo' => 'I'
                ]);
                $tipoReferencia = 'mantenimiento';
                $cantidadDevolver = 1;
                $depositoDestino = $ultimoMovimiento->id_deposito_entrada;
                $mensaje = "Retorno de mantenimiento realizado exitosamente: {$maquinaria->maquinaria}";
            } else {
                $tipoMovimiento = TipoMovimiento::firstOrCreate([
                    'tipo_movimiento' => 'Devolución Maquinaria',
                    'tipo' => 'I'
                ]);
                $tipoReferencia = 'empleado';
                
                $cantidadAsignada = MovimientoMaquinaria::where('id_maquinaria', $maquinaria->id)
                    ->whereHas('tipoMovimiento', function($q) {
                        $q->where('tipo_movimiento', 'Asignación Maquinaria');
                    })
                    ->sum('cantidad');
                
                $cantidadDevuelta = MovimientoMaquinaria::where('id_maquinaria', $maquinaria->id)
                    ->whereHas('tipoMovimiento', function($q) {
                        $q->where('tipo_movimiento', 'Devolución Maquinaria');
                    })
                    ->sum('cantidad');
                
                $cantidadPendiente = $cantidadAsignada - $cantidadDevuelta;
                
                $cantidadDevolver = 1;
                
                if ($cantidadPendiente < $cantidadDevolver) {
                    throw new \Exception('No hay suficientes unidades asignadas para devolver.');
                }

                // Devolver al depósito de donde salió la última asignación
                $ultimaAsignacion = MovimientoMaquinaria::where('id_maquinaria', $maquinaria->id)
                    ->whereHas('tipoMovimiento', function($q) {
                        $q->where('tipo_movimiento', 'Asignación Maquinaria');
                    })
                    ->latest('fecha')
                    ->first();
                
                $depositoDestino = $ultimaAsignacion ? $ultimaAsignacion->id_deposito_entrada : $maquinaria->id_deposito;
                
                $mensaje = "Devolución realizada exitosamente: {$cantidadDevolver} " .
                        ($cantidadDevolver == 1 ? 'unidad' : 'unidades') .
                        " de {$maquinaria->maquinaria}";
            }

            MovimientoMaquinaria::create([
                'id_maquinaria' => $maquinaria->id,
                'cantidad' => $cantidadDevolver,
                'id_tipo_movimiento' => $tipoMovimiento->id,
                'fecha' => now(),
                'fecha_devolucion' => null,
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $depositoDestino,
                'id_referencia' => 0,
                'tipo_referencia' => $tipoReferencia,
            ]);

            // ✅ NUEVO: Actualizar el campo cantidad
            $this->actualizarCantidadMaquinaria($maquinaria->id);

            DB::commit();
            session()->flash('message', $mensaje);
            \Illuminate\Support\Facades\Cache::flush();
            $this->cerrarModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al realizar la devolución: ' . $e->getMessage());
            $this->cerrarModal();
            \Log::error('Error en guardarDevolucion: ' . $e->getMessage());
        }
    }

    private function guardarMantenimiento()
    {
        try {
            DB::beginTransaction();

            $tipoMovimiento = TipoMovimiento::firstOrCreate([
                'tipo_movimiento' => 'Mantenimiento Maquinaria',
                'tipo' => 'E'
            ]);

            $maquinaria = Maquinaria::find($this->maquinaria_id);
            
            if (!$maquinaria) {
                throw new \Exception('No se encontró la maquinaria seleccionada.');
            }

            MovimientoMaquinaria::create([
                'id_maquinaria' => $maquinaria->id,
                'cantidad' => 1,
                'id_tipo_movimiento' => $tipoMovimiento->id,
                'fecha' => now(),
                'fecha_devolucion' => null,
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $maquinaria->id_deposito,
                'id_referencia' => 0,
                'tipo_referencia' => 'mantenimiento',
            ]);

            // ✅ NUEVO: Actualizar el campo cantidad
            $this->actualizarCantidadMaquinaria($maquinaria->id);

            DB::commit();
            session()->flash('message', "Maquinaria enviada a mantenimiento exitosamente: {$maquinaria->maquinaria}");
            \Illuminate\Support\Facades\Cache::flush();
            $this->cerrarModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al enviar a mantenimiento: ' . $e->getMessage());
            $this->cerrarModal();
            \Log::error('Error en guardarMantenimiento: ' . $e->getMessage());
        }
    }

    private function actualizarCantidadMaquinaria($maquinariaId)
    {
        $maquinaria = Maquinaria::find($maquinariaId);
        
        if (!$maquinaria) {
            return;
        }

        // Calcular la cantidad real basada en movimientos
        $cantidadCalculada = $maquinaria->getCantidadEnDeposito($maquinaria->id_deposito);
        
        // Actualizar el campo cantidad en la base de datos
        DB::table('maquinarias')
            ->where('id', $maquinariaId)
            ->update(['cantidad' => $cantidadCalculada]);
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->paso_actual = 1;
        $this->maquinaria_seleccionada = null;
        $this->maquinaria_id = null;
        $this->tipo_movimiento = '';
        $this->id_deposito_destino = '';
        $this->id_deposito_origen = '';
        $this->observaciones = '';
        $this->fecha_devolucion_esperada = '';
        $this->cantidad_a_transferir = 1;
        $this->cantidad_a_asignar = 1;
        $this->cantidad_a_cargar = 1; 
        $this->search_maquinaria = '';
        $this->mostrar_lista = false;
        $this->depositos_disponibles = [];
        $this->tipos_movimiento_disponibles = [];
        $this->resetErrorBag();
    }
}