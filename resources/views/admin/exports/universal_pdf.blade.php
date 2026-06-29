<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $title }}</title>
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
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
        }
        .doc-title {
            font-size: 14px;
            font-weight: bold;
            text-align: right;
            color: #2c3e50;
            text-transform: uppercase;
        }
        .table-details {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .table-details th {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            color: #444;
        }
        .table-details td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            vertical-align: middle;
        }
        .table-details tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer-note {
            font-size: 8px;
            color: #777;
            text-align: right;
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 5px;
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
                <div style="font-size:10px; color:#666;">Sistema de Gestión de Inventario</div>
            </td>
            <td width="40%" style="text-align: right;">
                <div class="doc-title">{{ $title }}</div>
                <div style="font-size:9px; color:#555;">Generado: {{ $date }}</div>
                <div style="font-size:9px; color:#555;">Registros: {{ $count }}</div>
            </td>
        </tr>
    </table>

    <table class="table-details">
        <thead>
            <tr>
                @foreach($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
            <tr>
                @foreach($row as $cell)
                    <td>{{ $cell }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-note">
        Usuario: {{ auth()->user()->name ?? 'Sistema' }} | Página: <span class="pagenum"></span>
    </div>
</body>
</html>

