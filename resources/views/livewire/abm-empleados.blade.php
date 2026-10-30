<x-ui.pagina titulo="Empleados" icono="fa-users">
    <!-- Búsqueda -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
        <x-ui.buscador wire:model.live.debounce.400ms="search" placeholder="Buscar por nombre, legajo o DNI..." />

        <div class="text-sm text-gray-500">
            {{ $empleados->total() }} empleado{{ $empleados->total() === 1 ? '' : 's' }} activo{{ $empleados->total() === 1 ? '' : 's' }}
        </div>
    </div>

    {{-- Tabla --}}
    <x-ui.tabla>
        <x-slot:cabecera>
            <x-ui.th columna="legajo" :orden="$this->orden()">Legajo</x-ui.th>
            <x-ui.th columna="nombre" :orden="$this->orden()">Nombre</x-ui.th>
            <x-ui.th columna="dni" :orden="$this->orden()">DNI</x-ui.th>
            <x-ui.th columna="categoria" :orden="$this->orden()">Categoría</x-ui.th>
            <x-ui.th columna="ingreso" :orden="$this->orden()">Ingreso</x-ui.th>
        </x-slot:cabecera>

        @forelse ($empleados as $empleado)
            <tr wire:key="fila-{{ $empleado->LEGAJO }}" class="hover:bg-gray-50/50 transition-colors duration-150">
                <td class="px-6 py-4">
                    <span class="px-3 py-1 inline-flex text-xs font-medium rounded-full bg-gradient-to-r from-blue-50 to-blue-100 text-blue-700 border border-blue-200">
                        {{ $empleado->LEGAJO }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ $empleado->nombre_formateado }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-600">{{ $empleado->DNI ? number_format($empleado->DNI, 0, ',', '.') : '—' }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-600">{{ $empleado->CATEGORIA ?: '—' }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-600">{{ $empleado->FECING ? $empleado->FECING->format('d/m/Y') : '—' }}</div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-6 py-6">
                    <x-ui.vacio icono="fa-users">{{ $search ? 'No hay empleados que coincidan con la búsqueda' : 'No hay empleados activos' }}</x-ui.vacio>
                </td>
            </tr>
        @endforelse

        <x-slot:pie>
            <x-ui.paginacion :paginador="$empleados" />
        </x-slot:pie>
    </x-ui.tabla>
</x-ui.pagina>
