@extends('admin.layouts.app')

@section('title', 'Productos')

@push('css-datatable')
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/style_Categoria.css') }}">
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
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0" style="padding: 0.4rem 0.75rem;">
                                    <i class="fas fa-search text-muted small"></i>
                                </span>
                                <input type="text" name="busqueda" class="form-control form-control-clean border-start-0 ps-0"
                                    placeholder="Buscar producto..." value="{{ $busqueda ?? '' }}">
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
                            <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 6px;">
                                <i class="fas fa-undo me-1"></i> Mostrar Todo
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- NUEVA SECCION: ACCIONES DE SELECCION -->
            <div class="selection-actions-container" id="selectionActions" style="display: none;">
                <div class="selected-counter">
                    <span>Productos seleccionados:</span>
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
                                    <div class="custom-checkbox select-all" id="selectAll">
                                    </div>
                                </th>
                                <th>
                                    <button class="sort-btn {{ $sort == 'nombre' ? 'active ' . $direction : '' }}"
                                            data-column="nombre">
                                        Producto <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th>
                                    <button class="sort-btn {{ $sort == 'precio_venta' ? 'active ' . $direction : '' }}"
                                            data-column="precio_venta">
                                        Precios <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th class="text-center">
                                    <button class="sort-btn {{ $sort == 'stock_total' ? 'active ' . $direction : '' }}"
                                            data-column="stock_total">
                                        Stock Total <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th>
                                    <button class="sort-btn {{ $sort == 'categoria' ? 'active ' . $direction : '' }}"
                                            data-column="categoria">
                                        Categoria <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th>
                                    <button class="sort-btn {{ $sort == 'estado' ? 'active ' . $direction : '' }}"
                                            data-column="estado">
                                        Estado <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th class="text-center">Acciones</th>
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
                                        <span class="badge-pill {{ $totalStock <= 10 ? 'badge-danger' : 'badge-success' }}">
                                            {{ $totalStock }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $item->categoria->nombre }}</span>
                                    </td>
                                    <td>
                                        @if ($item->estado == 1)
                                            <span class="badge-pill badge-success">Activo</span>
                                        @else
                                            <span class="badge-pill badge-danger">Inactivo</span>
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
                                                        <label class="info-subtext mb-1">Categoria</label>
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
                                                        <i class="fas fa-warehouse me-2 text-muted"></i>Stock por Almacen
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

                                <!-- Modal de confirmacion -->
                                <div class="modal fade" id="confirmModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content modal-content-clean">
                                            <div class="modal-header modal-header-clean">
                                                <h5 class="modal-title fs-6">Confirmar accion</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4 text-center">
                                                <h6 class="mb-3">
                                                    @if($item->estado == 1)
                                                        Â¿Eliminar o Desactivar producto?
                                                    @else
                                                        Â¿Restaurar producto?
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
                        {{ $productos->appends(['busqueda' => $busqueda, 'per_page' => $perPage, 'sort' => $sort, 'direction' => $direction])->links() }}
                    </div>
                </div>
            </div>
        <!-- Modal de Exportacion Genérico -->
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
@endsection

@push('js')
<script>
    let debounceTimer;
    const tableContainer = document.getElementById('table-container');
    let selectedProducts = new Set();

    // NUEVO: Sistema de selección
    function initializeSelectionSystem() {
        const selectionActions = document.getElementById('selectionActions');
        const selectAllCheckbox = document.getElementById('selectAll');
        const productCheckboxes = document.querySelectorAll('.product-checkbox');
        const selectedCountElement = document.getElementById('selectedCount');
        const deselectAllBtn = document.getElementById('deselectAll');
        const exportExcelBtn = document.getElementById('exportExcel');
        const exportPdfBtn = document.getElementById('exportPdf');

        // Seleccionar/Deseleccionar producto individual
        productCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const row = this.closest('tr');

                if (this.classList.contains('checked')) {
                    // Deseleccionar
                    this.classList.remove('checked');
                    row.classList.remove('selected');
                    selectedProducts.delete(productId);
                } else {
                    // Seleccionar
                    this.classList.add('checked');
                    row.classList.add('selected');
                    selectedProducts.add(productId);
                }

                updateSelectionUI();
            });
        });

        // Seleccionar todos
        selectAllCheckbox.addEventListener('click', function() {
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

            selectAllCheckbox.classList.toggle('checked');
            updateSelectionUI();
        });

        // Deseleccionar todos
        deselectAllBtn.addEventListener('click', function() {
            selectedProducts.clear();
            productCheckboxes.forEach(checkbox => {
                checkbox.classList.remove('checked');
                const row = checkbox.closest('tr');
                row.classList.remove('selected');
            });
            selectAllCheckbox.classList.remove('checked');
            updateSelectionUI();
        });

        // Eventos de Exportacion
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
            selectedCountElement.textContent = count;

            if (count > 0) {
                selectionActions.style.display = 'flex';
                const totalCheckboxes = productCheckboxes.length;
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

    // Manejar confirmacion de exportacion
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'confirmExportBtn') {
            const format = document.getElementById('exportFormat').value;
            const productIds = Array.from(selectedProducts);
            const includePrices = document.getElementById('modalIncludePrices').checked;
            const includeStock = document.getElementById('modalIncludeStock').checked;
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
                input.value = options[key];
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();

            // Limpiar seleccion
            setTimeout(() => {
                document.getElementById('deselectAll').click();
                Swal.fire({
                    icon: 'success',
                    title: 'Exportacion iniciada',
                    text: 'El archivo se descargará automaticamente.',
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

        // Búsqueda
        if (searchInput) {
            searchInput.focus();
            const len = searchInput.value.length;
            searchInput.setSelectionRange(len, len);

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => fetchProducts(), 300);
            });
        }

        // Items por página
        if (perPageSelect) {
            perPageSelect.addEventListener('change', () => fetchProducts());
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

                fetchProducts(`{{ route('productos.index') }}?${params.toString()}`);
            });
        });
    }

    function fetchProducts(url = null) {
        const searchInput = document.querySelector('input[name="busqueda"]');
        const perPageSelect = document.getElementById('per_page');

        let fetchUrl = url;
        if (!fetchUrl) {
            const params = new URLSearchParams(window.location.search);
            if (searchInput) params.set('busqueda', searchInput.value);
            if (perPageSelect) params.set('per_page', perPageSelect.value);
            fetchUrl = `{{ route('productos.index') }}?${params.toString()}`;
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

                // Limpiar seleccion al actualizar
                selectedProducts.clear();
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

    document.addEventListener('DOMContentLoaded', function() {
        initializeEvents();
        initializeSelectionSystem();
    });

    // Manejar clic en fila (seleccionar producto)
    document.addEventListener('click', function(e) {
        if (e.target.closest('tr[data-product-id]') && !e.target.closest('.btn-action-group') && !e.target.closest('.custom-checkbox')) {
            const row = e.target.closest('tr[data-product-id]');
            const productId = row.dataset.productId;
            const checkbox = row.querySelector('.product-checkbox');

            if (checkbox) {
                checkbox.click();
            }
        }
    });
</script>
@endpush

