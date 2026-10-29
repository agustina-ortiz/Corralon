<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentoVehiculo extends Model
{
    protected $table = 'documentos_vehiculos';

    /** Disco privado: los documentos solo se sirven por las rutas autenticadas documentos-vehiculo.* */
    const DISCO = 'local';

    const DIRECTORIO = 'vehiculos/documentos';

    protected $fillable = [
        'id_vehiculo',
        'descripcion',
        'archivo',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'id_vehiculo');
    }

    /**
     * Ruta relativa (en el disco DISCO) con un nombre legible y único:
     * vehiculos/documentos/{id}/{patente}_{descripcion}_{fecha}.{ext}
     */
    public static function rutaParaArchivo(Vehiculo $vehiculo, string $descripcion, string $extension, ?\DateTimeInterface $fecha = null): string
    {
        $patente = trim((string) $vehiculo->patente);

        if ($patente !== '' && !in_array(mb_strtolower($patente), Vehiculo::PATENTES_NO_POSEE)) {
            $identificador = Str::slug($patente);
        } elseif (trim((string) $vehiculo->nro_patrimonio) !== '') {
            $identificador = 'patrimonio-' . Str::slug($vehiculo->nro_patrimonio);
        } else {
            $identificador = 'vehiculo-' . $vehiculo->id;
        }

        $nombre = $identificador . '_' . (Str::limit(Str::slug($descripcion), 60, '') ?: 'documento') . '_' . ($fecha ?? now())->format('Y-m-d_His');
        $directorio = self::DIRECTORIO . '/' . $vehiculo->id;
        $extension = strtolower($extension);

        $ruta = "{$directorio}/{$nombre}.{$extension}";
        for ($i = 2; Storage::disk(self::DISCO)->exists($ruta); $i++) {
            $ruta = "{$directorio}/{$nombre}-{$i}.{$extension}";
        }

        return $ruta;
    }
}
