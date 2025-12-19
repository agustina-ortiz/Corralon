{{-- resources/views/livewire/abm-usuarios.blade.php --}}
<div class="p-6">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Usuarios</h1>
        <button wire:click="abrirModal('crear')" 
                class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo Usuario
        </button>
    </div>

    @if (session()->has('mensaje'))
        <div class="mb-4 rounded-lg bg-green-100 p-4 text-green-700">
            {{ session('mensaje') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-100 p-4 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filtros --}}
    <div class="mb-6 rounded-lg bg-white p-4 shadow">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="font-semibold text-gray-700">Filtros</h3>
            @if($busqueda || $filtro_acceso || $filtro_corralon)
                <button wire:click="resetearFiltros" 
                        class="text-sm text-blue-600 hover:text-blue-800">
                    Limpiar filtros
                </button>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Búsqueda</label>
                <input type="text" 
                       wire:model.live="busqueda" 
                       placeholder="Buscar por nombre o email..."
                       class="w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Tipo de Acceso</label>
                <select wire:model.live="filtro_acceso" 
                        class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="">Todos</option>
                    <option value="todos">Acceso a todos los corralones</option>
                    <option value="limitado">Acceso limitado</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Corralón</label>
                <select wire:model.live="filtro_corralon" 
                        class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="">Todos los corralones</option>
                    @foreach($corralones as $corralon)
                        <option value="{{ $corralon->id }}">{{ $corralon->descripcion }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="overflow-hidden rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th wire:click="ordenarPor('name')" 
                        class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        <div class="flex items-center gap-1">
                            Nombre
                            @if($orden_campo === 'name')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="{{ $orden_direccion === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/>
                                </svg>
                            @endif
                        </div>
                    </th>
                    <th wire:click="ordenarPor('email')" 
                        class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        <div class="flex items-center gap-1">
                            Email
                            @if($orden_campo === 'email')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="{{ $orden_direccion === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/>
                                </svg>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Acceso
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Corralones
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($usuarios as $usuario)
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $usuario->name }}</div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $usuario->email }}</div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            @if($usuario->acceso_todos_corralones)
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                    Todos los corralones
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800">
                                    Limitado
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($usuario->acceso_todos_corralones)
                                <span class="text-sm text-gray-500">Todos</span>
                            @else
                                <div class="flex flex-wrap gap-1">
                                    @foreach($corralones->whereIn('id', $usuario->corralones_permitidos ?? []) as $corralon)
                                        <span class="inline-flex rounded bg-gray-100 px-2 py-1 text-xs text-gray-700">
                                            {{ $corralon->descripcion }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                            <button wire:click="abrirModal('editar', {{ $usuario->id }})" 
                                    class="text-blue-600 hover:text-blue-900">
                                Editar
                            </button>
                            @if($usuario->id !== auth()->id())
                                <button wire:click="eliminar({{ $usuario->id }})" 
                                        onclick="return confirm('¿Está seguro de eliminar este usuario?')"
                                        class="ml-3 text-red-600 hover:text-red-900">
                                    Eliminar
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                            No se encontraron usuarios
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4">
            {{ $usuarios->links() }}
        </div>
    </div>

    {{-- Modal --}}
    @if($modalAbierto)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data x-show="true" x-transition>
            <div class="flex min-h-screen items-center justify-center px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     wire:click="cerrarModal"></div>

                <div class="relative w-full max-w-2xl transform rounded-lg bg-white p-6 shadow-xl transition-all">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">
                            {{ $modo === 'crear' ? 'Nuevo Usuario' : 'Editar Usuario' }}
                        </h3>
                        <button wire:click="cerrarModal" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="guardar">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                                <input type="text" 
                                       wire:model="name" 
                                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 @error('name') border-red-500 @enderror">
                                @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email *</label>
                                <input type="email" 
                                       wire:model="email" 
                                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 @error('email') border-red-500 @enderror">
                                @error('email') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Contraseña {{ $modo === 'editar' ? '(dejar en blanco para no cambiar)' : '*' }}
                                </label>
                                <input type="password" 
                                       wire:model="password" 
                                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 @error('password') border-red-500 @enderror">
                                @error('password') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                                <input type="password" 
                                       wire:model="password_confirmation" 
                                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                            </div>

                            <div class="rounded-lg border border-gray-200 p-4">
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           wire:model.live="acceso_todos_corralones" 
                                           class="rounded border-gray-300">
                                    <span class="ml-2 text-sm font-medium text-gray-700">
                                        Acceso a todos los corralones
                                    </span>
                                </label>
                            </div>

                            @if(!$acceso_todos_corralones)
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">
                                        Corralones Permitidos *
                                    </label>
                                    <div class="space-y-2 rounded-lg border border-gray-200 p-4">
                                        @foreach($corralones as $corralon)
                                            <label class="flex items-center">
                                                <input type="checkbox" 
                                                       wire:model="corralones_seleccionados" 
                                                       value="{{ $corralon->id }}" 
                                                       class="rounded border-gray-300">
                                                <span class="ml-2 text-sm text-gray-700">{{ $corralon->descripcion }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('corralones_seleccionados') 
                                        <span class="text-sm text-red-600">{{ $message }}</span> 
                                    @enderror
                                </div>
                            @endif
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" 
                                    wire:click="cerrarModal" 
                                    class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit" 
                                    class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                                {{ $modo === 'crear' ? 'Crear Usuario' : 'Guardar Cambios' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>