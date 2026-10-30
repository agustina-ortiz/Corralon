<x-ui.pagina titulo="Estadísticas" icono="fa-chart-pie">
    <x-slot:acciones>
        <x-ui.boton variante="secundario" icono="fa-regular fa-file-pdf" wire:click="exportarPdf"
                    wire:loading.attr="disabled" wire:target="exportarPdf"
                    title="Descargar los gráficos visibles en PDF">
            <span wire:loading.remove wire:target="exportarPdf">Imprimir PDF</span>
            <span wire:loading wire:target="exportarPdf">Generando…</span>
        </x-ui.boton>
        <x-ui.boton variante="secundario" icono="fa-sliders" wire:click="abrirModalPersonalizar">Personalizar</x-ui.boton>
    </x-slot:acciones>

    <!-- Panel de filtros -->
    @php
        $filtrosActivos = collect([
            $filtro_corralon, $filtro_deposito, $fecha_desde, $fecha_hasta,
            $filtro_categoria_insumo, $filtro_categoria_maquinaria, $filtro_tipo_movimiento,
            $filtro_tipo_destino, $filtro_destino_id, $filtro_area,
        ])->filter()->count();
    @endphp
    {{-- Sin overflow-hidden: recortaría el desplegable de tipos de movimiento --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6">
        <!-- Encabezado -->
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white rounded-t-2xl">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#77BF43]/10 text-[#77BF43]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                </span>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 leading-tight flex items-center gap-2">
                        Filtros
                        @if($filtrosActivos > 0)
                            <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 text-[11px] font-bold text-white bg-[#77BF43] rounded-full">{{ $filtrosActivos }}</span>
                        @endif
                    </h3>
                    <p class="text-xs text-gray-400">Acotá las estadísticas por ubicación, período, categoría y tipo de movimiento</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span wire:loading class="flex items-center gap-1.5 text-xs text-[#77BF43] font-medium mr-1">
                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Actualizando…
                </span>
                @if($filtrosActivos > 0)
                    <x-ui.boton variante="secundario" tamano="chico" icono="fa-xmark" wire:click="limpiarFiltros">Limpiar</x-ui.boton>
                @endif
            </div>
        </div>

        <!-- Cuerpo: campos de filtro -->
        <div class="px-6 py-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <x-ui.campo etiqueta="Corralón" nombre="filtro_corralon">
                    <x-ui.combobox nombre="filtro_corralon" wire:model.live="filtro_corralon" :opciones="$corralones" etiqueta="descripcion"
                                   vacio="Todos los corralones" placeholder="Todos los corralones" />
                </x-ui.campo>
                <x-ui.campo etiqueta="Depósito" nombre="filtro_deposito">
                    <x-ui.combobox nombre="filtro_deposito" wire:model.live="filtro_deposito"
                                   :opciones="$depositos->mapWithKeys(fn ($d) => [$d->id => $d->deposito.' — '.($d->corralon->descripcion ?? 's/c')])->all()"
                                   vacio="Todos los depósitos" placeholder="Todos los depósitos" />
                </x-ui.campo>
                <x-ui.campo etiqueta="Desde" nombre="fecha_desde">
                    <x-ui.input nombre="fecha_desde" type="date" wire:model.live="fecha_desde" />
                </x-ui.campo>
                <x-ui.campo etiqueta="Hasta" nombre="fecha_hasta">
                    <x-ui.input nombre="fecha_hasta" type="date" wire:model.live="fecha_hasta" />
                </x-ui.campo>
                <x-ui.campo etiqueta="Categoría de insumo" nombre="filtro_categoria_insumo">
                    <x-ui.combobox nombre="filtro_categoria_insumo" wire:model.live="filtro_categoria_insumo" :opciones="$categoriasInsumos"
                                   vacio="Todas las categorías" placeholder="Todas las categorías" />
                </x-ui.campo>
                <x-ui.campo etiqueta="Categoría de maquinaria" nombre="filtro_categoria_maquinaria">
                    <x-ui.combobox nombre="filtro_categoria_maquinaria" wire:model.live="filtro_categoria_maquinaria" :opciones="$categoriasMaquinarias"
                                   vacio="Todas las categorías" placeholder="Todas las categorías" />
                </x-ui.campo>
                {{-- Selección múltiple: casillas en un desplegable propio (el combobox elige uno solo) --}}
                <div>
                    <span id="rotulo-filtro_tipo_movimiento" class="block text-sm font-medium text-gray-700 mb-2">Tipo de movimiento</span>
                    @php
                        $tiposSeleccionados = collect($filtro_tipo_movimiento)->filter()->map(fn($v) => (int) $v);
                        $nombresSeleccionados = $tiposMovimiento->whereIn('id', $tiposSeleccionados->all())->pluck('tipo_movimiento');
                    @endphp
                    <div class="relative" x-data="{ abierto: false }" @click.outside="abierto = false" @keydown.escape.window="abierto = false">
                        <button type="button" @click="abierto = !abierto" aria-labelledby="rotulo-filtro_tipo_movimiento" :aria-expanded="abierto"
                                class="{{ \App\Support\Tema::controlCombobox() }}">
                            <span class="flex-1 truncate {{ $nombresSeleccionados->count() ? '' : 'text-gray-400' }}">
                                @if($nombresSeleccionados->count() === 0)
                                    Todos los tipos
                                @elseif($nombresSeleccionados->count() === 1)
                                    {{ $nombresSeleccionados->first() }}
                                @else
                                    {{ $nombresSeleccionados->count() }} tipos seleccionados
                                @endif
                            </span>
                            <i class="fa-solid fa-chevron-down shrink-0 text-xs text-gray-400 transition-transform" :class="abierto && 'rotate-180'" aria-hidden="true"></i>
                        </button>

                        <div x-show="abierto" x-transition.opacity style="display: none"
                             class="absolute z-30 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                            <div class="flex items-center justify-between px-3 py-2 border-b border-gray-100 bg-gray-50">
                                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Elegí uno o varios</span>
                                @if($nombresSeleccionados->count() > 0)
                                    <button type="button" wire:click="limpiarTiposMovimiento" class="text-[11px] font-medium text-gray-500 hover:text-gray-700">
                                        Limpiar
                                    </button>
                                @endif
                            </div>
                            <div class="max-h-60 overflow-y-auto py-1">
                                @forelse($tiposMovimiento as $tm)
                                    <label class="flex items-center gap-2.5 px-3 py-2 text-sm text-gray-700 hover:bg-[#77BF43]/5 cursor-pointer">
                                        <input type="checkbox" wire:model.live="filtro_tipo_movimiento" value="{{ $tm->id }}"
                                               class="w-4 h-4 rounded text-[#77BF43] border-gray-300 focus:ring-[#77BF43]">
                                        <span class="truncate" title="{{ $tm->tipo_movimiento }}">{{ $tm->tipo_movimiento }}</span>
                                    </label>
                                @empty
                                    <p class="px-3 py-3 text-sm text-gray-400">No hay tipos disponibles</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    @if($nombresSeleccionados->count() > 1)
                        <div class="flex flex-wrap gap-1 mt-2">
                            @foreach($nombresSeleccionados as $nombre)
                                <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium text-[#4a7a2a] bg-[#77BF43]/10 rounded-full">{{ $nombre }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
                <x-ui.campo etiqueta="Destino — Tipo" nombre="filtro_tipo_destino">
                    <x-ui.combobox nombre="filtro_tipo_destino" wire:model.live="filtro_tipo_destino" :opciones="$tiposDestino" :ordenar="false"
                                   vacio="Todos los destinos" placeholder="Todos los destinos" />
                </x-ui.campo>
                @if($filtro_tipo_destino)
                    @php
                        // Resultados de la búsqueda (hasta 50) + el elegido, para que el botón lo muestre aunque no esté entre ellos.
                        $opcionesRegistro = $opcionesDestino->map(fn ($op) => ['id' => $op['id'], 'nombre' => $op['label'], 'detalle' => $op['detalle'] ?: null]);
                        if ($filtro_destino_id !== '' && ! $opcionesRegistro->contains(fn ($op) => (string) $op['id'] === (string) $filtro_destino_id)) {
                            $opcionesRegistro->prepend(['id' => $filtro_destino_id, 'nombre' => $destinoLabel]);
                        }
                    @endphp
                    <x-ui.campo :etiqueta="$tiposDestino[$filtro_tipo_destino]" nombre="filtro_destino_id"
                                :ayuda="$opcionesDestino->count() >= 50 ? 'Se muestran los primeros 50: refiná la búsqueda.' : null">
                        <x-ui.combobox nombre="filtro_destino_id" wire:model.live="filtro_destino_id" :opciones="$opcionesRegistro->all()"
                                       buscar-en="filtro_destino_busqueda" accion="seleccionarDestino"
                                       vacio="Todos" placeholder="Todos" sin-resultados="Sin resultados" />
                    </x-ui.campo>
                @endif
                @if($filtro_tipo_destino === 'secretaria' && $filtro_destino_id !== '')
                    <x-ui.campo etiqueta="Destino — Área" nombre="filtro_area"
                                :ayuda="$areas->count() === 0 ? 'Esta secretaría no tiene áreas cargadas.' : null">
                        <x-ui.combobox nombre="filtro_area" wire:model.live="filtro_area" :opciones="$areas->pluck('area')->all()"
                                       vacio="Todas las áreas" placeholder="Todas las áreas" />
                    </x-ui.campo>
                @endif
            </div>
            <p class="mt-3 text-[11px] text-gray-400">
                Los filtros de categoría aplican a los gráficos de insumos y maquinarias; los de tipos de movimiento (podés elegir varios) y destino (vehículo, evento, empleado o secretaría/área), a los gráficos de movimientos de insumos.
            </p>
        </div>
    </div>

    <!-- Grilla de gráficos -->
    @if($hayWidgets)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            @foreach($widgets as $key => $w)
                @switch($w['tipo'])
                    @case('donut')
                        @include('livewire.partials.chart-donut', ['titulo' => $w['titulo'], 'data' => $w['data'], 'decimales' => $w['decimales']])
                        @break

                    @case('barras')
                        @include('livewire.partials.chart-barras', [
                            'titulo' => $w['titulo'],
                            'data' => $w['data'],
                            'decimales' => $w['decimales'],
                            'maxFijo' => $w['max'] ?? null,
                            'paginacion' => $w['paginacion'] ?? null,
                        ])
                        @break

                    @case('series')
                        @include('livewire.partials.chart-series', ['titulo' => $w['titulo'], 'data' => $w['data']])
                        @break

                    @case('lista')
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 flex flex-col md:col-span-2">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-semibold text-gray-700">{{ $w['titulo'] }}</h3>
                                <x-ui.badge color="ambar">{{ $w['data']->count() }}</x-ui.badge>
                            </div>
                            @if($w['data']->count() > 0)
                                <div class="space-y-2 max-h-72 overflow-y-auto">
                                    @foreach($w['data'] as $chofer)
                                        @php
                                            $venc = $chofer->vencimiento_licencia;
                                            $dias = $venc ? (int) \Carbon\Carbon::today()->diffInDays($venc, false) : null;
                                        @endphp
                                        <div class="flex items-center justify-between p-2.5 rounded-lg border
                                            @if($dias !== null && $dias < 0) bg-red-50 border-red-200
                                            @elseif($dias !== null && $dias <= 15) bg-orange-50 border-orange-200
                                            @else bg-amber-50 border-amber-100 @endif">
                                            <div class="min-w-0">
                                                <p class="font-medium text-gray-800 text-sm truncate">{{ $chofer->nombre }}</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $chofer->tipo_licencia ? 'Lic. ' . $chofer->tipo_licencia : 'Licencia' }}
                                                    @if($chofer->secretaria) • {{ $chofer->secretaria->secretaria }} @endif
                                                </p>
                                            </div>
                                            <div class="text-right ml-3 flex-shrink-0">
                                                <p class="text-sm font-semibold
                                                    @if($dias !== null && $dias < 0) text-red-700
                                                    @elseif($dias !== null && $dias <= 15) text-orange-700
                                                    @else text-amber-700 @endif">
                                                    {{ $venc?->format('d/m/Y') }}
                                                </p>
                                                <p class="text-[11px] font-medium
                                                    @if($dias !== null && $dias < 0) text-red-600 @else text-gray-500 @endif">
                                                    @if($dias === null) —
                                                    @elseif($dias < 0) Vencida hace {{ abs($dias) }}d
                                                    @elseif($dias === 0) Vence hoy
                                                    @else Vence en {{ $dias }}d @endif
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <x-ui.vacio icono="fa-id-card" class="flex-1 py-10">No hay licencias por vencer</x-ui.vacio>
                            @endif
                        </div>
                        @break
                @endswitch
            @endforeach
        </div>
    @elseif($todosVacios)
        <div class="py-16">
            <x-ui.vacio icono="fa-filter">
                Ningún gráfico tiene datos con estos filtros
                <span class="block mt-1 font-normal">Probá acotar menos la búsqueda o limpiar los filtros.</span>
            </x-ui.vacio>
            @if($filtrosActivos > 0)
                <div class="mt-2 flex justify-center">
                    <x-ui.boton variante="secundario" icono="fa-xmark" wire:click="limpiarFiltros">Limpiar filtros</x-ui.boton>
                </div>
            @endif
        </div>
    @elseif($sinOpciones)
        <x-ui.vacio icono="fa-chart-column" class="py-16">No tenés módulos con estadísticas disponibles</x-ui.vacio>
    @else
        <x-ui.vacio icono="fa-chart-column" class="py-16">
            No hay gráficos activos
            <span class="block mt-1 font-normal">Usá "Personalizar" para elegir qué estadísticas ver.</span>
        </x-ui.vacio>
    @endif

    <!-- Modal Personalizar -->
    @if($modalPersonalizar)
        <x-ui.modal titulo="Personalizar estadísticas" icono="fa-sliders" cerrar="intentarCerrar" ancho="lg">
            @foreach($grupos as $grupoKey => $grupoLabel)
                @if(!empty($opcionesPorGrupo[$grupoKey]))
                <fieldset class="w-full min-w-0 mb-5">
                    <legend class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">{{ $grupoLabel }}</legend>
                    <div class="space-y-1.5">
                        @foreach($opcionesPorGrupo[$grupoKey] as $key => $cfg)
                        <label class="flex items-center gap-3 p-2.5 rounded-lg border border-gray-100 hover:border-[#77BF43]/40 hover:bg-[#77BF43]/5 cursor-pointer transition-colors">
                            <input type="checkbox" wire:model="seleccionWidgets" value="{{ $key }}"
                                   class="w-4 h-4 rounded text-[#77BF43] border-gray-300 focus:ring-[#77BF43]">
                            <span class="text-sm font-medium text-gray-700">{{ $cfg['label'] }}</span>
                        </label>
                        @endforeach
                    </div>
                </fieldset>
                @endif
            @endforeach

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
