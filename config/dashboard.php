<?php

return [

    'cards' => [
        'card_insumos'    => ['label' => 'Total Insumos',    'permiso' => 'lInsumosABM'],
        'card_maquinaria' => ['label' => 'Maquinaria',       'permiso' => 'lMaquinariasABM'],
        'card_vehiculos'  => ['label' => 'Vehículos',        'permiso' => 'lVehiculosABM'],
        'card_eventos'    => ['label' => 'Próximos Eventos', 'permiso' => 'lEventosABM'],
    ],

    'widgets' => [
        'stock_bajo'       => ['label' => 'Insumos con Stock Bajo', 'permiso' => 'lInsumosABM'],
        'vtv_vencer'       => ['label' => 'VTVs Próximas a Vencer', 'permiso' => 'lVehiculosABM'],
        'vehiculos_en_uso' => ['label' => 'Vehículos en Uso',       'permiso' => 'lVehiculosABM'],
        'proximos_eventos' => ['label' => 'Próximos Eventos',       'permiso' => 'lEventosABM'],
    ],

];
