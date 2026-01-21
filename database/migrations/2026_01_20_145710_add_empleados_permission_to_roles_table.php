<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            // Agregar el permiso lEmpleadosABM después de lEventosABM
            $table->boolean('lEmpleadosABM')->default(false)->after('lEventosABM');
        });
    }

    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('lEmpleadosABM');
        });
    }
};