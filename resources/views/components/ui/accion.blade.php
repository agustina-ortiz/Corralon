{{--
    <x-ui.accion> — acción de una fila (tabla o card): SOLO EL ICONO (kit, spec 15).

    La etiqueta es obligatoria: va en `title` (tooltip) y `aria-label`
    (lector de pantalla). Área de toque de 36 px. Las acciones de dominio
    (devolver, dar de baja...) llevan texto: <x-ui.boton variante="secundario" tamano="chico">.

    Props:
      icono     Font Awesome ("fa-trash-can"); se antepone fa-solid.
      etiqueta  Qué hace la acción (obligatoria).
      color     neutro (azul, como las de siempre) | peligro (rojo).
      href      Renderiza un <a>; los internos llevan wire:navigate solos.
--}}
@props([
    'icono',
    'etiqueta',
    'color' => 'neutro',
    'href' => null,
    'navegar' => true,
])

@php
    use App\Support\Tema;
    use Illuminate\Support\Str;

    $clases = $color === 'peligro'
        ? 'text-red-600 hover:bg-red-50 focus:ring-red-500/40'
        : Tema::accionNeutra();

    $base = 'inline-flex items-center justify-center w-9 h-9 rounded-lg transition-colors duration-150 focus:outline-none focus:ring-2 disabled:opacity-60 disabled:cursor-not-allowed';

    if (! Str::startsWith($icono, ['fa-solid', 'fa-regular', 'fa-brands'])) {
        $icono = 'fa-solid '.$icono;
    }

    $navegaConLivewire = $navegar
        && ! $attributes->has('target')
        && Str::startsWith($href ?? '', ['/', url('/')]);
@endphp

@if($href)
    <a href="{{ $href }}" @if($navegaConLivewire) wire:navigate @endif title="{{ $etiqueta }}" aria-label="{{ $etiqueta }}"
       {{ $attributes->class([$base, $clases]) }}>
        <i class="{{ $icono }}" aria-hidden="true"></i>
    </a>
@else
    <button type="button" title="{{ $etiqueta }}" aria-label="{{ $etiqueta }}"
            {{ $attributes->class([$base, $clases]) }}>
        <i class="{{ $icono }}" aria-hidden="true"></i>
    </button>
@endif
