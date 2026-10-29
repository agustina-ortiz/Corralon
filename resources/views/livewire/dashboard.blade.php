<x-ui.pagina titulo="Dashboard" icono="fa-house">
    <x-slot:acciones>
        <x-ui.boton variante="secundario" icono="fa-sliders" wire:click="abrirModalPersonalizar">Personalizar panel</x-ui.boton>
    </x-slot:acciones>

    <!-- Cards con estadísticas generales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        @if(in_array('card_insumos', $cardsActivas))
        <div class="bg-gradient-to-br from-white to-[#77BF43]/5 border border-gray-200 rounded-lg shadow-sm p-4 transform hover:shadow-xl hover:shadow-[#77BF43]/30 hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs font-medium uppercase tracking-wide">Total Insumos</p>
                    <p class="text-2xl font-bold mt-1 text-gray-800">{{ number_format($totalInsumos) }}</p>
                </div>
                <div class="w-12 h-12 flex items-center justify-center rounded-full bg-blue-50">
                    <i class="fa-solid fa-boxes-stacked text-xl text-blue-600" aria-hidden="true"></i>
                </div>
            </div>
        </div>
        @endif

        @if(in_array('card_maquinaria', $cardsActivas))
        <div class="bg-gradient-to-br from-white to-[#77BF43]/5 border border-gray-200 rounded-lg shadow-sm p-4 transform hover:shadow-xl hover:shadow-[#77BF43]/30 hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs font-medium uppercase tracking-wide">Maquinaria</p>
                    <p class="text-2xl font-bold mt-1 text-gray-800">{{ number_format($totalMaquinaria) }}</p>
                </div>
                <div class="w-12 h-12 flex items-center justify-center rounded-full bg-green-50">
                    <i class="fa-solid fa-screwdriver-wrench text-xl text-green-600" aria-hidden="true"></i>
                </div>
            </div>
        </div>
        @endif

        @if(in_array('card_vehiculos', $cardsActivas))
        <div class="bg-gradient-to-br from-white to-[#77BF43]/5 border border-gray-200 rounded-lg shadow-sm p-4 transform hover:shadow-xl hover:shadow-[#77BF43]/30 hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs font-medium uppercase tracking-wide">Vehículos</p>
                    <p class="text-2xl font-bold mt-1 text-gray-800">{{ number_format($totalVehiculos) }}</p>
                </div>
                <div class="w-12 h-12 flex items-center justify-center rounded-full bg-purple-50">
                    <i class="fa-solid fa-car text-xl text-purple-600" aria-hidden="true"></i>
                </div>
            </div>
        </div>
        @endif

        @if(in_array('card_eventos', $cardsActivas))
        <div class="bg-gradient-to-br from-white to-[#77BF43]/5 border border-gray-200 rounded-lg shadow-sm p-4 transform hover:shadow-xl hover:shadow-[#77BF43]/30 hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-xs font-medium uppercase tracking-wide">Próximos Eventos</p>
                    <p class="text-2xl font-bold mt-1 text-gray-800">{{ number_format($countProximosEventos) }}</p>
                </div>
                <div class="w-12 h-12 flex items-center justify-center rounded-full bg-indigo-50">
                    <i class="fa-regular fa-calendar-days text-xl text-indigo-600" aria-hidden="true"></i>
                </div>
            </div>
        </div>
        @endif

    </div>

    <!-- Sección de Estadísticas de Alerta -->
    <h2 class="mb-6 text-2xl font-bold text-gray-800">Estadísticas y Alertas</h2>

    @if($widgetsActivos)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        @if(in_array('stock_bajo', $widgetsActivos))
        <!-- Insumos con Stock Bajo -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-orange-50 to-orange-100 px-6 py-4 flex items-center justify-between border-b border-orange-200">
                <div class="flex items-center">
                    <i class="fa-solid fa-triangle-exclamation text-xl text-orange-600 mr-3" aria-hidden="true"></i>
                    <h3 class="text-lg font-semibold text-gray-800">Insumos con Stock Bajo</h3>
                </div>
                <span class="bg-orange-200 text-orange-800 font-bold px-3 py-1 rounded-full text-sm">
                    {{ $countInsumosBajoMinimo }}
                </span>
            </div>
            <div class="p-6">
                @if($insumosBajoMinimo->count() > 0)
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($insumosBajoMinimo as $insumo)
                            <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg border border-orange-100 hover:border-orange-200 hover:bg-orange-100 transition-colors">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800">{{ $insumo->insumo }}</p>
                                    <p class="text-sm text-gray-600">
                                        {{ $insumo->categoriaInsumo->categoria ?? 'Sin categoría' }} •
                                        {{ $insumo->deposito->deposito ?? 'Sin depósito' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $insumo->deposito->corralon->descripcion ?? 'Sin corralón' }}
                                    </p>
                                </div>
                                <div class="text-right ml-4">
                                    <div class="flex items-center justify-end space-x-2">
                                        <span class="text-2xl font-bold text-orange-600">{{ number_format($insumo->stock_actual, 2) }}</span>
                                        <span class="text-sm text-gray-500">{{ $insumo->unidad }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Mínimo: {{ number_format($insumo->stock_minimo, 2) }}</p>
                                    <p class="text-xs font-semibold text-orange-600">
                                        Faltan: {{ number_format($insumo->stock_minimo - $insumo->stock_actual, 2) }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-ui.vacio icono="fa-regular fa-circle-check">No hay insumos con stock bajo el mínimo</x-ui.vacio>
                @endif
            </div>
        </div>
        @endif

        @foreach($vencimientos as $v)
            @include('livewire.partials.dashboard-vencimiento', ['v' => $v])
        @endforeach

        @if(in_array('vehiculos_datos', $widgetsActivos))
            @include('livewire.partials.dashboard-contadores-vehiculos', [
                'titulo'       => 'Vehículos con Datos Incompletos',
                'subtitulo'    => 'Sobre ' . number_format($totalVehiculosActivos) . ' vehículos no dados de baja',
                'alertas'      => $vehiculosDatos,
                'total'        => $totalVehiculosActivos,
                'mensajeVacio' => 'Todos los vehículos tienen los datos completos',
                'icono'        => 'fa-clipboard-list',
            ])
        @endif

        @if(in_array('vehiculos_revisar', $widgetsActivos))
            @include('livewire.partials.dashboard-contadores-vehiculos', [
                'titulo'       => 'Vehículos a Revisar',
                'alertas'      => $vehiculosRevisar,
                'mensajeVacio' => 'No hay vehículos para revisar',
                'icono'        => 'fa-bullhorn',
            ])
        @endif

        @if(in_array('vehiculos_en_uso', $widgetsActivos))
        <!-- Vehículos en Uso -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-purple-50 to-purple-100 px-6 py-4 flex items-center justify-between border-b border-purple-200">
                <div class="flex items-center">
                    <i class="fa-solid fa-bolt text-xl text-purple-600 mr-3" aria-hidden="true"></i>
                    <h3 class="text-lg font-semibold text-gray-800">Vehículos en Uso</h3>
                </div>
                <span class="bg-purple-200 text-purple-800 font-bold px-3 py-1 rounded-full text-sm">
                    {{ $countVehiculosEnUso }}
                </span>
            </div>
            <div class="p-6">
                @if($vehiculosEnUso->count() > 0)
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($vehiculosEnUso as $vehiculo)
                            <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg border border-purple-100 hover:border-purple-200 hover:bg-purple-100 transition-colors">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="bg-purple-600 text-white px-2 py-1 rounded text-xs font-bold">
                                            {{ $vehiculo->nro_patrimonio }}
                                        </span>
                                        <p class="font-semibold text-gray-800">{{ $vehiculo->vehiculo }}</p>
                                    </div>
                                    @if($vehiculo->marca_modelo)
                                        <p class="text-sm text-gray-600 mt-1">{{ $vehiculo->marca_modelo }}</p>
                                    @endif
                                    @if($vehiculo->patente)
                                        <p class="text-xs text-gray-500 mt-1">Patente: {{ $vehiculo->patente }}</p>
                                    @endif
                                    <p class="text-xs text-gray-500">
                                        {{ $vehiculo->secretaria->secretaria ?? 'Sin secretaría' }}
                                    </p>
                                </div>
                                <div class="text-right ml-4">
                                    <x-ui.badge color="violeta">En Uso</x-ui.badge>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-ui.vacio icono="fa-car">No hay vehículos en uso actualmente</x-ui.vacio>
                @endif
            </div>
        </div>
        @endif

        @if(in_array('proximos_eventos', $widgetsActivos))
        <!-- Próximos Eventos -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-indigo-50 to-indigo-100 px-6 py-4 flex items-center justify-between border-b border-indigo-200">
                <div class="flex items-center">
                    <i class="fa-regular fa-calendar-days text-xl text-indigo-600 mr-3" aria-hidden="true"></i>
                    <h3 class="text-lg font-semibold text-gray-800">Próximos Eventos</h3>
                </div>
                <span class="bg-indigo-200 text-indigo-800 font-bold px-3 py-1 rounded-full text-sm">
                    {{ $countProximosEventosWidget }}
                </span>
            </div>
            <div class="p-6">
                @if($proximosEventos->count() > 0)
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($proximosEventos as $evento)
                            <div class="flex items-start p-3 bg-indigo-50 rounded-lg border border-indigo-100 hover:border-indigo-200 hover:bg-indigo-100 transition-colors">
                                <div class="flex-shrink-0 mr-3">
                                    <div class="bg-indigo-600 text-white rounded-lg p-2 text-center min-w-[60px]">
                                        <p class="text-2xl font-bold">{{ $evento->fecha->format('d') }}</p>
                                        <p class="text-xs uppercase">{{ $evento->fecha->format('M') }}</p>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800">{{ $evento->evento }}</p>
                                    @if($evento->ubicacion)
                                        <p class="text-sm text-gray-600 mt-1">
                                            {{ $evento->ubicacion }}
                                        </p>
                                    @endif
                                    @if($evento->secretaria)
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $evento->secretaria }}
                                        </p>
                                    @endif
                                    <p class="text-xs text-indigo-600 font-medium mt-1">
                                        {{ $evento->fecha->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-ui.vacio icono="fa-regular fa-calendar">No hay eventos programados</x-ui.vacio>
                @endif
            </div>
        </div>
        @endif

    </div>
    @else
    <x-ui.vacio icono="fa-table-cells-large" class="py-16">
        No hay widgets activos
        <span class="block mt-1 font-normal">Usá "Personalizar panel" para activar estadísticas.</span>
    </x-ui.vacio>
    @endif

    <!-- Modal Personalizar Panel -->
    @if($modalPersonalizar)
        <x-ui.modal titulo="Personalizar panel" icono="fa-sliders" cerrar="intentarCerrar" ancho="md">
            @if($opcionesCards)
            <fieldset class="w-full min-w-0 mb-5">
                <legend class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Tarjetas de resumen</legend>
                <div class="space-y-2">
                    @foreach($opcionesCards as $key => $card)
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:border-[#77BF43]/40 hover:bg-[#77BF43]/5 cursor-pointer transition-colors">
                        <input
                            type="checkbox"
                            wire:model="seleccionCards"
                            value="{{ $key }}"
                            class="w-4 h-4 rounded text-[#77BF43] border-gray-300 focus:ring-[#77BF43]"
                        >
                        <span class="text-sm font-medium text-gray-700">{{ $card['label'] }}</span>
                    </label>
                    @endforeach
                </div>
            </fieldset>
            @endif

            @if($opcionesWidgets)
            <fieldset class="w-full min-w-0">
                <legend class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Estadísticas y Alertas</legend>
                <div class="space-y-2">
                    @foreach($opcionesWidgets as $key => $widget)
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:border-[#77BF43]/40 hover:bg-[#77BF43]/5 cursor-pointer transition-colors">
                        <input
                            type="checkbox"
                            wire:model="seleccionWidgets"
                            value="{{ $key }}"
                            class="w-4 h-4 rounded text-[#77BF43] border-gray-300 focus:ring-[#77BF43]"
                        >
                        <span class="text-sm font-medium text-gray-700">{{ $widget['label'] }}</span>
                    </label>
                    @endforeach
                </div>
            </fieldset>
            @endif

            <x-slot:footer>
                <x-ui.boton variante="secundario" wire:click="intentarCerrar">Cancelar</x-ui.boton>
                <x-ui.boton wire:click="guardarPreferencias" data-atajo-guardar
                            wire:loading.attr="disabled" wire:target="guardarPreferencias">
                    Guardar
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
</x-ui.pagina>
