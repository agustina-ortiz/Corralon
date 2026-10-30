{{--
    <x-ui.campo> — un campo de formulario: rótulo, control y error inline
    (kit, spec 14).

    El error de validación lo muestra el campo, debajo del control. El error y
    la ayuda llevan id (`error-{nombre}`, `ayuda-{nombre}`) para que
    <x-ui.input> y <x-ui.textarea> los asocien con aria-describedby.

    Props:
      etiqueta     Texto del rótulo.
      nombre       Nombre de la propiedad Livewire, para @error y el `for`
                   (el control lleva id="campo-{nombre}").
      obligatorio  Marca el rótulo con un asterisco rojo.
      ayuda        Texto chico debajo del control (opcional).
      oculto       El rótulo queda solo para lectores de pantalla (sr-only):
                   filtros en la fila del buscador, sin rótulo visible.
    Slot: el control (<x-ui.input>, <x-ui.textarea>, un <select> nativo con
    id="campo-{nombre}" hasta la fase de combobox...).
--}}
@props([
    'etiqueta' => null,
    'nombre' => null,
    'obligatorio' => false,
    'ayuda' => null,
    'oculto' => false,
])

<div {{ $attributes->class(['w-full']) }}>
    @if($etiqueta)
        <label @if($nombre) for="campo-{{ $nombre }}" @endif class="{{ $oculto ? 'sr-only' : 'block text-sm font-medium text-gray-700 mb-2' }}">
            {{ $etiqueta }}
            @if($obligatorio)<span class="text-red-600" aria-hidden="true">*</span>@endif
        </label>
    @endif

    {{ $slot }}

    @if($nombre && isset($errors))
        @error($nombre)
            <p id="error-{{ $nombre }}" class="mt-1 text-xs text-red-600" data-error="{{ $nombre }}">{{ $message }}</p>
        @enderror
    @endif

    @if($ayuda)
        <p @if($nombre) id="ayuda-{{ $nombre }}" @endif class="mt-1 text-xs text-gray-500">{{ $ayuda }}</p>
    @endif
</div>
