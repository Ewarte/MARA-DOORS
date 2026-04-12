@extends('admin.layouts.app')

@section('title', 'Realizar venta')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .border-section {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background: #fff;
        }
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e9ecef;
        }
        .section-title i {
            margin-right: 8px;
            color: #3498db;
        }
        .form-label {
            font-weight: 500;
            font-size: 13px;
            margin-bottom: 4px;
            color: #495057;
        }
        .form-control-sm {
            font-size: 13px;
            padding: 4px 8px;
            height: 32px;
        }
        .table th {
            font-size: 13px;
            padding: 10px 8px;
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .table td {
            font-size: 13px;
            padding: 8px;
            vertical-align: middle;
        }

        /* Search Component */
        .search-wrapper { position: relative; }
        .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6c757d; z-index: 4; }
        #producto_search { padding-left: 35px; border-radius: 4px; }

        .products-dropdown {
            position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #dee2e6;
            border-radius: 4px; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.1); max-height: 350px; overflow-y: auto; display: none;
        }
        .product-item {
            padding: 10px 15px; cursor: pointer; border-bottom: 1px solid #f1f1f1; display: flex; justify-content: space-between; align-items: center;
        }
        .product-item:hover, .product-item.active { background-color: #f8f9fa; }
        .product-item.disabled { opacity: 0.5; cursor: not-allowed; }
        .product-item .prod-main { font-weight: 500; color: #333; font-size: 13px; }
        .product-item .prod-sub { font-size: 11px; color: #6c757d; }
        .product-item .prod-price { font-weight: 600; color: #2c3e50; font-size: 12px; }

        /* Selection Card */
        .product-selection-card {
            background-color: #f8fbff; border: 1px solid #d1e3ff; border-radius: 6px; padding: 15px; margin-bottom: 15px; display: none;
        }
        .selection-title { font-size: 14px; font-weight: 700; color: #0056b3; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }

        .badge-stock { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .stock-ok { background-color: #d1ecf1; color: #0c5460; }
        .stock-low { background-color: #fff3cd; color: #856404; }
        .stock-out { background-color: #f8d7da; color: #721c24; }

        .h-32 { height: 32px !important; }
    </style>
@endpush

@section('content')
    @include('admin.layouts.partials.alert')
    <div class="container-fluid px-4">
        <h1 class="mt-4 fs-4 fw-bold">Realizar Venta</h1>
        <ol class="breadcrumb mb-3">
            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('ventas.index') }}">Ventas</a></li>
            <li class="breadcrumb-item active">Nueva Venta</li>
        </ol>
    </div>

    <form action="{{ route('ventas.store') }}" method="post" id="ventaForm">
        @csrf
        <div class="container-lg">
            <div class="border-section">
                <div class="section-title"><i class="fas fa-info-circle"></i> Datos Generales</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Cliente:</label>
                        <select name="cliente_id" id="cliente_id" class="form-control form-control-sm selectpicker show-tick" data-live-search="true" title="Seleccione un cliente" required>
                            @foreach ($clientes as $item)
                                <option value="{{ $item->id }}" data-descuento="{{ $item->grupo->descuento_global ?? 0 }}">
                                    {{ $item->persona->razon_social }} 
                                    @if(isset($item->grupo) && $item->grupo->descuento_global > 0)
                                        ({{ $item->grupo->nombre }}: {{ number_format($item->grupo->descuento_global, 2) }}%)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sucursal:</label>
                        <select name="almacen_id" id="almacen_id" class="form-control form-control-sm selectpicker" required>
                            @foreach ($almacenes as $item)
                                <option value="{{ $item->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $item->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Comprobante:</label>
                        <select name="comprobante_id" id="comprobante_id" class="form-control form-control-sm selectpicker" required>
                            @foreach ($comprobantes as $item)
                                <option value="{{ $item->id }}">{{ $item->tipo_comprobante }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Número:</label>
                        <input type="text" name="numero_comprobante" class="form-control form-control-sm" value="{{ $nextComprobanteNumber }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha:</label>
                        <input type="date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" readonly>
                        <input type="hidden" name="fecha_hora" value="{{ now()->toDateTimeString() }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vendedor:</label>
                        <input type="text" class="form-control form-control-sm" value="{{ auth()->user()->name }}" readonly>
                        <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                    </div>
                </div>
            </div>

            <div class="border-section">
                <div class="section-title"><i class="fas fa-shopping-cart"></i> Detalles de la Venta</div>

                <div class="search-wrapper mb-3">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="producto_search" class="form-control form-control-sm" placeholder="Buscar por codigo o nombre del producto...">
                    <div class="products-dropdown" id="products_dropdown"></div>
                </div>

                <div class="product-selection-card" id="selection_card">
                    <div class="selection-title">
                        <span><i class="fas fa-box-open"></i> <span id="sel_name">Producto</span></span>
                        <span id="sel_stock_badge" class="badge-stock stock-ok">Stock: 0</span>
                    </div>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Código</label>
                            <input type="text" id="sel_codigo" class="form-control form-control-sm bg-white" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Precio Venta (Bs.)</label>
                            <input type="number" id="sel_precio" class="form-control form-control-sm" step="0.01">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cantidad</label>
                            <input type="number" id="sel_cantidad" class="form-control form-control-sm" value="1.000" step="0.001">
                        </div>
                        <div class="col-md-3">
                            <button type="button" id="btn_add_item" class="btn btn-primary btn-sm w-100 h-32"><i class="fas fa-plus me-1"></i> Añadir a Tabla</button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="tabla_detalle" class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th>Producto</th>
                                <th width="12%">Cantidad</th>
                                <th width="12%">P. Venta</th>
                                <th width="12%">Descuento</th>
                                <th width="15%">Subtotal</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="row justify-content-end">
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between align-items-center p-2 bg-light border rounded">
                            <span class="fw-bold">TOTAL VENTA:</span>
                            <span class="fs-5 fw-bold text-primary">Bs. <span id="label_total">0.00</span></span>
                            <input type="hidden" name="total" id="input_total" value="0">
                        </div>
                    </div>
                </div>

                <div class="row mt-3 g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">Pagos (puede ser mixto):</label>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0" id="pagos_table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Método</th>
                                        <th width="30%">Monto (Bs.)</th>
                                        <th width="5%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select name="pagos_metodo[]" class="form-control form-control-sm">
                                                <option value="efectivo" selected>Efectivo</option>
                                                <option value="debito">Débito</option>
                                                <option value="qr">QR</option>
                                                <option value="deposito">Depósito</option>
                                                <option value="otro">Otro</option>
                                            </select>
                                        </td>
                                        <td><input type="number" name="pagos_monto[]" class="form-control form-control-sm pago-monto" step="0.01" min="0" value="0.00"></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-link text-danger p-0 remove-pago" title="Quitar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end mt-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addPago">
                                <i class="fas fa-plus me-1"></i> Agregar pago
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Saldo/Deuda (Bs.):</label>
                        <input type="text" id="saldo_label" class="form-control form-control-sm bg-white" readonly value="0.00">
                        <div class="info-subtext mt-1">Pagado: Bs. <span id="pagado_label">0.00</span></div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Nota Interna:</label>
                        <textarea name="nota_personal" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nota Cliente:</label>
                        <textarea name="nota_cliente" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button id="btn_cancelar" type="button" class="btn btn-outline-danger btn-sm px-4" style="display:none;">Cancelar</button>
                    <button id="btn_guardar" type="submit" class="btn btn-success btn-sm px-5 fw-bold" style="display:none;">Realizar Venta</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
    <script>
        $(document).ready(function() {
            const PRODUCTOR_RAW = '{!! addslashes(json_encode($productos)) !!}';
            const PRODUCTOS = JSON.parse(PRODUCTOR_RAW);
            let itemsAgregados = new Set();
            let selectedItem = null;
            let rowCount = 0;
            let previousAlmacenId = $('#almacen_id').val();
            let stockRequestSeq = 0;
            let suppressAlmacenChange = false;
            let currentClienteDescuento = 0;

            $('#cliente_id').on('change', function() {
                const selected = $(this).find('option:selected');
                currentClienteDescuento = parseFloat(selected.data('descuento')) || 0;
                
                // Si ya hay items, preguntar si desea aplicar el nuevo descuento a todos
                const hasItems = $('#tabla_detalle tbody tr').length > 0;
                if (hasItems && currentClienteDescuento > 0) {
                    Swal.fire({
                        title: '¿Aplicar descuento?',
                        text: `El cliente seleccionado tiene un descuento del ${currentClienteDescuento}%. ¿Desea aplicarlo a todos los productos ya agregados?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, aplicar',
                        cancelButtonText: 'No, mantener actuales'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            aplicarDescuentoGlobal(currentClienteDescuento);
                        }
                    });
                }
            });

            function aplicarDescuentoGlobal(porcentaje) {
                $('#tabla_detalle tbody tr').each(function() {
                    const tr = $(this);
                    const qty = parseFloat(tr.find('.t-qty').val()) || 0;
                    const price = parseFloat(tr.find('.t-price').val()) || 0;
                    const desc = (qty * price) * (porcentaje / 100);
                    tr.find('.t-desc').val(desc.toFixed(2));
                    const sub = ((qty * price) - desc).toFixed(2);
                    tr.find('.t-sub').text(sub);
                });
                updateTotals();
            }

            $('.selectpicker').selectpicker();

            function updatePagoFromTotal() {
                const total = parseFloat($('#input_total').val()) || 0;
                let pagado = 0;
                $('.pago-monto').each(function() {
                    pagado += parseFloat($(this).val()) || 0;
                });
                pagado = Math.max(0, Math.min(pagado, total));
                const saldo = Math.max(0, total - pagado);
                $('#saldo_label').val(saldo.toFixed(2));
                $('#pagado_label').text(pagado.toFixed(2));
            }

            $(document).on('input', '.pago-monto', updatePagoFromTotal);

            $('#addPago').on('click', function() {
                const row = `
                    <tr>
                        <td>
                            <select name="pagos_metodo[]" class="form-control form-control-sm">
                                <option value="efectivo">Efectivo</option>
                                <option value="debito">Débito</option>
                                <option value="qr">QR</option>
                                <option value="deposito">Depósito</option>
                                <option value="otro">Otro</option>
                            </select>
                        </td>
                        <td><input type="number" name="pagos_monto[]" class="form-control form-control-sm pago-monto" step="0.01" min="0" value="0.00"></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-link text-danger p-0 remove-pago" title="Quitar">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                `;
                $('#pagos_table tbody').append(row);
            });

            $(document).on('click', '.remove-pago', function() {
                const tbody = $('#pagos_table tbody');
                if (tbody.find('tr').length <= 1) {
                    tbody.find('.pago-monto').val('0.00');
                } else {
                    $(this).closest('tr').remove();
                }
                updatePagoFromTotal();
            });

            function setStockBadge(stock, ilimitado) {
                const badge = $('#sel_stock_badge');
                badge.removeClass('stock-ok stock-low stock-out');

                if (ilimitado) {
                    badge.text('Stock: Ilimitado');
                    badge.addClass('stock-ok');
                    return;
                }

                const stockNum = parseFloat(stock) || 0;
                badge.text(`Stock: ${stockNum}`);
                if (stockNum <= 0) badge.addClass('stock-out');
                else if (stockNum < 10) badge.addClass('stock-low');
                else badge.addClass('stock-ok');
            }

            async function fetchStock(productoId, almacenId) {
                const mySeq = ++stockRequestSeq;
                const res = await $.ajax({
                    url: '{{ route("ventas.check-stock") }}',
                    method: 'GET',
                    data: { producto_id: productoId, almacen_id: almacenId }
                });
                return { res, mySeq };
            }


            function resetVentaItems() {
                $('#tabla_detalle tbody').empty();
                itemsAgregados.clear();
                rowCount = 0;
                updateTotals();
                checkVisibility();
                $('#selection_card').hide();
                selectedItem = null;
                $('#producto_search').val('');
            }

            // --- SEARCH LOGIC ---
            $('#producto_search').on('input', function() {
                const q = $(this).val().toLowerCase().trim();
                const dropdown = $('#products_dropdown');
                if (q.length < 1) { dropdown.hide(); return; }

                const matches = PRODUCTOS.filter(p => p.nombre.toLowerCase().includes(q) || p.codigo.toLowerCase().includes(q)).slice(0, 10);

                dropdown.empty();
                if (matches.length === 0) {
                    dropdown.append('<div class="p-3 text-muted small text-center">No se encontraron productos</div>').show();
                    return;
                }

                matches.forEach(p => {
                    const isAdded = itemsAgregados.has(p.id);
                    const item = $(`
                        <div class="product-item ${isAdded ? 'disabled' : ''}">
                            <div>
                                <div class="prod-main">${p.nombre}</div>
                                <div class="prod-sub">${p.codigo}</div>
                            </div>
                            <div class="prod-price">Bs. ${parseFloat(p.precio_venta).toFixed(2)}</div>
                        </div>
                    `);

                    if (!isAdded) {
                        item.on('click', () => selectProduct(p));
                    }
                    dropdown.append(item);
                });
                dropdown.show();
            });

            $(document).on('click', (e) => {
                if (!$(e.target).closest('.search-wrapper').length) $('#products_dropdown').hide();
            });

            async function selectProduct(p) {
                $('#products_dropdown').hide();
                $('#producto_search').val('');

                const storageId = $('#almacen_id').val();

                const isServicio = p.tipounidad && p.tipounidad.maneja_stock === false;
                if (isServicio) {
                    selectedItem = { ...p, stock: Number.POSITIVE_INFINITY, ilimitado: true };
                    $('#sel_name').text(p.nombre);
                    $('#sel_codigo').val(p.codigo);
                    $('#sel_precio').val(parseFloat(p.precio_venta).toFixed(2));
                    $('#sel_cantidad').val('1.000').focus();
                    setStockBadge(0, true);
                    $('#selection_card').slideDown();
                    return;
                }

                if (!storageId) {
                    Swal.fire('Sucursal requerida', 'Seleccione una sucursal antes de elegir un producto.', 'warning');
                    return;
                }

                // Show loading state
                Swal.showLoading();

                try {
                    const { res, mySeq } = await fetchStock(p.id, storageId);

                    Swal.close();
                    if (mySeq !== stockRequestSeq) return;
                    if (res.success) {
                        const ilimitado = !!res.ilimitado;
                        const stockValue = ilimitado ? Number.POSITIVE_INFINITY : parseFloat(res.stock);
                        selectedItem = { ...p, stock: stockValue, ilimitado };
                        $('#sel_name').text(p.nombre);
                        $('#sel_codigo').val(p.codigo);
                        $('#sel_precio').val(parseFloat(p.precio_venta).toFixed(2));
                        $('#sel_cantidad').val('1.000').focus();

                        setStockBadge(res.stock, ilimitado);

                        $('#selection_card').slideDown();
                    } else {
                        Swal.fire("Error", "No se pudo consultar el stock", "error");
                    }
                } catch (err) {
                    Swal.fire("Error", "Error en el servidor", "error");
                }
            }

            async function refreshSelectedStockForAlmacen(almacenId) {
                if (!selectedItem) return;
                if (!$('#selection_card').is(':visible')) return;
                if (selectedItem.ilimitado) return;

                try {
                    Swal.showLoading();
                    const { res, mySeq } = await fetchStock(selectedItem.id, almacenId);
                    Swal.close();
                    if (mySeq !== stockRequestSeq) return;
                    if (!res.success) {
                        Swal.fire("Error", "No se pudo consultar el stock", "error");
                        return;
                    }

                    const ilimitado = !!res.ilimitado;
                    selectedItem.stock = ilimitado ? Number.POSITIVE_INFINITY : parseFloat(res.stock);
                    selectedItem.ilimitado = ilimitado;
                    setStockBadge(res.stock, ilimitado);
                } catch (err) {
                    Swal.fire("Error", "Error en el servidor", "error");
                }
            }

            $('#almacen_id').on('changed.bs.select', async function() {
                if (suppressAlmacenChange) return;
                const newAlmacenId = $(this).val();
                const hasItems = $('#tabla_detalle tbody tr').length > 0;

                if (hasItems) {
                    const result = await Swal.fire({
                        title: 'Cambiar sucursal',
                        text: 'Al cambiar la sucursal se borrarán los items agregados para evitar inconsistencias de stock. ¿Desea continuar?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Sí, cambiar',
                        cancelButtonText: 'No'
                    });

                    if (!result.isConfirmed) {
                        suppressAlmacenChange = true;
                        $('#almacen_id').selectpicker('val', previousAlmacenId);
                        suppressAlmacenChange = false;
                        return;
                    }

                    resetVentaItems();
                }

                previousAlmacenId = newAlmacenId;
                await refreshSelectedStockForAlmacen(newAlmacenId);
            });

            // --- TABLE LOGIC ---
            $('#btn_add_item').on('click', function() {
                if (!selectedItem) return;
                const qty = parseFloat($('#sel_cantidad').val()) || 0;
                const price = parseFloat($('#sel_precio').val()) || 0;

                if (qty <= 0) { Swal.fire("Error", "Ingrese una cantidad valida", "warning"); return; }
                if (qty > selectedItem.stock) {
                    const msg = selectedItem.ilimitado ? 'Stock ilimitado' : `Solo dispone de ${parseFloat(selectedItem.stock) || 0}`;
                    Swal.fire("Stock Insuficiente", msg, "error");
                    return;
                }

                addItem(selectedItem, qty, price);
                $('#selection_card').hide();
                selectedItem = null;
            });

            function addItem(p, qty, price) {
                rowCount++;
                itemsAgregados.add(p.id);
                const subTotalBruto = qty * price;
                const descCalculado = subTotalBruto * (currentClienteDescuento / 100);
                const sub = (subTotalBruto - descCalculado).toFixed(2);

                const row = `
                    <tr id="row_${rowCount}" data-id="${p.id}">
                        <td class="row-index">${rowCount}</td>
                        <td>
                            <div class="fw-bold">${p.nombre}</div>
                            <div class="small text-muted">${p.codigo}</div>
                            <input type="hidden" name="arrayidproducto[]" value="${p.id}">
                        </td>
                        <td><input type="number" name="arraycantidad[]" class="form-control form-control-sm t-qty" value="${qty.toFixed(3)}" step="0.001"></td>
                        <td><input type="number" name="arrayprecioventa[]" class="form-control form-control-sm t-price" value="${price.toFixed(2)}" step="0.01"></td>
                        <td><input type="number" name="arraydescuento[]" class="form-control form-control-sm t-desc" value="${descCalculado.toFixed(2)}" step="0.01"></td>
                        <td class="text-end fw-bold">Bs. <span class="t-sub">${sub}</span></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-link text-danger p-0 delete-row"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                `;
                $('#tabla_detalle tbody').append(row);
                updateTotals();
                checkVisibility();
            }

            $(document).on('input', '.t-qty, .t-price, .t-desc', function() {
                const tr = $(this).closest('tr');
                const q = parseFloat(tr.find('.t-qty').val()) || 0;
                const p = parseFloat(tr.find('.t-price').val()) || 0;
                const d = parseFloat(tr.find('.t-desc').val()) || 0;
                const sub = ((q * p) - d).toFixed(2);
                tr.find('.t-sub').text(sub);
                updateTotals();
            });

            $(document).on('click', '.delete-row', function() {
                const tr = $(this).closest('tr');
                itemsAgregados.delete(parseInt(tr.data('id')));
                tr.remove();
                renumber();
                updateTotals();
                checkVisibility();
            });

            function renumber() {
                $('#tabla_detalle tbody tr').each((i, el) => $(el).find('.row-index').text(i + 1));
            }

            function updateTotals() {
                let total = 0;
                $('.t-sub').each(function() { total += parseFloat($(this).text()) || 0; });
                $('#label_total').text(total.toLocaleString('en-US', { minimumFractionDigits: 2 }));
                $('#input_total').val(total.toFixed(2));
                updatePagoFromTotal();
            }

            function checkVisibility() {
                const count = $('#tabla_detalle tbody tr').length;
                if (count > 0) { $('#btn_guardar, #btn_cancelar').fadeIn(); }
                else { $('#btn_guardar, #btn_cancelar').fadeOut(); }
            }

            $('#btn_cancelar').on('click', function() {
                Swal.fire({
                    title: '¿Está seguro?',
                    text: "Se borrarán todos los items agregados.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Sí, cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        resetVentaItems();
                    }
                });
            });
        });
    </script>
@endpush
