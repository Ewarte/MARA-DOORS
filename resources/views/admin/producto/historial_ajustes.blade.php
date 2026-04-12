@extends('admin.layouts.app')

@section('title', 'Historial de Ajustes')

@push('css')
    <style>
        .page-header {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
            font-size: 0.9rem;
        }

        .card-clean {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background: #fff;
        }

        .card-header-clean {
            background: #f8f9fa;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-header-title {
            font-weight: 600;
            font-size: 1rem;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
        }

        .custom-table thead th {
            background: #fff;
            color: #6c757d;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.75rem 1rem;
            border-bottom: 2px solid #dee2e6;
        }

        .custom-table tbody td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
            color: #495057;
            font-size: 0.9rem;
        }

        .custom-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .badge-pill {
            padding: 0.35em 0.65em;
            border-radius: 50rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-success { background-color: #d4edda; color: #155724; }
        .badge-danger { background-color: #f8d7da; color: #721c24; }
        .badge-info { background-color: #d1ecf1; color: #0c5460; }

        .search-container {
            padding: 1rem 1.5rem;
            background: #fff;
            border-bottom: 1px solid #e9ecef;
        }

        .form-control-clean {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 0.4rem 0.75rem;
            font-size: 0.9rem;
            color: #495057;
        }

        .form-control-clean:focus {
            border-color: #adb5bd;
            box-shadow: none;
            outline: none;
        }

        .sort-btn {
            background: transparent;
            border: none;
            color: #6c757d;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            padding: 0;
            display: inline-flex;
            align-items: center;
            transition: color 0.15s;
            width: 100%;
            text-align: left;
            justify-content: space-between;
        }

        .sort-btn:hover { color: #495057; }
        .sort-btn.active { color: #2c3e50; }
        .sort-btn .sort-icon { font-size: 0.7rem; opacity: 0.7; margin-left: 4px; }

        .table-totals {
            background-color: #f8f9fa;
            border-top: 2px solid #dee2e6;
        }

        .table-totals td { padding: 1rem; font-weight: 600; }
        .totals-label { font-size: 0.85rem; color: #495057; font-weight: 600; }
        .totals-value { font-size: 1rem; font-weight: 700; color: #2c3e50; display: block; margin-bottom: 2px; }
        .totals-value.success { color: #198754; }
        .totals-value.danger { color: #dc3545; }
        .totals-subtext { font-size: 0.75rem; color: #6c757d; display: block; }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4 py-4">
        
        <div class="page-header">
            <div>
                <h1 class="page-title">Historial de Ajustes</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none text-muted">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('productos.index') }}" class="text-decoration-none text-muted">Productos</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Historial</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('productos.createAjuste') }}" class="btn btn-dark btn-sm rounded-pill px-3">
                <i class="fas fa-plus me-1"></i> Nuevo Ajuste
            </a>
        </div>

        <div class="card-clean">
            <div class="card-header-clean">
                <div class="card-header-title">
                    <i class="fas fa-history"></i> Registros de Modificaciones
                </div>
            </div>

            <div class="search-container">
                <form id="searchForm">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted small"></i>
                                </span>
                                <input type="text" name="busqueda" class="form-control form-control-clean border-start-0 ps-0" 
                                    placeholder="Buscar por producto..." value="{{ $busqueda ?? '' }}">
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
                    </div>
                </form>
            </div>

            <div class="card-body p-0" id="table-container">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>
                                    <button class="sort-btn {{ $sort == 'created_at' ? 'active' : '' }}" data-column="created_at">
                                        Fecha/Hora <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th>
                                    <button class="sort-btn {{ $sort == 'usuario' ? 'active' : '' }}" data-column="usuario">
                                        Usuario <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th>
                                    <button class="sort-btn {{ $sort == 'producto' ? 'active' : '' }}" data-column="producto">
                                        Producto <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th>
                                    <button class="sort-btn {{ $sort == 'almacen' ? 'active' : '' }}" data-column="almacen">
                                        Almacen <i class="fas fa-sort sort-icon"></i>
                                    </button>
                                </th>
                                <th class="text-center">Ajuste</th>
                                <th class="text-center">Final</th>
                                <th>Motivo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ajustes as $ajuste)
                                <tr>
                                    <td>
                                        <div class="small fw-semibold">{{ $ajuste->created_at->format('d/m/Y') }}</div>
                                        <div class="text-muted extra-small">{{ $ajuste->created_at->format('H:i') }}</div>
                                    </td>
                                    <td>
                                        <span class="badge-pill badge-info">{{ $ajuste->user->name }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $ajuste->producto->nombre }}</div>
                                        <span class="text-muted extra-small">{{ $ajuste->producto->codigo }}</span>
                                    </td>
                                    <td>{{ $ajuste->almacen->nombre }}</td>
                                    <td class="text-center">
                                        @php $diff = $ajuste->cantidad_nueva - $ajuste->cantidad_anterior; @endphp
                                        <span class="fw-bold {{ $diff > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="fw-bold">{{ number_format($ajuste->cantidad_nueva, 2) }}</div>
                                        <div class="text-muted extra-small">Ant: {{ number_format($ajuste->cantidad_anterior, 2) }}</div>
                                    </td>
                                    <td>
                                        <span class="small text-muted" title="{{ $ajuste->motivo }}">{{ Str::limit($ajuste->motivo, 30) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No se encontraron ajustes.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-totals">
                                <td colspan="3" class="text-end">
                                    <span class="totals-label">RESUMEN HISTORICO</span>
                                </td>
                                <td class="text-center">
                                    <span class="totals-value">{{ number_format($totalAjustes, 0) }}</span>
                                    <span class="totals-subtext">Ajustes</span>
                                </td>
                                <td class="text-center">
                                    <span class="totals-value success">{{ number_format($ajustesPositivos, 0) }}</span>
                                    <span class="totals-subtext">Incrementos</span>
                                </td>
                                <td class="text-center">
                                    <span class="totals-value danger">{{ number_format($ajustesNegativos, 0) }}</span>
                                    <span class="totals-subtext">Reducciones</span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="p-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted extra-small">
                        Mostrando {{ $ajustes->firstItem() }} - {{ $ajustes->lastItem() }} de {{ $ajustes->total() }} registros
                    </div>
                    <div>
                        {{ $ajustes->appends(['busqueda' => $busqueda, 'per_page' => $perPage, 'sort' => $sort, 'direction' => $direction])->links() }}
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

    function initializeEvents() {
        const searchInput = document.querySelector('input[name="busqueda"]');
        const perPageSelect = document.getElementById('per_page');
        const sortButtons = document.querySelectorAll('.sort-btn');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => fetchAdjustments(), 300);
            });
        }

        if (perPageSelect) {
            perPageSelect.addEventListener('change', () => fetchAdjustments());
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
                
                fetchAdjustments(`{{ route('productos.historialAjustes') }}?${params.toString()}`);
            });
        });
    }

    function fetchAdjustments(url = null) {
        const searchInput = document.querySelector('input[name="busqueda"]');
        const perPageSelect = document.getElementById('per_page');
        
        let fetchUrl = url;
        if (!fetchUrl) {
            const params = new URLSearchParams(window.location.search);
            if (searchInput) params.set('busqueda', searchInput.value);
            if (perPageSelect) params.set('per_page', perPageSelect.value);
            fetchUrl = `{{ route('productos.historialAjustes') }}?${params.toString()}`;
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
                
                window.history.pushState({}, '', fetchUrl);
                initializeEvents();
            })
            .catch(error => {
                console.error('Error:', error);
                tableContainer.style.opacity = '1';
            });
    }

    document.addEventListener('DOMContentLoaded', initializeEvents);
</script>
@endpush

