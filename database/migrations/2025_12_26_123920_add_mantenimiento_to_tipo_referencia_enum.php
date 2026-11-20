<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Solo tests (SQLite en memoria). En MySQL sigue el SQL de siempre.
            Schema::table('movimiento_maquinarias', fn (Blueprint $table) => $table->enum('tipo_referencia', ['empleado', 'evento', 'secretaria', 'deposito', 'mantenimiento'])->change());
            return;
        }

        DB::statement("ALTER TABLE movimiento_maquinarias MODIFY COLUMN tipo_referencia ENUM('empleado', 'evento', 'secretaria', 'deposito', 'mantenimiento') NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Solo tests (SQLite en memoria). En MySQL sigue el SQL de siempre.
            Schema::table('movimiento_maquinarias', fn (Blueprint $table) => $table->enum('tipo_referencia', ['empleado', 'evento', 'secretaria', 'deposito'])->change());
            return;
        }

        // Primero cambiar 'mantenimiento' a otro valor válido
        DB::table('movimiento_maquinarias')
            ->where('tipo_referencia', 'mantenimiento')
            ->update(['tipo_referencia' => 'evento']);
            
        DB::statement("ALTER TABLE movimiento_maquinarias MODIFY COLUMN tipo_referencia ENUM('empleado', 'evento', 'secretaria', 'deposito') NOT NULL");
    }
};