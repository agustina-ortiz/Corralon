<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriasMaquinariasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categoria_maquinarias')->insert([
            [
                'id' => 1,
                'nombre' => 'Maquinaria Vial',
                'descripcion' => 'Motoniveladoras, palas cargadoras, retroexcavadoras y equipos viales',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nombre' => 'Equipos de Construcción',
                'descripcion' => 'Hormigoneras, mezcladoras, compactadores y equipos de obra',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nombre' => 'Herramientas Eléctricas',
                'descripcion' => 'Amoladoras, taladros, sierras eléctricas y herramientas de poder',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'nombre' => 'Equipos de Jardinería',
                'descripcion' => 'Cortadoras de césped, motosierras, desmalezadoras y equipos de parques',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'nombre' => 'Equipos de Limpieza',
                'descripcion' => 'Hidrolavadoras, aspiradoras industriales, barredoras',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'nombre' => 'Maquinaria Pesada',
                'descripcion' => 'Topadoras, excavadoras, grúas y equipamiento pesado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'nombre' => 'Equipos de Soldadura',
                'descripcion' => 'Soldadoras eléctricas, autógenas y equipos de soldadura',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'nombre' => 'Generadores y Compresores',
                'descripcion' => 'Grupos electrógenos, compresores de aire y equipos auxiliares',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}