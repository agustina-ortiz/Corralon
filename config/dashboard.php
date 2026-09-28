<?php

return [

    'cards' => [
        'card_insumos'    => ['label' => 'Total Insumos',    'permiso_modulo' => 'insumos'],
        'card_maquinaria' => ['label' => 'Maquinaria',       'permiso_modulo' => 'maquinarias'],
        'card_vehiculos'  => ['label' => 'Vehículos',        'permiso_modulo' => 'vehiculos'],
        'card_eventos'    => ['label' => 'Próximos Eventos', 'permiso_modulo' => 'eventos'],
    ],

    'widgets' => [
        'stock_bajo'           => ['label' => 'Insumos con Stock Bajo',          'permiso_modulo' => 'insumos'],
        'vtv_vencer'           => ['label' => 'VTVs Próximas a Vencer',          'permiso_modulo' => 'vehiculos'],
        'poliza_vencer'        => ['label' => 'Pólizas Próximas a Vencer',       'permiso_modulo' => 'vehiculos'],
        'oblea_vencer'         => ['label' => 'Obleas GNC Próximas a Vencer',    'permiso_modulo' => 'vehiculos'],
        'vehiculos_datos'      => ['label' => 'Vehículos con Datos Incompletos', 'permiso_modulo' => 'vehiculos'],
        'vehiculos_revisar'    => ['label' => 'Vehículos a Revisar',             'permiso_modulo' => 'vehiculos'],
        'vehiculos_en_uso'     => ['label' => 'Vehículos en Uso',                'permiso_modulo' => 'vehiculos'],
        'proximos_eventos'     => ['label' => 'Próximos Eventos',                'permiso_modulo' => 'eventos'],
    ],

    /*
     * Claves que existían antes de registrar 'conocidos' en las preferencias.
     * Sirve para que a los usuarios que ya guardaron su panel les aparezcan
     * activados los widgets agregados después (ver User::dashboardActivosPara()).
     */
    'conocidos_legado' => [
        'cards'   => ['card_insumos', 'card_maquinaria', 'card_vehiculos', 'card_eventos'],
        'widgets' => ['stock_bajo', 'vtv_vencer', 'vehiculos_en_uso', 'proximos_eventos'],
    ],

];
