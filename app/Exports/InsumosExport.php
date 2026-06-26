<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Exporta el listado de insumos respetando el query ya filtrado por permisos
 * y filtros activos de la pantalla /insumos.
 */
class InsumosExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(private Builder $query)
    {
    }

    public function query()
    {
        return $this->query->with(['categoriaInsumo', 'deposito.corralon'])->orderBy('insumo');
    }

    public function title(): string
    {
        return 'Insumos';
    }

    public function headings(): array
    {
        return [
            'Insumo',
            'Categoría',
            'Depósito',
            'Corralón',
            'Unidad',
            'Stock actual',
            'Stock mínimo',
            'Estado',
        ];
    }

    public function map($insumo): array
    {
        return [
            $insumo->insumo,
            $insumo->categoriaInsumo->nombre ?? '',
            $insumo->deposito->deposito ?? '',
            $insumo->deposito->corralon->descripcion ?? '',
            $insumo->unidad,
            (float) $insumo->stock_actual,
            (float) $insumo->stock_minimo,
            $this->estado($insumo),
        ];
    }

    private function estado($insumo): string
    {
        if ((float) $insumo->stock_actual <= 0) {
            return 'Sin stock';
        }
        if ((float) $insumo->stock_actual < (float) $insumo->stock_minimo) {
            return 'Bajo mínimo';
        }
        return 'OK';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
