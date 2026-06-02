<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comprobantes_movimiento_maquinaria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_movimiento_maquinaria')
                ->constrained('movimiento_maquinarias', 'id', 'cmm_mov_maq_fk')
                ->onDelete('cascade');
            $table->string('archivo'); // path en storage
            $table->string('nombre_original');
            $table->string('tipo_mime', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobantes_movimiento_maquinaria');
    }
};
