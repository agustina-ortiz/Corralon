<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE vehiculos MODIFY marca_modelo VARCHAR(191) NULL");
        DB::statement("ALTER TABLE vehiculos MODIFY nro_motor VARCHAR(191) NULL");
        DB::statement("ALTER TABLE vehiculos MODIFY nro_chasis VARCHAR(191) NULL");
        DB::statement("ALTER TABLE vehiculos MODIFY patente VARCHAR(191) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE vehiculos MODIFY marca_modelo VARCHAR(191) NOT NULL");
        DB::statement("ALTER TABLE vehiculos MODIFY nro_motor VARCHAR(191) NOT NULL");
        DB::statement("ALTER TABLE vehiculos MODIFY nro_chasis VARCHAR(191) NOT NULL");
        DB::statement("ALTER TABLE vehiculos MODIFY patente VARCHAR(191) NOT NULL");
    }
};
