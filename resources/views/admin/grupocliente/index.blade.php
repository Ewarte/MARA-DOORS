@extends('admin.layouts.app')

@section('title','Grupo de Clientes')

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
                <h1 class="page-title">Grupo de Clientes</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none text-muted">Inicio</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Grupo de Clientes</li>
                    </ol>
                </nav>
            </div>
            @can('crear-grupocliente')
            <a href="{{ route('grupoclientes.create') }}" class="btn-create">
                <i class="fas fa-plus"></i> Añadir Nuevo Grupo de Clientes
            </a>
            @endcan
        </div>

        <div class="card-clean">
            <div class="card-header-clean">
                <div class="card-header-title">
                    <i class="fas fa-tags"></i> Tabla de Grupo de Clientes
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="datatablesSimple" class="custom-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Descuento Global</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($grupoclientes as $item)
                            <tr>
                                {{-- Nombre --}}
                                <td class="fw-semibold">
                                    {{ $item->nombre }}
                                </td>

                                {{-- Descripción --}}
                                <td>
                                    @if($item->descripcion)
                                        <div class="descripcion-truncada" title="{{ $item->descripcion }}">
                                            {{ $item->descripcion }}
                                        </div>
                                    @else
                                        <span class="text-muted fst-italic small">--</span>
                                    @endif
                                </td>

                                {{-- Descuento Global --}}
                                <td>
                                    @if($item->descuento_global)
                                        <span class="badge bg-info text-dark">
                                            {{ $item->descuento_global }}%
                                        </span>
                                    @else
                                        <span class="text-muted fst-italic small">0%</span>
                                    @endif
                                </td>

                                {{-- Estado --}}
                                <td>
                                    @if($item->estado == 1)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                </td>

                                {{-- Acciones --}}
                                <td>
                                    <div class="btn-action-group">

                                        

                                        {{-- Editar --}}
                                        @can('editar-grupocliente')
                                        <a href="{{ route('grupoclientes.edit', $item) }}" 
                                        class="btn-icon-soft" 
                                        title="Editar">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        @endcan

                                        {{-- Eliminar --}}
                                        @can('eliminar-grupocliente')
                                        <form action="{{ route('grupoclientes.destroy', $item) }}" 
                                            method="POST" 
                                            class="d-inline formulario-eliminar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn-icon-soft text-danger btn-eliminar"
                                                    data-nombre="{{ $item->nombre }}"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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

    // Inicializar DataTable
    const tabla = document.querySelector("#datatablesSimple");
    if (tabla) {
        new simpleDatatables.DataTable(tabla, {
            perPage: 10,
            perPageSelect: [10, 25, 50, 100, -1],
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

// 🔥 Delegación de eventos (esto es lo importante)
document.addEventListener('submit', function(e) {

    if (e.target.matches('.formulario-eliminar')) {
        e.preventDefault();

        const form = e.target;
        const nombre = form.querySelector('.btn-eliminar')?.dataset.nombre ?? '';

        Swal.fire({
            title: '¿Eliminar grupo?',
            text: nombre ? "Se eliminará: " + nombre : "Este grupo no podrá recuperarse",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }

});
</script>
@endpush
