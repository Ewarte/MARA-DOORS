<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>REPORTE DE PRODUCTOS</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .company-name {
            font-size: 15px;
            font-weight: bold;
            color: #2c3e50;
        }
        .doc-title {
            font-size: 13px;
            font-weight: bold;
            text-align: right;
            color: #2c3e50;
        }
        table.main-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        table.main-table th {
            background-color: #2c3e50;
            color: #ffffff;
            border: 1px solid #1a252f;
            padding: 5px 4px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
        }
        table.main-table td {
            border: 1px solid #c0c0c0;
            padding: 4px;
            vertical-align: top;
        }
        .row-odd  { background-color: #ffffff; }
        .row-even { background-color: #f4f4f4; }
        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .badge-activo   { color: #065f46; font-weight: bold; }
        .badge-inactivo { color: #991b1b; font-weight: bold; }
        .stock-ok  { color: #065f46; font-weight: bold; }
        .stock-low { color: #991b1b; font-weight: bold; }
        .footer-note {
            font-size: 8px;
            color: #777;
            text-align: right;
            margin-top: 15px;
            border-top: 1px solid #ddd;
            padding-top: 4px;
            position: fixed;
            bottom: 15px;
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
                <div style="font-size:9px; color:#666;">Sistema de Gestion de Inventario</div>
            </td>
            <td width="40%" style="text-align:right;">
                <div class="doc-title">REPORTE DE PRODUCTOS</div>
                <div style="font-size:8px; color:#555;">Fecha: {{ now()->format('d/m/Y H:i A') }}</div>
                <div style="font-size:8px; color:#555;">Total registros: {{ $productos->count() }}</div>
            </td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th width="5%">ID</th>
                <th width="9%">CÓDIGO</th>
                <th>PRODUCTO</th>
                @if(!empty($includeAllDetails))
                    <th width="11%">CATEGORÍA</th>
                    <th width="10%">MARCA</th>
                    <th width="8%">UNIDAD</th>
                @endif
                @if(!empty($includePrices))
                    <th width="9%" class="text-right">P. COMPRA</th>
                    <th width="9%" class="text-right">P. VENTA</th>
                @endif
                @if(!empty($includeStock))
                    <th width="7%" class="text-center">STOCK TOT.</th>
                    @foreach(($almacenes ?? []) as $almacen)
                        <th class="text-center" width="7%">{{ strtoupper(substr($almacen->nombre, 0, 8)) }}</th>
                    @endforeach
                @endif
                <th width="7%" class="text-center">ESTADO</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $i => $producto)
            @php
                $rowClass = ($i % 2 === 0) ? 'row-odd' : 'row-even';
                $invByAlmacen = isset($producto->inventarios) ? $producto->inventarios->keyBy('almacen_id') : collect();
            @endphp
            <tr class="{{ $rowClass }}">
                <td class="text-center">{{ $producto->id }}</td>
                <td>{{ $producto->codigo }}</td>
                <td><strong>{{ $producto->nombre }}</strong></td>
                @if(!empty($includeAllDetails))
                    <td>{{ optional($producto->categoria)->nombre ?? 'N/A' }}</td>
                    <td>{{ optional($producto->marca)->nombre ?? 'N/A' }}</td>
                    <td>{{ optional($producto->tipounidad)->nombre ?? 'N/A' }}</td>
                @endif
                @if(!empty($includePrices))
                    <td class="text-right">Bs. {{ number_format($producto->precio_compra, 2) }}</td>
                    <td class="text-right">Bs. {{ number_format($producto->precio_venta, 2) }}</td>
                @endif
                @if(!empty($includeStock))
                    @php $stockTotal = $producto->stock_total ?? 0; @endphp
                    <td class="text-center">
                        <span class="{{ $stockTotal <= 10 ? 'stock-low' : 'stock-ok' }}">{{ number_format($stockTotal, 0) }}</span>
                    </td>
                    @foreach(($almacenes ?? []) as $almacen)
                        @php $s = optional($invByAlmacen->get($almacen->id))->stock ?? 0; @endphp
                        <td class="text-center {{ $s <= 5 ? 'stock-low' : '' }}">{{ number_format($s, 0) }}</td>
                    @endforeach
                @endif
                <td class="text-center">
                    @if($producto->estado == 1)
                        <span class="badge-activo">ACTIVO</span>
                    @else
                        <span class="badge-inactivo">INACTIVO</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-note">
        Usuario: {{ auth()->user()->name }} | Generado: {{ now()->format('d/m/Y H:i:s') }}
    </div>

</body>
</html>
