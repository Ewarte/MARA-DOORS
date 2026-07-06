<style>
    /* ===== SHOW MODAL PREMIUM STYLES ===== */
    .cot-modal-header-card {
        background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
        border-radius: 10px;
        padding: 18px 20px;
        margin-bottom: 16px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .cot-modal-header-card::before {
        content: '';
        position: absolute;
        top: -30px; right: -30px;
        width: 120px; height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,0.07);
    }
    .cot-modal-header-card .cot-number { font-size: 1.2rem; font-weight: 700; }
    .cot-modal-header-card .cot-meta { font-size: 0.78rem; opacity: 0.85; margin-top: 3px; }

    .cot-badge-estado { display: inline-block; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px; padding: 4px 14px; border-radius: 20px; text-transform: uppercase; }
    .cot-badge-pendiente   { background: rgba(255,255,255,0.2); color: #fff; border: 1px solid rgba(255,255,255,0.4); }
    .cot-badge-venta       { background: #d4edda; color: #155724; }
    .cot-badge-compra      { background: #cce5ff; color: #004085; }
    .cot-badge-anulado     { background: #f8d7da; color: #721c24; }

    .cot-info-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 14px; }
    @media (max-width: 576px) { .cot-info-grid { grid-template-columns: 1fr 1fr; } }

    .cot-info-box { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 10px 14px; }
    .cot-info-box .label { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; margin-bottom: 4px; }
    .cot-info-box .value { font-size: 0.88rem; font-weight: 600; color: #212529; }

    .cot-vencimiento-alert { border-radius: 8px; padding: 8px 14px; font-size: 0.82rem; font-weight: 600; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
    .cot-vencimiento-alert.vencido  { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
    .cot-vencimiento-alert.hoy      { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
    .cot-vencimiento-alert.proximo  { background: #fff3cd; color: #856404; border-left: 4px solid #fd7e14; }
    .cot-vencimiento-alert.ok       { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }

    .cot-section-title { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #6c757d; margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1px solid #e9ecef; display: flex; align-items: center; gap: 6px; }
    .cot-section-title i { color: #1a73e8; }

    .cot-products-table { border-radius: 8px; overflow: hidden; border: 1px solid #e9ecef; }
    .cot-products-table thead th { background: #f1f5fb; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; border-bottom: 2px solid #dee2e6; padding: 10px 12px; }
    .cot-products-table tbody td { font-size: 0.83rem; vertical-align: middle; padding: 9px 12px; border-bottom: 1px solid #f1f1f1; }
    .cot-products-table tbody tr:last-child td { border-bottom: none; }
    .cot-products-table tbody tr:hover { background: #f8fbff; }
    .cot-products-table tfoot td { background: #f1f5fb; font-weight: 700; padding: 10px 12px; font-size: 0.9rem; border-top: 2px solid #dee2e6; }

    .cot-nota-box { background: #fffbf0; border: 1px solid #ffe5a0; border-left: 4px solid #ffc107; border-radius: 6px; padding: 10px 14px; font-size: 0.83rem; color: #6d5c00; }
    .cot-nota-privada-box { background: #f0f4ff; border: 1px solid #b8cdf8; border-left: 4px solid #1a73e8; border-radius: 6px; padding: 10px 14px; font-size: 0.83rem; color: #003380; }
    .cot-footer-meta { font-size: 0.68rem; color: #adb5bd; text-align: right; margin-top: 8px; }
</style>

@php
    $hoy = \Carbon\Carbon::today();
    $vencimiento = $cotizacion->vencimiento ? \Carbon\Carbon::parse($cotizacion->vencimiento) : null;
    $vencDiff = $vencimiento ? (int) $hoy->diffInDays($vencimiento, false) : null;

    $badgeClass = match($cotizacion->estado) {
        'pendiente'        => 'cot-badge-pendiente',
        'venta_realizada'  => 'cot-badge-venta',
        'compra_realizada' => 'cot-badge-compra',
        'anulado'          => 'cot-badge-anulado',
        default            => 'bg-secondary text-white',
    };
    $estadoText = match($cotizacion->estado) {
        'pendiente'        => 'Pendiente',
        'venta_realizada'  => 'Venta Realizada',
        'compra_realizada' => 'Compra Realizada',
        'anulado'          => 'Anulado',
        default            => ucfirst($cotizacion->estado),
    };
@endphp

<div class="container-fluid p-0">

    {{-- HEADER CARD --}}
    <div class="cot-modal-header-card">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="cot-number">
                    <i class="fas fa-file-invoice-dollar me-2 opacity-75"></i>COTIZACION #{{ $cotizacion->numero_cotizacion }}
                </div>
                <div class="cot-meta">
                    <i class="fas fa-user-edit me-1"></i>Por: <strong>{{ $cotizacion->user->name ?? 'Sistema' }}</strong>
                    &nbsp;&middot;&nbsp;
                    <i class="fas fa-clock me-1"></i>{{ optional($cotizacion->fecha_hora)->format('d/m/Y H:i') ?? 'N/A' }}
                </div>
            </div>
            <span class="cot-badge-estado {{ $badgeClass }}">{{ $estadoText }}</span>
        </div>
    </div>

    {{-- ALERTA DE VENCIMIENTO --}}
    @if($vencimiento && $cotizacion->estado === 'pendiente')
        @if($vencDiff < 0)
            <div class="cot-vencimiento-alert vencido">
                <i class="fas fa-exclamation-triangle"></i>
                Cotizacion vencida hace {{ abs($vencDiff) }} dia(s) &mdash; {{ $vencimiento->format('d/m/Y') }}
            </div>
        @elseif($vencDiff === 0)
            <div class="cot-vencimiento-alert hoy">
                <i class="fas fa-exclamation-circle"></i>
                La cotizacion vence <strong>hoy</strong> &mdash; {{ $vencimiento->format('d/m/Y') }}
            </div>
        @elseif($vencDiff <= 3)
            <div class="cot-vencimiento-alert proximo">
                <i class="fas fa-hourglass-half"></i>
                Vence en <strong>{{ $vencDiff }}</strong> dia(s) &mdash; {{ $vencimiento->format('d/m/Y') }}
            </div>
        @else
            <div class="cot-vencimiento-alert ok">
                <i class="fas fa-calendar-check"></i>
                Valida hasta: <strong>{{ $vencimiento->format('d/m/Y') }}</strong> ({{ $vencDiff }} dias restantes)
            </div>
        @endif
    @endif

    {{-- GRID DE INFORMACION --}}
    <div class="cot-info-grid">
        <div class="cot-info-box">
            <div class="label"><i class="fas fa-user me-1"></i>Tercero</div>
            @if($cotizacion->cliente)
                <div class="value" style="color:#1a73e8;">
                    <i class="fas fa-user-circle me-1"></i>{{ $cotizacion->cliente->persona->razon_social }}
                </div>
                <div style="font-size:0.73rem;color:#6c757d;">
                    Cliente &middot; {{ $cotizacion->cliente->persona->numero_documento ?? 'Sin doc.' }}
                </div>
            @elseif($cotizacion->proveedor)
                <div class="value" style="color:#fd7e14;">
                    <i class="fas fa-truck me-1"></i>{{ $cotizacion->proveedor->persona->razon_social }}
                </div>
                <div style="font-size:0.73rem;color:#6c757d;">
                    Proveedor &middot; {{ $cotizacion->proveedor->persona->numero_documento ?? 'Sin doc.' }}
                </div>
            @else
                <div class="value text-muted"><i class="fas fa-users me-1"></i>Publico General</div>
            @endif
        </div>

        <div class="cot-info-box">
            <div class="label"><i class="fas fa-store me-1"></i>Sucursal</div>
            <div class="value">{{ $cotizacion->almacen->nombre ?? 'N/A' }}</div>
        </div>

        <div class="cot-info-box">
            <div class="label"><i class="fas fa-money-bill-wave me-1"></i>Total Cotizado</div>
            <div class="value" style="font-size:1.05rem;color:#198754;">Bs. {{ number_format($cotizacion->total, 2) }}</div>
        </div>
    </div>

    {{-- NOTAS --}}
    @if($cotizacion->nota_personal || $cotizacion->nota_cliente)
    <div class="row g-2 mb-3">
        @if($cotizacion->nota_personal)
        <div class="{{ $cotizacion->nota_cliente ? 'col-md-6' : 'col-12' }}">
            <div class="cot-nota-privada-box">
                <div style="font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">
                    <i class="fas fa-lock me-1"></i>Nota Interna (Privada)
                </div>
                {{ $cotizacion->nota_personal }}
            </div>
        </div>
        @endif
        @if($cotizacion->nota_cliente)
        <div class="{{ $cotizacion->nota_personal ? 'col-md-6' : 'col-12' }}">
            <div class="cot-nota-box">
                <div style="font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">
                    <i class="fas fa-comment me-1"></i>Nota para el Cliente
                </div>
                {{ $cotizacion->nota_cliente }}
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- SECCION PRODUCTOS --}}
    <div class="cot-section-title">
        <i class="fas fa-list-ul"></i> Detalle de Productos
        <span class="badge bg-primary ms-auto" style="font-size:0.68rem;border-radius:20px;">{{ $cotizacion->detalles->count() }} item(s)</span>
    </div>

    <div class="table-responsive mb-2">
        <table class="table cot-products-table mb-0">
            <thead>
                <tr>
                    <th width="4%">#</th>
                    <th>Producto</th>
                    <th class="text-center" width="10%">Cant.</th>
                    <th class="text-end" width="14%">P. Unit.</th>
                    <th class="text-end" width="12%">Descuento</th>
                    <th class="text-end" width="14%">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cotizacion->detalles as $i => $d)
                    @php
                        $bruto    = $d->cantidad * $d->precio_unitario;
                        $subtotal = $bruto - $d->descuento;
                    @endphp
                    <tr>
                        <td class="text-muted">{{ $i + 1 }}</td>
                        <td>
                            <div class="fw-semibold text-dark" style="font-size:0.85rem;">{{ $d->producto->nombre }}</div>
                            <div style="font-size:0.72rem;color:#adb5bd;">
                                <i class="fas fa-barcode me-1"></i>{{ $d->producto->codigo }}
                            </div>
                        </td>
                        <td class="text-center fw-semibold">{{ number_format($d->cantidad, 2) }}</td>
                        <td class="text-end">Bs. {{ number_format($d->precio_unitario, 2) }}</td>
                        <td class="text-end text-danger">
                            @if($d->descuento > 0)
                                -Bs. {{ number_format($d->descuento, 2) }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold" style="color:#198754;">Bs. {{ number_format($subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end text-muted" style="font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;">Total Cotizado:</td>
                    <td class="text-end" style="color:#198754;font-size:1.05rem;">Bs. {{ number_format($cotizacion->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="cot-footer-meta">
        Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
    </div>
</div>
