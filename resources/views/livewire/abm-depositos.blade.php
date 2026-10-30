<x-ui.pagina titulo="Depósitos" icono="fa-warehouse">
    @if($puedeCrear)
        <x-slot:acciones>
            <x-ui.boton icono="fa-plus" wire:click="crear">Nuevo Depósito</x-ui.boton>
        </x-slot:acciones>
    @endif

    {{-- Búsqueda y filtros --}}
    <div class="mb-6 flex flex-col sm:flex-row gap-3 sm:items-center">
        <x-ui.buscador wire:model.live="search" placeholder="Buscar depósitos..." />
        @if($tieneMultiplesCorralones)
            <x-ui.campo etiqueta="Corralón" nombre="filtro_corralon" oculto class="sm:w-56">
                <x-ui.combobox nombre="filtro_corralon" wire:model.live="filtro_corralon"
                               :opciones="$corralones" etiqueta="descripcion"
                               vacio="Todos los corralones" placeholder="Todos los corralones" />
            </x-ui.campo>
        @endif
    </div>

    {{-- Tabla --}}
    <x-ui.tabla>
        <x-slot:cabecera>
            <x-ui.th columna="id" :orden="$this->orden()">ID</x-ui.th>
            <x-ui.th columna="deposito" :orden="$this->orden()">Depósito</x-ui.th>
            <x-ui.th columna="corralon" :orden="$this->orden()">Corralón</x-ui.th>
            <x-ui.th columna="ubicacion" :orden="$this->orden()">Ubicación</x-ui.th>
            @if($puedeEditar || $puedeEliminar)
                <x-ui.th derecha>Acciones</x-ui.th>
            @endif
        </x-slot:cabecera>

        @forelse ($depositos as $item)
            <tr wire:key="fila-{{ $item->id }}" class="hover:bg-gray-50/50 transition-colors duration-150">
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ $item->id }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ $item->deposito }}</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    {{ $item->corralon->descripcion }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    {{ $item->corralon->ubicacion }}
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
                <td colspan="5" class="px-6 py-6">
                    <x-ui.vacio icono="fa-warehouse">
                        {{ $search || $filtro_corralon ? 'No hay depósitos que coincidan con la búsqueda' : 'Todavía no hay depósitos cargados' }}
                    </x-ui.vacio>
                </td>
            </tr>
        @endforelse

        <x-slot:pie>
            <x-ui.paginacion :paginador="$depositos" />
        </x-slot:pie>
    </x-ui.tabla>

<!-- Modal -->
    @if($showModal)
        <x-ui.modal :titulo="$editMode ? 'Editar Depósito' : 'Nuevo Depósito'"
                    :icono="$editMode ? 'fa-pen' : 'fa-plus'"
                    cerrar="intentarCerrar" ancho="lg" submit="guardar">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.campo etiqueta="Depósito" nombre="deposito" obligatorio>
                    <x-ui.input nombre="deposito" wire:model="deposito" maxlength="100" autocomplete="off" />
                </x-ui.campo>

                <x-ui.campo etiqueta="Corralón" nombre="id_corralon" obligatorio>
                    <x-ui.combobox nombre="id_corralon" wire:model="id_corralon" placeholder="Seleccione un corralón"
                                   :opciones="$corralones->mapWithKeys(fn ($c) => [$c->id => $c->descripcion.' - '.$c->ubicacion])" />
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

    @if($confirmandoDescarte)
        <x-ui.modal-confirmacion tipo="advertencia" titulo="Cambios sin guardar" boton="Descartar cambios"
                                 confirmar="descartarCambios" cancelar="seguirEditando" :z="60">
            Hay cambios sin guardar. ¿Desea descartarlos?
        </x-ui.modal-confirmacion>
    @endif

    @if($eliminarId)
        <x-ui.modal-confirmacion confirmar="eliminar" cancelar="cancelarEliminar">
            ¿Está seguro de eliminar este depósito?
        </x-ui.modal-confirmacion>
    @endif
</x-ui.pagina>
