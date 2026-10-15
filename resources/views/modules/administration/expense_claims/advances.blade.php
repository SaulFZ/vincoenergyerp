{{-- ════════════════════════════════════════════════════════════════════════════
     VISTA BLADE: PANEL DE ANTICIPOS (advances.blade.php)
     VesCore - Autorización y Gestión de Fondos Operativos
     ════════════════════════════════════════════════════════════════════════════ --}}
@extends('modules.administration.expense_claims.index')

@push('styles')
    <link href="{{ asset('assets/css/administration/expense_claims/advance.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="advances-container">

        {{-- ── DASHBOARD DE MÉTRICAS GLOBALES ── --}}
        <div class="adv-metrics-grid">
            <div class="adv-metric-card adv-metric-pending">
                <div class="adv-metric-icon-wrap"><i class="bx bx-hourglass"></i></div>
                <div class="adv-metric-info">
                    <span class="adv-metric-label">Solicitudes Pendientes</span>
                    <div class="adv-metric-value-wrap">
                        <span id="metric-pending-val" class="adv-metric-value">0</span>
                        <span id="metric-pending-amount" class="adv-metric-pill">$0.00</span>
                    </div>
                </div>
            </div>
            <div class="adv-metric-card adv-metric-approved">
                <div class="adv-metric-icon-wrap"><i class="bx bx-check-shield"></i></div>
                <div class="adv-metric-info">
                    <span class="adv-metric-label">Anticipos Aprobados</span>
                    <div class="adv-metric-value-wrap">
                        <span id="metric-approved-val" class="adv-metric-value">0</span>
                        <span id="metric-approved-amount" class="adv-metric-pill">$0.00</span>
                    </div>
                </div>
            </div>
            <div class="adv-metric-card adv-metric-rejected">
                <div class="adv-metric-icon-wrap"><i class="bx bx-x-circle"></i></div>
                <div class="adv-metric-info">
                    <span class="adv-metric-label">Anticipos Rechazados</span>
                    <div class="adv-metric-value-wrap">
                        <span id="metric-rejected-val" class="adv-metric-value">0</span>
                        <span id="metric-rejected-amount" class="adv-metric-pill">$0.00</span>
                    </div>
                </div>
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
                        <button class="adv-filter-tab" data-filter="pendiente">Pendientes</button>
                        <button class="adv-filter-tab" data-filter="aprobado">Aprobados</button>
                        <button class="adv-filter-tab" data-filter="rechazado">Rechazados</button>
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
                            <th class="text-center">Autorización</th>
                            <th class="text-center">Comprobación</th>
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
                <i class="bx bx-file-blank adv-empty-icon"></i>
                <p class="adv-empty-title">Sin resultados encontrados</p>
                <p class="adv-empty-desc">No existen registros de anticipos que coincidan con los filtros aplicados.</p>
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
                    <div class="text-center">
                        <span class="adv-info-label">Comprobación</span>
                        <span id="adv-modal-settlement" class="adv-status-badge">...</span>
                    </div>
                    <div class="text-right">
                        <span class="adv-info-label">Autorización</span>
                        <span id="adv-modal-status" class="adv-status-badge">...</span>
                    </div>
                </div>

                {{-- Cajas de Lectura (Divs) --}}
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
                    <span class="adv-readonly-label">Justificación Operativa / Motivo</span>
                    <div id="adv-desc" class="adv-readonly-desc">
                        Cargando información descriptiva...
                    </div>
                </div>

            </div>

            <div class="adv-modal-footer">
                <span id="adv-modal-note" class="adv-footer-note">
                    <i class="bx bx-info-circle"></i> Panel de lectura y auditoría interna.
                </span>

                {{-- BOTONES DE SOLO LECTURA --}}
                <div id="adv-footer-view" class="adv-footer-actions hidden">
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="closeAdvanceModal()">Cerrar Consulta</button>
                </div>

                {{-- BOTONES PARA DICTAMINAR --}}
                <div id="adv-footer-evaluate" class="adv-footer-actions hidden">
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="closeAdvanceModal()">Cancelar</button>
                    <button type="button" class="btn-modal btn-modal-danger" onclick="processAdvanceEvaluation('rechazado')"><i class="bx bx-x"></i> Rechazar</button>
                    <button type="button" class="btn-modal btn-modal-success" onclick="processAdvanceEvaluation('aprobado')"><i class="bx bx-check-double"></i> Aprobar</button>
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
        let itemsPerPage = 5;
        let searchQuery = '';
        let activeFilter = 'all';
        let currentEvaluateAdvId = null;

        const fmt = n => new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n);

        // 👇 Mapeo estricto de Autorización 👇
        function getStatusConfig(status) {
            const map = {
                'pendiente': { class: 'adv-badge-pendiente', icon: 'bx bx-hourglass', label: 'Pendiente' },
                'aprobado':  { class: 'adv-badge-aprobado',  icon: 'bx bx-check-shield', label: 'Aprobado' },
                'rechazado': { class: 'adv-badge-rechazado', icon: 'bx bx-x-circle', label: 'Rechazado' }
            };
            return map[status] || { class: 'adv-badge-disabled', icon: 'bx bx-minus', label: status };
        }

        // 👇 Mapeo estricto de Comprobación 👇
        function getSettlementConfig(status) {
            const map = {
                'no_comprobado': { class: 'adv-badge-no_comprobado', icon: 'bx bx-file-blank', label: 'Sin Comprobar' },
                'comprobando':   { class: 'adv-badge-comprobando',   icon: 'bx bx-refresh',    label: 'Comprobando' },
                'comprobado':    { class: 'adv-badge-comprobado',    icon: 'bx bx-check-double', label: 'Comprobado' }
            };
            return map[status] || { class: 'adv-badge-disabled', icon: 'bx bx-minus', label: status || 'N/A' };
        }

        function initDashboard() {
            let pCount = 0, pMonto = 0;
            let aCount = 0, aMonto = 0;
            let rCount = 0, rMonto = 0;

            advancesData.forEach(adv => {
                if (adv.status === 'pendiente') { pCount++; pMonto += adv.monto; }
                if (adv.status === 'aprobado') { aCount++; aMonto += adv.monto; }
                if (adv.status === 'rechazado') { rCount++; rMonto += adv.monto; }
            });

            document.getElementById('metric-pending-val').textContent = pCount;
            document.getElementById('metric-pending-amount').textContent = fmt(pMonto);

            document.getElementById('metric-approved-val').textContent = aCount;
            document.getElementById('metric-approved-amount').textContent = fmt(aMonto);

            document.getElementById('metric-rejected-val').textContent = rCount;
            document.getElementById('metric-rejected-amount').textContent = fmt(rMonto);
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
                const settleSt = getSettlementConfig(adv.settlement_status);

                let shortName = adv.nombre.length > 15 ? adv.nombre.substring(0, 15) + '...' : adv.nombre;
                let fullMotive = adv.motivo || '';
                let words = fullMotive.split(' ');
                let shortMotive = words.length > 3 ? words.slice(0, 3).join(' ') + '...' : fullMotive;
                const fullMotiveAttr = fullMotive.replace(/"/g, '&quot;').replace(/'/g, '&#39;');

                const evaluateBtn = adv.status === 'pendiente' ?
                    `<button class="btn-icon btn-icon-evaluate" onclick="evaluarAnticipo(${adv.id})" title="Dictaminar Anticipo"><i class="bx bx-clipboard"></i></button>` : '';

                let html = `
                <tr>
                    <td><span class="adv-row-folio">${adv.folio}</span></td>
                    <td><span class="adv-row-name" title="${adv.nombre}">${shortName}</span></td>
                    <td><span class="adv-row-depto">${adv.depto}</span></td>
                    <td class="text-center"><span class="adv-row-date">${adv.fecha}</span></td>
                    <td><i class="${getTypeIcon(adv.tipo)} adv-icon-muted"></i> ${adv.tipo}</td>
                    <td><span class="row-motive" data-full="${fullMotiveAttr}" title="${fullMotive}">${shortMotive}</span></td>
                    <td class="text-right"><span class="adv-row-amount">${fmt(adv.monto)}</span></td>
                    <td class="text-center">
                        <span class="adv-status-badge ${st.class}"><i class="${st.icon}"></i> ${st.label}</span>
                    </td>
                    <td class="text-center">
                        <span class="adv-status-badge ${settleSt.class}"><i class="${settleSt.icon}"></i> ${settleSt.label}</span>
                    </td>
                    <td class="text-center">
                        <div style="display:flex; justify-content:center; gap:0.5rem;">
                            <button class="btn-icon btn-icon-view" onclick="verDetalles(${adv.id})" title="Inspeccionar Solicitud"><i class="bx bx-show"></i></button>
                            ${evaluateBtn}
                        </div>
                    </td>
                </tr>`;
                list.innerHTML += html;
            });

            renderPagination(totalPages);
            animateTableRows('#advances-data-table');
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

            if (left + popRect.width > window.innerWidth - 12) {
                left = window.innerWidth - popRect.width - 12;
            }
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

        async function fetchAndPopulateAdvance(id) {
            Swal.fire({ title: 'Cargando Detalles...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const response = await fetch(`{{ url('administration/expense-claims/advances') }}/${id}`);
                const res = await response.json();

                if (res.success) {
                    const adv = res.data;
                    const st = getStatusConfig(adv.status);
                    const settleSt = getSettlementConfig(adv.settlement_status || 'no_comprobado');

                    document.getElementById('adv-modal-folio').textContent = adv.folio_system;

                    document.getElementById('adv-modal-status').className = `adv-status-badge ${st.class}`;
                    document.getElementById('adv-modal-status').innerHTML = `<i class="${st.icon}"></i> ${st.label}`;

                    document.getElementById('adv-modal-settlement').className = `adv-status-badge ${settleSt.class}`;
                    document.getElementById('adv-modal-settlement').innerHTML = `<i class="${settleSt.icon}"></i> ${settleSt.label}`;

                    document.getElementById('adv-user-name').textContent = adv.user.name;

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

        function processAdvanceEvaluation(status) {
            const evalId = currentEvaluateAdvId;
            if (!evalId) {
                Swal.fire('Error', 'Se perdió la referencia del anticipo. Cierra la ventana y vuelve a intentarlo.', 'error');
                return;
            }

            let actionText = status === 'aprobado' ? 'Aprobar Definitivamente este Anticipo' : 'Denegar y Rechazar el Anticipo';
            let confirmColor = status === 'aprobado' ? '#16a34a' : '#dc2626';

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
                    updateAdvanceStatus(evalId, status);
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
                    Swal.fire({
                        title: `<span style="font-family:'Poppins', sans-serif;">¡Listo!</span>`,
                        html: `<span style="font-family:'Poppins', sans-serif; font-size:14px; color:#64748b;">${data.message}</span>`,
                        icon: 'success',
                        confirmButtonColor: '#2d7d46',
                        confirmButtonText: `<span style="font-family:'Poppins', sans-serif; font-weight:600;">Entendido</span>`
                    });
                    closeAdvanceModal();
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    Swal.fire({
                        title: `<span style="font-family:'Poppins', sans-serif;">Error en la operación</span>`,
                        html: `<span style="font-family:'Poppins', sans-serif; font-size:14px; color:#64748b;">${data.message}</span>`,
                        icon: 'error',
                        confirmButtonColor: '#b91c1c',
                        confirmButtonText: `<span style="font-family:'Poppins', sans-serif; font-weight:600;">Entendido</span>`
                    });
                }
            } catch (error) {
                Swal.fire({
                    title: `<span style="font-family:'Poppins', sans-serif;">Error en la operación</span>`,
                    html: `<span style="font-family:'Poppins', sans-serif; font-size:14px; color:#64748b;">Hubo un error al intentar guardar los cambios. Intenta de nuevo.</span>`,
                    icon: 'error',
                    confirmButtonColor: '#b91c1c',
                    confirmButtonText: `<span style="font-family:'Poppins', sans-serif; font-weight:600;">Entendido</span>`
                });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            initDashboard();
            renderTable();
        });
    </script>
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/administration/expense_claims/advance.js') }}"></script>
@endpush
