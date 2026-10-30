{{--
    Buscador del destinatario vehículo / evento / empleado de los movimientos
    (Transferencias de Insumos y de Maquinarias, Nuevo Movimiento y Movimientos
    Múltiples): x-ui.combobox en modo servidor. Las listas las arma el
    componente en render() (hasta 50, filtradas por la búsqueda).

    Variables:
      $tipo       vehiculo | evento | empleado
      $buscarEn   propiedad Livewire del texto buscado
      $accion     método Livewire que recibe el id (LEGAJO para empleados)
      $alAbrir    expresión Alpine al abrir (marca la lista como visible)
      $vehiculos, $eventos, $empleados   resultados del componente
      $nombre     propiedad del error (por omisión id_referencia)
--}}
@php
    $opcionesDestino = match ($tipo) {
        'vehiculo' => $vehiculos->map(fn ($v) => [
            'id' => $v->id,
            'nombre' => $v->vehiculo,
            'detalle' => $v->patente.' • '.$v->marca_modelo,
        ]),
        'evento' => $eventos->map(fn ($ev) => [
            'id' => $ev->id,
            'nombre' => $ev->evento,
            'detalle' => $ev->fecha?->format('d/m/Y').' • '.$ev->ubicacion,
        ]),
        default => $empleados->map(fn ($emp) => [
            'id' => $emp->LEGAJO,
            'nombre' => $emp->nombre_formateado,
            'detalle' => 'Legajo '.$emp->LEGAJO.' • DNI '.number_format($emp->DNI, 0, '', '.'),
        ]),
    };
@endphp

<x-ui.combobox :nombre="$nombre ?? 'id_referencia'" :buscar-en="$buscarEn" :accion="$accion"
               :placeholder="'Buscar '.match ($tipo) { 'vehiculo' => 'por nombre, patente o marca...', 'evento' => 'por nombre de evento...', default => 'por nombre, legajo o DNI...' }"
               :sin-resultados="'No hay '.match ($tipo) { 'vehiculo' => 'vehículos', 'evento' => 'eventos', default => 'empleados' }.' que coincidan con la búsqueda'"
               x-on:combobox-abierto="{{ $alAbrir }}"
               :opciones="$opcionesDestino" />
