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
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th { background: #f3f4f6; text-align: left; padding: 5px 6px; border-bottom: 1px solid #d1d5db; font-size: 9px; text-transform: uppercase; }
        td { padding: 4px 6px; border-bottom: 1px solid #f0f0f0; word-wrap: break-word; }
        .num { text-align: right; }
        .badge { padding: 1px 5px; border-radius: 4px; font-size: 8px; font-weight: bold; }
        .ok { background: #dcfce7; color: #166534; }
        .no { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 10px; font-size: 8px; color: #9ca3af; text-align: right; }
        .c-maq { width: 26%; } .c-cat { width: 18%; } .c-dep { width: 18%; }
        .c-corr { width: 14%; } .c-tot { width: 8%; } .c-disp { width: 8%; } .c-est { width: 8%; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Listado de Maquinarias</h1>
        <div class="sub">
            Municipalidad de Mercedes &middot; Generado el {{ now()->format('d/m/Y H:i') }}
            @if($filtros) &middot; {{ $filtros }} @endif
        </div>
    </div>

    @forelse($maquinarias->chunk(40) as $chunk)
        <table>
            <thead>
                <tr>
                    <th class="c-maq">Maquinaria</th>
                    <th class="c-cat">Categoría</th>
                    <th class="c-dep">Depósito</th>
                    <th class="c-corr">Corralón</th>
                    <th class="c-tot num">Total</th>
                    <th class="c-disp num">Disp.</th>
                    <th class="c-est">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chunk as $m)
                    @php $disp = (int) $m->cantidad_disponible; @endphp
                    <tr>
                        <td>{{ $m->maquinaria }}</td>
                        <td>{{ $m->categoriaMaquinaria->nombre ?? '' }}</td>
                        <td>{{ $m->deposito->deposito ?? '' }}</td>
                        <td>{{ $m->deposito->corralon->descripcion ?? '' }}</td>
                        <td class="num">{{ (int) $m->cantidad }}</td>
                        <td class="num">{{ $disp }}</td>
                        <td><span class="badge {{ $disp > 0 ? 'ok' : 'no' }}">{{ $disp > 0 ? 'Disponible' : 'No disp.' }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <table><tbody><tr><td style="text-align:center; padding:12px; color:#9ca3af;">No hay maquinarias para los filtros seleccionados.</td></tr></tbody></table>
    @endforelse

    <div class="footer">Total: {{ $maquinarias->count() }} maquinaria(s)</div>
</body>
</html>
