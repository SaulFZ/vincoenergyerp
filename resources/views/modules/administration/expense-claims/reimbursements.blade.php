{{-- ════════════════════════════════════════════════════════════════════════════
     VISTA BLADE: PANEL DE REEMBOLSOS (reembolsos.blade.php)
     ════════════════════════════════════════════════════════════════════════════ --}}
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
                    <button class="btn btn-secondary btn-new-request" onclick="openAdvanceModalForCreate()" aria-label="Pedir Anticipo Operativo">
                        <i class="bx bx-money-withdraw"></i> Solicitar Anticipo
                    </button>

                    {{-- BOTÓN NUEVA SOLICITUD --}}
                    <button class="btn btn-primary btn-new-request" onclick="openModalForCreate()" aria-label="Crear un nuevo trámite">
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
                <p class="empty-desc">No hay solicitudes que coincidan con los criterios de búsqueda o filtro aplicados en este momento.</p>
            </div>

            <div class="table-footer">
                <div class="table-footer-left">
                    <span id="table-count" class="table-count-label">0 solicitudes registradas</span>
                </div>

                {{-- PAGINACIÓN CONTROLES CENTRALES --}}
                <div id="pagination-controls" class="pagination-controls table-footer-center"></div>

                {{-- SELECTOR DE PÁGINAS DERECHA --}}
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
                                    <select id="modal-advance-id" class="input-field modal-focusable" onchange="toggleAdvanceViewButton()">
                                        <option value="">Ninguno (Gasto Independiente)</option>
                                    </select>
                                    <button type="button" id="btn-view-advance" class="btn-input-action hidden" onclick="openAdvanceFromSelect()">
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
                                        <input type="radio" name="is_delegated" value="1" class="modal-focusable" onchange="handleDelegationToggle()">
                                        <i class="bx bx-check"></i> Sí
                                    </label>
                                    <label class="radio-pill-label">
                                        <input type="radio" name="is_delegated" value="0" class="modal-focusable" onchange="handleDelegationToggle()" checked>
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
                                    <input type="text" id="modal-nombre" value="{{ Auth::user()->name ?? 'Usuario No Definido' }}" class="input-field" readonly autocomplete="off">
                                    <div id="employee-dropdown" class="reimburse-custom-dropdown hidden"></div>
                                </div>
                            </div>

                            <div>
                                <label class="input-label">Área de Adscripción</label>
                                <div class="input-group">
                                    <i class="bx bx-buildings field-icon"></i>
                                    <input type="text" id="modal-depto" value="Desarrollo de Software" class="input-field" readonly>
                                </div>
                            </div>
                            <div>
                                <label class="input-label">Centro de Costos (Imputación)</label>
                                <div class="input-group">
                                    <i class="bx bx-building-house field-icon"></i>
                                    <select id="modal-centro-costos" class="input-field modal-focusable">
                                        <option value="" disabled selected>Seleccione código...</option>
                                        <option value="VNC-OP-01">VNC-OP-01 | Dir. de Operaciones</option>
                                        <option value="VNC-TI-02">VNC-TI-02 | Tecnologías de Información</option>
                                        <option value="VNC-QH-03">VNC-QH-03 | Dir. de Calidad y QHSE</option>
                                        <option value="VNC-AD-04">VNC-AD-04 | Administración y Finanzas</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="input-label">Motivo de la Erogación</label>
                                <div class="input-group">
                                    <i class="bx bx-text field-icon"></i>
                                    <input type="text" id="modal-motivo" class="input-field modal-focusable" placeholder="Ej. Viáticos técnicos a pozo foráneo">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PANEL SAT CON PESTAÑAS --}}
                <div id="sat-panel" class="sat-panel hidden">
                    <div class="sat-panel-header">
                        <h4 class="sat-panel-title"><i class="bx bx-link-external"></i> Vinculación SAT</h4>
                        <div class="sat-tabs">
                            <button type="button" class="sat-tab active" data-target="sat-tab-uuid"><i class="bx bx-search-alt"></i> Buscar UUID</button>
                            <button type="button" class="sat-tab" data-target="sat-tab-xml"><i class="bx bx-upload"></i> Cargar XML</button>
                        </div>
                    </div>

                    <div class="sat-panel-body">
                        <div id="sat-tab-uuid" class="sat-content active">
                            <label class="sat-label">Folio Fiscal (UUID) del comprobante SAT</label>
                            <div class="sat-search-group">
                                <div class="sat-input-wrap">
                                    <i class="bx bx-barcode"></i>
                                    <input type="text" id="search-uuid" class="sat-input modal-focusable" placeholder="Buscar por últimos dígitos, Folio o Proveedor..." autocomplete="off">
                                    <div id="uuid-dropdown" class="sat-dropdown hidden"></div>
                                </div>
                                <button type="button" id="btn-buscar" class="btn btn-primary" onclick="buscarFactura()"><i class="bx bx-search"></i> Buscar</button>
                            </div>
                        </div>

                        <div id="sat-tab-xml" class="sat-content hidden">
                            <div id="drop-zone" class="sat-drop-zone">
                                <i class="bx bx-cloud-upload"></i>
                                <p>Arrastra tu archivo .XML aquí o haz clic para examinar</p>
                                <small>El sistema extraerá automáticamente el UUID, RFC y Fecha.</small>
                                <input type="file" id="xml-input" accept=".xml" class="hidden">
                            </div>
                        </div>

                        <div id="sat-result-container" class="sat-result-box hidden">
                            <div class="sat-result-success">
                                <i class="bx bx-check-circle"></i>
                                <div>
                                    <strong>¡Comprobante Identificado Exitosamente!</strong>
                                    <span id="sat-result-uuid" class="d-block text-xs text-slate-400"></span>
                                </div>
                            </div>
                            <div class="sat-result-actions">
                                <div class="input-group">
                                    <i class="bx bx-purchase-tag-alt field-icon"></i>
                                    <select id="sat-category" class="input-field">
                                        <option value="" disabled selected>¿A qué categoría pertenece esta factura?</option>
                                        <option value="cat-vuelos">Transportación, Vuelos y Peajes</option>
                                        <option value="cat-restaurantes">Consumo de Alimentos y Restaurantes</option>
                                        <option value="cat-combustible">Abastecimiento de Combustible</option>
                                        <option value="cat-otros">Cargos Varios / Misceláneos</option>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-primary" onclick="agregarFilaDesdeSAT()"><i class="bx bx-plus"></i> Integrar Gasto a Matriz</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MATRIZ DINÁMICA DE GASTOS --}}
                <div class="expense-card">
                    <div class="expense-card-header">
                        <span class="expense-card-title"><i class="bx bx-table"></i> Desglose y Análisis Analítico de Gastos</span>
                    </div>
                    <div class="table-scroll">
                        <table class="expense-table">
                            <thead>
                                <tr class="th-group">
                                    <th colspan="3" class="text-left">Comprobante Identificador</th>
                                    <th colspan="3" class="th-importes">Importes Subtotales</th>
                                    <th colspan="2">Retenciones / Impuestos</th>
                                    <th rowspan="2" class="th-total-header">Monto Total</th>
                                    <th rowspan="2" class="th-total-empty"></th>
                                </tr>
                                <tr class="th-cols">
                                    <th class="th-w-140">Fecha Factura</th>
                                    <th class="th-w-100">Folio/Num. Fac.</th>
                                    <th>Descripción Comercial</th>
                                    <th class="th-w-90">Comp. Fiscal<br>(PDF + XML)</th>
                                    <th class="th-w-90">Comp. Simple<br>No Fiscal</th>
                                    <th class="th-w-90">Sin Comp.<br>y Propinas</th>
                                    <th class="th-w-80">I.S.H.<br>Otros Imp.</th>
                                    <th class="th-w-75">I.V.A.</th>
                                </tr>
                            </thead>
                            <tbody id="cat-vuelos">
                                <tr class="cat-row">
                                    <td colspan="10">
                                        <div class="cat-row-content">
                                            <span><i class="bx bxs-plane-alt"></i> I. Transportación, Vuelos y Peajes</span>
                                            <button type="button" class="btn-add-row" onclick="addRow('cat-vuelos')" title="Agregar Fila de Gasto"><i class="bx bx-plus"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody id="cat-restaurantes">
                                <tr class="cat-row">
                                    <td colspan="10">
                                        <div class="cat-row-content">
                                            <span><i class="bx bx-restaurant"></i> II. Consumo de Alimentos y Restaurantes</span>
                                            <button type="button" class="btn-add-row" onclick="addRow('cat-restaurantes')" title="Agregar Fila de Gasto"><i class="bx bx-plus"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody id="cat-combustible">
                                <tr class="cat-row">
                                    <td colspan="10">
                                        <div class="cat-row-content">
                                            <span><i class="bx bxs-gas-pump"></i> III. Abastecimiento de Combustible</span>
                                            <button type="button" class="btn-add-row" onclick="addRow('cat-combustible')" title="Agregar Fila de Gasto"><i class="bx bx-plus"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody id="cat-otros">
                                <tr class="cat-row">
                                    <td colspan="10">
                                        <div class="cat-row-content">
                                            <span><i class="bx bx-package"></i> IV. Cargos Varios / Misceláneos</span>
                                            <button type="button" class="btn-add-row" onclick="addRow('cat-otros')" title="Agregar Fila de Gasto"><i class="bx bx-plus"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- PANEL INFERIOR --}}
                <div class="bottom-section">
                    <div class="evidence-panel" id="evidence-panel" onclick="document.getElementById('evidence-upload').click()">
                        <i class="bx bxs-file-pdf evidence-icon"></i>
                        <h4 class="evidence-title">Gestor Documental (PDF)</h4>
                        <p class="evidence-desc">Arrastra y suelta tus facturas, tickets y documentación probatoria aquí.<br>Carga máxima de 10MB por archivo unitario.</p>
                        <button type="button" class="btn btn-secondary" onclick="event.stopPropagation(); document.getElementById('evidence-upload').click()">
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
                            <div class="summary-row"><span class="sum-lbl">Gastos Fiscales (XML+PDF):</span><span id="sum-fiscal" class="sum-val">$0.00</span></div>
                            <div class="summary-row"><span class="sum-lbl">Gastos No Fiscales (Notas):</span><span id="sum-simple" class="sum-val">$0.00</span></div>
                            <div class="summary-row"><span class="sum-lbl">Sin Comprobante / Propinas:</span><span id="sum-propinas" class="sum-val">$0.00</span></div>
                            <div class="summary-row"><span class="sum-lbl">Impuesto (I.V.A.):</span><span id="sum-iva" class="sum-val">$0.00</span></div>
                            <div class="summary-row"><span class="sum-lbl">I.S.H. y Retenciones:</span><span id="sum-ish" class="sum-val">$0.00</span></div>
                            <div class="sum-total-row">
                                <span class="sum-total-lbl">TOTAL A REEMBOLSAR</span>
                                <span id="sum-total" class="sum-total-val" data-value="0">$0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <div><span class="modal-footer-note"><i class="bx bx-info-circle"></i> Para garantizar una autorización rápida, asegúrate de adjuntar el PDF de soporte.</span></div>
                <div class="modal-footer-right" id="footer-create">
                    <button type="button" class="btn btn-cancel" onclick="closeModal()">Cancelar</button>
                    <button type="button" id="btn-borrador" class="btn btn-secondary" onclick="saveDraft()"><i class="bx bx-save"></i> Guardar Borrador</button>
                    <button type="button" id="btn-enviar" class="btn btn-primary" onclick="verifyAndSubmit()"><i class="bx bx-send"></i> Emitir Solicitud a Revisión</button>
                </div>
                <div class="modal-footer-right hidden" id="footer-view">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cerrar</button>
                </div>
                <div class="modal-footer-right hidden" id="footer-evaluate">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cerrar</button>
                    <button type="button" class="btn btn-fail-solid" onclick="processEvaluation('Rechazado')"><i class="bx bx-x"></i> Rechazar</button>
                    <button type="button" class="btn btn-secondary btn-validate-special" id="btn-eval-validate" onclick="processEvaluation('Validado')"><i class="bx bx-list-check"></i> Validar</button>
                    <button type="button" class="btn btn-ok-solid" id="btn-eval-approve" onclick="processEvaluation('Aprobado')"><i class="bx bx-check-double"></i> Aprobar</button>
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
                <h2 class="modal-title" id="adv-modal-title"><i class="bx bx-money-withdraw"></i> Solicitud de <strong>Anticipo</strong></h2>
                <div class="modal-header-actions">
                    <button class="btn-close" onclick="closeAdvanceModal()" aria-label="Cerrar ventana"><i class="bx bx-x"></i></button>
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
                                <input type="number" id="adv-amount" class="input-field adv-focusable" placeholder="Ej. 5000.00" min="1" step="0.01">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="input-label">Descripción / Justificación Operativa</label>
                        <div class="input-group">
                            <textarea id="adv-desc" class="input-field adv-focusable" placeholder="Explique para qué se destinarán los fondos solicitados..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <span class="modal-footer-note" id="adv-modal-note"><i class="bx bx-info-circle"></i> Los anticipos requieren validación de la gerencia financiera.</span>
                <div class="modal-footer-right" id="adv-footer-create">
                    <button type="button" class="btn btn-cancel" onclick="closeAdvanceModal()">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="submitAdvance()"><i class="bx bx-send"></i> Emitir Solicitud</button>
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
/* ── VARIABLES REALES DESDE EL BACKEND Y ESTADO GLOBAL ── */
/* ── VARIABLES REALES DESDE EL BACKEND Y ESTADO GLOBAL ── */
const rfcEmpresa = "{{ $rfcEmpresa }}";
const companyEmployees = {!! json_encode($usersList) !!};

