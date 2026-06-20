{{-- ════════════════════════════════════════════════════════════════════════════
     VISTA BLADE: PANEL DE REEMBOLSOS (reembolsos.blade.php)
     ════════════════════════════════════════════════════════════════════════════ --}}
@extends('modules.administration.expense-claims.index')

@section('content')
    <div class="reimbursements-container">

        {{-- ── ENCABEZADO PRINCIPAL DE LA VISTA ── --}}
        <header class="view-header">
            <div>
                <h2 class="view-title">Panel de <strong>Reembolsos</strong></h2>
                <p class="view-subtitle">
                    <i class="bx bx-line-chart"></i>
                    Administra el historial de gastos, valida facturas y gestiona comprobaciones departamentales.
                </p>
            </div>
            <button class="btn btn-primary" onclick="openModalForCreate()" aria-label="Crear un nuevo reembolso">
                <i class="bx bx-plus-circle"></i> Nuevo Reembolso
            </button>
        </header>

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
                <p class="empty-desc">No hay solicitudes que coincidan con los criterios de búsqueda o filtro aplicados en este momento.</p>
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

                {{-- CABECERA INFORMATIVA DEL REEMBOLSO --}}
                <div class="form-header-card">
                    <div class="fh-info-strip">
                        <div class="fh-info-item">
                            <span>RFC Empresa Matriz / Emisor</span>
                            <strong id="res-rfc">VES0000000</strong>
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
                                <input type="text" id="modal-lugar" value="VHSA, TAB." class="input-field input-location modal-focusable">
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
                                        <input type="radio" name="tipo_gasto" value="viaje" class="modal-focusable" checked>
                                        <i class="bx bxs-plane-alt"></i> Viáticos y Viaje
                                    </label>
                                    <label class="radio-pill-label">
                                        <input type="radio" name="tipo_gasto" value="operacion" class="modal-focusable">
                                        <i class="bx bx-briefcase"></i> Operaciones y Campo
                                    </label>
                                    <label class="radio-pill-label">
                                        <input type="radio" name="tipo_gasto" value="otros" class="modal-focusable">
                                        <i class="bx bx-dots-horizontal-rounded"></i> Diversos / Otros
                                    </label>
                                </div>
                            </div>

                            {{-- TOGGLE DE CAPTURA DELEGADA --}}
                            <div class="delegation-wrapper" id="delegation-container">
                                <div class="delegation-toggle-wrap">
                                    <label class="switch">
                                        <input type="checkbox" id="toggle-delegation" onchange="handleDelegationToggle()">
                                        <span class="slider round"></span>
                                    </label>
                                    <span class="delegation-text">Capturar Otro Beneficiario</span>
                                </div>
                            </div>
                        </div>

                        <div class="fh-grid-4">
                            {{-- BUSCADOR DE USUARIO INTEGRADO CON NUEVA CLASE EXCLUSIVA --}}
                            <div>
                                <label class="input-label">Nombre del Beneficiario</label>
                                <div class="input-group reimburse-dropdown-container">
                                    <i class="bx bx-user field-icon" id="icon-solicitante"></i>
                                    <input type="hidden" id="modal-beneficiary-id" value="1">
                                    <input type="text" id="modal-nombre" value="{{ $userData['nombre'] ?? 'Saul Falcon Perez' }}" class="input-field" readonly autocomplete="off">
                                    <div id="employee-dropdown" class="reimburse-custom-dropdown hidden"></div>
                                </div>
                            </div>

                            <div>
                                <label class="input-label">Área de Adscripción</label>
                                <div class="input-group">
                                    <i class="bx bx-buildings field-icon"></i>
                                    <input type="text" id="modal-depto" value="{{ $userData['departamento'] ?? 'Desarrollo de Software' }}" class="input-field" readonly>
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
                        {{-- Pestaña 1: Búsqueda Manual --}}
                        <div id="sat-tab-uuid" class="sat-content active">
                            <label class="sat-label">Folio Fiscal (UUID) del comprobante SAT</label>
                            <div class="sat-search-group">
                                <div class="sat-input-wrap">
                                    <i class="bx bx-barcode"></i>
                                    <input type="text" id="search-uuid" class="sat-input modal-focusable"
                                        placeholder="550E8400-E29B-41D4-A716-446655440000" autocomplete="off">
                                </div>
                                <button type="button" id="btn-buscar" class="btn btn-primary" onclick="buscarFactura()">
                                    <i class="bx bx-search"></i> Buscar
                                </button>
                            </div>
                        </div>

                        {{-- Pestaña 2: Carga de Archivo --}}
                        <div id="sat-tab-xml" class="sat-content hidden">
                            <div id="drop-zone" class="sat-drop-zone">
                                <i class="bx bx-cloud-upload"></i>
                                <p>Arrastra tu archivo .XML aquí o haz clic para examinar</p>
                                <small>El sistema extraerá automáticamente el UUID, RFC y Fecha.</small>
                                <input type="file" id="xml-input" accept=".xml" class="hidden">
                            </div>
                        </div>

                        {{-- ÁREA DE RESULTADO Y ASIGNACIÓN (Compartida) --}}
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
                                    <th    class="th-w-100">Folio/Num. Fac.</th>
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
                                            <span><i class="bx bxs-plane-alt"></i> I. Transportación, Vuelos y Peajes</span>
                                            <button type="button" class="btn-add-row" onclick="addRow('cat-vuelos')" title="Agregar Fila de Gasto"><i class="bx bx-plus"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="data-row">
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
                                </tr>
                            </tbody>

                            {{-- SECCIÓN: RESTAURANTES --}}
                            <tbody id="cat-restaurantes">
                                <tr class="cat-row">
                                    <td colspan="10">
                                        <div class="cat-row-content">
                                            <span><i class="bx bx-restaurant"></i> II. Consumo de Alimentos y Restaurantes</span>
                                            <button type="button" class="btn-add-row" onclick="addRow('cat-restaurantes')" title="Agregar Fila de Gasto"><i class="bx bx-plus"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="data-row">
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
                                </tr>
                            </tbody>

                            {{-- SECCIÓN: COMBUSTIBLE --}}
                            <tbody id="cat-combustible">
                                <tr class="cat-row">
                                    <td colspan="10">
                                        <div class="cat-row-content">
                                            <span><i class="bx bxs-gas-pump"></i> III. Abastecimiento de Combustible</span>
                                            <button type="button" class="btn-add-row" onclick="addRow('cat-combustible')" title="Agregar Fila de Gasto"><i class="bx bx-plus"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="data-row">
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
                                </tr>
                            </tbody>

                            {{-- SECCIÓN: OTROS --}}
                            <tbody id="cat-otros">
                                <tr class="cat-row">
                                    <td colspan="10">
                                        <div class="cat-row-content">
                                            <span><i class="bx bx-package"></i> IV. Cargos Varios / Misceláneos</span>
                                            <button type="button" class="btn-add-row" onclick="addRow('cat-otros')" title="Agregar Fila de Gasto"><i class="bx bx-plus"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="data-row">
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
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- PANEL INFERIOR: GESTOR DOCUMENTAL Y RESUMEN FINANCIERO --}}
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
                            <div class="summary-row"><span class="sum-lbl">Sub-Total Neto:</span><span id="sum-subtotal" class="sum-val">$0.00</span></div>
                            <div class="summary-row"><span class="sum-lbl">Suma Erogada (Base):</span><span id="sum-gastos" class="sum-val">$0.00</span></div>
                            <div class="summary-row"><span class="sum-lbl">Impuesto (I.V.A.):</span><span id="sum-iva" class="sum-val">$0.00</span></div>
                            <div class="summary-row"><span class="sum-lbl">Impuestos Locales (I.S.H.):</span><span id="sum-ish" class="sum-val">$0.00</span></div>
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
                        <i class="bx bx-info-circle"></i> Para garantizar una autorización rápida, asegúrate de adjuntar el PDF de soporte.
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
                    <button type="button" class="btn btn-secondary btn-validate-special" id="btn-eval-validate" onclick="processEvaluation('Validado')">
                        <i class="bx bx-list-check"></i> Validar
                    </button>
                    <button type="button" class="btn btn-ok-solid" id="btn-eval-approve" onclick="processEvaluation('Aprobado')">
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
        /* ── VARIABLES DE SESIÓN (Logueado) ── */
        const sessionUser = {
            id: 1,
            nombre: "Saul Falcon Perez",
            depto: "Desarrollo de Software",
            rfc: "VES0000000"
        };

        /* ── SIMULADOR DB DE EMPLEADOS PARA BÚSQUEDA ── */
        const companyEmployees = [
            { id: 1, nombre: "Saul Falcon Perez", depto: "Desarrollo de Software", rfc: "VES0000000" },
            { id: 2, nombre: "Carlos Izquierdo", depto: "Calidad y QHSE", rfc: "IZQC850101ABC" },
            { id: 3, nombre: "Yanuri Martinez", depto: "Operaciones", rfc: "MARY900101DEF" },
            { id: 4, nombre: "Jasiel", depto: "Administración y Finanzas", rfc: "JAS990101GHI" }
        ];

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

        /* ── CONTROL DE DELEGACIÓN Y AUTOCOMPLETADO (CON NUEVAS CLASES) ── */
        const modalNombre = document.getElementById('modal-nombre');
        const modalDepto = document.getElementById('modal-depto');
        const resRfc = document.getElementById('res-rfc');
        const beneficiaryId = document.getElementById('modal-beneficiary-id');
        const dropdown = document.getElementById('employee-dropdown');
        const iconSolicitante = document.getElementById('icon-solicitante');

        function handleDelegationToggle() {
            const isDelegated = document.getElementById('toggle-delegation').checked;

            if (isDelegated) {
                modalNombre.removeAttribute('readonly');
                modalNombre.value = '';
                modalNombre.placeholder = 'Buscar empleado por nombre...';
                modalNombre.focus();
                modalNombre.classList.add('reimburse-input-active-search');
                iconSolicitante.className = 'bx bx-search field-icon';
                iconSolicitante.style.color = 'var(--teal-dark)';
            } else {
                modalNombre.setAttribute('readonly', 'true');
                modalNombre.value = sessionUser.nombre;
                modalDepto.value = sessionUser.depto;
                resRfc.textContent = sessionUser.rfc;
                beneficiaryId.value = sessionUser.id;
                modalNombre.classList.remove('reimburse-input-active-search');
                iconSolicitante.className = 'bx bx-user field-icon';
                iconSolicitante.style.color = '#94a3b8';
                dropdown.classList.add('hidden');
            }
        }

        modalNombre.addEventListener('input', function() {
            if (modalNombre.hasAttribute('readonly')) return;

            const query = this.value.toLowerCase();
            dropdown.innerHTML = '';

            if (query.length < 2) {
                dropdown.classList.add('hidden');
                return;
            }

            const results = companyEmployees.filter(emp => emp.nombre.toLowerCase().includes(query));

            if (results.length > 0) {
                results.forEach(emp => {
                    const item = document.createElement('div');
                    item.className = 'reimburse-dropdown-item';
                    item.innerHTML = `<strong>${emp.nombre}</strong>`;
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
            resRfc.textContent = emp.rfc;
            beneficiaryId.value = emp.id;
            dropdown.classList.add('hidden');
            showToast(`Beneficiario actualizado a: ${emp.nombre}`, 'success');
        }

        document.addEventListener('click', function(e) {
            if (!modalNombre.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

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

        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 4500, timerProgressBar: true,
            didOpen: t => { t.addEventListener('mouseenter', Swal.stopTimer); t.addEventListener('mouseleave', Swal.resumeTimer); }
        });
        const showToast = (msg, type = 'success') => Toast.fire({
            icon: type, title: `<span style="font-family:'Poppins', sans-serif; font-size:14px;">${msg}</span>`
        });

        /* ── GENERADOR DE FOLIOS Y DATA INICIAL ── */
        function generateFolio(id) {
            const today = new Date();
            const dd = String(today.getDate()).padStart(2, '0');
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            return `SIS${dd}${mm}-${String(id).padStart(2, '0')}`;
        }

        let currentId = 1;

        let requests = [
            { id: 1001, folioP: generateFolio(1), folioU: 'SFP-001', fecha: '02/05/2026', nombre: 'Saul Falcon Perez', motivo: 'Visita a cliente externo para auditoría en sitio', depto: 'Desarrollo de Software', amount: 3500.00, status: 'Aprobado', pago: 'Pagado' },
            { id: 1002, folioP: generateFolio(2), folioU: 'SFP-002', fecha: '05/05/2026', nombre: 'Yanuri Martinez', motivo: 'Compra equipo menor', depto: 'Operaciones', amount: 850.50, status: 'Validado', pago: 'Por autorizar' },
            { id: 1003, folioP: generateFolio(3), folioU: 'SFP-003', fecha: '08/05/2026', nombre: 'Saul Falcon Perez', motivo: 'Viáticos proyecto Dell', depto: 'Desarrollo de Software', amount: 6200.00, status: 'Pendiente', pago: 'En espera' },
            { id: 1004, folioP: generateFolio(4), folioU: 'SFP-004', fecha: '09/05/2026', nombre: 'Carlos Izquierdo', motivo: 'Mobiliario de oficina', depto: 'Calidad y QHSE', amount: 430.00, status: 'Rechazado', pago: 'No procede' },
            { id: 1006, folioP: generateFolio(5), folioU: 'SFP-005', fecha: '11/05/2026', nombre: 'Saul Falcon Perez', motivo: 'Suscripción IONOS', depto: 'Desarrollo de Software', amount: 1450.00, status: 'Aprobado', pago: 'Por pagar' }
        ];

        currentId = 6;
        let currentEvaluateId = null;

        const fmt = n => new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n);

        document.getElementById('modal-fecha-hoy').textContent = new Date().toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

        let activeFilter = 'all';
        let searchQuery = '';

        document.getElementById('table-search').addEventListener('input', function() {
            searchQuery = this.value.toLowerCase().trim();
            renderDashboard();
        });

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

                const evaluateBtn = (req.status === 'Pendiente' || req.status === 'Validado') ? `<button class="btn-icon btn-icon-evaluate" onclick="evaluarSolicitud(${req.id})" title="Gestionar Resolución"><i class="bx bx-check-shield"></i></button>` : '';

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
                            <button class="btn-icon btn-icon-view" onclick="verDetalles(${req.id})" title="Visualizar Documentación"><i class="bx bx-show"></i></button>
                            ${evaluateBtn}
                        </div>
                    </td>
                </tr>`;
            });
        }

        function getRowTemplate() {
            return `
            <tr class="data-row">
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

        function addRow(tbodyId) {
            const tbody = document.getElementById(tbodyId);
            tbody.insertAdjacentHTML('beforeend', getRowTemplate());
            flatpickr(tbody.lastElementChild.querySelector('[data-fp]'), { locale: "es", dateFormat: "d/m/Y", allowInput: true, disableMobile: "true" });
        }

        function removeRow(btn) {
            const tbody = btn.closest('tbody');
            if (tbody.querySelectorAll('.data-row').length > 1) {
                btn.closest('tr').remove();
                calcTotal();
            } else {
                showToast('Se requiere de manera obligatoria al menos una fila de ingreso.', 'warning');
            }
        }

        function resetModalForm() {
            ['cat-vuelos', 'cat-restaurantes', 'cat-combustible', 'cat-otros'].forEach(cat => {
                const rows = document.getElementById(cat).querySelectorAll('.data-row');
                for (let i = 1; i < rows.length; i++) rows[i].remove();
            });
            document.querySelectorAll('.cell-input:not([readonly])').forEach(el => el.value = '');
            document.getElementById('modal-motivo').value = '';
            document.getElementById('modal-centro-costos').value = '';
            document.getElementById('sat-panel').classList.add('hidden');

            document.getElementById('sat-result-container').classList.add('hidden');
            tempSatData = null;

            const toggleDel = document.getElementById('toggle-delegation');
            if(toggleDel) { toggleDel.checked = false; handleDelegationToggle(); }
            document.getElementById('delegation-container').classList.remove('hidden');

            evidenciasFiles = [];
            renderFileList();
            actualizarInputFiles();
            calcTotal();
            flatpickr(".data-row [data-fp]", { locale: "es", dateFormat: "d/m/Y", allowInput: true, disableMobile: "true" });
        }

        function openModalForCreate() {
            resetModalForm();
            document.getElementById('main-modal-title').innerHTML = '<i class="bx bx-receipt"></i> Generación de <strong>Reembolso Múltiple</strong>';

            document.getElementById('modal-folio-p').textContent = generateFolio(currentId);
            document.getElementById('modal-folio-u').textContent = 'SFP-006';

            document.querySelector('input[name="tipo_gasto"][value="viaje"]').checked = true;

            document.getElementById('footer-create').classList.remove('hidden');
            document.getElementById('footer-view').classList.add('hidden');
            document.getElementById('footer-evaluate').classList.add('hidden');
            document.getElementById('reimbursement-modal').classList.remove('hidden');
        }

        function verDetalles(id) {
            const req = requests.find(r => r.id === id);
            if (!req) return;
            resetModalForm();

            document.getElementById('delegation-container').classList.add('hidden');

            document.getElementById('main-modal-title').innerHTML = `<i class="bx bx-search-alt"></i> Inspección del <strong>Folio: ${req.folioP}</strong>`;
            document.getElementById('modal-folio-p').textContent = req.folioP;
            document.getElementById('modal-folio-u').textContent = req.folioU;
            document.getElementById('modal-nombre').value = req.nombre;
            document.getElementById('modal-depto').value = req.depto;
            document.getElementById('modal-motivo').value = req.motivo;
            document.getElementById('sum-total').textContent = fmt(req.amount);

            document.getElementById('footer-create').classList.add('hidden');
            document.getElementById('footer-view').classList.remove('hidden');
            document.getElementById('footer-evaluate').classList.add('hidden');
            document.getElementById('reimbursement-modal').classList.remove('hidden');
        }

        function evaluarSolicitud(id) {
            const req = requests.find(r => r.id === id);
            if (!req) return;
            resetModalForm();

            document.getElementById('delegation-container').classList.add('hidden');

            document.getElementById('main-modal-title').innerHTML = '<i class="bx bx-check-shield"></i> Ejecución de Dictamen Administrativo';
            currentEvaluateId = id;

            document.getElementById('modal-folio-p').textContent = req.folioP;
            document.getElementById('modal-folio-u').textContent = req.folioU;
            document.getElementById('modal-nombre').value = req.nombre;
            document.getElementById('modal-depto').value = req.depto;
            document.getElementById('modal-motivo').value = req.motivo;
            document.getElementById('sum-total').textContent = fmt(req.amount);

            const btnValidate = document.getElementById('btn-eval-validate');
            const btnApprove = document.getElementById('btn-eval-approve');

            if (req.status === 'Pendiente') { btnValidate.classList.remove('hidden'); btnApprove.classList.add('hidden'); }
            else if (req.status === 'Validado') { btnValidate.classList.add('hidden'); btnApprove.classList.remove('hidden'); }

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

            Swal.fire({
                title: `<span style="font-family:'Poppins', sans-serif;">¿Emisión de Dictamen Final?</span>`,
                html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">La resolución afectará los balances financieros. Desea <strong>${actionText}</strong> este folio?</span>`,
                icon: 'warning', showCancelButton: true, confirmButtonColor: confirmColor, cancelButtonColor: '#94a3b8',
                confirmButtonText: `<span style="font-family:'Poppins', sans-serif; font-weight:600;">Autorizar Movimiento</span>`,
                cancelButtonText: `<span style="font-family:'Poppins', sans-serif;">Cancelar Acción</span>`
            }).then((result) => {
                if (result.isConfirmed) {
                    updateStatus(currentEvaluateId, status);
                    closeModal();
                    currentEvaluateId = null;
                }
            });
        }

        function updateStatus(id, status) {
            const i = requests.findIndex(r => r.id === id);
            if (i !== -1) {
                requests[i].status = status;
                if (status === 'Rechazado') requests[i].pago = 'No procede';
                else if (status === 'Validado') requests[i].pago = 'Por autorizar';
                else if (status === 'Aprobado') requests[i].pago = 'Por pagar';
                renderDashboard();
                showToast(`El folio fue procesado como ${status.toUpperCase()}.`, status === 'Rechazado' ? 'error' : 'success');
            }
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
            if (errorType) showToast('Restringido a documentos Adobe PDF.', 'warning');
            if (errorSize) showToast('Uno o más ficheros superan los 10 MB.', 'error');
            renderFileList(); actualizarInputFiles();
        }

        function removeFile(index) { evidenciasFiles.splice(index, 1); renderFileList(); actualizarInputFiles(); }
        function actualizarInputFiles() { const dt = new DataTransfer(); evidenciasFiles.forEach(file => dt.items.add(file)); evidenciaInput.files = dt.files; }
        function formatBytes(bytes, decimals = 2) {
            if (!+bytes) return '0 Bytes';
            const k = 1024, dm = decimals < 0 ? 0 : decimals, sizes = ['Bytes', 'KB', 'MB', 'GB'], i = Math.floor(Math.log(bytes) / Math.log(k));
            return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
        }
        function renderFileList() {
            const listDiv = document.getElementById('evidence-list');
            listDiv.innerHTML = evidenciasFiles.length > 0 ? `<div class="file-grid">${evidenciasFiles.map((f, i) => `
                <div class="file-card"><i class="bx bxs-file-pdf file-icon-lg"></i><div class="file-info"><span class="file-name" title="${f.name}">${f.name}</span><span class="file-size">${formatBytes(f.size)}</span></div><button type="button" class="btn-remove-file" onclick="event.stopPropagation(); removeFile(${i})"><i class="bx bx-x"></i></button></div>`).join('')}</div>` : '';
        }

        /* ── INTERACCIÓN LÓGICA DEL SAT ── */
        let tempSatData = null;

        function buscarFactura() {
            const uuid = document.getElementById('search-uuid').value.trim();
            const btnB = document.getElementById('btn-buscar');
            if (uuid.length < 10) { showToast('Esquema UUID Inválido.', 'warning'); return; }

            btnB.innerHTML = '<span class="spinner"></span> Consultando...';
            btnB.disabled = true;

            setTimeout(() => {
                btnB.innerHTML = '<i class="bx bx-search"></i> Buscar';
                btnB.disabled = false;

                tempSatData = {
                    fecha: new Date().toLocaleDateString('es-MX', {day: '2-digit', month: '2-digit', year: 'numeric'}),
                    folio: uuid.substring(0, 8),
                    desc: 'Servicios amparados por UUID',
                    sub: 1200.00,
                    iva: 192.00,
                    ish: 0.00
                };

                document.getElementById('sat-result-uuid').textContent = uuid;
                document.getElementById('sat-result-container').classList.remove('hidden');
                showToast('Comprobante validado y localizado.', 'success');
            }, 850);
        }

        const dropZoneUI = document.getElementById('drop-zone');
        const xmlInput = document.getElementById('xml-input');
        dropZoneUI.addEventListener('click', () => xmlInput.click());
        xmlInput.addEventListener('change', e => leerXML(e.target.files[0]));
        dropZoneUI.addEventListener('dragover', e => { e.preventDefault(); dropZoneUI.classList.add('dragover'); });
        dropZoneUI.addEventListener('dragleave', e => { e.preventDefault(); dropZoneUI.classList.remove('dragover'); });
        dropZoneUI.addEventListener('drop', e => { e.preventDefault(); dropZoneUI.classList.remove('dragover'); if (e.dataTransfer.files.length) leerXML(e.dataTransfer.files[0]); });

        function leerXML(file) {
            if (!file || file.type !== 'text/xml') { showToast('Provee un archivo .xml', 'error'); return; }
            const reader = new FileReader();
            reader.onload = e => {
                const xml = new DOMParser().parseFromString(e.target.result, 'text/xml');
                const attr = (tag, a) => { const n = xml.getElementsByTagNameNS('*', tag)[0] || xml.getElementsByTagName(tag)[0] || xml.getElementsByTagName('cfdi:' + tag)[0]; return n ? n.getAttribute(a) : null; };
                const d = { uuid: attr('TimbreFiscalDigital', 'UUID'), rfc: attr('Emisor', 'Rfc') || 'Sin RFC', fecha: (attr('Comprobante', 'Fecha') || '').split('T')[0] };
                if (d.fecha) { const [y, m, dd] = d.fecha.split('-'); d.fechaFormateada = `${dd}/${m}/${y}`; }

                if (d.uuid) {
                    document.getElementById('res-rfc').textContent = d.rfc;
                    document.getElementById('search-uuid').value = d.uuid;

                    tempSatData = {
                        fecha: d.fechaFormateada || '',
                        folio: d.uuid.substring(0, 8),
                        desc: 'Gasto extraído desde archivo XML',
                        sub: parseFloat(attr('Comprobante', 'SubTotal')) || 0,
                        iva: 0,
                        ish: 0
                    };

                    document.getElementById('sat-result-uuid').textContent = d.uuid;
                    document.getElementById('sat-result-container').classList.remove('hidden');

                    showToast('Extracción XML completada.', 'success');
                } else { showToast('Estructura CFDI desconocida.', 'error'); }
            };
            reader.readAsText(file);
        }

        /* ── INYECCIÓN AUTOMÁTICA EVITANDO FILAS DUPLICADAS ── */
        function agregarFilaDesdeSAT() {
            const cat = document.getElementById('sat-category').value;

            if(!cat) { showToast('Seleccione la categoría correspondiente.', 'warning'); return; }
            if(!tempSatData) return;

            const tbody = document.getElementById(cat);
            const dataRows = tbody.querySelectorAll('.data-row');
            let targetRow = null;

            if (dataRows.length > 0) {
                const lastRow = dataRows[dataRows.length - 1];
                const inputs = lastRow.querySelectorAll('.cell-input');
                const isEmpty = !inputs[1].value.trim() && !inputs[2].value.trim();

                if (isEmpty) {
                    targetRow = lastRow;
                }
            }

            if (!targetRow) {
                addRow(cat);
                targetRow = tbody.lastElementChild;
            }

            const inputs = targetRow.querySelectorAll('.cell-input');

            if(inputs[0]._flatpickr && tempSatData.fecha) inputs[0]._flatpickr.setDate(tempSatData.fecha, true, "d/m/Y");
            else inputs[0].value = tempSatData.fecha || '';

            inputs[1].value = tempSatData.folio;
            inputs[2].value = tempSatData.desc;
            inputs[3].value = tempSatData.sub;
            inputs[6].value = tempSatData.ish;
            inputs[7].value = tempSatData.iva;

            calcTotal();

            document.getElementById('sat-result-container').classList.add('hidden');
            document.getElementById('sat-category').value = '';
            tempSatData = null;
            showToast('El comprobante se inyectó en la matriz con éxito.', 'success');
        }

        /* ── COMPUTADORA CONTABLE ── */
        function calcTotal() {
            let gSub = 0, gIva = 0, gIsh = 0;
            document.querySelectorAll('.data-row').forEach(row => {
                let rSub = 0;
                const rIva = parseFloat(row.querySelector('.c-iva')?.value) || 0;
                const rIsh = parseFloat(row.querySelector('.c-ish')?.value) || 0;
                row.querySelectorAll('.c-sub').forEach(i => rSub += parseFloat(i.value) || 0);
                const rowTotal = row.querySelector('.cell-row-total');
                if (rowTotal) rowTotal.textContent = (rSub + rIva + rIsh) > 0 ? fmt(rSub + rIva + rIsh) : '-';
                gSub += rSub; gIva += rIva; gIsh += rIsh;
            });
            const gTotal = gSub + gIva + gIsh;
            document.getElementById('sum-subtotal').textContent = fmt(gSub);
            document.getElementById('sum-gastos').textContent = fmt(gSub);
            document.getElementById('sum-iva').textContent = fmt(gIva);
            document.getElementById('sum-ish').textContent = fmt(gIsh);
            document.getElementById('sum-total').textContent = fmt(gTotal);
            document.getElementById('sum-total').setAttribute('data-value', gTotal);
        }

        function verifyAndSubmit() {
            const total = parseFloat(document.getElementById('sum-total').getAttribute('data-value'));
            if (total <= 0) { showToast('Las sumas deben ser mayores a $0.', 'error'); return; }
            if (!document.getElementById('modal-centro-costos').value) { showToast('Ingrese un Centro de Costos válido.', 'error'); return; }
            if (!document.getElementById('modal-motivo').value.trim()) { showToast('Ingrese justificación del gasto.', 'error'); return; }
            if (evidenciasFiles.length === 0) { showToast('Es mandatorio proveer evidencia digital (PDF).', 'warning'); return; }

            Swal.fire({
                title: `<span style="font-family:'Poppins', sans-serif;">Consentimiento</span>`,
                html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">La matriz por valor de <strong>${fmt(total)}</strong> pasará a revisión.</span>`,
                icon: 'question', showCancelButton: true, confirmButtonColor: 'var(--teal-dark)', cancelButtonColor: '#94a3b8',
                confirmButtonText: `<span style="font-family:'Poppins', sans-serif; font-weight:600;">Emitir Responsiva</span>`, cancelButtonText: `<span style="font-family:'Poppins', sans-serif;">Retornar</span>`
            }).then((result) => {
                if (result.isConfirmed) {
                    onEnviarSubmit();
                }
            });
        }

        function onEnviarSubmit() {
            procesarEnvio('Pendiente', 'En espera');
            Swal.fire({ title: '<span style="font-family:\'Poppins\', sans-serif;">¡Desembolso Creado!</span>', icon: 'success', confirmButtonColor: 'var(--teal-dark)' });
        }

        function saveDraft() {
            if (!document.getElementById('modal-motivo').value.trim()) { showToast('Ingrese una descripción de motivo para guardar borrador.', 'warning'); return; }
            procesarEnvio('Borrador', 'N/A');
            showToast('Backup Sistémico empaquetado con éxito.', 'success');
        }

        function procesarEnvio(estadoRevision, estadoPago) {
            requests.unshift({
                id: currentId++, fecha: document.getElementById('modal-fecha-hoy').textContent,
                folioP: document.getElementById('modal-folio-p').textContent, folioU: 'SFP-006',
                nombre: document.getElementById('modal-nombre').value, motivo: document.getElementById('modal-motivo').value.trim(),
                depto: document.getElementById('modal-depto').value || 'Sin Asignar', amount: parseFloat(document.getElementById('sum-total').getAttribute('data-value')),
                status: estadoRevision, pago: estadoPago
            });
            closeModal(); renderDashboard();
        }

        renderDashboard();
    </script>
@endpush
