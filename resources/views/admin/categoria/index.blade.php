@extends('admin.layouts.app')

@section('title', 'Categorías')

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
                <h1 class="page-title">Categorías</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none text-muted">Inicio</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Categorías</li>
                    </ol>
                </nav>
            </div>
            @can('crear-categoria')
                <a href="{{ route('categorias.create') }}" class="btn-create">
                    <i class="fas fa-plus"></i> Añadir Nueva Categoría
                </a>
            @endcan
        </div>

        <div class="card-clean">
            <div class="card-header-clean">
                <div class="card-header-title">
                    <i class="fas fa-layer-group"></i> Lista de Categorías
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="datatablesSimple" class="custom-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categorias as $categoria)
                                <tr>
                                    <td class="fw-semibold">
                                        {{ $categoria->nombre }}
                                    </td>
                                    <td>
                                        @if($categoria->descripcion)
                                        <div class="descripcion-truncada" title="{{ $categoria->descripcion }}">
                                            {{ $categoria->descripcion }}
                                        </div>
                                        @else
                                        <span class="text-muted fst-italic small">--</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-action-group">
                                            @can('editar-categoria')
                                            <a href="{{ route('categorias.edit', $categoria) }}" class="btn-icon-soft" title="Editar">
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
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabla = document.querySelector("#datatablesSimple");
            if (tabla) {
                new simpleDatatables.DataTable(tabla, {
                    perPage: 10,
                    perPageSelect: [5, 10, 15, 20, 25],
                    searchable: true,
                    labels: {
                        placeholder: "Buscar categorías...",
                        perPage: "Mostrar {select} registros",
                        noRows: "No se encontraron categorías",
                        info: "Mostrando {start} a {end} de {rows} categorías"
                    }
                });
            }
        });
    </script>
@endpush
