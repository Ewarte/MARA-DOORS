@extends('admin.layouts.app')

@section('title','Almacenes')

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
                <h1 class="page-title">Almacenes</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none text-muted">Inicio</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Almacenes</li>
                    </ol>
                </nav>
            </div>
            @can('crear-almacen')
            <a href="{{ route('almacenes.create') }}" class="btn-create">
                <i class="fas fa-plus"></i> Nuevo Almacén
            </a>
            @endcan
        </div>

        <div class="card-clean">
            <div class="card-header-clean">
                <div class="card-header-title">
                    <i class="fas fa-warehouse"></i> Lista de Almacenes
                </div>
            </div>

            <!-- SECCIÓN: ACCIONES DE SELECCIÓN -->
            <div class="selection-actions-container" id="selectionActions" style="display: none;">
                <div class="selected-counter">
                    <span>Almacenes seleccionados:</span>
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
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="datatablesSimple" class="custom-table" data-module="almacenes">
                        <thead>
                            <tr>
                                <th class="checkbox-header">
                                    <div class="custom-checkbox select-all" id="selectAll"></div>
                                </th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Dirección</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($almacenes as $item)
                            <tr data-id="{{ $item->id }}">
                                <td class="checkbox-cell">
                                    <div class="custom-checkbox row-checkbox" data-id="{{ $item->id }}"></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $item->codigo }}</span>
                                </td>

                                <td class="fw-semibold">
                                    {{ $item->nombre }}
                                </td>

                                <td>
                                    @if($item->descripcion)
                                        <div class="text-muted small">{{ $item->descripcion }}</div>
                                    @else
                                        <span class="text-muted fst-italic small">--</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="text-muted small">
                                        <i class="fas fa-map-marker-alt me-1"></i> {{ $item->direccion }}
                                    </div>
                                </td>

                                <td>
                                    @if ($item->estado)
                                        <span class="badge-pill badge-success">Activo</span>
                                    @else
                                        <span class="badge-pill badge-danger">Inactivo</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="btn-action-group">
                                        @can('dar-de-baja-almacen')
                                        <form action="{{ route('almacenes.updateEstado', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-icon-soft {{ $item->estado ? 'delete' : '' }}"
                                                title="{{ $item->estado ? 'Dar de baja' : 'Activar' }}"
                                                onclick="return confirm('Â¿Seguro que deseas cambiar el estado del almacén?')">
                                                @if($item->estado == '1')
                                                    <i class="fas fa-trash"></i>
                                                @else
                                                    <i class="fas fa-check text-success"></i>
                                                @endif
                                            </button>
                                        </form>
                                        @endcan
                                        
                                        @can('editar-almacen')
                                        <a href="{{ route('almacenes.edit', $item->id) }}" class="btn-icon-soft" title="Editar">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal de Exportación Genérico -->
        <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modal-content-clean">
                    <div class="modal-header modal-header-clean">
                        <h5 class="modal-title fs-6" id="exportModalTitle">
                            <i class="fas fa-file-export me-2"></i> Exportar Almacenes
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" id="exportFormat" value="excel">
                        <div class="alert alert-success border-0 bg-success bg-opacity-10 d-flex align-items-center mb-4" id="exportAlert" style="border-radius: 12px;">
                            <i class="fas fa-info-circle me-3 fs-5 text-success" id="exportAlertIcon"></i>
                            <div class="small fw-medium text-success">
                                Se exportarán <strong id="exportCountDisplay">0</strong> almacenes seleccionados.
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
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
<script src="{{ asset('js/table-export.js') }}"></script>
<script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabla = document.querySelector("#datatablesSimple");
            let dataTable;
            if (tabla) {
                dataTable = new simpleDatatables.DataTable(tabla, {
                    perPage: 10,
                    perPageSelect: [10, 25, 50, 100, -1], // -1 = todos
                    searchable: true,
                    columns: [
                        { select: 0, sortable: false }
                    ],
                    labels: {
                        placeholder: "Buscar...",
                        perPage: "Mostrar {select} registros",
                        noRows: "No se encontraron resultados",
                        info: "Mostrando {start} a {end} de {rows} registros"
                    }
                });
                // Hook for re-init on page/search
                dataTable.on('datatable.page', () => window.TableExport.init());
                dataTable.on('datatable.search', () => window.TableExport.init());
                dataTable.on('datatable.sort', () => window.TableExport.init());
            }
        });

    </script>
@endpush
