<?php

use App\Models\DocumentoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Mueve los documentos de vehículos del disco público (accesible por URL sin login)
 * al disco privado, renombrándolos a {patente}_{descripcion}_{fecha}.{ext}.
 * Idempotente: los que ya están en el disco privado se saltean.
 */
return new class extends Migration
{
    public function up(): void
    {
        $publico = Storage::disk('public');
        $privado = Storage::disk(DocumentoVehiculo::DISCO);

        foreach (DB::table('documentos_vehiculos')->orderBy('id')->get() as $doc) {
            if ($privado->exists($doc->archivo) || !$publico->exists($doc->archivo)) {
                continue;
            }

            $vehiculo = Vehiculo::find($doc->id_vehiculo);
            if (!$vehiculo) {
                continue;
            }

            $extension = pathinfo($doc->archivo, PATHINFO_EXTENSION) ?: 'pdf';
            $fecha = $doc->created_at ? new \DateTime($doc->created_at) : null;
            $nuevaRuta = DocumentoVehiculo::rutaParaArchivo($vehiculo, (string) $doc->descripcion, $extension, $fecha);

            if ($privado->writeStream($nuevaRuta, $publico->readStream($doc->archivo))) {
                DB::table('documentos_vehiculos')->where('id', $doc->id)->update(['archivo' => $nuevaRuta]);
                $publico->delete($doc->archivo);
            }
        }
    }

    public function down(): void
    {
        $publico = Storage::disk('public');
        $privado = Storage::disk(DocumentoVehiculo::DISCO);

        foreach (DB::table('documentos_vehiculos')->orderBy('id')->get() as $doc) {
            if ($privado->exists($doc->archivo) && !$publico->exists($doc->archivo)) {
                $publico->writeStream($doc->archivo, $privado->readStream($doc->archivo));
                $privado->delete($doc->archivo);
            }
        }
    }
};
