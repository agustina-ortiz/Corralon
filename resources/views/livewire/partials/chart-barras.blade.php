@php
    /**
     * Gráfico de barras horizontales (CSS).
     * Props: $titulo (string), $data (array de ['label'=>, 'value'=>]), $decimales (int)
     */
    $decimales = $decimales ?? 0;
    $paleta = ['#77BF43', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6', '#14B8A6', '#EC4899', '#6366F1', '#84CC16', '#F97316', '#06B6D4', '#A855F7'];

    $items = collect($data ?? [])->filter(fn($d) => ($d['value'] ?? 0) > 0)->values();
    $max = $items->max('value') ?: 1;
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 flex flex-col">
    <h3 class="text-sm font-semibold text-gray-700 mb-4">{{ $titulo }}</h3>

    @if($items->count() > 0)
        <div class="space-y-2.5">
            @foreach($items as $i => $it)
                @php $color = $paleta[$i % count($paleta)]; @endphp
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="text-gray-600 truncate pr-2" title="{{ $it['label'] }}">{{ $it['label'] }}</span>
                        <span class="text-gray-800 font-semibold whitespace-nowrap">{{ number_format($it['value'], $decimales, ',', '.') }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                        <div class="h-2.5 rounded-full transition-all" style="width: {{ max(3, $it['value'] / $max * 100) }}%; background: {{ $color }}"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex-1 flex items-center justify-center text-sm text-gray-400 py-10">Sin datos para mostrar</div>
    @endif
</div>
