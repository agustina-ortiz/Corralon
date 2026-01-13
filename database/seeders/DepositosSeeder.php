<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepositosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('depositos')->insert([
            // Depósitos del Corralón Municipal (id_corralon: 1)
            [
                'id' => 1,
                'sector' => 'A',
                'deposito' => 'Depósito General',
                'id_corralon' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'sector' => 'B',
                'deposito' => 'Depósito de Herramientas',
                'id_corralon' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'sector' => 'C',
                'deposito' => 'Depósito de Materiales de Construcción',
                'id_corralon' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'sector' => 'D',
                'deposito' => 'Depósito de Pinturas y Químicos',
                'id_corralon' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Depósitos del Corralón Municipal II (id_corralon: 2)
            [
                'id' => 5,
                'sector' => 'A',
                'deposito' => 'Depósito Central',
                'id_corralon' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'sector' => 'B',
                'deposito' => 'Depósito de Maquinaria Pesada',
                'id_corralon' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'sector' => 'C',
                'deposito' => 'Depósito de Repuestos',
                'id_corralon' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Depósitos de Seguridad (id_corralon: 3)
            [
                'id' => 8,
                'sector' => 'A',
                'deposito' => 'Depósito de Seguridad',
                'id_corralon' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'sector' => 'B',
                'deposito' => 'Depósito de Equipamiento',
                'id_corralon' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}