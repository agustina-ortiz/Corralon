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
    
    protected $updatesQueryString = [
        'search' => ['except' => ''],
        'filtro_fecha_desde' => ['except' => ''],
        'filtro_fecha_hasta' => ['except' => ''],
        'filtro_deposito_origen' => ['except' => ''],
        'filtro_usuario' => ['except' => ''],
        'filtro_insumo' => ['except' => ''],
        'filtro_categoria' => ['except' => ''],
        'filtro_tipo_movimiento' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public $search = '';
    public $showModal = false;
    public $showModalTransferencia = false;
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
    
    // Pasos del modal de movimientos individuales
    public $paso_actual = 1;
    
    // Insumo seleccionado (para movimientos individuales)
    public $insumo_seleccionado = null;
    public $insumo_id = null;
    
    // Tipo de movimiento seleccionado
    public $tipo_movimiento = '';
    
    // Campos del formulario (movimientos individuales)
    public $cantidad = '';
    public $observaciones = '';
    
    // Búsqueda de insumos
    public $search_insumo = '';
    public $mostrar_lista = false;
    
    // Para transferencias múltiples
    public $id_deposito_origen = '';
    public $id_deposito_destino = '';
    public $insumos_transferencia = [];
    public $search_insumo_transferencia = '';
    public $mostrar_lista_transferencia = false; // Controlador de visibilidad de la lista
    public $observaciones_transferencia = '';
    
    // Para expandir/colapsar movimientos
    public $movimientos_expandidos = [];
    
    // Datos auxiliares
    public $depositos_disponibles = [];
    public $tipos_movimiento_disponibles = [];

    public function mount() {
        $this->filtro_fecha_desde = today()->subMonth()->format('Y-m-d');
        $this->filtro_fecha_hasta = today()->format('Y-m-d');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFiltroFechaDesde()
    {
        $this->resetPage();
    }

    public function updatedFiltroFechaHasta()
    {
        $this->resetPage();
    }

    public function updatedFiltroDepositoOrigen()
    {
        $this->resetPage();
    }

    public function updatedFiltroUsuario()
    {
        $this->resetPage();
    }

    public function updatedFiltroInsumo()
    {
        $this->resetPage();
    }

    public function updatedFiltroCategoria()
    {
        $this->resetPage();
    }

    public function updatedFiltroTipoMovimiento()
    {
        $this->resetPage();
    }

    protected function rules()
    {
        if ($this->showModalTransferencia) {
            return [
                'id_deposito_origen' => 'required|exists:depositos,id',
                'id_deposito_destino' => 'required|exists:depositos,id|different:id_deposito_origen',
                'insumos_transferencia' => 'required|array|min:1',
                'insumos_transferencia.*.cantidad' => 'required|numeric|min:0.01',
                'observaciones_transferencia' => 'nullable|string|max:500',
            ];
        }

        $rules = [
            'cantidad' => 'required|numeric|min:0.01',
            'observaciones' => 'nullable|string|max:500',
        ];

        return $rules;
    }

    protected $messages = [
        'cantidad.required' => 'La cantidad es obligatoria.',
        'cantidad.min' => 'La cantidad debe ser mayor a 0.',
        'id_deposito_origen.required' => 'Debe seleccionar un depósito de origen.',
        'id_deposito_destino.required' => 'Debe seleccionar un depósito destino.',
        'insumos_transferencia.required' => 'Debe seleccionar al menos un insumo.',
        'insumos_transferencia.min' => 'Debe seleccionar al menos un insumo.',
        'insumos_transferencia.*.cantidad.required' => 'La cantidad es obligatoria.',
        'insumos_transferencia.*.cantidad.min' => 'La cantidad debe ser mayor a 0.',
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

        // Obtener transferencias agrupadas
        $transferencias = MovimientoEncabezado::with([
                'depositoOrigen',
                'depositoDestino',
                'usuario',
                'movimientos.insumo.categoriaInsumo',
                'movimientos.tipoMovimiento'
            ])
            ->where(function($query) use ($depositosAccesibles) {
                $query->whereHas('depositoOrigen', function($q) use ($depositosAccesibles) {
                    $q->whereIn('id', $depositosAccesibles);
                })
                ->orWhereHas('depositoDestino', function($q) use ($depositosAccesibles) {
                    $q->whereIn('id', $depositosAccesibles);
                });
            })
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->whereHas('depositoOrigen', function($dep) {
                        $dep->where('deposito', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('depositoDestino', function($dep) {
                        $dep->where('deposito', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('movimientos.insumo', function($ins) {
                        $ins->where('insumo', 'like', '%' . $this->search . '%');
                    });
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
            ->when($this->filtro_tipo_movimiento, function($query) {
                $query->whereHas('movimientos', function($q) {
                    $q->where('id_tipo_movimiento', $this->filtro_tipo_movimiento);
                });
            })
            ->get();

        // Obtener movimientos individuales
        $movimientosIndividuales = MovimientoInsumo::with([
                'insumo.categoriaInsumo',
                'insumo.deposito',
                'tipoMovimiento',
                'usuario'
            ])
            ->whereNull('id_movimiento_encabezado')
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
            ->get();

        // Combinar y ordenar
        $movimientosCombinados = collect();
        
        foreach ($transferencias as $transferencia) {
            $movimientosCombinados->push([
                'tipo' => 'transferencia',
                'data' => $transferencia,
                'fecha' => $transferencia->fecha,
                'created_at' => $transferencia->created_at,
            ]);
        }
        
        foreach ($movimientosIndividuales as $movimiento) {
            $movimientosCombinados->push([
                'tipo' => 'individual',
                'data' => $movimiento,
                'fecha' => $movimiento->fecha,
                'created_at' => $movimiento->created_at,
            ]);
        }
        
        $movimientosCombinados = $movimientosCombinados->sortByDesc(function($item) {
            return $item['created_at'];
        });

        // ✅ CORREGIDO: Paginación manual mejorada
        $currentPage = $this->getPage();
        $perPage = 15;
        
        $movimientosPaginados = new \Illuminate\Pagination\LengthAwarePaginator(
            $movimientosCombinados->forPage($currentPage, $perPage),
            $movimientosCombinados->count(),
            $perPage,
            $currentPage,
            [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );

        // Insumos filtrados para el listado
        $insumos_filtrados = collect();
        
        if ($this->paso_actual === 1 && ($this->mostrar_lista || $this->search_insumo)) {
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
                ->get(['id', 'insumo', 'id_categoria', 'id_deposito', 'stock_actual', 'stock_minimo', 'unidad']);
        }

        // Insumos disponibles para transferencia
        $insumos_disponibles_transferencia = collect();
        
        if ($this->showModalTransferencia && $this->id_deposito_origen && ($this->mostrar_lista_transferencia || $this->search_insumo_transferencia)) {
            $insumos_disponibles_transferencia = Insumo::with(['categoriaInsumo', 'deposito'])
                ->where('id_deposito', $this->id_deposito_origen)
                ->where('stock_actual', '>', 0)
                ->when($this->search_insumo_transferencia, function($query) {
                    $query->where(function($q) {
                        $q->where('insumo', 'like', '%' . $this->search_insumo_transferencia . '%')
                        ->orWhereHas('categoriaInsumo', function($cat) {
                            $cat->where('nombre', 'like', '%' . $this->search_insumo_transferencia . '%');
                        });
                    });
                })
                ->orderBy('insumo')
                ->limit(50)
                ->get(['id', 'insumo', 'id_categoria', 'id_deposito', 'stock_actual', 'stock_minimo', 'unidad']);
        }

        $depositos = Deposito::whereIn('id', $depositosAccesibles)
            ->orderBy('deposito')
            ->get();
            
        $categorias = CategoriaInsumo::orderBy('nombre')->get();
        $usuarios = User::orderBy('name')->get();
        $tipos_movimiento = TipoMovimiento::orderBy('tipo_movimiento')->get();

        return view('livewire.transferencias-insumos', [
            'movimientos' => $movimientosPaginados,
            'insumos_filtrados' => $insumos_filtrados,
            'insumos_disponibles_transferencia' => $insumos_disponibles_transferencia,
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

    // Mostrar lista de transferencia al hacer focus
    public function mostrarListaTransferencia()
    {
        $this->mostrar_lista_transferencia = true;
    }

    public function updatedIdDepositoOrigen($value)
    {
        // Limpiar los insumos y búsqueda cuando cambia el depósito
        $this->insumos_transferencia = [];
        $this->search_insumo_transferencia = '';
        $this->mostrar_lista_transferencia = false;
        
        // Limpiar error de depósito origen
        $this->resetErrorBag('id_deposito_origen');
        
        // Si había seleccionado un depósito destino igual, limpiar ese error también
        if (!empty($value) && $value == $this->id_deposito_destino) {
            $this->addError('id_deposito_destino', 'El depósito destino debe ser diferente al de origen');
        } else {
            $this->resetErrorBag('id_deposito_destino');
        }
    }

    public function updatedIdDepositoDestino($value)
    {
        // Limpiar error anterior
        $this->resetErrorBag('id_deposito_destino');
        
        // Validar que no sea vacío
        if (empty($value)) {
            // No agregar error aquí, solo cuando intente guardar
            return;
        }
        
        // Validar que sea diferente al origen
        if (!empty($this->id_deposito_origen) && $value == $this->id_deposito_origen) {
            $this->addError('id_deposito_destino', 'El depósito destino debe ser diferente al de origen');
        }
    }

    public function crearTransferencia()
    {
        $this->resetFormTransferencia();
        $this->showModalTransferencia = true;
    }

    public function agregarInsumoTransferencia($insumoId)
    {
        $insumo = Insumo::with(['categoriaInsumo', 'deposito'])->find($insumoId);
        
        if (!$insumo || $insumo->id_deposito != $this->id_deposito_origen) {
            return;
        }

        $existe = collect($this->insumos_transferencia)->contains('id', $insumoId);
        
        if (!$existe) {
            $this->insumos_transferencia[] = [
                'id' => $insumo->id,
                'nombre' => $insumo->insumo,
                'categoria' => $insumo->categoriaInsumo->nombre,
                'stock_actual' => $insumo->stock_actual,
                'unidad' => $insumo->unidad,
                'cantidad' => '',
            ];
        }

        $this->search_insumo_transferencia = '';
        $this->mostrar_lista_transferencia = false;
    }

    public function removerInsumoTransferencia($index)
    {
        unset($this->insumos_transferencia[$index]);
        $this->insumos_transferencia = array_values($this->insumos_transferencia);
    }

    public function updatedInsumosTransferencia($value, $key)
    {
        // Validar que la cantidad no exceda el stock
        if (strpos($key, '.cantidad') !== false) {
            $index = explode('.', $key)[0];
            $cantidad = floatval($value);
            $stockDisponible = floatval($this->insumos_transferencia[$index]['stock_actual']);
            
            // No auto-ajustar, solo validar
            if ($cantidad > $stockDisponible) {
                // Agregar error específico para este campo
                $this->addError(
                    "insumos_transferencia.{$index}.cantidad",
                    "No puede transferir más de " . number_format($stockDisponible, 2) . " {$this->insumos_transferencia[$index]['unidad']} disponibles"
                );
            } else {
                // Limpiar el error si la cantidad es válida
                $this->resetErrorBag("insumos_transferencia.{$index}.cantidad");
            }
            
            // Validar que no sea negativa o cero
            if ($cantidad <= 0 && $cantidad != '') {
                $this->addError(
                    "insumos_transferencia.{$index}.cantidad",
                    "La cantidad debe ser mayor a 0"
                );
            }
        }
    }

    // Activar mostrar_lista_transferencia cuando cambia el search
    public function updatedSearchInsumoTransferencia()
    {
        $this->mostrar_lista_transferencia = true;
    }

    public function crear()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->paso_actual = 1;
    }

    public function seleccionarInsumo($insumoId)
    {
        $depositosAccesibles = $this->getDepositosAccesibles();
        
        $this->insumo_seleccionado = Insumo::with(['categoriaInsumo', 'deposito'])
            ->whereIn('id_deposito', $depositosAccesibles)
            ->find($insumoId);
        
        if (!$this->insumo_seleccionado) {
            session()->flash('error', 'No tiene acceso a este insumo.');
            return;
        }
        
        $this->insumo_seleccionado->refresh();
        
        $this->insumo_id = $insumoId;
        $this->tipos_movimiento_disponibles = $this->calcularTiposDisponibles();
        $this->paso_actual = 2;
        $this->search_insumo = '';
        $this->mostrar_lista = false;
    }

    public function seleccionarTipoMovimiento($tipo)
    {
        $this->tipo_movimiento = $tipo;
        $this->paso_actual = 3;
    }

    public function volverPaso()
    {
        if ($this->paso_actual > 1) {
            $this->paso_actual--;
            
            if ($this->paso_actual === 1) {
                $this->insumo_seleccionado = null;
                $this->tipo_movimiento = '';
                $this->tipos_movimiento_disponibles = [];
            } elseif ($this->paso_actual === 2) {
                if ($this->insumo_id) {
                    $this->insumo_seleccionado = Insumo::with(['categoriaInsumo', 'deposito'])
                        ->find($this->insumo_id);
                    $this->tipos_movimiento_disponibles = $this->calcularTiposDisponibles();
                }
                $this->tipo_movimiento = '';
                $this->cantidad = '';
            }
        }
    }

    public function guardar()
    {
        try {
            $this->validate();

            switch ($this->tipo_movimiento) {
                case 'carga':
                    return $this->guardarCarga();
                case 'ajuste_positivo':
                    return $this->guardarAjustePositivo();
                case 'ajuste_negativo':
                    return $this->guardarAjusteNegativo();
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

    public function guardarTransferencia()
    {
        try {
            // Validar depósitos antes que nada
            if (empty($this->id_deposito_origen)) {
                $this->addError('id_deposito_origen', 'Debe seleccionar un depósito de origen');
                session()->flash('error', 'Debe seleccionar un depósito de origen');
                return;
            }

            if (empty($this->id_deposito_destino)) {
                $this->addError('id_deposito_destino', 'Debe seleccionar un depósito de destino');
                session()->flash('error', 'Debe seleccionar un depósito de destino');
                return;
            }

            if ($this->id_deposito_origen == $this->id_deposito_destino) {
                $this->addError('id_deposito_destino', 'El depósito destino debe ser diferente al de origen');
                session()->flash('error', 'El depósito destino debe ser diferente al de origen');
                return;
            }

            // Validar que haya insumos seleccionados
            if (empty($this->insumos_transferencia) || count($this->insumos_transferencia) === 0) {
                $this->addError('insumos_transferencia', 'Debe seleccionar al menos un insumo para transferir');
                session()->flash('error', 'Debe seleccionar al menos un insumo para transferir');
                return;
            }

            // Verificar si hay errores de validación en tiempo real
            if ($this->getErrorBag()->isNotEmpty()) {
                session()->flash('error', 'Por favor corrija los errores antes de continuar.');
                return;
            }

            // Validación personalizada de cantidades
            foreach ($this->insumos_transferencia as $index => $item) {
                if (empty($item['cantidad']) || floatval($item['cantidad']) <= 0) {
                    $this->addError(
                        "insumos_transferencia.{$index}.cantidad",
                        "Debe ingresar una cantidad válida"
                    );
                    session()->flash('error', "Debe ingresar una cantidad válida para {$item['nombre']}");
                    return;
                }
                
                if (floatval($item['cantidad']) > floatval($item['stock_actual'])) {
                    $this->addError(
                        "insumos_transferencia.{$index}.cantidad",
                        "No puede transferir más de " . number_format($item['stock_actual'], 2) . " {$item['unidad']} disponibles"
                    );
                    session()->flash('error', "La cantidad de {$item['nombre']} excede el stock disponible");
                    return;
                }
            }
            
            $this->validate();

            DB::beginTransaction();

            $tipoMovimientoSalida = TipoMovimiento::firstOrCreate([
                'tipo_movimiento' => 'Transferencia Salida',
                'tipo' => 'E'
            ]);

            $tipoMovimientoEntrada = TipoMovimiento::firstOrCreate([
                'tipo_movimiento' => 'Transferencia Entrada',
                'tipo' => 'I'
            ]);

            $encabezado = MovimientoEncabezado::create([
                'fecha' => now(),
                'id_deposito_origen' => $this->id_deposito_origen,
                'id_deposito_destino' => $this->id_deposito_destino,
                'observaciones' => $this->observaciones_transferencia,
                'id_usuario' => Auth::id(),
            ]);

            $totalInsumos = 0;
            $detalleInsumos = [];

            // Procesar cada insumo
            foreach ($this->insumos_transferencia as $item) {
                // Refrescar el insumo desde la base de datos para tener el stock actualizado
                $insumo = Insumo::find($item['id']);
                
                if (!$insumo || $insumo->id_deposito != $this->id_deposito_origen) {
                    throw new \Exception("Insumo {$item['nombre']} no válido o ya no pertenece al depósito origen.");
                }

                $cantidad = floatval($item['cantidad']);

                if ($cantidad <= 0) {
                    throw new \Exception("La cantidad para {$item['nombre']} debe ser mayor a 0.");
                }

                // Validación crítica: verificar stock actual de la BD
                if ($cantidad > $insumo->stock_actual) {
                    throw new \Exception("La cantidad de {$item['nombre']} (" . number_format($cantidad, 2) . " {$insumo->unidad}) excede el stock disponible actual (" . number_format($insumo->stock_actual, 2) . " {$insumo->unidad}). El stock pudo haber cambiado.");
                }

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

                // Movimiento de SALIDA (origen)
                MovimientoInsumo::create([
                    'id_insumo' => $insumo->id,
                    'id_movimiento_encabezado' => $encabezado->id,
                    'id_tipo_movimiento' => $tipoMovimientoSalida->id,
                    'cantidad' => $cantidad,
                    'fecha' => now(),
                    'fecha_devolucion' => null,
                    'id_usuario' => Auth::id(),
                    'id_deposito_entrada' => $this->id_deposito_origen,
                    'id_referencia' => $insumo_destino->id,
                    'tipo_referencia' => 'transferencia',
                ]);

                // Movimiento de ENTRADA (destino)
                MovimientoInsumo::create([
                    'id_insumo' => $insumo_destino->id,
                    'id_movimiento_encabezado' => $encabezado->id,
                    'id_tipo_movimiento' => $tipoMovimientoEntrada->id,
                    'cantidad' => $cantidad,
                    'fecha' => now(),
                    'fecha_devolucion' => null,
                    'id_usuario' => Auth::id(),
                    'id_deposito_entrada' => $this->id_deposito_destino,
                    'id_referencia' => $insumo->id,
                    'tipo_referencia' => 'transferencia',
                ]);

                // Sincronizar stocks
                $insumo->sincronizarStock();
                $insumo_destino->sincronizarStock();

                $totalInsumos++;
                $detalleInsumos[] = "{$cantidad} {$insumo->unidad} de {$insumo->insumo}";
            }

            DB::commit();

            $depositoOrigen = Deposito::find($this->id_deposito_origen);
            $depositoDestino = Deposito::find($this->id_deposito_destino);

            $mensaje = "Transferencia realizada exitosamente: {$totalInsumos} insumo(s) desde {$depositoOrigen->deposito} hacia {$depositoDestino->deposito}";
            session()->flash('message', $mensaje);
            
            $this->cerrarModalTransferencia();

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al realizar la transferencia: ' . $e->getMessage());
            \Log::error('Error en guardarTransferencia múltiple: ' . $e->getMessage());
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
            
            \Illuminate\Support\Facades\Cache::flush();
            
            $this->cerrarModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al realizar la carga: ' . $e->getMessage());
            $this->cerrarModal();
            \Log::error('Error en guardarCarga: ' . $e->getMessage());
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
                'tipo_referencia' => 'inventario',
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
                'tipo_referencia' => 'inventario',
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
        $this->dispatch('refreshComponent');
    }

    public function cerrarModalTransferencia()
    {
        $this->showModalTransferencia = false;
        $this->resetFormTransferencia();
        $this->dispatch('refreshComponent');
    }

    private function resetForm()
    {
        $this->paso_actual = 1;
        $this->insumo_seleccionado = null;
        $this->insumo_id = null;
        $this->tipo_movimiento = '';
        $this->cantidad = '';
        $this->observaciones = '';
        $this->search_insumo = '';
        $this->mostrar_lista = false;
        $this->depositos_disponibles = [];
        $this->tipos_movimiento_disponibles = [];
        $this->resetErrorBag();
    }

    private function resetFormTransferencia()
    {
        $this->id_deposito_origen = '';
        $this->id_deposito_destino = '';
        $this->insumos_transferencia = [];
        $this->search_insumo_transferencia = '';
        $this->mostrar_lista_transferencia = false;
        $this->observaciones_transferencia = '';
        $this->resetErrorBag();
    }
}