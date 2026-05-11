@extends('modules.administration.reembolsos.index')

@section('content')
    {{-- ── LIBRERÍAS EXTERNAS ── --}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">


    <div class="reimbursements-container">

        {{-- ── ENCABEZADO DE VISTA ── --}}
        <header class="view-header">
            <div>
                <h2 class="view-title">Panel de <strong>Reembolsos</strong></h2>
                <p class="view-subtitle">
                    <i class="bx bx-line-chart"></i>
                    Administra el historial y registra nuevas comprobaciones.
                </p>
            </div>
            <button class="btn btn-primary" onclick="openModalForCreate()">
                <i class="bx bx-plus-circle"></i> Nuevo Reembolso
            </button>
        </header>

        {{-- ── TARJETAS DE MÉTRICAS ── --}}
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
                    <span class="metric-label">Pendientes</span>
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

        {{-- ── TABLA DE HISTORIAL ── --}}
        <div class="card history-card">
            <div class="card-header">
                <span class="card-title">
                    <i class="bx bx-history"></i> Historial de Solicitudes
                </span>
                <div class="table-controls">
                    <div class="search-wrap">
                        <i class="bx bx-search search-icon"></i>
                        <input type="text" id="table-search" class="search-input"
                            placeholder="Buscar por motivo o folio...">
                    </div>
                    <div class="filter-tabs" id="filter-tabs">
                        <button class="filter-tab active" data-filter="all">Todos</button>
                        <button class="filter-tab" data-filter="Pendiente">Pendiente</button>
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
                            <th>Solicitante</th>
                            <th>Motivo</th>
                            <th>Departamento</th>
                            <th>Monto Total</th>
                            <th>Estado de Revisión</th>
                            <th>Estado de Pago</th>
                            <th class="cell-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="dashboard-list"></tbody>
                </table>
            </div>

            {{-- Estado vacío --}}
            <div id="empty-state" class="empty-state hidden">
                <i class="bx bx-file-blank empty-icon"></i>
                <p class="empty-title">Sin resultados</p>
                <p class="empty-desc">No hay solicitudes que coincidan con tu búsqueda o filtro.</p>
            </div>

            <div class="table-footer">
                <span id="table-count" class="table-count-label">0 solicitudes</span>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════
         MODAL DE REEMBOLSO
         ══════════════════════════════════════════════════════ --}}
    <div id="reimbursement-modal" class="modal-bg hidden">
        <div class="modal-box">

            <div class="modal-header">
                <h2 class="modal-title" id="main-modal-title">
                    <i class="bx bx-receipt"></i>
                    Formato de <strong>Reembolso</strong>
                </h2>
                <div style="display:flex; align-items:center; gap:.75rem;">
                    <button type="button" class="btn btn-secondary" onclick="toggleUuidPanel()"
                        title="Autocompletar desde factura SAT">
                        <i class="bx bx-barcode"></i> Cargar Factura SAT
                    </button>
                    <button class="btn-close" onclick="closeModal()">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            </div>

            <div class="modal-body" id="modal-body-scroll">

                {{-- ── PANEL UUID ── --}}
                <div id="uuid-panel" class="hidden"
                    style="background:#0f172a; border:1px solid #1e293b; border-radius:.75rem; padding:1.5rem; margin-bottom:1.5rem; position:relative; overflow:hidden;">
                    <div
                        style="position:absolute;top:0;left:0;width:100%;height:4px;background:linear-gradient(to right,#14b8a6,#0d9488);">
                    </div>
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
                    <p id="search-message" class="hidden" style="font-size:.72rem;color:#ef4444;margin-bottom:.75rem;">
                    </p>
                    <div id="drop-zone-container" class="hidden" style="border-top:1px solid #334155; padding-top:1rem;">
                        <div id="drop-zone"
                            style="border:2px dashed rgba(20,184,166,.45);background:rgba(20,184,166,.08);border-radius:.75rem;padding:1.5rem;text-align:center;cursor:pointer;transition:background .15s;">
                            <i class="bx bx-cloud-upload" style="font-size:3rem;color:#14b8a6;"></i>
                            <p style="margin-top:.5rem;font-size:.85rem;color:#cbd5e1;">No encontrado en base local.</p>
                            <small
                                style="display:block;margin-top:.25rem;font-size:.75rem;font-weight:600;color:#14b8a6;text-transform:uppercase;">Arrastra
                                tu .XML aquí</small>
                            <input type="file" id="xml-input" accept=".xml" style="display:none;">
                        </div>
                    </div>
                </div>

                {{-- ── NUEVO DISEÑO: CABECERA DEL COMPROBANTE ── --}}
                <div class="form-header-card">

                    {{-- Barra Superior Clara: Datos Fijos del Sistema y Lugar --}}
                    <div class="fh-info-strip">
                        <div class="fh-info-item">
                            <span>RFC Empresa</span>
                            <strong id="res-rfc">VES1607057K7</strong>
                        </div>
                        <div class="fh-info-item folio">
                            <span>Folio Principal</span>
                            <strong id="modal-folio-p">VES-0001</strong>
                        </div>
                        <div class="fh-info-item">
                            <span>Folio del Usuario</span>
                            <strong id="modal-folio-u" style="color:#64748b;">SFP-001</strong>
                        </div>

                        {{-- Campo LUGAR integrado en la cabecera --}}
                        <div class="fh-info-item">
                            <span>Lugar</span>
                            <div class="input-group" style="margin-top: 3px;">
                                <i class="bx bx-map field-icon" style="font-size: 1.05rem; left: 0.6rem;"></i>
                                <input type="text" id="modal-lugar" value="VHSA, TAB."
                                    class="input-field modal-focusable"
                                    style="padding: 0.4rem 0.5rem 0.4rem 2rem; width: 140px; font-weight: 600; color: #0f172a; background: #fff;">
                            </div>
                        </div>

                        <div class="fh-info-item" style="text-align:right;">
                            <span>Fecha de Solicitud</span>
                            <strong id="modal-fecha-hoy"></strong>
                        </div>
                    </div>

                    {{-- Cuerpo del Formulario: Datos Editables --}}
                    <div class="fh-body">

                        <div class="fh-row-pills">
                            <div>
                                <label class="input-label">Tipo de Gasto</label>
                                <div class="radio-pill-group">
                                    <label class="radio-pill-label">
                                        <input type="radio" name="tipo_gasto" value="viaje" class="modal-focusable"
                                            checked>
                                        <i class="bx bxs-plane-alt"></i> Viaje
                                    </label>
                                    <label class="radio-pill-label">
                                        <input type="radio" name="tipo_gasto" value="operacion"
                                            class="modal-focusable">
                                        <i class="bx bx-briefcase"></i> Operación
                                    </label>
                                    <label class="radio-pill-label">
                                        <input type="radio" name="tipo_gasto" value="otros" class="modal-focusable">
                                        <i class="bx bx-dots-horizontal-rounded"></i> Otros
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="fh-grid-4">
                            <div>
                                <label class="input-label">Nombre del Solicitante</label>
                                <div class="input-group">
                                    <i class="bx bx-user field-icon"></i>
                                    <input type="text" id="modal-nombre"
                                        value="{{ $userData['nombre'] ?? 'Saul Falcon Perez' }}" class="input-field"
                                        readonly>
                                </div>
                            </div>
                            <div>
                                <label class="input-label">Departamento</label>
                                <div class="input-group">
                                    <i class="bx bx-buildings field-icon"></i>
                                    <input type="text" id="modal-depto"
                                        value="{{ $userData['departamento'] ?? 'Desarrollo' }}" class="input-field"
                                        readonly>
                                </div>
                            </div>
                            <div>
                                <label class="input-label">Centro de Costos</label>
                                <div class="input-group">
                                    <i class="bx bx-building-house field-icon"></i>
                                    <select id="modal-centro-costos" class="input-field modal-focusable">
                                        <option value="" disabled selected>Seleccione...</option>
                                        <option value="VNC-OP-01">VNC-OP-01 Operaciones</option>
                                        <option value="VNC-TI-02">VNC-TI-02 Tecnologías de la Información</option>
                                        <option value="VNC-AD-03">VNC-AD-03 Administración</option>
                                        <option value="VNC-CM-04">VNC-CM-04 Comercial</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="input-label">Motivo General del Reembolso</label>
                                <div class="input-group">
                                    <i class="bx bx-text field-icon"></i>
                                    <input type="text" id="modal-motivo" class="input-field modal-focusable"
                                        placeholder="Ej. Viáticos proyecto foráneo">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ── TABLA DE ANÁLISIS DE GASTOS ── --}}
                <div class="expense-card">
                    <div class="expense-card-header">
                        <span class="expense-card-title">
                            <i class="bx bx-table"></i> Análisis de Gastos
                        </span>
                    </div>
                    <div class="table-scroll">
                        <table class="expense-table">
                            <thead>
                                <tr class="th-group">
                                    <th colspan="3" style="text-align:left;">Comprobante</th>
                                    <th colspan="3" class="th-importes">Importes</th>
                                    <th colspan="2">Impuestos</th>
                                    <th rowspan="2"
                                        style="background:#0f172a; color:#fff; text-align:right; padding:.6rem .75rem;">
                                        Total</th>
                                    <th rowspan="2" style="background:#0f172a; width:45px;"></th>
                                </tr>
                                <tr class="th-cols">
                                    <th style="width:140px;">Fecha Factura</th>
                                    <th style="width:100px;">No. Factura</th>
                                    <th>Concepto</th>
                                    <th style="width:90px;">Con Comp.<br>(PDF y XML)</th>
                                    <th style="width:90px;">Con Comp.<br>No Fiscal</th>
                                    <th style="width:90px;">Sin Comp.<br>/ Propinas</th>
                                    <th style="width:80px;">I.S.H.<br>Otros</th>
                                    <th style="width:75px;">I.V.A.</th>
                                </tr>
                            </thead>

                            {{-- VUELOS Y/O TRANSPORTE --}}
                            <tbody id="cat-vuelos">
                                <tr class="cat-row">
                                    <td colspan="10">
                                        <div style="display:flex; justify-content:space-between; align-items:center;">
                                            <span><i class="bx bxs-plane-alt"></i> Vuelos y / o Transporte</span>
                                            <button type="button" class="btn-add-row" onclick="addRow('cat-vuelos')"
                                                title="Agregar Fila">
                                                <i class="bx bx-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="data-row">
                                    <td>
                                        <div class="date-wrap"><i class="bx bx-calendar"></i><input type="text"
                                                class="cell-input date-in modal-focusable" placeholder="DD/MM/AAAA"
                                                data-fp></div>
                                    </td>
                                    <td><input type="text" class="cell-input modal-focusable" placeholder="—"></td>
                                    <td><input type="text" class="cell-input modal-focusable" placeholder="—"></td>
                                    <td><input type="number" oninput="calcTotal()"
                                            class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
                                    <td><input type="number" oninput="calcTotal()"
                                            class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
                                    <td><input type="number" oninput="calcTotal()"
                                            class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
                                    <td><input type="number" oninput="calcTotal()"
                                            class="cell-input num c-ish modal-focusable" placeholder="0.00"></td>
                                    <td><input type="number" oninput="calcTotal()"
                                            class="cell-input num c-iva modal-focusable" placeholder="0.00"></td>
                                    <td class="cell-row-total">-</td>
                                    <td style="text-align:center;"><button type="button" class="btn-remove-row"
                                            onclick="removeRow(this)" title="Eliminar fila"><i
                                                class="bx bx-trash"></i></button></td>
                                </tr>
                            </tbody>

                            {{-- RESTAURANTES Y/O COMIDAS --}}
                            <tbody id="cat-restaurantes">
                                <tr class="cat-row">
                                    <td colspan="10">
                                        <div style="display:flex; justify-content:space-between; align-items:center;">
                                            <span><i class="bx bx-restaurant"></i> Restaurantes y / o Comidas</span>
                                            <button type="button" class="btn-add-row"
                                                onclick="addRow('cat-restaurantes')" title="Agregar Fila">
                                                <i class="bx bx-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="data-row">
                                    <td>
                                        <div class="date-wrap"><i class="bx bx-calendar"></i><input type="text"
                                                class="cell-input date-in modal-focusable" placeholder="DD/MM/AAAA"
                                                data-fp></div>
                                    </td>
                                    <td><input type="text" class="cell-input modal-focusable" placeholder="—"></td>
                                    <td><input type="text" class="cell-input modal-focusable" placeholder="—"></td>
                                    <td><input type="number" oninput="calcTotal()"
                                            class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
                                    <td><input type="number" oninput="calcTotal()"
                                            class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
                                    <td><input type="number" oninput="calcTotal()"
                                            class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
                                    <td><input type="number" oninput="calcTotal()"
                                            class="cell-input num c-ish modal-focusable" placeholder="0.00"></td>
                                    <td><input type="number" oninput="calcTotal()"
                                            class="cell-input num c-iva modal-focusable" placeholder="0.00"></td>
                                    <td class="cell-row-total">-</td>
                                    <td style="text-align:center;"><button type="button" class="btn-remove-row"
                                            onclick="removeRow(this)" title="Eliminar fila"><i
                                                class="bx bx-trash"></i></button></td>
                                </tr>
                            </tbody>

                            {{-- COMBUSTIBLE --}}
                            <tbody id="cat-combustible">
                                <tr class="cat-row">
                                    <td colspan="10">
                                        <div style="display:flex; justify-content:space-between; align-items:center;">
                                            <span><i class="bx bxs-gas-pump"></i> Combustible</span>
                                            <button type="button" class="btn-add-row"
                                                onclick="addRow('cat-combustible')" title="Agregar Fila">
                                                <i class="bx bx-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="data-row">
                                    <td>
                                        <div class="date-wrap"><i class="bx bx-calendar"></i><input type="text"
                                                class="cell-input date-in modal-focusable" placeholder="DD/MM/AAAA"
                                                data-fp></div>
                                    </td>
                                    <td><input type="text" class="cell-input modal-focusable" placeholder="—"></td>
                                    <td><input type="text" class="cell-input modal-focusable" placeholder="—"></td>
                                    <td><input type="number" oninput="calcTotal()"
                                            class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
                                    <td><input type="number" oninput="calcTotal()"
                                            class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
                                    <td><input type="number" oninput="calcTotal()"
                                            class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
                                    <td><input type="number" oninput="calcTotal()"
                                            class="cell-input num c-ish modal-focusable" placeholder="0.00"></td>
                                    <td><input type="number" oninput="calcTotal()"
                                            class="cell-input num c-iva modal-focusable" placeholder="0.00"></td>
                                    <td class="cell-row-total">-</td>
                                    <td style="text-align:center;"><button type="button" class="btn-remove-row"
                                            onclick="removeRow(this)" title="Eliminar fila"><i
                                                class="bx bx-trash"></i></button></td>
                                </tr>
                            </tbody>

                            {{-- OTROS --}}
                            <tbody id="cat-otros">
                                <tr class="cat-row">
                                    <td colspan="10">
                                        <div style="display:flex; justify-content:space-between; align-items:center;">
                                            <span><i class="bx bx-package"></i> Otros</span>
                                            <button type="button" class="btn-add-row" onclick="addRow('cat-otros')"
                                                title="Agregar Fila">
                                                <i class="bx bx-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="data-row">
                                    <td>
                                        <div class="date-wrap"><i class="bx bx-calendar"></i><input type="text"
                                                class="cell-input date-in modal-focusable" placeholder="DD/MM/AAAA"
                                                data-fp></div>
                                    </td>
                                    <td><input type="text" class="cell-input modal-focusable" placeholder="—"></td>
                                    <td><input type="text" class="cell-input modal-focusable" placeholder="—"></td>
                                    <td><input type="number" oninput="calcTotal()"
                                            class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
                                    <td><input type="number" oninput="calcTotal()"
                                            class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
                                    <td><input type="number" oninput="calcTotal()"
                                            class="cell-input num c-sub modal-focusable" placeholder="0.00"></td>
                                    <td><input type="number" oninput="calcTotal()"
                                            class="cell-input num c-ish modal-focusable" placeholder="0.00"></td>
                                    <td><input type="number" oninput="calcTotal()"
                                            class="cell-input num c-iva modal-focusable" placeholder="0.00"></td>
                                    <td class="cell-row-total">-</td>
                                    <td style="text-align:center;"><button type="button" class="btn-remove-row"
                                            onclick="removeRow(this)" title="Eliminar fila"><i
                                                class="bx bx-trash"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ── ZONA INFERIOR: EVIDENCIA Y RESUMEN ── --}}
                <div class="bottom-section">

                    <div class="evidence-panel" id="evidence-panel"
                        onclick="document.getElementById('evidence-upload').click()">
                        <i class="bx bxs-file-pdf evidence-icon"></i>
                        <h4 class="evidence-title">Subir Evidencia PDF</h4>
                        <p class="evidence-desc">Arrastra y suelta tus comprobantes aquí.<br>Max. 10MB por archivo.</p>
                        <button type="button" class="btn btn-secondary"
                            onclick="event.stopPropagation(); document.getElementById('evidence-upload').click()">
                            <i class="bx bx-folder-plus"></i> Seleccionar PDFs
                        </button>
                        <input type="file" id="evidence-upload" accept=".pdf" multiple class="hidden">
                        <div id="evidence-list" class="evidence-list" onclick="event.stopPropagation()"></div>
                    </div>

                    <div class="summary-box">
                        <div class="summary-head">
                            <i class="bx bx-calculator"></i>
                            <span>Resumen Total de Gastos</span>
                        </div>
                        <div class="summary-body">
                            <div class="summary-row"><span class="sum-lbl">Sub-Total de Gastos:</span><span
                                    id="sum-subtotal" class="sum-val">$0.00</span></div>
                            <div class="summary-row"><span class="sum-lbl">Total de Gastos:</span><span id="sum-gastos"
                                    class="sum-val">$0.00</span></div>
                            <div class="summary-row"><span class="sum-lbl">I.V.A.:</span><span id="sum-iva"
                                    class="sum-val">$0.00</span></div>
                            <div class="summary-row"><span class="sum-lbl">I.S.H. / Otros Impuestos:</span><span
                                    id="sum-ish" class="sum-val">$0.00</span></div>
                            <div class="sum-total-row">
                                <span class="sum-total-lbl">TOTAL</span>
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
                        Asegúrate de adjuntar la evidencia en PDF antes de guardar.
                    </span>
                </div>

                {{-- Controles para Crear --}}
                <div class="modal-footer-right" id="footer-create">
                    <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancelar</button>
                    <button type="button" id="btn-borrador" class="btn btn-secondary" onclick="saveDraft()">
                        <i class="bx bx-save"></i> Guardar Borrador
                    </button>
                    <button type="button" id="btn-enviar" class="btn btn-primary" onclick="verifyAndSubmit()">
                        <i class="bx bx-send"></i> Verificar y Enviar
                    </button>
                </div>

                {{-- Controles para Solo Ver --}}
                <div class="modal-footer-right hidden" id="footer-view">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cerrar Detalles</button>
                </div>

                {{-- Controles para Evaluar --}}
                <div class="modal-footer-right hidden" id="footer-evaluate">
                    <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancelar</button>
                    <button type="button" class="btn btn-fail-solid" onclick="processEvaluation('Rechazado')">
                        <i class="bx bx-x"></i> Rechazar
                    </button>
                    <button type="button" class="btn btn-ok-solid" onclick="processEvaluation('Aprobado')">
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
        /* ── NAVEGACIÓN CON TECLA ENTER ── */
        document.getElementById('reimbursement-modal').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                // Buscar todos los elementos focusables marcados con la clase modal-focusable
                const focusableElements = Array.from(this.querySelectorAll('.modal-focusable'));
                const currentIndex = focusableElements.indexOf(document.activeElement);

                if (currentIndex > -1 && currentIndex < focusableElements.length - 1) {
                    focusableElements[currentIndex + 1].focus();
                }
            }
        });

        /* ── TOAST ── */
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

        /* ── DATOS MOCK (Actualizados con lógica correcta) ── */
        let requests = [{
                id: 1001,
                folioP: 'VES-0001',
                folioU: 'SFP-001',
                nombre: 'Saul Falcon Perez',
                motivo: 'Visita a cliente externo',
                depto: 'Desarrollo',
                amount: 3500.00,
                status: 'Aprobado',
                pago: 'Pagado'
            },
            {
                id: 1002,
                folioP: 'VES-0002',
                folioU: 'SFP-002',
                nombre: 'Saul Falcon Perez',
                motivo: 'Compra equipo menor',
                depto: 'Desarrollo',
                amount: 850.50,
                status: 'Pendiente',
                pago: 'En proceso'
            },
            {
                id: 1003,
                folioP: 'VES-0003',
                folioU: 'SFP-003',
                nombre: 'Saul Falcon Perez',
                motivo: 'Viáticos proyecto foráneo',
                depto: 'Desarrollo',
                amount: 6200.00,
                status: 'Pendiente',
                pago: 'En proceso'
            },
            {
                id: 1004,
                folioP: 'VES-0004',
                folioU: 'SFP-004',
                nombre: 'Saul Falcon Perez',
                motivo: 'Material de oficina',
                depto: 'Desarrollo',
                amount: 430.00,
                status: 'Rechazado',
                pago: 'Cancelado'
            },
        ];
        let currentId = 1005;
        let currentEvaluateId = null;

        const fmt = n => new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN'
        }).format(n);

        /* ── FECHA HOY ── */
        document.getElementById('modal-fecha-hoy').textContent =
            new Date().toLocaleDateString('es-MX', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

        /* ── ESTADO FILTRO / BÚSQUEDA ── */
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

        /* ── RENDER DASHBOARD ── */
        function renderDashboard() {
            const list = document.getElementById('dashboard-list');
            const emptyState = document.getElementById('empty-state');
            const tableCount = document.getElementById('table-count');
            list.innerHTML = '';

            let totalAcc = 0,
                pendCount = 0,
                pendAmt = 0,
                appCount = 0,
                appAmt = 0,
                rejCount = 0,
                rejAmt = 0;
            requests.forEach(req => {
                if (req.status !== 'Borrador') {
                    totalAcc += req.amount;
                }
                if (req.status === 'Pendiente') {
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

            document.getElementById('metric-total-val').textContent = fmt(totalAcc);
            document.getElementById('metric-pending-val').textContent = pendCount;
            document.getElementById('metric-approved-val').textContent = appCount;
            document.getElementById('metric-rejected-val').textContent = rejCount;
            document.getElementById('metric-pending-amount').textContent = fmt(pendAmt);
            document.getElementById('metric-approved-amount').textContent = fmt(appAmt);
            document.getElementById('metric-rejected-amount').textContent = fmt(rejAmt);

            let filtered = requests.filter(req => {
                const matchFilter = activeFilter === 'all' || req.status === activeFilter;
                const matchSearch = !searchQuery || req.motivo.toLowerCase().includes(searchQuery) || req.folioP
                    .toLowerCase().includes(searchQuery) || req.folioU.toLowerCase().includes(searchQuery);
                return matchFilter && matchSearch;
            });

            tableCount.textContent = `${filtered.length} solicitud${filtered.length !== 1 ? 'es' : ''}`;

            if (filtered.length === 0) {
                emptyState.classList.remove('hidden');
                return;
            }
            emptyState.classList.add('hidden');

            filtered.forEach((req) => {
                const globalIdx = requests.findIndex(r => r.id === req.id);

                // Badge de Estado de Revisión
                let badge = '';
                if (req.status === 'Aprobado') badge =
                    `<span class="status-badge badge-ok"><i class="bx bx-check-circle"></i> Aprobado</span>`;
                else if (req.status === 'Rechazado') badge =
                    `<span class="status-badge badge-fail"><i class="bx bx-x-circle"></i> Rechazado</span>`;
                else if (req.status === 'Borrador') badge =
                    `<span class="status-badge badge-draft"><i class="bx bx-edit-alt"></i> Borrador</span>`;
                else badge =
                    `<span class="status-badge badge-wait"><i class="bx bx-hourglass"></i> Pendiente</span>`;

                // Badge de Estado de Pago (NUEVOS COLORES)
                let badgePago = '';
                if (req.pago === 'Pagado') badgePago =
                    `<span class="status-badge badge-pay-ok"><i class="bx bx-money"></i> Pagado</span>`;
                else if (req.pago === 'En proceso') badgePago =
                    `<span class="status-badge badge-pay-wait"><i class="bx bx-loader-alt bx-spin"></i> En proceso</span>`;
                else if (req.pago === 'Cancelado') badgePago =
                    `<span class="status-badge badge-pay-fail"><i class="bx bx-block"></i> Cancelado</span>`;
                else badgePago = `<span class="status-badge badge-pay-na"><i class="bx bx-minus"></i> N/A</span>`;

                const evaluateBtn = req.status === 'Pendiente' ?
                    `<button class="btn-icon btn-icon-evaluate" onclick="evaluarSolicitud(${req.id})" title="Evaluar Solicitud"><i class="bx bx-check-shield"></i></button>` :
                    '';

                const actions = `
                    <div class="actions-wrap">
                        <button class="btn-icon btn-icon-view" onclick="verDetalles(${req.id})" title="Ver Detalles"><i class="bx bx-show"></i></button>
                        ${evaluateBtn}
                    </div>
                `;

                list.innerHTML += `
                <tr>
                    <td class="row-index">${globalIdx + 1}</td>
                    <td><span class="row-folio"><i class="bx bx-hash"></i> ${req.folioP}</span></td>
                    <td><span class="row-folio user-folio">${req.folioU}</span></td>
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

        /* ── FILAS DINÁMICAS (AGREGAR Y QUITAR) ── */
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
                <td style="text-align:center;"><button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Eliminar fila"><i class="bx bx-trash"></i></button></td>
            </tr>`;
        }

        function addRow(tbodyId) {
            const tbody = document.getElementById(tbodyId);
            tbody.insertAdjacentHTML('beforeend', getRowTemplate());
            const newRow = tbody.lastElementChild;
            flatpickr(newRow.querySelector('[data-fp]'), {
                locale: "es",
                dateFormat: "d/m/Y",
                allowInput: true,
                disableMobile: "true"
            });
        }

        function removeRow(btn) {
            const row = btn.closest('tr');
            const tbody = row.closest('tbody');
            if (tbody.querySelectorAll('.data-row').length > 1) {
                row.remove();
                calcTotal();
            } else {
                showToast('Debe haber al menos una fila en esta categoría.', 'warning');
            }
        }

        /* ── MODOS DEL MODAL ── */
        function resetModalForm() {
            ['cat-vuelos', 'cat-restaurantes', 'cat-combustible', 'cat-otros'].forEach(cat => {
                const tbody = document.getElementById(cat);
                const rows = tbody.querySelectorAll('.data-row');
                for (let i = 1; i < rows.length; i++) {
                    rows[i].remove();
                }
            });

            document.querySelectorAll('.cell-input').forEach(el => {
                if (!el.hasAttribute('readonly') && (el.type === 'number' || (el.type === 'text' && !el
                        .hasAttribute('data-fp')))) el.value = '';
            });
            document.getElementById('modal-motivo').value = '';
            document.getElementById('modal-centro-costos').value = '';
            document.getElementById('uuid-panel').classList.add('hidden');
            evidenciasFiles = [];
            renderFileList();
            actualizarInputFiles();
            calcTotal();

            flatpickr(".data-row [data-fp]", {
                locale: "es",
                dateFormat: "d/m/Y",
                allowInput: true,
                disableMobile: "true"
            });
        }

        function openModalForCreate() {
            resetModalForm();
            document.getElementById('main-modal-title').innerHTML =
                '<i class="bx bx-receipt"></i> Formato de <strong>Reembolso</strong>';
            document.getElementById('modal-folio-p').textContent = `VES-${String(currentId).padStart(4, '0')}`;
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
            if (!req) return;
            resetModalForm();
            document.getElementById('main-modal-title').innerHTML =
                '<i class="bx bx-show"></i> Detalles de <strong>Reembolso</strong>';

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
            document.getElementById('main-modal-title').innerHTML =
                '<i class="bx bx-check-shield"></i> Evaluar <strong>Reembolso</strong>';
            currentEvaluateId = id;

            document.getElementById('modal-folio-p').textContent = req.folioP;
            document.getElementById('modal-folio-u').textContent = req.folioU;
            document.getElementById('modal-nombre').value = req.nombre;
            document.getElementById('modal-depto').value = req.depto;
            document.getElementById('modal-motivo').value = req.motivo;
            document.getElementById('sum-total').textContent = fmt(req.amount);

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
            const isApprove = status === 'Aprobado';
            const actionText = isApprove ? 'aprobar' : 'rechazar';
            const confirmColor = isApprove ? 'var(--teal-dark)' : '#ef4444';

            Swal.fire({
                title: `<span style="font-family:'Poppins', sans-serif;">¿Confirmar Evaluación?</span>`,
                html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">¿Estás seguro de que deseas <strong>${actionText}</strong> esta solicitud?</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#94a3b8',
                confirmButtonText: `<span style="font-family:'Poppins', sans-serif; font-weight:600;">Sí, ${actionText}</span>`,
                cancelButtonText: `<span style="font-family:'Poppins', sans-serif;">Cancelar</span>`
            }).then((result) => {
                if (result.isConfirmed) {
                    updateStatus(currentEvaluateId, status);
                    closeModal();
                    currentEvaluateId = null;
                }
            });
        }

        /* ── ACTUALIZAR ESTADOS (Lógica de Revisión -> Pago) ── */
        function updateStatus(id, status) {
            const i = requests.findIndex(r => r.id === id);
            if (i !== -1) {
                requests[i].status = status;

                // Si se rechaza, el pago automáticamente se cancela
                if (status === 'Rechazado') {
                    requests[i].pago = 'Cancelado';
                }
                // Si se aprueba, el pago entra en proceso para Tesorería
                else if (status === 'Aprobado') {
                    requests[i].pago = 'En proceso';
                }

                renderDashboard();
                showToast(`Solicitud ${status.toLowerCase()} correctamente`, status === 'Aprobado' ? 'success' : 'error');
            }
        }

        /* ── ARCHIVOS DE EVIDENCIA ── */
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
            let errorSize = false,
                errorType = false;
            Array.from(files).forEach(file => {
                if (file.type !== 'application/pdf') {
                    errorType = true;
                    return;
                }
                if (file.size > maxFileSize) {
                    errorSize = true;
                    return;
                }
                if (!evidenciasFiles.some(f => f.name === file.name)) evidenciasFiles.push(file);
            });
            if (errorType) showToast('Solo se permiten archivos en formato PDF.', 'warning');
            if (errorSize) showToast('Uno o más archivos superan los 10MB.', 'error');
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
            const k = 1024,
                dm = decimals < 0 ? 0 : decimals;
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
                        <button type="button" class="btn-remove-file" onclick="event.stopPropagation(); removeFile(${index})" title="Eliminar">
                            <i class="bx bx-x"></i>
                        </button>
                    </div>`;
                });
                html += '</div>';
                listDiv.innerHTML = html;
            }
        }

        /* ── TOGGLE PANEL UUID ── */
        function toggleUuidPanel() {
            document.getElementById('uuid-panel').classList.toggle('hidden');
        }

        /* ── BÚSQUEDA UUID ── */
        function buscarFactura() {
            const uuid = document.getElementById('search-uuid').value.trim();
            const btnB = document.getElementById('btn-buscar');
            const dz = document.getElementById('drop-zone-container');
            if (uuid.length < 10) {
                showToast('Ingresa un UUID válido', 'warning');
                return;
            }
            btnB.innerHTML = '<span class="spinner"></span> Buscando...';
            btnB.disabled = true;
            dz.classList.add('hidden');
            setTimeout(() => {
                btnB.innerHTML = '<i class="bx bx-search"></i> Buscar';
                btnB.disabled = false;
                dz.classList.remove('hidden');
                showToast('UUID no encontrado. Usa el XML manual.', 'error');
            }, 800);
        }

        /* ── DRAG & DROP XML ── */
        const dropZoneUI = document.getElementById('drop-zone');
        const xmlInput = document.getElementById('xml-input');
        dropZoneUI.addEventListener('click', () => xmlInput.click());
        xmlInput.addEventListener('change', e => leerXML(e.target.files[0]));
        dropZoneUI.addEventListener('dragover', e => {
            e.preventDefault();
            dropZoneUI.style.background = 'rgba(20,184,166,.18)';
        });
        dropZoneUI.addEventListener('dragleave', e => {
            e.preventDefault();
            dropZoneUI.style.background = '';
        });
        dropZoneUI.addEventListener('drop', e => {
            e.preventDefault();
            dropZoneUI.style.background = '';
            if (e.dataTransfer.files.length) leerXML(e.dataTransfer.files[0]);
        });

        function leerXML(file) {
            if (!file || file.type !== 'text/xml') {
                showToast('Sube un archivo .xml válido', 'error');
                return;
            }
            const reader = new FileReader();
            reader.onload = e => {
                const xml = new DOMParser().parseFromString(e.target.result, 'text/xml');
                const attr = (tag, a) => {
                    const n = xml.getElementsByTagNameNS('*', tag)[0] || xml.getElementsByTagName(tag)[0] || xml
                        .getElementsByTagName('cfdi:' + tag)[0];
                    return n ? n.getAttribute(a) : null;
                };
                const d = {
                    uuid: attr('TimbreFiscalDigital', 'UUID'),
                    rfc: attr('Emisor', 'Rfc') || 'Sin RFC',
                    fecha: (attr('Comprobante', 'Fecha') || '').split('T')[0]
                };
                if (d.fecha) {
                    const [y, m, dd] = d.fecha.split('-');
                    d.fechaFormateada = `${dd}/${m}/${y}`;
                }
                if (d.uuid) {
                    document.getElementById('res-rfc').textContent = d.rfc;
                    document.getElementById('search-uuid').value = d.uuid;
                    const firstDateInput = document.querySelector('[data-fp]');
                    if (firstDateInput && d.fechaFormateada) {
                        if (firstDateInput._flatpickr) firstDateInput._flatpickr.setDate(d.fechaFormateada, true,
                            "d/m/Y");
                        else firstDateInput.value = d.fechaFormateada;
                    }
                    document.getElementById('uuid-panel').classList.add('hidden');
                    showToast('Datos de factura cargados', 'success');
                } else {
                    showToast('El XML no parece ser del SAT.', 'error');
                }
            };
            reader.readAsText(file);
        }

        /* ── CÁLCULO TOTALES ── */
        function calcTotal() {
            let gSub = 0,
                gIva = 0,
                gIsh = 0;
            document.querySelectorAll('.data-row').forEach(row => {
                let rSub = 0;
                const rIva = parseFloat(row.querySelector('.c-iva')?.value) || 0;
                const rIsh = parseFloat(row.querySelector('.c-ish')?.value) || 0;
                row.querySelectorAll('.c-sub').forEach(i => rSub += parseFloat(i.value) || 0);
                const rowTotal = row.querySelector('.cell-row-total');
                if (rowTotal) rowTotal.textContent = (rSub + rIva + rIsh) > 0 ? fmt(rSub + rIva + rIsh) : '-';
                gSub += rSub;
                gIva += rIva;
                gIsh += rIsh;
            });
            const gTotal = gSub + gIva + gIsh;
            document.getElementById('sum-subtotal').textContent = fmt(gSub);
            document.getElementById('sum-gastos').textContent = fmt(gSub);
            document.getElementById('sum-iva').textContent = fmt(gIva);
            document.getElementById('sum-ish').textContent = fmt(gIsh);
            const el = document.getElementById('sum-total');
            el.textContent = fmt(gTotal);
            el.setAttribute('data-value', gTotal);
        }

        /* ── VERIFICACIÓN Y ENVÍO (SWEETALERT CONFIRM) ── */
        function verifyAndSubmit() {
            const total = parseFloat(document.getElementById('sum-total').getAttribute('data-value'));
            const motivo = document.getElementById('modal-motivo').value.trim();
            const centroCostos = document.getElementById('modal-centro-costos').value;

            if (total <= 0) {
                showToast('El desglose debe ser mayor a $0.', 'error');
                return;
            }
            if (!centroCostos) {
                showToast('Selecciona un Centro de Costos.', 'error');
                return;
            }
            if (!motivo) {
                showToast('Por favor escribe el motivo del gasto.', 'error');
                return;
            }
            if (evidenciasFiles.length === 0) {
                showToast('Adjunta al menos un PDF de evidencia.', 'warning');
                return;
            }

            Swal.fire({
                title: `<span style="font-family:'Poppins', sans-serif;">¿Verificaste la información?</span>`,
                html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">El reembolso por <strong>${fmt(total)}</strong> será enviado a tu supervisor para evaluación.</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: 'var(--teal-dark)',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: `<span style="font-family:'Poppins', sans-serif; font-weight:600;">Sí, enviar a revisión</span>`,
                cancelButtonText: `<span style="font-family:'Poppins', sans-serif;">Revisar de nuevo</span>`
            }).then((result) => {
                if (result.isConfirmed) {
                    procesarEnvio('Pendiente', 'En proceso');
                    Swal.fire({
                        title: '<span style="font-family:\'Poppins\', sans-serif;">¡Reembolso Solicitado!</span>',
                        html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">Tu solicitud con ${evidenciasFiles.length} evidencia(s) ha sido enviada.</span>`,
                        icon: 'success',
                        confirmButtonColor: 'var(--teal-dark)',
                        confirmButtonText: '<span style="font-family:\'Poppins\', sans-serif; font-weight:600;">Entendido</span>'
                    });
                }
            });
        }

        /* ── GUARDADO TEMPORAL ── */
        function saveDraft() {
            const total = parseFloat(document.getElementById('sum-total').getAttribute('data-value'));
            const motivo = document.getElementById('modal-motivo').value.trim();

            if (!motivo) {
                showToast('Necesitas al menos un motivo para guardar el borrador.', 'warning');
                return;
            }

            procesarEnvio('Borrador', 'N/A');
            showToast('Borrador guardado exitosamente.', 'success');
        }

        /* ── FUNCIÓN CENTRAL DE PUSH A MOCK DB ── */
        function procesarEnvio(estadoRevision, estadoPago) {
            const total = parseFloat(document.getElementById('sum-total').getAttribute('data-value'));

            requests.unshift({
                id: currentId++,
                folioP: `VES-${String(currentId).padStart(4, '0')}`,
                folioU: 'SFP-005',
                nombre: document.getElementById('modal-nombre').value,
                motivo: document.getElementById('modal-motivo').value.trim(),
                depto: document.getElementById('modal-depto').value || 'N/A',
                amount: total,
                status: estadoRevision,
                pago: estadoPago
            });
            closeModal();
            renderDashboard();
        }

        /* ── INIT ── */
        renderDashboard();
    </script>
