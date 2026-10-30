<x-ui.pagina titulo="Vehículos" icono="fa-car">
    <x-slot:acciones>
        @if($puedeCrear)
            <x-ui.boton icono="fa-plus" wire:click="crear">Nuevo Vehículo</x-ui.boton>
        @endif
    </x-slot:acciones>

    <!-- Búsqueda y filtros -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
        <div class="flex-1 max-w-md">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input
                    type="text"
                    wire:model.live="search"
                    placeholder="Buscar por nro. patrimonio, vehículo, marca/modelo, patente..."
                    class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                >
            </div>
        </div>
        <div class="flex gap-3">
            <x-ui.boton variante="secundario" icono="fa-filter" wire:click="$toggle('showFilters')">
                Filtros
                @if($filtro_marca_modelo || $filtro_estado || $filtro_secretaria)
                    <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-blue-600 rounded-full">
                        {{ collect([$filtro_marca_modelo, $filtro_estado, $filtro_secretaria])->filter()->count() }}
                    </span>
                @endif
            </x-ui.boton>
        </div>
    </div>

    <!-- Panel de Filtros con animación suave -->
    <div
        x-data="{ show: @entangle('showFilters') }"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-4"
        class="mb-6 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
        style="display: none;"
    >
        <div class="px-6 py-5">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filtros Avanzados
                </h3>
                @if($filtro_marca_modelo || $filtro_estado || $filtro_secretaria)
                    <button
                        wire:click="limpiarFiltros"
                        class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Limpiar filtros
                    </button>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Filtro Marca/Modelo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Marca/Modelo</label>
                    <select
                        wire:model.live="filtro_marca_modelo"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                    >
                        <option value="">Todas las marcas/modelos</option>
                        @foreach($marcasModelos as $mm)
                            <option value="{{ $mm }}">{{ $mm }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Estado -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select
                        wire:model.live="filtro_estado"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                    >
                        <option value="">Todos los estados</option>
                        <option value="EN USO">En Uso</option>
                        <option value="BAJA">Baja</option>
                        <option value="MANTENIMIENTO">Mantenimiento</option>
                    </select>
                </div>

                <!-- Filtro Secretaría -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Secretaría</label>
                    <select
                        wire:model.live="filtro_secretaria"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                    >
                        <option value="">Todas las secretarías</option>
                        @foreach($secretarias as $secretaria)
                            <option value="{{ $secretaria->id }}">{{ $secretaria->secretaria }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerta del dashboard aplicada como filtro -->
    @if($alertaActiva)
        <div class="mb-6 px-4 py-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span class="text-sm">
                    Mostrando vehículos con alerta: <span class="font-semibold">{{ $alertaActiva['label'] }}</span>
                    <span class="text-amber-600">(no dados de baja)</span>
                </span>
            </div>
            <button wire:click="quitarAlerta" class="text-sm font-medium text-amber-700 hover:text-amber-900 flex items-center gap-1 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Quitar
            </button>
        </div>
    @endif

    <!-- Tabla -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nro. Patrimonio</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Vehículo</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Patente</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Secretaría</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Vencimiento VTV</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Documentos</th>
                        @if($puedeEditar || $puedeEliminar)
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse ($vehiculos as $item)
                        <tr class="hover:bg-gray-50/50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $item->nro_patrimonio ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $item->vehiculo }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $item->patente ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $estadoColores = [
                                        'EN USO' => 'azul',
                                        'BAJA' => 'rojo',
                                        'MANTENIMIENTO' => 'ambar',
                                    ];
                                    $estadoNombres = [
                                        'EN USO' => 'En Uso',
                                        'BAJA' => 'Baja',
                                        'MANTENIMIENTO' => 'Mantenimiento',
                                    ];
                                @endphp
                                <x-ui.badge :color="$estadoColores[$item->estado] ?? 'gris'">{{ $estadoNombres[$item->estado] ?? $item->estado }}</x-ui.badge>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <x-ui.badge color="azul">{{ $item->secretaria?->secretaria ?? 'Sin secretaría' }}</x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                @if($item->vencimiento_vtv)
                                    {{ \Carbon\Carbon::parse($item->vencimiento_vtv)->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center">
                                    <x-ui.boton variante="secundario" tamano="chico" icono="fa-regular fa-file-lines"
                                                wire:click="abrirModalDocumentos({{ $item->id }})"
                                                title="Ver documentos" aria-label="Ver documentos ({{ $item->documentos_count }})">
                                        <x-ui.badge :color="$item->documentos_count > 0 ? 'azul' : 'gris'">{{ $item->documentos_count }}</x-ui.badge>
                                    </x-ui.boton>
                                </div>
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
                                <x-ui.vacio icono="fa-car">{{ $search || $filtro_marca_modelo || $filtro_estado || $filtro_secretaria || $alertaActiva ? 'No hay vehículos que coincidan con la búsqueda o los filtros' : 'Todavía no hay vehículos cargados' }}</x-ui.vacio>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
            {{ $vehiculos->links() }}
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <x-ui.modal :titulo="$editMode ? 'Editar Vehículo' : 'Nuevo Vehículo'"
                    :icono="$editMode ? 'fa-pen' : 'fa-plus'"
                    cerrar="intentarCerrar" ancho="4xl" submit="guardar">
            <!-- Sección: Información Básica -->
            <div class="bg-gray-50 p-4 rounded-xl">
                <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                    Información Básica
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.campo etiqueta="Nro. Patrimonio" nombre="nro_patrimonio" obligatorio>
                        <x-ui.input nombre="nro_patrimonio" wire:model="nro_patrimonio" maxlength="50" autocomplete="off" placeholder="Ej: 12345" />
                    </x-ui.campo>

                    <x-ui.campo etiqueta="Vehículo" nombre="vehiculo" obligatorio>
                        <x-ui.input nombre="vehiculo" wire:model="vehiculo" maxlength="255" autocomplete="off" placeholder="Ej: Camioneta" />
                    </x-ui.campo>

                    <x-ui.campo etiqueta="Marca/Modelo" nombre="marca_modelo" obligatorio>
                        <x-ui.input nombre="marca_modelo" wire:model="marca_modelo" maxlength="255" autocomplete="off" placeholder="Ej: Toyota Hilux" />
                    </x-ui.campo>

                    <x-ui.campo etiqueta="Año" nombre="anio">
                        <x-ui.input nombre="anio" wire:model="anio" maxlength="4" autocomplete="off" placeholder="Ej: 2017" />
                    </x-ui.campo>

                    <x-ui.campo etiqueta="Patente" nombre="patente" obligatorio>
                        <x-ui.input nombre="patente" wire:model="patente" maxlength="20" autocomplete="off" placeholder="Ej: ABC123" />
                    </x-ui.campo>

                    <x-ui.grupo etiqueta="Tipo de Combustible" nombre="tipo_combustible" obligatorio>
                        <x-ui.opciones nombre="tipo_combustible" wire:model.live="tipo_combustible" class="w-full"
                                       :opciones="['nafta' => 'Nafta', 'diesel' => 'Diesel', 'gas' => 'Gas']" />
                    </x-ui.grupo>

                    {{-- Vencimiento Oblea: solo si es GAS --}}
                    @if($tipo_combustible === 'gas')
                        <x-ui.campo etiqueta="Vencimiento Oblea" nombre="vencimiento_oblea" obligatorio>
                            <x-ui.input nombre="vencimiento_oblea" type="date" wire:model="vencimiento_oblea" />
                        </x-ui.campo>
                    @endif
                </div>
            </div>

            <!-- Sección: Identificación Técnica -->
            <div class="bg-gray-50 p-4 rounded-xl">
                <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-gears" aria-hidden="true"></i>
                    Identificación Técnica
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.campo etiqueta="Nro. Motor" nombre="nro_motor" obligatorio>
                        <x-ui.input nombre="nro_motor" wire:model="nro_motor" maxlength="255" autocomplete="off" placeholder="Número de motor" />
                    </x-ui.campo>

                    <x-ui.campo etiqueta="Nro. Chasis" nombre="nro_chasis" obligatorio>
                        <x-ui.input nombre="nro_chasis" wire:model="nro_chasis" maxlength="255" autocomplete="off" placeholder="Número de chasis" />
                    </x-ui.campo>
                </div>
            </div>

            <!-- Sección: Seguro y Vencimientos -->
            <div class="bg-gray-50 p-4 rounded-xl">
                <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                    Seguro y Vencimientos
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.campo etiqueta="Nro. Póliza" nombre="nro_poliza" obligatorio>
                        <x-ui.input nombre="nro_poliza" wire:model="nro_poliza" maxlength="255" autocomplete="off" placeholder="Número de póliza" />
                    </x-ui.campo>

                    <x-ui.campo etiqueta="Vencimiento Póliza" nombre="vencimiento_poliza">
                        <x-ui.input nombre="vencimiento_poliza" type="date" wire:model="vencimiento_poliza" />
                    </x-ui.campo>

                    <x-ui.campo etiqueta="Vencimiento VTV" nombre="vencimiento_vtv">
                        <x-ui.input nombre="vencimiento_vtv" type="date" wire:model="vencimiento_vtv" />
                    </x-ui.campo>
                </div>
            </div>

            <!-- Sección: Origen y Telepase -->
            <div class="bg-gray-50 p-4 rounded-xl">
                <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-earth-americas" aria-hidden="true"></i>
                    Origen y Telepase
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <x-ui.campo etiqueta="Origen" nombre="origen">
                        <x-ui.input nombre="origen" wire:model="origen" maxlength="255" placeholder="Ej: Nacional" />
                    </x-ui.campo>

                    <x-ui.campo etiqueta="Jurisdicción de Procedencia" nombre="jurisdiccion_procedencia">
                        <x-ui.input nombre="jurisdiccion_procedencia" wire:model="jurisdiccion_procedencia" maxlength="255" placeholder="Ej: Buenos Aires" />
                    </x-ui.campo>

                    <x-ui.campo etiqueta="Nro. Telepase" nombre="nro_telepase">
                        <x-ui.input nombre="nro_telepase" wire:model="nro_telepase" maxlength="255" autocomplete="off" placeholder="Número de telepase" />
                    </x-ui.campo>
                </div>
            </div>

            <!-- Sección: Estado y Ubicación -->
            <div class="bg-gray-50 p-4 rounded-xl">
                <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                    Estado y Ubicación
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.grupo etiqueta="Estado" nombre="estado" obligatorio>
                        <x-ui.opciones nombre="estado" wire:model="estado" class="w-full"
                                       :opciones="['EN USO' => 'En Uso', 'BAJA' => 'Baja', 'MANTENIMIENTO' => 'Mantenimiento']" />
                    </x-ui.grupo>

                    <x-ui.campo etiqueta="Secretaría" nombre="id_secretaria">
                        <x-ui.combobox nombre="id_secretaria" wire:model="id_secretaria" placeholder="Seleccione una secretaría"
                                       :opciones="$secretarias" etiqueta="secretaria" />
                    </x-ui.campo>

                    <x-ui.campo etiqueta="Depósito" nombre="id_deposito">
                        <x-ui.combobox nombre="id_deposito" wire:model="id_deposito" placeholder="Seleccione un depósito" :ordenar="false"
                                       :opciones="$depositos->mapWithKeys(fn ($d) => [$d->id => $d->deposito.($d->corralon ? ' — '.$d->corralon->descripcion : '')])" />
                    </x-ui.campo>
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

    <!-- Modal de Documentos -->
    @if($showModalDocumentos)
        <x-ui.modal titulo="Documentos del Vehículo" icono="fa-folder-open" cerrar="intentarCerrar" ancho="3xl">
            @if($vehiculoSeleccionado)
                <x-slot:encabezado>
                    <span class="text-sm text-gray-500 font-normal">{{ $vehiculoSeleccionado->vehiculo }} - {{ $vehiculoSeleccionado->marca_modelo }}@if($vehiculoSeleccionado->patente) ({{ $vehiculoSeleccionado->patente }})@endif</span>
                </x-slot:encabezado>
            @endif

            <!-- Agregar nuevo documento -->
            @if($puedeEditar)
            <div class="mb-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-plus text-blue-600" aria-hidden="true"></i>
                    Agregar Nuevo Documento
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.campo etiqueta="Descripción" nombre="nueva_descripcion" obligatorio>
                        <x-ui.input nombre="nueva_descripcion" wire:model="nueva_descripcion" maxlength="255" autocomplete="off" placeholder="Ej: Título del vehículo" />
                    </x-ui.campo>

                    <x-ui.campo etiqueta="Archivo" nombre="nuevo_documento" obligatorio ayuda="PDF, JPG, JPEG o PNG. Máximo 10MB.">
                        <x-ui.input nombre="nuevo_documento" type="file" wire:model="nuevo_documento" accept=".pdf,.jpg,.jpeg,.png"
                                    class="text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                        <div wire:loading wire:target="nuevo_documento" class="mt-2 text-sm text-blue-600">Cargando archivo...</div>

                        {{-- Preview del archivo seleccionado antes de subir --}}
                        @if($nuevo_documento)
                            @php
                                $nombrePrev = $nuevo_documento->getClientOriginalName();
                                $extPrev = strtolower(pathinfo($nombrePrev, PATHINFO_EXTENSION));
                                $esImagenPrev = in_array($extPrev, ['jpg', 'jpeg', 'png']);
                            @endphp
                            <div class="relative mt-3 inline-block border border-gray-200 rounded-xl overflow-hidden bg-gray-50 w-40">
                                <button type="button" wire:click="quitarNuevoDocumento" title="Quitar archivo" aria-label="Quitar archivo"
                                        class="absolute top-1 right-1 z-10 bg-red-600 hover:bg-red-700 text-white rounded-full w-6 h-6 flex items-center justify-center shadow">
                                    <i class="fa-solid fa-xmark text-xs" aria-hidden="true"></i>
                                </button>
                                @if($esImagenPrev)
                                    <img src="{{ $nuevo_documento->temporaryUrl() }}" alt="{{ $nombrePrev }}" class="w-full h-24 object-cover">
                                @else
                                    <div class="w-full h-24 flex items-center justify-center bg-red-50">
                                        <i class="fa-regular fa-file-pdf text-4xl text-red-500" aria-hidden="true"></i>
                                    </div>
                                @endif
                                <p class="px-2 py-1 text-[11px] text-gray-600 truncate" title="{{ $nombrePrev }}">{{ $nombrePrev }}</p>
                            </div>
                        @endif
                    </x-ui.campo>
                </div>

                <div class="mt-4 flex items-center justify-end">
                    <x-ui.boton icono="fa-plus" wire:click="agregarDocumento" data-atajo-guardar
                                wire:loading.attr="disabled" wire:target="nuevo_documento">
                        <span wire:loading.remove wire:target="nuevo_documento">Agregar</span>
                        <span wire:loading wire:target="nuevo_documento">Cargando...</span>
                    </x-ui.boton>
                </div>
            </div>
            @endif

            <!-- Lista de documentos existentes -->
            <div>
                @if(count($documentos_existentes) > 0)
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Documentos Actuales ({{ count($documentos_existentes) }})</h4>
                    <div class="space-y-2">
                        @foreach($documentos_existentes as $doc)
                            <div class="flex items-center gap-3 p-4 bg-white border border-gray-200 rounded-xl hover:border-blue-300 transition-colors">
                                {{-- Icono según tipo de archivo --}}
                                @php
                                    $extension = strtolower(pathinfo($doc['archivo'], PATHINFO_EXTENSION));
                                    $isPdf = $extension === 'pdf';
                                @endphp

                                @if($isPdf)
                                    <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                        <i class="fa-regular fa-file-pdf text-xl text-red-600" aria-hidden="true"></i>
                                    </div>
                                @else
                                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fa-regular fa-file-image text-xl text-blue-600" aria-hidden="true"></i>
                                    </div>
                                @endif

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $doc['descripcion'] }}</p>
                                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($doc['created_at'])->format('d/m/Y H:i') }}</p>
                                </div>

                                <div class="flex items-center gap-1">
                                    <x-ui.accion icono="fa-regular fa-eye" etiqueta="Ver documento"
                                                 :href="route('documentos-vehiculo.ver', $doc['id'])" target="_blank" />
                                    <x-ui.accion icono="fa-download" etiqueta="Descargar documento"
                                                 :href="route('documentos-vehiculo.descargar', $doc['id'])" :navegar="false" />
                                    @if($puedeEditar)
                                    <x-ui.accion icono="fa-regular fa-trash-can" color="peligro" etiqueta="Eliminar documento"
                                                 wire:click="confirmarEliminarDocumento({{ $doc['id'] }})" />
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-ui.vacio icono="fa-regular fa-file-lines" class="py-10">
                        No hay documentos cargados
                        @if($puedeEditar)
                            <span class="block mt-1 text-xs font-normal">Agregue documentos con el formulario de arriba.</span>
                        @endif
                    </x-ui.vacio>
                @endif
            </div>

            <x-slot:footer>
                <x-ui.boton variante="secundario" wire:click="intentarCerrar">Cerrar</x-ui.boton>
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
            ¿Está seguro de eliminar este vehículo?
        </x-ui.modal-confirmacion>
    @endif

    @if($eliminarDocumentoId)
        <x-ui.modal-confirmacion confirmar="eliminarDocumento" cancelar="cancelarEliminarDocumento" :z="60">
            ¿Está seguro de eliminar este documento?
        </x-ui.modal-confirmacion>
    @endif
</x-ui.pagina>
