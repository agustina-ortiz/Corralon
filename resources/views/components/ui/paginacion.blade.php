{{--
    <x-ui.paginacion> — pie de paginación de un listado (kit, spec 17).
    Reemplaza a `$paginador->links()`; va en el slot `pie` de <x-ui.tabla>.

      <x-ui.paginacion :paginador="$depositos" />

    Props:
      paginador     Un LengthAwarePaginator (WithPagination o uno armado a
                    mano). Si viene, los demás valores salen de él.
      current-page, total-pages, offset, per-page, total-records
                    Estado del listado sin paginador.
      accion        Acción Livewire que recibe el número de página
                    ("gotoPage", la de WithPagination, por omisión).

    Mobile: Anterior / "Página X de Y" / Siguiente. Escritorio: además los
    números, con "…" cuando hay muchas páginas. Como los links() de Livewire,
    al cambiar de página lleva la pantalla arriba.
--}}
@props([
    'paginador' => null,
    'currentPage' => 1,
    'totalPages' => 1,
    'offset' => 0,
    'perPage' => 10,
    'totalRecords' => 0,
    'accion' => 'gotoPage',
])

@php
    use App\Support\Tema;

    if ($paginador !== null) {
        $currentPage = $paginador->currentPage();
        $totalPages = $paginador->lastPage();
        $perPage = $paginador->perPage();
        $offset = ($currentPage - 1) * $perPage;
        $totalRecords = $paginador->total();
    }

    $currentPage = max(1, (int) $currentPage);
    $totalPages = max(1, (int) $totalPages);
    $totalRecords = (int) $totalRecords;
    $desde = $totalRecords > 0 ? $offset + 1 : 0;
    $hasta = min($offset + $perPage, $totalRecords);

    // Ventana de números: primera, última, actual ±1, con "…" en los huecos.
    $paginas = [];
    if ($totalPages <= 7) {
        $paginas = range(1, $totalPages);
    } else {
        $candidatas = array_unique(array_filter(
            [1, 2, $currentPage - 1, $currentPage, $currentPage + 1, $totalPages - 1, $totalPages],
            fn ($p) => $p >= 1 && $p <= $totalPages,
        ));
        sort($candidatas);
        $anterior = 0;
        foreach ($candidatas as $p) {
            if ($p - $anterior > 1) {
                $paginas[] = '…';
            }
            $paginas[] = $p;
            $anterior = $p;
        }
    }

    $claseBoton = 'inline-flex items-center justify-center min-w-[2.25rem] h-9 px-3 rounded-lg border text-sm font-medium transition-colors duration-150 focus:outline-none '.Tema::anilloFoco();
    $claseNormal = 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50';
    $claseActiva = Tema::paginaActiva();
    $claseDeshabilitado = 'border-gray-100 bg-gray-50 text-gray-300 cursor-not-allowed';
    $subir = "(\$el.closest('body') || document.querySelector('body')).scrollIntoView()";
@endphp

<nav {{ $attributes->class(['flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between px-6 py-4 border-t border-gray-100 bg-gray-50/30 text-sm text-gray-500']) }}
     aria-label="Paginación"
     data-paginacion>

    <p class="text-center sm:text-left">
        @if($totalRecords === 0)
            No hay registros para mostrar
        @elseif($totalRecords === 1)
            Mostrando 1 registro
        @else
            Mostrando {{ $desde }} a {{ $hasta }} de {{ $totalRecords }} registros
        @endif
    </p>

    @if($totalPages > 1)
        <div class="flex items-center justify-center gap-1">
            {{-- Anterior --}}
            @if($currentPage > 1)
                <button type="button" wire:click="{{ $accion }}({{ $currentPage - 1 }})" x-on:click="{{ $subir }}"
                        class="{{ $claseBoton }} {{ $claseNormal }}" aria-label="Anterior">
                    <i class="fa-solid fa-chevron-left text-xs" aria-hidden="true"></i>
                </button>
            @else
                <span class="{{ $claseBoton }} {{ $claseDeshabilitado }}" aria-disabled="true">
                    <i class="fa-solid fa-chevron-left text-xs" aria-hidden="true"></i>
                </span>
            @endif

            {{-- Mobile: solo "Página X de Y" --}}
            <span class="md:hidden px-3 text-gray-700">Página {{ $currentPage }} de {{ $totalPages }}</span>

            {{-- Escritorio: los números --}}
            <div class="hidden md:flex items-center gap-1">
                @foreach($paginas as $pagina)
                    @if($pagina === '…')
                        <span class="px-2 text-gray-400">…</span>
                    @elseif($pagina === $currentPage)
                        <span class="{{ $claseBoton }} {{ $claseActiva }}" aria-current="page">{{ $pagina }}</span>
                    @else
                        <button type="button" wire:click="{{ $accion }}({{ $pagina }})" x-on:click="{{ $subir }}"
                                class="{{ $claseBoton }} {{ $claseNormal }}"
                                aria-label="Ir a la página {{ $pagina }}">{{ $pagina }}</button>
                    @endif
                @endforeach
            </div>

            {{-- Siguiente --}}
            @if($currentPage < $totalPages)
                <button type="button" wire:click="{{ $accion }}({{ $currentPage + 1 }})" x-on:click="{{ $subir }}"
                        class="{{ $claseBoton }} {{ $claseNormal }}" aria-label="Siguiente">
                    <i class="fa-solid fa-chevron-right text-xs" aria-hidden="true"></i>
                </button>
            @else
                <span class="{{ $claseBoton }} {{ $claseDeshabilitado }}" aria-disabled="true">
                    <i class="fa-solid fa-chevron-right text-xs" aria-hidden="true"></i>
                </span>
            @endif
        </div>
    @endif
</nav>
