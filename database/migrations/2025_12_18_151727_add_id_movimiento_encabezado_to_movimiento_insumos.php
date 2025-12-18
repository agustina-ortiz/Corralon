<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('movimiento_insumos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_movimiento_encabezado')
                  ->nullable()
                  ->after('id');

            $table->foreign('id_movimiento_encabezado')
                  ->references('id')
                  ->on('movimiento_encabezados')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movimiento_insumos', function (Blueprint $table) {
            $table->dropForeign(['id_movimiento_encabezado']);
            $table->dropColumn('id_movimiento_encabezado');
        });
    }
};