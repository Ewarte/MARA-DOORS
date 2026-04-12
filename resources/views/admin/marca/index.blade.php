@extends('admin.layouts.app')

@section('title','Marcas')

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
                <h1 class="page-title">Marcas</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none text-muted">Inicio</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Marcas</li>
                    </ol>
                </nav>
            </div>
            @can('crear-marca')
            <a href="{{ route('marcas.create') }}" class="btn-create">
                <i class="fas fa-plus"></i> Añadir Nueva Marca
            </a>
            @endcan
        </div>

        <div class="card-clean">
            <div class="card-header-clean">
                <div class="card-header-title">
                    <i class="fas fa-tags"></i> Tabla de Marcas
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
                            @foreach ($marcas as $item)
                            <tr>
                                <td class="fw-semibold">
                                    {{ $item->nombre }}
                                </td>

                                <td>
                                    @if($item->descripcion)
                                        <div class="descripcion-truncada" title="{{ $item->descripcion }}">
                                            {{ $item->descripcion }}
                                        </div>
                                    @else
                                        <span class="text-muted fst-italic small">--</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="btn-action-group">
                                        @can('editar-marca')
                                        <a href="{{ route('marcas.edit', $item) }}" class="btn-icon-soft" title="Editar">
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
                    perPageSelect: [10, 25, 50, 100, -1], // -1 = todos
                    searchable: true,
                    labels: {
                        placeholder: "Buscar...",
                        perPage: "Mostrar {select} registros",
                        noRows: "No se encontraron resultados",
                        info: "Mostrando {start} a {end} de {rows} registros"
                    }
                });
            }
        });
    </script>
@endpush
