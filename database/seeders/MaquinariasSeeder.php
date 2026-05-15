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
            ['id' => 1, 'maquinaria' => 'AMOLADORA 115 MM', 'id_categoria_maquinaria' => 1, 'cantidad' => 2],
            ['id' => 2, 'maquinaria' => 'AMOLADORA 180 MM', 'id_categoria_maquinaria' => 1, 'cantidad' => 1],
            ['id' => 3, 'maquinaria' => 'AMOLADORA 230 MM', 'id_categoria_maquinaria' => 1, 'cantidad' => 3],
            ['id' => 4, 'maquinaria' => 'ATORNILLADOR DCD 777192', 'id_categoria_maquinaria' => 2, 'cantidad' => 1],
            ['id' => 5, 'maquinaria' => 'ATORNILLADOR DCD 79602', 'id_categoria_maquinaria' => 2, 'cantidad' => 2],
            ['id' => 6, 'maquinaria' => 'ATORNILLADOR DCD 996P2T', 'id_categoria_maquinaria' => 2, 'cantidad' => 1],
            ['id' => 7, 'maquinaria' => 'SIERRA CIRCULAR', 'id_categoria_maquinaria' => 3, 'cantidad' => 2],
            ['id' => 8, 'maquinaria' => 'COMPRENSORES', 'id_categoria_maquinaria' => 4],
            ['id' => 9, 'maquinaria' => 'COMPRESOR 300 LT', 'id_categoria_maquinaria' => 4, 'cantidad' => 1],
            ['id' => 10, 'maquinaria' => 'COMPRESOR 50 LT', 'id_categoria_maquinaria' => 4],
            ['id' => 11, 'maquinaria' => 'CORTA CERCO ECHO', 'id_categoria_maquinaria' => 5],
            ['id' => 12, 'maquinaria' => 'MAQUINA CORTA CESPED ARRASTRE HONDA', 'id_categoria_maquinaria' => 21, 'id_deposito' => 16, 'cantidad' => 2],
            ['id' => 13, 'maquinaria' => 'DESMALEZADORA ECHO 4605', 'id_categoria_maquinaria' => 6, 'id_deposito' => 16, 'cantidad' => 28],
            ['id' => 14, 'maquinaria' => 'GRUPO ELECTROGENO LMB', 'id_categoria_maquinaria' => 7],
            ['id' => 15, 'maquinaria' => 'GRUPO ELECTROGENO LUSQTOFF', 'id_categoria_maquinaria' => 7, 'cantidad' => 1],
            ['id' => 16, 'maquinaria' => 'HIDROLAVADORA A EXPLOSION', 'id_categoria_maquinaria' => 8, 'cantidad' => 1],
            ['id' => 17, 'maquinaria' => 'HORMIGONERA 150 LT', 'id_categoria_maquinaria' => 9, 'cantidad' => 4],
            ['id' => 18, 'maquinaria' => 'MOTOBOMBA', 'id_categoria_maquinaria' => 10, 'cantidad' => 1],
            ['id' => 19, 'maquinaria' => 'MOTOCULTIVADOR', 'id_categoria_maquinaria' => 11],
            ['id' => 20, 'maquinaria' => 'MOTOPISON KLD', 'id_categoria_maquinaria' => 12, 'cantidad' => 1],
            ['id' => 21, 'maquinaria' => 'MOTOSIERA NIWA CNW-45', 'id_categoria_maquinaria' => 13, 'id_deposito' => 16, 'cantidad' => 3],
            ['id' => 22, 'maquinaria' => 'MOTOSIERA STHILL MS194', 'id_categoria_maquinaria' => 13],
            ['id' => 23, 'maquinaria' => 'MOTOSIERA STHILL MS250', 'id_categoria_maquinaria' => 13],
            ['id' => 24, 'maquinaria' => 'MOTOSIERA STHILL MS260', 'id_categoria_maquinaria' => 13],
            ['id' => 25, 'maquinaria' => 'MOTOSIERA STHILL MS661', 'id_categoria_maquinaria' => 13, 'id_deposito' => 16, 'cantidad' => 3],
            ['id' => 26, 'maquinaria' => 'MOTOSIERRA ECHO 303', 'id_categoria_maquinaria' => 13],
            ['id' => 27, 'maquinaria' => 'MOTOSIERRA ECHO 2511', 'id_categoria_maquinaria' => 13, 'id_deposito' => 16, 'cantidad' => 2],
            ['id' => 28, 'maquinaria' => 'MOTOSIERRA ECHO CS-355T', 'id_categoria_maquinaria' => 13, 'id_deposito' => 16, 'cantidad' => 3],
            ['id' => 29, 'maquinaria' => 'MOTOSIERRA ECHO CS-420ES', 'id_categoria_maquinaria' => 13, 'id_deposito' => 16, 'cantidad' => 3],
            ['id' => 30, 'maquinaria' => 'MOTOSIERRA ECHO 510', 'id_categoria_maquinaria' => 13],
            ['id' => 31, 'maquinaria' => 'MOTOSIERRA ECHO 600', 'id_categoria_maquinaria' => 13],
            ['id' => 32, 'maquinaria' => 'MOTOSIERRA ECHO CS 620 P', 'id_categoria_maquinaria' => 13],
            ['id' => 33, 'maquinaria' => 'MOTOSIERRA ECHO PPT 266', 'id_categoria_maquinaria' => 13],
            ['id' => 34, 'maquinaria' => 'MOTOSIERRA ECHO PPT 2620', 'id_categoria_maquinaria' => 13, 'id_deposito' => 16, 'cantidad' => 2],
            ['id' => 35, 'maquinaria' => 'PISTOLA DE PINTAR ADIABATIC SC 3000', 'id_categoria_maquinaria' => 14],
            ['id' => 36, 'maquinaria' => 'ROTOMARTILLO BOSCH GBH-3-28-DRE', 'id_categoria_maquinaria' => 15, 'cantidad' => 1],
            ['id' => 37, 'maquinaria' => 'ROTOMARTILLO DEWALT', 'id_categoria_maquinaria' => 15],
            ['id' => 38, 'maquinaria' => 'SOLDADORA INVERTER LABOR 120', 'id_categoria_maquinaria' => 16],
            ['id' => 39, 'maquinaria' => 'SOPLADORA ECHO PB2520', 'id_categoria_maquinaria' => 20, 'cantidad' => 7],
            ['id' => 40, 'maquinaria' => 'TALADRO / ROTOMARTILLO TOTAL', 'id_categoria_maquinaria' => 17],
            ['id' => 41, 'maquinaria' => 'TALADRO 13 MM', 'id_categoria_maquinaria' => 17, 'cantidad' => 3],
            ['id' => 42, 'maquinaria' => 'TERMOFUSORA', 'id_categoria_maquinaria' => 18, 'cantidad' => 1],
            ['id' => 43, 'maquinaria' => 'ZANJEADORA HONDA', 'id_categoria_maquinaria' => 19],
        ];

        foreach ($maquinarias as $maquinaria) {
            DB::table('maquinarias')->insert([
                'id' => $maquinaria['id'],
                'maquinaria' => $maquinaria['maquinaria'],
                'id_categoria_maquinaria' => $maquinaria['id_categoria_maquinaria'],
                'estado' => 'disponible',
                'id_deposito' => $maquinaria['id_deposito'] ?? 1,
                'cantidad' => $maquinaria['cantidad'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ----------------------------------------------------------------
        // Movimientos de Inventario Inicial para cada maquinaria con cantidad > 0
        // ----------------------------------------------------------------
        $tipoInventarioInicial = DB::table('tipo_movimientos')
            ->where('tipo_movimiento', 'Inventario Inicial Maquinaria')
            ->value('id');

        if (!$tipoInventarioInicial) {
            $this->command->warn('⚠ Tipo de movimiento "Inventario Inicial Maquinaria" no encontrado. No se crearon movimientos.');
            return;
        }

        $movimientos = 0;
        $fecha = '2026-05-15';

        foreach ($maquinarias as $maq) {
            $cantidad = $maq['cantidad'] ?? 0;
            if ($cantidad <= 0) {
                continue;
            }

            $depositoId = $maq['id_deposito'] ?? 1;

            DB::table('movimiento_maquinarias')->insert([
                'id_maquinaria'      => $maq['id'],
                'cantidad'           => $cantidad,
                'id_tipo_movimiento' => $tipoInventarioInicial,
                'fecha'              => $fecha,
                'id_usuario'         => 1,
                'id_deposito_entrada'=> $depositoId,
                'id_referencia'      => 0,
                'tipo_referencia'    => 'deposito',
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            $movimientos++;
        }

        $this->command->info("✓ {$movimientos} movimientos de inventario inicial (Maquinarias) creados.");
    }
}