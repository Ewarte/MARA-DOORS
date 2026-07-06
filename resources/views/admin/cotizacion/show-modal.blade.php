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

@php
    $hoy = \Carbon\Carbon::today();
    $vencimiento = $cotizacion->vencimiento ? \Carbon\Carbon::parse($cotizacion->vencimiento) : null;
    $vencDiff = $vencimiento ? (int) $hoy->diffInDays($vencimiento, false) : null;

    $estadoClass = match($cotizacion->estado) {
        'pendiente'        => 'bg-info',
        'venta_realizada'  => 'bg-success',
        'compra_realizada' => 'bg-primary',
        'anulado'          => 'bg-danger',
        default            => 'bg-secondary',
    };
    $estadoText = match($cotizacion->estado) {
        'pendiente'        => 'Pendiente',
        'venta_realizada'  => 'Venta Realizada',
        'compra_realizada' => 'Compra Realizada',
        'anulado'          => 'Anulado',
        default            => ucfirst($cotizacion->estado),
    };
@endphp

<div class="container-fluid p-0 show-modal-content">

    <div class="border-section">
        <div class="row mb-3">
            <div class="col-md-6">
                <h5 class="mb-1 text-primary fw-bold">COTIZACIÓN #{{ $cotizacion->numero_cotizacion }}</h5>
                <p class="text-muted small mb-0">Registrado por: {{ $cotizacion->user->name ?? 'Sistema' }}</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="badge bg-light text-dark border mb-2">
                    <i class="far fa-clock me-1"></i>
                    {{ optional($cotizacion->fecha_hora)->format('d/m/Y H:i A') ?? 'N/A' }}
                </div>
                <div>
                    <span class="badge {{ $estadoClass }}">{{ $estadoText }}</span>
                </div>
            </div>
        </div>

        @if($vencimiento && $cotizacion->estado === 'pendiente')
            @if($vencDiff < 0)
                <div class="alert alert-danger py-2 small mb-3">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Cotización vencida hace {{ abs($vencDiff) }} día(s) — {{ $vencimiento->format('d/m/Y') }}
                </div>
            @elseif($vencDiff === 0)
                <div class="alert alert-warning py-2 small mb-3">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    La cotización vence <strong>hoy</strong> — {{ $vencimiento->format('d/m/Y') }}
                </div>
            @elseif($vencDiff <= 3)
                <div class="alert alert-warning py-2 small mb-3">
                    <i class="fas fa-hourglass-half me-1"></i>
                    Vence en <strong>{{ $vencDiff }}</strong> día(s) — {{ $vencimiento->format('d/m/Y') }}
                </div>
            @endif
        @endif

        <div class="section-title">
            <i class="fas fa-info-circle"></i> Datos Generales
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="label-title">Tercero</div>
                @if($cotizacion->cliente)
                    <div class="value-text">{{ $cotizacion->cliente->persona->razon_social }}</div>
                    <small class="text-muted">Cliente · {{ $cotizacion->cliente->persona->tipo_documento ?? 'Doc.' }}: {{ $cotizacion->cliente->persona->numero_documento ?? 'N/A' }}</small>
                @elseif($cotizacion->proveedor)
                    <div class="value-text">{{ $cotizacion->proveedor->persona->razon_social }}</div>
                    <small class="text-muted">Proveedor · {{ $cotizacion->proveedor->persona->tipo_documento ?? 'Doc.' }}: {{ $cotizacion->proveedor->persona->numero_documento ?? 'N/A' }}</small>
                @else
                    <div class="value-text text-muted">Público General</div>
                @endif
            </div>
            <div class="col-md-4">
                <div class="label-title">Sucursal</div>
                <div class="value-text">{{ $cotizacion->almacen->nombre ?? 'N/A' }}</div>
            </div>
            <div class="col-md-4">
                <div class="label-title">Vencimiento</div>
                <div class="value-text">
                    @if($vencimiento)
                        {{ $vencimiento->format('d/m/Y') }}
                        @if($cotizacion->estado === 'pendiente' && $vencDiff > 3)
                            <small class="text-success">({{ $vencDiff }} días restantes)</small>
                        @endif
                    @else
                        <span class="text-muted">Sin fecha de vencimiento</span>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="label-title">Total Cotizado</div>
                <div class="value-text text-success">Bs. {{ number_format($cotizacion->total, 2) }}</div>
            </div>
            <div class="col-md-4">
                <div class="label-title">Ítems</div>
                <div class="value-text">{{ $cotizacion->detalles->count() }} producto(s)</div>
            </div>
        </div>

        <div class="section-title mt-4">
            <i class="fas fa-sticky-note"></i> Notas
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="label-title">Nota Interna</div>
                <div class="value-text fst-italic text-muted">
                    {{ $cotizacion->nota_personal ?: 'Sin nota interna' }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="label-title">Nota Cliente</div>
                <div class="value-text fst-italic text-muted">
                    {{ $cotizacion->nota_cliente ?: 'Sin nota al cliente' }}
                </div>
            </div>
        </div>
    </div>

    <div class="border-section">
        <div class="section-title">
            <i class="fas fa-list-ul"></i> Detalles de la Cotización
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
                    @foreach ($cotizacion->detalles as $index => $detalle)
                        @php
                            $subtotal = ($detalle->cantidad * $detalle->precio_unitario) - $detalle->descuento;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $detalle->producto->nombre }}</div>
                                <small class="text-muted">{{ $detalle->producto->codigo }}</small>
                            </td>
                            <td class="text-end">{{ number_format($detalle->cantidad, 2) }}</td>
                            <td class="text-end">{{ number_format($detalle->precio_unitario, 2) }}</td>
                            <td class="text-end text-danger">{{ number_format($detalle->descuento, 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-light">
                    <tr>
                        <td colspan="5" class="text-end fw-bold">TOTAL:</td>
                        <td class="text-end fw-bold text-primary fs-6">{{ number_format($cotizacion->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="text-end small text-muted">
        Documento generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
    </div>
</div>
