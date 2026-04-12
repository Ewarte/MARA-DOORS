@extends('admin.layouts.app')

@section('title', 'Crear Traslado')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/style_Traslado_create.css') }}">
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Crear Traslado</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('traslados.index') }}">Traslados</a></li>
        <li class="breadcrumb-item active">Crear Traslado</li>
    </ol>
</div>

<form action="{{ route('traslados.store') }}" method="POST" id="trasladoForm">
    @csrf
    <div class="container-lg mt-4">

        <!-- Datos Generales -->
        <div class="border-section mb-4">
            <div class="section-title"><i class="fas fa-info-circle"></i> Datos Generales del Traslado</div>
            <div class="row g-3">

                <!-- Almacén Origen -->
                <div class="col-md-6">
                    <label for="origen_almacen_id" class="form-label">Almacén Origen:</label>
                    <select name="origen_almacen_id" id="origen_almacen_id" class="form-control" required>
                        <option value="">Seleccione almacén origen</option>
                        @foreach ($almacenes as $almacen)
                        <option value="{{ $almacen->id }}" {{ old('origen_almacen_id') == $almacen->id ? 'selected' : '' }}>
                            {{ $almacen->nombre }}
                        </option>
                        @endforeach
                    </select>
                    @error('origen_almacen_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <!-- Almacén Destino -->
                <div class="col-md-6">
                    <label for="destino_almacen_id" class="form-label">Almacén Destino:</label>
                    <select name="destino_almacen_id" id="destino_almacen_id" class="form-control" required>
                        <option value="">Seleccione almacén destino</option>
                        @foreach ($almacenesDestino as $almacen)
                        <option value="{{ $almacen->id }}" {{ old('destino_almacen_id') == $almacen->id ? 'selected' : '' }}>
                            {{ $almacen->nombre }}
                        </option>
                        @endforeach
                    </select>
                    @error('destino_almacen_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <!-- Fecha y Hora -->
                <div class="col-md-6">
                    <label class="form-label">Fecha y Hora:</label>
                    <input type="text" class="form-control" disabled value="{{ now()->format('d/m/Y H:i:s') }}">
                    <input type="hidden" name="fecha_hora" value="{{ now()->format('Y-m-d H:i:s') }}">
                </div>

                <!-- Costo de Envío -->
                <div class="col-md-6">
                    <label for="costo_envio" class="form-label">Costo de Envío:</label>
                    <input type="number" name="costo_envio" id="costo_envio" class="form-control" 
                        min="0" step="0.01" value="{{ old('costo_envio', '0.00') }}" required>
                    @error('costo_envio') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

            </div>
        </div>

        <!-- Productos -->
        <div class="border-section" id="productos_section" style="display: none;">
            <div class="section-title"><i class="fas fa-boxes"></i> Productos del Traslado</div>

            <div class="product-search-container mb-3">
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="producto_search" class="form-control" placeholder="Buscar producto por nombre o código...">
                    <div class="products-dropdown" id="products_dropdown"></div>
                </div>
            </div>

            <div class="detail-inputs mb-3">
                <div class="input-group-custom">
                    <label for="stock" class="form-label">Stock Disponible:</label>
                    <input disabled id="stock" type="text" class="form-control" placeholder="0">
                </div>
                <div class="input-group-custom">
                    <label for="cantidad" class="form-label">Cantidad:</label>
                    <input type="number" id="cantidad" class="form-control" min="1" step="1" value="1">
                </div>
                <div>
                    <label class="form-label" style="visibility: hidden;">Acción</label>
                    <button type="button" id="btn_agregar" class="btn btn-primary w-100">
                        <i class="fas fa-plus-circle"></i> Agregar
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Stock</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbody_detalle"></tbody>
                </table>
            </div>

            <div id="sin_productos" class="alert alert-info text-center mt-3" style="display: none;">
                <i class="fas fa-info-circle"></i> No hay productos agregados aún
            </div>
        </div>

        <!-- Botones -->
        <div class="row mt-4 mb-4">
            <div class="col-md-6">
                <a href="{{ route('traslados.index') }}" class="btn btn-secondary w-100">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
            </div>
            <div class="col-md-6">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-save"></i> Guardar Traslado
                </button>
            </div>
        </div>

    </div>
</form>
@endsection

@push('js')
<script>
    window.productosData = @json($productos);
</script>

<script src="{{ asset('js/traslados.js') }}"></script>

@endpush

