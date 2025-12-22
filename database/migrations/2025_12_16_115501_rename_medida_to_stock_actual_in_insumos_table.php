<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->integer('stock_actual')->after('medida');
        });

        DB::statement('UPDATE insumos SET stock_actual = medida');

        Schema::table('insumos', function (Blueprint $table) {
            $table->dropColumn('medida');
        });
    }

    public function down(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->integer('medida');
        });

        DB::statement('UPDATE insumos SET medida = stock_actual');

        Schema::table('insumos', function (Blueprint $table) {
            $table->dropColumn('stock_actual');
        });
    }

};