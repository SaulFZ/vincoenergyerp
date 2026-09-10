<!-- ════════════════════════════════════════════════════════════════════════════
     VISTA BLADE: PANEL DE REEMBOLSOS (reembolsos.blade.php) - CORREGIDO
     ════════════════════════════════════════════════════════════════════════════ -->
@extends('modules.administration.expense-claims.index')

@section('content')
<div class="reimbursements-container">

    {{-- ── DASHBOARD: TARJETAS DE MÉTRICAS GLOBALES ── --}}
    <div class="metrics-grid">
        <div class="metric-card metric-total">
            <div class="metric-icon-wrap"><i class="bx bx-wallet-alt"></i></div>
            <div class="metric-info">
                <span class="metric-label">Acumulado Total</span>
                <span id="metric-total-val" class="metric-value">$0.00</span>
            </div>
        </div>
        <div class="metric-card metric-pending">
            <div class="metric-icon-wrap"><i class="bx bx-hourglass"></i></div>
            <div class="metric-info">
                <span class="metric-label">Pendientes / Validados</span>
                <span id="metric-pending-val" class="metric-value">0</span>
            </div>
            <div class="metric-pill" id="metric-pending-amount">$0.00</div>
        </div>
        <div class="metric-card metric-approved">
            <div class="metric-icon-wrap"><i class="bx bx-check-shield"></i></div>
            <div class="metric-info">
                <span class="metric-label">Aprobados</span>
                <span id="metric-approved-val" class="metric-value">0</span>
            </div>
            <div class="metric-pill" id="metric-approved-amount">$0.00</div>
        </div>
        <div class="metric-card metric-rejected">
            <div class="metric-icon-wrap"><i class="bx bxs-shield-x"></i></div>
            <div class="metric-info">
                <span class="metric-label">Rechazados</span>
                <span id="metric-rejected-val" class="metric-value">0</span>
            </div>
            <div class="metric-pill" id="metric-rejected-amount">$0.00</div>
        </div>
    </div>

    {{-- ── TABLA MAESTRA: HISTORIAL DE SOLICITUDES ── --}}
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
                    <button class="filter-tab" data-filter="Pendiente">Pendiente</button>
                    <button class="filter-tab" data-filter="Validado">Validado</button>
                    <button class="filter-tab" data-filter="Aprobado">Aprobado</button>
                    <button class="filter-tab" data-filter="Rechazado">Rechazado</button>
                </div>

                {{-- BOTÓN EMITIR ANTICIPO --}}
                <button class="btn btn-secondary btn-new-request" onclick="openAdvanceModalForCreate()"
                    aria-label="Pedir Anticipo Operativo">
                    <i class="bx bx-money-withdraw"></i> Solicitar Anticipo
                </button>

                {{-- BOTÓN NUEVA SOLICITUD --}}
                <button class="btn btn-primary btn-new-request" onclick="openModalForCreate()"
                    aria-label="Crear un nuevo trámite">
                    <i class="bx bx-plus-circle"></i> Nueva Solicitud
                </button>
            </div>
        </div>

        <div class="table-scroll">
            <table class="data-table table-animated" id="main-data-table">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Folio Usuario</th>
                        <th>Departamento</th>
                        <th>Solicitante</th>
                        <th>Fecha</th>
                        <th>Motivo</th>
                        <th>Monto</th>
                        <th>Anticipo</th>
                        <th>Revisión</th>
                        <th>Pago</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="dashboard-list">
                    {{-- Las filas se inyectan dinámicamente vía JavaScript con animación en cascada --}}
                </tbody>
            </table>
        </div>

        {{-- ESTADO VACÍO --}}
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
                    <select id="page-size-select" class="page-size-select" onchange="changePageSize()">
                        <option value="5" selected>5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="all">Todos</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ════════════════════════════════════════════════════════════════════════
