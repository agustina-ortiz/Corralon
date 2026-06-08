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
                @if($filtro_corralon || $filtro_fecha_desde || $filtro_fecha_hasta || $filtro_deposito_origen || $filtro_usuario || $filtro_maquinaria || $filtro_categoria || $filtro_tipo_movimiento)
                    <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-blue-600 rounded-full">
                        {{ collect([$filtro_corralon, $filtro_fecha_desde, $filtro_fecha_hasta, $filtro_deposito_origen, $filtro_usuario, $filtro_maquinaria, $filtro_categoria, $filtro_tipo_movimiento])->filter()->count() }}
                    </span>
                @endif
            </button>

            {{-- Botón Nuevo Movimiento solo si tiene permisos --}}
            @if($puedeCrear)
            <button
                wire:click="crear"
                class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 transition-all duration-200 flex items-center justify-center gap-2 font-medium"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nuevo Movimiento
            </button>
            @endif
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
                @if($filtro_corralon || $filtro_fecha_desde || $filtro_fecha_hasta || $filtro_deposito_origen || $filtro_usuario || $filtro_maquinaria || $filtro_categoria || $filtro_tipo_movimiento)
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
                <!-- Filtro Corralón -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Corralón</label>
                    <select
                        wire:model.live="filtro_corralon"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                    >
                        <option value="">Todos los corralones</option>
                        @foreach($corralonesFiltro as $corralon)
                            <option value="{{ $corralon->id }}">{{ $corralon->descripcion }}</option>
                        @endforeach
                    </select>
                </div>

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

    <!-- Panel de Asignaciones Pendientes (solo si hay pendientes) -->
    @if($puedeCrear && $asignacionesPendientes->count() > 0)
    <div class="mb-6">
        <button
            wire:click="$toggle('showAsignacionesPendientes')"
            class="w-full flex items-center justify-between px-5 py-3 bg-white border-2 border-orange-200 rounded-xl hover:bg-orange-50 transition-colors duration-200"
        >
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-semibold text-orange-800">Asignaciones Pendientes de Reposición</span>
            </div>
            <svg class="w-5 h-5 text-orange-500 transition-transform duration-200 {{ $showAsignacionesPendientes ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        @if($showAsignacionesPendientes)
            <div class="mt-2 bg-white border border-orange-100 rounded-xl overflow-hidden">
                @if($asignacionesPendientes->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-orange-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-orange-700 uppercase">Maquinaria</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-orange-700 uppercase">Destino</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-orange-700 uppercase">Pendiente</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-orange-700 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($asignacionesPendientes as $index => $item)
                                    <tr class="hover:bg-orange-50/50">
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $item['maquinaria_nombre'] }}</div>
                                            <div class="text-xs text-gray-500">{{ $item['categoria'] }} • {{ $item['deposito'] }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm text-gray-900">{{ $item['referencia_nombre'] }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 text-sm font-bold text-orange-700 bg-orange-100 rounded-full">
                                                {{ $item['cantidad_pendiente'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center gap-2">
                                                <div class="flex items-center gap-1" x-data="{ cantidad: 1 }">
                                                    <input
                                                        type="number"
                                                        x-model="cantidad"
                                                        min="1"
                                                        max="{{ $item['cantidad_pendiente'] }}"
                                                        class="w-16 px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-center focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"
                                                    >
                                                    <button
                                                        type="button"
                                                        x-on:click="$wire.devolverAsignacion({{ $item['id_maquinaria'] }}, '{{ $item['tipo_referencia'] }}', '{{ $item['id_referencia'] }}', cantidad)"
                                                        class="px-3 py-1.5 text-xs font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition-colors"
                                                        title="Devolver"
                                                    >
                                                        Devolver
                                                    </button>
                                                </div>
                                                <button
                                                    type="button"
                                                    wire:click="darDeBajaAsignacion({{ $item['id_maquinaria'] }}, '{{ $item['tipo_referencia'] }}', '{{ $item['id_referencia'] }}')"
                                                    wire:confirm="¿Está seguro de dar de baja {{ $item['cantidad_pendiente'] }} unidad(es) de {{ $item['maquinaria_nombre'] }}? Esta acción NO devuelve stock."
                                                    class="px-3 py-1.5 text-xs font-medium text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors"
                                                    title="Dar de baja (no devuelve stock)"
                                                >
                                                    Baja
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm">No hay asignaciones pendientes de reposición</p>
                    </div>
                @endif
            </div>
        @endif
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
                            @if($movimiento->tipoMovimiento->esEntrada())
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
                                $nombreTipo = $movimiento->tipoMovimiento->tipo_movimiento;
                                $esSalidaTransferencia = $nombreTipo === 'Transferencia Salida' || $nombreTipo === 'Transferencia Salida Maquinaria';
                                $esEntradaTransferencia = $nombreTipo === 'Transferencia Entrada' || $nombreTipo === 'Transferencia Entrada Maquinaria';
                                $esTransferencia = $esSalidaTransferencia || $esEntradaTransferencia;

                                if ($esTransferencia) {
                                    if ($esSalidaTransferencia) {
                                        // Movimiento de SALIDA — el origen es el depósito del movimiento
                                        $depositoOrigen = $movimiento->depositoEntrada;
                                    } else {
                                        // Movimiento de ENTRADA — buscar el complementario de salida
                                        $movimientoSalida = \App\Models\MovimientoMaquinaria::where('id_referencia', $movimiento->id_maquinaria)
                                            ->whereHas('tipoMovimiento', fn($q) => $q->where('tipo_movimiento', 'like', '%Transferencia Salida%'))
                                            ->whereBetween('created_at', [
                                                $movimiento->created_at->copy()->subSeconds(5),
                                                $movimiento->created_at->copy()->addSeconds(5),
                                            ])
                                            ->first();
                                        $depositoOrigen = $movimientoSalida?->depositoEntrada;
                                    }
                                } else {
                                    // No es transferencia — origen solo aplica a salidas
                                    $depositoOrigen = $movimiento->tipoMovimiento->esSalida()
                                        ? $movimiento->depositoEntrada
                                        : null;
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
                                    if ($esSalidaTransferencia) {
                                        // Movimiento de SALIDA — buscar el complementario de entrada
                                        $movimientoEntrada = \App\Models\MovimientoMaquinaria::where('id_referencia', $movimiento->id_maquinaria)
                                            ->whereHas('tipoMovimiento', fn($q) => $q->where('tipo_movimiento', 'like', '%Transferencia Entrada%'))
                                            ->whereBetween('created_at', [
                                                $movimiento->created_at->copy()->subSeconds(5),
                                                $movimiento->created_at->copy()->addSeconds(5),
                                            ])
                                            ->first();
                                        $depositoDestino = $movimientoEntrada?->depositoEntrada;
                                    } else {
                                        // Movimiento de ENTRADA — el destino es el depósito del movimiento
                                        $depositoDestino = $movimiento->depositoEntrada;
                                    }
                                } else {
                                    // No es transferencia — destino solo aplica a entradas
                                    $depositoDestino = $movimiento->tipoMovimiento->esEntrada()
                                        ? $movimiento->depositoEntrada
                                        : null;
                                }
                            @endphp
                            
                            @if($depositoDestino)
                                <span class="block w-full text-center px-3 py-1.5 text-xs bg-green-100 text-green-700 rounded-lg font-medium">
                                    {{ $depositoDestino->deposito }}
                                </span>
                            @elseif($movimiento->id_secretaria || $movimiento->area)
                                <span class="block w-full text-center px-3 py-1.5 text-xs bg-purple-100 text-purple-700 rounded-lg font-medium">
                                    @if($movimiento->secretaria)
                                        {{ $movimiento->secretaria->secretaria }}
                                    @endif
                                    @if($movimiento->area)
                                        <span class="block text-purple-500">{{ $movimiento->area }}</span>
                                    @endif
                                </span>
                            @elseif(in_array($movimiento->tipo_referencia, ['vehiculo', 'evento', 'empleado']) && $movimiento->id_referencia)
                                @php
                                    $refLabel = ['vehiculo' => 'Vehículo', 'evento' => 'Evento', 'empleado' => 'Empleado'][$movimiento->tipo_referencia];
                                    $refNombre = match($movimiento->tipo_referencia) {
                                        'vehiculo' => optional(\App\Models\Vehiculo::find($movimiento->id_referencia))->vehiculo,
                                        'evento'   => optional(\App\Models\Evento::find($movimiento->id_referencia))->evento,
                                        'empleado' => 'Legajo ' . $movimiento->id_referencia,
                                        default    => null,
                                    };
                                @endphp
                                <span class="block w-full text-center px-3 py-1.5 text-xs bg-blue-100 text-blue-700 rounded-lg font-medium">
                                    {{ $refNombre ?? $refLabel }}
                                    <span class="block text-blue-500">{{ $refLabel }}</span>
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
                                $clase = $movimiento->tipoMovimiento->esEntrada()
                                    ? 'bg-green-100 text-green-700'
                                    : ($movimiento->tipoMovimiento->esSalida() ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700');
                            @endphp
                            <span class="block w-full text-center px-3 py-1.5 text-xs font-medium {{ $clase }} rounded-lg">
                                {{ $movimiento->tipoMovimiento->tipo_movimiento }}
                            </span>
                            @if($movimiento->nro_orden_compra)
                                <div class="text-xs text-gray-500 text-center mt-1">OC: {{ $movimiento->nro_orden_compra }}</div>
                            @endif
                        </td>

                        <!-- Usuario -->
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <div class="flex items-center gap-2">
                                <span>{{ $movimiento->usuario->name }}</span>
                                @if($movimiento->comprobantes->count() > 0)
                                    <div x-data="{ open: false }" class="relative">
                                        <button type="button" @click="open = !open" class="p-1 text-blue-600 hover:bg-blue-50 rounded transition-colors" title="Ver comprobantes ({{ $movimiento->comprobantes->count() }})">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                            </svg>
                                        </button>
                                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 z-50 mt-1 w-64 bg-white border border-gray-200 rounded-xl shadow-lg p-3">
                                            <div class="text-xs font-semibold text-gray-700 mb-2">Comprobantes adjuntos</div>
                                            @foreach($movimiento->comprobantes as $comp)
                                                <div class="flex items-center justify-between gap-1 px-2 py-1.5 rounded-lg hover:bg-gray-50">
                                                    <span class="text-sm text-gray-800 truncate flex-1">{{ $comp->nombre_original }}</span>
                                                    <div class="flex items-center gap-1 flex-shrink-0">
                                                        <a href="{{ route('comprobantes-maquinaria.ver', $comp->id) }}" target="_blank" title="Ver" class="p-1 text-blue-600 hover:bg-blue-100 rounded transition-colors">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                        </a>
                                                        <a href="{{ route('comprobantes-maquinaria.descargar', $comp->id) }}" title="Descargar" class="p-1 text-green-600 hover:bg-green-100 rounded transition-colors">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                @if($puedeCrear && in_array($movimiento->tipoMovimiento->tipo_movimiento, ['Carga de Stock', 'Ajuste Positivo']))
                                    <button type="button" wire:click="abrirEdicion({{ $movimiento->id }})" class="p-1 text-amber-600 hover:bg-amber-50 rounded transition-colors" title="Editar N° orden, observaciones y comprobantes">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                @endif
                            </div>
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

                                        @if(in_array($tipo_movimiento, ['asignacion_con_reposicion', 'asignacion_sin_reposicion', 'entrada_reposicion', 'ajuste_negativo']))
                                            @if(in_array($tipo_movimiento, ['asignacion_con_reposicion', 'asignacion_sin_reposicion']))
                                                {{-- Depósito auto-determinado --}}
                                                <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl text-sm">
                                                    <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                    </svg>
                                                    <span class="text-blue-800">
                                                        Depósito de origen: <strong>{{ $maquinaria_seleccionada->deposito->deposito }}</strong>
                                                        &nbsp;—&nbsp;
                                                        @php $dispAsig = $maquinaria_seleccionada->getCantidadEnDeposito($maquinaria_seleccionada->id_deposito); @endphp
                                                        {{ $dispAsig }} {{ $dispAsig == 1 ? 'unidad disponible' : 'unidades disponibles' }}
                                                    </span>
                                                </div>
                                            @endif

                                            @if($tipo_movimiento === 'ajuste_negativo')
                                                {{-- Depósito y disponible (ajuste negativo) --}}
                                                <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm">
                                                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                    </svg>
                                                    <span class="text-red-800">
                                                        Depósito: <strong>{{ $maquinaria_seleccionada->deposito->deposito }}</strong>
                                                        — Disponible: <strong>{{ $maquinaria_seleccionada->getCantidadTotalDisponible() }}</strong> {{ $maquinaria_seleccionada->getCantidadTotalDisponible() == 1 ? 'unidad' : 'unidades' }}
                                                    </span>
                                                </div>
                                            @endif

                                            <!-- Selector de tipo de destino -->
                                            @php
                                                $destinoLabel = $tipo_movimiento === 'entrada_reposicion'
                                                    ? 'Devuelto desde *'
                                                    : ($tipo_movimiento === 'ajuste_negativo' ? 'Asignar a (opcional)' : 'Asignar a *');
                                            @endphp
                                            <div class="mb-5">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    {{ $destinoLabel }}
                                                </label>
                                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                                    <button
                                                        type="button"
                                                        wire:click="seleccionarTipoDestino('vehiculo')"
                                                        class="p-3 border-2 rounded-xl text-center transition-all duration-200 {{ $tipo_destino === 'vehiculo' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 hover:border-gray-300 text-gray-600' }}"
                                                    >
                                                        <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                                                        </svg>
                                                        <span class="text-sm font-medium">Vehículo</span>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        wire:click="seleccionarTipoDestino('evento')"
                                                        class="p-3 border-2 rounded-xl text-center transition-all duration-200 {{ $tipo_destino === 'evento' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 hover:border-gray-300 text-gray-600' }}"
                                                    >
                                                        <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                        <span class="text-sm font-medium">Evento</span>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        wire:click="seleccionarTipoDestino('empleado')"
                                                        class="p-3 border-2 rounded-xl text-center transition-all duration-200 {{ $tipo_destino === 'empleado' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 hover:border-gray-300 text-gray-600' }}"
                                                    >
                                                        <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                        </svg>
                                                        <span class="text-sm font-medium">Empleado</span>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        wire:click="seleccionarTipoDestino('secretaria')"
                                                        class="p-3 border-2 rounded-xl text-center transition-all duration-200 {{ $tipo_destino === 'secretaria' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 hover:border-gray-300 text-gray-600' }}"
                                                    >
                                                        <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                        </svg>
                                                        <span class="text-sm font-medium">Secretaría</span>
                                                    </button>
                                                </div>
                                                @error('tipo_destino') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                            </div>

                                            <!-- Buscar y seleccionar destino (vehículo/evento/empleado) -->
                                            @if(in_array($tipo_destino, ['vehiculo', 'evento', 'empleado']))
                                                <div class="mb-5 relative">
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                                        Seleccionar {{ $tipo_destino === 'vehiculo' ? 'Vehículo' : ($tipo_destino === 'evento' ? 'Evento' : 'Empleado') }} *
                                                    </label>

                                                    @if($id_referencia)
                                                        @php
                                                            if ($tipo_destino === 'vehiculo') {
                                                                $destinoSeleccionado = \App\Models\Vehiculo::find($id_referencia);
                                                            } elseif ($tipo_destino === 'evento') {
                                                                $destinoSeleccionado = \App\Models\Evento::find($id_referencia);
                                                            } else {
                                                                $destinoSeleccionado = \App\Models\EmpleadoMunicipal::find($id_referencia);
                                                            }
                                                        @endphp
                                                        <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl p-3">
                                                            <div>
                                                                @if($tipo_destino === 'vehiculo' && $destinoSeleccionado)
                                                                    <div class="text-sm font-medium text-green-900">{{ $destinoSeleccionado->vehiculo }}</div>
                                                                    <div class="text-xs text-green-700">{{ $destinoSeleccionado->patente }} • {{ $destinoSeleccionado->marca_modelo }}</div>
                                                                @elseif($tipo_destino === 'evento' && $destinoSeleccionado)
                                                                    <div class="text-sm font-medium text-green-900">{{ $destinoSeleccionado->evento }}</div>
                                                                    <div class="text-xs text-green-700">{{ $destinoSeleccionado->fecha?->format('d/m/Y') }} • {{ $destinoSeleccionado->ubicacion }}</div>
                                                                @elseif($tipo_destino === 'empleado' && $destinoSeleccionado)
                                                                    <div class="text-sm font-medium text-green-900">{{ $destinoSeleccionado->nombre_formateado }}</div>
                                                                    <div class="text-xs text-green-700">Legajo {{ $destinoSeleccionado->LEGAJO }} • DNI {{ number_format($destinoSeleccionado->DNI, 0, '', '.') }}</div>
                                                                @endif
                                                            </div>
                                                            <button type="button" wire:click="$set('id_referencia', '')" class="text-green-600 hover:text-green-800">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    @else
                                                        <div class="relative">
                                                            <input
                                                                type="text"
                                                                wire:model.live="search_destino"
                                                                wire:focus="$set('mostrar_lista_destino', true)"
                                                                placeholder="Buscar {{ $tipo_destino === 'vehiculo' ? 'por nombre, patente o marca...' : ($tipo_destino === 'evento' ? 'por nombre de evento...' : 'por nombre, legajo o DNI...') }}"
                                                                class="w-full px-4 py-2.5 pr-10 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                                                autocomplete="off"
                                                            >
                                                            <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                            </svg>
                                                        </div>

                                                        @if($mostrar_lista_destino)
                                                            @if($tipo_destino === 'vehiculo' && $vehiculos_destino->count() > 0)
                                                                <div class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                                                                    <ul class="py-2">
                                                                        @foreach($vehiculos_destino as $v)
                                                                            <li>
                                                                                <button type="button" wire:click="seleccionarDestino({{ $v->id }})" class="w-full text-left px-4 py-3 hover:bg-blue-50 transition-colors duration-150 border-b border-gray-100 last:border-b-0">
                                                                                    <div class="text-sm font-medium text-gray-900">{{ $v->vehiculo }}</div>
                                                                                    <div class="text-xs text-gray-500 mt-0.5">{{ $v->patente }} • {{ $v->marca_modelo }}</div>
                                                                                </button>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            @elseif($tipo_destino === 'evento' && $eventos_destino->count() > 0)
                                                                <div class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                                                                    <ul class="py-2">
                                                                        @foreach($eventos_destino as $ev)
                                                                            <li>
                                                                                <button type="button" wire:click="seleccionarDestino({{ $ev->id }})" class="w-full text-left px-4 py-3 hover:bg-blue-50 transition-colors duration-150 border-b border-gray-100 last:border-b-0">
                                                                                    <div class="text-sm font-medium text-gray-900">{{ $ev->evento }}</div>
                                                                                    <div class="text-xs text-gray-500 mt-0.5">{{ $ev->fecha?->format('d/m/Y') }} • {{ $ev->ubicacion }}</div>
                                                                                </button>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            @elseif($tipo_destino === 'empleado' && $empleados_destino->count() > 0)
                                                                <div class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                                                                    <ul class="py-2">
                                                                        @foreach($empleados_destino as $emp)
                                                                            <li>
                                                                                <button type="button" wire:click="seleccionarDestino({{ $emp->LEGAJO }})" class="w-full text-left px-4 py-3 hover:bg-blue-50 transition-colors duration-150 border-b border-gray-100 last:border-b-0">
                                                                                    <div class="text-sm font-medium text-gray-900">{{ $emp->nombre_formateado }}</div>
                                                                                    <div class="text-xs text-gray-500 mt-0.5">Legajo {{ $emp->LEGAJO }} • DNI {{ number_format($emp->DNI, 0, '', '.') }}</div>
                                                                                </button>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            @elseif($search_destino)
                                                                <div class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg">
                                                                    <div class="px-4 py-6 text-center text-gray-400">
                                                                        <p class="text-sm">No se encontraron {{ $tipo_destino === 'vehiculo' ? 'vehículos' : ($tipo_destino === 'evento' ? 'eventos' : 'empleados') }}</p>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endif
                                                    @endif
                                                    @error('id_referencia') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                                </div>
                                            @elseif($tipo_destino === 'secretaria')
                                                <!-- Secretaría y Área -->
                                                <div class="mb-5 grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Secretaría *</label>
                                                        <select
                                                            wire:model.live="id_secretaria_ajuste"
                                                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                                        >
                                                            <option value="">— Seleccionar —</option>
                                                            @foreach($secretarias as $sec)
                                                                <option value="{{ $sec->id }}">{{ $sec->secretaria }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('id_secretaria_ajuste') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                                    </div>
                                                    <div x-data="{ modoLibre: false }" x-init="$watch('modoLibre', val => { if(val) $nextTick(() => $refs.areaInputMaquinarias?.focus()) })">
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Área</label>
                                                        <template x-if="!modoLibre">
                                                            <div>
                                                                <select
                                                                    wire:model="area_ajuste"
                                                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                                                >
                                                                    <option value="">— Seleccionar —</option>
                                                                    @foreach($areas_disponibles as $area)
                                                                        <option value="{{ $area }}">{{ $area }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <button type="button" @click="modoLibre = true; $wire.set('area_ajuste', '')" class="text-xs text-blue-600 hover:text-blue-800 mt-1 inline-flex items-center gap-1">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                                    Escribir área manualmente
                                                                </button>
                                                            </div>
                                                        </template>
                                                        <template x-if="modoLibre">
                                                            <div>
                                                                <input
                                                                    type="text"
                                                                    wire:model="area_ajuste"
                                                                    x-ref="areaInputMaquinarias"
                                                                    placeholder="Escribir nombre del área"
                                                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                                                >
                                                                @if(count($areas_disponibles) > 0)
                                                                    <button type="button" @click="modoLibre = false; $wire.set('area_ajuste', '')" class="text-xs text-blue-600 hover:text-blue-800 mt-1 inline-flex items-center gap-1">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                                                                        Seleccionar de la lista
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </template>
                                                        @if($id_secretaria_ajuste && count($areas_disponibles) === 0)
                                                            <p class="text-xs text-gray-400 mt-1">No hay áreas cargadas para esta secretaría</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Cantidad (asignaciones / entrada reposición) -->
                                            @if(in_array($tipo_movimiento, ['asignacion_con_reposicion', 'asignacion_sin_reposicion', 'entrada_reposicion']))
                                            <div class="mb-5">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    Cantidad *
                                                    @if(in_array($tipo_movimiento, ['asignacion_con_reposicion', 'asignacion_sin_reposicion']))
                                                        <span class="text-xs text-gray-500 font-normal">
                                                            (Disponible: {{ $maquinaria_seleccionada->getCantidadEnDeposito($maquinaria_seleccionada->id_deposito) }})
                                                        </span>
                                                    @endif
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
                                                        @if(in_array($tipo_movimiento, ['asignacion_con_reposicion', 'asignacion_sin_reposicion']))
                                                            max="{{ $maquinaria_seleccionada->getCantidadEnDeposito($maquinaria_seleccionada->id_deposito) }}"
                                                        @endif
                                                        class="w-24 px-4 py-2.5 border border-gray-200 rounded-xl text-center focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                                    >

                                                    <button
                                                        type="button"
                                                        wire:click="incrementarCantidad"
                                                        class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                                    >
                                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                        </svg>
                                                    </button>

                                                    @if(in_array($tipo_movimiento, ['asignacion_con_reposicion', 'asignacion_sin_reposicion']))
                                                        <div class="flex-1">
                                                            <input
                                                                type="range"
                                                                wire:model.live="cantidad_a_asignar"
                                                                min="1"
                                                                max="{{ $maquinaria_seleccionada->getCantidadEnDeposito($maquinaria_seleccionada->id_deposito) }}"
                                                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600"
                                                            >
                                                        </div>
                                                    @endif
                                                </div>
                                                @error('cantidad_a_asignar')
                                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            @endif
                                        @endif

                                        @if(in_array($tipo_movimiento, ['carga_stock', 'ajuste_positivo']))
                                            {{-- Depósito auto-determinado por la maquinaria seleccionada --}}
                                            <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-indigo-50 border border-indigo-200 rounded-xl text-sm">
                                                <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                                <span class="text-indigo-800">
                                                    Depósito destino: <strong>{{ $maquinaria_seleccionada->deposito->deposito }}</strong>
                                                </span>
                                            </div>

                                            <div class="mb-5">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    {{ $tipo_movimiento === 'ajuste_positivo' ? 'Cantidad a Ajustar' : 'Cantidad a Cargar' }} *
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

                                        <!-- Cantidad a Ajustar (Ajuste Negativo) -->
                                        @if($tipo_movimiento === 'ajuste_negativo')
                                            <div class="mb-5">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    Cantidad a Ajustar *
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
                                                        max="{{ $maquinaria_seleccionada->getCantidadTotalDisponible() }}"
                                                        class="w-24 px-4 py-2.5 border border-gray-200 rounded-xl text-center focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all duration-200"
                                                    >

                                                    <button
                                                        type="button"
                                                        wire:click="incrementarCantidad"
                                                        class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                                        {{ $cantidad_a_cargar >= $maquinaria_seleccionada->getCantidadTotalDisponible() ? 'disabled' : '' }}
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
                                                            max="{{ $maquinaria_seleccionada->getCantidadTotalDisponible() }}"
                                                            step="1"
                                                            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-red-600"
                                                        >
                                                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                                                            <span>1</span>
                                                            <span>{{ $maquinaria_seleccionada->getCantidadTotalDisponible() }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                @error('cantidad_a_cargar')
                                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        @endif

                                        <!-- Transferencia -->
                                        @if($tipo_movimiento === 'transferencia')
                                            {{-- Origen auto-determinado, destino a elegir --}}
                                            <div class="mb-5 grid grid-cols-2 gap-3">
                                                <div class="flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm">
                                                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                                    </svg>
                                                    <div>
                                                        <div class="text-xs text-red-600 font-medium">Origen</div>
                                                        <div class="text-red-900 font-semibold">{{ $maquinaria_seleccionada->deposito->deposito }}</div>
                                                        @php $dispTrans = $maquinaria_seleccionada->getCantidadEnDeposito($maquinaria_seleccionada->id_deposito); @endphp
                                                        <div class="text-xs text-red-600">{{ $dispTrans }} {{ $dispTrans == 1 ? 'unidad' : 'unidades' }} disponibles</div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm">
                                                    <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18m-6 4v1a3 3 0 003 3h1a3 3 0 003-3V7a3 3 0 00-3-3h-1a3 3 0 00-3 3v1"></path>
                                                    </svg>
                                                    <div>
                                                        <div class="text-xs text-green-600 font-medium">Destino</div>
                                                        <div class="text-green-900 font-semibold">{{ $id_deposito_destino ? $depositos_disponibles->firstWhere('id', $id_deposito_destino)?->deposito ?? '—' : 'Seleccionar' }}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Cantidad a Transferir -->
                                            <div class="mb-5">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    Cantidad a Transferir *
                                                    <span class="text-xs text-gray-500 font-normal">
                                                        (Disponible: {{ $maquinaria_seleccionada->getCantidadEnDeposito($maquinaria_seleccionada->id_deposito) }})
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
                                                        max="{{ $maquinaria_seleccionada->getCantidadEnDeposito($maquinaria_seleccionada->id_deposito) }}"
                                                        class="w-24 px-4 py-2.5 border border-gray-200 rounded-xl text-center focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                                    >

                                                    <button
                                                        type="button"
                                                        wire:click="incrementarCantidad"
                                                        class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                                        {{ $cantidad_a_transferir >= $maquinaria_seleccionada->getCantidadEnDeposito($maquinaria_seleccionada->id_deposito) ? 'disabled' : '' }}
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
                                                            max="{{ $maquinaria_seleccionada->getCantidadEnDeposito($maquinaria_seleccionada->id_deposito) }}"
                                                            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600"
                                                        >
                                                    </div>
                                                </div>
                                                @error('cantidad_a_transferir')
                                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <!-- Depósito Destino -->
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

                                        <!-- Fecha de Devolución (solo para devolución) -->
                                        @if($tipo_movimiento === 'devolucion')
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

                                        @if(in_array($tipo_movimiento, ['carga_stock', 'ajuste_positivo']))
                                            <!-- N° Orden de Compra -->
                                            <div class="mb-5">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">N° Orden de Compra / Suministro</label>
                                                <input
                                                    type="text"
                                                    wire:model="nro_orden_compra"
                                                    placeholder="Opcional"
                                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                                >
                                                @error('nro_orden_compra') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                            </div>

                                            <!-- Comprobantes -->
                                            <div class="mb-5">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Comprobantes (PDF, JPG, PNG — máx. 5 archivos, 5 MB c/u)</label>
                                                <input
                                                    type="file"
                                                    wire:model="comprobantes"
                                                    multiple
                                                    accept=".pdf,.jpg,.jpeg,.png"
                                                    class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                                >
                                                @error('comprobantes') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                                @error('comprobantes.*') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                                <div wire:loading wire:target="comprobantes" class="mt-2 text-sm text-blue-600">Subiendo archivos...</div>

                                                @if(count($comprobantes) > 0)
                                                    <ul class="mt-2 space-y-1">
                                                        @foreach($comprobantes as $index => $file)
                                                            <li class="flex items-center justify-between gap-2 px-3 py-2 bg-blue-50 rounded-lg">
                                                                <span class="text-sm text-gray-700 truncate flex-1">{{ $file->getClientOriginalName() }}</span>
                                                                <button type="button" wire:click="removeComprobante({{ $index }})" class="text-red-500 hover:text-red-700 text-xs">Quitar</button>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
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

    {{-- Modal de Edición de Movimiento (N° OC, observaciones, comprobantes) --}}
    @if($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" wire:click="cerrarEdicion"></div>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-visible shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="guardarEdicion">
                        <div class="bg-white px-6 pt-6 pb-4 rounded-t-2xl">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="p-3 bg-amber-100 rounded-lg">
                                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Editar Movimiento</h3>
                                    <p class="text-sm text-gray-500">{{ $edit_movimiento_tipo }}</p>
                                </div>
                            </div>

                            {{-- N° Orden de Compra --}}
                            <div class="mb-5">
                                <label class="block text-sm font-medium text-gray-700 mb-2">N° Orden de Compra / Suministro</label>
                                <input type="text" wire:model="edit_nro_orden_compra" placeholder="Opcional"
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-200">
                                @error('edit_nro_orden_compra') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Observaciones --}}
                            <div class="mb-5">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                                <textarea wire:model="edit_observaciones" rows="3" placeholder="Opcional"
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-200"></textarea>
                                @error('edit_observaciones') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Comprobantes existentes --}}
                            @if($comprobantesEdicion->count() > 0)
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Comprobantes adjuntos</label>
                                    <div class="space-y-1">
                                        @foreach($comprobantesEdicion as $comp)
                                            <div class="flex items-center justify-between gap-2 px-3 py-2 bg-gray-50 rounded-lg">
                                                <span class="text-sm text-gray-800 truncate flex-1">{{ $comp->nombre_original }}</span>
                                                <div class="flex items-center gap-1 flex-shrink-0">
                                                    <a href="{{ route('comprobantes-maquinaria.ver', $comp->id) }}" target="_blank" title="Ver" class="p-1 text-blue-600 hover:bg-blue-100 rounded">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                    </a>
                                                    <button type="button" wire:click="eliminarComprobanteExistente({{ $comp->id }})" wire:confirm="¿Eliminar este comprobante?" title="Eliminar" class="p-1 text-red-600 hover:bg-red-100 rounded">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Agregar nuevos comprobantes --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Agregar comprobantes (PDF, JPG, PNG — máx. 5 MB c/u)</label>
                                <input type="file" wire:model="edit_comprobantes" multiple accept=".pdf,.jpg,.jpeg,.png"
                                    class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                                @error('edit_comprobantes') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                @error('edit_comprobantes.*') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                <div wire:loading wire:target="edit_comprobantes" class="mt-2 text-sm text-blue-600">Subiendo archivos...</div>

                                @if(count($edit_comprobantes) > 0)
                                    <ul class="mt-2 space-y-1">
                                        @foreach($edit_comprobantes as $index => $file)
                                            <li class="flex items-center justify-between gap-2 px-3 py-2 bg-amber-50 rounded-lg">
                                                <span class="text-sm text-gray-700 truncate flex-1">{{ $file->getClientOriginalName() }}</span>
                                                <button type="button" wire:click="removeEditComprobante({{ $index }})" class="text-red-500 hover:text-red-700 text-xs">Quitar</button>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 rounded-b-2xl">
                            <button type="button" wire:click="cerrarEdicion" class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-100 transition-colors duration-200 font-medium">Cancelar</button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="guardarEdicion,edit_comprobantes"
                                class="px-5 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 text-white rounded-xl hover:from-amber-600 hover:to-amber-700 shadow-lg shadow-amber-500/30 transition-all duration-200 font-medium">
                                Guardar Cambios
                            </button>
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