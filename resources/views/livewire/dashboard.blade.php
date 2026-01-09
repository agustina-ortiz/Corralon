<div>
    <!-- Cards con estadísticas generales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Card: Total Insumos -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium uppercase tracking-wide">Total Insumos</p>
                    <p class="text-4xl font-bold mt-2">{{ number_format($totalInsumos) }}</p>
                </div>
                <div class="p-4 rounded-full bg-white bg-opacity-20">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
        </div>
    
        <!-- Card: Maquinaria -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium uppercase tracking-wide">Maquinaria</p>
                    <p class="text-4xl font-bold mt-2">{{ number_format($totalMaquinaria) }}</p>
                </div>
                <div class="p-4 rounded-full bg-white bg-opacity-20">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                </div>
            </div>
        </div>
    
        <!-- Card: Vehículos -->
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium uppercase tracking-wide">Vehículos</p>
                    <p class="text-4xl font-bold mt-2">{{ number_format($totalVehiculos) }}</p>
                </div>
                <div class="p-4 rounded-full bg-white bg-opacity-20">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sección de Estadísticas de Alerta -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">📊 Estadísticas y Alertas</h2>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Cuadrante 1: Insumos con Stock Bajo -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-white">Insumos con Stock Bajo</h3>
                </div>
                <span class="bg-white text-red-600 font-bold px-3 py-1 rounded-full text-sm">
                    {{ $countInsumosBajoMinimo }}
                </span>
            </div>
            <div class="p-6">
                @if($insumosBajoMinimo->count() > 0)
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($insumosBajoMinimo as $insumo)
                            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200 hover:bg-red-100 transition-colors">
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
                                        <span class="text-2xl font-bold text-red-600">{{ number_format($insumo->stock_actual, 2) }}</span>
                                        <span class="text-sm text-gray-500">{{ $insumo->unidad }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Mínimo: {{ number_format($insumo->stock_minimo, 2) }}</p>
                                    <p class="text-xs font-semibold text-red-600">
                                        Faltan: {{ number_format($insumo->stock_minimo - $insumo->stock_actual, 2) }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="font-medium">No hay insumos con stock bajo el mínimo</p>
                    </div>
                @endif
            </div>
        </div>
    
        <!-- Cuadrante 2: Maquinaria No Disponible -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-white">Maquinaria No Disponible</h3>
                </div>
                <span class="bg-white text-yellow-600 font-bold px-3 py-1 rounded-full text-sm">
                    {{ $countMaquinariaNoDisponible }}
                </span>
            </div>
            <div class="p-6">
                @if($maquinariaNoDisponible->count() > 0)
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($maquinariaNoDisponible as $maquina)
                            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200 hover:bg-yellow-100 transition-colors">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800">{{ $maquina->maquinaria }}</p>
                                    <p class="text-sm text-gray-600">
                                        {{ $maquina->categoriaMaquinaria->categoria ?? 'Sin categoría' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $maquina->deposito->deposito ?? 'Sin depósito' }} • 
                                        {{ $maquina->deposito->corralon->descripcion ?? 'Sin corralón' }}
                                    </p>
                                </div>
                                <div class="text-right ml-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                                        @if($maquina->estado === 'En uso') bg-orange-100 text-orange-800
                                        @elseif($maquina->estado === 'En mantenimiento') bg-blue-100 text-blue-800
                                        @elseif($maquina->estado === 'Averiada') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $maquina->estado }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="font-medium">Toda la maquinaria está disponible</p>
                    </div>
                @endif
            </div>
        </div>
    
        <!-- Cuadrante 3: Vehículos en Uso -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-white">Vehículos en Uso</h3>
                </div>
                <span class="bg-white text-purple-600 font-bold px-3 py-1 rounded-full text-sm">
                    {{ $countVehiculosEnUso }}
                </span>
            </div>
            <div class="p-6">
                @if($vehiculosEnUso->count() > 0)
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($vehiculosEnUso as $vehiculo)
                            <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg border border-purple-200 hover:bg-purple-100 transition-colors">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="bg-purple-600 text-white px-2 py-1 rounded text-xs font-bold">
                                            {{ $vehiculo->nro_movil }}
                                        </span>
                                        <p class="font-semibold text-gray-800">{{ $vehiculo->vehiculo }}</p>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">
                                        @if($vehiculo->marca){{ $vehiculo->marca }}@endif
                                        @if($vehiculo->marca && $vehiculo->modelo) - @endif
                                        @if($vehiculo->modelo){{ $vehiculo->modelo }}@endif
                                    </p>
                                    @if($vehiculo->patente)
                                        <p class="text-xs text-gray-500 mt-1">Patente: {{ $vehiculo->patente }}</p>
                                    @endif
                                    <p class="text-xs text-gray-500">
                                        {{ $vehiculo->deposito->deposito ?? 'Sin depósito' }} • 
                                        {{ $vehiculo->deposito->corralon->descripcion ?? 'Sin corralón' }}
                                    </p>
                                </div>
                                <div class="text-right ml-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                        En Uso
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="font-medium">No hay vehículos en uso actualmente</p>
                    </div>
                @endif
            </div>
        </div>
    
        <!-- Cuadrante 4: Próximos Eventos -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-white">Próximos Eventos</h3>
                </div>
                <span class="bg-white text-indigo-600 font-bold px-3 py-1 rounded-full text-sm">
                    {{ $countProximosEventos }}
                </span>
            </div>
            <div class="p-6">
                @if($proximosEventos->count() > 0)
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($proximosEventos as $evento)
                            <div class="flex items-start p-3 bg-indigo-50 rounded-lg border border-indigo-200 hover:bg-indigo-100 transition-colors">
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
                                            📍 {{ $evento->ubicacion }}
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
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="font-medium">No hay eventos programados</p>
                    </div>
                @endif
            </div>
        </div>
    
    </div>
</div>