MODAL FLOTANTE: REGISTRO Y EVALUACIÓN DE COMPROBACIONES
════════════════════════════════════════════════════════════════════════ --}}
<div id="reimbursement-modal" class="modal-bg hidden" aria-hidden="true" role="dialog">
    <div class="modal-box large">

        {{-- Cabecera del Modal --}}
        <div class="modal-header">
            <h2 class="modal-title" id="main-modal-title">
                <i class="bx bx-receipt"></i>
                Formato de <strong>Comprobación</strong>
            </h2>
            <div class="modal-header-actions">
                <button type="button" class="btn btn-secondary" onclick="toggleSatPanel()"
                    title="Extraer datos desde el SAT">
                    <i class="bx bx-barcode"></i> Cargar Factura SAT
                </button>
                <button class="btn-close" onclick="closeModal()" aria-label="Cerrar ventana">
                    <i class="bx bx-x"></i>
                </button>
            </div>
        </div>

        <div class="modal-body" id="modal-body-scroll">

            {{-- PANEL DE ALERTA DE RECHAZO --}}
            <div id="rejection-container" class="rejection-alert hidden">
                <i class="bx bxs-error-circle"></i>
                <div class="rejection-alert-content">
                    <div class="rejection-alert-title">Motivo de Rechazo</div>
                    <div class="rejection-alert-text" id="rejection-text"></div>
                </div>
            </div>

            {{-- CABECERA INFORMATIVA DEL REEMBOLSO --}}
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

                <div class="fh-body">

                    {{-- CONTROLES ESTRUCTURALES GRIDS --}}
                    <div class="fh-grid-controls">

                        {{-- SELECT 1: Tipo de Trámite --}}
                        <div>
                            <label class="input-label">Tipo de Trámite</label>
                            <div class="input-group">
                                <i class="bx bx-layer field-icon"></i>
                                <select id="modal-tipo-solicitud" class="input-field modal-focusable">
                                    <option value="Reembolso" selected>Reembolso</option>
                                    <option value="Comprobacion Electronica">Comprobación Electrónica</option>
                                    <option value="Comprobacion Directa">Comprobación Directa</option>
                                </select>
                            </div>
                        </div>

                        {{-- SELECT 2: Categoría de Gasto --}}
                        <div>
                            <label class="input-label">Categoría del Gasto</label>
                            <div class="input-group">
                                <i class="bx bx-purchase-tag field-icon"></i>
                                <select id="modal-tipo-gasto" class="input-field modal-focusable">
                                    <option value="viaje" selected>Viáticos y Viaje</option>
                                    <option value="operacion">Operaciones y Campo</option>
                                    <option value="otros">Diversos / Otros</option>
                                </select>
                            </div>
                        </div>

                        {{-- SELECT 3: ANTICIPO RELACIONADO --}}
                        <div>
                            <label class="input-label">Anticipo a Comprobar</label>
                            <div class="input-group">
                                <i class="bx bx-link field-icon"></i>
                                <select id="modal-advance-id" class="input-field modal-focusable"
                                    onchange="toggleAdvanceViewButton()">
                                    <option value="">Ninguno</option>
                                </select>
                                <button type="button" id="btn-view-advance" class="btn-input-action hidden"
                                    onclick="openAdvanceFromSelect()">
                                    <i class="bx bx-show"></i> <span>Ver</span>
                                </button>
                            </div>
                        </div>

                        {{-- PILL: Deducible --}}
                        <div>
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

                        {{-- PILL: Capturar Otro Beneficiario --}}
                        <div>
                            <label class="input-label">Capturar Otro Beneficiario</label>
                            <div class="radio-pill-group">
                                <label class="radio-pill-label">
                                    <input type="radio" name="is_delegated" value="1" class="modal-focusable"
                                        onchange="handleDelegationToggle()">
                                    <i class="bx bx-check"></i> Sí
                                </label>
                                <label class="radio-pill-label">
                                    <input type="radio" name="is_delegated" value="0" class="modal-focusable"
                                        onchange="handleDelegationToggle()" checked>
                                    <i class="bx bx-x"></i> No
                                </label>
                            </div>
                        </div>

                    </div>

                    {{-- INFORMACIÓN DE TRAZABILIDAD --}}
                    <div class="fh-grid-4">
                        {{-- BUSCADOR DE USUARIO INTEGRADO --}}
                        <div>
                            <label class="input-label">Nombre del Beneficiario</label>
                            <div class="input-group reimburse-dropdown-container">
                                <i class="bx bx-user field-icon" id="icon-solicitante"></i>
                                <input type="hidden" id="modal-beneficiary-id" value="{{ Auth::id() ?? 1 }}">
                                <input type="text" id="modal-nombre"
                                    value="{{ Auth::user()->name ?? 'Usuario No Definido' }}" class="input-field"
                                    readonly autocomplete="off">
                                <div id="employee-dropdown" class="reimburse-custom-dropdown hidden"></div>
                            </div>
                        </div>

                        <div>
                            <label class="input-label">Área de Adscripción</label>
                            <div class="input-group">
                                <i class="bx bx-buildings field-icon"></i>
                                <input type="text" id="modal-depto" value="Desarrollo de Software" class="input-field"
                                    readonly>
                            </div>
                        </div>

                        {{-- 👇 NUEVO SELECTOR PRINCIPAL DE CENTRO DE COSTOS 👇 --}}
                        <div>
                            <label class="input-label">Centro de Costos</label>
                            <div class="input-group">
                                <i class="bx bx-building-house field-icon"></i>
                                <select id="modal-centro-costos" class="input-field modal-focusable">
                                    <!-- Options generados en JavaScript -->
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="input-label">Motivo de la Erogación</label>
                            <div class="input-group">
                                <i class="bx bx-text field-icon"></i>
                                <input type="text" id="modal-motivo" class="input-field modal-focusable"
                                    placeholder="Ej. Viáticos técnicos a pozo foráneo">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

{{-- PANEL SAT (DISEÑO UX PREMIUM) --}}
            <div id="sat-panel" class="sat-panel hidden">
                <div class="sat-panel-header">
                    <div class="sat-panel-title-area">
                        <div class="sat-icon-box"><i class="bx bx-link-external"></i></div>
                        <div>
                            <h4 class="sat-panel-title">Bóveda Fiscal SAT</h4>
                            <span class="sat-panel-subtitle">Vincule comprobantes XML directamente desde el servidor</span>
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
                    {{-- PESTAÑA: BÚSQUEDA TIPO TYPEAHEAD --}}
                    <div id="sat-tab-uuid" class="sat-content active">
                        <label class="sat-label">Ingrese el Folio Fiscal (UUID) o la Serie del comprobante</label>

                        <div class="sat-search-bar-modern">
                            <i class="bx bx-barcode search-prefix-icon"></i>
                            <input type="text" id="search-uuid" class="sat-input-modern modal-focusable" placeholder="Ej. A102 o 25f60d40-bdd5..." autocomplete="off">
                            <button type="button" id="btn-clear-uuid" class="btn-clear-input hidden" onclick="limpiarBuscadorSat()">
                                <i class="bx bx-x-circle"></i>
                            </button>
                            <button type="button" id="btn-buscar" class="btn-search-modern" onclick="window.buscarFacturaManual()">
                                Buscar
                            </button>

                            {{-- Dropdown de Resultados --}}
                            <div id="uuid-dropdown" class="sat-dropdown hidden"></div>
                        </div>
                    </div>

                    {{-- PESTAÑA: CARGA DE XML --}}
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

                    {{-- CAJA DE RESULTADO (EXITOSO) --}}
                    <div id="sat-result-container" class="sat-result-box hidden">
                        <div class="sat-result-success">
                            <div class="success-icon-wrapper">
                                <i class="bx bx-check"></i>
                            </div>
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
                            <button type="button" class="btn btn-primary btn-integrate" onclick="window.agregarFilaDesdeSAT()">
                                <i class="bx bx-plus"></i> Integrar Gasto
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MATRIZ DINÁMICA DE GASTOS --}}
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

                                {{-- 👇 NUEVA COLUMNA DINÁMICA 👇 --}}
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
                                        <button type="button" class="btn-add-row" onclick="addRow('cat-vuelos')"
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
                                        <button type="button" class="btn-add-row" onclick="addRow('cat-restaurantes')"
                                            title="Agregar Fila de Gasto"><i class="bx bx-plus"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tbody id="cat-combustible">
                            <tr class="cat-row">
                                <td colspan="10" class="cat-row-colspan">
                                    <div class="cat-row-content">
                                        <span><i class="bx bxs-gas-pump"></i> III. Abastecimiento de Combustible</span>
                                        <button type="button" class="btn-add-row" onclick="addRow('cat-combustible')"
                                            title="Agregar Fila de Gasto"><i class="bx bx-plus"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tbody id="cat-otros">
                            <tr class="cat-row">
                                <td colspan="10" class="cat-row-colspan">
                                    <div class="cat-row-content">
                                        <span><i class="bx bx-package"></i> IV. Cargos Varios / Misceláneos</span>
                                        <button type="button" class="btn-add-row" onclick="addRow('cat-otros')"
                                            title="Agregar Fila de Gasto"><i class="bx bx-plus"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PANEL INFERIOR --}}
            <div class="bottom-section">
                <div class="evidence-panel" id="evidence-panel"
                    onclick="document.getElementById('evidence-upload').click()">
                    <i class="bx bxs-file-pdf evidence-icon"></i>
                    <h4 class="evidence-title">Gestor Documental (PDF)</h4>
                    <p class="evidence-desc">Arrastra y suelta tus facturas, tickets y documentación probatoria
                        aquí.<br>Carga máxima de 10MB por archivo unitario.</p>
                    <button type="button" class="btn btn-secondary"
                        onclick="event.stopPropagation(); document.getElementById('evidence-upload').click()">
                        <i class="bx bx-folder-plus"></i> Examinar archivos locales
                    </button>
                    <input type="file" id="evidence-upload" accept=".pdf" multiple class="hidden">
                    <div id="evidence-list" class="evidence-list" onclick="event.stopPropagation()"></div>
                </div>

                <div class="summary-box">
                    <div class="summary-head">
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
                            <span class="sum-total-lbl">TOTAL A REEMBOLSAR</span>
                            <span id="sum-total" class="sum-total-val" data-value="0">$0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <div><span class="modal-footer-note"><i class="bx bx-info-circle"></i> Para garantizar una autorización
                    rápida, asegúrate de adjuntar el PDF de soporte.</span></div>
            <div class="modal-footer-right" id="footer-create">
                <button type="button" class="btn btn-cancel" onclick="closeModal()">Cancelar</button>
                <button type="button" id="btn-borrador" class="btn btn-secondary" onclick="saveDraft()"><i
                        class="bx bx-save"></i> Guardar Borrador</button>
                <button type="button" id="btn-enviar" class="btn btn-primary" onclick="verifyAndSubmit()"><i
                        class="bx bx-send"></i> Emitir Solicitud a Revisión</button>
            </div>
            <div class="modal-footer-right hidden" id="footer-view">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cerrar</button>
            </div>
            <div class="modal-footer-right hidden" id="footer-evaluate">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cerrar</button>
                <button type="button" class="btn btn-fail-solid" onclick="processEvaluation('Rechazado')"><i
                        class="bx bx-x"></i> Rechazar</button>
                <button type="button" class="btn btn-secondary btn-validate-special" id="btn-eval-validate"
                    onclick="processEvaluation('Validado')"><i class="bx bx-list-check"></i> Validar</button>
                <button type="button" class="btn btn-ok-solid" id="btn-eval-approve"
                    onclick="processEvaluation('Aprobado')"><i class="bx bx-check-double"></i> Aprobar</button>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════
