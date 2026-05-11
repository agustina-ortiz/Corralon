<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tipo_movimientos')->updateOrInsert(
            ['tipo_movimiento' => 'Baja Reposición'],
            ['tipo' => 'I']
        );
    }

    public function down(): void
    {
        DB::table('tipo_movimientos')->where('tipo_movimiento', 'Baja Reposición')->delete();
    }
};
