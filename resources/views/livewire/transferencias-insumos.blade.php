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
                    placeholder="Buscar transferencias..."
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
                @if($filtro_fecha_desde || $filtro_fecha_hasta || $filtro_deposito_origen || $filtro_deposito_destino || $filtro_usuario || $filtro_insumo || $filtro_categoria)
                    <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-blue-600 rounded-full">
                        {{ collect([$filtro_fecha_desde, $filtro_fecha_hasta, $filtro_deposito_origen, $filtro_deposito_destino, $filtro_usuario, $filtro_insumo, $filtro_categoria])->filter()->count() }}
                    </span>
                @endif
            </button>
            <button 
                wire:click="crear" 
                class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 transition-all duration-200 flex items-center justify-center gap-2 font-medium"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                Nueva Transferencia
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
                @if($filtro_fecha_desde || $filtro_fecha_hasta || $filtro_deposito_origen || $filtro_deposito_destino || $filtro_usuario || $filtro_insumo || $filtro_categoria)
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

                <!-- Filtro Depósito Origen -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Depósito Origen</label>
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

                <!-- Filtro Depósito Destino -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Depósito Destino</label>
                    <select 
                        wire:model.live="filtro_deposito_destino"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                    >
                        <option value="">Todos los depósitos</option>
                        @foreach($depositos as $deposito)
                            <option value="{{ $deposito->id }}">{{ $deposito->deposito }}</option>
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

    <!-- Lista de Transferencias Agrupadas -->
    <div class="space-y-4">
        @forelse ($transferencias as $transferencia)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Encabezado de la transferencia -->
                <div class="px-6 py-4 hover:bg-gray-50 transition-colors cursor-pointer" wire:click="toggleTransferencia({{ $transferencia->id }})">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 grid grid-cols-5 gap-4 items-center">
                            <!-- Fecha -->
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $transferencia->fecha->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $transferencia->created_at->format('H:i') }}</div>
                            </div>

                            <!-- Ruta de transferencia -->
                            <div class="col-span-2">
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-full">
                                        {{ $transferencia->depositoOrigen->deposito }}
                                    </span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                    <span class="px-3 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                                        {{ $transferencia->depositoDestino->deposito }}
                                    </span>
                                </div>
                            </div>

                            <!-- Cantidad de items -->
                            <div>
                                <span class="px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-700 rounded-full">
                                    {{ $transferencia->movimientos->where('cantidad', '>', 0)->count() }} insumos
                                </span>
                            </div>

                            <!-- Usuario -->
                            <div class="text-right">
                                <div class="text-xs text-gray-500">Por: {{ $transferencia->usuario->name }}</div>
                            </div>
                        </div>

                        <!-- Botón expandir/colapsar -->
                        <button type="button" class="ml-4 p-2 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5 text-gray-400 transform transition-transform {{ in_array($transferencia->id, $transferencias_expandidas) ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>
                    @if($transferencia->observaciones)
                        <div class="mt-2 text-xs text-gray-500 italic">
                            Obs: {{ $transferencia->observaciones }}
                        </div>
                    @endif
                </div>

                <!-- Detalle de insumos (expandible) -->
                @if(in_array($transferencia->id, $transferencias_expandidas))
                    <div class="border-t border-gray-100">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Insumo</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Categoría</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-50">
                                @foreach($transferencia->movimientos->where('cantidad', '>', 0) as $movimiento)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-3 text-sm font-medium text-gray-900">
                                            {{ $movimiento->insumo->insumo }}
                                        </td>
                                        <td class="px-6 py-3">
                                            <span class="px-2 py-1 text-xs font-medium bg-blue-50 text-blue-700 rounded">
                                                {{ $movimiento->insumo->categoriaInsumo->nombre }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3">
                                            <span class="text-sm font-semibold text-green-600">
                                                +{{ number_format($movimiento->cantidad, 2) }} {{ $movimiento->insumo->unidad }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12">
                <div class="flex flex-col items-center justify-center text-gray-400">
                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                    <p class="text-sm font-medium">No se encontraron transferencias</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Paginación -->
    <div class="mt-6">
        {{ $transferencias->links() }}
    </div>

    <!-- Modal (sin cambios) -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" wire:click="cerrarModal"></div>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-visible shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <form wire:submit.prevent="guardar">
                        <div class="bg-white px-6 pt-6 pb-14 rounded-xl">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xl font-semibold text-gray-900">Nueva Transferencia</h3>
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
                                <!-- Buscador de Insumos -->
                                <div class="relative">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Agregar Insumos *</label>
                                    
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            wire:model.live="search_insumo"
                                            wire:focus="mostrarLista"
                                            placeholder="Buscar insumo para agregar..."
                                            class="w-full px-4 py-2.5 pr-10 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                            autocomplete="off"
                                        >
                                        
                                        <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>

                                    @if($mostrar_lista && $insumos_filtrados->count() > 0)
                                        <div class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                                            <ul class="py-2">
                                                @foreach($insumos_filtrados as $insumo)
                                                    <li>
                                                        <button 
                                                            type="button"
                                                            wire:click="agregarInsumo({{ $insumo->id }})"
                                                            class="w-full text-left px-4 py-3 hover:bg-blue-50 transition-colors duration-150 border-b border-gray-100 last:border-b-0"
                                                        >
                                                            <div class="flex justify-between items-start">
                                                                <div class="flex-1">
                                                                    <div class="text-sm font-medium text-gray-900">{{ $insumo->insumo }}</div>
                                                                    <div class="text-xs text-gray-500 mt-1">
                                                                        {{ $insumo->categoriaInsumo->nombre }} • {{ $insumo->deposito->deposito }}
                                                                    </div>
                                                                </div>
                                                                <div class="text-right ml-3">
                                                                    <div class="text-sm font-semibold text-green-600">
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

                                    @error('insumos_a_transferir') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                @if(count($insumos_a_transferir) > 0)
                                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                            <h4 class="text-sm font-semibold text-gray-700">Insumos a Transferir ({{ count($insumos_a_transferir) }})</h4>
                                        </div>
                                        <div class="divide-y divide-gray-100">
                                            @foreach($insumos_a_transferir as $index => $item)
                                                <div class="p-4 hover:bg-gray-50 transition-colors">
                                                    <div class="flex items-start gap-4">
                                                        <div class="flex-1">
                                                            <div class="text-sm font-medium text-gray-900">{{ $item['insumo'] }}</div>
                                                            <div class="text-xs text-gray-500 mt-1">
                                                                {{ $item['categoria'] }} • {{ $item['deposito_origen'] }}
                                                            </div>
                                                            <div class="text-xs text-green-600 mt-1">
                                                                Stock disponible: {{ number_format($item['stock_actual'], 2) }} {{ $item['unidad'] }}
                                                            </div>
                                                        </div>

                                                        <div class="w-32">
                                                            <label class="block text-xs font-medium text-gray-600 mb-1">Cantidad</label>
                                                            <input 
                                                                type="number" 
                                                                step="0.01"
                                                                wire:model="insumos_a_transferir.{{ $index }}.cantidad"
                                                                max="{{ $item['stock_actual'] }}"
                                                                placeholder="0.00"
                                                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                                                            >
                                                            @error('insumos_a_transferir.' . $index . '.cantidad') 
                                                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                                                            @enderror
                                                        </div>

                                                        <button 
                                                            type="button"
                                                            wire:click="eliminarInsumo({{ $index }})"
                                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                            title="Eliminar"
                                                        >
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div>
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

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                                        <textarea 
                                            wire:model="observaciones"
                                            rows="3"
                                            placeholder="Motivo de la transferencia (opcional)"
                                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                        ></textarea>
                                        @error('observaciones') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                @else
                                    <div class="text-center py-8 text-gray-400">
                                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        <p class="text-sm">No hay insumos agregados</p>
                                        <p class="text-xs mt-1">Use el buscador para agregar insumos a la transferencia</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 rounded-xl">
                            <button 
                                type="button"
                                wire:click="cerrarModal"
                                class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-100 transition-colors duration-200 font-medium"
                            >
                                Cancelar
                            </button>
                            <button 
                                type="submit"
                                class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 shadow-lg shadow-blue-500/30 transition-all duration-200 font-medium"
                                @disabled(count($insumos_a_transferir) == 0)
                            >
                                Realizar Transferencia ({{ count($insumos_a_transferir) }})
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>