NUEVO MODAL: SOLICITAR / VER ANTICIPO DE GASTO OPERATIVO
════════════════════════════════════════════════════════════════════════ --}}
<div id="advance-modal" class="modal-bg hidden" aria-hidden="true" role="dialog">
    <div class="modal-box medium">
        <div class="modal-header">
            <h2 class="modal-title" id="adv-modal-title"><i class="bx bx-money-withdraw"></i> Solicitud de
                <strong>Anticipo</strong>
            </h2>
            <div class="modal-header-actions">
                <button class="btn-close" onclick="closeAdvanceModal()" aria-label="Cerrar ventana"><i
                        class="bx bx-x"></i></button>
            </div>
        </div>

        <div class="modal-body">
            <div class="form-header-card m-bottom-125">
                <div class="fh-info-strip">
                    <div class="fh-info-item folio">
                        <span>Folio del Anticipo</span>
                        <strong id="adv-modal-folio">Nuevo Trámite</strong>
                    </div>
                    <div class="fh-info-item text-right">
                        <span>Estado de Autorización</span>
                        <strong id="adv-modal-status">Generando...</strong>
                    </div>
                </div>
            </div>

            <div class="fh-body-no-pad">
                <div class="fh-grid-2">
                    <div>
                        <label class="input-label">Nombre del Solicitante</label>
                        <div class="input-group">
                            <i class="bx bx-user field-icon"></i>
                            <input type="text" id="adv-user-name" class="input-field" readonly>
                        </div>
                    </div>
                    <div>
                        <label class="input-label">Fecha de Solicitud</label>
                        <div class="input-group">
                            <i class="bx bx-calendar field-icon"></i>
                            <input type="text" id="adv-date-text" class="input-field" readonly>
                        </div>
                    </div>
                </div>

                <div class="fh-grid-2">
                    <div>
                        <label class="input-label">Tipo de Anticipo</label>
                        <div class="input-group">
                            <i class="bx bx-briefcase field-icon"></i>
                            <select id="adv-type" class="input-field adv-focusable">
                                <option value="Viaticos" selected>Viáticos y Hospedaje</option>
                                <option value="Operativos">Gastos Operativos (Campo)</option>
                                <option value="Caja Chica">Fondo de Caja Chica</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="input-label">Monto Solicitado (MXN)</label>
                        <div class="input-group">
                            <i class="bx bx-dollar field-icon"></i>
                            <input type="number" id="adv-amount" class="input-field adv-focusable"
                                placeholder="Ej. 5000.00" min="1" step="0.01">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="input-label">Descripción / Justificación Operativa</label>
                    <div class="input-group">
                        <textarea id="adv-desc" class="input-field adv-focusable"
                            placeholder="Explique para qué se destinarán los fondos solicitados..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <span class="modal-footer-note" id="adv-modal-note"><i class="bx bx-info-circle"></i> Los anticipos
                requieren validación de la gerencia financiera.</span>
            <div class="modal-footer-right" id="adv-footer-create">
                <button type="button" class="btn btn-cancel" onclick="closeAdvanceModal()">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="submitAdvance()"><i class="bx bx-send"></i>
                    Emitir Solicitud</button>
            </div>
            <div class="modal-footer-right hidden" id="adv-footer-view">
                <button type="button" class="btn btn-secondary" onclick="closeAdvanceModal()">Cerrar Vista</button>
            </div>
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
   ── 1. VARIABLES GLOBALES Y ESTADO ──
   ========================================================================== */
const rfcEmpresa = "{{ $rfcEmpresa }}";
const companyEmployees = {!! json_encode($usersList ?? '[]') !!};
const costCentersData = {!! json_encode($costCenters ?? '[]') !!};

const sessionUser = {
    id: {{ Auth::id() ?? 1 }},
    nombre: "{{ Auth::user()->name ?? 'Usuario No Definido' }}",
    depto: "{{ Auth::user()->employee->area->name ?? 'Sin Asignar' }}",
    rfc: "{{ Auth::user()->employee->rfc ?? 'S/N' }}"
};

let currentActiveClaimId = null;
let isEditMode = false;
let tempSatData = null;

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
   ── 2. UTILIDADES Y ALERTAS GLOBALES ──
   ========================================================================== */
