@extends('admin.layouts.app')

@section('title','Roles')

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
            <h1 class="page-title">Roles</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none text-muted">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Roles</li>
                </ol>
            </nav>
        </div>
        @can('crear-role')
        <a href="{{ route('roles.create') }}" class="btn-create">
            <i class="fas fa-plus"></i> Añadir Nuevo Rol
        </a>
        @endcan
    </div>

    <div class="card-clean">
        <div class="card-header-clean">
            <div class="card-header-title">
                <i class="fas fa-user-tag"></i> Tabla de Roles
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="datatablesSimple" class="custom-table">
                    <thead>
                        <tr>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $item)
                        <tr>
                            <td class="fw-semibold">
                                {{ $item->name }}
                            </td>
                            <td>
                                <div class="btn-action-group">
                                    @can('editar-role')
                                    <a href="{{ route('roles.edit', ['role'=>$item]) }}" class="btn-icon-soft" title="Editar">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    @endcan

                                    @can('eliminar-role')
                                    <button type="button" class="btn-icon-soft delete" data-bs-toggle="modal" data-bs-target="#confirmModal-{{$item->id}}" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>

                        <!-- Modal de confirmacion -->
                        <div class="modal fade" id="confirmModal-{{$item->id}}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content modal-content-clean">
                                    <div class="modal-header modal-header-clean">
                                        <h5 class="modal-title fs-6">Confirmar accion</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <h6 class="mb-3">Â¿Eliminar Rol?</h6>
                                        <p class="text-muted small mb-4">
                                            Â¿Seguro que quieres eliminar este rol del sistema?
                                        </p>
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                            <form action="{{ route('roles.destroy', ['role'=>$item->id]) }}" method="post">
                                                @method('DELETE')
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Confirmar</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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

