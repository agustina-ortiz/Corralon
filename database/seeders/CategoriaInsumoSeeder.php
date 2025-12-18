<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaInsumoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categorias_insumos')->insert([
            [
                'id' => 1,
                'nombre' => 'Materiales de Construcción',
                'descripcion' => 'Cemento, cal, arena, piedra y materiales básicos para construcción',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nombre' => 'Herramientas',
                'descripcion' => 'Herramientas manuales y eléctricas para uso en obra',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nombre' => 'Materiales Eléctricos',
                'descripcion' => 'Cables, caños, llaves, tomas y accesorios eléctricos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'nombre' => 'Plomería y Sanitarios',
                'descripcion' => 'Caños, codos, conexiones, grifería y accesorios sanitarios',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'nombre' => 'Pintura y Revestimientos',
                'descripcion' => 'Pinturas, barnices, solventes y materiales de terminación',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}