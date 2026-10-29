{{--
    <x-ui.badge> — etiqueta chica de estado (kit, spec 15). Solo colores
    semánticos y el gris neutro.

    Props:
      color  gris (por omisión) | verde | rojo | ambar | azul | violeta.
             También los nombres de Tailwind: gray | emerald | green | red |
             amber | yellow | blue | purple.
--}}
@props([
    'color' => 'gris',
])

@php
    // Clases literales.
    $clases = match ($color) {
        'verde', 'emerald', 'green' => 'bg-green-100 text-green-800',
        'rojo', 'red' => 'bg-red-100 text-red-800',
        'ambar', 'amber', 'yellow' => 'bg-amber-100 text-amber-800',
        'azul', 'blue' => 'bg-blue-100 text-blue-800',
        'violeta', 'purple' => 'bg-purple-100 text-purple-800',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap', $clases]) }}>
    {{ $slot }}
</span>
