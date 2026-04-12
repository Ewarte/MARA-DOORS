<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>VENTA #{{ $venta->numero_comprobante }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #555;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }
        .doc-title {
            font-size: 14px;
            font-weight: bold;
            text-align: right;
            color: #2c3e50;
        }
        .section-box {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            background-color: #fff;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            margin-bottom: 8px;
            padding-bottom: 4px;
        }
        .info-table {
            width: 100%;
        }
        .info-table td {
            vertical-align: top;
            padding: 3px;
        }
        .label {
            font-weight: bold;
            color: #555;
            font-size: 9px;
            margin-bottom: 2px;
        }
        .value {
            font-size: 11px;
            color: #000;
        }
        .table-details {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .table-details th {
            background-color: #f5f5f5;
            border-bottom: 1px solid #ccc;
            padding: 6px;
            text-align: left;
            font-weight: bold;
        }
        .table-details td {
            border-bottom: 1px solid #eee;
            padding: 6px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer-note {
            font-size: 8px;
            color: #777;
            text-align: right;
            margin-top: 10px;
        }
        .badge {
            background-color: #eee; 
            padding: 2px 5px; 
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <table class="header-table">
        <tr>
            <td width="60%">
                <div class="company-name">MARA-DOORS</div>
                <div style="font-size:10px;">Ballivian entre 13 y 14 - Telf: 71190122</div>
            </td>
            <td width="40%" class="text-right">
                <div class="doc-title">NOTA DE VENTA</div>
                <div style="font-size:12px;">Nro: {{ $venta->numero_comprobante }}</div>
                <div style="font-size:9px; color:#555;">{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/Y H:i A') }}</div>
            </td>
        </tr>
    </table>

    <!-- Datos Generales -->
    <div class="section-box">
        <div class="section-title">DATOS GENERALES</div>
        <table class="info-table">
            <tr>
                <td width="33%">
                    <div class="label">CLIENTE</div>
                    <div class="value">{{ $venta->cliente->persona->razon_social }}</div>
                    <div style="font-size:9px;">{{ $venta->cliente->persona->tipo_documento }} {{ $venta->cliente->persona->numero_documento }}</div>
                </td>
                <td width="33%">
                    <div class="label">SUCURSAL</div>
                    <div class="value">{{ $venta->almacen->nombre ?? 'N/A' }}</div>
                </td>
                <td width="33%">
                    <div class="label">COMPROBANTE</div>
                    <div class="value">{{ $venta->comprobante->tipo_comprobante ?? 'N/A' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label" style="margin-top:5px;">VENDEDOR</div>
                    <div class="value">{{ $venta->user->name ?? 'Sistema' }}</div>
                </td>
                <td colspan="2">
                     <div class="label" style="margin-top:5px;">ESTADO</div>
                     <div class="value">{{ $venta->estado ? 'ACTIVA' : 'ANULADA' }}</div>
                </td>
            </tr>
            @if($venta->nota_personal || $venta->nota_cliente)
            <tr>
                <td colspan="3">
                    <div class="label" style="margin-top:5px;">NOTAS</div>
                    @if($venta->nota_personal)
                    <div class="value" style="font-style:italic;">Interna: {{ $venta->nota_personal }}</div>
                    @endif
                    @if($venta->nota_cliente)
                    <div class="value" style="font-style:italic;">Cliente: {{ $venta->nota_cliente }}</div>
                    @endif
                </td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Detalles -->
    <div class="section-box">
        <div class="section-title">DETALLES DE LA VENTA</div>
        <table class="table-details">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th>PRODUCTO</th>
                    <th width="10%" class="text-right">CANT.</th>
                    <th width="15%" class="text-right">PRECIO</th>
                    <th width="15%" class="text-right">DESC.</th>
                    <th width="15%" class="text-right">SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($venta->detalles as $index => $detalle)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $detalle->producto->nombre }}
                        <br><span style="color:#777; font-size:8px;">{{ $detalle->producto->codigo }}</span>
                    </td>
                    <td class="text-right">{{ number_format($detalle->cantidad, 2) }}</td>
                    <td class="text-right">{{ number_format($detalle->precio_venta, 2) }}</td>
                    <td class="text-right">{{ number_format($detalle->descuento, 2) }}</td>
                    <td class="text-right">{{ number_format(($detalle->cantidad * $detalle->precio_venta) - $detalle->descuento, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right" style="padding-top:10px;"><strong>TOTAL:</strong></td>
                    <td class="text-right" style="padding-top:10px; font-size:12px;"><strong>Bs. {{ number_format($venta->total, 2) }}</strong></td>
                </tr>
                <tr>
                    <td colspan="5" class="text-right"><strong>POR COBRAR:</strong></td>
                    <td class="text-right">Bs. {{ number_format($venta->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Firmas -->
    <table style="width: 100%; margin-top: 40px;">
        <tr>
            <td width="40%" class="text-center">
                <div style="border-top: 1px solid #000; margin: 0 20px; padding-top: 5px; font-size: 9px;">
                    ENTREGUE CONFORME (Vendedor)
                </div>
            </td>
            <td width="20%"></td>
            <td width="40%" class="text-center">
                <div style="border-top: 1px solid #000; margin: 0 20px; padding-top: 5px; font-size: 9px;">
                    RECIBI CONFORME (Cliente)
                </div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Usuario: {{ auth()->user()->name }} | Fecha Impreso: {{ now()->format('d/m/Y H:i:s') }}
        <br>{{ $venta->comprobante->tipo_comprobante === 'Factura' ? 'Documento valido como credito fiscal' : 'Documento no valido como factura' }}
    </div>
</body>
</html>

