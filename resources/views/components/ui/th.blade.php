{{--
    <x-ui.th> — cabecera de columna de <x-ui.tabla> (kit, spec 17, D55),
    con el aspecto de siempre del Corralón (gris, mayúsculas, xs semibold).

    Con `columna`, el rótulo es un botón que llama `ordenarPor('columna')` en
    el componente (trait OrdenaPorColumna) y muestra la flecha asc/desc cuando
    esa columna es la que manda; las demás la insinúan al pasar el mouse. Sin
    `columna`, es un <th> común (acciones, columnas que no se ordenan).

      <x-ui.th columna="deposito" :orden="$this->orden()">Depósito</x-ui.th>
      <x-ui.th columna="stock" :orden="$this->orden()" derecha>Stock</x-ui.th>
      <x-ui.th derecha>Acciones</x-ui.th>

    Props:
      columna  Clave que recibe ordenarPor(). Sin ella no se ordena.
      orden    ['columna' => ..., 'direccion' => 'asc'|'desc'] ($this->orden()).
      derecha  Alinea a la derecha (cantidades, acciones).
      centro   Centra la columna (badges Sí/No).
--}}
@props([
    'columna' => null,
    'orden' => ['columna' => null, 'direccion' => 'asc'],
    'derecha' => false,
    'centro' => false,
])

@php
    use App\Support\Tema;

    $activa = $columna !== null && ($orden['columna'] ?? null) === $columna;
    $direccion = $activa ? ($orden['direccion'] ?? 'asc') : null;
    $ariaSort = $activa ? ($direccion === 'desc' ? 'descending' : 'ascending') : null;
    $rotulo = trim(strip_tags((string) $slot));
@endphp

<th scope="col" {{ $attributes->class([
        'px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider',
        'text-right' => $derecha,
        'text-center' => $centro && ! $derecha,
        'text-left' => ! $derecha && ! $centro,
    ]) }}
    @if($ariaSort) aria-sort="{{ $ariaSort }}" @endif
    @if($columna) data-columna="{{ $columna }}" @endif>
    @if($columna)
        <button type="button" wire:click="ordenarPor('{{ $columna }}')"
                class="group inline-flex items-center gap-1.5 uppercase tracking-wider hover:text-gray-900 focus:outline-none focus-visible:underline {{ $derecha ? 'flex-row-reverse' : '' }} {{ $activa ? 'text-gray-900' : '' }}"
                title="Ordenar por {{ $rotulo }}">
            <span>{{ $slot }}</span>
            @if($activa)
                <i class="fa-solid {{ $direccion === 'desc' ? 'fa-arrow-down-wide-short' : 'fa-arrow-up-short-wide' }} text-[11px] {{ Tema::acentoTexto() }}" aria-hidden="true"></i>
            @else
                <i class="fa-solid fa-arrow-up-short-wide text-[11px] text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity" aria-hidden="true"></i>
            @endif
        </button>
    @else
        {{ $slot }}
    @endif
</th>
