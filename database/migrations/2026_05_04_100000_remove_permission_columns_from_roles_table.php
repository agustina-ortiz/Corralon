<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $columns = [
                'lInsumosABM',
                'lMaquinariasABM',
                'lVehiculosABM',
                'lCategoriasInsumosABM',
                'lCategoriasMaquinariasABM',
                'lDepositosABM',
                'lEventosABM',
                'lEmpleadosABM',
                'lChoferesABM',
                'lUsuariosABM',
                'lMovimientosInsumos',
                'lMovimientosMaquinarias',
            ];

            foreach ($columns as $col) {
                if (Schema::hasColumn('roles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('lInsumosABM')->default(false);
            $table->boolean('lMaquinariasABM')->default(false);
            $table->boolean('lVehiculosABM')->default(false);
            $table->boolean('lCategoriasInsumosABM')->default(false);
            $table->boolean('lCategoriasMaquinariasABM')->default(false);
            $table->boolean('lDepositosABM')->default(false);
            $table->boolean('lEventosABM')->default(false);
            $table->boolean('lEmpleadosABM')->default(false);
            $table->boolean('lChoferesABM')->default(false);
            $table->boolean('lUsuariosABM')->default(false);
            $table->boolean('lMovimientosInsumos')->default(false);
            $table->boolean('lMovimientosMaquinarias')->default(false);
        });
    }
};
