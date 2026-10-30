{{--
    <x-ui.grupo> — un grupo de radios o casillas con su rótulo y su error
    (kit, spec 14, la pieza de inversiones). Es el <fieldset> + <legend> que
    pide un grupo: <x-ui.campo> dibuja un <label for="campo-x"> y un grupo no
    tiene un control con ese id.

    Props: los de <x-ui.campo> (etiqueta, nombre, obligatorio, ayuda).
    Slot: el grupo (<x-ui.opciones>, casillas).
--}}
@props([
    'etiqueta' => null,
    'nombre' => null,
    'obligatorio' => false,
    'ayuda' => null,
])

<fieldset {{ $attributes->class(['w-full min-w-0']) }}>
    @if($etiqueta)
        <legend class="block text-sm font-medium text-gray-700 mb-2">
            {{ $etiqueta }}
            @if($obligatorio)<span class="text-red-600" aria-hidden="true">*</span>@endif
        </legend>
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
</fieldset>
