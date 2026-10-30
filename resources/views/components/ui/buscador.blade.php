{{--
    <x-ui.buscador> — buscador de texto de un listado (kit, spec 17): lupa a
    la izquierda y una X para limpiarlo cuando tiene algo escrito. Mismo
    aspecto que los inputs del Corralón (rounded-xl, borde gris).

    Filtra el listado paginado con `like` en el componente (decisión del
    usuario: no BuscaEntidad), así que el mismo texto llega a Estadísticas y
    a la exportación por baseQuery().

      <x-ui.buscador wire:model.live="search" placeholder="Buscar depósitos..." />

    El wire:model va al <input>; `class` va al contenedor (por omisión ocupa
    el espacio libre hasta max-w-md). Sin `etiqueta`, el placeholder hace de
    aria-label.
--}}
@props([
    'placeholder' => 'Buscar...',
    'etiqueta' => null,
])

@php
    use App\Support\Tema;

    $modelo = $attributes->wire('model')->value();
@endphp

<div {{ $attributes->only('class')->class(['relative flex-1 max-w-md']) }}
     x-data
     data-buscador>
    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" aria-hidden="true"></i>
    <input
        type="search"
        x-ref="campo"
        placeholder="{{ $placeholder }}"
        aria-label="{{ $etiqueta ?? $placeholder }}"
        autocomplete="off"
        {{ $attributes->except('class')->class(['w-full pl-10 pr-10 py-2.5 bg-white border border-gray-200 rounded-xl text-gray-800 transition-all duration-200 focus:outline-none [&::-webkit-search-cancel-button]:hidden', Tema::anilloFoco()]) }}
    >
    @if($modelo)
        <button type="button"
                x-show="($wire.get('{{ $modelo }}') ?? '') !== ''"
                x-cloak
                x-on:click="$wire.set('{{ $modelo }}', ''); $refs.campo.value = ''; $refs.campo.focus()"
                class="absolute right-2 top-1/2 -translate-y-1/2 inline-flex items-center justify-center w-7 h-7 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500/40"
                title="Limpiar búsqueda"
                aria-label="Limpiar búsqueda">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    @endif
</div>
