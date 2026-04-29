<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            // 1. Eliminar FK y columna id_deposito
            $table->dropForeign(['id_deposito']);
            $table->dropColumn('id_deposito');

            // 2. Convertir id_secretaria a FK hacia secretarias
            //    Primero se modifica el tipo de columna a unsignedBigInteger
            //    (actualmente es varchar)
            $table->unsignedBigInteger('id_secretaria')->change();
        });

        Schema::table('vehiculos', function (Blueprint $table) {
            // Se agrega la FK en un segundo llamado para evitar problemas
            // con el change() anterior en algunos drivers
            $table->foreign('id_secretaria')
                  ->references('id')
                  ->on('secretarias');
        });
    }

    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropForeign(['id_secretaria']);

            // Revertir id_secretaria a varchar como estaba originalmente
            $table->string('id_secretaria')->change();

            // Restaurar columna id_deposito con su FK
            $table->unsignedBigInteger('id_deposito')->nullable();
            $table->foreign('id_deposito')
                  ->references('id')
                  ->on('depositos');
        });
    }
};