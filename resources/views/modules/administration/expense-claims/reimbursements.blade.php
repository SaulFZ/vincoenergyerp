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
                    <button class="btn btn-primary" style="padding: 0.45rem 1.2rem; font-size: 0.8rem;" onclick="openModalForCreate()" aria-label="Crear un nuevo reembolso">
                        <i class="bx bx-plus-circle"></i> Nuevo Reembolso
                    </button>
                </div>
            </div>

            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="th-w-42">#</th>
                            <th>Folio Principal</th>
                            <th>Folio Usuario</th>
                            <th>Fecha</th>
                            <th>Solicitante</th>
                            <th>Motivo</th>
                            <th>Departamento</th>
                            <th>Monto Total</th>
                            <th>Estado de Revisión</th>
                            <th>Estado de Pago</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="dashboard-list">
                        {{-- Las filas se inyectan dinámicamente vía JavaScript --}}
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
                <span id="table-count" class="table-count-label">0 solicitudes registradas</span>
            </div>
        </div>

    </div>

    {{-- ════════════════════════════════════════════════════════════════════════
         MODAL FLOTANTE: REGISTRO Y EVALUACIÓN DE REEMBOLSO
         ════════════════════════════════════════════════════════════════════════ --}}
    <div id="reimbursement-modal" class="modal-bg hidden" aria-hidden="true" role="dialog">
        <div class="modal-box">

            {{-- Cabecera del Modal --}}
            <div class="modal-header">
                <h2 class="modal-title" id="main-modal-title">
                    <i class="bx bx-receipt"></i>
                    Formato de <strong>Reembolso</strong>
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

                {{-- NUEVO: PANEL DE ALERTA DE RECHAZO --}}
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
                        <div class="fh-row-pills">
                            <div>
                                <label class="input-label">Categoría del Gasto Asignado</label>
                                <div class="radio-pill-group">
                                    <label class="radio-pill-label">
                                        <input type="radio" name="tipo_gasto" value="viaje" class="modal-focusable"
                                            checked>
                                        <i class="bx bxs-plane-alt"></i> Viáticos y Viaje
                                    </label>
                                    <label class="radio-pill-label">
                                        <input type="radio" name="tipo_gasto" value="operacion"
                                            class="modal-focusable">
                                        <i class="bx bx-briefcase"></i> Operaciones y Campo
                                    </label>
                                    <label class="radio-pill-label">
                                        <input type="radio" name="tipo_gasto" value="otros" class="modal-focusable">
                                        <i class="bx bx-dots-horizontal-rounded"></i> Diversos / Otros
                                    </label>
                                </div>
                            </div>

                            {{-- NUEVO: TOGGLE DE GASTO DEDUCIBLE COMO PÍLDORAS SI/NO --}}
                            <div>
                                <label class="input-label">¿Es Gasto Deducible?</label>
                                <div class="radio-pill-group" style="gap: 0.25rem;">
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

                            {{-- TOGGLE DE CAPTURA DELEGADA --}}
                            <div class="delegation-wrapper" id="delegation-container">
                                <div class="delegation-toggle-wrap">
                                    <label class="switch">
                                        <input type="checkbox" id="toggle-delegation"
                                            onchange="handleDelegationToggle()">
                                        <span class="slider round"></span>
                                    </label>
                                    <span class="delegation-text">Capturar Otro Beneficiario</span>
                                </div>
                            </div>
                        </div>

                        <div class="fh-grid-4">
                            {{-- BUSCADOR DE USUARIO INTEGRADO --}}
                            <div>
                                <label class="input-label">Nombre del Beneficiario</label>
                                <div class="input-group reimburse-dropdown-container">
                                    <i class="bx bx-user field-icon" id="icon-solicitante"></i>
                                    <input type="hidden" id="modal-beneficiary-id" value="{{ Auth::id() ?? 1 }}">
                                    <input type="text" id="modal-nombre"
                                        value="{{ Auth::user()->name ?? 'Saul Falcon Perez' }}" class="input-field"
                                        readonly autocomplete="off">
                                    <div id="employee-dropdown" class="reimburse-custom-dropdown hidden"></div>
                                </div>
                            </div>

                            <div>
                                <label class="input-label">Área de Adscripción</label>
                                <div class="input-group">
                                    <i class="bx bx-buildings field-icon"></i>
                                    <input type="text" id="modal-depto" value="Desarrollo de Software"
                                        class="input-field" readonly>
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
                                    <input type="text" id="modal-motivo" class="input-field modal-focusable"
                                        placeholder="Ej. Viáticos técnicos a pozo foráneo">
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
                            <button type="button" class="sat-tab active" data-target="sat-tab-uuid"><i
                                    class="bx bx-search-alt"></i> Buscar UUID</button>
                            <button type="button" class="sat-tab" data-target="sat-tab-xml"><i
                                    class="bx bx-upload"></i> Cargar XML</button>
                        </div>
                    </div>

                    <div class="sat-panel-body">
                        {{-- Pestaña 1: Búsqueda Manual en BD --}}
                        <div id="sat-tab-uuid" class="sat-content active">
                            <label class="sat-label">Folio Fiscal (UUID) del comprobante SAT</label>
                            <div class="sat-search-group">
                                <div class="sat-input-wrap" style="position: relative;">
                                    <i class="bx bx-barcode"></i>
                                    <input type="text" id="search-uuid" class="sat-input modal-focusable"
                                        placeholder="Buscar por últimos dígitos, Folio o Proveedor..." autocomplete="off">

                                    <div id="uuid-dropdown" class="sat-dropdown hidden"></div>
                                </div>
                                <button type="button" id="btn-buscar" class="btn btn-primary"
                                    onclick="buscarFactura()">
                                    <i class="bx bx-search"></i> Buscar
                                </button>
                            </div>
                        </div>

                        {{-- Pestaña 2: Carga de Archivo (Manual) --}}
                        <div id="sat-tab-xml" class="sat-content hidden">
                            <div id="drop-zone" class="sat-drop-zone">
                                <i class="bx bx-cloud-upload"></i>
                                <p>Arrastra tu archivo .XML aquí o haz clic para examinar</p>
                                <small>El sistema extraerá automáticamente el UUID, RFC y Fecha.</small>
                                <input type="file" id="xml-input" accept=".xml" class="hidden">
                            </div>
                        </div>

                        {{-- ÁREA DE RESULTADO Y ASIGNACIÓN --}}
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
                                        <option value="" disabled selected>¿A qué categoría pertenece esta factura?
                                        </option>
                                        <option value="cat-vuelos">Transportación, Vuelos y Peajes</option>
                                        <option value="cat-restaurantes">Consumo de Alimentos y Restaurantes</option>
                                        <option value="cat-combustible">Abastecimiento de Combustible</option>
                                        <option value="cat-otros">Cargos Varios / Misceláneos</option>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-primary" onclick="agregarFilaDesdeSAT()">
                                    <i class="bx bx-plus"></i> Integrar Gasto a Matriz
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- MATRIZ DE DESGLOSE FINANCIERO --}}
                <div class="expense-card">
                    <div class="expense-card-header">
                        <span class="expense-card-title">
                            <i class="bx bx-table"></i> Desglose y Análisis Analítico de Gastos
                        </span>
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

                            {{-- SECCIÓN: VUELOS Y/O TRANSPORTE --}}
                            <tbody id="cat-vuelos">
                                <tr class="cat-row">
                                    <td colspan="10">
                                        <div class="cat-row-content">
                                            <span><i class="bx bxs-plane-alt"></i> I. Transportación, Vuelos y
                                                Peajes</span>
                                            <button type="button" class="btn-add-row" onclick="addRow('cat-vuelos')"
                                                title="Agregar Fila de Gasto"><i class="bx bx-plus"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>

                            {{-- SECCIÓN: RESTAURANTES --}}
                            <tbody id="cat-restaurantes">
                                <tr class="cat-row">
                                    <td colspan="10">
                                        <div class="cat-row-content">
                                            <span><i class="bx bx-restaurant"></i> II. Consumo de Alimentos y
                                                Restaurantes</span>
                                            <button type="button" class="btn-add-row"
                                                onclick="addRow('cat-restaurantes')" title="Agregar Fila de Gasto"><i
                                                    class="bx bx-plus"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>

                            {{-- SECCIÓN: COMBUSTIBLE --}}
                            <tbody id="cat-combustible">
                                <tr class="cat-row">
                                    <td colspan="10">
                                        <div class="cat-row-content">
                                            <span><i class="bx bxs-gas-pump"></i> III. Abastecimiento de Combustible</span>
                                            <button type="button" class="btn-add-row"
                                                onclick="addRow('cat-combustible')" title="Agregar Fila de Gasto"><i
                                                    class="bx bx-plus"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>

                            {{-- SECCIÓN: OTROS --}}
                            <tbody id="cat-otros">
                                <tr class="cat-row">
                                    <td colspan="10">
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

                {{-- PANEL INFERIOR: GESTOR DOCUMENTAL Y RESUMEN FINANCIERO --}}
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
                            <div class="summary-row"><span class="sum-lbl">I.S.H. y Retenciones:</span><span
                                    id="sum-ish" class="sum-val">$0.00</span></div>
                            <div class="sum-total-row">
                                <span class="sum-total-lbl">TOTAL A REEMBOLSAR</span>
                                <span id="sum-total" class="sum-total-val" data-value="0">$0.00</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>{{-- /modal-body --}}

            <div class="modal-footer">
                <div>
                    <span class="modal-footer-note">
                        <i class="bx bx-info-circle"></i> Para garantizar una autorización rápida, asegúrate de adjuntar el
                        PDF de soporte.
                    </span>
                </div>

                {{-- MODO DE CREACIÓN --}}
                <div class="modal-footer-right" id="footer-create">
                    <button type="button" class="btn btn-cancel" onclick="closeModal()">Cancelar</button>
                    <button type="button" id="btn-borrador" class="btn btn-secondary" onclick="saveDraft()">
                        <i class="bx bx-save"></i> Guardar Borrador
                    </button>
                    <button type="button" id="btn-enviar" class="btn btn-primary" onclick="verifyAndSubmit()">
                        <i class="bx bx-send"></i> Emitir Solicitud a Revisión
                    </button>
                </div>

                {{-- MODO DE VISUALIZACIÓN --}}
                <div class="modal-footer-right hidden" id="footer-view">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cerrar</button>
                </div>

                {{-- MODO DE EVALUACIÓN --}}
                <div class="modal-footer-right hidden" id="footer-evaluate">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cerrar</button>
                    <button type="button" class="btn btn-fail-solid" onclick="processEvaluation('Rechazado')">
                        <i class="bx bx-x"></i> Rechazar
                    </button>
                    <button type="button" class="btn btn-secondary btn-validate-special" id="btn-eval-validate"
                        onclick="processEvaluation('Validado')">
                        <i class="bx bx-list-check"></i> Validar
                    </button>
                    <button type="button" class="btn btn-ok-solid" id="btn-eval-approve"
                        onclick="processEvaluation('Aprobado')">
                        <i class="bx bx-check-double"></i> Aprobar
                    </button>
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
        /* ── VARIABLES REALES DESDE EL BACKEND ── */
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

        document.getElementById('company-rfc').textContent = rfcEmpresa;

        /* ── ALERTA CENTRALIZADA NORMAL (SWEETALERT2) ── */
        function showAlert(msg, type = 'success') {
            const titles = {
                'success': '¡Operación Exitosa!',
                'error': 'Error de Validación',
                'warning': 'Atención Requerida',
                'info': 'Información'
            };

            Swal.fire({
                title: `<span style="font-family:'Poppins', sans-serif;">${titles[type]}</span>`,
                html: `<span style="font-family:'Poppins', sans-serif; font-size:14px; color:#64748b;">${msg}</span>`,
                icon: type,
                confirmButtonColor: 'var(--teal-dark)',
                confirmButtonText: `<span style="font-family:'Poppins', sans-serif; font-weight:600;">Entendido</span>`
            });
        }

        /* ── NAVEGACIÓN Y ACCESIBILIDAD ── */
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

        /* ── CONTROL DE DELEGACIÓN ── */
        const modalNombre = document.getElementById('modal-nombre');
        const modalDepto = document.getElementById('modal-depto');
        const beneficiaryId = document.getElementById('modal-beneficiary-id');
        const dropdown = document.getElementById('employee-dropdown');
        const iconSolicitante = document.getElementById('icon-solicitante');

        function handleDelegationToggle() {
            const isDelegated = document.getElementById('toggle-delegation').checked;

            if (isDelegated) {
                // Solo si el campo no está bloqueado por el sistema
                if(!document.getElementById('toggle-delegation').disabled) {
                    modalNombre.removeAttribute('readonly');
                    modalNombre.value = ''; // Se limpia para que busque uno nuevo
                    modalNombre.focus();
                }
                modalNombre.placeholder = 'Buscar empleado por nombre...';
                modalNombre.classList.add('reimburse-input-active-search');
                iconSolicitante.className = 'bx bx-search field-icon';
                iconSolicitante.style.color = 'var(--teal-dark)';
            } else {
                modalNombre.setAttribute('readonly', 'true');
                modalNombre.value = sessionUser.nombre;
                modalDepto.value = sessionUser.depto;
                beneficiaryId.value = sessionUser.id;
                modalNombre.classList.remove('reimburse-input-active-search');
                iconSolicitante.className = 'bx bx-user field-icon';
                iconSolicitante.style.color = '#94a3b8';
                dropdown.classList.add('hidden');
            }
        }

        modalNombre.addEventListener('input', function() {
            if (modalNombre.hasAttribute('readonly') || modalNombre.disabled) return;
            const query = this.value.toLowerCase();
            dropdown.innerHTML = '';
            if (query.length < 2) { dropdown.classList.add('hidden'); return; }

            const results = companyEmployees.filter(emp => emp.nombre.toLowerCase().includes(query));
            if (results.length > 0) {
                results.forEach(emp => {
                    const item = document.createElement('div');
                    item.className = 'reimburse-dropdown-item';
                    item.innerHTML = `<strong>${emp.nombre}</strong><small style="color:#64748b; font-size:11px;">${emp.depto}</small>`;
                    item.onclick = () => selectEmployee(emp);
                    dropdown.appendChild(item);
                });
                dropdown.classList.remove('hidden');
            } else {
                dropdown.innerHTML = '<div class="reimburse-dropdown-empty">No se encontraron coincidencias</div>';
                dropdown.classList.remove('hidden');
            }
        });

        function selectEmployee(emp) {
            modalNombre.value = emp.nombre;
            modalDepto.value = emp.depto;
            beneficiaryId.value = emp.id;
            dropdown.classList.add('hidden');
            showAlert(`El beneficiario del reembolso ha sido actualizado a: <strong>${emp.nombre}</strong>`, 'info');
        }

        document.addEventListener('click', function(e) {
            if (!modalNombre.contains(e.target) && !dropdown.contains(e.target)) dropdown.classList.add('hidden');
        });

        /* ── CONTROL DE PESTAÑAS DEL PANEL SAT ── */
        function toggleSatPanel() { document.getElementById('sat-panel').classList.toggle('hidden'); }
        document.querySelectorAll('.sat-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.sat-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.sat-content').forEach(c => c.classList.add('hidden'));
                this.classList.add('active');
                document.getElementById(this.dataset.target).classList.remove('hidden');
            });
        });

        /* ── VARIABLES TABLA MAESTRA ── */
        let requests = {!! json_encode($requestsData) !!};
        let currentEvaluateId = null;

        const fmt = n => new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n);
        document.getElementById('modal-fecha-hoy').textContent = new Date().toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

        let activeFilter = 'all';
        let searchQuery = '';

        document.getElementById('table-search').addEventListener('input', function() { searchQuery = this.value.toLowerCase().trim(); renderDashboard(); });
        document.querySelectorAll('.filter-tab').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                activeFilter = this.dataset.filter;
                renderDashboard();
            });
        });

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
                const matchSearch = !searchQuery || req.motivo.toLowerCase().includes(searchQuery) || req.folioP.toLowerCase().includes(searchQuery) || req.folioU.toLowerCase().includes(searchQuery) || req.nombre.toLowerCase().includes(searchQuery);
                return matchFilter && matchSearch;
            });

            tableCount.textContent = `${filtered.length} solicitud${filtered.length !== 1 ? 'es' : ''} registrada(s)`;
            if (filtered.length === 0) { emptyState.classList.remove('hidden'); return; }
            emptyState.classList.add('hidden');

            filtered.forEach((req) => {
                const globalIdx = requests.findIndex(r => r.id === req.id);
                let badge = '', badgePago = '';

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

                const evaluateBtn = (req.status === 'Pendiente' || req.status === 'Validado') ?
                    `<button class="btn-icon btn-icon-evaluate" onclick="evaluarSolicitud(${req.id})" title="Gestionar Resolución"><i class="bx bx-check-shield"></i></button>` : '';

                list.innerHTML += `
                <tr>
                    <td class="row-index">${globalIdx + 1}</td>
                    <td><span class="row-folio"><i class="bx bx-hash"></i> ${req.folioP}</span></td>
                    <td><span class="row-folio user-folio">${req.folioU}</span></td>
                    <td><span class="row-date">${req.fecha}</span></td>
                    <td><span class="row-name">${req.nombre}</span></td>
                    <td><span class="row-motive">${req.motivo}</span></td>
                    <td><span class="row-depto">${req.depto}</span></td>
                    <td><div class="row-amount-wrap"><span class="row-amount">${fmt(req.amount)}</span><span class="row-amount-label">MXN</span></div></td>
                    <td>${badge}</td>
                    <td>${badgePago}</td>
                    <td class="cell-actions">
                        <div class="actions-wrap">
                            <button class="btn-icon btn-icon-view" onclick="verDetalles(${req.id})" title="Ver o Editar"><i class="bx bx-show"></i></button>
                            ${evaluateBtn}
                        </div>
                    </td>
                </tr>`;
            });
        }

        /* ── INYECCIÓN EN HTML DEL TEMPLATE ── */
        function getRowTemplate(cfdiId = '') {
            return `
            <tr class="data-row">
                <input type="hidden" class="c-cfdi-id" value="${cfdiId}">
                <td><div class="date-wrap"><i class="bx bx-calendar"></i><input type="text" class="cell-input date-in modal-focusable" placeholder="DD/MM/AAAA" data-fp></div></td>
                <td><input type="text" class="cell-input modal-focusable" placeholder="—"></td>
                <td><input type="text" class="cell-input modal-focusable" placeholder="—"></td>
                <td><input type="number" oninput="calcTotal()" class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
                <td><input type="number" oninput="calcTotal()" class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
                <td><input type="number" oninput="calcTotal()" class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
                <td><input type="number" oninput="calcTotal()" class="cell-input num c-ish modal-focusable" placeholder="0.00"></td>
                <td><input type="number" oninput="calcTotal()" class="cell-input num c-iva modal-focusable" placeholder="0.00"></td>
                <td class="cell-row-total">-</td>
                <td class="text-center"><button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Eliminar"><i class="bx bx-trash"></i></button></td>
            </tr>`;
        }

        function addRow(tbodyId, cfdiId = '') {
            const tbody = document.getElementById(tbodyId);
            tbody.insertAdjacentHTML('beforeend', getRowTemplate(cfdiId));
            flatpickr(tbody.lastElementChild.querySelector('[data-fp]'), { locale: "es", dateFormat: "d/m/Y", allowInput: true, disableMobile: "true" });
        }

        function removeRow(btn) {
            const tbody = btn.closest('tbody');
            if (tbody.querySelectorAll('.data-row').length > 1) {
                btn.closest('tr').remove();
                calcTotal();
            } else {
                showAlert('Se requiere de manera obligatoria mantener al menos una fila de captura en la categoría.', 'warning');
            }
        }

        /* ── BLOQUEO / DESBLOQUEO DE FORMULARIO ── */
        function lockForm() {
            // Bloqueamos los inputs generales
            document.querySelectorAll('#reimbursement-modal .modal-focusable, #reimbursement-modal .cell-input').forEach(el => el.setAttribute('disabled', 'true'));
            document.querySelectorAll('input[name="is_deductible"]').forEach(el => el.disabled = true);
            document.querySelectorAll('.btn-add-row, .btn-remove-row').forEach(el => el.classList.add('hidden'));

            document.getElementById('evidence-panel').style.pointerEvents = 'none';
            document.querySelector('.modal-header-actions .btn-secondary').classList.add('hidden');

            // Bloqueamos el Switch y el campo de nombre para que no sean editables en modo Vista
            document.getElementById('toggle-delegation').disabled = true;
            document.getElementById('modal-nombre').setAttribute('readonly', 'true');
            document.getElementById('modal-nombre').disabled = true;
        }

        function unlockForm() {
            document.querySelectorAll('#reimbursement-modal .modal-focusable, #reimbursement-modal .cell-input').forEach(el => el.removeAttribute('disabled'));
            document.querySelectorAll('input[name="is_deductible"]').forEach(el => el.disabled = false);
            document.querySelectorAll('.btn-add-row, .btn-remove-row').forEach(el => el.classList.remove('hidden'));
            document.getElementById('evidence-panel').style.pointerEvents = 'auto';
            document.querySelector('.modal-header-actions .btn-secondary').classList.remove('hidden');

            document.getElementById('toggle-delegation').disabled = false;
            document.getElementById('modal-nombre').disabled = false;

            // Si el toggle está activado, quitamos el readonly para permitir búsqueda
            if (document.getElementById('toggle-delegation').checked) {
                document.getElementById('modal-nombre').removeAttribute('readonly');
            } else {
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

            document.getElementById('modal-motivo').value = '';
            document.getElementById('modal-centro-costos').value = '';
            document.getElementById('sat-panel').classList.add('hidden');
            document.getElementById('sat-result-container').classList.add('hidden');
            document.getElementById('rejection-container').classList.add('hidden');

            tempSatData = null; currentActiveClaimId = null; isEditMode = false;

            const toggleDel = document.getElementById('toggle-delegation');
            if (toggleDel) { toggleDel.checked = false; handleDelegationToggle(); }
            document.getElementById('delegation-container').classList.remove('hidden');

            evidenciasFiles = []; renderFileList(); actualizarInputFiles(); calcTotal();
            unlockForm();
        }

        /* ── MODOS DEL MODAL ── */
        function openModalForCreate() {
            resetModalForm();
            document.getElementById('main-modal-title').innerHTML = '<i class="bx bx-receipt"></i> Generación de <strong>Reembolso Múltiple</strong>';
            document.getElementById('modal-folio-p').innerHTML = '<span class="status-badge badge-draft" style="border:none; padding: 2px 6px;">Asignación Automática</span>';
            document.getElementById('modal-folio-u').innerHTML = '<span class="status-badge badge-draft" style="border:none; padding: 2px 6px;">Automático</span>';

            document.querySelector('input[name="tipo_gasto"][value="viaje"]').checked = true;
            document.querySelector('input[name="is_deductible"][value="1"]').checked = true;

            document.getElementById('btn-enviar').innerHTML = '<i class="bx bx-send"></i> Emitir Solicitud a Revisión';
            document.getElementById('btn-borrador').classList.remove('hidden');

            document.getElementById('footer-create').classList.remove('hidden');
            document.getElementById('footer-view').classList.add('hidden');
            document.getElementById('footer-evaluate').classList.add('hidden');
            document.getElementById('reimbursement-modal').classList.remove('hidden');

            ['cat-vuelos', 'cat-restaurantes', 'cat-combustible', 'cat-otros'].forEach(cat => {
                if (document.getElementById(cat).querySelectorAll('.data-row').length === 0) { addRow(cat); }
            });
        }

        async function fetchAndPopulateClaim(id) {
            Swal.fire({ title: 'Cargando Documento...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
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

                    // Ajuste estético delegado sin borrar el valor cargado de la BD
                    if (claim.user_id !== sessionUser.id) {
                        document.getElementById('toggle-delegation').checked = true;
                        iconSolicitante.className = 'bx bx-search field-icon';
                        iconSolicitante.style.color = 'var(--teal-dark)';
                        modalNombre.classList.add('reimburse-input-active-search');
                    } else {
                        document.getElementById('toggle-delegation').checked = false;
                        iconSolicitante.className = 'bx bx-user field-icon';
                        iconSolicitante.style.color = '#94a3b8';
                        modalNombre.classList.remove('reimburse-input-active-search');
                    }

                    // Asignar los valores inyectados de la consulta
                    document.getElementById('modal-nombre').value = claim.beneficiary.name;
                    document.getElementById('modal-depto').value = claim.beneficiary.employee?.area?.name || 'Sin asignar';
                    document.getElementById('modal-beneficiary-id').value = claim.user_id;

                    document.getElementById('modal-centro-costos').value = claim.cost_center;
                    document.getElementById('modal-motivo').value = claim.motive;
                    document.querySelector(`input[name="tipo_gasto"][value="${claim.category}"]`).checked = true;
                    document.querySelector(`input[name="is_deductible"][value="${claim.is_deductible ? '1' : '0'}"]`).checked = true;

                    ['cat-vuelos', 'cat-restaurantes', 'cat-combustible', 'cat-otros'].forEach(cat => {
                        document.getElementById(cat).querySelectorAll('.data-row').forEach(r => r.remove());
                    });

                    claim.lines.forEach(line => {
                        const cat = line.concept_group;
                        addRow(cat, line.expense_cfdi_id || '');

                        const targetRow = document.getElementById(cat).lastElementChild;
                        const inputs = targetRow.querySelectorAll('.cell-input');

                        const dateOnly = line.expense_date.substring(0, 10);
                        const [y, m, d] = dateOnly.split('-');

                        if (inputs[0]._flatpickr) { inputs[0]._flatpickr.setDate(dateOnly, true, "Y-m-d"); }
                        else { inputs[0].value = `${d}/${m}/${y}`; }

                        inputs[1].value = line.document_number || '';
                        inputs[2].value = line.description || '';
                        inputs[3].value = line.amount_fiscal > 0 ? line.amount_fiscal : '';
                        inputs[4].value = line.amount_simple > 0 ? line.amount_simple : '';
                        inputs[5].value = line.amount_none > 0 ? line.amount_none : '';
                        inputs[6].value = line.tax_ish !== '0.00' ? line.tax_ish : '';
                        inputs[7].value = line.tax_iva > 0 ? line.tax_iva : '';
                    });

                    ['cat-vuelos', 'cat-restaurantes', 'cat-combustible', 'cat-otros'].forEach(cat => {
                        if (document.getElementById(cat).querySelectorAll('.data-row').length === 0) { addRow(cat); }
                    });

                    calcTotal();
                    Swal.close();
                    return claim;
                }
            } catch (error) {
                console.error(error);
                showAlert('Error del servidor: No se pudo cargar la información.', 'error');
            }
        }

        async function verDetalles(id) {
            resetModalForm();
            const claim = await fetchAndPopulateClaim(id);
            if(!claim) return;

            document.getElementById('main-modal-title').innerHTML = `<i class="bx bx-search-alt"></i> Inspección del <strong>Folio: ${claim.folio_system}</strong>`;

            lockForm(); // ── BLOQUEAMOS EDICIÓN EN MODO VISTA ──

            document.getElementById('footer-create').classList.add('hidden');
            document.getElementById('footer-view').classList.remove('hidden');
            document.getElementById('footer-evaluate').classList.add('hidden');

            const viewFooter = document.getElementById('footer-view');
            viewFooter.innerHTML = '<button type="button" class="btn btn-secondary" onclick="closeModal()">Cerrar</button>';

            // Si es Borrador o fue Rechazado, damos opción de corregir
            if (claim.status_review === 'Borrador' || claim.status_review === 'Rechazado') {
                const btnEdit = document.createElement('button');
                btnEdit.type = 'button';
                btnEdit.className = 'btn btn-primary';
                btnEdit.innerHTML = '<i class="bx bx-edit"></i> ' + (claim.status_review === 'Rechazado' ? 'Corregir y Reenviar' : 'Continuar Borrador');
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
            if(!claim) return;

            document.getElementById('main-modal-title').innerHTML = '<i class="bx bx-check-shield"></i> Ejecución de Dictamen Administrativo';
            currentEvaluateId = id;

            lockForm(); // ── EN EVALUACIÓN NADIE EDITA LOS CAMPOS ──

            const btnValidate = document.getElementById('btn-eval-validate');
            const btnApprove = document.getElementById('btn-eval-approve');

            if (claim.status_review === 'Pendiente') { btnValidate.classList.remove('hidden'); btnApprove.classList.add('hidden'); }
            else if (claim.status_review === 'Validado') { btnValidate.classList.add('hidden'); btnApprove.classList.remove('hidden'); }

            document.getElementById('footer-create').classList.add('hidden');
            document.getElementById('footer-view').classList.add('hidden');
            document.getElementById('footer-evaluate').classList.remove('hidden');
            document.getElementById('reimbursement-modal').classList.remove('hidden');
        }

        function closeModal() { document.getElementById('reimbursement-modal').classList.add('hidden'); }

        function processEvaluation(status) {
            if (!currentEvaluateId) return;
            let actionText = status === 'Aprobado' ? 'Aprobar Definitivamente' : (status === 'Validado' ? 'Dar Visto Bueno a Documentación' : 'Denegar y Rechazar');
            let confirmColor = status === 'Aprobado' ? 'var(--teal-dark)' : (status === 'Validado' ? '#0284c7' : '#ef4444');

            if (status === 'Rechazado') {
                Swal.fire({
                    title: `<span style="font-family:'Poppins', sans-serif;">Motivo de Rechazo</span>`,
                    html: `<span style="font-family:'Poppins', sans-serif; color:#64748b; font-size: 0.85rem;">Explica por qué no procede esta solicitud. El usuario podrá ver el comentario y corregirlo.</span>`,
                    input: 'textarea',
                    inputPlaceholder: 'Ej. Montos incorrectos, falta factura de hotel...',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: confirmColor,
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: `Rechazar Folio`,
                    cancelButtonText: `Cancelar`,
                    inputValidator: (value) => { if (!value.trim()) return '¡Es obligatorio justificar el rechazo para auditoría!'; }
                }).then((result) => {
                    if (result.isConfirmed) { updateStatus(currentEvaluateId, status, result.value); closeModal(); currentEvaluateId = null; }
                });
            } else {
                Swal.fire({
                    title: `<span style="font-family:'Poppins', sans-serif;">¿Emisión de Dictamen Final?</span>`,
                    html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">Desea <strong>${actionText}</strong> este folio?</span>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: confirmColor,
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: `Autorizar Movimiento`,
                    cancelButtonText: `Cancelar Acción`
                }).then((result) => {
                    if (result.isConfirmed) { updateStatus(currentEvaluateId, status, null); closeModal(); currentEvaluateId = null; }
                });
            }
        }

        async function updateStatus(id, status, comments) {
            try {
                Swal.fire({ title: 'Dictaminando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                let formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('new_status', status);
                if (comments) formData.append('comments', comments);

                const statusUrl = `{{ url('administration/expense-claims/reimbursements') }}/${id}/status`;
                const response = await fetch(statusUrl, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await response.json();

                if (data.success) {
                    Swal.fire('Completado', data.message, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else { showAlert(data.message, 'error'); }
            } catch (error) { showAlert('No se pudo comunicar con el servidor.', 'error'); }
        }

        /* ── GESTOR PDF ── */
        let evidenciasFiles = [];
        const maxFileSize = 10 * 1024 * 1024;
        const evidenciaInput = document.getElementById('evidence-upload');
        const evidenciaPanel = document.getElementById('evidence-panel');

        evidenciaInput.addEventListener('change', function(e) { procesarArchivosEvidencia(e.target.files); this.value = ''; });
        evidenciaPanel.addEventListener('dragover', e => { e.preventDefault(); evidenciaPanel.classList.add('dragover'); });
        evidenciaPanel.addEventListener('dragleave', e => { e.preventDefault(); evidenciaPanel.classList.remove('dragover'); });
        evidenciaPanel.addEventListener('drop', e => { e.preventDefault(); evidenciaPanel.classList.remove('dragover'); if (e.dataTransfer.files.length) procesarArchivosEvidencia(e.dataTransfer.files); });

        function procesarArchivosEvidencia(files) {
            let errorSize = false, errorType = false;
            Array.from(files).forEach(file => {
                if (file.type !== 'application/pdf') { errorType = true; return; }
                if (file.size > maxFileSize) { errorSize = true; return; }
                if (!evidenciasFiles.some(f => f.name === file.name)) evidenciasFiles.push(file);
            });
            if (errorType) showAlert('Aviso: Solo se permiten subir documentos en formato PDF.', 'warning');
            if (errorSize) showAlert('Límite de tamaño: Uno o más archivos superan el máximo permitido de 10 MB.', 'error');
            renderFileList(); actualizarInputFiles();
        }

        function removeFile(index) { evidenciasFiles.splice(index, 1); renderFileList(); actualizarInputFiles(); }
        function actualizarInputFiles() { const dt = new DataTransfer(); evidenciasFiles.forEach(file => dt.items.add(file)); evidenciaInput.files = dt.files; }
        function formatBytes(bytes, decimals = 2) { if (!+bytes) return '0 Bytes'; const k = 1024, dm = decimals < 0 ? 0 : decimals, sizes = ['Bytes', 'KB', 'MB', 'GB'], i = Math.floor(Math.log(bytes) / Math.log(k)); return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`; }
        function renderFileList() { const listDiv = document.getElementById('evidence-list'); listDiv.innerHTML = evidenciasFiles.length > 0 ? `<div class="file-grid">${evidenciasFiles.map((f, i) => `<div class="file-card"><i class="bx bxs-file-pdf file-icon-lg"></i><div class="file-info"><span class="file-name" title="${f.name}">${f.name}</span><span class="file-size">${formatBytes(f.size)}</span></div><button type="button" class="btn-remove-file" onclick="event.stopPropagation(); removeFile(${i})"><i class="bx bx-x"></i></button></div>`).join('')}</div>` : ''; }

        /* ── INTERACCIÓN SAT & BÚSQUEDA ── */
        let tempSatData = null;

        async function buscarFactura() {
            const uuid = document.getElementById('search-uuid').value.trim();
            const btnB = document.getElementById('btn-buscar');
            if (uuid.length !== 36) { showAlert('Revisión requerida: El código UUID ingresado debe contener exactamente 36 caracteres.', 'warning'); return; }
            btnB.innerHTML = '<span class="spinner"></span> Consultando...'; btnB.disabled = true;

            try {
                const response = await fetch(`{{ route('expense-claims.cfdi.search') }}?uuid=${uuid}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await response.json();

                if (response.ok && data.success) {
                    const cfdi = data.data;
                    let serieFolio = '';
                    if (cfdi.serie) serieFolio += cfdi.serie + '-';
                    if (cfdi.folio) serieFolio += cfdi.folio;
                    if (!serieFolio) serieFolio = cfdi.uuid.substring(0, 8);

                    tempSatData = {
                        id: cfdi.id,
                        fecha_iso: cfdi.issue_date.split(' ')[0],
                        folio: serieFolio,
                        desc: cfdi.concept_summary || 'Servicios amparados por UUID',
                        sub: parseFloat(cfdi.subtotal) || 0, iva: parseFloat(cfdi.tax_iva) || 0,
                        ish: (parseFloat(cfdi.tax_ish) || 0) - (parseFloat(cfdi.tax_retenciones) || 0)
                    };

                    document.getElementById('sat-result-uuid').textContent = cfdi.uuid;
                    document.getElementById('sat-result-container').classList.remove('hidden');

                    showAlert('El comprobante fiscal se ha localizado exitosamente en la bóveda.', 'success');
                } else { showAlert(data.message, 'warning'); }
            } catch (error) { showAlert('Falla de Red: Imposible contactar con la bóveda satelital.', 'error'); }
            finally { btnB.innerHTML = '<i class="bx bx-search"></i> Buscar'; btnB.disabled = false; }
        }

        const dropZoneUI = document.getElementById('drop-zone');
        const xmlInput = document.getElementById('xml-input');
        dropZoneUI.addEventListener('click', () => xmlInput.click());
        xmlInput.addEventListener('change', e => leerXML(e.target.files[0]));
        dropZoneUI.addEventListener('dragover', e => { e.preventDefault(); dropZoneUI.classList.add('dragover'); });
        dropZoneUI.addEventListener('dragleave', e => { e.preventDefault(); dropZoneUI.classList.remove('dragover'); });
        dropZoneUI.addEventListener('drop', e => { e.preventDefault(); dropZoneUI.classList.remove('dragover'); if (e.dataTransfer.files.length) leerXML(e.dataTransfer.files[0]); });

        async function leerXML(file) {
            if (!file || file.type !== 'text/xml') { showAlert('Por favor, asegúrese de seleccionar un archivo con extensión .xml válido.', 'error'); return; }
            Swal.fire({ title: 'Procesando XML...', text: 'Validando ante la Bóveda del Sistema.', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            let formData = new FormData();
            formData.append('xml_file', file);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch('{{ route('expense-claims.cfdi.upload') }}', { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await response.json();

                if (response.ok && data.success) {
                    Swal.close(); const cfdi = data.data;
                    document.getElementById('search-uuid').value = cfdi.uuid;

                    let serieFolio = '';
                    if (cfdi.serie) serieFolio += cfdi.serie + '-';
                    if (cfdi.folio) serieFolio += cfdi.folio;
                    if (!serieFolio) serieFolio = cfdi.uuid.substring(0, 8);

                    tempSatData = {
                        id: cfdi.id,
                        fecha_iso: cfdi.issue_date.split(' ')[0],
                        folio: serieFolio, desc: cfdi.concept_summary || 'Gasto importado (XML)',
                        sub: parseFloat(cfdi.subtotal) || 0, iva: parseFloat(cfdi.tax_iva) || 0,
                        ish: (parseFloat(cfdi.tax_ish) || 0) - (parseFloat(cfdi.tax_retenciones) || 0)
                    };

                    document.getElementById('sat-result-uuid').textContent = cfdi.uuid;
                    document.getElementById('sat-result-container').classList.remove('hidden');

                    showAlert(data.message, 'success');
                } else { showAlert(data.message || 'El XML proporcionado presenta inconsistencias fiscales.', 'error'); }
            } catch (error) { showAlert('No se pudo establecer conexión con el servidor de análisis.', 'error'); }
        }

        function agregarFilaDesdeSAT() {
            const cat = document.getElementById('sat-category').value;
            if (!cat) { showAlert('Debe indicar en qué categoría se contabilizará este gasto antes de integrarlo.', 'warning'); return; }
            if (!tempSatData) return;

            const tbody = document.getElementById(cat);
            const dataRows = tbody.querySelectorAll('.data-row');
            let targetRow = null;

            if (dataRows.length > 0) {
                const lastRow = dataRows[dataRows.length - 1];
                const inputs = lastRow.querySelectorAll('.cell-input');
                if (!inputs[1].value.trim() && !inputs[2].value.trim()) targetRow = lastRow;
            }

            if (!targetRow) { addRow(cat, tempSatData.id); targetRow = tbody.lastElementChild; }

            let hiddenCfdiInput = targetRow.querySelector('.c-cfdi-id');
            if (hiddenCfdiInput) hiddenCfdiInput.value = tempSatData.id || '';

            const inputs = targetRow.querySelectorAll('.cell-input');
            if (inputs[0]._flatpickr && tempSatData.fecha_iso) { inputs[0]._flatpickr.setDate(tempSatData.fecha_iso, true, "Y-m-d"); }
            else if (tempSatData.fecha_iso) { const [y, m, d] = tempSatData.fecha_iso.split('-'); inputs[0].value = `${d}/${m}/${y}`; }

            inputs[1].value = tempSatData.folio;
            inputs[2].value = tempSatData.desc;
            inputs[3].value = tempSatData.sub;
            inputs[4].value = ''; inputs[5].value = '';
            inputs[6].value = tempSatData.ish;
            inputs[7].value = tempSatData.iva;

            calcTotal();
            document.getElementById('sat-result-container').classList.add('hidden');
            document.getElementById('sat-category').value = '';
            tempSatData = null;

            // Reemplazo del Toast por Alerta Normal
            showAlert('El comprobante fiscal ha sido inyectado correctamente en la matriz de gastos.', 'success');
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

                gFiscal += rFiscal; gSimple += rSimple; gPropina += rPropina; gIsh += rIsh; gIva += rIva;
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

        /* ── VALIDACIONES ORDENADAS PARA GUARDAR Y ENVIAR ── */
        function verifyAndSubmit() {
            const nombreBen = document.getElementById('modal-nombre').value.trim();
            const motivo = document.getElementById('modal-motivo').value.trim();
            const centroCosto = document.getElementById('modal-centro-costos').value;
            const total = parseFloat(document.getElementById('sum-total').getAttribute('data-value'));

            if (!nombreBen) {
                showAlert('Debe asignar o buscar el nombre del beneficiario.', 'warning');
                return;
            }
            if (!centroCosto) {
                showAlert('Debe asignar el Centro de Costos correspondiente al departamento responsable.', 'warning');
                return;
            }
            if (!motivo) {
                showAlert('Es obligatorio detallar la justificación o motivo de la erogación para auditoría.', 'warning');
                return;
            }

            let hasValidRows = false;
            document.querySelectorAll('.data-row').forEach(row => {
                const dateVal = row.querySelector('.cell-input').value;
                if (dateVal) hasValidRows = true;
            });

            if (!hasValidRows) {
                showAlert('El comprobante no tiene información válida. Registre al menos un concepto de gasto en la matriz.', 'error');
                return;
            }

            if (total <= 0) {
                showAlert('La sumatoria contable debe ser mayor a cero. Verifique los importes desglosados en las filas.', 'error');
                return;
            }

            Swal.fire({
                title: `<span style="font-family:'Poppins', sans-serif;">¿Confirmar Revisión Gerencial?</span>`,
                html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">Los datos por un valor de <strong>${fmt(total)}</strong> quedarán temporalmente inmutables y la solicitud será procesada.</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: 'var(--teal-dark)',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: `Emitir Responsiva`,
                cancelButtonText: `Cancelar`
            }).then((result) => { if (result.isConfirmed) procesarEnvio('Pendiente', 'En espera'); });
        }

        function saveDraft() {
            const nombreBen = document.getElementById('modal-nombre').value.trim();
            const motivo = document.getElementById('modal-motivo').value.trim();

            if (!nombreBen) {
                showAlert('Para salvaguardar un borrador, debe existir un nombre de beneficiario.', 'warning');
                return;
            }
            if (!motivo) {
                showAlert('Para salvaguardar su información parcial, requerimos al menos capturar el Motivo de la Erogación.', 'warning');
                return;
            }
            procesarEnvio('Borrador', 'N/A');
        }

        /* ── EMPAQUETADO FINAL (POST) ── */
        async function procesarEnvio(estadoRevision, estadoPago) {
            let lineasArray = [];
            ['cat-vuelos', 'cat-restaurantes', 'cat-combustible', 'cat-otros'].forEach(cat => {
                const rows = document.getElementById(cat).querySelectorAll('.data-row');
                rows.forEach(row => {
                    const cfdiIdInput = row.querySelector('.c-cfdi-id');
                    const inputs = row.querySelectorAll('.cell-input');
                    if (inputs[0].value) {
                        lineasArray.push({
                            categoria: cat,
                            cfdi_id: cfdiIdInput ? cfdiIdInput.value : null,
                            fecha: inputs[0].value, folio: inputs[1].value, descripcion: inputs[2].value,
                            monto_fiscal: parseFloat(inputs[3].value) || 0, monto_simple: parseFloat(inputs[4].value) || 0, monto_sin: parseFloat(inputs[5].value) || 0,
                            ish: parseFloat(inputs[6].value) || 0, iva: parseFloat(inputs[7].value) || 0,
                            total_linea: parseFloat(row.querySelector('.cell-row-total').textContent.replace(/[^0-9.-]+/g, "")) || 0
                        });
                    }
                });
            });

            if (lineasArray.length === 0 && estadoRevision !== 'Borrador') {
                showAlert('El sistema ha detectado una matriz vacía. Debe agregar conceptos contables.', 'error');
                return;
            }

            let totalSubtotal = 0;
            ['sum-fiscal', 'sum-simple', 'sum-propinas'].forEach(id => { totalSubtotal += parseFloat(document.getElementById(id).textContent.replace(/[^0-9.-]+/g, "")) || 0; });

            let formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('motivo', document.getElementById('modal-motivo').value.trim());
            formData.append('is_deductible', document.querySelector('input[name="is_deductible"]:checked').value);
            formData.append('centro_costo', document.getElementById('modal-centro-costos').value);
            formData.append('tipo_gasto', document.querySelector('input[name="tipo_gasto"]:checked').value);
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
                Swal.fire({ title: 'Sincronizando Base de Datos...', text: 'Registrando matrices y empaquetando evidencias documentales.', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                let fetchUrl = '{{ route('expense-claims.store') }}';
                if (isEditMode) { fetchUrl = `{{ url('administration/expense-claims/reimbursements') }}/${currentActiveClaimId}/update`; }

                const response = await fetch(fetchUrl, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await response.json();

                if (data.success) {
                    Swal.fire({ title: 'Trámite Procesado', html: data.message + '<br><strong>Folio Asignado: ' + data.folio + '</strong>', icon: 'success', confirmButtonColor: 'var(--teal-dark)' });
                    closeModal(); setTimeout(() => window.location.reload(), 1800);
                } else { showAlert(data.message || 'Error del servidor en la sincronización de datos.', 'error'); }
            } catch (error) { showAlert('No se pudo establecer el enlace seguro con los servidores internos.', 'error'); }
        }

        const searchUuidInput = document.getElementById('search-uuid');
        const uuidDropdown = document.getElementById('uuid-dropdown');
        let uuidTimeout = null;

        searchUuidInput.addEventListener('input', function() {
            clearTimeout(uuidTimeout); const term = this.value.trim();
            if (term.length < 2) { uuidDropdown.classList.add('hidden'); return; }

            uuidTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`{{ route('expense-claims.cfdi.autocomplete') }}?term=${term}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    const data = await response.json(); uuidDropdown.innerHTML = '';

                    if (data.length > 0) {
                        data.forEach(cfdi => {
                            const item = document.createElement('div'); item.className = 'sat-dropdown-item';
                            let serieFolio = '';
                            if (cfdi.serie) serieFolio += cfdi.serie + '-';
                            if (cfdi.folio) serieFolio += cfdi.folio;
                            const folioBadge = serieFolio ? `<span style="background: var(--teal-dark); color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 600;">Folio: ${serieFolio}</span>` : '';

                            item.innerHTML = `
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <strong style="color: var(--teal-light); font-family: monospace; font-size: 0.85rem;">${cfdi.uuid.substring(0, 13)}...</strong>
                                    ${folioBadge}
                                </div>
                                <span style="display: block; color: #cbd5e1; font-size: 0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <i class="bx bx-store-alt"></i> ${cfdi.issuer_name} &nbsp;&nbsp;|&nbsp;&nbsp; <strong>${fmt(cfdi.total)}</strong>
                                </span>`;
                            item.onclick = () => { searchUuidInput.value = cfdi.uuid; uuidDropdown.classList.add('hidden'); buscarFactura(); };
                            uuidDropdown.appendChild(item);
                        });
                    } else { uuidDropdown.innerHTML = '<div class="sat-dropdown-empty">El código introducido no existe en nuestra base receptora.</div>'; }
                    uuidDropdown.classList.remove('hidden');
                } catch (error) { console.error("Error al autocompletar UUID:", error); }
            }, 300);
        });

        document.addEventListener('click', function(e) { if (!searchUuidInput.contains(e.target) && !uuidDropdown.contains(e.target)) uuidDropdown.classList.add('hidden'); });

        renderDashboard();
    </script>
@endpush
