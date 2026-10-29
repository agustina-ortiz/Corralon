<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CATEGORIAS = [
        ['A.1.1', 'Ciclomotores hasta 50 cc / 4kw'],
        ['A.1.2', 'Motocicletas hasta 150 cc / 11kw'],
        ['A.1.3', 'Motocicletas hasta 300 cc / 20Kw'],
        ['A.1.4', 'Motocicletas mas 300 cc / 20Kw'],
        ['A.2.1', 'Triciclos y Cuiatri hasta 300 cc / 20Kw'],
        ['A.2.2', 'Triciclos y Cuiatri mas 300 cc / 20Kw'],
        ['A.3', 'Triciclos y Cuiatri Cabinados'],
        ['B.1', 'Automoviles'],
        ['B.2', 'Automoviles con acoplado'],
        ['C.1', 'Camiones sin acoplados hasta 12000 kg'],
        ['C.2', 'Camiones sin acoplados hasta 24000 kg'],
        ['C.3', 'Camiones sin acoplados mas 24000 kg'],
        ['D.1', 'Aut. transporte pasajeros hasta 8 plazas'],
        ['D.2', 'Aut. transporte pasajeros hasta 20 plazas'],
        ['D.3', 'Aut. transporte pasajeros mas 20 plazas'],
        ['D.4', 'Servicio de urgencia, etc.'],
        ['E.1', 'Camiones con acoplados'],
        ['E.2', 'Maquinaria especial no agricola'],
        ['F', 'Vehiculos adaptados p/discapacitados'],
        ['G.1', 'Tractores'],
        ['G.2', 'Maquinaria Agricola'],
        ['G.3', 'Tren Agricola'],
    ];

    public function up(): void
    {
        Schema::create('categorias_licencia', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique();
            $table->string('descripcion');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('choferes_categorias_licencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chofer_id')->constrained('choferes')->cascadeOnDelete();
            $table->foreignId('categoria_licencia_id')->constrained('categorias_licencia')->cascadeOnDelete();
            $table->unique(['chofer_id', 'categoria_licencia_id'], 'chofer_categoria_unique');
        });

        $now = now();
        $ids = [];
        foreach (self::CATEGORIAS as $i => [$codigo, $descripcion]) {
            $ids[$codigo] = DB::table('categorias_licencia')->insertGetId([
                'codigo' => $codigo,
                'descripcion' => $descripcion,
                'orden' => $i + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Migrar el texto libre de choferes.tipo_licencia (ej: "A.1.4B.2D.1", "A1.4 B 2").
        // Se quitan puntos/espacios y se reconocen los códigos de mayor a menor largo.
        $compactos = [];
        foreach (array_keys($ids) as $codigo) {
            $compactos[str_replace('.', '', $codigo)] = $codigo;
        }
        uksort($compactos, fn ($a, $b) => strlen($b) <=> strlen($a));

        $choferes = DB::table('choferes')->whereNotNull('tipo_licencia')->get(['id', 'tipo_licencia']);
        foreach ($choferes as $chofer) {
            $texto = preg_replace('/[^A-Z0-9]/', '', strtoupper($chofer->tipo_licencia));
            $encontrados = [];
            $pos = 0;
            while ($pos < strlen($texto)) {
                $match = null;
                foreach ($compactos as $compacto => $codigo) {
                    if (substr($texto, $pos, strlen($compacto)) === $compacto) {
                        $match = $compacto;
                        $encontrados[$codigo] = true;
                        break;
                    }
                }
                $pos += $match ? strlen($match) : 1;
            }

            foreach (array_keys($encontrados) as $codigo) {
                DB::table('choferes_categorias_licencia')->insert([
                    'chofer_id' => $chofer->id,
                    'categoria_licencia_id' => $ids[$codigo],
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('choferes_categorias_licencia');
        Schema::dropIfExists('categorias_licencia');
    }
};
