@extends('admin.layouts.app')

@section('title','Editar cliente')

@push('css')
<style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #34495e;
        --accent-color: #3498db;
        --light-color: #ecf0f1;
        --text-color: #2c3e50;
    }

    .form-container {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(44, 62, 80, 0.1);
        border: 1px solid #e9ecef;
        max-width: 800px;
        margin: 0 auto;
    }

    .info-header {
        background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
        color: white;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        border-left: 4px solid var(--accent-color);
    }

    .info-header p {
        margin: 0;
        font-size: 0.95rem;
    }

    .info-header .fw-bold {
        color: #3498db;
        text-transform: capitalize;
    }

    .form-label {
        color: var(--primary-color);
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        background-color: #ffffff;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        color: var(--text-color);
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        background-color: #ffffff;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--accent-color) 0%, #2980b9 100%);
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 8px;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        transition: all 0.3s ease;
        min-width: 120px;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        color: white;
    }

    .btn-secondary:hover {
        transform: translateY(-2px);
        color: white;
    }

    small.text-danger {
        color: #e74c3c !important;
        font-size: 0.8rem;
        font-weight: 500;
        display: block;
        margin-top: 0.25rem;
    }

    .breadcrumb {
        background-color: transparent;
        padding: 0.5rem 0;
        margin-bottom: 1.5rem;
    }

    .breadcrumb-item a {
        color: #7f8c8d;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .breadcrumb-item a:hover {
        color: var(--accent-color);
    }

    h1 {
        color: var(--primary-color);
        font-weight: 700;
        font-size: 2rem;
        margin-bottom: 1.5rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .form-container {
            padding: 1.5rem;
            margin: 0 1rem;
        }

        h1 {
            font-size: 1.5rem;
        }

        .btn-primary, .btn-secondary {
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .row.g-3 {
            --bs-gutter-x: 1rem;
            --bs-gutter-y: 1rem;
        }
    }

    /* Efectos de hover */
    .form-control:hover, .form-select:hover {
        border-color: #adb5bd;
    }

    /* Estilo para el tipo de cliente */
    .client-type-badge {
        background: rgba(52, 152, 219, 0.1);
        color: var(--accent-color);
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-left: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Editar Cliente</h1>
        <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('clientes.index')}}">Clientes</a></li>
            <li class="breadcrumb-item active">Editar cliente</li>
        </ol>
    </nav>

    <div class="form-container">
        <form action="{{ route('clientes.update',['cliente'=>$cliente]) }}" method="post">
            @method('PATCH')
            @csrf

            <!-- Información del tipo de cliente -->
            <div class="info-header">
                <p>Tipo de cliente: <span class="fw-bold">{{ strtoupper($cliente->persona->tipo_persona) }}</span>
                <span class="client-type-badge">
                    @if($cliente->persona->tipo_persona == 'natural')
                    Persona Natural
                    @else
                    Persona Jurídica
                    @endif
                </span>
                </p>
            </div>

            <div class="row g-3">
                <!-------Razón social------->
                <div class="col-12">
                    @if ($cliente->persona->tipo_persona == 'natural')
                    <label for="razon_social" class="form-label">Nombres y apellidos:</label>
                    <input required type="text" name="razon_social" id="razon_social" class="form-control"
                           value="{{ old('razon_social', $cliente->persona->razon_social) }}"
                           placeholder="Ingrese nombres y apellidos completos">
                    @else
                    <label for="razon_social" class="form-label">Nombre de la empresa:</label>
                    <input required type="text" name="razon_social" id="razon_social" class="form-control"
                           value="{{ old('razon_social', $cliente->persona->razon_social) }}"
                           placeholder="Ingrese el nombre de la empresa">
                    @endif

                    @error('razon_social')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <!------Dirección---->
                <div class="col-12">
                    <label for="direccion" class="form-label">Dirección:</label>
                    <input required type="text" name="direccion" id="direccion" class="form-control"
                           value="{{ old('direccion', $cliente->persona->direccion) }}"
                           placeholder="Ingrese la dirección completa">
                    @error('direccion')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="telefono" class="form-label">Teléfono:</label>
                    <input required type="text" name="telefono" id="telefono" class="form-control"
                           value="{{ old('telefono', $cliente->persona->telefono) }}"
                           placeholder="Ingrese el número de teléfono">
                    @error('telefono')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="grupo_cliente_id" class="form-label">Grupo de cliente:</label>
                    <select class="form-select" name="grupo_cliente_id" id="grupo_cliente_id" required>
                        @foreach ($grupos as $grupo)
                            <option value="{{ $grupo->id }}"
                                {{ (old('grupo_cliente_id', $cliente->grupo_cliente_id) == $grupo->id) ? 'selected' : '' }}>
                                {{ $grupo->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('grupo_cliente_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="estado" class="form-label">Estado:</label>
                    <select name="estado" id="estado" class="form-select" required>
                        <option value="1" {{ old('estado', $cliente->persona->estado) == 1 ? 'selected' : '' }}>
                            Activo
                        </option>
                        <option value="0" {{ old('estado', $cliente->persona->estado) == 0 ? 'selected' : '' }}>
                            Inactivo
                        </option>
                    </select>
                    @error('estado')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <!--------------Documento------->
                <div class="col-md-6">
                    <label for="documento_id" class="form-label">Tipo de documento:</label>
                    <select class="form-select" name="documento_id" id="documento_id">
                        @foreach ($documentos as $item)
                        <option value="{{$item->id}}"
                            {{ ($cliente->persona->documento_id == $item->id || old('documento_id') == $item->id) ? 'selected' : '' }}>
                            {{$item->tipo_documento}}
                        </option>
                        @endforeach
                    </select>
                    @error('documento_id')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="numero_documento" class="form-label">Número de documento:</label>
                    <input required type="text" name="numero_documento" id="numero_documento" class="form-control"
                           value="{{ old('numero_documento', $cliente->persona->numero_documento) }}"
                           placeholder="Ingrese el número de documento">
                    @error('numero_documento')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-save me-2"></i>Actualizar Cliente
                </button>
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
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
        // Efectos de focus en inputs
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

        // Mostrar información del cliente en consola para debugging
        console.log('Cliente actual:', {
            tipo: '{{ $cliente->persona->tipo_persona }}',
            razon_social: '{{ $cliente->persona->razon_social }}',
            documento: '{{ $cliente->persona->numero_documento }}'
        });
    });
</script>
@endpush