const sessionUser = {
    id: {{ Auth::id() ?? 1 }},
    nombre: "{{ Auth::user()->name ?? 'Usuario No Definido' }}",
    depto: "{{ Auth::user()->employee->area->name ?? 'Sin Asignar' }}",
    rfc: "{{ Auth::user()->employee->rfc ?? 'S/N' }}"
};

let currentActiveClaimId = null;
let isEditMode = false;
let tempSatData = null; // Guardará el UUID/XML temporalmente para inyección

document.getElementById('company-rfc').textContent = rfcEmpresa;

/* ── ELEMENTOS DEL DOM PARA BENEFICIARIOS ── */
const modalNombre = document.getElementById('modal-nombre');
const modalDepto = document.getElementById('modal-depto');
const beneficiaryId = document.getElementById('modal-beneficiary-id');
const dropdown = document.getElementById('employee-dropdown');
const iconSolicitante = document.getElementById('icon-solicitante');

/* ── FECHAS GLOBALES FORMATO TEXTO ── */
const todayText = new Date().toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
document.getElementById('modal-fecha-hoy').textContent = new Date().toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

/* ── ALERTA CENTRALIZADA NORMAL (SWEETALERT2) ── */
function showAlert(msg, type = 'success') {
    const titles = {
        'success': '¡Operación Exitosa!',
        'error': 'Error de Validación',
        'warning': 'Atención Requerida',
        'info': 'Información'
    };
    const colors = {
        'success': '#2d7d46',
        'error': '#b91c1c',
        'warning': '#b45309',
        'info': '#1d4ed8'
    };
    Swal.fire({
        title: `<span style="font-family:'Poppins', sans-serif;">${titles[type]}</span>`,
        html: `<span style="font-family:'Poppins', sans-serif; font-size:14px; color:#64748b;">${msg}</span>`,
        icon: type,
        confirmButtonColor: colors[type],
        confirmButtonText: `<span style="font-family:'Poppins', sans-serif; font-weight:600;">Entendido</span>`
    });
}

