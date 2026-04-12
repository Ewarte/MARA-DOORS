@extends('admin.layouts.app')

@section('title', 'Compras')

@push('css-datatable')
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css " rel="stylesheet" type="text/css">
@endpush

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11 "></script>
    <link rel="stylesheet" href="{{ asset('css/style_Categoria.css') }}">
@endpush

@section('content')
    @include('admin.layouts.partials.alert')
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
        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }
    </style>

    <div class="container-fluid px-4 py-4">

        <div class="page-header">
            <div>
                <h1 class="page-title">Gestión de Compras</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none text-muted">Inicio</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Compras</li>
                    </ol>
                </nav>
            </div>
            @can('crear-compra')
            <a href="{{ route('compras.create') }}" class="btn-create">
                <i class="fas fa-plus"></i> Nueva Compra
            </a>
            @endcan
        </div>

        <div class="card-clean">
            <div class="card-header-clean">
                <div class="card-header-title">
                    <i class="fas fa-shopping-cart"></i> Lista de Compras
                </div>
            </div>

            <!-- SECCIÓN: BÚSQUEDA Y FILTROS (NUEVO - como en Productos) -->
            <div class="search-container">
                <form action="{{ route('compras.index') }}" method="GET" id="searchForm">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0" style="padding: 0.4rem 0.75rem;">
                                    <i class="fas fa-search text-muted small"></i>
                                </span>
                                <input type="text" name="busqueda" class="form-control form-control-clean border-start-0 ps-0"
                                    placeholder="Buscar compra..." value="{{ $busqueda ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <label for="per_page" class="me-2 text-muted small">Mostrar:</label>
                                <select name="per_page" id="per_page" class="form-select form-select-sm w-auto" style="border-radius: 6px;">
                                    @foreach([5, 10, 15, 20, 25] as $option)
                                        <option value="{{ $option }}" {{ ($perPage ?? 10) == $option ? 'selected' : '' }}>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5 text-end">
                            <a href="{{ route('compras.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 6px;">
                                <i class="fas fa-undo me-1"></i> Mostrar Todo
                            </a>
                        </div>
                    </div>

                    <div class="row g-3 align-items-end mt-1">
                        <div class="col-md-3">
                            <label class="text-muted small d-block mb-1">Metodo</label>
                            <select name="metodo_pago" class="form-select form-select-sm" style="border-radius: 6px;">
                                <option value="">Todos</option>
                                <option value="efectivo" {{ ($metodoPago ?? '') === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                <option value="debito" {{ ($metodoPago ?? '') === 'debito' ? 'selected' : '' }}>Débito</option>
                                <option value="qr" {{ ($metodoPago ?? '') === 'qr' ? 'selected' : '' }}>QR</option>
                                <option value="deposito" {{ ($metodoPago ?? '') === 'deposito' ? 'selected' : '' }}>Depósito</option>
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
                        <div class="col-md-3 text-end">
                            <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px;">
                                <i class="fas fa-filter me-1"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- SECCIÓN: ACCIONES DE SELECCIÓN -->
            <div class="selection-actions-container" id="selectionActions" style="display: none;">
                <div class="selected-counter">
                    <span>Compras seleccionadas:</span>
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
                                <!-- Undefined variable $sort-->
                                <th>
                                    <button class="sort-btn {{ $sort == 'comprobante_id' ? 'active ' . $direction : '' }}"
                                            data-column="comprobante_id">
                                        Comprobante <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                <th>
                                    <button class="sort-btn {{ $sort == 'proveedor' ? 'active ' . $direction : '' }}"
                                            data-column="proveedor">
                                        Proveedor <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th>
                                    <button class="sort-btn {{ $sort == 'fecha_hora' ? 'active ' . $direction : '' }}"
                                            data-column="fecha_hora">
                                        Fecha y Hora <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th class="text-center">
                                    <button class="sort-btn {{ $sort == 'total' ? 'active ' . $direction : '' }}"
                                            data-column="total">
                                        Total <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th class="text-center">Metodo</th>
                                <th class="text-center">Pagado / Saldo</th>
                                <th class="text-center">Estado Pago</th>
                                <th class="text-center">Entrega</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($compras as $item)
                                <tr data-compra-id="{{ $item->id }}">
                                    <td class="checkbox-cell">
                                        <div class="custom-checkbox compra-checkbox" data-compra-id="{{ $item->id }}"></div>
                                    </td>
                                    <td>
                                        <div class="compra-info">
                                            <div class="compra-avatar">
                                                <i class="fas fa-file-invoice small"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $item->comprobante->tipo_comprobante }}</div>
                                                <span class="info-subtext">Nro: {{ $item->numero_comprobante }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ ucfirst($item->proveedor->persona->tipo_persona) }}</div>
                                        <div class="info-subtext">{{ $item->proveedor->persona->razon_social }}</div>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-semibold">
                                                <i class="fas fa-calendar-day me-1 text-muted small"></i>
                                                {{ \Carbon\Carbon::parse($item->fecha_hora)->format('d-m-Y') }}
                                            </div>
                                            <div class="info-subtext">
                                                <i class="fas fa-clock me-1 small"></i>
                                                {{ \Carbon\Carbon::parse($item->fecha_hora)->format('H:i') }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-success">${{ number_format($item->total, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $metodo = $item->metodo_pago ?? 'efectivo';
                                            $metodoLabel = match ($metodo) {
                                                'debito' => 'DÃ©bito',
                                                'qr' => 'QR',
                                                'deposito' => 'DepÃ³sito',
                                                default => 'Efectivo',
                                            };
                                        @endphp
                                        <span class="badge bg-light text-dark border">{{ $metodoLabel }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="fw-semibold">Bs. {{ number_format((float)($item->monto_pagado ?? 0), 2) }}</div>
                                        <div class="info-subtext">Debe: Bs. {{ number_format((float)($item->saldo ?? 0), 2) }}</div>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown custom-dropdown">
                                            @php
                                                $estadoPago = $item->estado_pago;
                                                $statusClass = (in_array($estadoPago, ['pendiente', '0', 0])) ? 'badge-danger' :
                                                              ($estadoPago === 'parcial' ? 'badge-warning' :
                                                              (in_array($estadoPago, ['cancelado', 'anulado']) ? 'badge-secondary' : 'badge-success'));
                                                $statusText = (in_array($estadoPago, ['pendiente', '0', 0])) ? 'Pendiente' :
                                                             ($estadoPago === 'parcial' ? 'Parcial' :
                                                             (in_array($estadoPago, ['cancelado', 'anulado']) ? ucfirst($estadoPago) : 'Pagado'));
                                            @endphp
                                            <button class="status-btn badge-pill {{ $statusClass }} dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                {{ $statusText }}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                <li><a class="dropdown-item change-status-pago" href="#" data-compra-id="{{ $item->id }}" data-status="pagado">Pagado</a></li>
                                                <li><a class="dropdown-item change-status-pago" href="#" data-compra-id="{{ $item->id }}" data-status="parcial">Parcial...</a></li>
                                                <li><a class="dropdown-item change-status-pago" href="#" data-compra-id="{{ $item->id }}" data-status="pendiente">Pendiente</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown custom-dropdown">
                                            <button class="status-btn badge-pill {{ $item->estado_entrega == 'entregado' ? 'badge-success' : 'badge-warning' }} dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                {{ $item->estado_entrega == 'entregado' ? 'Entregado' : 'Por Recibir' }}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                <li><a class="dropdown-item change-status-entrega" href="#" data-compra-id="{{ $item->id }}" data-status="entregado">Entregado</a></li>
                                                <li><a class="dropdown-item change-status-entrega" href="#" data-compra-id="{{ $item->id }}" data-status="por_entregar">Por Recibir</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-action-group">
                                            @can('mostrar-compra')
                                                <button type="button" class="btn-icon-soft view-compra"
                                                        data-compra-id="{{ $item->id }}"
                                                        title="Ver Detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endcan

                                            @can('editar-compra')
                                                <a href="{{ route('compras.edit', ['compra' => $item]) }}"
                                                   class="btn-icon-soft" title="Editar">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                            @endcan

                                            @can('eliminar-compra')
                                                <button type="button" class="btn-icon-soft delete"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#confirmModal-{{ $item->id }}"
                                                        title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal de confirmación de eliminación -->
                                <div class="modal fade" id="confirmModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content modal-content-clean">
                                            <div class="modal-header modal-header-clean">
                                                <h5 class="modal-title fs-6">Confirmar Eliminación</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4 text-center">
                                                <h6 class="mb-3">¿Eliminar Compra?</h6>
                                                <p class="text-muted small mb-4">¿Está seguro de que desea eliminar esta compra? Esta acción no se puede deshacer.</p>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancelar</button>
                                                    <form action="{{ route('compras.destroy', $item->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm px-3">Eliminar</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>

                        <!-- NUEVO: TFOOT con resumen (como en Productos) -->
                        <tfoot>
                            <tr class="table-totals">
                                <td colspan="4" class="text-end">
                                    <span class="totals-label">RESUMEN GENERAL</span>
                                </td>
                                <td class="text-center">
                                    <span class="totals-value">{{ $compras->total() }}</span>
                                    <span class="totals-subtext">Total Compras</span>
                                </td>
                                <td class="text-center">
                                    <span class="totals-value success">${{ number_format($totalCompras ?? $compras->sum('total'), 2) }}</span>
                                    <span class="totals-subtext">Monto Total</span>
                                </td>
                                <td class="text-center">
                                    <span class="totals-value warning">{{ $comprasHoy ?? $compras->where('fecha_hora', '>=', now()->startOfDay())->count() }}</span>
                                    <span class="totals-subtext">Hoy</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- NUEVO: Paginación con info (como en Productos) -->
                <div class="p-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted extra-small">
                        Mostrando {{ $compras->firstItem() }} - {{ $compras->lastItem() }} de {{ $compras->total() }} registros
                    </div>
                    <div>
                        {{ $compras->appends(['busqueda' => $busqueda ?? '', 'per_page' => $perPage ?? 10, 'sort' => $sort ?? '', 'direction' => $direction ?? ''])->links() }}
                    </div>
                </div>
            </div>

            <!-- Modal para ver compra (FUERA del loop, estático) -->
            <div class="modal fade" id="viewCompraModal" tabindex="-1" aria-labelledby="viewCompraModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
                    <div class="modal-content modal-content-clean">
                        <div class="modal-header modal-header-clean">
                            <h5 class="modal-title fs-6">Detalles de la Compra</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4" id="modalCompraContent">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2 text-muted small">Cargando detalles de la compra...</p>
                            </div>
                        </div>
                        <div class="modal-footer border-0 d-flex flex-wrap gap-2 justify-content-between">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <a href="#" id="pdfButton" class="btn btn-outline-danger btn-sm px-4" target="_blank">
                                    <i class="fas fa-file-pdf me-1"></i> Descargar PDF
                                </a>

                                <input type="text" id="whatsappPhoneInput" class="form-control form-control-sm" style="width: 220px;"
                                    placeholder="WhatsApp (ej: 5917xxxxxxx)">

                                <button id="whatsappButton" type="button" class="btn btn-success btn-sm px-4" title="Enviar link por WhatsApp">
                                    <i class="fab fa-whatsapp me-1"></i> WhatsApp
                                </button>
                            </div>

                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <button id="printPdfButton" class="btn btn-outline-primary btn-sm px-4">
                                    <i class="fas fa-print me-1"></i> Imprimir
                                </button>
                                <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Exportación Genérico (ACTUALIZADO al estilo Productos) -->
            <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content modal-content-clean">
                        <div class="modal-header modal-header-clean">
                            <h5 class="modal-title fs-6" id="exportModalTitle">
                                <i class="fas fa-file-export me-2"></i> Exportar Compras
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <input type="hidden" id="exportFormat" value="excel">
                            <div class="alert alert-success border-0 bg-success bg-opacity-10 d-flex align-items-center mb-4" id="exportAlert" style="border-radius: 12px;">
                                <i class="fas fa-info-circle me-3 fs-5 text-success" id="exportAlertIcon"></i>
                                <div class="small fw-medium text-success">
                                    Se exportarán <strong id="exportCountDisplay">0</strong> compras seleccionadas.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="info-subtext mb-2 text-uppercase letter-spacing-05 small fw-bold">Opciones de Datos</label>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="modalIncludeDetails" checked>
                                    <label class="form-check-label d-block" for="modalIncludeDetails">
                                        <span class="d-block fw-semibold small">Incluir detalles de productos</span>
                                        <span class="extra-small text-muted">Productos comprados, cantidades y precios unitarios</span>
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="modalIncludeProvider" checked>
                                    <label class="form-check-label d-block" for="modalIncludeProvider">
                                        <span class="d-block fw-semibold small">Incluir información del proveedor</span>
                                        <span class="extra-small text-muted">Datos completos del proveedor</span>
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="modalIncludeAllDetails" checked>
                                    <label class="form-check-label d-block" for="modalIncludeAllDetails">
                                        <span class="d-block fw-semibold small">Incluir todos los detalles</span>
                                        <span class="extra-small text-muted">Comprobante, fechas y totales desglosados</span>
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
        </div>
    </div>
@endsection

@push('js')
<script>
    const comprasBaseUrl = '{{ route('compras.index') }}';
    let debounceTimer;
    const tableContainer = document.getElementById('table-container');
    let selectedCompras = new Set();
    let currentFacturaUrl = null;

    function normalizeWhatsappPhone(raw) {
        if (!raw) return '';
        let digits = String(raw).replace(/\D/g, '');
        if (!digits) return '';

        // Si el número parece local (7-8 dígitos), prefijar Bolivia (591) por defecto.
        if (digits.length <= 8) digits = `591${digits}`;

        return digits;
    }

    function openWhatsappWithText(phoneDigits, text) {
        const url = `https://wa.me/${phoneDigits}?text=${encodeURIComponent(text)}`;
        window.open(url, '_blank', 'noopener,noreferrer');
    }

    // NUEVO: Sistema de selección (igual que Productos)
    function initializeSelectionSystem() {
        const selectionActions = document.getElementById('selectionActions');
        const selectAllCheckbox = document.getElementById('selectAll');
        const compraCheckboxes = document.querySelectorAll('.compra-checkbox');
        const selectedCountElement = document.getElementById('selectedCount');
        const deselectAllBtn = document.getElementById('deselectAll');
        const exportExcelBtn = document.getElementById('exportExcel');
        const exportPdfBtn = document.getElementById('exportPdf');

        // Seleccionar/Deseleccionar compra individual
        compraCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('click', function() {
                const compraId = this.dataset.compraId;
                const row = this.closest('tr');

                if (this.classList.contains('checked')) {
                    this.classList.remove('checked');
                    row.classList.remove('selected');
                    selectedCompras.delete(compraId);
                } else {
                    this.classList.add('checked');
                    row.classList.add('selected');
                    selectedCompras.add(compraId);
                }

                updateSelectionUI();
            });
        });

        // Seleccionar todos
        selectAllCheckbox.addEventListener('click', function() {
            const isSelectAll = !this.classList.contains('checked');

            compraCheckboxes.forEach(checkbox => {
                const compraId = checkbox.dataset.compraId;
                const row = checkbox.closest('tr');

                if (isSelectAll) {
                    checkbox.classList.add('checked');
                    row.classList.add('selected');
                    selectedCompras.add(compraId);
                } else {
                    checkbox.classList.remove('checked');
                    row.classList.remove('selected');
                    selectedCompras.delete(compraId);
                }
            });

            this.classList.toggle('checked');
            updateSelectionUI();
        });

        // Deseleccionar todos
        deselectAllBtn.addEventListener('click', function() {
            selectedCompras.clear();
            compraCheckboxes.forEach(checkbox => {
                checkbox.classList.remove('checked');
                const row = checkbox.closest('tr');
                row.classList.remove('selected');
            });
            selectAllCheckbox.classList.remove('checked');
            updateSelectionUI();
        });

        // Eventos de Exportación
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
            document.getElementById('exportCountDisplay').textContent = selectedCompras.size;

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
            const count = selectedCompras.size;
            selectedCountElement.textContent = count;

            if (count > 0) {
                selectionActions.style.display = 'flex';
                const totalCheckboxes = compraCheckboxes.length;
                if (count === totalCheckboxes) {
                    selectAllCheckbox.classList.add('checked');
                } else {
                    selectAllCheckbox.classList.remove('checked');
                }
            } else {
                selectionActions.style.display = 'none';
                selectAllCheckbox.classList.remove('checked');
            }
        }
    }

    // NUEVO: Manejar confirmación de exportación (igual que Productos)
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'confirmExportBtn') {
            const format = document.getElementById('exportFormat').value;
            const compraIds = Array.from(selectedCompras);
            const includeDetails = document.getElementById('modalIncludeDetails').checked;
            const includeProvider = document.getElementById('modalIncludeProvider').checked;
            const includeAllDetails = document.getElementById('modalIncludeAllDetails').checked;

            // Cerrar modal
            const modalElement = document.getElementById('exportModal');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            modalInstance.hide();

            // Mostrar loading
            Swal.fire({
                title: 'Generando archivo...',
                text: 'Por favor espere un momento.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            // Crear y enviar formulario
            const form = document.createElement('form');
            form.method = 'POST';
            //Route [compras.export.excel] not defined.
            form.action = format === 'excel'
                ? '{{ route('export.excel', ['module' => 'compras']) }}'
                : '{{ route('export.pdf', ['module' => 'compras']) }}';


            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            compraIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });

            const options = { includeDetails, includeProvider, includeAllDetails };
            Object.keys(options).forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = options[key];
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();

            // Limpiar selección
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

    // Eventos de búsqueda y ordenamiento (igual que Productos)
    function initializeEvents() {
        const searchInput = document.querySelector('input[name="busqueda"]');
        const perPageSelect = document.getElementById('per_page');
        const sortButtons = document.querySelectorAll('.sort-btn');

        // Note: Status Change Hooks moved to global event delegation below

        // Búsqueda con debounce
        if (searchInput) {
            searchInput.focus();
            const len = searchInput.value.length;
            searchInput.setSelectionRange(len, len);

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => fetchCompras(), 300);
            });
        }

        // Items por página
        if (perPageSelect) {
            perPageSelect.addEventListener('change', () => fetchCompras());
        }

        // Ordenamiento
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

                fetchCompras(`{{ route('compras.index') }}?${params.toString()}`);
            });
        });
    }

    // Función AJAX para actualizar tabla (igual que Productos)
    function fetchCompras(url = null) {
        const searchInput = document.querySelector('input[name="busqueda"]');
        const perPageSelect = document.getElementById('per_page');

        let fetchUrl = url;
        if (!fetchUrl) {
            const params = new URLSearchParams(window.location.search);
            if (searchInput) params.set('busqueda', searchInput.value);
            if (perPageSelect) params.set('per_page', perPageSelect.value);
            fetchUrl = `{{ route('compras.index') }}?${params.toString()}`;
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

                // Limpiar selección al actualizar
                selectedCompras.clear();
                document.getElementById('selectionActions').style.display = 'none';

                // Actualizar URL sin recargar
                window.history.pushState({}, '', fetchUrl);

                initializeEvents();
                initializeSelectionSystem();
            })
            .catch(error => {
                console.error('Error:', error);
                tableContainer.style.opacity = '1';
            });
    }

    // Inicialización
    document.addEventListener('DOMContentLoaded', function() {
        initializeEvents();
        initializeSelectionSystem();
    });

    // Manejar clic en fila para seleccionar (igual que Productos)
    document.addEventListener('click', function(e) {
        // Status Change Hooks (Global Delegation)
        if (e.target.classList.contains('change-status-pago')) {
            e.preventDefault();
            const compraId = e.target.dataset.compraId;
            const status = e.target.dataset.status;
            updatePurchaseStatus(compraId, 'pago', status);
            return;
        }
        if (e.target.classList.contains('change-status-entrega')) {
            e.preventDefault();
            const compraId = e.target.dataset.compraId;
            const status = e.target.dataset.status;
            updatePurchaseStatus(compraId, 'entrega', status);
            return;
        }

        if (e.target.closest('tr[data-compra-id]') && !e.target.closest('.btn-action-group') && !e.target.closest('.custom-checkbox') && !e.target.closest('.dropdown')) {
            const row = e.target.closest('tr[data-compra-id]');
            const checkbox = row.querySelector('.compra-checkbox');

            if (checkbox) {
                checkbox.click();
            }
        }
    });

    // Mantener funcionalidad de Ver Compra (adaptada)
    document.body.addEventListener('click', function(e) {
        if (e.target.classList.contains('view-compra') || e.target.closest('.view-compra')) {
            const button = e.target.classList.contains('view-compra') ?
                e.target : e.target.closest('.view-compra');
            const compraId = button.dataset.compraId;
            
            const modalElement = document.getElementById('viewCompraModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();

            loadCompraDetails(compraId);
        }

        if (e.target.closest('#whatsappButton')) {
            e.preventDefault();
            const phoneInput = document.getElementById('whatsappPhoneInput');
            const pdfBtn = document.getElementById('pdfButton');

            const phoneDigits = normalizeWhatsappPhone(phoneInput ? phoneInput.value : '');
            const facturaUrl = currentFacturaUrl || (pdfBtn ? pdfBtn.href : '');

            if (!phoneDigits) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Número requerido',
                    text: 'Escribe un número de WhatsApp (ej: 5917xxxxxxx).'
                });
                return;
            }

            if (!facturaUrl) {
                Swal.fire({
                    icon: 'error',
                    title: 'Sin link',
                    text: 'No se pudo obtener el link de la factura.'
                });
                return;
            }

            openWhatsappWithText(phoneDigits, facturaUrl);
            return;
        }

        if (e.target.closest('#printPdfButton')) {
            e.preventDefault();
            const pdfBtn = document.getElementById('pdfButton');
            if(pdfBtn && pdfBtn.href) {
                const compraId = pdfBtn.href.split('/').pop();
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = `${comprasBaseUrl}/pdf/${compraId}?print=1`;
                document.body.appendChild(iframe);

                iframe.onload = function() {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                };
            }
        }
    });

    function loadCompraDetails(compraId) {
        const modalContent = document.getElementById('modalCompraContent');
        const pdfButton = document.getElementById('pdfButton');

        modalContent.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2 text-muted small">Cargando detalles de la compra...</p>
            </div>
        `;

        fetch(`${comprasBaseUrl}/${compraId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error al cargar los datos');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                modalContent.innerHTML = data.html;
                pdfButton.href = data.pdf_url || `${comprasBaseUrl}/pdf/${compraId}`;
                currentFacturaUrl = data.factura_url || null;

                const phoneInput = document.getElementById('whatsappPhoneInput');
                if (phoneInput) phoneInput.value = data.telefono || '';
            } else {
                throw new Error(data.message || 'Error en los datos recibidos');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error al cargar los detalles: ${error.message}
                    <button class="btn btn-sm btn-warning mt-2" onclick="loadCompraDetails(${compraId})">
                        <i class="fas fa-redo me-1"></i>Reintentar
                    </button>
                </div>
            `;
        });
    }

    async function updatePurchaseStatus(compraId, type, status) {
        const url = type === 'pago' ? `${comprasBaseUrl}/${compraId}/estado-pago` : `${comprasBaseUrl}/${compraId}/estado-entrega`;
        let data = type === 'pago' ? { estado_pago: status } : { estado_entrega: status };

        if (type === 'pago' && status === 'parcial') {
            const { value } = await Swal.fire({
                title: 'Pago parcial',
                input: 'number',
                inputLabel: 'Monto pagado (Bs.)',
                inputAttributes: { min: 0, step: 0.01 },
                showCancelButton: true,
                confirmButtonText: 'Guardar',
            });
            if (value === undefined) return;
            data.monto_pagado = parseFloat(value || 0);
        }

        Swal.fire({
            title: 'Actualizando estado...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
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
                    fetchCompras(); // Recargar tabla

                    // Si el modal de detalles está abierto, actualizarlo también
                    const modalElement = document.getElementById('viewCompraModal');
                    if (modalElement && modalElement.classList.contains('show')) {
                        loadCompraDetails(compraId);
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
</script>
@endpush



