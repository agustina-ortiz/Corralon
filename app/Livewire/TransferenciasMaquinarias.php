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

    protected function rules()
    {
        $rules = [
            'observaciones' => 'nullable|string|max:500',
        ];

        if ($this->tipo_movimiento === 'transferencia') {
            $rules['id_deposito_destino'] = 'required|exists:depositos,id|different:maquinaria_seleccionada.id_deposito';
        }

        if (in_array($this->tipo_movimiento, ['asignacion', 'devolucion'])) {
            $rules['fecha_devolucion_esperada'] = 'nullable|date|after:today';
        }

        return $rules;
    }

    protected $messages = [
        'id_deposito_destino.required' => 'Debe seleccionar un depósito destino.',
        'id_deposito_destino.different' => 'El depósito destino debe ser diferente al de origen.',
        'fecha_devolucion_esperada.after' => 'La fecha de devolución debe ser posterior a hoy.',
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

        $tipos = [
            [
                'key' => 'asignacion',
                'nombre' => 'Asignación',
                'descripcion' => 'Asignar maquinaria a un empleado o evento',
                'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                'color' => 'blue',
                'disponible' => $this->maquinaria_seleccionada->estado === 'disponible'
            ],
            [
                'key' => 'mantenimiento',
                'nombre' => 'Enviar a Mantenimiento',
                'descripcion' => 'Marcar maquinaria como en mantenimiento',
                'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                'color' => 'orange',
                'disponible' => true
            ],
        ];

        // Solo si está disponible puede hacer transferencia
        if ($this->maquinaria_seleccionada->estado === 'disponible') {
            $tipos[] = [
                'key' => 'transferencia',
                'nombre' => 'Transferencia',
                'descripcion' => 'Mover maquinaria a otro depósito',
                'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
                'color' => 'purple',
                'disponible' => true
            ];
        }

        // Solo si está NO disponible puede hacer devolución
        if ($this->maquinaria_seleccionada->estado === 'no disponible') {
            $tipos[] = [
                'key' => 'devolucion',
                'nombre' => 'Devolución',
                'descripcion' => 'Devolver maquinaria al depósito',
                'icon' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6',
                'color' => 'green',
                'disponible' => true
            ];
        }

        return collect($tipos)->where('disponible', true)->values()->toArray();
    }

    public function render()
    {
        $depositosAccesibles = $this->getDepositosAccesibles();

        // Obtener movimientos con filtros
        $movimientos = MovimientoMaquinaria::with([
                'maquinaria.categoriaMaquinaria',
                'maquinaria.deposito',
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
                $query->whereHas('maquinaria', function($q) {
                    $q->where('id_deposito', $this->filtro_deposito_origen);
                });
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

        // Maquinarias filtradas para el listado (paso 1)
        $maquinarias_filtradas = collect();
        
        if ($this->paso_actual === 1 && ($this->mostrar_lista || $this->search_maquinaria)) {
            $maquinarias_filtradas = Maquinaria::with(['categoriaMaquinaria', 'deposito'])
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
                ->get(['id', 'maquinaria', 'id_categoria_maquinaria', 'id_deposito', 'estado']);
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
        
        // Si es transferencia, cargar depósitos disponibles
        if ($tipo === 'transferencia') {
            $depositosAccesibles = $this->getDepositosAccesibles();
            $this->depositos_disponibles = Deposito::whereIn('id', $depositosAccesibles)
                ->where('id', '!=', $this->maquinaria_seleccionada->id_deposito)
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
                throw new \Exception('No se encontró la maquinaria seleccionada.');
            }

            if ($maquinaria->estado !== 'disponible') {
                throw new \Exception('La maquinaria no está disponible para asignación.');
            }

            MovimientoMaquinaria::create([
                'id_maquinaria' => $maquinaria->id,
                'id_tipo_movimiento' => $tipoMovimiento->id,
                'fecha' => now(),
                'fecha_devolucion' => $this->fecha_devolucion_esperada,
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $maquinaria->id_deposito,
                'id_referencia' => 0,
                'tipo_referencia' => 'empleado', // ✅ Usar 'empleado' del ENUM
            ]);

            // Cambiar estado a no disponible
            $maquinaria->update(['estado' => 'no disponible']);

            DB::commit();

            $mensaje = "Asignación realizada exitosamente: {$maquinaria->maquinaria}";
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

            if ($maquinaria->estado !== 'disponible') {
                throw new \Exception('La maquinaria no está disponible para transferencia.');
            }

            DB::beginTransaction();

            $tipoMovimientoSalida = TipoMovimiento::firstOrCreate([
                'tipo_movimiento' => 'Transferencia Salida Maquinaria',
                'tipo' => 'E'
            ]);

            $tipoMovimientoEntrada = TipoMovimiento::firstOrCreate([
                'tipo_movimiento' => 'Transferencia Entrada Maquinaria',
                'tipo' => 'I'
            ]);

            $encabezado = MovimientoEncabezado::create([
                'fecha' => now(),
                'id_deposito_origen' => $maquinaria->id_deposito,
                'id_deposito_destino' => $this->id_deposito_destino,
                'observaciones' => $this->observaciones,
                'id_usuario' => Auth::id(),
            ]);

            // ✅ Movimiento de SALIDA
            MovimientoMaquinaria::create([
                'id_maquinaria' => $maquinaria->id,
                'id_movimiento_encabezado' => $encabezado->id,
                'id_tipo_movimiento' => $tipoMovimientoSalida->id,
                'fecha' => now(),
                'fecha_devolucion' => null,
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $maquinaria->id_deposito, // ✅ Depósito origen
                'id_referencia' => $this->id_deposito_destino,
                'tipo_referencia' => 'maquina', // ✅ Usar 'maquina' del ENUM
            ]);

            // ✅ Movimiento de ENTRADA
            MovimientoMaquinaria::create([
                'id_maquinaria' => $maquinaria->id,
                'id_movimiento_encabezado' => $encabezado->id,
                'id_tipo_movimiento' => $tipoMovimientoEntrada->id,
                'fecha' => now(),
                'fecha_devolucion' => null,
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $this->id_deposito_destino, // ✅ Depósito destino
                'id_referencia' => $maquinaria->id_deposito,
                'tipo_referencia' => 'maquina', // ✅ Usar 'maquina' del ENUM
            ]);

            // Actualizar depósito de la maquinaria
            $maquinaria->update(['id_deposito' => $this->id_deposito_destino]);

            DB::commit();

            $deposito_origen = Deposito::find($encabezado->id_deposito_origen);
            $deposito_destino = Deposito::find($this->id_deposito_destino);
            $mensaje = "Transferencia realizada exitosamente: {$maquinaria->maquinaria} desde {$deposito_origen->deposito} hacia {$deposito_destino->deposito}";
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

            $tipoMovimiento = TipoMovimiento::firstOrCreate([
                'tipo_movimiento' => 'Devolución Maquinaria',
                'tipo' => 'I'
            ]);

            $maquinaria = Maquinaria::find($this->maquinaria_id);
            
            if (!$maquinaria) {
                throw new \Exception('No se encontró la maquinaria seleccionada.');
            }

            if ($maquinaria->estado !== 'no disponible') {
                throw new \Exception('La maquinaria no está asignada.');
            }

            MovimientoMaquinaria::create([
                'id_maquinaria' => $maquinaria->id,
                'id_tipo_movimiento' => $tipoMovimiento->id,
                'fecha' => now(),
                'fecha_devolucion' => null,
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $maquinaria->id_deposito,
                'id_referencia' => 0,
                'tipo_referencia' => 'empleado', // ✅ Usar 'empleado' del ENUM
            ]);

            // Cambiar estado a disponible
            $maquinaria->update(['estado' => 'disponible']);

            DB::commit();

            $mensaje = "Devolución realizada exitosamente: {$maquinaria->maquinaria}";
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
                'id_tipo_movimiento' => $tipoMovimiento->id,
                'fecha' => now(),
                'fecha_devolucion' => null,
                'id_usuario' => Auth::id(),
                'id_deposito_entrada' => $maquinaria->id_deposito,
                'id_referencia' => 0,
                'tipo_referencia' => 'maquina', // ✅ Usar 'maquina' del ENUM
            ]);

            // Cambiar estado a no disponible
            $maquinaria->update(['estado' => 'no disponible']);

            DB::commit();

            $mensaje = "Maquinaria enviada a mantenimiento exitosamente: {$maquinaria->maquinaria}";
            session()->flash('message', $mensaje);
            
            \Illuminate\Support\Facades\Cache::flush();
            $this->cerrarModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al enviar a mantenimiento: ' . $e->getMessage());
            $this->cerrarModal();
            \Log::error('Error en guardarMantenimiento: ' . $e->getMessage());
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
        $this->observaciones = '';
        $this->fecha_devolucion_esperada = '';
        $this->search_maquinaria = '';
        $this->mostrar_lista = false;
        $this->depositos_disponibles = [];
        $this->tipos_movimiento_disponibles = [];
        $this->resetErrorBag();
    }
}