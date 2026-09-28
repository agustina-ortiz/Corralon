{{--
    Widget de contadores de alertas de vehículos (una fila por alerta, link a /vehiculos filtrado).
    Recibe: $titulo, $subtitulo (opcional), $alertas (colección de Vehiculo::ALERTAS con 'clave' y 'cantidad'),
            $total (opcional, para la barra de proporción), $mensajeVacio, $icono (path SVG)
--}}
<div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
    <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4 flex items-center justify-between border-b border-slate-200">
        <div class="flex items-center min-w-0">
            <svg class="w-6 h-6 text-slate-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icono }}"></path>
            </svg>
            <div class="min-w-0">
                <h3 class="text-lg font-semibold text-gray-800">{{ $titulo }}</h3>
                @if(!empty($subtitulo))
                    <p class="text-xs text-gray-500 mt-0.5">{{ $subtitulo }}</p>
                @endif
            </div>
        </div>
        <span class="bg-slate-200 text-slate-800 font-bold px-3 py-1 rounded-full text-sm" title="Alertas con vehículos">
            {{ $alertas->count() }}
        </span>
    </div>
    <div class="p-6">
        @if($alertas->count() > 0)
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @foreach($alertas as $alerta)
                    <a href="{{ route('vehiculos', ['alerta' => $alerta['clave']]) }}" wire:navigate
                       class="block p-3 rounded-lg border border-slate-100 bg-slate-50 hover:bg-slate-100 hover:border-slate-200 transition-colors">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm font-medium text-gray-700">{{ $alerta['label'] }}</span>
                            <span class="flex items-center gap-2 flex-shrink-0">
                                <span class="text-lg font-bold text-slate-800">{{ number_format($alerta['cantidad']) }}</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </span>
                        </div>
                        @if(!empty($total))
                            <div class="mt-2 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-500 rounded-full" style="width: {{ min(100, round($alerta['cantidad'] * 100 / $total)) }}%"></div>
                            </div>
                        @endif
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="font-medium">{{ $mensajeVacio }}</p>
            </div>
        @endif
    </div>
</div>
