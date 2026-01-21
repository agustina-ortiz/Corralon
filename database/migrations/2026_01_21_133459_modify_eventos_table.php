<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            // Creamos la columna secretaria_id después de ubicacion
            $table->unsignedBigInteger('secretaria_id')->after('ubicacion');
            
            // Agregamos la foreign key
            $table->foreign('secretaria_id')
                  ->references('id')
                  ->on('secretarias')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            // Agregamos el campo evento_anual después de secretaria_id
            $table->boolean('evento_anual')->default(false)->after('secretaria_id');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropForeign(['secretaria_id']);
            $table->dropColumn(['secretaria_id', 'evento_anual']);
        });
    }
};