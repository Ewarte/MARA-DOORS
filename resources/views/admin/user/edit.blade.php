@extends('admin.layouts.app')

@section('title','Editar usuario')

@push('css')
<style>
    /* Estilos base consistentes con el sistema */
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
    }

    .breadcrumb {
        display: flex;
        padding: 0.75rem 1rem;
        margin-bottom: 1.5rem;
        list-style: none;
        background-color: #f4f6f9;
        border-radius: 6px;
    }

    .breadcrumb-item {
        display: flex;
        align-items: center;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "/";
        display: inline-block;
        padding: 0 0.5rem;
        color: #7b8a8b;
    }

    .breadcrumb-item a {
        color: #3498db;
        text-decoration: none;
        transition: color 0.2s;
    }

    .breadcrumb-item a:hover {
        color: #2980b9;
    }

    /* Estilos de la tarjeta */
    .card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 2rem;
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
    }

    .card-footer {
        padding: 1rem 1.5rem;
        background-color: #f4f6f9;
        border-top: 1px solid #e1e1e1;
        text-align: center;
    }

    /* Estilos del formulario */
    .form-row {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
        align-items: center;
    }

    .form-label {
        width: 16.666%;
        padding-right: 15px;
        font-weight: 500;
        color: #2c3e50;
    }

    .form-input-group {
        width: 33.333%;
    }

    .form-help-text {
        width: 33.333%;
        color: #7b8a8b;
        font-size: 0.875rem;
    }

    .form-error-text {
        width: 16.666%;
        color: #e74c3c;
        font-size: 0.875rem;
    }

    /* Elementos del formulario */
    input[type="text"],
    input[type="email"],
    input[type="password"],
    select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus,
    select:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
    }

    select {
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1rem;
    }

    /* Boton primario */
    .btn-primary {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 4px;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.2s, transform 0.2s;
    }

    .btn-primary:hover {
        background-color: #2980b9;
        transform: translateY(-1px);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-label,
        .form-input-group,
        .form-help-text,
        .form-error-text {
            width: 100%;
            padding-right: 0;
        }

        .form-label {
            margin-bottom: 0.5rem;
        }

        .form-help-text {
            margin-top: 0.5rem;
        }

        .form-error-text {
            margin-top: 0.25rem;
        }
    }
</style>

@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Editar Usuario</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index')}}">Usuarios</a></li>
        <li class="breadcrumb-item active">Editar Usuario</li>
    </ol>

    <div class="card text-bg-light">
        <form action="{{ route('users.update',['user' => $user]) }}" method="post">
            @method('PATCH')
            @csrf
            <div class="card-header">
                <p class="">Nota: Los usuarios son los que pueden ingresar al sistema</p>
            </div>
            <div class="card-body">
                <!---Nombre---->
                <div class="row mb-4">
                    <label for="name" class="col-lg-2 col-form-label">Nombres:</label>
                    <div class="col-lg-4">
                        <input type="text" name="name" id="name" class="form-control" value="{{old('name',$user->name)}}">
                    </div>
                    <div class="col-lg-4">
                        <div class="form-text">
                            Escriba un solo nombre
                        </div>
                    </div>
                    <div class="col-lg-2">
                        @error('name')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>
                </div>

                <!---Email---->
                <div class="row mb-4">
                    <label for="email" class="col-lg-2 col-form-label">Email:</label>
                    <div class="col-lg-4">
                        <input type="email" name="email" id="email" class="form-control" value="{{old('email',$user->email)}}">
                    </div>
                    <div class="col-lg-4">
                        <div class="form-text">
                            Direccion de correo electronico
                        </div>
                    </div>
                    <div class="col-lg-2">
                        @error('email')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>
                </div>

                <!---Password---->
                <div class="row mb-4">
                    <label for="password" class="col-lg-2 col-form-label">Contrasena:</label>
                    <div class="col-lg-4">
                        <input type="password" name="password" id="password" class="form-control">
                    </div>
                    <div class="col-lg-4">
                        <div class="form-text">
                            Escriba una contrasena segura. Debe incluir numeros.
                        </div>
                    </div>
                    <div class="col-lg-2">
                        @error('password')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>
                </div>

                <!---Confirm_Password---->
                <div class="row mb-4">
                    <label for="password_confirmation" class="col-lg-2 col-form-label">Confirmar:</label>
                    <div class="col-lg-4">
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                    </div>
                    <div class="col-lg-4">
                        <div class="form-text">
                            Vuelva a escribir su contrasena.
                        </div>
                    </div>
                    <div class="col-lg-2">
                        @error('password_confirmation')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>
                </div>

                <!---Roles---->
                <div class="row mb-4">
                    <label for="role" class="col-lg-2 col-form-label">Seleccionar rol: </label>
                    <div class="col-lg-4">
                        <select name="role" id="role" class="form-select">
                            @foreach ($roles as $item)
                            @if ( in_array($item->name,$user->roles->pluck('name')->toArray()) )
                            <option selected value="{{$item->name}}" @selected(old('role')==$item->name)>{{$item->name}}</option>
                            @else
                            <option value="{{$item->name}}" @selected(old('role')==$item->name)>{{$item->name}}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-text">
                            Escoja un rol para el usuario.
                        </div>
                    </div>
                    <div class="col-lg-2">
                        @error('role')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>
                </div>

                <!---Almacen---->
                <div class="row mb-4" id="almacen_row">
                    <label for="almacen_id" class="col-lg-2 col-form-label">Almacen:</label>
                    <div class="col-lg-4">
                        <select name="almacen_id" id="almacen_id" class="form-select">
                            <option value="">Sin Almacen asignado</option>
                            @foreach ($almacenes as $almacen)
                                <option value="{{ $almacen->id }}" @selected(old('almacen_id', $user->almacen_id) == $almacen->id)>
                                    {{ $almacen->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-text">
                            Seleccione el Almacen donde trabaja (opcional para administrador).
                        </div>
                    </div>
                    <div class="col-lg-2">
                        @error('almacen_id')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>
                </div>

            </div>
            <div class="card-footer text-center">
                <button type="submit" class="btn btn-primary">Actualizar</button>
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
    document.addEventListener('DOMContentLoaded', updateAlmacenField);
</script>
@endpush

