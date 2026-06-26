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
 * Exporta el listado de maquinarias respetando el query ya filtrado por permisos
 * y filtros activos de la pantalla /maquinarias.
 */
class MaquinariasExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(private Builder $query)
    {
    }

    public function query()
    {
        return $this->query->with(['categoriaMaquinaria', 'deposito.corralon'])->orderBy('maquinaria');
    }

    public function title(): string
    {
        return 'Maquinarias';
    }

    public function headings(): array
    {
        return [
            'Maquinaria',
            'Categoría',
            'Depósito',
            'Corralón',
            'Cantidad total',
            'Disponible',
            'Estado',
        ];
    }

    public function map($maquinaria): array
    {
        $disponible = (int) $maquinaria->cantidad_disponible;

        return [
            $maquinaria->maquinaria,
            $maquinaria->categoriaMaquinaria->nombre ?? '',
            $maquinaria->deposito->deposito ?? '',
            $maquinaria->deposito->corralon->descripcion ?? '',
            (int) $maquinaria->cantidad,
            $disponible,
            $disponible > 0 ? 'Disponible' : 'No disponible',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
