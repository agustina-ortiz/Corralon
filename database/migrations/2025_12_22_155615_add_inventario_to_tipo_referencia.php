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
            Schema::table('movimiento_insumos', fn (Blueprint $table) => $table->enum('tipo_referencia', ['empleado', 'evento', 'maquina', 'secretaria', 'transferencia', 'inventario'])->nullable()->change());
            return;
        }

        DB::statement("ALTER TABLE movimiento_insumos MODIFY COLUMN tipo_referencia ENUM('empleado', 'evento', 'maquina', 'secretaria', 'transferencia', 'inventario')");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Solo tests (SQLite en memoria). En MySQL sigue el SQL de siempre.
            Schema::table('movimiento_insumos', fn (Blueprint $table) => $table->enum('tipo_referencia', ['empleado', 'evento', 'maquina', 'secretaria', 'transferencia'])->nullable()->change());
            return;
        }

        DB::statement("ALTER TABLE movimiento_insumos MODIFY COLUMN tipo_referencia ENUM('empleado', 'evento', 'maquina', 'secretaria', 'transferencia')");
    }
};