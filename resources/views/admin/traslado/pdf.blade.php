<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Traslados</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
        }

        .header h1 {
            margin: 0 0 5px 0;
            color: #667eea;
            font-size: 24px;
        }

        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table thead {
            background-color: #667eea;
            color: white;
        }

        table th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            border: 1px solid #667eea;
        }

        table td {
            padding: 10px 12px;
            border: 1px solid #ddd;
            font-size: 10px;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tbody tr:hover {
            background-color: #f0f0f0;
        }

        .estado-pendiente {
            color: #ff9800;
            font-weight: bold;
        }

        .estado-completado {
            color: #4caf50;
            font-weight: bold;
        }

        .estado-cancelado {
            color: #f44336;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }

        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f5f5f5;
            border-left: 4px solid #667eea;
            border-radius: 4px;
        }

        .summary p {
            margin: 5px 0;
            font-size: 11px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Traslados</h1>
        <p>Fecha de generacion: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Total de registros: {{ count($traslados) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Origen</th>
                <th>Destino</th>
                @if($includeUsuario ?? true)
                    <th>Usuario</th>
                @endif
                @if($includeCosto ?? true)
                    <th>Costo</th>
                @endif
                <th>Estado</th>
                @if($includeDetalles ?? true)
                    <th>Productos</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @php
                $estadoMap = [1 => 'Pendiente', 2 => 'Completado', 3 => 'Cancelado'];
                $costoTotal = 0;
            @endphp
            @foreach ($traslados as $traslado)
                @php
                    if ($includeCosto ?? true) {
                        $costoTotal += $traslado->costo_envio;
                    }
                    $estadoClass = 'estado-' . strtolower($estadoMap[$traslado->estado] ?? 'desconocido');
                @endphp
                <tr>
                    <td>{{ $traslado->id }}</td>
                    <td>{{ $traslado->fecha_hora->format('d/m/Y H:i') }}</td>
                    <td>{{ $traslado->origenAlmacen?->nombre ?? 'N/A' }}</td>
                    <td>{{ $traslado->destinoAlmacen?->nombre ?? 'N/A' }}</td>
                    @if($includeUsuario ?? true)
                        <td>{{ $traslado->user?->name ?? 'N/A' }}</td>
                    @endif
                    @if($includeCosto ?? true)
                        <td>Bs {{ number_format($traslado->costo_envio, 2) }}</td>
                    @endif
                    <td><span class="{{ $estadoClass }}">{{ $estadoMap[$traslado->estado] ?? 'Desconocido' }}</span></td>
                    @if($includeDetalles ?? true)
                        <td>
                            @if($traslado->detalles->count() > 0)
                                @foreach ($traslado->detalles as $detalle)
                                    <div>{{ $detalle->producto?->nombre ?? 'Producto eliminado' }} (x{{ $detalle->cantidad }})</div>
                                @endforeach
                            @else
                                <div>-</div>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <p><strong>Resumen:</strong></p>
        <p>Total de traslados: <strong>{{ count($traslados) }}</strong></p>
        @if($includeCosto ?? true)
            <p>Costo total de envio: <strong>Bs {{ number_format($costoTotal, 2) }}</strong></p>
        @endif
        <p>Pendientes: <strong>{{ $traslados->where('estado', 1)->count() }}</strong></p>
        <p>Completados: <strong>{{ $traslados->where('estado', 2)->count() }}</strong></p>
        <p>Cancelados: <strong>{{ $traslados->where('estado', 3)->count() }}</strong></p>
    </div>

    <div class="footer">
        <p>Este documento fue generado automaticamente por el sistema de gestion de traslados.</p>
    </div>
</body>
</html>

