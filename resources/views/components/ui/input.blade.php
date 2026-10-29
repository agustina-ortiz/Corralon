{{--
    <x-ui.input> — input de texto / número / fecha (kit, spec 14). Va adentro
    de <x-ui.campo>. El `id` sale de `nombre` para que el rótulo lo enfoque.
    Mismo aspecto que los inputs de siempre del Corralón (rounded-xl).

    Props:
      nombre  Nombre de la propiedad Livewire (id y borde rojo si tiene error).
      type    text por omisión.
    Todo lo demás pasa como atributo (wire:model, maxlength, placeholder...).
--}}
@props([
    'nombre' => null,
    'type' => 'text',
])

@php
    use App\Support\Tema;

    // Fuera de una request (Blade::render en tests) $errors no está compartido.
    $conError = $nombre !== null && isset($errors) && $errors->has($nombre);
    // Error y ayuda de <x-ui.campo>: un id que no existe (campo sin ayuda) lo
    // ignoran los lectores de pantalla.
    $describe = $nombre === null ? null : trim(($conError ? 'error-'.$nombre.' ' : '').'ayuda-'.$nombre);
@endphp

<input type="{{ $type }}"
       @if($nombre) id="campo-{{ $nombre }}" aria-describedby="{{ $describe }}" @endif
       @if($conError) aria-invalid="true" @endif
       {{ $attributes->class([
           'block w-full px-4 py-2.5 border rounded-xl text-gray-800 focus:outline-none transition-all duration-200 disabled:bg-gray-50 disabled:text-gray-400',
           Tema::anilloFoco(),
           'border-gray-200' => ! $conError,
           'border-red-500' => $conError,
       ]) }}>
