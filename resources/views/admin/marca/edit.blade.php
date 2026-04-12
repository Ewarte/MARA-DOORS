@extends('admin.layouts.app')

@section('title', 'Editar marca')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/style_edit_create.css') }}">
@endpush

@section('content')
    <div class="container-fluid px-3 px-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Editar Marca</h1>
            <a href="{{ route('marcas.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('marcas.index') }}">Marcas</a></li>
                <li class="breadcrumb-item active">Editar categoría</li>
            </ol>
        </nav>
        <div class="form-container">
            <form action="{{ route('marcas.update', ['marca' => $marca]) }}" method="post">
                @method('PATCH')
                @csrf   
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="nombre" class="form-label">Nombre de la marca:</label>
                        <input type="text" name="nombre" id="nombre" class="form-control"
                            value="{{ old('nombre', $marca->nombre) }}"
                            placeholder="Ingrese el nombre de la Marca">
                        @error('nombre')
                            <small class="text-danger">{{ '*' . $message }}</small>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción:</label>
                        <textarea name="descripcion" id="descripcion" rows="3" class="form-control">{{ old('descripcion', $marca->descripcion) }}</textarea>
                        @error('descripcion')
                            <small class="text-danger">{{ '*' . $message }}</small>
                        @enderror
                    </div>
                </div>
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-save me-2"></i>Actualizar
                    </button>
                    <a href="{{ route('marcas.index') }}" class="btn btn-secondary">
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
            console.log('Marca actual:', {
                nombre: '{{ $marca->nombre }}',
                descripcion: '{{ $marca->descripcion }}'
            });
        });
    </script>
@endpush
