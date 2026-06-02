<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimiento_insumos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_secretaria')->nullable()->after('tipo_referencia');
            $table->string('area', 255)->nullable()->after('id_secretaria');

            $table->foreign('id_secretaria')->references('id')->on('secretarias')->onDelete('set null');
        });

        Schema::table('movimiento_maquinarias', function (Blueprint $table) {
            $table->unsignedBigInteger('id_secretaria')->nullable()->after('tipo_referencia');
            $table->string('area', 255)->nullable()->after('id_secretaria');

            $table->foreign('id_secretaria')->references('id')->on('secretarias')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('movimiento_insumos', function (Blueprint $table) {
            $table->dropForeign(['id_secretaria']);
            $table->dropColumn(['id_secretaria', 'area']);
        });

        Schema::table('movimiento_maquinarias', function (Blueprint $table) {
            $table->dropForeign(['id_secretaria']);
            $table->dropColumn(['id_secretaria', 'area']);
        });
    }
};
