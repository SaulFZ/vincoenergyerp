{{-- ════════════════════════════════════════════════════════════════════════════
     VISTA BLADE: PANEL DE ANTICIPOS (advances.blade.php)
     VesCore - Autorización y Gestión de Fondos Operativos
     ════════════════════════════════════════════════════════════════════════════ --}}
@extends('modules.administration.expense-claims.index')


@section('content')
    <div class="advances-container">

        {{-- ── DASHBOARD DE MÉTRICAS GLOBALES ── --}}
        <div class="adv-metrics-grid">
            <div class="adv-metric-card adv-metric-pending">
                <div class="adv-metric-icon-wrap"><i class="bx bx-hourglass"></i></div>
                <div class="adv-metric-info">
                    <span class="adv-metric-label">Solicitudes Pendientes</span>
                    <span id="metric-pending-val" class="adv-metric-value">0</span>
                </div>
                <div class="adv-metric-pill" id="metric-pending-amount">$0.00</div>
            </div>
            <div class="adv-metric-card adv-metric-approved">
                <div class="adv-metric-icon-wrap"><i class="bx bx-check-shield"></i></div>
                <div class="adv-metric-info">
                    <span class="adv-metric-label">Anticipos Aprobados</span>
                    <span id="metric-approved-val" class="adv-metric-value">0</span>
                </div>
                <div class="adv-metric-pill" id="metric-approved-amount">$0.00</div>
            </div>
            <div class="adv-metric-card adv-metric-total">
                <div class="adv-metric-icon-wrap"><i class="bx bx-wallet"></i></div>
                <div class="adv-metric-info">
                    <span class="adv-metric-label">Capital Entregado (Vivo)</span>
                    <span id="metric-delivered-val" class="adv-metric-value">0</span>
                </div>
                <div class="adv-metric-pill" id="metric-delivered-amount">$0.00</div>
            </div>
        </div>

        {{-- ── TABLA MAESTRA: HISTORIAL DE ANTICIPOS ── --}}
        <div class="adv-card">
            <div class="adv-card-header">
                <div class="adv-card-title">
                    <i class="bx bx-history"></i> Autorización de Anticipos Operativos
                </div>
                <div class="adv-table-controls">
                    <div class="adv-search-wrap">
                        <i class="bx bx-search adv-search-icon"></i>
                        <input type="text" id="adv-table-search" class="adv-search-input" placeholder="Buscar por folio, nombre o motivo...">
                    </div>
                    <div class="adv-filter-tabs" id="adv-filter-tabs">
                        <button class="adv-filter-tab active" data-filter="all">Todos</button>
                        <button class="adv-filter-tab" data-filter="Pendiente">Pendientes</button>
                        <button class="adv-filter-tab" data-filter="Aprobado">Aprobados</button>
                        <button class="adv-filter-tab" data-filter="Entregado">Entregados</button>
                        <button class="adv-filter-tab" data-filter="Comprobado">Comprobados</button>
                    </div>
                </div>
            </div>

            <div class="adv-table-scroll">
                <table class="adv-data-table" id="advances-data-table">
                    <thead>
                        <tr>
                            <th>Folio Sistema</th>
                            <th>Solicitante</th>
                            <th>Departamento</th>
                            <th class="text-center">Fecha Creación</th>
                            <th>Tipo de Anticipo</th>
                            <th>Motivo / Justificación</th>
                            <th class="text-right">Monto (MXN)</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="advances-list">
                        {{-- Inyección JS --}}
                    </tbody>
                </table>
            </div>

            {{-- ESTADO VACÍO --}}
            <div id="adv-empty-state" class="adv-empty-state hidden">
                <div class="adv-empty-content">
                    <i class="bx bx-folder-open adv-empty-icon"></i>
                    <p class="adv-empty-title">Sin resultados encontrados</p>
                    <p class="adv-empty-desc">No existen registros de anticipos que coincidan con los filtros aplicados.</p>
                </div>
            </div>

            {{-- FOOTER Y PAGINACIÓN --}}
            <div class="adv-table-footer">
                <div class="adv-table-footer-left">
                    <span id="adv-table-count" class="adv-table-count-label">0 registros listados</span>
                </div>
                <div id="adv-pagination-controls" class="adv-pagination-controls adv-table-footer-center"></div>
                <div class="adv-table-footer-right">
                    <div class="adv-page-size-wrap">
                        <span>Mostrar:</span>
                        <select id="adv-page-size-select" class="adv-page-size-select" onchange="changePageSize()">
                            <option value="7" selected>7</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="all">Todos</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ════════════════════════════════════════════════════════════════════════
         MODAL: INSPECCIONAR / DICTAMINAR ANTICIPO (TIPO TARJETA DE LECTURA)
         ════════════════════════════════════════════════════════════════════════ --}}
    <div id="advance-modal" class="modal-bg hidden" aria-hidden="true" role="dialog">
        <div class="modal-box adv-modal-box">
            <div class="adv-modal-header">
                <h2 class="adv-modal-title" id="adv-modal-title">
                    <i class="bx bx-search-alt"></i> Inspección de <strong>Anticipo</strong>
                </h2>
                <div class="modal-header-actions">
                    <button class="adv-btn-close" onclick="closeAdvanceModal()" aria-label="Cerrar ventana"><i class="bx bx-x"></i></button>
                </div>
            </div>

            <div class="adv-modal-body">

                {{-- Tira de Información Principal --}}
                <div class="adv-info-strip">
                    <div>
                        <span class="adv-info-label">Folio Operativo</span>
                        <strong id="adv-modal-folio" class="adv-info-val-primary">...</strong>
                    </div>
                    <div class="text-right">
                        <span class="adv-info-label">Estado Actual</span>
                        <span id="adv-modal-status" class="adv-status-badge">...</span>
                    </div>
                </div>

                {{-- Reemplazo de Inputs por Cajas de Lectura (Divs) --}}
                <div class="adv-grid-2">
                    <div class="adv-readonly-box">
                        <span class="adv-readonly-label">Colaborador Solicitante</span>
                        <div class="adv-readonly-content">
                            <i class="bx bx-user"></i>
                            <span id="adv-user-name" class="adv-readonly-value">Cargando...</span>
                        </div>
                    </div>
                    <div class="adv-readonly-box">
                        <span class="adv-readonly-label">Fecha de Creación</span>
                        <div class="adv-readonly-content">
                            <i class="bx bx-calendar"></i>
                            <span id="adv-date-text" class="adv-readonly-value">--/--/----</span>
                        </div>
                    </div>
                </div>

                <div class="adv-grid-2">
                    <div class="adv-readonly-box">
                        <span class="adv-readonly-label">Clasificación</span>
                        <div class="adv-readonly-content">
                            <i class="bx bx-briefcase"></i>
                            <span id="adv-type" class="adv-readonly-value">...</span>
                        </div>
                    </div>
                    <div class="adv-readonly-box">
                        <span class="adv-readonly-label">Monto Aprobado / Solicitado</span>
                        <div class="adv-readonly-content">
                            <i class="bx bx-dollar-circle"></i>
                            <span id="adv-amount" class="adv-readonly-value adv-readonly-amount">0.00</span>
                        </div>
                    </div>
                </div>

                <div>
                    <span class="adv-readonly-label" style="margin-bottom: 0.5rem; display:block;">Justificación Operativa / Motivo</span>
                    <div id="adv-desc" class="adv-readonly-desc">
                        Cargando información descriptiva...
                    </div>
                </div>

            </div>

            <div class="adv-modal-footer">
                <span id="adv-modal-note" class="adv-footer-note">
                    <i class="bx bx-shield-quarter"></i> Panel de lectura y auditoría interna.
                </span>

                {{-- BOTONES DE SOLO LECTURA --}}
                <div id="adv-footer-view" class="adv-footer-actions hidden">
                    <button type="button" class="btn btn-secondary" onclick="closeAdvanceModal()" style="padding: 0.6rem 1.5rem; border-radius: 0.5rem; font-weight: 700;">Cerrar Consulta</button>
                </div>

                {{-- BOTONES PARA DICTAMINAR --}}
                <div id="adv-footer-evaluate" class="adv-footer-actions hidden">
                    <button type="button" class="btn btn-secondary" onclick="closeAdvanceModal()" style="padding: 0.6rem 1.2rem; border-radius: 0.5rem; font-weight: 700;">Cancelar</button>
                    <button type="button" class="btn" style="background:#ef4444; color:#fff; padding: 0.6rem 1.2rem; border-radius: 0.5rem; font-weight: 700; box-shadow: 0 4px 6px rgba(239, 68, 68, 0.2);" onclick="processAdvanceEvaluation('Rechazado')"><i class="bx bx-x"></i> Rechazar</button>
                    <button type="button" class="btn" style="background:var(--primary-dark); color:#fff; padding: 0.6rem 1.5rem; border-radius: 0.5rem; font-weight: 700; box-shadow: 0 4px 6px rgba(21, 40, 69, 0.2);" onclick="processAdvanceEvaluation('Aprobado')"><i class="bx bx-check-double"></i> Aprobar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const advancesData = {!! json_encode($advances) !!};
        let currentPage = 1;
        let itemsPerPage = 7; // 👈 Paginación ajustada a 7 por defecto
        let searchQuery = '';
        let activeFilter = 'all';
        let currentEvaluateAdvId = null;

        const fmt = n => new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n);

        function getStatusConfig(status) {
            const map = {
                'Pendiente': { class: 'badge-wait', icon: 'bx bx-hourglass', label: 'Pendiente' },
                'Aprobado': { class: 'badge-ok', icon: 'bx bx-check-shield', label: 'Aprobado' },
                'Rechazado': { class: 'badge-fail', icon: 'bx bx-x-circle', label: 'Rechazado' },
                'Entregado': { class: 'badge-process', icon: 'bx bx-wallet', label: 'Entregado' },
                'Comprobado': { class: 'badge-paid', icon: 'bx bx-check-double', label: 'Comprobado' }
            };
            return map[status] || { class: 'badge-disabled', icon: 'bx bx-minus', label: status };
        }

        function initDashboard() {
            let pCount = 0, pMonto = 0;
            let aCount = 0, aMonto = 0;
            let eCount = 0, eMonto = 0;

            advancesData.forEach(adv => {
                if (adv.status === 'Pendiente') { pCount++; pMonto += adv.monto; }
                if (adv.status === 'Aprobado') { aCount++; aMonto += adv.monto; }
                if (adv.status === 'Entregado') { eCount++; eMonto += adv.monto; }
            });

            document.getElementById('metric-pending-val').textContent = pCount;
            document.getElementById('metric-pending-amount').textContent = fmt(pMonto);

            document.getElementById('metric-approved-val').textContent = aCount;
            document.getElementById('metric-approved-amount').textContent = fmt(aMonto);

            document.getElementById('metric-delivered-val').textContent = eCount;
            document.getElementById('metric-delivered-amount').textContent = fmt(eMonto);
        }

        document.getElementById('adv-table-search').addEventListener('input', function() {
            searchQuery = this.value.toLowerCase().trim();
            currentPage = 1;
            renderTable();
        });

        document.querySelectorAll('.adv-filter-tab').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.adv-filter-tab').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                activeFilter = this.dataset.filter;
                currentPage = 1;
                renderTable();
            });
        });

        function changePageSize() {
            const val = document.getElementById('adv-page-size-select').value;
            itemsPerPage = val === 'all' ? 999999 : parseInt(val);
            currentPage = 1;
            renderTable();
        }

        function renderTable() {
            const list = document.getElementById('advances-list');
            const emptyState = document.getElementById('adv-empty-state');
            list.innerHTML = '';

            let filtered = advancesData.filter(adv => {
                const matchSearch = !searchQuery ||
                                    adv.folio.toLowerCase().includes(searchQuery) ||
                                    adv.nombre.toLowerCase().includes(searchQuery) ||
                                    adv.motivo.toLowerCase().includes(searchQuery);
                const matchFilter = activeFilter === 'all' || adv.status === activeFilter;
                return matchSearch && matchFilter;
            });

            document.getElementById('adv-table-count').textContent = `${filtered.length} registros listados`;

            if (filtered.length === 0) {
                emptyState.classList.remove('hidden');
                document.getElementById('adv-pagination-controls').innerHTML = '';
                return;
            }
            emptyState.classList.add('hidden');

            const totalPages = Math.ceil(filtered.length / itemsPerPage);
            if (currentPage > totalPages) currentPage = totalPages;
            const startIdx = (currentPage - 1) * itemsPerPage;
            const paginatedData = filtered.slice(startIdx, startIdx + itemsPerPage);

            const getTypeIcon = (type) => {
                if (type === 'Viaticos') return 'bx bx-plane';
                if (type === 'Operativos') return 'bx bx-hard-hat';
                if (type === 'Caja Chica') return 'bx bx-wallet';
                return 'bx bx-briefcase';
            };

            paginatedData.forEach((adv, index) => {
                const st = getStatusConfig(adv.status);
                let shortMotive = adv.motivo.length > 40 ? adv.motivo.substring(0, 40) + '...' : adv.motivo;

                // Botón de Evaluación (Solo si está pendiente)
                const evaluateBtn = adv.status === 'Pendiente' ?
                    `<button class="btn-icon-view" onclick="evaluarAnticipo(${adv.id})" title="Dictaminar Anticipo" style="background:#e0e7ff; color:#4f46e5; border-color:#c7d2fe;"><i class="bx bx-check-shield"></i></button>` : '';

                let html = `
                <tr class="adv-row-enter" style="animation-delay: ${index * 0.04}s;">
                    <td><strong class="adv-text-primary adv-font-mono">${adv.folio}</strong></td>
                    <td><strong class="adv-fw-700">${adv.nombre}</strong></td>
                    <td>${adv.depto}</td>
                    <td class="text-center">${adv.fecha}</td>
                    <td><i class="${getTypeIcon(adv.tipo)} adv-icon-muted"></i> ${adv.tipo}</td>
                    <td title="${adv.motivo}">${shortMotive}</td>
                    <td class="text-right adv-font-mono adv-fw-700">${fmt(adv.monto)}</td>
                    <td class="text-center">
                        <span class="adv-status-badge ${st.class}"><i class="${st.icon}"></i> ${st.label}</span>
                    </td>
                    <td class="text-center">
                        <div style="display:flex; justify-content:center; gap:0.4rem;">
                            <button class="btn-icon-view" onclick="verDetalles(${adv.id})" title="Inspeccionar Solicitud"><i class="bx bx-show"></i></button>
                            ${evaluateBtn}
                        </div>
                    </td>
                </tr>`;
                list.innerHTML += html;
            });

            renderPagination(totalPages);
        }

        function renderPagination(totalPages) {
            const container = document.getElementById('adv-pagination-controls');
            container.innerHTML = '';
            if (totalPages <= 1) return;

            const btnPrev = document.createElement('button');
            btnPrev.className = 'adv-page-btn';
            btnPrev.innerHTML = '<i class="bx bx-chevron-left"></i>';
            btnPrev.disabled = currentPage === 1;
            btnPrev.onclick = () => { currentPage--; renderTable(); };
            container.appendChild(btnPrev);

            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.className = `adv-page-btn ${i === currentPage ? 'adv-page-btn-active' : ''}`;
                btn.textContent = i;
                btn.onclick = () => { currentPage = i; renderTable(); };
                container.appendChild(btn);
            }

            const btnNext = document.createElement('button');
            btnNext.className = 'adv-page-btn';
            btnNext.innerHTML = '<i class="bx bx-chevron-right"></i>';
            btnNext.disabled = currentPage === totalPages;
            btnNext.onclick = () => { currentPage++; renderTable(); };
            container.appendChild(btnNext);
        }

        /* ── LÓGICA DE APERTURA DEL MODAL (AHORA INYECTA EN DIVS) ── */
        async function fetchAndPopulateAdvance(id) {
            Swal.fire({ title: 'Cargando Detalles...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const response = await fetch(`{{ url('administration/expense-claims/advances') }}/${id}`);
                const res = await response.json();

                if (res.success) {
                    const adv = res.data;
                    const st = getStatusConfig(adv.status);

                    // Reemplazamos .value por .textContent ya que ahora son divs/spans
                    document.getElementById('adv-modal-folio').textContent = adv.folio_system;

                    // Inyectamos el badge en lugar de solo texto plano para mejor UI
                    document.getElementById('adv-modal-status').className = `adv-status-badge ${st.class}`;
                    document.getElementById('adv-modal-status').innerHTML = `<i class="${st.icon}"></i> ${st.label}`;

                    document.getElementById('adv-user-name').textContent = adv.user.name;

                    // CORRECCIÓN DE FECHA
                    const cleanDate = adv.advance_date ? adv.advance_date.split('T')[0] : '';
                    if (cleanDate) {
                        const [y, m, d] = cleanDate.split('-');
                        document.getElementById('adv-date-text').textContent = `${d}/${m}/${y}`;
                    } else {
                        document.getElementById('adv-date-text').textContent = adv.advance_date;
                    }

                    document.getElementById('adv-type').textContent = adv.advance_type;
                    document.getElementById('adv-amount').textContent = fmt(adv.amount);
                    document.getElementById('adv-desc').textContent = adv.description;

                    Swal.close();
                    return adv;
                }
            } catch (error) {
                Swal.fire('Error', 'No se pudo cargar la información.', 'error');
            }
            return null;
        }

        async function verDetalles(id) {
            const adv = await fetchAndPopulateAdvance(id);
            if (!adv) return;

            document.getElementById('adv-modal-title').innerHTML = `<i class="bx bx-search-alt"></i> Inspección de <strong>Anticipo</strong>`;

            document.getElementById('adv-footer-evaluate').classList.add('hidden');
            document.getElementById('adv-footer-view').classList.remove('hidden');
            document.getElementById('advance-modal').classList.remove('hidden');
        }

        async function evaluarAnticipo(id) {
            const adv = await fetchAndPopulateAdvance(id);
            if (!adv) return;

            currentEvaluateAdvId = id;
            document.getElementById('adv-modal-title').innerHTML = `<i class="bx bx-check-shield"></i> Dictamen de <strong>Anticipo</strong>`;

            document.getElementById('adv-footer-view').classList.add('hidden');
            document.getElementById('adv-footer-evaluate').classList.remove('hidden');
            document.getElementById('advance-modal').classList.remove('hidden');
        }

        function closeAdvanceModal() {
            document.getElementById('advance-modal').classList.add('hidden');
            currentEvaluateAdvId = null;
        }

        /* ── LÓGICA DE APROBACIÓN / RECHAZO ── */
        function processAdvanceEvaluation(status) {
            if (!currentEvaluateAdvId) return;

            let actionText = status === 'Aprobado' ? 'Aprobar Definitivamente este Anticipo' : 'Denegar y Rechazar el Anticipo';
            let confirmColor = status === 'Aprobado' ? '#16a34a' : '#dc2626';

            Swal.fire({
                title: `<span style="font-family:'Poppins', sans-serif;">¿Confirmar Dictamen?</span>`,
                html: `<span style="font-family:'Poppins', sans-serif; color:#64748b;">¿Estás seguro de que deseas <strong>${actionText}</strong>?</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#94a3b8',
                confirmButtonText: `Sí, confirmar`,
                cancelButtonText: `Cancelar`
            }).then((result) => {
                if (result.isConfirmed) {
                    updateAdvanceStatus(currentEvaluateAdvId, status);
                }
            });
        }

        async function updateAdvanceStatus(id, status) {
            try {
                Swal.fire({ title: 'Actualizando estado...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                let formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('new_status', status);

                const response = await fetch(`{{ url('administration/expense-claims/advances') }}/${id}/status`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire('¡Listo!', data.message, 'success');
                    closeAdvanceModal();
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Hubo un error al intentar guardar los cambios. Intenta de nuevo.', 'error');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            initDashboard();
            renderTable();
        });
    </script>
@endpush
