@extends('admin.layouts.app')

@section('title', 'Editar Cotización')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/style_Categoria.css') }}">
    <style>
        .border-section { border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; margin-bottom: 15px; background: #fff; }
        .section-title { font-size: 16px; font-weight: 600; color: #2c3e50; margin-bottom: 15px; padding-bottom: 8px; border-bottom: 2px solid #e9ecef; }
        .section-title i { margin-right: 8px; color: #3498db; }
        .form-label { font-weight: 500; font-size: 13px; margin-bottom: 4px; color: #495057; }
        .form-control-sm { font-size: 13px; padding: 4px 8px; height: 32px; }

        /* Search Component */
        .search-wrapper { position: relative; }
        .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6c757d; z-index: 4; }
        #producto_search { padding-left: 35px; border-radius: 4px; }

        .products-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #dee2e6; border-radius: 4px; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.1); max-height: 350px; overflow-y: auto; display: none; }
        .product-item { padding: 10px 15px; cursor: pointer; border-bottom: 1px solid #f1f1f1; display: flex; justify-content: space-between; align-items: center; }
        .product-item:hover { background-color: #f8f9fa; }

        .product-selection-card { background-color: #f8fbff; border: 1px solid #d1e3ff; border-radius: 6px; padding: 15px; margin-bottom: 15px; display: none; }

        .btn-add-item { height: 32px; }
    </style>
@endpush

@section('content')
    @include('admin.layouts.partials.alert')

    <div class="container-fluid px-4 py-4">
        <div class="page-header mb-4">
            <div>
                <h1 class="page-title fs-3">Editar Cotización #{{ $cotizacion->numero_cotizacion }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none text-muted">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('cotizaciones.index') }}" class="text-decoration-none text-muted">Cotizaciones</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Editar #{{ $cotizacion->numero_cotizacion }}</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('cotizaciones.index') }}" class="btn btn-outline-secondary btn-sm px-4" style="border-radius: 8px;">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>

        <form action="{{ route('cotizaciones.update', $cotizacion->id) }}" method="post" id="cotizacionForm">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-lg-12">
                    <div class="border-section">
                        <div class="section-title"><i class="fas fa-info-circle"></i> Datos de la transacción</div>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">Cliente (Opcional)</label>
                                <select name="cliente_id" id="cliente_id" class="form-control selectpicker" data-live-search="true" data-style="btn-outline-secondary btn-sm" title="Seleccione un cliente">
                                    <option value="">Ninguno</option>
                                    @foreach ($clientes as $item)
                                        <option value="{{ $item->id }}" {{ $cotizacion->cliente_id == $item->id ? 'selected' : '' }}>{{ $item->persona->razon_social }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Proveedor (Opcional)</label>
                                <select name="proveedor_id" id="proveedor_id" class="form-control selectpicker" data-live-search="true" data-style="btn-outline-secondary btn-sm" title="Seleccione un proveedor">
                                    <option value="">Ninguno</option>
                                    @foreach ($proveedores as $item)
                                        <option value="{{ $item->id }}" {{ $cotizacion->proveedor_id == $item->id ? 'selected' : '' }}>{{ $item->persona->razon_social }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sucursal / Almacén</label>
                                <select name="almacen_id" class="form-control selectpicker" data-style="btn-outline-secondary btn-sm" required>
                                    @foreach ($almacenes as $item)
                                        <option value="{{ $item->id }}" {{ $cotizacion->almacen_id == $item->id ? 'selected' : '' }}>{{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Número de Cotización</label>
                                <input type="text" name="numero_cotizacion" class="form-control form-control-sm h-40" value="{{ $cotizacion->numero_cotizacion }}" required style="border-radius: 8px;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha de Emisión</label>
                                <input type="datetime-local" name="fecha_hora" class="form-control form-control-sm h-40" value="{{ $cotizacion->fecha_hora->format('Y-m-d\TH:i') }}" required style="border-radius: 8px;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Vencimiento</label>
                                <input type="date" name="vencimiento" class="form-control form-control-sm h-40" value="{{ $cotizacion->vencimiento ? $cotizacion->vencimiento->format('Y-m-d') : '' }}" style="border-radius: 8px;">
                            </div>
                        </div>
                    </div>

                    <div class="border-section">
                        <div class="section-title"><i class="fas fa-cubes"></i> Selección de Productos</div>

                        <div class="search-wrapper mb-4">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="producto_search" class="form-control" placeholder="Escribe el nombre o código del producto para buscar...">
                            <div class="products-dropdown" id="products_dropdown"></div>
                        </div>

                        <div class="product-selection-card" id="selection_card">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-bold fs-5 text-primary" id="sel_name">Producto Seleccionado</div>
                                <button type="button" class="btn-close" onclick="$('#selection_card').slideUp()"></button>
                            </div>
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Precio Unitario (Bs.)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light border-end-0">Bs.</span>
                                        <input type="number" id="sel_precio" class="form-control border-start-0" step="0.01">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Cantidad</label>
                                    <input type="number" id="sel_cantidad" class="form-control form-control-sm" value="1.000" step="0.001">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Descuento (%)</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" id="sel_descuento_porc" class="form-control border-end-0" value="0" step="0.01">
                                        <span class="input-group-text bg-light border-start-0">%</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" id="btn_add_item" class="btn btn-primary btn-add-item w-100">
                                        <i class="fas fa-plus-circle me-1"></i> Añadir a la lista
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="tabla_detalle" class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%" class="text-center py-3">#</th>
                                        <th class="py-3">Descripción del Producto</th>
                                        <th width="12%" class="text-center py-3">Cantidad</th>
                                        <th width="15%" class="text-end py-3">Precio Unit.</th>
                                        <th width="12%" class="text-end py-3">Desc.</th>
                                        <th width="18%" class="text-end py-3">Subtotal</th>
                                        <th width="5%" class="text-center py-3"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cotizacion->detalles as $index => $detalle)
                                        <tr id="row_pre_{{ $index }}">
                                            <td class="text-center text-muted small">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="fw-bold">{{ $detalle->producto->nombre }}</div>
                                                <div class="extra-small text-muted">{{ $detalle->producto->codigo }}</div>
                                                <input type="hidden" name="arrayidproducto[]" value="{{ $detalle->producto_id }}">
                                            </td>
                                            <td class="text-center">
                                                <input type="number" name="arraycantidad[]" class="form-control form-control-sm text-center t-qty" value="{{ number_format($detalle->cantidad, 3, '.', '') }}" step="0.001">
                                            </td>
                                            <td class="text-end">
                                                <input type="number" name="arraypreciounitario[]" class="form-control form-control-sm text-end t-price" value="{{ number_format($detalle->precio_unitario, 2, '.', '') }}" step="0.01">
                                            </td>
                                            <td class="text-end">
                                                <input type="number" name="arraydescuento[]" class="form-control form-control-sm text-end t-desc" value="{{ number_format($detalle->descuento, 2, '.', '') }}" step="0.01">
                                            </td>
                                            <td class="text-end fw-bold">
                                                <span class="text-dark">Bs. <span class="t-sub">{{ number_format(($detalle->cantidad * $detalle->precio_unitario) - $detalle->descuento, 2, '.', '') }}</span></span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-link text-danger p-0 delete-row" title="Quitar item"><i class="fas fa-times-circle fs-5"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row justify-content-end">
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between align-items-center p-2 bg-light border rounded">
                                    <span class="fw-bold">TOTAL COTIZACIÓN:</span>
                                    <span class="fs-5 fw-bold text-primary">Bs. <span id="label_total">{{ number_format($cotizacion->total, 2) }}</span></span>
                                    <input type="hidden" name="total" id="input_total" value="{{ $cotizacion->total }}">
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3 g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nota Interna (Privada):</label>
                                <textarea name="nota_personal" class="form-control form-control-sm" rows="2">{{ $cotizacion->nota_personal }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nota para el Cliente:</label>
                                <textarea name="nota_cliente" class="form-control form-control-sm" rows="2">{{ $cotizacion->nota_cliente }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('cotizaciones.index') }}" class="btn btn-outline-danger btn-sm px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary btn-sm px-5 fw-bold">
                                <i class="fas fa-save me-1"></i> Actualizar Cotización
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
    <script>
        $(document).ready(function() {
            const PRODUCTOS = JSON.parse(@json($productos->toJson()));
            let selectedItem = null;
            let rowCount = {{ $cotizacion->detalles->count() }};

            $('.selectpicker').selectpicker();

            // Cliente/Proveedor: solo uno a la vez
            $('#cliente_id').on('changed.bs.select', function() {
                if ($(this).val()) $('#proveedor_id').val('').selectpicker('refresh');
            });
            $('#proveedor_id').on('changed.bs.select', function() {
                if ($(this).val()) $('#cliente_id').val('').selectpicker('refresh');
            });

            // --- Búsqueda de Productos ---
            $('#producto_search').on('input', function() {
                const q = $(this).val().toLowerCase().trim();
                const dropdown = $('#products_dropdown');
                if (q.length < 1) { dropdown.hide(); return; }

                const matches = PRODUCTOS.filter(p =>
                    p.nombre.toLowerCase().includes(q) ||
                    p.codigo.toLowerCase().includes(q)
                ).slice(0, 10);

                dropdown.empty();

                if (matches.length === 0) {
                    dropdown.append('<div class="p-4 text-muted text-center small"><i class="fas fa-box-open d-block mb-2 fs-4"></i>No hay coincidencias</div>').show();
                    return;
                }

                matches.forEach(p => {
                    const priceV = parseFloat(p.precio_venta).toFixed(2);
                    const item = $(`
                        <div class="product-item">
                            <div>
                                <div class="fw-bold text-dark">${p.nombre}</div>
                                <div class="extra-small text-muted"><i class="fas fa-barcode me-1"></i>${p.codigo}</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-primary small">Bs. ${priceV}</div>
                                <div class="extra-small text-muted">Precio Sugerido</div>
                            </div>
                        </div>
                    `);
                    item.on('click', () => {
                        selectedItem = p;
                        $('#sel_name').text(p.nombre);
                        $('#sel_precio').val(p.precio_venta);
                        $('#selection_card').slideDown();
                        $('#products_dropdown').hide();
                        $('#producto_search').val('');
                        $('#sel_cantidad').focus().select();
                    });
                    dropdown.append(item);
                });
                dropdown.show();
            });

            // Cerrar dropdown al hacer clic fuera
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.search-wrapper').length) {
                    $('#products_dropdown').hide();
                }
            });

            $('#btn_add_item').on('click', function() {
                if (!selectedItem) return;
                const qty = parseFloat($('#sel_cantidad').val()) || 0;
                const price = parseFloat($('#sel_precio').val()) || 0;
                const desc_porc = parseFloat($('#sel_descuento_porc').val()) || 0;

                if (qty <= 0) { Swal.fire("Atención", "Especifique una cantidad válida", "warning"); return; }

                addItem(selectedItem, qty, price, desc_porc);
                $('#selection_card').hide();
                selectedItem = null;
                $('#producto_search').focus();
            });

            function addItem(p, qty, price, desc_porc) {
                rowCount++;
                const subTotalBruto = qty * price;
                const descMonto = subTotalBruto * (desc_porc / 100);
                const subValue = (subTotalBruto - descMonto);

                const row = `
                    <tr id="row_${rowCount}">
                        <td class="text-center text-muted small">${rowCount}</td>
                        <td>
                            <div class="fw-bold">${p.nombre}</div>
                            <div class="extra-small text-muted">${p.codigo}</div>
                            <input type="hidden" name="arrayidproducto[]" value="${p.id}">
                        </td>
                        <td class="text-center">
                            <input type="number" name="arraycantidad[]" class="form-control form-control-sm text-center t-qty" value="${qty.toFixed(3)}" step="0.001">
                        </td>
                        <td class="text-end">
                            <input type="number" name="arraypreciounitario[]" class="form-control form-control-sm text-end t-price" value="${price.toFixed(2)}" step="0.01">
                        </td>
                        <td class="text-end">
                            <input type="number" name="arraydescuento[]" class="form-control form-control-sm text-end t-desc" value="${descMonto.toFixed(2)}" step="0.01">
                        </td>
                        <td class="text-end fw-bold">
                            <span class="text-dark">Bs. <span class="t-sub">${subValue.toFixed(2)}</span></span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-link text-danger p-0 delete-row" title="Quitar item"><i class="fas fa-times-circle fs-5"></i></button>
                        </td>
                    </tr>
                `;
                $('#tabla_detalle tbody').append(row);
                updateTotals();
            }

            $(document).on('input', '.t-qty, .t-price, .t-desc', function() {
                const tr = $(this).closest('tr');
                const q = parseFloat(tr.find('.t-qty').val()) || 0;
                const p = parseFloat(tr.find('.t-price').val()) || 0;
                const d = parseFloat(tr.find('.t-desc').val()) || 0;
                const sub = (q * p) - d;
                tr.find('.t-sub').text(sub.toFixed(2));
                updateTotals();
            });

            $(document).on('click', '.delete-row', function() {
                $(this).closest('tr').fadeOut(200, function() {
                    $(this).remove();
                    updateTotals();
                });
            });

            function updateTotals() {
                let total = 0;
                $('.t-sub').each(function() { total += parseFloat($(this).text()) || 0; });
                $('#label_total').text(total.toLocaleString('en-US', { minimumFractionDigits: 2 }));
                $('#input_total').val(total.toFixed(2));
            }

            // Enter en cantidad para añadir
            $('#sel_cantidad, #sel_precio, #sel_descuento_porc').on('keypress', function(e) {
                if (e.which == 13) {
                    $('#btn_add_item').click();
                    return false;
                }
            });
        });
    </script>
@endpush
