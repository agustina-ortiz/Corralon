{{--
    <x-ui.pagina> — encabezado y contenedor de una pantalla (kit, spec 15).

    Los componentes Livewire de página completa no llenan el slot `header` del
    layout, así que el título va acá. Adaptado al Corralón: el layout ya pone
    el relleno (p-4 sm:p-6), así que el encabezado es una fila de título +
    acciones, no una barra a sangre.

    Props:
      titulo  Título de la pantalla.
      icono   Font Awesome ("fa-warehouse"); se antepone fa-solid.
    Slots: default (contenido) y `acciones` (botones a la derecha del título).
--}}
@props([
    'titulo' => '',
    'icono' => null,
])

@php
    use App\Support\Tema;
    use Illuminate\Support\Str;

    if ($icono !== null && ! Str::startsWith($icono, ['fa-solid', 'fa-regular', 'fa-brands'])) {
        $icono = 'fa-solid '.$icono;
    }
@endphp

<div {{ $attributes }}>
    <header class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="flex items-center gap-3 text-xl font-semibold text-gray-800 leading-tight">
            @if($icono)
                <i class="{{ $icono }} {{ Tema::acentoTexto() }}" aria-hidden="true"></i>
            @endif
            <span>{{ $titulo }}</span>
        </h1>
        @isset($acciones)
            <div class="flex flex-wrap items-center gap-2">
                {{ $acciones }}
            </div>
        @endisset
    </header>

    {{ $slot }}
</div>
