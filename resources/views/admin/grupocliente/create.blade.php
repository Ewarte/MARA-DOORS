@extends('admin.layouts.app')

@section('title', 'Crear Grupo de Clientes')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/style_edit_create.css') }}">
@endpush

@section('content')
    <div class="container-fluid px-3 px-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Crear Grupo de Clientes</h1>
            <a href="{{ route('grupoclientes.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>

        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('panel') }}">Inicio</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('grupoclientes.index') }}">Grupo de Clientes</a>
                </li>
                <li class="breadcrumb-item active">Crear Grupo de Clientes</li>
            </ol>
        </nav>

        <div class="form-container">
            <form action="{{ route('grupoclientes.store') }}" method="POST">
                @csrf

                <div class="row g-3">

                    {{-- Nombre --}}
                    <div class="col-12">
                        <label for="nombre" class="form-label">Nombre del grupo:</label>
                        <input type="text"
                               name="nombre"
                               id="nombre"
                               class="form-control"
                               value="{{ old('nombre') }}"
                               placeholder="Ej: Clientes Mayoristas">

                        @error('nombre')
                            <small class="text-danger">{{ '*' . $message }}</small>
                        @enderror
                    </div>

                    {{-- Descripción --}}
                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción:</label>
                        <textarea name="descripcion"
                                  id="descripcion"
                                  rows="3"
                                  class="form-control"
                                  placeholder="Descripción opcional del grupo">{{ old('descripcion') }}</textarea>

                        @error('descripcion')
                            <small class="text-danger">{{ '*' . $message }}</small>
                        @enderror
                    </div>

                    {{-- Descuento Global --}}
                    <div class="col-md-6">
                        <label for="descuento_global" class="form-label">Descuento Global (%):</label>
                        <input type="number"
                               name="descuento_global"
                               id="descuento_global"
                               class="form-control"
                               value="{{ old('descuento_global', 0) }}"
                               min="0"
                               max="100"
                               step="0.01">

                        @error('descuento_global')
                            <small class="text-danger">{{ '*' . $message }}</small>
                        @enderror
                    </div>

                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar Grupo
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('js')
<script>
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
