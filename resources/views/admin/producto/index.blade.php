@extends('admin.layouts.app')

@section('title', 'Productos')

@push('css-datatable')
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/style_Categoria.css') }}">
    <style>
        .search-container {
            margin: 1rem 0 1.25rem;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            background: #fff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .filter-pill {
            border: 1px solid #d1d5db;
            background: #fff;
            color: #6b7280;
            padding: 0.42rem 0.8rem;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.18s ease;
            cursor: pointer;
        }

        .filter-pill:hover,
        .filter-pill.active {
            background: #1f2937;
            color: #fff;
            border-color: #1f2937;
        }

        .form-control-clean,
        .form-select.form-control-clean {
            border-radius: 10px;
            min-height: 2.85rem;
            border-color: #d1d5db;
            box-shadow: none;
        }

        .form-control-clean:focus,
        .form-select.form-control-clean:focus {
            border-color: #6b7280;
            box-shadow: 0 0 0 0.15rem rgba(107, 114, 128, 0.12);
        }

        .badge-success {
            background: #10b981;
            color: #fff;
        }

        .badge-danger {
            background: #ef4444;
            color: #fff;
        }

        .badge-warning {
            background: #f59e0b;
            color: #fff;
        }

        .badge-info {
            background: #3b82f6;
            color: #fff;
        }

        .pagination .page-item.active .page-link {
            background: #1f2937;
            border-color: #1f2937;
            color: #fff;
        }

        .pagination .page-link {
            color: #1f2937;
        }

        .pagination .page-link:hover {
            background: #f3f4f6;
        }

        /* Estilos para los estados originales */
        .bg-success {
            background-color: #10b981 !important;
        }

        .bg-danger {
            background-color: #ef4444 !important;
        }

        .text-white {
            color: #fff !important;
        }

        .badge-pill {
            padding: 0.25rem 0.75rem;
            border-radius: 10rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-pill.badge-success {
            background: #10b981;
            color: #fff;
        }

        .badge-pill.badge-danger {
            background: #ef4444;
            color: #fff;
        }

        .badge-pill.badge-warning {
            background: #f59e0b;
            color: #fff;
        }

        .badge-pill.badge-info {
            background: #3b82f6;
            color: #fff;
        }
        .btn-stock-branch {
            display: inline-flex;
            align-items: center;
            padding: 0.15rem 0.4rem;
            border-radius: 6px;
            font-size: 0.68rem;
            font-weight: 500;
            background-color: #f8fafc;
            border-color: #e2e8f0;
            color: #475569;
            transition: all 0.2s ease;
        }

        .btn-stock-branch:hover {
            background-color: #f1f5f9;
            border-color: #cbd5e1;
            color: #1e293b;
        }

        .btn-stock-branch .stock-name {
            margin-right: 0.25rem;
            max-width: 90px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .badge-stock {
            display: inline-block;
            padding: 0.05rem 0.3rem;
            border-radius: 4px;
            font-size: 0.65rem;
            font-weight: 700;
        }

        .badge-stock.low {
            background-color: #fee2e2;
            color: #ef4444;
        }

        .badge-stock.normal {
            background-color: #dcfce7;
            color: #10b981;
        }
    </style>
@endpush

@section('content')
    @include('admin.layouts.partials.alert')

    <div class="container-fluid px-4 py-4">

        <div class="page-header">
            <div>
                <h1 class="page-title">Productos</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none text-muted">Inicio</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Productos</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <button type="button" class="btn btn-outline-success btn-sm d-flex align-items-center gap-2" id="btnExportAllExcel" style="padding: 0.5rem 1rem; border-radius: 8px; font-weight: 500;">
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-2" id="btnExportAllPdf" style="padding: 0.5rem 1rem; border-radius: 8px; font-weight: 500;">
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </button>
                @can('crear-producto')
                <a href="{{ route('productos.create') }}" class="btn-create ms-2">
                    <i class="fas fa-plus"></i> Nuevo Producto
                </a>
                @endcan
            </div>
        </div>

        <div class="card-clean">
            <div class="card-header-clean">
                <div class="card-header-title">
                    <i class="fas fa-list"></i> Lista de Productos
                </div>
            </div>

            <div class="search-container">
                <form action="{{ route('productos.index') }}" method="GET" id="searchForm">
                    <input type="hidden" name="estado" id="estado" value="{{ $estado ?? 'all' }}">
                    <input type="hidden" name="stock" id="stock" value="{{ $stock ?? 'all' }}">

                    <div class="row g-3">
                        <!-- Búsqueda -->
                        <div class="col-lg-3">
                            <label class="info-subtext mb-2 text-uppercase letter-spacing-05 small fw-bold">Búsqueda</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0" style="padding: 0.8rem 0.95rem;">
                                    <i class="fas fa-search text-muted small"></i>
                                </span>
                                <input type="text" name="busqueda" class="form-control form-control-clean border-start-0 ps-0"
                                    placeholder="Busca por código, nombre o descripción..." value="{{ $busqueda ?? '' }}" autocomplete="off">
                            </div>
                        </div>

                        <!-- Categoría -->
                        <div class="col-lg-2">
                            <label for="categoria_id" class="info-subtext mb-2 text-uppercase letter-spacing-05 small fw-bold">Categoría</label>
                            <select name="categoria_id" id="categoria_id" class="form-select form-select-lg form-control-clean">
                                <option value="all" {{ ($categoriaId ?? 'all') === 'all' ? 'selected' : '' }}>Todas</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}" {{ (string)($categoriaId ?? 'all') === (string)$categoria->id ? 'selected' : '' }}>
                                        {{ $categoria->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Almacén -->
                        <div class="col-lg-2">
                            <label for="almacen_id" class="info-subtext mb-2 text-uppercase letter-spacing-05 small fw-bold">Almacén</label>
                            <select name="almacen_id" id="almacen_id" class="form-select form-select-lg form-control-clean">
                                <option value="all" {{ ($almacenId ?? 'all') === 'all' ? 'selected' : '' }}>Todos</option>
                                @foreach($almacenes as $almacen)
                                    <option value="{{ $almacen->id }}" {{ (string)($almacenId ?? 'all') === (string)$almacen->id ? 'selected' : '' }}>
                                        {{ $almacen->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Ver por página -->
                        <div class="col-lg-2">
                            <label for="per_page" class="info-subtext mb-2 text-uppercase letter-spacing-05 small fw-bold">Ver</label>
                            <select name="per_page" id="per_page" class="form-select form-select-lg form-control-clean">
                                @foreach([5, 10, 15, 20, 25, 50, 100] as $option)
                                    <option value="{{ $option }}" {{ ($perPage ?? 10) == $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                                <option value="all" {{ ($perPage ?? 10) == 'all' ? 'selected' : '' }}>Todos</option>
                            </select>
                        </div>

                        <!-- Acciones rápidas -->
                        <div class="col-lg-2 d-flex align-items-end">
                            <div class="w-100 d-flex flex-column gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="resetFilters">
                                    <i class="fas fa-rotate-left me-1"></i> Limpiar filtros
                                </button>
                                <div class="text-muted small text-end">
                                    Resultados: <strong id="resultsCount">{{ $productos->total() }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros rápidos (Pills) -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <span class="small text-muted me-2 fw-semibold">Estado:</span>
                                <button type="button" class="filter-pill {{ ($estado ?? 'all') === 'all' ? 'active' : '' }}" data-filter="estado" data-value="all">Todos</button>
                                <button type="button" class="filter-pill {{ ($estado ?? 'all') === 'active' ? 'active' : '' }}" data-filter="estado" data-value="active">Activos</button>
                                <button type="button" class="filter-pill {{ ($estado ?? 'all') === 'inactive' ? 'active' : '' }}" data-filter="estado" data-value="inactive">Inactivos</button>

                                <span class="small text-muted mx-2 fw-semibold">|</span>
                                <span class="small text-muted me-2 fw-semibold">Stock:</span>
                                <button type="button" class="filter-pill stock-pill {{ ($stock ?? 'all') === 'all' ? 'active' : '' }}" data-filter="stock" data-value="all">Todo el stock</button>
                                <button type="button" class="filter-pill stock-pill {{ ($stock ?? 'all') === 'low' ? 'active' : '' }}" data-filter="stock" data-value="low">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Bajo stock
                                </button>
                                <button type="button" class="filter-pill stock-pill {{ ($stock ?? 'all') === 'normal' ? 'active' : '' }}" data-filter="stock" data-value="normal">Stock normal</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Acciones de selección -->
            <div class="selection-actions-container" id="selectionActions" style="display: none; padding: 0.75rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e5e7eb;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <span class="fw-semibold small">Productos seleccionados:</span>
                        <span class="badge bg-dark ms-1" id="selectedCount">0</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="deselectAll">
                            <i class="fas fa-times"></i> Deseleccionar
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm" id="exportExcel">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="exportPdf">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body p-0" id="table-container">
                <div class="table-responsive">
                    <table id="datatablesSimple" class="custom-table">
                        <thead>
                            <tr>
                                <th class="checkbox-header" style="width: 40px;">
                                    <div class="custom-checkbox select-all" id="selectAll"></div>
                                </th>
                                <th style="min-width: 200px;">
                                    <button class="sort-btn {{ $sort == 'nombre' ? 'active ' . $direction : '' }}"
                                            data-column="nombre">
                                        Producto <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th style="min-width: 120px;">
                                    <button class="sort-btn {{ $sort == 'precio_venta' ? 'active ' . $direction : '' }}"
                                            data-column="precio_venta">
                                        Precios <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th class="text-center" style="width: 100px;">
                                    <button class="sort-btn {{ $sort == 'stock_total' ? 'active ' . $direction : '' }}"
                                            data-column="stock_total">
                                        Stock <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th style="min-width: 120px;">
                                    <button class="sort-btn {{ $sort == 'categoria' ? 'active ' . $direction : '' }}"
                                            data-column="categoria">
                                        Categoría <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th style="width: 100px;">
                                    <button class="sort-btn {{ $sort == 'estado' ? 'active ' . $direction : '' }}"
                                            data-column="estado">
                                        Estado <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th class="text-center" style="width: 150px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productos as $item)
                                <tr data-product-id="{{ $item->id }}">
                                    <td class="checkbox-cell">
                                        <div class="custom-checkbox product-checkbox" data-product-id="{{ $item->id }}"></div>
                                    </td>
                                    <td>
                                        <div class="product-info">
                                            <div class="product-avatar">
                                                <i class="fas fa-box small"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $item->nombre }}</div>
                                                <span class="info-subtext">Código: {{ $item->codigo }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <span class="text-muted">Bs</span> <span class="fw-semibold">{{ number_format($item->precio_venta, 2) }}</span>
                                            <div class="info-subtext">Costo: Bs {{ number_format($item->precio_compra, 2) }}</div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-column align-items-center gap-2">
                                            <span class="badge {{ ($item->stock_total ?? 0) <= 10 ? 'bg-danger' : 'bg-success' }} px-3 py-1.5" style="font-size: 0.85rem;" title="Stock Total">
                                                Total: {{ number_format($item->stock_total ?? 0, 0) }}
                                            </span>
                                            <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                @foreach ($item->inventarios as $inv)
                                                    <span class="btn-stock-branch border text-nowrap" title="{{ $inv->almacen->nombre }}">
                                                        <i class="fas fa-warehouse text-muted small me-1"></i>
                                                        <span class="stock-name">{{ $inv->almacen->nombre }}:</span>
                                                        <span class="badge-stock {{ $inv->stock <= 5 ? 'low' : 'normal' }}">{{ number_format($inv->stock, 0) }}</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $item->categoria->nombre }}</span>
                                    </td>
                                    <td>
                                        @if($item->estado == 1)
                                            <span class="badge bg-success text-white">Activo</span>
                                        @else
                                            <span class="badge bg-danger text-white">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-action-group">
                                            @can('ver-producto')
                                                <button class="btn-icon-soft btn-ver-producto"
                                                    data-product-id="{{ $item->id }}" title="Ver Detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endcan

                                            @can('editar-producto')
                                                <a href="{{ route('productos.edit', $item) }}" class="btn-icon-soft" title="Editar">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                            @endcan

                                            @can('ajustar-stock')
                                                <a href="{{ route('productos.ajusteCantidad', $item) }}" class="btn-icon-soft adjust" title="Ajustar Stock">
                                                    <i class="fas fa-boxes"></i>
                                                </a>
                                            @endcan

                                            @can('eliminar-producto')
                                                <button type="button" class="btn-icon-soft delete btn-eliminar-producto"
                                                    data-product-id="{{ $item->id }}"
                                                    data-product-nombre="{{ $item->nombre }}"
                                                    data-product-estado="{{ $item->estado }}"
                                                    data-delete-url="{{ route('productos.destroy', $item) }}"
                                                    title="Eliminar/Estado">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr class="table-totals">
                                <td colspan="3" class="text-end">
                                    <span class="totals-label">RESUMEN GENERAL</span>
                                </td>
                                <td class="text-center">
                                    <span class="totals-value">{{ number_format($totalStockGlobal, 0) }}</span>
                                    <span class="totals-subtext">Stock Global</span>
                                </td>
                                <td class="text-center">
                                    <span class="totals-value">{{ $productos->total() }}</span>
                                    <span class="totals-subtext">Productos</span>
                                </td>
                                <td class="text-center">
                                    <span class="totals-value success">{{ $productosActivos }}</span>
                                    <span class="totals-subtext">Activos</span>
                                </td>
                                <td class="text-center">
                                    <span class="totals-value warning">{{ $bajoStockCount }}</span>
                                    <span class="totals-subtext">Bajo Stock</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="p-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted extra-small">
                        Mostrando {{ $productos->firstItem() }} - {{ $productos->lastItem() }} de {{ $productos->total() }} registros
                    </div>
                    <div>
                        {{ $productos->appends(['busqueda' => $busqueda, 'per_page' => $perPage, 'sort' => $sort, 'direction' => $direction, 'estado' => $estado, 'categoria_id' => $categoriaId, 'stock' => $stock])->links() }}
                    </div>
                </div>
            </div>

            <!-- === MODAL ÚNICO: Ver Detalles de Producto (cargado por AJAX) === -->
            <div class="modal fade" id="verProductoModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
                    <div class="modal-content modal-content-clean">
                        <div class="modal-header modal-header-clean">
                            <h5 class="modal-title fs-6">Detalles del Producto</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4" id="verProductoModalBody">
                            <div class="text-center py-5">
                                <div class="spinner-border text-secondary" role="status"></div>
                                <p class="mt-3 text-muted small">Cargando detalles...</p>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- === MODAL ÚNICO: Confirmar Eliminar/Estado === -->
            <div class="modal fade" id="confirmProductoModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content modal-content-clean">
                        <div class="modal-header modal-header-clean">
                            <h5 class="modal-title fs-6">Confirmar acción</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4 text-center">
                            <h6 class="mb-3" id="confirmModalTitle"></h6>
                            <p class="text-muted small mb-4" id="confirmModalText"></p>
                            <div class="d-flex justify-content-center gap-2" id="confirmModalActions"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overlay de carga para "Todos" -->
            <div id="loadingOverlay" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,0.45); backdrop-filter:blur(3px); align-items:center; justify-content:center; flex-direction:column;">
                <div class="spinner-border text-light mb-3" style="width:3rem; height:3rem;" role="status"></div>
                <p class="text-white fw-semibold mb-1">Cargando todos los productos...</p>
                <p class="text-white-50 small">Esto puede tardar unos segundos</p>
            </div>

            <!-- Modal de Exportación Genérico -->
            <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content modal-content-clean">
                        <div class="modal-header modal-header-clean">
                            <h5 class="modal-title fs-6" id="exportModalTitle">
                                <i class="fas fa-file-export me-2"></i> Exportar Productos
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <input type="hidden" id="exportFormat" value="excel">
                            <div class="alert alert-success border-0 bg-success bg-opacity-10 d-flex align-items-center mb-4" id="exportAlert" style="border-radius: 12px;">
                                <i class="fas fa-info-circle me-3 fs-5 text-success" id="exportAlertIcon"></i>
                                <div class="small fw-medium text-success">
                                    Se exportarán <strong id="exportCountDisplay">0</strong> productos seleccionados.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="info-subtext mb-2 text-uppercase letter-spacing-05 small fw-bold">Opciones de Datos</label>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="modalIncludePrices" checked>
                                    <label class="form-check-label d-block" for="modalIncludePrices">
                                        <span class="d-block fw-semibold small">Incluir precios</span>
                                        <span class="extra-small text-muted">Precio de compra y venta unitario</span>
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="modalIncludeStock" checked>
                                    <label class="form-check-label d-block" for="modalIncludeStock">
                                        <span class="d-block fw-semibold small">Incluir stock</span>
                                        <span class="extra-small text-muted">Cantidades totales en almacenes</span>
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="modalIncludeAllDetails" checked>
                                    <label class="form-check-label d-block" for="modalIncludeAllDetails">
                                        <span class="d-block fw-semibold small">Incluir todos los detalles</span>
                                        <span class="extra-small text-muted">Categorías, marcas e información técnica</span>
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
    let debounceTimer;
    const tableContainer = document.getElementById('table-container');
    let selectedProducts = new Set();



    function initializeSelectionSystem() {
        const selectionActions = document.getElementById('selectionActions');
        const selectAllCheckbox = document.getElementById('selectAll');
        const productCheckboxes = document.querySelectorAll('.product-checkbox');
        const selectedCountElement = document.getElementById('selectedCount');
        const deselectAllBtn = document.getElementById('deselectAll');
        const exportExcelBtn = document.getElementById('exportExcel');
        const exportPdfBtn = document.getElementById('exportPdf');

        productCheckboxes.forEach(checkbox => {
            checkbox.removeEventListener('click', handleCheckboxClick);
            checkbox.addEventListener('click', handleCheckboxClick);
        });

        function handleCheckboxClick() {
            const productId = this.dataset.productId;
            const row = this.closest('tr');

            if (this.classList.contains('checked')) {
                this.classList.remove('checked');
                row.classList.remove('selected');
                selectedProducts.delete(productId);
            } else {
                this.classList.add('checked');
                row.classList.add('selected');
                selectedProducts.add(productId);
            }

            updateSelectionUI();
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.removeEventListener('click', handleSelectAll);
            selectAllCheckbox.addEventListener('click', handleSelectAll);
        }

        function handleSelectAll() {
            const isSelectAll = !this.classList.contains('checked');

            productCheckboxes.forEach(checkbox => {
                const productId = checkbox.dataset.productId;
                const row = checkbox.closest('tr');

                if (isSelectAll) {
                    checkbox.classList.add('checked');
                    row.classList.add('selected');
                    selectedProducts.add(productId);
                } else {
                    checkbox.classList.remove('checked');
                    row.classList.remove('selected');
                    selectedProducts.delete(productId);
                }
            });

            this.classList.toggle('checked');
            updateSelectionUI();
        }

        if (deselectAllBtn && !deselectAllBtn.dataset.bound) {
            deselectAllBtn.addEventListener('click', function() {
                selectedProducts.clear();
                productCheckboxes.forEach(checkbox => {
                    checkbox.classList.remove('checked');
                    const row = checkbox.closest('tr');
                    if (row) row.classList.remove('selected');
                });
                if (selectAllCheckbox) selectAllCheckbox.classList.remove('checked');
                updateSelectionUI();
            });
            deselectAllBtn.dataset.bound = '1';
        }

        const btnExportAllExcel = document.getElementById('btnExportAllExcel');
        const btnExportAllPdf = document.getElementById('btnExportAllPdf');

        if (btnExportAllExcel && !btnExportAllExcel.dataset.bound) {
            btnExportAllExcel.addEventListener('click', () => openExportModal('excel', true));
            btnExportAllExcel.dataset.bound = '1';
        }

        if (btnExportAllPdf && !btnExportAllPdf.dataset.bound) {
            btnExportAllPdf.addEventListener('click', () => openExportModal('pdf', true));
            btnExportAllPdf.dataset.bound = '1';
        }

        if (exportExcelBtn && !exportExcelBtn.dataset.bound) {
            exportExcelBtn.addEventListener('click', () => openExportModal('excel', false));
            exportExcelBtn.dataset.bound = '1';
        }

        if (exportPdfBtn && !exportPdfBtn.dataset.bound) {
            exportPdfBtn.addEventListener('click', () => openExportModal('pdf', false));
            exportPdfBtn.dataset.bound = '1';
        }

        function openExportModal(format, isAll = false) {
            const modal = new bootstrap.Modal(document.getElementById('exportModal'));
            const title = document.getElementById('exportModalTitle');
            const formatInput = document.getElementById('exportFormat');
            const confirmBtn = document.getElementById('confirmExportBtn');
            const alertBox = document.getElementById('exportAlert');
            const alertIcon = document.getElementById('exportAlertIcon');

            formatInput.value = format;

            if (isAll) {
                selectedProducts.clear();
                updateSelectionUI();
                alertBox.querySelector('.small.fw-medium').innerHTML = 'Se exportarán <strong>todos</strong> los productos aplicando los filtros actuales.';
            } else {
                alertBox.querySelector('.small.fw-medium').innerHTML = `Se exportarán <strong id="exportCountDisplay">${selectedProducts.size}</strong> productos seleccionados.`;
            }

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
            const count = selectedProducts.size;
            if (selectedCountElement) selectedCountElement.textContent = count;

            if (selectionActions) {
                if (count > 0) {
                    selectionActions.style.display = 'block';
                    const totalCheckboxes = productCheckboxes.length;
                    if (selectAllCheckbox) {
                        if (count === totalCheckboxes) {
                            selectAllCheckbox.classList.add('checked');
                        } else {
                            selectAllCheckbox.classList.remove('checked');
                        }
                    }
                } else {
                    selectionActions.style.display = 'none';
                    if (selectAllCheckbox) selectAllCheckbox.classList.remove('checked');
                }
            }
        }

        updateSelectionUI();
    }

    function buildQueryParams() {
        const searchInput = document.querySelector('input[name="busqueda"]');
        const perPageSelect = document.getElementById('per_page');
        const categoriaSelect = document.getElementById('categoria_id');
        const estadoInput = document.getElementById('estado');
        const stockFilterInput = document.getElementById('stock');
        const almacenSelect = document.getElementById('almacen_id');

        const params = new URLSearchParams();
        if (searchInput && searchInput.value.trim()) params.set('busqueda', searchInput.value.trim());
        if (perPageSelect) params.set('per_page', perPageSelect.value);
        if (categoriaSelect) params.set('categoria_id', categoriaSelect.value);
        if (estadoInput) params.set('estado', estadoInput.value);
        if (stockFilterInput) params.set('stock', stockFilterInput.value);
        if (almacenSelect) params.set('almacen_id', almacenSelect.value);

        const currentUrl = new URL(window.location.href);
        if (currentUrl.searchParams.get('sort')) params.set('sort', currentUrl.searchParams.get('sort'));
        if (currentUrl.searchParams.get('direction')) params.set('direction', currentUrl.searchParams.get('direction'));

        return params;
    }

    function syncFilterPills() {
        const estadoInput = document.getElementById('estado');
        const stockFilterInput = document.getElementById('stock');

        document.querySelectorAll('.filter-pill[data-filter="estado"]').forEach(btn => {
            btn.classList.toggle('active', estadoInput && estadoInput.value === btn.dataset.value);
        });

        document.querySelectorAll('.filter-pill[data-filter="stock"]').forEach(btn => {
            btn.classList.toggle('active', stockFilterInput && stockFilterInput.value === btn.dataset.value);
        });
    }

    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'confirmExportBtn') {
            const format = document.getElementById('exportFormat').value;
            const productIds = Array.from(selectedProducts);
            const includePrices = document.getElementById('modalIncludePrices').checked;
            const includeStock = document.getElementById('modalIncludeStock').checked;
            const includeAllDetails = document.getElementById('modalIncludeAllDetails').checked;

            const modalElement = document.getElementById('exportModal');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) modalInstance.hide();

            Swal.fire({
                title: 'Generando archivo...',
                text: 'Por favor espere un momento.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = format === 'excel' ? '{{ route("productos.export.excel") }}' : '{{ route("productos.export.pdf") }}';

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            productIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'product_ids[]';
                input.value = id;
                form.appendChild(input);
            });

            // Copiar los filtros activos al formulario de exportación
            const params = buildQueryParams();
            for (const [key, value] of params.entries()) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }

            const options = { includePrices, includeStock, includeAllDetails };
            Object.keys(options).forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = options[key] ? '1' : '0';
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);

            setTimeout(() => {
                if (document.getElementById('deselectAll')) {
                    document.getElementById('deselectAll').click();
                }
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

    function initializeEvents() {
        const searchInput = document.querySelector('input[name="busqueda"]');
        const perPageSelect = document.getElementById('per_page');
        const sortButtons = document.querySelectorAll('.sort-btn');

        if (searchInput && !searchInput.dataset.bound) {
            searchInput.focus();
            const len = searchInput.value.length;
            searchInput.setSelectionRange(len, len);

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => fetchProducts(), 300);
            });
            searchInput.dataset.bound = '1';
        }

        if (perPageSelect && !perPageSelect.dataset.bound) {
            perPageSelect.addEventListener('change', () => fetchProducts());
            perPageSelect.dataset.bound = '1';
        }

        const categoriaSelect = document.getElementById('categoria_id');
        if (categoriaSelect && !categoriaSelect.dataset.bound) {
            categoriaSelect.addEventListener('change', () => fetchProducts());
            categoriaSelect.dataset.bound = '1';
        }

        const almacenSelect = document.getElementById('almacen_id');
        if (almacenSelect && !almacenSelect.dataset.bound) {
            almacenSelect.addEventListener('change', () => fetchProducts());
            almacenSelect.dataset.bound = '1';
        }

        const resetFiltersBtn = document.getElementById('resetFilters');
        if (resetFiltersBtn && !resetFiltersBtn.dataset.bound) {
            resetFiltersBtn.addEventListener('click', function() {
                if (searchInput) searchInput.value = '';
                if (perPageSelect) perPageSelect.value = '10';
                if (categoriaSelect) categoriaSelect.value = 'all';
                if (almacenSelect) almacenSelect.value = 'all';
                const estadoInput = document.getElementById('estado');
                if (estadoInput) estadoInput.value = 'all';
                const stockFilterInput = document.getElementById('stock');
                if (stockFilterInput) stockFilterInput.value = 'all';
                syncFilterPills();
                fetchProducts();
            });
            resetFiltersBtn.dataset.bound = '1';
        }

        sortButtons.forEach(btn => {
            if (btn.dataset.bound) return;
            btn.addEventListener('click', function() {
                const column = this.dataset.column;
                const currentUrl = new URL(window.location.href);
                let direction = 'asc';

                if (currentUrl.searchParams.get('sort') === column) {
                    direction = currentUrl.searchParams.get('direction') === 'asc' ? 'desc' : 'asc';
                }

                const params = buildQueryParams();
                params.set('sort', column);
                params.set('direction', direction);

                fetchProducts(`{{ route('productos.index') }}?${params.toString()}`);
            });
            btn.dataset.bound = '1';
        });

        document.querySelectorAll('.filter-pill').forEach(pill => {
            if (pill.dataset.bound) return;
            pill.addEventListener('click', function() {
                const filter = this.dataset.filter;
                const value = this.dataset.value;
                const estadoInput = document.getElementById('estado');
                const stockFilterInput = document.getElementById('stock');

                if (filter === 'estado' && estadoInput) {
                    estadoInput.value = value;
                }

                if (filter === 'stock' && stockFilterInput) {
                    stockFilterInput.value = value;
                }

                syncFilterPills();
                fetchProducts();
            });
            pill.dataset.bound = '1';
        });
    }

    function fetchProducts(url = null) {
        let fetchUrl = url;
        if (!fetchUrl) {
            const params = buildQueryParams();
            fetchUrl = `{{ route('productos.index') }}?${params.toString()}`;
        }

        // Mostrar overlay si se solicita "Todos"
        const perPageSelect = document.getElementById('per_page');
        const isAll = perPageSelect && perPageSelect.value === 'all';
        const overlay = document.getElementById('loadingOverlay');
        if (isAll && overlay) {
            overlay.style.display = 'flex';
        } else if (tableContainer) {
            tableContainer.style.opacity = '0.6';
        }

        fetch(fetchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.text();
        })
        .then(html => {
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');
            const newContainer = newDoc.getElementById('table-container');

            if (newContainer && tableContainer) {
                tableContainer.innerHTML = newContainer.innerHTML;
            }

            const resultsCount = newDoc.getElementById('resultsCount');
            const resultsCountElement = document.getElementById('resultsCount');
            if (resultsCountElement && resultsCount) {
                resultsCountElement.textContent = resultsCount.textContent;
            }

            if (tableContainer) tableContainer.style.opacity = '1';
            if (overlay) overlay.style.display = 'none';

            selectedProducts.clear();
            const selectionActions = document.getElementById('selectionActions');
            if (selectionActions) selectionActions.style.display = 'none';

            window.history.pushState({}, '', fetchUrl);
            syncFilterPills();
            initializeEvents();
            initializeSelectionSystem();
        })
        .catch(error => {
            console.error('Error al cargar productos:', error);
            if (tableContainer) tableContainer.style.opacity = '1';
            if (overlay) overlay.style.display = 'none';
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los productos. Intente nuevamente.',
                timer: 3000,
                showConfirmButton: false
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        syncFilterPills();
        initializeEvents();
        initializeSelectionSystem();
    });

    // ====== MODAL DINÁMICO: Ver Detalles ======
    // Usamos route() con ID=0 como placeholder y lo reemplazamos en JS con el ID real
    const detalleUrlTemplate = '{{ route("productos.detalle", 0) }}';
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-ver-producto');
        if (!btn) return;
        const productId = btn.dataset.productId;
        const modalBody = document.getElementById('verProductoModalBody');
        const modalEl   = document.getElementById('verProductoModal');

        // Mostrar spinner
        modalBody.innerHTML = `<div class="text-center py-5">
            <div class="spinner-border text-secondary" role="status"></div>
            <p class="mt-3 text-muted small">Cargando detalles...</p>
        </div>`;

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        const fetchUrl = detalleUrlTemplate.replace('/0/', `/${productId}/`);
        fetch(fetchUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(p => {
            const stockHtml = p.inventarios.length
                ? p.inventarios.map(inv => `
                    <div class="col-md-6 mb-2">
                        <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-white">
                            <span class="small fw-medium">${inv.almacen}</span>
                            <span class="badge ${inv.stock <= 5 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success'} px-3">${inv.stock}</span>
                        </div>
                    </div>`).join('')
                : '<div class="col-12"><div class="alert alert-light border py-2 small">Sin registros de stock</div></div>';

            modalBody.innerHTML = `
                <div class="row g-4 d-flex align-items-center mb-4">
                    <div class="col-auto">
                        <div class="product-avatar" style="width:60px;height:60px;font-size:1.5rem;"><i class="fas fa-box"></i></div>
                    </div>
                    <div class="col">
                        <h4 class="mb-1 text-dark fw-bold">${p.nombre}</h4>
                        <span class="badge bg-light text-dark border">${p.codigo}</span>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="info-subtext mb-1">Descripción</label>
                        <div class="p-2 border rounded bg-light small">${p.descripcion || 'Sin descripción'}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="info-subtext mb-1">Precio Venta</label>
                        <div class="fw-bold">Bs ${parseFloat(p.precio_venta).toFixed(2)}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="info-subtext mb-1">Precio Compra</label>
                        <div class="fw-bold text-muted">Bs ${parseFloat(p.precio_compra).toFixed(2)}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="info-subtext mb-1">Categoría</label>
                        <div><i class="fas fa-tag me-1 small"></i>${p.categoria || '-'}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="info-subtext mb-1">Marca</label>
                        <div><i class="fas fa-copyright me-1 small"></i>${p.marca || '-'}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="info-subtext mb-1">Unidad</label>
                        <div><i class="fas fa-ruler me-1 small"></i>${p.tipounidad || '-'}</div>
                    </div>
                </div>
                <div class="mt-4">
                    <h6 class="fw-semibold border-bottom pb-2 small"><i class="fas fa-warehouse me-2 text-muted"></i>Stock por Almacén</h6>
                    <div class="row mt-2">${stockHtml}</div>
                </div>`;
        })
        .catch(() => {
            modalBody.innerHTML = '<div class="alert alert-danger m-3">Error al cargar los detalles.</div>';
        });
    });

    // ====== MODAL DINÁMICO: Confirmar Eliminar/Estado ======
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-eliminar-producto');
        if (!btn) return;

        const nombre  = btn.dataset.productNombre;
        const estado  = parseInt(btn.dataset.productEstado);
        const url     = btn.dataset.deleteUrl;
        const csrf    = '{{ csrf_token() }}';

        const titleEl   = document.getElementById('confirmModalTitle');
        const textEl    = document.getElementById('confirmModalText');
        const actionsEl = document.getElementById('confirmModalActions');

        if (estado === 1) {
            titleEl.textContent   = '¿Eliminar o Desactivar producto?';
            textEl.innerHTML      = `Puede <strong>desactivar</strong> <em>${nombre}</em> para que no aparezca en ventas, o <strong>eliminarlo</strong> permanentemente.`;
            actionsEl.innerHTML   = `
                <button class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancelar</button>
                <form action="${url}" method="post" class="d-inline">
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="_token" value="${csrf}">
                    <input type="hidden" name="accion" value="inactivar">
                    <button type="submit" class="btn btn-outline-warning btn-sm px-3">Desactivar</button>
                </form>
                <form action="${url}" method="post" class="d-inline">
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="_token" value="${csrf}">
                    <input type="hidden" name="accion" value="eliminar">
                    <button type="submit" class="btn btn-outline-danger btn-sm px-3">Eliminar</button>
                </form>`;
        } else {
            titleEl.textContent   = '¿Restaurar producto?';
            textEl.innerHTML      = `El producto <em>${nombre}</em> volverá a estar <strong>activo</strong> en el sistema.`;
            actionsEl.innerHTML   = `
                <button class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancelar</button>
                <form action="${url}" method="post" class="d-inline">
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="_token" value="${csrf}">
                    <input type="hidden" name="accion" value="activar">
                    <button type="submit" class="btn btn-outline-success btn-sm px-3">Activar</button>
                </form>`;
        }

        bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmProductoModal')).show();
    });

    // Click en fila abre el modal de detalles
    document.addEventListener('click', function(e) {
        if (e.target.closest('tr[data-product-id]') && !e.target.closest('.btn-action-group') && !e.target.closest('.custom-checkbox')) {
            const row = e.target.closest('tr[data-product-id]');
            if (row) {
                const btn = row.querySelector('.btn-ver-producto');
                if (btn) btn.click();
            }
        }
    });
</script>
@endpush
