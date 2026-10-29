{{--
    Widget de vencimientos de vehículos (VTV / póliza / oblea).
    Recibe $v = ['titulo', 'documento', 'campo', 'alerta', 'sin_fecha', 'vehiculos', 'vencidos', 'cantSinFecha']
--}}
@php
    $hoy = \Carbon\Carbon::today();
    $porVencer = $v['vehiculos']->count() - $v['vencidos'];
@endphp
<div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
    <div class="bg-gradient-to-r from-amber-50 to-amber-100 px-6 py-4 flex items-center justify-between border-b border-amber-200">
        <div class="flex items-center min-w-0">
            <i class="fa-solid fa-shield-halved text-xl text-amber-600 mr-3 flex-shrink-0" aria-hidden="true"></i>
            <div class="min-w-0">
                <h3 class="text-lg font-semibold text-gray-800">{{ $v['titulo'] }}</h3>
                @if($v['vehiculos']->count() > 0)
                    <p class="text-xs mt-0.5">
                        <span class="font-semibold text-red-600">{{ $v['vencidos'] }} vencida{{ $v['vencidos'] != 1 ? 's' : '' }}</span>
                        <span class="text-gray-400">·</span>
                        <span class="font-semibold text-amber-700">{{ $porVencer }} por vencer</span>
                    </p>
                @endif
            </div>
        </div>
        <a href="{{ route('vehiculos', ['alerta' => $v['alerta']]) }}" wire:navigate
           title="Ver en Vehículos"
           class="bg-amber-200 text-amber-800 font-bold px-3 py-1 rounded-full text-sm hover:bg-amber-300 transition-colors">
            {{ $v['vehiculos']->count() }}
        </a>
    </div>
    <div class="p-6">
        @if($v['vehiculos']->count() > 0)
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @foreach($v['vehiculos'] as $vehiculo)
                    @php
                        $vencimiento = $vehiculo->{$v['campo']};
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
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2">
                                @if(trim((string) $vehiculo->nro_patrimonio) !== '')
                                    <span class="
                                        @if($estaVencida) bg-red-600
                                        @elseif($vencePronto) bg-orange-600
                                        @else bg-amber-600
                                        @endif
                                        text-white px-2 py-1 rounded text-xs font-bold">
                                        {{ $vehiculo->nro_patrimonio }}
                                    </span>
                                @endif
                                <p class="font-semibold text-gray-800 truncate">{{ $vehiculo->vehiculo }}</p>
                            </div>
                            @if($vehiculo->marca_modelo)
                                <p class="text-sm text-gray-600 mt-1">{{ $vehiculo->marca_modelo }}</p>
                            @endif
                            @if($vehiculo->patente)
                                <p class="text-xs text-gray-500 mt-1">Patente: {{ $vehiculo->patente }}</p>
                            @endif
                            <p class="text-xs text-gray-500">{{ $vehiculo->secretaria->secretaria ?? 'Sin secretaría' }}</p>
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
            <x-ui.vacio icono="fa-regular fa-circle-check">No hay vencimientos de {{ $v['documento'] }} en los próximos {{ \App\Models\Vehiculo::DIAS_ALERTA_VENCIMIENTO }} días</x-ui.vacio>
        @endif

        {{-- Aviso de fechas sin cargar: sin él, "no hay vencimientos" puede ser engañoso --}}
        @if($v['cantSinFecha'] > 0)
            <a href="{{ route('vehiculos', ['alerta' => $v['sin_fecha']]) }}" wire:navigate
               class="mt-4 flex items-center justify-between gap-2 px-3 py-2 rounded-lg bg-gray-50 border border-gray-200 text-xs text-gray-600 hover:bg-gray-100 transition-colors">
                <span>
                    <span class="font-semibold text-gray-800">{{ $v['cantSinFecha'] }}</span>
                    vehículo{{ $v['cantSinFecha'] != 1 ? 's' : '' }} sin fecha de vencimiento de {{ $v['documento'] }} cargada
                </span>
                <span class="text-[#77BF43] font-medium whitespace-nowrap">Ver →</span>
            </a>
        @endif
    </div>
</div>
