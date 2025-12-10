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
        Schema::create('maquinarias', function (Blueprint $table) {
            $table->id();
            $table->string('maquinaria');
            $table->foreignId('id_categoria_maquinaria')->constrained('categoria_maquinarias')->onDelete('no action');
            $table->enum('estado', ['disponible', 'no disponible'])->default('disponible');
            $table->foreignId('id_deposito')->constrained('depositos')->onDelete('no action');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maquinarias');
    }
};
