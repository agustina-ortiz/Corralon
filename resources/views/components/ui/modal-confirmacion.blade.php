{{--
    <x-ui.modal-confirmacion> — kit de especificaciones, spec 12.

    El único diálogo de confirmación del sistema: ni confirm() ni wire:confirm.
    Se monta sobre <x-ui.modal>, así que hereda scroll-lock, Escape y foco.

    Props:
      confirmar  Acción Livewire del botón de confirmar (obligatoria).
      cancelar   Acción Livewire de cancelar / X / Escape (obligatoria).
      tipo       peligro (por omisión, rojo) | advertencia (ámbar) | info (azul).
      titulo     Por omisión según el tipo.
      boton      Texto del botón de confirmar; por omisión según el tipo.
      z          50 | 60 | 70, igual que <x-ui.modal>.

    Slot: el texto de la pregunta.
--}}
@props([
    'confirmar',
    'cancelar',
    'tipo' => 'peligro',
    'titulo' => null,
    'boton' => null,
    'z' => 50,
])

@php
    use App\Support\Tema;

    // Colores semánticos: no cambian con el acento del sistema.
    $estilos = [
        'peligro' => [
            'titulo' => 'Confirmar eliminación',
            'boton' => 'Eliminar',
            'icono' => 'fa-triangle-exclamation',
            'iconoClase' => 'bg-red-100 text-red-600',
            'botonClase' => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
        ],
        'advertencia' => [
            'titulo' => 'Atención',
            'boton' => 'Continuar',
            'icono' => 'fa-circle-exclamation',
            'iconoClase' => 'bg-amber-100 text-amber-600',
            'botonClase' => 'bg-amber-600 hover:bg-amber-700 text-white focus:ring-amber-500',
        ],
        'info' => [
            'titulo' => 'Confirmar',
            'boton' => 'Aceptar',
            'icono' => 'fa-circle-info',
            'iconoClase' => 'bg-blue-100 text-blue-600',
            'botonClase' => 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500',
        ],
    ];
    $tipo = array_key_exists($tipo, $estilos) ? $tipo : 'peligro';
    $estilo = $estilos[$tipo];

    $titulo ??= $estilo['titulo'];
    $boton ??= $estilo['boton'];
@endphp

{{-- Sin icono en el header: el del body ya lleva el color semántico. --}}
<x-ui.modal :titulo="$titulo" :cerrar="$cancelar" ancho="md" :z="$z" {{ $attributes }}>
    <div class="flex items-start gap-4">
        <div class="shrink-0 flex items-center justify-center w-10 h-10 rounded-full {{ $estilo['iconoClase'] }}">
            <i class="fa-solid {{ $estilo['icono'] }}" aria-hidden="true"></i>
        </div>
        <div class="text-sm text-gray-700 pt-2">{{ $slot }}</div>
    </div>

    <x-slot:footer>
        <button type="button" wire:click="{{ $cancelar }}"
                class="inline-flex justify-center items-center px-5 py-2.5 rounded-xl text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 {{ Tema::botonSecundario() }}">
            Cancelar
        </button>
        {{-- wire:loading SIEMPRE acotado con wire:target --}}
        <button type="button" wire:click="{{ $confirmar }}" data-atajo-guardar
                wire:loading.attr="disabled" wire:target="{{ $confirmar }}"
                class="inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed {{ $estilo['botonClase'] }}">
            <span wire:loading.remove wire:target="{{ $confirmar }}">{{ $boton }}</span>
            <span wire:loading wire:target="{{ $confirmar }}" class="inline-flex items-center gap-2">
                <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
                Procesando...
            </span>
        </button>
    </x-slot:footer>
</x-ui.modal>
