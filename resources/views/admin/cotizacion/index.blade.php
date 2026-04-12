@extends('admin.layouts.app')

@section('title', 'Cotizaciones')

@push('css-datatable')
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/style_Categoria.css') }}">
    <style>
        .custom-dropdown .status-btn {
            border: none;
            background: transparent;
            padding: 4px 12px;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .custom-dropdown .status-btn:hover {
            opacity: 0.8;
            transform: translateY(-1px);
        }
        .custom-dropdown .status-btn::after {
            display: none;
        }
        .custom-dropdown .dropdown-item {
            font-size: 0.8rem;
            padding: 8px 16px;
        }
        .badge-info { background-color: #0dcaf0; color: white; }
        .badge-success { background-color: #198754; color: white; }
        .badge-primary { background-color: #0d6efd; color: white; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-warning { background-color: #ffc107; color: #000; }
        .badge-secondary { background-color: #6c757d; color: white; }

        .text-orange { color: #fd7e14; }

        /* Sistema de selección */
        .custom-checkbox {
            width: 18px;
            height: 18px;
            border: 2px solid #dee2e6;
            border-radius: 4px;
            cursor: pointer;
            position: relative;
            transition: all 0.2s;
        }
        .custom-checkbox:hover {
            border-color: #0d6efd;
        }
        .custom-checkbox.checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .custom-checkbox.checked::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: white;
            font-size: 11px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        tr.selected {
            background-color: rgba(13, 110, 253, 0.05) !important;
        }
        .selection-actions-container {
            display: none;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            gap: 16px;
            flex-wrap: wrap;
        }
        .selected-counter {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            color: #495057;
        }
        .selected-counter-badge {
            background: #0d6efd;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.75rem;
        }
        .action-buttons-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .btn-action-outline {
            border: 1px solid #6c757d;
            background: white;
            color: #6c757d;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-action-outline:hover {
            background: #6c757d;
            color: white;
        }
        .btn-action-secondary {
            border: none;
            background: #0d6efd;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-action-secondary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* Totales en tfoot */
        .table-totals {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .table-totals td {
            border-top: 2px solid #dee2e6;
            padding: 12px;
        }
        .totals-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
        }
        .totals-value {
            display: block;
            font-size: 1.1rem;
            font-weight: 700;
            color: #212529;
        }
        .totals-value.success { color: #198754; }
        .totals-value.warning { color: #fd7e14; }
        .totals-value.info { color: #0dcaf0; }
        .totals-subtext {
            font-size: 0.7rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
    </style>
@endpush

@section('content')
    @include('admin.layouts.partials.alert')

    <div class="container-fluid px-4 py-4">

        <div class="page-header">
            <div>
                <h1 class="page-title">Gestión de Cotizaciones</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none text-muted">Inicio</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Cotizaciones</li>
                    </ol>
                </nav>
            </div>
            @can('crear-cotizacion')
            <a href="{{ route('cotizaciones.create') }}" class="btn-create">
                <i class="fas fa-plus"></i> Nueva Cotización
            </a>
            @endcan
        </div>

        <div class="card-clean">
            <div class="card-header-clean">
                <div class="card-header-title">
                    <i class="fas fa-file-invoice-dollar"></i> Registro de Cotizaciones
                </div>
            </div>

            <!-- SECCION: BUSQUEDA Y FILTROS (Unificado con Ventas) -->
            <div class="search-container">
                <form action="{{ route('cotizaciones.index') }}" method="GET" id="searchForm">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0" style="padding: 0.4rem 0.75rem;">
                                    <i class="fas fa-search text-muted small"></i>
                                </span>
                                <input type="text" name="busqueda" class="form-control form-control-clean border-start-0 ps-0"
                                    placeholder="Buscar por número, cliente..." value="{{ $busqueda ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <label for="per_page" class="me-2 text-muted small">Mostrar:</label>
                                <select name="per_page" id="per_page" class="form-select form-select-sm w-auto" style="border-radius: 6px;">
                                    @foreach([5, 10, 15, 20, 25] as $option)
                                        <option value="{{ $option }}" {{ ($perPage ?? 10) == $option ? 'selected' : '' }}>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5 text-end">
                            <a href="{{ route('cotizaciones.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 6px;">
                                <i class="fas fa-undo me-1"></i> Mostrar Todo
                            </a>
                        </div>
                    </div>

                    <div class="row g-3 align-items-end mt-1">
                        <div class="col-md-3">
                            <label class="text-muted small d-block mb-1">Estado</label>
                            <select name="estado" class="form-select form-select-sm" style="border-radius: 6px;">
                                <option value="">Todos</option>
                                <option value="pendiente" {{ ($estado ?? '') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="venta_realizada" {{ ($estado ?? '') === 'venta_realizada' ? 'selected' : '' }}>Venta Realizada</option>
                                <option value="compra_realizada" {{ ($estado ?? '') === 'compra_realizada' ? 'selected' : '' }}>Compra Realizada</option>
                                <option value="anulado" {{ ($estado ?? '') === 'anulado' ? 'selected' : '' }}>Anulado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small d-block mb-1">Tipo</label>
                            <select name="tipo" class="form-select form-select-sm" style="border-radius: 6px;">
                                <option value="">Todos</option>
                                <option value="cliente" {{ ($tipo ?? '') === 'cliente' ? 'selected' : '' }}>Cliente</option>
                                <option value="proveedor" {{ ($tipo ?? '') === 'proveedor' ? 'selected' : '' }}>Proveedor</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small d-block mb-1">Desde</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom ?? '' }}" style="border-radius: 6px;">
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small d-block mb-1">Hasta</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo ?? '' }}" style="border-radius: 6px;">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px;">
                                <i class="fas fa-filter me-1"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- SECCION: ACCIONES DE SELECCION (NUEVO - Como en Ventas) -->
            <div class="selection-actions-container" id="selectionActions" style="display: none;">
                <div class="selected-counter">
                    <span>Cotizaciones seleccionadas:</span>
                    <span class="selected-counter-badge" id="selectedCount">0</span>
                </div>
                <div class="action-buttons-group">
                    <button type="button" class="btn-action-outline" id="deselectAll">
                        <i class="fas fa-times"></i> Deseleccionar Todos
                    </button>
                    <button type="button" class="btn-action-secondary" id="exportExcel">
                        <i class="fas fa-file-excel"></i> Exportar a Excel
                    </button>
                    <button type="button" class="btn-action-secondary" id="exportPdf" style="background: #e74c3c;">
                        <i class="fas fa-file-pdf"></i> Exportar a PDF
                    </button>
                </div>
            </div>

            <div class="card-body p-0" id="table-container">
                <div class="table-responsive">
                    <table id="datatablesSimple" class="custom-table">
                        <thead>
                            <tr>
                                <th class="checkbox-header">
                                    <div class="custom-checkbox select-all" id="selectAll"></div>
                                </th>
                                <th>
                                    <button class="sort-btn {{ $sort == 'numero_cotizacion' ? 'active ' . $direction : '' }}" data-column="numero_cotizacion">
                                        Nro. <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th>
                                    <button class="sort-btn {{ $sort == 'cliente' ? 'active ' . $direction : '' }}" data-column="cliente">
                                        Cliente / Proveedor <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th>
                                    <button class="sort-btn {{ $sort == 'fecha_hora' ? 'active ' . $direction : '' }}" data-column="fecha_hora">
                                        Fecha <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th class="text-center">
                                    <button class="sort-btn {{ $sort == 'total' ? 'active ' . $direction : '' }}" data-column="total">
                                        Total <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th class="text-center">
                                    <button class="sort-btn {{ $sort == 'estado' ? 'active ' . $direction : '' }}" data-column="estado">
                                        Estado <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cotizaciones as $cotizacion)
                                <tr data-cotizacion-id="{{ $cotizacion->id }}">
                                    <td class="checkbox-cell">
                                        <div class="custom-checkbox cotizacion-checkbox" data-cotizacion-id="{{ $cotizacion->id }}"></div>
                                    </td>
                                    <td>
                                        <div class="venta-info">
                                            <div class="venta-avatar">
                                                <i class="fas fa-file-invoice-dollar small"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $cotizacion->numero_cotizacion }}</div>
                                                <span class="info-subtext">{{ $cotizacion->almacen->nombre ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($cotizacion->cliente)
                                            <div class="fw-semibold text-primary">
                                                <i class="fas fa-user me-1"></i> {{ $cotizacion->cliente->persona->razon_social }}
                                            </div>
                                            <div class="info-subtext">Cliente • {{ $cotizacion->cliente->persona->numero_documento ?? 'N/A' }}</div>
                                        @elseif($cotizacion->proveedor)
                                            <div class="fw-semibold text-orange">
                                                <i class="fas fa-truck me-1"></i> {{ $cotizacion->proveedor->persona->razon_social }}
                                            </div>
                                            <div class="info-subtext">Proveedor • {{ $cotizacion->proveedor->persona->numero_documento ?? 'N/A' }}</div>
                                        @else
                                            <div class="fw-semibold text-muted">
                                                <i class="fas fa-users me-1"></i> Público General
                                            </div>
                                            <div class="info-subtext">Sin documento</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-semibold">
                                                <i class="fas fa-calendar-day me-1 text-muted small"></i>
                                                {{ $cotizacion->fecha_hora->format('d/m/Y') }}
                                            </div>
                                            <div class="info-subtext">
                                                <i class="fas fa-clock me-1 small"></i>
                                                {{ $cotizacion->fecha_hora->format('H:i') }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-success">Bs. {{ number_format($cotizacion->total, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown custom-dropdown">
                                            @php
                                                $estadoClases = [
                                                    'pendiente' => 'badge-info',
                                                    'venta_realizada' => 'badge-success',
                                                    'compra_realizada' => 'badge-primary',
                                                    'anulado' => 'badge-danger'
                                                ];
                                                $estadoTextos = [
                                                    'pendiente' => 'Pendiente',
                                                    'venta_realizada' => 'Venta Realizada',
                                                    'compra_realizada' => 'Compra Realizada',
                                                    'anulado' => 'Anulado'
                                                ];
                                                $clase = $estadoClases[$cotizacion->estado] ?? 'badge-secondary';
                                                $texto = $estadoTextos[$cotizacion->estado] ?? ucfirst($cotizacion->estado);
                                            @endphp
                                            <button class="status-btn badge-pill {{ $clase }} dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                {{ $texto }}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                <li><a class="dropdown-item change-status" href="#" data-cotizacion-id="{{ $cotizacion->id }}" data-status="pendiente">Pendiente</a></li>
                                                <li><a class="dropdown-item change-status" href="#" data-cotizacion-id="{{ $cotizacion->id }}" data-status="venta_realizada">Venta Realizada</a></li>
                                                <li><a class="dropdown-item change-status" href="#" data-cotizacion-id="{{ $cotizacion->id }}" data-status="compra_realizada">Compra Realizada</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item change-status text-danger" href="#" data-cotizacion-id="{{ $cotizacion->id }}" data-status="anulado">Anulado</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-action-group">
                                            @can('mostrar-cotizacion')
                                            <button type="button" class="btn-icon-soft view-cotizacion" data-id="{{ $cotizacion->id }}" title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @endcan

                                            @if($cotizacion->estado == 'pendiente')
                                                @can('editar-cotizacion')
                                                <a href="{{ route('cotizaciones.edit', $cotizacion->id) }}" class="btn-icon-soft" title="Editar">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                                @endcan
                                            @endif

                                            @can('eliminar-cotizacion')
                                            <button type="button" class="btn-icon-soft delete-cotizacion" data-id="{{ $cotizacion->id }}" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 opacity-20"></i>
                                            <p>No se encontraron cotizaciones.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        <!-- TFOOT con resumen (NUEVO - Como en Ventas) -->
                        @if($cotizaciones->count() > 0)
                        <tfoot>
                            <tr class="table-totals">
                                <td colspan="4" class="text-end">
                                    <span class="totals-label">RESUMEN GENERAL</span>
                                </td>
                                <td class="text-center">
                                    <span class="totals-value">{{ $cotizaciones->total() }}</span>
                                    <span class="totals-subtext">Total Cotizaciones</span>
                                </td>
                                <td class="text-center">
                                    <span class="totals-value info">{{ $cotizaciones->where('estado', 'pendiente')->count() }}</span>
                                    <span class="totals-subtext">Pendientes</span>
                                </td>
                                <td class="text-center">
                                    <span class="totals-value success">Bs. {{ number_format($cotizaciones->whereIn('estado', ['venta_realizada', 'compra_realizada'])->sum('total'), 2) }}</span>
                                    <span class="totals-subtext">Monto Convertido</span>
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

                <!-- Paginacion mejorada (Como en Ventas) -->
                <div class="p-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted extra-small">
                        Mostrando {{ $cotizaciones->firstItem() ?? 0 }} - {{ $cotizaciones->lastItem() ?? 0 }} de {{ $cotizaciones->total() }} registros
                    </div>
                    <div>
                        {{ $cotizaciones->appends(request()->all())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver cotización (Mejorado) -->
    <div class="modal fade" id="viewCotizacionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content modal-content-clean">
                <div class="modal-header modal-header-clean">
                    <h5 class="modal-title fs-6 fw-bold">
                        <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Detalles de la Cotización
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="modalCotizacionContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted small">Cargando información...</p>
                    </div>
                </div>
                <div class="modal-footer border-0 d-flex justify-content-between" id="modalFooter">
                    <div id="conversionButtons"></div>
                    <div class="d-flex gap-2">
                        <button type="button" id="printButton" class="btn btn-outline-dark btn-sm px-4" title="Imprimir cotización">
                            <i class="fas fa-print me-1"></i> Imprimir
                        </button>
                        <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Iframe oculto para impresión -->
    <iframe id="printFrame" style="display:none; position:absolute; width:0; height:0; border:0;"></iframe>

    <!-- Modal de Exportación Genérico (NUEVO - Como en Ventas) -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-clean">
                <div class="modal-header modal-header-clean">
                    <h5 class="modal-title fs-6" id="exportModalTitle">
                        <i class="fas fa-file-export me-2"></i> Exportar Cotizaciones
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="exportFormat" value="excel">
                    <div class="alert alert-success border-0 bg-success bg-opacity-10 d-flex align-items-center mb-4" id="exportAlert" style="border-radius: 12px;">
                        <i class="fas fa-info-circle me-3 fs-5 text-success" id="exportAlertIcon"></i>
                        <div class="small fw-medium text-success">
                            Se exportarán <strong id="exportCountDisplay">0</strong> cotizaciones seleccionadas.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="info-subtext mb-2 text-uppercase letter-spacing-05 small fw-bold">Opciones de Datos</label>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="modalIncludeDetails" checked>
                            <label class="form-check-label d-block" for="modalIncludeDetails">
                                <span class="d-block fw-semibold small">Incluir detalles de productos</span>
                                <span class="extra-small text-muted">Productos, cantidades y precios unitarios</span>
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="modalIncludeClient" checked>
                            <label class="form-check-label d-block" for="modalIncludeClient">
                                <span class="d-block fw-semibold small">Incluir información del cliente/proveedor</span>
                                <span class="extra-small text-muted">Datos completos de contacto</span>
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="modalIncludeAllDetails" checked>
                            <label class="form-check-label d-block" for="modalIncludeAllDetails">
                                <span class="d-block fw-semibold small">Incluir todos los detalles</span>
                                <span class="extra-small text-muted">Estados, totales desglosados y almacén</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-outline-danger btn-sm px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-primary btn-sm px-4" id="confirmExportBtn">
                        <i class="fas fa-download me-1"></i> Generar Archivo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <form id="deleteForm" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('js')
<script>
    const cotizacionBaseUrl = '{{ url('admin/cotizaciones') }}';
    const tableContainer = document.getElementById('table-container');
    let selectedCotizaciones = new Set();
    let debounceTimer;

    // Sistema de selección (Idéntico al de Ventas)
    function initializeSelectionSystem() {
        const selectionActions = document.getElementById('selectionActions');
        const selectAllCheckbox = document.getElementById('selectAll');
        const cotizacionCheckboxes = document.querySelectorAll('.cotizacion-checkbox');
        const selectedCountElement = document.getElementById('selectedCount');
        const deselectAllBtn = document.getElementById('deselectAll');
        const exportExcelBtn = document.getElementById('exportExcel');
        const exportPdfBtn = document.getElementById('exportPdf');

        cotizacionCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('click', function() {
                const cotizacionId = this.dataset.cotizacionId;
                const row = this.closest('tr');

                if (this.classList.contains('checked')) {
                    this.classList.remove('checked');
                    row.classList.remove('selected');
                    selectedCotizaciones.delete(cotizacionId);
                } else {
                    this.classList.add('checked');
                    row.classList.add('selected');
                    selectedCotizaciones.add(cotizacionId);
                }

                updateSelectionUI();
            });
        });

        selectAllCheckbox.addEventListener('click', function() {
            const isSelectAll = !this.classList.contains('checked');
            const allCheckboxes = document.querySelectorAll('.cotizacion-checkbox');

            allCheckboxes.forEach(checkbox => {
                const cotizacionId = checkbox.dataset.cotizacionId;
                const row = checkbox.closest('tr');

                if (isSelectAll) {
                    checkbox.classList.add('checked');
                    row.classList.add('selected');
                    selectedCotizaciones.add(cotizacionId);
                } else {
                    checkbox.classList.remove('checked');
                    row.classList.remove('selected');
                    selectedCotizaciones.delete(cotizacionId);
                }
            });

            this.classList.toggle('checked');
            updateSelectionUI();
        });

        deselectAllBtn.addEventListener('click', function() {
            selectedCotizaciones.clear();
            document.querySelectorAll('.cotizacion-checkbox').forEach(checkbox => {
                checkbox.classList.remove('checked');
                checkbox.closest('tr').classList.remove('selected');
            });
            selectAllCheckbox.classList.remove('checked');
            updateSelectionUI();
        });

        if (exportExcelBtn) {
            exportExcelBtn.addEventListener('click', () => openExportModal('excel'));
        }
        if (exportPdfBtn) {
            exportPdfBtn.addEventListener('click', () => openExportModal('pdf'));
        }

        function openExportModal(format) {
            const modal = new bootstrap.Modal(document.getElementById('exportModal'));
            const title = document.getElementById('exportModalTitle');
            const formatInput = document.getElementById('exportFormat');
            const confirmBtn = document.getElementById('confirmExportBtn');
            const alertBox = document.getElementById('exportAlert');
            const alertIcon = document.getElementById('exportAlertIcon');

            formatInput.value = format;
            document.getElementById('exportCountDisplay').textContent = selectedCotizaciones.size;

            if (format === 'excel') {
                title.innerHTML = '<i class="fas fa-file-excel me-2 text-success"></i> Exportar a Excel';
                confirmBtn.className = 'btn btn-outline-success btn-sm px-4';
                alertBox.className = 'alert alert-success border-0 bg-success bg-opacity-10 d-flex align-items-center mb-4';
                alertIcon.className = 'fas fa-info-circle me-3 fs-5 text-success';
            } else {
                title.innerHTML = '<i class="fas fa-file-pdf me-2 text-danger"></i> Exportar a PDF';
                confirmBtn.className = 'btn btn-outline-danger btn-sm px-4';
                alertBox.className = 'alert alert-danger border-0 bg-danger bg-opacity-10 d-flex align-items-center mb-4';
                alertIcon.className = 'fas fa-info-circle me-3 fs-5 text-danger';
            }

            modal.show();
        }

        function updateSelectionUI() {
            const count = selectedCotizaciones.size;
            selectedCountElement.textContent = count;

            if (count > 0) {
                selectionActions.style.display = 'flex';
                const totalCheckboxes = document.querySelectorAll('.cotizacion-checkbox').length;
                selectAllCheckbox.classList.toggle('checked', count === totalCheckboxes);
            } else {
                selectionActions.style.display = 'none';
                selectAllCheckbox.classList.remove('checked');
            }
        }
    }

    // Exportación masiva (Igual que en Ventas)
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'confirmExportBtn') {
            const format = document.getElementById('exportFormat').value;
            const ids = Array.from(selectedCotizaciones);
            const includeDetails = document.getElementById('modalIncludeDetails').checked;
            const includeClient = document.getElementById('modalIncludeClient').checked;
            const includeAllDetails = document.getElementById('modalIncludeAllDetails').checked;

            bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();

            Swal.fire({
                title: 'Generando archivo...',
                text: 'Por favor espere un momento.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = format === 'excel'
                ? '{{ route('export.excel', ['module' => 'cotizaciones']) }}'
                : '{{ route('export.pdf', ['module' => 'cotizaciones']) }}';

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });

            ['includeDetails', 'includeClient', 'includeAllDetails'].forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = document.getElementById('modal' + key.charAt(0).toUpperCase() + key.slice(1)).checked;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();

            setTimeout(() => {
                document.getElementById('deselectAll').click();
                Swal.fire({
                    icon: 'success',
                    title: 'Exportación iniciada',
                    text: 'El archivo se descargará automáticamente.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }, 1000);
        }
    });

    // Eventos de búsqueda y ordenamiento (Igual que en Ventas)
    function initializeEvents() {
        const searchInput = document.querySelector('input[name="busqueda"]');
        const perPageSelect = document.getElementById('per_page');
        const sortButtons = document.querySelectorAll('.sort-btn');

        if (searchInput) {
            searchInput.focus();
            const len = searchInput.value.length;
            searchInput.setSelectionRange(len, len);

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => fetchCotizaciones(), 300);
            });
        }

        if (perPageSelect) {
            perPageSelect.addEventListener('change', () => fetchCotizaciones());
        }

        sortButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const column = this.dataset.column;
                const currentUrl = new URL(window.location.href);
                let direction = 'asc';

                if (currentUrl.searchParams.get('sort') === column) {
                    direction = currentUrl.searchParams.get('direction') === 'asc' ? 'desc' : 'asc';
                }

                const params = new URLSearchParams(window.location.search);
                params.set('sort', column);
                params.set('direction', direction);
                fetchCotizaciones(`{{ route('cotizaciones.index') }}?${params.toString()}`);
            });
        });
    }

    function fetchCotizaciones(url = null) {
        const searchInput = document.querySelector('input[name="busqueda"]');
        const perPageSelect = document.getElementById('per_page');

        let fetchUrl = url;
        if (!fetchUrl) {
            const params = new URLSearchParams(window.location.search);
            if (searchInput) params.set('busqueda', searchInput.value);
            if (perPageSelect) params.set('per_page', perPageSelect.value);
            fetchUrl = `{{ route('cotizaciones.index') }}?${params.toString()}`;
        }

        tableContainer.style.opacity = '0.6';

        fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const newDoc = parser.parseFromString(html, 'text/html');
                const newContent = newDoc.getElementById('table-container').innerHTML;

                tableContainer.innerHTML = newContent;
                tableContainer.style.opacity = '1';

                selectedCotizaciones.clear();
                document.getElementById('selectionActions').style.display = 'none';

                window.history.pushState({}, '', fetchUrl);
                initializeEvents();
                initializeSelectionSystem();
            })
            .catch(error => {
                console.error('Error:', error);
                tableContainer.style.opacity = '1';
            });
    }

    // Eventos globales
    document.addEventListener('DOMContentLoaded', function() {
        initializeEvents();
        initializeSelectionSystem();
    });

    // Clic en fila para seleccionar (Igual que en Ventas)
    document.addEventListener('click', function(e) {
        // Cambio de estado inline (NUEVO)
        if (e.target.classList.contains('change-status')) {
            e.preventDefault();
            const cotizacionId = e.target.dataset.cotizacionId;
            const status = e.target.dataset.status;
            updateCotizacionStatus(cotizacionId, status);
            return;
        }

        if (e.target.closest('tr[data-cotizacion-id]') &&
            !e.target.closest('.btn-action-group') &&
            !e.target.closest('.custom-checkbox') &&
            !e.target.closest('.dropdown')) {
            const row = e.target.closest('tr[data-cotizacion-id]');
            const checkbox = row.querySelector('.cotizacion-checkbox');
            if (checkbox) checkbox.click();
        }
    });

    // Cambio de estado vía AJAX (NUEVO - Como en Ventas)
    async function updateCotizacionStatus(cotizacionId, status) {
        if (status === 'anulado') {
            const result = await Swal.fire({
                title: '¿Anular cotización?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Sí, anular',
                cancelButtonText: 'Cancelar'
            });
            if (!result.isConfirmed) return;
        }

        Swal.fire({
            title: 'Actualizando estado...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch(`${cotizacionBaseUrl}/${cotizacionId}/estado`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ estado: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    fetchCotizaciones();
                    const modalElement = document.getElementById('viewCotizacionModal');
                    if (modalElement && modalElement.classList.contains('show')) {
                        showCotizacion(cotizacionId);
                    }
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'No se pudo actualizar el estado', 'error');
        });
    }

    // Modal de detalles (Mejorado)
    const viewModalElement = document.getElementById('viewCotizacionModal');
    const viewModal = new bootstrap.Modal(viewModalElement);
    const modalContent = document.getElementById('modalCotizacionContent');
    const conversionButtons = document.getElementById('conversionButtons');
    const printButton = document.getElementById('printButton');
    const printFrame = document.getElementById('printFrame');
    let currentPdfUrl = null;

    // Botón Imprimir: carga el PDF en un iframe oculto y abre el diálogo de impresión del navegador
    printButton.addEventListener('click', function() {
        if (!currentPdfUrl) return;
        printFrame.src = currentPdfUrl;
        printFrame.onload = function() {
            try {
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
            } catch(e) {
                // Si no se puede imprimir por CORS, abrir en nueva pestaña
                window.open(currentPdfUrl, '_blank');
            }
        };
    });

    document.body.addEventListener('click', function(e) {
        // Ver detalles
        if (e.target.classList.contains('view-cotizacion') || e.target.closest('.view-cotizacion')) {
            const button = e.target.classList.contains('view-cotizacion') ? e.target : e.target.closest('.view-cotizacion');
            const id = button.dataset.id;
            showCotizacion(id);
        }

        // Convertir a venta/compra
        const convertBtn = e.target.closest('.btn-convertir');
        if (convertBtn) {
            const id = convertBtn.dataset.id;
            const action = convertBtn.dataset.action;
            confirmConversion(id, action);
        }

        // Eliminar
        const delBtn = e.target.closest('.delete-cotizacion');
        if (delBtn) {
            const id = delBtn.dataset.id;
            confirmDeletion(id);
        }
    });

    function showCotizacion(id) {
        modalContent.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted small">Cargando información...</p>
            </div>
        `;
        conversionButtons.innerHTML = '';
        currentPdfUrl = null;
        viewModal.show();

        fetch(`${cotizacionBaseUrl}/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async (res) => {
            if (!res.ok) {
                if (res.status === 401) throw new Error('Sesión expirada. Vuelve a iniciar sesión.');
                if (res.status === 403) throw new Error('No tienes permiso para ver esta cotización.');
                if (res.status === 404) throw new Error('Cotización no encontrada.');
                throw new Error(`Error del servidor (HTTP ${res.status})`);
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                modalContent.innerHTML = data.html;
                currentPdfUrl = data.pdf_url || null;

                // Botones de conversión solo si está pendiente
                if (data.cotizacion && data.cotizacion.estado === 'pendiente') {
                    conversionButtons.innerHTML = `
                        <a href="${cotizacionBaseUrl}/${id}/edit" class="btn btn-warning btn-sm me-2 text-white">
                            <i class="fas fa-pen me-1"></i> Editar
                        </a>
                        <button class="btn btn-success btn-sm me-2 btn-convertir" data-id="${id}" data-action="venta">
                            <i class="fas fa-shopping-cart me-1"></i> Convertir en Venta
                        </button>
                        <button class="btn btn-primary btn-sm btn-convertir" data-id="${id}" data-action="compra">
                            <i class="fas fa-shopping-bag me-1"></i> Convertir en Compra
                        </button>
                    `;
                } else {
                    conversionButtons.innerHTML = `
                        <span class="badge bg-secondary">Cotización ${data.cotizacion.estado.replace('_', ' ')}</span>
                    `;
                }
            } else {
                throw new Error(data.message || 'Error al cargar');
            }
        })
        .catch(err => {
            console.error(err);
            modalContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error: ${err.message}
                    <button class="btn btn-sm btn-warning mt-2" onclick="showCotizacion(${id})">
                        <i class="fas fa-redo me-1"></i> Reintentar
                    </button>
                </div>
            `;
        });
    }

    function confirmConversion(id, action) {
        const title = action === 'venta' ? '¿Convertir en Venta?' : '¿Convertir en Compra?';
        const text = action === 'venta'
            ? "Se creará un registro de venta y se descontará stock."
            : "Se creará un registro de compra y se aumentará stock.";

        Swal.fire({
            title: title,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: action === 'venta' ? '#198754' : '#0d6efd',
            confirmButtonText: 'Sí, convertir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch(`${cotizacionBaseUrl}/${id}/convertir-${action}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('¡Éxito!', data.message, 'success').then(() => {
                            fetchCotizaciones();
                            viewModal.hide();
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(err => Swal.fire('Error', 'Error en la petición: ' + err.message, 'error'));
            }
        });
    }

    function confirmDeletion(id) {
        Swal.fire({
            title: '¿Eliminar cotización?',
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteForm');
                form.action = `${cotizacionBaseUrl}/${id}`;
                form.submit();
            }
        });
    }
</script>
@endpush
