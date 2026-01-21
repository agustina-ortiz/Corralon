<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categoria_maquinarias', function (Blueprint $table) {
            $table->string('descripcion')
                  ->nullable()
                  ->after('nombre')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('categoria_maquinarias', function (Blueprint $table) {
            $table->string('descripcion')
                  ->nullable()
                  ->after('updated_at')
                  ->change();
        });
    }
};
