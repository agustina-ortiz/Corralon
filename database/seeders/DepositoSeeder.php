<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepositoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('depositos')->insert([
            [
                'id' => 1,
                'sector' => 'A',
                'deposito' => 'Depósito Principal',
                'id_corralon' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'sector' => 'B',
                'deposito' => 'Depósito Materiales Pesados',
                'id_corralon' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'sector' => 'A',
                'deposito' => 'Depósito General',
                'id_corralon' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'sector' => 'C',
                'deposito' => 'Depósito Herramientas',
                'id_corralon' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}