/* ── COLA DE EVENTOS DE NAVEGACIÓN (Accesibilidad) ── */
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

/* ── FUNCIÓN DE ANIMACIÓN EN CASCADA (UI) ── */
function animateTableRows(tableSelector, delayPerRow = 0.04, initialDelay = 0) {
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
}

/* ── GESTIÓN DE ANTICIPOS (AJAX Y LÓGICA ASÍNCRONA) ── */
async function fetchUserAdvances(userId, selectedAdvanceId = null) {
    try {
        const fetchUrl = `{{ url('administration/expense-claims/advances/user') }}/${userId}`;
        const response = await fetch(fetchUrl);
        const data = await response.json();

        const select = document.getElementById('modal-advance-id');
        select.innerHTML = '<option value="">Ninguno (Gasto Independiente)</option>';

        data.forEach(adv => {
            const opt = document.createElement('option');
            opt.value = adv.id;
            opt.textContent = adv.folio_system;
            select.appendChild(opt);
        });
        if (selectedAdvanceId) select.value = selectedAdvanceId;
        toggleAdvanceViewButton();
    } catch (error) {
        console.error("No se pudieron cargar los anticipos.", error);
    }
}

function toggleAdvanceViewButton() {
    const selectVal = document.getElementById('modal-advance-id').value;
    const viewBtn = document.getElementById('btn-view-advance');
    if (selectVal) {
        viewBtn.classList.remove('hidden');
    } else {
        viewBtn.classList.add('hidden');
    }
}

function openAdvanceModalForCreate() {
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
}

async function verDetalleAnticipo(advanceId) {
    if (!advanceId) return;
    Swal.fire({
        title: 'Cargando Detalles...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
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
            showAlert('No se encontró el anticipo solicitado en los registros del sistema.', 'error');
        }
    } catch (error) {
        showAlert('Error al cargar la información del anticipo. Verifique su conexión de red.', 'error');
    }
}

function closeAdvanceModal() {
    document.getElementById('advance-modal').classList.add('hidden');
}

async function submitAdvance() {
    const tipo = document.getElementById('adv-type').value;
    const fecha = document.getElementById('adv-date-text').value;
    const monto = document.getElementById('adv-amount').value;
    const desc = document.getElementById('adv-desc').value.trim();

    if (!monto || !desc) {
        showAlert('Todos los campos son obligatorios para generar el anticipo.', 'warning');
        return;
    }

    let formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('advance_type', tipo);
    formData.append('advance_date', fecha);
    formData.append('amount', monto);
    formData.append('description', desc);

    try {
        Swal.fire({
            title: 'Procesando...',
            text: 'Registrando la solicitud de fondos operativos.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        const response = await fetch('{{ route('expense-claims.advances.store') }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();

        if (data.success) {
            Swal.fire({
                title: '¡Anticipo Solicitado!',
                text: data.message + ' Folio Asignado: ' + data.folio,
                icon: 'success',
                confirmButtonColor: '#2d7d46'
            });
            closeAdvanceModal();
            fetchUserAdvances(sessionUser.id);
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Error al conectar con el servidor para emitir el anticipo.', 'error');
    }
}

function openAdvanceFromSelect() {
    const selectId = document.getElementById('modal-advance-id').value;
    if (selectId) verDetalleAnticipo(selectId);
}

/* ── GESTIÓN INTEGRADA DEL BUSCADOR DE COLABORADORES (FEEDBACK VISUAL) ── */
modalNombre.addEventListener('input', function() {
    if (modalNombre.hasAttribute('readonly') || modalNombre.disabled) return;
    const query = this.value.toLowerCase().trim();

    if (query.length < 2) {
        dropdown.innerHTML = '';
        dropdown.classList.add('hidden');
        return;
    }

    dropdown.innerHTML = '<div class="reimburse-dropdown-searching"><i class="bx bx-loader-alt bx-spin"></i> Buscando en el padrón de personal...</div>';
    dropdown.classList.remove('hidden');

    const results = companyEmployees.filter(emp => emp.nombre.toLowerCase().includes(query));

    setTimeout(() => {
        dropdown.innerHTML = '';
        if (results.length > 0) {
            results.forEach(emp => {
                const item = document.createElement('div');
                item.className = 'reimburse-dropdown-item';
                item.innerHTML = `<strong>${emp.nombre}</strong><small>${emp.depto}</small>`;
                item.onclick = () => selectEmployee(emp);
                dropdown.appendChild(item);
            });
        } else {
            dropdown.innerHTML = '<div class="reimburse-dropdown-empty"><i class="bx bx-search-alt"></i> No se encontraron coincidencias en la nómina</div>';
        }
    }, 180);
});

function selectEmployee(emp) {
    modalNombre.value = emp.nombre;
    modalDepto.value = emp.depto;
    beneficiaryId.value = emp.id;
    dropdown.classList.add('hidden');
    fetchUserAdvances(emp.id);
    showAlert(`El beneficiario titular del trámite ha sido actualizado a: <strong>${emp.nombre}</strong>`, 'info');
}

document.addEventListener('click', function(e) {
    if (!modalNombre.contains(e.target) && !dropdown.contains(e.target))
        dropdown.classList.add('hidden');
});

/* ── GESTIÓN DE INTERRUPTORES DE FORMULARIO (DELEGACIÓN) ── */
function handleDelegationToggle() {
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
        fetchUserAdvances(sessionUser.id);
    }
}

/* ── CONTROL DE PESTAÑAS DEL PANEL SAT ── */
function toggleSatPanel() {
    document.getElementById('sat-panel').classList.toggle('hidden');
}
document.querySelectorAll('.sat-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.sat-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.sat-content').forEach(c => c.classList.add('hidden'));
        this.classList.add('active');
        document.getElementById(this.dataset.target).classList.remove('hidden');
    });
});

/* ── VARIABLES TABLA MAESTRA Y PAGINACIÓN ── */
let requests = {!! json_encode($requestsData) !!};
let currentEvaluateId = null;

let currentPage = 1;
let itemsPerPage = 5;

const fmt = n => new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n);

let activeFilter = 'all';
let searchQuery = '';

document.getElementById('table-search').addEventListener('input', function() {
    searchQuery = this.value.toLowerCase().trim();
    currentPage = 1;
    renderDashboard();
});

document.querySelectorAll('.filter-tab').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        activeFilter = this.dataset.filter;
        currentPage = 1;
        renderDashboard();
    });
});

function changePageSize() {
    const val = document.getElementById('page-size-select').value;
    itemsPerPage = val === 'all' ? 999999 : parseInt(val);
    currentPage = 1;
    renderDashboard();
}

