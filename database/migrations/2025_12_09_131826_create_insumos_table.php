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
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->string('insumo');
            $table->foreignId('id_categoria')->constrained('categorias_insumos')->onDelete('no action');
            $table->string('unidad');
            $table->decimal('medida', 10, 2)->nullable();
            $table->integer('stock_minimo')->default(0);
            $table->foreignId('id_deposito')->constrained('depositos')->onDelete('no action');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insumos');
    }
};
