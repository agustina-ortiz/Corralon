<?php
// app/Livewire/TransferenciasInsumos.php

namespace App\Livewire;

use App\Models\Insumo;
use App\Models\Deposito;
use App\Models\MovimientoInsumo;
use App\Models\MovimientoEncabezado;
use App\Models\TipoMovimiento;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransferenciasInsumos extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    
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

    protected $rules = [
        'insumos_a_transferir' => 'required|array|min:1',
        'insumos_a_transferir.*.id_insumo' => 'required|exists:insumos,id',
        'insumos_a_transferir.*.cantidad' => 'required|numeric|min:0.01',
        'id_deposito_destino' => 'required|exists:depositos,id',
        'observaciones' => 'nullable|string|max:500',
    ];

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

    public function render()
    {
        // Obtener transferencias agrupadas
        $transferencias = MovimientoEncabezado::with([
                'movimientos.insumo.categoriaInsumo',
                'movimientos.insumo.deposito',
                'depositoOrigen',
                'depositoDestino',
                'usuario'
            ])
            ->when($this->search, function($query) {
                $query->whereHas('movimientos.insumo', function($q) {
                    $q->where('insumo', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Insumos filtrados para el listado
        $insumos_filtrados = collect();
        
        if ($this->mostrar_lista || $this->search_insumo) {
            // IDs de insumos ya agregados
            $ids_agregados = collect($this->insumos_a_transferir)->pluck('id_insumo')->toArray();
            
            $insumos_filtrados = Insumo::with(['categoriaInsumo', 'deposito'])
                ->where('stock_actual', '>', 0)
                ->whereNotIn('id', $ids_agregados)
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
                ->get();
        }

        return view('livewire.transferencias-insumos', [
            'transferencias' => $transferencias,
            'insumos_filtrados' => $insumos_filtrados,
        ])->layout('layouts.app', [
            'header' => 'Transferencias de Insumos'
        ]);
    }

    public function toggleTransferencia($id)
    {
        if (in_array($id, $this->transferencias_expandidas)) {
            $this->transferencias_expandidas = array_diff($this->transferencias_expandidas, [$id]);
        } else {
            $this->transferencias_expandidas[] = $id;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSearchInsumo()
    {
        $this->mostrar_lista = true;
    }

    public function agregarInsumo($insumoId)
    {
        $insumo = Insumo::with(['categoriaInsumo', 'deposito'])->find($insumoId);
        
        if ($insumo) {
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
            
            // Si es el primer insumo, cargar depósitos disponibles
            if (count($this->insumos_a_transferir) == 1) {
                $this->cargarDepositosDisponibles($insumo->id_deposito);
            }
            
            $this->search_insumo = '';
            $this->mostrar_lista = false;
        }
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
        $this->depositos_disponibles = Deposito::where('id', '!=', $id_deposito_origen)
            ->orderBy('deposito')
            ->get();
    }

    public function limpiarBusqueda()
    {
        $this->search_insumo = '';
        $this->mostrar_lista = false;
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

        // Validaciones adicionales
        $errores = [];
        
        foreach ($this->insumos_a_transferir as $index => $item) {
            $insumo = Insumo::find($item['id_insumo']);
            
            if (!$insumo) {
                $errores[] = "Insumo no encontrado.";
                continue;
            }
            
            if ($item['cantidad'] > $insumo->stock_actual) {
                $errores[] = "{$item['insumo']}: La cantidad ({$item['cantidad']}) no puede ser mayor al stock disponible (" . number_format($insumo->stock_actual, 2) . ").";
            }
            
            if ($insumo->id_deposito == $this->id_deposito_destino) {
                $errores[] = "{$item['insumo']}: No puede transferir al mismo depósito de origen.";
            }
        }
        
        if (count($errores) > 0) {
            session()->flash('error', implode('<br>', $errores));
            return;
        }

        try {
            DB::beginTransaction();

            // 1. Crear encabezado de movimiento
            $primer_insumo = Insumo::find($this->insumos_a_transferir[0]['id_insumo']);
            
            $encabezado = MovimientoEncabezado::create([
                'fecha' => now(),
                'id_deposito_origen' => $primer_insumo->id_deposito,
                'id_deposito_destino' => $this->id_deposito_destino,
                'observaciones' => $this->observaciones,
                'id_usuario' => Auth::id(),
            ]);

            $items_procesados = [];

            // 2. Procesar cada insumo
            foreach ($this->insumos_a_transferir as $item) {
                $insumo_origen = Insumo::findOrFail($item['id_insumo']);

                // Descontar del insumo origen
                $insumo_origen->stock_actual -= $item['cantidad'];
                $insumo_origen->save();

                // Buscar o crear insumo en depósito destino
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

                // Sumar al insumo destino
                $insumo_destino->stock_actual += $item['cantidad'];
                $insumo_destino->save();

                // Registrar movimiento de salida
                MovimientoInsumo::create([
                    'id_movimiento_encabezado' => $encabezado->id,
                    'id_insumo' => $insumo_origen->id,
                    'id_tipo_movimiento' => $this->id_tipo_transferencia,
                    'cantidad' => -$item['cantidad'],
                    'fecha' => now(),
                    'id_usuario' => Auth::id(),
                    'id_deposito_entrada' => $this->id_deposito_destino,
                    'id_referencia' => $insumo_destino->id,
                    'tipo_referencia' => 'transferencia',
                ]);

                // Registrar movimiento de entrada
                MovimientoInsumo::create([
                    'id_movimiento_encabezado' => $encabezado->id,
                    'id_insumo' => $insumo_destino->id,
                    'id_tipo_movimiento' => $this->id_tipo_transferencia,
                    'cantidad' => $item['cantidad'],
                    'fecha' => now(),
                    'id_usuario' => Auth::id(),
                    'id_deposito_entrada' => $insumo_origen->id_deposito,
                    'id_referencia' => $insumo_origen->id,
                    'tipo_referencia' => 'transferencia',
                ]);

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
        $this->insumos_a_transferir = [];
        $this->id_deposito_destino = '';
        $this->observaciones = '';
        $this->search_insumo = '';
        $this->mostrar_lista = false;
        $this->depositos_disponibles = [];
        $this->resetErrorBag();
    }
}