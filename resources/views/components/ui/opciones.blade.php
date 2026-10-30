{{--
    <x-ui.opciones> — kit de especificaciones, specs 14 y 16, adaptado al
    Corralón: un grupo de opciones excluyentes (radio) dibujado como botones
    segmentados, para elegir entre 2 y 4 valores fijos de un vistazo
    (combustible, estado del vehículo). Mismo alto, borde y redondeo que
    <x-ui.input>; la elegida en azul (Tema::opcionElegida()).

    Es un <input type="radio"> por opción, escondido (sr-only) con `peer`.
    Respeta wire:model / wire:model.live tal cual. Las flechas recorren el
    grupo (comportamiento nativo de los radios).

    No va en <x-ui.campo> (no hay un control con id="campo-<nombre>"): va en
    <x-ui.grupo>, que pone el fieldset, el rótulo y el error.

      <x-ui.grupo etiqueta="Combustible" nombre="tipo_combustible" obligatorio>
          <x-ui.opciones nombre="tipo_combustible" wire:model.live="tipo_combustible"
                         :opciones="['nafta' => 'Nafta', 'diesel' => 'Diesel', 'gas' => 'Gas']" />
      </x-ui.grupo>

    Props:
      opciones  array asociativo valor => texto, o lista de enums con etiqueta().
      nombre    Propiedad Livewire: name de los radios, prefijo del id
                (campo-<nombre>-<valor>) y clave del error (borde rojo +
                aria-invalid en el primero, para que el modal lleve el foco).
      disabled  Deshabilita el grupo.
--}}
@props([
    'opciones' => [],
    'nombre' => null,
    'disabled' => false,
])

@php
    use App\Support\Tema;
    use Illuminate\Support\Str;

    $normalizadas = [];
    foreach ($opciones as $valor => $texto) {
        if ($texto instanceof \BackedEnum) {
            $normalizadas[$texto->value] = method_exists($texto, 'etiqueta') ? $texto->etiqueta() : $texto->name;
        } else {
            $normalizadas[$valor] = $texto;
        }
    }

    $grupo = $nombre ?? 'opciones-'.Str::random(6);
    $conError = $nombre !== null && isset($errors) && $errors->has($nombre);
    $describe = $nombre === null ? null : trim(($conError ? 'error-'.$nombre.' ' : '').'ayuda-'.$nombre);
    $claseOpcion = 'flex h-full items-center justify-center text-center cursor-pointer select-none whitespace-nowrap px-3 py-2.5 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors '
        .'peer-disabled:cursor-not-allowed peer-disabled:opacity-60 '
        .Tema::opcionElegida();
@endphp

<div role="radiogroup" data-opciones="{{ $grupo }}"
     @if($describe) aria-describedby="{{ $describe }}" @endif
     {{ $attributes->whereDoesntStartWith('wire:model')->class([
         'inline-flex max-w-full rounded-xl border overflow-hidden divide-x',
         'border-gray-200 divide-gray-200' => ! $conError,
         'border-red-500 divide-red-200' => $conError,
     ]) }}>
    @foreach($normalizadas as $valor => $texto)
        <label class="relative flex-auto">
            <input type="radio" name="{{ $grupo }}" value="{{ $valor }}" id="campo-{{ $grupo }}-{{ Str::slug((string) $valor) ?: 'vacio' }}"
                   class="peer sr-only" @disabled($disabled)
                   @if($conError && $loop->first) aria-invalid="true" @endif
                   {{ $attributes->whereStartsWith('wire:model') }}>
            <span class="{{ $claseOpcion }}">{{ $texto }}</span>
        </label>
    @endforeach
</div>
