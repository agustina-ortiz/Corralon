<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tipos = [
            ['tipo_movimiento' => 'Asignación Maquinaria con Reposición', 'tipo' => 'M'],
            ['tipo_movimiento' => 'Asignación Maquinaria sin Reposición', 'tipo' => 'M'],
            ['tipo_movimiento' => 'Entrada Reposición Maquinaria', 'tipo' => 'M'],
            ['tipo_movimiento' => 'Baja Reposición Maquinaria', 'tipo' => 'M'],
        ];

        foreach ($tipos as $tipo) {
            DB::table('tipo_movimientos')->updateOrInsert(
                ['tipo_movimiento' => $tipo['tipo_movimiento']],
                $tipo
            );
        }
    }

    public function down(): void
    {
        DB::table('tipo_movimientos')->whereIn('tipo_movimiento', [
            'Asignación Maquinaria con Reposición',
            'Asignación Maquinaria sin Reposición',
            'Entrada Reposición Maquinaria',
            'Baja Reposición Maquinaria',
        ])->delete();
    }
};
