<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->string('vehiculo');
            $table->string('marca');
            $table->string('nro_motor');
            $table->string('nro_chasis');
            $table->string('modelo');
            $table->string('patente');
            $table->string('id_secretaria');
            $table->string('estado');
            $table->foreignId('id_deposito')->constrained('depositos')->onDelete('no action');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
