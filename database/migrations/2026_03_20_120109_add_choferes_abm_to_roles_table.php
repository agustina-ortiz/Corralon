<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('lChoferesABM')->default(false)->after('lEmpleadosABM');
        });

        // Dar acceso al rol Administrador
        DB::table('roles')->where('nombre', 'Administrador')->update(['lChoferesABM' => true]);
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('lChoferesABM');
        });
    }
};
