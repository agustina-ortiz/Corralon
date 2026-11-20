{{-- resources/views/livewire/actividad.blade.php — Actividad (kit, spec 38), solo lectura --}}
@php
    use Illuminate\Support\Str;

    $colorAccion = ['created' => 'verde', 'updated' => 'azul', 'deleted' => 'rojo', 'sistema' => 'gris'];
    $dia = function ($fecha) {
        if ($fecha->isToday()) {
            return __('actividad.hoy');
        }
        if ($fecha->isYesterday()) {
            return __('actividad.ayer');
        }

        return Str::ucfirst($fecha->locale('es')->translatedFormat($fecha->isCurrentYear() ? 'l j \d\e F' : 'l j \d\e F \d\e Y'));
    };
    $columnas = 'md:grid-cols-[110px_minmax(0,1fr)_220px_70px]';
@endphp

<x-ui.pagina :titulo="__('actividad.titulo')" icono="fa-clock-rotate-left">
    {{-- Búsqueda y filtros --}}
    <div class="mb-6 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
        <x-ui.buscador wire:model.live.debounce.400ms="buscar" :placeholder="__('actividad.buscar')" />
        <div class="flex items-center gap-3">
            <p class="text-sm text-gray-500" aria-live="polite" data-total-actividad>
                {{ trans_choice('actividad.registros', $actividades->total(), ['cantidad' => number_format($actividades->total(), 0, ',', '.')]) }}
            </p>
            <x-ui.boton-filtros :activos="$filtrosActivos" wire:click="$toggle('mostrarFiltros')" />
        </div>
    </div>

    <x-ui.panel-filtros mostrar="mostrarFiltros" :activos="$filtrosActivos" limpiar="limpiarFiltros" columnas="5">
        <x-ui.campo :etiqueta="__('actividad.filtros.accion.etiqueta')" nombre="filtroEvento">
            <x-ui.combobox nombre="filtroEvento" wire:model.live="filtroEvento" :opciones="$acciones" :ordenar="false"
                           :vacio="__('actividad.filtros.accion.todos')" :placeholder="__('actividad.filtros.accion.todos')" />
        </x-ui.campo>
        <x-ui.campo :etiqueta="__('actividad.filtros.tipo.etiqueta')" nombre="filtroTipo">
            <x-ui.combobox nombre="filtroTipo" wire:model.live="filtroTipo" :opciones="$grupos" :ordenar="false"
                           :vacio="__('actividad.filtros.tipo.todos')" :placeholder="__('actividad.filtros.tipo.todos')" />
        </x-ui.campo>
        <x-ui.campo :etiqueta="__('actividad.filtros.usuario.etiqueta')" nombre="filtroUsuario">
            <x-ui.combobox nombre="filtroUsuario" wire:model.live="filtroUsuario" :opciones="$quienes" :ordenar="false"
                           :vacio="__('actividad.filtros.usuario.todos')" :placeholder="__('actividad.filtros.usuario.todos')" />
        </x-ui.campo>
        <x-ui.campo :etiqueta="__('actividad.filtros.desde')" nombre="desde">
            <x-ui.input type="date" nombre="desde" wire:model.live="desde" />
        </x-ui.campo>
        <x-ui.campo :etiqueta="__('actividad.filtros.hasta')" nombre="hasta">
            <x-ui.input type="date" nombre="hasta" wire:model.live="hasta" />
        </x-ui.campo>
    </x-ui.panel-filtros>

    {{-- Listado: una grilla que en escritorio son columnas y en mobile, una card por fila. --}}
    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white text-sm shadow-sm">
        <div class="hidden items-center border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 md:grid {{ $columnas }}">
            <div class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('actividad.columnas.accion') }}</div>
            <div class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('actividad.columnas.registro') }}</div>
            <div class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('actividad.columnas.usuario') }}</div>
            <div class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('actividad.columnas.hora') }}</div>
        </div>

        @php $diaAnterior = null; @endphp
        @forelse($filas as $f)
            @php
                $diaFila = $f['fecha']->toDateString();
                $tieneDetalle = (bool) $f['cambios'];
            @endphp

            {{-- Separador por día --}}
            @if($diaFila !== $diaAnterior)
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-2 text-xs font-semibold text-gray-500 md:px-5 {{ $diaAnterior ? 'border-t' : '' }}">
                    {{ $dia($f['fecha']) }}
                </div>
                @php $diaAnterior = $diaFila; @endphp
            @endif

            <div x-data="{ abierto: false }" wire:key="act-{{ $f['id'] }}" class="border-b border-gray-100 last:border-b-0" data-actividad="{{ $f['evento'] }}">
                <div class="grid grid-cols-[1fr_auto] items-start gap-x-2 gap-y-1.5 px-4 py-3 md:gap-0 md:px-0 md:py-0 {{ $columnas }}">
                    {{-- Acción --}}
                    <div class="col-start-1 row-start-1 md:col-start-auto md:row-start-auto md:px-5 md:py-3">
                        <x-ui.badge :color="$colorAccion[$f['evento']] ?? 'gris'">{{ __('actividad.acciones.'.$f['evento']) }}</x-ui.badge>
                    </div>

                    {{-- Registro --}}
                    <div class="col-span-2 col-start-1 row-start-2 min-w-0 md:col-span-1 md:col-start-auto md:row-start-auto md:px-4 md:py-3">
                        <p class="break-words font-semibold text-gray-900">
                            {{ $f['nombre'] }}
                            @if($f['borrado'])
                                <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 align-middle text-xs font-medium text-gray-500">{{ __('actividad.ya_no_existe') }}</span>
                            @endif
                        </p>
                        <p class="mt-0.5 break-words text-xs text-gray-500">
                            {{ $f['tipo'] }}@if($f['contexto'])<span class="text-gray-400"> · {{ __('actividad.en') }} </span>{{ $f['contexto'] }}@endif
                        </p>
                        @if($f['resumen'])
                            <p class="mt-1 text-xs text-gray-600">{{ $f['resumen'] }}</p>
                        @endif

                        @if($tieneDetalle)
                            <button type="button" x-on:click="abierto = ! abierto" x-bind:aria-expanded="abierto"
                                    aria-controls="act-detalle-{{ $f['id'] }}"
                                    class="mt-1.5 inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800">
                                <span x-text="abierto ? @js(__('actividad.ocultar_detalle')) : @js(__('actividad.ver_detalle'))">{{ __('actividad.ver_detalle') }}</span>
                                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" x-bind:class="{ 'rotate-180': abierto }" aria-hidden="true"></i>
                            </button>
                        @endif
                    </div>

                    {{-- Quién --}}
                    <div class="col-span-2 col-start-1 row-start-3 md:col-span-1 md:col-start-auto md:row-start-auto md:px-4 md:py-3">
                        <span class="text-xs text-gray-400 md:hidden">{{ __('actividad.por') }} </span>
                        @if($f['usuario'])
                            <span class="text-gray-700" data-quien>{{ $f['usuario'] }}</span>
                        @else
                            <span class="italic text-gray-400" data-quien>{{ __('actividad.sistema') }}</span>
                        @endif
                    </div>

                    {{-- Hora --}}
                    <div class="col-start-2 row-start-1 whitespace-nowrap text-right text-xs text-gray-500 md:col-start-auto md:row-start-auto md:px-4 md:py-3 md:text-left"
                         title="{{ $f['fecha']->format('d/m/Y H:i') }}">
                        {{ $f['fecha']->format('H:i') }}
                    </div>
                </div>

                {{-- Detalle: antes → después (edición), datos cargados (alta) o los que tenía (baja) --}}
                @if($tieneDetalle)
                    <div id="act-detalle-{{ $f['id'] }}" x-show="abierto" x-cloak class="px-4 pb-4 md:pl-[126px] md:pr-5">
                        <div class="overflow-hidden rounded-lg border border-gray-200 bg-gray-50/60">
                            @if($f['evento'] === 'updated')
                                <div class="hidden grid-cols-[180px_1fr_1fr] border-b border-gray-200 text-xs font-semibold uppercase tracking-wide text-gray-500 md:grid">
                                    <div class="px-3 py-2">{{ __('actividad.detalle.campo') }}</div>
                                    <div class="px-3 py-2">{{ __('actividad.detalle.antes') }}</div>
                                    <div class="px-3 py-2">{{ __('actividad.detalle.despues') }}</div>
                                </div>
                                <div class="divide-y divide-gray-200">
                                    @foreach($f['cambios'] as $c)
                                        <div class="grid grid-cols-1 gap-1 px-3 py-2 md:grid-cols-[180px_1fr_1fr] md:gap-0 md:px-0 md:py-0">
                                            <div class="text-xs font-semibold text-gray-600 md:px-3 md:py-2">{{ $c['campo'] }}</div>
                                            <div class="min-w-0 text-gray-500 md:px-3 md:py-2">
                                                <span class="text-xs font-semibold uppercase text-gray-400 md:hidden">{{ __('actividad.detalle.antes') }}: </span>
                                                @include('livewire.partials.valor-actividad', ['valor' => $c['antes']])
                                            </div>
                                            <div class="min-w-0 text-gray-900 md:px-3 md:py-2">
                                                <span class="text-xs font-semibold uppercase text-gray-400 md:hidden">{{ __('actividad.detalle.despues') }}: </span>
                                                @include('livewire.partials.valor-actividad', ['valor' => $c['despues']])
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="border-b border-gray-200 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {{ __('actividad.detalle.'.$f['evento']) }}
                                </p>
                                <div class="divide-y divide-gray-200">
                                    @foreach($f['cambios'] as $c)
                                        <div class="grid grid-cols-1 gap-1 px-3 py-2 md:grid-cols-[180px_1fr] md:gap-0 md:px-0 md:py-0">
                                            <div class="text-xs font-semibold text-gray-600 md:px-3 md:py-2">{{ $c['campo'] }}</div>
                                            <div class="min-w-0 text-gray-900 md:px-3 md:py-2">
                                                @include('livewire.partials.valor-actividad', ['valor' => $c['despues'] ?? $c['antes']])
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <x-ui.vacio icono="fa-clock-rotate-left">
                {{ $hayFiltros ? __('actividad.vacios.sin_resultados') : __('actividad.vacios.sin_actividad') }}
            </x-ui.vacio>
        @endforelse

        @if($actividades->total() > 0)
            <div class="border-t border-gray-100">
                <x-ui.paginacion :paginador="$actividades" />
            </div>
        @endif
    </div>
</x-ui.pagina>
