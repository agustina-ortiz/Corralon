<div>
    <!-- Header con búsqueda y botón crear -->
    <div class="mb-8 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
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
        @if($puedeCrear)
        <button
            wire:click="crear"
            class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 transition-all duration-200 flex items-center justify-center gap-2 font-medium"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Nuevo Depósito
        </button>
        @endif
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
                                <div class="flex items-center justify-end gap-2">
                                    @if($puedeEditar)
                                    <button 
                                        wire:click="editar({{ $item->id }})"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-150"
                                        title="Editar"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    @endif

                                    @if($puedeEliminar)
                                    <button 
                                        wire:click="confirmarEliminar({{ $item->id }})"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-150"
                                        title="Eliminar"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <p class="text-sm font-medium">No se encontraron depósitos</p>
                                </div>
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
            <!-- Depósito -->
            <div>
                <label for="deposito" class="block text-sm font-medium text-gray-700 mb-2">Depósito *</label>
                <input
                    id="deposito"
                    type="text"
                    wire:model="deposito"
                    @error('deposito') aria-invalid="true" @enderror
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                >
                @error('deposito') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Corralón -->
            <div>
                <label for="id_corralon" class="block text-sm font-medium text-gray-700 mb-2">Corralón *</label>
                <select
                    id="id_corralon"
                    wire:model="id_corralon"
                    @error('id_corralon') aria-invalid="true" @enderror
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                >
                    <option value="">Seleccione un corralón</option>
                    @foreach($corralones as $corralon)
                        <option value="{{ $corralon->id }}">
                            {{ $corralon->descripcion }} - {{ $corralon->ubicacion }}
                        </option>
                    @endforeach
                </select>
                @error('id_corralon') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <x-slot:footer>
                <button
                    type="button"
                    wire:click="intentarCerrar"
                    class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-100 transition-colors duration-200 font-medium"
                >
                    Cancelar
                </button>
                <button
                    type="submit"
                    wire:loading.attr="disabled" wire:target="guardar"
                    class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 shadow-lg shadow-blue-500/30 transition-all duration-200 font-medium disabled:opacity-60"
                >
                    {{ $editMode ? 'Actualizar' : 'Crear' }}
                </button>
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
</div>
