<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite' && Schema::hasColumn('vehiculos', 'id_deposito')) {
            // Solo tests (SQLite en memoria): la tabla ya nace con id_deposito.
            return;
        }

        Schema::table('vehiculos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_deposito')->nullable()->after('estado');
            $table->foreign('id_deposito')->references('id')->on('depositos')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropForeign(['id_deposito']);
            $table->dropColumn('id_deposito');
        });
    }
};
