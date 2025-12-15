<div>
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar categorías..."
            class="w-full max-w-md px-4 py-2 border rounded-lg"
        >

        <button
            wire:click="crear"
            class="ml-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
            Nueva Categoría
        </button>
    </div>

    <!-- Mensaje -->
    @if (session()->has('message'))
        <div class="mb-4 px-4 py-3 bg-green-100 text-green-700 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium">Nombre</th>
                    <th class="px-6 py-3 text-right text-xs font-medium">Acciones</th>
                </tr>
            </thead>

            <tbody>
            @forelse ($categorias as $categoria)
                <tr>
                    <td class="px-6 py-4">{{ $categoria->nombre }}</td>
                    <td class="px-6 py-4 text-right">
                        <button wire:click="editar({{ $categoria->id }})" class="text-blue-600 mr-3">
                            Editar
                        </button>
                        <button
                            wire:click="eliminar({{ $categoria->id }})"
                            onclick="return confirm('¿Eliminar categoría?')"
                            class="text-red-600"
                        >
                            Eliminar
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="px-6 py-4 text-center text-gray-500">
                        No hay categorías cargadas
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4 border-t">
            {{ $categorias->links() }}
        </div>
    </div>

    <!-- Modal -->
    @if ($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-lg font-semibold mb-4">
                    {{ $editMode ? 'Editar Categoría' : 'Nueva Categoría' }}
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm">Nombre *</label>
                        <input wire:model="nombre" class="w-full border rounded px-3 py-2">
                        @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm">Descripción</label>
                        <textarea wire:model="descripcion" class="w-full border rounded px-3 py-2"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="cerrarModal" class="px-4 py-2 border rounded">
                        Cancelar
                    </button>
                    <button wire:click="guardar" class="px-4 py-2 bg-blue-600 text-white rounded">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
