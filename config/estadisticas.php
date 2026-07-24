<?php

/*
|--------------------------------------------------------------------------
| Estadísticas — configuración de widgets/gráficos
|--------------------------------------------------------------------------
|
| Fuente única de verdad de la solapa /estadisticas. Cada widget declara:
|  - 'label'   => nombre visible (en el gráfico y en el modal "Personalizar")
|  - 'grupo'   => clave de agrupación (ver 'grupos')
|  - 'permiso' => módulo de permiso requerido (User::tieneAccesoAModulo)
|  - 'chart'   => tipo de gráfico ('donut', 'barras', 'series', 'lista')
|  - 'usa_fecha' => (opcional) true si el widget respeta el filtro de fechas
|
| Para agregar un widget nuevo: 1) agregar entrada acá, 2) agregar el cálculo
| en App\Livewire\Estadisticas::calcularWidget(), 3) renderizarlo en la vista.
|
*/

return [

    'grupos' => [
        'insumos'      => 'Insumos',
        'maquinarias'  => 'Maquinarias',
        'vehiculos'    => 'Vehículos',
        'movimientos'  => 'Movimientos de Insumos',
        'otros'        => 'Choferes y Eventos',
    ],

    'widgets' => [

        // ============ INSUMOS ============
        'ins_categoria' => [
            'label'   => 'Insumos por categoría',
            'grupo'   => 'insumos',
            'permiso' => 'insumos',
            'chart'   => 'donut',
        ],
        'ins_estado_stock' => [
            'label'   => 'Estado de stock (OK / Bajo / Sin stock)',
            'grupo'   => 'insumos',
            'permiso' => 'insumos',
            'chart'   => 'donut',
        ],
        'ins_top_stock' => [
            'label'   => 'Top 10 insumos por stock',
            'grupo'   => 'insumos',
            'permiso' => 'insumos',
            'chart'   => 'barras',
        ],
        'ins_unidad' => [
            'label'   => 'Insumos por unidad de medida',
            'grupo'   => 'insumos',
            'permiso' => 'insumos',
            'chart'   => 'donut',
        ],
        'ins_stock_deposito' => [
            'label'   => 'Stock total por depósito',
            'grupo'   => 'insumos',
            'permiso' => 'insumos',
            'chart'   => 'barras',
        ],

        // ============ MAQUINARIAS ============
        'maq_categoria' => [
            'label'   => 'Maquinarias por categoría',
            'grupo'   => 'maquinarias',
            'permiso' => 'maquinarias',
            'chart'   => 'donut',
        ],
        'maq_disponibilidad' => [
            'label'   => 'Disponibles vs. asignadas',
            'grupo'   => 'maquinarias',
            'permiso' => 'maquinarias',
            'chart'   => 'barras',
        ],
        'maq_deposito' => [
            'label'   => 'Maquinarias por depósito',
            'grupo'   => 'maquinarias',
            'permiso' => 'maquinarias',
            'chart'   => 'barras',
        ],

        // ============ VEHÍCULOS ============
        'veh_estado' => [
            'label'   => 'Vehículos por estado',
            'grupo'   => 'vehiculos',
            'permiso' => 'vehiculos',
            'chart'   => 'donut',
        ],
        'veh_secretaria' => [
            'label'   => 'Vehículos por secretaría',
            'grupo'   => 'vehiculos',
            'permiso' => 'vehiculos',
            'chart'   => 'barras',
        ],
        'veh_combustible' => [
            'label'   => 'Vehículos por combustible',
            'grupo'   => 'vehiculos',
            'permiso' => 'vehiculos',
            'chart'   => 'donut',
        ],
        'veh_vtv' => [
            'label'   => 'Estado de VTV',
            'grupo'   => 'vehiculos',
            'permiso' => 'vehiculos',
            'chart'   => 'donut',
        ],

        // ============ MOVIMIENTOS (INSUMOS) ============
        'mov_entrada_salida' => [
            'label'     => 'Entradas vs. salidas por mes',
            'grupo'     => 'movimientos',
            'permiso'   => 'movimientos_insumos',
            'chart'     => 'series',
            'usa_fecha' => true,
        ],
        'mov_tipo' => [
            'label'     => 'Movimientos por tipo',
            'grupo'     => 'movimientos',
            'permiso'   => 'movimientos_insumos',
            'chart'     => 'barras',
            'usa_fecha' => true,
        ],
        'mov_top_insumos' => [
            'label'     => 'Top insumos más movidos',
            'grupo'     => 'movimientos',
            'permiso'   => 'movimientos_insumos',
            'chart'     => 'barras',
            'usa_fecha' => true,
        ],
        'mov_destino' => [
            'label'     => 'Asignaciones por tipo de destino',
            'grupo'     => 'movimientos',
            'permiso'   => 'movimientos_insumos',
            'chart'     => 'donut',
            'usa_fecha' => true,
        ],
        'mov_usuario' => [
            'label'     => 'Movimientos por usuario',
            'grupo'     => 'movimientos',
            'permiso'   => 'movimientos_insumos',
            'chart'     => 'barras',
            'usa_fecha' => true,
        ],

        // ============ CHOFERES / EVENTOS ============
        'cho_licencias' => [
            'label'   => 'Choferes con licencia por vencer',
            'grupo'   => 'otros',
            'permiso' => 'choferes',
            'chart'   => 'lista',
        ],
        'eve_por_mes' => [
            'label'     => 'Eventos por mes',
            'grupo'     => 'otros',
            'permiso'   => 'eventos',
            'chart'     => 'barras',
            'usa_fecha' => true,
        ],

    ],

];
