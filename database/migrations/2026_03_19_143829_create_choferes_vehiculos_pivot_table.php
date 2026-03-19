<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('choferes_vehiculos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('chofer_id');
            $table->unsignedBigInteger('vehiculo_id');

            $table->timestamps();

            $table->foreign('chofer_id')
                  ->references('id')
                  ->on('choferes')
                  ->onDelete('cascade');

            $table->foreign('vehiculo_id')
                  ->references('id')
                  ->on('vehiculos')
                  ->onDelete('cascade');

            // Evita duplicar asignaciones
            $table->unique(['chofer_id', 'vehiculo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('choferes_vehiculos');
    }
};