<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            // Permisos ABM
            $table->boolean('lInsumosABM')->default(false)->after('descripcion');
            $table->boolean('lMaquinariasABM')->default(false)->after('lInsumosABM');
            $table->boolean('lVehiculosABM')->default(false)->after('lMaquinariasABM');
            $table->boolean('lCategoriasInsumosABM')->default(false)->after('lVehiculosABM');
            $table->boolean('lCategoriasMaquinariasABM')->default(false)->after('lCategoriasInsumosABM');
            $table->boolean('lDepositosABM')->default(false)->after('lCategoriasMaquinariasABM');
            $table->boolean('lEventosABM')->default(false)->after('lDepositosABM');
            $table->boolean('lUsuariosABM')->default(false)->after('lEventosABM');
            
            // Permisos de Movimientos
            $table->boolean('lMovimientosInsumos')->default(false)->after('lUsuariosABM');
            $table->boolean('lMovimientosMaquinarias')->default(false)->after('lMovimientosInsumos');
        });
    }

    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn([
                'lInsumosABM',
                'lMaquinariasABM',
                'lVehiculosABM',
                'lCategoriasInsumosABM',
                'lCategoriasMaquinariasABM',
                'lDepositosABM',
                'lEventosABM',
                'lUsuariosABM',
                'lMovimientosInsumos',
                'lMovimientosMaquinarias',
            ]);
        });
    }
};