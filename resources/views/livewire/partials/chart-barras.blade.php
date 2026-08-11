@php
    /**
     * Gráfico de barras horizontales (CSS).
     * Props: $titulo (string), $data (array de ['label'=>, 'value'=>]), $decimales (int)
     *        $max (float|null)       — escala fija de las barras (opcional)
     *        $paginacion (array|null) — ['pagina','total_paginas','total','desde','hasta','metodo']
     */
    $decimales = $decimales ?? 0;
    $maxFijo = $maxFijo ?? ($max ?? null);
    $paginacion = $paginacion ?? null;
    $paleta = ['#77BF43', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6', '#14B8A6', '#EC4899', '#6366F1', '#84CC16', '#F97316', '#06B6D4', '#A855F7'];

    // En modo paginado se muestran todos los ítems (incluidos los de valor 0)
    $items = collect($data ?? []);
    if (!$paginacion) {
        $items = $items->filter(fn($d) => ($d['value'] ?? 0) > 0);
    }
    $items = $items->values();
    $max = $maxFijo ?: ($items->max('value') ?: 1);
    $offsetColor = $paginacion ? (($paginacion['desde'] ?: 1) - 1) : 0;
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 flex flex-col">
    <div class="flex items-center justify-between mb-4 gap-2">
        <h3 class="text-sm font-semibold text-gray-700">{{ $titulo }}</h3>
        @if($paginacion)
            <span class="text-xs text-gray-400 whitespace-nowrap">{{ number_format($paginacion['total'], 0, ',', '.') }} en total</span>
        @endif
    </div>

    @if($items->count() > 0)
        <div class="space-y-2.5">
            @foreach($items as $i => $it)
                @php $color = $paleta[($i + $offsetColor) % count($paleta)]; @endphp
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="text-gray-600 truncate pr-2" title="{{ $it['label'] }}">{{ $it['label'] }}</span>
                        <span class="text-gray-800 font-semibold whitespace-nowrap">{{ number_format($it['value'], $decimales, ',', '.') }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                        <div class="h-2.5 rounded-full transition-all" style="width: {{ ($it['value'] ?? 0) > 0 ? max(3, $it['value'] / $max * 100) : 0 }}%; background: {{ $color }}"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex-1 flex items-center justify-center text-sm text-gray-400 py-10">Sin datos para mostrar</div>
    @endif

    @if($paginacion && $paginacion['total'] > 0)
        <div class="flex items-center justify-between gap-2 mt-4 pt-3 border-t border-gray-100">
            <span class="text-xs text-gray-500">
                {{ $paginacion['desde'] }}–{{ $paginacion['hasta'] }} de {{ number_format($paginacion['total'], 0, ',', '.') }}
            </span>
            <div class="flex items-center gap-1.5">
                <button type="button"
                        wire:click="{{ $paginacion['metodo'] }}({{ $paginacion['pagina'] - 1 }})"
                        @disabled($paginacion['pagina'] <= 1)
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        title="Página anterior">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <span class="text-xs font-medium text-gray-600 px-1 whitespace-nowrap">
                    {{ $paginacion['pagina'] }} / {{ $paginacion['total_paginas'] }}
                </span>
                <button type="button"
                        wire:click="{{ $paginacion['metodo'] }}({{ $paginacion['pagina'] + 1 }})"
                        @disabled($paginacion['pagina'] >= $paginacion['total_paginas'])
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        title="Página siguiente">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif
</div>