window.showAlert = function(msg, type = 'success') {
    const titles = { 'success': '¡Operación Exitosa!', 'error': 'Error en la operación', 'warning': 'Atención Requerida', 'info': 'Información' };
    const colors = { 'success': '#2d7d46', 'error': '#b91c1c', 'warning': '#b45309', 'info': '#1d4ed8' };
    Swal.fire({
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

/* ==========================================================================
   ── 3. CENTRO DE COSTOS Y PROYECTOS ──
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
   ── 4. EVENTOS DE NAVEGACIÓN Y ANIMACIÓN ──
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
    if (!table) return;
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach((row, index) => {
        const delay = initialDelay + index * delayPerRow;
        row.style.animationDelay = `${delay}s`;
        row.style.animation = 'none';
        row.offsetHeight;
        row.style.animation = '';
    });
};

/* ==========================================================================
   ── 5. GESTIÓN DE ANTICIPOS (AJAX) ──
   ========================================================================== */
window.fetchUserAdvances = async function(userId, selectedAdvanceId = null) {
    try {
        const fetchUrl = `{{ url('administration/expense-claims/advances/user') }}/${userId}`;
        const response = await fetch(fetchUrl);
        const data = await response.json();

        const select = document.getElementById('modal-advance-id');
        if(!select) return;
        select.innerHTML = '<option value="">Ninguno</option>';

        data.forEach(adv => {
            const opt = document.createElement('option');
            opt.value = adv.id;
            opt.textContent = adv.folio_system;
            select.appendChild(opt);
        });
        if (selectedAdvanceId) select.value = selectedAdvanceId;
        window.toggleAdvanceViewButton();
    } catch (error) {
        console.error("No se pudieron cargar los anticipos.", error);
    }
};

window.toggleAdvanceViewButton = function() {
    const select = document.getElementById('modal-advance-id');
    const viewBtn = document.getElementById('btn-view-advance');
    if(!select || !viewBtn) return;
    if (select.value) viewBtn.classList.remove('hidden');
    else viewBtn.classList.add('hidden');
};

window.openAdvanceModalForCreate = function() {
    document.getElementById('adv-modal-title').innerHTML = '<i class="bx bx-money-withdraw"></i> Solicitud de <strong>Anticipo</strong>';
    document.getElementById('adv-modal-folio').textContent = 'Asignación Automática';
    document.getElementById('adv-modal-status').textContent = 'Borrador / Pendiente';
    document.getElementById('adv-user-name').value = sessionUser.nombre;
    document.getElementById('adv-date-text').value = todayText;
    document.getElementById('adv-amount').value = '';
    document.getElementById('adv-desc').value = '';
    document.querySelectorAll('.adv-focusable').forEach(el => el.removeAttribute('disabled'));
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
            document.getElementById('adv-modal-status').textContent = adv.status;
            document.getElementById('adv-user-name').value = adv.user.name;
            document.getElementById('adv-date-text').value = new Date(adv.advance_date).toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
            document.getElementById('adv-type').value = adv.advance_type;
            document.getElementById('adv-amount').value = adv.amount;
            document.getElementById('adv-desc').value = adv.description;

            document.querySelectorAll('.adv-focusable').forEach(el => el.setAttribute('disabled', 'true'));
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

window.submitAdvance = async function() {
    const tipo = document.getElementById('adv-type').value;
    const fecha = document.getElementById('adv-date-text').value;
    const monto = document.getElementById('adv-amount').value;
    const desc = document.getElementById('adv-desc').value.trim();

    if (!monto || !desc) { window.showAlert('Todos los campos son obligatorios.', 'warning'); return; }

    let formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('advance_type', tipo);
    formData.append('advance_date', fecha);
    formData.append('amount', monto);
    formData.append('description', desc);

    try {
        Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        const response = await fetch('{{ route('expense-claims.advances.store') }}', { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await response.json();
        if (data.success) {
            Swal.fire({ title: '¡Anticipo Solicitado!', text: data.message + ' Folio Asignado: ' + data.folio, icon: 'success', confirmButtonColor: '#2d7d46' });
            window.closeAdvanceModal();
            window.fetchUserAdvances(sessionUser.id);
        } else { window.showAlert(data.message, 'error'); }
    } catch (error) { window.showAlert('No se pudo enviar la solicitud.', 'error'); }
};

window.openAdvanceFromSelect = function() {
    const selectId = document.getElementById('modal-advance-id').value;
    if (selectId) window.verDetalleAnticipo(selectId);
};

/* ==========================================================================
   ── 6. BUSCADOR DE COLABORADORES Y DELEGACIÓN ──
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

window.selectEmployee = function(emp) {
    modalNombre.value = emp.nombre;
    modalDepto.value = emp.depto;
    beneficiaryId.value = emp.id;
    dropdown.classList.add('hidden');
    window.fetchUserAdvances(emp.id);
    window.showAlert(`El beneficiario del trámite ahora es: <strong>${emp.nombre}</strong>`, 'info');
};

document.addEventListener('click', function(e) {
    if (modalNombre && dropdown && !modalNombre.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});

window.handleDelegationToggle = function() {
    const isDelegated = document.querySelector('input[name="is_delegated"]:checked').value === "1";
    const radioPills = document.querySelectorAll('input[name="is_delegated"]');

    if (isDelegated) {
        if (!radioPills[0].disabled) {
            modalNombre.removeAttribute('readonly');
            modalNombre.value = '';
            modalNombre.focus();
        }
        modalNombre.placeholder = 'Buscar empleado por nombre o apellido...';
        modalNombre.classList.add('reimburse-input-active-search');
        iconSolicitante.className = 'bx bx-search field-icon';
        iconSolicitante.style.color = '#1d4ed8';
    } else {
        modalNombre.setAttribute('readonly', 'true');
        modalNombre.value = sessionUser.nombre;
        modalDepto.value = sessionUser.depto;
        beneficiaryId.value = sessionUser.id;
        modalNombre.classList.remove('reimburse-input-active-search');
        iconSolicitante.className = 'bx bx-user field-icon';
        iconSolicitante.style.color = '#94a3b8';
        dropdown.classList.add('hidden');
        window.fetchUserAdvances(sessionUser.id);
    }
};

/* ==========================================================================
   ── 7. GESTIÓN DE LA TABLA MAESTRA (DASHBOARD) ──
   ========================================================================== */
let requests = {!! isset($requestsData) ? json_encode($requestsData) : '[]' !!};
let currentEvaluateId = null;
let currentPage = 1;
let itemsPerPage = 5;
let activeFilter = 'all';
let searchQuery = '';

const tableSearchInput = document.getElementById('table-search');
if(tableSearchInput) {
    tableSearchInput.addEventListener('input', function() {
        searchQuery = this.value.toLowerCase().trim();
        currentPage = 1;
        window.renderDashboard();
    });
}

document.querySelectorAll('.filter-tab').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        activeFilter = this.dataset.filter;
        currentPage = 1;
        window.renderDashboard();
    });
});

window.changePageSize = function() {
    const val = document.getElementById('page-size-select').value;
    itemsPerPage = val === 'all' ? 999999 : parseInt(val);
    currentPage = 1;
    window.renderDashboard();
};

window.renderDashboard = function() {
    const list = document.getElementById('dashboard-list');
    const emptyState = document.getElementById('empty-state');
    const tableCount = document.getElementById('table-count');
    if(!list) return;
    list.innerHTML = '';

    let totalAcc = 0, pendCount = 0, pendAmt = 0, appCount = 0, appAmt = 0, rejCount = 0, rejAmt = 0;

    requests.forEach(req => {
        if (req.status !== 'Borrador') totalAcc += req.amount;
        if (req.status === 'Pendiente' || req.status === 'Validado') { pendCount++; pendAmt += req.amount; }
        if (req.status === 'Aprobado') { appCount++; appAmt += req.amount; }
        if (req.status === 'Rechazado') { rejCount++; rejAmt += req.amount; }
    });

    if(document.getElementById('metric-total-val')) document.getElementById('metric-total-val').textContent = window.fmt(totalAcc);
    if(document.getElementById('metric-pending-val')) document.getElementById('metric-pending-val').textContent = pendCount;
    if(document.getElementById('metric-approved-val')) document.getElementById('metric-approved-val').textContent = appCount;
    if(document.getElementById('metric-rejected-val')) document.getElementById('metric-rejected-val').textContent = rejCount;
    if(document.getElementById('metric-pending-amount')) document.getElementById('metric-pending-amount').textContent = window.fmt(pendAmt);
    if(document.getElementById('metric-approved-amount')) document.getElementById('metric-approved-amount').textContent = window.fmt(appAmt);
    if(document.getElementById('metric-rejected-amount')) document.getElementById('metric-rejected-amount').textContent = window.fmt(rejAmt);

    let filtered = requests.filter(req => {
        const matchFilter = activeFilter === 'all' || req.status === activeFilter;
        const matchSearch = !searchQuery ||
                            req.motivo.toLowerCase().includes(searchQuery) ||
                            req.folioP.toLowerCase().includes(searchQuery) ||
                            req.folioU.toLowerCase().includes(searchQuery) ||
                            req.nombre.toLowerCase().includes(searchQuery);
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
        let badge = '', badgePago = '', advanceBadge = '';

        if (req.status === 'Aprobado') badge = `<span class="status-badge badge-ok"><i class="bx bx-check-circle"></i> Aprobado</span>`;
        else if (req.status === 'Rechazado') badge = `<span class="status-badge badge-fail"><i class="bx bx-x-circle"></i> Rechazado</span>`;
        else if (req.status === 'Validado') badge = `<span class="status-badge badge-review"><i class="bx bx-list-check"></i> Validado</span>`;
        else if (req.status === 'Borrador') badge = `<span class="status-badge badge-draft"><i class="bx bx-edit-alt"></i> Borrador</span>`;
        else badge = `<span class="status-badge badge-wait"><i class="bx bx-hourglass"></i> Pendiente</span>`;

        if (req.pago === 'Pagado') badgePago = `<span class="status-badge badge-payment-paid"><i class="bx bx-money"></i> Pagado</span>`;
        else if (req.pago === 'Por pagar') badgePago = `<span class="status-badge badge-payment-process"><i class="bx bx-wallet"></i> Por pagar</span>`;
        else if (req.pago === 'Por autorizar') badgePago = `<span class="status-badge badge-payment-auth"><i class="bx bx-user-voice"></i> Por autorizar</span>`;
        else if (req.pago === 'En espera') badgePago = `<span class="status-badge badge-payment-wait"><i class="bx bx-time-five"></i> En espera</span>`;
        else if (req.pago === 'No procede') badgePago = `<span class="status-badge badge-payment-void"><i class="bx bx-block"></i> No procede</span>`;
        else badgePago = `<span class="status-badge badge-disabled"><i class="bx bx-minus"></i> ${req.pago || 'N/A'}</span>`;

        if (req.advance_folio && req.advance_id) {
            let advClass = '';
            if (req.advance_status === 'Pendiente') advClass = 'advance-folio-pending';
            else if (['Aprobado', 'Entregado', 'Comprobado'].includes(req.advance_status)) advClass = 'advance-folio-approved';
            else if (req.advance_status === 'Rechazado') advClass = 'advance-folio-rejected';
            advanceBadge = `<span class="row-folio advance-folio ${advClass}" onclick="window.verDetalleAnticipo(${req.advance_id})" title="Clic para revisar detalles"><i class="bx bx-link"></i> ${req.advance_folio}</span>`;
        } else {
            advanceBadge = `<span class="row-text-empty">Trámite Independiente</span>`;
        }

        const evaluateBtn = (req.status === 'Pendiente' || req.status === 'Validado') ?
            `<button class="btn-icon btn-icon-evaluate" onclick="window.evaluarSolicitud(${req.id})" title="Gestionar Resolución"><i class="bx bx-check-shield"></i></button>` : '';

        let shortName = req.nombre.length > 15 ? req.nombre.substring(0, 15) + '...' : req.nombre;
        let fullMotive = req.motivo || '';
        let words = fullMotive.split(' ');
        let shortMotive = words.length > 3 ? words.slice(0, 3).join(' ') + '...' : fullMotive;

        list.innerHTML += `
        <tr>
            <td><span class="row-folio"><i class="bx bx-hash"></i> ${req.folioP}</span></td>
            <td><span class="row-folio user-folio">${req.folioU}</span></td>
            <td><span class="row-depto">${req.depto}</span></td>
            <td><span class="row-name" title="${req.nombre}">${shortName}</span></td>
            <td><span class="row-date">${req.fecha}</span></td>
            <td><span class="row-motive" title="${fullMotive}">${shortMotive}</span></td>
            <td><div class="row-amount-wrap"><span class="row-amount">${window.fmt(req.amount)}</span><span class="row-amount-label">MXN</span></div></td>
            <td>${advanceBadge}</td>
            <td>${badge}</td>
            <td>${badgePago}</td>
            <td class="cell-actions">
                <div class="actions-wrap">
                    <button class="btn-icon btn-icon-view" onclick="window.verDetalles(${req.id})" title="Inspeccionar Documento o Editar Borrador"><i class="bx bx-show"></i></button>
                    ${evaluateBtn}
                </div>
            </td>
        </tr>`;
    });

    window.renderPagination(totalPages);
    window.animateTableRows('#main-data-table');
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
    btnPrev.onclick = () => { currentPage--; window.renderDashboard(); };
    container.appendChild(btnPrev);

    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.className = `page-btn ${i === currentPage ? 'active' : ''}`;
        btn.textContent = i;
        btn.onclick = () => { currentPage = i; window.renderDashboard(); };
        container.appendChild(btn);
    }

    const btnNext = document.createElement('button');
    btnNext.className = 'page-btn';
    btnNext.innerHTML = '<i class="bx bx-chevron-right"></i>';
    btnNext.disabled = currentPage === totalPages;
    btnNext.onclick = () => { currentPage++; window.renderDashboard(); };
    container.appendChild(btnNext);
};

/* ==========================================================================
   ── 8. PANEL SAT (TYPEAHEAD Y DROPZONE XML UX PREMIUM) ──
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

// Botón de limpiar input
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

        // Mostrar u ocultar el botón X
        if (query.length > 0) btnClearUuid.classList.remove('hidden');
        else btnClearUuid.classList.add('hidden');

        if (query.length < 3) {
            uuidDropdownSat.innerHTML = '';
            uuidDropdownSat.classList.add('hidden');
            return;
        }

        // Estado de carga elegante
        uuidDropdownSat.innerHTML = `
            <div class="sat-dropdown-state loading">
                <i class="bx bx-loader-alt bx-spin"></i>
                <span>Conectando a la bóveda fiscal...</span>
            </div>`;
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
                        uuidDropdownSat.innerHTML = `
                            <div class="sat-dropdown-state">
                                <i class="bx bx-folder-open"></i>
                                <span>No se encontraron facturas con esa serie o UUID.</span>
                            </div>`;
                        return;
                    }

                    // Renderizado UX de Tarjetas de Resultado
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
                            <div class="item-icon-wrap">
                                <i class="bx bx-receipt"></i>
                            </div>
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
                            </div>
                        `;
                        uuidDropdownSat.appendChild(item);
                    });
                } else {
                    uuidDropdownSat.innerHTML = `
                        <div class="sat-dropdown-state">
                            <i class="bx bx-search-alt"></i>
                            <span>No se encontraron resultados en el servidor.</span>
                        </div>`;
                }
            } catch (error) {
                uuidDropdownSat.innerHTML = `
                    <div class="sat-dropdown-state">
                        <i class="bx bx-error" style="color: #ef4444;"></i>
                        <span>Error al intentar conectar con el servidor interno.</span>
                    </div>`;
            }
        }, 500); // 500ms debounce
    });

    // Cerrar al hacer click fuera
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

    // Estado de carga en el botón
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

    // Auto-scroll suave a la caja de resultados
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

    window.calcTotal();
    document.getElementById('sat-result-container').classList.add('hidden');
    document.getElementById('sat-category').value = '';
    tempSatData = null;
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Factura agregada a la matriz.', showConfirmButton: false, timer: 2000 });
};

/* ==========================================================================
   ── 9. GESTOR DOCUMENTAL (EVIDENCIAS PDF) ──
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
    evidenciaPanel.addEventListener('dragover', e => { e.preventDefault(); evidenciaPanel.classList.add('dragover'); });
    evidenciaPanel.addEventListener('dragleave', e => { e.preventDefault(); evidenciaPanel.classList.remove('dragover'); });
    evidenciaPanel.addEventListener('drop', e => {
        e.preventDefault();
        evidenciaPanel.classList.remove('dragover');
        if (e.dataTransfer.files.length) window.procesarArchivosEvidencia(e.dataTransfer.files);
    });
}

window.procesarArchivosEvidencia = function(files) {
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
    if (!listDiv) return;
    listDiv.innerHTML = evidenciasFiles.length > 0 ?
        `<div class="file-grid">${evidenciasFiles.map((f, i) => `
            <div class="file-card">
                <i class="bx bxs-file-pdf file-icon-lg"></i>
                <div class="file-info">
                    <span class="file-name" title="${f.name}">${f.name}</span>
                    <span class="file-size">${window.formatBytes(f.size)}</span>
                </div>
                <button type="button" class="btn-remove-file" onclick="event.stopPropagation(); window.removeFile(${i})">
                    <i class="bx bx-x"></i>
                </button>
            </div>`).join('')}</div>` : '';
};

/* ==========================================================================
   ── 10. LÓGICA DE LA MATRIZ DINÁMICA DE GASTOS ──
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

window.markAsManual = function(element) {
    const row = element.closest('tr');
    if(!row) return;

    const hiddenLoadMethod = row.querySelector('.c-load-method');
    const iconSpan = row.querySelector('.load-method-icon');

    if (hiddenLoadMethod && hiddenLoadMethod.value === 'unassigned') {
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
    tbody.insertAdjacentHTML('beforeend', window.getRowTemplate(cfdiId, loadMethod, ccValue));
    flatpickr(tbody.lastElementChild.querySelector('[data-fp]'), {
        locale: "es", dateFormat: "d/m/Y", allowInput: true, disableMobile: "true"
    });
};

window.removeRow = function(btn) {
    const tbody = btn.closest('tbody');
    if (tbody.querySelectorAll('.data-row').length > 1) {
        btn.closest('tr').remove();
        window.calcTotal();
    } else {
        window.showAlert('Debes mantener al menos una fila en la tabla de gastos.', 'warning');
    }
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
};

/* ==========================================================================
   ── 11. ESTADOS DE MODAL (ABRIR, CERRAR, EDITAR, VER) ──
   ========================================================================== */
window.lockForm = function() {
    document.querySelectorAll('#reimbursement-modal .modal-focusable, #reimbursement-modal .cell-input').forEach(el => el.setAttribute('disabled', 'true'));
    document.querySelectorAll('input[name="is_deductible"], input[name="is_delegated"]').forEach(el => el.disabled = true);
    document.querySelectorAll('.btn-add-row, .btn-remove-row').forEach(el => el.classList.add('hidden'));
    document.getElementById('evidence-panel').style.pointerEvents = 'none';
    const actionBtn = document.querySelector('.modal-header-actions .btn-secondary');
    if(actionBtn) actionBtn.classList.add('hidden');
    document.getElementById('modal-nombre').setAttribute('readonly', 'true');
    document.getElementById('modal-nombre').disabled = true;
    document.getElementById('modal-advance-id').disabled = true;
    window.toggleAdvanceViewButton();
};

window.unlockForm = function() {
    document.querySelectorAll('#reimbursement-modal .modal-focusable, #reimbursement-modal .cell-input').forEach(el => el.removeAttribute('disabled'));
    document.querySelectorAll('input[name="is_deductible"], input[name="is_delegated"]').forEach(el => el.disabled = false);
    document.querySelectorAll('.btn-add-row, .btn-remove-row').forEach(el => el.classList.remove('hidden'));
    document.getElementById('evidence-panel').style.pointerEvents = 'auto';
    const actionBtn = document.querySelector('.modal-header-actions .btn-secondary');
    if(actionBtn) actionBtn.classList.remove('hidden');
    document.getElementById('modal-nombre').disabled = false;
    document.getElementById('modal-advance-id').disabled = false;
    document.getElementById('btn-view-advance').classList.add('hidden');

    const isDelegated = document.querySelector('input[name="is_delegated"]:checked').value === "1";
    if (isDelegated) {
        document.getElementById('modal-nombre').removeAttribute('readonly');
    } else {
        document.getElementById('modal-nombre').setAttribute('readonly', 'true');
    }
};

window.resetModalForm = function() {
    ['cat-vuelos', 'cat-restaurantes', 'cat-combustible', 'cat-otros'].forEach(cat => {
        const rows = document.getElementById(cat).querySelectorAll('.data-row');
        for (let i = 1; i < rows.length; i++) rows[i].remove();
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

    document.getElementById('modal-tipo-solicitud').value = 'Reembolso';
    document.getElementById('modal-tipo-gasto').value = 'viaje';
    document.getElementById('sat-panel').classList.add('hidden');
    document.getElementById('sat-result-container').classList.add('hidden');
    document.getElementById('rejection-container').classList.add('hidden');

    tempSatData = null;
    currentActiveClaimId = null;
    isEditMode = false;
    document.querySelector('input[name="is_delegated"][value="0"]').checked = true;
    window.handleDelegationToggle();

    evidenciasFiles = [];
    window.renderFileList();
    window.actualizarInputFiles();
    window.calcTotal();
    window.unlockForm();
};

window.openModalForCreate = function() {
    window.resetModalForm();
    window.fetchUserAdvances(sessionUser.id);

    document.getElementById('main-modal-title').innerHTML = '<i class="bx bx-receipt"></i> Generación de <strong>Comprobación</strong>';
    document.getElementById('modal-folio-p').innerHTML = '<span class="status-badge badge-draft badge-draft-auto">Asignación Automática</span>';
    document.getElementById('modal-folio-u').innerHTML = '<span class="status-badge badge-draft badge-draft-auto">Automático</span>';
    document.querySelector('input[name="is_deductible"][value="1"]').checked = true;

    document.getElementById('btn-enviar').innerHTML = '<i class="bx bx-send"></i> Enviar a Revisión';
    document.getElementById('btn-borrador').classList.remove('hidden');
    document.getElementById('footer-create').classList.remove('hidden');
    document.getElementById('footer-view').classList.add('hidden');
    document.getElementById('footer-evaluate').classList.add('hidden');
    document.getElementById('reimbursement-modal').classList.remove('hidden');

    ['cat-vuelos', 'cat-restaurantes', 'cat-combustible', 'cat-otros'].forEach(cat => {
        if (document.getElementById(cat).querySelectorAll('.data-row').length === 0) {
            window.addRow(cat);
        }
    });
};

window.fetchAndPopulateClaim = async function(id) {
    Swal.fire({ title: 'Cargando información...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    try {
        const fetchUrl = `{{ url('administration/expense-claims/reimbursements') }}/${id}`;
        const response = await fetch(fetchUrl);
        const res = await response.json();

        if (res.success) {
            const claim = res.data;
            currentActiveClaimId = claim.id;

            document.getElementById('rejection-container').classList.add('hidden');
            if (claim.status_review === 'Rechazado' && claim.logs && claim.logs.length > 0) {
                const rejectLog = claim.logs.find(log => log.new_status === 'Rechazado');
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
            document.getElementById('modal-depto').value = claim.beneficiary.employee?.area?.name || 'Sin asignar';
            document.getElementById('modal-beneficiary-id').value = claim.user_id;

            const headerVal = claim.cost_center_id
                            ? (claim.project_id ? `PRJ_${claim.project_id}_${claim.cost_center_id}` : `CC_${claim.cost_center_id}`)
                            : 'VARIOS';
            mainCostCenterSelect.value = headerVal;
            window.toggleVariosMode();

            document.getElementById('modal-motivo').value = claim.motive;
            document.getElementById('modal-tipo-solicitud').value = claim.request_type || 'Reembolso';
            document.getElementById('modal-tipo-gasto').value = claim.category;
            document.querySelector(`input[name="is_deductible"][value="${claim.is_deductible ? '1' : '0'}"]`).checked = true;

            await window.fetchUserAdvances(claim.user_id, claim.expense_advance_id);

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
            });

            ['cat-vuelos', 'cat-restaurantes', 'cat-combustible', 'cat-otros'].forEach(cat => {
                if (document.getElementById(cat).querySelectorAll('.data-row').length === 0) {
                    window.addRow(cat);
                }
            });

            window.calcTotal();
            Swal.close();
            return claim;
        }
    } catch (error) {
        window.showAlert('Error al cargar la información.', 'error');
    }
};

window.verDetalles = async function(id) {
    window.resetModalForm();
    const claim = await window.fetchAndPopulateClaim(id);
    if (!claim) return;

    document.getElementById('main-modal-title').innerHTML = `<i class="bx bx-search-alt"></i> Revisión del <strong>Folio: ${claim.folio_system}</strong>`;
    window.lockForm();

    document.getElementById('footer-create').classList.add('hidden');
    document.getElementById('footer-view').classList.remove('hidden');
    document.getElementById('footer-evaluate').classList.add('hidden');

    const viewFooter = document.getElementById('footer-view');
    viewFooter.innerHTML = '<button type="button" class="btn btn-secondary" onclick="window.closeModal()">Cerrar</button>';

    if (claim.status_review === 'Borrador' || claim.status_review === 'Rechazado') {
        const btnEdit = document.createElement('button');
        btnEdit.type = 'button';
        btnEdit.className = 'btn btn-primary';
        btnEdit.innerHTML = '<i class="bx bx-edit"></i> ' + (claim.status_review === 'Rechazado' ? 'Corregir y Reenviar' : 'Continuar Borrador');
        btnEdit.onclick = () => window.habilitarEdicion(claim.status_review);
        viewFooter.appendChild(btnEdit);
    }
    document.getElementById('reimbursement-modal').classList.remove('hidden');
};

window.habilitarEdicion = function(status) {
    isEditMode = true;
    window.unlockForm();
    document.getElementById('main-modal-title').innerHTML = `<i class="bx bx-edit"></i> Editando <strong>Folio: ${document.getElementById('modal-folio-p').textContent}</strong>`;
    document.getElementById('footer-view').classList.add('hidden');
    document.getElementById('footer-create').classList.remove('hidden');

    const btnEnviar = document.getElementById('btn-enviar');
    if (status === 'Rechazado') {
        btnEnviar.innerHTML = '<i class="bx bx-send"></i> Enviar de nuevo';
        document.getElementById('btn-borrador').classList.add('hidden');
    } else {
        btnEnviar.innerHTML = '<i class="bx bx-send"></i> Enviar a Revisión';
        document.getElementById('btn-borrador').classList.remove('hidden');
    }
};

window.evaluarSolicitud = async function(id) {
    window.resetModalForm();
    const claim = await window.fetchAndPopulateClaim(id);
    if (!claim) return;

    document.getElementById('main-modal-title').innerHTML = '<i class="bx bx-check-shield"></i> Evaluar Trámite';
    currentEvaluateId = id;
    window.lockForm();

    const btnValidate = document.getElementById('btn-eval-validate');
    const btnApprove = document.getElementById('btn-eval-approve');

    if (claim.status_review === 'Pendiente') {
        btnValidate.classList.remove('hidden');
        btnApprove.classList.add('hidden');
    } else if (claim.status_review === 'Validado') {
        btnValidate.classList.add('hidden');
        btnApprove.classList.remove('hidden');
    }

    document.getElementById('footer-create').classList.add('hidden');
    document.getElementById('footer-view').classList.add('hidden');
    document.getElementById('footer-evaluate').classList.remove('hidden');
    document.getElementById('reimbursement-modal').classList.remove('hidden');
};

window.closeModal = function() {
    document.getElementById('reimbursement-modal').classList.add('hidden');
};

window.processEvaluation = function(status) {
    if (!currentEvaluateId) return;
    let actionText = status === 'Aprobado' ? 'Aprobar y Autorizar Pago' : (status === 'Validado' ? 'Dar Visto Bueno (Validar)' : 'Rechazar Trámite');
    let confirmColor = status === 'Aprobado' ? '#2d7d46' : (status === 'Validado' ? '#1d4ed8' : '#b91c1c');

    if (status === 'Rechazado') {
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
            inputValidator: (value) => {
                if (!value.trim()) return '¡Debes escribir un motivo de rechazo!';
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.updateStatus(currentEvaluateId, status, result.value);
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
                window.updateStatus(currentEvaluateId, status, null);
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
            Swal.fire('¡Listo!', data.message, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            window.showAlert(data.message, 'error');
        }
    } catch (error) {
        window.showAlert('Hubo un error al intentar guardar los cambios.', 'error');
    }
};

/* ==========================================================================
   ── 12. ENVÍO FINAL Y GUARDADO ──
   ========================================================================== */
window.verifyAndSubmit = function() {
    const nombreBen = document.getElementById('modal-nombre').value.trim();
    const motivo = document.getElementById('modal-motivo').value.trim();
    const centroCosto = mainCostCenterSelect.value;
    const total = parseFloat(document.getElementById('sum-total').getAttribute('data-value'));

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
    if (total <= 0) { window.showAlert('El monto total a reembolsar no puede ser cero.', 'error'); return; }
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
        if (result.isConfirmed) window.procesarEnvio('Pendiente', 'En espera');
    });
};

window.saveDraft = function() {
    const nombreBen = document.getElementById('modal-nombre').value.trim();
    const motivo = document.getElementById('modal-motivo').value.trim();
    if (!nombreBen || !motivo) { window.showAlert('Para guardar un borrador, necesitas al menos seleccionar al empleado y escribir un motivo.', 'warning'); return; }
    window.procesarEnvio('Borrador', 'N/A');
};

window.procesarEnvio = async function(estadoRevision, estadoPago) {
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

    if (lineasArray.length === 0 && estadoRevision !== 'Borrador') { window.showAlert('Debes agregar al menos un gasto a la tabla antes de enviar.', 'error'); return; }

    let totalSubtotal = 0;
    ['sum-fiscal', 'sum-simple', 'sum-propinas'].forEach(id => {
        totalSubtotal += parseFloat(document.getElementById(id).textContent.replace(/[^0-9.-]+/g, "")) || 0;
    });

    let formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('motivo', document.getElementById('modal-motivo').value.trim());
    formData.append('tipo_solicitud', document.getElementById('modal-tipo-solicitud').value);
    formData.append('tipo_gasto', document.getElementById('modal-tipo-gasto').value);
    formData.append('is_deductible', document.querySelector('input[name="is_deductible"]:checked').value);
    formData.append('advance_id', document.getElementById('modal-advance-id').value);
    formData.append('centro_costo', mainCostCenterSelect.value);
    formData.append('beneficiary_id', document.getElementById('modal-beneficiary-id').value);
    formData.append('depto', document.getElementById('modal-depto').value);
    formData.append('lugar_emision', document.getElementById('modal-lugar').value);
    formData.append('is_draft', estadoRevision === 'Borrador');
    formData.append('total_subtotal', totalSubtotal);
    formData.append('total_iva', document.getElementById('sum-iva').textContent.replace(/[^0-9.-]+/g, ""));
    formData.append('total_ish', document.getElementById('sum-ish').textContent.replace(/[^0-9.-]+/g, ""));
    formData.append('total_amount', document.getElementById('sum-total').getAttribute('data-value'));
    formData.append('lineas', JSON.stringify(lineasArray));

    evidenciasFiles.forEach((file, index) => { formData.append(`evidencias[${index}]`, file); });

    try {
        Swal.fire({ title: 'Guardando datos...', text: 'Por favor, no cierres esta ventana.', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        let fetchUrl = '{{ route('expense-claims.store') }}';
        if (isEditMode) { fetchUrl = `{{ url('administration/expense-claims/reimbursements') }}/${currentActiveClaimId}/update`; }
        const response = await fetch(fetchUrl, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await response.json();
        if (data.success) {
            Swal.fire({ title: '¡Guardado correctamente!', html: `${data.message}<br><br><strong>Folio: ${data.folio}</strong>`, icon: 'success', confirmButtonColor: '#2d7d46' });
            window.closeModal();
            setTimeout(() => window.location.reload(), 2000);
        } else { window.showAlert(data.message || 'Ocurrió un error al intentar guardar los datos.', 'error'); }
    } catch (error) { window.showAlert('No se pudo conectar al servidor.', 'error'); }
};

document.addEventListener('DOMContentLoaded', function() {
    window.renderDashboard();
});
</script>
@endpush
