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
            Schema::table('vehiculos', function (Blueprint $table) {
                foreach (['marca_modelo', 'nro_motor', 'nro_chasis', 'patente'] as $columna) {
                    $table->string($columna, 191)->nullable()->change();
                }
            });
            return;
        }

        DB::statement("ALTER TABLE vehiculos MODIFY marca_modelo VARCHAR(191) NULL");
        DB::statement("ALTER TABLE vehiculos MODIFY nro_motor VARCHAR(191) NULL");
        DB::statement("ALTER TABLE vehiculos MODIFY nro_chasis VARCHAR(191) NULL");
        DB::statement("ALTER TABLE vehiculos MODIFY patente VARCHAR(191) NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Solo tests (SQLite en memoria). En MySQL sigue el SQL de siempre.
            Schema::table('vehiculos', function (Blueprint $table) {
                foreach (['marca_modelo', 'nro_motor', 'nro_chasis', 'patente'] as $columna) {
                    $table->string($columna, 191)->nullable(false)->change();
                }
            });
            return;
        }

        DB::statement("ALTER TABLE vehiculos MODIFY marca_modelo VARCHAR(191) NOT NULL");
        DB::statement("ALTER TABLE vehiculos MODIFY nro_motor VARCHAR(191) NOT NULL");
        DB::statement("ALTER TABLE vehiculos MODIFY nro_chasis VARCHAR(191) NOT NULL");
        DB::statement("ALTER TABLE vehiculos MODIFY patente VARCHAR(191) NOT NULL");
    }
};
