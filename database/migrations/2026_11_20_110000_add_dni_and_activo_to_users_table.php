<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Integración con la intranet (intranet/docs/integracion.md §4). Aditiva:
 *
 * - `dni`: el de la persona, para entrar desde la intranet por SSO y para que la
 *   intranet encuentre al usuario por API. Opcional y único (nadie lo tiene todavía).
 * - `activo`: un usuario inactivo no entra (ni con su clave ni desde la intranet) y
 *   pierde sus sesiones. Todos los usuarios que ya existen quedan activos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('dni')->nullable()->unique()->after('email');
            $table->boolean('activo')->default(true)->after('dni');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['dni']);
            $table->dropColumn(['dni', 'activo']);
        });
    }
};
