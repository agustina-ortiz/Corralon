{{--
    <x-ui.select-nativo> — PROVISORIO: <select> nativo dentro de un
    <x-ui.campo>, con el id, el aria y el borde rojo de <x-ui.input>.
    En la fase de combobox (spec 16) cada uno pasa a <x-ui.combobox> o
    <x-ui.opciones>; buscar "x-ui.select-nativo" para encontrarlos todos.

    Props:
      nombre  Nombre de la propiedad Livewire.
    Slot: los <option>.
--}}
@props([
    'nombre' => null,
])

@php
    use App\Support\Tema;

    $conError = $nombre !== null && isset($errors) && $errors->has($nombre);
    $describe = $nombre === null ? null : trim(($conError ? 'error-'.$nombre.' ' : '').'ayuda-'.$nombre);
@endphp

<select @if($nombre) id="campo-{{ $nombre }}" aria-describedby="{{ $describe }}" @endif
        @if($conError) aria-invalid="true" @endif
        {{ $attributes->class([Tema::selectNativo($conError)]) }}>
    {{ $slot }}
</select>
