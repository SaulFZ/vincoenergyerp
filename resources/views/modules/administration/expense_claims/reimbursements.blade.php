{{-- ════════════════════════════════════════════════════════════════════════════
VISTA BLADE: PANEL DE REEMBOLSOS Y ANTICIPOS (reimbursements.blade.php)
════════════════════════════════════════════════════════════════════════════ --}}
@extends('modules.administration.expense_claims.index')
@push('styles')
<link href="{{ asset('assets/css/administration/expense_claims/reimbursements.css') }}" rel="stylesheet">
@endpush

@section('content')

<div class="reimbursements-container">

    <div class="metrics-grid">
        <div class="metric-card metric-pending">
            <div class="metric-icon-wrap"><i class="bx bx-hourglass"></i></div>
            <div class="metric-info">
                <span class="metric-label">Pendientes</span>
                <span id="metric-pending-val" class="metric-value">0</span>
            </div>
        </div>
        <div class="metric-card metric-validated">
            <div class="metric-icon-wrap"><i class="bx bx-list-check"></i></div>
            <div class="metric-info">
                <span class="metric-label">Validados</span>
                <span id="metric-validated-val" class="metric-value">0</span>
            </div>
        </div>
        <div class="metric-card metric-approved">
            <div class="metric-icon-wrap"><i class="bx bx-check-shield"></i></div>
            <div class="metric-info">
                <span class="metric-label">Aprobados</span>
                <span id="metric-approved-val" class="metric-value">0</span>
            </div>
        </div>
        <div class="metric-card metric-rejected">
            <div class="metric-icon-wrap"><i class="bx bxs-shield-x"></i></div>
            <div class="metric-info">
                <span class="metric-label">Rechazados</span>
                <span id="metric-rejected-val" class="metric-value">0</span>
            </div>
        </div>
    </div>

    <div class="card history-card">
        <div class="card-header">
            <span class="card-title">
                <i class="bx bx-history"></i> Historial de Solicitudes
            </span>

            <div class="table-controls">
                <div class="search-wrap">
                    <i class="bx bx-search search-icon"></i>
                    <input type="text" id="table-search" class="search-input"
                        placeholder="Buscar por motivo, solicitante o folio...">
                </div>
                <div class="filter-tabs" id="filter-tabs">
                    <button class="filter-tab active" data-filter="all">Todos</button>
                    <button class="filter-tab" data-filter="pendiente">Pendiente</button>
                    <button class="filter-tab" data-filter="validado">Validado</button>
                    <button class="filter-tab" data-filter="aprobado">Aprobado</button>
                    <button class="filter-tab" data-filter="rechazado">Rechazado</button>
                </div>

                <button class="btn btn-secondary btn-new-request" onclick="window.openAdvanceModalForCreate()"
                    aria-label="Pedir Anticipo Operativo">
                    <i class="bx bx-money-withdraw"></i> Solicitar Anticipo
                </button>

                <button class="btn btn-primary btn-new-request" onclick="window.openModalForCreate()"
                    aria-label="Crear un nuevo trámite">
                    <i class="bx bx-plus-circle"></i> Nueva Solicitud
                </button>

                @if(!empty($userPermissions['ver_notificaciones_anticipos']))
                <button id="btn-notification-bell" class="btn-notification-bell" onclick="window.toggleAdvancesDrawer()"
                    aria-label="Ver historial de anticipos" title="Historial de Anticipos">
                    <i class="bx bx-bell"></i>
                    <span id="advance-bell-badge" class="notification-badge hidden">0</span>
                </button>
                @endif
            </div>
        </div>

        <div class="table-scroll" id="main-table-scroll">
            {{-- Overlay suave de actualización sin recargar la página --}}
            <div id="table-updating-overlay" class="table-updating-overlay" aria-hidden="true">
                <div class="table-updating-pill">
                    <i class="bx bx-loader-alt bx-spin"></i>
                    <span>Actualizando...</span>
                </div>
            </div>

            <table class="data-table table-animated" id="main-data-table">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>F.Usuario</th>
                        <th>Fecha</th>
                        <th>Solicitante</th>
                        <th>Área</th>
                        <th>Tipo</th>
                        <th>Motivo</th>
                        <th>Monto</th>
                        <th>Anticipo</th>
                        <th>Revisión</th>
                        <th>Estatus</th>
                        <th>Conciliación</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="dashboard-list"></tbody>
            </table>
        </div>

        <div id="empty-state" class="empty-state hidden">
            <i class="bx bx-file-blank empty-icon"></i>
            <p class="empty-title">Sin resultados encontrados</p>
            <p class="empty-desc">No hay solicitudes que coincidan con los criterios de búsqueda o filtro aplicados en
                este momento.</p>
        </div>

        <div class="table-footer">
            <div class="table-footer-left">
                <span id="table-count" class="table-count-label">0 solicitudes registradas</span>
            </div>
            <div id="pagination-controls" class="pagination-controls table-footer-center"></div>
            <div class="table-footer-right">
                <div class="page-size-wrap">
                    <span>Mostrar:</span>
                    <select id="page-size-select" class="page-size-select" onchange="window.changePageSize()">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="all">Todos</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- DRAWER ANTICIPOS --}}
<div id="advances-drawer-overlay" class="drawer-overlay" onclick="window.toggleAdvancesDrawer()"></div>

<aside id="advances-drawer" class="advances-drawer" aria-hidden="true">
    <div class="drawer-header">
        <div class="drawer-title-wrap">
            <div class="drawer-icon-box">
                <i class="bx bx-money-withdraw"></i>
            </div>
            <div>
                <h3 class="drawer-title">Historial de Anticipos</h3>
                <span class="drawer-subtitle">Seguimiento de estados en tiempo real</span>
            </div>
        </div>
        <button class="btn-close-header" onclick="window.toggleAdvancesDrawer()" aria-label="Cerrar panel">
            <i class="bx bx-x"></i>
        </button>
    </div>

    <div class="drawer-filters">
        <div class="search-wrap" style="width: 100%;">
            <i class="bx bx-search search-icon"></i>
            <input type="text" id="drawer-search" class="search-input" placeholder="Buscar por folio o solicitante..."
                style="width: 100%;">
        </div>
        <div class="filter-tabs" id="drawer-filter-tabs" style="width: 100%; justify-content: center;">
            <button class="drawer-tab active" data-filter="all">Todos</button>
            <button class="drawer-tab" data-filter="pendiente">Pendientes</button>
            <button class="drawer-tab" data-filter="aprobado">Aprobados</button>
            <button class="drawer-tab" data-filter="rechazado">Rechazados</button>
        </div>
    </div>

    <div class="drawer-body" id="advances-drawer-body"></div>
</aside>

{{-- ════════════════════════════════════════════════════════════════════════
MODAL PRINCIPAL
═════════════════════════════════════════════════════════════════════════ --}}
<div id="reimbursement-modal" class="modal-bg hidden" aria-hidden="true" role="dialog">
    <div class="modal-box large">

        <div class="modal-header">
            <h2 class="modal-title" id="main-modal-title">
                <i class="bx bx-receipt"></i> Formato de <strong>Solicitud</strong>
            </h2>
            <div class="modal-header-actions">
                <button type="button" class="btn btn-secondary" onclick="window.toggleSatPanel()"
                    title="Extraer datos desde el SAT">
                    <i class="bx bx-barcode"></i> Cargar Factura SAT
                </button>
                <button class="btn-close" onclick="window.closeModal()" aria-label="Cerrar ventana">
                    <i class="bx bx-x"></i>
                </button>
            </div>
        </div>

        <div class="modal-body" id="modal-body-scroll">

            <div id="rejection-container" class="rejection-alert hidden">
                <i class="bx bxs-error-circle"></i>
                <div class="rejection-alert-content">
                    <div class="rejection-alert-title">Motivo de Rechazo</div>
                    <div class="rejection-alert-text" id="rejection-text"></div>
                </div>
            </div>

            <div class="form-header-card">
                <div class="fh-info-strip">
                    <div class="fh-info-item">
                        <span>RFC Empresa Matriz</span>
                        <strong id="company-rfc">Cargando...</strong>
                    </div>
                    <div class="fh-info-item folio">
                        <span>Folio Principal (Sistema)</span>
                        <strong id="modal-folio-p">SIS-0000</strong>
                    </div>
                    <div class="fh-info-item user-folio">
                        <span>Folio Interno del Usuario</span>
                        <strong id="modal-folio-u">SFP-000</strong>
                    </div>
                    <div class="fh-info-item">
                        <span>Lugar de Emisión</span>
                        <div class="input-group location-group">
                            <i class="bx bx-map field-icon"></i>
                            <input type="text" id="modal-lugar" value="VHSA, TAB."
                                class="input-field input-location modal-focusable">
                        </div>
                    </div>
                    <div class="fh-info-item text-right">
                        <span>Fecha del Documento</span>
                        <strong id="modal-fecha-hoy"></strong>
                    </div>
                </div>

                <div class="fh-section">
                    <h4 class="fh-section-title"><i class="bx bx-slider-alt"></i> Configuración del Trámite</h4>
                    <div class="fh-row">

                        <div class="col-15">
                            <label class="input-label">Tipo de Solicitud <span class="req">*</span></label>
                            <div class="input-group">
                                <i class="bx bx-layer field-icon"></i>
                                <select id="modal-tipo-solicitud" class="input-field modal-focusable"
                                    onchange="window.handleTipoSolicitudChange()">
                                    <option value="" disabled selected>Seleccione tipo de solicitud...</option>
                                    <option value="Reembolso">Reembolso</option>
                                    <option value="Comprobacion Electronica">Comprobación Electrónica</option>
                                    <option value="Comprobacion Directa">Comprobación Directa</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-15">
                            <label class="input-label" id="lbl-advance">Anticipo a Comprobar</label>
                            <div class="input-group advance-select-group" id="advance-select-group">
                                <i class="bx bx-link field-icon" id="adv-icon-state"
                                    style="transition: all 0.3s ease;"></i>
                                <select id="modal-advance-id" class="input-field modal-focusable"
                                    onchange="window.toggleAdvanceViewButton(); window.onAdvanceSelected();" disabled>
                                    <option value="" disabled selected>Seleccione un tipo de solicitud...</option>
                                </select>
                                <button type="button" id="btn-view-advance" class="btn-input-action hidden"
                                    onclick="window.openAdvanceFromSelect()" title="Ver anticipo"
                                    aria-label="Ver anticipo">
                                    <i class="bx bx-show"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-15">
                            <label class="input-label">Categoría del Gasto <span class="req">*</span></label>
                            <div class="input-group">
                                <i class="bx bx-purchase-tag field-icon"></i>
                                <select id="modal-tipo-gasto" class="input-field modal-focusable">
                                    <option value="" disabled selected>Seleccione categoría de gasto...</option>
                                    <option value="viaje">Viáticos y Viaje</option>
                                    <option value="operacion">Operaciones y Campo</option>
                                    <option value="otros">Diversos / Otros</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-auto">
                            <label class="input-label">¿Es Gasto Deducible?</label>
                            <div class="radio-pill-group">
                                <label class="radio-pill-label">
                                    <input type="radio" name="is_deductible" value="1" class="modal-focusable" checked>
                                    <i class="bx bx-check"></i> Sí
                                </label>
                                <label class="radio-pill-label">
                                    <input type="radio" name="is_deductible" value="0" class="modal-focusable">
                                    <i class="bx bx-x"></i> No
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="fh-section">
                    <div class="fh-row">

                        <div class="col-auto">
                            <label class="input-label">Generar para otro?</label>
                            <div class="radio-pill-group">
                                <label class="radio-pill-label">
                                    <input type="radio" name="is_delegated" value="1" class="modal-focusable"
                                        onchange="window.handleDelegationToggle()">
                                    <i class="bx bx-check"></i> Sí
                                </label>
                                <label class="radio-pill-label">
                                    <input type="radio" name="is_delegated" value="0" class="modal-focusable"
                                        onchange="window.handleDelegationToggle()" checked>
                                    <i class="bx bx-x"></i> No
                                </label>
                            </div>
                        </div>

                        <div class="col-15">
                            <label class="input-label">Nombre del Beneficiario <span class="req">*</span></label>
                            <div class="input-group reimburse-dropdown-container">
                                <i class="bx bx-user field-icon" id="icon-solicitante"></i>
                                <input type="hidden" id="modal-beneficiary-id" value="{{ Auth::id() ?? 1 }}">
                                <input type="text" id="modal-nombre"
                                    value="{{ Auth::user()->name ?? 'Usuario No Definido' }}" class="input-field"
                                    readonly autocomplete="off">
                                <div id="employee-dropdown" class="reimburse-custom-dropdown hidden"></div>
                            </div>
                        </div>

                        <div class="col-10">
                            <label class="input-label">Área de Adscripción</label>
                            <div class="input-group">
                                <i class="bx bx-buildings field-icon"></i>
                                <input type="text" id="modal-depto"
                                    value="{{ Auth::user()->employee->area->name ?? 'Sin Asignar' }}"
                                    class="input-field" readonly>
                            </div>
                        </div>

                        <div class="col-15">
                            <label class="input-label">Centro de Costos <span class="req">*</span></label>
                            <div class="input-group">
                                <i class="bx bx-building-house field-icon"></i>
                                <select id="modal-centro-costos" class="input-field modal-focusable"></select>
                            </div>
                        </div>

                        <div class="col-25">
                            <label class="input-label">Motivo de la Erogación <span class="req">*</span></label>
                            <div class="input-group">
                                <i class="bx bx-text field-icon"></i>
                                <input type="text" id="modal-motivo" class="input-field modal-focusable"
                                    placeholder="Ej. Viáticos técnicos a pozo foráneo">
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div id="sat-panel" class="sat-panel hidden">
                <div class="sat-panel-header">
                    <div class="sat-panel-title-area">
                        <div class="sat-icon-box"><i class="bx bx-link-external"></i></div>
                        <div>
                            <h4 class="sat-panel-title">Bóveda Fiscal SAT</h4>
                            <span class="sat-panel-subtitle">Vincule comprobantes XML directamente desde el
                                servidor</span>
                        </div>
                    </div>
                    <div class="sat-tabs">
                        <button type="button" class="sat-tab active" data-target="sat-tab-uuid">
                            <i class="bx bx-search-alt"></i> Buscar Folio
                        </button>
                        <button type="button" class="sat-tab" data-target="sat-tab-xml">
                            <i class="bx bx-cloud-upload"></i> Subir XML
                        </button>
                    </div>
                </div>

                <div class="sat-panel-body">
                    <div id="sat-tab-uuid" class="sat-content active">
                        <label class="sat-label">Ingrese el Folio Fiscal (UUID) o la Serie del comprobante</label>
                        <div class="sat-search-bar-modern">
                            <i class="bx bx-barcode search-prefix-icon"></i>
                            <input type="text" id="search-uuid" class="sat-input-modern modal-focusable"
                                placeholder="Ej. A102 o 25f60d40-bdd5..." autocomplete="off">
                            <button type="button" id="btn-clear-uuid" class="btn-clear-input hidden"
                                onclick="window.limpiarBuscadorSat()">
                                <i class="bx bx-x-circle"></i>
                            </button>
                            <button type="button" id="btn-buscar" class="btn-search-modern"
                                onclick="window.buscarFacturaManual()">Buscar</button>
                            <div id="uuid-dropdown" class="sat-dropdown hidden"></div>
                        </div>
                    </div>

                    <div id="sat-tab-xml" class="sat-content hidden">
                        <div id="drop-zone" class="sat-drop-zone">
                            <div class="drop-zone-content">
                                <i class="bx bx-cloud-upload drop-icon"></i>
                                <h5>Arrastra tu archivo .XML aquí</h5>
                                <p>O haz clic para examinar en tu equipo</p>
                                <span class="drop-badge">Extracción Automática de UUID, RFC y Montos</span>
                            </div>
                            <input type="file" id="xml-input" accept=".xml" class="hidden">
                        </div>
                    </div>

                    <div id="sat-result-container" class="sat-result-box hidden">
                        <div class="sat-result-success">
                            <div class="success-icon-wrapper"><i class="bx bx-check"></i></div>
                            <div class="success-info">
                                <strong>¡Comprobante Identificado y Validado!</strong>
                                <span id="sat-result-uuid" class="uuid-text"></span>
                            </div>
                        </div>
                        <div class="sat-result-actions">
                            <div class="input-group sat-category-group">
                                <i class="bx bx-purchase-tag-alt field-icon"></i>
                                <select id="sat-category" class="input-field">
                                    <option value="" disabled selected>Clasifica este gasto...</option>
                                    <option value="cat-vuelos">Transportación, Vuelos y Peajes</option>
                                    <option value="cat-restaurantes">Consumo de Alimentos y Restaurantes</option>
                                    <option value="cat-combustible">Abastecimiento de Combustible</option>
                                    <option value="cat-otros">Cargos Varios / Misceláneos</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary btn-integrate"
                                onclick="window.agregarFilaDesdeSAT()">
                                <i class="bx bx-plus"></i> Integrar Gasto
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="expense-card">
                <div class="expense-card-header">
                    <span class="expense-card-title"><i class="bx bx-table"></i> Desglose y Análisis Analítico de
                        Gastos</span>
                </div>
                <div class="table-scroll">
                    <table class="expense-table" id="matrix-table">
                        <thead>
                            <tr class="th-group">
                                <th colspan="3" id="th-group-identificador" class="text-left"
                                    style="transition: all 0.3s;">Comprobante Identificador</th>
                                <th colspan="3" class="th-importes">Importes Subtotales</th>
                                <th colspan="2">Retenciones / Impuestos</th>
                                <th rowspan="2" class="th-total-header">Monto Total</th>
                                <th rowspan="2" class="th-total-empty"></th>
                            </tr>
                            <tr class="th-cols">
                                <th class="th-w-140">Fecha Factura</th>
                                <th class="th-w-100">Folio/Num. Fac.</th>
                                <th>Descripción Comercial</th>
                                <th class="th-w-140 th-cc-col">Centro de Costos</th>
                                <th class="th-w-90">Comp. Fiscal<br>(PDF + XML)</th>
                                <th class="th-w-90">Comp. Simple<br>No Fiscal</th>
                                <th class="th-w-90">Sin Comp.<br>y Propinas</th>
                                <th class="th-w-80">I.S.H.<br>Otros Imp.</th>
                                <th class="th-w-75">I.V.A.</th>
                            </tr>
                        </thead>
                        <tbody id="cat-vuelos">
                            <tr class="cat-row">
                                <td colspan="10" class="cat-row-colspan">
                                    <div class="cat-row-content">
                                        <span><i class="bx bxs-plane-alt"></i> I. Transportación, Vuelos y Peajes</span>
                                        <button type="button" class="btn-add-row" onclick="window.addRow('cat-vuelos')"
                                            title="Agregar Fila de Gasto"><i class="bx bx-plus"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tbody id="cat-restaurantes">
                            <tr class="cat-row">
                                <td colspan="10" class="cat-row-colspan">
                                    <div class="cat-row-content">
                                        <span><i class="bx bx-restaurant"></i> II. Consumo de Alimentos y
                                            Restaurantes</span>
                                        <button type="button" class="btn-add-row"
                                            onclick="window.addRow('cat-restaurantes')" title="Agregar Fila de Gasto"><i
                                                class="bx bx-plus"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tbody id="cat-combustible">
                            <tr class="cat-row">
                                <td colspan="10" class="cat-row-colspan">
                                    <div class="cat-row-content">
                                        <span><i class="bx bxs-gas-pump"></i> III. Abastecimiento de Combustible</span>
                                        <button type="button" class="btn-add-row"
                                            onclick="window.addRow('cat-combustible')" title="Agregar Fila de Gasto"><i
                                                class="bx bx-plus"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tbody id="cat-otros">
                            <tr class="cat-row">
                                <td colspan="10" class="cat-row-colspan">
                                    <div class="cat-row-content">
                                        <span><i class="bx bx-package"></i> IV. Cargos Varios / Misceláneos</span>
                                        <button type="button" class="btn-add-row" onclick="window.addRow('cat-otros')"
                                            title="Agregar Fila de Gasto"><i class="bx bx-plus"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════════════════
            BOTTOM SECTION: PDF + SUMMARY
            ═════════════════════════════════════════════════════════════════════════ --}}
            <div class="bottom-section">
                <div class="evidence-panel" id="evidence-panel"
                    onclick="if(window.isFormEditable){document.getElementById('evidence-upload').click();}">
                    <i class="bx bxs-file-pdf evidence-icon" id="evidence-icon"></i>
                    <h4 class="evidence-title" id="evidence-title">Gestor Documental (PDF)</h4>
                    <p class="evidence-desc" id="evidence-desc">Arrastra y suelta tus facturas, tickets y documentación
                        probatoria aquí. Carga máxima de 10MB por archivo unitario.</p>
                    <button type="button" class="btn btn-secondary hidden" id="evidence-browse-btn"
                        onclick="event.stopPropagation(); document.getElementById('evidence-upload').click()">
                        <i class="bx bx-folder-plus"></i> <span id="evidence-browse-text">Examinar archivos</span>
                    </button>
                    <input type="file" id="evidence-upload" accept=".pdf" multiple class="hidden">
                    <div id="evidence-list" class="evidence-list" onclick="event.stopPropagation()"></div>
                </div>

                <div class="summary-box">
                    <div class="summary-box-header">
                        <i class="bx bx-calculator"></i>
                        <span>Consolidado Financiero</span>
                    </div>
                    <div class="summary-body">
                        <div class="summary-row"><span class="sum-lbl">Gastos Fiscales (XML+PDF):</span><span
                                id="sum-fiscal" class="sum-val">$0.00</span></div>
                        <div class="summary-row"><span class="sum-lbl">Gastos No Fiscales (Notas):</span><span
                                id="sum-simple" class="sum-val">$0.00</span></div>
                        <div class="summary-row"><span class="sum-lbl">Sin Comprobante / Propinas:</span><span
                                id="sum-propinas" class="sum-val">$0.00</span></div>
                        <div class="summary-row"><span class="sum-lbl">Impuesto (I.V.A.):</span><span id="sum-iva"
                                class="sum-val">$0.00</span></div>
                        <div class="summary-row"><span class="sum-lbl">I.S.H. y Retenciones:</span><span id="sum-ish"
                                class="sum-val">$0.00</span></div>
                        <div class="sum-total-row">
                            <span class="sum-total-lbl" id="sum-total-lbl">TOTAL COMPROBADO</span>
                            <span id="sum-total" class="sum-total-val" data-value="0">$0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ★ SECCIÓN SOBRANTE DEL ANTICIPO (A favor de la empresa: Anticipo > Comprobado) ★ --}}
            <div class="leftover-section hidden" id="leftover-section">
                <div class="leftover-question-bar bar-sobrante">
                    <div class="leftover-question-left">
                        <i class="bx bx-layer-minus"></i>
                        <div>
                            <strong>Sobrante del anticipo detectado (A devolver a la empresa)</strong>
                            <small id="leftover-stage-subtitle">
                                El monto comprobado es menor al anticipo entregado. La devolución y carga del
                                comprobante PDF se habilitarán una vez que el trámite sea aprobado.
                            </small>
                        </div>
                    </div>
                </div>

                <div id="leftover-preapproval-banner" class="leftover-body">
                    <div class="leftover-preapproval-box">
                        <div class="leftover-preapproval-info">
                            <i class="bx bx-lock-alt"></i>
                            <div>
                                <div class="leftover-preapproval-title">Devolución en espera de Aprobación Final</div>
                                <div class="leftover-preapproval-desc">Para garantizar que transfieras el importe
                                    exacto, espera a que el revisor y el aprobador autoricen tus facturas.</div>
                            </div>
                        </div>
                        <div class="leftover-preapproval-amount">
                            <span>Sobrante estimado a devolver:</span>
                            <strong id="leftover-preview-amount">$0.00</strong>
                        </div>
                    </div>
                </div>

                <div class="leftover-body hidden" id="leftover-body">
                    <div class="leftover-grid">
                        <div class="leftover-fields">

                            <div class="leftover-field">
                                <label class="input-label">Método de Devolución <span class="req">*</span></label>
                                <div class="input-group">
                                    <i class="bx bx-credit-card field-icon"></i>
                                    <select id="leftover-method" class="input-field leftover-stage-input">
                                        <option value="Transferencia" selected>Transferencia Bancaria</option>
                                    </select>
                                </div>
                            </div>

                            <div class="leftover-field">
                                <label class="input-label">Monto del Sobrante a Devolver (MXN)</label>
                                <div class="input-group">
                                    <i class="bx bx-dollar field-icon"></i>
                                    <input type="number" id="leftover-amount" class="input-field leftover-amount-locked"
                                        step="0.01" min="0" placeholder="0.00" readonly tabindex="-1">
                                </div>
                                <small
                                    style="display:block; margin-top:.35rem; font-size:.7rem; color:var(--secondary-lighter);">
                                    <i class="bx bx-lock-alt"></i> Monto definitivo autorizado: Anticipo − Total
                                    comprobado
                                </small>
                            </div>

                            <div class="leftover-field full">
                                <label class="input-label">Notas / Referencia Bancaria</label>
                                <div class="input-group">
                                    <textarea id="leftover-notes" class="input-field leftover-stage-input"
                                        placeholder="Ej. Devolución vía transferencia a cuenta Banorte, referencia 0012345..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="leftover-evidence">
                            <label class="input-label">Evidencia de Devolución (PDF) <span class="req">*</span></label>
                            <div class="leftover-evidence-panel" id="leftover-evidence-panel"
                                onclick="if(window.isLeftoverEditable){document.getElementById('leftover-proof-upload').click();}">
                                <i class="bx bxs-file-pdf leftover-ev-icon" id="leftover-ev-icon"></i>
                                <h4 class="leftover-ev-title" id="leftover-ev-title">Comprobante de Devolución</h4>
                                <p class="leftover-ev-desc" id="leftover-ev-desc">Sube el comprobante de transferencia o
                                    ficha de depósito (PDF, máx. 10MB).</p>
                                <button type="button" class="btn btn-secondary hidden" id="leftover-browse-btn"
                                    onclick="event.stopPropagation(); document.getElementById('leftover-proof-upload').click()">
                                    <i class="bx bx-folder-plus"></i> <span id="leftover-browse-text">Examinar archivo
                                        PDF</span>
                                </button>
                                <input type="file" id="leftover-proof-upload" accept=".pdf" class="hidden">
                                <div id="leftover-proof-list" class="evidence-list" onclick="event.stopPropagation()">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ★ SECCIÓN EXCEDENTE DEL ANTICIPO (A favor del colaborador: Comprobado > Anticipo) ★ --}}
            <div class="leftover-section hidden" id="excess-section" style="margin-top: 1.25rem;">
                <div class="leftover-question-bar bar-excedente">
                    <div class="leftover-question-left">
                        <i class="bx bx-trending-up"></i>
                        <div>
                            <strong>Excedente a favor del colaborador detectado</strong>
                            <small>El monto comprobado supera al anticipo entregado. La diferencia se reembolsará dentro
                                de este mismo trámite una vez aprobado.</small>
                        </div>
                    </div>
                </div>

                <div class="leftover-body" id="excess-body">
                    <div class="leftover-grid">
                        <div class="leftover-fields">
                            <div class="leftover-field">
                                <label class="input-label">Método de Reembolso de Excedente</label>
                                <div class="input-group">
                                    <i class="bx bx-credit-card field-icon"></i>
                                    <select id="excess-method" class="input-field modal-focusable">
                                        <option value="Transferencia" selected>Transferencia Bancaria</option>
                                    </select>
                                </div>
                            </div>

                            <div class="leftover-field">
                                <label class="input-label">Monto del Excedente a Reembolsar (MXN)</label>
                                <div class="input-group">
                                    <i class="bx bx-dollar field-icon"></i>
                                    <input type="number" id="excess-amount" class="input-field leftover-amount-locked"
                                        step="0.01" min="0" placeholder="0.00" readonly tabindex="-1">
                                </div>
                                <small
                                    style="display:block; margin-top:.35rem; font-size:.7rem; color:var(--secondary-lighter);">
                                    <i class="bx bx-lock-alt"></i> Cálculo automático: Total comprobado − Monto del
                                    anticipo
                                </small>
                            </div>

                            <div class="leftover-field full">
                                <label class="input-label">Comentarios / Cuenta Destino (Opcional)</label>
                                <div class="input-group">
                                    <textarea id="excess-notes" class="input-field modal-focusable"
                                        placeholder="Ej. Solicito depósito de diferencia en cuenta de nómina..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="leftover-evidence">
                            <label class="input-label">Comprobante de Pago de Tesorería</label>
                            <div class="leftover-evidence-panel readonly-mode" id="excess-evidence-panel">
                                <i class="bx bx-time-five leftover-ev-icon" id="excess-ev-icon"></i>
                                <h4 class="leftover-ev-title" id="excess-ev-title">Pendiente de Liquidación por
                                    Tesorería</h4>
                                <p class="leftover-ev-desc" id="excess-ev-desc">Una vez aprobada la comprobación,
                                    Tesorería adjuntará aquí el comprobante del pago del excedente.</p>
                                <div id="excess-proof-list" class="evidence-list" onclick="event.stopPropagation()">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="modal-footer">
            <div><span class="modal-footer-note"><i class="bx bx-info-circle"></i> Para garantizar una autorización
                    rápida, asegúrate de adjuntar el PDF de soporte.</span></div>
            <div class="modal-footer-right" id="footer-create">
                <button type="button" class="btn btn-cancel" onclick="window.closeModal()">Cancelar</button>
                <button type="button" id="btn-borrador" class="btn btn-secondary" onclick="window.saveDraft()"><i
                        class="bx bx-save"></i> Guardar Borrador</button>
                <button type="button" id="btn-enviar" class="btn btn-primary" onclick="window.verifyAndSubmit()"><i
                        class="bx bx-send"></i> Emitir Solicitud a Revisión</button>
            </div>
            <div class="modal-footer-right hidden" id="footer-view">
                <button type="button" class="btn btn-secondary" onclick="window.closeModal()">Cerrar</button>
            </div>
            <div class="modal-footer-right hidden" id="footer-evaluate">
                <button type="button" class="btn btn-secondary" onclick="window.closeModal()">Cerrar</button>
                <button type="button" class="btn btn-fail-solid" onclick="window.processEvaluation('rechazado')"><i
                        class="bx bx-x"></i> Rechazar</button>
                <button type="button" class="btn btn-secondary btn-validate-special" id="btn-eval-validate"
                    onclick="window.processEvaluation('validado')"><i class="bx bx-list-check"></i> Validar</button>
                <button type="button" class="btn btn-ok-solid" id="btn-eval-approve"
                    onclick="window.processEvaluation('aprobado')"><i class="bx bx-check-double"></i> Aprobar</button>
            </div>
            <div class="modal-footer-right hidden" id="footer-leftover">
                <button type="button" class="btn btn-cancel" onclick="window.closeModal()">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="window.submitLeftoverProof()"><i
                        class="bx bx-upload"></i> Enviar Comprobante de Devolución</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL ANTICIPO --}}
