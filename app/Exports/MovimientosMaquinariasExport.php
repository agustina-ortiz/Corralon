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
 * Exporta el historial de movimientos de maquinarias (filas planas) respetando
 * el query ya filtrado por permisos y filtros activos de /transferencias-maquinarias.
 */
class MovimientosMaquinariasExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    /** Cache simple para no repetir queries de destino por fila. */
    private array $cacheDestino = [];

    public function __construct(private Builder $query)
    {
    }

    public function query()
    {
        return $this->query
            ->with(['maquinaria.categoriaMaquinaria', 'maquinaria.deposito.corralon', 'depositoEntrada', 'tipoMovimiento', 'usuario', 'secretaria'])
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
            'Maquinaria',
            'Categoría',
            'Cantidad',
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
            optional($mov->fecha)->format('d/m/Y'),
            $tipo->tipo_movimiento ?? '',
            $es,
            $mov->maquinaria->maquinaria ?? '',
            $mov->maquinaria->categoriaMaquinaria->nombre ?? '',
            (int) $mov->cantidad,
            $mov->depositoEntrada->deposito ?? ($mov->maquinaria->deposito->deposito ?? ''),
            $mov->maquinaria->deposito->corralon->descripcion ?? '',
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

        if ($tipoRef === 'transferencia') {
            return 'Transferencia';
        }
        if (in_array($tipoRef, ['inventario', 'deposito', 'maquina']) || !$idRef) {
            return '';
        }

        $key = "{$tipoRef}-{$idRef}";
        if (isset($this->cacheDestino[$key])) {
            $base = $this->cacheDestino[$key];
        } else {
            $base = $this->cacheDestino[$key] = match ($tipoRef) {
                'vehiculo' => optional(Vehiculo::find($idRef))->vehiculo ?? "Vehículo #{$idRef}",
                'evento' => optional(Evento::find($idRef))->evento ?? "Evento #{$idRef}",
                'empleado' => optional(EmpleadoMunicipal::find($idRef))->nombre_formateado ?? "Empleado #{$idRef}",
                'secretaria' => 'Secretaría: ' . (optional(Secretaria::find($idRef))->secretaria ?? "#{$idRef}"),
                default => '',
            };
        }

        if ($tipoRef === 'secretaria' && $mov->area) {
            return $base . " ({$mov->area})";
        }
        return $base;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
