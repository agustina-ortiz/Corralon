<?php

namespace App\Livewire;

use App\Models\Maquinaria;
use App\Models\Deposito;
use App\Models\MovimientoMaquinaria;
use App\Models\TipoMovimiento;
use App\Models\CategoriaMaquinaria;
use App\Models\Corralon;
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

    // Tipo de movimiento seleccionado (key interno: carga_stock, asignacion, transferencia, devolucion, mantenimiento)
    public $tipo_movimiento = '';

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

    // Para transferencia multi-maquinaria (modal separado)
    public $showModalTransferencia = false;
    public $id_corralon_origen = '';
    public $id_corralon_destino = '';
    public $id_deposito_origen_tf = '';
    public $id_deposito_destino_tf = '';
    public $maquinarias_transferencia = [];
    public $search_maquinaria_transferencia = '';
    public $mostrar_lista_transferencia = false;

    // Configuración UI por tipo (icons, colores, descripciones)
    // Mapeado al nombre exacto en la tabla tipo_movimientos
    private const UI_CONFIG = [
        'Carga de Stock' => [
            'key'        => 'carga_stock',
            'descripcion'=> 'Agregar unidades al inventario',
            'icon'       => 'M12 4v16m8-8H4',
            'color'      => 'indigo',
        ],
        'Asignación Maquinaria' => [
            'key'        => 'asignacion',
            'descripcion'=> 'Asignar maquinaria a un empleado o evento',
            'icon'       => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            'color'      => 'blue',
        ],
        'Mantenimiento Maquinaria' => [
            'key'        => 'mantenimiento',
            'descripcion'=> 'Marcar maquinaria como en mantenimiento',
            'icon'       => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
            'color'      => 'orange',
        ],
        'Transferencia Salida' => [
            'key'        => 'transferencia',
            'descripcion'=> 'Mover maquinaria a otro depósito',
            'icon'       => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
            'color'      => 'purple',
        ],
        'Devolución' => [
            'key'        => 'devolucion',
            'descripcion'=> 'Devolver maquinaria al depósito',
            'icon'       => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6',
            'color'      => 'green',
        ],
    ];

    protected function rules()
    {
        if ($this->showModalTransferencia) {
            return [
                'id_deposito_origen_tf'               => 'required|exists:depositos,id',
                'id_deposito_destino_tf'              => 'required|exists:depositos,id|different:id_deposito_origen_tf',
                'maquinarias_transferencia'            => 'required|array|min:1',
                'maquinarias_transferencia.*.cantidad' => 'required|integer|min:1',
            ];
        }

        $rules = [
            'observaciones' => 'nullable|string|max:500',
        ];

        if ($this->tipo_movimiento === 'transferencia') {
            $rules['id_deposito_destino'] = 'required|exists:depositos,id';

            $cantidadDisponible = $this->maquinaria_seleccionada
                ? $this->maquinaria_seleccionada->cantidad_disponible
                : 1;

            $rules['cantidad_a_transferir'] = ['required', 'integer', 'min:1', 'max:' . $cantidadDisponible];
        }

        if ($this->tipo_movimiento === 'carga_stock') {
            $rules['id_deposito_origen'] = 'required|exists:depositos,id';
            $rules['cantidad_a_cargar']  = ['required', 'integer', 'min:1', 'max:1000'];
        }

        if ($this->tipo_movimiento === 'asignacion') {
            $rules['id_deposito_origen'] = 'required|exists:depositos,id';

            $cantidadDisponible = $this->maquinaria_seleccionada && $this->id_deposito_origen
                ? $this->maquinaria_seleccionada->getCantidadEnDeposito($this->id_deposito_origen)
                : 1;

            $rules['cantidad_a_asignar']      = ['required', 'integer', 'min:1', 'max:' . $cantidadDisponible];
            $rules['fecha_devolucion_esperada'] = 'required|date|after:today';
        }

        return $rules;
    }

    protected $messages = [
        'id_deposito_destino.required'      => 'Debe seleccionar un depósito destino.',
        'id_deposito_origen.required'       => 'Debe seleccionar un depósito de origen.',
        'fecha_devolucion_esperada.required' => 'Debe ingresar una fecha de devolución esperada.',
        'fecha_devolucion_esperada.after'   => 'La fecha de devolución debe ser posterior a hoy.',
        'cantidad_a_transferir.required'    => 'Debe ingresar la cantidad a transferir.',
        'cantidad_a_transferir.min'         => 'La cantidad debe ser al menos 1.',
        'cantidad_a_transferir.max'         => 'No hay suficiente cantidad disponible en el depósito origen.',
        'cantidad_a_asignar.required'       => 'Debe ingresar la cantidad a asignar.',
        'cantidad_a_asignar.min'            => 'La cantidad debe ser al menos 1.',
        'cantidad_a_asignar.max'            => 'No hay suficiente cantidad disponible en el depósito seleccionado.',
        'cantidad_a_cargar.required'        => 'Debe ingresar la cantidad a cargar.',
        'cantidad_a_cargar.min'             => 'La cantidad debe ser al menos 1.',
        'cantidad_a_cargar.max'             => 'La cantidad excede el límite permitido.',
    ];

    public function mount()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $this->filtro_fecha_desde = today()->subMonth()->format('Y-m-d');
        $this->filtro_fecha_hasta = today()->format('Y-m-d');
    }

    private function getDepositosAccesibles(): array
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
     * Resuelve un TipoMovimiento por nombre. Lanza excepción si no existe en DB.
     */
    private function resolverTipo(string $nombre): TipoMovimiento
    {
        $tipo = TipoMovimiento::where('tipo_movimiento', $nombre)->first();

        if (!$tipo) {
            throw new \Exception("Tipo de movimiento '{$nombre}' no encontrado en la base de datos. Ejecute las migraciones.");
        }

        return $tipo;
    }

    /**
     * Carga los tipos disponibles para la maquinaria seleccionada desde la DB.
     * La disponibilidad de cada tipo depende del stock actual.
     */
    private function calcularTiposDisponibles(): array
    {
        if (!$this->maquinaria_seleccionada) {
            return [];
        }

        $tieneStock = $this->maquinaria_seleccionada->getCantidadTotalDisponible() > 0;

        // Disponibilidad por tipo según contexto de stock
        $disponibilidad = [
            'Carga de Stock'           => true,
            'Asignación Maquinaria'    => $tieneStock,
            'Mantenimiento Maquinaria' => $tieneStock,
            'Transferencia Salida'     => $tieneStock,
            'Devolución'               => true,
        ];

        $nombres = array_keys(self::UI_CONFIG);
        $tiposDB = TipoMovimiento::whereIn('tipo_movimiento', $nombres)
            ->get()
            ->keyBy('tipo_movimiento');

        $tipos = [];
        foreach (self::UI_CONFIG as $nombre => $ui) {
            if (!($disponibilidad[$nombre] ?? false)) {
                continue;
            }
            if (!isset($tiposDB[$nombre])) {
                continue; // tipo no seeded, se omite
            }

            $tipos[] = array_merge($ui, [
                'nombre' => $nombre,
                'id_tipo' => $tiposDB[$nombre]->id,
            ]);
        }

        return $tipos;
    }

    public function render()
    {
        $user = auth()->user();
        $depositosAccesibles = $this->getDepositosAccesibles();

        $movimientos = MovimientoMaquinaria::with([
            'maquinaria.categoriaMaquinaria',
            'maquinaria.deposito',
            'depositoEntrada',
            'tipoMovimiento',
            'usuario',
        ])
        ->whereHas('maquinaria', function ($query) use ($depositosAccesibles) {
            $query->whereIn('id_deposito', $depositosAccesibles);
        })
        ->when($this->search, function ($query) {
            $query->whereHas('maquinaria', function ($q) {
                $q->where('maquinaria', 'like', '%' . $this->search . '%');
            });
        })
        ->when($this->filtro_fecha_desde, fn($q) => $q->whereDate('fecha', '>=', $this->filtro_fecha_desde))
        ->when($this->filtro_fecha_hasta, fn($q) => $q->whereDate('fecha', '<=', $this->filtro_fecha_hasta))
        ->when($this->filtro_deposito_origen, fn($q) => $q->where('id_deposito_entrada', $this->filtro_deposito_origen))
        ->when($this->filtro_usuario, fn($q) => $q->where('id_usuario', $this->filtro_usuario))
        ->when($this->filtro_maquinaria, function ($query) {
            $query->whereHas('maquinaria', function ($q) {
                $q->where('maquinaria', 'like', '%' . $this->filtro_maquinaria . '%');
            });
        })
        ->when($this->filtro_categoria, function ($query) {
            $query->whereHas('maquinaria', function ($q) {
                $q->where('id_categoria_maquinaria', $this->filtro_categoria);
            });
        })
        ->when($this->filtro_tipo_movimiento, fn($q) => $q->where('id_tipo_movimiento', $this->filtro_tipo_movimiento))
        ->orderBy('created_at', 'desc')
        ->paginate(15);

        $movimientos->getCollection()->transform(function ($movimiento) use ($depositosAccesibles) {
            $stockTotal = 0;
            foreach ($depositosAccesibles as $depositoId) {
                $stockTotal += $movimiento->maquinaria->getCantidadEnDeposito($depositoId);
            }
            $movimiento->estado_calculado = $stockTotal > 0 ? 'disponible' : 'no disponible';

            return $movimiento;
        });

        // Maquinarias filtradas para el paso 1 del modal
        $maquinarias_filtradas = collect();

        if ($this->paso_actual === 1 && ($this->mostrar_lista || $this->search_maquinaria)) {
            $maquinarias_filtradas = Maquinaria::with([
                'categoriaMaquinaria',
                'deposito',
                'movimientos.tipoMovimiento',
            ])
            ->whereIn('id_deposito', $depositosAccesibles)
            ->when($this->search_maquinaria, function ($query) {
                $query->where(function ($q) {
                    $q->where('maquinaria', 'like', '%' . $this->search_maquinaria . '%')
                        ->orWhereHas('categoriaMaquinaria', fn($cat) => $cat->where('nombre', 'like', '%' . $this->search_maquinaria . '%'))
                        ->orWhereHas('deposito', fn($dep) => $dep->where('deposito', 'like', '%' . $this->search_maquinaria . '%'));
                });
            })
            ->orderBy('maquinaria')
            ->limit(50)
            ->get()
            ->map(function ($maquinaria) use ($depositosAccesibles) {
                $cantidadTotal = 0;
                foreach ($depositosAccesibles as $depositoId) {
                    $cantidadTotal += $maquinaria->getCantidadEnDeposito($depositoId);
                }
                $maquinaria->cantidad_disponible = $cantidadTotal;
                return $maquinaria;
            });
        }

        $depositos        = Deposito::whereIn('id', $depositosAccesibles)->orderBy('deposito')->get();
        $categorias       = CategoriaMaquinaria::orderBy('nombre')->get();
        $usuarios         = User::orderBy('name')->get();
        $tipos_movimiento = TipoMovimiento::whereIn('tipo', ['M', 'IM'])->orderBy('tipo_movimiento')->get();

        // Datos para el modal de transferencia multi-maquinaria
        $corralonesPermitidosIds  = $user->getCorralonesPermitidosIds();
        $tieneMultiplesCorralones = $user->acceso_todos_corralones || count($corralonesPermitidosIds) > 1;

        $corralones = collect();
        if ($tieneMultiplesCorralones) {
            $corralones = $user->acceso_todos_corralones
                ? Corralon::orderBy('descripcion')->get()
                : Corralon::whereIn('id', $corralonesPermitidosIds)->orderBy('descripcion')->get();
        }

        // Depósitos origen para el modal de transferencia (filtrados por corralón si aplica)
        if ($tieneMultiplesCorralones && $this->id_corralon_origen) {
            $depositosOrigenTf = Deposito::whereIn('id', $depositosAccesibles)
                ->where('id_corralon', $this->id_corralon_origen)
                ->orderBy('deposito')->get();
        } else {
            $depositosOrigenTf = Deposito::whereIn('id', $depositosAccesibles)->orderBy('deposito')->get();
        }

        // Depósitos destino para el modal de transferencia
        if ($tieneMultiplesCorralones && $this->id_corralon_destino) {
            $depositosDestinoTf = Deposito::where('id_corralon', $this->id_corralon_destino)
                ->when($this->id_deposito_origen_tf, fn($q) => $q->where('id', '!=', $this->id_deposito_origen_tf))
                ->orderBy('deposito')->get();
        } else {
            $depositosDestinoTf = Deposito::when($this->id_deposito_origen_tf, fn($q) => $q->where('id', '!=', $this->id_deposito_origen_tf))
                ->orderBy('deposito')->get();
        }

        // Maquinarias disponibles en el depósito origen para transferir
        $maquinarias_disponibles_transferencia = collect();
        if ($this->showModalTransferencia && $this->id_deposito_origen_tf
            && ($this->mostrar_lista_transferencia || $this->search_maquinaria_transferencia)) {
            $maquinarias_disponibles_transferencia = Maquinaria::with(['categoriaMaquinaria'])
                ->where('id_deposito', $this->id_deposito_origen_tf)
                ->when($this->search_maquinaria_transferencia, function ($q) {
                    $q->where(function ($inner) {
                        $inner->where('maquinaria', 'like', '%' . $this->search_maquinaria_transferencia . '%')
                            ->orWhereHas('categoriaMaquinaria', fn($cat) => $cat->where('nombre', 'like', '%' . $this->search_maquinaria_transferencia . '%'));
                    });
                })
                ->orderBy('maquinaria')
                ->limit(50)
                ->get()
                ->map(function ($m) {
                    $m->cantidad_disponible = $m->getCantidadEnDeposito($m->id_deposito);
                    return $m;
                })
                ->filter(fn($m) => $m->cantidad_disponible > 0)
                ->values();
        }

        return view('livewire.transferencias-maquinarias', [
            'movimientos'                          => $movimientos,
            'maquinarias_filtradas'                => $maquinarias_filtradas,
            'depositos'                            => $depositos,
            'categorias'                           => $categorias,
            'usuarios'                             => $usuarios,
            'tipos_movimiento'                     => $tipos_movimiento,
            'puedeCrear'                           => $user->puedeCrearMovimientosMaquinarias(),
            'puedeCrearTransferencias'             => $user->puedeCrearTransferenciasMaquinarias(),
            'tieneMultiplesCorralones'             => $tieneMultiplesCorralones,
            'corralones'                           => $corralones,
            'depositosOrigenTf'                    => $depositosOrigenTf,
            'depositosDestinoTf'                   => $depositosDestinoTf,
            'maquinarias_disponibles_transferencia'=> $maquinarias_disponibles_transferencia,
        ])->layout('layouts.app', ['header' => 'Movimientos de Maquinarias']);
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
        $this->filtro_fecha_desde    = '';
        $this->filtro_fecha_hasta    = '';
        $this->filtro_deposito_origen = '';
        $this->filtro_deposito_destino = '';
        $this->filtro_usuario        = '';
        $this->filtro_maquinaria     = '';
        $this->filtro_categoria      = '';
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
        if (!auth()->user()->puedeCrearMovimientosMaquinarias()) {
            session()->flash('error', 'No tienes permisos para crear movimientos de maquinarias.');
            return;
        }

        $this->resetForm();
        $this->showModal = true;
        $this->paso_actual = 1;
    }

    public function incrementarCantidad()
    {
        if ($this->tipo_movimiento === 'transferencia') {
            $max = $this->maquinaria_seleccionada ? $this->maquinaria_seleccionada->cantidad_disponible : 1;
            if ($this->cantidad_a_transferir < $max) {
                $this->cantidad_a_transferir++;
            }
        } elseif ($this->tipo_movimiento === 'asignacion') {
            $max = $this->maquinaria_seleccionada && $this->id_deposito_origen
                ? $this->maquinaria_seleccionada->getCantidadEnDeposito($this->id_deposito_origen)
                : 1;
            if ($this->cantidad_a_asignar < $max) {
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
            if ($this->cantidad_a_transferir > 1) $this->cantidad_a_transferir--;
        } elseif ($this->tipo_movimiento === 'asignacion') {
            if ($this->cantidad_a_asignar > 1) $this->cantidad_a_asignar--;
        } elseif ($this->tipo_movimiento === 'carga_stock') {
            if ($this->cantidad_a_cargar > 1) $this->cantidad_a_cargar--;
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

        if ($tipo === 'transferencia') {
            // Origen = depósito de la maquinaria; el usuario elige el destino
            $this->id_deposito_origen = $this->maquinaria_seleccionada->id_deposito;
            $this->depositos_disponibles = Deposito::whereIn('id', $depositosAccesibles)
                ->orderBy('deposito')->get();
            $cantidadDisponible = $this->maquinaria_seleccionada->getCantidadEnDeposito($this->id_deposito_origen);
            $this->cantidad_a_transferir = min(1, $cantidadDisponible);
        }

        if ($tipo === 'asignacion') {
            // Origen = depósito de la maquinaria (no se elige)
            $this->id_deposito_origen = $this->maquinaria_seleccionada->id_deposito;
            $cantidadDisponible = $this->maquinaria_seleccionada->getCantidadEnDeposito($this->id_deposito_origen);
            $this->cantidad_a_asignar = min(1, max(1, $cantidadDisponible));
        }

        if ($tipo === 'carga_stock') {
            // Destino = depósito de la maquinaria (no se elige)
            $this->id_deposito_origen = $this->maquinaria_seleccionada->id_deposito;
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
        if (!auth()->user()->puedeCrearMovimientosMaquinarias()) {
            session()->flash('error', 'No tienes permisos para crear movimientos de maquinarias.');
            $this->showModal = false;
            return;
        }

        try {
            $this->validate();

            switch ($this->tipo_movimiento) {
                case 'carga_stock':   return $this->guardarCargaStock();
                case 'asignacion':    return $this->guardarAsignacion();
                case 'transferencia': return $this->ejecutarTransferencia();
                case 'devolucion':    return $this->guardarDevolucion();
                case 'mantenimiento': return $this->guardarMantenimiento();
                default:
                    session()->flash('error', 'Tipo de movimiento no válido.');
                    $this->cerrarModal();
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al procesar el movimiento: ' . $e->getMessage());
            $this->cerrarModal();
            \Log::error('Error en guardar movimiento maquinaria: ' . $e->getMessage());
        }
    }

    private function guardarCargaStock()
    {
        DB::beginTransaction();
        try {
            $tipoMovimiento = $this->resolverTipo('Carga de Stock');
            $maquinaria     = Maquinaria::findOrFail($this->maquinaria_id);

            MovimientoMaquinaria::create([
                'id_maquinaria'      => $maquinaria->id,
                'cantidad'           => $this->cantidad_a_cargar,
                'id_tipo_movimiento' => $tipoMovimiento->id,
                'fecha'              => now(),
                'fecha_devolucion'   => null,
                'id_usuario'         => Auth::id(),
                'id_deposito_entrada'=> $this->id_deposito_origen,
                'id_referencia'      => 0,
                'tipo_referencia'    => 'deposito',
            ]);

            $this->actualizarCantidadMaquinaria($maquinaria->id);
            DB::commit();

            $deposito = Deposito::find($this->id_deposito_origen);
            $unidad   = $this->cantidad_a_cargar == 1 ? 'unidad' : 'unidades';
            session()->flash('message', "Carga de stock realizada: {$this->cantidad_a_cargar} {$unidad} de {$maquinaria->maquinaria} en {$deposito->deposito}");
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
        DB::beginTransaction();
        try {
            $tipoMovimiento = $this->resolverTipo('Asignación Maquinaria');
            $maquinaria     = Maquinaria::findOrFail($this->maquinaria_id);

            $stockDisponible = $maquinaria->getCantidadEnDeposito($this->id_deposito_origen);
            if ($stockDisponible < $this->cantidad_a_asignar) {
                throw new \Exception("Solo hay {$stockDisponible} unidades disponibles en el depósito seleccionado.");
            }

            MovimientoMaquinaria::create([
                'id_maquinaria'      => $maquinaria->id,
                'cantidad'           => $this->cantidad_a_asignar,
                'id_tipo_movimiento' => $tipoMovimiento->id,
                'fecha'              => now(),
                'fecha_devolucion'   => $this->fecha_devolucion_esperada,
                'id_usuario'         => Auth::id(),
                'id_deposito_entrada'=> $this->id_deposito_origen,
                'id_referencia'      => 0,
                'tipo_referencia'    => 'empleado',
            ]);

            $this->actualizarCantidadMaquinaria($maquinaria->id);
            DB::commit();

            $deposito = Deposito::find($this->id_deposito_origen);
            $unidad   = $this->cantidad_a_asignar == 1 ? 'unidad' : 'unidades';
            session()->flash('message', "Asignación realizada: {$this->cantidad_a_asignar} {$unidad} de {$maquinaria->maquinaria} desde {$deposito->deposito}");
            \Illuminate\Support\Facades\Cache::flush();
            $this->cerrarModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al realizar la asignación: ' . $e->getMessage());
            $this->cerrarModal();
            \Log::error('Error en guardarAsignacion: ' . $e->getMessage());
        }
    }

    private function ejecutarTransferencia()
    {
        DB::beginTransaction();
        try {
            $maquinaria = Maquinaria::findOrFail($this->maquinaria_id);

            $deposito_origen_id  = $maquinaria->id_deposito;
            $deposito_destino_id = $this->id_deposito_destino;

            if ($deposito_origen_id == $deposito_destino_id) {
                throw new \Exception('El depósito destino debe ser diferente al depósito origen.');
            }

            $maquinariaOrigen = Maquinaria::where('maquinaria', $maquinaria->maquinaria)
                ->where('id_deposito', $deposito_origen_id)
                ->where('id_categoria_maquinaria', $maquinaria->id_categoria_maquinaria)
                ->firstOrFail();

            $cantidadDisponible = $maquinariaOrigen->getCantidadEnDeposito($deposito_origen_id);
            if ($this->cantidad_a_transferir > $cantidadDisponible) {
                throw new \Exception("Solo hay {$cantidadDisponible} unidades disponibles en el depósito origen.");
            }

            $maquinariaDestino = Maquinaria::where('maquinaria', $maquinaria->maquinaria)
                ->where('id_deposito', $deposito_destino_id)
                ->where('id_categoria_maquinaria', $maquinaria->id_categoria_maquinaria)
                ->first();

            if (!$maquinariaDestino) {
                $maquinariaDestino = Maquinaria::create([
                    'maquinaria'           => $maquinaria->maquinaria,
                    'id_categoria_maquinaria' => $maquinaria->id_categoria_maquinaria,
                    'id_deposito'          => $deposito_destino_id,
                    'estado'               => 'disponible',
                    'cantidad'             => 0,
                    'descripcion'          => $maquinaria->descripcion,
                    'modelo'               => $maquinaria->modelo,
                    'numero_serie'         => null,
                    'anio_fabricacion'     => $maquinaria->anio_fabricacion,
                ]);
            }

            $tipoEntrada = $this->resolverTipo('Transferencia Entrada');
            $tipoSalida  = $this->resolverTipo('Transferencia Salida');

            MovimientoMaquinaria::create([
                'id_maquinaria'      => $maquinariaDestino->id,
                'cantidad'           => $this->cantidad_a_transferir,
                'id_tipo_movimiento' => $tipoEntrada->id,
                'fecha'              => now(),
                'id_usuario'         => Auth::id(),
                'id_deposito_entrada'=> $deposito_destino_id,
                'id_referencia'      => $maquinariaOrigen->id,
                'tipo_referencia'    => 'deposito',
            ]);

            MovimientoMaquinaria::create([
                'id_maquinaria'      => $maquinariaOrigen->id,
                'cantidad'           => $this->cantidad_a_transferir,
                'id_tipo_movimiento' => $tipoSalida->id,
                'fecha'              => now(),
                'id_usuario'         => Auth::id(),
                'id_deposito_entrada'=> $deposito_origen_id,
                'id_referencia'      => $maquinariaDestino->id,
                'tipo_referencia'    => 'deposito',
            ]);

            $this->actualizarCantidadMaquinaria($maquinariaOrigen->id);
            $this->actualizarCantidadMaquinaria($maquinariaDestino->id);
            DB::commit();

            $dep_origen  = Deposito::find($deposito_origen_id);
            $dep_destino = Deposito::find($deposito_destino_id);
            $unidad      = $this->cantidad_a_transferir == 1 ? 'unidad' : 'unidades';
            session()->flash('message', "Transferencia realizada: {$this->cantidad_a_transferir} {$unidad} de {$maquinaria->maquinaria} desde {$dep_origen->deposito} hacia {$dep_destino->deposito}");
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
        DB::beginTransaction();
        try {
            $tipoMovimiento = $this->resolverTipo('Devolución');
            $maquinaria     = Maquinaria::findOrFail($this->maquinaria_id);

            // Determinar el último movimiento de salida para saber de dónde volvió
            $ultimaSalida = MovimientoMaquinaria::where('id_maquinaria', $maquinaria->id)
                ->whereHas('tipoMovimiento', fn($q) => $q->whereIn('tipo_movimiento', TipoMovimiento::NOMBRES_SALIDA))
                ->latest('fecha')
                ->first();

            $tipoReferencia = $ultimaSalida?->tipo_referencia ?? 'empleado';
            $depositoDestino = $ultimaSalida?->id_deposito_entrada ?? $maquinaria->id_deposito;

            // Verificar que hay unidades pendientes de devolver
            if ($ultimaSalida?->tipo_referencia === 'mantenimiento') {
                // Devolución desde mantenimiento: siempre 1 unidad
                $cantidadDevolver = 1;
            } else {
                // Devolución desde asignación: verificar pendiente
                $nombreAsignacion = 'Asignación Maquinaria';
                $nombreDevolucion = 'Devolución';

                $cantidadAsignada = MovimientoMaquinaria::where('id_maquinaria', $maquinaria->id)
                    ->whereHas('tipoMovimiento', fn($q) => $q->where('tipo_movimiento', $nombreAsignacion))
                    ->sum('cantidad');

                $cantidadDevuelta = MovimientoMaquinaria::where('id_maquinaria', $maquinaria->id)
                    ->whereHas('tipoMovimiento', fn($q) => $q->where('tipo_movimiento', $nombreDevolucion))
                    ->sum('cantidad');

                $cantidadPendiente = $cantidadAsignada - $cantidadDevuelta;

                if ($cantidadPendiente < 1) {
                    throw new \Exception('No hay unidades asignadas pendientes de devolución.');
                }
                $cantidadDevolver = 1;
            }

            MovimientoMaquinaria::create([
                'id_maquinaria'      => $maquinaria->id,
                'cantidad'           => $cantidadDevolver,
                'id_tipo_movimiento' => $tipoMovimiento->id,
                'fecha'              => now(),
                'fecha_devolucion'   => null,
                'id_usuario'         => Auth::id(),
                'id_deposito_entrada'=> $depositoDestino,
                'id_referencia'      => 0,
                'tipo_referencia'    => $tipoReferencia,
            ]);

            $this->actualizarCantidadMaquinaria($maquinaria->id);
            DB::commit();

            session()->flash('message', "Devolución realizada exitosamente: {$maquinaria->maquinaria}");
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
        DB::beginTransaction();
        try {
            $tipoMovimiento = $this->resolverTipo('Mantenimiento Maquinaria');
            $maquinaria     = Maquinaria::findOrFail($this->maquinaria_id);

            MovimientoMaquinaria::create([
                'id_maquinaria'      => $maquinaria->id,
                'cantidad'           => 1,
                'id_tipo_movimiento' => $tipoMovimiento->id,
                'fecha'              => now(),
                'fecha_devolucion'   => null,
                'id_usuario'         => Auth::id(),
                'id_deposito_entrada'=> $maquinaria->id_deposito,
                'id_referencia'      => 0,
                'tipo_referencia'    => 'mantenimiento',
            ]);

            $this->actualizarCantidadMaquinaria($maquinaria->id);
            DB::commit();

            session()->flash('message', "Maquinaria enviada a mantenimiento: {$maquinaria->maquinaria}");
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
        if (!$maquinaria) return;

        $cantidad = $maquinaria->getCantidadEnDeposito($maquinaria->id_deposito);
        DB::table('maquinarias')->where('id', $maquinariaId)->update(['cantidad' => $cantidad]);
    }

    // =========================================================
    // TRANSFERENCIA MULTI-MAQUINARIA (modal separado)
    // =========================================================

    public function crearTransferencia()
    {
        if (!auth()->user()->puedeCrearTransferenciasMaquinarias()) {
            session()->flash('error', 'No tienes permisos para crear transferencias.');
            return;
        }
        $this->resetFormTransferencia();
        $this->showModalTransferencia = true;
    }

    public function cerrarModalTransferencia()
    {
        $this->showModalTransferencia = false;
        $this->resetFormTransferencia();
    }

    private function resetFormTransferencia()
    {
        $this->id_corralon_origen              = '';
        $this->id_corralon_destino             = '';
        $this->id_deposito_origen_tf           = '';
        $this->id_deposito_destino_tf          = '';
        $this->maquinarias_transferencia       = [];
        $this->search_maquinaria_transferencia = '';
        $this->mostrar_lista_transferencia     = false;
        $this->resetErrorBag();
    }

    public function updatedIdCorralonOrigen()
    {
        $this->id_deposito_origen_tf           = '';
        $this->maquinarias_transferencia       = [];
        $this->search_maquinaria_transferencia = '';
        $this->mostrar_lista_transferencia     = false;
        $this->resetErrorBag('id_deposito_origen_tf');
    }

    public function updatedIdCorralonDestino()
    {
        $this->id_deposito_destino_tf = '';
        $this->resetErrorBag('id_deposito_destino_tf');
    }

    public function updatedIdDepositoOrigenTf()
    {
        $this->maquinarias_transferencia       = [];
        $this->search_maquinaria_transferencia = '';
        $this->mostrar_lista_transferencia     = false;
        $this->resetErrorBag('id_deposito_origen_tf');

        if (!empty($this->id_deposito_origen_tf) && $this->id_deposito_origen_tf == $this->id_deposito_destino_tf) {
            $this->addError('id_deposito_destino_tf', 'El depósito destino debe ser diferente al de origen.');
        } else {
            $this->resetErrorBag('id_deposito_destino_tf');
        }
    }

    public function updatedIdDepositoDestinoTf()
    {
        $this->resetErrorBag('id_deposito_destino_tf');

        if (!empty($this->id_deposito_destino_tf) && !empty($this->id_deposito_origen_tf)
            && $this->id_deposito_destino_tf == $this->id_deposito_origen_tf) {
            $this->addError('id_deposito_destino_tf', 'El depósito destino debe ser diferente al de origen.');
        }
    }

    public function mostrarListaTransferencia()
    {
        $this->mostrar_lista_transferencia = true;
    }

    public function updatedSearchMaquinariaTransferencia()
    {
        $this->mostrar_lista_transferencia = true;
    }

    public function agregarMaquinariaTransferencia($maquinariaId)
    {
        $maquinaria = Maquinaria::with(['categoriaMaquinaria'])->find($maquinariaId);

        if (!$maquinaria || $maquinaria->id_deposito != $this->id_deposito_origen_tf) {
            return;
        }

        $existe = collect($this->maquinarias_transferencia)->contains('id', $maquinariaId);
        if (!$existe) {
            $cantidadDisponible = $maquinaria->getCantidadEnDeposito($this->id_deposito_origen_tf);
            $this->maquinarias_transferencia[] = [
                'id'                  => $maquinaria->id,
                'nombre'              => $maquinaria->maquinaria,
                'categoria'           => $maquinaria->categoriaMaquinaria->nombre,
                'cantidad_disponible' => $cantidadDisponible,
                'cantidad'            => '',
            ];
            $this->resetErrorBag('maquinarias_transferencia');
        }

        $this->search_maquinaria_transferencia = '';
        $this->mostrar_lista_transferencia     = false;
    }

    public function removerMaquinariaTransferencia($index)
    {
        unset($this->maquinarias_transferencia[$index]);
        $this->maquinarias_transferencia = array_values($this->maquinarias_transferencia);
    }

    public function updatedMaquinariasTransferencia($value, $key)
    {
        if (str_contains($key, '.cantidad')) {
            $index      = explode('.', $key)[0];
            $cantidad   = intval($value);
            $disponible = intval($this->maquinarias_transferencia[$index]['cantidad_disponible'] ?? 0);

            if ($cantidad > $disponible) {
                $this->addError(
                    "maquinarias_transferencia.{$index}.cantidad",
                    "No puede transferir más de {$disponible} unidades disponibles"
                );
            } else {
                $this->resetErrorBag("maquinarias_transferencia.{$index}.cantidad");
            }

            if ($cantidad < 1 && $value !== '') {
                $this->addError(
                    "maquinarias_transferencia.{$index}.cantidad",
                    "La cantidad debe ser al menos 1"
                );
            }
        }
    }

    public function guardarTransferencia()
    {
        if (!auth()->user()->puedeCrearTransferenciasMaquinarias()) {
            session()->flash('error', 'No tienes permisos para crear transferencias.');
            $this->cerrarModalTransferencia();
            return;
        }

        try {
            if (empty($this->id_deposito_origen_tf)) {
                $this->addError('id_deposito_origen_tf', 'Debe seleccionar un depósito de origen.');
                return;
            }
            if (empty($this->id_deposito_destino_tf)) {
                $this->addError('id_deposito_destino_tf', 'Debe seleccionar un depósito de destino.');
                return;
            }
            if ($this->id_deposito_origen_tf == $this->id_deposito_destino_tf) {
                $this->addError('id_deposito_destino_tf', 'El depósito destino debe ser diferente al de origen.');
                return;
            }
            if (empty($this->maquinarias_transferencia)) {
                $this->addError('maquinarias_transferencia', 'Debe seleccionar al menos una maquinaria para transferir.');
                return;
            }

            foreach ($this->maquinarias_transferencia as $index => $item) {
                if (empty($item['cantidad']) || intval($item['cantidad']) < 1) {
                    $this->addError("maquinarias_transferencia.{$index}.cantidad", 'Debe ingresar una cantidad válida.');
                    session()->flash('error', "Debe ingresar una cantidad válida para {$item['nombre']}.");
                    return;
                }
                if (intval($item['cantidad']) > intval($item['cantidad_disponible'])) {
                    $this->addError("maquinarias_transferencia.{$index}.cantidad", "No puede transferir más de {$item['cantidad_disponible']} unidades disponibles.");
                    session()->flash('error', "La cantidad de {$item['nombre']} excede las unidades disponibles.");
                    return;
                }
            }

            DB::beginTransaction();

            $tipoEntrada = $this->resolverTipo('Transferencia Entrada');
            $tipoSalida  = $this->resolverTipo('Transferencia Salida');
            $total       = 0;

            foreach ($this->maquinarias_transferencia as $item) {
                $maquinariaOrigen = Maquinaria::findOrFail($item['id']);
                $cantidad         = intval($item['cantidad']);

                $cantidadDisponible = $maquinariaOrigen->getCantidadEnDeposito($this->id_deposito_origen_tf);
                if ($cantidad > $cantidadDisponible) {
                    throw new \Exception("Solo hay {$cantidadDisponible} unidades disponibles de {$maquinariaOrigen->maquinaria} en el depósito origen.");
                }

                $maquinariaDestino = Maquinaria::where('maquinaria', $maquinariaOrigen->maquinaria)
                    ->where('id_deposito', $this->id_deposito_destino_tf)
                    ->where('id_categoria_maquinaria', $maquinariaOrigen->id_categoria_maquinaria)
                    ->first();

                if (!$maquinariaDestino) {
                    $maquinariaDestino = Maquinaria::create([
                        'maquinaria'              => $maquinariaOrigen->maquinaria,
                        'id_categoria_maquinaria' => $maquinariaOrigen->id_categoria_maquinaria,
                        'id_deposito'             => $this->id_deposito_destino_tf,
                        'estado'                  => 'disponible',
                        'cantidad'                => 0,
                        'descripcion'             => $maquinariaOrigen->descripcion,
                        'modelo'                  => $maquinariaOrigen->modelo,
                        'numero_serie'            => null,
                        'anio_fabricacion'        => $maquinariaOrigen->anio_fabricacion,
                    ]);
                }

                MovimientoMaquinaria::create([
                    'id_maquinaria'       => $maquinariaDestino->id,
                    'cantidad'            => $cantidad,
                    'id_tipo_movimiento'  => $tipoEntrada->id,
                    'fecha'               => now(),
                    'id_usuario'          => Auth::id(),
                    'id_deposito_entrada' => $this->id_deposito_destino_tf,
                    'id_referencia'       => $maquinariaOrigen->id,
                    'tipo_referencia'     => 'deposito',
                ]);

                MovimientoMaquinaria::create([
                    'id_maquinaria'       => $maquinariaOrigen->id,
                    'cantidad'            => $cantidad,
                    'id_tipo_movimiento'  => $tipoSalida->id,
                    'fecha'               => now(),
                    'id_usuario'          => Auth::id(),
                    'id_deposito_entrada' => $this->id_deposito_origen_tf,
                    'id_referencia'       => $maquinariaDestino->id,
                    'tipo_referencia'     => 'deposito',
                ]);

                $this->actualizarCantidadMaquinaria($maquinariaOrigen->id);
                $this->actualizarCantidadMaquinaria($maquinariaDestino->id);
                $total++;
            }

            DB::commit();
            \Illuminate\Support\Facades\Cache::flush();

            $depOrigen  = Deposito::find($this->id_deposito_origen_tf);
            $depDestino = Deposito::find($this->id_deposito_destino_tf);

            session()->flash('message', "Transferencia realizada: {$total} maquinaria(s) desde {$depOrigen->deposito} hacia {$depDestino->deposito}.");
            $this->cerrarModalTransferencia();

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al realizar la transferencia: ' . $e->getMessage());
            \Log::error('Error en guardarTransferencia maquinarias: ' . $e->getMessage());
        }
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
