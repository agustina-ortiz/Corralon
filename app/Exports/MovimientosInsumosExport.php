<?php

namespace App\Exports;

use App\Models\Vehiculo;
use App\Models\Evento;
use App\Models\Secretaria;
use App\Models\EmpleadoMunicipal;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Exporta el historial de movimientos de insumos (filas planas) respetando
 * el query ya filtrado por permisos y filtros activos de /transferencias-insumos.
 */
class MovimientosInsumosExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    /** Cache simple para no repetir queries de destino por fila. */
    private array $cacheDestino = [];

    public function __construct(private Builder $query)
    {
    }

    public function query()
    {
        return $this->query
            ->with(['insumo.categoriaInsumo', 'insumo.deposito.corralon', 'tipoMovimiento', 'usuario', 'secretaria'])
            ->orderByDesc('fecha')
            ->orderByDesc('id');
    }

    public function title(): string
    {
        return 'Movimientos';
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Tipo de movimiento',
            'E/S',
            'Insumo',
            'Categoría',
            'Cantidad',
            'Unidad',
            'Depósito',
            'Corralón',
            'Destino',
            'Usuario',
            'N° OC',
            'Observaciones',
        ];
    }

    public function map($mov): array
    {
        $tipo = $mov->tipoMovimiento;
        $es = $tipo?->esEntrada() ? 'Entrada' : ($tipo?->esSalida() ? 'Salida' : 'Neutral');

        return [
            optional($mov->fecha)->format('d/m/Y H:i'),
            $tipo->tipo_movimiento ?? '',
            $es,
            $mov->insumo->insumo ?? '',
            $mov->insumo->categoriaInsumo->nombre ?? '',
            (float) $mov->cantidad,
            $mov->insumo->unidad ?? '',
            $mov->insumo->deposito->deposito ?? '',
            $mov->insumo->deposito->corralon->descripcion ?? '',
            $this->destino($mov),
            $mov->usuario->name ?? '',
            $mov->nro_orden_compra ?? '',
            $mov->observaciones ?? '',
        ];
    }

    /** Resuelve el nombre legible del destino del movimiento. */
    private function destino($mov): string
    {
        $tipoRef = $mov->tipo_referencia;
        $idRef = $mov->id_referencia;

        if (in_array($tipoRef, ['inventario', 'deposito', 'transferencia']) || !$idRef) {
            if ($tipoRef === 'transferencia') {
                return 'Transferencia';
            }
            return '';
        }

        $key = "{$tipoRef}-{$idRef}";
        if (isset($this->cacheDestino[$key])) {
            return $this->cacheDestino[$key];
        }

        $nombre = match ($tipoRef) {
            'vehiculo' => optional(Vehiculo::find($idRef))->vehiculo ?? "Vehículo #{$idRef}",
            'evento' => optional(Evento::find($idRef))->evento ?? "Evento #{$idRef}",
            'empleado' => optional(EmpleadoMunicipal::find($idRef))->nombre_formateado ?? "Empleado #{$idRef}",
            'secretaria' => 'Secretaría: ' . (optional(Secretaria::find($idRef))->secretaria ?? "#{$idRef}")
                . ($mov->area ? " ({$mov->area})" : ''),
            default => '',
        };

        return $this->cacheDestino[$key] = $nombre;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
