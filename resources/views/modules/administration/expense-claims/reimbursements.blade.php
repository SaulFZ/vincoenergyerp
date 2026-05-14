{{-- ════════════════════════════════════════════════════════════════════════════
     VISTA BLADE: PANEL DE REEMBOLSOS (reembolsos.blade.php)
     ════════════════════════════════════════════════════════════════════════════ --}}
@extends('modules.administration.expense-claims.index')

@section('content')
    {{-- ── LIBRERÍAS EXTERNAS ── --}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

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
                            <th style="width:42px;">#</th>
                            <th>Folio Principal</th>
                            <th>Folio Usuario</th>
                            <th>Fecha</th>
                            <th>Solicitante</th>
                            <th>Motivo</th>
                            <th>Departamento</th>
                            <th>Monto Total</th>
                            <th>Estado de Revisión</th>
                            <th>Estado de Pago</th>
                            <th class="cell-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="dashboard-list">
                        {{-- Las filas se inyectan dinámicamente vía JavaScript --}}
                    </tbody>
                </table>
            </div>

            {{-- ESTADO VACÍO (Manejo de errores/Búsqueda sin resultados) --}}
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
                <div style="display:flex; align-items:center; gap:.75rem;">
                    <button type="button" class="btn btn-secondary" onclick="toggleUuidPanel()"
                        title="Autocompletar datos de los importes desde factura del SAT">
                        <i class="bx bx-barcode"></i> Cargar Factura SAT
                    </button>
                    <button class="btn-close" onclick="closeModal()" aria-label="Cerrar ventana">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            </div>

            <div class="modal-body" id="modal-body-scroll">

                {{-- PANEL DE LECTURA DE UUID / XML DEL SAT --}}
                <div id="uuid-panel" class="hidden"
                    style="background:#0f172a; border:1px solid #1e293b; border-radius:.75rem; padding:1.5rem; margin-bottom:1.5rem; position:relative; overflow:hidden;">
                    <div style="position:absolute;top:0;left:0;width:100%;height:4px;background:linear-gradient(to right,#14b8a6,#0d9488);"></div>
                    <label class="input-label" style="color:#cbd5e1; font-size:.85rem; margin-bottom:.75rem;">
                        Folio Fiscal (UUID) del comprobante SAT
                    </label>
                    <div style="display:flex; gap:.75rem; margin-bottom:.75rem;">
                        <div style="position:relative; flex:1;">
                            <i class="bx bx-barcode"
                                style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:1.4rem;pointer-events:none;"></i>
                            <input type="text" id="search-uuid" class="modal-focusable"
                                style="width:100%;padding:.75rem 1rem .75rem 3rem;background:#1e293b;border:1px solid #334155;border-radius:.5rem;color:#fff;font-family:monospace;text-transform:uppercase;outline:none;"
                                placeholder="550E8400-E29B-41D4-A716-446655440000" autocomplete="off">
                        </div>
                        <button type="button" id="btn-buscar" class="btn btn-primary" onclick="buscarFactura()">
                            <i class="bx bx-search"></i> Buscar
                        </button>
                    </div>
                    <p id="search-message" class="hidden" style="font-size:.72rem;color:#ef4444;margin-bottom:.75rem;"></p>
                    <div id="drop-zone-container" class="hidden" style="border-top:1px solid #334155; padding-top:1rem;">
                        <div id="drop-zone"
                            style="border:2px dashed rgba(20,184,166,.45);background:rgba(20,184,166,.08);border-radius:.75rem;padding:1.5rem;text-align:center;cursor:pointer;transition:background .15s;">
                            <i class="bx bx-cloud-upload" style="font-size:3rem;color:#14b8a6;"></i>
                            <p style="margin-top:.5rem;font-size:.85rem;color:#cbd5e1;">Búsqueda local fallida o no vinculada.</p>
                            <small
                                style="display:block;margin-top:.25rem;font-size:.75rem;font-weight:600;color:#14b8a6;text-transform:uppercase;">Arrastra tu archivo .XML aquí para extracción local</small>
                            <input type="file" id="xml-input" accept=".xml" style="display:none;">
                        </div>
                    </div>
                </div>

                {{-- CABECERA INFORMATIVA DEL REEMBOLSO (Datos inmutables y de categorización) --}}
                <div class="form-header-card">

                    <div class="fh-info-strip">
                        <div class="fh-info-item">
                            <span>RFC Empresa Matriz</span>
                            <strong id="res-rfc">VES1607057K7</strong>
                        </div>
                        <div class="fh-info-item folio">
                            <span>Folio Principal (Sistema)</span>
                            <strong id="modal-folio-p">VES-0000</strong>
                        </div>
                        <div class="fh-info-item">
                            <span>Folio Interno del Usuario</span>
                            <strong id="modal-folio-u" style="color:#64748b;">SFP-000</strong>
                        </div>

                        <div class="fh-info-item">
                            <span>Lugar de Emisión</span>
                            <div class="input-group" style="margin-top: 3px;">
                                <i class="bx bx-map field-icon" style="font-size: 1.05rem; left: 0.6rem;"></i>
                                <input type="text" id="modal-lugar" value="VHSA, TAB." class="input-field modal-focusable"
                                       style="padding: 0.4rem 0.5rem 0.4rem 2rem; width: 140px; font-weight: 600; color: #0f172a; background: #fff;">
                            </div>
                        </div>

                        <div class="fh-info-item" style="text-align:right;">
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
                        </div>

                        <div class="fh-grid-4">
                            <div>
                                <label class="input-label">Nombre del Solicitante</label>
                                <div class="input-group">
                                    <i class="bx bx-user field-icon"></i>
                                    <input type="text" id="modal-nombre" value="{{ $userData['nombre'] ?? 'Saul Falcon Perez' }}" class="input-field" readonly>
                                </div>
                            </div>
                            <div>
                                <label class="input-label">Área / Departamento</label>
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
                                        <option value="VNC-AD-03">VNC-AD-03 | Administración y Finanzas</option>
                                        <option value="VNC-CM-04">VNC-CM-04 | Dpto. Comercialización</option>
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

                {{-- MATRIZ DE DESGLOSE FINANCIERO (Facturas, importes e impuestos) --}}
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
                                    <th colspan="3" style="text-align:left;">Comprobante Identificador</th>
                                    <th colspan="3" class="th-importes">Importes Subtotales</th>
                                    <th colspan="2">Retenciones / Impuestos</th>
                                    <th rowspan="2" style="background:#0f172a; color:#fff; text-align:right; padding:.6rem .75rem;">Monto Total</th>
                                    <th rowspan="2" style="background:#0f172a; width:45px;"></th>
                                </tr>
                                <tr class="th-cols">
                                    <th style="width:140px;">Fecha Factura</th>
                                    <th style="width:100px;">Folio/Num. Fac.</th>
                                    <th>Descripción Comercial</th>
                                    <th style="width:90px;">Comp. Fiscal<br>(PDF + XML)</th>
                                    <th style="width:90px;">Comp. Simple<br>No Fiscal</th>
                                    <th style="width:90px;">Sin Comp.<br>y Propinas</th>
                                    <th style="width:80px;">I.S.H.<br>Otros Imp.</th>
                                    <th style="width:75px;">I.V.A. (16%)</th>
                                </tr>
                            </thead>

                            {{-- SECCIÓN: VUELOS Y/O TRANSPORTE --}}
                            <tbody id="cat-vuelos">
                                <tr class="cat-row">
                                    <td colspan="10">
                                        <div style="display:flex; justify-content:space-between; align-items:center;">
                                            <span><i class="bx bxs-plane-alt"></i> I. Transportación, Vuelos y Peajes</span>
                                            <button type="button" class="btn-add-row" onclick="addRow('cat-vuelos')" title="Agregar Fila de Gasto">
                                                <i class="bx bx-plus"></i>
                                            </button>
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
                                    <td style="text-align:center;"><button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Eliminar este concepto"><i class="bx bx-trash"></i></button></td>
                                </tr>
                            </tbody>

                            {{-- SECCIÓN: RESTAURANTES Y/O COMIDAS --}}
                            <tbody id="cat-restaurantes">
                                <tr class="cat-row">
                                    <td colspan="10">
                                        <div style="display:flex; justify-content:space-between; align-items:center;">
                                            <span><i class="bx bx-restaurant"></i> II. Consumo de Alimentos y Restaurantes</span>
                                            <button type="button" class="btn-add-row" onclick="addRow('cat-restaurantes')" title="Agregar Fila de Gasto">
                                                <i class="bx bx-plus"></i>
                                            </button>
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
                                    <td style="text-align:center;"><button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Eliminar este concepto"><i class="bx bx-trash"></i></button></td>
                                </tr>
                            </tbody>

                            {{-- SECCIÓN: COMBUSTIBLE --}}
                            <tbody id="cat-combustible">
                                <tr class="cat-row">
                                    <td colspan="10">
                                        <div style="display:flex; justify-content:space-between; align-items:center;">
                                            <span><i class="bx bxs-gas-pump"></i> III. Abastecimiento de Combustible</span>
                                            <button type="button" class="btn-add-row" onclick="addRow('cat-combustible')" title="Agregar Fila de Gasto">
                                                <i class="bx bx-plus"></i>
                                            </button>
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
                                    <td style="text-align:center;"><button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Eliminar este concepto"><i class="bx bx-trash"></i></button></td>
                                </tr>
                            </tbody>

                            {{-- SECCIÓN: OTROS --}}
                            <tbody id="cat-otros">
                                <tr class="cat-row">
                                    <td colspan="10">
                                        <div style="display:flex; justify-content:space-between; align-items:center;">
                                            <span><i class="bx bx-package"></i> IV. Cargos Varios / Misceláneos</span>
                                            <button type="button" class="btn-add-row" onclick="addRow('cat-otros')" title="Agregar Fila de Gasto">
                                                <i class="bx bx-plus"></i>
                                            </button>
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
                                    <td style="text-align:center;"><button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Eliminar este concepto"><i class="bx bx-trash"></i></button></td>
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
                        <p class="evidence-desc">Arrastra y suelta tus facturas, tickets y documentación probatoria aquí.<br>Carga máxima de 10MB por archivo unitario.</p>
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
                            <div class="summary-row"><span class="sum-lbl">Sub-Total Neto:</span><span
                                    id="sum-subtotal" class="sum-val">$0.00</span></div>
                            <div class="summary-row"><span class="sum-lbl">Suma Erogada (Base):</span><span id="sum-gastos"
                                    class="sum-val">$0.00</span></div>
                            <div class="summary-row"><span class="sum-lbl">Impuesto (I.V.A.):</span><span id="sum-iva"
                                    class="sum-val">$0.00</span></div>
                            <div class="summary-row"><span class="sum-lbl">Impuestos Locales (I.S.H.):</span><span
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
                    <span style="font-size:.75rem; color:#94a3b8; display:flex; align-items:center; gap:5px;">
                        <i class="bx bx-info-circle" style="font-size:1.1rem;"></i>
                        Para garantizar una autorización rápida, asegúrate de adjuntar el PDF de soporte.
                    </span>
                </div>

                {{-- MODO DE CREACIÓN: INTERFAZ DEL SOLICITANTE --}}
                <div class="modal-footer-right" id="footer-create">
                    <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancelar Operación</button>
                    <button type="button" id="btn-borrador" class="btn btn-secondary" onclick="saveDraft()">
                        <i class="bx bx-save"></i> Guardar Borrador
                    </button>
                    <button type="button" id="btn-enviar" class="btn btn-primary" onclick="verifyAndSubmit()">
                        <i class="bx bx-send"></i> Emitir Solicitud a Revisión
                    </button>
                </div>

                {{-- MODO DE VISUALIZACIÓN LECTURA --}}
                <div class="modal-footer-right hidden" id="footer-view">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cerrar Pestaña</button>
                </div>

                {{-- MODO DE EVALUACIÓN: INTERFAZ ADMINISTRATIVA / GERENCIAL --}}
                <div class="modal-footer-right hidden" id="footer-evaluate">
                    <button type="button" class="btn btn-ghost" onclick="closeModal()">Posponer</button>

                    {{-- Botón para Rechazar (Común) --}}
                    <button type="button" class="btn btn-fail-solid" onclick="processEvaluation('Rechazado')">
                        <i class="bx bx-x"></i> Rechazar
                    </button>

                    {{-- Botón Validar (Fase 1: Administración) --}}
                    <button type="button" class="btn btn-secondary" id="btn-eval-validate" style="color:var(--teal-dark); border-color:var(--teal-dark);" onclick="processEvaluation('Validado')">
                        <i class="bx bx-list-check"></i> Validar
                    </button>

                    {{-- Botón Aprobar (Fase 2: Gerencia) --}}
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
        /* ── INTERACCIÓN Y ACCESIBILIDAD: NAVEGACIÓN CON ENTER ── */
        document.getElementById('reimbursement-modal').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const focusableElements = Array.from(this.querySelectorAll('.modal-focusable'));
                const currentIndex = focusableElements.indexOf(document.activeElement);

                // Salto automático al siguiente campo disponible en el flujo
                if (currentIndex > -1 && currentIndex < focusableElements.length - 1) {
                    focusableElements[currentIndex + 1].focus();
                }
            }
        });

        /* ── SISTEMA DE NOTIFICACIONES TOAST (SWEETALERT2) ── */
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4500,
            timerProgressBar: true,
            didOpen: t => {
                t.addEventListener('mouseenter', Swal.stopTimer);
                t.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        const showToast = (msg, type = 'success') => Toast.fire({
            icon: type,
            title: `<span style="font-family:'Poppins', sans-serif; font-size:14px;">${msg}</span>`
        });

        /* ── DATOS TRANSACCIONALES (MOCK DB) ── */
        // Aquí simulamos un backend. La estructura contempla el ciclo de 3 fases completas.
        let requests = [{
                id: 1001,
                folioP: 'VES-0001',
                folioU: 'SFP-001',
                fecha: '02/05/2026',
                nombre: 'Saul Falcon Perez',
                motivo: 'Visita a cliente externo para auditoría en sitio',
                depto: 'Desarrollo de Software',
                amount: 3500.00,
                status: 'Aprobado',
                pago: 'Pagado'
            },
            {
                id: 1002,
                folioP: 'VES-0002',
                folioU: 'SFP-002',
                fecha: '05/05/2026',
                nombre: 'Saul Falcon Perez',
                motivo: 'Compra equipo menor (Ratón y Teclado Magnético Aula)',
                depto: 'Desarrollo de Software',
                amount: 850.50,
                status: 'Validado', // Fase 1 administrativa: Papeleo OK
                pago: 'Por autorizar' // Tesorería sabe que aún falta el gerente
            },
            {
                id: 1003,
                folioP: 'VES-0003',
                folioU: 'SFP-003',
                fecha: '08/05/2026',
                nombre: 'Saul Falcon Perez',
                motivo: 'Viáticos proyecto de despliegue servidor Dell / Ubuntu',
                depto: 'Desarrollo de Software',
                amount: 6200.00,
                status: 'Pendiente', // Fase 0: Esperando a Administración
                pago: 'En espera'
            },
            {
                id: 1004,
                folioP: 'VES-0004',
                folioU: 'SFP-004',
                fecha: '09/05/2026',
                nombre: 'Saul Falcon Perez',
                motivo: 'Mobiliario de oficina y organización general',
                depto: 'Desarrollo de Software',
                amount: 430.00,
                status: 'Rechazado', // Rechazo en cualquier fase detiene la cadena
                pago: 'No procede'
            },
            {
                id: 1006,
                folioP: 'VES-0005',
                folioU: 'SFP-005',
                fecha: '11/05/2026',
                nombre: 'Saul Falcon Perez',
                motivo: 'Pago de suscripción servidores IONOS y Dominio web',
                depto: 'Desarrollo de Software',
                amount: 1450.00,
                status: 'Aprobado',  // Aprobado por el Gerente, ya pasó la Fase 2
                pago: 'Por pagar'    // Tesorería (Fase 3) ya está preparando el dinero (Reemplazo de "En proceso")
            }
        ];

        let currentId = 1007; // Secuenciador AI / DB Mock
        let currentEvaluateId = null;

        const fmt = n => new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN'
        }).format(n);

        /* ── INICIALIZACIÓN DE FECHAS DE ENCABEZADO ── */
        document.getElementById('modal-fecha-hoy').textContent =
            new Date().toLocaleDateString('es-MX', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

        /* ── MANEJADORES DE FILTROS Y BÚSQUEDA DEL DATATABLE ── */
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

        /* ── MOTOR DE RENDERIZADO DEL DASHBOARD (COMPUTADOR DE ESTADOS) ── */
        function renderDashboard() {
            const list = document.getElementById('dashboard-list');
            const emptyState = document.getElementById('empty-state');
            const tableCount = document.getElementById('table-count');
            list.innerHTML = '';

            let totalAcc = 0, pendCount = 0, pendAmt = 0, appCount = 0, appAmt = 0, rejCount = 0, rejAmt = 0;

            // Computar métricas analíticas
            requests.forEach(req => {
                if (req.status !== 'Borrador') {
                    totalAcc += req.amount;
                }
                if (req.status === 'Pendiente' || req.status === 'Validado') {
                    pendCount++;
                    pendAmt += req.amount;
                }
                if (req.status === 'Aprobado') {
                    appCount++;
                    appAmt += req.amount;
                }
                if (req.status === 'Rechazado') {
                    rejCount++;
                    rejAmt += req.amount;
                }
            });

            // Actualizar tarjetas del DOM
            document.getElementById('metric-total-val').textContent = fmt(totalAcc);
            document.getElementById('metric-pending-val').textContent = pendCount;
            document.getElementById('metric-approved-val').textContent = appCount;
            document.getElementById('metric-rejected-val').textContent = rejCount;
            document.getElementById('metric-pending-amount').textContent = fmt(pendAmt);
            document.getElementById('metric-approved-amount').textContent = fmt(appAmt);
            document.getElementById('metric-rejected-amount').textContent = fmt(rejAmt);

            // Algoritmo de filtrado anidado
            let filtered = requests.filter(req => {
                const matchFilter = activeFilter === 'all' || req.status === activeFilter;
                const matchSearch = !searchQuery ||
                                    req.motivo.toLowerCase().includes(searchQuery) ||
                                    req.folioP.toLowerCase().includes(searchQuery) ||
                                    req.folioU.toLowerCase().includes(searchQuery) ||
                                    req.nombre.toLowerCase().includes(searchQuery);
                return matchFilter && matchSearch;
            });

            tableCount.textContent = `${filtered.length} solicitud${filtered.length !== 1 ? 'es' : ''} registrada(s)`;

            if (filtered.length === 0) {
                emptyState.classList.remove('hidden');
                return;
            }
            emptyState.classList.add('hidden');

            // Renderizado de las filas
            filtered.forEach((req) => {
                const globalIdx = requests.findIndex(r => r.id === req.id);

                // 1. ÁRBOL DE DECISIÓN: ESTADO DE REVISIÓN
                let badge = '';
                if (req.status === 'Aprobado') badge = `<span class="status-badge badge-ok"><i class="bx bx-check-circle"></i> Aprobado</span>`;
                else if (req.status === 'Rechazado') badge = `<span class="status-badge badge-fail"><i class="bx bx-x-circle"></i> Rechazado</span>`;
                else if (req.status === 'Validado') badge = `<span class="status-badge badge-review"><i class="bx bx-list-check"></i> Validado</span>`;
                else if (req.status === 'Borrador') badge = `<span class="status-badge badge-draft"><i class="bx bx-edit-alt"></i> Borrador</span>`;
                else badge = `<span class="status-badge badge-wait"><i class="bx bx-hourglass"></i> Pendiente</span>`;

                // 2. ÁRBOL DE DECISIÓN: ESTADO FINANCIERO / PAGO
                let badgePago = '';
                if (req.pago === 'Pagado') badgePago = `<span class="status-badge badge-payment-paid"><i class="bx bx-money"></i> Pagado</span>`;
                else if (req.pago === 'Por pagar') badgePago = `<span class="status-badge badge-payment-process"><i class="bx bx-wallet"></i> Por pagar</span>`;
                else if (req.pago === 'Por autorizar') badgePago = `<span class="status-badge badge-payment-auth"><i class="bx bx-user-voice"></i> Por autorizar</span>`;
                else if (req.pago === 'En espera') badgePago = `<span class="status-badge badge-payment-wait"><i class="bx bx-time-five"></i> En espera</span>`;
                else if (req.pago === 'No procede') badgePago = `<span class="status-badge badge-payment-void"><i class="bx bx-block"></i> No procede</span>`;
                else badgePago = `<span class="status-badge badge-disabled"><i class="bx bx-minus"></i> ${req.pago || 'N/A'}</span>`;

                // 3. RENDERIZACIÓN CONDICIONAL DE CONTROLES Y ACCIONES
                // El botón evaluar se muestra a roles directivos en 'Pendiente' o 'Validado'.
                const evaluateBtn = (req.status === 'Pendiente' || req.status === 'Validado') ? `<button class="btn-icon btn-icon-evaluate" onclick="evaluarSolicitud(${req.id})" title="Gestionar Resolución"><i class="bx bx-check-shield"></i></button>` : '';

                const actions = `
                    <div class="actions-wrap">
                        <button class="btn-icon btn-icon-view" onclick="verDetalles(${req.id})" title="Visualizar Documentación"><i class="bx bx-show"></i></button>
                        ${evaluateBtn}
                    </div>
                `;

                // Insertando el HTML en el DOM. Notar el orden de columnas exigido: Folios -> Fecha -> Solicitante -> ...
                list.innerHTML += `
                <tr>
                    <td class="row-index">${globalIdx + 1}</td>
                    <td><span class="row-folio"><i class="bx bx-hash"></i> ${req.folioP}</span></td>
                    <td><span class="row-folio user-folio">${req.folioU}</span></td>
                    <td><span class="row-date">${req.fecha}</span></td>
                    <td><span class="row-name">${req.nombre}</span></td>
                    <td><span class="row-motive">${req.motivo}</span></td>
                    <td><span class="row-depto">${req.depto}</span></td>
                    <td>
                        <div class="row-amount-wrap">
                            <span class="row-amount">${fmt(req.amount)}</span>
                            <span class="row-amount-label">MXN</span>
                        </div>
                    </td>
                    <td>${badge}</td>
                    <td>${badgePago}</td>
                    <td class="cell-actions">${actions}</td>
                </tr>`;
            });
        }

        /* ── GESTIÓN DINÁMICA DE CONCEPTOS EN LA MATRIZ DE TABLAS ── */
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
                <td style="text-align:center;"><button type="button" class="btn-remove-row" onclick="removeRow(this)" aria-label="Eliminar concepto de la tabla"><i class="bx bx-trash"></i></button></td>
            </tr>`;
        }

        function addRow(tbodyId) {
            const tbody = document.getElementById(tbodyId);
            tbody.insertAdjacentHTML('beforeend', getRowTemplate());
            const newRow = tbody.lastElementChild;
            // Instanciar Flatpickr para el nuevo input de fecha
            flatpickr(newRow.querySelector('[data-fp]'), { locale: "es", dateFormat: "d/m/Y", allowInput: true, disableMobile: "true" });
        }

        function removeRow(btn) {
            const row = btn.closest('tr');
            const tbody = row.closest('tbody');
            // Impedir que se quede un body completamente vacío para garantizar integridad en calcTotal
            if(tbody.querySelectorAll('.data-row').length > 1) {
                row.remove();
                calcTotal();
            } else {
                showToast('Se requiere de manera obligatoria al menos una fila de ingreso para esta sección.', 'warning');
            }
        }

        /* ── MANEJADORES DE ESTADO Y FLUJO DEL MODAL ── */
        function resetModalForm() {
            // Limpieza y reseteo integral
            ['cat-vuelos', 'cat-restaurantes', 'cat-combustible', 'cat-otros'].forEach(cat => {
                const tbody = document.getElementById(cat);
                const rows = tbody.querySelectorAll('.data-row');
                for(let i = 1; i < rows.length; i++) { rows[i].remove(); } // Limpia clónicas
            });

            document.querySelectorAll('.cell-input').forEach(el => {
                if (!el.hasAttribute('readonly') && (el.type === 'number' || (el.type === 'text' && !el.hasAttribute('data-fp')))) el.value = '';
            });
            document.getElementById('modal-motivo').value = '';
            document.getElementById('modal-centro-costos').value = '';
            document.getElementById('uuid-panel').classList.add('hidden');

            // Restablece ficheros
            evidenciasFiles = [];
            renderFileList();
            actualizarInputFiles();
            calcTotal();

            // Re-vincular Datepickers
            flatpickr(".data-row [data-fp]", { locale: "es", dateFormat: "d/m/Y", allowInput: true, disableMobile: "true" });
        }

        function openModalForCreate() {
            resetModalForm();
            document.getElementById('main-modal-title').innerHTML = '<i class="bx bx-receipt"></i> Generación de <strong>Reembolso Múltiple</strong>';
            document.getElementById('modal-folio-p').textContent = `VES-${String(currentId).padStart(4, '0')}`;

            // Datos del contexto personal de la aplicación, como solicitaste anteriormente
            document.getElementById('modal-folio-u').textContent = 'SFP-005';
            document.getElementById('modal-nombre').value = 'Saul Falcon Perez';

            document.querySelector('input[name="tipo_gasto"][value="viaje"]').checked = true;

            document.getElementById('footer-create').classList.remove('hidden');
            document.getElementById('footer-view').classList.add('hidden');
            document.getElementById('footer-evaluate').classList.add('hidden');
            document.getElementById('reimbursement-modal').classList.remove('hidden');
        }

        function verDetalles(id) {
            const req = requests.find(r => r.id === id);
            if(!req) return;
            resetModalForm();
            document.getElementById('main-modal-title').innerHTML = '<i class="bx bx-search-alt"></i> Inspección del <strong>Folio: ' + req.folioP + '</strong>';

            // Rellenar información analizada
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
            if(!req) return;
            resetModalForm();
            document.getElementById('main-modal-title').innerHTML = '<i class="bx bx-check-shield"></i> Ejecución de Dictamen Administrativo';
            currentEvaluateId = id;

            // Inyección de Data Base Local
            document.getElementById('modal-folio-p').textContent = req.folioP;
            document.getElementById('modal-folio-u').textContent = req.folioU;
            document.getElementById('modal-nombre').value = req.nombre;
            document.getElementById('modal-depto').value = req.depto;
            document.getElementById('modal-motivo').value = req.motivo;
            document.getElementById('sum-total').textContent = fmt(req.amount);

            // Lógica de botones según el estado actual
            const btnValidate = document.getElementById('btn-eval-validate');
            const btnApprove = document.getElementById('btn-eval-approve');

            // Si está pendiente, Fase 1 (Validar) es el paso correcto. Si ya se validó, entra Fase 2 (Aprobar).
            if(req.status === 'Pendiente') {
                btnValidate.classList.remove('hidden');
                btnApprove.classList.add('hidden');
            } else if (req.status === 'Validado') {
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

        /* ── MOTOR DE ORQUESTACIÓN Y FLUJOS CORPORATIVOS DE ESTADOS ── */
        function processEvaluation(status) {
            if(!currentEvaluateId) return;

            let actionText = '';
            let confirmColor = '';

            // Asignando colores empresariales e intención de mensaje según caso de uso
            if (status === 'Aprobado') {
                actionText = 'Aprobar Definitivamente';
                confirmColor = 'var(--teal-dark)';
            } else if (status === 'Validado') {
                actionText = 'Dar Visto Bueno a Documentación';
                confirmColor = '#0284c7'; // Azul formal
            } else if (status === 'Rechazado') {
                actionText = 'Denegar y Rechazar';
                confirmColor = '#ef4444'; // Rojo alarma
            }

            Swal.fire({
                title: `<span style="font-family:'Poppins', sans-serif;">¿Emisión de Dictamen Final?</span>`,
                html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">La resolución afectará los balances financieros departamentales. Desea proceder a <strong>${actionText}</strong> este folio?</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#94a3b8',
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

        // Sistema de Cascada para Estados Conectados (Revisión -> Pagos)
        function updateStatus(id, status) {
            const i = requests.findIndex(r => r.id === id);
            if (i !== -1) {
                requests[i].status = status;

                // Aplicar el ciclo de vida del módulo financiero:
                if (status === 'Rechazado') {
                    // Detiene cualquier posibilidad de erogación futura
                    requests[i].pago = 'No procede';
                } else if (status === 'Validado') {
                    // Fase administrativa validada, aún no aprueba el gerente
                    requests[i].pago = 'Por autorizar';
                } else if (status === 'Aprobado') {
                    // El dinero queda consignado en tesorería para pago
                    requests[i].pago = 'Por pagar';
                }

                renderDashboard();
                showToast(`El folio fue procesado como ${status.toUpperCase()} exitosamente en la plataforma.`, status === 'Rechazado' ? 'error' : 'success');
            }
        }

        /* ── SUBSISTEMA DE GESTIÓN Y CARGA DE DOCUMENTOS COMPROBATORIOS (PDF) ── */
        let evidenciasFiles = [];
        const maxFileSize = 10 * 1024 * 1024; // Limitante 10MB
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

            // Emisión de Alertas de validación de extensión
            if (errorType) showToast('Exclusión de formato detectada: Restringido a documentos Adobe PDF.', 'warning');
            if (errorSize) showToast('Restricción de volúmen: Uno o más ficheros superan la cota técnica (10 MB).', 'error');

            renderFileList();
            actualizarInputFiles();
        }

        function removeFile(index) {
            evidenciasFiles.splice(index, 1);
            renderFileList();
            actualizarInputFiles();
        }

        // Mantiene la integridad técnica del input de archivos ocultos en el DOM
        function actualizarInputFiles() {
            const dt = new DataTransfer();
            evidenciasFiles.forEach(file => dt.items.add(file));
            evidenciaInput.files = dt.files;
        }

        function formatBytes(bytes, decimals = 2) {
            if (!+bytes) return '0 Bytes';
            const k = 1024, dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
        }

        function renderFileList() {
            const listDiv = document.getElementById('evidence-list');
            listDiv.innerHTML = '';
            if (evidenciasFiles.length > 0) {
                let html = '<div class="file-grid">';
                evidenciasFiles.forEach((file, index) => {
                    html += `
                    <div class="file-card">
                        <i class="bx bxs-file-pdf file-icon-lg"></i>
                        <div class="file-info">
                            <span class="file-name" title="${file.name}">${file.name}</span>
                            <span class="file-size">${formatBytes(file.size)}</span>
                        </div>
                        <button type="button" class="btn-remove-file" onclick="event.stopPropagation(); removeFile(${index})" aria-label="Remover evidencia de la pasarela">
                            <i class="bx bx-x"></i>
                        </button>
                    </div>`;
                });
                html += '</div>';
                listDiv.innerHTML = html;
            }
        }

        /* ── HERRAMIENTAS ADICIONALES Y PARSEADO DE XML DEL SAT ── */
        function toggleUuidPanel() {
            document.getElementById('uuid-panel').classList.toggle('hidden');
        }

        function buscarFactura() {
            const uuid = document.getElementById('search-uuid').value.trim();
            const btnB = document.getElementById('btn-buscar');
            const dz = document.getElementById('drop-zone-container');
            if (uuid.length < 10) { showToast('Esquema UUID Inválido. Ingrese una nomenclatura SAT compatible.', 'warning'); return; }

            // Feedback visual UX de red local
            btnB.innerHTML = '<span class="spinner"></span> Consultando BD Local...';
            btnB.disabled = true; dz.classList.add('hidden');

            setTimeout(() => {
                btnB.innerHTML = '<i class="bx bx-search"></i> Ejecutar Búsqueda';
                btnB.disabled = false; dz.classList.remove('hidden');
                showToast('Búsqueda SAT en vacio: Registros no encontrados en el cache del sistema. Utilice Parseo XML Manual.', 'error');
            }, 850);
        }

        const dropZoneUI = document.getElementById('drop-zone');
        const xmlInput = document.getElementById('xml-input');
        dropZoneUI.addEventListener('click', () => xmlInput.click());
        xmlInput.addEventListener('change', e => leerXML(e.target.files[0]));
        dropZoneUI.addEventListener('dragover', e => { e.preventDefault(); dropZoneUI.style.background = 'rgba(20,184,166,.18)'; });
        dropZoneUI.addEventListener('dragleave', e => { e.preventDefault(); dropZoneUI.style.background = ''; });
        dropZoneUI.addEventListener('drop', e => { e.preventDefault(); dropZoneUI.style.background = ''; if (e.dataTransfer.files.length) leerXML(e.dataTransfer.files[0]); });

        function leerXML(file) {
            if (!file || file.type !== 'text/xml') { showToast('Incompatibilidad: Provea un archivo con extensión (.xml)', 'error'); return; }
            const reader = new FileReader();
            reader.onload = e => {
                const xml = new DOMParser().parseFromString(e.target.result, 'text/xml');
                const attr = (tag, a) => {
                    const n = xml.getElementsByTagNameNS('*', tag)[0] || xml.getElementsByTagName(tag)[0] || xml.getElementsByTagName('cfdi:' + tag)[0];
                    return n ? n.getAttribute(a) : null;
                };
                // Análisis Estructural del CFDI 3.3/4.0
                const d = {
                    uuid: attr('TimbreFiscalDigital', 'UUID'),
                    rfc: attr('Emisor', 'Rfc') || 'Sin RFC Asociado',
                    fecha: (attr('Comprobante', 'Fecha') || '').split('T')[0]
                };
                if (d.fecha) { const [y, m, dd] = d.fecha.split('-'); d.fechaFormateada = `${dd}/${m}/${y}`; }
                if (d.uuid) {
                    document.getElementById('res-rfc').textContent = d.rfc;
                    document.getElementById('search-uuid').value = d.uuid;
                    const firstDateInput = document.querySelector('[data-fp]');
                    if (firstDateInput && d.fechaFormateada) {
                        if (firstDateInput._flatpickr) firstDateInput._flatpickr.setDate(d.fechaFormateada, true, "d/m/Y");
                        else firstDateInput.value = d.fechaFormateada;
                    }
                    document.getElementById('uuid-panel').classList.add('hidden');
                    showToast('Extracción sintáctica completada. Nodos poblados exitosamente.', 'success');
                } else {
                    showToast('Estructura CFDI desconocida. Imposible leer atributos fiscales del SAT.', 'error');
                }
            };
            reader.readAsText(file);
        }

        /* ── COMPUTADORA CONTABLE DE LA TABLA ── */
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

            const el = document.getElementById('sum-total');
            el.textContent = fmt(gTotal);
            // Salvar el valor numérico en el atributo data para usar en el Submit
            el.setAttribute('data-value', gTotal);
        }

        /* ── MANEJADORES DE ALMACENAMIENTO DE EROGACIONES (CREAR/ENVIAR) ── */
        function verifyAndSubmit() {
            const total = parseFloat(document.getElementById('sum-total').getAttribute('data-value'));
            const motivo = document.getElementById('modal-motivo').value.trim();
            const centroCostos = document.getElementById('modal-centro-costos').value;

            // Restricciones del Negocio
            if (total <= 0) { showToast('Restricción: Las sumas del desglose deben ser mayores a $0 para enviar a contabilidad.', 'error'); return; }
            if (!centroCostos) { showToast('Restricción: Por favor, ingrese un Centro de Costos válido.', 'error'); return; }
            if (!motivo) { showToast('Falta de Información: Ingrese la justificación del gasto incurrido.', 'error'); return; }
            if (evidenciasFiles.length === 0) { showToast('Auditoría Contable: Es mandatorio proveer la evidencia digital (Archivo PDF).', 'warning'); return; }

            Swal.fire({
                title: `<span style="font-family:'Poppins', sans-serif;">Consentimiento de Transacción</span>`,
                html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">La matriz de reembolsos por valor de <strong>${fmt(total)}</strong> pasará a revisión de su jefatura. Toda alteración post-emisión quedará bloqueada.</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: 'var(--teal-dark)',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: `<span style="font-family:'Poppins', sans-serif; font-weight:600;">Emitir Responsiva</span>`,
                cancelButtonText: `<span style="font-family:'Poppins', sans-serif;">Retornar al Formato</span>`
            }).then((result) => {
                if (result.isConfirmed) {
                    // Empuja a fase 'Pendiente' con el status de caja 'En espera' de flujo inicial
                    procesarEnvio('Pendiente', 'En espera');
                    Swal.fire({
                        title: '<span style="font-family:\'Poppins\', sans-serif;">¡Expediente de Desembolso Creado!</span>',
                        html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">Su proceso compuesto por ${evidenciasFiles.length} documento(s) soporte ha sido despachado en la plataforma.</span>`,
                        icon: 'success',
                        confirmButtonColor: 'var(--teal-dark)',
                        confirmButtonText: '<span style="font-family:\'Poppins\', sans-serif; font-weight:600;">Cerrar Sistema</span>'
                    });
                }
            });
        }

        /* ── PROTOCOLO DE SALVAGUARDAS Y CONTINUIDAD OPERATIVA ── */
        function saveDraft() {
            const motivo = document.getElementById('modal-motivo').value.trim();

            if (!motivo) {
                showToast('Advertencia: El guardado referencial exige una descripción de motivo para ubicarlo en el futuro.', 'warning');
                return;
            }

            procesarEnvio('Borrador', 'N/A');
            showToast('Backup Sistémico: El borrador contable se empaquetó con éxito en su carpeta local.', 'success');
        }

        /* ── ENSAMBLADOR DE OBJETOS AL DB MOCK FRONTEND ── */
        function procesarEnvio(estadoRevision, estadoPago) {
            const total = parseFloat(document.getElementById('sum-total').getAttribute('data-value'));
            const f = new Date();
            const fechaStr = `${String(f.getDate()).padStart(2, '0')}/${String(f.getMonth() + 1).padStart(2, '0')}/${f.getFullYear()}`;

            requests.unshift({
                id: currentId++,
                fecha: fechaStr,
                folioP: `VES-${String(currentId).padStart(4, '0')}`,
                folioU: 'SFP-005',
                nombre: document.getElementById('modal-nombre').value,
                motivo: document.getElementById('modal-motivo').value.trim(),
                depto: document.getElementById('modal-depto').value || 'Sin Asignar',
                amount: total,
                status: estadoRevision,
                pago: estadoPago
            });
            closeModal();
            renderDashboard();
        }

        /* ── INICIALIZADOR DE SUBSISTEMA AL DOM LOADED ── */
        renderDashboard();
    </script>
@endpush
