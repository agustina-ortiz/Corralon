<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CorraloneSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('corralones')->insert([
            [
                'id' => 1,
                'descripcion' => 'Corralón Central',
                'ubicacion' => 'Av. San Martín 1250',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'descripcion' => 'Corralón Norte',
                'ubicacion' => 'Ruta 9 Km 4.5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}