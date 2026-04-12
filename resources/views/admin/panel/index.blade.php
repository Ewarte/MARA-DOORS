@extends('admin.layouts.app')

@section('title', 'Panel de Administración')

@push('css')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary-color: #4f46e5;       /* Indigo */
            --secondary-color: #64748b;     /* Slate */
            --success-color: #10b981;       /* Emerald */
            --warning-color: #f59e0b;       /* Amber */
            --danger-color: #ef4444;        /* Red */
            --info-color: #3b82f6;          /* Blue */
            --bg-body: #f3f4f6;             /* Gris muy suave */
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-dark);
        }

        /* UTILIDADES Y ANIMACIONES */
        .fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }

        /* TARJETAS KPI (Key Performance Indicators) */
        .kpi-card {
            background: #fff;
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
            border-left: 4px solid transparent; /* Acento de color */
        }

        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .kpi-icon-wrapper {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 15px;
        }

        .kpi-title {
            font-size: 0.875rem;
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
        }

        .kpi-link {
            font-size: 0.8rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-top: 1rem;
            transition: opacity 0.2s;
        }

        .kpi-link:hover { opacity: 0.8; }

        /* Variantes de Color para KPIs */
        .kpi-primary { border-color: var(--primary-color); }
        .kpi-primary .kpi-icon-wrapper { background-color: rgba(79, 70, 229, 0.1); color: var(--primary-color); }
        .kpi-primary .kpi-link { color: var(--primary-color); }

        .kpi-success { border-color: var(--success-color); }
        .kpi-success .kpi-icon-wrapper { background-color: rgba(16, 185, 129, 0.1); color: var(--success-color); }
        .kpi-success .kpi-link { color: var(--success-color); }

        .kpi-warning { border-color: var(--warning-color); }
        .kpi-warning .kpi-icon-wrapper { background-color: rgba(245, 158, 11, 0.1); color: var(--warning-color); }
        .kpi-warning .kpi-link { color: var(--warning-color); }

        .kpi-danger { border-color: var(--danger-color); }
        .kpi-danger .kpi-icon-wrapper { background-color: rgba(239, 68, 68, 0.1); color: var(--danger-color); }
        .kpi-danger .kpi-link { color: var(--danger-color); }

        .kpi-info { border-color: var(--info-color); }
        .kpi-info .kpi-icon-wrapper { background-color: rgba(59, 130, 246, 0.1); color: var(--info-color); }
        .kpi-info .kpi-link { color: var(--info-color); }

        .kpi-purple { border-color: #8b5cf6; }
        .kpi-purple .kpi-icon-wrapper { background-color: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
        .kpi-purple .kpi-link { color: #8b5cf6; }

        .kpi-orange { border-color: #f97316; }
        .kpi-orange .kpi-icon-wrapper { background-color: rgba(249, 115, 22, 0.1); color: #f97316; }
        .kpi-orange .kpi-link { color: #f97316; }

        .kpi-teal { border-color: #14b8a6; }
        .kpi-teal .kpi-icon-wrapper { background-color: rgba(20, 184, 166, 0.1); color: #14b8a6; }
        .kpi-teal .kpi-link { color: #14b8a6; }

        .kpi-dark { border-color: #1f2937; }
        .kpi-dark .kpi-icon-wrapper { background-color: rgba(31, 41, 55, 0.1);color: #1f2937;}
        .kpi-dark .kpi-link { color: #1f2937;}

        .kpi-cyan {border-color: #06b6d4;}
        .kpi-cyan .kpi-icon-wrapper {background-color: rgba(6, 182, 212, 0.1);color: #06b6d4;}
        .kpi-cyan .kpi-link {color: #06b6d4;}

        .kpi-amber {border-color: #f59e0b;}
        .kpi-amber .kpi-icon-wrapper {background-color: rgba(245, 158, 11, 0.1);color: #f59e0b;}
        .kpi-amber .kpi-link {color: #f59e0b;}

        .kpi-pink {border-color: #ec4899;}
        .kpi-pink .kpi-icon-wrapper {background-color: rgba(236, 72, 153, 0.1);color: #ec4899;}
        .kpi-pink .kpi-link {color: #ec4899;}

        .kpi-indigo {border-color: #4f46e5;}
        .kpi-indigo .kpi-icon-wrapper {background-color: rgba(79, 70, 229, 0.1);color: #4f46e5;}
        .kpi-indigo .kpi-link {color: #4f46e5;}

        .kpi-lime {border-color: #84cc16;}        
        .kpi-lime .kpi-icon-wrapper { background-color: rgba(132, 204, 22, 0.1);color: #84cc16;}
        .kpi-lime .kpi-link {color: #84cc16;}



        /* TARJETAS DE CONTENIDO (Gráficos y Tablas) */
        .content-card {
            background: #fff;
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .content-card .card-header {
            background: #fff;
            border-bottom: 1px solid #f1f5f9;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
            display: flex;
            align-items: center;
        }

        /* Header especial para stock crítico */
        .content-card .card-header.header-danger {
            background: #fef2f2;
            color: var(--danger-color);
            border-bottom: 1px solid #fee2e2;
        }

        .content-card .card-body {
            padding: 1.5rem;
        }

        /* TABLAS MODERNAS */
        .table-modern {
            width: 100%;
            margin-bottom: 0;
            vertical-align: middle;
        }

        .table-modern thead th {
            border-bottom: 2px solid #e2e8f0;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1rem;
            background-color: #f8fafc;
        }

        .table-modern td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            color: var(--text-dark);
            font-size: 0.9rem;
        }

        .table-modern tr:last-child td { border-bottom: none; }
        .table-modern tr:hover td { background-color: #f8fafc; }

        /* BADGES (Píldoras) */
        .badge-pill {
            padding: 0.35em 0.8em;
            border-radius: 50rem;
            font-size: 0.75em;
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

        /* Botones y Elementos UI */
        .btn-primary-soft {
            background-color: rgba(79, 70, 229, 0.1);
            color: var(--primary-color);
            font-weight: 600;
            border: none;
            transition: all 0.2s;
        }

        .btn-primary-soft:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-1px);
        }

        /* Responsive overrides */
        @media (max-width: 768px) {
            .kpi-value { font-size: 1.5rem; }
            .content-card .card-body { padding: 1rem; }
        }
    </style>
@endpush

@section('content')

    {{-- Lógica de Datos (Movida al principio para limpieza) --}}
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
                    iconColor: '#10b981'
                });
            });
        </script>
    @endif

    <div class="container-fluid px-4 py-4">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 fade-in-up">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">Panel de Control</h1>
                <p class="text-muted mb-0">
                    <i class="far fa-calendar me-1"></i> {{ now()->format('l, d F Y') }}
                </p>
            </div>
            <div class="mt-3 mt-md-0">
                <button type="button" class="btn btn-primary-soft px-4 py-2 rounded-3" data-bs-toggle="modal" data-bs-target="#metricasModal">
                    <i class="fas fa-bolt me-2"></i>Métricas del Día
                </button>
            </div>
        </div>

        <div class="row g-4 mb-5">
            @foreach ($cards as $index => $card)
                <div class="col-xl-3 col-md-6 fade-in-up" style="animation-delay: {{ $index * 0.05 }}s">
                    <div class="kpi-card {{ $card['variant'] }} p-4 d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between mb-2">
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

        <div class="row mb-4">
            <div class="col-lg-8 mb-4 mb-lg-0 fade-in-up" style="animation-delay: 0.4s">
                <div class="content-card h-100">
                    <div class="card-header justify-content-between bg-white">
                        <div class="d-flex align-items-center">
                            <span class="bg-primary bg-opacity-10 text-primary p-2 rounded me-3">
                                <i class="fas fa-chart-area"></i>
                            </span>
                            <h5 class="mb-0 fw-bold fs-6">Flujo de Caja (Ventas vs Compras)</h5>
                        </div>
                        <span class="badge bg-light text-dark border">Año {{ date('Y') }}</span>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height: 300px;">
                            <canvas id="comparisonChart"></canvas>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0 pt-0 pb-4">
                        <div class="alert alert-light border d-flex align-items-center mb-0" role="alert">
                            <i class="fas fa-coins text-warning me-3 fs-4"></i>
                            <div>
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem">Balance Neto</small>
                                <span class="fw-bold text-dark">Bs/ {{ number_format($totalVentas - $totalCompras, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 fade-in-up" style="animation-delay: 0.5s">
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
                            <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                                <span class="text-muted small">Total Ventas</span>
                                <span class="fw-bold text-success">Bs/ {{ number_format($totalVentas, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small">Total Compras</span>
                                <span class="fw-bold text-danger">Bs/ {{ number_format($totalCompras, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4 fade-in-up" style="animation-delay: 0.6s">
                <div class="content-card h-100">
                    <div class="card-header bg-white">
                        <div class="d-flex align-items-center">
                            <span class="bg-purple bg-opacity-10 text-purple p-2 rounded me-3" style="color: #8b5cf6; background: rgba(139, 92, 246, 0.1);">
                                <i class="fas fa-trophy"></i>
                            </span>
                            <h5 class="mb-0 fw-bold fs-6">Top 5 Productos Más Vendidos</h5>
                        </div>
                    </div>
                    <div class="card-body">
                         <div style="position: relative; height: 250px;">
                            <canvas id="cantidadTotal"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4 fade-in-up" style="animation-delay: 0.7s">
                <div class="content-card h-100 border-danger border-opacity-25" style="border: 1px solid rgba(239, 68, 68, 0.2);">
                    <div class="card-header header-danger justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <h5 class="mb-0 fw-bold fs-6">Alerta de Stock Bajo</h5>
                        </div>
                        <span class="badge bg-danger rounded-pill">{{ count($productosBajoStock) }} Items</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-modern table-hover">
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
                                                <p class="mb-0 fw-medium">Todo en orden! Inventario saludable.</p>
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
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // 1. CONFIGURACIÓN GLOBAL DE CHART.JS (Estilo Moderno)
            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.color = '#64748b';

            // 2. UTILIDAD: Formateador de Moneda
            const currencyFormatter = (value) => {
                return 'Bs/ ' + value.toLocaleString('es-BO', { minimumFractionDigits: 2 });
            };

            // ---------------------------------------------------------
            // GRÁFICO 1: FLUJO DE CAJA (Ventas vs Compras) - TENDENCIA
            // ---------------------------------------------------------
            var ctxComp = document.getElementById("comparisonChart");
            if (ctxComp) {
                new Chart(ctxComp, {
                    type: 'line', // Usamos línea suave (Area Chart) para ver tendencias
                    data: {
                        labels: @json($labelsMeses), // Viene directo del Controller optimizado
                        datasets: [
                            {
                                label: 'Ventas (Ingresos)',
                                data: @json($mesesVentas),
                                backgroundColor: 'rgba(79, 70, 229, 0.1)', // Indigo muy suave
                                borderColor: '#4f46e5', // Indigo fuerte
                                borderWidth: 2,
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: '#4f46e5',
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                fill: true,
                                tension: 0.4 // Curva suave
                            },
                            {
                                label: 'Compras (Egresos)',
                                data: @json($mesesCompras),
                                backgroundColor: 'rgba(239, 68, 68, 0.05)', // Rojo muy suave
                                borderColor: '#ef4444', // Rojo fuerte
                                borderWidth: 2,
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: '#ef4444',
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                fill: true,
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                align: 'end',
                                labels: { usePointStyle: true, boxWidth: 6 }
                            },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                padding: 12,
                                titleFont: { size: 13 },
                                bodyFont: { size: 13 },
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
                                grid: { borderDash: [2, 4], color: '#f1f5f9', drawBorder: false },
                                ticks: { callback: function(value) { return 'Bs/ ' + value.toLocaleString(); } }
                            },
                            x: {
                                grid: { display: false, drawBorder: false }
                            }
                        },
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                    }
                });
            }

            // ---------------------------------------------------------
            // GRÁFICO 2: DISTRIBUCIÓN FINANCIERA (Doughnut Chart)
            // ---------------------------------------------------------
            var ctxPie = document.getElementById("myPieChart");
            if (ctxPie) {
                new Chart(ctxPie, {
                    type: "doughnut", // Doughnut se ve más moderno que Pie
                    data: {
                        labels: ["Ventas Totales", "Compras Totales"],
                        datasets: [{
                            data: [@json($totalVentas), @json($totalCompras)],
                            backgroundColor: ["#4f46e5", "#ef4444"], // Indigo vs Rojo
                            hoverBackgroundColor: ["#4338ca", "#dc2626"],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        cutout: '75%', // Hace el anillo más fino y elegante
                        plugins: {
                            legend: { display: false }, // Ocultamos leyenda porque ya tenemos texto HTML abajo
                            tooltip: {
                                backgroundColor: '#1e293b',
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + currencyFormatter(context.raw);
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // ---------------------------------------------------------
            // GRÁFICO 3: TOP 5 PRODUCTOS (Polar Area o Doughnut)
            // ---------------------------------------------------------
            var ctxProd = document.getElementById("cantidadTotal");
            if (ctxProd) {
                new Chart(ctxProd, {
                    type: "doughnut",
                    data: {
                        labels: @json($nombresProductos),
                        datasets: [{
                            data: @json($cantidadesProductos),
                            backgroundColor: [
                                "#4f46e5", // Indigo
                                "#10b981", // Emerald
                                "#f59e0b", // Amber
                                "#8b5cf6", // Violet
                                "#ec4899"  // Pink
                            ],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: { boxWidth: 10, usePointStyle: true, font: { size: 11 } }
                            },
                            tooltip: {
                                callbacks: {
                                     label: function(context) {
                                        return ' Vendidos: ' + context.raw + ' unidades';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // ---------------------------------------------------------
            // LOGICA DEL MODAL (Accesibilidad)
            // ---------------------------------------------------------
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

