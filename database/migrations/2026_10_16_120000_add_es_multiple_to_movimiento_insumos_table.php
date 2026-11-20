<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimiento_insumos', function (Blueprint $table) {
            $table->boolean('es_multiple')->default(false)->after('observaciones');
        });

        // Marcar los movimientos múltiples ya cargados: asignaciones sueltas que comparten
        // usuario + instante de creación + destinatario (guardarMultiple() las crea en una
        // misma transacción). Una carga múltiple de una sola línea no es distinguible.
        if (DB::getDriverName() === 'sqlite') {
            // Solo tests (SQLite en memoria): base vacía, no hay nada que marcar.
            return;
        }

        DB::statement("
            UPDATE movimiento_insumos m
            JOIN tipo_movimientos t ON t.id = m.id_tipo_movimiento
            JOIN (
                SELECT id_usuario, created_at, tipo_referencia, id_referencia
                FROM movimiento_insumos
                WHERE id_movimiento_encabezado IS NULL
                GROUP BY id_usuario, created_at, tipo_referencia, id_referencia
                HAVING COUNT(*) > 1
            ) g ON g.id_usuario = m.id_usuario
               AND g.created_at = m.created_at
               AND g.tipo_referencia = m.tipo_referencia
               AND g.id_referencia = m.id_referencia
            SET m.es_multiple = 1
            WHERE m.id_movimiento_encabezado IS NULL
              AND t.tipo_movimiento IN ('Asignación con Reposición', 'Asignación sin Reposición')
        ");
    }

    public function down(): void
    {
        Schema::table('movimiento_insumos', function (Blueprint $table) {
            $table->dropColumn('es_multiple');
        });
    }
};