function renderDashboard() {
    const list = document.getElementById('dashboard-list');
    const emptyState = document.getElementById('empty-state');
    const tableCount = document.getElementById('table-count');
    list.innerHTML = '';

    let totalAcc = 0, pendCount = 0, pendAmt = 0, appCount = 0, appAmt = 0, rejCount = 0, rejAmt = 0;

    requests.forEach(req => {
        if (req.status !== 'Borrador') totalAcc += req.amount;
        if (req.status === 'Pendiente' || req.status === 'Validado') { pendCount++; pendAmt += req.amount; }
        if (req.status === 'Aprobado') { appCount++; appAmt += req.amount; }
        if (req.status === 'Rechazado') { rejCount++; rejAmt += req.amount; }
    });

    document.getElementById('metric-total-val').textContent = fmt(totalAcc);
    document.getElementById('metric-pending-val').textContent = pendCount;
    document.getElementById('metric-approved-val').textContent = appCount;
    document.getElementById('metric-rejected-val').textContent = rejCount;
    document.getElementById('metric-pending-amount').textContent = fmt(pendAmt);
    document.getElementById('metric-approved-amount').textContent = fmt(appAmt);
    document.getElementById('metric-rejected-amount').textContent = fmt(rejAmt);

    let filtered = requests.filter(req => {
        const matchFilter = activeFilter === 'all' || req.status === activeFilter;
        const matchSearch = !searchQuery ||
                            req.motivo.toLowerCase().includes(searchQuery) ||
                            req.folioP.toLowerCase().includes(searchQuery) ||
                            req.folioU.toLowerCase().includes(searchQuery) ||
                            req.nombre.toLowerCase().includes(searchQuery);
        return matchFilter && matchSearch;
    });

    tableCount.textContent = `${filtered.length} solicitud(es) registrada(s)`;

    if (filtered.length === 0) {
        emptyState.classList.remove('hidden');
        document.getElementById('pagination-controls').innerHTML = '';
        return;
    }
    emptyState.classList.add('hidden');

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

            advanceBadge = `<span class="row-folio advance-folio ${advClass}" onclick="verDetalleAnticipo(${req.advance_id})" title="Clic para revisar el estatus y detalles de este anticipo"><i class="bx bx-link"></i> ${req.advance_folio}</span>`;
        } else {
            advanceBadge = `<span class="row-text-empty">Trámite Independiente</span>`;
        }

        const evaluateBtn = (req.status === 'Pendiente' || req.status === 'Validado') ?
            `<button class="btn-icon btn-icon-evaluate" onclick="evaluarSolicitud(${req.id})" title="Gestionar Resolución (Auditoría)"><i class="bx bx-check-shield"></i></button>` : '';

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
            <td><div class="row-amount-wrap"><span class="row-amount">${fmt(req.amount)}</span><span class="row-amount-label">MXN</span></div></td>
            <td>${advanceBadge}</td>
            <td>${badge}</td>
            <td>${badgePago}</td>
            <td class="cell-actions">
                <div class="actions-wrap">
                    <button class="btn-icon btn-icon-view" onclick="verDetalles(${req.id})" title="Inspeccionar Documento o Editar Borrador"><i class="bx bx-show"></i></button>
                    ${evaluateBtn}
                </div>
            </td>
        </tr>`;
    });

    renderPagination(totalPages);
    animateTableRows('#main-data-table');
}

function renderPagination(totalPages) {
    const container = document.getElementById('pagination-controls');
    container.innerHTML = '';
    if (totalPages <= 1) return;

    const btnPrev = document.createElement('button');
    btnPrev.className = 'page-btn';
    btnPrev.innerHTML = '<i class="bx bx-chevron-left"></i>';
    btnPrev.disabled = currentPage === 1;
    btnPrev.onclick = () => { currentPage--; renderDashboard(); };
    container.appendChild(btnPrev);

    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.className = `page-btn ${i === currentPage ? 'active' : ''}`;
        btn.textContent = i;
        btn.onclick = () => { currentPage = i; renderDashboard(); };
        container.appendChild(btn);
    }

    const btnNext = document.createElement('button');
    btnNext.className = 'page-btn';
    btnNext.innerHTML = '<i class="bx bx-chevron-right"></i>';
    btnNext.disabled = currentPage === totalPages;
    btnNext.onclick = () => { currentPage++; renderDashboard(); };
    container.appendChild(btnNext);
}

/* ── MÉTODOS AUXILIARES PARA LOS ICONOS DE CARGA (INGLÉS) ── */
function getLoadMethodIcon(method) {
    if (method === 'sat_uuid') return "<i class='bx bx-cloud-download'></i>";
    if (method === 'sat_xml') return "<i class='bx bx-file'></i>";
    if (method === 'manual_entry') return "<i class='bx bx-edit-alt'></i>"; // 👈 Cambiamos a lápiz para mayor compatibilidad
    return "<i class='bx bx-dots-horizontal-rounded'></i>"; // unassigned
}

function getLoadMethodTooltip(method) {
    if (method === 'sat_uuid') return "Dato importado desde el SAT (Búsqueda por Folio Fiscal UUID)";
    if (method === 'sat_xml') return "Dato importado automáticamente mediante extracción de archivo XML";
    if (method === 'manual_entry') return "Registro capturado manualmente por el usuario";
    return "Fila vacía - Esperando ingreso de datos"; // unassigned
}

/* ── FUNCIÓN DISPARADORA: DE UNASSIGNED A MANUAL_ENTRY ── */
window.markAsManual = function(element) {
    const row = element.closest('tr');
    if(!row) return;

    const hiddenLoadMethod = row.querySelector('.c-load-method');
    const iconSpan = row.querySelector('.load-method-icon');

    if (hiddenLoadMethod && hiddenLoadMethod.value === 'unassigned') {
        hiddenLoadMethod.value = 'manual_entry';
        if (iconSpan) {
            iconSpan.setAttribute('data-method', 'manual_entry');
            iconSpan.title = getLoadMethodTooltip('manual_entry');
            iconSpan.innerHTML = getLoadMethodIcon('manual_entry');
        }
    }
};

/* ── INYECCIÓN EN HTML DEL TEMPLATE DE FILAS DINÁMICAS ── */
function getRowTemplate(cfdiId = '', loadMethod = 'unassigned') {
    return `
    <tr class="data-row">
        <input type="hidden" class="c-cfdi-id" value="${cfdiId}">
        <input type="hidden" class="c-load-method" value="${loadMethod}">

        <td><div class="date-wrap"><i class="bx bx-calendar"></i><input type="text" class="cell-input date-in modal-focusable" placeholder="DD/MM/AAAA" data-fp onchange="markAsManual(this)"></div></td>
        <td><input type="text" class="cell-input modal-focusable" placeholder="—" oninput="markAsManual(this)"></td>
        <td>
            <div class="desc-wrap">
                <input type="text" class="cell-input modal-focusable desc-input" placeholder="—" oninput="markAsManual(this)">
                <span class="load-method-icon" data-method="${loadMethod}" title="${getLoadMethodTooltip(loadMethod)}">
                    ${getLoadMethodIcon(loadMethod)}
                </span>
            </div>
        </td>
        <td><input type="number" oninput="markAsManual(this); calcTotal()" class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
        <td><input type="number" oninput="markAsManual(this); calcTotal()" class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
        <td><input type="number" oninput="markAsManual(this); calcTotal()" class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
        <td><input type="number" oninput="markAsManual(this); calcTotal()" class="cell-input num c-ish modal-focusable" placeholder="0.00"></td>
        <td><input type="number" oninput="markAsManual(this); calcTotal()" class="cell-input num c-iva modal-focusable" placeholder="0.00"></td>
        <td class="cell-row-total">-</td>
        <td class="text-center"><button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Eliminar Concepto de la Matriz"><i class="bx bx-trash"></i></button></td>
    </tr>`;
}

function addRow(tbodyId, cfdiId = '', loadMethod = 'unassigned') {
    const tbody = document.getElementById(tbodyId);
    tbody.insertAdjacentHTML('beforeend', getRowTemplate(cfdiId, loadMethod));
    flatpickr(tbody.lastElementChild.querySelector('[data-fp]'), {
        locale: "es",
        dateFormat: "d/m/Y",
        allowInput: true,
        disableMobile: "true"
    });
}

function removeRow(btn) {
    const tbody = btn.closest('tbody');
    if (tbody.querySelectorAll('.data-row').length > 1) {
        btn.closest('tr').remove();
        calcTotal();
    }
    else {
        showAlert('Se requiere de manera obligatoria mantener al menos una fila de captura en la matriz contable para procesar el trámite.', 'warning');
    }
}

/* ── REASIGNACIÓN DE BLOQUEOS DEL FORMULARIO ── */
function lockForm() {
    document.querySelectorAll('#reimbursement-modal .modal-focusable, #reimbursement-modal .cell-input').forEach(el => el.setAttribute('disabled', 'true'));
    document.querySelectorAll('input[name="is_deductible"], input[name="is_delegated"]').forEach(el => el.disabled = true);
    document.querySelectorAll('.btn-add-row, .btn-remove-row').forEach(el => el.classList.add('hidden'));
    document.getElementById('evidence-panel').style.pointerEvents = 'none';
    document.querySelector('.modal-header-actions .btn-secondary').classList.add('hidden');
    document.getElementById('modal-nombre').setAttribute('readonly', 'true');
    document.getElementById('modal-nombre').disabled = true;
    document.getElementById('modal-advance-id').disabled = true;
    toggleAdvanceViewButton();
}

function unlockForm() {
    document.querySelectorAll('#reimbursement-modal .modal-focusable, #reimbursement-modal .cell-input').forEach(el => el.removeAttribute('disabled'));
    document.querySelectorAll('input[name="is_deductible"], input[name="is_delegated"]').forEach(el => el.disabled = false);
    document.querySelectorAll('.btn-add-row, .btn-remove-row').forEach(el => el.classList.remove('hidden'));
    document.getElementById('evidence-panel').style.pointerEvents = 'auto';
    document.querySelector('.modal-header-actions .btn-secondary').classList.remove('hidden');
    document.getElementById('modal-nombre').disabled = false;
    document.getElementById('modal-advance-id').disabled = false;
    document.getElementById('btn-view-advance').classList.add('hidden');

    const isDelegated = document.querySelector('input[name="is_delegated"]:checked').value === "1";
    if (isDelegated) {
        document.getElementById('modal-nombre').removeAttribute('readonly');
    }
    else {
        document.getElementById('modal-nombre').setAttribute('readonly', 'true');
    }
}

function resetModalForm() {
    ['cat-vuelos', 'cat-restaurantes', 'cat-combustible', 'cat-otros'].forEach(cat => {
        const rows = document.getElementById(cat).querySelectorAll('.data-row');
        for (let i = 1; i < rows.length; i++) rows[i].remove();
    });
    document.querySelectorAll('.cell-input:not([readonly])').forEach(el => el.value = '');
    document.querySelectorAll('.c-cfdi-id').forEach(el => el.value = '');
    document.querySelectorAll('.c-load-method').forEach(el => el.value = 'unassigned');

    document.querySelectorAll('.load-method-icon').forEach(iconSpan => {
        iconSpan.setAttribute('data-method', 'unassigned');
        iconSpan.title = getLoadMethodTooltip('unassigned');
        iconSpan.innerHTML = getLoadMethodIcon('unassigned');
    });

    document.getElementById('modal-motivo').value = '';
    document.getElementById('modal-centro-costos').value = '';
    document.getElementById('modal-tipo-solicitud').value = 'Reembolso';
    document.getElementById('modal-tipo-gasto').value = 'viaje';

    document.getElementById('sat-panel').classList.add('hidden');
    document.getElementById('sat-result-container').classList.add('hidden');
    document.getElementById('rejection-container').classList.add('hidden');

    tempSatData = null;
    currentActiveClaimId = null;
    isEditMode = false;
    document.querySelector('input[name="is_delegated"][value="0"]').checked = true;
    handleDelegationToggle();

    evidenciasFiles = [];
    renderFileList();
    actualizarInputFiles();
    calcTotal();
    unlockForm();
}

/* ── MODOS DE ACCIÓN PRINCIPALES DEL MODAL ── */
function openModalForCreate() {
    resetModalForm();
    fetchUserAdvances(sessionUser.id);

    document.getElementById('main-modal-title').innerHTML = '<i class="bx bx-receipt"></i> Generación de <strong>Comprobación</strong>';
    document.getElementById('modal-folio-p').innerHTML = '<span class="status-badge badge-draft badge-draft-auto">Asignación Automática</span>';
    document.getElementById('modal-folio-u').innerHTML = '<span class="status-badge badge-draft badge-draft-auto">Automático</span>';
    document.querySelector('input[name="is_deductible"][value="1"]').checked = true;

    document.getElementById('btn-enviar').innerHTML = '<i class="bx bx-send"></i> Emitir Solicitud a Revisión';
    document.getElementById('btn-borrador').classList.remove('hidden');
    document.getElementById('footer-create').classList.remove('hidden');
    document.getElementById('footer-view').classList.add('hidden');
    document.getElementById('footer-evaluate').classList.add('hidden');
    document.getElementById('reimbursement-modal').classList.remove('hidden');

    ['cat-vuelos', 'cat-restaurantes', 'cat-combustible', 'cat-otros'].forEach(cat => {
        if (document.getElementById(cat).querySelectorAll('.data-row').length === 0) {
            addRow(cat);
        }
    });
}

async function fetchAndPopulateClaim(id) {
    Swal.fire({
        title: 'Cargando Documento Contable...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
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

            if (claim.user_id !== sessionUser.id) {
                document.querySelector('input[name="is_delegated"][value="1"]').checked = true;
            }
            else {
                document.querySelector('input[name="is_delegated"][value="0"]').checked = true;
            }
            handleDelegationToggle();

            document.getElementById('modal-nombre').value = claim.beneficiary.name;
            document.getElementById('modal-depto').value = claim.beneficiary.employee?.area?.name || 'Sin asignar';
            document.getElementById('modal-beneficiary-id').value = claim.user_id;
            document.getElementById('modal-centro-costos').value = claim.cost_center;
            document.getElementById('modal-motivo').value = claim.motive;
            document.getElementById('modal-tipo-solicitud').value = claim.request_type || 'Reembolso';
            document.getElementById('modal-tipo-gasto').value = claim.category;
            document.querySelector(`input[name="is_deductible"][value="${claim.is_deductible ? '1' : '0'}"]`).checked = true;

            await fetchUserAdvances(claim.user_id, claim.expense_advance_id);

            ['cat-vuelos', 'cat-restaurantes', 'cat-combustible', 'cat-otros'].forEach(cat => {
                document.getElementById(cat).querySelectorAll('.data-row').forEach(r => r.remove());
            });

            claim.lines.forEach(line => {
                const cat = line.concept_group;
                let method = line.load_method || 'manual_entry';

                addRow(cat, line.expense_cfdi_id || '', method);

                const targetRow = document.getElementById(cat).lastElementChild;
                const inputs = targetRow.querySelectorAll('.cell-input');
                const dateOnly = line.expense_date.substring(0, 10);
                const [y, m, d] = dateOnly.split('-');

                if (inputs[0]._flatpickr) {
                    inputs[0]._flatpickr.setDate(dateOnly, true, "Y-m-d");
                }
                else {
                    inputs[0].value = `${d}/${m}/${y}`;
                }

                inputs[1].value = line.document_number || '';
                inputs[2].value = line.description || '';
                inputs[3].value = line.amount_fiscal > 0 ? line.amount_fiscal : '';
                inputs[4].value = line.amount_simple > 0 ? line.amount_simple : '';
                inputs[5].value = line.amount_none > 0 ? line.amount_none : '';
                inputs[6].value = line.tax_ish !== '0.00' ? line.tax_ish : '';
                inputs[7].value = line.tax_iva > 0 ? line.tax_iva : '';
            });

            ['cat-vuelos', 'cat-restaurantes', 'cat-combustible', 'cat-otros'].forEach(cat => {
                if (document.getElementById(cat).querySelectorAll('.data-row').length === 0) {
                    addRow(cat);
                }
            });

            calcTotal();
            Swal.close();
            return claim;
        }
    } catch (error) {
        showAlert('Error del servidor: No se pudo cargar la información para la inspección.', 'error');
    }
}

async function verDetalles(id) {
    resetModalForm();
    const claim = await fetchAndPopulateClaim(id);
    if (!claim) return;

    document.getElementById('main-modal-title').innerHTML = `<i class="bx bx-search-alt"></i> Inspección del <strong>Folio: ${claim.folio_system}</strong>`;
    lockForm();

    document.getElementById('footer-create').classList.add('hidden');
    document.getElementById('footer-view').classList.remove('hidden');
    document.getElementById('footer-evaluate').classList.add('hidden');

    const viewFooter = document.getElementById('footer-view');
    viewFooter.innerHTML = '<button type="button" class="btn btn-secondary" onclick="closeModal()">Cerrar Consulta</button>';

    if (claim.status_review === 'Borrador' || claim.status_review === 'Rechazado') {
        const btnEdit = document.createElement('button');
        btnEdit.type = 'button';
        btnEdit.className = 'btn btn-primary';
        btnEdit.innerHTML = '<i class="bx bx-edit"></i> ' + (claim.status_review === 'Rechazado' ? 'Corregir Dictamen y Reenviar' : 'Continuar Elaboración de Borrador');
        btnEdit.onclick = () => habilitarEdicion(claim.status_review);
        viewFooter.appendChild(btnEdit);
    }
    document.getElementById('reimbursement-modal').classList.remove('hidden');
}

function habilitarEdicion(status) {
    isEditMode = true;
    unlockForm();
    document.getElementById('main-modal-title').innerHTML = `<i class="bx bx-edit"></i> Edición del <strong>Folio: ${document.getElementById('modal-folio-p').textContent}</strong>`;
    document.getElementById('footer-view').classList.add('hidden');
    document.getElementById('footer-create').classList.remove('hidden');

    const btnEnviar = document.getElementById('btn-enviar');
    if (status === 'Rechazado') {
        btnEnviar.innerHTML = '<i class="bx bx-send"></i> Enviar Nuevamente a Revisión';
        document.getElementById('btn-borrador').classList.add('hidden');
    } else {
        btnEnviar.innerHTML = '<i class="bx bx-send"></i> Emitir Solicitud a Revisión';
        document.getElementById('btn-borrador').classList.remove('hidden');
    }
}

async function evaluarSolicitud(id) {
    resetModalForm();
    const claim = await fetchAndPopulateClaim(id);
    if (!claim) return;

    document.getElementById('main-modal-title').innerHTML = '<i class="bx bx-check-shield"></i> Ejecución de Dictamen Administrativo / Auditoría';
    currentEvaluateId = id;
    lockForm();

    const btnValidate = document.getElementById('btn-eval-validate');
    const btnApprove = document.getElementById('btn-eval-approve');

    if (claim.status_review === 'Pendiente') {
        btnValidate.classList.remove('hidden');
        btnApprove.classList.add('hidden');
    }
    else if (claim.status_review === 'Validado') {
        btnValidate.classList.add('hidden');
        btnApprove.classList.remove('hidden');
    }

    document.getElementById('footer-create').classList.add('hidden');
    document.getElementById('footer-view').classList.add('hidden');
    document.getElementById('footer-evaluate').classList.remove('hidden');
    document.getElementById('reimbursement-modal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('reimbursement-modal').classList.add('hidden');
}

function processEvaluation(status) {
    if (!currentEvaluateId) return;
    let actionText = status === 'Aprobado' ? 'Aprobar Definitivamente y Autorizar Pago' : (status === 'Validado' ? 'Dar Visto Bueno a Documentación Fiscal' : 'Denegar, Cancelar y Rechazar Movimiento');
    let confirmColor = status === 'Aprobado' ? '#2d7d46' : (status === 'Validado' ? '#1d4ed8' : '#b91c1c');

    if (status === 'Rechazado') {
        Swal.fire({
            title: `<span style="font-family:'Poppins', sans-serif;">Motivo de Rechazo para Auditoría</span>`,
            html: `<span style="font-family:'Poppins', sans-serif; color:#64748b; font-size: 0.85rem;">Explique el criterio por el cual no procede esta solicitud. El colaborador responsable podrá visualizar los motivos para rectificar la información.</span>`,
            input: 'textarea',
            inputPlaceholder: 'Ej. Se requiere la inclusión imperativa de los importes XML desglosados...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#94a3b8',
            confirmButtonText: `Efectuar Rechazo de Folio`,
            cancelButtonText: `Cancelar Procedimiento`,
            inputValidator: (value) => {
                if (!value.trim()) return '¡Es un requisito mandatorio justificar el rechazo dentro de las políticas de auditoría interna!';
            }
        }).then((result) => {
            if (result.isConfirmed) {
                updateStatus(currentEvaluateId, status, result.value);
                closeModal();
                currentEvaluateId = null;
            }
        });
    } else {
        Swal.fire({
            title: `<span style="font-family:'Poppins', sans-serif;">¿Emisión de Dictamen Final?</span>`,
            html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">¿Confirma la decisión administrativa de <strong>${actionText}</strong> respecto a este folio operativo?</span>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#94a3b8',
            confirmButtonText: `Autorizar y Procesar Movimiento`,
            cancelButtonText: `Cancelar Acción Pendiente`
        }).then((result) => {
            if (result.isConfirmed) {
                updateStatus(currentEvaluateId, status, null);
                closeModal();
                currentEvaluateId = null;
            }
        });
    }
}

async function updateStatus(id, status, comments) {
    try {
        Swal.fire({
            title: 'Ejecutando Dictamen en Base de Datos...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        let formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('new_status', status);
        if (comments) formData.append('comments', comments);

        const statusUrl = `{{ url('administration/expense-claims/reimbursements') }}/${id}/status`;
        const response = await fetch(statusUrl, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();

        if (data.success) {
            Swal.fire('Procedimiento Completado', data.message, 'success');
            setTimeout(() => window.location.reload(), 1500);
        }
        else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('No fue posible establecer la vinculación con el nodo principal del servidor para tramitar el cambio de estado.', 'error');
    }
}

/* ── INTERACCIÓN DOCUMENTAL SAT Y ARCHIVOS EVIDENCIA ── */
let evidenciasFiles = [];
const maxFileSize = 10 * 1024 * 1024;
const evidenciaInput = document.getElementById('evidence-upload');
const evidenciaPanel = document.getElementById('evidence-panel');

evidenciaInput.addEventListener('change', function(e) {
    procesarArchivosEvidencia(e.target.files);
    this.value = '';
});
evidenciaPanel.addEventListener('dragover', e => {
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
    if (e.dataTransfer.files.length) procesarArchivosEvidencia(e.dataTransfer.files);
});

function procesarArchivosEvidencia(files) {
    let errorSize = false, errorType = false;
    Array.from(files).forEach(file => {
        if (file.type !== 'application/pdf') {
            errorType = true;
            return;
        }
        if (file.size > maxFileSize) {
            errorSize = true;
            return;
        }
        if (!evidenciasFiles.some(f => f.name === file.name))
            evidenciasFiles.push(file);
    });
    if (errorType) showAlert('Políticas de Carga: La arquitectura del sistema permite exclusivamente la anexión de documentación probatoria en formato de extensión PDF.', 'warning');
    if (errorSize) showAlert('Límite de Almacenamiento Excedido: Uno o más archivos intentan rebasar el umbral máximo de capacidad establecido en 10 Megabytes por elemento individual.', 'error');
    renderFileList();
    actualizarInputFiles();
}

function removeFile(index) {
    evidenciasFiles.splice(index, 1);
    renderFileList();
    actualizarInputFiles();
}
function actualizarInputFiles() {
    const dt = new DataTransfer();
    evidenciasFiles.forEach(file => dt.items.add(file));
    evidenciaInput.files = dt.files;
}
function formatBytes(bytes, decimals = 2) {
    if (!+bytes) return '0 Bytes';
    const k = 1024, dm = decimals < 0 ? 0 : decimals, sizes = ['Bytes', 'KB', 'MB', 'GB'], i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
}
function renderFileList() {
    const listDiv = document.getElementById('evidence-list');
    listDiv.innerHTML = evidenciasFiles.length > 0 ?
        `<div class="file-grid">${evidenciasFiles.map((f, i) => `
            <div class="file-card">
                <i class="bx bxs-file-pdf file-icon-lg"></i>
                <div class="file-info">
                    <span class="file-name" title="${f.name}">${f.name}</span>
                    <span class="file-size">${formatBytes(f.size)}</span>
                </div>
                <button type="button" class="btn-remove-file" onclick="event.stopPropagation(); removeFile(${i})">
                    <i class="bx bx-x"></i>
                </button>
            </div>`).join('')}</div>` : '';
}

async function buscarFactura() {
    const uuid = document.getElementById('search-uuid').value.trim();
    const btnB = document.getElementById('btn-buscar');
    if (uuid.length !== 36) {
        showAlert('Control de Validación: El vector de código alfanumérico asociado al UUID requiere obligatoriamente una extensión de 36 caracteres exactos.', 'warning');
        return;
    }
    btnB.innerHTML = '<span class="spinner"></span> Ejecutando Query...';
    btnB.disabled = true;
    try {
        const response = await fetch(`{{ route('expense-claims.cfdi.search') }}?uuid=${uuid}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        if (response.ok && data.success) {
            const cfdi = data.data;
            let serieFolio = '';
            if (cfdi.serie) serieFolio += cfdi.serie + '-';
            if (cfdi.folio) serieFolio += cfdi.folio;
            if (!serieFolio) serieFolio = cfdi.uuid.substring(0, 8);

            tempSatData = {
                id: cfdi.id,
                load_method: 'sat_uuid',
                fecha_iso: cfdi.issue_date.split(' ')[0],
                folio: serieFolio,
                desc: cfdi.concept_summary || 'Extracción y vinculación de servicios amparados bajo codificación UUID',
                sub: parseFloat(cfdi.subtotal) || 0,
                iva: parseFloat(cfdi.tax_iva) || 0,
                ish: (parseFloat(cfdi.tax_ish) || 0) - (parseFloat(cfdi.tax_retenciones) || 0)
            };
            document.getElementById('sat-result-uuid').textContent = cfdi.uuid;
            document.getElementById('sat-result-container').classList.remove('hidden');
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Comprobante fiscal enlazado satisfactoriamente desde la bóveda matriz del sistema.',
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            showAlert(data.message, 'warning');
        }
    } catch (error) {
        showAlert('Colapso de Transmisión: Inviabilidad técnica para establecer protocolos de comunicación con la bóveda de resguardo de facturación electrónica.', 'error');
    }
    finally {
        btnB.innerHTML = '<i class="bx bx-search"></i> Buscar UUID en Sistema';
        btnB.disabled = false;
    }
}

