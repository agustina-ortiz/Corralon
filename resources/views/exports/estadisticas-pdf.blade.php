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

        /* Cada gráfico es un bloque; se evita cortarlo entre páginas. */
        .grafico { border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 12px; margin-bottom: 12px; page-break-inside: avoid; }
        .grafico h2 { font-size: 11px; margin: 0 0 8px 0; color: #374151; }
        .grafico .meta { font-size: 8px; color: #9ca3af; font-weight: normal; }
        .vacio { font-size: 9px; color: #9ca3af; padding: 8px 0; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td { vertical-align: middle; padding: 2px 0; word-wrap: break-word; }
        .lbl { font-size: 9px; color: #4b5563; padding-right: 6px; }
        .val { font-size: 9px; color: #111827; font-weight: bold; text-align: right; padding-left: 6px; }
        .pct { font-size: 8px; color: #9ca3af; font-weight: normal; }

        /* Barra: contenedor gris + relleno de color con ancho porcentual */
        .pista { background: #f1f5f9; height: 8px; border-radius: 4px; }
        .relleno { height: 8px; border-radius: 4px; }

        .chip { display: inline-block; width: 8px; height: 8px; border-radius: 2px; }

        /* Tabla de choferes */
        .tabla th { background: #f3f4f6; text-align: left; padding: 4px 6px; border-bottom: 1px solid #d1d5db; font-size: 8px; text-transform: uppercase; }
        .tabla td { padding: 4px 6px; border-bottom: 1px solid #f0f0f0; font-size: 9px; }
        .rojo { color: #b91c1c; font-weight: bold; }
        .naranja { color: #c2410c; font-weight: bold; }

        .footer { margin-top: 8px; font-size: 8px; color: #9ca3af; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Estadísticas</h1>
        <div class="sub">
            Municipalidad de Mercedes &middot; Generado el {{ now()->format('d/m/Y H:i') }} por {{ $usuario }}
            @if($filtros) &middot; {{ $filtros }} @endif
        </div>
    </div>

    @foreach($widgets as $key => $w)
        @php $decimales = $w['decimales'] ?? 0; @endphp
        <div class="grafico">
            <h2>
                {{ $w['titulo'] }}
                @if($w['paginacion'] ?? null)
                    <span class="meta">
                        &middot; {{ $w['paginacion']['desde'] }}–{{ $w['paginacion']['hasta'] }} de
                        {{ number_format($w['paginacion']['total'], 0, ',', '.') }}
                        (página {{ $w['paginacion']['pagina'] }} de {{ $w['paginacion']['total_paginas'] }})
                    </span>
                @endif
            </h2>

            @switch($w['tipo'])

                {{-- ---------- DONA ---------- --}}
                @case('donut')
                    @php
                        $segmentos = collect($w['data'])->filter(fn($d) => ($d['value'] ?? 0) > 0)->values();
                        $total = $segmentos->sum('value');
                    @endphp
                    @if($total > 0)
                        <table>
                            <tr>
                                <td style="width: 32%; text-align: center;">
                                    @if($w['imagen'])
                                        <img src="{{ $w['imagen'] }}" style="width: 130px; height: 130px;">
                                    @endif
                                    <div style="font-size: 9px; color: #6b7280; margin-top: 2px;">
                                        Total: <b style="color:#111827">{{ number_format($total, $decimales, ',', '.') }}</b>
                                    </div>
                                </td>
                                <td style="width: 68%; padding-left: 10px;">
                                    <table>
                                        @foreach($segmentos as $i => $seg)
                                            <tr>
                                                {{-- El chip va dentro de la celda del label: dompdf no respeta
                                                     bien anchos en px con table-layout fixed. --}}
                                                <td class="lbl" style="width: 68%;">
                                                    <span class="chip" style="background: {{ $paleta[$i % count($paleta)] }}"></span>
                                                    {{ $seg['label'] }}
                                                </td>
                                                <td class="val" style="width: 32%;">
                                                    {{ number_format($seg['value'], $decimales, ',', '.') }}
                                                    <span class="pct">({{ round($seg['value'] / $total * 100) }}%)</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </td>
                            </tr>
                        </table>
                    @else
                        <div class="vacio">Sin datos para mostrar</div>
                    @endif
                    @break

                {{-- ---------- BARRAS HORIZONTALES ---------- --}}
                @case('barras')
                    @php
                        $items = collect($w['data']);
                        if (!($w['paginacion'] ?? null)) {
                            $items = $items->filter(fn($d) => ($d['value'] ?? 0) > 0);
                        }
                        $items = $items->values();
                        $max = ($w['max'] ?? null) ?: ($items->max('value') ?: 1);
                        $offsetColor = ($w['paginacion'] ?? null) ? (($w['paginacion']['desde'] ?: 1) - 1) : 0;
                    @endphp
                    @if($items->count() > 0)
                        <table>
                            @foreach($items as $i => $it)
                                @php
                                    $color = $paleta[($i + $offsetColor) % count($paleta)];
                                    $ancho = ($it['value'] ?? 0) > 0 ? max(2, round($it['value'] / $max * 100, 2)) : 0;
                                @endphp
                                <tr>
                                    <td class="lbl" style="width: 32%;">{{ $it['label'] }}</td>
                                    <td style="width: 50%;">
                                        <div class="pista">
                                            @if($ancho > 0)
                                                <div class="relleno" style="width: {{ $ancho }}%; background: {{ $color }};"></div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="val" style="width: 18%;">{{ number_format($it['value'], $decimales, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <div class="vacio">Sin datos para mostrar</div>
                    @endif
                    @break

                {{-- ---------- SERIES (Entradas vs. Salidas) ---------- --}}
                @case('series')
                    @php
                        $items = collect($w['data']);
                        $max = max(1, $items->max(fn($d) => max($d['entradas'] ?? 0, $d['salidas'] ?? 0)));
                        $hayDatos = $items->sum(fn($d) => ($d['entradas'] ?? 0) + ($d['salidas'] ?? 0)) > 0;
                    @endphp
                    @if($hayDatos)
                        <div style="font-size: 8px; color: #6b7280; margin-bottom: 4px;">
                            <span class="chip" style="background:#77BF43"></span> Entradas
                            &nbsp;&nbsp;
                            <span class="chip" style="background:#EF4444"></span> Salidas
                        </div>
                        <table>
                            @foreach($items as $it)
                                @php
                                    $ent = (float) ($it['entradas'] ?? 0);
                                    $sal = (float) ($it['salidas'] ?? 0);
                                @endphp
                                <tr>
                                    <td class="lbl" style="width: 14%;" rowspan="2">{{ $it['label'] }}</td>
                                    <td style="width: 68%;">
                                        <div class="pista" style="height: 6px;">
                                            @if($ent > 0)
                                                <div class="relleno" style="height: 6px; width: {{ max(2, round($ent / $max * 100, 2)) }}%; background:#77BF43;"></div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="val" style="width: 18%;">{{ number_format($ent, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="pista" style="height: 6px;">
                                            @if($sal > 0)
                                                <div class="relleno" style="height: 6px; width: {{ max(2, round($sal / $max * 100, 2)) }}%; background:#EF4444;"></div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="val">{{ number_format($sal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <div class="vacio">Sin movimientos en el período</div>
                    @endif
                    @break

                {{-- ---------- LISTA (choferes con licencia por vencer) ---------- --}}
                @case('lista')
                    @if($w['data']->count() > 0)
                        <table class="tabla">
                            <thead>
                                <tr>
                                    <th style="width: 38%;">Chofer</th>
                                    <th style="width: 14%;">Licencia</th>
                                    <th style="width: 28%;">Secretaría</th>
                                    <th style="width: 12%;">Vencimiento</th>
                                    <th style="width: 8%;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($w['data'] as $chofer)
                                    @php
                                        $venc = $chofer->vencimiento_licencia;
                                        $dias = $venc ? (int) \Carbon\Carbon::today()->diffInDays($venc, false) : null;
                                        $clase = $dias === null ? '' : ($dias < 0 ? 'rojo' : ($dias <= 15 ? 'naranja' : ''));
                                    @endphp
                                    <tr>
                                        <td>{{ $chofer->nombre }}</td>
                                        <td>{{ $chofer->tipo_licencia ?: '—' }}</td>
                                        <td>{{ $chofer->secretaria->secretaria ?? '—' }}</td>
                                        <td>{{ $venc?->format('d/m/Y') ?: '—' }}</td>
                                        <td class="{{ $clase }}">
                                            @if($dias === null) —
                                            @elseif($dias < 0) Vencida ({{ abs($dias) }}d)
                                            @elseif($dias === 0) Vence hoy
                                            @else {{ $dias }}d @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="vacio">No hay licencias por vencer</div>
                    @endif
                    @break

            @endswitch
        </div>
    @endforeach

    <div class="footer">{{ count($widgets) }} gráfico(s) &middot; Sistema de Gestión de Corralones</div>
</body>
</html>
