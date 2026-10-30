<x-ui.pagina titulo="Eventos" icono="fa-calendar-days">
    @if($puedeCrear)
        <x-slot:acciones>
            <x-ui.boton icono="fa-plus" wire:click="crear">Nuevo Evento</x-ui.boton>
        </x-slot:acciones>
    @endif

    <!-- Búsqueda y filtros -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
        <x-ui.buscador wire:model.live="search" placeholder="Buscar eventos..." />
    </div>

    {{-- Tabla --}}
    <x-ui.tabla>
        <x-slot:cabecera>
            <x-ui.th columna="evento" :orden="$this->orden()">Evento</x-ui.th>
            <x-ui.th columna="fecha" :orden="$this->orden()">Fecha</x-ui.th>
            <x-ui.th columna="ubicacion" :orden="$this->orden()">Ubicación</x-ui.th>
            <x-ui.th columna="secretaria" :orden="$this->orden()">Secretaría</x-ui.th>
            <x-ui.th columna="anual" :orden="$this->orden()" centro>Anual</x-ui.th>
            @if ($puedeEditar || $puedeEliminar)
                <x-ui.th derecha>Acciones</x-ui.th>
            @endif
        </x-slot:cabecera>

        @forelse ($eventos as $item)
            <tr wire:key="fila-{{ $item->id }}" class="hover:bg-gray-50/50 transition-colors duration-150">
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ $item->evento }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ $item->fecha_formateada }}
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        {{ $item->ubicacion }}
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        {{ $item->secretaria->secretaria ?? 'N/A' }}
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="flex justify-center">
                        @if($item->evento_anual)
                            <x-ui.badge color="verde"><i class="fa-solid fa-circle-check mr-1" aria-hidden="true"></i>Sí</x-ui.badge>
                        @else
                            <x-ui.badge>No</x-ui.badge>
                        @endif
                    </div>
                </td>
                @if ($puedeEditar || $puedeEliminar)
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-1">
                        {{-- Botón Editar solo si tiene permisos --}}
                        @if($puedeEditar)
                        <x-ui.accion icono="fa-regular fa-pen-to-square" etiqueta="Editar" wire:click="editar({{ $item->id }})" />
                        @endif
                        
                        {{-- Botón Eliminar solo si tiene permisos --}}
                        @if($puedeEliminar)
                        <x-ui.accion icono="fa-regular fa-trash-can" color="peligro" etiqueta="Eliminar" wire:click="confirmarEliminar({{ $item->id }})" />
                        @endif
                    </div>
                </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-6 py-6">
                    <x-ui.vacio icono="fa-calendar-days">{{ $search ? 'No hay eventos que coincidan con la búsqueda' : 'Todavía no hay eventos cargados' }}</x-ui.vacio>
                </td>
            </tr>
        @endforelse

        <x-slot:pie>
            <x-ui.paginacion :paginador="$eventos" />
        </x-slot:pie>
    </x-ui.tabla>

    <!-- Modal -->
    @if($showModal)
        <x-ui.modal :titulo="$editMode ? 'Editar Evento' : 'Nuevo Evento'"
                    :icono="$editMode ? 'fa-pen' : 'fa-plus'"
                    cerrar="intentarCerrar" ancho="lg" submit="guardar">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.campo class="sm:col-span-2" etiqueta="Evento" nombre="evento" obligatorio>
                    <x-ui.input nombre="evento" wire:model="evento" maxlength="200" autocomplete="off" placeholder="Nombre del evento" />
                </x-ui.campo>

                <x-ui.campo etiqueta="Fecha" nombre="fecha" obligatorio>
                    <x-ui.input nombre="fecha" type="date" wire:model="fecha" />
                </x-ui.campo>

                <x-ui.campo etiqueta="Ubicación" nombre="ubicacion" obligatorio>
                    <x-ui.input nombre="ubicacion" wire:model="ubicacion" maxlength="200" autocomplete="off" placeholder="Lugar del evento" />
                </x-ui.campo>

                <x-ui.campo class="sm:col-span-2" etiqueta="Secretaría" nombre="secretaria_id">
                    <x-ui.combobox nombre="secretaria_id" wire:model="secretaria_id" placeholder="Seleccione una secretaría"
                                   :opciones="$secretarias" etiqueta="secretaria" />
                </x-ui.campo>

                <div class="sm:col-span-2">
                    <x-ui.checkbox nombre="evento_anual" etiqueta="Evento Anual" wire:model="evento_anual" />
                </div>
            </div>

            <x-slot:footer>
                <x-ui.boton variante="secundario" wire:click="intentarCerrar">Cancelar</x-ui.boton>
                <x-ui.boton type="submit" wire:loading.attr="disabled" wire:target="guardar">
                    {{ $editMode ? 'Actualizar' : 'Crear' }}
                </x-ui.boton>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    @if($eliminarId)
        <x-ui.modal-confirmacion confirmar="eliminar" cancelar="cancelarEliminar">
            ¿Está seguro de eliminar este evento?
        </x-ui.modal-confirmacion>
    @endif

    @if($confirmandoDescarte)
        <x-ui.modal-confirmacion tipo="advertencia" titulo="Cambios sin guardar" boton="Descartar cambios"
                                 confirmar="descartarCambios" cancelar="seguirEditando" :z="60">
            Hay cambios sin guardar. ¿Desea descartarlos?
        </x-ui.modal-confirmacion>
    @endif
</x-ui.pagina>