const dropZoneUI = document.getElementById('drop-zone');
const xmlInput = document.getElementById('xml-input');
dropZoneUI.addEventListener('click', () => xmlInput.click());
xmlInput.addEventListener('change', e => leerXML(e.target.files[0]));
dropZoneUI.addEventListener('dragover', e => {
    e.preventDefault();
    dropZoneUI.classList.add('dragover');
});
dropZoneUI.addEventListener('dragleave', e => {
    e.preventDefault();
    dropZoneUI.classList.remove('dragover');
});
dropZoneUI.addEventListener('drop', e => {
    e.preventDefault();
    dropZoneUI.classList.remove('dragover');
    if (e.dataTransfer.files.length) leerXML(e.dataTransfer.files[0]);
});

async function leerXML(file) {
    if (!file || file.type !== 'text/xml') {
        showAlert('Requerimiento de Formato: El sistema únicamente procesa metadatos encapsulados bajo la terminación estructural .xml legítima.', 'error');
        return;
    }
    Swal.fire({
        title: 'Inspeccionando y Procesando Nodos XML...',
        text: 'Desencriptando la validez criptográfica ante los servidores de la Bóveda del Sistema Matriz.',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    let formData = new FormData();
    formData.append('xml_file', file);
    formData.append('_token', '{{ csrf_token() }}');
    try {
        const response = await fetch('{{ route('expense-claims.cfdi.upload') }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        if (response.ok && data.success) {
            Swal.close();
            const cfdi = data.data;
            document.getElementById('search-uuid').value = cfdi.uuid;
            let serieFolio = '';
            if (cfdi.serie) serieFolio += cfdi.serie + '-';
            if (cfdi.folio) serieFolio += cfdi.folio;
            if (!serieFolio) serieFolio = cfdi.uuid.substring(0, 8);
            tempSatData = {
                id: cfdi.id,
                load_method: 'sat_xml',
                fecha_iso: cfdi.issue_date.split(' ')[0],
                folio: serieFolio,
                desc: cfdi.concept_summary || 'Lectura de gastos y conceptos importados mediante motor XML',
                sub: parseFloat(cfdi.subtotal) || 0,
                iva: parseFloat(cfdi.tax_iva) || 0,
                ish: (parseFloat(cfdi.tax_ish) || 0) - (parseFloat(cfdi.tax_retenciones) || 0)
            };
            document.getElementById('sat-result-uuid').textContent = cfdi.uuid;
            document.getElementById('sat-result-container').classList.remove('hidden');
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            showAlert(data.message || 'El documento XML adjuntado proyecta severas inconsistencias o carece de la firma digital fiscal reglamentaria requerida.', 'error');
        }
    } catch (error) {
        showAlert('Excepción de Integridad: Colapso en el túnel de comunicación con los clústeres de análisis y desencriptado.', 'error');
    }
}

function agregarFilaDesdeSAT() {
    const cat = document.getElementById('sat-category').value;
    if (!cat) {
        showAlert('Mapeo Contable Incompleto: Usted debe categorizar contablemente a qué rubro pertenece la factura extraída de manera previa a su consolidación final.', 'warning');
        return;
    }
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
        addRow(cat, tempSatData.id, tempSatData.load_method);
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
        iconSpan.title = getLoadMethodTooltip(currentMethod);
        iconSpan.innerHTML = getLoadMethodIcon(currentMethod);
    }

    const inputs = targetRow.querySelectorAll('.cell-input');
    if (inputs[0]._flatpickr && tempSatData.fecha_iso) {
        inputs[0]._flatpickr.setDate(tempSatData.fecha_iso, true, "Y-m-d");
    }
    else if (tempSatData.fecha_iso) {
        const [y, m, d] = tempSatData.fecha_iso.split('-');
        inputs[0].value = `${d}/${m}/${y}`;
    }
    inputs[1].value = tempSatData.folio;
    inputs[2].value = tempSatData.desc;
    inputs[3].value = tempSatData.sub;
    inputs[4].value = '';
    inputs[5].value = '';
    inputs[6].value = tempSatData.ish;
    inputs[7].value = tempSatData.iva;

    calcTotal();
    document.getElementById('sat-result-container').classList.add('hidden');
    document.getElementById('sat-category').value = '';
    tempSatData = null;

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Los vectores del comprobante fiscal se inyectaron simétricamente en el núcleo de la matriz contable.',
        showConfirmButton: false,
        timer: 3000
    });
}