@endpush

<style>
    /* =====================================================
               ROOT: VARIABLES DE COLOR Y FUENTE
               ===================================================== */
    :root {
        --slate-dark: #0f172a;
        --slate-mid: #1e293b;
        --slate-light: #f1f5f9;
        --surface: #ffffff;
        --surface-alt: #f8fafc;
        --teal-dark: #0d9488;
        --teal-medium: #14b8a6;
        --teal-light: #ccfbf1;

        --status-pending-bg: #fef9c3;
        --status-pending-text: #92400e;
        --status-pending-border: #fde68a;

        --status-approved-bg: #d1fae5;
        --status-approved-text: #065f46;
        --status-approved-border: #a7f3d0;

        --status-rejected-bg: #fee2e2;
        --status-rejected-text: #991b1b;
        --status-rejected-border: #fecaca;
    }

    /* =====================================================
               BASE
               ===================================================== */
    * {
        font-family: 'Poppins', sans-serif;
    }

    input,
    select,
    button {
        font-family: inherit;
        font-size: inherit;
    }

    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
    }

    .hidden {
        display: none !important;
    }

    .opacity-0 {
        opacity: 0;
    }

    /* ── ALERTAS FLOTANTES SIEMPRE AL FRENTE ── */
    .swal2-container {
        z-index: 100000 !important;
    }

    .view-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .view-title {
        font-size: 2rem;
        font-weight: 400;
        color: #1e293b;
        margin: 0;
    }

    .view-title strong {
        font-weight: 800;
        color: #0f172a;
    }

    .view-subtitle {
        margin-top: .25rem;
        font-size: 0.85rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: .4rem;
    }

    .view-subtitle i {
        color: var(--teal-dark);
        font-size: 1.1rem;
    }

    /* =====================================================
               BOTONES
               ===================================================== */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .7rem 1.5rem;
        border-radius: .75rem;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        border: none;
        transition: background-color .15s, transform .15s, box-shadow .15s;
    }

    .btn-primary {
        background: var(--teal-dark);
        color: #fff;
        box-shadow: 0 4px 14px rgba(13, 148, 136, .25);
    }

    .btn-primary:hover {
        background: var(--teal-medium);
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(20, 184, 166, .3);
    }

    .btn-ghost {
        background: transparent;
        color: #64748b;
    }

    .btn-ghost:hover {
        background: var(--surface-alt);
    }

    .btn-secondary {
        background: var(--surface);
        color: var(--slate-mid);
        border: 1px solid #cbd5e1;
    }

    .btn-secondary:hover {
        background: var(--slate-light);
    }

    .btn-close {
        background: none;
        border: none;
        cursor: pointer;
        padding: .4rem;
        border-radius: 50%;
        color: #94a3b8;
        display: flex;
        align-items: center;
        font-size: 1.35rem;
        transition: background .15s, color .15s;
    }

    .btn-close:hover {
        background: #fee2e2;
        color: #ef4444;
    }

    .btn-ok-solid {
        background: var(--teal-dark);
        color: #fff;
        box-shadow: 0 4px 14px rgba(13, 148, 136, .25);
    }

    .btn-ok-solid:hover {
        background: var(--teal-medium);
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(20, 184, 166, .3);
    }

    .btn-fail-solid {
        background: #ef4444;
        color: #fff;
        box-shadow: 0 4px 14px rgba(239, 68, 68, .25);
    }

    .btn-fail-solid:hover {
        background: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(220, 38, 38, .3);
    }

    /* =====================================================
               TARJETAS MÉTRICAS
               ===================================================== */
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    .metric-card {
        background: var(--surface);
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
        position: relative;
        overflow: hidden;
        transition: transform .15s, box-shadow .15s;
    }

    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, .1);
    }

    .metric-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
    }

    .metric-total::after {
        background: linear-gradient(to right, #0d9488, #14b8a6);
    }

    .metric-pending::after {
        background: linear-gradient(to right, #f59e0b, #fbbf24);
    }

    .metric-approved::after {
        background: linear-gradient(to right, #059669, #34d399);
    }

    .metric-rejected::after {
        background: linear-gradient(to right, #dc2626, #f87171);
    }

    .metric-icon-wrap {
        width: 3rem;
        height: 3rem;
        border-radius: .75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .metric-total .metric-icon-wrap {
        background: #ccfbf1;
        color: #0d9488;
    }

    .metric-pending .metric-icon-wrap {
        background: #fef3c7;
        color: #d97706;
    }

    .metric-approved .metric-icon-wrap {
        background: #d1fae5;
        color: #059669;
    }

    .metric-rejected .metric-icon-wrap {
        background: #fee2e2;
        color: #dc2626;
    }

    .metric-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: .15rem;
        min-width: 0;
    }

    .metric-label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #94a3b8;
    }

    .metric-value {
        font-size: 1.35rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .metric-pill {
        font-size: 0.7rem;
        font-weight: 600;
        color: #64748b;
        background: var(--surface-alt);
        border: 1px solid #e2e8f0;
        padding: .2rem .65rem;
        border-radius: 999px;
        white-space: nowrap;
        align-self: flex-start;
        margin-top: .2rem;
    }

    /* =====================================================
               TARJETAS Y TABLAS (DASHBOARD)
               ===================================================== */
    .card {
        background: var(--surface);
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .card-header {
        padding: 1.1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--surface);
        border-bottom: 1px solid #f1f5f9;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .card-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .card-title i {
        color: #94a3b8;
        font-size: 1.2rem;
    }

    /* ── CONTROLES DE TABLA ── */
    .table-controls {
        display: flex;
        align-items: center;
        gap: .6rem;
        flex-wrap: wrap;
    }

    .search-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-icon {
        position: absolute;
        left: .7rem;
        color: #94a3b8;
        font-size: 1.1rem;
        pointer-events: none;
    }

    .search-input {
        padding: .5rem .75rem .5rem 2.2rem;
        background: var(--surface-alt);
        border: 1px solid #e2e8f0;
        border-radius: .6rem;
        font-size: 0.8rem;
        color: #1e293b;
        font-family: 'Poppins', sans-serif;
        outline: none;
        width: 210px;
        transition: border-color .15s, box-shadow .15s;
    }

    .search-input:focus {
        border-color: var(--teal-dark);
        box-shadow: 0 0 0 2px rgba(13, 148, 136, .15);
        background: #fff;
    }

    .filter-tabs {
        display: flex;
        gap: .25rem;
        background: var(--surface-alt);
        border: 1px solid #e2e8f0;
        border-radius: .6rem;
        padding: .2rem;
    }

    .filter-tab {
        padding: .32rem .8rem;
        border: none;
        border-radius: .4rem;
        font-size: 0.72rem;
        font-weight: 600;
        color: #64748b;
        background: transparent;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
        transition: background .15s, color .15s;
        white-space: nowrap;
    }

    .filter-tab:hover {
        background: #e2e8f0;
        color: #1e293b;
    }

    .filter-tab.active {
        background: var(--slate-dark);
        color: #fff;
    }

    /* ── TABLA ── */
    .table-scroll {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .data-table thead tr {
        background: var(--slate-dark);
        color: #cbd5e1;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .data-table thead th {
        padding: .9rem 1rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .data-table tbody tr {
        border-bottom: 1px solid var(--surface-alt);
        transition: background .12s;
    }

    .data-table tbody tr:hover {
        background: #f8fafc;
    }

    .data-table tbody td {
        padding: .85rem 1rem;
        vertical-align: middle;
    }

    .cell-actions {
        text-align: right;
    }

    .row-motive {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.85rem;
        display: block;
        margin-bottom: 2px;
    }

    .row-name {
        font-weight: 600;
        color: #0f172a;
        font-size: 0.8rem;
    }

    .row-folio {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--teal-dark);
        display: inline-flex;
        align-items: center;
        gap: 2px;
        background: var(--teal-light);
        padding: .2rem .5rem;
        border-radius: .4rem;
    }

    .row-folio.user-folio {
        color: #475569;
        background: #e2e8f0;
    }

    .row-depto {
        font-size: 0.72rem;
        font-weight: 600;
        color: #64748b;
        background: var(--surface);
        border: 1px solid #e2e8f0;
        padding: .28rem .7rem;
        border-radius: .4rem;
        display: inline-block;
    }

    .row-amount-wrap {
        display: flex;
        flex-direction: column;
        gap: .08rem;
    }

    .row-amount {
        font-weight: 700;
        color: #0f172a;
        font-size: 0.88rem;
    }

    .row-amount-label {
        font-size: 0.62rem;
        color: #94a3b8;
        font-weight: 500;
    }

    .row-index {
        font-size: 0.7rem;
        font-weight: 700;
        color: #000000;
        text-align: center;
    }

    /* ── STATUS BADGES REVISIÓN ── */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .32rem .75rem;
        border-radius: .4rem;
        font-size: 0.72rem;
        font-weight: 600;
        border: 1px solid transparent;
    }

    .badge-wait {
        background: var(--status-pending-bg);
        color: var(--status-pending-text);
        border-color: var(--status-pending-border);
    }

    .badge-ok {
        background: var(--status-approved-bg);
        color: var(--status-approved-text);
        border-color: var(--status-approved-border);
    }

    .badge-fail {
        background: var(--status-rejected-bg);
        color: var(--status-rejected-text);
        border-color: var(--status-rejected-border);
    }

    .badge-draft {
        background: #f1f5f9;
        color: #475569;
        border-color: #cbd5e1;
    }

    /* ── STATUS BADGES PAGO (Nuevos Colores) ── */
    .badge-pay-ok {
        background: #dcfce7;
        /* Verde muy suave */
        color: #14532d;
        /* Verde oscuro */
        border-color: #86efac;
    }

    .badge-pay-wait {
        background: #ffedd5;
        /* Naranja suave */
        color: #c2410c;
        /* Naranja oscuro */
        border-color: #fdba74;
    }

    .badge-pay-fail {
        background: #fee2e2;
        /* Rojo suave */
        color: #b91c1c;
        /* Rojo oscuro */
        border-color: #fca5a5;
    }

    .badge-pay-na {
        background: #f8fafc;
        color: #94a3b8;
        border-color: #e2e8f0;
    }

    .actions-wrap {
        display: flex;
        justify-content: flex-end;
        gap: .5rem;
    }

    .btn-icon {
        width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: .5rem;
        border: 1px solid transparent;
        cursor: pointer;
        font-size: 1.05rem;
        transition: all .15s;
    }

    .btn-icon-view {
        color: #64748b;
        background: var(--surface-alt);
        border-color: #cbd5e1;
    }

    .btn-icon-view:hover {
        background: #e2e8f0;
        color: #1e293b;
    }

    .btn-icon-evaluate {
        color: var(--teal-dark);
        background: var(--teal-light);
        border-color: #a7f3d0;
    }

    .btn-icon-evaluate:hover {
        background: var(--teal-dark);
        color: #fff;
    }

    .empty-state {
        padding: 3.5rem 1.5rem;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .5rem;
    }

    .empty-icon {
        font-size: 3.5rem;
        color: #cbd5e1;
    }

    .empty-title {
        font-size: 1rem;
        font-weight: 700;
        color: #64748b;
        margin: 0;
    }

    .empty-desc {
        font-size: 0.8rem;
        color: #94a3b8;
        margin: 0;
    }

    .table-footer {
        padding: .75rem 1.5rem;
        border-top: 1px solid #f1f5f9;
        background: var(--surface-alt);
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .table-count-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: #94a3b8;
    }

    /* =====================================================
               MODAL
               ===================================================== */
    .modal-bg {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(0, 0, 0, 0.55);
        display: flex;
        align-items: flex-start;
        justify-content: center;
        overflow-y: auto;
        padding: 20px;
        backdrop-filter: blur(3px);
    }

    .modal-box {
        background: var(--surface);
        border: 1px solid #334155;
        border-radius: 1.25rem;
        box-shadow: 0 24px 60px rgba(0, 0, 0, .45);
        width: 100%;
        max-width: 82rem;
        max-height: 95vh;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .modal-header {
        padding: 1.25rem 2rem;
        border-bottom: 1px solid var(--surface-alt);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        background: var(--surface);
        z-index: 10;
        border-radius: 1.25rem 1.25rem 0 0;
    }

    .modal-title {
        font-size: 1.6rem;
        font-weight: 400;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: .75rem;
        margin: 0;
    }

    .modal-title i {
        color: var(--teal-dark);
        font-size: 1.8rem;
    }

    .modal-title strong {
        font-weight: 800;
        color: #0f172a;
    }

    .modal-body {
        padding: 2rem;
        overflow-y: auto;
        flex: 1;
        background: rgba(248, 250, 252, .5);
    }

    .modal-footer {
        padding: 1.25rem 2rem;
        border-top: 1px solid #e2e8f0;
        background: var(--slate-light);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .75rem;
        position: sticky;
        bottom: 0;
        z-index: 10;
        border-radius: 0 0 1.25rem 1.25rem;
    }

    .modal-footer-right {
        display: flex;
        gap: .75rem;
    }

    /* =====================================================
               FORMULARIO: NUEVO DISEÑO CLARO (ENTERPRISE GRADE)
               ===================================================== */
    .form-header-card {
        background: var(--surface);
        border: 1px solid #e2e8f0;
        border-radius: .75rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, .03);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    /* BARRA SUPERIOR CLARA (DATOS FIJOS DEL SISTEMA) */
    .fh-info-strip {
        background: var(--surface-alt);
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.25rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .fh-info-item {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }

    .fh-info-item span {
        font-size: 0.68rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
    }

    .fh-info-item strong {
        font-size: 0.95rem;
        color: #0f172a;
        font-weight: 600;
    }

    .fh-info-item.folio strong {
        color: var(--teal-dark);
        font-size: 1.1rem;
        letter-spacing: 0.05em;
    }

    /* CUERPO DEL FORMULARIO */
    .fh-body {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .fh-row-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 2rem;
        align-items: flex-end;
    }

    .fh-grid-4 {
        display: grid;
        grid-template-columns: 1fr 1fr 1.2fr 1.5fr;
        gap: 1.25rem;
    }

    /* TÍTULOS DE CAMPO */
    .input-label {
        display: block;
        font-size: 0.72rem;
        font-weight: 600;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: .4rem;
    }

    /* RADIO BUTTONS ESTILO "PILLS" (PÍLDORAS) */
    .radio-pill-group {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .radio-pill-label {
        padding: 0.45rem 1rem;
        border-radius: 99px;
        background: var(--surface-alt);
        border: 1px solid #cbd5e1;
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        user-select: none;
    }

    .radio-pill-label input[type="radio"] {
        display: none;
    }

    .radio-pill-label:has(input:checked) {
        background: var(--teal-dark);
        border-color: var(--teal-dark);
        color: #fff;
        box-shadow: 0 4px 10px rgba(13, 148, 136, 0.25);
    }

    .radio-pill-label i {
        font-size: 1.05rem;
    }

    /* INPUTS CON ÍCONOS */
    .input-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-group i.field-icon {
        position: absolute;
        left: 0.85rem;
        color: #94a3b8;
        font-size: 1.15rem;
        pointer-events: none;
    }

    .input-field {
        width: 100%;
        padding: .6rem .75rem;
        background: var(--slate-light);
        border: 1px solid #e2e8f0;
        border-radius: .5rem;
        font-size: 0.85rem;
        color: #1e293b;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
    }

    .input-group .input-field {
        padding-left: 2.5rem;
    }

    .input-field:focus {
        border-color: var(--teal-dark);
        box-shadow: 0 0 0 2px rgba(13, 148, 136, .2);
        background: #fff;
    }

    .input-field[readonly] {
        cursor: default;
        color: #64748b;
        background: #e2e8f0;
        border-color: #cbd5e1;
    }


    /* =====================================================
               TABLA DE GASTOS
               ===================================================== */
    .expense-card {
        background: var(--surface);
        border: 1px solid #e2e8f0;
        border-radius: .75rem;
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
    }

    .expense-card-header {
        padding: .85rem 1.25rem;
        background: var(--slate-dark);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .expense-card-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #e2e8f0;
        text-transform: uppercase;
        letter-spacing: .08em;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .expense-card-title i {
        color: var(--teal-medium);
        font-size: 1.2rem;
    }

    .expense-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 950px;
    }

    .expense-table thead tr.th-group {
        background: var(--slate-dark);
        color: #94a3b8;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: .07em;
    }

    .expense-table thead tr.th-group th {
        padding: .6rem .6rem;
        border-right: 1px solid #334155;
        text-align: center;
        font-weight: 600;
    }

    .expense-table thead tr.th-group th.th-importes {
        color: var(--teal-medium);
    }

    .expense-table thead tr.th-cols {
        background: #1e293b;
        color: #cbd5e1;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .expense-table thead tr.th-cols th {
        padding: .7rem .5rem;
        border-right: 1px solid #334155;
        font-weight: 500;
        text-align: center;
        white-space: nowrap;
    }

    .expense-table tbody tr.cat-row td {
        background: var(--teal-dark);
        color: var(--teal-light);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        padding: .5rem .75rem;
        border-bottom: 1px solid #0f766e;
    }

    /* ── CORRECCIÓN DEL MARGEN DEL ÍCONO ── */
    .expense-table tbody tr.cat-row td span i {
        font-size: 1.1rem;
        vertical-align: text-bottom;
        margin-right: 0.4rem;
    }

    .expense-table tbody tr.data-row {
        border-bottom: 1px solid #f1f5f9;
        transition: background .1s;
    }

    .expense-table tbody tr.data-row:hover {
        background: rgba(204, 251, 241, .15);
    }

    .expense-table tbody tr.data-row td {
        padding: .35rem .4rem;
        border-right: 1px solid #f1f5f9;
    }

    .expense-table tbody tr.data-row td:last-child {
        border-right: none;
    }

    .cell-row-total {
        text-align: right;
        font-weight: 700;
        font-size: 0.8rem;
        color: #0f172a;
        background: var(--surface-alt);
        white-space: nowrap;
        padding: .3rem .6rem !important;
    }

    .cell-input {
        width: 100%;
        background: transparent;
        border: 1px solid transparent;
        border-radius: .3rem;
        padding: .4rem .4rem;
        font-size: 0.75rem;
        font-weight: 500;
        color: #374151;
        outline: none;
        transition: border-color .1s, background .1s;
    }

    .cell-input:focus {
        border-color: var(--teal-dark);
        background: #fff;
        box-shadow: 0 0 0 2px rgba(13, 148, 136, .15);
    }

    .cell-input.num {
        text-align: right;
    }

    .cell-input.date-in {
        padding-left: 2.2rem;
        cursor: pointer;
        width: 140px;
        font-weight: 700;
        color: var(--teal-dark);
        font-size: 0.8rem;
    }

    .date-wrap {
        position: relative;
    }

    .date-wrap i {
        position: absolute;
        left: .6rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--teal-medium);
        font-size: 1.1rem;
        pointer-events: none;
    }

    /* Botón de Agregar Fila */
    .btn-add-row {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--teal-light);
        color: var(--teal-dark);
        border: none;
        border-radius: .3rem;
        width: 1.8rem;
        height: 1.8rem;
        cursor: pointer;
        font-size: 1.2rem;
        transition: all .15s;
        padding: 0;
    }

    .btn-add-row:hover {
        background: #fff;
        transform: scale(1.05);
    }

    /* Botón de Quitar Fila */
    .btn-remove-row {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: none;
        border: none;
        color: #ef4444;
        cursor: pointer;
        border-radius: .3rem;
        width: 1.8rem;
        height: 1.8rem;
        font-size: 1.2rem;
        transition: all .15s;
        padding: 0;
    }

    .btn-remove-row:hover {
        background: #fee2e2;
        color: #dc2626;
    }

    /* =====================================================
               ZONA INFERIOR: EVIDENCIA Y RESUMEN
               ===================================================== */
    .bottom-section {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 2rem;
        margin-bottom: 1.5rem;
    }

    .evidence-panel {
        flex: 1;
        background: var(--surface);
        border: 2px dashed #cbd5e1;
        border-radius: .75rem;
        padding: 2rem 1.5rem;
        text-align: center;
        transition: background .2s, border-color .2s;
        cursor: pointer;
    }

    .evidence-panel.dragover {
        border-color: var(--teal-medium);
        background: rgba(20, 184, 166, 0.08);
    }

    .evidence-panel:hover {
        border-color: var(--teal-medium);
        background: rgba(20, 184, 166, 0.03);
    }

    .evidence-icon {
        font-size: 3rem;
        color: #ef4444;
        margin-bottom: .5rem;
    }

    .evidence-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: .25rem;
    }

    .evidence-desc {
        font-size: .8rem;
        color: #64748b;
        margin-bottom: 1rem;
    }

    .file-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: .75rem;
        margin-top: 1.5rem;
    }

    .file-card {
        background: var(--surface-alt);
        border: 1px solid #e2e8f0;
        border-radius: .5rem;
        padding: .6rem;
        display: flex;
        align-items: center;
        gap: .5rem;
        text-align: left;
        position: relative;
        cursor: default;
    }

    .file-icon-lg {
        font-size: 2rem;
        color: #ef4444;
    }

    .file-info {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
    }

    .file-name {
        font-size: 0.75rem;
        font-weight: 600;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .file-size {
        font-size: 0.65rem;
        color: #64748b;
        margin-top: .15rem;
    }

    .btn-remove-file {
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: .2rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .15s;
        font-size: 1.1rem;
    }

    .btn-remove-file:hover {
        background: #fee2e2;
        color: #ef4444;
    }

    .summary-box {
        width: 100%;
        max-width: 26rem;
        background: var(--slate-dark);
        border: 1px solid #1e293b;
        border-radius: .75rem;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(0, 0, 0, .3);
        position: relative;
    }

    .summary-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(to right, var(--teal-medium), var(--teal-dark));
    }

    .summary-head {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #1e293b;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .summary-head i {
        color: var(--teal-medium);
        font-size: 1.3rem;
    }

    .summary-head span {
        font-size: 0.75rem;
        font-weight: 600;
        color: #e2e8f0;
        text-transform: uppercase;
        letter-spacing: .1em;
    }

    .summary-body {
        padding: 1rem 1.25rem;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.85rem;
        margin-bottom: .75rem;
    }

    .sum-lbl {
        color: #94a3b8;
    }

    .sum-val {
        color: #e2e8f0;
        font-weight: 500;
    }

    .sum-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: .85rem;
        border-top: 1px solid #334155;
        margin-top: .25rem;
    }

    .sum-total-lbl {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--teal-medium);
    }

    .sum-total-val {
        font-size: 1.6rem;
        font-weight: 800;
        color: #fff;
        letter-spacing: -.02em;
    }

    .spinner {
        width: 1.25rem;
        height: 1.25rem;
        border: 2px solid rgba(255, 255, 255, .25);
        border-top-color: var(--teal-dark);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        display: inline-block;
    }

    @keyframes spin {
        100% {
            transform: rotate(360deg);
        }
    }

    /* =====================================================
               RESPONSIVE
               ===================================================== */
    @media (max-width: 900px) {
        .metrics-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .fh-row-pills {
            flex-direction: column;
            align-items: flex-start;
            gap: 1.5rem;
        }

        .fh-grid-4 {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .view-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .bottom-section {
            flex-direction: column-reverse;
        }

        .summary-box {
            max-width: 100%;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .radio-pill-group {
            flex-direction: column;
            align-items: flex-start;
        }

        .radio-pill-label {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 560px) {
        .metrics-grid {
            grid-template-columns: 1fr;
        }

        .table-controls {
            flex-direction: column;
            align-items: flex-start;
        }

        .search-input {
            width: 100%;
        }
    }
</style>
