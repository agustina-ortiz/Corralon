{{--
    <x-ui.boton-filtros> — botón que abre/cierra el panel de filtros, con la
    burbuja de cuántos filtros hay aplicados (kit, spec 17).

      <x-ui.boton-filtros :activos="$filtrosActivos" wire:click="$toggle('showFilters')" />
      <x-ui.boton-filtros :activos="$filtrosActivos" solo-icono wire:click="$toggle('showFilters')" />

    Props:
      activos    Cantidad de filtros aplicados (0 = sin burbuja).
      soloIcono  Solo el embudo, con la burbuja flotando en la esquina
                 (Transferencias, para darle ancho al buscador).
--}}
@props([
    'activos' => 0,
    'soloIcono' => false,
])

@php
    use App\Support\Tema;

    $activos = (int) $activos;
    $medidas = $soloIcono ? 'relative px-3 py-2.5' : 'gap-2 px-5 py-2.5';
@endphp

<button type="button"
        title="Filtros"
        @if($soloIcono) aria-label="Filtros{{ $activos ? " ($activos aplicados)" : '' }}" @endif
        {{ $attributes->class(['inline-flex items-center justify-center font-medium whitespace-nowrap rounded-xl text-base focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200', $medidas, Tema::botonSecundario()]) }}
        data-boton-filtros>
    <i class="fa-solid fa-filter" aria-hidden="true"></i>
    @unless($soloIcono)
        <span>Filtros</span>
    @endunless
    @if($activos > 0)
        <span @class([
            'inline-flex items-center justify-center w-5 h-5 text-xs font-semibold rounded-full',
            'absolute -top-1.5 -right-1.5' => $soloIcono,
            Tema::contadorFiltros(),
        ]) data-filtros-activos>{{ $activos }}</span>
    @endif
</button>
