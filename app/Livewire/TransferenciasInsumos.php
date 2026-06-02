<?php
// app/Livewire/TransferenciasInsumos.php

namespace App\Livewire;

use App\Models\Insumo;
use App\Models\Deposito;
use App\Models\MovimientoInsumo;
use App\Models\MovimientoEncabezado;
use App\Models\TipoMovimiento;
use App\Models\CategoriaInsumo;
use App\Models\Vehiculo;
use App\Models\Evento;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\ComprobanteMovimiento;
use App\Models\EmpleadoMunicipal;
use App\Models\Corralon;
use App\Models\Secretaria;
use App\Models\Area;

class TransferenciasInsumos extends Component
{
    use WithPagination, WithFileUploads;

    protected $listeners = ['refreshComponent' => '$refresh'];
    
    protected $updatesQueryString = [
        'search' => ['except' => ''],
        'filtro_corralon' => ['except' => ''],
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
    public $filtro_corralon = '';
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
    public $nro_orden_compra = '';
    public $observaciones = '';
    public $comprobantes = [];
    
    // Campos para asignaciones (vehículo/evento)
    public $tipo_destino = ''; // 'vehiculo', 'evento' o 'empleado'
    public $id_referencia = '';
    public $search_destino = '';
    public $mostrar_lista_destino = false;

    // Campos para Ajuste Negativo (secretaría y área)
    public $id_secretaria_ajuste = '';
    public $area_ajuste = '';
    public $areas_disponibles = [];

    // Búsqueda de insumos
    public $search_insumo = '';
    public $mostrar_lista = false;
    
    // Para transferencias múltiples
    public $id_corralon_origen = '';
    public $id_corralon_destino = '';
    public $id_deposito_origen = '';
    public $id_deposito_destino = '';
    public $insumos_transferencia = [];
    public $search_insumo_transferencia = '';
    public $mostrar_lista_transferencia = false;
    public $observaciones_transferencia = '';
    
    // Para expandir/colapsar movimientos
    public $movimientos_expandidos = [];
    
    // Panel de asignaciones pendientes
    public $showAsignacionesPendientes = false;

    // Datos auxiliares
    public $depositos_disponibles = [];
    public $tipos_movimiento_disponibles = [];

    public function mount()
    {
        // Verificar que el usuario esté autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $this->filtro_fecha_desde = today()->subMonth()->format('Y-m-d');
        $this->filtro_fecha_hasta = today()->format('Y-m-d');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFiltroCorralon()
    {
        $this->filtro_deposito_origen = '';
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

        if (in_array($this->tipo_movimiento, ['carga', 'ajuste_positivo'])) {
            $rules['nro_orden_compra'] = 'nullable|string|max:100';
            $rules['comprobantes'] = 'nullable|array|max:5';
            $rules['comprobantes.*'] = 'file|mimes:pdf,jpg,jpeg,png|max:5120';
        }

        if (in_array($this->tipo_movimiento, ['asignacion_con_reposicion', 'asignacion_sin_reposicion', 'entrada_reposicion'])) {
            $tiposDestinoPermitidos = $this->tipo_movimiento === 'asignacion_sin_reposicion'
                ? 'vehiculo,evento'
                : 'vehiculo,evento,empleado';
            $rules['tipo_destino'] = 'required|in:' . $tiposDestinoPermitidos;
            $rules['id_referencia'] = 'required';
        }

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
        'tipo_destino.required' => 'Debe seleccionar el tipo de destino (Vehículo, Evento o Empleado).',
        'id_referencia.required' => 'Debe seleccionar un vehículo, evento o empleado.',
        'comprobantes.max' => 'Puede adjuntar un máximo de 5 archivos.',
        'comprobantes.*.mimes' => 'Solo se permiten archivos PDF, JPG o PNG.',
        'comprobantes.*.max' => 'Cada archivo no debe superar los 5 MB.',
    ];

    /**
     * Obtiene los IDs de depósitos accesibles por el usuario actual
     */
    private function getDepositosAccesibles()
    {
        $user = Auth::user();
        return $user->getDepositosPermitidosParaModulo('movimientos_insumos');
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
            $tipos[] = [
                'key' => 'asignacion_con_reposicion',
                'nombre' => 'Asignación con Reposición',
                'descripcion' => 'Asignar insumo a un vehículo o evento (con devolución posterior)',
                'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
                'color' => 'orange',
                'disponible' => true
            ];
            $tipos[] = [
                'key' => 'asignacion_sin_reposicion',
                'nombre' => 'Asignación sin Reposición',
                'descripcion' => 'Asignar insumo a un vehículo o evento (sin devolución)',
                'icon' => 'M17 8l4 4m0 0l-4 4m4-4H3',
                'color' => 'red',
                'disponible' => true
            ];
        }

        $tipos[] = [
            'key' => 'entrada_reposicion',
            'nombre' => 'Entrada Reposición',
            'descripcion' => 'Devolver insumos previamente asignados a un vehículo o evento',
            'icon' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6',
            'color' => 'teal',
            'disponible' => true
        ];

        return $tipos;
    }

    public function render()
    {
        $user = auth()->user();
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
            ->when($this->filtro_corralon, function($query) {
                $query->where(function($q) {
                    $q->whereHas('depositoOrigen', function($dep) {
                        $dep->where('id_corralon', $this->filtro_corralon);
                    })
                    ->orWhereHas('depositoDestino', function($dep) {
                        $dep->where('id_corralon', $this->filtro_corralon);
                    });
                });
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
                'usuario',
                'comprobantes',
                'secretaria'
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
            ->when($this->filtro_corralon, function($query) {
                $query->whereHas('insumo.deposito', function($q) {
                    $q->where('id_corralon', $this->filtro_corralon);
                });
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

        // Vehículos, eventos y empleados para asignaciones
        $vehiculos_destino = collect();
        $eventos_destino = collect();
        $empleados_destino = collect();
        if ($this->showModal && in_array($this->tipo_movimiento, ['asignacion_con_reposicion', 'asignacion_sin_reposicion', 'entrada_reposicion'])) {
            if ($this->tipo_destino === 'vehiculo') {
                $vehiculos_destino = Vehiculo::when($this->search_destino, function($query) {
                        $query->where(function($q) {
                            $q->where('vehiculo', 'like', '%' . $this->search_destino . '%')
                              ->orWhere('patente', 'like', '%' . $this->search_destino . '%')
                              ->orWhere('marca_modelo', 'like', '%' . $this->search_destino . '%')
                              ->orWhere('nro_patrimonio', 'like', '%' . $this->search_destino . '%');
                        });
                    })
                    ->orderBy('vehiculo')
                    ->limit(50)
                    ->get();
            } elseif ($this->tipo_destino === 'evento') {
                $eventos_destino = Evento::when($this->search_destino, function($query) {
                        $query->where('evento', 'like', '%' . $this->search_destino . '%');
                    })
                    ->orderBy('evento')
                    ->limit(50)
                    ->get();
            } elseif ($this->tipo_destino === 'empleado') {
                $empleados_destino = EmpleadoMunicipal::activos()
                    ->when($this->search_destino, function($query) {
                        $query->where(function($q) {
                            $q->where('NOMBRE', 'like', '%' . $this->search_destino . '%')
                              ->orWhere('LEGAJO', 'like', '%' . $this->search_destino . '%')
                              ->orWhere('DNI', 'like', '%' . $this->search_destino . '%');
                        });
                    })
                    ->orderBy('NOMBRE')
                    ->limit(50)
                    ->get();
            }
        }

        // Asignaciones pendientes de reposición
        $asignacionesPendientes = collect();
        if ($this->showAsignacionesPendientes) {
            $tipoConReposicion = TipoMovimiento::where('tipo_movimiento', 'Asignación con Reposición')->first();
            $tiposDescuento = TipoMovimiento::whereIn('tipo_movimiento', ['Entrada Reposición', 'Baja Reposición'])->pluck('id');

            if ($tipoConReposicion) {
                // Traer todos los movimientos relevantes ordenados cronológicamente
                $todosLosTipos = collect([$tipoConReposicion->id])->merge($tiposDescuento);
                $movsPendientes = MovimientoInsumo::whereIn('id_tipo_movimiento', $todosLosTipos)
                    ->whereHas('insumo', function($q) use ($depositosAccesibles) {
                        $q->whereIn('id_deposito', $depositosAccesibles);
                    })
                    ->orderBy('id')
                    ->get();

                // Agrupar por combinación y calcular balance cronológico (nunca baja de 0)
                $grupos = $movsPendientes->groupBy(fn($m) => "{$m->id_insumo}-{$m->tipo_referencia}-{$m->id_referencia}");

                foreach ($grupos as $key => $movimientosGrupo) {
                    $balance = 0;
                    foreach ($movimientosGrupo as $mov) {
                        if ($mov->id_tipo_movimiento == $tipoConReposicion->id) {
                            $balance += $mov->cantidad;
                        } else {
                            $balance = max(0, $balance - $mov->cantidad);
                        }
                    }

                    if ($balance > 0) {
                        $primer = $movimientosGrupo->first();
                        $insumo = Insumo::with(['categoriaInsumo', 'deposito'])->find($primer->id_insumo);
                        $referenciaNombre = '';

                        if ($primer->tipo_referencia === 'vehiculo') {
                            $referencia = Vehiculo::find($primer->id_referencia);
                            $referenciaNombre = $referencia ? "{$referencia->vehiculo} ({$referencia->patente})" : "Vehículo #{$primer->id_referencia}";
                        } elseif ($primer->tipo_referencia === 'evento') {
                            $referencia = Evento::find($primer->id_referencia);
                            $referenciaNombre = $referencia ? $referencia->evento : "Evento #{$primer->id_referencia}";
                        } elseif ($primer->tipo_referencia === 'empleado') {
                            $referencia = EmpleadoMunicipal::find($primer->id_referencia);
                            $referenciaNombre = $referencia ? "{$referencia->nombre_formateado} (Leg. {$referencia->LEGAJO})" : "Empleado #{$primer->id_referencia}";
                        }

                        $asignacionesPendientes->push([
                            'id_insumo' => $primer->id_insumo,
                            'insumo_nombre' => $insumo?->insumo ?? 'Desconocido',
                            'categoria' => $insumo?->categoriaInsumo?->nombre ?? '',
                            'deposito' => $insumo?->deposito?->deposito ?? '',
                            'unidad' => $insumo?->unidad ?? '',
                            'tipo_referencia' => $primer->tipo_referencia,
                            'id_referencia' => $primer->id_referencia,
                            'referencia_nombre' => $referenciaNombre,
                            'cantidad_pendiente' => $balance,
                        ]);
                    }
                }
            }
        }

        $depositos = Deposito::whereIn('id', $depositosAccesibles)
            ->when($this->filtro_corralon, fn($q) => $q->where('id_corralon', $this->filtro_corralon))
            ->orderBy('deposito')
            ->get();

        $categorias = CategoriaInsumo::orderBy('nombre')->get();
        $usuarios = User::orderBy('name')->get();
        $tipos_movimiento = TipoMovimiento::whereIn('tipo', ['I', 'IM'])->orderBy('tipo_movimiento')->get();

        // Determinar si el usuario tiene acceso a múltiples corralones
        $corralonesPermitidosIds = $user->getCorralonesParaModulo('movimientos_insumos');

        // Corralones para el filtro
        $corralonesFiltro = Corralon::when(!$user->esAdministrador(), fn($q) => $q->whereIn('id', $corralonesPermitidosIds))
            ->orderBy('descripcion')
            ->get();
        $tieneMultiplesCorralones = $user->esAdministrador() || count($corralonesPermitidosIds) > 1;

        $corralones = collect();
        if ($tieneMultiplesCorralones) {
            $corralones = \App\Models\Corralon::when(!$user->esAdministrador(), fn($q) => $q->whereIn('id', $corralonesPermitidosIds))
                ->orderBy('descripcion')->get();
        }

        // Depósitos Origen: filtrar por corralón seleccionado si el usuario tiene múltiples
        if ($tieneMultiplesCorralones && $this->id_corralon_origen) {
            $depositosOrigen = Deposito::whereIn('id', $depositosAccesibles)
                ->where('id_corralon', $this->id_corralon_origen)
                ->orderBy('deposito')
                ->get();
        } else {
            $depositosOrigen = Deposito::whereIn('id', $depositosAccesibles)
                ->orderBy('deposito')
                ->get();
        }

        // Depósitos Destino: filtrar por corralón destino seleccionado si aplica
        if ($tieneMultiplesCorralones && $this->id_corralon_destino) {
            $depositosDestino = Deposito::where('id_corralon', $this->id_corralon_destino)
                ->when($this->id_deposito_origen, function($query) {
                    $query->where('id', '!=', $this->id_deposito_origen);
                })
                ->orderBy('deposito')
                ->get();
        } else {
            $depositosDestino = Deposito::when($this->id_deposito_origen, function($query) {
                    $query->where('id', '!=', $this->id_deposito_origen);
                })
                ->orderBy('deposito')
                ->get();
        }

        $secretarias = Secretaria::orderBy('secretaria')->get();

        return view('livewire.transferencias-insumos', [
            'movimientos' => $movimientosPaginados,
            'insumos_filtrados' => $insumos_filtrados,
            'insumos_disponibles_transferencia' => $insumos_disponibles_transferencia,
            'depositos' => $depositos,
            'depositosOrigen' => $depositosOrigen,
            'depositosDestino' => $depositosDestino,
            'corralones' => $corralones,
            'corralonesFiltro' => $corralonesFiltro,
            'tieneMultiplesCorralones' => $tieneMultiplesCorralones,
            'categorias' => $categorias,
            'usuarios' => $usuarios,
            'tipos_movimiento' => $tipos_movimiento,
            'vehiculos_destino' => $vehiculos_destino,
            'eventos_destino' => $eventos_destino,
            'empleados_destino' => $empleados_destino,
            'asignacionesPendientes' => $asignacionesPendientes,
            'secretarias' => $secretarias,
            // Pasar permisos a la vista
            'puedeCrearMovimientos' => $user->puedeCrearMovimientosInsumos(),
            'puedeCrearTransferencias' => $user->puedeCrearTransferenciasInsumos(),
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
        $this->filtro_corralon = '';
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

    public function mostrarListaTransferencia()
    {
        $this->mostrar_lista_transferencia = true;
    }

    public function updatedIdCorralonOrigen($value)
    {
        $this->id_deposito_origen = '';
        $this->insumos_transferencia = [];
        $this->search_insumo_transferencia = '';
        $this->mostrar_lista_transferencia = false;
        $this->resetErrorBag('id_corralon_origen');
        $this->resetErrorBag('id_deposito_origen');
    }

    public function updatedIdCorralonDestino($value)
    {
        $this->id_deposito_destino = '';
        $this->resetErrorBag('id_corralon_destino');
        $this->resetErrorBag('id_deposito_destino');
    }

    public function updatedIdDepositoOrigen($value)
    {
        $this->insumos_transferencia = [];
        $this->search_insumo_transferencia = '';
        $this->mostrar_lista_transferencia = false;
        $this->resetErrorBag('id_deposito_origen');

        if (!empty($value) && $value == $this->id_deposito_destino) {
            $this->addError('id_deposito_destino', 'El depósito destino debe ser diferente al de origen');
        } else {
            $this->resetErrorBag('id_deposito_destino');
        }
    }

    public function updatedIdDepositoDestino($value)
    {
        $this->resetErrorBag('id_deposito_destino');
        
        if (empty($value)) {
            return;
        }
        
        if (!empty($this->id_deposito_origen) && $value == $this->id_deposito_origen) {
            $this->addError('id_deposito_destino', 'El depósito destino debe ser diferente al de origen');
        }
    }

    public function crearTransferencia()
    {
        // Verificar permiso de creación
        if (!auth()->user()->puedeCrearTransferenciasInsumos()) {
            session()->flash('error', 'No tienes permisos para crear transferencias.');
            return;
        }

        $this->resetFormTransferencia();
        $this->showModalTransferencia = true;
    }

    public function abrirModalTransferenciaDesdeMovimiento($depositoId, $insumoId)
    {
        if (!auth()->user()->puedeCrearTransferenciasInsumos()) {
            session()->flash('error', 'No tienes permisos para crear transferencias.');
            return;
        }

        $this->resetFormTransferencia();

        $deposito = Deposito::find($depositoId);
        if (!$deposito) return;

        $this->id_deposito_origen = $depositoId;
        $this->id_corralon_origen = $deposito->id_corralon;

        $insumo = Insumo::with('categoriaInsumo')->find($insumoId);
        if ($insumo) {
            $this->insumos_transferencia[] = [
                'id'          => $insumo->id,
                'nombre'      => $insumo->insumo,
                'categoria'   => $insumo->categoriaInsumo->nombre,
                'stock_actual' => $insumo->stock_actual,
                'unidad'      => $insumo->unidad,
                'cantidad'    => '',
            ];
        }

        $this->showModalTransferencia = true;
    }

    public function abrirModalTransferenciaDesdeTransferencia($encabezadoId)
    {
        if (!auth()->user()->puedeCrearTransferenciasInsumos()) {
            session()->flash('error', 'No tienes permisos para crear transferencias.');
            return;
        }

        $this->resetFormTransferencia();

        $encabezado = \App\Models\MovimientoEncabezado::with('depositoDestino')->find($encabezadoId);

        if (!$encabezado) return;

        $deposito = $encabezado->depositoDestino;
        $this->id_deposito_origen = $deposito->id;
        $this->id_corralon_origen = $deposito->id_corralon;

        $movimientosEntrada = $encabezado->movimientosEntrada()->with('insumo.categoriaInsumo')->get();

        foreach ($movimientosEntrada as $mov) {
            $insumo = $mov->insumo;
            if (!$insumo) continue;

            $this->insumos_transferencia[] = [
                'id'           => $insumo->id,
                'nombre'       => $insumo->insumo,
                'categoria'    => $insumo->categoriaInsumo->nombre,
                'stock_actual' => $insumo->stock_actual,
                'unidad'       => $insumo->unidad,
                'cantidad'     => '',
            ];
        }

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

            $this->resetErrorBag('insumos_transferencia');
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
        if (strpos($key, '.cantidad') !== false) {
            $index = explode('.', $key)[0];
            $cantidad = floatval($value);
            $stockDisponible = floatval($this->insumos_transferencia[$index]['stock_actual']);
            
            if ($cantidad > $stockDisponible) {
                $this->addError(
                    "insumos_transferencia.{$index}.cantidad",
                    "No puede transferir más de " . number_format($stockDisponible, 2) . " {$this->insumos_transferencia[$index]['unidad']} disponibles"
                );
            } else {
                $this->resetErrorBag("insumos_transferencia.{$index}.cantidad");
            }
            
            if ($cantidad <= 0 && $cantidad != '') {
                $this->addError(
                    "insumos_transferencia.{$index}.cantidad",
                    "La cantidad debe ser mayor a 0"
                );
            }
        }
    }

    public function updatedSearchInsumoTransferencia()
    {
        $this->mostrar_lista_transferencia = true;
    }

    public function crear()
    {
        // Verificar permiso de creación
        if (!auth()->user()->puedeCrearMovimientosInsumos()) {
            session()->flash('error', 'No tienes permisos para crear movimientos.');
            return;
        }

        $this->resetForm();
        $this->showModal = true;
        $this->paso_actual = 1;
    }

    public function abrirModalConInsumo($insumoId)
    {
        if (!auth()->user()->puedeCrearMovimientosInsumos()) {
            session()->flash('error', 'No tienes permisos para crear movimientos.');
            return;
        }

        $this->resetForm();
        $this->showModal = true;
        $this->seleccionarInsumo($insumoId);
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
                $this->tipo_destino = '';
                $this->id_referencia = '';
                $this->search_destino = '';
                $this->mostrar_lista_destino = false;
            }
        }
    }

    public function guardar()
    {
        // Verificar permiso antes de guardar
        if (!auth()->user()->puedeCrearMovimientosInsumos()) {
            session()->flash('error', 'No tienes permisos para crear movimientos.');
            $this->cerrarModal();
            return;
        }
        
        try {
            $this->validate();

            switch ($this->tipo_movimiento) {
                case 'carga':
                    return $this->guardarCarga();
                case 'ajuste_positivo':
                    return $this->guardarAjustePositivo();
                case 'ajuste_negativo':
                    return $this->guardarAjusteNegativo();
                case 'asignacion_con_reposicion':
                case 'asignacion_sin_reposicion':
                    return $this->guardarAsignacion();
                case 'entrada_reposicion':
                    return $this->guardarEntradaReposicion();
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
        // Verificar permiso antes de guardar
        if (!auth()->user()->puedeCrearTransferenciasInsumos()) {
            session()->flash('error', 'No tienes permisos para crear transferencias.');
            $this->cerrarModalTransferencia();
            return;
        }
        
        try {
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

            if (empty($this->insumos_transferencia) || count($this->insumos_transferencia) === 0) {
                $this->addError('insumos_transferencia', 'Debe seleccionar al menos un insumo para transferir');
                session()->flash('error', 'Debe seleccionar al menos un insumo para transferir');
                return;
            }

            if ($this->getErrorBag()->isNotEmpty()) {
                session()->flash('error', 'Por favor corrija los errores antes de continuar.');
                return;
            }

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

            $tipoMovimientoSalida = TipoMovimiento::where('tipo_movimiento', 'Transferencia Salida')->first();
            $tipoMovimientoEntrada = TipoMovimiento::where('tipo_movimiento', 'Transferencia Entrada')->first();

            $encabezado = MovimientoEncabezado::create([
                'fecha' => now(),
                'id_deposito_origen' => $this->id_deposito_origen,
                'id_deposito_destino' => $this->id_deposito_destino,
                'observaciones' => $this->observaciones_transferencia,
                'id_usuario' => Auth::id(),
            ]);

            $totalInsumos = 0;
            $detalleInsumos = [];

            foreach ($this->insumos_transferencia as $item) {
                $insumo = Insumo::find($item['id']);
                
                if (!$insumo || $insumo->id_deposito != $this->id_deposito_origen) {
                    throw new \Exception("Insumo {$item['nombre']} no válido o ya no pertenece al depósito origen.");
                }

                $cantidad = floatval($item['cantidad']);

                if ($cantidad <= 0) {
                    throw new \Exception("La cantidad para {$item['nombre']} debe ser mayor a 0.");
                }

                if ($cantidad > $insumo->stock_actual) {
                    throw new \Exception("La cantidad de {$item['nombre']} (" . number_format($cantidad, 2) . " {$insumo->unidad}) excede el stock disponible actual (" . number_format($insumo->stock_actual, 2) . " {$insumo->unidad}). El stock pudo haber cambiado.");
                }

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
    
    public function removeComprobante($index)
    {
        $comprobantes = $this->comprobantes;
        unset($comprobantes[$index]);
        $this->comprobantes = array_values($comprobantes);
    }

    private function guardarComprobantes($movimientoId)
    {
        if (empty($this->comprobantes)) {
            return;
        }

        foreach ($this->comprobantes as $file) {
            $path = $file->store('comprobantes', 'local');
            $filename = basename($path);

            ComprobanteMovimiento::create([
                'id_movimiento_insumo' => $movimientoId,
                'archivo' => $filename,
                'nombre_original' => $file->getClientOriginalName(),
                'tipo_mime' => $file->getMimeType(),
            ]);
        }
    }

    private function guardarCarga()
    {
        try {
            DB::beginTransaction();

            $tipoMovimiento = TipoMovimiento::where('tipo_movimiento', 'Carga de Stock')->first();

            $insumo = Insumo::find($this->insumo_id);
            
            if (!$insumo) {
                throw new \Exception('No se encontró el insumo seleccionado.');
            }

            $movimiento = MovimientoInsumo::create([
                'id_insumo' => $insumo->id,
                'id_tipo_movimiento' => $tipoMovimiento->id,
                'cantidad' => $this->cantidad,
                'nro_orden_compra' => $this->nro_orden_compra ?: null,
                'fecha' => now(),
                'fecha_devolucion' => null,
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $insumo->id_deposito,
                'id_referencia' => 0,
                'tipo_referencia' => 'inventario',
            ]);

            $this->guardarComprobantes($movimiento->id);

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

            $tipoMovimiento = TipoMovimiento::where('tipo_movimiento', 'Ajuste Positivo')->first();

            $insumo = Insumo::find($this->insumo_id);
            
            if (!$insumo) {
                throw new \Exception('No se encontró el insumo seleccionado.');
            }

            $movimiento = MovimientoInsumo::create([
                'id_insumo' => $insumo->id,
                'id_tipo_movimiento' => $tipoMovimiento->id,
                'cantidad' => $this->cantidad,
                'nro_orden_compra' => $this->nro_orden_compra ?: null,
                'fecha' => now(),
                'fecha_devolucion' => null,
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $insumo->id_deposito,
                'id_referencia' => 0,
                'tipo_referencia' => 'inventario',
            ]);

            $this->guardarComprobantes($movimiento->id);

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

            $tipoMovimiento = TipoMovimiento::where('tipo_movimiento', 'Ajuste Negativo')->first();

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
                'id_secretaria' => $this->id_secretaria_ajuste ?: null,
                'area' => $this->area_ajuste ?: null,
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

    public function updatedTipoDestino()
    {
        $this->id_referencia = '';
        $this->search_destino = '';
        $this->mostrar_lista_destino = false;
        $this->resetErrorBag('id_referencia');
    }

    public function updatedSearchDestino()
    {
        $this->mostrar_lista_destino = true;
    }

    public function updatedIdSecretariaAjuste()
    {
        $this->area_ajuste = '';
        $this->areas_disponibles = $this->id_secretaria_ajuste
            ? Area::where('id_secretaria', $this->id_secretaria_ajuste)->orderBy('area')->pluck('area')->toArray()
            : [];
    }

    public function seleccionarDestino($id)
    {
        $this->id_referencia = $id;
        $this->search_destino = '';
        $this->mostrar_lista_destino = false;
    }

    private function guardarAsignacion()
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

            $nombreTipo = $this->tipo_movimiento === 'asignacion_con_reposicion'
                ? 'Asignación con Reposición'
                : 'Asignación sin Reposición';

            DB::beginTransaction();

            $tipoMovimiento = TipoMovimiento::where('tipo_movimiento', $nombreTipo)->first();

            MovimientoInsumo::create([
                'id_insumo' => $insumo->id,
                'id_tipo_movimiento' => $tipoMovimiento->id,
                'cantidad' => $this->cantidad,
                'fecha' => now(),
                'fecha_devolucion' => null,
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $insumo->id_deposito,
                'id_referencia' => $this->id_referencia,
                'tipo_referencia' => $this->tipo_destino,
            ]);

            $insumo->sincronizarStock();

            DB::commit();

            $destinoNombre = $this->obtenerNombreDestino();
            $mensaje = "{$nombreTipo} realizada: {$this->cantidad} {$insumo->unidad} de {$insumo->insumo} → {$destinoNombre}";
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

    private function guardarEntradaReposicion()
    {
        try {
            $insumo = Insumo::find($this->insumo_id);

            if (!$insumo) {
                throw new \Exception('No se encontró el insumo seleccionado.');
            }

            DB::beginTransaction();

            $tipoMovimiento = TipoMovimiento::where('tipo_movimiento', 'Entrada Reposición')->first();

            MovimientoInsumo::create([
                'id_insumo' => $insumo->id,
                'id_tipo_movimiento' => $tipoMovimiento->id,
                'cantidad' => $this->cantidad,
                'fecha' => now(),
                'fecha_devolucion' => null,
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $insumo->id_deposito,
                'id_referencia' => $this->id_referencia,
                'tipo_referencia' => $this->tipo_destino,
            ]);

            $insumo->sincronizarStock();

            DB::commit();

            $destinoNombre = $this->obtenerNombreDestino();
            $mensaje = "Entrada Reposición realizada: +{$this->cantidad} {$insumo->unidad} de {$insumo->insumo} ← {$destinoNombre}";
            session()->flash('message', $mensaje);

            \Illuminate\Support\Facades\Cache::flush();

            $this->cerrarModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al realizar la entrada reposición: ' . $e->getMessage());
            $this->cerrarModal();
            \Log::error('Error en guardarEntradaReposicion: ' . $e->getMessage());
        }
    }

    private function obtenerNombreDestino(): string
    {
        if ($this->tipo_destino === 'vehiculo') {
            $vehiculo = Vehiculo::find($this->id_referencia);
            return $vehiculo ? "Vehículo: {$vehiculo->vehiculo} ({$vehiculo->patente})" : 'Vehículo desconocido';
        }
        if ($this->tipo_destino === 'evento') {
            $evento = Evento::find($this->id_referencia);
            return $evento ? "Evento: {$evento->evento}" : 'Evento desconocido';
        }
        if ($this->tipo_destino === 'empleado') {
            $empleado = EmpleadoMunicipal::find($this->id_referencia);
            return $empleado ? "Empleado: {$empleado->nombre_formateado} (Leg. {$empleado->LEGAJO})" : 'Empleado desconocido';
        }
        return 'Destino desconocido';
    }

    public function devolverAsignacion($insumoId, $tipoReferencia, $idReferencia, $cantidadDevolver)
    {
        if (!auth()->user()->puedeCrearMovimientosInsumos()) {
            session()->flash('error', 'No tienes permisos para crear movimientos.');
            return;
        }

        $cantidadDevolver = floatval($cantidadDevolver);
        if ($cantidadDevolver <= 0) {
            session()->flash('error', 'La cantidad debe ser mayor a 0.');
            return;
        }

        try {
            $insumo = Insumo::find($insumoId);
            if (!$insumo) {
                throw new \Exception('No se encontró el insumo.');
            }

            $pendiente = $this->calcularPendiente($insumoId, $tipoReferencia, $idReferencia);
            if ($cantidadDevolver > $pendiente) {
                session()->flash('error', "No puede devolver más de " . number_format($pendiente, 2) . " {$insumo->unidad} pendientes.");
                return;
            }

            DB::beginTransaction();

            $tipoMovimiento = TipoMovimiento::where('tipo_movimiento', 'Entrada Reposición')->first();

            MovimientoInsumo::create([
                'id_insumo' => $insumo->id,
                'id_tipo_movimiento' => $tipoMovimiento->id,
                'cantidad' => $cantidadDevolver,
                'fecha' => now(),
                'fecha_devolucion' => null,
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $insumo->id_deposito,
                'id_referencia' => $idReferencia,
                'tipo_referencia' => $tipoReferencia,
            ]);

            $insumo->sincronizarStock();

            DB::commit();

            $referenciaNombre = '';
            if ($tipoReferencia === 'vehiculo') {
                $v = Vehiculo::find($idReferencia);
                $referenciaNombre = $v ? "{$v->vehiculo} ({$v->patente})" : "Vehículo #{$idReferencia}";
            } elseif ($tipoReferencia === 'evento') {
                $ev = Evento::find($idReferencia);
                $referenciaNombre = $ev ? $ev->evento : "Evento #{$idReferencia}";
            }

            session()->flash('message', "Devolución realizada: +{$cantidadDevolver} {$insumo->unidad} de {$insumo->insumo} desde {$referenciaNombre}");

            \Illuminate\Support\Facades\Cache::flush();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al realizar la devolución: ' . $e->getMessage());
            \Log::error('Error en devolverAsignacion: ' . $e->getMessage());
        }
    }

    public function darDeBajaAsignacion($insumoId, $tipoReferencia, $idReferencia)
    {
        if (!auth()->user()->puedeCrearMovimientosInsumos()) {
            session()->flash('error', 'No tienes permisos para crear movimientos.');
            return;
        }

        try {
            $insumo = Insumo::find($insumoId);
            if (!$insumo) {
                throw new \Exception('No se encontró el insumo.');
            }

            $pendiente = $this->calcularPendiente($insumoId, $tipoReferencia, $idReferencia);
            if ($pendiente <= 0) {
                session()->flash('error', 'No hay cantidad pendiente para dar de baja.');
                return;
            }

            DB::beginTransaction();

            $tipoMovimiento = TipoMovimiento::where('tipo_movimiento', 'Baja Reposición')->first();

            MovimientoInsumo::create([
                'id_insumo' => $insumo->id,
                'id_tipo_movimiento' => $tipoMovimiento->id,
                'cantidad' => $pendiente,
                'fecha' => now(),
                'fecha_devolucion' => null,
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $insumo->id_deposito,
                'id_referencia' => $idReferencia,
                'tipo_referencia' => $tipoReferencia,
            ]);

            DB::commit();

            $referenciaNombre = '';
            if ($tipoReferencia === 'vehiculo') {
                $v = Vehiculo::find($idReferencia);
                $referenciaNombre = $v ? "{$v->vehiculo} ({$v->patente})" : "Vehículo #{$idReferencia}";
            } elseif ($tipoReferencia === 'evento') {
                $ev = Evento::find($idReferencia);
                $referenciaNombre = $ev ? $ev->evento : "Evento #{$idReferencia}";
            }

            session()->flash('message', "Baja realizada: {$pendiente} {$insumo->unidad} de {$insumo->insumo} en {$referenciaNombre}");

            \Illuminate\Support\Facades\Cache::flush();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al dar de baja: ' . $e->getMessage());
            \Log::error('Error en darDeBajaAsignacion: ' . $e->getMessage());
        }
    }

    private function calcularPendiente($insumoId, $tipoReferencia, $idReferencia): float
    {
        $tipoConReposicion = TipoMovimiento::where('tipo_movimiento', 'Asignación con Reposición')->first();
        $tiposDescuento = TipoMovimiento::whereIn('tipo_movimiento', ['Entrada Reposición', 'Baja Reposición'])->pluck('id');

        $todosLosTipos = collect([$tipoConReposicion->id])->merge($tiposDescuento);
        $movimientos = MovimientoInsumo::whereIn('id_tipo_movimiento', $todosLosTipos)
            ->where('id_insumo', $insumoId)
            ->where('tipo_referencia', $tipoReferencia)
            ->where('id_referencia', $idReferencia)
            ->orderBy('id')
            ->get();

        $balance = 0;
        foreach ($movimientos as $mov) {
            if ($mov->id_tipo_movimiento == $tipoConReposicion->id) {
                $balance += $mov->cantidad;
            } else {
                $balance = max(0, $balance - $mov->cantidad);
            }
        }

        return $balance;
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
        $this->nro_orden_compra = '';
        $this->observaciones = '';
        $this->search_insumo = '';
        $this->mostrar_lista = false;
        $this->tipo_destino = '';
        $this->id_referencia = '';
        $this->search_destino = '';
        $this->mostrar_lista_destino = false;
        $this->comprobantes = [];
        $this->depositos_disponibles = [];
        $this->tipos_movimiento_disponibles = [];
        $this->id_secretaria_ajuste = '';
        $this->area_ajuste = '';
        $this->areas_disponibles = [];
        $this->resetErrorBag();
    }

    private function resetFormTransferencia()
    {
        $this->id_corralon_origen = '';
        $this->id_corralon_destino = '';
        $this->id_deposito_origen = '';
        $this->id_deposito_destino = '';
        $this->insumos_transferencia = [];
        $this->search_insumo_transferencia = '';
        $this->mostrar_lista_transferencia = false;
        $this->observaciones_transferencia = '';
        $this->resetErrorBag();
    }
}