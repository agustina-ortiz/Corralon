{{-- Un valor del detalle de Actividad: ['tipo' => vacio|texto, 'texto' => …]. Espera $valor. --}}
@if(($valor['tipo'] ?? 'vacio') === 'vacio')
    <span class="italic text-gray-400">{{ $valor['texto'] ?? __('actividad.vacio') }}</span>
@else
    <span class="whitespace-pre-line break-words">{{ $valor['texto'] }}</span>
@endif
