<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Pasar datos de modelo a anio (solo valores de 4 dígitos, donde anio esté vacío)
        DB::statement("UPDATE vehiculos SET anio = modelo WHERE (anio IS NULL OR anio = '') AND modelo REGEXP '^[0-9]{4}$'");

        // 2. Renombrar marca a marca_modelo
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->renameColumn('marca', 'marca_modelo');
        });

        // 3. Eliminar columna modelo y agregar nuevos campos
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropColumn('modelo');
            $table->string('origen')->nullable()->after('vencimiento_vtv');
            $table->string('jurisdiccion_procedencia')->nullable()->after('origen');
            $table->string('nro_telepase')->nullable()->after('jurisdiccion_procedencia');
        });
    }

    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropColumn(['origen', 'jurisdiccion_procedencia', 'nro_telepase']);
        });

        Schema::table('vehiculos', function (Blueprint $table) {
            $table->renameColumn('marca_modelo', 'marca');
        });

        Schema::table('vehiculos', function (Blueprint $table) {
            $table->string('modelo')->nullable()->after('marca');
        });
    }
};
