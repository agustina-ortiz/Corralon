<div>
    <!-- Header con búsqueda y botón crear -->
    <div class="mb-8 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
        <div class="flex-1 max-w-md">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input
                    type="text"
                    wire:model.live="search"
                    placeholder="Buscar por nombre, DNI, legajo o área..."
                    class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                >
            </div>
        </div>

        @if($puedeCrear)
        <button
            wire:click="crear"
            class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 transition-all duration-200 flex items-center justify-center gap-2 font-medium"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Nuevo Chofer
        </button>
        @endif
    </div>

    <!-- Mensaje de éxito -->
    @if (session()->has('message'))
        <div class="mb-6 px-4 py-3.5 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('message') }}
        </div>
    @endif

    <!-- Mensaje de error -->
    @if (session()->has('error'))
        <div class="mb-6 px-4 py-3.5 bg-gradient-to-r from-red-50 to-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Tabla -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Legajo</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">DNI</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Licencia</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Venc. Licencia</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Área</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Vehículos</th>
                        @if($puedeEditar || $puedeEliminar)
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse ($choferes as $chofer)
                        <tr class="hover:bg-gray-50/50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 inline-flex text-xs font-medium rounded-full bg-gradient-to-r from-blue-50 to-blue-100 text-blue-700 border border-blue-200">
                                    {{ $chofer->numero_empleado }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $chofer->nombre }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600">{{ $chofer->dni }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($chofer->licencia)
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700">{{ $chofer->licencia }}</span>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($chofer->vencimiento_licencia)
                                    @php
                                        $hoy = now();
                                        $venc = $chofer->vencimiento_licencia;
                                        $dias = $hoy->diffInDays($venc, false);
                                    @endphp
                                    @if($dias < 0)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">
                                            Vencida {{ $venc->format('d/m/Y') }}
                                        </span>
                                    @elseif($dias <= 30)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700">
                                            {{ $venc->format('d/m/Y') }} ({{ $dias }}d)
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-600">{{ $venc->format('d/m/Y') }}</span>
                                    @endif
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600">{{ $chofer->area ?? '—' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($chofer->vehiculos->count() > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($chofer->vehiculos as $v)
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700">
                                                {{ $v->patente ?? 'Móvil ' . $v->nro_movil }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">Sin asignar</span>
                                @endif
                            </td>
                            @if($puedeEditar || $puedeEliminar)
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($puedeEditar)
                                    <button
                                        wire:click="editar({{ $chofer->id }})"
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
                                        wire:click="eliminar({{ $chofer->id }})"
                                        wire:confirm="¿Está seguro de eliminar este chofer? Se eliminarán también sus asignaciones de vehículos."
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
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <p class="text-sm font-medium">No se encontraron choferes</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
            {{ $choferes->links() }}
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay -->
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" wire:click="cerrarModal"></div>

                <!-- Modal -->
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit.prevent="guardar">
                        <div class="bg-white px-6 pt-6 pb-5">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xl font-semibold text-gray-900">
                                    {{ $editMode ? 'Editar Chofer' : 'Nuevo Chofer' }}
                                </h3>
                                <button
                                    type="button"
                                    wire:click="cerrarModal"
                                    class="text-gray-400 hover:text-gray-600 transition-colors"
                                >
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="space-y-5">
                                <!-- Fila: Nombre -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre y Apellido *</label>
                                    <input
                                        type="text"
                                        wire:model="nombre"
                                        placeholder="Ej: García, Juan"
                                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                    >
                                    @error('nombre') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Fila: DNI + Legajo -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">DNI *</label>
                                        <input
                                            type="text"
                                            wire:model="dni"
                                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                        >
                                        @error('dni') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">N° Empleado *</label>
                                        <input
                                            type="text"
                                            wire:model="numero_empleado"
                                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                        >
                                        @error('numero_empleado') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- Fila: Licencia + Vencimiento -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Licencia</label>
                                        <select
                                            wire:model="licencia"
                                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                        >
                                            <option value="">— Sin especificar —</option>
                                            <option value="PROFESIONAL">PROFESIONAL</option>
                                            <option value="COMUN">COMÚN</option>
                                        </select>
                                        @error('licencia') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Vencimiento Licencia</label>
                                        <input
                                            type="date"
                                            wire:model="vencimiento_licencia"
                                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                        >
                                        @error('vencimiento_licencia') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- Categorías de licencia -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Categorías (ej: A.1.4 B.2 D.1)</label>
                                    <input
                                        type="text"
                                        wire:model="tipo_licencia"
                                        placeholder="Ej: A.1.4 B.2 D.1 D.4"
                                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                    >
                                    @error('tipo_licencia') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Fila: Área + Domicilio -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Área</label>
                                        <input
                                            type="text"
                                            wire:model="area"
                                            placeholder="Ej: Tránsito"
                                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                        >
                                        @error('area') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Domicilio</label>
                                        <input
                                            type="text"
                                            wire:model="domicilio"
                                            placeholder="Ej: 39 N°3285"
                                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                        >
                                        @error('domicilio') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- Secretaría -->
                                @if($secretarias->count() > 0)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Secretaría</label>
                                    <select
                                        wire:model="secretaria_id"
                                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                    >
                                        <option value="">— Sin asignar —</option>
                                        @foreach($secretarias as $secretaria)
                                            <option value="{{ $secretaria->id }}">{{ $secretaria->secretaria }}</option>
                                        @endforeach
                                    </select>
                                    @error('secretaria_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                @endif

                                <!-- Vehículos asignados -->
                                <div x-data="{ busquedaVehiculo: '' }">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Vehículos asignados</label>
                                    <div class="relative mb-2">
                                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                        <input
                                            type="text"
                                            x-model="busquedaVehiculo"
                                            placeholder="Buscar por patente o móvil..."
                                            class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                                        >
                                    </div>
                                    <div class="border border-gray-200 rounded-xl max-h-40 overflow-y-auto p-3 space-y-2">
                                        @forelse($vehiculos as $vehiculo)
                                            <label
                                                class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 rounded-lg p-1"
                                                x-show="busquedaVehiculo === '' || '{{ strtolower($vehiculo->patente . ' ' . $vehiculo->nro_movil) }}'.includes(busquedaVehiculo.toLowerCase())"
                                            >
                                                <input
                                                    type="checkbox"
                                                    wire:model="vehiculosSeleccionados"
                                                    value="{{ $vehiculo->id }}"
                                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                >
                                                <span class="text-sm text-gray-700">
                                                    <span class="font-medium">Móvil {{ $vehiculo->nro_movil }}</span>
                                                    @if($vehiculo->vehiculo) — {{ $vehiculo->vehiculo }}@endif
                                                    @if($vehiculo->patente) ({{ $vehiculo->patente }})@endif
                                                </span>
                                            </label>
                                        @empty
                                            <p class="text-sm text-gray-400">No hay vehículos registrados.</p>
                                        @endforelse
                                    </div>
                                    @error('vehiculosSeleccionados') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3">
                            <button
                                type="button"
                                wire:click="cerrarModal"
                                class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-100 transition-colors duration-200 font-medium"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 shadow-lg shadow-blue-500/30 transition-all duration-200 font-medium"
                            >
                                {{ $editMode ? 'Actualizar' : 'Crear' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
