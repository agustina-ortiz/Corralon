<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriasInsumosSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categorias_insumos')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('categorias_insumos')->insert([
            ['id' => 1,  'nombre' => 'Cubiertas',                  'descripcion' => 'Cubiertas y neumáticos para vehículos y maquinarias',          'created_at' => now(), 'updated_at' => now()],
            ['id' => 2,  'nombre' => 'Pinturas',                   'descripcion' => 'Pinturas, látex, sintéticos, rodillos y accesorios de pintura', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3,  'nombre' => 'Aceites',                    'descripcion' => 'Aceites, lubricantes y fluidos para vehículos y maquinarias',   'created_at' => now(), 'updated_at' => now()],
            ['id' => 4,  'nombre' => 'Baterías',                   'descripcion' => 'Baterías para vehículos y equipos',                            'created_at' => now(), 'updated_at' => now()],
            ['id' => 5,  'nombre' => 'Abrazaderas y Accesorios',   'descripcion' => 'Abrazaderas, juntamas, caños PVC, llaves y accesorios de red',  'created_at' => now(), 'updated_at' => now()],
            ['id' => 6,  'nombre' => 'Caños Blancos y Accesorios', 'descripcion' => 'Caños blancos, cuplas, curvas y accesorios de desagüe',         'created_at' => now(), 'updated_at' => now()],
            ['id' => 7,  'nombre' => 'Herramientas y Varios',      'descripcion' => 'Herramientas manuales, EPP, adhesivos y materiales varios',     'created_at' => now(), 'updated_at' => now()],
            ['id' => 8,  'nombre' => 'Cuchillas y Pernos',         'descripcion' => 'Cuchillas de corte y pernos para maquinaria vial',              'created_at' => now(), 'updated_at' => now()],
            ['id' => 9,  'nombre' => 'Electricidad',               'descripcion' => 'Cables, termicas, lamparas, gabinetes y materiales eléctricos', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'nombre' => 'Materiales de Construcción', 'descripcion' => 'Arena, cemento, cal, varillas y caños de hormigón',             'created_at' => now(), 'updated_at' => now()],
            ['id' => 11, 'nombre' => 'Hierros y Metales',          'descripcion' => 'Ángulos, chapas, caños de hierro, planchuelas y perfiles',      'created_at' => now(), 'updated_at' => now()],
            ['id' => 12, 'nombre' => 'Repuestos Motoguadaña',      'descripcion' => 'Repuestos y accesorios para motoguadañas Echo',                 'created_at' => now(), 'updated_at' => now()],
            ['id' => 13, 'nombre' => 'Repuestos Motosierra',       'descripcion' => 'Cadenas, espadas y repuestos para motosierras',                 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}