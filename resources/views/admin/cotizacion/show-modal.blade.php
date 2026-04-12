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
    .show-modal-content .text-orange { color: #fd7e14; }
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
                <h5 class="mb-1 text-primary fw-bold">COTIZACIÓN #{{ $cotizacion->numero_cotizacion }}</h5>
                <p class="text-muted small mb-0">Creado por: {{ $cotizacion->user->name ?? 'Sistema' }}</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="badge bg-light text-dark border p-2">
                    <i class="far fa-clock me-1"></i> 
                    {{ optional($cotizacion->fecha_hora)->format('d/m/Y H:i A') ?? 'N/A' }}
                </div>
                @if($cotizacion->vencimiento)
                    <div class="mt-1">
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 p-2">
                            <i class="fas fa-calendar-times me-1"></i> Vence: {{ optional($cotizacion->vencimiento)->format('d/m/Y') ?? 'N/A' }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="section-title">
            <i class="fas fa-info-circle"></i> Información del Cliente / Proveedor
        </div>
        
        <div class="row g-3">
            <div class="col-md-4">
                <div class="label-title">Tercero</div>
                @if($cotizacion->cliente)
                    <div class="value-text text-primary"><i class="fas fa-user me-1"></i> {{ $cotizacion->cliente->persona->razon_social }}</div>
                    <small class="text-muted">Cliente ({{ $cotizacion->cliente->persona->numero_documento }})</small>
                @elseif($cotizacion->proveedor)
                    <div class="value-text text-orange"><i class="fas fa-truck me-1"></i> {{ $cotizacion->proveedor->persona->razon_social }}</div>
                    <small class="text-muted">Proveedor ({{ $cotizacion->proveedor->persona->numero_documento }})</small>
                @else
                    <div class="value-text text-muted">Público General</div>
                @endif
            </div>
            <div class="col-md-4">
                <div class="label-title">Sucursal</div>
                <div class="value-text">{{ $cotizacion->almacen->nombre ?? 'N/A' }}</div>
            </div>
            <div class="col-md-4">
                <div class="label-title">Estado Actual</div>
                @php
                    $badgeClass = match($cotizacion->estado) {
                        'pendiente' => 'bg-info',
                        'venta_realizada' => 'bg-success',
                        'compra_realizada' => 'bg-primary',
                        'anulado' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                    $estadoText = match($cotizacion->estado) {
                        'pendiente' => 'Pendiente',
                        'venta_realizada' => 'Venta Realizada',
                        'compra_realizada' => 'Compra Realizada',
                        'anulado' => 'Anulado',
                        default => ucfirst($cotizacion->estado)
                    };
                @endphp
                <span class="badge {{ $badgeClass }} text-white fw-bold" style="border-radius: 20px; padding: 5px 15px;">
                    {{ $estadoText }}
                </span>
            </div>
        </div>

        <div class="row g-3 mt-2">
            @if($cotizacion->nota_personal)
            <div class="col-md-6">
                <div class="label-title">Nota Interna</div>
                <div class="value-text fst-italic text-muted small">
                    {{ $cotizacion->nota_personal }}
                </div>
            </div>
            @endif
            @if($cotizacion->nota_cliente)
            <div class="col-md-6">
                <div class="label-title">Nota para Cliente</div>
                <div class="value-text fst-italic text-muted small">
                    {{ $cotizacion->nota_cliente }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="border-section">
        <div class="section-title">
            <i class="fas fa-list"></i> Detalles de Productos
        </div>
        
        <div class="table-responsive">
            <table class="table table-sm table-hover border-bottom mb-0">
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
                    @foreach ($cotizacion->detalles as $index => $detalle)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $detalle->producto->nombre }}</div>
                                <small class="text-muted">{{ $detalle->producto->codigo }}</small>
                            </td>
                            <td class="text-end">{{ number_format($detalle->cantidad, 2) }}</td>
                            <td class="text-end">Bs. {{ number_format($detalle->precio_unitario, 2) }}</td>
                            <td class="text-end text-danger">-{{ number_format($detalle->descuento, 2) }}</td>
                            <td class="text-end fw-bold">Bs. {{ number_format(($detalle->cantidad * $detalle->precio_unitario) - $detalle->descuento, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-light">
                    <tr>
                        <td colspan="5" class="text-end fw-bold py-2">TOTAL COTIZADO:</td>
                        <td class="text-end fw-bold text-success fs-6 py-2">Bs. {{ number_format($cotizacion->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
    <div class="text-end extra-small text-muted px-2">
        Cotización generada el {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
    </div>
</div>