function calcTotal() {
    let gFiscal = 0, gSimple = 0, gPropina = 0, gIva = 0, gIsh = 0;
    document.querySelectorAll('.data-row').forEach(row => {
        const inputs = row.querySelectorAll('.cell-input');
        const rFiscal = parseFloat(inputs[3].value) || 0;
        const rSimple = parseFloat(inputs[4].value) || 0;
        const rPropina = parseFloat(inputs[5].value) || 0;
        const rIsh = parseFloat(inputs[6].value) || 0;
        const rIva = parseFloat(inputs[7].value) || 0;
        const rowTotal = rFiscal + rSimple + rPropina + rIsh + rIva;
        const rowTotalCell = row.querySelector('.cell-row-total');
        if (rowTotalCell) rowTotalCell.textContent = rowTotal > 0 ? fmt(rowTotal) : '-';
        gFiscal += rFiscal;
        gSimple += rSimple;
        gPropina += rPropina;
        gIsh += rIsh;
        gIva += rIva;
    });
    const gTotal = gFiscal + gSimple + gPropina + gIva + gIsh;
    document.getElementById('sum-fiscal').textContent = fmt(gFiscal);
    document.getElementById('sum-simple').textContent = fmt(gSimple);
    document.getElementById('sum-propinas').textContent = fmt(gPropina);
    document.getElementById('sum-iva').textContent = fmt(gIva);
    document.getElementById('sum-ish').textContent = fmt(gIsh);
    document.getElementById('sum-total').textContent = fmt(gTotal);
    document.getElementById('sum-total').setAttribute('data-value', gTotal);
}

