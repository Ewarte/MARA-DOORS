@extends('admin.layouts.app')

@section('title', 'Realizar compra')

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
        .section-title i { margin-right: 8px; color: #3498db; }
        .form-label { font-weight: 500; font-size: 13px; margin-bottom: 4px; color: #495057; }
        .form-control-sm { font-size: 13px; padding: 4px 8px; height: 32px; }

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
        .product-item:hover { background-color: #f8f9fa; }
        .product-item.disabled { opacity: 0.5; cursor: not-allowed; }
        .product-item .prod-main { font-weight: 500; color: #333; font-size: 13px; }
        .product-item .prod-sub { font-size: 11px; color: #6c757d; }
        .product-item .prod-price { font-weight: 600; color: #28a745; font-size: 11px; }

        /* Selection Card */
        .product-selection-card {
            background-color: #f8fbff; border: 1px solid #d1e3ff; border-radius: 6px; padding: 15px; margin-bottom: 15px; display: none;
        }
        .selection-title { font-size: 14px; font-weight: 700; color: #0056b3; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }

        .margen-badge { padding: 3px 6px; border-radius: 3px; font-size: 11px; font-weight: 700; }
        .margen-alto { background-color: #d4edda; color: #155724; }
        .margen-medio { background-color: #fff3cd; color: #856404; }
        .margen-bajo { background-color: #f8d7da; color: #721c24; }

        .h-32 { height: 32px !important; }
    </style>
@endpush

@section('content')
    @include('admin.layouts.partials.alert')
    <div class="container-fluid px-4">
        <h1 class="mt-4 fs-4 fw-bold">Realizar Compra</h1>
        <ol class="breadcrumb mb-3">
            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('compras.index') }}">Compras</a></li>
            <li class="breadcrumb-item active">Nueva Compra</li>
        </ol>
    </div>

    <form action="{{ route('compras.store') }}" method="post" id="compraForm">
        @csrf
        <div class="container-lg">
            <div class="border-section">
                <div class="section-title"><i class="fas fa-info-circle"></i> Datos Generales</div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Proveedor:</label>
                        <select name="proveedor_id" id="proveedor_id" class="form-control form-control-sm selectpicker" data-live-search="true" title="Seleccione proveedor" required>
                            @foreach ($proveedores as $item)
                                <option value="{{ $item->id }}">{{ $item->persona->razon_social }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sucursal:</label>
                        <select name="almacen_id" id="almacen_id" class="form-control form-control-sm selectpicker" required>
                            @foreach ($almacenes as $item)
                                <option value="{{ $item->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $item->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Comprobante:</label>
                        <select name="comprobante_id" id="comprobante_id" class="form-control form-control-sm selectpicker">
                            @foreach ($comprobantes as $item)
                                <option value="{{ $item->id }}">{{ $item->tipo_comprobante }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Costo Transporte (Bs):</label>
                        <input type="number" step="0.01" name="costo_transporte" class="form-control form-control-sm" value="0.00">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado Entrega:</label>
                        <select name="estado_entrega" class="form-select form-select-sm">
                            <option value="entregado">Entregado</option>
                            <option value="por_entregar">Por recibir</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha:</label>
                        <input type="date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" readonly>
                        <input type="hidden" name="fecha_hora" value="{{ now()->toDateTimeString() }}">
                    </div>
                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                </div>
            </div>

            <div class="border-section">
                <div class="section-title"><i class="fas fa-boxes"></i> Detalles de la Compra</div>

                <div class="search-wrapper mb-3">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="producto_search" class="form-control form-control-sm" placeholder="Buscar producto para añadir...">
                    <div class="products-dropdown" id="products_dropdown"></div>
                </div>

                <div class="product-selection-card" id="selection_card">
                    <div class="selection-title">
                        <span><i class="fas fa-box"></i> <span id="sel_name">Producto</span></span>
                        <span id="sel_margen_badge" class="margen-badge margen-bajo">Margen: 0%</span>
                    </div>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label">Cantidad</label>
                            <input type="number" id="sel_cantidad" class="form-control form-control-sm" value="1.00" step="0.01">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Costo Compra (Bs.)</label>
                            <input type="number" id="sel_costo" class="form-control form-control-sm" step="0.01">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Precio Venta (Bs.)</label>
                            <input type="number" id="sel_venta" class="form-control form-control-sm" step="0.01">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Código</label>
                            <input type="text" id="sel_codigo" class="form-control form-control-sm bg-white" readonly>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btn_add_item" class="btn btn-primary btn-sm w-100 h-32"><i class="fas fa-plus me-1"></i> Añadir</button>
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
                                <th width="12%">Costo Compra</th>
                                <th width="12%">P. Venta</th>
                                <th width="10%">Margen</th>
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
                            <span class="fw-bold">TOTAL COMPRA:</span>
                            <span class="fs-5 fw-bold text-success">Bs. <span id="label_total">0.00</span></span>
                            <input type="hidden" name="total" id="input_total" value="0">
                        </div>
                    </div>
                </div>

                <div class="row mt-3 g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Método de Pago:</label>
                        <select name="metodo_pago" id="metodo_pago" class="form-control form-control-sm selectpicker" required>
                            <option value="efectivo" selected>Efectivo</option>
                            <option value="debito">DÉBITO</option>
                            <option value="qr">QR</option>
                            <option value="deposito">Depósito (Banco Fisco)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Monto Pagado (Bs.):</label>
                        <input type="number" name="monto_pagado" id="monto_pagado" class="form-control form-control-sm" step="0.01" min="0" value="0.00">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Saldo/Deuda (Bs.):</label>
                        <input type="text" id="saldo_label" class="form-control form-control-sm bg-white" readonly value="0.00">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button id="btn_cancelar" type="button" class="btn btn-outline-danger btn-sm px-4" style="display:none;">Cancelar</button>
                    <button id="btn_guardar" type="submit" class="btn btn-primary btn-sm px-5 fw-bold" style="display:none;">Registrar Compra</button>
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

            $('.selectpicker').selectpicker();

            function updatePagoFromTotal() {
                const total = parseFloat($('#input_total').val()) || 0;
                let pagado = parseFloat($('#monto_pagado').val());
                if (isNaN(pagado)) pagado = total;
                pagado = Math.max(0, Math.min(pagado, total));
                const saldo = Math.max(0, total - pagado);
                $('#monto_pagado').val(pagado.toFixed(2));
                $('#saldo_label').val(saldo.toFixed(2));
            }

            $('#monto_pagado').on('input', updatePagoFromTotal);

            // --- SEARCH LOGIC ---
            $('#producto_search').on('input', function() {
                const q = $(this).val().toLowerCase().trim();
                const dropdown = $('#products_dropdown');
                if (q.length < 1) { dropdown.hide(); return; }

                const matches = PRODUCTOS.filter(p => p.nombre.toLowerCase().includes(q) || p.codigo.toLowerCase().includes(q)).slice(0, 10);

                dropdown.empty();
                if (matches.length === 0) {
                    dropdown.append('<div class="p-3 text-muted small text-center">No encontrado</div>').show();
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
                            <div class="prod-price">Costo sugerido: Bs.${parseFloat(p.precio_compra).toFixed(2)}</div>
                        </div>
                    `);

                    if (!isAdded) item.on('click', () => {
                        selectedItem = p;
                        $('#sel_name').text(p.nombre);
                        $('#sel_codigo').val(p.codigo);
                        $('#sel_costo').val(parseFloat(p.precio_compra).toFixed(2));
                        $('#sel_venta').val(parseFloat(p.precio_venta).toFixed(2));
                        $('#sel_cantidad').val('1.00').focus();
                        updateMargenBadge();
                        $('#selection_card').slideDown();
                        dropdown.hide();
                        $('#producto_search').val('');
                    });
                    dropdown.append(item);
                });
                dropdown.show();
            });

            $(document).on('click', (e) => {
                if (!$(e.target).closest('.search-wrapper').length) $('#products_dropdown').hide();
            });

            function updateMargenBadge() {
                const costo = parseFloat($('#sel_costo').val()) || 0;
                const venta = parseFloat($('#sel_venta').val()) || 0;
                if (costo > 0) {
                    const margen = ((venta - costo) / costo * 100).toFixed(2);
                    const badge = $('#sel_margen_badge').text(`Margen: ${margen}%`);
                    badge.removeClass('margen-alto margen-medio margen-bajo');
                    if (margen >= 30) badge.addClass('margen-alto');
                    else if (margen >= 10) badge.addClass('margen-medio');
                    else badge.addClass('margen-bajo');
                }
            }
            $('#sel_costo, #sel_venta').on('input', updateMargenBadge);

            // --- TABLE LOGIC ---
            $('#btn_add_item').on('click', function() {
                if (!selectedItem) return;
                const qty = parseFloat($('#sel_cantidad').val()) || 0;
                const costo = parseFloat($('#sel_costo').val()) || 0;
                const venta = parseFloat($('#sel_venta').val()) || 0;

                if (qty <= 0) { Swal.fire("Error", "Cantidad inválida", "warning"); return; }
                if (costo <= 0) { Swal.fire("Error", "Costo inválido", "warning"); return; }

                addItem(selectedItem, qty, costo, venta);
                $('#selection_card').hide();
                selectedItem = null;
            });

            function addItem(p, qty, costo, venta) {
                rowCount++;
                itemsAgregados.add(p.id);
                const sub = (qty * costo).toFixed(2);
                const margen = costo > 0 ? ((venta - costo) / costo * 100).toFixed(2) : 0;
                let mClass = 'margen-bajo';
                if (margen >= 30) mClass = 'margen-alto';
                else if (margen >= 10) mClass = 'margen-medio';

                const row = `
                    <tr id="row_${rowCount}" data-id="${p.id}">
                        <td class="row-index">${rowCount}</td>
                        <td>
                            <div class="fw-bold">${p.nombre}</div>
                            <div class="small text-muted">${p.codigo}</div>
                            <input type="hidden" name="arrayidproducto[]" value="${p.id}">
                        </td>
                        <td><input type="number" name="arraycantidad[]" class="form-control form-control-sm t-qty" value="${qty.toFixed(2)}" step="0.01"></td>
                        <td><input type="number" name="arraypreciocompra[]" class="form-control form-control-sm t-cost" value="${costo.toFixed(2)}" step="0.01"></td>
                        <td><input type="number" name="arrayprecioventa[]" class="form-control form-control-sm t-sell" value="${venta.toFixed(2)}" step="0.01"></td>
                        <td class="text-center"><span class="margen-badge ${mClass} t-margen">${margen}%</span></td>
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

            $(document).on('input', '.t-qty, .t-cost, .t-sell', function() {
                const tr = $(this).closest('tr');
                const q = parseFloat(tr.find('.t-qty').val()) || 0;
                const c = parseFloat(tr.find('.t-cost').val()) || 0;
                const v = parseFloat(tr.find('.t-sell').val()) || 0;

                const sub = (q * c).toFixed(2);
                tr.find('.t-sub').text(sub);

                const marg = c > 0 ? ((v - c) / c * 100).toFixed(2) : 0;
                const badge = tr.find('.t-margen').text(marg + '%');
                badge.removeClass('margen-alto margen-medio margen-bajo');
                if (marg >= 30) badge.addClass('margen-alto');
                else if (marg >= 10) badge.addClass('margen-medio');
                else badge.addClass('margen-bajo');

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
                    title: 'Â¿Confirmar?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, limpiar'
                }).then((r) => {
                    if (r.isConfirmed) {
                        $('#tabla_detalle tbody').empty();
                        itemsAgregados.clear();
                        updateTotals();
                        checkVisibility();
                    }
                });
            });
        });
    </script>
@endpush
