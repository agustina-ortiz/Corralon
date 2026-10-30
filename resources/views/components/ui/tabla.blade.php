{{--
    <x-ui.tabla> — tarjeta del listado con su tabla (kit, spec 17), con el
    aspecto de siempre del Corralón: tarjeta blanca rounded-2xl, cabecera
    gris con degradé, filas con hover suave.

    Adaptación: las cards de mobile quedan para una pasada final (decisión del
    usuario), así que por ahora la tabla se ve en todos los anchos y scrollea
    horizontal en pantallas chicas.

      <x-ui.tabla>
          <x-slot:cabecera>
              <x-ui.th columna="deposito" :orden="$this->orden()">Depósito</x-ui.th>
              <x-ui.th derecha>Acciones</x-ui.th>
          </x-slot:cabecera>

          @forelse($filas as $fila)
              <tr wire:key="fila-{{ $fila->id }}" class="hover:bg-gray-50/50 transition-colors duration-150">...</tr>
          @empty
              <tr><td colspan="2" class="px-6 py-6"><x-ui.vacio>...</x-ui.vacio></td></tr>
          @endforelse

          <x-slot:pie>
              <x-ui.paginacion :paginador="$filas" />
          </x-slot:pie>
      </x-ui.tabla>

    Slots:
      cabecera  Los <x-ui.th> (van dentro de <thead><tr>). Sin cabecera, el
                slot va directo en el <table> (tablas con varios <tbody>).
      default   Las filas (<tr>) del <tbody>.
      pie       Debajo de la tabla, dentro de la tarjeta (la paginación).
--}}
<div {{ $attributes->class(['bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden']) }} data-tabla>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            @isset($cabecera)
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100/50">
                    <tr>
                        {{ $cabecera }}
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    {{ $slot }}
                </tbody>
            @else
                {{ $slot }}
            @endisset
        </table>
    </div>

    @isset($pie)
        {{ $pie }}
    @endisset
</div>
