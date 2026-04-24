<div>
    <!-- Cards con estadísticas generales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        @if(in_array('card_insumos', $cardsActivas))
        <div class="bg-gradient-to-br from-white to-[#77BF43]/5 border border-gray-200 rounded-lg shadow-sm p-4 transform hover:shadow-xl hover:shadow-[#77BF43]/30 hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs font-medium uppercase tracking-wide">Total Insumos</p>
                    <p class="text-2xl font-bold mt-1 text-gray-800">{{ number_format($totalInsumos) }}</p>
                </div>
                <div class="p-3 rounded-full bg-blue-50">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
        </div>
        @endif

        @if(in_array('card_maquinaria', $cardsActivas))
        <div class="bg-gradient-to-br from-white to-[#77BF43]/5 border border-gray-200 rounded-lg shadow-sm p-4 transform hover:shadow-xl hover:shadow-[#77BF43]/30 hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs font-medium uppercase tracking-wide">Maquinaria</p>
                    <p class="text-2xl font-bold mt-1 text-gray-800">{{ number_format($totalMaquinaria) }}</p>
                </div>
                <div class="p-3 rounded-full bg-green-50">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                </div>
            </div>
        </div>
        @endif

        @if(in_array('card_vehiculos', $cardsActivas))
        <div class="bg-gradient-to-br from-white to-[#77BF43]/5 border border-gray-200 rounded-lg shadow-sm p-4 transform hover:shadow-xl hover:shadow-[#77BF43]/30 hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs font-medium uppercase tracking-wide">Vehículos</p>
                    <p class="text-2xl font-bold mt-1 text-gray-800">{{ number_format($totalVehiculos) }}</p>
                </div>
                <div class="p-3 rounded-full bg-orange-50">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>
        @endif

        @if(in_array('card_eventos', $cardsActivas))
        <div class="bg-gradient-to-br from-white to-[#77BF43]/5 border border-gray-200 rounded-lg shadow-sm p-4 transform hover:shadow-xl hover:shadow-[#77BF43]/30 hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs font-medium uppercase tracking-wide">Próximos Eventos</p>
                    <p class="text-2xl font-bold mt-1 text-gray-800">{{ number_format($countProximosEventos) }}</p>
                </div>
                <div class="p-3 rounded-full bg-indigo-50">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>
        @endif

    </div>

    <!-- Sección de Estadísticas de Alerta -->
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Estadísticas y Alertas</h2>
        <button
            wire:click="abrirModalPersonalizar"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all duration-150"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
            </svg>
            Personalizar panel
        </button>
    </div>

    @if($widgetsActivos)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        @if(in_array('stock_bajo', $widgetsActivos))
        <!-- Insumos con Stock Bajo -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-orange-50 to-orange-100 px-6 py-4 flex items-center justify-between border-b border-orange-200">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-orange-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-800">Insumos con Stock Bajo</h3>
                </div>
                <span class="bg-orange-200 text-orange-800 font-bold px-3 py-1 rounded-full text-sm">
                    {{ $countInsumosBajoMinimo }}
                </span>
            </div>
            <div class="p-6">
                @if($insumosBajoMinimo->count() > 0)
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($insumosBajoMinimo as $insumo)
                            <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg border border-orange-100 hover:border-orange-200 hover:bg-orange-100 transition-colors">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800">{{ $insumo->insumo }}</p>
                                    <p class="text-sm text-gray-600">
                                        {{ $insumo->categoriaInsumo->categoria ?? 'Sin categoría' }} •
                                        {{ $insumo->deposito->deposito ?? 'Sin depósito' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $insumo->deposito->corralon->descripcion ?? 'Sin corralón' }}
                                    </p>
                                </div>
                                <div class="text-right ml-4">
                                    <div class="flex items-center justify-end space-x-2">
                                        <span class="text-2xl font-bold text-orange-600">{{ number_format($insumo->stock_actual, 2) }}</span>
                                        <span class="text-sm text-gray-500">{{ $insumo->unidad }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Mínimo: {{ number_format($insumo->stock_minimo, 2) }}</p>
                                    <p class="text-xs font-semibold text-orange-600">
                                        Faltan: {{ number_format($insumo->stock_minimo - $insumo->stock_actual, 2) }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="font-medium">No hay insumos con stock bajo el mínimo</p>
                    </div>
                @endif
            </div>
        </div>
        @endif

        @if(in_array('vtv_vencer', $widgetsActivos))
        <!-- VTVs Próximas a Vencer -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-amber-50 to-amber-100 px-6 py-4 flex items-center justify-between border-b border-amber-200">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-amber-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-800">VTVs Próximas a Vencer</h3>
                </div>
                <span class="bg-amber-200 text-amber-800 font-bold px-3 py-1 rounded-full text-sm">
                    {{ $countVtvProximasVencer }}
                </span>
            </div>
            <div class="p-6">
                @if($vtvProximasVencer->count() > 0)
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($vtvProximasVencer as $vehiculo)
                            @php
                                $vencimiento = \Carbon\Carbon::parse($vehiculo->vencimiento_vtv);
                                $hoy = \Carbon\Carbon::today();
                                $diasRestantes = (int) $hoy->diffInDays($vencimiento, false);
                                $estaVencida = $diasRestantes < 0;
                                $vencePronto = $diasRestantes >= 0 && $diasRestantes <= 7;
                            @endphp
                            <div class="flex items-center justify-between p-3
                                @if($estaVencida) bg-red-50 border border-red-200 hover:border-red-300 hover:bg-red-100
                                @elseif($vencePronto) bg-orange-50 border border-orange-200 hover:border-orange-300 hover:bg-orange-100
                                @else bg-amber-50 border border-amber-100 hover:border-amber-200 hover:bg-amber-100
                                @endif
                                rounded-lg transition-colors">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        @if($vehiculo->nro_movil)
                                            <span class="
                                                @if($estaVencida) bg-red-600 text-white
                                                @elseif($vencePronto) bg-orange-600 text-white
                                                @else bg-amber-600 text-white
                                                @endif
                                                px-2 py-1 rounded text-xs font-bold">
                                                {{ $vehiculo->nro_movil }}
                                            </span>
                                        @endif
                                        <p class="font-semibold text-gray-800">{{ $vehiculo->vehiculo }}</p>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">
                                        @if($vehiculo->marca){{ $vehiculo->marca }}@endif
                                        @if($vehiculo->marca && $vehiculo->modelo) - @endif
                                        @if($vehiculo->modelo){{ $vehiculo->modelo }}@endif
                                    </p>
                                    @if($vehiculo->patente)
                                        <p class="text-xs text-gray-500 mt-1">Patente: {{ $vehiculo->patente }}</p>
                                    @endif
                                    <p class="text-xs text-gray-500">
                                        {{ $vehiculo->deposito->deposito ?? 'Sin depósito' }} •
                                        {{ $vehiculo->deposito->corralon->descripcion ?? 'Sin corralón' }}
                                    </p>
                                </div>
                                <div class="text-right ml-4">
                                    <div class="text-sm font-semibold
                                        @if($estaVencida) text-red-700
                                        @elseif($vencePronto) text-orange-700
                                        @else text-amber-700
                                        @endif">
                                        {{ $vencimiento->format('d/m/Y') }}
                                    </div>
                                    <p class="text-xs font-medium mt-1
                                        @if($estaVencida) text-red-600
                                        @elseif($vencePronto) text-orange-600
                                        @else text-amber-600
                                        @endif">
                                        @if($estaVencida)
                                            Vencida hace {{ abs($diasRestantes) }} día{{ abs($diasRestantes) != 1 ? 's' : '' }}
                                        @elseif($diasRestantes == 0)
                                            Vence hoy
                                        @elseif($diasRestantes == 1)
                                            Vence mañana
                                        @else
                                            Vence en {{ $diasRestantes }} días
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="font-medium">Todas las VTVs están al día</p>
                        <p class="text-xs text-gray-400 mt-1">No hay vehículos con VTV próxima a vencer (30 días)</p>
                    </div>
                @endif
            </div>
        </div>
        @endif

        @if(in_array('vehiculos_en_uso', $widgetsActivos))
        <!-- Vehículos en Uso -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-purple-50 to-purple-100 px-6 py-4 flex items-center justify-between border-b border-purple-200">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-800">Vehículos en Uso</h3>
                </div>
                <span class="bg-purple-200 text-purple-800 font-bold px-3 py-1 rounded-full text-sm">
                    {{ $countVehiculosEnUso }}
                </span>
            </div>
            <div class="p-6">
                @if($vehiculosEnUso->count() > 0)
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($vehiculosEnUso as $vehiculo)
                            <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg border border-purple-100 hover:border-purple-200 hover:bg-purple-100 transition-colors">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="bg-purple-600 text-white px-2 py-1 rounded text-xs font-bold">
                                            {{ $vehiculo->nro_movil }}
                                        </span>
                                        <p class="font-semibold text-gray-800">{{ $vehiculo->vehiculo }}</p>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">
                                        @if($vehiculo->marca){{ $vehiculo->marca }}@endif
                                        @if($vehiculo->marca && $vehiculo->modelo) - @endif
                                        @if($vehiculo->modelo){{ $vehiculo->modelo }}@endif
                                    </p>
                                    @if($vehiculo->patente)
                                        <p class="text-xs text-gray-500 mt-1">Patente: {{ $vehiculo->patente }}</p>
                                    @endif
                                    <p class="text-xs text-gray-500">
                                        {{ $vehiculo->deposito->deposito ?? 'Sin depósito' }} •
                                        {{ $vehiculo->deposito->corralon->descripcion ?? 'Sin corralón' }}
                                    </p>
                                </div>
                                <div class="text-right ml-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">
                                        En Uso
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="font-medium">No hay vehículos en uso actualmente</p>
                    </div>
                @endif
            </div>
        </div>
        @endif

        @if(in_array('proximos_eventos', $widgetsActivos))
        <!-- Próximos Eventos -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-indigo-50 to-indigo-100 px-6 py-4 flex items-center justify-between border-b border-indigo-200">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-800">Próximos Eventos</h3>
                </div>
                <span class="bg-indigo-200 text-indigo-800 font-bold px-3 py-1 rounded-full text-sm">
                    {{ $countProximosEventosWidget }}
                </span>
            </div>
            <div class="p-6">
                @if($proximosEventos->count() > 0)
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($proximosEventos as $evento)
                            <div class="flex items-start p-3 bg-indigo-50 rounded-lg border border-indigo-100 hover:border-indigo-200 hover:bg-indigo-100 transition-colors">
                                <div class="flex-shrink-0 mr-3">
                                    <div class="bg-indigo-600 text-white rounded-lg p-2 text-center min-w-[60px]">
                                        <p class="text-2xl font-bold">{{ $evento->fecha->format('d') }}</p>
                                        <p class="text-xs uppercase">{{ $evento->fecha->format('M') }}</p>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800">{{ $evento->evento }}</p>
                                    @if($evento->ubicacion)
                                        <p class="text-sm text-gray-600 mt-1">
                                            {{ $evento->ubicacion }}
                                        </p>
                                    @endif
                                    @if($evento->secretaria)
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $evento->secretaria }}
                                        </p>
                                    @endif
                                    <p class="text-xs text-indigo-600 font-medium mt-1">
                                        {{ $evento->fecha->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="font-medium">No hay eventos programados</p>
                    </div>
                @endif
            </div>
        </div>
        @endif

    </div>
    @else
    <div class="text-center py-16 text-gray-400">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
        </svg>
        <p class="font-medium text-gray-500">No hay widgets activos</p>
        <p class="text-sm mt-1">Usá "Personalizar panel" para activar estadísticas.</p>
    </div>
    @endif

    <!-- Modal Personalizar Panel -->
    @if($modalPersonalizar)
    <div
        class="fixed inset-0 z-50 flex items-center justify-center"
        x-data
        x-init="$el.querySelector('[data-modal]').focus()"
    >
        <div class="absolute inset-0 bg-black/40" wire:click="$set('modalPersonalizar', false)"></div>
        <div
            data-modal
            tabindex="-1"
            class="relative bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 outline-none"
            @keydown.escape.window="$wire.set('modalPersonalizar', false)"
        >
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800">Personalizar panel</h3>
                <button wire:click="$set('modalPersonalizar', false)" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            @if($opcionesCards)
            <div class="mb-5">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Tarjetas de resumen</p>
                <div class="space-y-2">
                    @foreach($opcionesCards as $key => $card)
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:border-[#77BF43]/40 hover:bg-[#77BF43]/5 cursor-pointer transition-colors">
                        <input
                            type="checkbox"
                            wire:model="seleccionCards"
                            value="{{ $key }}"
                            class="w-4 h-4 rounded text-[#77BF43] border-gray-300 focus:ring-[#77BF43]"
                        >
                        <span class="text-sm font-medium text-gray-700">{{ $card['label'] }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            @if($opcionesWidgets)
            <div class="mb-6">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Estadísticas y Alertas</p>
                <div class="space-y-2">
                    @foreach($opcionesWidgets as $key => $widget)
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:border-[#77BF43]/40 hover:bg-[#77BF43]/5 cursor-pointer transition-colors">
                        <input
                            type="checkbox"
                            wire:model="seleccionWidgets"
                            value="{{ $key }}"
                            class="w-4 h-4 rounded text-[#77BF43] border-gray-300 focus:ring-[#77BF43]"
                        >
                        <span class="text-sm font-medium text-gray-700">{{ $widget['label'] }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="flex justify-end gap-3">
                <button
                    wire:click="$set('modalPersonalizar', false)"
                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    Cancelar
                </button>
                <button
                    wire:click="guardarPreferencias"
                    class="px-4 py-2 text-sm font-medium text-white bg-[#77BF43] rounded-lg hover:bg-[#69ab3a] transition-colors"
                >
                    Guardar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
