@php
    /**
     * Gráfico de torta/dona en SVG puro.
     * Props: $titulo (string), $data (array de ['label'=>, 'value'=>]), $decimales (int)
     */
    $decimales = $decimales ?? 0;
    $paleta = ['#77BF43', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6', '#14B8A6', '#EC4899', '#6366F1', '#84CC16', '#F97316', '#06B6D4', '#A855F7'];

    $segmentos = collect($data ?? [])->filter(fn($d) => ($d['value'] ?? 0) > 0)->values();
    $total = $segmentos->sum('value');

    $r = 70;
    $circ = 2 * pi() * $r;
    $acc = 0;
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 flex flex-col">
    <h3 class="text-sm font-semibold text-gray-700 mb-4">{{ $titulo }}</h3>

    @if($total > 0)
        <div class="flex items-center gap-5 flex-wrap">
            <div class="relative flex-shrink-0">
                <svg viewBox="0 0 200 200" class="w-40 h-40">
                    <g transform="rotate(-90 100 100)">
                        <circle cx="100" cy="100" r="{{ $r }}" fill="none" stroke="#f1f5f9" stroke-width="30" />
                        @foreach($segmentos as $i => $seg)
                            @php
                                $len = ($seg['value'] / $total) * $circ;
                                $color = $paleta[$i % count($paleta)];
                            @endphp
                            <circle cx="100" cy="100" r="{{ $r }}" fill="none"
                                    stroke="{{ $color }}" stroke-width="30"
                                    stroke-dasharray="{{ $len }} {{ $circ - $len }}"
                                    stroke-dashoffset="{{ -$acc }}">
                                <title>{{ $seg['label'] }}: {{ number_format($seg['value'], $decimales, ',', '.') }} ({{ round($seg['value'] / $total * 100) }}%)</title>
                            </circle>
                            @php $acc += $len; @endphp
                        @endforeach
                    </g>
                    <circle cx="100" cy="100" r="45" fill="white" />
                    <text x="100" y="96" text-anchor="middle" class="fill-gray-800" style="font-size:26px;font-weight:700;">{{ number_format($total, 0, ',', '.') }}</text>
                    <text x="100" y="116" text-anchor="middle" class="fill-gray-400" style="font-size:12px;">total</text>
                </svg>
            </div>

            <div class="flex-1 min-w-[140px] space-y-1.5">
                @foreach($segmentos as $i => $seg)
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-3 h-3 rounded-sm flex-shrink-0" style="background: {{ $paleta[$i % count($paleta)] }}"></span>
                            <span class="text-gray-600 truncate" title="{{ $seg['label'] }}">{{ $seg['label'] }}</span>
                        </div>
                        <span class="text-gray-800 font-medium whitespace-nowrap ml-2">
                            {{ number_format($seg['value'], $decimales, ',', '.') }}
                            <span class="text-gray-400">({{ round($seg['value'] / $total * 100) }}%)</span>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="flex-1 flex items-center justify-center text-sm text-gray-400 py-10">Sin datos para mostrar</div>
    @endif
</div>
