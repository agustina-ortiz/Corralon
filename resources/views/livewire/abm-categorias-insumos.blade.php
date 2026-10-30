<x-ui.pagina titulo="Categorías de Insumos" icono="fa-tags">
    @if($puedeCrear)
        <x-slot:acciones>
            <x-ui.boton icono="fa-plus" wire:click="crear">Nueva Categoría</x-ui.boton>
        </x-slot:acciones>
    @endif

    {{-- Búsqueda --}}
    <div class="mb-6 flex flex-col sm:flex-row gap-3 sm:items-center">
        <x-ui.buscador wire:model.live="search" placeholder="Buscar categorías..." />
    </div>

    {{-- Tabla --}}
    <x-ui.tabla>
        <x-slot:cabecera>
            <x-ui.th columna="id" :orden="$this->orden()">ID</x-ui.th>
            <x-ui.th columna="nombre" :orden="$this->orden()">Nombre</x-ui.th>
            <x-ui.th columna="descripcion" :orden="$this->orden()">Descripción</x-ui.th>
            @if($puedeEditar || $puedeEliminar)
                <x-ui.th derecha>Acciones</x-ui.th>
            @endif
        </x-slot:cabecera>

        @forelse ($categorias as $categoria)
            <tr wire:key="fila-{{ $categoria->id }}" class="hover:bg-gray-50/50 transition-colors duration-150">
                <td class="px-6 py-4 text-sm text-gray-600">
                    {{ $categoria->id }}
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ $categoria->nombre }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-600">
                        {{ $categoria->descripcion ? Str::limit($categoria->descripcion, 60) : '-' }}
                    </div>
                </td>
                @if($puedeEditar || $puedeEliminar)
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-1">
                        @if($puedeEditar)
                        <x-ui.accion icono="fa-regular fa-pen-to-square" etiqueta="Editar" wire:click="editar({{ $categoria->id }})" />
                        @endif

                        @if($puedeEliminar)
                        <x-ui.accion icono="fa-regular fa-trash-can" color="peligro" etiqueta="Eliminar" wire:click="confirmarEliminar({{ $categoria->id }})" />
                        @endif
                    </div>
                </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="4" class="px-6 py-6">
                    <x-ui.vacio icono="fa-tags">{{ $search ? 'No hay categorías que coincidan con la búsqueda' : 'Todavía no hay categorías cargadas' }}</x-ui.vacio>
                </td>
            </tr>
        @endforelse

        <x-slot:pie>
            <x-ui.paginacion :paginador="$categorias" />
        </x-slot:pie>
    </x-ui.tabla>

    
<!-- Modal -->
    @if ($showModal)
        <x-ui.modal :titulo="$editMode ? 'Editar Categoría' : 'Nueva Categoría'"
                    :icono="$editMode ? 'fa-pen' : 'fa-plus'"
                    cerrar="intentarCerrar" ancho="lg" submit="guardar">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.campo class="sm:col-span-2" etiqueta="Nombre" nombre="nombre" obligatorio>
                    <x-ui.input nombre="nombre" wire:model="nombre" maxlength="100" autocomplete="off" />
                </x-ui.campo>

                <x-ui.campo class="sm:col-span-2" etiqueta="Descripción" nombre="descripcion">
                    <x-ui.textarea nombre="descripcion" wire:model="descripcion" :filas="3" class="resize-none" />
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

    @if($eliminarId)
        <x-ui.modal-confirmacion confirmar="eliminar" cancelar="cancelarEliminar">
            ¿Está seguro de eliminar esta categoría?
        </x-ui.modal-confirmacion>
    @endif

    @if($confirmandoDescarte)
        <x-ui.modal-confirmacion tipo="advertencia" titulo="Cambios sin guardar" boton="Descartar cambios"
                                 confirmar="descartarCambios" cancelar="seguirEditando" :z="60">
            Hay cambios sin guardar. ¿Desea descartarlos?
        </x-ui.modal-confirmacion>
    @endif
</x-ui.pagina>
