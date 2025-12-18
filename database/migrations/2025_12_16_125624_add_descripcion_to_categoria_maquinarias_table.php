<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categoria_maquinarias', function (Blueprint $table) {
            // Solo agregar si no existe
            if (!Schema::hasColumn('categoria_maquinarias', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('nombre');
            }
        });
    }

    public function down(): void
    {
        Schema::table('categoria_maquinarias', function (Blueprint $table) {
            if (Schema::hasColumn('categoria_maquinarias', 'descripcion')) {
                $table->dropColumn('descripcion');
            }
        });
    }
};