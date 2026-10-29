{{--
    <x-ui.textarea> — área de texto, mismo criterio que <x-ui.input>.

    Props:
      nombre  Nombre de la propiedad Livewire.
      filas   Alto en renglones (3).
--}}
@props([
    'nombre' => null,
    'filas' => 3,
])

@php
    use App\Support\Tema;

    $conError = $nombre !== null && isset($errors) && $errors->has($nombre);
    $describe = $nombre === null ? null : trim(($conError ? 'error-'.$nombre.' ' : '').'ayuda-'.$nombre);
@endphp

<textarea rows="{{ $filas }}"
          @if($nombre) id="campo-{{ $nombre }}" aria-describedby="{{ $describe }}" @endif
          @if($conError) aria-invalid="true" @endif
          {{ $attributes->class([
              'block w-full px-4 py-2.5 border rounded-xl text-gray-800 focus:outline-none transition-all duration-200 disabled:bg-gray-50 disabled:text-gray-400',
              Tema::anilloFoco(),
              'border-gray-200' => ! $conError,
              'border-red-500' => $conError,
          ]) }}>{{ $slot }}</textarea>
