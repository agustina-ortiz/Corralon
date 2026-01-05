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
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->enum('tipo_combustible', ['nafta', 'diesel', 'gas'])->nullable()->after('patente');
            $table->date('vencimiento_oblea')->nullable()->after('tipo_combustible')->comment('Solo para vehículos a gas');
            $table->string('nro_poliza')->nullable()->after('vencimiento_oblea');
            $table->string('documento_titulo')->nullable()->after('nro_poliza');
            $table->string('documento_poliza')->nullable()->after('documento_titulo');
            $table->date('vencimiento_poliza')->nullable()->after('documento_poliza');
            $table->date('vencimiento_vtv')->nullable()->after('vencimiento_poliza');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropColumn([
            'tipo_combustible',
            'vencimiento_oblea',
            'nro_poliza',
            'documento_titulo',
            'documento_poliza',
            'vencimiento_poliza',
            'vencimiento_vtv'
            ]);
        });
    }
};
