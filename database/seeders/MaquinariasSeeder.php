<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaquinariasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('maquinarias')->insert([
            // Maquinaria Vial (Categoría 1)
            [
                'maquinaria' => 'Motoniveladora Caterpillar 140K',
                'id_categoria_maquinaria' => 1,
                'estado' => 'disponible',
                'id_deposito' => 6,
                'cantidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Pala Cargadora John Deere 544K',
                'id_categoria_maquinaria' => 1,
                'estado' => 'disponible',
                'id_deposito' => 6,
                'cantidad' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Retroexcavadora Case 580N',
                'id_categoria_maquinaria' => 1,
                'estado' => 'no disponible',
                'id_deposito' => 6,
                'cantidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Compactadora Vibromax',
                'id_categoria_maquinaria' => 1,
                'estado' => 'disponible',
                'id_deposito' => 6,
                'cantidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Equipos de Construcción (Categoría 2)
            [
                'maquinaria' => 'Hormigonera 350L',
                'id_categoria_maquinaria' => 2,
                'estado' => 'disponible',
                'id_deposito' => 3,
                'cantidad' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Mezcladora de Concreto',
                'id_categoria_maquinaria' => 2,
                'estado' => 'disponible',
                'id_deposito' => 3,
                'cantidad' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Compactador Manual Tipo Canguro',
                'id_categoria_maquinaria' => 2,
                'estado' => 'disponible',
                'id_deposito' => 3,
                'cantidad' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Cortadora de Piso Gasolina',
                'id_categoria_maquinaria' => 2,
                'estado' => 'no disponible',
                'id_deposito' => 3,
                'cantidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Herramientas Eléctricas (Categoría 3)
            [
                'maquinaria' => 'Amoladora Angular 9"',
                'id_categoria_maquinaria' => 3,
                'estado' => 'disponible',
                'id_deposito' => 2,
                'cantidad' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Taladro Percutor 13mm',
                'id_categoria_maquinaria' => 3,
                'estado' => 'disponible',
                'id_deposito' => 2,
                'cantidad' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Sierra Circular 7-1/4"',
                'id_categoria_maquinaria' => 3,
                'estado' => 'disponible',
                'id_deposito' => 2,
                'cantidad' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Rotomartillo 26mm',
                'id_categoria_maquinaria' => 3,
                'estado' => 'no disponible',
                'id_deposito' => 2,
                'cantidad' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Lijadora Orbital',
                'id_categoria_maquinaria' => 3,
                'estado' => 'disponible',
                'id_deposito' => 2,
                'cantidad' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Equipos de Jardinería (Categoría 4)
            [
                'maquinaria' => 'Cortadora de Césped Autopropulsada',
                'id_categoria_maquinaria' => 4,
                'estado' => 'disponible',
                'id_deposito' => 5,
                'cantidad' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Motosierra Stihl MS 381',
                'id_categoria_maquinaria' => 4,
                'estado' => 'disponible',
                'id_deposito' => 5,
                'cantidad' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Desmalezadora a Nafta',
                'id_categoria_maquinaria' => 4,
                'estado' => 'disponible',
                'id_deposito' => 5,
                'cantidad' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Bordeadora Eléctrica',
                'id_categoria_maquinaria' => 4,
                'estado' => 'disponible',
                'id_deposito' => 5,
                'cantidad' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Sopladora de Hojas',
                'id_categoria_maquinaria' => 4,
                'estado' => 'no disponible',
                'id_deposito' => 5,
                'cantidad' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Equipos de Limpieza (Categoría 5)
            [
                'maquinaria' => 'Hidrolavadora Industrial 3000PSI',
                'id_categoria_maquinaria' => 5,
                'estado' => 'disponible',
                'id_deposito' => 9,
                'cantidad' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Aspiradora Industrial 80L',
                'id_categoria_maquinaria' => 5,
                'estado' => 'disponible',
                'id_deposito' => 9,
                'cantidad' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Barredora Autopropulsada',
                'id_categoria_maquinaria' => 5,
                'estado' => 'disponible',
                'id_deposito' => 9,
                'cantidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Pulidora de Pisos Industrial',
                'id_categoria_maquinaria' => 5,
                'estado' => 'disponible',
                'id_deposito' => 9,
                'cantidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Maquinaria Pesada (Categoría 6)
            [
                'maquinaria' => 'Topadora Caterpillar D6N',
                'id_categoria_maquinaria' => 6,
                'estado' => 'disponible',
                'id_deposito' => 6,
                'cantidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Excavadora Hidráulica CAT 320',
                'id_categoria_maquinaria' => 6,
                'estado' => 'disponible',
                'id_deposito' => 6,
                'cantidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Grúa Hidráulica 5 Ton',
                'id_categoria_maquinaria' => 6,
                'estado' => 'no disponible',
                'id_deposito' => 6,
                'cantidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Minicargadora Bobcat',
                'id_categoria_maquinaria' => 6,
                'estado' => 'disponible',
                'id_deposito' => 6,
                'cantidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Equipos de Soldadura (Categoría 7)
            [
                'maquinaria' => 'Soldadora Eléctrica 250A',
                'id_categoria_maquinaria' => 7,
                'estado' => 'disponible',
                'id_deposito' => 7,
                'cantidad' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Soldadora Autógena Completa',
                'id_categoria_maquinaria' => 7,
                'estado' => 'disponible',
                'id_deposito' => 7,
                'cantidad' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Soldadora MIG/MAG',
                'id_categoria_maquinaria' => 7,
                'estado' => 'disponible',
                'id_deposito' => 7,
                'cantidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Plasma Cortador 40A',
                'id_categoria_maquinaria' => 7,
                'estado' => 'no disponible',
                'id_deposito' => 7,
                'cantidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Generadores y Compresores (Categoría 8)
            [
                'maquinaria' => 'Grupo Electrógeno 10KVA',
                'id_categoria_maquinaria' => 8,
                'estado' => 'disponible',
                'id_deposito' => 6,
                'cantidad' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Grupo Electrógeno 5KVA',
                'id_categoria_maquinaria' => 8,
                'estado' => 'disponible',
                'id_deposito' => 3,
                'cantidad' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Compresor de Aire 100L 3HP',
                'id_categoria_maquinaria' => 8,
                'estado' => 'disponible',
                'id_deposito' => 7,
                'cantidad' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Compresor Portátil 50L',
                'id_categoria_maquinaria' => 8,
                'estado' => 'disponible',
                'id_deposito' => 7,
                'cantidad' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'maquinaria' => 'Motobomba 4" Gasolina',
                'id_categoria_maquinaria' => 8,
                'estado' => 'disponible',
                'id_deposito' => 6,
                'cantidad' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}