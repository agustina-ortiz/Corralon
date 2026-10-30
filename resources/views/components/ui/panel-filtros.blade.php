{{--
    <x-ui.panel-filtros> — panel desplegable de filtros de un listado (kit,
    spec 17), con el aspecto de siempre del Corralón ("Filtros Avanzados" en
    una tarjeta blanca debajo del buscador). Lo abre <x-ui.boton-filtros>.

      <x-ui.panel-filtros mostrar="showFilters" :activos="$filtrosActivos" columnas="5">
          <x-ui.campo etiqueta="Categoría" nombre="filtro_categoria">
              <x-ui.combobox nombre="filtro_categoria" wire:model.live="filtro_categoria"
                             :opciones="$categorias" vacio="Todas las categorías" placeholder="Todas las categorías" />
          </x-ui.campo>
          ...
      </x-ui.panel-filtros>

    Props:
      mostrar   Propiedad Livewire booleana que lo abre (se enlaza con $wire.entangle).
      activos   Cantidad de filtros aplicados: con alguno aparece "Limpiar filtros".
      limpiar   Método Livewire de "Limpiar filtros" (limpiarFiltros por omisión).
      columnas  Columnas de la grilla en escritorio (2 a 6).
    Slots: default (los campos) y `pie` (debajo de la grilla: chips, avisos).

    Sin overflow-hidden: recortaría los desplegables que no son fixed.
--}}
@props([
    'mostrar' => 'showFilters',
    'activos' => 0,
    'limpiar' => 'limpiarFiltros',
    'columnas' => 4,
])

@php
    use App\Support\Tema;

    // Clases literales: Tailwind no ve las armadas en ejecución.
    $grilla = match ((int) $columnas) {
        2 => 'lg:grid-cols-2',
        3 => 'lg:grid-cols-3',
        5 => 'lg:grid-cols-5',
        6 => 'lg:grid-cols-6',
        default => 'lg:grid-cols-4',
    };
@endphp

<section
    x-data="{ show: $wire.entangle('{{ $mostrar }}') }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 -translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-4"
    style="display: none;"
    aria-label="Filtros"
    {{ $attributes->class(['mb-6 bg-white rounded-2xl shadow-sm border border-gray-100']) }}
    data-panel-filtros>
    <div class="px-6 py-5">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                <i class="fa-solid fa-filter {{ Tema::acentoTexto() }}" aria-hidden="true"></i>
                Filtros Avanzados
            </h2>
            @if((int) $activos > 0)
                <button type="button" wire:click="{{ $limpiar }}"
                        class="text-sm font-medium flex items-center gap-1.5 rounded-md transition-colors focus:outline-none focus-visible:underline {{ Tema::enlace() }}"
                        data-limpiar-filtros>
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    Limpiar filtros
                </button>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 {{ $grilla }} gap-4">
            {{ $slot }}
        </div>

        @isset($pie)
            {{ $pie }}
        @endisset
    </div>
</section>
