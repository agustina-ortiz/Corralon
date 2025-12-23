{{-- resources/views/livewire/transferencias-insumos.blade.php --}}
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
                @if($filtro_fecha_desde || $filtro_fecha_hasta || $filtro_deposito_origen || $filtro_usuario || $filtro_insumo || $filtro_categoria || $filtro_tipo_movimiento)
                    <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-blue-600 rounded-full">
                        {{ collect([$filtro_fecha_desde, $filtro_fecha_hasta, $filtro_deposito_origen, $filtro_usuario, $filtro_insumo, $filtro_categoria, $filtro_tipo_movimiento])->filter()->count() }}
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
                @if($filtro_fecha_desde || $filtro_fecha_hasta || $filtro_deposito_origen || $filtro_usuario || $filtro_insumo || $filtro_categoria || $filtro_tipo_movimiento)
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

                <!-- Filtro Insumo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Insumo</label>
                    <input 
                        type="text" 
                        wire:model.live="filtro_insumo"
                        placeholder="Nombre del insumo"
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
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Insumo</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Depósito</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Cantidad</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Usuario</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse ($movimientos as $movimiento)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $movimiento->fecha->format('d/m/Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $movimiento->created_at->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $movimiento->insumo->insumo }}</div>
                            <div class="text-xs text-gray-500">{{ $movimiento->insumo->categoriaInsumo->nombre }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">
                                {{ $movimiento->insumo->deposito->deposito }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $tipoClasses = [
                                    'I' => 'bg-green-100 text-green-700',
                                    'E' => 'bg-red-100 text-red-700',
                                ];
                                $clase = $tipoClasses[$movimiento->tipoMovimiento->tipo] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium {{ $clase }} rounded-full">
                                {{ $movimiento->tipoMovimiento->tipo_movimiento }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-semibold {{ $movimiento->tipoMovimiento->tipo === 'I' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $movimiento->tipoMovimiento->tipo === 'I' ? '+' : '-' }}{{ number_format($movimiento->cantidad, 2) }} {{ $movimiento->insumo->unidad }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $movimiento->usuario->name }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p class="text-sm font-medium">No se encontraron movimientos</p>
                            </div>
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
                                    <h3 class="text-xl font-semibold text-gray-900">Nuevo Movimiento de Insumo</h3>
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
                                                Paso 1: Seleccionar Insumo
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
                                <!-- PASO 1: Seleccionar Insumo -->
                                @if($paso_actual === 1)
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar Insumo *</label>
                                        
                                        <div class="relative">
                                            <input 
                                                type="text" 
                                                wire:model.live="search_insumo"
                                                wire:focus="mostrarLista"
                                                placeholder="Buscar por nombre, categoría o depósito..."
                                                class="w-full px-4 py-2.5 pr-10 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                                autocomplete="off"
                                            >
                                            
                                            <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                        </div>

                                        @if($mostrar_lista && $insumos_filtrados->count() > 0)
                                            <div class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg max-h-96 overflow-y-auto">
                                                <ul class="py-2">
                                                    @foreach($insumos_filtrados as $insumo)
                                                        <li>
                                                            <button 
                                                                type="button"
                                                                wire:click="seleccionarInsumo({{ $insumo->id }})"
                                                                class="w-full text-left px-4 py-3 hover:bg-blue-50 transition-colors duration-150 border-b border-gray-100 last:border-b-0"
                                                            >
                                                                <div class="flex justify-between items-start">
                                                                    <div class="flex-1">
                                                                        <div class="text-sm font-medium text-gray-900">{{ $insumo->insumo }}</div>
                                                                        <div class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                                                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded">{{ $insumo->categoriaInsumo->nombre }}</span>
                                                                            <span>•</span>
                                                                            <span>{{ $insumo->deposito->deposito }}</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-right ml-3">
                                                                        <div class="text-sm font-semibold {{ $insumo->stock_actual > 0 ? 'text-green-600' : 'text-orange-600' }}">
                                                                            {{ number_format($insumo->stock_actual, 2) }}
                                                                        </div>
                                                                        <div class="text-xs text-gray-400">{{ $insumo->unidad }}</div>
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        @if($mostrar_lista && $search_insumo && $insumos_filtrados->count() == 0)
                                            <div class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg">
                                                <div class="px-4 py-8 text-center text-gray-400">
                                                    <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                                    </svg>
                                                    <p class="text-sm">No se encontraron insumos</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    @if(!$search_insumo)
                                        <div class="text-center py-12 text-gray-400">
                                            <svg class="w-16 h-16 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                            <p class="text-sm font-medium">Comience buscando un insumo</p>
                                            <p class="text-xs mt-1">Escriba el nombre del insumo, categoría o depósito</p>
                                        </div>
                                    @endif
                                @endif

                                <!-- PASO 2: Seleccionar Tipo de Movimiento -->
                                @if($paso_actual === 2 && $insumo_seleccionado)
                                    <div>
                                        <!-- Info del insumo seleccionado -->
                                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="text-sm font-semibold text-blue-900">{{ $insumo_seleccionado->insumo }}</div>
                                                    <div class="text-xs text-blue-700 mt-1">
                                                        {{ $insumo_seleccionado->categoriaInsumo->nombre }} • {{ $insumo_seleccionado->deposito->deposito }}
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-sm font-bold {{ $insumo_seleccionado->stock_actual > 0 ? 'text-green-600' : 'text-orange-600' }}">
                                                        Stock: {{ number_format($insumo_seleccionado->stock_actual, 2) }} {{ $insumo_seleccionado->unidad }}
                                                    </div>
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
                                @if($paso_actual === 3 && $insumo_seleccionado && $tipo_movimiento)
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
                                                        <div class="text-xs text-gray-600">{{ $insumo_seleccionado->insumo }}</div>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-xs text-gray-600">Stock actual</div>
                                                    <div class="text-sm font-bold text-blue-900">
                                                        {{ number_format($insumo_seleccionado->stock_actual, 2) }} {{ $insumo_seleccionado->unidad }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Campo Cantidad -->
                                        <div class="mb-5">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Cantidad *</label>
                                            <div class="relative">
                                                <input 
                                                    type="number" 
                                                    step="0.01"
                                                    wire:model="cantidad"
                                                    @if(in_array($tipo_movimiento, ['transferencia', 'ajuste_negativo']))
                                                        max="{{ $insumo_seleccionado->stock_actual }}"
                                                    @endif
                                                    placeholder="0.00"
                                                    class="w-full px-4 py-2.5 pr-20 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                                >
                                                <span class="absolute right-4 top-1/2 transform -translate-y-1/2 text-sm text-gray-500 font-medium">
                                                    {{ $insumo_seleccionado->unidad }}
                                                </span>
                                            </div>
                                            @error('cantidad') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                            
                                            @if(in_array($tipo_movimiento, ['transferencia', 'ajuste_negativo']))
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Máximo disponible: {{ number_format($insumo_seleccionado->stock_actual, 2) }} {{ $insumo_seleccionado->unidad }}
                                                </p>
                                            @endif
                                        </div>

                                        <!-- Depósito Destino (solo para transferencia) -->
                                        @if($tipo_movimiento === 'transferencia')
                                            <div class="mb-5">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Depósito Destino *</label>
                                                <select 
                                                    wire:model="id_deposito_destino"
                                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                                >
                                                    <option value="">Seleccione un depósito</option>
                                                    @foreach($depositos_disponibles as $deposito)
                                                        <option value="{{ $deposito->id }}">{{ $deposito->deposito }}</option>
                                                    @endforeach
                                                </select>
                                                @error('id_deposito_destino') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
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

    <!-- Debug de errores (temporal) -->
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