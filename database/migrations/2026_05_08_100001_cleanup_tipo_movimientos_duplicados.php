<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Los firstOrCreate anteriores pudieron crear duplicados con tipo='E'.
        // Reasignar movimientos que referencien esos duplicados al registro correcto (tipo I o IM),
        // y luego eliminar los duplicados.
        $duplicados = DB::table('tipo_movimientos')
            ->where('tipo', 'E')
            ->get();

        foreach ($duplicados as $duplicado) {
            // Buscar el registro correcto (mismo nombre, tipo I o IM)
            $correcto = DB::table('tipo_movimientos')
                ->where('tipo_movimiento', $duplicado->tipo_movimiento)
                ->whereIn('tipo', ['I', 'IM', 'M'])
                ->first();

            if ($correcto) {
                // Reasignar movimientos de insumos
                DB::table('movimiento_insumos')
                    ->where('id_tipo_movimiento', $duplicado->id)
                    ->update(['id_tipo_movimiento' => $correcto->id]);

                // Reasignar movimientos de maquinarias
                DB::table('movimiento_maquinarias')
                    ->where('id_tipo_movimiento', $duplicado->id)
                    ->update(['id_tipo_movimiento' => $correcto->id]);

                // Eliminar el duplicado
                DB::table('tipo_movimientos')->where('id', $duplicado->id)->delete();
            }
        }
    }

    public function down(): void
    {
        // No se puede revertir de forma confiable
    }
};
