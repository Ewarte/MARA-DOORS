@extends('admin.layouts.app')

@section('title', 'Proveedores')

@push('css-datatable')
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css " rel="stylesheet" type="text/css">
@endpush

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11 "></script>
    <link rel="stylesheet" href="{{ asset('css/style_Categoria.css') }}">
@endpush

@section('content')
    @include('admin.layouts.partials.alert')

    <div class="container-fluid px-4 py-4">

        <div class="page-header">
            <div>
                <h1 class="page-title">Proveedores</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none text-muted">Inicio</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Proveedores</li>
                    </ol>
                </nav>
            </div>
            @can('crear-proveedor')
            <a href="{{ route('proveedores.create') }}" class="btn-create">
                <i class="fas fa-plus"></i> Nuevo Proveedor
            </a>
            @endcan
        </div>

        <div class="card-clean">
            <div class="card-header-clean">
                <div class="card-header-title">
                    <i class="fas fa-truck"></i> Lista de Proveedores
                </div>
            </div>

            <!-- SECCION: BUSQUEDA Y FILTROS -->
            <div class="search-container">
                <form action="{{ route('proveedores.index') }}" method="GET" id="searchForm">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0" style="padding: 0.4rem 0.75rem;">
                                    <i class="fas fa-search text-muted small"></i>
                                </span>
                                <input type="text" name="busqueda" class="form-control form-control-clean border-start-0 ps-0"
                                    placeholder="Buscar proveedor..." value="{{ $busqueda ?? '' }}">
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
                            <a href="{{ route('proveedores.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 6px;">
                                <i class="fas fa-undo me-1"></i> Mostrar Todo
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- SECCION: ACCIONES DE SELECCION -->
            <div class="selection-actions-container" id="selectionActions" style="display: none;">
                <div class="selected-counter">
                    <span>Proveedores seleccionados:</span>
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
                                <th>Proveedor / Razon Social</th>
                                <th>Contacto</th>
                                <th>Documento</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($proveedores as $item)
                                <tr data-proveedor-id="{{ $item->id }}">
                                    <td class="checkbox-cell">
                                        <div class="custom-checkbox proveedor-checkbox" data-proveedor-id="{{ $item->id }}"></div>
                                    </td>
                                    <td>
                                        <div class="proveedor-info">
                                            <div class="proveedor-avatar">
                                                {{ strtoupper(substr($item->persona->razon_social, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $item->persona->razon_social }}</div>
                                                <span class="info-subtext">{{ Str::limit($item->persona->direccion, 30) }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $item->persona->telefono ?? 'N/A' }}</div>
                                        <div class="info-subtext">{{ $item->persona->email ?? 'Sin email' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $item->persona->documento->tipo_documento ?? 'N/A' }}</div>
                                        <span class="info-subtext">{{ $item->persona->numero_documento }}</span>
                                    </td>
                                    <td>
                                        @if($item->persona->tipo_persona == 'natural')
                                            <span class="badge bg-light text-dark border">Natural</span>
                                        @else
                                            <span class="badge bg-light text-dark border">Juridica</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->persona->estado == 1)
                                            <span class="badge-pill badge-success">Activo</span>
                                        @else
                                            <span class="badge-pill badge-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-action-group">
                                            @can('editar-proveedor')
                                            <a href="{{ route('proveedores.edit', ['proveedore' => $item]) }}" class="btn-icon-soft" title="Editar">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            @endcan

                                            @can('eliminar-proveedor')
                                                @if ($item->persona->estado == 1)
                                                    <button class="btn-icon-soft delete" data-bs-toggle="modal"
                                                        data-bs-target="#confirmModal-{{ $item->id }}" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @else
                                                    <button class="btn-icon-soft" data-bs-toggle="modal"
                                                        data-bs-target="#confirmModal-{{ $item->id }}" title="Restaurar">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal -->
                                <div class="modal fade" id="confirmModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content modal-content-clean">
                                            <div class="modal-header modal-header-clean">
                                                <h5 class="modal-title fs-6">Confirmar accion</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4 text-center">
                                                <h6 class="mb-3">{{ $item->persona->estado == 1 ? 'Â¿Eliminar proveedor?' : 'Â¿Restaurar proveedor?' }}</h6>
                                                <p class="text-muted small mb-4">
                                                    {{ $item->persona->estado == 1
                                                        ? 'El proveedor se desactivara del sistema.'
                                                        : 'El proveedor volvera a estar activo.' }}
                                                </p>

                                                <div class="d-flex justify-content-center gap-2">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancelar</button>
                                                    <form action="{{ route('proveedores.destroy', ['proveedore' => $item->persona->id]) }}" method="post" class="d-inline">
                                                        @method('DELETE')
                                                        @csrf
                                                        <button type="submit" class="btn {{ $item->persona->estado == 1 ? 'btn-outline-danger' : 'btn-outline-success' }} btn-sm px-3">
                                                            {{ $item->persona->estado == 1 ? 'Eliminar' : 'Restaurar' }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>

                        <!-- TFOOT con resumen -->
                        <tfoot>
                            <tr class="table-totals">
                                <td colspan="4" class="text-end">
                                    <span class="totals-label">RESUMEN</span>
                                </td>
                                <td class="text-center">
                                    <span class="totals-value">{{ $totalProveedores ?? $proveedores->total() }}</span>
                                    <span class="totals-subtext">Total Proveedores</span>
                                </td>
                                <td class="text-center">
                                    <span class="totals-value success">{{ $proveedoresActivos ?? 0 }}</span>
                                    <span class="totals-subtext">Activos</span>
                                </td>
                                <td class="text-center">
                                    <span class="totals-value warning">{{ $proveedoresInactivos ?? 0 }}</span>
                                    <span class="totals-subtext">Inactivos</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            <!-- Paginacion -->
            <div class="p-3 d-flex justify-content-between align-items-center border-top">
                <div class="text-muted extra-small">
                    Mostrando {{ $proveedores->firstItem() }} - {{ $proveedores->lastItem() }} de {{ $proveedores->total() }} registros
                </div>
                <div>
                    {{ $proveedores->appends(['busqueda' => $busqueda ?? '', 'per_page' => $perPage ?? 10])->links() }}
                </div>
            </div>
            </div>

        </div>

        <!-- Modal de Exportacion Generico -->
        <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modal-content-clean">
                    <div class="modal-header modal-header-clean">
                        <h5 class="modal-title fs-6" id="exportModalTitle">
                            <i class="fas fa-file-export me-2"></i> Exportar Proveedores
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" id="exportFormat" value="excel">
                        <div class="alert alert-success border-0 bg-success bg-opacity-10 d-flex align-items-center mb-4" id="exportAlert" style="border-radius: 12px;">
                            <i class="fas fa-info-circle me-3 fs-5 text-success" id="exportAlertIcon"></i>
                            <div class="small fw-medium text-success">
                                Se exportarán <strong id="exportCountDisplay">0</strong> proveedores seleccionados.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="info-subtext mb-2 text-uppercase letter-spacing-05 small fw-bold">Opciones de Exportacion</label>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="modalIncludeContact" checked>
                                <label class="form-check-label d-block" for="modalIncludeContact">
                                    <span class="d-block fw-semibold small">Incluir informacion de contacto</span>
                                    <span class="extra-small text-muted">Telefono, email y direccion completa</span>
                                </label>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="modalIncludeDocument" checked>
                                <label class="form-check-label d-block" for="modalIncludeDocument">
                                    <span class="d-block fw-semibold small">Incluir documento</span>
                                    <span class="extra-small text-muted">Tipo y número de documento de identidad</span>
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modalIncludeAll" checked>
                                <label class="form-check-label d-block" for="modalIncludeAll">
                                    <span class="d-block fw-semibold small">Incluir todos los datos</span>
                                    <span class="extra-small text-muted">Datos completos del proveedor</span>
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
    let selectedProveedores = new Set();

    // Sistema de seleccion
    function initializeSelectionSystem() {
        const selectionActions = document.getElementById('selectionActions');
        const selectAllCheckbox = document.getElementById('selectAll');
        const proveedorCheckboxes = document.querySelectorAll('.proveedor-checkbox');
        const selectedCountElement = document.getElementById('selectedCount');
        const deselectAllBtn = document.getElementById('deselectAll');
        const exportExcelBtn = document.getElementById('exportExcel');
        const exportPdfBtn = document.getElementById('exportPdf');

        proveedorCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('click', function() {
                const proveedorId = this.dataset.proveedorId;
                const row = this.closest('tr');

                if (this.classList.contains('checked')) {
                    this.classList.remove('checked');
                    row.classList.remove('selected');
                    selectedProveedores.delete(proveedorId);
                } else {
                    this.classList.add('checked');
                    row.classList.add('selected');
                    selectedProveedores.add(proveedorId);
                }

                updateSelectionUI();
            });
        });

        selectAllCheckbox.addEventListener('click', function() {
            const isSelectAll = !this.classList.contains('checked');
            const visibleRows = document.querySelectorAll('#datatablesSimple tbody tr');

            visibleRows.forEach(row => {
                const checkbox = row.querySelector('.proveedor-checkbox');
                if (checkbox) {
                    const proveedorId = checkbox.dataset.proveedorId;

                    if (isSelectAll) {
                        checkbox.classList.add('checked');
                        row.classList.add('selected');
                        selectedProveedores.add(proveedorId);
                    } else {
                        checkbox.classList.remove('checked');
                        row.classList.remove('selected');
                        selectedProveedores.delete(proveedorId);
                    }
                }
            });

            this.classList.toggle('checked');
            updateSelectionUI();
        });

        deselectAllBtn.addEventListener('click', function() {
            selectedProveedores.clear();
            proveedorCheckboxes.forEach(checkbox => {
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
            document.getElementById('exportCountDisplay').textContent = selectedProveedores.size;

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
            const count = selectedProveedores.size;
            selectedCountElement.textContent = count;

            if (count > 0) {
                selectionActions.style.display = 'flex';
            } else {
                selectionActions.style.display = 'none';
                selectAllCheckbox.classList.remove('checked');
            }
        }
    }

    // Exportacion con rutas genericas
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'confirmExportBtn') {
            const format = document.getElementById('exportFormat').value;
            const proveedorIds = Array.from(selectedProveedores);
            const includeContact = document.getElementById('modalIncludeContact').checked;
            const includeDocument = document.getElementById('modalIncludeDocument').checked;
            const includeAll = document.getElementById('modalIncludeAll').checked;

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
            form.action = format === 'excel'
                ? '{{ route('export.excel', ['module' => 'proveedores']) }}'
                : '{{ route('export.pdf', ['module' => 'proveedores']) }}';

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            proveedorIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });

            const options = { includeContact, includeDocument, includeAll };
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

    // Eventos de búsqueda y paginación
    function initializeEvents() {
        const searchInput = document.querySelector('input[name="busqueda"]');
        const perPageSelect = document.getElementById('per_page');

        if (searchInput) {
            searchInput.focus();
            const len = searchInput.value.length;
            searchInput.setSelectionRange(len, len);

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => fetchProveedores(), 300);
            });
        }

        if (perPageSelect) {
            perPageSelect.addEventListener('change', () => fetchProveedores());
        }
    }

    // AJAX para refrescar la tabla
    function fetchProveedores(url = null) {
        const searchInput = document.querySelector('input[name="busqueda"]');
        const perPageSelect = document.getElementById('per_page');

        let fetchUrl = url;
        if (!fetchUrl) {
            const params = new URLSearchParams(window.location.search);
            if (searchInput) params.set('busqueda', searchInput.value);
            if (perPageSelect) params.set('per_page', perPageSelect.value);
            fetchUrl = `{{ route('proveedores.index') }}?${params.toString()}`;
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

                selectedProveedores.clear();
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

    // Inicializacion
    document.addEventListener('DOMContentLoaded', function() {
        initializeEvents();
        initializeSelectionSystem();
    });

    // Clic en fila para seleccionar
    document.addEventListener('click', function(e) {
        if (e.target.closest('tr[data-proveedor-id]') &&
            !e.target.closest('.btn-action-group') &&
            !e.target.closest('.custom-checkbox') &&
            !e.target.closest('button')) {

            const row = e.target.closest('tr[data-proveedor-id]');
            const checkbox = row.querySelector('.proveedor-checkbox');

            if (checkbox) {
                checkbox.click();
            }
        }
    });
</script>
@endpush

