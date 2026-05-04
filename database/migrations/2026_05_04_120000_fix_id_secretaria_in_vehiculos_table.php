<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $columnType = DB::selectOne("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vehiculos' AND COLUMN_NAME = 'id_secretaria'");

        if ($columnType && $columnType->DATA_TYPE === 'varchar') {
            // Local: id_secretaria es varchar con nombres de secretarías
            // Paso 1: mapear nombres conocidos a IDs de secretarias
            $secretariasMap = [
                'Secretaria Desarrollo de la Comunidad' => 'Desarrollo de la Comunidad',
                'Secretaria de Gobierno' => 'Gobierno',
                'Sub Secretaria Servicio Publicos' => 'Servicios Públicos',
                'Secretaria de Seguridad' => 'Seguridad',
                'Secretaria Educación' => 'Educación',
            ];

            foreach ($secretariasMap as $valorActual => $nombreSecretaria) {
                $secretaria = DB::table('secretarias')->where('secretaria', $nombreSecretaria)->first();
                if (!$secretaria) {
                    $secretariaId = DB::table('secretarias')->insertGetId([
                        'secretaria' => $nombreSecretaria,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $secretariaId = $secretaria->id;
                }

                DB::statement("UPDATE vehiculos SET id_secretaria = ? WHERE id_secretaria = ?", [$secretariaId, $valorActual]);
            }

            // Paso 2: hacer nullable ANTES de limpiar valores inválidos
            DB::statement("ALTER TABLE vehiculos MODIFY id_secretaria VARCHAR(191) NULL");

            // Paso 3: limpiar valores que no sean numéricos (como 'sec')
            DB::statement("UPDATE vehiculos SET id_secretaria = NULL WHERE id_secretaria NOT REGEXP '^[0-9]+$'");

            // Paso 4: cambiar tipo a bigint unsigned nullable
            DB::statement("ALTER TABLE vehiculos MODIFY id_secretaria BIGINT UNSIGNED NULL");

            // Paso 5: agregar FK si no existe
            $fkExists = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vehiculos' AND COLUMN_NAME = 'id_secretaria' AND REFERENCED_TABLE_NAME = 'secretarias'");

            if (empty($fkExists)) {
                Schema::table('vehiculos', function (Blueprint $table) {
                    $table->foreign('id_secretaria')->references('id')->on('secretarias')->onDelete('set null');
                });
            }
        } else {
            // Servidor: ya es bigint, solo asegurar nullable
            DB::statement("ALTER TABLE vehiculos MODIFY id_secretaria BIGINT UNSIGNED NULL");
        }
    }

    public function down(): void
    {
        // No se revierte — el tipo correcto es bigint
    }
};
