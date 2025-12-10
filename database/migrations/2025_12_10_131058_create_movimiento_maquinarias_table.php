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
        Schema::create('movimiento_maquinarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_maquinaria')->constrained('maquinarias')->onDelete('no action');
            $table->foreignId('id_tipo_movimiento')->constrained('tipo_movimientos')->onDelete('no action');
            $table->date('fecha');
            $table->date('fecha_devolucion')->nullable();
            $table->foreignId('id_usuario')->constrained('users')->onDelete('no action');
            $table->unsignedBigInteger('id_referencia')->nullable();
            $table->enum('tipo_referencia', ['empleado', 'maquina', 'evento', 'secretaria']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_maquinarias');
    }
};
