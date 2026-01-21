<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaquinariasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $maquinarias = [
            ['id' => 1, 'maquinaria' => 'AMOLADORA 115 MM', 'id_categoria_maquinaria' => 1],
            ['id' => 2, 'maquinaria' => 'AMOLADORA 180 MM', 'id_categoria_maquinaria' => 1],
            ['id' => 3, 'maquinaria' => 'AMOLADORA 230 MM', 'id_categoria_maquinaria' => 1],
            ['id' => 4, 'maquinaria' => 'ATORNILLADOR DCD 777192', 'id_categoria_maquinaria' => 2],
            ['id' => 5, 'maquinaria' => 'ATORNILLADOR DCD 79602', 'id_categoria_maquinaria' => 2],
            ['id' => 6, 'maquinaria' => 'SIERRA CIRCULAR', 'id_categoria_maquinaria' => 3],
            ['id' => 7, 'maquinaria' => 'COMPRENSORES', 'id_categoria_maquinaria' => 4],
            ['id' => 8, 'maquinaria' => 'COMPRESOR 300 LT', 'id_categoria_maquinaria' => 4],
            ['id' => 9, 'maquinaria' => 'COMPRESOR 50 LT', 'id_categoria_maquinaria' => 4],
            ['id' => 10, 'maquinaria' => 'CORTA CERCO ECHO', 'id_categoria_maquinaria' => 5],
            ['id' => 11, 'maquinaria' => 'DESMALEZADORA ECHO 4605', 'id_categoria_maquinaria' => 6],
            ['id' => 12, 'maquinaria' => 'GRUPO ELECTROGENO LMB', 'id_categoria_maquinaria' => 7],
            ['id' => 13, 'maquinaria' => 'GRUPO ELECTROGENO LUSQTOFF', 'id_categoria_maquinaria' => 7],
            ['id' => 14, 'maquinaria' => 'HIDROLAVADORA A EXPLOSION', 'id_categoria_maquinaria' => 8],
            ['id' => 15, 'maquinaria' => 'HORMIGONERA 150 LT', 'id_categoria_maquinaria' => 9],
            ['id' => 16, 'maquinaria' => 'MOTOBOMBA', 'id_categoria_maquinaria' => 10],
            ['id' => 17, 'maquinaria' => 'MOTOCULTIVADOR', 'id_categoria_maquinaria' => 11],
            ['id' => 18, 'maquinaria' => 'MOTOPISON', 'id_categoria_maquinaria' => 12],
            ['id' => 19, 'maquinaria' => 'MOTOSIERA NIWA', 'id_categoria_maquinaria' => 13],
            ['id' => 20, 'maquinaria' => 'MOTOSIERA STHILL', 'id_categoria_maquinaria' => 13],
            ['id' => 21, 'maquinaria' => 'MOTOSIERRA ECHO 303', 'id_categoria_maquinaria' => 13],
            ['id' => 22, 'maquinaria' => 'MOTOSIERRA ECHO 510', 'id_categoria_maquinaria' => 13],
            ['id' => 23, 'maquinaria' => 'MOTOSIERRA ECHO 600', 'id_categoria_maquinaria' => 13],
            ['id' => 24, 'maquinaria' => 'MOTOSIERRA ECHO 620', 'id_categoria_maquinaria' => 13],
            ['id' => 25, 'maquinaria' => 'PISTOLA DE PINTAR ADIABATIC SC 3000', 'id_categoria_maquinaria' => 14],
            ['id' => 26, 'maquinaria' => 'ROTOMARTILLO BOSCH', 'id_categoria_maquinaria' => 15],
            ['id' => 27, 'maquinaria' => 'ROTOMARTILLO DEWALT', 'id_categoria_maquinaria' => 15],
            ['id' => 28, 'maquinaria' => 'SOLDADORA INVERTER LABOR 120', 'id_categoria_maquinaria' => 16],
            ['id' => 29, 'maquinaria' => 'TALADRO / ROTOMARTILLO TOTAL', 'id_categoria_maquinaria' => 17],
            ['id' => 30, 'maquinaria' => 'TALADRO 13 MM', 'id_categoria_maquinaria' => 17],
            ['id' => 31, 'maquinaria' => 'TERMOFUSORA', 'id_categoria_maquinaria' => 18],
            ['id' => 32, 'maquinaria' => 'ZANJEADORA HONDA', 'id_categoria_maquinaria' => 19],
        ];

        foreach ($maquinarias as $maquinaria) {
            DB::table('maquinarias')->insert([
                'id' => $maquinaria['id'],
                'maquinaria' => $maquinaria['maquinaria'],
                'id_categoria_maquinaria' => $maquinaria['id_categoria_maquinaria'],
                'estado' => 'disponible',
                'id_deposito' => 1,
                'cantidad' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}