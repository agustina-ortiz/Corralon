<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Solo tests (SQLite en memoria). En MySQL sigue el SQL de siempre.
            if (! Schema::hasColumn('movimiento_maquinarias', 'id_deposito_entrada')) {
                Schema::table('movimiento_maquinarias', function (Blueprint $table) {
                    $table->unsignedBigInteger('id_deposito_entrada')->nullable();
                    $table->foreign('id_deposito_entrada', 'fk_movimiento_maquinarias_deposito_entrada')->references('id')->on('depositos')->onDelete('restrict');
                });
            }
            return;
        }

        // ✅ Verificar primero si la columna NO existe
        $hasColumn = DB::select("SHOW COLUMNS FROM movimiento_maquinarias LIKE 'id_deposito_entrada'");
        
        if (empty($hasColumn)) {
            // Agregar columna con SQL directo
            DB::statement('ALTER TABLE movimiento_maquinarias ADD COLUMN id_deposito_entrada BIGINT UNSIGNED NULL AFTER id_usuario');
            
            // Agregar foreign key
            DB::statement('ALTER TABLE movimiento_maquinarias ADD CONSTRAINT fk_movimiento_maquinarias_deposito_entrada 
                          FOREIGN KEY (id_deposito_entrada) REFERENCES depositos(id) ON DELETE RESTRICT');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Solo tests (SQLite en memoria). En MySQL sigue el SQL de siempre.
            if (Schema::hasColumn('movimiento_maquinarias', 'id_deposito_entrada')) {
                Schema::table('movimiento_maquinarias', function (Blueprint $table) {
                    $table->dropForeign('fk_movimiento_maquinarias_deposito_entrada');
                    $table->dropColumn('id_deposito_entrada');
                });
            }
            return;
        }

        $hasColumn = DB::select("SHOW COLUMNS FROM movimiento_maquinarias LIKE 'id_deposito_entrada'");
        
        if (!empty($hasColumn)) {
            // Eliminar foreign key primero
            DB::statement('ALTER TABLE movimiento_maquinarias DROP FOREIGN KEY fk_movimiento_maquinarias_deposito_entrada');
            
            // Eliminar columna
            DB::statement('ALTER TABLE movimiento_maquinarias DROP COLUMN id_deposito_entrada');
        }
    }
};