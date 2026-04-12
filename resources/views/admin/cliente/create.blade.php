@extends('admin.layouts.app')

@section('title','Crear cliente')

@push('css')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
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

    #box-razon-social {
        display: none;
        background: rgba(52, 152, 219, 0.05);
        border-radius: 8px;
        padding: 1rem;
        border-left: 4px solid var(--accent-color);
        margin: 1rem 0;
        transition: all 0.3s ease;
    }

    .form-select {
        cursor: pointer;
    }

    .form-select option {
        padding: 0.5rem;
    }

    /* Animación para mostrar/ocultar */
    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
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

        .btn-primary {
            width: 100%;
        }

        .row.g-3 {
            --bs-gutter-x: 1rem;
            --bs-gutter-y: 1rem;
        }
    }

    /* Estilos para los labels dinámicos */
    #label-natural, #label-juridica {
        font-weight: 600;
        color: var(--primary-color);
    }

    /* Efectos de hover en inputs */
    .form-control:hover, .form-select:hover {
        border-color: #adb5bd;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Crear Cliente</h1>
        <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('clientes.index')}}">Clientes</a></li>
            <li class="breadcrumb-item active">Crear cliente</li>
        </ol>
    </nav>

    <div class="form-container">
        <form action="{{ route('clientes.store') }}" method="post">
            @csrf
            <div class="row g-3">
                <!----Tipo de persona----->
                <div class="col-md-6">
                    <label for="tipo_persona" class="form-label">Tipo de cliente:</label>
                    <select class="form-select" name="tipo_persona" id="tipo_persona">
                        <option value="" selected disabled>Seleccione una opción</option>
                        <option value="natural" {{ old('tipo_persona') == 'natural' ? 'selected' : '' }}>Persona natural</option>
                        <option value="juridica" {{ old('tipo_persona') == 'juridica' ? 'selected' : '' }}>Persona jurídica</option>
                    </select>
                    @error('tipo_persona')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="grupo_cliente_id" class="form-label">Grupo de cliente:</label>
                    <select class="form-select" name="grupo_cliente_id" id="grupo_cliente_id" required>
                        <option value="" selected disabled>Seleccione un grupo</option>
                        @foreach ($grupos as $grupo)
                            <option value="{{ $grupo->id }}" {{ old('grupo_cliente_id') == $grupo->id ? 'selected' : '' }}>
                                {{ $grupo->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('grupo_cliente_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <!-------Razón social------->
                <div class="col-12" id="box-razon-social">
                    <label id="label-natural" for="razon_social" class="form-label">Nombres y apellidos:</label>
                    <label id="label-juridica" for="razon_social" class="form-label">Nombre de la empresa:</label>

                    <input required type="text" name="razon_social" id="razon_social" class="form-control"
                           value="{{ old('razon_social') }}" placeholder="Ingrese la información correspondiente">

                    @error('razon_social')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <!------Dirección---->
                <div class="col-12">
                    <label for="direccion" class="form-label">Dirección:</label>
                    <input required type="text" name="direccion" id="direccion" class="form-control"
                           value="{{ old('direccion') }}" placeholder="Ingrese la dirección completa">
                    @error('direccion')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <!------Teléfono---->
                <div class="col-12">
                    <label for="telefono" class="form-label">Teléfono:</label>
                    <input required type="text" name="telefono" id="telefono" class="form-control"
                           value="{{ old('telefono') }}" placeholder="Ingrese el número de teléfono">
                    @error('telefono')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <!--------------Documento------->
                <div class="col-md-6">
                    <label for="documento_id" class="form-label">Tipo de documento:</label>
                    <select class="form-select" name="documento_id" id="documento_id">
                        <option value="" selected disabled>Seleccione el tipo de documento</option>
                        @foreach ($documentos as $item)
                        <option value="{{$item->id}}"
                            {{ old('documento_id') == $item->id ? 'selected' : '' }}>{{$item->tipo_documento}}</option>
                        @endforeach
                    </select>
                    @error('documento_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="numero_documento" class="form-label">Número de documento:</label>
                    <input required type="text" name="numero_documento" id="numero_documento" class="form-control"
                           value="{{ old('numero_documento') }}" placeholder="Ingrese el número de documento">
                    @error('numero_documento')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Guardar Cliente
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        // Ocultar ambos labels inicialmente
        $('#label-natural').hide();
        $('#label-juridica').hide();

        $('#tipo_persona').on('change', function() {
            let selectValue = $(this).val();

            if (selectValue == 'natural') {
                $('#label-juridica').hide();
                $('#label-natural').show().addClass('fade-in');
                $('#razon_social').attr('placeholder', 'Ej: Juan Pérez López');
            } else if (selectValue == 'juridica') {
                $('#label-natural').hide();
                $('#label-juridica').show().addClass('fade-in');
                $('#razon_social').attr('placeholder', 'Ej: Empresa XYZ S.A.C.');
            }

            $('#box-razon-social').show().addClass('fade-in');
        });

        // Efectos de focus en inputs
        $('.form-control, .form-select').on('focus', function() {
            $(this).css('transform', 'translateY(-1px)');
        }).on('blur', function() {
            $(this).css('transform', 'translateY(0)');
        });
    });
</script>
@endpush

