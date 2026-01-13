<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriasInsumosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categorias_insumos')->insert([
            [
                'id' => 1,
                'nombre' => 'Materiales de Construcción',
                'descripcion' => 'Cemento, arena, ladrillos, cal y otros materiales para obras',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nombre' => 'Herramientas Manuales',
                'descripcion' => 'Palas, picos, martillos, destornilladores y herramientas de mano',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nombre' => 'Pinturas y Revestimientos',
                'descripcion' => 'Pinturas, barnices, diluyentes y productos para acabados',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'nombre' => 'Electricidad',
                'descripcion' => 'Cables, enchufes, llaves térmicas, luminarias y materiales eléctricos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'nombre' => 'Plomería',
                'descripcion' => 'Caños, llaves, codos, uniones y accesorios de plomería',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'nombre' => 'Ferretería General',
                'descripcion' => 'Tornillos, clavos, bulones, arandelas y elementos de fijación',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'nombre' => 'Seguridad e Higiene',
                'descripcion' => 'EPP, cascos, guantes, antiparras, barbijos y elementos de protección',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'nombre' => 'Limpieza y Mantenimiento',
                'descripcion' => 'Productos de limpieza, escobas, trapos y útiles de mantenimiento',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'nombre' => 'Jardinería',
                'descripcion' => 'Semillas, fertilizantes, tierra, macetas y elementos de jardinería',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'nombre' => 'Señalización Vial',
                'descripcion' => 'Conos, vallas, cintas, carteles y elementos de señalización',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}