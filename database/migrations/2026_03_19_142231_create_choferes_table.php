<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('choferes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('dni')->unique();
            $table->string('numero_empleado')->unique();
            $table->string('licencia')->nullable();
            $table->string('tipo_licencia')->nullable();
            $table->date('vencimiento_licencia')->nullable();
            $table->string('domicilio')->nullable();

            $table->unsignedBigInteger('secretaria_id')->nullable();
            $table->string('area')->nullable();

            $table->timestamps();

            $table->foreign('secretaria_id')
                  ->references('id')
                  ->on('secretarias')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('choferes');
    }
};