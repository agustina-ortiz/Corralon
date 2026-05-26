<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE vehiculos DROP INDEX vehiculos_nro_movil_unique");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE vehiculos ADD UNIQUE vehiculos_nro_movil_unique (nro_patrimonio)");
    }
};
