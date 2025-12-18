<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('movimiento_encabezados', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->unsignedBigInteger('id_deposito_origen');
            $table->unsignedBigInteger('id_deposito_destino');
            $table->text('observaciones')->nullable();
            $table->unsignedBigInteger('id_usuario');
            $table->timestamps();

            $table->foreign('id_deposito_origen')->references('id')->on('depositos');
            $table->foreign('id_deposito_destino')->references('id')->on('depositos');
            $table->foreign('id_usuario')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimiento_encabezados');
    }
};
