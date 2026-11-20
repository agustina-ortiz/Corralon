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
            DB::statement('DROP INDEX IF EXISTS vehiculos_nro_movil_unique');
            return;
        }

        DB::statement("ALTER TABLE vehiculos DROP INDEX vehiculos_nro_movil_unique");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Solo tests (SQLite en memoria). En MySQL sigue el SQL de siempre.
            Schema::table('vehiculos', fn (Blueprint $table) => $table->unique('nro_patrimonio', 'vehiculos_nro_movil_unique'));
            return;
        }

        DB::statement("ALTER TABLE vehiculos ADD UNIQUE vehiculos_nro_movil_unique (nro_patrimonio)");
    }
};
