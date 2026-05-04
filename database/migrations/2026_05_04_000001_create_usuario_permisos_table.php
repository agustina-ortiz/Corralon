<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_permisos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_corralon')->nullable();
            $table->unsignedBigInteger('id_deposito')->nullable();
            $table->string('modulo', 50);
            $table->enum('nivel_acceso', ['ver', 'editar'])->default('ver');
            $table->timestamps();

            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_corralon')->references('id')->on('corralones')->onDelete('cascade');
            $table->foreign('id_deposito')->references('id')->on('depositos')->onDelete('cascade');

            $table->unique(['id_usuario', 'id_corralon', 'id_deposito', 'modulo'], 'usuario_permiso_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_permisos');
    }
};
