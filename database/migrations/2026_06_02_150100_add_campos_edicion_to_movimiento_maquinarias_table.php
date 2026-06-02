<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimiento_maquinarias', function (Blueprint $table) {
            $table->string('nro_orden_compra')->nullable()->after('cantidad');
            $table->text('observaciones')->nullable()->after('nro_orden_compra');
        });
    }

    public function down(): void
    {
        Schema::table('movimiento_maquinarias', function (Blueprint $table) {
            $table->dropColumn(['nro_orden_compra', 'observaciones']);
        });
    }
};
