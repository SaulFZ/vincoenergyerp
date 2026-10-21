@extends('modules.administration.expense_claims.index')

@push('styles')
    <link href="{{ asset('assets/css/administration/expense_claims/accounts_payable.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="ap-wrap">

    {{-- ═══════════════ MÉTRICAS ═══════════════ --}}
    <div class="ap-metrics">
        <div class="ap-metric m-pending">
            <div class="ap-metric-ico"><i class="bx bx-hourglass"></i></div>
            <div class="ap-metric-info">
                <span class="ap-metric-label">Trámites pendientes de dictamen</span>
                <span class="ap-metric-value" id="m-pending">0</span>
            </div>
        </div>
        <div class="ap-metric m-adv">
            <div class="ap-metric-ico"><i class="bx bx-up-arrow-circle"></i></div>
            <div class="ap-metric-info">
                <span class="ap-metric-label">Anticipos por comprobar</span>
                <span class="ap-metric-value" id="m-adv">$0.00</span>
            </div>
        </div>
        <div class="ap-metric m-reemb">
            <div class="ap-metric-ico"><i class="bx bx-receipt"></i></div>
            <div class="ap-metric-info">
                <span class="ap-metric-label">Reembolsos por pagar</span>
                <span class="ap-metric-value" id="m-reemb">$0.00</span>
            </div>
        </div>
        <div class="ap-metric m-net">
            <div class="ap-metric-ico"><i class="bx bx-wallet"></i></div>
            <div class="ap-metric-info">
                <span class="ap-metric-label">Saldo neto en libro mayor</span>
                <span class="ap-metric-value" id="m-net">$0.00</span>
            </div>
        </div>
    </div>

    {{-- ═══════════════ TABLA PRINCIPAL ═══════════════ --}}
    <div class="ap-card">
        <div class="ap-card-head">
            <div class="ap-card-title">
                <i class="bx bx-group"></i> Cuentas por Pagar · Estado de Cuenta
                <span class="ap-badge-count" id="ap-count">0</span>
            </div>
            <div class="ap-controls">
                <div class="ap-search">
                    <i class="bx bx-search"></i>
                    <input type="text" id="ap-search" placeholder="Buscar colaborador o departamento...">
                </div>
                <div class="ap-tabs" id="ap-tabs">
                    <button type="button" class="ap-tab active" data-filter="all">Todos</button>
                    <button type="button" class="ap-tab" data-filter="pendiente">Pendientes</button>
                    <button type="button" class="ap-tab" data-filter="anticipos">Anticipo vivo</button>
                    <button type="button" class="ap-tab" data-filter="reembolsos">Reembolso por pagar</button>
                </div>
            </div>
        </div>

        <div class="ap-table-scroll">
            <table class="ap-table" id="ap-table">
                <thead>
                    <tr>
                        <th style="width:42px;"></th>
                        <th>Colaborador</th>
                        <th>Departamento</th>
                        <th>Trámites</th>
                        <th>Anticipos vivos</th>
                        <th>Reembolsos por pagar</th>
                        <th>Balance corriente</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="ap-tbody"></tbody>
            </table>
        </div>

        <div id="ap-empty" class="ap-empty hidden">
            <i class="bx bx-folder-open"></i>
            <p>Sin resultados</p>
            <span>No hay colaboradores que coincidan con la búsqueda o el filtro actual.</span>
        </div>

        <div class="ap-footer">
            <span class="lbl" id="ap-footer-lbl">0 colaborador(es)</span>
            <div class="ap-pager" id="ap-pager"></div>
            <div class="ap-size">
                <span>Mostrar:</span>
                <select id="ap-size">
                    <option value="5" selected>5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="all">Todos</option>
                </select>
            </div>
        </div>
    </div>
</div>

{{-- Tooltip flotante para conceptos largos --}}
<div id="cc-tip" class="cc-tip" role="tooltip"></div>

{{-- ═══════════════ MODAL: KARDEX (ADAPTADO A LAPTOP) ═══════════════ --}}
<div class="md-overlay" id="md-kardex" role="dialog" aria-modal="true" aria-label="Kardex Contable Integral" tabindex="-1">
    <div class="md-box xl">
        <div class="md-head">
            <div class="md-title"><i class="bx bx-receipt"></i> Kardex Contable Integral</div>
            <button type="button" class="md-close" data-close="md-kardex" title="Cerrar"><i class="bx bx-x"></i></button>
        </div>
        <div class="md-body" id="md-kardex-body"></div>
    </div>
