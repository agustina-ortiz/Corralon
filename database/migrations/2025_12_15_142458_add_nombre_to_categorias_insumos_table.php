<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categorias_insumos', function (Blueprint $table) {
            $table->string('nombre', 100)->after('id');
        });

        DB::table('categorias_insumos')->update([
            'nombre' => DB::raw('descripcion')
        ]);

        Schema::table('categorias_insumos', function (Blueprint $table) {
            // 3️⃣ Eliminar descripcion
            $table->dropColumn('descripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('categorias_insumos', function (Blueprint $table) {
            // Volver a crear descripcion
            $table->text('descripcion')->nullable();
        });

        DB::table('categorias_insumos')->update([
            'descripcion' => DB::raw('nombre')
        ]);
        
        Schema::table('categorias_insumos', function (Blueprint $table) {
            $table->dropColumn('nombre');
        });
    }
};
