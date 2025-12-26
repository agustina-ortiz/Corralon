<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ✅ PASO 1: Actualizar registros con valores incorrectos a un valor válido
        DB::table('movimiento_maquinarias')
            ->whereNotIn('tipo_referencia', ['empleado', 'evento', 'secretaria'])
            ->whereNotNull('tipo_referencia')
            ->update(['tipo_referencia' => 'empleado']); // Temporal, asigna cualquier valor válido

        // ✅ PASO 2: Actualizar registros NULL (si los hay)
        DB::table('movimiento_maquinarias')
            ->whereNull('tipo_referencia')
            ->update(['tipo_referencia' => 'empleado']);

        // ✅ PASO 3: Modificar el ENUM
        DB::statement("ALTER TABLE movimiento_maquinarias MODIFY COLUMN tipo_referencia ENUM('empleado', 'evento', 'secretaria', 'deposito') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Primero cambiar 'deposito' a algo válido del enum anterior
        DB::table('movimiento_maquinarias')
            ->where('tipo_referencia', 'deposito')
            ->update(['tipo_referencia' => 'empleado']);
            
        DB::statement("ALTER TABLE movimiento_maquinarias MODIFY COLUMN tipo_referencia ENUM('empleado', 'evento', 'secretaria') NOT NULL");
    }
};
