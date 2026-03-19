<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CorralonesSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('corralones')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('corralones')->insert([
            [
                'id'            => 1,
                'descripcion'   => 'Corralón Municipal',
                'ubicacion'     => 'Calle 35 e/ 104 y 110',
                'secretaria_id' => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'id'            => 2,
                'descripcion'   => 'Corralón Municipal II - Materiales Pesados',
                'ubicacion'     => 'Acceso Manuel San Martín y 132',
                'secretaria_id' => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'id'            => 3,
                'descripcion'   => 'Seguridad',
                'ubicacion'     => 'Calle 2 - Martín Rodríguez',
                'secretaria_id' => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);
    }
}