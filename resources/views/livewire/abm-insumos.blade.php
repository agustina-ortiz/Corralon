<x-ui.pagina titulo="Insumos" icono="fa-boxes-stacked">
    <x-slot:acciones>
        <x-ui.boton variante="secundario" icono="fa-chart-column" wire:click="toggleEstadisticas">Estadísticas</x-ui.boton>

        {{-- Exportar (dropdown Excel/PDF) --}}
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <x-ui.boton variante="secundario" icono="fa-download" x-on:click="open = !open">
                Exportar <i class="fa-solid fa-chevron-down text-xs" aria-hidden="true"></i>
            </x-ui.boton>
            <div
                x-show="open" x-transition x-cloak
                class="absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-20"
            >
                <button wire:click="exportarExcel" @click="open = false" class="w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Excel (.xlsx)
                </button>
                <button wire:click="exportarPdf" @click="open = false" class="w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    PDF
                </button>
            </div>
        </div>

        @if($puedeCrear)
            <x-ui.boton icono="fa-plus" wire:click="crear">Nuevo Insumo</x-ui.boton>
        @endif
    </x-slot:acciones>

    <!-- Búsqueda y filtros -->
    @php
        $filtrosActivos = collect([$filtro_corralon, $filtro_categoria, $filtro_unidad, $filtro_deposito, $filtro_stock_bajo])->filter()->count();
    @endphp
    <div class="mb-6 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
        <x-ui.buscador wire:model.live="search" placeholder="Buscar insumos..." />
        <div class="flex gap-3">
            <x-ui.boton-filtros :activos="$filtrosActivos" wire:click="$toggle('showFilters')" />
        </div>
    </div>

    {{-- Panel de Filtros --}}
    <x-ui.panel-filtros mostrar="showFilters" :activos="$filtrosActivos" columnas="5">
        <x-ui.campo etiqueta="Categoría" nombre="filtro_categoria">
            <x-ui.combobox nombre="filtro_categoria" wire:model.live="filtro_categoria" :opciones="$categorias"
                           vacio="Todas las categorías" placeholder="Todas las categorías" />
        </x-ui.campo>

        <x-ui.campo etiqueta="Unidad de Medida" nombre="filtro_unidad">
            <x-ui.combobox nombre="filtro_unidad" wire:model.live="filtro_unidad" :opciones="$unidades->all()"
                           vacio="Todas las unidades" placeholder="Todas las unidades" />
        </x-ui.campo>

        <x-ui.campo etiqueta="Corralón" nombre="filtro_corralon">
            <x-ui.combobox nombre="filtro_corralon" wire:model.live="filtro_corralon" :opciones="$corralones" etiqueta="descripcion"
                           vacio="Todos los corralones" placeholder="Todos los corralones" />
        </x-ui.campo>

        <x-ui.campo etiqueta="Depósito" nombre="filtro_deposito">
            <x-ui.combobox nombre="filtro_deposito" wire:model.live="filtro_deposito" :opciones="$depositos" etiqueta="deposito"
                           vacio="Todos los depósitos" placeholder="Todos los depósitos" />
        </x-ui.campo>

        <div>
            <span class="block text-sm font-medium text-gray-700 mb-2">Estado de Stock</span>
            <label class="flex items-center px-4 py-2.5 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-all duration-200">
                <input
                    type="checkbox"
                    wire:model.live="filtro_stock_bajo"
                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                >
                <span class="ml-3 text-sm text-gray-700 font-medium">Solo stock bajo mínimo</span>
            </label>
        </div>
    </x-ui.panel-filtros>

    
{{-- Tabla --}}
<x-ui.tabla>
    <x-slot:cabecera>
            <x-ui.th columna="insumo" :orden="$this->orden()" centro>Insumo</x-ui.th>
            <x-ui.th columna="categoria" :orden="$this->orden()" centro>Categoría</x-ui.th>
            <x-ui.th columna="stock" :orden="$this->orden()">Stock Actual</x-ui.th>
            <x-ui.th columna="unidad" :orden="$this->orden()">Unidad</x-ui.th>
            <x-ui.th columna="minimo" :orden="$this->orden()">Stock Mínimo</x-ui.th>
            <x-ui.th columna="deposito" :orden="$this->orden()">Depósito</x-ui.th>
            <x-ui.th columna="corralon" :orden="$this->orden()">Corralón</x-ui.th>
            @if($puedeEditar || $puedeEliminar)
            <x-ui.th derecha>Acciones</x-ui.th>
            @endif
    </x-slot:cabecera>

        @forelse ($insumos as $item)
            <tr wire:key="fila-{{ $item->id }}" class="hover:bg-gray-50/50 transition-colors duration-150 {{ $item->stock_actual < $item->stock_minimo ? 'bg-red-50/50' : '' }}">
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ $item->insumo }}</div>
                </td>
                <td class="px-6 py-4 w-1/4 text-center">
                    <x-ui.badge color="azul">{{ $item->categoriaInsumo->nombre }}</x-ui.badge>
                </td>
                <td class="px-6 py-4">
                    <span class="text-sm {{ $item->stock_actual < $item->stock_minimo ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                        {{ number_format($item->stock_actual, 2) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    {{ $item->unidad }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    {{ number_format($item->stock_minimo, 2) }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    {{ $item->deposito->deposito }}
                </td>
                <td class="whitespace-nowrap px-6 py-4">
                    <x-ui.badge color="azul">{{ $item->deposito?->corralon?->descripcion ?? 'Sin corralón' }}</x-ui.badge>
                </td>
                @if($puedeEditar || $puedeEliminar)
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-1">
                        @if($puedeEditar)
                        <x-ui.accion icono="fa-regular fa-pen-to-square" etiqueta="Editar" wire:click="editar({{ $item->id }})" />
                        @endif
                        
                        @if($puedeEliminar)
                        <x-ui.accion icono="fa-regular fa-trash-can" color="peligro" etiqueta="Eliminar" wire:click="confirmarEliminar({{ $item->id }})" />
                        @endif
                    </div>
                </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-6 py-6">
                    <x-ui.vacio icono="fa-boxes-stacked">{{ $search || $filtro_corralon || $filtro_categoria || $filtro_unidad || $filtro_deposito || $filtro_stock_bajo ? 'No hay insumos que coincidan con la búsqueda o los filtros' : 'Todavía no hay insumos cargados' }}</x-ui.vacio>
                </td>
            </tr>
        @endforelse

    <x-slot:pie>
        <x-ui.paginacion :paginador="$insumos" />
    </x-slot:pie>
