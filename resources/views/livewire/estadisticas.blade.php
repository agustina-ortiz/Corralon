<div>
    <!-- Panel de filtros + acciones -->
    @php
        $filtrosActivos = collect([$filtro_corralon, $filtro_deposito, $fecha_desde, $fecha_hasta])->filter()->count();
    @endphp
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 overflow-hidden">
        <!-- Encabezado -->
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#77BF43]/10 text-[#77BF43]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                </span>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 leading-tight flex items-center gap-2">
                        Filtros
                        @if($filtrosActivos > 0)
                            <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 text-[11px] font-bold text-white bg-[#77BF43] rounded-full">{{ $filtrosActivos }}</span>
                        @endif
                    </h3>
                    <p class="text-xs text-gray-400">Acotá las estadísticas por ubicación y período</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span wire:loading class="flex items-center gap-1.5 text-xs text-[#77BF43] font-medium mr-1">
                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Actualizando…
                </span>
                @if($filtrosActivos > 0)
                    <button wire:click="limpiarFiltros" class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Limpiar
                    </button>
                @endif
                <button wire:click="abrirModalPersonalizar" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-[#77BF43] rounded-xl shadow-sm hover:bg-[#69ab3a] transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                    Personalizar
                </button>
            </div>
        </div>

        <!-- Cuerpo: campos de filtro -->
        <div class="px-6 py-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div>
                    <label class="flex items-center gap-1.5 text-sm font-medium text-gray-700 mb-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Corralón
                    </label>
                    <select wire:model.live="filtro_corralon" class="w-full px-4 py-2.5 text-sm text-gray-700 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#77BF43]/25 focus:border-[#77BF43] transition-all duration-200">
                        <option value="">Todos los corralones</option>
                        @foreach($corralones as $c)
                            <option value="{{ $c->id }}">{{ $c->descripcion }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="flex items-center gap-1.5 text-sm font-medium text-gray-700 mb-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Depósito
                    </label>
                    <select wire:model.live="filtro_deposito" class="w-full px-4 py-2.5 text-sm text-gray-700 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#77BF43]/25 focus:border-[#77BF43] transition-all duration-200">
                        <option value="">Todos los depósitos</option>
                        @foreach($depositos as $d)
                            <option value="{{ $d->id }}">{{ $d->deposito }} — {{ $d->corralon->descripcion ?? 's/c' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="flex items-center gap-1.5 text-sm font-medium text-gray-700 mb-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Desde
                    </label>
                    <input type="date" wire:model.live="fecha_desde" class="w-full px-4 py-2.5 text-sm text-gray-700 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#77BF43]/25 focus:border-[#77BF43] transition-all duration-200">
                </div>
                <div>
                    <label class="flex items-center gap-1.5 text-sm font-medium text-gray-700 mb-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Hasta
                    </label>
                    <input type="date" wire:model.live="fecha_hasta" class="w-full px-4 py-2.5 text-sm text-gray-700 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#77BF43]/25 focus:border-[#77BF43] transition-all duration-200">
                </div>
            </div>
        </div>
    </div>

    <!-- Grilla de gráficos -->
    @if($hayWidgets)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            @foreach($widgets as $key => $w)
                @switch($w['tipo'])
                    @case('donut')
                        @include('livewire.partials.chart-donut', ['titulo' => $w['titulo'], 'data' => $w['data'], 'decimales' => $w['decimales']])
                        @break

                    @case('barras')
                        @include('livewire.partials.chart-barras', ['titulo' => $w['titulo'], 'data' => $w['data'], 'decimales' => $w['decimales']])
                        @break

                    @case('series')
                        @include('livewire.partials.chart-series', ['titulo' => $w['titulo'], 'data' => $w['data']])
                        @break

                    @case('lista')
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 flex flex-col md:col-span-2">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-semibold text-gray-700">{{ $w['titulo'] }}</h3>
                                <span class="bg-amber-100 text-amber-800 font-bold px-2.5 py-0.5 rounded-full text-xs">{{ $w['data']->count() }}</span>
                            </div>
                            @if($w['data']->count() > 0)
                                <div class="space-y-2 max-h-72 overflow-y-auto">
                                    @foreach($w['data'] as $chofer)
                                        @php
                                            $venc = $chofer->vencimiento_licencia;
                                            $dias = $venc ? (int) \Carbon\Carbon::today()->diffInDays($venc, false) : null;
                                        @endphp
                                        <div class="flex items-center justify-between p-2.5 rounded-lg border
                                            @if($dias !== null && $dias < 0) bg-red-50 border-red-200
                                            @elseif($dias !== null && $dias <= 15) bg-orange-50 border-orange-200
                                            @else bg-amber-50 border-amber-100 @endif">
                                            <div class="min-w-0">
                                                <p class="font-medium text-gray-800 text-sm truncate">{{ $chofer->nombre }}</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $chofer->tipo_licencia ? 'Lic. ' . $chofer->tipo_licencia : 'Licencia' }}
                                                    @if($chofer->secretaria) • {{ $chofer->secretaria->secretaria }} @endif
                                                </p>
                                            </div>
                                            <div class="text-right ml-3 flex-shrink-0">
                                                <p class="text-sm font-semibold
                                                    @if($dias !== null && $dias < 0) text-red-700
                                                    @elseif($dias !== null && $dias <= 15) text-orange-700
                                                    @else text-amber-700 @endif">
                                                    {{ $venc?->format('d/m/Y') }}
                                                </p>
                                                <p class="text-[11px] font-medium
                                                    @if($dias !== null && $dias < 0) text-red-600 @else text-gray-500 @endif">
                                                    @if($dias === null) —
                                                    @elseif($dias < 0) Vencida hace {{ abs($dias) }}d
                                                    @elseif($dias === 0) Vence hoy
                                                    @else Vence en {{ $dias }}d @endif
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="flex-1 flex items-center justify-center text-sm text-gray-400 py-10">No hay licencias por vencer</div>
                            @endif
                        </div>
                        @break
                @endswitch
            @endforeach
        </div>
    @elseif($sinOpciones)
        <div class="text-center py-16 text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <p class="font-medium text-gray-500">No tenés módulos con estadísticas disponibles</p>
        </div>
    @else
        <div class="text-center py-16 text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <p class="font-medium text-gray-500">No hay gráficos activos</p>
            <p class="text-sm mt-1">Usá "Personalizar" para elegir qué estadísticas ver.</p>
        </div>
    @endif

    <!-- Modal Personalizar -->
    @if($modalPersonalizar)
    <div class="fixed inset-0 z-50 flex items-center justify-center" x-data>
        <div class="absolute inset-0 bg-black/40" wire:click="$set('modalPersonalizar', false)"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 p-6 max-h-[85vh] overflow-y-auto"
             @keydown.escape.window="$wire.set('modalPersonalizar', false)">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-gray-800">Personalizar estadísticas</h3>
                <button wire:click="$set('modalPersonalizar', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            @foreach($grupos as $grupoKey => $grupoLabel)
                @if(!empty($opcionesPorGrupo[$grupoKey]))
                <div class="mb-5">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">{{ $grupoLabel }}</p>
                    <div class="space-y-1.5">
                        @foreach($opcionesPorGrupo[$grupoKey] as $key => $cfg)
                        <label class="flex items-center gap-3 p-2.5 rounded-lg border border-gray-100 hover:border-[#77BF43]/40 hover:bg-[#77BF43]/5 cursor-pointer transition-colors">
                            <input type="checkbox" wire:model="seleccionWidgets" value="{{ $key }}"
                                   class="w-4 h-4 rounded text-[#77BF43] border-gray-300 focus:ring-[#77BF43]">
                            <span class="text-sm font-medium text-gray-700">{{ $cfg['label'] }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach

            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                <button wire:click="$set('modalPersonalizar', false)" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancelar
                </button>
                <button wire:click="guardarPreferencias" class="px-4 py-2 text-sm font-medium text-white bg-[#77BF43] rounded-lg hover:bg-[#69ab3a]">
                    Guardar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
