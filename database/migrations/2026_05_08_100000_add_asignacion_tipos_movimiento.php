<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar 'vehiculo' al enum tipo_referencia en movimiento_insumos
        DB::statement("ALTER TABLE movimiento_insumos MODIFY COLUMN tipo_referencia ENUM('empleado', 'evento', 'maquina', 'secretaria', 'transferencia', 'inventario', 'vehiculo')");

        // Insertar nuevos tipos de movimiento si no existen
        $tipos = [
            ['tipo_movimiento' => 'Asignación con Reposición', 'tipo' => 'I'],
            ['tipo_movimiento' => 'Asignación sin Reposición', 'tipo' => 'I'],
            ['tipo_movimiento' => 'Entrada Reposición', 'tipo' => 'I'],
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
        // Eliminar los tipos de movimiento agregados
        DB::table('tipo_movimientos')->whereIn('tipo_movimiento', [
            'Asignación con Reposición',
            'Asignación sin Reposición',
            'Entrada Reposición',
        ])->delete();

        // Revertir enum (quitar 'vehiculo')
        DB::statement("ALTER TABLE movimiento_insumos MODIFY COLUMN tipo_referencia ENUM('empleado', 'evento', 'maquina', 'secretaria', 'transferencia', 'inventario')");
    }
};
