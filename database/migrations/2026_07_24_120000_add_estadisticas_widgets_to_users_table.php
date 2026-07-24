<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Preferencias por usuario de qué widgets/gráficos ver en la solapa Estadísticas.
     * null = mostrar todos los que el usuario tenga permiso de ver.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('estadisticas_widgets')->nullable()->after('dashboard_widgets');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('estadisticas_widgets');
        });
    }
};
