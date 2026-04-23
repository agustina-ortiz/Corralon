<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoMovimientoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['tipo_movimiento' => 'Ajuste Positivo',          'tipo' => 'IM'],
            ['tipo_movimiento' => 'Ajuste Negativo',          'tipo' => 'IM'],
            ['tipo_movimiento' => 'Carga de Stock',           'tipo' => 'IM'],
            ['tipo_movimiento' => 'Inventario Inicial',       'tipo' => 'IM'],
            ['tipo_movimiento' => 'Mantenimiento Maquinaria', 'tipo' => 'M'],
            ['tipo_movimiento' => 'Transferencia Entrada',    'tipo' => 'IM'],
            ['tipo_movimiento' => 'Transferencia Salida',     'tipo' => 'IM'],
            ['tipo_movimiento' => 'Devolución',               'tipo' => 'IM'],
            ['tipo_movimiento' => 'Asignación Maquinaria',   'tipo' => 'M'],
        ];

        foreach ($tipos as $tipo) {
            DB::table('tipo_movimientos')->updateOrInsert(
                ['tipo_movimiento' => $tipo['tipo_movimiento']],
                array_merge($tipo, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