/* ── ENVÍOS SEGUROS A BASE DE DATOS ── */
function verifyAndSubmit() {
    const nombreBen = document.getElementById('modal-nombre').value.trim();
    const motivo = document.getElementById('modal-motivo').value.trim();
    const centroCosto = document.getElementById('modal-centro-costos').value;
    const total = parseFloat(document.getElementById('sum-total').getAttribute('data-value'));

    if (!nombreBen) {
        showAlert('Deficiencia de Asignación: Debe estipular o direccionar sistemáticamente el nombre completo del individuo beneficiario que encabeza el movimiento.', 'warning');
        return;
    }
    if (!centroCosto) {
        showAlert('Déficit Estructural: Imprescindible asignar el Centro de Costos matricial asociado al bloque orgánico o departamento responsable del gasto operativo.', 'warning');
        return;
    }
    if (!motivo) {
        showAlert('Justificación Obligatoria: Por cánones normativos de la auditoría, usted se halla en la estricta obligación de redactar meticulosamente la justificación o el factor motor que motivó esta erogación de fondos.', 'warning');
        return;
    }

    let hasValidRows = false;
    document.querySelectorAll('.data-row').forEach(row => {
        const dateVal = row.querySelector('.cell-input').value;
        if (dateVal) hasValidRows = true;
    });

    if (!hasValidRows) {
        showAlert('Bóveda de Conceptos Carente de Datos: Se ha detectado la presencia de un comprobante que no atesora información contable íntegra y fehaciente. Debe plasmar cuando menos la captura elemental de un concepto pecuniario dentro de la arquitectura de la matriz.', 'error');
        return;
    }
    if (total <= 0) {
        showAlert('Discrepancia Cuantitativa: La proyección analítica dictamina que la sumatoria contable general ostenta un valor inerte. Verifique contundentemente los flujos y partidas presupuestarias desglosadas por cada peldaño.', 'error');
        return;
    }

    Swal.fire({
        title: `<span style="font-family:'Poppins', sans-serif;">¿Confirmar Clausura y Remisión a Revisión Gerencial?</span>`,
        html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">Los eslabones de información tasados por un importe absoluto y liquidable de <strong>${fmt(total)}</strong> quedarán en un blindaje de temporalidad inmutable y la solicitud ingresará al carril de procesamiento de las jefaturas.</span>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1d4ed8',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: `Efectuar Firma y Emitir Responsiva`,
        cancelButtonText: `Cancelar Envío Provisionalmente`
    }).then((result) => {
        if (result.isConfirmed) procesarEnvio('Pendiente', 'En espera');
    });
}