</div>

{{-- ═══════════════ MODAL: REEMBOLSOS ═══════════════ --}}
<div class="md-overlay" id="md-reembolsos" role="dialog" aria-modal="true" aria-label="Reembolsos del Colaborador" tabindex="-1">
    <div class="md-box lg">
        <div class="md-head">
            <div class="md-title"><i class="bx bx-wallet-alt"></i> Reembolsos del Colaborador</div>
            <button type="button" class="md-close" data-close="md-reembolsos" title="Cerrar"><i class="bx bx-x"></i></button>
        </div>
        <div class="md-body" id="md-reembolsos-body"></div>
    </div>
</div>

{{-- ═══════════════ MODAL: DESGLOSE DEL ANTICIPO ═══════════════ --}}
<div class="md-overlay" id="md-advance" role="dialog" aria-modal="true" aria-label="Desglose del Anticipo" tabindex="-1">
    <div class="md-box lg">
        <div class="md-head">
            <div class="md-title"><i class="bx bx-list-ul"></i> Desglose del Anticipo</div>
            <button type="button" class="md-close" data-close="md-advance" title="Cerrar"><i class="bx bx-x"></i></button>
        </div>
        <div class="md-body" id="md-advance-body"></div>
    </div>
</div>

{{-- ═══════════════ MODAL: AJUSTE EN LIBRO MAYOR ═══════════════ --}}
<div class="md-overlay" id="md-ajuste" role="dialog" aria-modal="true" aria-label="Retención de Sobrante" tabindex="-1">
    <div class="md-box sm">
        <div class="md-head">
            <div class="md-title"><i class="bx bx-slider-alt"></i> Retención de Sobrante</div>
            <button type="button" class="md-close" data-close="md-ajuste" title="Cerrar"><i class="bx bx-x"></i></button>
        </div>
        <div class="md-body">
            <div class="f-banner">
                <div class="lb">Colaborador titular</div>
                <div class="nm" id="adj-user-name"></div>
                <div id="adj-ref-wrap" class="hidden" style="margin-top:.5rem;">
                    <div class="lb">Anticipo vinculado</div>
                    <span class="folio folio-anticipo" id="adj-ref-folio"></span>
                </div>
            </div>

            <input type="hidden" id="adj-user-id">
            <input type="hidden" id="adj-adv-id">
            <input type="hidden" id="adj-adv-saldo">

            <div class="f-group">
                <label class="f-lbl" for="adj-type">Tipo de movimiento</label>
                <select id="adj-type" class="f-select"></select>
            </div>

            <div class="f-group">
                <label class="f-lbl" for="adj-amount">Monto a retener (MXN)</label>
                <input type="number" id="adj-amount" class="f-input" step="0.01" min="0.01">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem;">
                    <span class="f-help">Máximo: saldo pendiente del anticipo.</span>
                    <button type="button" id="adj-use-total" class="f-link hidden">Usar el saldo restante exacto</button>
                </div>
            </div>

            <div id="adj-effect" class="f-effect up f-group"></div>

            <div class="f-group">
                <label class="f-lbl" for="adj-desc">Justificación de auditoría</label>
                <textarea id="adj-desc" class="f-text" maxlength="255" placeholder="Motivo de la retención (obligatorio)..."></textarea>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-m btn-secondary" data-close="md-ajuste">Cancelar</button>
                <button type="button" class="btn-m btn-primary" id="adj-submit">Registrar retención</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.AP_CONFIG = {
        employees:   {!! json_encode($advancesData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!},
        urlAP:       "{{ url('administration/expense-claims/accounts-payable') }}",
        urlAdvances: "{{ url('administration/expense-claims/advances') }}",
        urlAdjust:   "{{ route('expense-claims.accounts-payable.adjust-balance') }}",
        evidenceUrl: {{ Illuminate\Support\Js::from(route('expense-claims.evidence.view')) }},
        csrf:        "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('assets/js/administration/expense_claims/accounts-payable.js') }}"></script>
@endpush