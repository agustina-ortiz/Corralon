<x-ui.pagina titulo="Secretarías y Áreas" icono="fa-building-columns">
    @if($puedeCrear)
        <x-slot:acciones>
            <x-ui.boton icono="fa-plus" wire:click="crearSecretaria">Nueva Secretaría</x-ui.boton>
            <x-ui.boton variante="exito" icono="fa-plus" wire:click="crearArea">Nueva Área</x-ui.boton>
        </x-slot:acciones>
    @endif

    <!-- Búsqueda -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
        <div class="flex flex-col sm:flex-row gap-3 flex-1">
            <x-ui.buscador wire:model.live="search" placeholder="Buscar secretarías o áreas..." />
        </div>
    </div>

    {{-- Tabla de Secretarías --}}
    <x-ui.tabla>
        <x-slot:cabecera>
            <x-ui.th columna="id" :orden="$this->orden()">ID</x-ui.th>
            <x-ui.th columna="secretaria" :orden="$this->orden()">Secretaría</x-ui.th>
            <x-ui.th columna="areas" :orden="$this->orden()">Áreas</x-ui.th>
            @if($puedeEditar || $puedeEliminar)
            <x-ui.th derecha>Acciones</x-ui.th>
            @endif
        </x-slot:cabecera>

        @forelse ($secretarias as $item)
            <tr wire:key="fila-{{ $item->id }}" class="hover:bg-gray-50/50 transition-colors duration-150">
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ $item->id }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-semibold text-gray-900">{{ $item->secretaria }}</div>
                </td>
                <td class="px-6 py-4">
                    <button
                        wire:click="verAreas({{ $item->id }})"
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium transition-colors duration-150 {{ $item->areas_count > 0 ? 'bg-blue-100 text-blue-800 hover:bg-blue-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        {{ $item->areas_count }} {{ $item->areas_count === 1 ? 'área' : 'áreas' }}
                    </button>
                </td>
                @if($puedeEditar || $puedeEliminar)
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-1">
                        @if($puedeCrear)
                        <x-ui.accion icono="fa-plus" color="exito" etiqueta="Agregar área" wire:click="crearArea({{ $item->id }})" />
                        @endif
                        @if($puedeEditar)
                        <x-ui.accion icono="fa-regular fa-pen-to-square" etiqueta="Editar secretaría" wire:click="editarSecretaria({{ $item->id }})" />
                        @endif
                        @if($puedeEliminar)
                        <x-ui.accion icono="fa-regular fa-trash-can" color="peligro" etiqueta="Eliminar secretaría" wire:click="confirmarEliminarSecretaria({{ $item->id }})" />
                        @endif
                    </div>
                </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="4" class="px-6 py-6">
                    <x-ui.vacio icono="fa-building-columns">{{ $search ? 'No hay secretarías que coincidan con la búsqueda' : 'Todavía no hay secretarías cargadas' }}</x-ui.vacio>
                </td>
            </tr>
        @endforelse

        <x-slot:pie>
            <x-ui.paginacion :paginador="$secretarias" />
        </x-slot:pie>
    </x-ui.tabla>

    <!-- Modal Ver Áreas -->
    @if($showModalAreas)
        <x-ui.modal :titulo="'Áreas de ' . $nombreSecretariaAreas" icono="fa-sitemap"
                    cerrar="cerrarModalAreas" ancho="lg">
            @if(count($areasSecretaria) > 0)
            <div class="divide-y divide-gray-100 border border-gray-100 rounded-xl overflow-hidden">
                @foreach($areasSecretaria as $areaItem)
                <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors" wire:key="area-{{ $areaItem['id'] }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                        <span class="text-sm text-gray-700">{{ $areaItem['area'] }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($puedeEditar)
                        <x-ui.accion icono="fa-regular fa-pen-to-square" etiqueta="Editar área" wire:click="editarArea({{ $areaItem['id'] }})" />
                        @endif
                        @if($puedeEliminar)
                        <x-ui.accion icono="fa-regular fa-trash-can" color="peligro" etiqueta="Eliminar área" wire:click="confirmarEliminarArea({{ $areaItem['id'] }})" />
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <x-ui.vacio icono="fa-sitemap">Esta secretaría no tiene áreas</x-ui.vacio>
            @endif

            <x-slot:footer>
                @if($puedeCrear)
                <x-ui.boton variante="exito" icono="fa-plus" class="mr-auto" wire:click="crearArea({{ $idSecretariaAreas }})">Agregar Área</x-ui.boton>
                @endif
                <x-ui.boton variante="secundario" wire:click="cerrarModalAreas">Cerrar</x-ui.boton>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    <!-- Modal Secretaría -->
    @if($showModalSecretaria)
        <x-ui.modal :titulo="$editModeSecretaria ? 'Editar Secretaría' : 'Nueva Secretaría'"
                    :icono="$editModeSecretaria ? 'fa-pen' : 'fa-plus'"
                    cerrar="intentarCerrar" ancho="lg" submit="guardarSecretaria">
            <x-ui.campo etiqueta="Nombre de la Secretaría" nombre="secretaria" obligatorio>
                <x-ui.input nombre="secretaria" wire:model="secretaria" maxlength="200" autocomplete="off" />
            </x-ui.campo>

            <x-slot:footer>
                <x-ui.boton variante="secundario" wire:click="intentarCerrar">Cancelar</x-ui.boton>
                <x-ui.boton type="submit" wire:loading.attr="disabled" wire:target="guardarSecretaria">
                    {{ $editModeSecretaria ? 'Actualizar' : 'Crear' }}
                </x-ui.boton>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    <!-- Modal Área -->
    @if($showModalArea)
        <x-ui.modal :titulo="$editModeArea ? 'Editar Área' : 'Nueva Área'"
                    :icono="$editModeArea ? 'fa-pen' : 'fa-plus'"
                    cerrar="intentarCerrar" ancho="lg" submit="guardarArea">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.campo class="sm:col-span-2" etiqueta="Secretaría" nombre="id_secretaria" obligatorio>
                    <x-ui.combobox nombre="id_secretaria" wire:model="id_secretaria" placeholder="Seleccione una secretaría"
                                   :opciones="\App\Models\Secretaria::orderBy('secretaria')->get()" etiqueta="secretaria" />
                </x-ui.campo>

                <x-ui.campo class="sm:col-span-2" etiqueta="Nombre del Área" nombre="area" obligatorio>
                    <x-ui.input nombre="area" wire:model="area" maxlength="200" autocomplete="off" />
                </x-ui.campo>
            </div>

            <x-slot:footer>
                <x-ui.boton variante="secundario" wire:click="intentarCerrar">Cancelar</x-ui.boton>
                <x-ui.boton variante="exito" type="submit" wire:loading.attr="disabled" wire:target="guardarArea">
                    {{ $editModeArea ? 'Actualizar' : 'Crear' }}
                </x-ui.boton>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    @if($confirmandoDescarte)
        <x-ui.modal-confirmacion tipo="advertencia" titulo="Cambios sin guardar" boton="Descartar cambios"
                                 confirmar="descartarCambios" cancelar="seguirEditando" :z="60">
            Hay cambios sin guardar. ¿Desea descartarlos?
        </x-ui.modal-confirmacion>
    @endif

    @if($eliminarSecretariaId)
        <x-ui.modal-confirmacion confirmar="eliminarSecretaria" cancelar="cancelarEliminarSecretaria">
            ¿Está seguro de eliminar esta secretaría?
        </x-ui.modal-confirmacion>
    @endif

    @if($eliminarAreaId)
        <x-ui.modal-confirmacion confirmar="eliminarAreaDesdeModal" cancelar="cancelarEliminarArea" :z="60">
            ¿Está seguro de eliminar el área "{{ collect($areasSecretaria)->firstWhere('id', $eliminarAreaId)['area'] ?? '' }}"?
        </x-ui.modal-confirmacion>
    @endif
</x-ui.pagina>
