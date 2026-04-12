@extends('admin.layouts.app')

@section('title', 'Traslados')

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
                <h1 class="page-title">Traslados</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none text-muted">Inicio</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Traslados</li>
                    </ol>
                </nav>
            </div>
            @can('crear-traslado')
            <a href="{{ route('traslados.create') }}" class="btn-create">
                <i class="fas fa-plus"></i> Nuevo Traslado
            </a>
            @endcan
        </div>

        <div class="card-clean">
            <div class="card-header-clean">
                <div class="card-header-title">
                    <i class="fas fa-truck-moving"></i> Lista de Traslados
                </div>
            </div>

            <div class="search-container">
                <form action="{{ route('traslados.index') }}" method="GET" id="searchForm">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0" style="padding: 0.4rem 0.75rem;">
                                    <i class="fas fa-search text-muted small"></i>
                                </span>
                                <input type="text" name="busqueda" class="form-control form-control-clean border-start-0 ps-0" 
                                    placeholder="Buscar traslado..." value="{{ $busqueda ?? '' }}">
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
                            <a href="{{ route('traslados.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 6px;">
                                <i class="fas fa-undo me-1"></i> Mostrar Todo
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- SECCION: ACCIONES DE SELECCION -->
            <div class="selection-actions-container" id="selectionActions" style="display: none;">
                <div class="selected-counter">
                    <span>Traslados seleccionados:</span>
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
                                <th>#</th>
                                <th>Fecha Registro</th>
                                <th>Origen</th>
                                <th>Destino</th>
                                <th>Usuario</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($traslados as $traslado)
                                <tr data-traslado-id="{{ $traslado->id }}">
                                    <td class="checkbox-cell">
                                        <div class="custom-checkbox traslado-checkbox" data-traslado-id="{{ $traslado->id }}"></div>
                                    </td>
                                    <td>{{ $traslado->id }}</td>
                                    <td>{{ $traslado->fecha_hora->format('d/m/Y H:i:s') }}</td>
                                    <td>{{ $traslado->origenAlmacen?->nombre ?? 'N/A' }}</td>
                                    <td>{{ $traslado->destinoAlmacen?->nombre ?? 'N/A' }}</td>
                                    <td>{{ $traslado->user?->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($traslado->estado == 1)
                                            <form action="{{ route('traslados.toggleEstado', $traslado) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <select name="estado" onchange="this.form.submit()" class="form-select form-select-sm" title="Cambiar estado">
                                                    <option value="1" {{ $traslado->estado == 1 ? 'selected' : '' }}>Pendiente</option>
                                                    <option value="2" {{ $traslado->estado == 2 ? 'selected' : '' }}>Completado</option>
                                                    <option value="3" {{ $traslado->estado == 3 ? 'selected' : '' }}>Cancelado</option>
                                                </select>
                                            </form>
                                        @elseif($traslado->estado == 2)
                                            <span class="badge-pill badge-success">Completado</span>
                                        @else
                                            <span class="badge-pill badge-danger">Cancelado</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-action-group">
                                            @can('ver-traslado')
                                                <button class="btn-icon-soft" data-bs-toggle="modal"
                                                    data-bs-target="#verModal-{{ $traslado->id }}" title="Ver Detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endcan

                                            @can('editar-traslado')
                                                <a href="{{ route('traslados.edit', $traslado) }}" class="btn-icon-soft" title="Editar">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                            @endcan

                                            @can('eliminar-traslado')
                                                <button type="button" class="btn-icon-soft delete" data-bs-toggle="modal"
                                                    data-bs-target="#confirmModal-{{ $traslado->id }}" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted extra-small">
                        Mostrando {{ $traslados->firstItem() }} - {{ $traslados->lastItem() }} de {{ $traslados->total() }} registros
                    </div>
                    <div>
                        {{ $traslados->appends(['busqueda' => $busqueda, 'per_page' => $perPage])->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Exportacion -->
        <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modal-content-clean">
                    <div class="modal-header modal-header-clean">
                        <h5 class="modal-title fs-6" id="exportModalTitle">
                            <i class="fas fa-file-export me-2"></i> Exportar Traslados
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" id="exportFormat" value="excel">
                        <div class="alert alert-success border-0 bg-success bg-opacity-10 d-flex align-items-center mb-4" id="exportAlert" style="border-radius: 12px;">
                            <i class="fas fa-info-circle me-3 fs-5 text-success" id="exportAlertIcon"></i>
                            <div class="small fw-medium text-success">
                                Se exportaran <strong id="exportCountDisplay">0</strong> traslados seleccionados.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="info-subtext mb-2 text-uppercase letter-spacing-05 small fw-bold">Opciones de Datos</label>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="modalIncludeDetalles" checked>
                                <label class="form-check-label d-block" for="modalIncludeDetalles">
                                    <span class="d-block fw-semibold small">Incluir detalles de productos</span>
                                    <span class="extra-small text-muted">Lista de productos trasladados</span>
                                </label>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="modalIncludeCosto" checked>
                                <label class="form-check-label d-block" for="modalIncludeCosto">
                                    <span class="d-block fw-semibold small">Incluir costo de envio</span>
                                    <span class="extra-small text-muted">Informacion de costos</span>
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modalIncludeUsuario" checked>
                                <label class="form-check-label d-block" for="modalIncludeUsuario">
                                    <span class="d-block fw-semibold small">Incluir informacion de usuario</span>
                                    <span class="extra-small text-muted">Usuario que realizo el traslado</span>
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

    <!-- MODALES FUERA DE LA TABLA -->
    <div id="modales-section">
    @foreach ($traslados as $traslado)
        <!-- Modal de detalles -->
        <div class="modal fade" id="verModal-{{ $traslado->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
                <div class="modal-content modal-content-clean">
                    <div class="modal-header modal-header-clean">
                        <h5 class="modal-title fs-6">Detalles del Traslado #{{ $traslado->id }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="info-subtext mb-1">Fecha Registro</label>
                                <div class="p-2 border rounded bg-light small">{{ $traslado->fecha_hora->format('d/m/Y H:i:s') }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="info-subtext mb-1">Usuario</label>
                                <div class="p-2 border rounded bg-light small">{{ $traslado->user?->name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="info-subtext mb-1">Almacen Origen</label>
                                <div class="p-2 border rounded bg-light small">{{ $traslado->origenAlmacen?->nombre ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="info-subtext mb-1">Almacen Destino</label>
                                <div class="p-2 border rounded bg-light small">{{ $traslado->destinoAlmacen?->nombre ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="info-subtext mb-1">Costo de Envio</label>
                                <div class="fw-bold">Bs {{ number_format($traslado->costo_envio, 2) }}</div>
                            </div>
                            <div class="col-md-6">
                                @php
                                    $estadoColor = match($traslado->estado) {
                                        1 => 'warning',
                                        2 => 'success',
                                        3 => 'danger',
                                        default => 'secondary',
                                    };
                                    $estadoText = match($traslado->estado) {
                                        1 => 'Pendiente',
                                        2 => 'Completado',
                                        3 => 'Cancelado',
                                        default => 'Desconocido',
                                    };
                                @endphp
                                <label class="info-subtext mb-1">Estado</label>
                                <div class="p-2 border rounded bg-{{ $estadoColor }} text-white text-center fw-bold">
                                    {{ $estadoText }}
                                </div>
                            </div>
                        </div>

                        <!-- PRODUCTOS TRASLADADOS -->
                        <div class="mt-4">
                            <h6 class="fw-semibold border-bottom pb-2 small uppercase letter-spacing-05">
                                <i class="fas fa-box me-2 text-muted"></i>Productos Trasladados
                            </h6>
                            @if($traslado->detalles->count())
                                <div class="table-responsive mt-2">
                                    <table class="table table-hover table-bordered align-middle">
                                        <thead class="table-light text-center">
                                            <tr>
                                                <th>Producto</th>
                                                <th class="text-center">Cantidad</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($traslado->detalles as $detalle)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="product-avatar me-2" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                                            <i class="fas fa-box small"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold small">{{ $detalle->producto?->nombre ?? 'Producto eliminado' }}</div>
                                                            @if($detalle->producto)
                                                                <span class="info-subtext">Código: {{ $detalle->producto->codigo }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge-pill badge-primary">{{ $detalle->cantidad }}</span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-light">
                                                <th class="text-end">Total:</th>
                                                <th class="text-center">
                                                    <span class="badge-pill badge-success">
                                                        {{ $traslado->detalles->sum('cantidad') }}
                                                    </span>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-light border py-2 small mt-2 text-center">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    No hay productos asociados a este traslado.
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de confirmacion -->
        <div class="modal fade" id="confirmModal-{{ $traslado->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modal-content-clean">
                    <div class="modal-header modal-header-clean">
                        <h5 class="modal-title fs-6">Confirmar eliminacion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <h6 class="mb-3">¿Estás seguro de eliminar el Traslado #{{ $traslado->id }}?</h6>
                        <p class="text-muted small mb-4">
                            Esta accion no se puede deshacer.
                        </p>
                        
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancelar</button>
                            
                            <form action="{{ route('traslados.destroy', $traslado) }}" method="post" class="d-inline">
                                @method('DELETE')
                                @csrf
                                <input type="hidden" name="accion" value="eliminar">
                                <button type="submit" class="btn btn-outline-danger btn-sm px-3">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endsection

@push('js')
<script>
    let debounceTimer;
    const tableContainer = document.getElementById('table-container');
    let selectedTraslados = new Set();

    // Sistema de seleccion
    function initializeSelectionSystem() {
        const selectionActions = document.getElementById('selectionActions');
        const selectAllCheckbox = document.getElementById('selectAll');
        const trasladoCheckboxes = document.querySelectorAll('.traslado-checkbox');
        const selectedCountElement = document.getElementById('selectedCount');
        const deselectAllBtn = document.getElementById('deselectAll');
        const exportExcelBtn = document.getElementById('exportExcel');
        const exportPdfBtn = document.getElementById('exportPdf');

        // Seleccionar/Deseleccionar traslado individual
        trasladoCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('click', function() {
                const trasladoId = this.dataset.trasladoId;
                const row = this.closest('tr');
                
                if (this.classList.contains('checked')) {
                    // Deseleccionar
                    this.classList.remove('checked');
                    row.classList.remove('selected');
                    selectedTraslados.delete(trasladoId);
                } else {
                    // Seleccionar
                    this.classList.add('checked');
                    row.classList.add('selected');
                    selectedTraslados.add(trasladoId);
                }
                
                updateSelectionUI();
            });
        });

        // Seleccionar todos
        selectAllCheckbox.addEventListener('click', function() {
            const isSelectAll = !this.classList.contains('checked');
            
            trasladoCheckboxes.forEach(checkbox => {
                const trasladoId = checkbox.dataset.trasladoId;
                const row = checkbox.closest('tr');
                
                if (isSelectAll) {
                    checkbox.classList.add('checked');
                    row.classList.add('selected');
                    selectedTraslados.add(trasladoId);
                } else {
                    checkbox.classList.remove('checked');
                    row.classList.remove('selected');
                    selectedTraslados.delete(trasladoId);
                }
            });
            
            selectAllCheckbox.classList.toggle('checked');
            updateSelectionUI();
        });

        // Deseleccionar todos
        deselectAllBtn.addEventListener('click', function() {
            selectedTraslados.clear();
            trasladoCheckboxes.forEach(checkbox => {
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
            document.getElementById('exportCountDisplay').textContent = selectedTraslados.size;
            
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
            const count = selectedTraslados.size;
            selectedCountElement.textContent = count;
            
            if (count > 0) {
                selectionActions.style.display = 'flex';
                const totalCheckboxes = trasladoCheckboxes.length;
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
            const trasladoIds = Array.from(selectedTraslados);
            const includeDetalles = document.getElementById('modalIncludeDetalles').checked;
            const includeCosto = document.getElementById('modalIncludeCosto').checked;
            const includeUsuario = document.getElementById('modalIncludeUsuario').checked;

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
            form.action = format === 'excel' ? '{{ route("traslados.exportar-excel") }}' : '{{ route("traslados.exportar-pdf") }}';

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            trasladoIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'traslado_ids[]';
                input.value = id;
                form.appendChild(input);
            });

            const options = { includeDetalles, includeCosto, includeUsuario };
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
                    text: 'El archivo se descargara automaticamente.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }, 1000);
        }
    });

    function initializeEvents() {
        const searchInput = document.querySelector('input[name="busqueda"]');
        const perPageSelect = document.getElementById('per_page');

        // Búsqueda
        if (searchInput) {
            searchInput.focus();
            const len = searchInput.value.length;
            searchInput.setSelectionRange(len, len);
            
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => fetchTraslados(), 300);
            });
        }

        // Items por página
        if (perPageSelect) {
            perPageSelect.addEventListener('change', () => fetchTraslados());
        }
    }

    function fetchTraslados(url = null) {
        const searchInput = document.querySelector('input[name="busqueda"]');
        const perPageSelect = document.getElementById('per_page');
        
        let fetchUrl = url;
        if (!fetchUrl) {
            const params = new URLSearchParams(window.location.search);
            if (searchInput) params.set('busqueda', searchInput.value);
            if (perPageSelect) params.set('per_page', perPageSelect.value);
            fetchUrl = `{{ route('traslados.index') }}?${params.toString()}`;
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
                
                // Actualizar modales (estan fuera del table-container)
                const modalesSection = document.querySelector('#modales-section');
                if (modalesSection) {
                    // Buscar la seccion de modales en el nuevo documento
                    const newModalesSection = newDoc.querySelector('#modales-section');
                    if (newModalesSection) {
                        // Reemplazar todo el contenido de la seccion de modales
                        modalesSection.innerHTML = newModalesSection.innerHTML;
                    }
                }
                
                // Limpiar seleccion al actualizar
                selectedTraslados.clear();
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

    // Cargar detalles del traslado cuando se abre el modal
    document.addEventListener('DOMContentLoaded', function() {
        initializeEvents();
        initializeSelectionSystem();
    });

    // Manejar clic en fila (seleccionar traslado)
    document.addEventListener('click', function(e) {
        if (e.target.closest('tr[data-traslado-id]') && !e.target.closest('.btn-action-group') && !e.target.closest('.custom-checkbox') && !e.target.closest('select') && !e.target.closest('form')) {
            const row = e.target.closest('tr[data-traslado-id]');
            const trasladoId = row.dataset.trasladoId;
            const checkbox = row.querySelector('.traslado-checkbox');
            
            if (checkbox) {
                checkbox.click();
            }
        }
    });

    // Manejar eliminacion
    document.addEventListener('click', function (e) {
        if (e.target.closest('.btn-real-delete')) {
            const btn = e.target.closest('.btn-real-delete');
            const form = btn.closest('form');
            const nombre = btn.dataset.nombre;

            Swal.fire({
                title: '¿Estas seguro?',
                text: `Deseas eliminar "${nombre}"`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Si, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    });

    // Mostrar SweetAlert si hay un mensaje de error
    @if ($errors->any() || session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            html: `
                @if (session('error'))
                    {{ session('error') }}
                @endif
                @if ($errors->any())
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            `,
            confirmButtonText: 'Aceptar'
        });
    @endif
</script>
@endpush

