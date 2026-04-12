@extends('admin.layouts.app')

@section('title', 'Crear usuario')

@push('css')
    <style>
        /* Estilos consistentes con el sistema */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        h1 {
            color: #2c3e50;
            font-size: 2rem;
            margin: 1.5rem 0;
            text-align: center;
            font-weight: 700;
        }

        /* Breadcrumb */
        .breadcrumb {
            background-color: #f4f6f9;
            border-radius: 6px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
        }

        .breadcrumb-item a {
            color: #3498db;
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb-item a:hover {
            color: #2980b9;
        }

        /* Tarjeta */
        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 2rem;
            border: 1px solid #e1e1e1;
        }

        .card-header {
            background-color: #34495e;
            color: #fff;
            padding: 1rem 1.5rem;
            font-size: 1rem;
            border-bottom: 2px solid #2c3e50;
        }

        .card-body {
            padding: 1.5rem;
            background-color: #f8f9fa;
        }

        .card-footer {
            padding: 1rem 1.5rem;
            background-color: #f4f6f9;
            border-top: 1px solid #e1e1e1;
            text-align: center;
        }

        /* Formulario */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            align-items: flex-start;
        }

        .form-label {
            width: 16.666%;
            padding-right: 15px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .form-input-group {
            width: 33.333%;
        }

        .form-help-text {
            width: 33.333%;
            color: #6c757d;
            font-size: 0.875rem;
            padding: 0 15px;
        }

        .form-error-text {
            width: 16.666%;
            color: #dc3545;
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Campos de formulario */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.3s;
            background-color: #fff;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        select:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
        }

        /* Botones */
        .btn-primary {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-1px);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .form-label {
                width: 25%;
            }

            .form-input-group {
                width: 75%;
            }

            .form-help-text {
                width: 100%;
                margin-top: 0.5rem;
                padding: 0;
            }

            .form-error-text {
                width: 100%;
                margin-top: 0.25rem;
            }
        }

        @media (max-width: 768px) {

            .form-label,
            .form-input-group {
                width: 100%;
                padding-right: 0;
            }

            .form-label {
                margin-bottom: 0.5rem;
            }

            .container {
                padding: 0 15px;
            }

            h1 {
                font-size: 1.75rem;
            }
        }

        /* Mejoras de accesibilidad */
        input:focus,
        select:focus {
            outline: 2px solid #3498db;
            outline-offset: 2px;
        }

        /* Mensajes de error */
        small {
            display: block;
            margin-top: 0.25rem;
        }
    </style>
@endpush

@section('content')
    @include('admin.layouts.partials.alert')

    <div class="container">
        <h1>Crear Usuario</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Usuarios</a></li>
            <li class="breadcrumb-item active">Crear Usuario</li>
        </ol>

        <div class="card">
            <form action="{{ route('users.store') }}" method="post">
                @csrf

                <div class="card-body">

                    <!-- Nombre -->
                    <div class="form-row">
                        <label for="name" class="form-label">Nombres:</label>
                        <div class="form-input-group">
                            <input autocomplete="off" type="text" name="name" id="name"
                                value="{{ old('name') }}" aria-labelledby="nameHelpBlock">
                        </div>
                        <div class="form-help-text" id="nameHelpBlock">
                            Escriba un solo nombre
                        </div>
                        <div class="form-error-text">
                            @error('name')
                                <small>{{ '*' . $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="form-row">
                        <label for="email" class="form-label">Email:</label>
                        <div class="form-input-group">
                            <input autocomplete="off" type="email" name="email" id="email"
                                value="{{ old('email') }}" aria-labelledby="emailHelpBlock">
                        </div>
                        <div class="form-help-text" id="emailHelpBlock">
                            Dirección de correo electrónico
                        </div>
                        <div class="form-error-text">
                            @error('email')
                                <small>{{ '*' . $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-row">
                        <label for="password" class="form-label">Contraseña:</label>
                        <div class="form-input-group">
                            <input type="password" name="password" id="password" aria-labelledby="passwordHelpBlock">
                        </div>
                        <div class="form-help-text" id="passwordHelpBlock">
                            Escriba una contraseña segura. Debe incluir números.
                        </div>
                        <div class="form-error-text">
                            @error('password')
                                <small>{{ '*' . $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-row">
                        <label for="password_confirmation" class="form-label">Confirmar:</label>
                        <div class="form-input-group">
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                aria-labelledby="passwordConfirmationHelpBlock">
                        </div>
                        <div class="form-help-text" id="passwordConfirmationHelpBlock">
                            Vuelva a escribir su contraseña.
                        </div>
                        <div class="form-error-text">
                            @error('password_confirmation')
                                <small>{{ '*' . $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <!-- Roles -->
                    <div class="form-row">
                        <label for="role" class="form-label">Rol:</label>
                        <div class="form-input-group">
                            <select name="role" id="role" aria-labelledby="rolHelpBlock">
                                <option value="" selected disabled>Seleccione:</option>
                                @foreach ($roles as $item)
                                    <option value="{{ $item->name }}" @selected(old('role') == $item->name)>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-help-text" id="rolHelpBlock">
                            Escoja un rol para el usuario.
                        </div>
                        <div class="form-error-text">
                            @error('role')
                                <small>{{ '*' . $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <!-- Almacen -->
                    <div class="form-row" id="almacen_row" style="display: none;">
                        <label for="almacen_id" class="form-label">Almacen:</label>
                        <div class="form-input-group">
                            <select name="almacen_id" id="almacen_id" aria-labelledby="almacenHelpBlock">
                                <option value="" selected>Sin Almacen asignado</option>
                                @foreach ($almacenes as $almacen)
                                    <option value="{{ $almacen->id }}" @selected(old('almacen_id') == $almacen->id)>
                                        {{ $almacen->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-help-text" id="almacenHelpBlock">
                            Seleccione el Almacen donde trabaja (opcional para administrador).
                        </div>
                        <div class="form-error-text">
                            @error('almacen_id')
                                <small>{{ '*' . $message }}</small>
                            @enderror
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('js')
<script>
    const roleSelect = document.getElementById('role');
    const almacenRow = document.getElementById('almacen_row');
    const almacenSelect = document.getElementById('almacen_id');

    // Roles de administrador (no necesitan Almacen)
    const adminRoles = ['Super Admin', 'Administrador', 'Admin'];

    function updateAlmacenField() {
        const selectedRole = roleSelect.value;
        const isAdmin = adminRoles.some(role => selectedRole.toLowerCase().includes(role.toLowerCase()));
        
        if (isAdmin) {
            almacenRow.style.display = 'none';
            almacenSelect.value = '';
        } else {
            almacenRow.style.display = 'flex';
        }
    }

    roleSelect.addEventListener('change', updateAlmacenField);

    // Ejecutar al cargar la pagina si hay un rol pre-seleccionado
    document.addEventListener('DOMContentLoaded', updateAlmacenField);
</script>
@endpush

