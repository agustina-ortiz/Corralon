{{--
    <x-ui.boton> — botones con texto (kit, spec 15): acciones de pantalla
    ("Nuevo depósito") y footers de modal. Colores de siempre del Corralón
    (degradé azul, vía App\Support\Tema).

    Props:
      variante  primario | secundario (blanco con borde) | peligro (rojo) |
                enlace (solo texto). Por omisión primario.
      tamano    normal | chico (acción de dominio en una fila o una card).
                Nunca se achica un botón a mano con clases px-/py- extra.
      icono     Font Awesome ("fa-plus"); se antepone fa-solid.
      href      Renderiza un <a>. Los internos llevan wire:navigate solos;
                `:navegar="false"` lo desactiva (descargas, PDF).
      type      "button" por omisión; "submit" en el footer de un modal con submit.

    El wire:loading de un botón va siempre acotado con wire:target.
--}}
@props([
    'variante' => 'primario',
    'tamano' => 'normal',
    'icono' => null,
    'href' => null,
    'type' => 'button',
    'navegar' => true,
])

@php
    use App\Support\Tema;
    use Illuminate\Support\Str;

    // Clases literales por variante: Tailwind no ve las armadas en ejecución.
    $clases = match ($variante) {
        'secundario' => Tema::botonSecundario(),
        'peligro' => Tema::botonPeligro(),
        'enlace' => 'bg-transparent '.Tema::enlace().' hover:underline focus:ring-blue-500/40',
        default => Tema::botonPrimario(),
    };

    // Una sola medida por botón: con dos px-/py- gana la que Tailwind genere última.
    $chico = $tamano === 'chico';
    $medidas = match (true) {
        $variante === 'enlace' && $chico => 'gap-1 px-1 py-0 text-xs rounded-md',
        $variante === 'enlace' => 'gap-2 px-1 py-2 text-sm rounded-md',
        $chico => 'gap-1.5 px-3 py-1.5 text-xs rounded-lg',
        default => 'gap-2 px-5 py-2.5 text-base rounded-xl',
    };

    $base = 'inline-flex items-center justify-center font-medium whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed transition-all duration-200 '.$medidas;

    if ($icono !== null && ! Str::startsWith($icono, ['fa-solid', 'fa-regular', 'fa-brands'])) {
        $icono = 'fa-solid '.$icono;
    }

    // wire:navigate solo en enlaces internos sin target propio.
    $navegaConLivewire = $navegar
        && ! $attributes->has('target')
        && Str::startsWith($href ?? '', ['/', url('/')]);
@endphp

@if($href)
    <a href="{{ $href }}" @if($navegaConLivewire) wire:navigate @endif {{ $attributes->class([$base, $clases]) }}>
        @if($icono)<i class="{{ $icono }}" aria-hidden="true"></i>@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class([$base, $clases]) }}>
        @if($icono)<i class="{{ $icono }}" aria-hidden="true"></i>@endif
        {{ $slot }}
    </button>
@endif
