@extends('admin.layouts.app')

@section('title','Crear rol')

@push('css')
<style>
    /* Estilos consistentes con el sistema */
    .card-header {
        background-color: #34495e;
        color: #fff;
        padding: 1rem 1.5rem;
        font-size: 1.1rem;
        border-bottom: 2px solid #2c3e50;
    }

    .card-body {
        background-color: #f8f9fa;
        padding: 1.5rem;
    }

    /* Formulario */
    .form-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .form-label {
        font-weight: 500;
        color: #2c3e50;
    }

    .form-control {
        border-radius: 4px;
        border: 1px solid #ced4da;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .form-control:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }

    /* Checkboxes */
    .permisos-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 12px;
        margin-top: 1rem;
    }

    .permiso-item {
        background-color: #fff;
        border: 1px solid #e1e1e1;
        border-radius: 6px;
        padding: 12px;
        transition: all 0.3s;
    }

    .permiso-item:hover {
        background-color: #f1f4f8;
        transform: translateY(-2px);
    }

    .form-check-input {
        width: 18px;
        height: 18px;
        margin-top: 0.1em;
    }

    .form-check-label {
        margin-left: 8px;
        color: #34495e;
    }

    /* Botones */
    .btn-primary {
        background-color: #3498db;
        border: none;
        padding: 8px 24px;
        border-radius: 4px;
        font-weight: 500;
        transition: all 0.3s;
    }

    .btn-primary:hover {
        background-color: #2980b9;
        transform: translateY(-1px);
    }

    /* Mensajes de error */
    .text-danger {
        font-size: 0.85rem;
        margin-top: 4px;
        display: block;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .permisos-container {
            grid-template-columns: 1fr;
        }

        .row.mb-4 {
            flex-direction: column;
        }

        .col-md-4 {
            width: 100%;
            margin-top: 8px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Crear Rol</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('roles.index')}}">Roles</a></li>
        <li class="breadcrumb-item active">Crear rol</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-info-circle me-2"></i>Nota: Los roles son un conjunto de permisos
        </div>
        <div class="card-body">
            <form action="{{ route('roles.store') }}" method="post" class="form-container">
                @csrf

                <!---Nombre de rol---->
                <div class="row mb-4 align-items-center">
                    <label for="name" class="col-md-3 col-form-label form-label">Nombre del rol:</label>
                    <div class="col-md-6">
                        <input autocomplete="off" type="text" name="name" id="name" class="form-control" value="{{old('name')}}">
                        @error('name')
                        <small class="text-danger">{{'* '.$message}}</small>
                        @enderror
                    </div>
                </div>

                <!---Permisos---->
                <div class="mb-4">
                    <h5 class="mb-3" style="color: #2c3e50;">Permisos para el rol:</h5>
                    <div class="permisos-container">
                        @foreach ($permisos as $item)
                        <div class="permiso-item">
                            <div class="form-check">
                                <input type="checkbox" name="permission[]" id="permiso-{{$item->id}}" class="form-check-input" value="{{$item->id}}">
                                <label for="permiso-{{$item->id}}" class="form-check-label">
                                    {{ucfirst(str_replace('-', ' ', $item->name))}}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @error('permission')
                    <small class="text-danger">{{'* '.$message}}</small>
                    @enderror
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    // Scripts adicionales si son necesarios
</script>
@endpush

