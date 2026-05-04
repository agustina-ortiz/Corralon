<?php

return [

    'cards' => [
        'card_insumos'    => ['label' => 'Total Insumos',    'permiso_modulo' => 'insumos'],
        'card_maquinaria' => ['label' => 'Maquinaria',       'permiso_modulo' => 'maquinarias'],
        'card_vehiculos'  => ['label' => 'Vehículos',        'permiso_modulo' => 'vehiculos'],
        'card_eventos'    => ['label' => 'Próximos Eventos', 'permiso_modulo' => 'eventos'],
    ],

    'widgets' => [
        'stock_bajo'       => ['label' => 'Insumos con Stock Bajo', 'permiso_modulo' => 'insumos'],
        'vtv_vencer'       => ['label' => 'VTVs Próximas a Vencer', 'permiso_modulo' => 'vehiculos'],
        'vehiculos_en_uso' => ['label' => 'Vehículos en Uso',       'permiso_modulo' => 'vehiculos'],
        'proximos_eventos' => ['label' => 'Próximos Eventos',       'permiso_modulo' => 'eventos'],
    ],

];
