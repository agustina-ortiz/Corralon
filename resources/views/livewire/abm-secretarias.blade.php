<div>
    <!-- Header con búsqueda y botones crear -->
    <div class="mb-8 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
        <div class="flex flex-col sm:flex-row gap-3 flex-1">
            <div class="relative flex-1 max-w-md">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input
                    type="text"
                    wire:model.live="search"
                    placeholder="Buscar secretarías o áreas..."
                    class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                >
            </div>
        </div>
        @if($puedeCrear)
        <div class="flex gap-2">
            <button
                wire:click="crearSecretaria"
                class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 transition-all duration-200 flex items-center justify-center gap-2 font-medium"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Nueva Secretaría
            </button>
            <button
                wire:click="crearArea"
                class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-xl hover:from-emerald-700 hover:to-emerald-800 shadow-lg shadow-emerald-500/30 hover:shadow-xl hover:shadow-emerald-500/40 transition-all duration-200 flex items-center justify-center gap-2 font-medium"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Nueva Área
            </button>
        </div>
        @endif
    </div>



    <!-- Tabla de Secretarías -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Secretaría</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Áreas</th>
                        @if($puedeEditar || $puedeEliminar)
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse ($secretarias as $item)
                        <tr class="hover:bg-gray-50/50 transition-colors duration-150">
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
                                <div class="flex items-center justify-end gap-2">
                                    @if($puedeCrear)
                                    <button
                                        wire:click="crearArea({{ $item->id }})"
                                        class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors duration-150"
                                        title="Agregar área"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </button>
                                    @endif
                                    @if($puedeEditar)
                                    <button
                                        wire:click="editarSecretaria({{ $item->id }})"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-150"
                                        title="Editar secretaría"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    @endif
                                    @if($puedeEliminar)
                                    <button
                                        wire:click="confirmarEliminarSecretaria({{ $item->id }})"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-150"
                                        title="Eliminar secretaría"
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
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <p class="text-sm font-medium">No se encontraron secretarías</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
            {{ $secretarias->links() }}
        </div>
    </div>

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
                        <button
                            type="button"
                            wire:click="editarArea({{ $areaItem['id'] }})"
                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-150"
                            title="Editar área"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        @endif
                        @if($puedeEliminar)
                        <button
                            type="button"
                            wire:click="confirmarEliminarArea({{ $areaItem['id'] }})"
                            class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-150"
                            title="Eliminar área"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8 text-gray-400">
                <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <p class="text-sm">Esta secretaría no tiene áreas</p>
            </div>
            @endif

            <x-slot:footer>
                @if($puedeCrear)
                <button
                    type="button"
                    wire:click="crearArea({{ $idSecretariaAreas }})"
                    class="mr-auto px-4 py-2 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-xl hover:from-emerald-700 hover:to-emerald-800 shadow-sm transition-all duration-200 flex items-center gap-2 text-sm font-medium"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Agregar Área
                </button>
                @endif
                <button
                    type="button"
                    wire:click="cerrarModalAreas"
                    class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-100 transition-colors duration-200 font-medium"
                >
                    Cerrar
                </button>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    <!-- Modal Secretaría -->
    @if($showModalSecretaria)
        <x-ui.modal :titulo="$editModeSecretaria ? 'Editar Secretaría' : 'Nueva Secretaría'"
                    :icono="$editModeSecretaria ? 'fa-pen' : 'fa-plus'"
                    cerrar="intentarCerrar" ancho="lg" submit="guardarSecretaria">
            <div>
                <label for="secretaria" class="block text-sm font-medium text-gray-700 mb-2">Nombre de la Secretaría *</label>
                <input
                    id="secretaria"
                    type="text"
                    wire:model="secretaria"
                    @error('secretaria') aria-invalid="true" @enderror
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                >
                @error('secretaria') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
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
                    wire:loading.attr="disabled" wire:target="guardarSecretaria"
                    class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 shadow-lg shadow-blue-500/30 transition-all duration-200 font-medium"
                >
                    {{ $editModeSecretaria ? 'Actualizar' : 'Crear' }}
                </button>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    <!-- Modal Área -->
    @if($showModalArea)
        <x-ui.modal :titulo="$editModeArea ? 'Editar Área' : 'Nueva Área'"
                    :icono="$editModeArea ? 'fa-pen' : 'fa-plus'"
                    cerrar="intentarCerrar" ancho="lg" submit="guardarArea">
            <div>
                <label for="id_secretaria" class="block text-sm font-medium text-gray-700 mb-2">Secretaría *</label>
                <select
                    id="id_secretaria"
                    wire:model="id_secretaria"
                    @error('id_secretaria') aria-invalid="true" @enderror
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                >
                    <option value="">Seleccione una secretaría</option>
                    @foreach(\App\Models\Secretaria::orderBy('secretaria')->get() as $sec)
                        <option value="{{ $sec->id }}">{{ $sec->secretaria }}</option>
                    @endforeach
                </select>
                @error('id_secretaria') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="area" class="block text-sm font-medium text-gray-700 mb-2">Nombre del Área *</label>
                <input
                    id="area"
                    type="text"
                    wire:model="area"
                    @error('area') aria-invalid="true" @enderror
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                >
                @error('area') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
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
                    wire:loading.attr="disabled" wire:target="guardarArea"
                    class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-xl hover:from-emerald-700 hover:to-emerald-800 shadow-lg shadow-emerald-500/30 transition-all duration-200 font-medium"
                >
                    {{ $editModeArea ? 'Actualizar' : 'Crear' }}
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
</div>
