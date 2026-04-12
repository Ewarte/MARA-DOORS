@extends('admin.layouts.app')

@section('title', 'Editar almacén')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/style_edit_create.css') }}">
@endpush

@section('content')
    <div class="container-fluid px-3 px-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Editar Almacén</h1>
            <a href="{{ route('almacenes.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>

        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('almacenes.index') }}">Almacenes</a></li>
                <li class="breadcrumb-item active">Editar Almacén</li>
            </ol>
        </nav>

        <div class="form-container">
            <form action="{{ route('almacenes.update', $almacen) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="row g-3">
                    <div class="col-12">
                        <label for="codigo" class="form-label">Código del almacén:</label>
                        <input type="text" name="codigo" id="codigo" class="form-control"
                            value="{{ old('codigo', $almacen->codigo) }}"
                            placeholder="Ingrese el código del almacén">
                        @error('codigo')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror 
                    </div>

                    <div class="col-12">
                        <label for="nombre" class="form-label">Nombre del almacén:</label>
                        <input type="text" name="nombre" id="nombre" class="form-control"
                            value="{{ old('nombre', $almacen->nombre) }}"
                            placeholder="Ingrese el nombre del almacén">
                        @error('nombre')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción:</label>
                        <textarea name="descripcion" id="descripcion" rows="3" class="form-control"
                            placeholder="Describa la categoría (opcional)">{{ old('descripcion', $almacen->descripcion) }}</textarea>
                        @error('descripcion')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="direccion" class="form-label">Dirección:</label>
                        <textarea name="direccion" id="direccion" rows="3" class="form-control"
                            placeholder="Ingrese la dirección del almacén">{{ old('direccion', $almacen->direccion) }}</textarea>
                        @error('direccion')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-save me-2"></i>Actualizar
                    </button>
                    <a href="{{ route('almacenes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </a>
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
                    this.style.boxShadow = '0 0 0 3px rgba(52, 152, 219, 0.2)';
                });

                input.addEventListener('blur', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 0 0 3px rgba(52, 152, 219, 0.1)';
                });
            });

            // Mostrar valores actuales en consola para debugging
            console.log('Almacén actual:', {
                nombre: '{{ $almacen->nombre }}',
                descripcion: '{{ $almacen->descripcion }}'
            });
        });
    </script>
@endpush

