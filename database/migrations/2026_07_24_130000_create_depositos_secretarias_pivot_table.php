<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivote muchos-a-muchos: cada secretaría puede tener asignados varios
     * depósitos (y un depósito puede pertenecer a varias secretarías).
     * Sin ABM: las asignaciones se cargan directamente en la base de datos.
     */
    public function up(): void
    {
        Schema::create('depositos_secretarias', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_secretaria');
            $table->unsignedBigInteger('id_deposito');

            $table->timestamps();

            $table->foreign('id_secretaria')
                  ->references('id')
                  ->on('secretarias')
                  ->onDelete('cascade');

            $table->foreign('id_deposito')
                  ->references('id')
                  ->on('depositos')
                  ->onDelete('cascade');

            // Evita duplicar la misma asignación secretaría + depósito
            $table->unique(['id_secretaria', 'id_deposito']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depositos_secretarias');
    }
};
