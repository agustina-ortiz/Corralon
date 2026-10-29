{{--
    <x-ui.vacio> — estado vacío de un listado (kit, spec 15). Va adentro de la
    tabla (en un <td colspan>) y de las cards. El texto distingue "todavía no
    hay registros" de "no hay resultados para el filtro".

    Props:
      icono  Font Awesome ("fa-warehouse"); se antepone fa-solid.
--}}
@props([
    'icono' => 'fa-inbox',
])

@php
    use Illuminate\Support\Str;

    if (! Str::startsWith($icono, ['fa-solid', 'fa-regular', 'fa-brands'])) {
        $icono = 'fa-solid '.$icono;
    }
@endphp

<div {{ $attributes->class(['flex flex-col items-center justify-center gap-3 py-6 text-center text-sm font-medium text-gray-400']) }} data-vacio>
    <i class="{{ $icono }} text-4xl text-gray-300" aria-hidden="true"></i>
    <p>{{ $slot }}</p>
</div>
