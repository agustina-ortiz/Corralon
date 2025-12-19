<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('corralones_permitidos')->nullable()->after('password');
            $table->boolean('acceso_todos_corralones')->default(false)->after('corralones_permitidos');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['corralones_permitidos', 'acceso_todos_corralones']);
        });
    }
};