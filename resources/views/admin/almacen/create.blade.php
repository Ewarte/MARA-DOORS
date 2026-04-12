@extends('admin.layouts.app')

@section('title', 'Crear marca')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/style_edit_create.css') }}">
@endpush

@section('content')
    <div class="container-fluid px-3 px-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Agregar Almacen</h1>
            <a href="{{ route('almacenes.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('almacenes.index') }}">Almacenes</a></li>
                <li class="breadcrumb-item active">Agregar Almacen</li>
            </ol>
        </nav>

        <div class="form-container">
            <form action="{{ route('almacenes.store') }}" method="post">
                @csrf
                <div class="row g-3">

                    <div class="col-12">
                        <label for="codigo" class="form-label">Codigo del Almacen:</label>
                        <input type="text" name="codigo" id="codigo" class="form-control" value="{{ old('codigo') }}"
                            placeholder="Ingrese codigo del almacen">
                        @error('codigo')
                            <small class="text-danger">{{ '*' . $message }}</small>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="nombre" class="form-label">Nombre del almacen:</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre') }}"
                            placeholder="Ingrese el nombre del almacen">
                        @error('nombre')
                            <small class="text-danger">{{ '*' . $message }}</small>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción:</label>
                        <textarea name="descripcion" id="descripcion" rows="3" class="form-control">{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <small class="text-danger">{{ '*' . $message }}</small>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="direccion" class="form-label">Dirección:</label>
                        <textarea name="direccion" id="direccion" rows="3" class="form-control">{{ old('direccion') }}</textarea>
                        @error('direccion')
                            <small class="text-danger">{{ '*' . $message }}</small>
                        @enderror
                    </div>

                </div>
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar Almacen
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('js')
    <script>
        // Efecto de foco suave en los inputs
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-control');

            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.transform = 'translateY(-1px)';
                });

                input.addEventListener('blur', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
@endpush

