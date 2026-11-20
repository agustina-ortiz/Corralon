<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de actividad (spatie/laravel-activitylog, kit spec 38): quién creó,
 * modificó o borró qué y cuándo, con el antes y el después de cada campo. Lo escribe
 * el trait `RegistraActividad` y lo muestra la pantalla Actividad.
 *
 * Es el esquema del paquete (con `event` y `batch_uuid`) más `contexto`: dónde estaba
 * el registro ("Permiso de Juan Pérez"), fijado en el momento junto con el nombre
 * (`description`). Migración aditiva: no toca ninguna tabla existente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->string('contexto', 500)->nullable();
            $table->nullableMorphs('subject', 'subject');
            $table->string('event')->nullable();
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();

            $table->index('log_name');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
