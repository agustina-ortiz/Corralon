<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimiento_maquinarias', function (Blueprint $table) {
            $table->integer('cantidad')->default(1)->after('id_maquinaria');
        });
    }

    public function down(): void
    {
        Schema::table('movimiento_maquinarias', function (Blueprint $table) {
            $table->dropColumn('cantidad');
        });
    }
};