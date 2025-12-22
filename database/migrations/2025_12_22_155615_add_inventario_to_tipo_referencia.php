<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE movimiento_insumos MODIFY COLUMN tipo_referencia ENUM('empleado', 'evento', 'maquina', 'secretaria', 'transferencia', 'inventario')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE movimiento_insumos MODIFY COLUMN tipo_referencia ENUM('empleado', 'evento', 'maquina', 'secretaria', 'transferencia')");
    }
};