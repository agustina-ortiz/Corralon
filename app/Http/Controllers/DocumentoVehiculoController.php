<?php

namespace App\Http\Controllers;

use App\Models\DocumentoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\Storage;

class DocumentoVehiculoController extends Controller
{
    /** Abre el documento inline en el navegador. */
    public function ver(DocumentoVehiculo $documento)
    {
        // filename en inline: al guardar desde el visor del navegador se usa el nombre legible
        return response()->file($this->rutaAccesible($documento), [
            'Content-Disposition' => 'inline; filename="' . basename($documento->archivo) . '"',
        ]);
    }

    /** Fuerza la descarga con el nombre legible del archivo. */
    public function descargar(DocumentoVehiculo $documento)
    {
        return response()->download($this->rutaAccesible($documento), basename($documento->archivo));
    }

    /**
     * Mismo criterio de acceso que el listado de /vehiculos (global, depósito o secretaría vía pivote).
     */
    private function rutaAccesible(DocumentoVehiculo $documento): string
    {
        $accesible = Vehiculo::whereKey($documento->id_vehiculo)->porCorralonesPermitidos()->exists();
        abort_unless($accesible, 403);

        $disco = Storage::disk(DocumentoVehiculo::DISCO);
        abort_unless($disco->exists($documento->archivo), 404);

        return $disco->path($documento->archivo);
    }
}
