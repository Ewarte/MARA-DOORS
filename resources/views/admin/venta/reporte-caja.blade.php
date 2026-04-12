@extends('admin.layouts.app')

@section('title', 'Reporte de Caja')

@push('css')
<style>
    .audit-wrap .kpi-card {
        border: 1px solid #e8edf3;
        border-radius: 14px;
        background: #fff;
        padding: 14px 16px;
        box-shadow: 0 8px 22px rgba(20, 43, 74, 0.05);
    }
    .audit-wrap .kpi-label {
        font-size: 12px;
        color: #6c7a89;
        margin-bottom: 4px;
    }
    .audit-wrap .kpi-value {
        font-size: 22px;
        font-weight: 700;
        color: #12314d;
    }
    .audit-wrap .filters-card {
        border: 1px solid #dde6ef;
        border-radius: 14px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }
    .audit-wrap .table thead th {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .02em;
    }
    .audit-wrap .table tbody td {
        vertical-align: middle;
    }
    .audit-wrap .chip {
        border-radius: 999px;
        padding: 2px 10px;
        font-size: 11px;
        background: #eef5fc;
        color: #35608a;
    }
</style>
@endpush

@section('content')
    @include('admin.layouts.partials.alert')

    <div class="container-fluid px-4 py-4 audit-wrap">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div>
                <h1 class="page-title mb-1">Reporte de Caja</h1>
                <div class="text-muted small">Análisis de cobranza por sucursal, usuario y método de pago.</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('ventas.reporte-caja.excel', request()->query()) }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> Excel
                </a>
                <a href="{{ route('ventas.reporte-caja.pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </a>
                <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
            </div>
        </div>

        <div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius: 12px; background-color: #e3f2fd;">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle fa-2x me-3 text-primary"></i>
                <div class="text-dark">
                    <h6 class="alert-heading fw-bold mb-1" style="color: #0d47a1;">¿Cómo funciona este reporte?</h6>
                    <p class="mb-0 small" style="opacity: 0.9;">Este reporte se basa en la **cobranza efectiva** (dinero que realmente entró). Las ventas en estado <strong>Pendiente</strong> que no tienen pagos registrados aún, figurarán con montos en cero en el detalle hasta que realicen su primer abono.</p>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-2">
                <div class="kpi-card">
                    <div class="kpi-label">Ventas Consideradas</div>
                    <div class="kpi-value">{{ number_format((int)($kpis['ventas_count'] ?? 0)) }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="kpi-card">
                    <div class="kpi-label">Usuarios con Movimientos</div>
                    <div class="kpi-value">{{ number_format((int)($kpis['usuarios_count'] ?? 0)) }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="kpi-card">
                    <div class="kpi-label">Cobrado Total (Bs.)</div>
                    <div class="kpi-value">{{ number_format((float)($kpis['cobrado_total'] ?? 0), 2) }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="kpi-card">
                    <div class="kpi-label">Debe Recoger Caja (Bs.)</div>
                    <div class="kpi-value">{{ number_format((float)($kpis['debe_caja'] ?? 0), 2) }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="kpi-card">
                    <div class="kpi-label">Ventas con Saldo</div>
                    <div class="kpi-value">{{ number_format((int)($kpis['ventas_con_saldo'] ?? 0)) }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="kpi-card">
                    <div class="kpi-label">Saldo Pendiente (Bs.)</div>
                    <div class="kpi-value">{{ number_format((float)($kpis['saldo_pendiente_total'] ?? 0), 2) }}</div>
                </div>
            </div>
        </div>

        <div class="filters-card p-3 mb-3">
            <form method="GET" action="{{ route('ventas.reporte-caja') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Desde</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Hasta</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sucursal</label>
                        <select name="almacen_id" class="form-select form-select-sm">
                            <option value="">Todas</option>
                            @foreach($almacenes as $almacen)
                                <option value="{{ $almacen->id }}" {{ (string)$almacenId === (string)$almacen->id ? 'selected' : '' }}>
                                    {{ $almacen->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Usuario</label>
                        <select name="user_id" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            @foreach($usuarios as $usuario)
                                <option value="{{ $usuario->id }}" {{ (string)$userId === (string)$usuario->id ? 'selected' : '' }}>
                                    {{ $usuario->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Método</label>
                        <select name="metodo_pago" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="efectivo" {{ $metodoPago === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                            <option value="qr" {{ $metodoPago === 'qr' ? 'selected' : '' }}>QR</option>
                            <option value="debito" {{ $metodoPago === 'debito' ? 'selected' : '' }}>Débito</option>
                            <option value="deposito" {{ $metodoPago === 'deposito' ? 'selected' : '' }}>Depósito</option>
                            <option value="otro" {{ $metodoPago === 'otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Estado Pago</label>
                        <select name="estado_pago" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="pagado" {{ $estadoPago === 'pagado' ? 'selected' : '' }}>Pagado</option>
                            <option value="parcial" {{ $estadoPago === 'parcial' ? 'selected' : '' }}>Parcial</option>
                            <option value="pendiente" {{ $estadoPago === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Estado Entrega</label>
                        <select name="estado_entrega" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="entregado" {{ $estadoEntrega === 'entregado' ? 'selected' : '' }}>Entregado</option>
                            <option value="por_entregar" {{ $estadoEntrega === 'por_entregar' ? 'selected' : '' }}>Por entregar</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Cartera</label>
                        <select name="saldo_filtro" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="con_saldo" {{ $saldoFiltro === 'con_saldo' ? 'selected' : '' }}>Con saldo</option>
                            <option value="sin_saldo" {{ $saldoFiltro === 'sin_saldo' ? 'selected' : '' }}>Sin saldo</option>
                        </select>
                    </div>
                    <div class="col-md-4 ms-auto d-flex justify-content-end gap-2">
                        <input type="text" id="quickSearchCaja" class="form-control form-control-sm" placeholder="Buscar en tabla...">
                        <button class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i>Filtrar</button>
                        <a href="{{ route('ventas.reporte-caja') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-clean">
            <div class="card-header-clean d-flex justify-content-between align-items-center">
                <div class="card-header-title">
                    <i class="fas fa-cash-register"></i> Detalle por Usuario y Sucursal
                </div>
                <span class="chip">{{ count($report) }} filas</span>
            </div>
            <div class="table-responsive p-3">
                <table class="table table-sm table-bordered align-middle mb-0" id="tablaReporteCaja">
                    <thead class="table-light">
                        <tr>
                            <th>Sucursal</th>
                            <th>Usuario</th>
                            <th class="text-end">Debe recoger</th>
                            <th class="text-end">Efectivo</th>
                            <th class="text-end">QR</th>
                            <th class="text-end">Débito</th>
                            <th class="text-end">Depósito</th>
                            <th class="text-end">Otro</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report as $row)
                            <tr>
                                <td class="fw-semibold">{{ $row['almacen'] }}</td>
                                <td>{{ $row['usuario'] }}</td>
                                <td class="text-end fw-bold">Bs. {{ number_format($row['debe_recoger_caja'], 2) }}</td>
                                <td class="text-end">Bs. {{ number_format($row['efectivo'], 2) }}</td>
                                <td class="text-end">Bs. {{ number_format($row['qr'], 2) }}</td>
                                <td class="text-end">Bs. {{ number_format($row['debito'], 2) }}</td>
                                <td class="text-end">Bs. {{ number_format($row['deposito'], 2) }}</td>
                                <td class="text-end">Bs. {{ number_format($row['otro'], 2) }}</td>
                                <td class="text-end fw-bold text-primary">Bs. {{ number_format($row['total'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No hay datos para los filtros seleccionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2" class="text-end">Totales:</th>
                            <th class="text-end fw-bold">Bs. {{ number_format($totales['debe_recoger_caja'], 2) }}</th>
                            <th class="text-end">Bs. {{ number_format($totales['efectivo'], 2) }}</th>
                            <th class="text-end">Bs. {{ number_format($totales['qr'], 2) }}</th>
                            <th class="text-end">Bs. {{ number_format($totales['debito'], 2) }}</th>
                            <th class="text-end">Bs. {{ number_format($totales['deposito'], 2) }}</th>
                            <th class="text-end">Bs. {{ number_format($totales['otro'], 2) }}</th>
                            <th class="text-end fw-bold">Bs. {{ number_format($totales['total'], 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('quickSearchCaja');
        const table = document.getElementById('tablaReporteCaja');
        if (!input || !table) return;

        input.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const txt = row.textContent.toLowerCase();
                row.style.display = txt.includes(q) ? '' : 'none';
            });
        });
    });
</script>
@endpush