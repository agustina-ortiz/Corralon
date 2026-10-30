<x-ui.pagina titulo="Movimientos de Insumos" icono="fa-right-left">
    <x-slot:acciones>
        @if($puedeCrearTransferencias)
            <x-ui.boton variante="secundario" icono="fa-right-left" wire:click="crearTransferencia">Nueva Transferencia</x-ui.boton>
        @endif
        @if($puedeCrearMovimientos)
            <x-ui.boton icono="fa-plus" wire:click="crear">Nuevo Movimiento</x-ui.boton>
            {{-- Varios insumos a un mismo destinatario --}}
            <x-ui.boton variante="secundario" icono="fa-layer-group" wire:click="abrirModalMultiple">Movimientos Múltiples</x-ui.boton>
        @endif
    </x-slot:acciones>

    <!-- Búsqueda, filtros, estadísticas y exportar -->
    @php
        $filtrosActivos = collect([$filtro_corralon, $filtro_fecha_desde, $filtro_fecha_hasta, $filtro_deposito_origen, $filtro_usuario, $filtro_insumo, $filtro_categoria, $filtro_tipo_movimiento])->filter()->count();
    @endphp
    <div class="mb-6 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
        <x-ui.buscador wire:model.live="search" placeholder="Buscar movimientos..." />
        <div class="flex gap-3">
            <x-ui.boton-filtros :activos="$filtrosActivos" solo-icono wire:click="$toggle('showFilters')" />

            {{-- Botón Estadísticas --}}
            <button type="button" wire:click="toggleEstadisticas" title="Estadísticas" aria-label="Estadísticas"
                    class="inline-flex items-center justify-center px-3 py-2.5 rounded-xl text-base font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200 {{ \App\Support\Tema::botonSecundario() }}">
                <i class="fa-solid fa-chart-column" aria-hidden="true"></i>
            </button>

            {{-- Botón Exportar (dropdown Excel/PDF) --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" x-on:click="open = !open" title="Exportar" aria-label="Exportar"
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl text-base font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200 {{ \App\Support\Tema::botonSecundario() }}">
                    <i class="fa-solid fa-download" aria-hidden="true"></i>
                    <i class="fa-solid fa-chevron-down text-xs" aria-hidden="true"></i>
                </button>
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

        </div>
    </div>

    <!-- Panel de Filtros -->
    <x-ui.panel-filtros mostrar="showFilters" :activos="$filtrosActivos" columnas="4">
        <x-ui.campo etiqueta="Corralón" nombre="filtro_corralon">
            <x-ui.combobox nombre="filtro_corralon" wire:model.live="filtro_corralon" :opciones="$corralonesFiltro" etiqueta="descripcion"
                           vacio="Todos los corralones" placeholder="Todos los corralones" />
        </x-ui.campo>

        <x-ui.campo etiqueta="Fecha Desde" nombre="filtro_fecha_desde">
            <x-ui.input nombre="filtro_fecha_desde" type="date" wire:model.live="filtro_fecha_desde" />
        </x-ui.campo>

        <x-ui.campo etiqueta="Fecha Hasta" nombre="filtro_fecha_hasta">
            <x-ui.input nombre="filtro_fecha_hasta" type="date" wire:model.live="filtro_fecha_hasta" />
        </x-ui.campo>

        <x-ui.campo etiqueta="Depósito" nombre="filtro_deposito_origen">
            <x-ui.combobox nombre="filtro_deposito_origen" wire:model.live="filtro_deposito_origen" :opciones="$depositos" etiqueta="deposito"
                           vacio="Todos los depósitos" placeholder="Todos los depósitos" />
        </x-ui.campo>

        <x-ui.campo etiqueta="Tipo de Movimiento" nombre="filtro_tipo_movimiento">
            {{-- Los tipos en el orden de la consulta y, al final, la opción de las cargas múltiples --}}
            <x-ui.combobox nombre="filtro_tipo_movimiento" wire:model.live="filtro_tipo_movimiento"
                           :opciones="$tipos_movimiento->pluck('tipo_movimiento', 'id')->put(\App\Livewire\TransferenciasInsumos::FILTRO_MULTIPLES, 'Movimientos Múltiples')->all()"
                           :ordenar="false" vacio="Todos los tipos" placeholder="Todos los tipos" />
        </x-ui.campo>

        <x-ui.campo etiqueta="Usuario" nombre="filtro_usuario">
            <x-ui.combobox nombre="filtro_usuario" wire:model.live="filtro_usuario" :opciones="$usuarios" etiqueta="name"
                           vacio="Todos los usuarios" placeholder="Todos los usuarios" />
        </x-ui.campo>

        <x-ui.campo etiqueta="Insumo" nombre="filtro_insumo">
            <x-ui.input nombre="filtro_insumo" wire:model.live="filtro_insumo" placeholder="Nombre del insumo" autocomplete="off" />
        </x-ui.campo>

        <x-ui.campo etiqueta="Categoría" nombre="filtro_categoria">
            <x-ui.combobox nombre="filtro_categoria" wire:model.live="filtro_categoria" :opciones="$categorias"
                           vacio="Todas las categorías" placeholder="Todas las categorías" />
        </x-ui.campo>
    </x-ui.panel-filtros>

    
{{-- Panel de Asignaciones Pendientes de Reposición (solo si hay pendientes) --}}
    @if($asignacionesPendientes->count() > 0)
    <div class="mb-6">
        <button
            type="button"
            wire:click="$toggle('showAsignacionesPendientes')"
            aria-expanded="{{ $showAsignacionesPendientes ? 'true' : 'false' }}"
            class="w-full flex items-center justify-between px-5 py-3.5 bg-white border border-orange-200 rounded-xl hover:bg-orange-50 transition-all duration-200 shadow-sm"
        >
            <div class="flex items-center gap-3">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <i class="fa-regular fa-clock w-5 text-center text-orange-600" aria-hidden="true"></i>
                </div>
                <span class="text-sm font-semibold text-gray-800">Asignaciones Pendientes de Reposición</span>
                <x-ui.badge color="ambar">{{ $asignacionesPendientes->count() }}</x-ui.badge>
            </div>
            <i class="fa-solid fa-chevron-down text-gray-400 transition-transform duration-200 {{ $showAsignacionesPendientes ? 'rotate-180' : '' }}" aria-hidden="true"></i>
        </button>

        @if($showAsignacionesPendientes)
            <div class="mt-2 bg-white border border-orange-100 rounded-xl shadow-sm overflow-hidden">
                @if($asignacionesPendientes->count() > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-orange-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Insumo</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Depósito</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Asignado a</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Pendiente</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Devolver</th>
                                @if($puedeCrearMovimientos)
                                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider"><span class="sr-only">Acciones</span></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100"
                               x-data="{ cantidades: {} }"
                               x-init="
                                    @foreach($asignacionesPendientes as $idx => $asig)
                                        cantidades['{{ $idx }}'] = {{ $asig['cantidad_pendiente'] }};
                                    @endforeach
                               "
                        >
                            @foreach($asignacionesPendientes as $idx => $asig)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-5 py-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $asig['insumo_nombre'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $asig['categoria'] }}</div>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="text-sm text-gray-700">{{ $asig['deposito'] }}</span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            @if($asig['tipo_referencia'] === 'vehiculo')
                                                <x-ui.badge color="azul" class="gap-1"><i class="fa-solid fa-truck text-[10px]" aria-hidden="true"></i> Vehículo</x-ui.badge>
                                            @elseif($asig['tipo_referencia'] === 'evento')
                                                <x-ui.badge color="violeta" class="gap-1"><i class="fa-regular fa-calendar text-[10px]" aria-hidden="true"></i> Evento</x-ui.badge>
                                            @elseif($asig['tipo_referencia'] === 'empleado')
                                                <x-ui.badge color="verde" class="gap-1"><i class="fa-regular fa-user text-[10px]" aria-hidden="true"></i> Empleado</x-ui.badge>
                                            @endif
                                            <span class="text-sm text-gray-900">{{ $asig['referencia_nombre'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="text-sm font-semibold text-orange-600">
                                            {{ number_format($asig['cantidad_pendiente'], 2) }} {{ $asig['unidad'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            max="{{ $asig['cantidad_pendiente'] }}"
                                            x-model="cantidades['{{ $idx }}']"
                                            aria-label="Cantidad a devolver de {{ $asig['insumo_nombre'] }}"
                                            class="w-24 px-2 py-1.5 text-sm text-center border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                                        >
                                    </td>
                                    @if($puedeCrearMovimientos)
                                    <td class="px-5 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-ui.boton variante="secundario" tamano="chico" icono="fa-rotate-left"
                                                        x-on:click="$wire.devolverAsignacion({{ $asig['id_insumo'] }}, '{{ $asig['tipo_referencia'] }}', {{ $asig['id_referencia'] }}, cantidades['{{ $idx }}'])">
                                                Devolver
                                            </x-ui.boton>
                                            <x-ui.boton variante="peligro" tamano="chico" icono="fa-xmark"
                                                        wire:click="confirmarBajaAsignacion({{ $asig['id_insumo'] }}, '{{ $asig['tipo_referencia'] }}', {{ $asig['id_referencia'] }})"
                                                        title="Dar de baja el pendiente restante (no devuelve stock)">
                                                Dar de baja
                                            </x-ui.boton>
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <x-ui.vacio icono="fa-regular fa-circle-check" class="py-8">No hay asignaciones pendientes de reposición</x-ui.vacio>
                @endif
            </div>
        @endif
    </div>
    @endif

    {{-- Lista de Movimientos --}}
    <x-ui.tabla>
        <x-slot:cabecera>
            <x-ui.th class="w-12"><span class="sr-only">Detalle</span></x-ui.th>
            <x-ui.th>Fecha</x-ui.th>
            <x-ui.th>Detalles</x-ui.th>
            <x-ui.th>Cantidad</x-ui.th>
            <x-ui.th centro>Origen</x-ui.th>
            <x-ui.th centro>Destino</x-ui.th>
            <x-ui.th centro>Tipo</x-ui.th>
            <x-ui.th>Usuario</x-ui.th>
            @if($puedeCrearTransferencias)
            <x-ui.th derecha><span class="sr-only">Acciones</span></x-ui.th>
            @endif
        </x-slot:cabecera>

        @forelse ($movimientos as $item)
            @if($item['tipo'] === 'transferencia')
                @php
                    $movimiento = $item['data'];
                    $movimientosEntrada = $movimiento->movimientosEntrada;
                    $cantidadInsumos = $movimientosEntrada->count();
                    $esTransferenciaMultiple = $cantidadInsumos > 1;
                    $expandido = in_array($movimiento->id, $movimientos_expandidos);
                @endphp

                {{-- Fila de Transferencia --}}
                <tr class="hover:bg-gray-50 transition-colors {{ $expandido ? 'bg-purple-50' : '' }}">
                    <td class="px-4 py-4">
                        @if($esTransferenciaMultiple)
                            <x-ui.accion icono="fa-chevron-right" :etiqueta="$expandido ? 'Ocultar detalle' : 'Ver detalle'"
                                         wire:click="toggleMovimiento({{ $movimiento->id }})"
                                         aria-expanded="{{ $expandido ? 'true' : 'false' }}"
                                         :class="$expandido ? 'rotate-90' : ''" />
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $movimiento->fecha->format('d/m/Y') }}</div>
                        <div class="text-xs text-gray-500">{{ $movimiento->created_at->format('H:i') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        @if($esTransferenciaMultiple)
                            <div class="flex items-center gap-2">
                                <x-ui.badge color="violeta">{{ $cantidadInsumos }} insumos</x-ui.badge>
                                <span class="text-xs text-gray-500 truncate max-w-xs">
                                    {{ $movimientosEntrada->first()->insumo->insumo }}, ...
                                </span>
                            </div>
                        @else
                            @php
                                $mov = $movimientosEntrada->first();
                            @endphp
                            @if($mov && $mov->insumo)
                                <div>
                                    @if($puedeCrearMovimientos)
                                        <button type="button" wire:click="abrirModalConInsumo({{ $mov->insumo->id }})" class="text-sm font-medium text-gray-900 hover:text-blue-600 hover:underline transition-colors text-left">{{ $mov->insumo->insumo }}</button>
                                    @else
                                        <div class="text-sm font-medium text-gray-900">{{ $mov->insumo->insumo }}</div>
                                    @endif
                                    <div class="text-xs text-gray-500">{{ $mov->insumo->categoriaInsumo->nombre }}</div>
                                </div>
                            @else
                                <div>
                                    <div class="text-sm font-medium text-red-600">Insumo eliminado</div>
                                    <div class="text-xs text-gray-500">El insumo ya no existe</div>
                                </div>
                            @endif
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if(!$esTransferenciaMultiple && $mov && $mov->insumo)
                            <div class="text-sm font-semibold text-purple-600">
                                {{ number_format($mov->cantidad, 2) }} {{ $mov->insumo->unidad }}
                            </div>
                        @else
                            <span class="text-xs text-gray-500">Ver detalle</span>
                        @endif
                    </td>

                    {{-- COLUMNA ORIGEN --}}
                    <td class="px-6 py-4 text-center">
                        <x-ui.badge color="rojo">{{ $movimiento->depositoOrigen->deposito }}</x-ui.badge>
                    </td>

                    {{-- COLUMNA DESTINO --}}
                    <td class="px-6 py-4 text-center">
                        <x-ui.badge color="verde">{{ $movimiento->depositoDestino->deposito }}</x-ui.badge>
                    </td>

                    {{-- COLUMNA TIPO --}}
                    <td class="px-6 py-4 text-center">
                        <x-ui.badge color="violeta" class="gap-1"><i class="fa-solid fa-right-left" aria-hidden="true"></i> Transferencia</x-ui.badge>
                    </td>

                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $movimiento->usuario->name }}
                    </td>
                    @if($puedeCrearTransferencias)
                    <td class="px-6 py-4 text-right">
                        <x-ui.accion icono="fa-right-left" :etiqueta="'Nueva transferencia desde '.$movimiento->depositoDestino->deposito"
                                     wire:click="abrirModalTransferenciaDesdeTransferencia({{ $movimiento->id }})" />
                    </td>
                    @endif
                </tr>

                {{-- Detalle expandible de transferencia --}}
                @if($esTransferenciaMultiple && $expandido)
                    <tr class="bg-gradient-to-r from-purple-50 to-indigo-50">
                        <td colspan="8" class="px-6 py-4">
                            <div class="ml-8">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                    <i class="fa-solid fa-clipboard-list text-purple-600" aria-hidden="true"></i>
                                    Detalle de Transferencia
                                </h4>

                                @if($movimiento->observaciones)
                                    <div class="mb-3 p-3 bg-white rounded-lg border border-purple-200">
                                        <div class="text-xs font-medium text-gray-700 mb-1">Observaciones:</div>
                                        <div class="text-sm text-gray-600">{{ $movimiento->observaciones }}</div>
                                    </div>
                                @endif

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($movimientosEntrada as $detalle)
                                        <div class="bg-white rounded-lg border border-purple-200 p-4 hover:shadow-md transition-shadow">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    @if($puedeCrearMovimientos)
                                                        <button type="button" wire:click="abrirModalConInsumo({{ $detalle->insumo->id }})" class="text-sm font-semibold text-gray-900 hover:text-blue-600 hover:underline transition-colors text-left">{{ $detalle->insumo->insumo }}</button>
                                                    @else
                                                        <div class="text-sm font-semibold text-gray-900">{{ $detalle->insumo->insumo }}</div>
                                                    @endif
                                                    <div class="mt-1">
                                                        <x-ui.badge color="azul">{{ $detalle->insumo->categoriaInsumo->nombre }}</x-ui.badge>
                                                    </div>
                                                </div>
                                                <div class="text-right ml-3">
                                                    <div class="text-lg font-bold text-purple-600">
                                                        {{ number_format($detalle->cantidad, 2) }}
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $detalle->insumo->unidad }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3 pt-3 border-t border-gray-100">
                                                <div class="flex items-center justify-between text-xs">
                                                    <span class="text-gray-500">Stock actual:</span>
                                                    <span class="font-semibold {{ $detalle->insumo->stock_actual > 0 ? 'text-green-600' : 'text-orange-600' }}">
                                                        {{ number_format($detalle->insumo->stock_actual, 2) }} {{ $detalle->insumo->unidad }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </td>
                    </tr>
                @endif

            @else
                @php
                    $movimiento = $item['data'];
                    $colorTipo = $movimiento->tipoMovimiento->esSalida()
                        ? 'rojo'
                        : ($movimiento->tipoMovimiento->esEntrada() ? 'verde' : 'gris');
                @endphp

                {{-- Fila de Movimiento Individual --}}
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-4">
                        {{-- Sin botón de expansión para movimientos individuales --}}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $movimiento->fecha->format('d/m/Y') }}</div>
                        <div class="text-xs text-gray-500">{{ $movimiento->created_at->format('H:i') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div>
                            @if($puedeCrearMovimientos)
                                <button type="button" wire:click="abrirModalConInsumo({{ $movimiento->insumo->id }})" class="text-sm font-medium text-gray-900 hover:text-blue-600 hover:underline transition-colors text-left">{{ $movimiento->insumo->insumo }}</button>
                            @else
                                <div class="text-sm font-medium text-gray-900">{{ $movimiento->insumo->insumo }}</div>
                            @endif
                            <div class="text-xs text-gray-500">{{ $movimiento->insumo->categoriaInsumo->nombre }}</div>
                            @if($movimiento->observaciones)
                                <div class="flex items-start gap-1 mt-1 text-xs text-gray-400 max-w-xs" title="{{ $movimiento->observaciones }}">
                                    <i class="fa-regular fa-comment-dots flex-shrink-0 mt-0.5" aria-hidden="true"></i>
                                    <span class="line-clamp-2">{{ $movimiento->observaciones }}</span>
                                </div>
                            @endif
                        </div>
                    </td>
                    @php $esSalida = $movimiento->tipoMovimiento->esSalida(); @endphp
                    <td class="px-6 py-4">
                        <div class="text-sm font-semibold {{ $esSalida ? 'text-red-600' : 'text-green-600' }}">
                            {{ $esSalida ? '-' : '+' }}{{ number_format($movimiento->cantidad, 2) }} {{ $movimiento->insumo->unidad }}
                        </div>
                    </td>

                    {{-- COLUMNA ORIGEN - Para movimientos individuales --}}
                    <td class="px-6 py-4 text-center">
                        @if($esSalida)
                            <x-ui.badge color="rojo">{{ $movimiento->insumo->deposito->deposito }}</x-ui.badge>
                        @elseif(in_array($movimiento->tipo_referencia, ['vehiculo', 'evento', 'empleado']))
                            @php $ref = $movimiento->referencia; @endphp
                            <x-ui.badge color="ambar">
                                @if($movimiento->tipo_referencia === 'vehiculo')
                                    {{ $ref->vehiculo ?? 'Vehículo #'.$movimiento->id_referencia }}
                                @elseif($movimiento->tipo_referencia === 'evento')
                                    {{ $ref->evento ?? 'Evento #'.$movimiento->id_referencia }}
                                @else
                                    {{ $ref ? $ref->nombre_formateado : 'Empleado #'.$movimiento->id_referencia }}
                                @endif
                            </x-ui.badge>
                        @else
                            <span class="text-sm text-gray-400">—</span>
                        @endif
                    </td>

                    {{-- COLUMNA DESTINO - Para movimientos individuales --}}
                    <td class="px-6 py-4 text-center">
                        @if($esSalida && in_array($movimiento->tipo_referencia, ['vehiculo', 'evento', 'empleado']))
                            @php $ref = $movimiento->referencia; @endphp
                            <x-ui.badge color="ambar">
                                @if($movimiento->tipo_referencia === 'vehiculo')
                                    {{ $ref->vehiculo ?? 'Vehículo #'.$movimiento->id_referencia }}
                                @elseif($movimiento->tipo_referencia === 'evento')
                                    {{ $ref->evento ?? 'Evento #'.$movimiento->id_referencia }}
                                @else
                                    {{ $ref ? $ref->nombre_formateado : 'Empleado #'.$movimiento->id_referencia }}
                                @endif
                            </x-ui.badge>
                        @elseif(!$esSalida)
                            <x-ui.badge color="verde">{{ $movimiento->insumo->deposito->deposito }}</x-ui.badge>
                        @elseif($movimiento->id_secretaria || $movimiento->area)
                            @if($movimiento->secretaria)
                                <x-ui.badge color="violeta">{{ $movimiento->secretaria->secretaria }}</x-ui.badge>
                            @endif
                            @if($movimiento->area)
                                <span class="block mt-1 text-xs text-purple-600">{{ $movimiento->area }}</span>
                            @endif
                        @else
                            <span class="text-sm text-gray-400">—</span>
                        @endif
                    </td>

                    {{-- COLUMNA TIPO --}}
                    <td class="px-6 py-4 text-center">
                        <x-ui.badge :color="$colorTipo">{{ $movimiento->tipoMovimiento->tipo_movimiento }}</x-ui.badge>
                        @if($movimiento->nro_orden_compra)
                            <div class="text-xs text-gray-500 text-center mt-1">OC: {{ $movimiento->nro_orden_compra }}</div>
                        @endif
                    </td>

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
                                                    <x-ui.accion icono="fa-regular fa-eye" etiqueta="Ver" :href="route('comprobantes.ver', $comp->id)" target="_blank" />
                                                    <x-ui.accion icono="fa-download" etiqueta="Descargar" color="exito" :href="route('comprobantes.descargar', $comp->id)" :navegar="false" />
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @if($puedeCrearMovimientos && in_array($movimiento->tipoMovimiento->tipo_movimiento, ['Carga de Stock', 'Ajuste Positivo', 'Inventario Inicial']))
                                <x-ui.accion icono="fa-regular fa-pen-to-square" etiqueta="Editar N° orden, observaciones y comprobantes"
                                             wire:click="abrirEdicion({{ $movimiento->id }})" />
                            @endif
                        </div>
                    </td>
                    @if($puedeCrearTransferencias)
                    <td class="px-6 py-4 text-right">
                        <x-ui.accion icono="fa-right-left" :etiqueta="'Nueva transferencia desde '.$movimiento->insumo->deposito->deposito"
                                     wire:click="abrirModalTransferenciaDesdeMovimiento({{ $movimiento->insumo->id_deposito }}, {{ $movimiento->insumo->id }})" />
                    </td>
                    @endif
                </tr>
            @endif
        @empty
            <tr>
                <td colspan="9" class="px-6 py-6">
                    <x-ui.vacio icono="fa-right-left">{{ $search || $filtro_corralon || $filtro_fecha_desde || $filtro_fecha_hasta || $filtro_deposito_origen || $filtro_usuario || $filtro_insumo || $filtro_categoria || $filtro_tipo_movimiento ? 'No hay movimientos que coincidan con la búsqueda o los filtros' : 'Todavía no hay movimientos registrados' }}</x-ui.vacio>
                </td>
            </tr>
        @endforelse

        <x-slot:pie>
            <x-ui.paginacion :paginador="$movimientos" />
        </x-slot:pie>
    </x-ui.tabla>

    {{-- ✅ NUEVO: Modal de Transferencia Múltiple --}}
    @if($showModalTransferencia)
        <x-ui.modal titulo="Nueva Transferencia" icono="fa-right-left"
                    cerrar="intentarCerrar" ancho="4xl" submit="guardarTransferencia">
            <x-slot:encabezado>
                <span class="text-sm text-gray-500 font-normal">Transfiera múltiples insumos entre depósitos</span>
            </x-slot:encabezado>

            {{-- Corralones (solo si el usuario tiene acceso a múltiples) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if($tieneMultiplesCorralones)
                    <x-ui.campo etiqueta="Corralón Origen" nombre="id_corralon_origen" obligatorio>
                        <x-ui.combobox nombre="id_corralon_origen" wire:model.live="id_corralon_origen" placeholder="Seleccione corralón origen"
                                       :opciones="$corralones" etiqueta="descripcion" />
                    </x-ui.campo>

                    <x-ui.campo etiqueta="Corralón Destino" nombre="id_corralon_destino" obligatorio>
                        <x-ui.combobox nombre="id_corralon_destino" wire:model.live="id_corralon_destino" placeholder="Seleccione corralón destino"
                                       :opciones="$corralones" etiqueta="descripcion" />
                    </x-ui.campo>
                @endif

                <x-ui.campo etiqueta="Depósito Origen" nombre="id_deposito_origen" obligatorio
                            :ayuda="$tieneMultiplesCorralones && !$id_corralon_origen ? 'Primero seleccione un corralón de origen.' : null">
                    <x-ui.combobox nombre="id_deposito_origen" wire:model.live="id_deposito_origen" placeholder="Seleccione depósito origen"
                                   :disabled="$tieneMultiplesCorralones && !$id_corralon_origen"
                                   :ordenar="false" :opciones="$depositosOrigen" etiqueta="deposito" />
                </x-ui.campo>

                @php
                    $ayudaDestino = $tieneMultiplesCorralones && !$id_corralon_destino
                        ? 'Primero seleccione un corralón de destino.'
                        : (!$tieneMultiplesCorralones && !$id_deposito_origen ? 'Primero seleccione un depósito de origen.' : null);
                @endphp
                <x-ui.campo etiqueta="Depósito Destino" nombre="id_deposito_destino" obligatorio :ayuda="$ayudaDestino">
                    <x-ui.combobox nombre="id_deposito_destino" wire:model="id_deposito_destino" placeholder="Seleccione depósito destino"
                                   :disabled="$tieneMultiplesCorralones ? !$id_corralon_destino : !$id_deposito_origen"
                                   :ordenar="false"
                                   :opciones="$depositosDestino->mapWithKeys(fn ($d) => [$d->id => $d->deposito.(! $tieneMultiplesCorralones ? ' - '.$d->corralon->descripcion : '')])" />
                </x-ui.campo>
            </div>

            @if($id_deposito_origen)
                <!-- Búsqueda y selección de insumos -->
                <div>
                    <x-ui.campo etiqueta="Agregar Insumos" nombre="insumos_transferencia" obligatorio>
                        {{-- Busca en el servidor (search_insumo_transferencia); al elegir agrega la línea. --}}
                        <x-ui.combobox nombre="insumos_transferencia" buscar-en="search_insumo_transferencia" accion="agregarInsumoTransferencia"
                                       enfocar="[id^='campo-insumos_transferencia.'][id$='.cantidad']"
                                       placeholder="Buscar insumo por nombre o categoría..."
                                       sin-resultados="No hay insumos con stock disponible que coincidan con la búsqueda"
                                       x-on:combobox-abierto="$wire.mostrarListaTransferencia()"
                                       :opciones="$insumos_disponibles_transferencia->map(function ($insumo) use ($insumos_transferencia) {
                                           $yaAgregado = collect($insumos_transferencia)->contains('id', $insumo->id);

                                           return [
                                               'id' => $insumo->id,
                                               'nombre' => $insumo->insumo,
                                               'insignia' => $insumo->categoriaInsumo->nombre,
                                               'cifra' => number_format($insumo->stock_actual, 2),
                                               'subcifra' => $insumo->unidad,
                                               'nota' => $yaAgregado ? '(agregado)' : null,
                                               'deshabilitada' => $yaAgregado ?: null,
                                           ];
                                       })" />
                    </x-ui.campo>
                </div>

                <!-- Lista de insumos seleccionados -->
                @if(count($insumos_transferencia) > 0)
                    <div class="border border-gray-200 rounded-xl p-4">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-clipboard-list text-purple-600" aria-hidden="true"></i>
                            Insumos a transferir ({{ count($insumos_transferencia) }})
                        </h4>

                        <div class="space-y-3">
                            @foreach($insumos_transferencia as $index => $item)
                                @php $campoCantidad = "insumos_transferencia.{$index}.cantidad"; @endphp
                                <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg
                                    @error($campoCantidad) bg-red-50 border border-red-200 @enderror">
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900">{{ $item['nombre'] }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            {{ $item['categoria'] }} • Disponible:
                                            <span class="font-semibold text-green-600">{{ number_format($item['stock_actual'], 2) }} {{ $item['unidad'] }}</span>
                                        </div>
                                    </div>
                                    <div class="w-44 flex-shrink-0">
                                        <x-ui.campo :nombre="$campoCantidad">
                                            <div class="relative">
                                                <x-ui.input :nombre="$campoCantidad" type="number" step="0.01" min="0.01"
                                                            wire:model.live="insumos_transferencia.{{ $index }}.cantidad"
                                                            max="{{ $item['stock_actual'] }}"
                                                            placeholder="Cantidad"
                                                            aria-label="Cantidad de {{ $item['nombre'] }}"
                                                            class="pr-16" />
                                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-medium text-gray-500 pointer-events-none">
                                                    {{ $item['unidad'] }}
                                                </span>
                                            </div>
                                        </x-ui.campo>
                                    </div>
                                    <x-ui.accion icono="fa-regular fa-trash-can" color="peligro" :etiqueta="'Quitar '.$item['nombre']"
                                                 wire:click="removerInsumoTransferencia({{ $index }})" class="mt-1" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="border border-dashed border-gray-300 rounded-xl">
                        <x-ui.vacio icono="fa-box-open" class="py-8">No hay insumos seleccionados: búsquelos y agréguelos para transferir</x-ui.vacio>
                    </div>
                @endif

                <x-ui.campo etiqueta="Observaciones" nombre="observaciones_transferencia">
                    <x-ui.textarea nombre="observaciones_transferencia" wire:model="observaciones_transferencia"
                                   placeholder="Motivo de la transferencia (opcional)" />
                </x-ui.campo>
            @else
                <x-ui.vacio icono="fa-warehouse" class="py-12">Seleccione un depósito de origen para comenzar a agregar insumos</x-ui.vacio>
            @endif

            <x-slot:footer>
                <x-ui.boton variante="secundario" wire:click="intentarCerrar">Cancelar</x-ui.boton>
                <x-ui.boton type="submit" wire:loading.attr="disabled" wire:target="guardarTransferencia">
                    Confirmar Transferencia
                </x-ui.boton>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    {{-- Modal de Movimientos Individuales (Carga, Ajustes) --}}
    @if($showModal)
        <x-ui.modal titulo="Nuevo Movimiento de Insumo" icono="fa-plus"
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
                            Paso 1: Seleccionar Insumo
                        @elseif($paso_actual === 2)
                            Paso 2: Tipo de Movimiento
                        @else
                            Paso 3: Completar Datos
                        @endif
                    </div>
                </div>
            </x-slot:encabezado>

            <!-- PASO 1: Seleccionar Insumo -->
            @if($paso_actual === 1)
                <x-ui.campo etiqueta="Buscar Insumo" nombre="search_insumo" obligatorio>
                    {{-- Busca en el servidor (search_insumo); al elegir avanza al paso 2. --}}
                    <x-ui.combobox nombre="search_insumo" buscar-en="search_insumo" accion="seleccionarInsumo"
                                   placeholder="Buscar por nombre, categoría o depósito..."
                                   sin-resultados="No hay insumos que coincidan con la búsqueda"
                                   x-on:combobox-abierto="$wire.mostrarLista()"
                                   :opciones="$insumos_filtrados->map(fn ($insumo) => [
                                       'id' => $insumo->id,
                                       'nombre' => $insumo->insumo,
                                       'insignia' => $insumo->categoriaInsumo->nombre,
                                       'detalle' => $insumo->deposito->deposito,
                                       'cifra' => number_format($insumo->stock_actual, 2),
                                       'cifra_color' => $insumo->stock_actual > 0 ? 'verde' : 'naranja',
                                       'subcifra' => $insumo->unidad,
                                   ])" />
                </x-ui.campo>

                @if(!$search_insumo)
                    <x-ui.vacio icono="fa-magnifying-glass" class="py-12">Comience buscando un insumo por nombre, categoría o depósito</x-ui.vacio>
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
            @if($paso_actual === 3 && $insumo_seleccionado && $tipo_movimiento)
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

                    @if(in_array($tipo_movimiento, ['asignacion_con_reposicion', 'asignacion_sin_reposicion', 'entrada_reposicion', 'ajuste_negativo']))
                        <!-- Selector de tipo de destino -->
                        @php
                            $destinoLabel = $tipo_movimiento === 'entrada_reposicion'
                                ? 'Devuelto desde'
                                : ($tipo_movimiento === 'ajuste_negativo' ? 'Asignar a (opcional)' : 'Asignar a');
                            $destinoObligatorio = $tipo_movimiento !== 'ajuste_negativo';
                            $tiposDestino = [
                                'vehiculo' => ['Vehículo', 'fa-truck'],
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
                                        <i class="{{ str_starts_with($iconoDestino, 'fa-regular') ? $iconoDestino : 'fa-solid '.$iconoDestino }} block text-xl mb-1" aria-hidden="true"></i>
                                        <span class="text-sm font-medium">{{ $etiquetaDestino }}</span>
                                    </button>
                                @endforeach
                            </div>
                            @error('tipo_destino')
                                <p id="error-tipo_destino" class="mt-1 text-xs text-red-600" data-error="tipo_destino">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <!-- Buscar y seleccionar vehículo/evento/empleado -->
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
                                            x-data="{ modoLibre: false }" x-init="$watch('modoLibre', val => { if(val) $nextTick(() => $refs.areaInputInsumos?.focus()) })">
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
                                            <x-ui.input nombre="area_ajuste" wire:model="area_ajuste" x-ref="areaInputInsumos"
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
                    @endif

                    <!-- Campo Cantidad -->
                    @php $limitaStock = in_array($tipo_movimiento, ['ajuste_negativo', 'asignacion_con_reposicion', 'asignacion_sin_reposicion']); @endphp
                    <x-ui.campo etiqueta="Cantidad" nombre="cantidad" obligatorio
                                :ayuda="$limitaStock ? 'Máximo disponible: '.number_format($insumo_seleccionado->stock_actual, 2).' '.$insumo_seleccionado->unidad : null">
                        <div class="relative">
                            <x-ui.input nombre="cantidad" type="number" step="0.01" wire:model="cantidad"
                                        :max="$limitaStock ? $insumo_seleccionado->stock_actual : null"
                                        placeholder="0.00" class="pr-20" />
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-gray-500 font-medium pointer-events-none">
                                {{ $insumo_seleccionado->unidad }}
                            </span>
                        </div>
                    </x-ui.campo>

                    <!-- Orden de compra / N° de suministro (solo para Carga de Stock y Ajuste Positivo) -->
                    @if(in_array($tipo_movimiento, ['carga', 'ajuste_positivo']))
                        <x-ui.campo etiqueta="Orden de compra / N° de suministro" nombre="nro_orden_compra">
                            <x-ui.input nombre="nro_orden_compra" wire:model="nro_orden_compra" placeholder="Ej: OC-2026-0042" autocomplete="off" />
                        </x-ui.campo>
                    @endif

                    <x-ui.campo etiqueta="Observaciones" nombre="observaciones">
                        <x-ui.textarea nombre="observaciones" wire:model="observaciones" placeholder="Motivo del movimiento (opcional)" />
                    </x-ui.campo>

                    <!-- Comprobantes (solo para Carga de Stock y Ajuste Positivo) -->
                    @if(in_array($tipo_movimiento, ['carga', 'ajuste_positivo']))
                        <x-ui.campo etiqueta="Comprobantes (opcional)" nombre="comprobantes" ayuda="PDF, JPG o PNG. Máximo 5 archivos, 5 MB cada uno.">
                            <x-ui.input nombre="comprobantes" type="file" wire:model="comprobantes" multiple accept=".pdf,.jpg,.jpeg,.png"
                                        class="text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:cursor-pointer" />
                            @error('comprobantes.*')
                                <p class="mt-1 text-xs text-red-600" data-error="comprobantes.*">{{ $message }}</p>
                            @enderror

                            @include('livewire.partials.preview-archivos', ['files' => $comprobantes, 'removeMethod' => 'removeComprobante'])

                            <div wire:loading wire:target="comprobantes" class="mt-2 text-sm text-blue-600">
                                Subiendo archivos...
                            </div>
                        </x-ui.campo>
                    @endif
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

    {{-- Modal de Movimientos Múltiples (varios insumos → un empleado, en una sola operación) --}}
    @if($showModalMultiple)
        <x-ui.modal titulo="Movimientos Múltiples" icono="fa-layer-group"
                    cerrar="intentarCerrar" ancho="5xl">
            <x-slot:encabezado>
                <div class="flex items-center gap-2">
                    @for($i = 1; $i <= 2; $i++)
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-7 h-7 rounded-full {{ $multi_paso >= $i ? 'bg-teal-600 text-white' : 'bg-gray-200 text-gray-500' }} font-medium text-xs transition-all duration-200">
                                {{ $i }}
                            </div>
                            @if($i < 2)
                                <div class="w-8 h-1 {{ $multi_paso > $i ? 'bg-teal-600' : 'bg-gray-200' }} transition-all duration-200"></div>
                            @endif
                        </div>
                    @endfor
                    <div class="ml-2 text-sm text-gray-600 font-normal">
                        @if($multi_paso === 1)
                            Paso 1: Seleccionar Empleado
                        @else
                            Paso 2: Insumos y Tipos de Movimiento
                        @endif
                    </div>
                </div>
            </x-slot:encabezado>

            {{-- Mensajes de error de validación (dentro del modal) --}}
            @if($errors->any())
                <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                    <div class="flex items-start">
                        <i class="fa-solid fa-triangle-exclamation mr-2 flex-shrink-0 mt-0.5" aria-hidden="true"></i>
                        <div class="text-sm">
                            @if($errors->any())
                                <ul class="list-disc list-inside space-y-0.5">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- PASO 1: Seleccionar destinatario (vehículo/evento/empleado/secretaría) --}}
            @if($multi_paso === 1)
                @php
                    $multiTiposDestino = [
                        'vehiculo' => ['Vehículo', 'fa-solid fa-truck'],
                        'evento' => ['Evento', 'fa-regular fa-calendar'],
                        'empleado' => ['Empleado', 'fa-regular fa-user'],
                        'secretaria' => ['Secretaría', 'fa-regular fa-building'],
                    ];
                @endphp
                <div class="space-y-5">
                    {{-- Selector de tipo de destino: grupo de opciones, fieldset + legend --}}
                    <fieldset class="w-full min-w-0">
                        <legend class="block text-sm font-medium text-gray-700 mb-2">
                            Destinatario <span class="text-red-600" aria-hidden="true">*</span>
                        </legend>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach($multiTiposDestino as $clave => [$etiquetaDestino, $iconoDestino])
                                <button type="button" wire:click="multiSeleccionarTipoDestino('{{ $clave }}')"
                                    aria-pressed="{{ $multi_tipo_destino === $clave ? 'true' : 'false' }}"
                                    class="p-3 border-2 rounded-xl text-center transition-all duration-200 {{ $multi_tipo_destino === $clave ? 'border-teal-500 bg-teal-50 text-teal-700' : 'border-gray-200 hover:border-gray-300 text-gray-600' }}">
                                    <i class="{{ $iconoDestino }} block text-xl mb-1" aria-hidden="true"></i>
                                    <span class="text-sm font-medium">{{ $etiquetaDestino }}</span>
                                </button>
                            @endforeach
                        </div>
                    </fieldset>

                    {{-- Buscar y seleccionar vehículo/evento/empleado --}}
                    @if(in_array($multi_tipo_destino, ['vehiculo', 'evento', 'empleado']))
                        <x-ui.campo :etiqueta="'Seleccionar '.($multi_tipo_destino === 'vehiculo' ? 'Vehículo' : ($multi_tipo_destino === 'evento' ? 'Evento' : 'Empleado'))"
                                    nombre="multi_id_referencia" obligatorio>
                            @if($multi_id_referencia)
                                @php
                                    if ($multi_tipo_destino === 'vehiculo') {
                                        $multiDestinoSeleccionado = \App\Models\Vehiculo::find($multi_id_referencia);
                                    } elseif ($multi_tipo_destino === 'evento') {
                                        $multiDestinoSeleccionado = \App\Models\Evento::find($multi_id_referencia);
                                    } else {
                                        $multiDestinoSeleccionado = \App\Models\EmpleadoMunicipal::find($multi_id_referencia);
                                    }
                                @endphp
                                <div class="flex items-center justify-between bg-teal-50 border border-teal-200 rounded-xl p-3">
                                    <div>
                                        @if($multi_tipo_destino === 'vehiculo' && $multiDestinoSeleccionado)
                                            <div class="text-sm font-medium text-teal-900">{{ $multiDestinoSeleccionado->vehiculo }}</div>
                                            <div class="text-xs text-teal-700">{{ $multiDestinoSeleccionado->patente }} • {{ $multiDestinoSeleccionado->marca_modelo }}</div>
                                        @elseif($multi_tipo_destino === 'evento' && $multiDestinoSeleccionado)
                                            <div class="text-sm font-medium text-teal-900">{{ $multiDestinoSeleccionado->evento }}</div>
                                            <div class="text-xs text-teal-700">{{ $multiDestinoSeleccionado->fecha?->format('d/m/Y') }} • {{ $multiDestinoSeleccionado->ubicacion }}</div>
                                        @elseif($multi_tipo_destino === 'empleado' && $multiDestinoSeleccionado)
                                            <div class="text-sm font-medium text-teal-900">{{ $multiDestinoSeleccionado->nombre_formateado }}</div>
                                            <div class="text-xs text-teal-700">Legajo {{ $multiDestinoSeleccionado->LEGAJO }} • DNI {{ number_format($multiDestinoSeleccionado->DNI, 0, '', '.') }}</div>
                                        @endif
                                    </div>
                                    <x-ui.accion icono="fa-xmark" etiqueta="Quitar la selección" wire:click="$set('multi_id_referencia', '')" />
                                </div>
                            @else
                                {{-- Busca en el servidor (multi_search_destino); al elegir marca el destinatario. --}}
                                @include('livewire.partials.combobox-destino', [
                                    'tipo' => $multi_tipo_destino,
                                    'nombre' => 'multi_id_referencia',
                                    'buscarEn' => 'multi_search_destino',
                                    'accion' => 'multiSeleccionarDestino',
                                    'alAbrir' => "\$wire.set('multi_mostrar_lista_destino', true)",
                                    'vehiculos' => $multi_vehiculos,
                                    'eventos' => $multi_eventos,
                                    'empleados' => $multi_empleados,
                                ])
                            @endif
                        </x-ui.campo>
                    @elseif($multi_tipo_destino === 'secretaria')
                        {{-- Secretaría y Área --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-ui.campo etiqueta="Secretaría" nombre="multi_id_secretaria" obligatorio>
                                <x-ui.combobox nombre="multi_id_secretaria" wire:model.live="multi_id_secretaria" placeholder="— Seleccionar —"
                                               :opciones="$secretarias" etiqueta="secretaria" />
                            </x-ui.campo>
                            <x-ui.campo etiqueta="Área" nombre="multi_area"
                                        :ayuda="$multi_id_secretaria && count($multi_areas_disponibles) === 0 ? 'No hay áreas cargadas para esta secretaría.' : null"
                                        x-data="{ modoLibre: false }" x-init="$watch('modoLibre', val => { if(val) $nextTick(() => $refs.areaInputMulti?.focus()) })">
                                <template x-if="!modoLibre">
                                    <div>
                                        <x-ui.combobox nombre="multi_area" wire:model="multi_area" placeholder="— Seleccionar —"
                                                       :opciones="collect($multi_areas_disponibles)->values()" />
                                        <x-ui.boton variante="enlace" tamano="chico" icono="fa-regular fa-pen-to-square" class="mt-1"
                                                    x-on:click="modoLibre = true; $wire.set('multi_area', '')">
                                            Escribir área manualmente
                                        </x-ui.boton>
                                    </div>
                                </template>
                                <template x-if="modoLibre">
                                    <div>
                                        <x-ui.input nombre="multi_area" wire:model="multi_area" x-ref="areaInputMulti" placeholder="Escribir nombre del área" />
                                        @if(count($multi_areas_disponibles) > 0)
                                            <x-ui.boton variante="enlace" tamano="chico" icono="fa-list" class="mt-1"
                                                        x-on:click="modoLibre = false; $wire.set('multi_area', '')">
                                                Seleccionar de la lista
                                            </x-ui.boton>
                                        @endif
                                    </div>
                                </template>
                            </x-ui.campo>
                        </div>
                    @endif
                </div>
            @endif

            {{-- PASO 2: Insumos + tipos --}}
            @if($multi_paso === 2)
                {{-- Destinatario seleccionado --}}
                <div class="flex items-center justify-between bg-teal-50 border border-teal-200 rounded-xl p-3 mb-5">
                    <div>
                        <div class="text-xs text-teal-700 uppercase tracking-wide font-medium">
                            Destinatario · {{ ucfirst($multi_tipo_destino) }}
                        </div>
                        <div class="text-sm font-semibold text-teal-900">{{ $multi_destino_sel }}</div>
                    </div>
                    <x-ui.boton variante="enlace" wire:click="multiVolverPaso">Cambiar</x-ui.boton>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Columna izquierda: buscador + lista de insumos --}}
                    <x-ui.campo class="flex flex-col" etiqueta="Agregar Insumo" nombre="multi_search_insumo">
                        <div class="relative">
                            <x-ui.input nombre="multi_search_insumo"
                                        wire:model.live="multi_search_insumo"
                                        wire:focus="$set('multi_mostrar_lista_insumo', true)"
                                        placeholder="Buscar insumo..."
                                        class="pr-10" autocomplete="off" />
                            <i class="fa-solid fa-magnifying-glass absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" aria-hidden="true"></i>
                        </div>

                        <div class="mt-2 border border-gray-200 rounded-xl h-56 overflow-y-auto">
                            @if($multi_insumos->count() > 0)
                                <ul class="divide-y divide-gray-100">
                                    @foreach($multi_insumos as $ins)
                                        <li>
                                            <button
                                                type="button"
                                                wire:click="agregarLineaMultiple({{ $ins->id }})"
                                                title="Agregar {{ $ins->insumo }}"
                                                class="w-full text-left px-4 py-3 hover:bg-teal-50 transition-colors duration-150 flex items-center justify-between gap-2"
                                            >
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $ins->insumo }}</div>
                                                    <div class="text-xs text-gray-500 mt-0.5">
                                                        {{ $ins->categoriaInsumo->nombre ?? 'Sin categoría' }} • {{ $ins->deposito->deposito ?? '' }} • Stock: {{ $ins->stock_actual }} {{ $ins->unidad }}
                                                    </div>
                                                </div>
                                                <i class="fa-solid fa-plus text-teal-600 flex-shrink-0" aria-hidden="true"></i>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <x-ui.vacio icono="fa-magnifying-glass">{{ $multi_search_insumo ? 'No hay insumos con stock que coincidan con la búsqueda' : 'Escriba para buscar un insumo' }}</x-ui.vacio>
                            @endif
                        </div>
                    </x-ui.campo>

                    {{-- Columna derecha: insumos agregados con tipo + cantidad --}}
                    <div class="flex flex-col">
                        <div class="block text-sm font-medium text-gray-700 mb-2">
                            Insumos agregados
                            <span class="text-gray-400 font-normal">({{ count($multi_lineas) }})</span>
                        </div>
                        <div class="border border-gray-200 rounded-xl flex-1 min-h-0 max-h-35 overflow-y-auto">
                            @forelse($multi_lineas as $index => $linea)
                                @php
                                    $campoTipo = "multi_lineas.{$index}.tipo";
                                    $campoCant = "multi_lineas.{$index}.cantidad";
                                @endphp
                                <div class="p-3 border-b border-gray-100 last:border-b-0">
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <div class="min-w-0">
                                            <div class="text-sm font-medium text-gray-900 truncate">{{ $linea['insumo_nombre'] }}</div>
                                            <div class="text-xs text-gray-500">Stock: {{ $linea['stock'] }} {{ $linea['unidad'] }}</div>
                                        </div>
                                        <x-ui.accion icono="fa-xmark" color="peligro" :etiqueta="'Quitar '.$linea['insumo_nombre']"
                                                     wire:click="quitarLineaMultiple({{ $index }})" class="flex-shrink-0" />
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        {{-- Grupo de radios: fieldset sin rótulo visible (lo dice el aria-label) --}}
                                        <x-ui.grupo class="col-span-2" :nombre="$campoTipo">
                                            <x-ui.opciones :nombre="$campoTipo" wire:model="multi_lineas.{{ $index }}.tipo" class="w-full"
                                                           aria-label="Tipo de movimiento de {{ $linea['insumo_nombre'] }}"
                                                           :opciones="['asignacion_con_reposicion' => 'Con reposición', 'asignacion_sin_reposicion' => 'Sin reposición']" />
                                        </x-ui.grupo>
                                        <x-ui.campo :nombre="$campoCant">
                                            <x-ui.input :nombre="$campoCant" type="number" min="1" step="any"
                                                        wire:model="multi_lineas.{{ $index }}.cantidad"
                                                        placeholder="Cant."
                                                        aria-label="Cantidad de {{ $linea['insumo_nombre'] }}" />
                                        </x-ui.campo>
                                    </div>
                                </div>
                            @empty
                                <x-ui.vacio icono="fa-box-open" class="py-8">Agregue insumos desde la columna izquierda</x-ui.vacio>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Observaciones: se guardan en todos los movimientos generados --}}
                <x-ui.campo class="mt-4" etiqueta="Observaciones (opcional)" nombre="multi_observaciones">
                    <x-ui.textarea nombre="multi_observaciones" wire:model="multi_observaciones" filas="2" maxlength="1000"
                                   placeholder="Comentario aplicado a todos los movimientos de esta carga..." class="resize-none" />
                </x-ui.campo>
            @endif

            <x-slot:footer class="justify-between">
                <div>
                    @if($multi_paso === 2)
                        <x-ui.boton variante="secundario" icono="fa-chevron-left" wire:click="multiVolverPaso">Volver</x-ui.boton>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <x-ui.boton variante="secundario" wire:click="intentarCerrar">Cancelar</x-ui.boton>
                    @if($multi_paso === 1)
                        <x-ui.boton wire:click="multiContinuar" data-atajo-guardar>
                            Continuar <i class="fa-solid fa-chevron-right text-sm" aria-hidden="true"></i>
                        </x-ui.boton>
                    @endif
                    @if($multi_paso === 2)
                        <x-ui.boton wire:click="guardarMultiple" data-atajo-guardar :disabled="count($multi_lineas) === 0"
                                    wire:loading.attr="disabled" wire:target="guardarMultiple">
                            Registrar {{ count($multi_lineas) > 0 ? count($multi_lineas) : '' }} {{ count($multi_lineas) === 1 ? 'movimiento' : 'movimientos' }}
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
                                        <x-ui.accion icono="fa-regular fa-eye" etiqueta="Ver" :href="route('comprobantes.ver', $comp->id)" target="_blank" />
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
                        <div class="text-xs text-gray-500 mt-1">Entradas ({{ number_format($estadisticas['entradas_sum'], 0) }} u.)</div>
                    </div>
                    <div class="bg-red-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-red-600">{{ number_format($estadisticas['salidas_cant']) }}</div>
                        <div class="text-xs text-gray-500 mt-1">Salidas ({{ number_format($estadisticas['salidas_sum'], 0) }} u.)</div>
                    </div>
                </div>

                {{-- Movimientos por tipo --}}
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

                {{-- Top insumos movidos --}}
                @if($estadisticas['top_insumos']->count() > 0)
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Insumos más movidos</h4>
                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs text-gray-500">
                                <tr>
                                    <th class="text-left px-3 py-2">Insumo</th>
                                    <th class="text-right px-3 py-2">Cant. total</th>
                                    <th class="text-right px-3 py-2">N° mov.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($estadisticas['top_insumos'] as $ins)
                                    <tr class="border-t border-gray-50">
                                        <td class="px-3 py-2 text-gray-700">{{ $ins['nombre'] }}</td>
                                        <td class="px-3 py-2 text-right font-medium text-gray-700">{{ number_format($ins['cantidad'], 2) }} {{ $ins['unidad'] }}</td>
                                        <td class="px-3 py-2 text-right text-gray-500">{{ $ins['movimientos'] }}</td>
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
            ¿Dar de baja el pendiente restante? Esta acción no devuelve stock al depósito.
        </x-ui.modal-confirmacion>
    @endif

    @if($eliminarComprobanteId)
        <x-ui.modal-confirmacion confirmar="eliminarComprobanteExistente" cancelar="cancelarEliminarComprobante" :z="60">
            ¿Eliminar este comprobante?
        </x-ui.modal-confirmacion>
    @endif
</x-ui.pagina>
