<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>REPORTE DE PRODUCTOS</title>
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
        .table-details {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .table-details th {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
        }
        .table-details td {
            border: 1px solid #ddd;
            padding: 6px;
            vertical-align: middle;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge {
            padding: 2px 5px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .footer-note {
            font-size: 8px;
            color: #777;
            text-align: right;
            margin-top: 20px;
            position: fixed;
            bottom: 20px;
            right: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td width="60%">
                <div class="company-name">HIERROPAR</div>
                <div style="font-size:10px;">Sistema de Gestion de Inventario</div>
            </td>
            <td width="40%" class="text-right">
                <div class="doc-title">REPORTE DE PRODUCTOS</div>
                <div style="font-size:9px; color:#555;">Fecha: {{ now()->format('d/m/Y H:i A') }}</div>
            </td>
        </tr>
    </table>

    <table class="table-details">
        <thead>
            <tr>
                <th width="5%">ID</th>
                <th width="10%">CODIGO</th>
                <th>PRODUCTO</th>
                @if(!empty($includeAllDetails))
                    <th width="12%">CATEGORIA</th>
                    <th width="12%">MARCA</th>
                    <th width="10%">UNIDAD</th>
                @endif
                @if(!empty($includePrices))
                    <th width="9%" class="text-right">P. COMPRA</th>
                    <th width="9%" class="text-right">P. VENTA</th>
                @endif
                @if(!empty($includeStock))
                    <th width="7%" class="text-center">STOCK TOTAL</th>
                    <th width="18%">STOCK POR SUCURSAL</th>
                @endif
                <th width="8%" class="text-center">ESTADO</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $producto)
            <tr>
                <td class="text-center">{{ $producto->id }}</td>
                <td>{{ $producto->codigo }}</td>
                <td>
                    <strong>{{ $producto->nombre }}</strong>
                </td>
                @if(!empty($includeAllDetails))
                    <td>{{ $producto->categoria->nombre ?? 'N/A' }}</td>
                    <td>{{ $producto->marca->nombre ?? 'N/A' }}</td>
                    <td>{{ $producto->tipounidad->nombre ?? 'N/A' }}</td>
                @endif
                @if(!empty($includePrices))
                    <td class="text-right">Bs. {{ number_format($producto->precio_compra, 2) }}</td>
                    <td class="text-right">Bs. {{ number_format($producto->precio_venta, 2) }}</td>
                @endif
                @if(!empty($includeStock))
                    <td class="text-center">
                        <span style="font-weight: bold; {{ (($producto->stock_total ?? 0) <= 10) ? 'color: #991b1b;' : 'color: #065f46;' }}">
                            {{ number_format($producto->stock_total ?? 0, 0) }}
                        </span>
                    </td>
                    <td style="font-size:8px; line-height:1.2;">
                        @php
                            $invByAlmacen = $producto->inventarios->keyBy('almacen_id');
                        @endphp
                        @foreach(($almacenes ?? []) as $almacen)
                            <div>
                                <strong>{{ $almacen->nombre }}:</strong>
                                {{ number_format(optional($invByAlmacen->get($almacen->id))->stock ?? 0, 0) }}
                            </div>
                        @endforeach
                    </td>
                @endif
                <td class="text-center">
                    @if($producto->estado == 1)
                        <span class="badge badge-success">ACTIVO</span>
                    @else
                        <span class="badge badge-danger">INACTIVO</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-note">
        Usuario: {{ auth()->user()->name }} | Pagina: {PAGINA_ACTUAL} de {TOTAL_PAGINAS} | Generado: {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>