function saveDraft() {
    const nombreBen = document.getElementById('modal-nombre').value.trim();
    const motivo = document.getElementById('modal-motivo').value.trim();
    if (!nombreBen) {
        showAlert('Precaución de Salvaguarda: Para posibilitar el almacenaje profiláctico de la información temporal y transitoria inherente a este borrador documental, es menester cardinal consolidar primeramente el nombramiento específico de la entidad beneficiaria.', 'warning');
        return;
    }
    if (!motivo) {
        showAlert('Bloqueo Operacional Transitorio: A los efectos de efectuar un rescate certero y resguardo estructural íntegro de su progreso de tabulación inconclusa o parcial, el sistema requiere imperativamente que se asiente textualmente el campo destinado al Motivo Central de la Erogación Monetaria.', 'warning');
        return;
    }
    procesarEnvio('Borrador', 'N/A');
}

async function procesarEnvio(estadoRevision, estadoPago) {
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

                lineasArray.push({
                    categoria: cat,
                    cfdi_id: cfdiIdInput ? cfdiIdInput.value : null,
                    load_method: finalMethod,
                    fecha: inputs[0].value,
                    folio: inputs[1].value,
                    descripcion: inputs[2].value,
                    monto_fiscal: parseFloat(inputs[3].value) || 0,
                    monto_simple: parseFloat(inputs[4].value) || 0,
                    monto_sin: parseFloat(inputs[5].value) || 0,
                    ish: parseFloat(inputs[6].value) || 0,
                    iva: parseFloat(inputs[7].value) || 0,
                    total_linea: parseFloat(row.querySelector('.cell-row-total').textContent.replace(/[^0-9.-]+/g, "")) || 0
                });
            }
        });
    });

    if (lineasArray.length === 0 && estadoRevision !== 'Borrador') {
        showAlert('Anomalía de Inyección Crítica: Los algoritmos de inspección integral han detectado un desolador vacío en la cuadrícula de la matriz. La estipulación de renglones y conceptos en el ordenamiento contable se reviste del grado de máxima obligatoriedad.', 'error');
        return;
    }
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
    formData.append('centro_costo', document.getElementById('modal-centro-costos').value);
    formData.append('beneficiary_id', document.getElementById('modal-beneficiary-id').value);
    formData.append('depto', document.getElementById('modal-depto').value);
    formData.append('lugar_emision', document.getElementById('modal-lugar').value);
    formData.append('is_draft', estadoRevision === 'Borrador');
    formData.append('total_subtotal', totalSubtotal);
    formData.append('total_iva', document.getElementById('sum-iva').textContent.replace(/[^0-9.-]+/g, ""));
    formData.append('total_ish', document.getElementById('sum-ish').textContent.replace(/[^0-9.-]+/g, ""));
    formData.append('total_amount', document.getElementById('sum-total').getAttribute('data-value'));
    formData.append('lineas', JSON.stringify(lineasArray));

    evidenciasFiles.forEach((file, index) => {
        formData.append(`evidencias[${index}]`, file);
    });

    try {
        Swal.fire({
            title: 'Sincronizando Interfaz con la Base de Datos Central...',
            text: 'Trazando líneas, registrando bloques de matrices perimetrales y empaquetando minuciosamente las evidencias probatorias y archivos documentales adjuntos de respaldo en este vector.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        let fetchUrl = '{{ route('expense-claims.store') }}';
        if (isEditMode) {
            fetchUrl = `{{ url('administration/expense-claims/reimbursements') }}/${currentActiveClaimId}/update`;
        }

        const response = await fetch(fetchUrl, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();

        if (data.success) {
            Swal.fire({
                title: 'Transmisión Exitosa y Trámite Consolidado Íntegramente',
                html: data.message + '<br><br><strong>Folio Identificador Maestro Asignado En La Bóveda: ' + data.folio + '</strong>',
                icon: 'success',
                confirmButtonColor: '#2d7d46'
            });
            closeModal();
            setTimeout(() => window.location.reload(), 2000);
        }
        else {
            showAlert(data.message || 'Misteriosa Falla Funcional: Catástrofe intempestiva e imprevista detectada en la sincronización, compilación o empaquetado algorítmico de los datos crudos durante la triangulación con el servidor.', 'error');
        }
    } catch (error) {
        showAlert('Caída Crítica del Enlace de Telecomunicaciones: Imposible y totalmente inviable forjar un puente cifrado o entablar un protocolo de conexión seguro y estabilizado hacia la infraestructura de los enrutadores y servidores corporativos internos.', 'error');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    renderDashboard();
});
</script>
@endpush
