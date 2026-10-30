{{-- resources/views/livewire/transferencias-maquinarias.blade.php --}}
<x-ui.pagina titulo="Movimientos de Maquinarias" icono="fa-right-left">
    <x-slot:acciones>
        <x-ui.boton variante="secundario" icono="fa-chart-column" wire:click="toggleEstadisticas">Estadísticas</x-ui.boton>

        {{-- Exportar (dropdown Excel/PDF) --}}
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <x-ui.boton variante="secundario" icono="fa-download" x-on:click="open = !open">
                Exportar <i class="fa-solid fa-chevron-down text-xs" aria-hidden="true"></i>
            </x-ui.boton>
            <div
                x-show="open" x-transition x-cloak
                class="absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-20"
            >
                <button wire:click="exportarExcel" @click="open = false" class="w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Excel (.xlsx)
                </button>
                <button wire:click="exportarPdf" @click="open = false" class="w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    PDF
                </button>
            </div>
        </div>

        @if($puedeCrear)
            <x-ui.boton icono="fa-plus" wire:click="crear">Nuevo Movimiento</x-ui.boton>
        @endif
    </x-slot:acciones>

    <!-- Búsqueda y filtros -->
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



    {{-- Panel de Asignaciones Pendientes (solo si hay pendientes) --}}
    @if($puedeCrear && $asignacionesPendientes->count() > 0)
    <div class="mb-6">
        <button
            type="button"
            wire:click="$toggle('showAsignacionesPendientes')"
            aria-expanded="{{ $showAsignacionesPendientes ? 'true' : 'false' }}"
            class="w-full flex items-center justify-between px-5 py-3 bg-white border-2 border-orange-200 rounded-xl hover:bg-orange-50 transition-colors duration-200"
        >
            <div class="flex items-center gap-3">
                <i class="fa-regular fa-clock text-orange-500" aria-hidden="true"></i>
                <span class="text-sm font-semibold text-orange-800">Asignaciones Pendientes de Reposición</span>
            </div>
            <i class="fa-solid fa-chevron-down text-orange-500 transition-transform duration-200 {{ $showAsignacionesPendientes ? 'rotate-180' : '' }}" aria-hidden="true"></i>
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
                                            <x-ui.badge color="ambar">{{ $item['cantidad_pendiente'] }}</x-ui.badge>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center gap-2">
                                                <div class="flex items-center gap-1" x-data="{ cantidad: 1 }">
                                                    <input
                                                        type="number"
                                                        x-model="cantidad"
                                                        min="1"
                                                        max="{{ $item['cantidad_pendiente'] }}"
                                                        aria-label="Cantidad a devolver de {{ $item['maquinaria_nombre'] }}"
                                                        class="w-16 px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-center focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"
                                                    >
                                                    <x-ui.boton variante="secundario" tamano="chico" icono="fa-rotate-left"
                                                                x-on:click="$wire.devolverAsignacion({{ $item['id_maquinaria'] }}, '{{ $item['tipo_referencia'] }}', '{{ $item['id_referencia'] }}', cantidad)">
                                                        Devolver
                                                    </x-ui.boton>
                                                </div>
                                                <x-ui.boton variante="peligro" tamano="chico" icono="fa-xmark"
                                                            wire:click="confirmarBajaAsignacion({{ $item['id_maquinaria'] }}, '{{ $item['tipo_referencia'] }}', '{{ $item['id_referencia'] }}')"
                                                            title="Dar de baja (no devuelve stock)">
                                                    Baja
                                                </x-ui.boton>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-ui.vacio icono="fa-regular fa-circle-check" class="py-8">No hay asignaciones pendientes de reposición</x-ui.vacio>
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
                            @if($movimiento->observaciones)
                                <div class="flex items-start gap-1 mt-1 text-xs text-gray-400 max-w-xs" title="{{ $movimiento->observaciones }}">
                                    <i class="fa-regular fa-comment-dots flex-shrink-0 mt-0.5" aria-hidden="true"></i>
                                    <span class="line-clamp-2">{{ $movimiento->observaciones }}</span>
                                </div>
                            @endif
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
                                    <i class="fa-solid fa-arrow-up text-[10px] text-green-600" aria-hidden="true"></i>
                                    <span class="text-xs text-green-600">
                                        +{{ $movimiento->cantidad }} {{ $movimiento->cantidad == 1 ? 'Entrada' : 'Entradas' }}
                                    </span>
                                </div>
                            @else
                                <div class="flex items-center gap-1 mt-1">
                                    <i class="fa-solid fa-arrow-down text-[10px] text-red-600" aria-hidden="true"></i>
                                    <span class="text-xs text-red-600">
                                        -{{ $movimiento->cantidad }} {{ $movimiento->cantidad == 1 ? 'Salida' : 'Salidas' }}
                                    </span>
                                </div>
                            @endif
                        </td>
                        
                        <!-- COLUMNA ORIGEN -->
                        <td class="px-6 py-4 text-center">
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
                                <x-ui.badge color="rojo">{{ $depositoOrigen->deposito }}</x-ui.badge>
                            @else
                                <span class="text-sm text-gray-400">—</span>
                            @endif
                        </td>

                        <!-- COLUMNA DESTINO -->
                        <td class="px-6 py-4 text-center">
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
                                <x-ui.badge color="verde">{{ $depositoDestino->deposito }}</x-ui.badge>
                            @elseif($movimiento->id_secretaria || $movimiento->area)
                                @if($movimiento->secretaria)
                                    <x-ui.badge color="violeta">{{ $movimiento->secretaria->secretaria }}</x-ui.badge>
                                @endif
                                @if($movimiento->area)
                                    <span class="block mt-1 text-xs text-purple-600">{{ $movimiento->area }}</span>
                                @endif
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
                                <x-ui.badge color="azul">{{ $refNombre ?? $refLabel }}</x-ui.badge>
                                <span class="block mt-1 text-xs text-blue-600">{{ $refLabel }}</span>
                            @else
                                <span class="text-sm text-gray-400">—</span>
                            @endif
                        </td>

                        <!-- Estado ACTUAL de la maquinaria -->
                        <td class="px-6 py-4">
                            @if($movimiento->estado_calculado === 'disponible')
                                <x-ui.badge color="verde">Disponible</x-ui.badge>
                            @else
                                <x-ui.badge color="ambar">No Disponible</x-ui.badge>
                            @endif
                        </td>
                        
                        <!-- Tipo de Movimiento -->
                        <td class="px-6 py-4 text-center">
                            @php
                                $colorTipo = $movimiento->tipoMovimiento->esEntrada()
                                    ? 'verde'
                                    : ($movimiento->tipoMovimiento->esSalida() ? 'rojo' : 'gris');
                            @endphp
                            <x-ui.badge :color="$colorTipo">{{ $movimiento->tipoMovimiento->tipo_movimiento }}</x-ui.badge>
                            @if($movimiento->nro_orden_compra)
                                <div class="text-xs text-gray-500 text-center mt-1">OC: {{ $movimiento->nro_orden_compra }}</div>
                            @endif
                        </td>

                        <!-- Usuario -->
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <div class="flex items-center gap-1">
                                <span class="mr-1">{{ $movimiento->usuario->name }}</span>
                                @if($movimiento->comprobantes->count() > 0)
                                    <div x-data="{ open: false }" class="relative">
                                        <x-ui.accion icono="fa-paperclip" :etiqueta="'Ver comprobantes ('.$movimiento->comprobantes->count().')'"
                                                     x-on:click="open = !open" />
                                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 z-50 mt-1 w-64 bg-white border border-gray-200 rounded-xl shadow-lg p-3">
                                            <div class="text-xs font-semibold text-gray-700 mb-2">Comprobantes adjuntos</div>
                                            @foreach($movimiento->comprobantes as $comp)
                                                <div class="flex items-center justify-between gap-1 px-2 py-1 rounded-lg hover:bg-gray-50">
                                                    <span class="text-sm text-gray-800 truncate flex-1">{{ $comp->nombre_original }}</span>
                                                    <div class="flex items-center flex-shrink-0">
                                                        <x-ui.accion icono="fa-regular fa-eye" etiqueta="Ver" :href="route('comprobantes-maquinaria.ver', $comp->id)" target="_blank" />
                                                        <x-ui.accion icono="fa-download" etiqueta="Descargar" color="exito" :href="route('comprobantes-maquinaria.descargar', $comp->id)" :navegar="false" />
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                @if($puedeCrear && in_array($movimiento->tipoMovimiento->tipo_movimiento, ['Carga de Stock', 'Ajuste Positivo']))
                                    <x-ui.accion icono="fa-regular fa-pen-to-square" etiqueta="Editar N° orden, observaciones y comprobantes"
                                                 wire:click="abrirEdicion({{ $movimiento->id }})" />
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-6">
                            <x-ui.vacio icono="fa-right-left">{{ $search || $filtro_corralon || $filtro_fecha_desde || $filtro_fecha_hasta || $filtro_deposito_origen || $filtro_usuario || $filtro_maquinaria || $filtro_categoria || $filtro_tipo_movimiento ? 'No hay movimientos que coincidan con la búsqueda o los filtros' : 'Todavía no hay movimientos registrados' }}</x-ui.vacio>
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
        <x-ui.modal titulo="Nuevo Movimiento de Maquinaria" icono="fa-plus"
                    cerrar="intentarCerrar" ancho="4xl">
            <x-slot:encabezado>
                <div class="flex items-center gap-2">
                    @for($i = 1; $i <= 3; $i++)
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-7 h-7 rounded-full {{ $paso_actual >= $i ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500' }} font-medium text-xs transition-all duration-200">
                                {{ $i }}
                            </div>
                            @if($i < 3)
                                <div class="w-8 h-1 {{ $paso_actual > $i ? 'bg-blue-600' : 'bg-gray-200' }} transition-all duration-200"></div>
                            @endif
                        </div>
                    @endfor
                    <div class="ml-2 text-sm text-gray-600 font-normal">
                        @if($paso_actual === 1)
                            Paso 1: Seleccionar Maquinaria
                        @elseif($paso_actual === 2)
                            Paso 2: Tipo de Movimiento
                        @else
                            Paso 3: Completar Datos
                        @endif
                    </div>
                </div>
            </x-slot:encabezado>

            <!-- PASO 1: Seleccionar Maquinaria -->
            @if($paso_actual === 1)
                <x-ui.campo etiqueta="Buscar Maquinaria" nombre="search_maquinaria" obligatorio>
                    {{-- Busca en el servidor (search_maquinaria); al elegir avanza al paso 2. --}}
                    <x-ui.combobox nombre="search_maquinaria" buscar-en="search_maquinaria" accion="seleccionarMaquinaria"
                                   placeholder="Buscar por nombre, categoría o depósito..."
                                   sin-resultados="No hay maquinarias que coincidan con la búsqueda"
                                   x-on:combobox-abierto="$wire.mostrarLista()"
                                   :opciones="$maquinarias_filtradas->map(fn ($m) => [
                                       'id' => $m->id,
                                       'nombre' => $m->maquinaria,
                                       'insignia' => $m->categoriaMaquinaria->nombre,
                                       'detalle' => $m->deposito->deposito,
                                       'cifra' => $m->cantidad > 0 ? 'Disponible' : 'Sin Stock',
                                       'cifra_color' => $m->cantidad > 0 ? 'verde' : 'naranja',
                                       'subcifra' => $m->cantidad.' '.($m->cantidad == 1 ? 'unidad' : 'unidades'),
                                   ])" />
                </x-ui.campo>

                @if(!$search_maquinaria)
                    <x-ui.vacio icono="fa-magnifying-glass" class="py-12">Comience buscando una maquinaria por nombre, categoría o depósito</x-ui.vacio>
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
                                    <span class="px-2 py-0.5 bg-white text-blue-900 rounded font-medium">
                                        {{ $maquinaria_seleccionada->cantidad }} {{ $maquinaria_seleccionada->cantidad == 1 ? 'unidad' : 'unidades' }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                @if($maquinaria_seleccionada->estado === 'disponible')
                                    <x-ui.badge color="verde">Disponible</x-ui.badge>
                                @else
                                    <x-ui.badge color="ambar">No Disponible</x-ui.badge>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Grupo de opciones: fieldset + legend (no va en x-ui.campo) --}}
                    <fieldset class="w-full min-w-0">
                        <legend class="block text-sm font-medium text-gray-700 mb-4">Seleccione el Tipo de Movimiento</legend>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($tipos_movimiento_disponibles as $tipo)
                                <button
                                    type="button"
                                    wire:click="seleccionarTipoMovimiento('{{ $tipo['key'] }}')"
                                    class="p-5 border-2 border-gray-200 rounded-xl hover:border-{{ $tipo['color'] }}-500 hover:bg-{{ $tipo['color'] }}-50 transition-all duration-200 text-left group"
                                >
                                    <div class="flex items-start gap-4">
                                        <div class="p-3 bg-{{ $tipo['color'] }}-100 rounded-lg group-hover:bg-{{ $tipo['color'] }}-200 transition-colors">
                                            <svg class="w-6 h-6 text-{{ $tipo['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
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
                    </fieldset>
                </div>
            @endif

            <!-- PASO 3: Completar Datos -->
            @if($paso_actual === 3 && $maquinaria_seleccionada && $tipo_movimiento)
                <div class="space-y-5">
                    <!-- Info del movimiento -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4 mb-6">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                @php
                                    $tipoInfo = collect($tipos_movimiento_disponibles)->firstWhere('key', $tipo_movimiento);
                                @endphp
                                <div class="p-2 bg-white rounded-lg">
                                    <svg class="w-5 h-5 text-{{ $tipoInfo['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
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
                                    <x-ui.badge color="verde">Disponible</x-ui.badge>
                                @else
                                    <x-ui.badge color="ambar">No Disponible</x-ui.badge>
                                @endif
                            </div>
                        </div>
                    </div>

                    @php $disponibleEnDeposito = $maquinaria_seleccionada->getCantidadEnDeposito($maquinaria_seleccionada->id_deposito); @endphp

                    @if(in_array($tipo_movimiento, ['asignacion_con_reposicion', 'asignacion_sin_reposicion', 'entrada_reposicion', 'ajuste_negativo']))
                        @if(in_array($tipo_movimiento, ['asignacion_con_reposicion', 'asignacion_sin_reposicion']))
                            {{-- Depósito auto-determinado --}}
                            <div class="flex items-center gap-3 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl text-sm">
                                <i class="fa-regular fa-building text-blue-500 shrink-0" aria-hidden="true"></i>
                                <span class="text-blue-800">
                                    Depósito de origen: <strong>{{ $maquinaria_seleccionada->deposito->deposito }}</strong>
                                    &nbsp;—&nbsp;
                                    {{ $disponibleEnDeposito }} {{ $disponibleEnDeposito == 1 ? 'unidad disponible' : 'unidades disponibles' }}
                                </span>
                            </div>
                        @endif

                        @if($tipo_movimiento === 'ajuste_negativo')
                            {{-- Depósito y disponible (ajuste negativo) --}}
                            <div class="flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm">
                                <i class="fa-regular fa-building text-red-500 shrink-0" aria-hidden="true"></i>
                                <span class="text-red-800">
                                    Depósito: <strong>{{ $maquinaria_seleccionada->deposito->deposito }}</strong>
                                    — Disponible: <strong>{{ $maquinaria_seleccionada->getCantidadTotalDisponible() }}</strong> {{ $maquinaria_seleccionada->getCantidadTotalDisponible() == 1 ? 'unidad' : 'unidades' }}
                                </span>
                            </div>
                        @endif

                        <!-- Selector de tipo de destino -->
                        @php
                            $destinoLabel = $tipo_movimiento === 'entrada_reposicion'
                                ? 'Devuelto desde'
                                : ($tipo_movimiento === 'ajuste_negativo' ? 'Asignar a (opcional)' : 'Asignar a');
                            $destinoObligatorio = $tipo_movimiento !== 'ajuste_negativo';
                            $tiposDestino = [
                                'vehiculo' => ['Vehículo', 'fa-solid fa-truck'],
                                'evento' => ['Evento', 'fa-regular fa-calendar'],
                                'empleado' => ['Empleado', 'fa-regular fa-user'],
                                'secretaria' => ['Secretaría', 'fa-regular fa-building'],
                            ];
                        @endphp
                        {{-- Grupo de opciones: fieldset + legend (no va en x-ui.campo) --}}
                        <fieldset class="w-full min-w-0">
                            <legend class="block text-sm font-medium text-gray-700 mb-2">
                                {{ $destinoLabel }}
                                @if($destinoObligatorio)<span class="text-red-600" aria-hidden="true">*</span>@endif
                            </legend>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                @foreach($tiposDestino as $clave => [$etiquetaDestino, $iconoDestino])
                                    <button
                                        type="button"
                                        wire:click="seleccionarTipoDestino('{{ $clave }}')"
                                        aria-pressed="{{ $tipo_destino === $clave ? 'true' : 'false' }}"
                                        class="p-3 border-2 rounded-xl text-center transition-all duration-200 {{ $tipo_destino === $clave ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 hover:border-gray-300 text-gray-600' }}"
                                    >
                                        <i class="{{ $iconoDestino }} block text-xl mb-1" aria-hidden="true"></i>
                                        <span class="text-sm font-medium">{{ $etiquetaDestino }}</span>
                                    </button>
                                @endforeach
                            </div>
                            @error('tipo_destino')
                                <p id="error-tipo_destino" class="mt-1 text-xs text-red-600" data-error="tipo_destino">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <!-- Buscar y seleccionar destino (vehículo/evento/empleado) -->
                        @if(in_array($tipo_destino, ['vehiculo', 'evento', 'empleado']))
                            <x-ui.campo :etiqueta="'Seleccionar '.($tipo_destino === 'vehiculo' ? 'Vehículo' : ($tipo_destino === 'evento' ? 'Evento' : 'Empleado'))"
                                        nombre="id_referencia" obligatorio>
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
                                        <x-ui.accion icono="fa-xmark" etiqueta="Quitar la selección" wire:click="$set('id_referencia', '')" />
                                    </div>
                                @else
                                    {{-- Busca en el servidor (search_destino); al elegir marca el destino. --}}
                                    @include('livewire.partials.combobox-destino', [
                                        'tipo' => $tipo_destino,
                                        'buscarEn' => 'search_destino',
                                        'accion' => 'seleccionarDestino',
                                        'alAbrir' => "\$wire.set('mostrar_lista_destino', true)",
                                        'vehiculos' => $vehiculos_destino,
                                        'eventos' => $eventos_destino,
                                        'empleados' => $empleados_destino,
                                    ])
                                @endif
                            </x-ui.campo>
                        @elseif($tipo_destino === 'secretaria')
                            <!-- Secretaría y Área -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-ui.campo etiqueta="Secretaría" nombre="id_secretaria_ajuste" obligatorio>
                                    <x-ui.combobox nombre="id_secretaria_ajuste" wire:model.live="id_secretaria_ajuste" placeholder="— Seleccionar —"
                                                   :opciones="$secretarias" etiqueta="secretaria" />
                                </x-ui.campo>
                                <x-ui.campo etiqueta="Área" nombre="area_ajuste"
                                            :ayuda="$id_secretaria_ajuste && count($areas_disponibles) === 0 ? 'No hay áreas cargadas para esta secretaría.' : null"
                                            x-data="{ modoLibre: false }" x-init="$watch('modoLibre', val => { if(val) $nextTick(() => $refs.areaInputMaquinarias?.focus()) })">
                                    <template x-if="!modoLibre">
                                        <div>
                                            <x-ui.combobox nombre="area_ajuste" wire:model="area_ajuste" placeholder="— Seleccionar —"
                                                           :opciones="collect($areas_disponibles)->values()" />
                                            <x-ui.boton variante="enlace" tamano="chico" icono="fa-regular fa-pen-to-square" class="mt-1"
                                                        x-on:click="modoLibre = true; $wire.set('area_ajuste', '')">
                                                Escribir área manualmente
                                            </x-ui.boton>
                                        </div>
                                    </template>
                                    <template x-if="modoLibre">
                                        <div>
                                            <x-ui.input nombre="area_ajuste" wire:model="area_ajuste" x-ref="areaInputMaquinarias"
                                                        placeholder="Escribir nombre del área" />
                                            @if(count($areas_disponibles) > 0)
                                                <x-ui.boton variante="enlace" tamano="chico" icono="fa-list" class="mt-1"
                                                            x-on:click="modoLibre = false; $wire.set('area_ajuste', '')">
                                                    Seleccionar de la lista
                                                </x-ui.boton>
                                            @endif
                                        </div>
                                    </template>
                                </x-ui.campo>
                            </div>
                        @endif

                        <!-- Cantidad (asignaciones / entrada reposición) -->
                        @if(in_array($tipo_movimiento, ['asignacion_con_reposicion', 'asignacion_sin_reposicion', 'entrada_reposicion']))
                            @php $esAsignacion = in_array($tipo_movimiento, ['asignacion_con_reposicion', 'asignacion_sin_reposicion']); @endphp
                            <x-ui.campo etiqueta="Cantidad" nombre="cantidad_a_asignar" obligatorio
                                        :ayuda="$esAsignacion ? 'Disponible: '.$disponibleEnDeposito.'.' : null">
                                <div class="flex items-center gap-3">
                                    <x-ui.accion icono="fa-minus" etiqueta="Restar uno" wire:click="decrementarCantidad"
                                                 class="border border-gray-300" :disabled="$cantidad_a_asignar <= 1" />
                                    <div class="w-24 shrink-0">
                                        <x-ui.input nombre="cantidad_a_asignar" type="number" wire:model.live="cantidad_a_asignar" min="1"
                                                    :max="$esAsignacion ? $disponibleEnDeposito : null" class="text-center" />
                                    </div>
                                    <x-ui.accion icono="fa-plus" etiqueta="Sumar uno" wire:click="incrementarCantidad" class="border border-gray-300" />

                                    @if($esAsignacion)
                                        <div class="flex-1">
                                            <input
                                                type="range"
                                                wire:model.live="cantidad_a_asignar"
                                                min="1"
                                                max="{{ $disponibleEnDeposito }}"
                                                aria-label="Cantidad"
                                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600"
                                            >
                                        </div>
                                    @endif
                                </div>
                            </x-ui.campo>
                        @endif
                    @endif

                    @if(in_array($tipo_movimiento, ['carga_stock', 'ajuste_positivo']))
                        {{-- Depósito auto-determinado por la maquinaria seleccionada --}}
                        <div class="flex items-center gap-3 px-4 py-3 bg-indigo-50 border border-indigo-200 rounded-xl text-sm">
                            <i class="fa-regular fa-building text-indigo-500 shrink-0" aria-hidden="true"></i>
                            <span class="text-indigo-800">
                                Depósito destino: <strong>{{ $maquinaria_seleccionada->deposito->deposito }}</strong>
                            </span>
                        </div>

                        <x-ui.campo :etiqueta="$tipo_movimiento === 'ajuste_positivo' ? 'Cantidad a Ajustar' : 'Cantidad a Cargar'"
                                    nombre="cantidad_a_cargar" obligatorio ayuda="Máximo: 1000 unidades.">
                            <div class="flex items-center gap-3">
                                <x-ui.accion icono="fa-minus" etiqueta="Restar uno" wire:click="decrementarCantidad"
                                             class="border border-gray-300" :disabled="$cantidad_a_cargar <= 1" />
                                <div class="w-24 shrink-0">
                                    <x-ui.input nombre="cantidad_a_cargar" type="number" wire:model.live="cantidad_a_cargar" min="1" max="1000" class="text-center" />
                                </div>
                                <x-ui.accion icono="fa-plus" etiqueta="Sumar uno" wire:click="incrementarCantidad"
                                             class="border border-gray-300" :disabled="$cantidad_a_cargar >= 1000" />

                                <div class="flex-1">
                                    <input
                                        type="range"
                                        wire:model.live="cantidad_a_cargar"
                                        min="1"
                                        max="100"
                                        step="1"
                                        aria-label="Cantidad"
                                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                                    >
                                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                                        <span>1</span>
                                        <span>100</span>
                                    </div>
                                </div>
                            </div>
                        </x-ui.campo>
                    @endif

                    <!-- Cantidad a Ajustar (Ajuste Negativo) -->
                    @if($tipo_movimiento === 'ajuste_negativo')
                        @php $disponibleTotal = $maquinaria_seleccionada->getCantidadTotalDisponible(); @endphp
                        <x-ui.campo etiqueta="Cantidad a Ajustar" nombre="cantidad_a_cargar" obligatorio>
                            <div class="flex items-center gap-3">
                                <x-ui.accion icono="fa-minus" etiqueta="Restar uno" wire:click="decrementarCantidad"
                                             class="border border-gray-300" :disabled="$cantidad_a_cargar <= 1" />
                                <div class="w-24 shrink-0">
                                    <x-ui.input nombre="cantidad_a_cargar" type="number" wire:model.live="cantidad_a_cargar" min="1"
                                                :max="$disponibleTotal" class="text-center" />
                                </div>
                                <x-ui.accion icono="fa-plus" etiqueta="Sumar uno" wire:click="incrementarCantidad"
                                             class="border border-gray-300" :disabled="$cantidad_a_cargar >= $disponibleTotal" />

                                <div class="flex-1">
                                    <input
                                        type="range"
                                        wire:model.live="cantidad_a_cargar"
                                        min="1"
                                        max="{{ $disponibleTotal }}"
                                        step="1"
                                        aria-label="Cantidad"
                                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-red-600"
                                    >
                                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                                        <span>1</span>
                                        <span>{{ $disponibleTotal }}</span>
                                    </div>
                                </div>
                            </div>
                        </x-ui.campo>
                    @endif

                    <!-- Transferencia -->
                    @if($tipo_movimiento === 'transferencia')
                        {{-- Origen auto-determinado, destino a elegir --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div class="flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm">
                                <i class="fa-solid fa-right-from-bracket text-red-500 shrink-0" aria-hidden="true"></i>
                                <div>
                                    <div class="text-xs text-red-600 font-medium">Origen</div>
                                    <div class="text-red-900 font-semibold">{{ $maquinaria_seleccionada->deposito->deposito }}</div>
                                    <div class="text-xs text-red-600">{{ $disponibleEnDeposito }} {{ $disponibleEnDeposito == 1 ? 'unidad' : 'unidades' }} disponibles</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm">
                                <i class="fa-solid fa-right-to-bracket text-green-500 shrink-0" aria-hidden="true"></i>
                                <div>
                                    <div class="text-xs text-green-600 font-medium">Destino</div>
                                    <div class="text-green-900 font-semibold">{{ $id_deposito_destino ? $depositos_disponibles->firstWhere('id', $id_deposito_destino)?->deposito ?? '—' : 'Seleccionar' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Cantidad a Transferir -->
                        <x-ui.campo etiqueta="Cantidad a Transferir" nombre="cantidad_a_transferir" obligatorio
                                    :ayuda="'Disponible: '.$disponibleEnDeposito.'.'">
                            <div class="flex items-center gap-3">
                                <x-ui.accion icono="fa-minus" etiqueta="Restar uno" wire:click="decrementarCantidad"
                                             class="border border-gray-300" :disabled="$cantidad_a_transferir <= 1" />
                                <div class="w-24 shrink-0">
                                    <x-ui.input nombre="cantidad_a_transferir" type="number" wire:model.live="cantidad_a_transferir" min="1"
                                                :max="$disponibleEnDeposito" class="text-center" />
                                </div>
                                <x-ui.accion icono="fa-plus" etiqueta="Sumar uno" wire:click="incrementarCantidad"
                                             class="border border-gray-300" :disabled="$cantidad_a_transferir >= $disponibleEnDeposito" />

                                <div class="flex-1">
                                    <input
                                        type="range"
                                        wire:model.live="cantidad_a_transferir"
                                        min="1"
                                        max="{{ $disponibleEnDeposito }}"
                                        aria-label="Cantidad"
                                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600"
                                    >
                                </div>
                            </div>
                        </x-ui.campo>

                        <!-- Depósito Destino -->
                        <x-ui.campo etiqueta="Depósito Destino" nombre="id_deposito_destino" obligatorio>
                            <x-ui.combobox nombre="id_deposito_destino" wire:model.live="id_deposito_destino" placeholder="Seleccione un depósito"
                                           :ordenar="false" etiqueta="deposito"
                                           :opciones="collect($depositos_disponibles)->where('id', '!=', $maquinaria_seleccionada->id_deposito)->values()" />
                        </x-ui.campo>
                    @endif

                    <!-- Fecha de Devolución (solo para devolución) -->
                    @if($tipo_movimiento === 'devolucion')
                        <x-ui.campo etiqueta="Fecha de Devolución Esperada" nombre="fecha_devolucion_esperada">
                            <x-ui.input nombre="fecha_devolucion_esperada" type="date" wire:model="fecha_devolucion_esperada"
                                        min="{{ date('Y-m-d', strtotime('+1 day')) }}" />
                        </x-ui.campo>
                    @endif

                    @if(in_array($tipo_movimiento, ['carga_stock', 'ajuste_positivo']))
                        <x-ui.campo etiqueta="N° Orden de Compra / Suministro" nombre="nro_orden_compra">
                            <x-ui.input nombre="nro_orden_compra" wire:model="nro_orden_compra" placeholder="Opcional" autocomplete="off" />
                        </x-ui.campo>

                        <x-ui.campo etiqueta="Comprobantes (opcional)" nombre="comprobantes" ayuda="PDF, JPG o PNG. Máximo 5 archivos, 5 MB cada uno.">
                            <x-ui.input nombre="comprobantes" type="file" wire:model="comprobantes" multiple accept=".pdf,.jpg,.jpeg,.png"
                                        class="text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:cursor-pointer" />
                            @error('comprobantes.*')
                                <p class="mt-1 text-xs text-red-600" data-error="comprobantes.*">{{ $message }}</p>
                            @enderror
                            <div wire:loading wire:target="comprobantes" class="mt-2 text-sm text-blue-600">Subiendo archivos...</div>

                            @include('livewire.partials.preview-archivos', ['files' => $comprobantes, 'removeMethod' => 'removeComprobante'])
                        </x-ui.campo>
                    @endif

                    <x-ui.campo etiqueta="Observaciones" nombre="observaciones">
                        <x-ui.textarea nombre="observaciones" wire:model="observaciones" placeholder="Motivo del movimiento (opcional)" />
                    </x-ui.campo>
                </div>
            @endif

            <x-slot:footer class="justify-between">
                <div>
                    @if($paso_actual > 1)
                        <x-ui.boton variante="secundario" icono="fa-chevron-left" wire:click="volverPaso">Volver</x-ui.boton>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <x-ui.boton variante="secundario" wire:click="intentarCerrar">Cancelar</x-ui.boton>

                    @if($paso_actual === 3)
                        <x-ui.boton wire:click="guardar" data-atajo-guardar wire:loading.attr="disabled" wire:target="guardar">
                            Guardar Movimiento
                        </x-ui.boton>
                    @endif
                </div>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    {{-- Modal de Edición de Movimiento (N° OC, observaciones, comprobantes) --}}
    @if($showEditModal)
        <x-ui.modal titulo="Editar Movimiento" icono="fa-pen"
                    cerrar="intentarCerrar" ancho="lg" submit="guardarEdicion">
            <x-slot:encabezado>
                <span class="text-sm text-gray-500 font-normal">{{ $edit_movimiento_tipo }}</span>
            </x-slot:encabezado>

            <div class="space-y-5">
                <x-ui.campo etiqueta="N° Orden de Compra / Suministro" nombre="edit_nro_orden_compra">
                    <x-ui.input nombre="edit_nro_orden_compra" wire:model="edit_nro_orden_compra" placeholder="Opcional" autocomplete="off" />
                </x-ui.campo>

                <x-ui.campo etiqueta="Observaciones" nombre="edit_observaciones">
                    <x-ui.textarea nombre="edit_observaciones" wire:model="edit_observaciones" placeholder="Opcional" />
                </x-ui.campo>

                {{-- Comprobantes existentes --}}
                @if($comprobantesEdicion->count() > 0)
                    <div>
                        <div class="block text-sm font-medium text-gray-700 mb-2">Comprobantes adjuntos</div>
                        <div class="space-y-1">
                            @foreach($comprobantesEdicion as $comp)
                                <div class="flex items-center justify-between gap-2 px-3 py-1 bg-gray-50 rounded-lg">
                                    <span class="text-sm text-gray-800 truncate flex-1">{{ $comp->nombre_original }}</span>
                                    <div class="flex items-center flex-shrink-0">
                                        <x-ui.accion icono="fa-regular fa-eye" etiqueta="Ver" :href="route('comprobantes-maquinaria.ver', $comp->id)" target="_blank" />
                                        <x-ui.accion icono="fa-regular fa-trash-can" color="peligro" etiqueta="Eliminar"
                                                     wire:click="confirmarEliminarComprobante({{ $comp->id }})" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Agregar nuevos comprobantes --}}
                <x-ui.campo etiqueta="Agregar comprobantes" nombre="edit_comprobantes" ayuda="PDF, JPG o PNG. Máximo 5 MB cada uno.">
                    <x-ui.input nombre="edit_comprobantes" type="file" wire:model="edit_comprobantes" multiple accept=".pdf,.jpg,.jpeg,.png"
                                class="text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:cursor-pointer" />
                    @error('edit_comprobantes.*')
                        <p class="mt-1 text-xs text-red-600" data-error="edit_comprobantes.*">{{ $message }}</p>
                    @enderror
                    <div wire:loading wire:target="edit_comprobantes" class="mt-2 text-sm text-blue-600">Subiendo archivos...</div>

                    @include('livewire.partials.preview-archivos', ['files' => $edit_comprobantes, 'removeMethod' => 'removeEditComprobante'])
                </x-ui.campo>
            </div>

            <x-slot:footer>
                <x-ui.boton variante="secundario" wire:click="intentarCerrar">Cancelar</x-ui.boton>
                <x-ui.boton type="submit" wire:loading.attr="disabled" wire:target="guardarEdicion,edit_comprobantes">
                    Guardar Cambios
                </x-ui.boton>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    {{-- Modal de Estadísticas --}}
    @if($showEstadisticas && $estadisticas)
        <x-ui.modal titulo="Estadísticas de Movimientos" icono="fa-chart-column" cerrar="toggleEstadisticas" ancho="3xl">
            <div class="space-y-6">
                @if($estadisticas['total'] == 0)
                    <x-ui.vacio icono="fa-chart-column" class="py-8">No hay movimientos que coincidan con la búsqueda o los filtros</x-ui.vacio>
                @else
                {{-- KPIs --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-gray-800">{{ number_format($estadisticas['total']) }}</div>
                        <div class="text-xs text-gray-500 mt-1">Movimientos</div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-green-700">{{ number_format($estadisticas['entradas_cant']) }}</div>
                        <div class="text-xs text-gray-500 mt-1">Entradas ({{ number_format($estadisticas['entradas_sum']) }} u.)</div>
                    </div>
                    <div class="bg-red-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-red-600">{{ number_format($estadisticas['salidas_cant']) }}</div>
                        <div class="text-xs text-gray-500 mt-1">Salidas ({{ number_format($estadisticas['salidas_sum']) }} u.)</div>
                    </div>
                </div>

                {{-- Por tipo --}}
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Movimientos por tipo</h4>
                    <div class="space-y-2">
                        @php $maxTipo = $estadisticas['por_tipo']->max() ?: 1; @endphp
                        @foreach($estadisticas['por_tipo']->take(10) as $tipo => $cant)
                            <div class="flex items-center gap-3">
                                <div class="w-40 text-xs text-gray-600 truncate" title="{{ $tipo }}">{{ $tipo }}</div>
                                <div class="flex-1 bg-gray-100 rounded-full h-4 overflow-hidden">
                                    <div class="bg-[#77BF43] h-4 rounded-full" style="width: {{ ($cant / $maxTipo) * 100 }}%"></div>
                                </div>
                                <div class="w-10 text-right text-xs font-medium text-gray-700">{{ $cant }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Por usuario --}}
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Movimientos por usuario</h4>
                    <div class="space-y-2">
                        @php $maxUsr = $estadisticas['por_usuario']->max() ?: 1; @endphp
                        @foreach($estadisticas['por_usuario']->take(8) as $usr => $cant)
                            <div class="flex items-center gap-3">
                                <div class="w-40 text-xs text-gray-600 truncate" title="{{ $usr }}">{{ $usr }}</div>
                                <div class="flex-1 bg-gray-100 rounded-full h-4 overflow-hidden">
                                    <div class="bg-blue-500 h-4 rounded-full" style="width: {{ ($cant / $maxUsr) * 100 }}%"></div>
                                </div>
                                <div class="w-10 text-right text-xs font-medium text-gray-700">{{ $cant }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Top maquinarias movidas --}}
                @if($estadisticas['top_maquinarias']->count() > 0)
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Maquinarias más movidas</h4>
                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs text-gray-500">
                                <tr>
                                    <th class="text-left px-3 py-2">Maquinaria</th>
                                    <th class="text-right px-3 py-2">Cant. total</th>
                                    <th class="text-right px-3 py-2">N° mov.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($estadisticas['top_maquinarias'] as $maq)
                                    <tr class="border-t border-gray-50">
                                        <td class="px-3 py-2 text-gray-700">{{ $maq['nombre'] }}</td>
                                        <td class="px-3 py-2 text-right font-medium text-gray-700">{{ number_format($maq['cantidad']) }}</td>
                                        <td class="px-3 py-2 text-right text-gray-500">{{ $maq['movimientos'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                @endif
            </div>
        </x-ui.modal>
    @endif

    @if($confirmandoDescarte)
        <x-ui.modal-confirmacion tipo="advertencia" titulo="Cambios sin guardar" boton="Descartar cambios"
                                 confirmar="descartarCambios" cancelar="seguirEditando" :z="60">
            Hay cambios sin guardar. ¿Desea descartarlos?
        </x-ui.modal-confirmacion>
    @endif

    @if($bajaAsignacion)
        <x-ui.modal-confirmacion tipo="advertencia" titulo="Dar de baja" boton="Dar de baja"
                                 confirmar="bajaAsignacionConfirmada" cancelar="cancelarBajaAsignacion">
            @php
                $bajaItem = $asignacionesPendientes->first(fn ($item) => $item['id_maquinaria'] == $bajaAsignacion['id']
                    && $item['tipo_referencia'] === $bajaAsignacion['tipo_referencia']
                    && $item['id_referencia'] == $bajaAsignacion['id_referencia']);
            @endphp
            ¿Está seguro de dar de baja {{ $bajaItem['cantidad_pendiente'] ?? '' }} unidad(es) de {{ $bajaItem['maquinaria_nombre'] ?? 'la maquinaria' }}? Esta acción NO devuelve stock.
        </x-ui.modal-confirmacion>
    @endif

    @if($eliminarComprobanteId)
        <x-ui.modal-confirmacion confirmar="eliminarComprobanteExistente" cancelar="cancelarEliminarComprobante" :z="60">
            ¿Eliminar este comprobante?
        </x-ui.modal-confirmacion>
    @endif
</x-ui.pagina>
