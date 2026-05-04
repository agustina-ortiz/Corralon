{{-- resources/views/livewire/abm-usuarios.blade.php --}}
<div>
    <!-- Header con busqueda y boton crear -->
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
            <button
                wire:click="$toggle('mostrarFiltros')"
                class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-200 flex items-center justify-center gap-2 font-medium shadow-sm"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                Filtros
                @if($filtro_acceso || $filtro_corralon || $filtro_rol)
                    <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-blue-600 rounded-full">
                        {{ collect([$filtro_acceso, $filtro_corralon, $filtro_rol])->filter()->count() }}
                    </span>
                @endif
            </button>

            @if($puedeCrear)
            <button
                wire:click="abrirModal('crear')"
                class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 transition-all duration-200 flex items-center justify-center gap-2 font-medium"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Nuevo Usuario
            </button>
            @endif
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

    <!-- Mensajes -->
    @if (session()->has('mensaje'))
        <div class="mb-6 px-4 py-3.5 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('mensaje') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-6 px-4 py-3.5 bg-gradient-to-r from-red-50 to-rose-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                <span class="px-3 py-1 inline-flex text-xs font-medium rounded-full
                                    {{ $usuario->rol->nombre === 'Administrador'
                                        ? 'bg-gradient-to-r from-purple-50 to-purple-100 text-purple-700 border border-purple-200'
                                        : 'bg-gradient-to-r from-gray-50 to-gray-100 text-gray-700 border border-gray-200' }}">
                                    {{ $usuario->rol->nombre }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($usuario->esAdministrador())
                                    <span class="px-3 py-1 inline-flex text-xs font-medium rounded-full bg-gradient-to-r from-green-50 to-green-100 text-green-700 border border-green-200">
                                        Acceso total
                                    </span>
                                @else
                                    @php $permisosUser = $usuario->permisos; @endphp
                                    @if($permisosUser->isEmpty())
                                        <span class="text-xs text-gray-400 italic">Sin permisos asignados</span>
                                    @else
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            @php
                                                $corralonesConPermisos = $permisosUser->whereNotNull('id_corralon')->pluck('id_corralon')->unique();
                                                $modulosGlobales = $permisosUser->whereNull('id_corralon')->pluck('modulo')->unique();
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
                                            @foreach($modulosGlobales as $mod)
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
                                <div class="flex items-center justify-end gap-2">
                                    @if($puedeEditar)
                                    <button
                                        wire:click="abrirModal('editar', {{ $usuario->id }})"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-150"
                                        title="Editar"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    @endif
                                    @if($puedeEliminar && $usuario->id !== auth()->id())
                                    <button
                                        wire:click="eliminar({{ $usuario->id }})"
                                        wire:confirm="Esta seguro de eliminar este usuario?"
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
                                <p class="text-sm font-medium text-gray-400">No se encontraron usuarios</p>
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
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" wire:click="cerrarModal"></div>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full max-h-[90vh] overflow-y-auto">
                <form wire:submit.prevent="guardar">
                    <div class="bg-white px-6 pt-6 pb-5">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                {{ $modo === 'crear' ? 'Nuevo Usuario' : 'Editar Usuario' }}
                            </h3>
                            <button type="button" wire:click="cerrarModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-5">
                            <!-- Datos basicos -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
                                    <input type="text" wire:model="name" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 @error('name') border-red-300 @enderror">
                                    @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                    <input type="email" wire:model="email" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 @error('email') border-red-300 @enderror">
                                    @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Rol -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">Rol *</label>
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
                                            value="{{ $rol->id }}"
                                            class="mt-0.5 h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                        >
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-semibold text-gray-900">{{ $rol->nombre }}</span>
                                                @if($rol->nombre === 'Administrador')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                                        Acceso total
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500 leading-relaxed">
                                                @if($rol->nombre === 'Administrador')
                                                    Acceso completo a todos los modulos y corralones. No requiere configurar permisos adicionales.
                                                @elseif($rol->nombre === 'Visualizador')
                                                    Acceso limitado segun los permisos que se configuren debajo. Puede tener permisos de solo lectura o de edicion por modulo.
                                                @elseif($rol->descripcion)
                                                    {{ $rol->descripcion }}
                                                @else
                                                    Rol personalizado. Configure los permisos de acceso en la seccion inferior.
                                                @endif
                                            </p>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                                @error('id_rol') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Contrasenas -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Contrasena {{ $modo === 'editar' ? '(dejar en blanco para no cambiar)' : '*' }}
                                    </label>
                                    <input type="password" wire:model="password" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 @error('password') border-red-300 @enderror">
                                    @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Contrasena</label>
                                    <input type="password" wire:model="password_confirmation" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                </div>
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
                                        El rol Administrador tiene acceso completo a todos los modulos y corralones. No es necesario configurar permisos adicionales.
                                    </p>
                                </div>
                            @else
                                <!-- Divisor -->
                                <div class="border-t border-gray-200 my-2"></div>

                                <h4 class="text-base font-semibold text-gray-900">Permisos de Acceso</h4>

                                <!-- PERMISOS GLOBALES -->
                                <div class="rounded-xl border border-gray-200 p-4 bg-gray-50/50">
                                    <h5 class="text-sm font-semibold text-gray-700 mb-3">Modulos Globales</h5>
                                    <p class="text-xs text-gray-500 mb-3">Estos modulos no dependen de un corralon especifico.</p>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                        @foreach($modulosGlobales as $modulo)
                                        <div class="flex items-center justify-between p-2 rounded-lg bg-white border border-gray-100">
                                            <span class="text-sm text-gray-700">{{ $todosLosModulos[$modulo] }}</span>
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
                                    <h5 class="text-sm font-semibold text-gray-700 mb-3">Permisos por Corralon</h5>
                                    <p class="text-xs text-gray-500 mb-3">Selecciona los corralones y configura que modulos puede acceder en cada uno.</p>

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
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3">
                        <button type="button" wire:click="cerrarModal" class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-100 transition-colors duration-200 font-medium">
                            Cancelar
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 shadow-lg shadow-blue-500/30 transition-all duration-200 font-medium">
                            {{ $modo === 'crear' ? 'Crear Usuario' : 'Guardar Cambios' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
