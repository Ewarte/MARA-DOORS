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
            @can('crear-producto')
            <a href="{{ route('productos.create') }}" class="btn-create">
                <i class="fas fa-plus"></i> Nuevo Producto
            </a>
            @endcan
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
                    <input type="hidden" name="stock_filter" id="stock_filter" value="{{ $stockFilter ?? 'all' }}">

                    <div class="row g-3">
                        <!-- Búsqueda -->
                        <div class="col-lg-4">
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

                        <!-- Ver por página -->
                        <div class="col-lg-1">
                            <label for="per_page" class="info-subtext mb-2 text-uppercase letter-spacing-05 small fw-bold">Ver</label>
                            <select name="per_page" id="per_page" class="form-select form-select-lg form-control-clean">
                                @foreach([5, 10, 15, 20, 25] as $option)
                                    <option value="{{ $option }}" {{ ($perPage ?? 10) == $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
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
                                <button type="button" class="filter-pill stock-pill {{ ($stockFilter ?? 'all') === 'all' ? 'active' : '' }}" data-filter="stock" data-value="all">Todo el stock</button>
                                <button type="button" class="filter-pill stock-pill {{ ($stockFilter ?? 'all') === 'low' ? 'active' : '' }}" data-filter="stock" data-value="low">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Bajo stock
                                </button>
                                <button type="button" class="filter-pill stock-pill {{ ($stockFilter ?? 'all') === 'normal' ? 'active' : '' }}" data-filter="stock" data-value="normal">Stock normal</button>
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
                                        @php
                                            $totalStock = $item->inventarios->sum('stock');
                                        @endphp
                                        <span class="badge {{ $totalStock <= 10 ? 'bg-danger' : 'bg-success' }}">
                                            {{ $totalStock }}
                                        </span>
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
                                                <button class="btn-icon-soft" data-bs-toggle="modal"
                                                    data-bs-target="#verModal-{{ $item->id }}" title="Ver Detalles">
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
                                                <button type="button" class="btn-icon-soft delete" data-bs-toggle="modal"
                                                    data-bs-target="#confirmModal-{{ $item->id }}" title="Eliminar/Estado">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal de detalles -->
                                <div class="modal fade" id="verModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
                                        <div class="modal-content modal-content-clean">
                                            <div class="modal-header modal-header-clean">
                                                <h5 class="modal-title fs-6">Detalles del Producto</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="row g-4 d-flex align-items-center mb-4">
                                                    <div class="col-auto">
                                                        <div class="product-avatar" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                                            <i class="fas fa-box"></i>
                                                        </div>
                                                    </div>
                                                    <div class="col">
                                                        <h4 class="mb-1 text-dark fw-bold">{{ $item->nombre }}</h4>
                                                        <span class="badge bg-light text-dark border">{{ $item->codigo }}</span>
                                                    </div>
                                                </div>

                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="info-subtext mb-1">Descripción</label>
                                                        <div class="p-2 border rounded bg-light small">{{ $item->descripcion ?? 'Sin descripción' }}</div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="info-subtext mb-1">Precio Venta</label>
                                                        <div class="fw-bold">Bs {{ number_format($item->precio_venta, 2) }}</div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="info-subtext mb-1">Precio Compra</label>
                                                        <div class="fw-bold text-muted">Bs {{ number_format($item->precio_compra, 2) }}</div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="info-subtext mb-1">Categoría</label>
                                                        <div><i class="fas fa-tag me-1 small"></i>{{ $item->categoria->nombre }}</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="info-subtext mb-1">Marca</label>
                                                        <div><i class="fas fa-copyright me-1 small"></i>{{ $item->marca->nombre }}</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="info-subtext mb-1">Unidad</label>
                                                        <div><i class="fas fa-ruler me-1 small"></i>{{ $item->tipounidad->nombre }}</div>
                                                    </div>
                                                </div>

                                                <div class="mt-4">
                                                    <h6 class="fw-semibold border-bottom pb-2 small uppercase letter-spacing-05">
                                                        <i class="fas fa-warehouse me-2 text-muted"></i>Stock por Almacén
                                                    </h6>
                                                    <div class="row mt-2">
                                                        @forelse($item->inventarios as $inv)
                                                            <div class="col-md-6 mb-2">
                                                                <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-white">
                                                                    <span class="small fw-medium">{{ $inv->almacen->nombre }}</span>
                                                                    <span class="badge {{ $inv->stock <= 5 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }} px-3">
                                                                        {{ $inv->stock }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        @empty
                                                            <div class="col-12">
                                                                <div class="alert alert-light border py-2 small">Sin registros de stock</div>
                                                            </div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal de confirmación -->
                                <div class="modal fade" id="confirmModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content modal-content-clean">
                                            <div class="modal-header modal-header-clean">
                                                <h5 class="modal-title fs-6">Confirmar acción</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4 text-center">
                                                <h6 class="mb-3">
                                                    @if($item->estado == 1)
                                                        ¿Eliminar o Desactivar producto?
                                                    @else
                                                        ¿Restaurar producto?
                                                    @endif
                                                </h6>
                                                <p class="text-muted small mb-4">
                                                    @if($item->estado == 1)
                                                        Puede <strong>desactivar</strong> el producto para que no aparezca en ventas, o <strong>eliminarlo</strong> permanentemente pero solo si no tiene registros de ventas o compras.
                                                    @else
                                                        El producto volverá a estar <strong>activo</strong> en el sistema.
                                                    @endif
                                                </p>

                                                <div class="d-flex justify-content-center gap-2">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancelar</button>

                                                    @if($item->estado == 1)
                                                        <form action="{{ route('productos.destroy', $item) }}" method="post" class="d-inline">
                                                            @method('DELETE')
                                                            @csrf
                                                            <input type="hidden" name="accion" value="inactivar">
                                                            <button type="submit" class="btn btn-outline-warning btn-sm px-3">Desactivar</button>
                                                        </form>
                                                        <form action="{{ route('productos.destroy', $item) }}" method="post" class="d-inline">
                                                            @method('DELETE')
                                                            @csrf
                                                            <input type="hidden" name="accion" value="eliminar">
                                                            <button type="submit" class="btn btn-outline-danger btn-sm px-3">Eliminar</button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('productos.destroy', $item) }}" method="post" class="d-inline">
                                                            @method('DELETE')
                                                            @csrf
                                                            <input type="hidden" name="accion" value="activar">
                                                            <button type="submit" class="btn btn-outline-success btn-sm px-3">Activar</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
            <div id="product-modals"></div>

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
    const productModals = document.getElementById('product-modals');
    let selectedProducts = new Set();

    function moveProductModals() {
        if (!tableContainer || !productModals) return;

        const modals = tableContainer.querySelectorAll('.modal.fade[id^="verModal-"], .modal.fade[id^="confirmModal-"]');
        productModals.innerHTML = '';

        modals.forEach(modal => {
            productModals.appendChild(modal);
        });
    }

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

        if (exportExcelBtn && !exportExcelBtn.dataset.bound) {
            exportExcelBtn.addEventListener('click', () => openExportModal('excel'));
            exportExcelBtn.dataset.bound = '1';
        }

        if (exportPdfBtn && !exportPdfBtn.dataset.bound) {
            exportPdfBtn.addEventListener('click', () => openExportModal('pdf'));
            exportPdfBtn.dataset.bound = '1';
        }

        function openExportModal(format) {
            const modal = new bootstrap.Modal(document.getElementById('exportModal'));
            const title = document.getElementById('exportModalTitle');
            const formatInput = document.getElementById('exportFormat');
            const confirmBtn = document.getElementById('confirmExportBtn');
            const alertBox = document.getElementById('exportAlert');
            const alertIcon = document.getElementById('exportAlertIcon');

            formatInput.value = format;
            document.getElementById('exportCountDisplay').textContent = selectedProducts.size;

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

        const params = new URLSearchParams();
        if (searchInput && searchInput.value.trim()) params.set('busqueda', searchInput.value.trim());
        if (perPageSelect) params.set('per_page', perPageSelect.value);
        if (categoriaSelect) params.set('categoria_id', categoriaSelect.value);
        if (estadoInput) params.set('estado', estadoInput.value);
        if (stockFilterInput) params.set('stock', stockFilterInput.value);

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

        const resetFiltersBtn = document.getElementById('resetFilters');
        if (resetFiltersBtn && !resetFiltersBtn.dataset.bound) {
            resetFiltersBtn.addEventListener('click', function() {
                if (searchInput) searchInput.value = '';
                if (perPageSelect) perPageSelect.value = '10';
                if (categoriaSelect) categoriaSelect.value = 'all';
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

        if (tableContainer) {
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
                moveProductModals();
            }

            const resultsCount = newDoc.getElementById('resultsCount');
            const resultsCountElement = document.getElementById('resultsCount');
            if (resultsCountElement && resultsCount) {
                resultsCountElement.textContent = resultsCount.textContent;
            }

            if (tableContainer) {
                tableContainer.style.opacity = '1';
            }

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
            if (tableContainer) {
                tableContainer.style.opacity = '1';
            }
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
        moveProductModals();
        syncFilterPills();
        initializeEvents();
        initializeSelectionSystem();
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('tr[data-product-id]') && !e.target.closest('.btn-action-group') && !e.target.closest('.custom-checkbox')) {
            const row = e.target.closest('tr[data-product-id]');
            if (row) {
                const productId = row.dataset.productId;
                const viewBtn = row.querySelector(`[data-bs-target="#verModal-${productId}"]`);
                if (viewBtn) {
                    viewBtn.click();
                }
            }
        }
    });
</script>
@endpush
