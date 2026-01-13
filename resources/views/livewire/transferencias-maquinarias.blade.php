{{-- resources/views/livewire/transferencias-maquinarias.blade.php --}}
<div>
    <!-- Header con búsqueda y botones -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
        <div class="flex-1 max-w-md">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input 
                    type="text" 
                    wire:model.live="search" 
                    placeholder="Buscar movimientos..."
                    class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                >
            </div>
        </div>
        <div class="flex gap-3">
            <button 
                wire:click="$toggle('showFilters')" 
                class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-200 flex items-center justify-center gap-2 font-medium shadow-sm"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                Filtros
                @if($filtro_fecha_desde || $filtro_fecha_hasta || $filtro_deposito_origen || $filtro_usuario || $filtro_maquinaria || $filtro_categoria || $filtro_tipo_movimiento)
                    <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-blue-600 rounded-full">
                        {{ collect([$filtro_fecha_desde, $filtro_fecha_hasta, $filtro_deposito_origen, $filtro_usuario, $filtro_maquinaria, $filtro_categoria, $filtro_tipo_movimiento])->filter()->count() }}
                    </span>
                @endif
            </button>
            <button 
                wire:click="crear" 
                class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 transition-all duration-200 flex items-center justify-center gap-2 font-medium"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nuevo Movimiento
            </button>
        </div>
    </div>

    <!-- Panel de Filtros -->
    <div 
        x-data="{ show: @entangle('showFilters') }"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-4"
        class="mb-6 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
        style="display: none;"
    >
        <div class="px-6 py-5">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filtros Avanzados
                </h3>
                @if($filtro_fecha_desde || $filtro_fecha_hasta || $filtro_deposito_origen || $filtro_usuario || $filtro_maquinaria || $filtro_categoria || $filtro_tipo_movimiento)
                    <button 
                        wire:click="limpiarFiltros"
                        class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Limpiar filtros
                    </button>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Filtro Fecha Desde -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                    <input 
                        type="date" 
                        wire:model.live="filtro_fecha_desde"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                    >
                </div>

                <!-- Filtro Fecha Hasta -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                    <input 
                        type="date" 
                        wire:model.live="filtro_fecha_hasta"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                    >
                </div>

                <!-- Filtro Depósito -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Depósito</label>
                    <select 
                        wire:model.live="filtro_deposito_origen"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                    >
                        <option value="">Todos los depósitos</option>
                        @foreach($depositos as $deposito)
                            <option value="{{ $deposito->id }}">{{ $deposito->deposito }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Tipo de Movimiento -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Movimiento</label>
                    <select 
                        wire:model.live="filtro_tipo_movimiento"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                    >
                        <option value="">Todos los tipos</option>
                        @foreach($tipos_movimiento as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->tipo_movimiento }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Usuario -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Usuario</label>
                    <select 
                        wire:model.live="filtro_usuario"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                    >
                        <option value="">Todos los usuarios</option>
                        @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Maquinaria -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Maquinaria</label>
                    <input 
                        type="text" 
                        wire:model.live="filtro_maquinaria"
                        placeholder="Nombre de la maquinaria"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                    >
                </div>

                <!-- Filtro Categoría -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                    <select 
                        wire:model.live="filtro_categoria"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                    >
                        <option value="">Todas las categorías</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensaje de éxito -->
    @if (session()->has('message'))
        <div class="mb-6 px-4 py-3.5 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{!! session('message') !!}</span>
        </div>
    @endif

    <!-- Mensaje de error -->
    @if (session()->has('error'))
        <div class="mb-6 px-4 py-3.5 bg-gradient-to-r from-red-50 to-rose-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{!! session('error') !!}</span>
        </div>
    @endif

    <!-- Lista de Movimientos -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Maquinaria</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Cantidad</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Origen</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Destino</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Usuario</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse ($movimientos as $movimiento)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <!-- Fecha -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $movimiento->fecha->format('d/m/Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $movimiento->created_at->format('H:i') }}</div>
                        </td>
                        
                        <!-- Maquinaria -->
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $movimiento->maquinaria->maquinaria }}</div>
                            <div class="text-xs text-gray-500">{{ $movimiento->maquinaria->categoriaMaquinaria->nombre }}</div>
                        </td>
                        
                        <!-- Cantidad -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                @php
                                    $cantidad = $movimiento->cantidad_historica;
                                @endphp
                                @if($cantidad > 0)
                                    <span class="text-sm font-semibold text-green-700">{{ $cantidad }}</span>
                                @elseif($cantidad == 0)
                                    <span class="text-sm font-semibold text-orange-700">{{ $cantidad }}</span>
                                @else
                                    <span class="text-sm font-semibold text-red-700">{{ $cantidad }}</span>
                                @endif
                                <span class="text-xs text-gray-500">
                                    {{ $cantidad == 1 ? 'unidad' : 'unidades' }}
                                </span>
                            </div>
                            
                            <!-- Indicador de entrada/salida -->
                            @if($movimiento->tipoMovimiento->tipo === 'I')
                                <div class="flex items-center gap-1 mt-1">
                                    <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                    </svg>
                                    <span class="text-xs text-green-600">
                                        +{{ $movimiento->cantidad }} {{ $movimiento->cantidad == 1 ? 'Entrada' : 'Entradas' }}
                                    </span>
                                </div>
                            @else
                                <div class="flex items-center gap-1 mt-1">
                                    <svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                    </svg>
                                    <span class="text-xs text-red-600">
                                        -{{ $movimiento->cantidad }} {{ $movimiento->cantidad == 1 ? 'Salida' : 'Salidas' }}
                                    </span>
                                </div>
                            @endif
                        </td>
                        
                        <!-- COLUMNA ORIGEN -->
                        <td class="px-6 py-4">
                            @php
                                $esTransferencia = str_contains($movimiento->tipoMovimiento->tipo_movimiento, 'Transferencia');
                                
                                if ($esTransferencia) {
                                    $esSalida = $movimiento->tipoMovimiento->tipo === 'E';
                                    
                                    if ($esSalida) {
                                        // Este es el movimiento de SALIDA - el origen está en depositoEntrada
                                        $depositoOrigen = $movimiento->depositoEntrada;
                                    } else {
                                        // Este es el movimiento de ENTRADA - buscar el complementario
                                        $movimientoSalida = \App\Models\MovimientoMaquinaria::where('id_maquinaria', '!=', $movimiento->id_maquinaria)
                                            ->where('id_referencia', $movimiento->id_maquinaria)
                                            ->whereHas('tipoMovimiento', function($q) {
                                                $q->where('tipo', 'E')
                                                ->where('tipo_movimiento', 'like', '%Transferencia%');
                                            })
                                            ->whereBetween('created_at', [
                                                $movimiento->created_at->copy()->subSeconds(5),
                                                $movimiento->created_at->copy()->addSeconds(5)
                                            ])
                                            ->first();
                                        
                                        $depositoOrigen = $movimientoSalida ? $movimientoSalida->depositoEntrada : null;
                                    }
                                } else {
                                    // Para movimientos que NO son transferencias
                                    if ($movimiento->tipoMovimiento->tipo === 'E') {
                                        // Es una SALIDA - mostrar el depósito de donde sale
                                        $depositoOrigen = $movimiento->depositoEntrada;
                                    } else {
                                        // Es una ENTRADA - no tiene origen relevante
                                        $depositoOrigen = null;
                                    }
                                }
                            @endphp
                            
                            @if($depositoOrigen)
                                <span class="block w-full text-center px-3 py-1.5 text-xs bg-red-100 text-red-700 rounded-lg font-medium">
                                    {{ $depositoOrigen->deposito }}
                                </span>
                            @else
                                <span class="block w-full text-center px-3 py-1.5 text-xs font-medium bg-gray-100 text-gray-400 rounded-lg">
                                    —
                                </span>
                            @endif
                        </td>

                        <!-- COLUMNA DESTINO -->
                        <td class="px-6 py-4">
                            @php
                                if ($esTransferencia) {
                                    if ($esSalida) {
                                        // Este es el movimiento de SALIDA - buscar el complementario
                                        $movimientoEntrada = \App\Models\MovimientoMaquinaria::where('id_maquinaria', '!=', $movimiento->id_maquinaria)
                                            ->where('id_referencia', $movimiento->id_maquinaria)
                                            ->whereHas('tipoMovimiento', function($q) {
                                                $q->where('tipo', 'I')
                                                ->where('tipo_movimiento', 'like', '%Transferencia%');
                                            })
                                            ->whereBetween('created_at', [
                                                $movimiento->created_at->copy()->subSeconds(5),
                                                $movimiento->created_at->copy()->addSeconds(5)
                                            ])
                                            ->first();
                                        
                                        $depositoDestino = $movimientoEntrada ? $movimientoEntrada->depositoEntrada : null;
                                    } else {
                                        // Este es el movimiento de ENTRADA - el destino está en depositoEntrada
                                        $depositoDestino = $movimiento->depositoEntrada;
                                    }
                                } else {
                                    // Para movimientos que NO son transferencias
                                    if ($movimiento->tipoMovimiento->tipo === 'I') {
                                        // Es una ENTRADA - mostrar el depósito de destino
                                        $depositoDestino = $movimiento->depositoEntrada;
                                    } else {
                                        // Es una SALIDA - no tiene destino relevante
                                        $depositoDestino = null;
                                    }
                                }
                            @endphp
                            
                            @if($depositoDestino)
                                <span class="block w-full text-center px-3 py-1.5 text-xs bg-green-100 text-green-700 rounded-lg font-medium">
                                    {{ $depositoDestino->deposito }}
                                </span>
                            @else
                                <span class="block w-full text-center px-3 py-1.5 text-xs font-medium bg-gray-100 text-gray-400 rounded-lg">
                                    —
                                </span>
                            @endif
                        </td>

                        <!-- Estado ACTUAL de la maquinaria -->
                        <td class="px-6 py-4">
                            @if($movimiento->estado_calculado === 'disponible')
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                                    Disponible
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-700 rounded-full">
                                    No Disponible
                                </span>
                            @endif
                        </td>
                        
                        <!-- Tipo de Movimiento -->
                        <td class="px-6 py-4">
                            @php
                                $tipoClasses = [
                                    'I' => 'bg-green-100 text-green-700',
                                    'E' => 'bg-red-100 text-red-700',
                                ];
                                $clase = $tipoClasses[$movimiento->tipoMovimiento->tipo] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="block w-full text-center px-3 py-1.5 text-xs font-medium {{ $clase }} rounded-lg">
                                {{ $movimiento->tipoMovimiento->tipo_movimiento }}
                            </span>
                        </td>
                        
                        <!-- Usuario -->
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $movimiento->usuario->name }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            No se encontraron movimientos
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-6">
        {{ $movimientos->links() }}
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" wire:click="cerrarModal"></div>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-visible shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <form wire:submit.prevent="guardar">
                        <div class="bg-white px-6 pt-6 pb-4 rounded-xl">
                            <!-- Header con indicador de pasos -->
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex-1">
                                    <h3 class="text-xl font-semibold text-gray-900">Nuevo Movimiento de Maquinaria</h3>
                                    <div class="flex items-center gap-2 mt-3">
                                        @for($i = 1; $i <= 3; $i++)
                                            <div class="flex items-center">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $paso_actual >= $i ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500' }} font-medium text-sm transition-all duration-200">
                                                    {{ $i }}
                                                </div>
                                                @if($i < 3)
                                                    <div class="w-12 h-1 {{ $paso_actual > $i ? 'bg-blue-600' : 'bg-gray-200' }} transition-all duration-200"></div>
                                                @endif
                                            </div>
                                        @endfor
                                        <div class="ml-3 text-sm text-gray-600">
                                            @if($paso_actual === 1)
                                                Paso 1: Seleccionar Maquinaria
                                            @elseif($paso_actual === 2)
                                                Paso 2: Tipo de Movimiento
                                            @else
                                                Paso 3: Completar Datos
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    wire:click="cerrarModal"
                                    class="text-gray-400 hover:text-gray-600 transition-colors"
                                >
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="space-y-5">
                                <!-- PASO 1: Seleccionar Maquinaria -->
                                @if($paso_actual === 1)
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar Maquinaria *</label>
                                        
                                        <div class="relative">
                                            <input 
                                                type="text" 
                                                wire:model.live="search_maquinaria"
                                                wire:focus="mostrarLista"
                                                placeholder="Buscar por nombre, categoría o depósito..."
                                                class="w-full px-4 py-2.5 pr-10 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                                autocomplete="off"
                                            >
                                            
                                            <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                        </div>

                                        @if($mostrar_lista && $maquinarias_filtradas->count() > 0)
                                            <div class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg max-h-96 overflow-y-auto">
                                                <ul class="py-2">
                                                   @foreach($maquinarias_filtradas as $maquinaria)
                                                        <li>
                                                            <button 
                                                                type="button"
                                                                wire:click="seleccionarMaquinaria({{ $maquinaria->id }})"
                                                                class="w-full text-left px-4 py-3 hover:bg-blue-50 transition-colors duration-150 border-b border-gray-100 last:border-b-0"
                                                            >
                                                                <div class="flex justify-between items-start">
                                                                    <div class="flex-1">
                                                                        <div class="text-sm font-medium text-gray-900">{{ $maquinaria->maquinaria }}</div>
                                                                        <div class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                                                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded">{{ $maquinaria->categoriaMaquinaria->nombre }}</span>
                                                                            <span>•</span>
                                                                            <span>{{ $maquinaria->deposito->deposito }}</span>
                                                                            <span>•</span>
                                                                            <span class="px-2 py-0.5 {{ $maquinaria->cantidad > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} rounded font-medium">
                                                                                {{ $maquinaria->cantidad }} {{ $maquinaria->cantidad == 1 ? 'unidad' : 'unidades' }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-right ml-3">
                                                                        @php
                                                                            $tieneStock = $maquinaria->cantidad > 0;
                                                                        @endphp
                                                                        @if($tieneStock)
                                                                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                                                                                Disponible
                                                                            </span>
                                                                        @else
                                                                            <span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-700 rounded-full">
                                                                                Sin Stock
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        @if($mostrar_lista && $search_maquinaria && $maquinarias_filtradas->count() == 0)
                                            <div class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg">
                                                <div class="px-4 py-8 text-center text-gray-400">
                                                    <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                                    </svg>
                                                    <p class="text-sm">No se encontraron maquinarias</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    @if(!$search_maquinaria)
                                        <div class="text-center py-12 text-gray-400">
                                            <svg class="w-16 h-16 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                            <p class="text-sm font-medium">Comience buscando una maquinaria</p>
                                            <p class="text-xs mt-1">Escriba el nombre de la maquinaria, categoría o depósito</p>
                                        </div>
                                    @endif
                                @endif

                                <!-- PASO 2: Info de la maquinaria seleccionada -->
                                @if($paso_actual === 2 && $maquinaria_seleccionada)
                                    <div>
                                        <!-- Info de la maquinaria seleccionada -->
                                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="text-sm font-semibold text-blue-900">{{ $maquinaria_seleccionada->maquinaria }}</div>
                                                    <div class="text-xs text-blue-700 mt-1 flex items-center gap-2">
                                                        <span>{{ $maquinaria_seleccionada->categoriaMaquinaria->nombre }}</span>
                                                        <span>•</span>
                                                        <span>{{ $maquinaria_seleccionada->deposito->deposito }}</span>
                                                        <span>•</span>
                                                        <!-- ✅ Mostrar cantidad -->
                                                        <span class="px-2 py-0.5 bg-white text-blue-900 rounded font-medium">
                                                            {{ $maquinaria_seleccionada->cantidad }} {{ $maquinaria_seleccionada->cantidad == 1 ? 'unidad' : 'unidades' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    @if($maquinaria_seleccionada->estado === 'disponible')
                                                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                                                            Disponible
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-700 rounded-full">
                                                            No Disponible
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <label class="block text-sm font-medium text-gray-700 mb-4">Seleccione el Tipo de Movimiento</label>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach($tipos_movimiento_disponibles as $tipo)
                                                <button 
                                                    type="button"
                                                    wire:click="seleccionarTipoMovimiento('{{ $tipo['key'] }}')"
                                                    class="p-5 border-2 border-gray-200 rounded-xl hover:border-{{ $tipo['color'] }}-500 hover:bg-{{ $tipo['color'] }}-50 transition-all duration-200 text-left group"
                                                >
                                                    <div class="flex items-start gap-4">
                                                        <div class="p-3 bg-{{ $tipo['color'] }}-100 rounded-lg group-hover:bg-{{ $tipo['color'] }}-200 transition-colors">
                                                            <svg class="w-6 h-6 text-{{ $tipo['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tipo['icon'] }}"></path>
                                                            </svg>
                                                        </div>
                                                        <div class="flex-1">
                                                            <div class="text-base font-semibold text-gray-900 mb-1">{{ $tipo['nombre'] }}</div>
                                                            <div class="text-sm text-gray-600">{{ $tipo['descripcion'] }}</div>
                                                        </div>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- PASO 3: Completar Datos -->
                                @if($paso_actual === 3 && $maquinaria_seleccionada && $tipo_movimiento)
                                    <div>
                                        <!-- Info del movimiento -->
                                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4 mb-6">
                                            <div class="flex items-center justify-between mb-3">
                                                <div class="flex items-center gap-3">
                                                    @php
                                                        $tipoInfo = collect($tipos_movimiento_disponibles)->firstWhere('key', $tipo_movimiento);
                                                    @endphp
                                                    <div class="p-2 bg-white rounded-lg">
                                                        <svg class="w-5 h-5 text-{{ $tipoInfo['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tipoInfo['icon'] }}"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-semibold text-gray-900">{{ $tipoInfo['nombre'] }}</div>
                                                        <div class="text-xs text-gray-600">{{ $maquinaria_seleccionada->maquinaria }}</div>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    @if($maquinaria_seleccionada->estado === 'disponible')
                                                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                                                            Disponible
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-700 rounded-full">
                                                            No Disponible
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        @if($tipo_movimiento === 'asignacion')
                                            <div class="mb-5">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Depósito de Origen *</label>
                                                <select 
                                                    wire:model.live="id_deposito_origen"
                                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                                >
                                                    <option value="">Seleccione un depósito</option>
                                                    @foreach($depositos_disponibles as $deposito)
                                                        @php
                                                            $cantidadEnDeposito = $maquinaria_seleccionada->getCantidadEnDeposito($deposito->id);
                                                        @endphp
                                                        <option value="{{ $deposito->id }}">
                                                            {{ $deposito->deposito }} ({{ $cantidadEnDeposito }} {{ $cantidadEnDeposito == 1 ? 'unidad' : 'unidades' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('id_deposito_origen') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                            </div>

                                            @if($id_deposito_origen)
                                                <div class="mb-5">
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                                        Cantidad a Asignar *
                                                        <span class="text-xs text-gray-500 font-normal">
                                                            (Disponible: {{ $maquinaria_seleccionada->getCantidadEnDeposito($id_deposito_origen) }})
                                                        </span>
                                                    </label>
                                                    <div class="flex items-center gap-3">
                                                        <button 
                                                            type="button"
                                                            wire:click="decrementarCantidad"
                                                            class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                                            {{ $cantidad_a_asignar <= 1 ? 'disabled' : '' }}
                                                        >
                                                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                            </svg>
                                                        </button>
                                                        
                                                        <input 
                                                            type="number" 
                                                            wire:model.live="cantidad_a_asignar"
                                                            min="1"
                                                            max="{{ $maquinaria_seleccionada->getCantidadEnDeposito($id_deposito_origen) }}"
                                                            class="w-24 px-4 py-2.5 border border-gray-200 rounded-xl text-center focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                                        >
                                                        
                                                        <button 
                                                            type="button"
                                                            wire:click="incrementarCantidad"
                                                            class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                                            {{ $cantidad_a_asignar >= $maquinaria_seleccionada->getCantidadEnDeposito($id_deposito_origen) ? 'disabled' : '' }}
                                                        >
                                                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                            </svg>
                                                        </button>

                                                        <div class="flex-1">
                                                            <input 
                                                                type="range" 
                                                                wire:model.live="cantidad_a_asignar"
                                                                min="1"
                                                                max="{{ $maquinaria_seleccionada->getCantidadEnDeposito($id_deposito_origen) }}"
                                                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600"
                                                            >
                                                        </div>
                                                    </div>
                                                    @error('cantidad_a_asignar') 
                                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                                                    @enderror
                                                </div>
                                            @endif

                                            <!-- Fecha de Devolución para asignación -->
                                            <div class="mb-5">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Devolución Esperada *</label>
                                                <input 
                                                    type="date" 
                                                    wire:model="fecha_devolucion_esperada"
                                                    min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                                >
                                                @error('fecha_devolucion_esperada') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                            </div>
                                        @endif

                                        @if($tipo_movimiento === 'carga_stock')
                                            <div class="mb-5">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Depósito Destino *</label>
                                                <select 
                                                    wire:model.live="id_deposito_origen"
                                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                                >
                                                    <option value="">Seleccione un depósito</option>
                                                    @foreach($depositos_disponibles as $deposito)
                                                        <option value="{{ $deposito->id }}">{{ $deposito->deposito }}</option>
                                                    @endforeach
                                                </select>
                                                @error('id_deposito_origen') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                            </div>

                                            @if($id_deposito_origen)
                                                <div class="mb-5">
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                                        Cantidad a Cargar *
                                                        <span class="text-xs text-gray-500 font-normal">(Máximo: 1000 unidades)</span>
                                                    </label>
                                                    <div class="flex items-center gap-3">
                                                        <button 
                                                            type="button"
                                                            wire:click="decrementarCantidad"
                                                            class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                                            {{ $cantidad_a_cargar <= 1 ? 'disabled' : '' }}
                                                        >
                                                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                            </svg>
                                                        </button>
                                                        
                                                        <input 
                                                            type="number" 
                                                            wire:model.live="cantidad_a_cargar"
                                                            min="1"
                                                            max="1000"
                                                            class="w-24 px-4 py-2.5 border border-gray-200 rounded-xl text-center focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                                        >
                                                        
                                                        <button 
                                                            type="button"
                                                            wire:click="incrementarCantidad"
                                                            class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                                            {{ $cantidad_a_cargar >= 1000 ? 'disabled' : '' }}
                                                        >
                                                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                            </svg>
                                                        </button>

                                                        <div class="flex-1">
                                                            <input 
                                                                type="range" 
                                                                wire:model.live="cantidad_a_cargar"
                                                                min="1"
                                                                max="100"
                                                                step="1"
                                                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                                                            >
                                                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                                                <span>1</span>
                                                                <span>100</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @error('cantidad_a_cargar') 
                                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                                                    @enderror
                                                </div>
                                            @endif
                                        @endif

                                        <!-- Transferencia -->
                                        @if($tipo_movimiento === 'transferencia')

                                            <!-- Cantidad a Transferir -->
                                            <div class="mb-5">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    Cantidad a Transferir *
                                                    <span class="text-xs text-gray-500 font-normal">
                                                        (Disponible: {{ $maquinaria_seleccionada->cantidad_disponible }})
                                                    </span>
                                                </label>
                                                <div class="flex items-center gap-3">
                                                    <button 
                                                        type="button"
                                                        wire:click="decrementarCantidad"
                                                        class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                                        {{ $cantidad_a_transferir <= 1 ? 'disabled' : '' }}
                                                    >
                                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                        </svg>
                                                    </button>
                                                    
                                                    <input 
                                                        type="number" 
                                                        wire:model.live="cantidad_a_transferir"
                                                        min="1"
                                                        max="{{ $maquinaria_seleccionada->cantidad_disponible }}"
                                                        class="w-24 px-4 py-2.5 border border-gray-200 rounded-xl text-center focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                                    >
                                                    
                                                    <button 
                                                        type="button"
                                                        wire:click="incrementarCantidad"
                                                        class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                                        {{ $cantidad_a_transferir >= $maquinaria_seleccionada->cantidad_disponible ? 'disabled' : '' }}
                                                    >
                                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                        </svg>
                                                    </button>

                                                    <div class="flex-1">
                                                        <input 
                                                            type="range" 
                                                            wire:model.live="cantidad_a_transferir"
                                                            min="1"
                                                            max="{{ $maquinaria_seleccionada->cantidad_disponible }}"
                                                            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600"
                                                        >
                                                    </div>
                                                </div>
                                                @error('cantidad_a_transferir') 
                                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                                                @enderror
                                            </div>
                                        @endif

                                        <!-- Depósito Destino (solo para transferencia) -->
                                        @if($tipo_movimiento === 'transferencia')
                                            <div class="mb-5">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Depósito Destino *</label>
                                                <select 
                                                    wire:model.live="id_deposito_destino"
                                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                                >
                                                    <option value="">Seleccione un depósito</option>
                                                    @foreach($depositos_disponibles as $deposito)
                                                        @if($deposito->id != $maquinaria_seleccionada->id_deposito)
                                                        <option value="{{ $deposito->id }}">{{ $deposito->deposito }}</option>
                                                    @endif
                                                    @endforeach
                                                </select>
                                                @error('id_deposito_destino') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                            </div>
                                        @endif

                                        <!-- Fecha de Devolución (solo para asignación y devolución) -->
                                        @if(in_array($tipo_movimiento, ['asignacion', 'devolucion']))
                                            <div class="mb-5">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Devolución Esperada</label>
                                                <input 
                                                    type="date" 
                                                    wire:model="fecha_devolucion_esperada"
                                                    min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                                >
                                                @error('fecha_devolucion_esperada') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                            </div>
                                        @endif

                                        <!-- Observaciones -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                                            <textarea 
                                                wire:model="observaciones"
                                                rows="3"
                                                placeholder="Motivo del movimiento (opcional)"
                                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                            ></textarea>
                                            @error('observaciones') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-between rounded-b-xl">
                            <div>
                                @if($paso_actual > 1)
                                    <button 
                                        type="button"
                                        wire:click="volverPaso"
                                        class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-100 transition-colors duration-200 font-medium flex items-center gap-2"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                        Volver
                                    </button>
                                @endif
                            </div>
                            
                            <div class="flex items-center gap-3">
                                <button 
                                    type="button"
                                    wire:click="cerrarModal"
                                    class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-100 transition-colors duration-200 font-medium"
                                >
                                    Cancelar
                                </button>
                                
                                @if($paso_actual === 3)
                                    <button 
                                        type="submit"
                                        class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 shadow-lg shadow-blue-500/30 transition-all duration-200 font-medium"
                                    >
                                        Guardar Movimiento
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Debug de errores -->
    @if (session()->has('error'))
        <div class="fixed bottom-4 right-4 z-[9999] max-w-md px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded-lg shadow-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <p class="font-semibold text-sm">Error</p>
                    <p class="text-sm mt-1">{!! session('error') !!}</p>
                </div>
                <button 
                    onclick="this.parentElement.parentElement.remove()"
                    class="text-red-500 hover:text-red-700"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif
</div>