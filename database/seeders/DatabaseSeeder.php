<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * ORDEN DE EJECUCIÓN:
     * Es importante ejecutar los seeders en este orden debido a las 
     * relaciones de foreign keys entre las tablas.
     */
    public function run(): void
    {
        $this->call([
            // 1. Primero los corralones (no tienen dependencias)
            
            // 2. Luego los depósitos (dependen de corralones)
            DepositosSeeder::class,
            
            // 3. Categorías de insumos (no tienen dependencias)
            CategoriasInsumosSeeder::class,
            
            // 4. Categorías de maquinarias (no tienen dependencias)
            CategoriasMaquinariasSeeder::class,
            
            // 5. Insumos (dependen de categorías_insumos y depósitos)
            InsumosSeeder::class,
            
            // 6. Maquinarias (dependen de categorias_maquinarias y depósitos)
            MaquinariasSeeder::class,
            
            // 7. Vehículos (dependen de depósitos y secretarias)
            // NOTA: Asegúrate de tener la tabla 'secretarias' poblada antes de ejecutar este seeder
            // o modifica el seeder para usar IDs existentes
            VehiculosSeeder::class,
        ]);
    }
}