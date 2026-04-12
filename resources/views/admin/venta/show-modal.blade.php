<style>
    .show-modal-content .border-section {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
        background: #fff;
    }
    .show-modal-content .section-title {
        font-size: 15px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e9ecef;
    }
    .show-modal-content .section-title i {
        margin-right: 8px;
        color: #3498db;
    }
    .show-modal-content .label-title {
        font-weight: 500;
        font-size: 13px;
        color: #495057;
        margin-bottom: 2px;
    }
    .show-modal-content .value-text {
        font-size: 14px;
        color: #212529;
        font-weight: 500;
    }
    .show-modal-content .badge-modern {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    .show-modal-content .table th {
        font-size: 13px;
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
    .show-modal-content .table td {
        font-size: 13px;
        vertical-align: middle;
    }
</style>

<div class="container-fluid p-0 show-modal-content">
    
    <div class="border-section">
        <div class="row mb-3">
            <div class="col-md-6">
                <h5 class="mb-1 text-primary fw-bold">VENTA #{{ $venta->numero_comprobante }}</h5>
                <p class="text-muted small mb-0">Vendedor: {{ $venta->user->name ?? 'Sistema' }}</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="badge bg-light text-dark border">
                    <i class="far fa-clock me-1"></i> 
                    {{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/Y H:i A') }}
                </div>
            </div>
        </div>
        
        <div class="section-title">
            <i class="fas fa-info-circle"></i> Datos Generales
        </div>
        
        <div class="row g-3">
            <div class="col-md-3">
                <div class="label-title">Cliente</div>
                <div class="value-text">{{ $venta->cliente->persona->razon_social }}</div>
                <small class="text-muted">{{ $venta->cliente->persona->tipo_documento }}: {{ $venta->cliente->persona->numero_documento }}</small>
            </div>
            <div class="col-md-3">
                <div class="label-title">Sucursal</div>
                <div class="value-text">{{ $venta->almacen->nombre ?? 'N/A' }}</div>
            </div>
            <div class="col-md-3">
                <div class="label-title">Tipo Comprobante</div>
                <div class="value-text">{{ $venta->comprobante->tipo_comprobante ?? 'N/A' }}</div>
            </div>
            <div class="col-md-3">
                <div class="label-title">Método de Pago</div>
                @php
                    $metodo = $venta->metodo_pago ?? 'efectivo';
                    $metodoLabel = match ($metodo) {
                        'debito' => 'Débito',
                        'qr' => 'QR',
                        'deposito' => 'Depósito',
                        default => 'Efectivo',
                    };
                @endphp
                <div class="value-text"><span class="badge bg-light text-dark border">{{ $metodoLabel }}</span></div>
            </div>
        </div>

        <div class="section-title mt-4">
            <i class="fas fa-tasks"></i> Estados y Acciones
        </div>

        <div class="row g-3 mb-2">
            <div class="col-md-4">
                <div class="label-title mb-2">Estado de Pago</div>
                <div class="dropdown custom-dropdown">
                    @php
                        $estadoPago = $venta->estado_pago;
                        $btnClass = (in_array($estadoPago, ['pendiente', '0', 0])) ? 'btn-danger' : 
                                   ($estadoPago === 'parcial' ? 'btn-warning' : 
                                   (in_array($estadoPago, ['cancelado', 'anulado']) ? 'btn-secondary' : 'btn-success'));
                        $statusTxt = (in_array($estadoPago, ['pendiente', '0', 0])) ? 'Pendiente' : 
                                    ($estadoPago === 'parcial' ? 'Parcial' : 
                                    (in_array($estadoPago, ['cancelado', 'anulado']) ? ucfirst($estadoPago) : 'Pagado'));
                    @endphp
                    <button class="btn {{ $btnClass }} btn-sm dropdown-toggle w-100 text-white fw-bold" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 20px;">
                        {{ $statusTxt }}
                    </button>
                    <ul class="dropdown-menu w-100 shadow-sm border-0">
                        <li><a class="dropdown-item change-status-pago" href="#" data-venta-id="{{ $venta->id }}" data-status="pagado" data-saldo="{{ (float)($venta->saldo ?? 0) }}">Pagado</a></li>
                        <li><a class="dropdown-item change-status-pago" href="#" data-venta-id="{{ $venta->id }}" data-status="parcial" data-saldo="{{ (float)($venta->saldo ?? 0) }}">Parcial...</a></li>
                        <li><a class="dropdown-item change-status-pago" href="#" data-venta-id="{{ $venta->id }}" data-status="pendiente" data-saldo="{{ (float)($venta->saldo ?? 0) }}">Pendiente</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="label-title mb-2">Estado de Entrega</div>
                <div class="dropdown custom-dropdown">
                    @php
                        $entregaTxt = $venta->estado_entrega == 'entregado' ? 'Entregado' : 'Por Entregar';
                        $entregaClass = $venta->estado_entrega == 'entregado' ? 'btn-success' : 'btn-warning';
                    @endphp
                    <button class="btn {{ $entregaClass }} btn-sm dropdown-toggle w-100 text-white fw-bold" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 20px;">
                        {{ $entregaTxt }}
                    </button>
                    <ul class="dropdown-menu w-100 shadow-sm border-0">
                        <li><a class="dropdown-item change-status-entrega" href="#" data-venta-id="{{ $venta->id }}" data-status="entregado">Entregado</a></li>
                        <li><a class="dropdown-item change-status-entrega" href="#" data-venta-id="{{ $venta->id }}" data-status="por_entregar">Por Entregar</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="label-title mb-2">Resumen Financiero</div>
                <div class="ps-2 border-start border-3 border-primary">
                    <div class="extra-small text-muted">Pagado: <span class="fw-bold text-dark">Bs. {{ number_format((float)($venta->monto_pagado ?? 0), 2) }}</span></div>
                    <div class="extra-small text-muted">Saldo: <span class="fw-bold text-danger">Bs. {{ number_format((float)($venta->saldo ?? 0), 2) }}</span></div>
                </div>
            </div>

            <div class="col-12">
                <div class="label-title mb-2">Movimientos de Pago</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="20%">Hora</th>
                                <th>Método</th>
                                <th class="text-end" width="25%">Monto (Bs.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($venta->pagos ?? collect())->sortBy('created_at') as $pago)
                                <tr>
                                    <td class="text-muted">{{ optional($pago->created_at)->format('H:i:s') ?? '--:--:--' }}</td>
                                    <td>{{ strtoupper($pago->metodo_pago ?? 'efectivo') }}</td>
                                    <td class="text-end fw-semibold">{{ number_format((float)($pago->monto ?? 0), 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-2">Sin movimientos</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-6">
                <div class="label-title">Nota Interna</div>
                <div class="value-text fst-italic text-muted">
                    {{ $venta->nota_personal ?: 'Sin nota interna' }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="label-title">Nota Cliente</div>
                <div class="value-text fst-italic text-muted">
                    {{ $venta->nota_cliente ?: 'Sin nota al cliente' }}
                </div>
            </div>
        </div>
    </div>

    <div class="border-section">
        <div class="section-title">
            <i class="fas fa-shopping-cart"></i> Detalles de la Venta
        </div>
        
        <div class="table-responsive">
            <table class="table table-sm table-hover border-bottom">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th>Producto</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Precio Unit.</th>
                        <th class="text-end">Descuento</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($venta->detalles as $index => $detalle)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $detalle->producto->nombre }}</div>
                                <small class="text-muted">{{ $detalle->producto->codigo }}</small>
                            </td>
                            <td class="text-end">{{ number_format($detalle->cantidad, 2) }}</td>
                            <td class="text-end">{{ number_format($detalle->precio_venta, 2) }}</td>
                            <td class="text-end text-danger">{{ number_format($detalle->descuento, 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format(($detalle->cantidad * $detalle->precio_venta) - $detalle->descuento, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-light">
                    <tr>
                        <td colspan="5" class="text-end fw-bold">TOTAL:</td>
                        <td class="text-end fw-bold text-primary fs-6">{{ number_format($venta->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
    <div class="text-end small text-muted">
        Documento generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
    </div>
</div>

