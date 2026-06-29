@extends('admin.layouts.app')

@section('title', 'Panel de Administración')

@push('css')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --bg-body: #f1f5f9;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-dark);
        }

        /* ===== ANIMACIONES ===== */
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }

        .pulse-soft {
            animation: pulseSoft 2s infinite;
        }

        @keyframes pulseSoft {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        /* ===== TARJETAS KPI ===== */
        .kpi-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--accent-color);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .kpi-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: var(--accent-color);
        }

        .kpi-card:hover::before {
            opacity: 1;
        }

        .kpi-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            background: var(--accent-bg);
            color: var(--accent-color);
            transition: transform 0.3s;
        }

        .kpi-card:hover .kpi-icon-wrapper {
            transform: scale(1.1) rotate(-5deg);
        }

        .kpi-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .kpi-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.2;
            letter-spacing: -0.025em;
        }

        .kpi-link {
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-top: 0.75rem;
            color: var(--accent-color);
            transition: all 0.2s;
        }

        .kpi-link:hover {
            opacity: 0.8;
            transform: translateX(4px);
        }

        /* Variantes de Color para KPIs */
        .kpi-cyan   { --accent-color: #06b6d4; --accent-bg: rgba(6, 182, 212, 0.1); }
        .kpi-amber  { --accent-color: #f59e0b; --accent-bg: rgba(245, 158, 11, 0.1); }
        .kpi-success{ --accent-color: #10b981; --accent-bg: rgba(16, 185, 129, 0.1); }
        .kpi-danger { --accent-color: #ef4444; --accent-bg: rgba(239, 68, 68, 0.1); }
        .kpi-purple { --accent-color: #8b5cf6; --accent-bg: rgba(139, 92, 246, 0.1); }
        .kpi-lime   { --accent-color: #84cc16; --accent-bg: rgba(132, 204, 22, 0.1); }
        .kpi-info   { --accent-color: #3b82f6; --accent-bg: rgba(59, 130, 246, 0.1); }
        .kpi-teal   { --accent-color: #14b8a6; --accent-bg: rgba(20, 184, 166, 0.1); }
        .kpi-pink   { --accent-color: #ec4899; --accent-bg: rgba(236, 72, 153, 0.1); }
        .kpi-dark   { --accent-color: #1f2937; --accent-bg: rgba(31, 41, 55, 0.1); }
        .kpi-indigo { --accent-color: #4f46e5; --accent-bg: rgba(79, 70, 229, 0.1); }

        /* ===== TARJETAS DE CONTENIDO ===== */
        .content-card {
            background: var(--card-bg);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: box-shadow 0.3s;
        }

        .content-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
        }

        .content-card .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
            display: flex;
            align-items: center;
        }

        .content-card .card-header.header-danger {
            background: #fef2f2;
            color: var(--danger-color);
            border-bottom: 1px solid #fee2e2;
        }

        .content-card .card-body {
            padding: 1.5rem;
        }

        /* ===== TABLAS MODERNAS ===== */
        .table-modern {
            width: 100%;
            margin-bottom: 0;
            vertical-align: middle;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-modern thead th {
            border-bottom: 2px solid var(--border-color);
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.08em;
            padding: 1rem;
            background-color: #f8fafc;
        }

        .table-modern td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            color: var(--text-dark);
            font-size: 0.875rem;
            transition: background-color 0.15s;
        }

        .table-modern tbody tr:hover td {
            background-color: #f8fafc;
        }

        .table-modern tbody tr:last-child td { border-bottom: none; }

        /* ===== BADGES ===== */
        .badge-pill {
            padding: 0.35em 0.9em;
            border-radius: 50rem;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.025em;
        }

        .badge-soft-danger {
            background-color: #fef2f2;
            color: #ef4444;
            border: 1px solid #fee2e2;
        }

        .badge-soft-warning {
            background-color: #fffbeb;
            color: #f59e0b;
            border: 1px solid #fef3c7;
        }

        /* ===== BOTONES ===== */
        .btn-primary-soft {
            background-color: rgba(79, 70, 229, 0.08);
            color: var(--primary-color);
            font-weight: 600;
            border: 1px solid rgba(79, 70, 229, 0.2);
            transition: all 0.2s;
        }

        .btn-primary-soft:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        /* ===== MINI STAT CARDS ===== */
        .mini-stat {
            padding: 1rem;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid var(--border-color);
            transition: all 0.2s;
        }

        .mini-stat:hover {
            background: #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .mini-stat-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .mini-stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* ===== BOTONES DE CONTROL DEL GRÁFICO ===== */
        .chart-control-btn {
            padding: 0.4rem 1rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid var(--border-color);
            background: #fff;
            color: var(--text-muted);
            transition: all 0.2s;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .chart-control-btn:hover {
            background: #f8fafc;
            color: var(--text-dark);
        }

        .chart-control-btn.active {
            background: var(--primary-color);
            color: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
        }

        .chart-control-btn .btn-icon {
            margin-right: 0.4rem;
        }

        /* Toggle switches para datasets */
        .dataset-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.8rem;
            border-radius: 50rem;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
            user-select: none;
        }

        .dataset-toggle.ventas {
            background: rgba(79, 70, 229, 0.1);
            color: #4f46e5;
            border-color: rgba(79, 70, 229, 0.2);
        }

        .dataset-toggle.ventas.inactive {
            background: #f1f5f9;
            color: #94a3b8;
            border-color: #e2e8f0;
        }

        .dataset-toggle.compras {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.2);
        }

        .dataset-toggle.compras.inactive {
            background: #f1f5f9;
            color: #94a3b8;
            border-color: #e2e8f0;
        }

        .dataset-toggle .toggle-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
            opacity: 1;
            transition: opacity 0.2s;
        }

        .dataset-toggle.inactive .toggle-dot {
            opacity: 0.3;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .kpi-value { font-size: 1.4rem; }
            .content-card .card-body { padding: 1rem; }
            .chart-controls-wrapper { flex-wrap: wrap; gap: 0.5rem !important; }
        }
    </style>
@endpush

@section('content')

    @php
        $cards = [
            ['variant'=>'kpi-cyan',   'icon'=>'fa-warehouse',       'title'=>'Almacenes',          'count'=>$metricas['totalAlmacenes'],       'route'=>route('almacenes.index')],
            ['variant'=>'kpi-amber',  'icon'=>'fa-tags',            'title'=>'Categorías',         'count'=>$metricas['totalCategorias'],      'route'=>route('categorias.index')],
            ['variant'=>'kpi-success','icon'=>'fa-people-group',     'title'=>'Clientes',           'count'=>$metricas['totalClientes'],        'route'=>route('clientes.index')],
            ['variant'=>'kpi-danger', 'icon'=>'fa-shopping-bag',    'title'=>'Compras',            'count'=>$metricas['totalCompras'],         'route'=>route('compras.index')],
            ['variant'=>'kpi-purple','icon'=>'fa-cart-shopping',    'title'=>'Ventas',             'count'=>$metricas['totalVentas'],          'route'=>route('ventas.index')],
            ['variant'=>'kpi-lime',   'icon'=>'fa-users',           'title'=>'Grupo Clientes',     'count'=>$metricas['totalGrupoClientes'],   'route'=>route('grupoclientes.index')],
            ['variant'=>'kpi-info',   'icon'=>'fa-bullhorn',        'title'=>'Marcas',             'count'=>$metricas['totalMarcas'],          'route'=>route('marcas.index')],
            ['variant'=>'kpi-teal',   'icon'=>'fa-cubes',           'title'=>'Productos',          'count'=>$metricas['totalProductos'],       'route'=>route('productos.index')],
            ['variant'=>'kpi-pink',   'icon'=>'fa-truck-field',     'title'=>'Proveedores',        'count'=>$metricas['totalProveedores'],     'route'=>route('proveedores.index')],
            ['variant'=>'kpi-dark',   'icon'=>'fa-truck',           'title'=>'Traslados',          'count'=>$metricas['totalTraslados'],       'route'=>route('traslados.index')],
            ['variant'=>'kpi-indigo', 'icon'=>'fa-users-gear',      'title'=>'Usuarios',           'count'=>$metricas['totalUsuarios'],        'route'=>route('users.index')],
        ];
    @endphp

    @if (session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: '¡Excelente!',
                    text: "{{ session('success') }}",
                    icon: 'success',
                    confirmButtonText: 'Continuar',
                    confirmButtonColor: '#4f46e5',
                    background: '#fff',
                    iconColor: '#10b981',
                    timer: 3000,
                    timerProgressBar: true
                });
            });
        </script>
    @endif

    <div class="container-fluid px-4 py-4">

        {{-- HEADER --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 fade-in-up">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">Panel de Control</h1>
                <p class="text-muted mb-0">
                    <i class="far fa-calendar me-1"></i> {{ now()->format('l, d F Y') }}
                </p>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <button type="button" class="btn btn-primary-soft px-4 py-2 rounded-3" data-bs-toggle="modal" data-bs-target="#metricasModal">
                    <i class="fas fa-bolt me-2"></i>Métricas del Día
                </button>
            </div>
        </div>

        {{-- KPI CARDS --}}
        <div class="row g-4 mb-5">
            @foreach ($cards as $index => $card)
                <div class="col-xl-3 col-md-6 fade-in-up" style="animation-delay: {{ $index * 0.05 }}s">
                    <div class="kpi-card {{ $card['variant'] }} p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <div class="kpi-title">{{ $card['title'] }}</div>
                                <div class="kpi-value">{{ number_format($card['count'], 0) }}</div>
                            </div>
                            <div class="kpi-icon-wrapper">
                                <i class="fas {{ $card['icon'] }}"></i>
                            </div>
                        </div>
                        <a href="{{ $card['route'] }}" class="kpi-link">
                            Ver detalles <i class="fas fa-arrow-right ms-2 fs-6"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- SECCIÓN PRINCIPAL: GRÁFICO MIXTO CON CONTROLES --}}
        <div class="row mb-4">
            <div class="col-12 fade-in-up" style="animation-delay: 0.4s">
                <div class="content-card">
                    {{-- HEADER CON CONTROLES --}}
                    <div class="card-header justify-content-between bg-white flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <span class="bg-primary bg-opacity-10 text-primary p-2 rounded me-3">
                                <i class="fas fa-chart-column"></i>
                            </span>
                            <div>
                                <h5 class="mb-0 fw-bold fs-6">Flujo de Caja</h5>
                                <small class="text-muted">
                                    @if($periodoActual == 'semanal')
                                        <i class="fas fa-calendar-week me-1"></i> Últimos 7 días — {{ now()->subDays(6)->format('d M') }} al {{ now()->format('d M Y') }}
                                    @elseif($periodoActual == 'mensual')
                                        <i class="fas fa-calendar-days me-1"></i> {{ ucfirst(now()->locale('es')->translatedFormat('F Y')) }}, por semanas
                                    @else
                                        <i class="fas fa-calendar me-1"></i> Año {{ date('Y') }}, todos los meses
                                    @endif
                                </small>
                            </div>
                        </div>

                        {{-- CONTROLES DEL GRÁFICO --}}
                        <div class="d-flex align-items-center gap-3 flex-wrap chart-controls-wrapper">
                            {{-- Toggles de Datasets --}}
                            <div class="d-flex gap-2">
                                <span class="dataset-toggle ventas" onclick="toggleDataset(0, this)">
                                    <span class="toggle-dot"></span> Ventas
                                </span>
                                <span class="dataset-toggle compras" onclick="toggleDataset(1, this)">
                                    <span class="toggle-dot"></span> Compras
                                </span>
                            </div>

                            <div class="vr d-none d-md-block" style="height: 24px;"></div>

                            {{-- Botones de Período --}}
                            <div class="btn-group" role="group">
                                <a href="{{ route('panel', ['periodo' => 'anual']) }}" 
                                   class="chart-control-btn {{ $periodoActual == 'anual' ? 'active' : '' }}">
                                    <i class="fas fa-calendar btn-icon"></i>Anual
                                </a>
                                <a href="{{ route('panel', ['periodo' => 'mensual']) }}" 
                                   class="chart-control-btn {{ $periodoActual == 'mensual' ? 'active' : '' }}">
                                    <i class="fas fa-calendar-days btn-icon"></i>Mensual
                                </a>
                                <a href="{{ route('panel', ['periodo' => 'semanal']) }}" 
                                   class="chart-control-btn {{ $periodoActual == 'semanal' ? 'active' : '' }}">
                                    <i class="fas fa-calendar-week btn-icon"></i>Semanal
                                </a>
                            </div>

                            {{-- Botón Exportar --}}
                            <button type="button" class="chart-control-btn" onclick="exportChart()" title="Descargar gráfico">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div style="position: relative; height: 380px;">
                            <canvas id="comparisonChart"></canvas>
                        </div>
                    </div>

                    {{-- MINI STATS DEBAJO DEL GRÁFICO --}}
                    <div class="card-footer bg-white border-top-0 pt-0 pb-4 px-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="mini-stat d-flex align-items-center gap-3">
                                    <div class="p-2 rounded" style="color: #4f46e5; background: rgba(79,70,229,0.1);">
                                        <i class="fas fa-arrow-trend-up"></i>
                                    </div>
                                    <div>
                                        <div class="mini-stat-value" style="color: #4f46e5;">Bs/ {{ number_format($totalVentas, 2) }}</div>
                                        <div class="mini-stat-label">Total Ventas</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mini-stat d-flex align-items-center gap-3">
                                    <div class="bg-danger bg-opacity-10 text-danger p-2 rounded">
                                        <i class="fas fa-arrow-trend-down"></i>
                                    </div>
                                    <div>
                                        <div class="mini-stat-value text-danger">Bs/ {{ number_format($totalCompras, 2) }}</div>
                                        <div class="mini-stat-label">Total Compras</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mini-stat d-flex align-items-center gap-3">
                                    <div class="bg-warning bg-opacity-10 text-warning p-2 rounded">
                                        <i class="fas fa-scale-balanced"></i>
                                    </div>
                                    <div>
                                        <div class="mini-stat-value text-warning">Bs/ {{ number_format($totalVentas - $totalCompras, 2) }}</div>
                                        <div class="mini-stat-label">Balance Neto</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mini-stat d-flex align-items-center gap-3">
                                    <div class="bg-success bg-opacity-10 text-success p-2 rounded">
                                        <i class="fas fa-percent"></i>
                                    </div>
                                    <div>
                                        <div class="mini-stat-value text-success">
                                            {{ $totalVentas > 0 ? number_format((($totalVentas - $totalCompras) / $totalVentas) * 100, 1) : 0 }}%
                                        </div>
                                        <div class="mini-stat-label">Margen</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DISTRIBUCIÓN + TOP PRODUCTOS + STOCK --}}
        <div class="row mb-4">
            {{-- DISTRIBUCIÓN FINANCIERA --}}
            <div class="col-lg-4 mb-4 mb-lg-0 fade-in-up" style="animation-delay: 0.5s">
                <div class="content-card h-100">
                    <div class="card-header bg-white">
                        <div class="d-flex align-items-center">
                            <span class="bg-info bg-opacity-10 text-info p-2 rounded me-3">
                                <i class="fas fa-chart-pie"></i>
                            </span>
                            <h5 class="mb-0 fw-bold fs-6">Distribución Financiera</h5>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <div style="position: relative; height: 220px; width: 100%;">
                            <canvas id="myPieChart"></canvas>
                        </div>
                        <div class="mt-4 w-100">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                <span class="text-muted small d-flex align-items-center gap-2">
                                    <span class="d-inline-block rounded-circle" style="width:8px;height:8px;background:#4f46e5"></span>
                                    Total Ventas
                                </span>
                                <span class="fw-bold text-dark">Bs/ {{ number_format($totalVentas, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small d-flex align-items-center gap-2">
                                    <span class="d-inline-block rounded-circle" style="width:8px;height:8px;background:#ef4444"></span>
                                    Total Compras
                                </span>
                                <span class="fw-bold text-dark">Bs/ {{ number_format($totalCompras, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TOP 5 PRODUCTOS --}}
            <div class="col-lg-4 mb-4 mb-lg-0 fade-in-up" style="animation-delay: 0.6s">
                <div class="content-card h-100">
                    <div class="card-header bg-white">
                        <div class="d-flex align-items-center">
                            <span class="p-2 rounded me-3" style="color: #8b5cf6; background: rgba(139, 92, 246, 0.1);">
                                <i class="fas fa-trophy"></i>
                            </span>
                            <h5 class="mb-0 fw-bold fs-6">Top 5 Productos Más Vendidos</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height: 260px;">
                            <canvas id="cantidadTotal"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ALERTA STOCK BAJO --}}
            <div class="col-lg-4 mb-4 fade-in-up" style="animation-delay: 0.7s">
                <div class="content-card h-100" style="border: 1px solid rgba(239, 68, 68, 0.3) !important;">
                    <div class="card-header header-danger justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2 pulse-soft"></i>
                            <h5 class="mb-0 fw-bold fs-6">Alerta de Stock Bajo</h5>
                        </div>
                        <span class="badge bg-danger rounded-pill">{{ count($productosBajoStock) }} Items</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-modern table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Stock</th>
                                        <th class="text-end">Precio Venta</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($productosBajoStock as $producto)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $producto->nombre }}</div>
                                                <small class="text-muted">Costo: Bs/ {{ number_format($producto->precio_compra, 2) }}</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-bold {{ $producto->total_stock <= 5 ? 'text-danger' : 'text-warning' }}">
                                                    {{ number_format($producto->total_stock, 2) }}
                                                </span>
                                            </td>
                                            <td class="text-end">Bs/ {{ number_format($producto->precio_venta, 2) }}</td>
                                            <td class="text-center">
                                                @if ($producto->total_stock <= 5)
                                                    <span class="badge-pill badge-soft-danger">Crítico</span>
                                                @else
                                                    <span class="badge-pill badge-soft-warning">Bajo</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5">
                                                <div class="text-muted opacity-50 mb-2"><i class="fas fa-check-circle fa-3x"></i></div>
                                                <p class="mb-0 fw-medium">¡Todo en orden! Inventario saludable.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL MÉTRICAS DEL DÍA --}}
    <div class="modal fade" id="metricasModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom-0 pb-0 ps-4 pt-4">
                    <h5 class="modal-title fw-bold">Resumen del Día</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 rounded-4 bg-primary bg-opacity-10 text-center h-100">
                                <i class="fas fa-money-bill-wave text-primary fs-3 mb-2"></i>
                                <div class="text-muted small fw-bold text-uppercase">Ventas Hoy</div>
                                <div class="h4 fw-bold text-dark mb-0">Bs/ {{ number_format($metricas['ventasHoy'], 0) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-4 bg-success bg-opacity-10 text-center h-100">
                                <i class="fas fa-shopping-cart text-success fs-3 mb-2"></i>
                                <div class="text-muted small fw-bold text-uppercase">Compras Hoy</div>
                                <div class="h4 fw-bold text-dark mb-0">Bs/ {{ number_format($metricas['comprasHoy'], 0) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 text-center">
                        <small class="text-muted">
                            <i class="far fa-clock me-1"></i> Actualizado: {{ now()->format('H:i') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js" crossorigin="anonymous"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // Configuración global
            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.color = '#64748b';
            Chart.defaults.scale.grid.color = '#f1f5f9';

            const currencyFormatter = (value) => {
                return 'Bs/ ' + value.toLocaleString('es-BO', { minimumFractionDigits: 2 });
            };

            // ============================================================
            // GRÁFICO MIXTO: 2 BARRAS (Ventas + Compras) - SIN LÍNEA DE BALANCE
            // ============================================================
            var ctxComp = document.getElementById("comparisonChart");
            let comparisonChart;

            if (ctxComp) {
                comparisonChart = new Chart(ctxComp, {
                    type: 'bar',
                    data: {
                        labels: @json($labelsMeses),
                        datasets: [
                            {
                                label: 'Ventas',
                                data: @json($mesesVentas),
                                backgroundColor: 'rgba(79, 70, 229, 0.85)',
                                borderColor: '#4f46e5',
                                borderWidth: 0,
                                borderRadius: 6,
                                borderSkipped: false,
                                barPercentage: 0.65,
                                categoryPercentage: 0.8
                            },
                            {
                                label: 'Compras',
                                data: @json($mesesCompras),
                                backgroundColor: 'rgba(239, 68, 68, 0.85)',
                                borderColor: '#ef4444',
                                borderWidth: 0,
                                borderRadius: 6,
                                borderSkipped: false,
                                barPercentage: 0.65,
                                categoryPercentage: 0.8
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                padding: 14,
                                cornerRadius: 8,
                                titleFont: { size: 13, weight: 600 },
                                bodyFont: { size: 13 },
                                displayColors: true,
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + currencyFormatter(context.raw);
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    borderDash: [4, 4],
                                    drawBorder: false
                                },
                                ticks: {
                                    callback: function(value) {
                                        return 'Bs/ ' + value.toLocaleString();
                                    },
                                    font: { size: 11 }
                                },
                                title: {
                                    display: true,
                                    text: 'Monto (Bs/)',
                                    font: { size: 11, weight: 600 },
                                    color: '#94a3b8'
                                }
                            },
                            x: {
                                grid: { display: false, drawBorder: false },
                                ticks: { font: { size: 11 } }
                            }
                        },
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        animation: {
                            duration: 1200,
                            easing: 'easeOutQuart'
                        }
                    }
                });
            }

            // ============================================================
            // FUNCIONES DE CONTROL
            // ============================================================

            // Toggle datasets (mostrar/ocultar Ventas/Compras)
            window.toggleDataset = function(index, element) {
                const meta = comparisonChart.getDatasetMeta(index);
                meta.hidden = meta.hidden === null ? !comparisonChart.data.datasets[index].hidden : null;
                
                if (meta.hidden) {
                    element.classList.add('inactive');
                } else {
                    element.classList.remove('inactive');
                }
                
                comparisonChart.update();
            };

            // Exportar gráfico como imagen
            window.exportChart = function() {
                const link = document.createElement('a');
                link.download = 'flujo-de-caja-' + new Date().toISOString().slice(0,10) + '.png';
                link.href = comparisonChart.toBase64Image();
                link.click();
            };

            // ============================================================
            // GRÁFICO 2: DISTRIBUCIÓN FINANCIERA (Doughnut)
            // ============================================================
            var ctxPie = document.getElementById("myPieChart");
            if (ctxPie) {
                new Chart(ctxPie, {
                    type: "doughnut",
                    data: {
                        labels: ["Ventas Totales", "Compras Totales"],
                        datasets: [{
                            data: [@json($totalVentas), @json($totalCompras)],
                            backgroundColor: ["#4f46e5", "#ef4444"],
                            hoverBackgroundColor: ["#4338ca", "#dc2626"],
                            borderWidth: 0,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + currencyFormatter(context.raw);
                                    }
                                }
                            }
                        },
                        animation: {
                            animateRotate: true,
                            duration: 1500
                        }
                    }
                });
            }

            // ============================================================
            // GRÁFICO 3: TOP 5 PRODUCTOS (Doughnut)
            // ============================================================
            var ctxProd = document.getElementById("cantidadTotal");
            if (ctxProd) {
                new Chart(ctxProd, {
                    type: "doughnut",
                    data: {
                        labels: @json($nombresProductos),
                        datasets: [{
                            data: @json($cantidadesProductos),
                            backgroundColor: [
                                "#4f46e5", "#10b981", "#f59e0b",
                                "#8b5cf6", "#ec4899"
                            ],
                            borderWidth: 2,
                            borderColor: '#fff',
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        cutout: '60%',
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    boxWidth: 10,
                                    usePointStyle: true,
                                    font: { size: 11, weight: 500 },
                                    padding: 15
                                }
                            },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.label + ': ' + context.raw + ' unidades';
                                    }
                                }
                            }
                        },
                        animation: {
                            duration: 1200,
                            easing: 'easeOutQuart'
                        }
                    }
                });
            }

            // Modal accesibilidad
            const metricasModal = document.getElementById('metricasModal');
            if (metricasModal) {
                metricasModal.addEventListener('show.bs.modal', function() {
                    this.removeAttribute('aria-hidden');
                });
                metricasModal.addEventListener('hide.bs.modal', function() {
                    this.setAttribute('aria-hidden', 'true');
                });
            }
        });
    </script>
@endpush