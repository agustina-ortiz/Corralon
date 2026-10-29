{{-- resources/views/livewire/abm-usuarios.blade.php --}}
<x-ui.pagina titulo="Usuarios" icono="fa-users-gear">
    @if($puedeCrear)
        <x-slot:acciones>
            <x-ui.boton icono="fa-plus" wire:click="abrirModal('crear')">Nuevo Usuario</x-ui.boton>
        </x-slot:acciones>
    @endif

    <!-- Búsqueda y filtros -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
        <div class="flex-1 max-w-md">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input
                    type="text"
                    wire:model.live="busqueda"
                    placeholder="Buscar usuarios..."
                    class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
                >
            </div>
        </div>
        <div class="flex gap-3">
            <x-ui.boton variante="secundario" icono="fa-filter" wire:click="$toggle('mostrarFiltros')">
                Filtros
                @if($filtro_acceso || $filtro_corralon || $filtro_rol)
                    <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-blue-600 rounded-full">
                        {{ collect([$filtro_acceso, $filtro_corralon, $filtro_rol])->filter()->count() }}
                    </span>
                @endif
            </x-ui.boton>
        </div>
    </div>

    <!-- Panel de Filtros -->
    @if($mostrarFiltros)
    <div class="mb-6 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900">Filtros Avanzados</h3>
                @if($filtro_acceso || $filtro_corralon || $filtro_rol)
                    <button wire:click="resetearFiltros" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Limpiar filtros</button>
                @endif
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
                    <select wire:model.live="filtro_rol" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        <option value="">Todos los roles</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Acceso</label>
                    <select wire:model.live="filtro_acceso" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        <option value="">Todos</option>
                        <option value="admin">Administradores</option>
                        <option value="limitado">Acceso limitado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Corralon</label>
                    <select wire:model.live="filtro_corralon" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        <option value="">Todos los corralones</option>
                        @foreach($corralones as $corralon)
                            <option value="{{ $corralon->id }}">{{ $corralon->descripcion }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    @endif


    <!-- Tabla -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100/50">
                    <tr>
                        <th wire:click="ordenarPor('name')" class="cursor-pointer px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <div class="flex items-center gap-1">
                                Nombre
                                @if($orden_campo === 'name')
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $orden_direccion === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th wire:click="ordenarPor('email')" class="cursor-pointer px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <div class="flex items-center gap-1">
                                Email
                                @if($orden_campo === 'email')
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $orden_direccion === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Permisos</th>
                        @if($puedeEditar || $puedeEliminar)
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse($usuarios as $usuario)
                        <tr class="hover:bg-gray-50/50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $usuario->name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600">{{ $usuario->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :color="$usuario->rol->nombre === 'Administrador' ? 'violeta' : 'gris'">{{ $usuario->rol->nombre }}</x-ui.badge>
                            </td>
                            <td class="px-6 py-4">
                                @if($usuario->esAdministrador())
                                    <x-ui.badge color="verde">Acceso total</x-ui.badge>
                                @else
                                    @php $permisosUser = $usuario->permisos; @endphp
                                    @if($permisosUser->isEmpty())
                                        <span class="text-xs text-gray-400 italic">Sin permisos asignados</span>
                                    @else
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            @php
                                                $corralonesConPermisos = $permisosUser->whereNotNull('id_corralon')->pluck('id_corralon')->unique();
                                                $modulosGlobalesUsuario = $permisosUser->whereNull('id_corralon')->pluck('modulo')->unique();
                                            @endphp
                                            @foreach($corralonesConPermisos as $cId)
                                                @php $corr = $corralones->firstWhere('id', $cId); @endphp
                                                @if($corr)
                                                <span class="inline-flex rounded-lg bg-blue-50 px-2 py-1 text-xs text-blue-700 border border-blue-200">
                                                    {{ $corr->descripcion }}
                                                    <span class="ml-1 text-blue-400">({{ $permisosUser->where('id_corralon', $cId)->pluck('modulo')->unique()->count() }} mod.)</span>
                                                </span>
                                                @endif
                                            @endforeach
                                            @foreach($modulosGlobalesUsuario as $mod)
                                            <span class="inline-flex rounded-lg bg-gray-100 px-2 py-1 text-xs text-gray-600">
                                                {{ $todosLosModulos[$mod] ?? $mod }}
                                            </span>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </td>
                            @if($puedeEditar || $puedeEliminar)
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    @if($puedeEditar)
                                    <x-ui.accion icono="fa-regular fa-pen-to-square" etiqueta="Editar" wire:click="abrirModal('editar', {{ $usuario->id }})" />
                                    @endif
                                    @if($puedeEliminar && $usuario->id !== auth()->id())
                                    <x-ui.accion icono="fa-regular fa-trash-can" color="peligro" etiqueta="Eliminar" wire:click="confirmarEliminar({{ $usuario->id }})" />
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-6">
                                <x-ui.vacio icono="fa-users">{{ $busqueda || $filtro_acceso || $filtro_corralon || $filtro_rol ? 'No hay usuarios que coincidan con la búsqueda o los filtros' : 'Todavía no hay usuarios cargados' }}</x-ui.vacio>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
            {{ $usuarios->links() }}
        </div>
    </div>

    <!-- Modal -->
    @if($modalAbierto)
    <x-ui.modal :titulo="$modo === 'crear' ? 'Nuevo Usuario' : 'Editar Usuario'"
                :icono="$modo === 'crear' ? 'fa-plus' : 'fa-pen'"
                cerrar="intentarCerrar" ancho="4xl" submit="guardar">
        <!-- Datos básicos -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-ui.campo etiqueta="Nombre" nombre="name" obligatorio>
                <x-ui.input nombre="name" wire:model="name" maxlength="255" autocomplete="off" />
            </x-ui.campo>
            <x-ui.campo etiqueta="Email" nombre="email" obligatorio>
                <x-ui.input nombre="email" type="email" wire:model="email" maxlength="255" autocomplete="off" />
            </x-ui.campo>
        </div>

        <!-- Rol -->
        <fieldset class="w-full min-w-0">
            <legend class="block text-sm font-medium text-gray-700 mb-3">Rol <span class="text-red-600" aria-hidden="true">*</span></legend>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($roles as $rol)
                <label
                    class="relative flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all duration-200
                        {{ (int)$id_rol === $rol->id
                            ? ($rol->nombre === 'Administrador'
                                ? 'border-purple-400 bg-purple-50/50 ring-2 ring-purple-200'
                                : 'border-blue-400 bg-blue-50/50 ring-2 ring-blue-200')
                            : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50/50' }}"
                >
                    <input
                        type="radio"
                        wire:model.live="id_rol"
                        @error('id_rol') aria-invalid="true" @enderror
                        value="{{ $rol->id }}"
                        class="mt-0.5 h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                    >
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-gray-900">{{ $rol->nombre }}</span>
                            @if($rol->nombre === 'Administrador')
                                <x-ui.badge color="violeta">Acceso total</x-ui.badge>
                            @endif
                        </div>
                        <p class="mt-1 text-xs text-gray-500 leading-relaxed">
                            @if($rol->nombre === 'Administrador')
                                Acceso completo a todos los módulos y corralones. No requiere configurar permisos adicionales.
                            @elseif($rol->nombre === 'Visualizador')
                                Acceso limitado según los permisos que se configuren debajo. Puede tener permisos de solo lectura o de edición por módulo.
                            @elseif($rol->descripcion)
                                {{ $rol->descripcion }}
                            @else
                                Rol personalizado. Configure los permisos de acceso en la sección inferior.
                            @endif
                        </p>
                    </div>
                </label>
                @endforeach
            </div>
            @error('id_rol') <p id="error-id_rol" class="mt-1 text-xs text-red-600" data-error="id_rol">{{ $message }}</p> @enderror
        </fieldset>

        <!-- Contraseñas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-ui.campo etiqueta="Contraseña" nombre="password" :obligatorio="$modo === 'crear'"
                        :ayuda="$modo === 'editar' ? 'Dejar en blanco para no cambiarla.' : 'Mínimo 8 caracteres.'">
                <x-ui.input nombre="password" type="password" wire:model="password" autocomplete="new-password" />
            </x-ui.campo>
            <x-ui.campo etiqueta="Confirmar Contraseña" nombre="password_confirmation">
                <x-ui.input nombre="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" />
            </x-ui.campo>
        </div>

        @php
            $rolSeleccionado = $roles->firstWhere('id', $id_rol);
            $esAdmin = $rolSeleccionado && $rolSeleccionado->nombre === 'Administrador';
        @endphp

        @if($esAdmin)
            <div class="rounded-xl border border-green-200 p-4 bg-green-50/50">
                <p class="text-sm font-medium text-green-700">
                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    El rol Administrador tiene acceso completo a todos los módulos y corralones. No es necesario configurar permisos adicionales.
                </p>
            </div>
        @else
            <!-- Divisor -->
            <div class="border-t border-gray-200 my-2"></div>

            <h4 class="text-base font-semibold text-gray-900">Permisos de Acceso</h4>

            <!-- PERMISOS GLOBALES -->
            <div class="rounded-xl border border-gray-200 p-4 bg-gray-50/50">
                <h5 class="text-sm font-semibold text-gray-700 mb-3">Módulos Globales</h5>
                <p class="text-xs text-gray-500 mb-3">Estos módulos no dependen de un corralón específico. "Vehículos (todos)" da acceso a todos los vehículos; para restringirlo a ciertos corralones/depósitos usá los permisos por corralón.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach($modulosGlobales as $modulo)
                    <div class="flex items-center justify-between p-2 rounded-lg bg-white border border-gray-100">
                        <span class="text-sm text-gray-700">{{ $todosLosModulos[$modulo] }}@if(in_array($modulo, $modulosMixtos)) (todos)@endif</span>
                        <div class="flex gap-1">
                            <button type="button"
                                wire:click="toggleModuloGlobal('{{ $modulo }}', 'ver')"
                                class="px-2 py-1 text-xs rounded-md transition-colors {{ isset($permisos_globales[$modulo]) && $permisos_globales[$modulo] === 'ver' ? 'bg-blue-100 text-blue-700 border border-blue-300' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                Ver
                            </button>
                            <button type="button"
                                wire:click="toggleModuloGlobal('{{ $modulo }}', 'editar')"
                                class="px-2 py-1 text-xs rounded-md transition-colors {{ isset($permisos_globales[$modulo]) && $permisos_globales[$modulo] === 'editar' ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                Editar
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- PERMISOS POR CORRALON -->
            <div class="rounded-xl border border-gray-200 p-4 bg-gray-50/50">
                <h5 class="text-sm font-semibold text-gray-700 mb-3">Permisos por Corralón</h5>
                <p class="text-xs text-gray-500 mb-3">Seleccioná los corralones y configurá a qué módulos puede acceder en cada uno.</p>

                <!-- Selector de corralones -->
                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach($corralones as $corralon)
                    <button type="button"
                        wire:click="toggleCorralon({{ $corralon->id }})"
                        class="px-3 py-1.5 text-sm rounded-lg transition-colors border {{ in_array((string)$corralon->id, $corralones_seleccionados) ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}">
                        {{ $corralon->descripcion }}
                    </button>
                    @endforeach
                </div>

                <!-- Configuracion por corralon -->
                @foreach($corralones_seleccionados as $corralonId)
                    @php $corralonObj = $corralones->firstWhere('id', $corralonId); @endphp
                    @if($corralonObj)
                    <div class="mb-4 rounded-lg border border-blue-200 bg-white p-4">
                        <h6 class="text-sm font-semibold text-blue-700 mb-3">{{ $corralonObj->descripcion }}</h6>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach($modulosPorUbicacion as $modulo)
                            <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 border border-gray-100">
                                <span class="text-sm text-gray-700">{{ $todosLosModulos[$modulo] }}</span>
                                @if(in_array($modulo, $modulosMixtos) && isset($permisos_globales[$modulo]))
                                <span class="text-xs text-gray-400 italic">Todos (global)</span>
                                @else
                                <div class="flex gap-1">
                                    <button type="button"
                                        wire:click="toggleModuloCorralon('{{ $corralonId }}', '{{ $modulo }}', 'ver')"
                                        class="px-2 py-1 text-xs rounded-md transition-colors {{ isset($permisos_por_corralon[$corralonId][$modulo]) && $permisos_por_corralon[$corralonId][$modulo] === 'ver' ? 'bg-blue-100 text-blue-700 border border-blue-300' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                        Ver
                                    </button>
                                    <button type="button"
                                        wire:click="toggleModuloCorralon('{{ $corralonId }}', '{{ $modulo }}', 'editar')"
                                        class="px-2 py-1 text-xs rounded-md transition-colors {{ isset($permisos_por_corralon[$corralonId][$modulo]) && $permisos_por_corralon[$corralonId][$modulo] === 'editar' ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                        Editar
                                    </button>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endforeach

                @if(empty($corralones_seleccionados))
                    <p class="text-sm text-gray-400 italic text-center py-4">Selecciona al menos un corralon para configurar permisos por ubicacion.</p>
                @endif
            </div>
        @endif

        <x-slot:footer>
            <x-ui.boton variante="secundario" wire:click="intentarCerrar">Cancelar</x-ui.boton>
            <x-ui.boton type="submit" wire:loading.attr="disabled" wire:target="guardar">
                {{ $modo === 'crear' ? 'Crear Usuario' : 'Guardar Cambios' }}
            </x-ui.boton>
        </x-slot:footer>
    </x-ui.modal>
    @endif

    @if($eliminarId)
        <x-ui.modal-confirmacion confirmar="eliminar" cancelar="cancelarEliminar">
            ¿Está seguro de eliminar este usuario?
        </x-ui.modal-confirmacion>
    @endif

    @if($confirmandoDescarte)
        <x-ui.modal-confirmacion tipo="advertencia" titulo="Cambios sin guardar" boton="Descartar cambios"
                                 confirmar="descartarCambios" cancelar="seguirEditando" :z="60">
            Hay cambios sin guardar. ¿Desea descartarlos?
        </x-ui.modal-confirmacion>
    @endif
</x-ui.pagina>
