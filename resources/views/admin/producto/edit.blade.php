@extends('admin.layouts.app')

@section('title', 'Editar Producto')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<link rel="stylesheet" href="{{ asset('css/style_create_edit_Producto.css') }}">
@endpush

@section('content')
<div class="container-fluid px-2 px-md-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Editar Producto</h1>
        <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="form-container">
        <form action="{{ route('productos.update', $producto) }}" method="POST">
            @csrf
            @method('PUT') {{-- IMPORTANTE --}}

            <div class="row g-3">

                <!-- Codigo (solo lectura) -->
                <div class="col-md-6">
                    <label class="form-label">Código:</label>
                    <input type="text"
                           class="form-control"
                           value="{{ $producto->codigo }}"
                           disabled>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nombre:</label>
                    <input type="text"
                        name="nombre"
                        class="form-control"
                        value="{{ old('nombre', $producto->nombre) }}">
                    @error('nombre') <div class="text-danger">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Precio Compra:</label>
                    <div class="input-group">
                        <span class="input-group-text">Bs</span>
                        <input type="number" step="0.01"
                            name="precio_compra"
                            class="form-control @error('precio_compra') is-invalid @enderror"
                            value="{{ old('precio_compra', $producto->precio_compra) }}">
                    </div>
                    @error('precio_compra')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Precio Venta:</label>
                    <div class="input-group">
                        <span class="input-group-text">Bs</span>
                        <input type="number" step="0.01"
                            name="precio_venta"
                            class="form-control @error('precio_venta') is-invalid @enderror"
                            value="{{ old('precio_venta', $producto->precio_venta) }}">
                    </div>
                    @error('precio_venta')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>


                <div class="col-12">
                    <label class="form-label">Descripción:</label>
                    <textarea name="descripcion"
                        rows="2"
                        class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $producto->descripcion) }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>


                <div class="col-md-6">
                    <label class="form-label">Marca:</label>
                    <select name="marca_id"
                        class="form-control selectpicker @error('marca_id') is-invalid @enderror">
                        @foreach($marcas as $marca)
                            <option value="{{ $marca->id }}"
                                {{ old('marca_id', $producto->marca_id) == $marca->id ? 'selected' : '' }}>
                                {{ $marca->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('marca_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>


                <div class="col-md-6">
                    <label class="form-label">Tipo Unidad:</label>
                    <select name="tipounidad_id" class="form-control selectpicker">
                        @foreach($tipounidades as $tipo)
                            <option value="{{ $tipo->id }}"
                                {{ $producto->tipounidad_id == $tipo->id ? 'selected' : '' }}>
                                {{ $tipo->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipounidad_id')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Categoria:</label>
                    <select name="categoria_id" class="form-control selectpicker">
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}"
                                {{ $producto->categoria_id == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('categoria_id')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-edit me-2"></i>Actualizar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>

<script>
$(function () {
    $('.selectpicker').selectpicker();
});
</script>
@endpush