<div id="advance-modal" class="modal-bg hidden" aria-hidden="true" role="dialog">
    <div class="modal-box medium">

        <form id="form-advance" onsubmit="event.preventDefault(); window.submitAdvance();">

            <div class="modal-header">
                <h2 class="modal-title" id="adv-modal-title"><i class="bx bx-money-withdraw"></i> Solicitud de
                    <strong>Anticipo</strong></h2>
                <div class="modal-header-actions">
                    <button type="button" class="btn-close" onclick="window.closeAdvanceModal()"
                        aria-label="Cerrar ventana"><i class="bx bx-x"></i></button>
                </div>
            </div>

            <div class="modal-body">
                <div class="form-header-card m-bottom-125">
                    <div class="fh-info-strip">
                        <div class="fh-info-item folio">
                            <span>Folio del Anticipo</span>
                            <strong id="adv-modal-folio">Nuevo Solicitud</strong>
                        </div>
                        <div class="fh-info-item text-center">
                            <span>Comprobación</span>
                            <div id="adv-modal-settlement">...</div>
                        </div>
                        <div class="fh-info-item text-right">
                            <span>Autorización</span>
                            <div id="adv-modal-status" style="margin-top: 4px;">Generando...</div>
                        </div>
                    </div>
                </div>

                <div class="fh-section" style="border: none; padding-bottom: 0;">

                    <div class="fh-row" style="margin-bottom: 1.25rem;">
                        <div class="col-12">
                            <label class="input-label">Fecha de Solicitud</label>
                            <div class="input-group">
                                <i class="bx bx-calendar field-icon"></i>
                                <input type="text" id="adv-date-text" class="input-field" readonly required>
                            </div>
                        </div>
                    </div>

                    <div class="fh-row" style="margin-bottom: 1.25rem;">
                        <div class="col-auto">
                            <label class="input-label">Capturar Otro Solicitante</label>
                            <div class="radio-pill-group">
                                <label class="radio-pill-label">
                                    <input type="radio" name="adv_is_delegated" value="1" class="adv-focusable"
                                        onchange="window.handleAdvDelegationToggle()">
                                    <i class="bx bx-check"></i> Sí
                                </label>
                                <label class="radio-pill-label">
                                    <input type="radio" name="adv_is_delegated" value="0" class="adv-focusable"
                                        onchange="window.handleAdvDelegationToggle()" checked>
                                    <i class="bx bx-x"></i> No
                                </label>
                            </div>
                        </div>

                        <div class="col-15">
                            <label class="input-label">Nombre del Solicitante <span class="req">*</span></label>
                            <div class="input-group reimburse-dropdown-container">
                                <i class="bx bx-user field-icon" id="adv-icon-solicitante"></i>
                                <input type="hidden" id="adv-beneficiary-id" value="{{ Auth::id() ?? 1 }}">
                                <input type="text" id="adv-user-name" class="input-field" readonly autocomplete="off"
                                    required>
                                <div id="adv-employee-dropdown" class="reimburse-custom-dropdown hidden"></div>
                            </div>
                        </div>
                    </div>

                    <div class="fh-row" style="margin-bottom: 1.25rem;">
                        <div class="col-12">
                            <label class="input-label">Motivo del Anticipo <span class="req">*</span></label>
                            <div class="input-group">
                                <i class="bx bx-briefcase field-icon"></i>
                                <select id="adv-reason" class="input-field adv-focusable" required>
                                    <option value="" disabled selected>Seleccione un motivo...</option>
                                    <option value="Viaticos">Viáticos y Hospedaje</option>
                                    <option value="Operativos">Gastos Operativos (Campo)</option>
                                    <option value="Caja Chica">Fondo de Caja Chica</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="input-label">Método de Entrega / Tipo <span class="req">*</span></label>
                            <div class="input-group">
                                <i class="bx bx-credit-card field-icon"></i>
                                <select id="adv-type" class="input-field adv-focusable" required>
                                    <option value="" disabled selected>Seleccione un método...</option>
                                    <option value="Transferencia">Transferencia Bancaria</option>
                                    <option value="Tarjeta Toka">Tarjeta de Toka</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="fh-row" style="margin-bottom: 1.25rem;">
                        <div class="col-15">
                            <label class="input-label" style="color: var(--primary-dark);">Monto Solicitado (MXN) <span
                                    class="req">*</span></label>
                            <div class="input-group">
                                <i class="bx bx-dollar field-icon icon-highlight"></i>
                                <input type="number" id="adv-amount" class="input-field adv-focusable amount-highlight"
                                    placeholder="Ej. 5000.00" min="1" step="0.01" required>
                            </div>
                        </div>

                        <div class="col-15">
                            <label class="input-label">Autorizador Asignado <span class="req">*</span></label>
                            <div class="input-group">
                                <i class="bx bx-user-check field-icon"></i>
                                <select id="adv-approver" class="input-field adv-focusable" required>
                                    <option value="" disabled selected>Seleccione autorizador...</option>
                                    @foreach($approvers as $appr)
                                    <option value="{{ $appr['id'] }}">{{ $appr['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="fh-row">
                        <div class="col-50">
                            <label class="input-label">Descripción / Justificación Operativa <span
                                    class="req">*</span></label>
                            <div class="input-group">
                                <textarea id="adv-desc" class="input-field adv-focusable"
                                    placeholder="Explique clara y brevemente para qué se destinarán los fondos solicitados..."
                                    required></textarea>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <div style="width: 100%; display: flex; flex-direction: column; gap: 1rem;">
                    <span class="modal-footer-note" id="adv-modal-note"><i class="bx bx-info-circle"></i> Los anticipos
                        requieren validación de la gerencia financiera para su autorización.</span>

                    <div id="adv-footer-create" style="display: flex; gap: 0.75rem; width: 100%;">
                        <button type="button" class="btn btn-cancel" style="flex: 1; justify-content: center;"
                            onclick="window.closeAdvanceModal()">
                            <i class="bx bx-x"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center;">
                            <i class="bx bx-send"></i> Emitir Solicitud
                        </button>
                    </div>

                    <div id="adv-footer-view" class="hidden"
                        style="display: flex; gap: 0.75rem; width: 100%; justify-content: flex-end;">
                        <button type="button" class="btn btn-secondary" onclick="window.closeAdvanceModal()">Cerrar
                            Vista</button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

{{-- MODAL HISTORIAL --}}
<div id="history-modal" class="modal-bg hidden" aria-hidden="true" role="dialog">
    <div class="modal-box medium">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="bx bx-history"></i> Historial del Solicitud <strong id="history-folio"></strong>
            </h2>
            <div class="modal-header-actions">
                <button type="button" class="btn-close" onclick="window.closeHistoryModal()"
                    aria-label="Cerrar ventana">
                    <i class="bx bx-x"></i>
                </button>
            </div>
        </div>

        <div class="modal-body" style="background-color: #f8fafc;">
            <div class="timeline" id="history-timeline"></div>
        </div>

        <div class="modal-footer" style="justify-content: flex-end;">
            <button type="button" class="btn btn-secondary" onclick="window.closeHistoryModal()">Cerrar
                Historial</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>

<script>
    /* ==========================================================================
       ── 1. VARIABLES GLOBALES, PERMISOS Y ESTADO ──
       ========================================================================== */
    const rfcEmpresa = "{{ $rfcEmpresa }}";
    const companyEmployees = {!! json_encode($usersList ?? []) !!};
    const costCentersData = {!! json_encode($costCenters ?? []) !!};
    let userPermissions = {!! json_encode($userPermissions ?? [
        'validar_solicitud' => false,
        'aprobar_solicitud' => false,
        'ver_todo_solicitudes' => false,
        'ver_notificaciones_anticipos' => false,
    ]) !!};

    const sessionUser = {
        id: {{ Auth::id() ?? 1 }},
        nombre: "{{ Auth::user()->name ?? 'Usuario No Definido' }}",
        depto: "{{ Auth::user()->employee->area->name ?? 'Sin Asignar' }}",
        rfc: "{{ Auth::user()->employee->rfc ?? 'S/N' }}"
    };

    let currentActiveClaimId = null;
    let currentClaimApprovalStatus = 'en_espera';
    let isEditMode = false;
    let tempSatData = null;

    // ★ Banderas globales de edición
    window.isFormEditable = false;
    window.isLeftoverEditable = false;

    // ★ SOBRANTE Y EXCEDENTE ★
    let leftoverProofFile = null;
    let existingLeftoverProofPath = null;
    let existingExcessProofPath = null;
    let currentAdvanceAmount = 0;
    window.advanceAmountsMap = {};
    const maxLeftoverSize = 10 * 1024 * 1024;

    // ★ EVIDENCIAS (facturas) ★
    let existingEvidencePaths = [];

    document.getElementById('company-rfc').textContent = rfcEmpresa;

    const modalNombre = document.getElementById('modal-nombre');
    const modalDepto = document.getElementById('modal-depto');
    const beneficiaryId = document.getElementById('modal-beneficiary-id');
    const dropdown = document.getElementById('employee-dropdown');
    const iconSolicitante = document.getElementById('icon-solicitante');
    const mainCostCenterSelect = document.getElementById('modal-centro-costos');

    const todayText = new Date().toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
    document.getElementById('modal-fecha-hoy').textContent = new Date().toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

    /* ==========================================================================
       ── 2. UTILIDADES, PERMISOS Y RECARGA SILENCIOSA DE TABLA ──
       ========================================================================== */
    window.normalizeAreaName = function(str) {
        return String(str || '').trim().toLowerCase();
    };

    window.canUserValidateClaim = function(req) {
        if (!req) return false;
        if (typeof req.can_validate === 'boolean') return req.can_validate;
        const statusReview = req.status || req.status_review;
        if (!userPermissions.validar_solicitud || statusReview !== 'pendiente') return false;
        if (userPermissions.ver_todo_solicitudes) return true;
        const claimArea = req.depto || req.area || (req.beneficiary?.employee?.area?.name ?? '');
        return window.normalizeAreaName(claimArea) === window.normalizeAreaName(sessionUser.depto);
    };

    window.canUserApproveClaim = function(req) {
        if (!req) return false;
        if (typeof req.can_approve === 'boolean') return req.can_approve;
        const statusReview   = req.status || req.status_review;
        const statusApproval = req.aprobacion || req.status_approval;
        if (!userPermissions.aprobar_solicitud || statusReview !== 'validado' || statusApproval !== 'en_revision') {
            return false;
        }
        const claimArea = req.depto || req.area || (req.beneficiary?.employee?.area?.name ?? '');
        return window.normalizeAreaName(claimArea) === window.normalizeAreaName(sessionUser.depto);
    };

    window.showAlert = function(msg, type = 'success') {
        const titles = { 'success': '¡Operación Exitosa!', 'error': 'Error en la operación', 'warning': 'Atención Requerida', 'info': 'Información' };
        const colors = { 'success': '#2d7d46', 'error': '#b91c1c', 'warning': '#b45309', 'info': '#1d4ed8' };
        return Swal.fire({
            title: `<span style="font-family:'Poppins', sans-serif;">${titles[type]}</span>`,
            html: `<span style="font-family:'Poppins', sans-serif; font-size:14px; color:#64748b;">${msg}</span>`,
            icon: type,
            confirmButtonColor: colors[type],
            confirmButtonText: `<span style="font-family:'Poppins', sans-serif; font-weight:600;">Entendido</span>`
        });
    };

    window.fmt = function(n) {
        return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n);
    };

    window.formatBytes = function(bytes, decimals = 2) {
        if (!+bytes) return '0 Bytes';
        const k = 1024, dm = decimals < 0 ? 0 : decimals, sizes = ['Bytes', 'KB', 'MB', 'GB'], i = Math.floor(Math.log(bytes) / Math.log(k));
        return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
    };

    // ★ Actualiza los datos de la tabla con overlay oscuro "Actualizando..." sin parpadear ★
    window.refreshTableData = async function() {
        const overlay = document.getElementById('table-updating-overlay');
        if (overlay) overlay.classList.add('active');

        const minWait = new Promise(resolve => setTimeout(resolve, 420));

        try {
            const response = await fetch(`{{ route('expense-claims.reimbursements') }}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            await minWait;

            if (data && data.success && Array.isArray(data.requestsData)) {
                requests = data.requestsData;
                if (data.userPermissions) {
                    userPermissions = data.userPermissions;
                }
                window.renderDashboard(true);
            }
            if (userPermissions.ver_notificaciones_anticipos) {
                window.fetchAdvancesHistory(true);
            }
        } catch (err) {
            console.error('Error al sincronizar la tabla:', err);
        } finally {
            if (overlay) overlay.classList.remove('active');
        }
    };

    /* ==========================================================================
       ── 2.1. VISOR DE PDFs EN VENTANA EMERGENTE ──
       ========================================================================== */
    window.openPdfViewerItems = function(items, startIndex = 0, title = 'Visor de PDF') {
        if (!items || items.length === 0) {
            window.showAlert('No hay archivos PDF para visualizar.', 'warning');
            return;
        }

        const w = window.open('', '_blank', 'width=1280,height=880,menubar=no,toolbar=no,location=no,status=no,scrollbars=yes,resizable=yes');
        if (!w) {
            window.showAlert('Debes permitir las ventanas emergentes del navegador para visualizar los PDFs.', 'warning');
            return;
        }

        const urls  = items.map(it => it.url);
        const names = items.map(it => it.name);
        const safeTitle = (title || 'Visor de PDF').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const initialIndex = Math.max(0, Math.min(startIndex, items.length - 1));

        const html = `<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>${safeTitle}</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  html,body{height:100%}
  body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',sans-serif;background:#0f172a;color:#fff;display:flex;flex-direction:column;overflow:hidden}
  .header{background:#152845;padding:14px 24px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #20406d;flex-shrink:0;box-shadow:0 2px 10px rgba(0,0,0,.3)}
  .header h1{font-size:15px;font-weight:600;color:#e2e8f0;display:flex;align-items:center;gap:8px}
  .header h1 svg{color:#93c5fd}
  .counter{background:#20406d;color:#fff;padding:5px 14px;border-radius:99px;font-size:12px;font-weight:700;letter-spacing:.5px}
  .toolbar{background:#1e293b;padding:10px 24px;display:flex;justify-content:space-between;align-items:center;gap:16px;border-bottom:1px solid #334155;flex-shrink:0}
  .filename{font-size:13px;color:#cbd5e1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1;font-weight:500}
  .filename strong{color:#fff}
  .nav{display:flex;gap:8px;flex-shrink:0}
  .nav button{background:#152845;color:#fff;border:1px solid #20406d;padding:8px 18px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;display:flex;align-items:center;gap:6px;transition:all .15s;font-family:inherit}
  .nav button:hover:not(:disabled){background:#20406d;border-color:#3b82f6;transform:translateY(-1px)}
  .nav button:disabled{opacity:.35;cursor:not-allowed}
  .content{flex:1;overflow:hidden;background:#334155;position:relative}
  iframe{width:100%;height:100%;border:none;background:#fff;display:block}
  .hint{position:absolute;bottom:12px;right:16px;background:rgba(15,23,42,.85);color:#94a3b8;padding:6px 12px;border-radius:6px;font-size:11px;font-weight:500;pointer-events:none}
</style>
</head>
<body>
  <div class="header">
    <h1>📄 ${safeTitle}</h1>
    <span class="counter" id="counter">1 / ${items.length}</span>
  </div>
  <div class="toolbar">
    <span class="filename" id="filename"></span>
    <div class="nav">
      <button id="btn-prev" type="button">◀ Anterior</button>
      <button id="btn-next" type="button">Siguiente ▶</button>
    </div>
  </div>
  <div class="content">
    <iframe id="pdf-frame" src=""></iframe>
    <div class="hint">Usa ← → para navegar · Mueve esta ventana a otro monitor</div>
  </div>
<script>
  var currentIndex = ${initialIndex};
  var urls  = ${JSON.stringify(urls)};
  var names = ${JSON.stringify(names)};
  var total = urls.length;

  function escapeHtml(s){return String(s).replace(/[&<>"']/g,function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];});}

  function render(){
    document.getElementById('filename').innerHTML = '<strong>' + escapeHtml(names[currentIndex]) + '</strong>';
    document.getElementById('counter').textContent = (currentIndex + 1) + ' / ' + total;
    document.getElementById('pdf-frame').src = urls[currentIndex];
    document.getElementById('btn-prev').disabled = currentIndex <= 0;
    document.getElementById('btn-next').disabled = currentIndex >= total - 1;
  }

  document.getElementById('btn-prev').onclick = function(){ if (currentIndex > 0){ currentIndex--; render(); } };
  document.getElementById('btn-next').onclick = function(){ if (currentIndex < total - 1){ currentIndex++; render(); } };
  document.addEventListener('keydown', function(e){
    if (e.key === 'ArrowLeft'  && currentIndex > 0){ currentIndex--; render(); }
    if (e.key === 'ArrowRight' && currentIndex < total - 1){ currentIndex++; render(); }
  });

  render();
<\/script>
</body>
</html>`;

        w.document.open();
        w.document.write(html);
        w.document.close();
    };

    window.openEvidenceViewer = function(startIndex) {
        const items = [];

        existingEvidencePaths.forEach(p => {
            items.push({
                url: `{{ url('administration/expense-claims/evidence/view') }}?path=${encodeURIComponent(p)}`,
                name: p.split('/').pop()
            });
        });

        evidenciasFiles.forEach(f => {
            items.push({
                url: URL.createObjectURL(f),
                name: f.name
            });
        });

        window.openPdfViewerItems(items, startIndex || 0, 'Facturas adjuntas');
    };

    window.openLeftoverViewer = function() {
        const items = [];

        if (existingLeftoverProofPath) {
            items.push({
                url: `{{ url('administration/expense-claims/evidence/view') }}?path=${encodeURIComponent(existingLeftoverProofPath)}`,
                name: existingLeftoverProofPath.split('/').pop()
            });
        }

        if (leftoverProofFile) {
            items.push({
                url: URL.createObjectURL(leftoverProofFile),
                name: leftoverProofFile.name
            });
        }

        window.openPdfViewerItems(items, 0, 'Comprobante de Devolución');
    };

    window.openExcessViewer = function() {
        if (!existingExcessProofPath) return;
        window.openPdfViewerItems([{
            url: `{{ url('administration/expense-claims/evidence/view') }}?path=${encodeURIComponent(existingExcessProofPath)}`,
            name: existingExcessProofPath.split('/').pop()
        }], 0, 'Comprobante de Pago de Excedente');
    };

    /* ==========================================================================
       ── 2.2. CONCILIACIÓN DE DIFERENCIAS: SOBRANTE Y EXCEDENTE ──
       ========================================================================== */
    window.isComprobacionType = function() {
        const tipo = document.getElementById('modal-tipo-solicitud').value;
        return tipo === 'Comprobacion Electronica' || tipo === 'Comprobacion Directa';
    };

    let excessAlertTimer = null;
    let excessAlertShownKey = null;
    window.resetExcessAlert = function() {
        clearTimeout(excessAlertTimer);
        excessAlertTimer = null;
        excessAlertShownKey = null;
    };
    window.scheduleExcessAlert = function() {
        clearTimeout(excessAlertTimer);
        const notify = () => {
            excessAlertTimer = null;
            const modal = document.getElementById('reimbursement-modal');
            const section = document.getElementById('excess-section');
            const amount = parseFloat(document.getElementById('excess-amount').value) || 0;
            const key = String(currentActiveClaimId || 'nuevo') + ':' + document.getElementById('modal-advance-id').value;
            if (!modal || modal.classList.contains('hidden') || !section || section.classList.contains('hidden') || !window.isComprobacionType() || amount <= 0 || excessAlertShownKey === key) return;
            if (Swal.isVisible()) {
                excessAlertTimer = setTimeout(notify, 350);
                return;
            }
            excessAlertShownKey = key;
            Swal.fire({
                icon: 'info',
                title: 'Excedente detectado',
                text: 'El monto comprobado supera al anticipo por ' + window.fmt(amount) + '. Esta diferencia está a favor del colaborador y se reembolsará dentro de este mismo trámite una vez aprobado.',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#152845',
                iconColor: '#20406d'
            });
        };
        excessAlertTimer = setTimeout(notify, 600);
    };

    window.recalcLeftover = function() {
        const leftoverSection   = document.getElementById('leftover-section');
        const excessSection     = document.getElementById('excess-section');
        const leftoverInput     = document.getElementById('leftover-amount');
        const leftoverPreview   = document.getElementById('leftover-preview-amount');
        const preApprovalBanner = document.getElementById('leftover-preapproval-banner');
        const leftoverBody      = document.getElementById('leftover-body');
        const excessInput       = document.getElementById('excess-amount');

        if (!leftoverSection || !excessSection) return;

        if (!window.isComprobacionType() || currentAdvanceAmount <= 0) {
            window.resetExcessAlert();
            leftoverSection.classList.add('hidden');
            excessSection.classList.add('hidden');
            if (leftoverInput) leftoverInput.value = '';
            if (excessInput) excessInput.value = '';
            return;
        }

        const totalComprobado = parseFloat(document.getElementById('sum-total').getAttribute('data-value')) || 0;
        const advanceAmt      = parseFloat(currentAdvanceAmount) || 0;
        const diff            = parseFloat((advanceAmt - totalComprobado).toFixed(2));

        if (diff >= 0) window.resetExcessAlert();

        if (diff > 0) {
            leftoverSection.classList.remove('hidden');
            excessSection.classList.add('hidden');
            if (leftoverInput) leftoverInput.value = diff.toFixed(2);
            if (leftoverPreview) leftoverPreview.textContent = window.fmt(diff);
            if (excessInput) excessInput.value = '';

            const isApproved = (currentClaimApprovalStatus === 'aprobado');
            if (preApprovalBanner) preApprovalBanner.classList.toggle('hidden', isApproved);
            if (leftoverBody) leftoverBody.classList.toggle('hidden', !isApproved);
        } else if (diff < 0) {
            leftoverSection.classList.add('hidden');
            if (leftoverInput) leftoverInput.value = '';

            excessSection.classList.remove('hidden');
            if (excessInput) excessInput.value = Math.abs(diff).toFixed(2);
            window.scheduleExcessAlert();
        } else {
            leftoverSection.classList.add('hidden');
            excessSection.classList.add('hidden');
            if (leftoverInput) leftoverInput.value = '';
            if (excessInput) excessInput.value = '';
        }
    };

    window.onAdvanceSelected = async function() {
        const sel = document.getElementById('modal-advance-id');
        const val = sel ? sel.value : null;

        if (!val) {
            currentAdvanceAmount = 0;
            window.recalcLeftover();
            return;
        }

        if (window.advanceAmountsMap[val]) {
            currentAdvanceAmount = parseFloat(window.advanceAmountsMap[val]) || 0;
            window.recalcLeftover();
            return;
        }

        try {
            const resp = await fetch(`{{ url('administration/expense-claims/advances') }}/${val}`);
            const data = await resp.json();
            if (data && data.success && data.data && data.data.amount != null) {
                currentAdvanceAmount = parseFloat(data.data.amount) || 0;
                window.advanceAmountsMap[val] = currentAdvanceAmount;
            }
        } catch (e) {
            console.warn('No se pudo obtener el monto del anticipo:', e);
        }
        window.recalcLeftover();
    };

    window.procesarArchivoLeftover = function(file) {
        if (!file) return;
        if (!window.isLeftoverEditable) return;
        if (file.type !== 'application/pdf') { window.showAlert('Solo se permiten archivos PDF.', 'warning'); return; }
        if (file.size > maxLeftoverSize) { window.showAlert('El archivo excede el límite de 10MB.', 'error'); return; }
        leftoverProofFile = file;
        window.renderLeftoverFile();
    };

    window.removeLeftoverFile = function() {
        leftoverProofFile = null;
        window.renderLeftoverFile();
    };

    window.removeExistingEvidence = function(index) {
        existingEvidencePaths.splice(index, 1);
        window.renderFileList();
    };

    window.renderLeftoverFile = function() {
        const list = document.getElementById('leftover-proof-list');
        const titleEl = document.getElementById('leftover-ev-title');
        const descEl = document.getElementById('leftover-ev-desc');
        const panelEl = document.getElementById('leftover-evidence-panel');
        if (!list) return;

        const editable = !!window.isLeftoverEditable;
        const hasNew = !!leftoverProofFile;
        const hasExisting = !!existingLeftoverProofPath;

        if (panelEl) panelEl.classList.toggle('readonly-mode', !editable);

        if (!hasNew && !hasExisting) {
            if (titleEl) titleEl.textContent = 'Comprobante de Devolución';
            if (descEl) descEl.textContent = editable
                ? 'Sube el comprobante (transferencia, ficha de depósito, etc.). PDF, máx. 10MB.'
                : 'Aún no se ha adjuntado el comprobante de devolución.';
            list.innerHTML = '';
            return;
        }

        if (titleEl) titleEl.textContent = 'Comprobante PDF adjuntado';
        if (descEl) descEl.textContent = editable
            ? 'Haz clic sobre el archivo para visualizarlo o cámbialo si es necesario.'
            : 'Haz clic sobre el archivo para visualizarlo en una ventana externa.';

        let html = '<div class="file-grid" style="margin-top: 1rem;">';

        if (hasExisting && !hasNew) {
            const name = existingLeftoverProofPath.split('/').pop();
            html += `
                <div class="file-card existing-file clickable" onclick="event.stopPropagation(); window.openLeftoverViewer();" title="Clic para visualizar">
                    <i class="bx bxs-file-pdf file-icon-lg"></i>
                    <div class="file-info">
                        <span class="file-name" title="${name}">${name}</span>
                        <span class="file-size" style="color:#16a34a; font-weight:600;">
                            <i class="bx bx-check-circle"></i> PDF adjuntado
                        </span>
                    </div>
                </div>`;
        }

        if (hasNew) {
            html += `
                <div class="file-card clickable" onclick="event.stopPropagation(); window.openLeftoverViewer();" title="Clic para visualizar">
                    <i class="bx bxs-file-pdf file-icon-lg"></i>
                    <div class="file-info">
                        <span class="file-name" title="${leftoverProofFile.name}">${leftoverProofFile.name}</span>
                        <span class="file-size">${window.formatBytes(leftoverProofFile.size)}</span>
                    </div>
                    ${editable ? `
                        <button type="button" class="btn-remove-file" onclick="event.stopPropagation(); window.removeLeftoverFile()" title="Quitar">
                            <i class="bx bx-x"></i>
                        </button>
                    ` : ''}
                </div>`;
        }

        html += '</div>';
        list.innerHTML = html;
    };

    window.renderExcessFile = function() {
        const list    = document.getElementById('excess-proof-list');
        const titleEl = document.getElementById('excess-ev-title');
        const descEl  = document.getElementById('excess-ev-desc');
        if (!list) return;

        if (!existingExcessProofPath) {
            if (titleEl) titleEl.textContent = 'Pendiente de Liquidación por Tesorería';
            if (descEl) descEl.textContent = 'Una vez aprobada la comprobación, Tesorería adjuntará aquí el comprobante del pago del excedente.';
            list.innerHTML = '';
            return;
        }

        const name = existingExcessProofPath.split('/').pop();
        if (titleEl) titleEl.textContent = 'Comprobante de Pago de Excedente';
        if (descEl) descEl.textContent = 'Haz clic sobre el comprobante emitido por Tesorería para visualizarlo.';

        list.innerHTML = `
            <div class="file-grid" style="margin-top: 1rem;">
                <div class="file-card existing-file clickable" onclick="event.stopPropagation(); window.openExcessViewer();" title="Clic para visualizar">
                    <i class="bx bxs-file-pdf file-icon-lg"></i>
                    <div class="file-info">
                        <span class="file-name" title="${name}">${name}</span>
                        <span class="file-size" style="color:#16a34a; font-weight:600;">
                            <i class="bx bx-check-circle"></i> Pagado por Tesorería
                        </span>
                    </div>
                </div>
            </div>`;
    };

    (function initLeftoverEvents() {
        const leftoverInput = document.getElementById('leftover-proof-upload');
        const leftoverPanel = document.getElementById('leftover-evidence-panel');
        if (!leftoverInput || !leftoverPanel) return;

        leftoverInput.addEventListener('change', function(e) {
            window.procesarArchivoLeftover(e.target.files[0]);
            this.value = '';
        });
        leftoverPanel.addEventListener('dragover', e => {
            if (!window.isLeftoverEditable) return;
            e.preventDefault();
            leftoverPanel.classList.add('dragover');
        });
        leftoverPanel.addEventListener('dragleave', e => {
            e.preventDefault();
            leftoverPanel.classList.remove('dragover');
        });
        leftoverPanel.addEventListener('drop', e => {
            e.preventDefault();
            leftoverPanel.classList.remove('dragover');
            if (!window.isLeftoverEditable) return;
            if (e.dataTransfer.files.length) window.procesarArchivoLeftover(e.dataTransfer.files[0]);
        });
    })();

    /* ==========================================================================
       ── 3. VISIBILIDAD DE CATEGORÍAS ──
       ========================================================================== */
    window.updateCategoryVisibility = function(isViewOnly) {
        ['cat-vuelos', 'cat-restaurantes', 'cat-combustible', 'cat-otros'].forEach(cat => {
            const tbody = document.getElementById(cat);
            if (!tbody) return;
            const rowCount = tbody.querySelectorAll('.data-row').length;
            if (isViewOnly && rowCount === 0) {
                tbody.style.display = 'none';
            } else {
                tbody.style.display = '';
            }
        });
    };

    /* ==========================================================================
       ── 4. CENTRO DE COSTOS ──
       ========================================================================== */
    window.buildCostCenterOptions = function(includeVarios = false, selectedVal = '') {
        let html = '<option value="" disabled ' + (!selectedVal ? 'selected' : '') + '>Seleccione imputación...</option>';
        if (includeVarios) {
            html += `<option value="VARIOS" ${selectedVal === 'VARIOS' ? 'selected' : ''}>Varios Centros de Costo</option>`;
        }
        costCentersData.forEach(cc => {
            const ccVal = `CC_${cc.id}`;
            html += `<option value="${ccVal}" ${selectedVal === ccVal ? 'selected' : ''}>▶${cc.code} | ${cc.name}</option>`;
            if (cc.projects && cc.projects.length > 0) {
                cc.projects.forEach(p => {
                    const pVal = `PRJ_${p.id}_${cc.id}`;
                    html += `<option value="${pVal}" ${selectedVal === pVal ? 'selected' : ''}>&nbsp;&nbsp;&nbsp;↳ ${p.code} | ${p.name}</option>`;
                });
            }
        });
        return html;
    };

    window.toggleVariosMode = function() {
        if (!mainCostCenterSelect) return;
        const isVarios = mainCostCenterSelect.value === 'VARIOS';
        const tableWrap = document.getElementById('matrix-table');
        const thGroup = document.getElementById('th-group-identificador');
        const catRowColspans = document.querySelectorAll('.cat-row-colspan');

        if (isVarios) {
            if(tableWrap) tableWrap.classList.add('cost-center-varios-mode');
            if(thGroup) thGroup.setAttribute('colspan', '4');
            catRowColspans.forEach(td => td.setAttribute('colspan', '11'));
        } else {
            if(tableWrap) tableWrap.classList.remove('cost-center-varios-mode');
            if(thGroup) thGroup.setAttribute('colspan', '3');
            catRowColspans.forEach(td => td.setAttribute('colspan', '10'));
            document.querySelectorAll('.c-cc-select').forEach(sel => sel.value = '');
        }
    };

    if (mainCostCenterSelect) {
        mainCostCenterSelect.innerHTML = window.buildCostCenterOptions(true);
        mainCostCenterSelect.addEventListener('change', window.toggleVariosMode);
    }

    /* ==========================================================================
       ── 5. NAVEGACIÓN Y ANIMACIÓN ──
       ========================================================================== */
    document.getElementById('reimbursement-modal').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const focusableElements = Array.from(this.querySelectorAll('.modal-focusable'));
            const currentIndex = focusableElements.indexOf(document.activeElement);
            if (currentIndex > -1 && currentIndex < focusableElements.length - 1) {
                focusableElements[currentIndex + 1].focus();
            }
        }
    });

    window.animateTableRows = function(tableSelector, delayPerRow = 0.04, initialDelay = 0) {
        const table = document.querySelector(tableSelector);
        if (!table || table.classList.contains('no-row-anim')) return;
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            const delay = initialDelay + index * delayPerRow;
            row.style.animationDelay = `${delay}s`;
            row.style.animation = 'none';
            row.offsetHeight;
            row.style.animation = '';
        });
    };

    function closeMotivePopover() {
        document.querySelectorAll('.motive-popover').forEach(p => p.remove());
    }

    document.addEventListener('click', function(e) {
        if (e.target.classList && e.target.classList.contains('motive-popover')) return;
        const motiveEl = e.target.closest('.row-motive');
        closeMotivePopover();
        if (!motiveEl) return;
        const fullText = motiveEl.getAttribute('data-full') || motiveEl.textContent.trim();
        if (!fullText) return;
        e.stopPropagation();

        const pop = document.createElement('div');
        pop.className = 'motive-popover';
        pop.textContent = fullText;
        document.body.appendChild(pop);

        const rect = motiveEl.getBoundingClientRect();
        const popRect = pop.getBoundingClientRect();

        let top = rect.bottom + 8;
        let left = rect.left;

        if (left + popRect.width > window.innerWidth - 12) left = window.innerWidth - popRect.width - 12;
        if (left < 12) left = 12;

        if (top + popRect.height > window.innerHeight - 12) {
            top = rect.top - popRect.height - 8;
            pop.style.setProperty('--arrow-left', (rect.left - left + 14) + 'px');
            pop.classList.add('motive-popover-top');
        } else {
            pop.style.setProperty('--arrow-left', (rect.left - left + 14) + 'px');
        }

        pop.style.top = top + 'px';
        pop.style.left = left + 'px';
    });

    window.addEventListener('scroll', closeMotivePopover, true);
    window.addEventListener('resize', closeMotivePopover);

    /* ==========================================================================
       ── 6. GESTIÓN DE ANTICIPOS Y BADGES DE CONCILIACIÓN ──
       ========================================================================== */
    window.getSettlementConfig = function(status) {
        const map = {
            'no_comprobado': { bg: 'var(--settle-no_comprobado-bg)', color: 'var(--settle-no_comprobado-text)', border: 'var(--settle-no_comprobado-border)', icon: 'bx-file-blank', label: 'Sin Comprobar' },
            'comprobando': { bg: 'var(--settle-comprobando-bg)', color: 'var(--settle-comprobando-text)', border: 'var(--settle-comprobando-border)', icon: 'bx-refresh', label: 'Comprobando' },
            'comprobado': { bg: 'var(--settle-comprobado-bg)', color: 'var(--settle-comprobado-text)', border: 'var(--settle-comprobado-border)', icon: 'bx-check-double', label: 'Comprobado' }
        };
        return map[status] || { bg: 'var(--bal-no_aplica-bg)', color: 'var(--bal-no_aplica-text)', border: 'var(--bal-no_aplica-border)', icon: 'bx-minus', label: status || 'N/A' };
    };

    window.getBalanceBadge = function(statusBalance) {
        const map = {
            'no_aplica': { bg: 'var(--bal-no_aplica-bg)', color: 'var(--bal-no_aplica-text)', border: 'var(--bal-no_aplica-border)', icon: 'bx-minus',         label: 'N/A' },
            'por_definir': { bg: 'var(--bal-por_definir-bg)', color: 'var(--bal-por_definir-text)', border: 'var(--bal-por_definir-border)', icon: 'bx-time',          label: 'Por Definir' },
            'devolucion_pendiente': { bg: 'var(--bal-devolucion_pendiente-bg)', color: 'var(--bal-devolucion_pendiente-text)', border: 'var(--bal-devolucion_pendiente-border)', icon: 'bx-upload',        label: 'Devolución Pendiente' },
            'por_verificar': { bg: 'var(--bal-por_verificar-bg)', color: 'var(--bal-por_verificar-text)', border: 'var(--bal-por_verificar-border)', icon: 'bx-search-alt',    label: 'Por Verificar' },
            'devuelto': { bg: 'var(--bal-devuelto-bg)', color: 'var(--bal-devuelto-text)', border: 'var(--bal-devuelto-border)', icon: 'bx-check-circle', label: 'Devuelto' },
            'pago_pendiente': { bg: 'var(--bal-pago_pendiente-bg)', color: 'var(--bal-pago_pendiente-text)', border: 'var(--bal-pago_pendiente-border)', icon: 'bx-wallet',        label: 'Pago Pendiente' },
            'pagado': { bg: 'var(--bal-pagado-bg)', color: 'var(--bal-pagado-text)', border: 'var(--bal-pagado-border)', icon: 'bx-check-double', label: 'Pagado' }
        };
        const st = map[statusBalance] || map['no_aplica'];
        return `<span class="status-badge" style="background:${st.bg}; color:${st.color}; border:1px solid ${st.border};"><i class="bx ${st.icon}"></i> ${st.label}</span>`;
    };

    let advanceFetchSeq = 0;

    window.handleTipoSolicitudChange = async function(preselectId = null, resetSelection = false) {
        const selectTipo    = document.getElementById('modal-tipo-solicitud');
        const selectAdvance = document.getElementById('modal-advance-id');
        const iconState     = document.getElementById('adv-icon-state');
        const lblAdvance    = document.getElementById('lbl-advance');
        const sumTotalLbl   = document.getElementById('sum-total-lbl');

        if (!selectTipo || !selectAdvance) return;

        if (typeof preselectId !== 'string' && typeof preselectId !== 'number') preselectId = null;

        const tipoVal = selectTipo.value;

        const setAdvanceUI = (message, iconClass, color, isDisabled = true) => {
            selectAdvance.innerHTML = `<option value="" disabled selected>${message}</option>`;
            selectAdvance.disabled = isDisabled;
            if (iconState) {
                iconState.className = `bx ${iconClass} field-icon`;
                iconState.style.color = color;
            }
            window.toggleAdvanceViewButton();
        };

        if (tipoVal === 'Reembolso') {
            lblAdvance.innerHTML = 'Anticipo a Comprobar';
            if (sumTotalLbl) sumTotalLbl.textContent = 'TOTAL A REEMBOLSAR';
        } else {
            lblAdvance.innerHTML = 'Anticipo a Comprobar <span class="req">*</span>';
            if (sumTotalLbl) sumTotalLbl.textContent = 'TOTAL COMPROBADO';
        }

        if (!tipoVal) {
            setAdvanceUI('Seleccione un tipo de solicitud...', 'bx-link', 'var(--secondary-lighter)');
            currentAdvanceAmount = 0;
            window.recalcLeftover();
        } else if (tipoVal === 'Reembolso') {
            setAdvanceUI('No aplica (Trámite Independiente)', 'bx-block', '#94a3b8');
            currentAdvanceAmount = 0;
            window.recalcLeftover();
        } else {
            advanceFetchSeq++;
            setAdvanceUI('Buscando anticipos...', 'bx-loader-alt bx-spin', 'var(--primary-dark)');

            const currentUserId = document.getElementById('modal-beneficiary-id').value;
            const isManualChange = !preselectId;
            const prev = (resetSelection || isManualChange) ? null : preselectId;

            await window.fetchUserAdvances(currentUserId, prev);
        }
    };

    window.fetchUserAdvances = async function(userId, selectedAdvanceId = null) {
        const select = document.getElementById('modal-advance-id');
        const iconState = document.getElementById('adv-icon-state');
        const tipoSolicitud = document.getElementById('modal-tipo-solicitud').value;

        if (!select) return;

        const seq = ++advanceFetchSeq;
        let prevLabel = null;

        if (selectedAdvanceId) {
            const prevOpt = Array.from(select.options).find(o => String(o.value) === String(selectedAdvanceId));
            if (prevOpt) prevLabel = prevOpt.textContent;
        }

        try {
            const fetchUrl = `{{ url('administration/expense-claims/advances/user') }}/${userId}`;
            const response = await fetch(fetchUrl);
            const data = await response.json();

            if (seq !== advanceFetchSeq) return;

            select.innerHTML = '';

            let filteredData = data || [];

            if (filteredData.length > 0) {
                if (tipoSolicitud === 'Comprobacion Directa') {
                    filteredData = filteredData.filter(adv => adv.advance_type === 'Transferencia');
                } else if (tipoSolicitud === 'Comprobacion Electronica') {
                    filteredData = filteredData.filter(adv => adv.advance_type === 'Tarjeta Toka');
                }
            }

            if (!filteredData || filteredData.length === 0) {
                select.innerHTML = '<option value="" disabled selected>No hay anticipos compatibles con esta solicitud</option>';
                select.disabled = true;
                if (iconState) {
                    iconState.className = 'bx bx-x-circle field-icon';
                    iconState.style.color = '#ef4444';
                }
            } else {
                select.innerHTML = '<option value="" disabled selected>Seleccione un anticipo...</option>';
                filteredData.forEach(adv => {
                    const opt = document.createElement('option');
                    opt.value = adv.id;
                    const tipoText = adv.advance_type ? ` - ${adv.advance_type}` : '';
                    opt.textContent = `${adv.folio_system}${tipoText}`;
                    select.appendChild(opt);

                    if (adv.amount != null) {
                        window.advanceAmountsMap[adv.id] = parseFloat(adv.amount) || 0;
                    }
                });

                select.disabled = !window.isFormEditable;
                if (iconState) {
                    iconState.className = 'bx bx-link field-icon';
                    iconState.style.color = 'var(--secondary-dark)';
                }
                if (selectedAdvanceId) {
                    const exists = Array.from(select.options).some(o => String(o.value) === String(selectedAdvanceId));
                    if (!exists && prevLabel) {
                        const opt = document.createElement('option');
                        opt.value = selectedAdvanceId;
                        opt.textContent = prevLabel;
                        select.appendChild(opt);
                    }
                    select.value = selectedAdvanceId;
                    await window.onAdvanceSelected();
                }
            }
            window.toggleAdvanceViewButton();
        } catch (error) {
            console.error("Error cargando anticipos:", error);
            select.innerHTML = '<option value="" disabled selected>Error al cargar. Intente de nuevo.</option>';
            select.disabled = true;
            if (iconState) {
                iconState.className = 'bx bx-error-circle field-icon';
                iconState.style.color = '#ef4444';
            }
            window.toggleAdvanceViewButton();
        }
    };

    window.toggleAdvanceViewButton = function() {
        const select = document.getElementById('modal-advance-id');
        const viewBtn = document.getElementById('btn-view-advance');
        const group = document.getElementById('advance-select-group');
        if (!select || !viewBtn) return;
        const hasValue = !!(select.value && select.value !== '');
        viewBtn.classList.toggle('hidden', !hasValue);
        if (group) group.classList.toggle('has-view', hasValue);
    };

    window.openAdvanceModalForCreate = function() {
        document.getElementById('adv-modal-title').innerHTML = '<i class="bx bx-money-withdraw"></i> Solicitud de <strong>Anticipo</strong>';
        document.getElementById('adv-modal-folio').textContent = 'Asignación Automática';
        document.getElementById('adv-modal-status').innerHTML = window.getAdvStatusBadge('pendiente');

        const settleSt = window.getSettlementConfig('no_comprobado');
        const stHtml = `<span class="status-badge" style="background:${settleSt.bg}; color:${settleSt.color}; border: 1px solid ${settleSt.border};"><i class="bx ${settleSt.icon}"></i> ${settleSt.label}</span>`;
        document.getElementById('adv-modal-settlement').innerHTML = stHtml;

        document.getElementById('adv-user-name').value = sessionUser.nombre;
        document.getElementById('adv-beneficiary-id').value = sessionUser.id;
        document.getElementById('adv-date-text').value = todayText;

        document.getElementById('adv-reason').value = '';
        document.getElementById('adv-type').value = '';
        document.getElementById('adv-approver').value = '';
        document.getElementById('adv-amount').value = '';
        document.getElementById('adv-desc').value = '';

        document.querySelectorAll('.adv-focusable').forEach(el => el.removeAttribute('disabled'));
        document.getElementById('adv-user-name').removeAttribute('disabled');
        document.querySelectorAll('input[name="adv_is_delegated"]').forEach(el => el.disabled = false);

        document.querySelector('input[name="adv_is_delegated"][value="0"]').checked = true;
        window.handleAdvDelegationToggle();

        document.getElementById('adv-footer-create').classList.remove('hidden');
        document.getElementById('adv-footer-view').classList.add('hidden');
        document.getElementById('advance-modal').classList.remove('hidden');
    };

    window.verDetalleAnticipo = async function(advanceId) {
        if (!advanceId) return;
        Swal.fire({ title: 'Cargando Detalles...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        try {
            const fetchUrl = `{{ url('administration/expense-claims/advances') }}/${advanceId}`;
            const response = await fetch(fetchUrl);
            const res = await response.json();

            if (res.success) {
                const adv = res.data;
                document.getElementById('adv-modal-title').innerHTML = `<i class="bx bx-search-alt"></i> Inspección de <strong>Anticipo</strong>`;
                document.getElementById('adv-modal-folio').textContent = adv.folio_system;
                document.getElementById('adv-modal-status').innerHTML = window.getAdvStatusBadge(adv.status || 'pendiente');

                const settleSt = window.getSettlementConfig(adv.settlement_status || 'no_comprobado');
                const stHtml = `<span class="status-badge" style="background:${settleSt.bg}; color:${settleSt.color}; border: 1px solid ${settleSt.border};"><i class="bx ${settleSt.icon}"></i> ${settleSt.label}</span>`;
                document.getElementById('adv-modal-settlement').innerHTML = stHtml;

                document.getElementById('adv-user-name').value = adv.user.name;
                document.getElementById('adv-date-text').value = new Date(adv.advance_date).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });

                document.getElementById('adv-reason').value = adv.advance_reason || 'Viaticos';
                document.getElementById('adv-type').value = adv.advance_type || 'Transferencia';
                document.getElementById('adv-approver').value = adv.approver_id || '';
                document.getElementById('adv-amount').value = adv.amount;
                document.getElementById('adv-desc').value = adv.description;

                if (adv.user_id !== sessionUser.id) {
                    document.querySelector('input[name="adv_is_delegated"][value="1"]').checked = true;
                } else {
                    document.querySelector('input[name="adv_is_delegated"][value="0"]').checked = true;
                }
                window.handleAdvDelegationToggle();

                document.querySelectorAll('.adv-focusable').forEach(el => el.setAttribute('disabled', 'true'));
                document.getElementById('adv-user-name').setAttribute('disabled', 'true');
                document.querySelectorAll('input[name="adv_is_delegated"]').forEach(el => el.disabled = true);

                document.getElementById('adv-footer-create').classList.add('hidden');
                document.getElementById('adv-footer-view').classList.remove('hidden');
                Swal.close();
                document.getElementById('advance-modal').classList.remove('hidden');
            } else {
                window.showAlert('No se encontró el anticipo solicitado.', 'error');
            }
        } catch (error) {
            window.showAlert('Hubo un problema al cargar los datos.', 'error');
        }
    };

    window.closeAdvanceModal = function() {
        document.getElementById('advance-modal').classList.add('hidden');
    };

    window.submitAdvance = function() {
        const reason = document.getElementById('adv-reason').value;
        const type = document.getElementById('adv-type').value;
        const fecha = document.getElementById('adv-date-text').value;
        const monto = document.getElementById('adv-amount').value;
        const approverSelect = document.getElementById('adv-approver');
        const approverId = approverSelect.value;
        const desc = document.getElementById('adv-desc').value.trim();
        const advBeneficiaryIdLocal = document.getElementById('adv-beneficiary-id').value;
        const advBeneficiaryName = document.getElementById('adv-user-name').value;

        if (!reason || !type || !monto || !approverId || !desc) {
            window.showAlert('Por favor, complete todos los campos requeridos.', 'warning');
            return;
        }

        const approverName = approverSelect.options[approverSelect.selectedIndex].text;

        Swal.fire({
            title: `<span style="font-family:'Poppins', sans-serif;">¿Confirmar Solicitud?</span>`,
            html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">Vas a solicitar un anticipo por <strong>${window.fmt(monto)}</strong> a nombre de <strong>${advBeneficiaryName}</strong>.<br><br>Esta solicitud será enviada a <strong>${approverName}</strong> para su autorización.</span>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1d4ed8',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: `Sí, enviar solicitud`,
            cancelButtonText: `Revisar de nuevo`
        }).then(async (result) => {
            if (result.isConfirmed) {
                let formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('advance_reason', reason);
                formData.append('advance_type', type);
                formData.append('advance_date', fecha);
                formData.append('amount', monto);
                formData.append('approver_id', approverId);
                formData.append('beneficiary_id', advBeneficiaryIdLocal);
                formData.append('description', desc);

                try {
                    Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    const response = await fetch('{{ route('expense-claims.advances.store') }}', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await response.json();

                    if (data.success) {
                        window.closeAdvanceModal();
                        await Swal.fire({
                            title: '¡Anticipo Solicitado!',
                            text: data.message + ' Folio Asignado: ' + data.folio,
                            icon: 'success',
                            confirmButtonColor: '#2d7d46'
                        });
                        window.handleTipoSolicitudChange();
                        await window.refreshTableData();
                    } else {
                        window.showAlert(data.message, 'error');
                    }
                } catch (error) {
                    window.showAlert('No se pudo enviar la solicitud. Verifique su conexión.', 'error');
                }
            }
        });
    };

    window.openAdvanceFromSelect = function() {
        const selectId = document.getElementById('modal-advance-id').value;
        if (selectId) window.verDetalleAnticipo(selectId);
    };

    /* ==========================================================================
       ── 7. BUSCADOR DE COLABORADORES ──
       ========================================================================== */
    if (modalNombre) {
        modalNombre.addEventListener('input', function() {
            if (modalNombre.hasAttribute('readonly') || modalNombre.disabled) return;
            const query = this.value.toLowerCase().trim();

            if (query.length < 2) {
                dropdown.innerHTML = '';
                dropdown.classList.add('hidden');
                return;
            }
            dropdown.innerHTML = '<div class="reimburse-dropdown-searching"><i class="bx bx-loader-alt bx-spin"></i> Buscando empleado...</div>';
            dropdown.classList.remove('hidden');

            const results = companyEmployees.filter(emp => emp.nombre.toLowerCase().includes(query));

            setTimeout(() => {
                dropdown.innerHTML = '';
                if (results.length > 0) {
                    results.forEach(emp => {
                        const item = document.createElement('div');
                        item.className = 'reimburse-dropdown-item';
                        item.innerHTML = `<strong>${emp.nombre}</strong><small>${emp.depto}</small>`;
                        item.onclick = () => window.selectEmployee(emp);
                        dropdown.appendChild(item);
                    });
                } else {
                    dropdown.innerHTML = '<div class="reimburse-dropdown-empty"><i class="bx bx-search-alt"></i> No se encontró al empleado</div>';
                }
            }, 180);
        });
    }

    const advNombre = document.getElementById('adv-user-name');
    const advDropdown = document.getElementById('adv-employee-dropdown');
    const advBeneficiaryId = document.getElementById('adv-beneficiary-id');

    if (advNombre) {
        advNombre.addEventListener('input', function() {
            if (advNombre.hasAttribute('readonly') || advNombre.disabled) return;
            const query = this.value.toLowerCase().trim();

            if (query.length < 2) {
                advDropdown.innerHTML = '';
                advDropdown.classList.add('hidden');
                return;
            }
            advDropdown.innerHTML = '<div class="reimburse-dropdown-searching"><i class="bx bx-loader-alt bx-spin"></i> Buscando...</div>';
            advDropdown.classList.remove('hidden');

            const results = companyEmployees.filter(emp => emp.nombre.toLowerCase().includes(query));

            setTimeout(() => {
                advDropdown.innerHTML = '';
                if (results.length > 0) {
                    results.forEach(emp => {
                        const item = document.createElement('div');
                        item.className = 'reimburse-dropdown-item';
                        item.innerHTML = `<strong>${emp.nombre}</strong><small>${emp.depto}</small>`;
                        item.onclick = () => {
                            advNombre.value = emp.nombre;
                            advBeneficiaryId.value = emp.id;
                            advDropdown.classList.add('hidden');
                        };
                        advDropdown.appendChild(item);
                    });
                } else {
                    advDropdown.innerHTML = '<div class="reimburse-dropdown-empty"><i class="bx bx-search-alt"></i> No encontrado</div>';
                }
            }, 180);
        });
    }

    window.selectEmployee = function(emp) {
        modalNombre.value = emp.nombre;
        modalDepto.value = emp.depto;
        beneficiaryId.value = emp.id;
        dropdown.classList.add('hidden');
        window.handleTipoSolicitudChange(null, true);
        window.showAlert(`El beneficiario del trámite ahora es: <strong>${emp.nombre}</strong>`, 'info');
    };

    document.addEventListener('click', function(e) {
        if (modalNombre && dropdown && !modalNombre.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
        if (advNombre && advDropdown && !advNombre.contains(e.target) && !advDropdown.contains(e.target)) {
            advDropdown.classList.add('hidden');
        }
    });

    window.handleDelegationToggle = function() {
        const isDelegated = document.querySelector('input[name="is_delegated"]:checked').value === "1";

        iconSolicitante.className = 'bx bx-user field-icon';
        iconSolicitante.style.color = '#94a3b8';

        if (isDelegated) {
            modalNombre.removeAttribute('readonly');
            modalNombre.value = '';
            modalNombre.focus();
            modalNombre.placeholder = 'Buscar empleado por nombre o apellido...';
            modalNombre.classList.add('reimburse-input-active-search');
        } else {
            modalNombre.setAttribute('readonly', 'true');
            modalNombre.value = sessionUser.nombre;
            modalDepto.value = sessionUser.depto;
            beneficiaryId.value = sessionUser.id;
            modalNombre.classList.remove('reimburse-input-active-search');
            dropdown.classList.add('hidden');
            window.handleTipoSolicitudChange(null, true);
        }
    };

    window.handleAdvDelegationToggle = function() {
        const isDelegated = document.querySelector('input[name="adv_is_delegated"]:checked').value === "1";
        if (isDelegated) {
            advNombre.removeAttribute('readonly');
            advNombre.removeAttribute('disabled');
            advNombre.value = '';
            advNombre.focus();
            advNombre.placeholder = 'Buscar empleado por nombre...';
            advNombre.classList.add('reimburse-input-active-search');
        } else {
            advNombre.setAttribute('readonly', 'true');
            advNombre.value = sessionUser.nombre;
            advBeneficiaryId.value = sessionUser.id;
            advNombre.classList.remove('reimburse-input-active-search');
            advDropdown.classList.add('hidden');
        }
    };

    /* ==========================================================================
       ── 8. TABLA MAESTRA (SIN RECORTAR TEXTO + PERMISOS DIRECTOS) ──
       ========================================================================== */
    let requests = {!! isset($requestsData) ? json_encode($requestsData) : '[]' !!};
    let currentEvaluateId = null;
    let currentPage = 1;
    let itemsPerPage = 10;
    let activeFilter = 'all';
    let searchQuery = '';

    const tableSearchInput = document.getElementById('table-search');
    if(tableSearchInput) {
        tableSearchInput.addEventListener('input', function() {
            searchQuery = this.value.toLowerCase().trim();
            currentPage = 1;
            window.renderDashboard(true);
        });
    }

    document.querySelectorAll('.filter-tab').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            activeFilter = this.dataset.filter;
            currentPage = 1;
            window.renderDashboard(true);
        });
    });

    window.changePageSize = function() {
        const val = document.getElementById('page-size-select').value;
        itemsPerPage = val === 'all' ? 999999 : parseInt(val);
        currentPage = 1;
        window.renderDashboard(true);
    };

    window.renderDashboard = function(skipRowAnimation = false) {
        const list = document.getElementById('dashboard-list');
        const tableEl = document.getElementById('main-data-table');
        const emptyState = document.getElementById('empty-state');
        const tableCount = document.getElementById('table-count');
        if(!list) return;

        if (tableEl) {
            tableEl.classList.toggle('no-row-anim', !!skipRowAnimation);
        }

        list.innerHTML = '';

        let pendCount = 0, valCount = 0, appCount = 0, rejCount = 0;

        requests.forEach(req => {
            if (req.status === 'rechazado' || req.aprobacion === 'rechazado' || req.aprobacion === 'no_procede') rejCount++;
            else if (req.aprobacion === 'aprobado') appCount++;
            else if (req.status === 'validado') valCount++;
            else if (req.status === 'pendiente') pendCount++;
        });

        if(document.getElementById('metric-pending-val')) document.getElementById('metric-pending-val').textContent = pendCount;
        if(document.getElementById('metric-validated-val')) document.getElementById('metric-validated-val').textContent = valCount;
        if(document.getElementById('metric-approved-val')) document.getElementById('metric-approved-val').textContent = appCount;
        if(document.getElementById('metric-rejected-val')) document.getElementById('metric-rejected-val').textContent = rejCount;

        let filtered = requests.filter(req => {
            let matchFilter = false;
            if (activeFilter === 'all') matchFilter = true;
            else if (activeFilter === 'aprobado') matchFilter = (req.aprobacion === 'aprobado');
            else matchFilter = (req.status === activeFilter);

            const matchSearch = !searchQuery ||
                                (req.motivo || '').toLowerCase().includes(searchQuery) ||
                                (req.folioP || '').toLowerCase().includes(searchQuery) ||
                                (req.folioU || '').toLowerCase().includes(searchQuery) ||
                                (req.nombre || '').toLowerCase().includes(searchQuery) ||
                                (req.depto || '').toLowerCase().includes(searchQuery);
            return matchFilter && matchSearch;
        });

        if(tableCount) tableCount.textContent = `${filtered.length} solicitud(es) registrada(s)`;

        if (filtered.length === 0) {
            if(emptyState) emptyState.classList.remove('hidden');
            document.getElementById('pagination-controls').innerHTML = '';
            return;
        }
        if(emptyState) emptyState.classList.add('hidden');

        const totalPages = Math.ceil(filtered.length / itemsPerPage);
        if (currentPage > totalPages) currentPage = totalPages;

        const startIdx = (currentPage - 1) * itemsPerPage;
        const paginatedData = filtered.slice(startIdx, startIdx + itemsPerPage);

        paginatedData.forEach((req) => {
            let badge = '', badgeAprobacion = '', advanceBadge = '', balanceBadge = '';

            if (req.status === 'validado') badge = `<span class="status-badge badge-rev-validado"><i class="bx bx-list-check"></i> Validado</span>`;
            else if (req.status === 'rechazado') badge = `<span class="status-badge badge-rev-rechazado"><i class="bx bx-x-circle"></i> Rechazado</span>`;
            else if (req.status === 'borrador') badge = `<span class="status-badge badge-rev-borrador"><i class="bx bx-edit-alt"></i> Borrador</span>`;
            else badge = `<span class="status-badge badge-rev-pendiente"><i class="bx bx-hourglass"></i> Pendiente</span>`;

            if (req.aprobacion === 'aprobado') badgeAprobacion = `<span class="status-badge badge-est-aprobado"><i class="bx bx-check-double"></i> Aprobado</span>`;
            else if (req.aprobacion === 'en_espera' || req.aprobacion === 'por_comprobar') badgeAprobacion = `<span class="status-badge badge-est-en_espera"><i class="bx bx-time-five"></i> En espera</span>`;
            else if (req.aprobacion === 'en_revision') badgeAprobacion = `<span class="status-badge badge-est-en_revision"><i class="bx bx-search"></i> En revisión</span>`;
            else if (req.aprobacion === 'rechazado' || req.aprobacion === 'no_procede') badgeAprobacion = `<span class="status-badge badge-est-rechazado"><i class="bx bx-block"></i> Rechazado</span>`;
            else badgeAprobacion = `<span class="status-badge badge-est-n_a"><i class="bx bx-minus"></i> ${req.aprobacion === 'n_a' ? 'N/A' : req.aprobacion}</span>`;

            balanceBadge = window.getBalanceBadge(req.status_balance || 'no_aplica');

            if (req.advance_folio && req.advance_id) {
                let advClass = '';
                if (req.advance_status === 'pendiente') advClass = 'advance-folio-pendiente';
                else if (req.advance_status === 'aprobado' || req.advance_status === 'comprobado') advClass = 'advance-folio-aprobado';
                else if (req.advance_status === 'rechazado') advClass = 'advance-folio-rechazado';

                advanceBadge = `<span class="row-folio advance-folio ${advClass}" onclick="window.verDetalleAnticipo(${req.advance_id})" title="Clic para revisar detalles"><i class="bx bx-link"></i> ${req.advance_folio}</span>`;
            } else {
                advanceBadge = `<span class="row-text-empty">Independiente</span>`;
            }

            // ★ Evaluación estricta según permisos directos (validar_solicitud / aprobar_solicitud) ★
            const canEvaluate = window.canUserValidateClaim(req) || window.canUserApproveClaim(req);
            const evaluateBtn = canEvaluate ?
                `<button class="btn-icon btn-icon-evaluate" onclick="window.evaluarSolicitud(${req.id})" title="Gestionar Resolución (Dictamen)"><i class="bx bx-clipboard"></i></button>` : '';

            const canUploadLeftover = (req.aprobacion === 'aprobado' && req.status_balance === 'devolucion_pendiente');
            const leftoverBtn = canUploadLeftover ?
                `<button class="btn-icon" style="background:#fffbeb; color:#d97706; border:1px solid #fde68a;" onclick="window.abrirModalSubirSobrante(${req.id})" title="Subir Comprobante de Devolución de Sobrante"><i class="bx bx-upload"></i></button>` : '';

            // ★ Se muestra el Nombre y el Motivo completos sin truncar ★
            const fullName = req.nombre || '';
            const fullMotive = req.motivo || '';
            const fullMotiveAttr = fullMotive.replace(/"/g, '&quot;').replace(/'/g, '&#39;');

            let viewBtnClass = 'btn-icon-view';
            let viewBtnIcon = 'bx-show';
            let viewBtnTitle = 'Inspeccionar Documento';

            const isOwnRequest = req.is_own !== undefined ? req.is_own : (Number(req.user_id) === Number(sessionUser.id));

            if (req.status === 'borrador' && isOwnRequest) {
                viewBtnClass = 'btn-icon-draft';
                viewBtnIcon = 'bx-edit-alt';
                viewBtnTitle = 'Continuar Borrador';
            } else if ((req.status === 'rechazado' || req.aprobacion === 'rechazado' || req.aprobacion === 'no_procede') && isOwnRequest) {
                viewBtnClass = 'btn-icon-correction';
                viewBtnIcon = 'bx-edit';
                viewBtnTitle = 'Corregir Solicitud Rechazada';
            }

            list.innerHTML += `
            <tr>
                <td><span class="row-folio">${req.folioP}</span></td>
                <td><span class="row-folio user-folio">${req.folioU}</span></td>
                <td><span class="row-date">${req.fecha}</span></td>
                <td><span class="row-name">${fullName}</span></td>
                <td><span class="row-depto">${req.depto}</span></td>
                <td><span class="row-depto">${({ 'Comprobacion Directa': 'Comp. Directa', 'Comprobación Directa': 'Comp. Directa', 'Comprobacion Electronica': 'Comp. Electrónica', 'Comprobación Electrónica': 'Comp. Electrónica' })[req.tipo] || req.tipo || 'Trámite'}</span></td>
                <td><span class="row-motive" data-full="${fullMotiveAttr}">${fullMotive}</span></td>
                <td><div class="row-amount-wrap"><span class="row-amount">${window.fmt(req.amount)}</span></div></td>
                <td>${advanceBadge}</td>
                <td>${badge}</td>
                <td>${badgeAprobacion}</td>
                <td>${balanceBadge}</td>
                <td class="cell-actions">
                    <div class="actions-wrap">
                        <button class="btn-icon ${viewBtnClass}" onclick="window.verDetalles(${req.id})" title="${viewBtnTitle}"><i class="bx ${viewBtnIcon}"></i></button>
                        ${leftoverBtn}
                        ${evaluateBtn}
                        <button class="btn-icon btn-icon-history" onclick="window.verHistorial(${req.id})" title="Ver Historial de Movimientos"><i class="bx bx-history"></i></button>
                    </div>
                </td>
            </tr>`;
        });

        window.renderPagination(totalPages);
        if (!skipRowAnimation) {
            window.animateTableRows('#main-data-table');
        }
    };

    window.renderPagination = function(totalPages) {
        const container = document.getElementById('pagination-controls');
        if (!container) return;
        container.innerHTML = '';
        if (totalPages <= 1) return;

        const btnPrev = document.createElement('button');
        btnPrev.className = 'page-btn';
        btnPrev.innerHTML = '<i class="bx bx-chevron-left"></i>';
        btnPrev.disabled = currentPage === 1;
        btnPrev.onclick = () => { currentPage--; window.renderDashboard(true); };
        container.appendChild(btnPrev);

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.className = `page-btn ${i === currentPage ? 'active' : ''}`;
            btn.textContent = i;
            btn.onclick = () => { currentPage = i; window.renderDashboard(true); };
            container.appendChild(btn);
        }

        const btnNext = document.createElement('button');
        btnNext.className = 'page-btn';
        btnNext.innerHTML = '<i class="bx bx-chevron-right"></i>';
        btnNext.disabled = currentPage === totalPages;
        btnNext.onclick = () => { currentPage++; window.renderDashboard(true); };
        container.appendChild(btnNext);
    };

    /* ==========================================================================
       ── 9. PANEL SAT ──
       ========================================================================== */
    window.toggleSatPanel = function() {
        document.getElementById('sat-panel').classList.toggle('hidden');
    };

    document.querySelectorAll('.sat-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.sat-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.sat-content').forEach(c => c.classList.add('hidden'));
            this.classList.add('active');
            document.getElementById(this.dataset.target).classList.remove('hidden');
        });
    });

    const searchUuidInputSat = document.getElementById('search-uuid');
    const uuidDropdownSat = document.getElementById('uuid-dropdown');
    const btnClearUuid = document.getElementById('btn-clear-uuid');
    let satSearchTimeoutSat = null;

    window.limpiarBuscadorSat = function() {
        searchUuidInputSat.value = '';
        searchUuidInputSat.focus();
        uuidDropdownSat.innerHTML = '';
        uuidDropdownSat.classList.add('hidden');
        btnClearUuid.classList.add('hidden');
    };

    if (searchUuidInputSat) {
        searchUuidInputSat.addEventListener('input', function() {
            const query = this.value.trim();

            if (query.length > 0) btnClearUuid.classList.remove('hidden');
            else btnClearUuid.classList.add('hidden');

            if (query.length < 3) {
                uuidDropdownSat.innerHTML = '';
                uuidDropdownSat.classList.add('hidden');
                return;
            }

            uuidDropdownSat.innerHTML = `<div class="sat-dropdown-state loading"><i class="bx bx-loader-alt bx-spin"></i><span>Conectando a la bóveda fiscal...</span></div>`;
            uuidDropdownSat.classList.remove('hidden');

            clearTimeout(satSearchTimeoutSat);
            satSearchTimeoutSat = setTimeout(async () => {
                try {
                    const response = await fetch(`{{ route('expense-claims.cfdi.autocomplete') }}?term=${encodeURIComponent(query)}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await response.json();
                    uuidDropdownSat.innerHTML = '';

                    if (response.ok && data.success && data.data) {
                        let results = Array.isArray(data.data) ? data.data : [data.data];

                        if (results.length === 0) {
                            uuidDropdownSat.innerHTML = `<div class="sat-dropdown-state"><i class="bx bx-folder-open"></i><span>No se encontraron facturas con esa serie o UUID.</span></div>`;
                            return;
                        }

                        results.forEach(cfdi => {
                            let serieFolio = '';
                            if (cfdi.serie) serieFolio += cfdi.serie + '-';
                            if (cfdi.folio) serieFolio += cfdi.folio;
                            if (!serieFolio) serieFolio = 'S/F';

                            const monto = cfdi.total ? cfdi.total : (cfdi.subtotal || 0);
                            const fechaLimpia = cfdi.issue_date ? cfdi.issue_date.split('T')[0] : 'Sin fecha';

                            const item = document.createElement('div');
                            item.className = 'sat-dropdown-item';
                            item.onclick = function() { window.selectCfdiFromDropdown(cfdi); };

                            item.innerHTML = `
                                <div class="item-icon-wrap"><i class="bx bx-receipt"></i></div>
                                <div class="item-main-info">
                                    <div class="item-uuid-row">
                                        <span class="item-uuid">${cfdi.uuid}</span>
                                        <span class="item-badge">${serieFolio}</span>
                                    </div>
                                    <span class="item-desc" title="${cfdi.concept_summary}">${cfdi.concept_summary || 'Factura sin descripción detallada'}</span>
                                </div>
                                <div class="item-meta">
                                    <span class="item-amount">${window.fmt(monto)}</span>
                                    <span class="item-date">${fechaLimpia}</span>
                                </div>`;
                            uuidDropdownSat.appendChild(item);
                        });
                    } else {
                        uuidDropdownSat.innerHTML = `<div class="sat-dropdown-state"><i class="bx bx-search-alt"></i><span>No se encontraron resultados en el servidor.</span></div>`;
                    }
                } catch (error) {
                    uuidDropdownSat.innerHTML = `<div class="sat-dropdown-state"><i class="bx bx-error" style="color: #ef4444;"></i><span>Error al intentar conectar con el servidor interno.</span></div>`;
                }
            }, 500);
        });

        document.addEventListener('click', function(e) {
            if (!searchUuidInputSat.contains(e.target) && !uuidDropdownSat.contains(e.target) && !btnClearUuid.contains(e.target)) {
                uuidDropdownSat.classList.add('hidden');
            }
        });
    }

    window.selectCfdiFromDropdown = function(cfdi) {
        searchUuidInputSat.value = cfdi.uuid;
        btnClearUuid.classList.remove('hidden');
        uuidDropdownSat.classList.add('hidden');
        window.procesarCfdiSeleccionado(cfdi);
    };

    window.buscarFacturaManual = async function() {
        const uuid = searchUuidInputSat.value.trim();
        const btnB = document.getElementById('btn-buscar');
        if (uuid.length !== 36) { window.showAlert('El UUID ingresado debe tener exactamente 36 caracteres.', 'warning'); return; }

        const originalText = btnB.innerHTML;
        btnB.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i>';
        btnB.disabled = true;

        try {
            const response = await fetch(`{{ route('expense-claims.cfdi.search') }}?uuid=${uuid}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            if (response.ok && data.success) {
                let cfdi = Array.isArray(data.data) ? data.data[0] : data.data;
                window.procesarCfdiSeleccionado(cfdi);
            } else {
                window.showAlert(data.message || 'No se encontró el comprobante en la base de datos.', 'warning');
            }
        } catch (error) {
            window.showAlert('No se pudo establecer conexión con el servidor. Revisa tu internet.', 'error');
        } finally {
            btnB.innerHTML = originalText;
            btnB.disabled = false;
        }
    };

    window.procesarCfdiSeleccionado = function(cfdi) {
        if (!cfdi) { window.showAlert('Los datos de esta factura no están completos o están dañados.', 'error'); return; }
        let serieFolio = '';
        if (cfdi.serie) serieFolio += cfdi.serie + '-';
        if (cfdi.folio) serieFolio += cfdi.folio;
        if (!serieFolio && cfdi.uuid) serieFolio = cfdi.uuid.substring(0, 8);
        let fechaLimpia = cfdi.issue_date ? cfdi.issue_date.split('T')[0].split(' ')[0] : '';

        tempSatData = {
            id: cfdi.id,
            load_method: 'sat_uuid',
            fecha_iso: fechaLimpia,
            folio: serieFolio,
            desc: cfdi.concept_summary || 'Extracción y vinculación de factura',
            sub: parseFloat(cfdi.subtotal) || 0,
            iva: parseFloat(cfdi.tax_iva) || 0,
            ish: (parseFloat(cfdi.tax_ish) || 0) - (parseFloat(cfdi.tax_retenciones) || 0)
        };

        document.getElementById('sat-result-uuid').textContent = cfdi.uuid;
        document.getElementById('sat-result-container').classList.remove('hidden');

        if (searchUuidInputSat) {
            searchUuidInputSat.value = '';
            btnClearUuid.classList.add('hidden');
        }

        document.getElementById('sat-result-container').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    };

    const dropZoneUI = document.getElementById('drop-zone');
    const xmlInput = document.getElementById('xml-input');
    if (dropZoneUI) {
        dropZoneUI.addEventListener('click', () => xmlInput.click());
        xmlInput.addEventListener('change', e => window.leerXML(e.target.files[0]));
        dropZoneUI.addEventListener('dragover', e => { e.preventDefault(); dropZoneUI.classList.add('dragover'); });
        dropZoneUI.addEventListener('dragleave', e => { e.preventDefault(); dropZoneUI.classList.remove('dragover'); });
        dropZoneUI.addEventListener('drop', e => {
            e.preventDefault();
            dropZoneUI.classList.remove('dragover');
            if (e.dataTransfer.files.length) window.leerXML(e.dataTransfer.files[0]);
        });
    }

    window.leerXML = async function(file) {
        if (!file || file.type !== 'text/xml') { window.showAlert('Por favor, sube un archivo .XML válido.', 'error'); return; }
        Swal.fire({ title: 'Procesando factura...', text: 'Validando datos con el SAT.', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        let formData = new FormData();
        formData.append('xml_file', file);
        formData.append('_token', '{{ csrf_token() }}');

        try {
            const response = await fetch('{{ route('expense-claims.cfdi.upload') }}', {
                method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();

            if (response.ok && data.success) {
                Swal.close();
                const cfdi = data.data;
                let serieFolio = '';
                if (cfdi.serie) serieFolio += cfdi.serie + '-';
                if (cfdi.folio) serieFolio += cfdi.folio;
                if (!serieFolio) serieFolio = cfdi.uuid.substring(0, 8);

                let fechaLimpia = cfdi.issue_date ? cfdi.issue_date.split('T')[0].split(' ')[0] : '';

                tempSatData = {
                    id: cfdi.id,
                    load_method: 'sat_xml',
                    fecha_iso: fechaLimpia,
                    folio: serieFolio,
                    desc: cfdi.concept_summary || 'Factura extraída (XML)',
                    sub: parseFloat(cfdi.subtotal) || 0,
                    iva: parseFloat(cfdi.tax_iva) || 0,
                    ish: (parseFloat(cfdi.tax_ish) || 0) - (parseFloat(cfdi.tax_retenciones) || 0)
                };

                document.getElementById('sat-result-uuid').textContent = cfdi.uuid;
                document.getElementById('sat-result-container').classList.remove('hidden');
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 3000 });
            } else {
                window.showAlert(data.message || 'El XML subido no es válido o está dañado.', 'error');
            }
        } catch (error) {
            window.showAlert('No se pudo conectar con el servidor para procesar el XML.', 'error');
        }
    };

    window.agregarFilaDesdeSAT = function() {
        const cat = document.getElementById('sat-category').value;
        if (!cat) { window.showAlert('Por favor, selecciona a qué categoría pertenece este gasto.', 'warning'); return; }
        if (!tempSatData) return;

        const tbody = document.getElementById(cat);
        const dataRows = tbody.querySelectorAll('.data-row');
        let targetRow = null;

        if (dataRows.length > 0) {
            const lastRow = dataRows[dataRows.length - 1];
            const inputs = lastRow.querySelectorAll('.cell-input');
            if (!inputs[1].value.trim() && !inputs[2].value.trim()) targetRow = lastRow;
        }

        if (!targetRow) {
            window.addRow(cat, tempSatData.id, tempSatData.load_method);
            targetRow = tbody.lastElementChild;
        }

        tbody.style.display = '';

        let hiddenCfdiInput = targetRow.querySelector('.c-cfdi-id');
        if (hiddenCfdiInput) hiddenCfdiInput.value = tempSatData.id || '';

        let hiddenLoadMethod = targetRow.querySelector('.c-load-method');
        if (hiddenLoadMethod) hiddenLoadMethod.value = tempSatData.load_method || 'manual_entry';

        let iconSpan = targetRow.querySelector('.load-method-icon');
        if (iconSpan) {
            let currentMethod = tempSatData.load_method || 'manual_entry';
            iconSpan.setAttribute('data-method', currentMethod);
            iconSpan.title = window.getLoadMethodTooltip(currentMethod);
            iconSpan.innerHTML = window.getLoadMethodIcon(currentMethod);
        }

        const inputs = targetRow.querySelectorAll('.cell-input');
        if (inputs[0]._flatpickr && tempSatData.fecha_iso) { inputs[0]._flatpickr.setDate(tempSatData.fecha_iso, true, "Y-m-d"); }
        else if (tempSatData.fecha_iso) { const [y, m, d] = tempSatData.fecha_iso.split('-'); inputs[0].value = `${d}/${m}/${y}`; }

        inputs[1].value = tempSatData.folio;
        inputs[2].value = tempSatData.desc;
        inputs[4].value = tempSatData.sub;
        inputs[5].value = '';
        inputs[6].value = '';
        inputs[7].value = tempSatData.ish;
        inputs[8].value = tempSatData.iva;

        if (['sat_uuid', 'sat_xml'].includes(tempSatData.load_method)) {
            window.lockRowInputs(targetRow, true);
        }

        window.calcTotal();
        document.getElementById('sat-result-container').classList.add('hidden');
        document.getElementById('sat-category').value = '';
        tempSatData = null;
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Factura agregada a la matriz.', showConfirmButton: false, timer: 2000 });
    };

    /* ==========================================================================
       ── 10. GESTOR DOCUMENTAL ──
       ========================================================================== */
    let evidenciasFiles = [];
    const maxFileSize = 10 * 1024 * 1024;
    const evidenciaInput = document.getElementById('evidence-upload');
    const evidenciaPanel = document.getElementById('evidence-panel');

    if (evidenciaInput && evidenciaPanel) {
        evidenciaInput.addEventListener('change', function(e) {
            window.procesarArchivosEvidencia(e.target.files);
            this.value = '';
        });
        evidenciaPanel.addEventListener('dragover', e => {
            if (!window.isFormEditable) return;
            e.preventDefault();
            evidenciaPanel.classList.add('dragover');
        });
        evidenciaPanel.addEventListener('dragleave', e => {
            e.preventDefault();
            evidenciaPanel.classList.remove('dragover');
        });
        evidenciaPanel.addEventListener('drop', e => {
            e.preventDefault();
            evidenciaPanel.classList.remove('dragover');
            if (!window.isFormEditable) return;
            if (e.dataTransfer.files.length) window.procesarArchivosEvidencia(e.dataTransfer.files);
        });
    }

    window.procesarArchivosEvidencia = function(files) {
        if (!window.isFormEditable) return;
        let errorSize = false, errorType = false;
        Array.from(files).forEach(file => {
            if (file.type !== 'application/pdf') { errorType = true; return; }
            if (file.size > maxFileSize) { errorSize = true; return; }
            if (!evidenciasFiles.some(f => f.name === file.name)) evidenciasFiles.push(file);
        });
        if (errorType) window.showAlert('Solo se permiten archivos en formato PDF.', 'warning');
        if (errorSize) window.showAlert('Uno o más archivos son demasiado pesados. El límite es de 10 MB.', 'error');
        window.renderFileList();
        window.actualizarInputFiles();
    };

    window.removeFile = function(index) {
        evidenciasFiles.splice(index, 1);
        window.renderFileList();
        window.actualizarInputFiles();
    };

    window.actualizarInputFiles = function() {
        const dt = new DataTransfer();
        evidenciasFiles.forEach(file => dt.items.add(file));
        if (evidenciaInput) evidenciaInput.files = dt.files;
    };

    window.renderFileList = function() {
        const listDiv = document.getElementById('evidence-list');
        const titleEl = document.getElementById('evidence-title');
        const descEl = document.getElementById('evidence-desc');
        const panelEl = document.getElementById('evidence-panel');
        if (!listDiv) return;

        const total = existingEvidencePaths.length + evidenciasFiles.length;
        const editable = !!window.isFormEditable;

        if (panelEl) panelEl.classList.toggle('readonly-mode', !editable);

        if (total === 0) {
            if (titleEl) titleEl.textContent = 'Gestor Documental (PDF)';
            if (descEl) descEl.textContent = editable
                ? 'Arrastra y suelta tus facturas, tickets y documentación probatoria aquí. Carga máxima de 10MB por archivo unitario.'
                : 'Sin facturas adjuntas en esta solicitud.';
            listDiv.innerHTML = '';
            return;
        }

        if (titleEl) titleEl.textContent = `Facturas en PDF adjuntadas (${total})`;
        if (descEl) descEl.textContent = editable
            ? 'Haz clic sobre un archivo para visualizarlo. Puedes agregar o quitar archivos.'
            : 'Haz clic sobre un archivo para visualizarlo en una ventana externa.';

        let html = '<div class="file-grid">';

        existingEvidencePaths.forEach((path, i) => {
            const name = path.split('/').pop();
            html += `
                <div class="file-card existing-file clickable" onclick="event.stopPropagation(); window.openEvidenceViewer(${i});" title="Clic para visualizar">
                    <i class="bx bxs-file-pdf file-icon-lg"></i>
                    <div class="file-info">
                        <span class="file-name" title="${name}">${name}</span>
                        <span class="file-size" style="color:#16a34a; font-weight:600;">
                            <i class="bx bx-check-circle"></i> PDF adjuntado
                        </span>
                    </div>
                    ${editable ? `
                        <button type="button" class="btn-remove-file" onclick="event.stopPropagation(); window.removeExistingEvidence(${i})" title="Quitar">
                            <i class="bx bx-x"></i>
                        </button>
                    ` : ''}
                </div>`;
        });

        evidenciasFiles.forEach((f, i) => {
            const globalIndex = existingEvidencePaths.length + i;
            html += `
                <div class="file-card clickable" onclick="event.stopPropagation(); window.openEvidenceViewer(${globalIndex});" title="Clic para visualizar">
                    <i class="bx bxs-file-pdf file-icon-lg"></i>
                    <div class="file-info">
                        <span class="file-name" title="${f.name}">${f.name}</span>
                        <span class="file-size">${window.formatBytes(f.size)}</span>
                    </div>
                    ${editable ? `
                        <button type="button" class="btn-remove-file" onclick="event.stopPropagation(); window.removeFile(${i})" title="Quitar">
                            <i class="bx bx-x"></i>
                        </button>
                    ` : ''}
                </div>`;
        });

        html += '</div>';
        listDiv.innerHTML = html;
    };

    /* ==========================================================================
       ── 11. MATRIZ DINÁMICA ──
       ========================================================================== */
    window.getLoadMethodIcon = function(method) {
        if (method === 'sat_uuid') return "<i class='bx bx-cloud-download'></i>";
        if (method === 'sat_xml') return "<i class='bx bx-file'></i>";
        if (method === 'manual_entry') return "<i class='bx bx-edit-alt'></i>";
        return "<i class='bx bx-dots-horizontal-rounded'></i>";
    };

    window.getLoadMethodTooltip = function(method) {
        if (method === 'sat_uuid') return "Factura importada del SAT";
        if (method === 'sat_xml') return "Factura extraída de un archivo XML";
        if (method === 'manual_entry') return "Gasto ingresado manualmente";
        return "Fila vacía - Escribe para registrar un gasto";
    };

    window.lockRowInputs = function(row, lock) {
        const inputsToLock = row.querySelectorAll('.date-in, .desc-input, .num');
        inputsToLock.forEach(input => {
            if (lock) {
                input.setAttribute('readonly', 'true');
                input.style.backgroundColor = '#f1f5f9';
                input.style.pointerEvents = input.classList.contains('date-in') ? 'none' : 'auto';
            } else {
                input.removeAttribute('readonly');
                input.style.backgroundColor = '';
                input.style.pointerEvents = 'auto';
            }
        });
        const allInputs = row.querySelectorAll('.cell-input');
        if (allInputs.length > 1) {
            const folioInput = allInputs[1];
            if(lock) {
                folioInput.setAttribute('readonly', 'true');
                folioInput.style.backgroundColor = '#f1f5f9';
            } else {
                folioInput.removeAttribute('readonly');
                folioInput.style.backgroundColor = '';
            }
        }
    };

    window.markAsManual = function(element) {
        const row = element.closest('tr');
        if(!row) return;

        const hiddenLoadMethod = row.querySelector('.c-load-method');
        if (hiddenLoadMethod && ['sat_uuid', 'sat_xml'].includes(hiddenLoadMethod.value)) {
            return;
        }

        const iconSpan = row.querySelector('.load-method-icon');
        const inputs = row.querySelectorAll('.cell-input');

        const date = inputs[0].value.trim();
        const folio = inputs[1].value.trim();
        const desc = inputs[2].value.trim();
        const amounts = Array.from(inputs).slice(4,9).some(i => i.value && parseFloat(i.value) !== 0 && !isNaN(parseFloat(i.value)));

        const isEmpty = !date && !folio && !desc && !amounts;

        if (isEmpty) {
            if (hiddenLoadMethod) hiddenLoadMethod.value = 'unassigned';
            if (iconSpan) {
                iconSpan.setAttribute('data-method', 'unassigned');
                iconSpan.title = window.getLoadMethodTooltip('unassigned');
                iconSpan.innerHTML = window.getLoadMethodIcon('unassigned');
            }
        } else if (hiddenLoadMethod && hiddenLoadMethod.value === 'unassigned') {
            hiddenLoadMethod.value = 'manual_entry';
            if (iconSpan) {
                iconSpan.setAttribute('data-method', 'manual_entry');
                iconSpan.title = window.getLoadMethodTooltip('manual_entry');
                iconSpan.innerHTML = window.getLoadMethodIcon('manual_entry');
            }
        }
    };

    window.getRowTemplate = function(cfdiId = '', loadMethod = 'unassigned', ccValue = '') {
        return `
        <tr class="data-row">
            <input type="hidden" class="c-cfdi-id" value="${cfdiId}">
            <input type="hidden" class="c-load-method" value="${loadMethod}">

            <td><div class="date-wrap"><i class="bx bx-calendar"></i><input type="text" class="cell-input date-in modal-focusable" placeholder="DD/MM/AAAA" data-fp onchange="window.markAsManual(this)"></div></td>
            <td><input type="text" class="cell-input modal-focusable" placeholder="—" oninput="window.markAsManual(this)"></td>
            <td>
                <div class="desc-wrap">
                    <input type="text" class="cell-input modal-focusable desc-input" placeholder="—" oninput="window.markAsManual(this)">
                    <span class="load-method-icon" data-method="${loadMethod}" title="${window.getLoadMethodTooltip(loadMethod)}">
                        ${window.getLoadMethodIcon(loadMethod)}
                    </span>
                </div>
            </td>

            <td class="td-cc-col">
                <select class="cell-input c-cc-select modal-focusable" onchange="window.markAsManual(this)">
                    ${window.buildCostCenterOptions(false, ccValue)}
                </select>
            </td>

            <td><input type="number" oninput="window.markAsManual(this); window.calcTotal()" class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
            <td><input type="number" oninput="window.markAsManual(this); window.calcTotal()" class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
            <td><input type="number" oninput="window.markAsManual(this); window.calcTotal()" class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
            <td><input type="number" oninput="window.markAsManual(this); window.calcTotal()" class="cell-input num c-ish modal-focusable" placeholder="0.00"></td>
            <td><input type="number" oninput="window.markAsManual(this); window.calcTotal()" class="cell-input num c-iva modal-focusable" placeholder="0.00"></td>
            <td class="cell-row-total">-</td>
            <td class="text-center"><button type="button" class="btn-remove-row" onclick="window.removeRow(this)" title="Eliminar fila"><i class="bx bx-trash"></i></button></td>
        </tr>`;
    };

    window.addRow = function(tbodyId, cfdiId = '', loadMethod = 'unassigned', ccValue = '') {
        const tbody = document.getElementById(tbodyId);
        if (!tbody) return;
        tbody.style.display = '';
        tbody.insertAdjacentHTML('beforeend', window.getRowTemplate(cfdiId, loadMethod, ccValue));
        flatpickr(tbody.lastElementChild.querySelector('[data-fp]'), {
            locale: "es", dateFormat: "d/m/Y", allowInput: true, disableMobile: "true"
        });
    };

    window.removeRow = function(btn) {
        const row = btn.closest('tr.data-row');
        if (!row) return;
        row.remove();
        window.calcTotal();
    };

    window.calcTotal = function() {
        let gFiscal = 0, gSimple = 0, gPropina = 0, gIva = 0, gIsh = 0;
        document.querySelectorAll('.data-row').forEach(row => {
            const inputs = row.querySelectorAll('.cell-input');
            const rFiscal = parseFloat(inputs[4].value) || 0;
            const rSimple = parseFloat(inputs[5].value) || 0;
            const rPropina = parseFloat(inputs[6].value) || 0;
            const rIsh = parseFloat(inputs[7].value) || 0;
            const rIva = parseFloat(inputs[8].value) || 0;

            const rowTotal = rFiscal + rSimple + rPropina + rIsh + rIva;
            const rowTotalCell = row.querySelector('.cell-row-total');
            if (rowTotalCell) rowTotalCell.textContent = rowTotal > 0 ? window.fmt(rowTotal) : '-';
            gFiscal += rFiscal; gSimple += rSimple; gPropina += rPropina; gIsh += rIsh; gIva += rIva;
        });
        const gTotal = gFiscal + gSimple + gPropina + gIva + gIsh;
        if (document.getElementById('sum-fiscal')) document.getElementById('sum-fiscal').textContent = window.fmt(gFiscal);
        if (document.getElementById('sum-simple')) document.getElementById('sum-simple').textContent = window.fmt(gSimple);
        if (document.getElementById('sum-propinas')) document.getElementById('sum-propinas').textContent = window.fmt(gPropina);
        if (document.getElementById('sum-iva')) document.getElementById('sum-iva').textContent = window.fmt(gIva);
        if (document.getElementById('sum-ish')) document.getElementById('sum-ish').textContent = window.fmt(gIsh);
        if (document.getElementById('sum-total')) {
            document.getElementById('sum-total').textContent = window.fmt(gTotal);
            document.getElementById('sum-total').setAttribute('data-value', gTotal);
        }

        window.recalcLeftover();
    };

    /* ==========================================================================
       ── 11.1. VISIBILIDAD DE ACCIONES SEGÚN MODO ──
       ========================================================================== */
    window.updateActionVisibility = function() {
        const editableForm = !!window.isFormEditable;
        const editableLft  = !!window.isLeftoverEditable;

        const evidenceBrowseBtn = document.getElementById('evidence-browse-btn');
        const leftoverBrowseBtn = document.getElementById('leftover-browse-btn');

        if (evidenceBrowseBtn) evidenceBrowseBtn.classList.toggle('hidden', !editableForm);
        if (leftoverBrowseBtn) leftoverBrowseBtn.classList.toggle('hidden', !editableLft);

        const evidencePanel = document.getElementById('evidence-panel');
        const leftoverPanel = document.getElementById('leftover-evidence-panel');
        if (evidencePanel) evidencePanel.classList.toggle('readonly-mode', !editableForm);
        if (leftoverPanel) leftoverPanel.classList.toggle('readonly-mode', !editableLft);

        document.querySelectorAll('.leftover-stage-input').forEach(el => {
            if (editableLft) el.removeAttribute('disabled');
            else el.setAttribute('disabled', 'true');
        });

        window.renderFileList();
        window.renderLeftoverFile();
        window.renderExcessFile();
    };

    /* ==========================================================================
       ── 12. LOCK / UNLOCK / RESET ──
       ========================================================================== */
    window.lockForm = function() {
        window.isFormEditable = false;
        window.isLeftoverEditable = false;

        document.querySelectorAll('#reimbursement-modal .modal-focusable, #reimbursement-modal .cell-input').forEach(el => el.setAttribute('disabled', 'true'));
        document.querySelectorAll('input[name="is_deductible"], input[name="is_delegated"]').forEach(el => el.disabled = true);
        document.querySelectorAll('.btn-add-row, .btn-remove-row').forEach(el => el.classList.add('hidden'));
        const actionBtn = document.querySelector('.modal-header-actions .btn-secondary');
        if(actionBtn) actionBtn.classList.add('hidden');
        document.getElementById('modal-nombre').setAttribute('readonly', 'true');
        document.getElementById('modal-nombre').disabled = true;
        document.getElementById('modal-advance-id').disabled = true;
        window.toggleAdvanceViewButton();

        window.updateActionVisibility();
    };

    window.unlockForm = function() {
        window.isFormEditable = true;
        window.isLeftoverEditable = false;

        document.querySelectorAll('#reimbursement-modal .modal-focusable, #reimbursement-modal .cell-input').forEach(el => el.removeAttribute('disabled'));
        document.querySelectorAll('input[name="is_deductible"], input[name="is_delegated"]').forEach(el => el.disabled = false);
        document.querySelectorAll('.btn-add-row, .btn-remove-row').forEach(el => el.classList.remove('hidden'));
        const actionBtn = document.querySelector('.modal-header-actions .btn-secondary');
        if(actionBtn) actionBtn.classList.remove('hidden');
        document.getElementById('modal-nombre').disabled = false;
        document.getElementById('modal-advance-id').disabled = false;
        document.getElementById('btn-view-advance').classList.add('hidden');

        const lAmount = document.getElementById('leftover-amount');
        if (lAmount) lAmount.setAttribute('readonly', 'true');
        const eAmount = document.getElementById('excess-amount');
        if (eAmount) eAmount.setAttribute('readonly', 'true');

        const isDelegated = document.querySelector('input[name="is_delegated"]:checked').value === "1";
        if (isDelegated) {
            document.getElementById('modal-nombre').removeAttribute('readonly');
        } else {
            document.getElementById('modal-nombre').setAttribute('readonly', 'true');
        }

        document.querySelectorAll('.data-row').forEach(row => {
            const method = row.querySelector('.c-load-method').value;
            if (['sat_uuid', 'sat_xml'].includes(method)) {
                window.lockRowInputs(row, true);
            }
        });

        window.updateActionVisibility();
    };

    window.resetModalForm = function() {
        window.resetExcessAlert();
        ['cat-vuelos', 'cat-restaurantes', 'cat-combustible', 'cat-otros'].forEach(cat => {
            const tbody = document.getElementById(cat);
            if (tbody) {
                const rows = tbody.querySelectorAll('.data-row');
                rows.forEach(r => r.remove());
                tbody.style.display = '';
            }
        });
        document.querySelectorAll('.cell-input:not([readonly])').forEach(el => el.value = '');
        document.querySelectorAll('.c-cfdi-id').forEach(el => el.value = '');
        document.querySelectorAll('.c-load-method').forEach(el => el.value = 'unassigned');
        document.querySelectorAll('.c-cc-select').forEach(el => el.value = '');

        document.querySelectorAll('.load-method-icon').forEach(iconSpan => {
            iconSpan.setAttribute('data-method', 'unassigned');
            iconSpan.title = window.getLoadMethodTooltip('unassigned');
            iconSpan.innerHTML = window.getLoadMethodIcon('unassigned');
        });

        document.getElementById('modal-motivo').value = '';

        if (mainCostCenterSelect) {
            mainCostCenterSelect.innerHTML = window.buildCostCenterOptions(true);
        }
        window.toggleVariosMode();

        document.getElementById('modal-tipo-solicitud').value = '';
        document.getElementById('modal-tipo-gasto').value = '';
        document.getElementById('lbl-advance').innerHTML = 'Anticipo a Comprobar';

        currentClaimApprovalStatus = 'en_espera';
        document.getElementById('leftover-amount').value = '';
        document.getElementById('leftover-method').value = 'Transferencia';
        document.getElementById('leftover-notes').value = '';
        document.getElementById('excess-amount').value = '';
        document.getElementById('excess-method').value = 'Transferencia';
        document.getElementById('excess-notes').value = '';

        leftoverProofFile = null;
        existingLeftoverProofPath = null;
        existingExcessProofPath = null;
        currentAdvanceAmount = 0;
        window.renderLeftoverFile();
        window.renderExcessFile();

        existingEvidencePaths = [];
        evidenciasFiles = [];
        window.renderFileList();
        window.actualizarInputFiles();

        document.getElementById('leftover-body').classList.add('hidden');
        document.getElementById('leftover-section').classList.add('hidden');
        document.getElementById('excess-section').classList.add('hidden');

        document.getElementById('sat-panel').classList.add('hidden');
        document.getElementById('sat-result-container').classList.add('hidden');
        document.getElementById('rejection-container').classList.add('hidden');

        document.getElementById('footer-leftover').classList.add('hidden');

        tempSatData = null;
        currentActiveClaimId = null;
        isEditMode = false;
        document.querySelector('input[name="is_delegated"][value="0"]').checked = true;
        window.handleDelegationToggle();

        window.calcTotal();
        window.unlockForm();
    };

    window.openModalForCreate = function() {
        window.resetModalForm();
        window.updateCategoryVisibility(false);

        document.getElementById('main-modal-title').innerHTML = '<i class="bx bx-receipt"></i> Generación de <strong>Solicitud</strong>';
        document.getElementById('modal-folio-p').innerHTML = '<span class="status-badge badge-draft badge-draft-auto">Asignación Automática</span>';
        document.getElementById('modal-folio-u').innerHTML = '<span class="status-badge badge-draft badge-draft-auto">Automático</span>';
        document.querySelector('input[name="is_deductible"][value="1"]').checked = true;

        document.getElementById('btn-enviar').innerHTML = '<i class="bx bx-send"></i> Enviar a Revisión';
        document.getElementById('btn-borrador').classList.remove('hidden');
        document.getElementById('footer-create').classList.remove('hidden');
        document.getElementById('footer-view').classList.add('hidden');
        document.getElementById('footer-evaluate').classList.add('hidden');
        document.getElementById('footer-leftover').classList.add('hidden');
        document.getElementById('reimbursement-modal').classList.remove('hidden');
    };

    window.fetchAndPopulateClaim = async function(id) {
        try {
            const fetchUrl = `{{ url('administration/expense-claims/reimbursements') }}/${id}`;
            const response = await fetch(fetchUrl);
            const res = await response.json();

            if (res.success) {
                const claim = res.data;
                currentActiveClaimId = claim.id;
                currentClaimApprovalStatus = claim.status_approval || 'en_espera';

                document.getElementById('rejection-container').classList.add('hidden');
                if ((claim.status_review === 'rechazado' || claim.status_approval === 'rechazado') && claim.logs && claim.logs.length > 0) {
                    const rejectLog = claim.logs.find(log => log.new_status === 'rechazado' || log.action.toLowerCase().includes('rechaz'));
                    if (rejectLog && rejectLog.comments) {
                        document.getElementById('rejection-text').textContent = rejectLog.comments;
                        document.getElementById('rejection-container').classList.remove('hidden');
                    }
                }

                document.getElementById('modal-folio-p').textContent = claim.folio_system;
                document.getElementById('modal-folio-u').textContent = claim.folio_user || 'N/A';
                document.getElementById('modal-lugar').value = claim.emission_place;

                if (claim.user_id !== sessionUser.id) { document.querySelector('input[name="is_delegated"][value="1"]').checked = true; }
                else { document.querySelector('input[name="is_delegated"][value="0"]').checked = true; }
                window.handleDelegationToggle();

                document.getElementById('modal-nombre').value = claim.beneficiary.name;
                document.getElementById('modal-depto').value = claim.beneficiary.employee?.area?.name || claim.area || 'Sin asignar';
                document.getElementById('modal-beneficiary-id').value = claim.user_id;

                const headerVal = claim.cost_center_id
                                ? (claim.project_id ? `PRJ_${claim.project_id}_${claim.cost_center_id}` : `CC_${claim.cost_center_id}`)
                                : 'VARIOS';
                mainCostCenterSelect.value = headerVal;
                window.toggleVariosMode();

                document.getElementById('modal-motivo').value = claim.motive;
                document.getElementById('modal-tipo-solicitud').value = claim.request_type || '';
                document.getElementById('modal-tipo-gasto').value = claim.category || '';
                document.querySelector(`input[name="is_deductible"][value="${claim.is_deductible ? '1' : '0'}"]`).checked = true;

                const advanceToSelect = claim.expense_advance_id || null;
                if (advanceToSelect) {
                    const selAdv = document.getElementById('modal-advance-id');
                    const seedLabel = (claim.expense_advance && claim.expense_advance.folio_system)
                        ? claim.expense_advance.folio_system
                        : `Anticipo #${advanceToSelect}`;
                    selAdv.innerHTML = `<option value="${advanceToSelect}" selected>${seedLabel}</option>`;
                    if (claim.expense_advance && claim.expense_advance.amount != null) {
                        window.advanceAmountsMap[advanceToSelect] = parseFloat(claim.expense_advance.amount) || 0;
                        currentAdvanceAmount = window.advanceAmountsMap[advanceToSelect];
                    }
                }

                await window.handleTipoSolicitudChange(advanceToSelect);

                document.getElementById('leftover-amount').value = claim.leftover_amount || '';
                document.getElementById('leftover-method').value = claim.leftover_method || 'Transferencia';
                document.getElementById('leftover-notes').value = claim.leftover_notes || '';
                existingLeftoverProofPath = claim.leftover_proof_path || null;
                leftoverProofFile = null;
                window.renderLeftoverFile();

                document.getElementById('excess-amount').value = claim.excess_amount || '';
                document.getElementById('excess-method').value = claim.excess_method || 'Transferencia';
                document.getElementById('excess-notes').value = claim.excess_notes || '';
                existingExcessProofPath = claim.excess_proof_path || null;
                window.renderExcessFile();

                existingEvidencePaths = Array.isArray(claim.evidence_documents)
                    ? claim.evidence_documents.filter(Boolean)
                    : [];
                evidenciasFiles = [];
                window.renderFileList();

                ['cat-vuelos', 'cat-restaurantes', 'cat-combustible', 'cat-otros'].forEach(cat => {
                    document.getElementById(cat).querySelectorAll('.data-row').forEach(r => r.remove());
                });

                claim.lines.forEach(line => {
                    const cat = line.concept_group;
                    let method = line.load_method || 'manual_entry';
                    let rowCcValue = line.cost_center_id
                                    ? (line.project_id ? `PRJ_${line.project_id}_${line.cost_center_id}` : `CC_${line.cost_center_id}`)
                                    : '';

                    window.addRow(cat, line.expense_cfdi_id || '', method, rowCcValue);

                    const targetRow = document.getElementById(cat).lastElementChild;
                    const inputs = targetRow.querySelectorAll('.cell-input');
                    const dateOnly = line.expense_date.substring(0, 10);
                    const [y, m, d] = dateOnly.split('-');

                    if (inputs[0]._flatpickr) { inputs[0]._flatpickr.setDate(dateOnly, true, "Y-m-d"); }
                    else { inputs[0].value = `${d}/${m}/${y}`; }

                    inputs[1].value = line.document_number || '';
                    inputs[2].value = line.description || '';

                    inputs[4].value = line.amount_fiscal > 0 ? line.amount_fiscal : '';
                    inputs[5].value = line.amount_simple > 0 ? line.amount_simple : '';
                    inputs[6].value = line.amount_none > 0 ? line.amount_none : '';
                    inputs[7].value = line.tax_ish !== '0.00' ? line.tax_ish : '';
                    inputs[8].value = line.tax_iva > 0 ? line.tax_iva : '';

                    if (['sat_uuid', 'sat_xml'].includes(method)) {
                        window.lockRowInputs(targetRow, true);
                    }
                });

                window.calcTotal();
                window.recalcLeftover();
                return claim;
            }
        } catch (error) {
            console.error('Error al cargar datos internos: ', error);
            throw error;
        }
    };

    window.verDetalles = async function(id) {
        Swal.fire({ title: 'Cargando información...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        try {
            window.resetModalForm();
            const claim = await window.fetchAndPopulateClaim(id);
            if (!claim) return;

            document.getElementById('main-modal-title').innerHTML = `<i class="bx bx-search-alt"></i> Revisión del <strong>Folio: ${claim.folio_system}</strong>`;
            window.lockForm();
            window.updateCategoryVisibility(true);

            document.getElementById('footer-create').classList.add('hidden');
            document.getElementById('footer-view').classList.remove('hidden');
            document.getElementById('footer-evaluate').classList.add('hidden');
            document.getElementById('footer-leftover').classList.add('hidden');

            const viewFooter = document.getElementById('footer-view');
            viewFooter.innerHTML = '<button type="button" class="btn btn-secondary" onclick="window.closeModal()">Cerrar</button>';

            const isOwnClaim = Number(claim.user_id) === Number(sessionUser.id) || Number(claim.created_by_id) === Number(sessionUser.id);

            if (isOwnClaim && (claim.status_review === 'borrador' || claim.status_review === 'rechazado' || claim.status_approval === 'rechazado')) {
                const btnEdit = document.createElement('button');
                btnEdit.type = 'button';
                btnEdit.className = 'btn btn-primary';
                btnEdit.innerHTML = '<i class="bx bx-edit"></i> ' + ((claim.status_review === 'rechazado' || claim.status_approval === 'rechazado') ? 'Corregir y Reenviar' : 'Continuar Borrador');
                btnEdit.onclick = () => window.habilitarEdicion(claim.status_review === 'rechazado' || claim.status_approval === 'rechazado' ? 'rechazado' : 'borrador');
                viewFooter.appendChild(btnEdit);
            } else if (claim.status_approval === 'aprobado' && claim.status_balance === 'devolucion_pendiente') {
                const btnLeftover = document.createElement('button');
                btnLeftover.type = 'button';
                btnLeftover.className = 'btn btn-primary';
                btnLeftover.innerHTML = '<i class="bx bx-upload"></i> Subir Devolución de Sobrante';
                btnLeftover.onclick = () => window.habilitarSubidaSobrante();
                viewFooter.appendChild(btnLeftover);
            }

            Swal.close();
            document.getElementById('reimbursement-modal').classList.remove('hidden');
        } catch(error) {
            Swal.close();
            window.showAlert('Hubo un problema cargando los datos. Intenta nuevamente.', 'error');
        }
    };

    window.abrirModalSubirSobrante = async function(id) {
        await window.verDetalles(id);
        window.habilitarSubidaSobrante();
    };

    window.habilitarSubidaSobrante = function() {
        window.lockForm();
        window.isLeftoverEditable = true;
        window.updateActionVisibility();

        document.getElementById('main-modal-title').innerHTML = `<i class="bx bx-upload"></i> Registrar Devolución de Sobrante — <strong>${document.getElementById('modal-folio-p').textContent}</strong>`;

        document.getElementById('footer-view').classList.add('hidden');
        document.getElementById('footer-create').classList.add('hidden');
        document.getElementById('footer-evaluate').classList.add('hidden');
        document.getElementById('footer-leftover').classList.remove('hidden');

        const leftoverSection = document.getElementById('leftover-section');
        if (leftoverSection) {
            leftoverSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    window.submitLeftoverProof = async function() {
        if (!currentActiveClaimId) return;

        const method = document.getElementById('leftover-method').value || 'Transferencia';
        const notes  = document.getElementById('leftover-notes').value.trim();

        if (!leftoverProofFile) {
            window.showAlert('Debes adjuntar el archivo PDF del comprobante de devolución del sobrante.', 'warning');
            return;
        }

        let formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('leftover_method', method);
        formData.append('leftover_notes', notes);
        formData.append('leftover_proof', leftoverProofFile);

        try {
            Swal.fire({ title: 'Enviando comprobante...', text: 'Registrando devolución de sobrante.', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            const url = `{{ url('administration/expense-claims/reimbursements') }}/${currentActiveClaimId}/leftover-proof`;
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();

            if (data.success) {
                window.closeModal();
                await Swal.fire({
                    title: '¡Comprobante Enviado!',
                    html: `${data.message}<br><br><strong>Folio: ${data.folio}</strong>`,
                    icon: 'success',
                    confirmButtonColor: '#2d7d46'
                });
                await window.refreshTableData();
            } else {
                window.showAlert(data.message || 'No se pudo guardar el comprobante.', 'error');
            }
        } catch (error) {
            window.showAlert('Error de conexión al enviar el comprobante.', 'error');
        }
    };

    window.habilitarEdicion = function(status) {
        isEditMode = true;
        window.unlockForm();
        window.updateCategoryVisibility(false);

        let prefixText = '';
        let iconHtml = '';

        if (status === 'rechazado') {
            prefixText = 'Corrigiendo';
            iconHtml = '<i class="bx bx-wrench"></i>';
        } else {
            prefixText = 'Continuando';
            iconHtml = '<i class="bx bx-play-circle"></i>';
        }

        document.getElementById('main-modal-title').innerHTML = `${iconHtml} ${prefixText} <strong>Folio: ${document.getElementById('modal-folio-p').textContent}</strong>`;

        document.getElementById('footer-view').classList.add('hidden');
        document.getElementById('footer-leftover').classList.add('hidden');
        document.getElementById('footer-create').classList.remove('hidden');

        const btnEnviar = document.getElementById('btn-enviar');
        if (status === 'rechazado') {
            btnEnviar.innerHTML = '<i class="bx bx-send"></i> Reenviar Solicitud';
            document.getElementById('btn-borrador').classList.add('hidden');
        } else {
            btnEnviar.innerHTML = '<i class="bx bx-send"></i> Enviar a Revisión';
            document.getElementById('btn-borrador').classList.remove('hidden');
        }
    };

    window.evaluarSolicitud = async function(id) {
        Swal.fire({ title: 'Cargando información...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        try {
            window.resetModalForm();
            const claim = await window.fetchAndPopulateClaim(id);
            if (!claim) return;

            const canVal = window.canUserValidateClaim(claim);
            const canApp = window.canUserApproveClaim(claim);

            if (!canVal && !canApp) {
                Swal.close();
                window.showAlert('No cuentas con permisos para dictaminar esta solicitud (solo lectura).', 'warning');
                return;
            }

            document.getElementById('main-modal-title').innerHTML = '<i class="bx bx-check-shield"></i> Evaluar Solicitud';
            currentEvaluateId = id;
            window.lockForm();
            window.updateCategoryVisibility(true);

            const btnValidate = document.getElementById('btn-eval-validate');
            const btnApprove = document.getElementById('btn-eval-approve');

            btnValidate.classList.toggle('hidden', !canVal);
            btnApprove.classList.toggle('hidden', !canApp);

            document.getElementById('footer-create').classList.add('hidden');
            document.getElementById('footer-view').classList.add('hidden');
            document.getElementById('footer-leftover').classList.add('hidden');
            document.getElementById('footer-evaluate').classList.remove('hidden');
            Swal.close();
            document.getElementById('reimbursement-modal').classList.remove('hidden');
        } catch (error) {
            Swal.close();
            window.showAlert('Hubo un problema cargando los datos. Intenta nuevamente.', 'error');
        }
    };

    window.closeModal = function() {
        window.resetExcessAlert();
        document.getElementById('reimbursement-modal').classList.add('hidden');
    };

    /* ==========================================================================
       ── HISTORIAL ──
       ========================================================================== */
    window.verHistorial = async function(id) {
        const modal = document.getElementById('history-modal');
        const timeline = document.getElementById('history-timeline');

        document.getElementById('history-folio').textContent = '...';

        timeline.innerHTML = `<div class="empty-state"><i class="bx bx-loader-alt bx-spin empty-icon" style="color: var(--primary-dark);"></i><p class="empty-title">Obteniendo historial...</p><p class="empty-desc">Por favor, espera un momento.</p></div>`;
        modal.classList.remove('hidden');

        try {
            const response = await fetch(`{{ url('administration/expense-claims/reimbursements') }}/${id}`);
            const res = await response.json();

            if (res.success) {
                const claim = res.data;
                document.getElementById('history-folio').textContent = claim.folio_system;
                timeline.innerHTML = '';

                if (claim.logs && claim.logs.length > 0) {
                    claim.logs.forEach(log => {
                        const dateObj = new Date(log.created_at);
                        const date = dateObj.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
                        const time = dateObj.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
                        const userName = log.user ? log.user.name : 'Sistema Automático';

                        let iconColor = '#152845';
                        let iconBx = 'bx-info-circle';
                        const actionName = log.action.toLowerCase();

                        if (actionName.includes('rechaza')) { iconColor = '#dc2626'; iconBx = 'bx-block'; }
                        else if (actionName.includes('aprobaci') || actionName.includes('aprobado') || log.new_status === 'aprobado') { iconColor = '#059669'; iconBx = 'bx-check-double'; }
                        else if (actionName.includes('validaci') || log.new_status === 'validado') { iconColor = '#0284c7'; iconBx = 'bx-list-check'; }
                        else if (actionName.includes('devoluci') || log.new_status === 'por_verificar') { iconColor = '#d97706'; iconBx = 'bx-upload'; }
                        else if (actionName.includes('corregid')) { iconColor = '#ea580c'; iconBx = 'bx-wrench'; }
                        else if (actionName.includes('creada')) { iconColor = '#2563eb'; iconBx = 'bx-send'; }
                        else if (actionName.includes('borrador')) { iconColor = '#64748b'; iconBx = 'bx-save'; }
                        else if (log.new_status === 'pendiente') { iconColor = '#d97706'; iconBx = 'bx-hourglass'; }

                        timeline.innerHTML += `
                            <div class="timeline-item">
                                <div class="timeline-icon" style="background-color: ${iconColor}; border-color: #f8fafc;"><i class="bx ${iconBx}"></i></div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-title">${log.action}</span>
                                        <span class="timeline-date"><i class="bx bx-calendar"></i> ${date} - ${time}</span>
                                    </div>
                                    <div class="timeline-comment" style="border-left-color: ${iconColor};">${log.comments || 'Sin detalles registrados.'}</div>
                                    <div class="timeline-user"><i class="bx bx-user-circle"></i> ${userName}</div>
                                </div>
                            </div>`;
                    });
                } else {
                    timeline.innerHTML = '<div class="empty-state"><i class="bx bx-time empty-icon"></i><p class="empty-desc">No hay movimientos registrados en el historial para este folio.</p></div>';
                }
            } else {
                timeline.innerHTML = '<div class="empty-state"><i class="bx bx-error-circle empty-icon" style="color:#ef4444;"></i><p class="empty-title">Hubo un error</p><p class="empty-desc">No se pudo cargar el historial. Intenta nuevamente.</p></div>';
            }
        } catch (error) {
            timeline.innerHTML = '<div class="empty-state"><i class="bx bx-wifi-off empty-icon" style="color:#ef4444;"></i><p class="empty-title">Error de conexión</p><p class="empty-desc">No se pudo conectar con el servidor.</p></div>';
        }
    };

    window.closeHistoryModal = function() {
        document.getElementById('history-modal').classList.add('hidden');
    };

    window.processEvaluation = function(status) {
        const evalId = currentEvaluateId;
        if (!evalId) { window.showAlert('Se perdió la referencia del trámite. Cierra la ventana y vuelve a intentarlo.', 'error'); return; }

        let actionText = status === 'aprobado' ? 'Aprobar' : (status === 'validado' ? 'Dar Visto Bueno (Validar)' : 'Rechazar Solicitud');
        let confirmColor = status === 'aprobado' ? '#2d7d46' : (status === 'validado' ? '#1d4ed8' : '#b91c1c');

        if (status === 'rechazado') {
            Swal.fire({
                title: `<span style="font-family:'Poppins', sans-serif;">Motivo del Rechazo</span>`,
                html: `<span style="font-family:'Poppins', sans-serif; color:#64748b; font-size: 0.85rem;">Explica brevemente por qué rechazas este trámite para que el empleado pueda corregirlo.</span>`,
                input: 'textarea',
                inputPlaceholder: 'Ej. Faltan los archivos XML o los montos no coinciden...',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#94a3b8',
                confirmButtonText: `Rechazar`,
                cancelButtonText: `Cancelar`,
                inputValidator: (value) => { if (!value.trim()) return '¡Debes escribir un motivo de rechazo!'; }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.updateStatus(evalId, status, result.value);
                    window.closeModal();
                    currentEvaluateId = null;
                }
            });
        } else {
            Swal.fire({
                title: `<span style="font-family:'Poppins', sans-serif;">¿Confirmar Acción?</span>`,
                html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">¿Estás seguro de que deseas <strong>${actionText}</strong> para este folio?</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#94a3b8',
                confirmButtonText: `Sí, confirmar`,
                cancelButtonText: `Cancelar`
            }).then((result) => {
                if (result.isConfirmed) {
                    window.updateStatus(evalId, status, null);
                    window.closeModal();
                    currentEvaluateId = null;
                }
            });
        }
    };

    window.updateStatus = async function(id, status, comments) {
        try {
            Swal.fire({ title: 'Actualizando estado...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            let formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('new_status', status);
            if (comments) formData.append('comments', comments);

            const statusUrl = `{{ url('administration/expense-claims/reimbursements') }}/${id}/status`;
            const response = await fetch(statusUrl, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    title: '¡Listo!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#2d7d46'
                });
                await window.refreshTableData();
            } else {
                window.showAlert(data.message, 'error');
            }
        } catch (error) {
            window.showAlert('Hubo un error al intentar guardar los cambios.', 'error');
        }
    };

    /* ==========================================================================
       ── 13. ENVÍO FINAL Y GUARDADO (ETAPA 1 SIN RECARGAR PÁGINA) ──
       ========================================================================== */
    window.verifyAndSubmit = function() {
        const nombreBen = document.getElementById('modal-nombre').value.trim();
        const motivo = document.getElementById('modal-motivo').value.trim();
        const centroCosto = mainCostCenterSelect.value;
        const total = parseFloat(document.getElementById('sum-total').getAttribute('data-value'));
        const tipoSolicitud = document.getElementById('modal-tipo-solicitud').value;
        const advanceId = document.getElementById('modal-advance-id').value;

        if (!tipoSolicitud) { window.showAlert('Falta seleccionar el tipo de solicitud.', 'warning'); return; }

        if ((tipoSolicitud === 'Comprobacion Electronica' || tipoSolicitud === 'Comprobacion Directa') && !advanceId) {
            window.showAlert('Para las comprobaciones es obligatorio seleccionar el Anticipo que se está comprobando.', 'warning');
            return;
        }

        if (!nombreBen) { window.showAlert('Falta seleccionar al empleado beneficiario.', 'warning'); return; }
        if (!centroCosto) { window.showAlert('Falta seleccionar la Imputación (Centro de Costos) en la cabecera.', 'warning'); return; }
        if (!motivo) { window.showAlert('Debes escribir un motivo o justificación para este trámite.', 'warning'); return; }

        let hasValidRows = false;
        let isMissingRowCc = false;

        document.querySelectorAll('.data-row').forEach(row => {
            const dateVal = row.querySelectorAll('.cell-input')[0].value;
            if (dateVal) {
                hasValidRows = true;
                if (centroCosto === 'VARIOS') {
                    const rowCc = row.querySelector('.c-cc-select').value;
                    if (!rowCc) isMissingRowCc = true;
                }
            }
        });

        if (!hasValidRows) { window.showAlert('La tabla de gastos está vacía. Debes agregar al menos un concepto.', 'error'); return; }
        if (total <= 0) { window.showAlert('El monto total comprobado no puede ser cero.', 'error'); return; }
        if (isMissingRowCc) { window.showAlert('Al elegir "VARIOS", es obligatorio asignar un Centro/Proyecto en cada fila de gasto.', 'error'); return; }

        Swal.fire({
            title: `<span style="font-family:'Poppins', sans-serif;">¿Enviar a Revisión?</span>`,
            html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">Vas a enviar un trámite por un total de <strong>${window.fmt(total)}</strong>.<br>Una vez enviado, no podrás editarlo hasta que sea revisado.</span>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1d4ed8',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: `Sí, enviar trámite`,
            cancelButtonText: `Revisar de nuevo`
        }).then((result) => {
            if (result.isConfirmed) window.procesarEnvio('pendiente', 'en_espera');
        });
    };

    window.saveDraft = function() {
        const nombreBen = document.getElementById('modal-nombre').value.trim();
        const motivo = document.getElementById('modal-motivo').value.trim();
        if (!nombreBen || !motivo) { window.showAlert('Para guardar un borrador, necesitas al menos seleccionar al empleado y escribir un motivo.', 'warning'); return; }
        window.procesarEnvio('borrador', 'n_a');
    };

    window.procesarEnvio = async function(estadoRevision, estadoAprobacion) {
        let lineasArray = [];
        ['cat-vuelos', 'cat-restaurantes', 'cat-combustible', 'cat-otros'].forEach(cat => {
            const rows = document.getElementById(cat).querySelectorAll('.data-row');
            rows.forEach(row => {
                const cfdiIdInput = row.querySelector('.c-cfdi-id');
                const loadMethodInput = row.querySelector('.c-load-method');
                const inputs = row.querySelectorAll('.cell-input');

                if (inputs[0].value) {
                    let finalMethod = loadMethodInput ? loadMethodInput.value : 'unassigned';
                    if(finalMethod === 'unassigned') finalMethod = 'manual_entry';

                    const lineCcValue = row.querySelector('.c-cc-select').value;

                    lineasArray.push({
                        categoria: cat,
                        cfdi_id: cfdiIdInput ? cfdiIdInput.value : null,
                        load_method: finalMethod,
                        fecha: inputs[0].value,
                        folio: inputs[1].value,
                        descripcion: inputs[2].value,
                        centro_costo: lineCcValue,
                        monto_fiscal: parseFloat(inputs[4].value) || 0,
                        monto_simple: parseFloat(inputs[5].value) || 0,
                        monto_sin: parseFloat(inputs[6].value) || 0,
                        ish: parseFloat(inputs[7].value) || 0,
                        iva: parseFloat(inputs[8].value) || 0,
                        total_linea: parseFloat(row.querySelector('.cell-row-total').textContent.replace(/[^0-9.-]+/g, "")) || 0
                    });
                }
            });
        });

        if (lineasArray.length === 0 && estadoRevision !== 'borrador') { window.showAlert('Debes agregar al menos un gasto a la tabla antes de enviar.', 'error'); return; }

        let totalSubtotal = 0;
        ['sum-fiscal', 'sum-simple', 'sum-propinas'].forEach(id => {
            totalSubtotal += parseFloat(document.getElementById(id).textContent.replace(/[^0-9.-]+/g, "")) || 0;
        });

        const tipoSol = document.getElementById('modal-tipo-solicitud').value;
        const isComprobacion = (tipoSol === 'Comprobacion Electronica' || tipoSol === 'Comprobacion Directa');

        let formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('motivo', document.getElementById('modal-motivo').value.trim());
        formData.append('tipo_solicitud', tipoSol);
        formData.append('tipo_gasto', document.getElementById('modal-tipo-gasto').value);
        formData.append('is_deductible', document.querySelector('input[name="is_deductible"]:checked').value);
        formData.append('advance_id', document.getElementById('modal-advance-id').value);
        formData.append('centro_costo', mainCostCenterSelect.value);
        formData.append('beneficiary_id', document.getElementById('modal-beneficiary-id').value);
        formData.append('depto', document.getElementById('modal-depto').value);
        formData.append('lugar_emision', document.getElementById('modal-lugar').value);
        formData.append('is_draft', estadoRevision === 'borrador');
        formData.append('total_subtotal', totalSubtotal);
        formData.append('total_iva', document.getElementById('sum-iva').textContent.replace(/[^0-9.-]+/g, ""));
        formData.append('total_ish', document.getElementById('sum-ish').textContent.replace(/[^0-9.-]+/g, ""));
        formData.append('total_amount', document.getElementById('sum-total').getAttribute('data-value'));
        formData.append('lineas', JSON.stringify(lineasArray));

        if (isComprobacion) {
            const eAmount = parseFloat(document.getElementById('excess-amount').value) || 0;
            if (eAmount > 0) {
                formData.append('excess_amount', eAmount);
                formData.append('excess_method', document.getElementById('excess-method').value || 'Transferencia');
                formData.append('excess_notes', document.getElementById('excess-notes').value || '');
            }
        }

        existingEvidencePaths.forEach((p, i) => formData.append(`existing_evidences[${i}]`, p));
        evidenciasFiles.forEach((file, index) => { formData.append(`evidencias[${index}]`, file); });

        try {
            Swal.fire({ title: 'Guardando datos...', text: 'Por favor, no cierres esta ventana.', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            let fetchUrl = '{{ route('expense-claims.store') }}';
            if (isEditMode) { fetchUrl = `{{ url('administration/expense-claims/reimbursements') }}/${currentActiveClaimId}/update`; }
            const response = await fetch(fetchUrl, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await response.json();
            if (data.success) {
                window.closeModal();
                await Swal.fire({
                    title: '¡Guardado correctamente!',
                    html: `${data.message}<br><br><strong>Folio: ${data.folio}</strong>`,
                    icon: 'success',
                    confirmButtonColor: '#2d7d46'
                });
                await window.refreshTableData();
            } else {
                window.showAlert(data.message || 'Ocurrió un error al intentar guardar los datos.', 'error');
            }
        } catch (error) {
            window.showAlert('No se pudo conectar al servidor.', 'error');
        }
    };

    /* ==========================================================================
       ── 14. DRAWER ANTICIPOS (CONDICIONADO A ver_notificaciones_anticipos) ──
       ========================================================================== */
    let advancesHistory = [];
    let drawerFilter = 'all';
    let drawerSearchQuery = '';

    window.fetchAdvancesHistory = async function(isInitialLoad = false) {
        if (!userPermissions.ver_notificaciones_anticipos) return;
        try {
            const fetchUrl = `{{ route('expense-claims.advances.history') }}`;
            const response = await fetch(fetchUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();

            if (data.success) {
                const needsInitialLoad = advancesHistory.length === 0;
                advancesHistory = data.data || [];
                window.updateBellBadge();
                window.renderAdvancesDrawer(needsInitialLoad);
            } else {
                console.warn('No se pudo cargar el historial de anticipos.');
            }
        } catch (error) {
            console.error('Error al obtener anticipos:', error);
        }
    };

    window.updateBellBadge = function() {
        const badge = document.getElementById('advance-bell-badge');
        const bell = document.querySelector('.btn-notification-bell');
        if (!badge) return;

        const pendingCount = advancesHistory.filter(a => ['pendiente'].includes(a.status)).length;

        if (pendingCount > 0) {
            badge.textContent = pendingCount > 99 ? '99+' : pendingCount;
            badge.classList.remove('hidden');
            if (bell) bell.classList.add('has-alerts');
        } else {
            badge.classList.add('hidden');
            if (bell) bell.classList.remove('has-alerts');
        }
    };

    window.toggleAdvancesDrawer = function() {
        if (!userPermissions.ver_notificaciones_anticipos) return;
        const drawer = document.getElementById('advances-drawer');
        const overlay = document.getElementById('advances-drawer-overlay');
        if (!drawer || !overlay) return;

        const isOpen = drawer.classList.contains('open');
        if (isOpen) {
            drawer.classList.remove('open');
            overlay.classList.remove('active');
            drawer.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        } else {
            drawer.classList.add('open');
            overlay.classList.add('active');
            drawer.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            if (advancesHistory.length === 0) window.fetchAdvancesHistory(true);
        }
    };

    window.getAdvStatusBadge = function(status) {
        const map = {
            'pendiente': { cls: 'adv-badge-pendiente', icon: 'bx-hourglass', text: 'Pendiente' },
            'aprobado':  { cls: 'adv-badge-aprobado',  icon: 'bx-check-circle', text: 'Aprobado' },
            'rechazado': { cls: 'adv-badge-rechazado', icon: 'bx-x-circle', text: 'Rechazado' }
        };
        const info = map[status] || { cls: 'badge-disabled', icon: 'bx-minus', text: status };
        return `<span class="status-badge ${info.cls}"><i class="bx ${info.icon}"></i> ${info.text}</span>`;
    };

    window.renderAdvancesDrawer = function(isInitialLoad = false) {
        const body = document.getElementById('advances-drawer-body');
        if (!body) return;

        let filtered = advancesHistory.filter(adv => {
            const matchFilter = drawerFilter === 'all' || adv.status === drawerFilter;
            const q = drawerSearchQuery.toLowerCase();
            const matchSearch = !q ||
                (adv.folio_system || '').toLowerCase().includes(q) ||
                (adv.user?.name || '').toLowerCase().includes(q) ||
                (adv.description || '').toLowerCase().includes(q) ||
                (adv.advance_reason || '').toLowerCase().includes(q);
            return matchFilter && matchSearch;
        });

        if (filtered.length === 0) {
            body.innerHTML = `<div class="empty-state"><i class="bx bx-folder-open empty-icon"></i><p class="empty-title">Sin anticipos para mostrar</p><p class="empty-desc">No hay registros que coincidan con el filtro o búsqueda aplicada.</p></div>`;
            return;
        }

        const animationClass = isInitialLoad ? '' : 'fade-in-up';

        body.innerHTML = filtered.map((adv, i) => {
            const [y, m, d] = (adv.advance_date || '').split('T')[0].split('-');
            const fecha = adv.advance_date ? `${d}/${m}/${y}` : 'Sin fecha';
            const userName = adv.user?.name || 'Usuario desconocido';
            const reason = adv.advance_reason || 'Sin motivo';
            const type = adv.advance_type || 'N/A';
            const desc = adv.description || 'Sin descripción adicional.';

            const settleSt = window.getSettlementConfig(adv.settlement_status || 'no_comprobado');

            return `
            <div class="adv-card status-${adv.status} ${animationClass}" style="animation-delay:${!isInitialLoad ? (i * 0.05) : 0}s">
                <div class="adv-card-header-top">
                    <span class="adv-card-folio">${adv.folio_system || 'S/F'}</span>
                    <span class="adv-card-date"><i class="bx bx-calendar"></i> ${fecha}</span>
                </div>

                <div class="adv-card-amount-area">
                    <div class="adv-card-user">
                        <i class="bx bx-user-circle"></i>
                        <span><strong>Solicitante:</strong> ${userName}</span>
                    </div>
                    <span class="adv-card-amount">${window.fmt(parseFloat(adv.amount) || 0)}</span>
                </div>

                <div class="adv-card-meta">
                    <span class="adv-meta-chip"><i class="bx bx-briefcase"></i> ${reason}</span>
                    <span class="adv-meta-chip"><i class="bx bx-credit-card"></i> ${type}</span>
                    ${window.getAdvStatusBadge(adv.status)}
                    <span class="adv-meta-chip" style="background:${settleSt.bg}; color:${settleSt.color}; border: 1px solid ${settleSt.border};"><i class="bx ${settleSt.icon}"></i> ${settleSt.label}</span>
                </div>

                <div class="adv-card-info-block">
                    <strong>Descripción:</strong>
                    <div style="margin-top: 0.35rem;">${desc}</div>
                </div>
            </div>`;
        }).join('');
    };

    document.addEventListener('DOMContentLoaded', function() {
        const bellBtn = document.querySelector('.btn-notification-bell');
        if (bellBtn && !userPermissions.ver_notificaciones_anticipos) {
            bellBtn.classList.add('hidden');
        }

        const drawerSearch = document.getElementById('drawer-search');
        if (drawerSearch) {
            drawerSearch.addEventListener('input', function() {
                drawerSearchQuery = this.value.trim();
                window.renderAdvancesDrawer();
            });
        }

        document.querySelectorAll('#drawer-filter-tabs .drawer-tab').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('#drawer-filter-tabs .drawer-tab').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                drawerFilter = this.dataset.filter;
                window.renderAdvancesDrawer();
            });
        });

        if (userPermissions.ver_notificaciones_anticipos) {
            window.fetchAdvancesHistory(true);
            setInterval(() => window.fetchAdvancesHistory(true), 60000);
        }

        window.renderDashboard();
    });
</script>
@endpush