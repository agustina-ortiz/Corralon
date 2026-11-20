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
            Schema::table('vehiculos', fn (Blueprint $table) => $table->unsignedBigInteger('id_deposito')->nullable()->change());
            return;
        }

        DB::statement("ALTER TABLE vehiculos MODIFY id_deposito BIGINT UNSIGNED NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Solo tests (SQLite en memoria). En MySQL sigue el SQL de siempre.
            Schema::table('vehiculos', fn (Blueprint $table) => $table->unsignedBigInteger('id_deposito')->nullable(false)->change());
            return;
        }

        DB::statement("ALTER TABLE vehiculos MODIFY id_deposito BIGINT UNSIGNED NOT NULL");
    }
};
