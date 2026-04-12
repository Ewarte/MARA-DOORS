@extends('admin.layouts.app')

@section('title', 'Editar Grupo de Clientes')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/style_edit_create.css') }}">
@endpush

@section('content')
    <div class="container-fluid px-3 px-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Editar Grupo de Clientes</h1>
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
                <li class="breadcrumb-item active">Editar Grupo</li>
            </ol>
        </nav>

        <div class="form-container">
            <form action="{{ route('grupoclientes.update', ['grupocliente' => $grupocliente]) }}" method="post">
                @method('PATCH')
                @csrf   

                <div class="row g-3">

                    {{-- Nombre --}}
                    <div class="col-md-12">
                        <label for="nombre" class="form-label">Nombre del grupo:</label>
                        <input type="text"
                               name="nombre"
                               id="nombre"
                               class="form-control"
                               value="{{ old('nombre', $grupocliente->nombre) }}"
                               placeholder="Ingrese el nombre del grupo">

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
                                  class="form-control">{{ old('descripcion', $grupocliente->descripcion) }}</textarea>

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
                               value="{{ old('descuento_global', $grupocliente->descuento_global) }}"
                               min="0"
                               max="100"
                               step="0.01">

                        @error('descuento_global')
                            <small class="text-danger">{{ '*' . $message }}</small>
                        @enderror
                    </div>

                    {{-- Estado --}}
                    <div class="col-md-6">
                        <label for="estado" class="form-label">Estado:</label>
                        <select name="estado" id="estado" class="form-select">
                            <option value="1" {{ old('estado', $grupocliente->estado) == 1 ? 'selected' : '' }}>
                                Activo
                            </option>
                            <option value="0" {{ old('estado', $grupocliente->estado) == 0 ? 'selected' : '' }}>
                                Inactivo
                            </option>
                        </select>

                        @error('estado')
                            <small class="text-danger">{{ '*' . $message }}</small>
                        @enderror
                    </div>

                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-save me-2"></i>Actualizar
                    </button>

                    <a href="{{ route('grupoclientes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.form-control, .form-select');

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

    console.log('Grupo actual:', {
        nombre: '{{ $grupocliente->nombre }}',
        descripcion: '{{ $grupocliente->descripcion }}',
        descuento: '{{ $grupocliente->descuento_global }}',
        estado: '{{ $grupocliente->estado }}'
    });
});
</script>
@endpush