</x-ui.tabla>

    <!-- Modal -->
    @if($showModal)
        <x-ui.modal :titulo="$editMode ? 'Editar Insumo' : 'Nuevo Insumo'"
                    :icono="$editMode ? 'fa-pen' : 'fa-plus'"
                    cerrar="intentarCerrar" ancho="lg" submit="guardar">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.campo class="sm:col-span-2" etiqueta="Insumo" nombre="insumo" obligatorio>
                    <x-ui.input nombre="insumo" wire:model="insumo" maxlength="100" autocomplete="off" />
                </x-ui.campo>

                <x-ui.campo etiqueta="Categoría" nombre="id_categoria" obligatorio>
                    <x-ui.combobox nombre="id_categoria" wire:model="id_categoria" placeholder="Seleccione una categoría"
                                   :opciones="$categorias" />
                </x-ui.campo>

                <x-ui.campo etiqueta="Unidad" nombre="unidad" obligatorio>
                    <x-ui.combobox nombre="unidad" wire:model="unidad" placeholder="Seleccionar unidad..." :ordenar="false"
                                   :opciones="['UNIDAD', 'LITROS', 'TAMBOR', 'METRO', 'ROLLO X 100 MT', 'PAQUETE', 'BOLSA', 'BALDE', 'KILOS']" />
                </x-ui.campo>

                @if(!$editMode)
                    <x-ui.campo etiqueta="Stock Inicial" nombre="stock_inicial" ayuda="Cantidad inicial en inventario (opcional).">
                        <x-ui.input nombre="stock_inicial" type="number" step="0.01" min="0" wire:model="stock_inicial" placeholder="0.00" />
                    </x-ui.campo>
                @else
                    {{-- Solo lectura: el stock se calcula desde los movimientos. --}}
                    <x-ui.campo etiqueta="Stock Actual (Calculado)" nombre="stock_actual" ayuda="El stock se calcula desde los movimientos.">
                        <x-ui.input nombre="stock_actual" :value="number_format($insumos->firstWhere('id', $insumo_id)?->stock_actual ?? 0, 2)" disabled />
                    </x-ui.campo>
                @endif

                <x-ui.campo etiqueta="Stock Mínimo" nombre="stock_minimo" obligatorio>
                    <x-ui.input nombre="stock_minimo" type="number" step="0.01" min="0" wire:model="stock_minimo" />
                </x-ui.campo>

                <x-ui.campo class="sm:col-span-2" etiqueta="Depósito" nombre="id_deposito" obligatorio>
                    <x-ui.combobox nombre="id_deposito" wire:model="id_deposito" placeholder="Seleccione un depósito" :ordenar="false"
                                   :opciones="$depositos->mapWithKeys(fn ($d) => [$d->id => $d->deposito.' - '.($d->corralon->descripcion ?? 'Sin corralón')])" />
                </x-ui.campo>

                {{-- Solo al crear: va al movimiento de Inventario Inicial. --}}
                @if(!$editMode)
                    <x-ui.campo class="sm:col-span-2" etiqueta="N° Orden de Compra / Suministro" nombre="nro_orden_compra"
                                ayuda="Se registra en el movimiento de inventario inicial (solo si hay stock inicial mayor a 0).">
                        <x-ui.input nombre="nro_orden_compra" wire:model="nro_orden_compra" maxlength="100" autocomplete="off" placeholder="Opcional" />
                    </x-ui.campo>
                @endif
            </div>

            <x-slot:footer>
                <x-ui.boton variante="secundario" wire:click="intentarCerrar">Cancelar</x-ui.boton>
                <x-ui.boton type="submit" wire:loading.attr="disabled" wire:target="guardar">
                    {{ $editMode ? 'Actualizar' : 'Crear' }}
                </x-ui.boton>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    {{-- Modal de Estadísticas --}}
    @if($showEstadisticas && $estadisticas)
        <x-ui.modal titulo="Estadísticas de Insumos" icono="fa-chart-column" cerrar="toggleEstadisticas" ancho="3xl">
            <div class="space-y-6">
                @if($estadisticas['total'] == 0)
                    <p class="text-center text-gray-500 py-8">No hay insumos para los filtros seleccionados.</p>
                @else
                {{-- KPIs --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-gray-800">{{ number_format($estadisticas['total']) }}</div>
                        <div class="text-xs text-gray-500 mt-1">Insumos</div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-green-700">{{ number_format($estadisticas['ok']) }}</div>
                        <div class="text-xs text-gray-500 mt-1">Con stock OK</div>
                    </div>
                    <div class="bg-amber-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-amber-600">{{ number_format($estadisticas['bajo_minimo']) }}</div>
                        <div class="text-xs text-gray-500 mt-1">Bajo mínimo</div>
                    </div>
                    <div class="bg-red-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-red-600">{{ number_format($estadisticas['sin_stock']) }}</div>
                        <div class="text-xs text-gray-500 mt-1">Sin stock</div>
                    </div>
                </div>

                {{-- Distribución por categoría --}}
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Insumos por categoría</h4>
                    <div class="space-y-2">
                        @php $maxCat = $estadisticas['por_categoria']->max() ?: 1; @endphp
                        @foreach($estadisticas['por_categoria']->take(8) as $cat => $cant)
                            <div class="flex items-center gap-3">
                                <div class="w-32 text-xs text-gray-600 truncate" title="{{ $cat }}">{{ $cat }}</div>
                                <div class="flex-1 bg-gray-100 rounded-full h-4 overflow-hidden">
                                    <div class="bg-[#77BF43] h-4 rounded-full" style="width: {{ ($cant / $maxCat) * 100 }}%"></div>
                                </div>
                                <div class="w-10 text-right text-xs font-medium text-gray-700">{{ $cant }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Stock por depósito --}}
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Stock total por depósito</h4>
                    <div class="space-y-2">
                        @php $maxDep = $estadisticas['por_deposito']->max() ?: 1; @endphp
                        @foreach($estadisticas['por_deposito']->take(8) as $dep => $stock)
                            <div class="flex items-center gap-3">
                                <div class="w-32 text-xs text-gray-600 truncate" title="{{ $dep }}">{{ $dep }}</div>
                                <div class="flex-1 bg-gray-100 rounded-full h-4 overflow-hidden">
                                    <div class="bg-blue-500 h-4 rounded-full" style="width: {{ ($stock / $maxDep) * 100 }}%"></div>
                                </div>
                                <div class="w-16 text-right text-xs font-medium text-gray-700">{{ number_format($stock, 0) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Top bajo mínimo --}}
                @if($estadisticas['top_bajo_minimo']->count() > 0)
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Insumos a reponer (bajo mínimo)</h4>
                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs text-gray-500">
                                <tr>
                                    <th class="text-left px-3 py-2">Insumo</th>
                                    <th class="text-left px-3 py-2">Depósito</th>
                                    <th class="text-right px-3 py-2">Stock</th>
                                    <th class="text-right px-3 py-2">Mínimo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($estadisticas['top_bajo_minimo'] as $i)
                                    <tr class="border-t border-gray-50">
                                        <td class="px-3 py-2 text-gray-700">{{ $i->insumo }}</td>
                                        <td class="px-3 py-2 text-gray-500">{{ $i->deposito->deposito ?? '' }}</td>
                                        <td class="px-3 py-2 text-right font-medium text-amber-600">{{ number_format($i->stock_actual, 2) }}</td>
                                        <td class="px-3 py-2 text-right text-gray-500">{{ number_format($i->stock_minimo, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                @endif
            </div>
        </x-ui.modal>
    @endif

    @if($confirmandoDescarte)
        <x-ui.modal-confirmacion tipo="advertencia" titulo="Cambios sin guardar" boton="Descartar cambios"
                                 confirmar="descartarCambios" cancelar="seguirEditando" :z="60">
            Hay cambios sin guardar. ¿Desea descartarlos?
        </x-ui.modal-confirmacion>
    @endif

    @if($eliminarId)
        <x-ui.modal-confirmacion confirmar="eliminar" cancelar="cancelarEliminar">
            ¿Está seguro de eliminar este insumo?
        </x-ui.modal-confirmacion>
    @endif
</x-ui.pagina>
