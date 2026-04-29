<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InsumosEconomiaSeeder extends Seeder
{
    /**
     * Insumos de librería/útiles de oficina del corralón Economía.
     * Depósito destino: id=57 ('Compras').
     *
     * Categorías nuevas que crea este seeder (con updateOrInsert):
     *   19 → Cuaderno         20 → Libro
     *   21 → Sellos           22 → Sobres
     *   23 → Lapices          24 → Pegamento
     *   25 → Cinta            26 → Gomas
     *   27 → Rollos Termicos  28 → Carpetas
     *   29 → Folios           30 → Biblioratos
     *   31 → Cajas            32 → Pizarra
     *
     * Categoría reutilizada:
     *   16 → Útiles  (ya existe)
     *
     * Stock tomado al momento del relevamiento.
     * Registros con cantidad en blanco se cargan con stock_actual = 0.
     */
    public function run(): void
    {
        // ----------------------------------------------------------------
        // 1. Categorías nuevas
        // ----------------------------------------------------------------
        $categorias = [
            ['id' => 19, 'nombre' => 'Cuaderno',        'descripcion' => null],
            ['id' => 20, 'nombre' => 'Libro',           'descripcion' => null],
            ['id' => 21, 'nombre' => 'Sellos',          'descripcion' => null],
            ['id' => 22, 'nombre' => 'Sobres',          'descripcion' => null],
            ['id' => 23, 'nombre' => 'Lapices',         'descripcion' => null],
            ['id' => 24, 'nombre' => 'Pegamento',       'descripcion' => null],
            ['id' => 25, 'nombre' => 'Cinta',           'descripcion' => null],
            ['id' => 26, 'nombre' => 'Gomas',           'descripcion' => null],
            ['id' => 27, 'nombre' => 'Rollos Termicos', 'descripcion' => null],
            ['id' => 28, 'nombre' => 'Carpetas',        'descripcion' => null],
            ['id' => 29, 'nombre' => 'Folios',          'descripcion' => null],
            ['id' => 30, 'nombre' => 'Biblioratos',     'descripcion' => null],
            ['id' => 31, 'nombre' => 'Cajas',           'descripcion' => null],
            ['id' => 32, 'nombre' => 'Pizarra',         'descripcion' => null],
        ];

        foreach ($categorias as $cat) {
            DB::table('categorias_insumos')->updateOrInsert(
                ['id' => $cat['id']],
                array_merge($cat, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // ----------------------------------------------------------------
        // 2. Insumos (depósito 57 — Compras / Economía)
        // ----------------------------------------------------------------
        $insumos = [
            ['insumo' => 'CUADERNILLOS GLORIA', 'id_categoria' => 19, 'unidad' => 'UNIDAD', 'stock_actual' => 32, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'CUADERNILLOS TRENDY', 'id_categoria' => 19, 'unidad' => 'UNIDAD', 'stock_actual' => 255, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'LIBROS DE ACTAS', 'id_categoria' => 20, 'unidad' => 'UNIDAD', 'stock_actual' => 20, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'LIBROS DE INDICE', 'id_categoria' => 20, 'unidad' => 'UNIDAD', 'stock_actual' => 15, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'CUADERNOS AZUL GLORIA', 'id_categoria' => 19, 'unidad' => 'UNIDAD', 'stock_actual' => 40, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'ALMOHADILLAS PARA SELLOS', 'id_categoria' => 21, 'unidad' => 'UNIDAD', 'stock_actual' => 6, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'SOBRES COLOR MADERA A4', 'id_categoria' => 22, 'unidad' => 'UNIDAD', 'stock_actual' => 200, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'SOBRES COLOR MADERA OFICIO', 'id_categoria' => 22, 'unidad' => 'UNIDAD', 'stock_actual' => 300, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'LAPIZ NEGRO', 'id_categoria' => 23, 'unidad' => 'UNIDAD', 'stock_actual' => 132, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'ADHESIVO SINTETICO', 'id_categoria' => 24, 'unidad' => 'UNIDAD', 'stock_actual' => 96, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'PLASTICOLA', 'id_categoria' => 24, 'unidad' => 'UNIDAD', 'stock_actual' => 65, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'PERFORADORAS', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 15, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'CORRECTOR', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 450, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'LAPICERAS AZULES', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 150, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'LAPICERAS ROJAS', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 119, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'LAPICERAS VERDES', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 8, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'LAPICERAS NEGRAS', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 300, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'CINTA ADHESIVAS CHICAS', 'id_categoria' => 25, 'unidad' => 'UNIDAD', 'stock_actual' => 25, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'CINTAS DE EMBALAR', 'id_categoria' => 25, 'unidad' => 'UNIDAD', 'stock_actual' => 32, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'CINTA DE PAPEL CHICAS', 'id_categoria' => 25, 'unidad' => 'UNIDAD', 'stock_actual' => 12, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'CINTAS DE PAPEL GRANDE', 'id_categoria' => 25, 'unidad' => 'UNIDAD', 'stock_actual' => 20, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'GOMAS DE BORRAR', 'id_categoria' => 26, 'unidad' => 'UNIDAD', 'stock_actual' => 60, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'TIJERAS', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 17, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'REGLAS', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 45, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'ROLLOS TERMICOS', 'id_categoria' => 27, 'unidad' => 'UNIDAD', 'stock_actual' => 50, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'ABROCHADORAS', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 17, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'RESALTADORES  FLUOR', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 180, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'RESALTADORES ROSAS', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 252, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'RESALTADORES NARANJAS', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 264, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'RESALTADORES VERDES', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 264, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'CARPETA COLGANTE', 'id_categoria' => 28, 'unidad' => 'UNIDAD', 'stock_actual' => 114, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'MARCADORES ROJOS PIZARRAS', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 48, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'MARCADORES NEGROS PIZARRA', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 48, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'MARCADORES NEGROS', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 96, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'ALFILERES CAJAS X 50', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 8, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'CHINCHES', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 140, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'CLIP N 6', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 79, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'CLIP N 4', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 53, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'FOLIOS POR PAQUETES DE 100 A4', 'id_categoria' => 29, 'unidad' => 'UNIDAD', 'stock_actual' => 1700, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'FOLIOS POR PAQUETES DE 10 A4', 'id_categoria' => 29, 'unidad' => 'UNIDAD', 'stock_actual' => 15, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'FOLIOS POR PAQUETES DE 100 OFICIOS', 'id_categoria' => 29, 'unidad' => 'UNIDAD', 'stock_actual' => 3000, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'BIBLIORATOS A4', 'id_categoria' => 30, 'unidad' => 'UNIDAD', 'stock_actual' => 20, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'BIBLIORATOS OFICIO', 'id_categoria' => 30, 'unidad' => 'UNIDAD', 'stock_actual' => 26, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'PAPEL CARBONICO', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 14, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'CARPETAS VERDES OFICIO 3SOLAPAS', 'id_categoria' => 28, 'unidad' => 'UNIDAD', 'stock_actual' => 66, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'CARPETAS ROSA 3 SOLAPAS', 'id_categoria' => 28, 'unidad' => 'UNIDAD', 'stock_actual' => 4, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'CARPETAS AMARILLAS 3 SOLAPAS', 'id_categoria' => 28, 'unidad' => 'UNIDAD', 'stock_actual' => 15, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'CARPETAS ROSA CLARITO 3 SOLAPAS', 'id_categoria' => 28, 'unidad' => 'UNIDAD', 'stock_actual' => 10, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'CARPETA DE CARTON', 'id_categoria' => 28, 'unidad' => 'UNIDAD', 'stock_actual' => 14, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'CARPETA 3SOLAPAS CELESTE PLASTICO OFICIO', 'id_categoria' => 28, 'unidad' => 'UNIDAD', 'stock_actual' => 26, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'CARPETAS A4 NEGRAS TAPA TRANSPARENTE', 'id_categoria' => 28, 'unidad' => 'UNIDAD', 'stock_actual' => 43, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'SACABROCHES', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 26, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'TACOS AUTOADHESIVOS', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 225, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'TACOS DE COLORES', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 0, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'TINTA PARA ALMOHADILLAS', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 55, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'SACAPUNTAS', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 0, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'CAJAS AZULES', 'id_categoria' => 31, 'unidad' => 'UNIDAD', 'stock_actual' => 67, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'CAJAS DE CARTON', 'id_categoria' => 31, 'unidad' => 'UNIDAD', 'stock_actual' => 101, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'MICROFIBRAS NEGRAS', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 0, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'FIBRAS DE COLORES', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 0, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'PIZARRA', 'id_categoria' => 32, 'unidad' => 'UNIDAD', 'stock_actual' => 1, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'BANDITAS GRANDES', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 4, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'BANDITAS CHICAS', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 30, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'GANCHOS DE ABROCHADORAS N10', 'id_categoria' => 16, 'unidad' => 'UNIDAD', 'stock_actual' => 2026, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],
            ['insumo' => 'SOBRES BLANCOS A4', 'id_categoria' => 22, 'unidad' => 'UNIDAD', 'stock_actual' => 150, 'stock_minimo' => 0, 'id_deposito' => 57, 'created_at' => now(), 'updated_at' => now()],        ];

        foreach (array_chunk($insumos, 50) as $chunk) {
            DB::table('insumos')->insert($chunk);
        }

        $total = count($insumos);
        $this->command->info("✓ {$total} insumos de Economía insertados correctamente.");
    }
}