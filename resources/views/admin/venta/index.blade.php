@extends('admin.layouts.app')

@section('title', 'Ventas')

@push('css-datatable')
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css " rel="stylesheet" type="text/css">
@endpush

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11 "></script>
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
        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }
    </style>
@endpush

@section('content')
    @include('admin.layouts.partials.alert')

    <div class="container-fluid px-4 py-4">

        <div class="page-header">
            <div>
                <h1 class="page-title">Gestion de Ventas</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none text-muted">Inicio</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Ventas</li>
                    </ol>
                </nav>
            </div>
            @can('crear-venta')
            <a href="{{ route('ventas.create') }}" class="btn-create">
                <i class="fas fa-plus"></i> Nueva Venta
            </a>
            @endcan
            <a href="{{ route('ventas.reporte-caja') }}" class="btn btn-outline-primary btn-sm ms-2" style="border-radius: 10px;">
                <i class="fas fa-cash-register me-1"></i> Reporte Caja
            </a>
        </div>

        <div class="card-clean">
            <div class="card-header-clean">
                <div class="card-header-title">
                    <i class="fas fa-shopping-bag"></i> Registro de Ventas
                </div>
            </div>

            <!-- SECCION: BUSQUEDA Y FILTROS (NUEVO - como en Productos) -->
            <div class="search-container">
                <form action="{{ route('ventas.index') }}" method="GET" id="searchForm">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0" style="padding: 0.4rem 0.75rem;">
                                    <i class="fas fa-search text-muted small"></i>
                                </span>
                                <input type="text" name="busqueda" class="form-control form-control-clean border-start-0 ps-0"
                                    placeholder="Buscar venta..." value="{{ $busqueda ?? '' }}">
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
                            <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 6px;">
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

            <!-- SECCION: ACCIONES DE SELECCION -->
            <div class="selection-actions-container" id="selectionActions" style="display: none;">
                <div class="selected-counter">
                    <span>Ventas seleccionadas:</span>
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
                                    <button class="sort-btn {{ $sort == 'numero_comprobante' ? 'active ' . $direction : '' }}"
                                            data-column="numero_comprobante">
                                        Comprobante <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th>
                                    <button class="sort-btn {{ $sort == 'cliente' ? 'active ' . $direction : '' }}"
                                            data-column="cliente">
                                        Cliente <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th>
                                    <button class="sort-btn {{ $sort == 'fecha_hora' ? 'active ' . $direction : '' }}"
                                            data-column="fecha_hora">
                                        Fecha/Hora <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th class="text-center">
                                    <button class="sort-btn {{ $sort == 'total' ? 'active ' . $direction : '' }}"
                                            data-column="total">
                                        Total <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th class="text-center">Método</th>
                                <th class="text-center">Pagado / Saldo</th>
                                <th class="text-center">
                                    <button class="sort-btn {{ $sort == 'estado_pago' ? 'active ' . $direction : '' }}"
                                            data-column="estado_pago">
                                        Estado Pago <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th class="text-center">
                                    <button class="sort-btn {{ $sort == 'estado_entrega' ? 'active ' . $direction : '' }}"
                                            data-column="estado_entrega">
                                        Estado Entrega <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ventas as $venta)
                                <tr data-venta-id="{{ $venta->id }}">
                                    <td class="checkbox-cell">
                                        <div class="custom-checkbox venta-checkbox" data-venta-id="{{ $venta->id }}"></div>
                                    </td>
                                    <td>
                                        <div class="venta-info">
                                            <div class="venta-avatar">
                                                <i class="fas fa-file-invoice small"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $venta->comprobante->tipo_comprobante }}</div>
                                                <span class="info-subtext">Nro: {{ $venta->numero_comprobante }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($venta->cliente && $venta->cliente->persona)
                                            <div class="fw-semibold">{{ $venta->cliente->persona->razon_social }}</div>
                                            <div class="info-subtext">{{ $venta->cliente->persona->numero_documento }}</div>
                                        @else
                                            <div class="fw-semibold text-danger">Cliente no disponible</div>
                                            <div class="info-subtext">---</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-semibold">
                                                <i class="fas fa-calendar-day me-1 text-muted small"></i>
                                                {{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d-m-Y') }}
                                            </div>
                                            <div class="info-subtext">
                                                <i class="fas fa-clock me-1 small"></i>
                                                {{ \Carbon\Carbon::parse($venta->fecha_hora)->format('H:i') }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-success">${{ number_format($venta->total, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $metodo = $venta->metodo_pago ?? 'efectivo';
                                            $metodoLabel = match ($metodo) {
                                                'debito' => 'DÃ©bito',
                                                'qr' => 'QR',
                                                'deposito' => 'DepÃ³sito',
                                                'mixto' => 'Mixto',
                                                default => 'Efectivo',
                                            };
                                        @endphp
                                        <span class="badge bg-light text-dark border">{{ $metodoLabel }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="fw-semibold">Bs. {{ number_format((float)($venta->monto_pagado ?? 0), 2) }}</div>
                                        <div class="info-subtext">Debe: Bs. {{ number_format((float)($venta->saldo ?? 0), 2) }}</div>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown custom-dropdown">
                                            @php
                                                $estadoPago = $venta->estado_pago;
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
                                                <li><a class="dropdown-item change-status-pago" href="#" data-venta-id="{{ $venta->id }}" data-status="pagado" data-saldo="{{ (float)($venta->saldo ?? 0) }}">Pagado</a></li>
                                                <li><a class="dropdown-item change-status-pago" href="#" data-venta-id="{{ $venta->id }}" data-status="parcial" data-saldo="{{ (float)($venta->saldo ?? 0) }}">Parcial...</a></li>
                                                <li><a class="dropdown-item change-status-pago" href="#" data-venta-id="{{ $venta->id }}" data-status="pendiente" data-saldo="{{ (float)($venta->saldo ?? 0) }}">Pendiente</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown custom-dropdown">
                                            <button class="status-btn badge-pill {{ $venta->estado_entrega == 'entregado' ? 'badge-success' : 'badge-warning' }} dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                {{ $venta->estado_entrega == 'entregado' ? 'Entregado' : 'Por Entregar' }}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                <li><a class="dropdown-item change-status-entrega" href="#" data-venta-id="{{ $venta->id }}" data-status="entregado">Entregado</a></li>
                                                <li><a class="dropdown-item change-status-entrega" href="#" data-venta-id="{{ $venta->id }}" data-status="por_entregar">Por Entregar</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-action-group">
                                            @can('mostrar-venta')
                                                <button type="button" class="btn-icon-soft view-venta"
                                                        data-venta-id="{{ $venta->id }}"
                                                        title="Ver Detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endcan

                                            @can('editar-venta')
                                                <a href="{{ route('ventas.edit', $venta->id) }}" class="btn-icon-soft" title="Editar">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                            @endcan

                                            @can('eliminar-venta')
                                                <button type="button" class="btn-icon-soft delete"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#confirmModal-{{ $venta->id }}"
                                                        title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan

                                            <a href="{{ route('ventas.pdf', $venta->id) }}" class="btn-icon-soft" target="_blank" title="Imprimir Comprobante">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal de confirmacion -->
                                <div class="modal fade" id="confirmModal-{{ $venta->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content modal-content-clean">
                                            <div class="modal-header modal-header-clean">
                                                <h5 class="modal-title fs-6">Confirmar Eliminacion</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4 text-center">
                                                <h6 class="mb-3">¿Eliminar Venta?</h6>
                                                <p class="text-muted small mb-4">Se eliminara el registro de venta y se devolvera el stock a los almacenes correspondientes.</p>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancelar</button>
                                                    <form action="{{ route('ventas.destroy', $venta->id) }}" method="POST" class="d-inline">
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

                        <!-- TFOOT con resumen (como en Productos) -->
                        <tfoot>
                            <tr class="table-totals">
                                <td colspan="4" class="text-end">
                                    <span class="totals-label">RESUMEN GENERAL</span>
                                </td>
                                <td class="text-center">
                                    <span class="totals-value">{{ $ventas->total() }}</span>
                                    <span class="totals-subtext">Total Ventas</span>
                                </td>
                                <td class="text-center">
                                    <span class="totals-value success">{{ $ventasPagadas ?? $ventas->where('estado_pago', '!=', 'pendiente')->where('estado_pago', '!=', 0)->count() }}</span>
                                    <span class="totals-subtext">Pagadas</span>
                                </td>
                                <td class="text-center">
                                    <span class="totals-value warning">{{ $ventasPendientesPago ?? $ventas->where('estado_pago', 'pendiente')->orWhere('estado_pago', 0)->count() }}</span>
                                    <span class="totals-subtext">Pendientes</span>
                                </td>
                                <td class="text-center">
                                    <span class="totals-value">${{ number_format($totalVentasMonto ?? $ventas->sum('total'), 2) }}</span>
                                    <span class="totals-subtext">Monto Total</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Paginacion (como en Productos) -->
                <div class="p-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted extra-small">
                        Mostrando {{ $ventas->firstItem() }} - {{ $ventas->lastItem() }} de {{ $ventas->total() }} registros
                    </div>
                    <div>
                        {{ $ventas->appends(['busqueda' => $busqueda ?? '', 'per_page' => $perPage ?? 10, 'sort' => $sort ?? '', 'direction' => $direction ?? ''])->links() }}
                    </div>
                </div>
            </div>

            <!-- Modal para ver venta (FUERA del loop) -->
            <div class="modal fade" id="viewVentaModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
                    <div class="modal-content modal-content-clean">
                        <div class="modal-header modal-header-clean">
                            <h5 class="modal-title fs-6">Detalles de la Venta</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4" id="modalVentaContent">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2 text-muted small">Cargando detalles de la venta...</p>
                            </div>
                        </div>
                        <div class="modal-footer border-0 d-flex flex-wrap gap-2 justify-content-between" id="modalVentaFooter">
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
                                <button id="printButton" class="btn btn-outline-primary btn-sm px-4">
                                    <i class="fas fa-print me-1"></i> Imprimir
                                </button>
                                <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Exportacion Generico (ACTUALIZADO) -->
            <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content modal-content-clean">
                        <div class="modal-header modal-header-clean">
                            <h5 class="modal-title fs-6" id="exportModalTitle">
                                <i class="fas fa-file-export me-2"></i> Exportar Ventas
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <input type="hidden" id="exportFormat" value="excel">
                            <div class="alert alert-success border-0 bg-success bg-opacity-10 d-flex align-items-center mb-4" id="exportAlert" style="border-radius: 12px;">
                                <i class="fas fa-info-circle me-3 fs-5 text-success" id="exportAlertIcon"></i>
                                <div class="small fw-medium text-success">
                                    Se exportaran <strong id="exportCountDisplay">0</strong> ventas seleccionadas.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="info-subtext mb-2 text-uppercase letter-spacing-05 small fw-bold">Opciones de Datos</label>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="modalIncludeDetails" checked>
                                    <label class="form-check-label d-block" for="modalIncludeDetails">
                                        <span class="d-block fw-semibold small">Incluir detalles de productos</span>
                                        <span class="extra-small text-muted">Productos vendidos, cantidades y precios unitarios</span>
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="modalIncludeClient" checked>
                                    <label class="form-check-label d-block" for="modalIncludeClient">
                                        <span class="d-block fw-semibold small">Incluir informacion del cliente</span>
                                        <span class="extra-small text-muted">Datos completos del cliente</span>
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="modalIncludeAllDetails" checked>
                                    <label class="form-check-label d-block" for="modalIncludeAllDetails">
                                        <span class="d-block fw-semibold small">Incluir todos los detalles</span>
                                        <span class="extra-small text-muted">Comprobante, estados y totales desglosados</span>
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
    const ventasBaseUrl = '{{ route('ventas.index') }}';
    let debounceTimer;
    const tableContainer = document.getElementById('table-container');
    let selectedVentas = new Set();
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

    // Sistema de seleccion (igual que Productos)
    function initializeSelectionSystem() {
        const selectionActions = document.getElementById('selectionActions');
        const selectAllCheckbox = document.getElementById('selectAll');
        const ventaCheckboxes = document.querySelectorAll('.venta-checkbox');
        const selectedCountElement = document.getElementById('selectedCount');
        const deselectAllBtn = document.getElementById('deselectAll');
        const exportExcelBtn = document.getElementById('exportExcel');
        const exportPdfBtn = document.getElementById('exportPdf');

        ventaCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('click', function() {
                const ventaId = this.dataset.ventaId;
                const row = this.closest('tr');

                if (this.classList.contains('checked')) {
                    this.classList.remove('checked');
                    row.classList.remove('selected');
                    selectedVentas.delete(ventaId);
                } else {
                    this.classList.add('checked');
                    row.classList.add('selected');
                    selectedVentas.add(ventaId);
                }

                updateSelectionUI();
            });
        });

        selectAllCheckbox.addEventListener('click', function() {
            const isSelectAll = !this.classList.contains('checked');

            ventaCheckboxes.forEach(checkbox => {
                const ventaId = checkbox.dataset.ventaId;
                const row = checkbox.closest('tr');

                if (isSelectAll) {
                    checkbox.classList.add('checked');
                    row.classList.add('selected');
                    selectedVentas.add(ventaId);
                } else {
                    checkbox.classList.remove('checked');
                    row.classList.remove('selected');
                    selectedVentas.delete(ventaId);
                }
            });

            this.classList.toggle('checked');
            updateSelectionUI();
        });

        deselectAllBtn.addEventListener('click', function() {
            selectedVentas.clear();
            ventaCheckboxes.forEach(checkbox => {
                checkbox.classList.remove('checked');
                const row = checkbox.closest('tr');
                row.classList.remove('selected');
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
            document.getElementById('exportCountDisplay').textContent = selectedVentas.size;

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
            const count = selectedVentas.size;
            selectedCountElement.textContent = count;

            if (count > 0) {
                selectionActions.style.display = 'flex';
                const totalCheckboxes = ventaCheckboxes.length;
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

    // Exportacion con rutas genericas (usando tu sistema de exportacion)
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'confirmExportBtn') {
            const format = document.getElementById('exportFormat').value;
            const ventaIds = Array.from(selectedVentas);
            const includeDetails = document.getElementById('modalIncludeDetails').checked;
            const includeClient = document.getElementById('modalIncludeClient').checked;
            const includeAllDetails = document.getElementById('modalIncludeAllDetails').checked;

            const modalElement = document.getElementById('exportModal');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            modalInstance.hide();

            Swal.fire({
                title: 'Generando archivo...',
                text: 'Por favor espere un momento.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const form = document.createElement('form');
            form.method = 'POST';
            // Usando tu sistema de rutas genericas
            form.action = format === 'excel'
                ? '{{ route('export.excel', ['module' => 'ventas']) }}'
                : '{{ route('export.pdf', ['module' => 'ventas']) }}';

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            ventaIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });

            const options = { includeDetails, includeClient, includeAllDetails };
            Object.keys(options).forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = options[key];
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();

            setTimeout(() => {
                document.getElementById('deselectAll').click();
                Swal.fire({
                    icon: 'success',
                    title: 'Exportacion iniciada',
                    text: 'El archivo se descargara automaticamente.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }, 1000);
        }
    });

    // Eventos de búsqueda y ordenamiento
    function initializeEvents() {
        const searchInput = document.querySelector('input[name="busqueda"]');
        const perPageSelect = document.getElementById('per_page');
        const sortButtons = document.querySelectorAll('.sort-btn');

        // Note: Status Change Hooks moved to global event delegation below

        if (searchInput) {
            searchInput.focus();
            const len = searchInput.value.length;
            searchInput.setSelectionRange(len, len);

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => fetchVentas(), 300);
            });
        }

        if (perPageSelect) {
            perPageSelect.addEventListener('change', () => fetchVentas());
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

                fetchVentas(`{{ route('ventas.index') }}?${params.toString()}`);
            });
        });
    }

    function fetchVentas(url = null) {
        const searchInput = document.querySelector('input[name="busqueda"]');
        const perPageSelect = document.getElementById('per_page');

        let fetchUrl = url;
        if (!fetchUrl) {
            const params = new URLSearchParams(window.location.search);
            if (searchInput) params.set('busqueda', searchInput.value);
            if (perPageSelect) params.set('per_page', perPageSelect.value);
            fetchUrl = `{{ route('ventas.index') }}?${params.toString()}`;
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

                selectedVentas.clear();
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

    document.addEventListener('DOMContentLoaded', function() {
        initializeEvents();
        initializeSelectionSystem();
    });

    // Clic en fila para seleccionar
    document.addEventListener('click', function(e) {
        // Status Change Hooks (Global Delegation)
        const pagoLink = e.target.closest('.change-status-pago');
        if (pagoLink) {
            e.preventDefault();
            const ventaId = pagoLink.dataset.ventaId;
            const status = pagoLink.dataset.status;
            const saldo = parseFloat(pagoLink.dataset.saldo || '');
            updateSaleStatus(ventaId, 'pago', status, isNaN(saldo) ? null : saldo);
            return;
        }

        const entregaLink = e.target.closest('.change-status-entrega');
        if (entregaLink) {
            e.preventDefault();
            const ventaId = entregaLink.dataset.ventaId;
            const status = entregaLink.dataset.status;
            updateSaleStatus(ventaId, 'entrega', status);
            return;
        }

        if (e.target.closest('tr[data-venta-id]') && !e.target.closest('.btn-action-group') && !e.target.closest('.custom-checkbox') && !e.target.closest('.dropdown')) {
            const row = e.target.closest('tr[data-venta-id]');
            const checkbox = row.querySelector('.venta-checkbox');

            if (checkbox) {
                checkbox.click();
            }
        }
    });

    // View Modal Logic (mantenido de tu codigo original)
    document.body.addEventListener('click', function(e) {
        if (e.target.classList.contains('view-venta') || e.target.closest('.view-venta')) {
            const button = e.target.classList.contains('view-venta') ? e.target : e.target.closest('.view-venta');
            const ventaId = button.dataset.ventaId;

            const modalElement = document.getElementById('viewVentaModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement, { focus: false });
            modal.show();

            loadVentaDetails(ventaId);
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

        if (e.target.closest('#printButton')) {
            e.preventDefault();
            const pdfBtn = document.getElementById('pdfButton');
            if(pdfBtn && pdfBtn.href) {
                const ventaId = pdfBtn.href.split('/').pop();
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = `${ventasBaseUrl}/pdf/${ventaId}?print=1`;
                document.body.appendChild(iframe);

                iframe.onload = function() {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                };
            }
        }
    });

    function loadVentaDetails(ventaId) {
        const content = document.getElementById('modalVentaContent');
        const pdfButton = document.getElementById('pdfButton');

        content.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2 text-muted small">Cargando detalles de la venta...</p>
            </div>
        `;

        fetch(`${ventasBaseUrl}/${ventaId}`, {
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
                content.innerHTML = data.html;
                pdfButton.href = data.pdf_url || `${ventasBaseUrl}/pdf/${ventaId}`;
                currentFacturaUrl = data.factura_url || null;

                const phoneInput = document.getElementById('whatsappPhoneInput');
                const telefono = data.telefono || (data.venta && data.venta.cliente && data.venta.cliente.persona ? data.venta.cliente.persona.telefono : '');
                if (phoneInput) phoneInput.value = telefono || '';
            } else {
                throw new Error(data.message || 'Error en los datos recibidos');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error al cargar los detalles: ${error.message}
                    <button class="btn btn-sm btn-warning mt-2" onclick="loadVentaDetails(${ventaId})">
                        <i class="fas fa-redo me-1"></i>Reintentar
                    </button>
                </div>
            `;
        });
    }

    async function updateSaleStatus(ventaId, type, status, saldoRestante = null) {
        const url = type === 'pago' ? `${ventasBaseUrl}/${ventaId}/estado-pago` : `${ventasBaseUrl}/${ventaId}/estado-entrega`;
        let data = type === 'pago' ? { estado_pago: status } : { estado_entrega: status };

        if (type === 'pago' && status === 'parcial') {
            const { value } = await Swal.fire({
                title: 'Pago parcial',
                html: `
                    <div class="text-start">
                        ${saldoRestante !== null ? `<div class="mb-2 small text-muted">Saldo restante: <b>Bs. ${saldoRestante.toFixed(2)}</b></div>` : ''}
                        <label class="form-label small">Método</label>
                        <select id="sw_metodo" class="form-select form-select-sm mb-2">
                            <option value="efectivo">Efectivo</option>
                            <option value="debito">Débito</option>
                            <option value="qr">QR</option>
                            <option value="deposito">Depósito</option>
                            <option value="otro">Otro</option>
                        </select>
                        <label class="form-label small">Monto (Bs.)</label>
                        <input id="sw_monto" type="number" class="form-control form-control-sm" min="0" step="0.01" value="0.00" />
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                preConfirm: () => ({
                    metodo: document.getElementById('sw_metodo').value,
                    monto: parseFloat(document.getElementById('sw_monto').value || 0),
                }),
            });
            if (value === undefined) return;
            if (saldoRestante !== null && value.monto > saldoRestante) {
                Swal.fire('Monto inválido', 'El monto no puede superar el saldo restante.', 'warning');
                return;
            }
            data.metodo_pago = value.metodo;
            data.monto_pagado = value.monto;
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
                    title: 'Exito',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    fetchVentas(); // Recargar tabla

                    // Si el modal de detalles esta abierto, actualizarlo tambien
                    const modalElement = document.getElementById('viewVentaModal');
                    if (modalElement && modalElement.classList.contains('show')) {
                        loadVentaDetails(ventaId);
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



