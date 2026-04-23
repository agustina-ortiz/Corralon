<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tipos = [
            ['tipo_movimiento' => 'Ajuste Negativo',        'tipo' => 'IM'],
            ['tipo_movimiento' => 'Ajuste Positivo',        'tipo' => 'IM'],
            ['tipo_movimiento' => 'Asignación Maquinaria', 'tipo' => 'M'],
            ['tipo_movimiento' => 'Carga de Stock',         'tipo' => 'I'],
            ['tipo_movimiento' => 'Carga de Stock Maquinaria',         'tipo' => 'M'],
            ['tipo_movimiento' => 'Devolución',             'tipo' => 'IM'],
            ['tipo_movimiento' => 'Inventario Inicial',     'tipo' => 'I'],
            ['tipo_movimiento' => 'Inventario Inicial Maquinaria',     'tipo' => 'M'],
            ['tipo_movimiento' => 'Mantenimiento Maquinaria', 'tipo' => 'M'],
            ['tipo_movimiento' => 'Transferencia Entrada',  'tipo' => 'I'],
            ['tipo_movimiento' => 'Transferencia Entrada Maquinaria',  'tipo' => 'M'],
            ['tipo_movimiento' => 'Transferencia Salida',   'tipo' => 'I'],
            ['tipo_movimiento' => 'Transferencia Salida Maquinaria',   'tipo' => 'M'],
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

    public function down(): void
    {
        DB::table('tipo_movimientos')->whereIn('tipo_movimiento', [
            'Ajuste Positivo',
            'Ajuste Negativo',
            'Asignación Maquinaria',
            'Carga de Stock',
            'Carga de Stock Maquinaria',
            'Devolución',
            'Inventario Inicial',
            'Inventario Inicial Maquinaria',
            'Mantenimiento Maquinaria',
            'Transferencia Entrada',
            'Transferencia Entrada Maquinaria',
            'Transferencia Salida',
            'Transferencia Salida Maquinaria',
        ])->delete();
    }
};
