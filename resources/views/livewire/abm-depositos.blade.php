<x-ui.pagina titulo="Depósitos" icono="fa-warehouse">
    @if($puedeCrear)
        <x-slot:acciones>
            <x-ui.boton icono="fa-plus" wire:click="crear">Nuevo Depósito</x-ui.boton>
        </x-slot:acciones>
    @endif

    <!-- Búsqueda y filtros -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
        <div class="flex flex-col sm:flex-row gap-3 flex-1">
            <div class="relative flex-1 max-w-md">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input
                    type="text"
                    wire:model.live="search"
                    placeholder="Buscar depósitos..."
                    class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                >
            </div>
            @if($tieneMultiplesCorralones)
            <div class="w-full sm:w-56">
                <select
                    wire:model.live="filtro_corralon"
                    class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                >
                    <option value="">Todos los corralones</option>
                    @foreach($corralones as $corralon)
                        <option value="{{ $corralon->id }}">{{ $corralon->descripcion }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Depósito</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Corralón</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Ubicación</th>
                        @if($puedeEditar || $puedeEliminar)
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse ($depositos as $item)
                        <tr class="hover:bg-gray-50/50 transition-colors duration-150">
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
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
            {{ $depositos->links() }}
        </div>
    </div>

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
