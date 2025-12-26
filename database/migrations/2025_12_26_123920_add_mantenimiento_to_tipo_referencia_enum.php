<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE movimiento_maquinarias MODIFY COLUMN tipo_referencia ENUM('empleado', 'evento', 'secretaria', 'deposito', 'mantenimiento') NOT NULL");
    }

    public function down(): void
    {
        // Primero cambiar 'mantenimiento' a otro valor válido
        DB::table('movimiento_maquinarias')
            ->where('tipo_referencia', 'mantenimiento')
            ->update(['tipo_referencia' => 'evento']);
            
        DB::statement("ALTER TABLE movimiento_maquinarias MODIFY COLUMN tipo_referencia ENUM('empleado', 'evento', 'secretaria', 'deposito') NOT NULL");
    }
};