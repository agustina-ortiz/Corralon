{{--
    Widget de contadores de alertas de vehículos (una fila por alerta, link a /vehiculos filtrado).
    Recibe: $titulo, $subtitulo (opcional), $alertas (colección de Vehiculo::ALERTAS con 'clave' y 'cantidad'),
            $total (opcional, para la barra de proporción), $mensajeVacio, $icono (Font Awesome, "fa-bullhorn")
--}}
<div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
    <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4 flex items-center justify-between border-b border-slate-200">
        <div class="flex items-center min-w-0">
            <i class="fa-solid {{ $icono }} text-xl text-slate-600 mr-3 flex-shrink-0" aria-hidden="true"></i>
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
                                <i class="fa-solid fa-chevron-right text-xs text-gray-400" aria-hidden="true"></i>
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
            <x-ui.vacio icono="fa-regular fa-circle-check">{{ $mensajeVacio }}</x-ui.vacio>
        @endif
    </div>
</div>
