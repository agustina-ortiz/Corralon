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
        Schema::create('categoria_insumo_movimiento', function (Blueprint $table) {
            $table->foreignId('id_categoria')->constrained('categorias_insumos')->onDelete('cascade');
            $table->foreignId('id_movimiento')->constrained('tipo_movimientos')->onDelete('cascade');
            
            // Clave primaria compuesta
            $table->primary(['id_categoria', 'id_movimiento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categoria_insumo_movimiento');
    }
};
