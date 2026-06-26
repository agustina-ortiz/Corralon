<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 10px; color: #1f2937; margin: 0; }
        .header { border-bottom: 2px solid #77BF43; padding-bottom: 8px; margin-bottom: 12px; }
        .header h1 { font-size: 16px; margin: 0; color: #374151; }
        .header .sub { font-size: 9px; color: #6b7280; margin-top: 2px; }
        /* table-layout fixed + anchos explícitos: mantiene las columnas alineadas
           entre las distintas tablas (una por chunk). */
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th { background: #f3f4f6; text-align: left; padding: 5px 6px; border-bottom: 1px solid #d1d5db; font-size: 9px; text-transform: uppercase; }
        td { padding: 4px 6px; border-bottom: 1px solid #f0f0f0; word-wrap: break-word; }
        .num { text-align: right; }
        .badge { padding: 1px 5px; border-radius: 4px; font-size: 8px; font-weight: bold; }
        .ok { background: #dcfce7; color: #166534; }
        .bajo { background: #fef3c7; color: #92400e; }
        .cero { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 10px; font-size: 8px; color: #9ca3af; text-align: right; }
        .col-insumo { width: 22%; } .col-cat { width: 16%; } .col-dep { width: 16%; }
        .col-corr { width: 14%; } .col-un { width: 8%; } .col-stock { width: 8%; }
        .col-min { width: 8%; } .col-est { width: 8%; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Listado de Insumos</h1>
        <div class="sub">
            Municipalidad de Mercedes &middot; Generado el {{ now()->format('d/m/Y H:i') }}
            @if($filtros) &middot; {{ $filtros }} @endif
        </div>
    </div>

    @forelse($insumos->chunk(40) as $chunk)
        <table>
            <thead>
                <tr>
                    <th class="col-insumo">Insumo</th>
                    <th class="col-cat">Categoría</th>
                    <th class="col-dep">Depósito</th>
                    <th class="col-corr">Corralón</th>
                    <th class="col-un">Unidad</th>
                    <th class="col-stock num">Stock</th>
                    <th class="col-min num">Mínimo</th>
                    <th class="col-est">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chunk as $i)
                    @php
                        $estado = (float)$i->stock_actual <= 0 ? 'cero' : ((float)$i->stock_actual < (float)$i->stock_minimo ? 'bajo' : 'ok');
                        $estadoTxt = $estado === 'cero' ? 'Sin stock' : ($estado === 'bajo' ? 'Bajo mínimo' : 'OK');
                    @endphp
                    <tr>
                        <td>{{ $i->insumo }}</td>
                        <td>{{ $i->categoriaInsumo->nombre ?? '' }}</td>
                        <td>{{ $i->deposito->deposito ?? '' }}</td>
                        <td>{{ $i->deposito->corralon->descripcion ?? '' }}</td>
                        <td>{{ $i->unidad }}</td>
                        <td class="num">{{ number_format($i->stock_actual, 2) }}</td>
                        <td class="num">{{ number_format($i->stock_minimo, 2) }}</td>
                        <td><span class="badge {{ $estado }}">{{ $estadoTxt }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <table>
            <tbody>
                <tr><td style="text-align:center; padding:12px; color:#9ca3af;">No hay insumos para los filtros seleccionados.</td></tr>
            </tbody>
        </table>
    @endforelse

    <div class="footer">Total: {{ $insumos->count() }} insumo(s)</div>
</body>
</html>
