<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Primero agregar como nullable
        DB::statement('ALTER TABLE vehiculos ADD COLUMN nro_movil VARCHAR(50) NULL AFTER id');
        
        // Asignar un valor temporal a los registros existentes
        DB::statement("UPDATE vehiculos SET nro_movil = CONCAT('TEMP-', id) WHERE nro_movil IS NULL");
        
        // Ahora hacer NOT NULL y UNIQUE
        DB::statement('ALTER TABLE vehiculos MODIFY COLUMN nro_movil VARCHAR(50) NOT NULL');
        DB::statement('ALTER TABLE vehiculos ADD UNIQUE INDEX vehiculos_nro_movil_unique (nro_movil)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE vehiculos DROP INDEX IF EXISTS vehiculos_nro_movil_unique');
        DB::statement('ALTER TABLE vehiculos DROP COLUMN IF EXISTS nro_movil');
    }
};