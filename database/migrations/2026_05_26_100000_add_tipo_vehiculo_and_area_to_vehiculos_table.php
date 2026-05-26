<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_tipo_vehiculo')->nullable()->after('id');
            $table->string('area')->nullable()->after('estado');

            $table->foreign('id_tipo_vehiculo')->references('id')->on('tipos_vehiculos')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropForeign(['id_tipo_vehiculo']);
            $table->dropColumn(['id_tipo_vehiculo', 'area']);
        });
    }
};
