<x-ui.pagina titulo="Maquinarias" icono="fa-screwdriver-wrench">
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
            <x-ui.boton icono="fa-plus" wire:click="crear">Nueva Maquinaria</x-ui.boton>
        @endif
    </x-slot:acciones>

    <!-- Búsqueda y filtros -->
    @php
        $filtrosActivos = collect([$filtro_corralon, $filtro_categoria, $filtro_estado, $filtro_deposito])->filter()->count();
    @endphp
    <div class="mb-6 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
        <x-ui.buscador wire:model.live="search" placeholder="Buscar maquinarias..." />
        <div class="flex gap-3">
            <x-ui.boton-filtros :activos="$filtrosActivos" wire:click="$toggle('showFilters')" />
        </div>
    </div>

    {{-- Panel de Filtros --}}
    <x-ui.panel-filtros mostrar="showFilters" :activos="$filtrosActivos" columnas="4">
        <x-ui.campo etiqueta="Corralón" nombre="filtro_corralon">
            <x-ui.combobox nombre="filtro_corralon" wire:model.live="filtro_corralon" :opciones="$corralones" etiqueta="descripcion"
                           vacio="Todos los corralones" placeholder="Todos los corralones" />
        </x-ui.campo>

        <x-ui.campo etiqueta="Categoría" nombre="filtro_categoria">
            <x-ui.combobox nombre="filtro_categoria" wire:model.live="filtro_categoria" :opciones="$categorias"
                           vacio="Todas las categorías" placeholder="Todas las categorías" />
        </x-ui.campo>

        <x-ui.campo etiqueta="Estado" nombre="filtro_estado">
            <x-ui.combobox nombre="filtro_estado" wire:model.live="filtro_estado"
                           :opciones="['disponible' => 'Disponible', 'no disponible' => 'No Disponible']" :ordenar="false"
                           vacio="Todos los estados" placeholder="Todos los estados" />
        </x-ui.campo>

        <x-ui.campo etiqueta="Depósito" nombre="filtro_deposito">
            <x-ui.combobox nombre="filtro_deposito" wire:model.live="filtro_deposito" :opciones="$depositos" etiqueta="deposito"
                           vacio="Todos los depósitos" placeholder="Todos los depósitos" />
        </x-ui.campo>
    </x-ui.panel-filtros>

    
{{-- Tabla --}}
<x-ui.tabla>
    <x-slot:cabecera>
            <x-ui.th columna="maquinaria" :orden="$this->orden()">Maquinaria</x-ui.th>
            <x-ui.th columna="categoria" :orden="$this->orden()">Categoría</x-ui.th>
            <x-ui.th columna="cantidad" :orden="$this->orden()">Cantidad</x-ui.th>
            <x-ui.th columna="estado" :orden="$this->orden()">Estado</x-ui.th>
            <x-ui.th columna="deposito" :orden="$this->orden()">Depósito</x-ui.th>
            <x-ui.th columna="corralon" :orden="$this->orden()">Corralón</x-ui.th>
            @if($puedeEditar || $puedeEliminar)
            <x-ui.th derecha>Acciones</x-ui.th>
            @endif
    </x-slot:cabecera>

        @forelse ($maquinarias as $item)
            <tr wire:key="fila-{{ $item->id }}" class="hover:bg-gray-50/50 transition-colors duration-150">
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ $item->maquinaria }}</div>
                </td>
                <td class="px-6 py-4">
                    <x-ui.badge color="azul">{{ $item->categoriaMaquinaria->nombre }}</x-ui.badge>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        @if($item->cantidad > 0)
                            <span class="text-sm font-semibold text-green-700">{{ $item->cantidad }}</span>
                        @else
                            <span class="text-sm font-semibold text-red-700">{{ $item->cantidad }}</span>
                        @endif
                        <span class="text-xs text-gray-500">
                            {{ $item->cantidad == 1 ? 'unidad' : 'unidades' }}
                        </span>
                    </div>
                </td>
                <td class="px-6 py-4">
                    @if($item->estado === 'disponible')
                        <x-ui.badge color="verde"><i class="fa-solid fa-circle-check mr-1.5" aria-hidden="true"></i>Disponible</x-ui.badge>
                    @else
                        <x-ui.badge color="rojo"><i class="fa-solid fa-circle-xmark mr-1.5" aria-hidden="true"></i>No Disponible</x-ui.badge>
                    @endif
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
                    <x-ui.vacio icono="fa-screwdriver-wrench">{{ $search || $filtro_corralon || $filtro_categoria || $filtro_estado || $filtro_deposito ? 'No hay maquinarias que coincidan con la búsqueda o los filtros' : 'Todavía no hay maquinarias cargadas' }}</x-ui.vacio>
                </td>
            </tr>
        @endforelse

    <x-slot:pie>
        <x-ui.paginacion :paginador="$maquinarias" />
    </x-slot:pie>
