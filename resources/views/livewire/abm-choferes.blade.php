<x-ui.pagina titulo="Choferes" icono="fa-id-card">
    @if($puedeCrear)
        <x-slot:acciones>
            <x-ui.boton icono="fa-plus" wire:click="crear">Nuevo Chofer</x-ui.boton>
        </x-slot:acciones>
    @endif

    <!-- Búsqueda y filtros -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
        <x-ui.buscador wire:model.live="search" placeholder="Buscar por nombre, DNI, legajo o área..." />

    </div>

    {{-- Tabla --}}
    <x-ui.tabla>
        <x-slot:cabecera>
            <x-ui.th columna="legajo" :orden="$this->orden()">Legajo</x-ui.th>
            <x-ui.th columna="nombre" :orden="$this->orden()">Nombre</x-ui.th>
            <x-ui.th columna="dni" :orden="$this->orden()">DNI</x-ui.th>
            <x-ui.th columna="licencia" :orden="$this->orden()">Licencia</x-ui.th>
            <x-ui.th columna="vencimiento" :orden="$this->orden()">Venc. Licencia</x-ui.th>
            <x-ui.th columna="area" :orden="$this->orden()">Área</x-ui.th>
            <x-ui.th columna="vehiculos" :orden="$this->orden()">Vehículos</x-ui.th>
            @if($puedeEditar || $puedeEliminar)
            <x-ui.th derecha>Acciones</x-ui.th>
            @endif
        </x-slot:cabecera>

        @forelse ($choferes as $chofer)
            <tr wire:key="fila-{{ $chofer->id }}" class="hover:bg-gray-50/50 transition-colors duration-150">
                <td class="px-6 py-4">
                    <x-ui.badge color="azul">{{ $chofer->numero_empleado }}</x-ui.badge>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ $chofer->nombre }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-600">{{ $chofer->dni }}</div>
                </td>
                <td class="px-6 py-4">
                    @if($chofer->licencia)
                        <x-ui.badge>{{ $chofer->licencia }}</x-ui.badge>
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
                            <x-ui.badge color="rojo">Vencida {{ $venc->format('d/m/Y') }}</x-ui.badge>
                        @elseif($dias <= 30)
                            <x-ui.badge color="ambar">{{ $venc->format('d/m/Y') }} ({{ (int) ceil($dias) }}d)</x-ui.badge>
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
                                <x-ui.badge color="verde">{{ $v->patente ?? 'Patrimonio ' . $v->nro_patrimonio }}</x-ui.badge>
                            @endforeach
                        </div>
                    @else
                        <span class="text-gray-400 text-xs">Sin asignar</span>
                    @endif
                </td>
                @if($puedeEditar || $puedeEliminar)
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-1">
                        @if($puedeEditar)
                        <x-ui.accion icono="fa-regular fa-pen-to-square" etiqueta="Editar" wire:click="editar({{ $chofer->id }})" />
                        @endif

                        @if($puedeEliminar)
                        <x-ui.accion icono="fa-regular fa-trash-can" color="peligro" etiqueta="Eliminar" wire:click="confirmarEliminar({{ $chofer->id }})" />
                        @endif
                    </div>
                </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-6 py-6">
                    <x-ui.vacio icono="fa-id-card">{{ $search ? 'No hay choferes que coincidan con la búsqueda' : 'Todavía no hay choferes cargados' }}</x-ui.vacio>
                </td>
            </tr>
        @endforelse

        <x-slot:pie>
            <x-ui.paginacion :paginador="$choferes" />
        </x-slot:pie>
    </x-ui.tabla>

    <!-- Modal -->
    @if($showModal)
        <x-ui.modal :titulo="$editMode ? 'Editar Chofer' : 'Nuevo Chofer'"
                    :icono="$editMode ? 'fa-pen' : 'fa-plus'"
                    cerrar="intentarCerrar" ancho="2xl" submit="guardar">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.campo class="sm:col-span-2" etiqueta="Nombre y Apellido" nombre="nombre" obligatorio>
                    <x-ui.input nombre="nombre" wire:model="nombre" maxlength="255" autocomplete="off" placeholder="Ej: García, Juan" />
                </x-ui.campo>

                <x-ui.campo etiqueta="DNI" nombre="dni" obligatorio>
                    <x-ui.input nombre="dni" wire:model="dni" maxlength="50" autocomplete="off" />
                </x-ui.campo>

                <x-ui.campo etiqueta="N° Empleado" nombre="numero_empleado" obligatorio>
                    <x-ui.input nombre="numero_empleado" wire:model="numero_empleado" maxlength="50" autocomplete="off" />
                </x-ui.campo>

                <x-ui.grupo etiqueta="Tipo de Licencia" nombre="licencia">
                    <x-ui.opciones nombre="licencia" wire:model="licencia" class="w-full"
                                   :opciones="['' => 'Sin especificar', 'PROFESIONAL' => 'Profesional', 'COMUN' => 'Común']" />
                </x-ui.grupo>

                <x-ui.campo etiqueta="Vencimiento Licencia" nombre="vencimiento_licencia">
                    <x-ui.input nombre="vencimiento_licencia" type="date" wire:model="vencimiento_licencia" />
                </x-ui.campo>
            </div>

            {{-- Categorías de licencia: grupo de casillas, va en fieldset + legend (no en x-ui.campo) --}}
            <fieldset class="w-full min-w-0">
                <legend class="block text-sm font-medium text-gray-700 mb-2">
                    Categorías
                    @if(count($categoriasSeleccionadas))
                        <span class="text-xs font-normal text-gray-500">({{ count($categoriasSeleccionadas) }} seleccionadas)</span>
                    @endif
                </legend>
                <div class="border border-gray-200 rounded-xl p-3 max-h-56 overflow-y-auto grid grid-cols-1 sm:grid-cols-2 gap-1">
                    @forelse($categoriasLicencia as $categoria)
                        <label class="flex items-start gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input
                                type="checkbox"
                                wire:model.live="categoriasSeleccionadas"
                                @error('categoriasSeleccionadas') aria-invalid="true" @enderror
                                value="{{ $categoria->id }}"
                                class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                            <span class="text-sm text-gray-700">
                                <span class="font-semibold">{{ $categoria->codigo }}</span>
                                <span class="text-gray-500">— {{ $categoria->descripcion }}</span>
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-gray-400">No hay categorías cargadas.</p>
                    @endforelse
                </div>
                @error('categoriasSeleccionadas') <p id="error-categoriasSeleccionadas" class="mt-1 text-xs text-red-600" data-error="categoriasSeleccionadas">{{ $message }}</p> @enderror
                @error('categoriasSeleccionadas.*') <p class="mt-1 text-xs text-red-600" data-error="categoriasSeleccionadas.*">{{ $message }}</p> @enderror
            </fieldset>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.campo etiqueta="Área" nombre="area">
                    <x-ui.input nombre="area" wire:model="area" maxlength="100" autocomplete="off" placeholder="Ej: Tránsito" />
                </x-ui.campo>

                <x-ui.campo etiqueta="Domicilio" nombre="domicilio">
                    <x-ui.input nombre="domicilio" wire:model="domicilio" maxlength="255" autocomplete="off" placeholder="Ej: 39 N°3285" />
                </x-ui.campo>

                @if($secretarias->count() > 0)
                    <x-ui.campo class="sm:col-span-2" etiqueta="Secretaría" nombre="secretaria_id">
                        <x-ui.combobox nombre="secretaria_id" wire:model="secretaria_id" placeholder="— Sin asignar —"
                                       vacio="— Sin asignar —" :opciones="$secretarias" etiqueta="secretaria" />
                    </x-ui.campo>
                @endif
            </div>

            <!-- Vehículos asignados -->
            <fieldset class="w-full min-w-0" x-data="{ busquedaVehiculo: '' }">
                <legend class="block text-sm font-medium text-gray-700 mb-2">Vehículos asignados</legend>
                <div class="relative mb-2">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        x-model="busquedaVehiculo"
                        placeholder="Buscar por patente o patrimonio..."
                        aria-label="Buscar vehículo por patente o patrimonio"
                        class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                    >
                </div>
                <div class="border border-gray-200 rounded-xl max-h-40 overflow-y-auto p-3 space-y-2">
                    @forelse($vehiculos as $vehiculo)
                        <label
                            class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 rounded-lg p-1"
                            x-show="busquedaVehiculo === '' || '{{ strtolower($vehiculo->patente . ' ' . $vehiculo->nro_patrimonio) }}'.includes(busquedaVehiculo.toLowerCase())"
                        >
                            <input
                                type="checkbox"
                                wire:model="vehiculosSeleccionados"
                                @error('vehiculosSeleccionados') aria-invalid="true" @enderror
                                value="{{ $vehiculo->id }}"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                            <span class="text-sm text-gray-700">
                                <span class="font-medium">Patrimonio {{ $vehiculo->nro_patrimonio }}</span>
                                @if($vehiculo->vehiculo) — {{ $vehiculo->vehiculo }}@endif
                                @if($vehiculo->patente) ({{ $vehiculo->patente }})@endif
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-gray-400">No hay vehículos registrados.</p>
                    @endforelse
                </div>
                @error('vehiculosSeleccionados') <p id="error-vehiculosSeleccionados" class="mt-1 text-xs text-red-600" data-error="vehiculosSeleccionados">{{ $message }}</p> @enderror
            </fieldset>

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
            ¿Está seguro de eliminar este chofer? Se eliminarán también sus asignaciones de vehículos.
        </x-ui.modal-confirmacion>
    @endif

    @if($confirmandoDescarte)
        <x-ui.modal-confirmacion tipo="advertencia" titulo="Cambios sin guardar" boton="Descartar cambios"
                                 confirmar="descartarCambios" cancelar="seguirEditando" :z="60">
            Hay cambios sin guardar. ¿Desea descartarlos?
        </x-ui.modal-confirmacion>
    @endif
</x-ui.pagina>
