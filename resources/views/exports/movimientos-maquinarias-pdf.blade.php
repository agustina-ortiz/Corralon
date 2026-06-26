<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 9px; color: #1f2937; margin: 0; }
        .header { border-bottom: 2px solid #77BF43; padding-bottom: 8px; margin-bottom: 12px; }
        .header h1 { font-size: 16px; margin: 0; color: #374151; }
        .header .sub { font-size: 9px; color: #6b7280; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th { background: #f3f4f6; text-align: left; padding: 4px 5px; border-bottom: 1px solid #d1d5db; font-size: 8px; text-transform: uppercase; }
        td { padding: 3px 5px; border-bottom: 1px solid #f0f0f0; word-wrap: break-word; overflow: hidden; }
        .num { text-align: right; }
        .es-entrada { color: #166534; font-weight: bold; text-align: center; }
        .es-salida { color: #991b1b; font-weight: bold; text-align: center; }
        .footer { margin-top: 10px; font-size: 8px; color: #9ca3af; text-align: right; }
        .c-fecha { width: 9%; } .c-tipo { width: 17%; } .c-es { width: 4%; }
        .c-maq { width: 18%; } .c-cant { width: 7%; }
        .c-dep { width: 13%; } .c-dest { width: 15%; } .c-usr { width: 10%; } .c-oc { width: 7%; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Movimientos de Maquinarias</h1>
        <div class="sub">
            Municipalidad de Mercedes &middot; Generado el {{ now()->format('d/m/Y H:i') }}
            @if($filtros) &middot; {{ $filtros }} @endif
        </div>
    </div>

    @forelse($movimientos->chunk(45) as $chunk)
        <table>
            <thead>
                <tr>
                    <th class="c-fecha">Fecha</th>
                    <th class="c-tipo">Tipo</th>
                    <th class="c-es">E/S</th>
                    <th class="c-maq">Maquinaria</th>
                    <th class="c-cant num">Cant.</th>
                    <th class="c-dep">Depósito</th>
                    <th class="c-dest">Destino</th>
                    <th class="c-usr">Usuario</th>
                    <th class="c-oc">N° OC</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chunk as $m)
                    @php
                        $tipo = $m->tipoMovimiento;
                        $esClase = $tipo?->esEntrada() ? 'es-entrada' : ($tipo?->esSalida() ? 'es-salida' : '');
                        $esTxt = $tipo?->esEntrada() ? '▲' : ($tipo?->esSalida() ? '▼' : '–');
                    @endphp
                    <tr>
                        <td>{{ optional($m->fecha)->format('d/m/Y') }}</td>
                        <td>{{ $tipo->tipo_movimiento ?? '' }}</td>
                        <td class="{{ $esClase }}">{{ $esTxt }}</td>
                        <td>{{ $m->maquinaria->maquinaria ?? '' }}</td>
                        <td class="num">{{ (int) $m->cantidad }}</td>
                        <td>{{ $m->depositoEntrada->deposito ?? ($m->maquinaria->deposito->deposito ?? '') }}</td>
                        <td>{{ $destinos[$m->id] ?? '' }}</td>
                        <td>{{ $m->usuario->name ?? '' }}</td>
                        <td>{{ $m->nro_orden_compra ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <table><tbody><tr><td style="text-align:center; padding:12px; color:#9ca3af;">No hay movimientos para los filtros seleccionados.</td></tr></tbody></table>
    @endforelse

    <div class="footer">Total: {{ $movimientos->count() }} movimiento(s)</div>
</body>
</html>