</x-ui.tabla>

    <!-- Modal -->
    @if($showModal)
        <x-ui.modal :titulo="$editMode ? 'Editar Maquinaria' : 'Nueva Maquinaria'"
                    :icono="$editMode ? 'fa-pen' : 'fa-plus'"
                    cerrar="intentarCerrar" ancho="lg" submit="guardar">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.campo class="sm:col-span-2" etiqueta="Maquinaria" nombre="maquinaria" obligatorio>
                    <x-ui.input nombre="maquinaria" wire:model="maquinaria" maxlength="100" autocomplete="off" />
                </x-ui.campo>

                <x-ui.campo class="sm:col-span-2" etiqueta="Categoría" nombre="id_categoria_maquinaria" obligatorio>
                    <x-ui.combobox nombre="id_categoria_maquinaria" wire:model="id_categoria_maquinaria" placeholder="Seleccione una categoría"
                                   :opciones="$categorias" />
                </x-ui.campo>

                <x-ui.campo etiqueta="Cantidad" nombre="cantidad" obligatorio
                            :ayuda="$editMode ? 'Se calcula automáticamente con los movimientos.' : null">
                    <div class="relative">
                        <x-ui.input nombre="cantidad" type="number" min="1" wire:model="cantidad" :disabled="$editMode" />
                        @if($editMode)
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fa-solid fa-lock text-gray-400" aria-hidden="true"></i>
                            </div>
                        @endif
                    </div>
                </x-ui.campo>

                <x-ui.grupo etiqueta="Estado" nombre="estado" obligatorio>
                    <x-ui.opciones nombre="estado" wire:model="estado" class="w-full"
                                   :opciones="['disponible' => 'Disponible', 'no disponible' => 'No Disponible']" />
                </x-ui.grupo>

                <x-ui.campo etiqueta="Corralón" nombre="id_corralon" obligatorio>
                    <x-ui.combobox nombre="id_corralon" wire:model.live="id_corralon" placeholder="Seleccione un corralón"
                                   :opciones="$corralones" etiqueta="descripcion" />
                </x-ui.campo>

                <x-ui.campo etiqueta="Depósito" nombre="id_deposito" obligatorio>
                    <x-ui.combobox nombre="id_deposito" wire:model="id_deposito" :disabled="! $id_corralon"
                                   :placeholder="$id_corralon ? 'Seleccione un depósito' : 'Primero seleccione un corralón'"
                                   :opciones="$depositosModal" etiqueta="deposito" />
                </x-ui.campo>
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
        <x-ui.modal titulo="Estadísticas de Maquinarias" icono="fa-chart-column" cerrar="toggleEstadisticas" ancho="3xl">
            <div class="space-y-6">
                @if($estadisticas['total'] == 0)
                    <p class="text-center text-gray-500 py-8">No hay maquinarias para los filtros seleccionados.</p>
                @else
                {{-- KPIs --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-gray-800">{{ number_format($estadisticas['total']) }}</div>
                        <div class="text-xs text-gray-500 mt-1">Maquinarias</div>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-blue-700">{{ number_format($estadisticas['unidades_total']) }}</div>
                        <div class="text-xs text-gray-500 mt-1">Unidades totales</div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-green-700">{{ number_format($estadisticas['disponibles']) }}</div>
                        <div class="text-xs text-gray-500 mt-1">Disponibles</div>
                    </div>
                    <div class="bg-red-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-red-600">{{ number_format($estadisticas['no_disponibles']) }}</div>
                        <div class="text-xs text-gray-500 mt-1">No disponibles</div>
                    </div>
                </div>

                {{-- Por categoría --}}
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Maquinarias por categoría</h4>
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

                {{-- Unidades por depósito --}}
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Unidades totales por depósito</h4>
                    <div class="space-y-2">
                        @php $maxDep = $estadisticas['por_deposito']->max() ?: 1; @endphp
                        @foreach($estadisticas['por_deposito']->take(8) as $dep => $u)
                            <div class="flex items-center gap-3">
                                <div class="w-32 text-xs text-gray-600 truncate" title="{{ $dep }}">{{ $dep }}</div>
                                <div class="flex-1 bg-gray-100 rounded-full h-4 overflow-hidden">
                                    <div class="bg-blue-500 h-4 rounded-full" style="width: {{ ($u / $maxDep) * 100 }}%"></div>
                                </div>
                                <div class="w-12 text-right text-xs font-medium text-gray-700">{{ number_format($u) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
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
            ¿Está seguro de eliminar esta maquinaria?
        </x-ui.modal-confirmacion>
    @endif
</x-ui.pagina>
