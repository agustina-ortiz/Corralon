@php
    /**
     * Barras verticales agrupadas: Entradas vs. Salidas por período.
     * Props: $titulo (string), $data (array de ['label'=>, 'entradas'=>, 'salidas'=>])
     */
    $items = collect($data ?? []);
    $max = max(1, $items->max(fn($d) => max($d['entradas'] ?? 0, $d['salidas'] ?? 0)));
    $hayDatos = $items->sum(fn($d) => ($d['entradas'] ?? 0) + ($d['salidas'] ?? 0)) > 0;
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 flex flex-col md:col-span-2">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-700">{{ $titulo }}</h3>
        <div class="flex items-center gap-4 text-xs">
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm" style="background:#77BF43"></span> Entradas</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm" style="background:#EF4444"></span> Salidas</span>
        </div>
    </div>

    @if($hayDatos)
        <div class="flex items-end justify-between gap-2 h-48 border-b border-gray-200 pt-2">
            @foreach($items as $it)
                <div class="flex-1 flex flex-col items-center justify-end h-full">
                    <div class="flex items-end justify-center gap-1 w-full h-full">
                        <div class="w-1/2 max-w-[22px] rounded-t transition-all"
                             style="height: {{ ($it['entradas'] ?? 0) / $max * 100 }}%; background:#77BF43; min-height: {{ ($it['entradas'] ?? 0) > 0 ? '3px' : '0' }}"
                             title="Entradas: {{ number_format($it['entradas'] ?? 0, 0, ',', '.') }}"></div>
                        <div class="w-1/2 max-w-[22px] rounded-t transition-all"
                             style="height: {{ ($it['salidas'] ?? 0) / $max * 100 }}%; background:#EF4444; min-height: {{ ($it['salidas'] ?? 0) > 0 ? '3px' : '0' }}"
                             title="Salidas: {{ number_format($it['salidas'] ?? 0, 0, ',', '.') }}"></div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="flex justify-between gap-2 mt-1">
            @foreach($items as $it)
                <div class="flex-1 text-center text-[10px] text-gray-500 uppercase">{{ $it['label'] }}</div>
            @endforeach
        </div>
    @else
        <div class="flex-1 flex items-center justify-center text-sm text-gray-400 py-10">Sin movimientos en el período</div>
    @endif
</div>
