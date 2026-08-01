@extends('modules.administration.expense-claims.index')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/modules/administration/expense-claims/accounts-payable.css') }}">
@endsection

@section('content')
    <div class="accounts-payable-container">

        {{-- ── DASHBOARD DE MÉTRICAS GLOBALES ── --}}
        <div class="ap-metrics-grid">
            <div class="ap-metric-card ap-metric-pending">
                <div class="ap-metric-icon-wrap"><i class="bx bx-hourglass"></i></div>
                <div class="ap-metric-info">
                    <span class="ap-metric-label">Pendientes</span>
                    <span id="ap-metric-pending-val" class="ap-metric-value">0</span>
                </div>
                <div class="ap-metric-pill">Esperando Dictamen</div>
            </div>
            <div class="ap-metric-card ap-metric-approved">
                <div class="ap-metric-icon-wrap"><i class="bx bx-check-shield"></i></div>
                <div class="ap-metric-info">
                    <span class="ap-metric-label">Aprobados</span>
                    <span id="ap-metric-approved-val" class="ap-metric-value">0</span>
                </div>
                <div class="ap-metric-pill">Por Pagar</div>
            </div>
            <div class="ap-metric-card ap-metric-total">
                <div class="ap-metric-icon-wrap"><i class="bx bx-wallet"></i></div>
                <div class="ap-metric-info">
                    <span class="ap-metric-label">En Tránsito</span>
                    <span id="ap-metric-transit-val" class="ap-metric-value">$0.00</span>
                </div>
                <div class="ap-metric-pill">Deuda Global</div>
            </div>
        </div>

        {{-- ── TABLA MAESTRA AGRUPADA POR COLABORADOR ── --}}
        <div class="ap-card">
            <div class="ap-card-header">
                <div class="ap-card-title">
                    <i class="bx bx-group"></i> Cuentas por Pagar
                    <span class="ap-badge-count" id="ap-total-count">0</span>
                </div>
                <div class="ap-table-controls">
                    <div class="ap-search-wrap">
                        <i class="bx bx-search ap-search-icon"></i>
                        <input type="text" id="ap-table-search" class="ap-search-input" placeholder="Buscar colaborador o departamento...">
                    </div>
                    <div class="ap-filter-tabs" id="ap-filter-tabs">
                        <button class="ap-filter-tab active" data-filter="all">Todos</button>
                        <button class="ap-filter-tab" data-filter="Pendiente">Pendientes</button>
                        <button class="ap-filter-tab" data-filter="Aprobado">Aprobados</button>
                        <button class="ap-filter-tab" data-filter="Entregado">Entregados</button>
                        <button class="ap-filter-tab" data-filter="Comprobado">Comprobados</button>
                    </div>
                </div>
            </div>

            <div class="ap-table-scroll">
                <table class="ap-data-table ap-table-animated" id="accounts-payable-data-table">
                    <thead>
                        <tr>
                            <th class="ap-th-w-42"></th>
                            <th>Colaborador / Titular</th>
                            <th>Departamento</th>
                            <th class="text-center">Trámites Activos</th>
                            <th class="text-right">Monto Histórico</th>
                            <th class="text-right">Deuda Actual</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="ap-advances-list">
                        {{-- Inyección JS (Acordeones por Empleado) --}}
                    </tbody>
                </table>
            </div>

            {{-- ESTADO VACÍO --}}
            <div id="ap-empty-state" class="ap-empty-state hidden">
                <div class="ap-empty-content">
                    <i class="bx bx-folder-open ap-empty-icon"></i>
                    <p class="ap-empty-title">Sin resultados encontrados</p>
                    <p class="ap-empty-desc">No existen colaboradores que coincidan con la búsqueda actual.</p>
                </div>
            </div>

            {{-- FOOTER Y PAGINACIÓN --}}
            <div class="ap-table-footer">
                <div class="ap-table-footer-left">
                    <span id="ap-table-count" class="ap-table-count-label">0 colaboradores registrados</span>
                </div>
                <div id="ap-pagination-controls" class="ap-pagination-controls ap-table-footer-center"></div>
                <div class="ap-table-footer-right">
                    <div class="ap-page-size-wrap">
                        <span>Mostrar:</span>
                        <select id="ap-page-size-select" class="ap-page-size-select" onchange="changePageSize()">
                            <option value="5" selected>5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="all">Todos</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ── VARIABLES GLOBALES ──
        const rawAdvances = {!! json_encode($advancesData) !!};
        let employeesData = [];
        let currentPage = 1;
        let itemsPerPage = 5;
        let searchQuery = '';
        let activeFilter = 'all';

        let subSearchQuery = '';
        let subTypeFilter = 'all';

        const fmt = n => new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n);

        const getInitials = (name) => {
            if (!name) return 'U';
            const words = name.trim().split(' ');
            if (words.length > 1) {
                return (words[0][0] + words[1][0]).toUpperCase();
            }
            return name.substring(0, 2).toUpperCase();
        };

        const formatDate = (dateStr) => {
            if (!dateStr || dateStr === '—') return '—';
            try {
                const date = new Date(dateStr);
                return date.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: '2-digit' });
            } catch {
                return dateStr;
            }
        };

        function getStatusConfig(status) {
            const map = {
                'Pendiente': { class: 'ap-badge-wait', icon: 'bx bx-hourglass', label: 'Pendiente' },
                'Aprobado': { class: 'ap-badge-ok', icon: 'bx bx-check-shield', label: 'Aprobado' },
                'Rechazado': { class: 'ap-badge-fail', icon: 'bx bx-x-circle', label: 'Rechazado' },
                'Entregado': { class: 'ap-badge-process', icon: 'bx bx-wallet', label: 'Entregado' },
                'Comprobado': { class: 'ap-badge-paid', icon: 'bx bx-check-double', label: 'Comprobado' },
                'Borrador': { class: 'ap-badge-draft', icon: 'bx bx-edit-alt', label: 'Borrador' },
                'Validado': { class: 'ap-badge-review', icon: 'bx bx-list-check', label: 'Validado' }
            };
            return map[status] || { class: 'ap-badge-disabled', icon: 'bx bx-minus', label: status };
        }

        function groupDataByEmployee() {
            const grouped = {};
            let totalTransit = 0, cPend = 0, cApp = 0;

            rawAdvances.forEach(emp => {
                if (emp.movimientos && emp.movimientos.length > 0) {
                    grouped[emp.user_id] = {
                        user_id: emp.user_id,
                        nombre: emp.nombre,
                        depto: emp.depto,
                        total_historico: emp.total_monto || 0,
                        saldo_actual: emp.total_saldo || 0,
                        tramites_activos: 0,
                        records: emp.movimientos || []
                    };

                    emp.movimientos.forEach(mov => {
                        if(mov.status !== 'Comprobado' && mov.status !== 'Rechazado' && mov.status !== 'Borrador') {
                            grouped[emp.user_id].tramites_activos++;
                        }

                        if (mov.status === 'Pendiente') cPend++;
                        if (mov.status === 'Aprobado') cApp++;
                        if (mov.status !== 'Rechazado' && mov.status !== 'Borrador') totalTransit += mov.saldo || 0;
                    });
                }
            });

            document.getElementById('ap-metric-pending-val').textContent = cPend;
            document.getElementById('ap-metric-approved-val').textContent = cApp;
            document.getElementById('ap-metric-transit-val').textContent = fmt(totalTransit);

            employeesData = Object.values(grouped).sort((a, b) => b.saldo_actual - a.saldo_actual);
            document.getElementById('ap-total-count').textContent = employeesData.length;
        }

        document.getElementById('ap-table-search').addEventListener('input', function() {
            searchQuery = this.value.toLowerCase().trim();
            currentPage = 1;
            renderTable();
        });

        document.querySelectorAll('.ap-filter-tab').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.ap-filter-tab').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                activeFilter = this.dataset.filter;
                currentPage = 1;
                renderTable();
            });
        });

        function changePageSize() {
            const val = document.getElementById('ap-page-size-select').value;
            itemsPerPage = val === 'all' ? 999999 : parseInt(val);
            currentPage = 1;
            renderTable();
        }

        function animateRows(tableSelector) {
            const table = document.querySelector(tableSelector);
            if (!table) return;
            const rows = table.querySelectorAll('tbody tr.ap-emp-row');
            rows.forEach((row, index) => {
                const delay = index * 0.04;
                row.style.animationDelay = `${delay}s`;
                row.style.animation = 'none';
                row.offsetHeight;
                row.style.animation = '';
            });
        }

        function toggleAccordion(userId) {
            const detailsRow = document.getElementById(`ap-details-${userId}`);
            const icon = document.getElementById(`ap-icon-toggle-${userId}`);
            const mainRow = document.getElementById(`ap-row-${userId}`);

            document.querySelectorAll('.ap-details-row:not(.hidden)').forEach(row => {
                if (row.id !== `ap-details-${userId}`) {
                    row.classList.add('hidden');
                    const iconId = row.id.replace('ap-details-', 'ap-icon-toggle-');
                    const iconEl = document.getElementById(iconId);
                    const rowId = row.id.replace('ap-details-', 'ap-row-');
                    const rowEl = document.getElementById(rowId);
                    if (iconEl) iconEl.classList.replace('bx-chevron-down', 'bx-chevron-right');
                    if (rowEl) rowEl.classList.remove('ap-row-expanded');
                }
            });

            if (detailsRow.classList.contains('hidden')) {
                detailsRow.classList.remove('hidden');
                mainRow.classList.add('ap-row-expanded');
                icon.classList.replace('bx-chevron-right', 'bx-chevron-down');
                setTimeout(() => {
                    mainRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);
            } else {
                detailsRow.classList.add('hidden');
                mainRow.classList.remove('ap-row-expanded');
                icon.classList.replace('bx-chevron-down', 'bx-chevron-right');
            }
        }

        function filterMovements(movements) {
            let filtered = movements;

            if (subSearchQuery) {
                const query = subSearchQuery.toLowerCase().trim();
                filtered = filtered.filter(mov =>
                    mov.folio?.toLowerCase().includes(query) ||
                    mov.descripcion?.toLowerCase().includes(query) ||
                    mov.tipo?.toLowerCase().includes(query)
                );
            }

            if (subTypeFilter !== 'all') {
                filtered = filtered.filter(mov => mov.tipo === subTypeFilter);
            }

            return filtered;
        }

        function showAdvanceHistory(advanceId, advanceFolio) {
            let advanceData = null;
            employeesData.forEach(emp => {
                emp.records.forEach(rec => {
                    if (rec.id === advanceId && rec.origen === 'anticipo') {
                        advanceData = rec;
                    }
                });
            });

            if (!advanceData) {
                Swal.fire('Error', 'No se pudo encontrar el anticipo.', 'error');
                return;
            }

            let historyHtml = `
            <div style="font-family:'Poppins',sans-serif; text-align:left;">
                <h4 style="color:var(--primary-dark); margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
                    <i class="bx bx-history"></i> Historial del Anticipo: <strong>${advanceFolio}</strong>
                </h4>
                <div style="background:var(--surface-alt); padding:1rem; border-radius:0.5rem; margin-bottom:1rem; display:flex; justify-content:space-between; align-items:center; border:1px solid #e2e8f0;">
                    <div>
                        <div style="font-size:0.7rem; color:var(--secondary-lighter); text-transform:uppercase; font-weight:600;">Monto Original</div>
                        <div style="font-size:1.2rem; font-weight:700; color:var(--secondary-dark);">${fmt(advanceData.monto)}</div>
                    </div>
                    <div>
                        <div style="font-size:0.7rem; color:var(--secondary-lighter); text-transform:uppercase; font-weight:600;">Saldo Actual</div>
                        <div style="font-size:1.2rem; font-weight:700; color:${advanceData.saldo > 0 ? '#dc2626' : '#16a34a'};">${fmt(advanceData.saldo)}</div>
                    </div>
                    <div>
                        <div style="font-size:0.7rem; color:var(--secondary-lighter); text-transform:uppercase; font-weight:600;">Estado</div>
                        <span class="ap-status-badge ${getStatusConfig(advanceData.status).class}" style="font-size:0.8rem;">
                            <i class="${getStatusConfig(advanceData.status).icon}"></i> ${advanceData.status}
                        </span>
                    </div>
                </div>

                <h5 style="color:var(--secondary-dark); font-size:0.9rem; margin-bottom:0.75rem; display:flex; align-items:center; gap:0.5rem;">
                    <i class="bx bx-receipt"></i> Comprobaciones Asociadas (${advanceData.claims ? advanceData.claims.length : 0})
                </h5>

                <div style="max-height:300px; overflow-y:auto;">`;

            if (advanceData.claims && advanceData.claims.length > 0) {
                advanceData.claims.forEach(claim => {
                    let claimStatus = getStatusConfig(claim.status);
                    historyHtml += `
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:0.6rem 1rem; border-bottom:1px solid var(--surface-muted); background:#fff; border-radius:0.25rem; margin-bottom:0.25rem;">
                        <div>
                            <div style="font-weight:600; color:var(--secondary-dark);">${claim.folio}</div>
                            <div style="font-size:0.75rem; color:var(--secondary-lighter);">${claim.fecha} - ${claim.tipo}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:700; color:var(--secondary-dark);">- ${fmt(claim.monto)}</div>
                            <span class="ap-status-badge ${claimStatus.class}" style="font-size:0.65rem;">
                                <i class="${claimStatus.icon}"></i> ${claim.status}
                            </span>
                        </div>
                    </div>`;
                });
            } else {
                historyHtml += `
                <div style="text-align:center; padding:2rem; color:var(--secondary-lighter);">
                    <i class="bx bx-file-blank" style="font-size:2.5rem; display:block; margin-bottom:0.5rem;"></i>
                    Este anticipo no tiene comprobaciones registradas aún.
                </div>`;
            }

            historyHtml += `
                </div>
                <div style="margin-top:1rem; padding-top:0.75rem; border-top:1px solid #e2e8f0; text-align:right; font-size:0.75rem; color:var(--secondary-lighter);">
                    <i class="bx bx-info-circle"></i> Las comprobaciones restan del saldo del anticipo
                </div>
            </div>`;

            Swal.fire({
                title: `<span style="font-family:'Poppins',sans-serif;">Historial del Anticipo</span>`,
                html: historyHtml,
                icon: 'info',
                confirmButtonColor: 'var(--primary-dark)',
                confirmButtonText: `<span style="font-family:'Poppins',sans-serif; font-weight:600;">Cerrar</span>`,
                width: '600px',
                customClass: {
                    popup: 'swal2-popup-custom'
                }
            });
        }

        function renderTable() {
            const list = document.getElementById('ap-advances-list');
            const emptyState = document.getElementById('ap-empty-state');
            list.innerHTML = '';

            let filtered = employeesData.filter(emp => {
                const matchSearch = !searchQuery ||
                    emp.nombre.toLowerCase().includes(searchQuery) ||
                    emp.depto.toLowerCase().includes(searchQuery);

                const matchFilter = activeFilter === 'all' ||
                    emp.records.some(rec => rec.status === activeFilter);

                return matchSearch && matchFilter;
            });

            document.getElementById('ap-table-count').textContent = `${filtered.length} colaborador(es) registrado(s)`;

            if (filtered.length === 0) {
                emptyState.classList.remove('hidden');
                document.getElementById('ap-pagination-controls').innerHTML = '';
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
                if (type === 'Reembolso') return 'bx bx-receipt';
                if (type === 'Comprobacion Electronica') return 'bx bx-file';
                if (type === 'Comprobacion Directa') return 'bx bx-file-blank';
                return 'bx bx-briefcase';
            };

            paginatedData.forEach((emp, index) => {
                let debtClass = emp.saldo_actual > 0 ? 'ap-text-danger' : 'ap-text-success';
                let initials = getInitials(emp.nombre);
                let delay = index * 0.04;

                let html = `
                <tr id="ap-row-${emp.user_id}" class="ap-emp-row" onclick="toggleAccordion(${emp.user_id})" style="animation: apRowIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: ${delay}s; opacity:0;">
                    <td class="text-center">
                        <div class="ap-toggle-wrap">
                            <i class="bx bx-chevron-right ap-toggle-icon" id="ap-icon-toggle-${emp.user_id}"></i>
                        </div>
                    </td>
                    <td>
                        <div class="ap-emp-info">
                            <div class="ap-avatar" title="${emp.nombre}">${initials}</div>
                            <div class="ap-emp-details">
                                <span class="ap-row-name">${emp.nombre}</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="ap-row-depto">${emp.depto}</span></td>
                    <td class="text-center"><span class="ap-pill-count">${emp.tramites_activos}</span></td>
                    <td class="text-right">
                        <div class="ap-row-amount-wrap">
                            <span class="ap-row-amount">${fmt(emp.total_historico)}</span>
                            <span class="ap-row-amount-label">Histórico</span>
                        </div>
                    </td>
                    <td class="text-right">
                        <div class="ap-row-amount-wrap">
                            <span class="ap-row-amount ${debtClass}">${fmt(emp.saldo_actual)}</span>
                            <span class="ap-row-amount-label">Saldo Actual</span>
                        </div>
                    </td>
                    <td class="cell-actions text-center">
                        <div class="ap-actions-wrap" style="justify-content:center;">
                            <button class="ap-btn-icon ap-btn-icon-view" onclick="event.stopPropagation(); Swal.fire('Kardex en Desarrollo', 'Pronto podrás ver el estado de cuenta detallado de ${emp.nombre}.', 'info')" title="Ver Kardex">
                                <i class="bx bx-receipt"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;

                html += `
                <tr id="ap-details-${emp.user_id}" class="ap-details-row hidden">
                    <td colspan="7" class="ap-p-0">
                        <div class="ap-sub-container">
                            <div class="ap-sub-header">
                                <h4 class="ap-sub-title"><i class="bx bx-layer"></i> Desglose de Trámites</h4>
                                <span class="ap-sub-count">${emp.records.length} registros</span>
                            </div>

                            <div class="ap-sub-filters">
                                <div class="ap-sub-search-wrap">
                                    <i class="bx bx-search ap-sub-search-icon"></i>
                                    <input type="text" id="ap-sub-search-${emp.user_id}" class="ap-sub-search-input" placeholder="Buscar por folio, concepto o tipo..." oninput="window.subFilterChange(${emp.user_id})">
                                </div>
                                <div class="ap-sub-filter-wrap">
                                    <i class="bx bx-filter-alt ap-sub-filter-icon"></i>
                                    <select id="ap-sub-type-filter-${emp.user_id}" class="ap-sub-filter-select" onchange="window.subFilterChange(${emp.user_id})">
                                        <option value="all">Todos los tipos</option>
                                        <option value="Reembolso">Reembolso</option>
                                        <option value="Comprobacion Electronica">Comprobación Electrónica</option>
                                        <option value="Comprobacion Directa">Comprobación Directa</option>
                                    </select>
                                </div>
                            </div>

                            <div class="ap-sub-table-wrap">
                                <table class="ap-sub-table" id="ap-sub-table-${emp.user_id}">
                                    <thead>
                                        <tr>
                                            <th class="ap-th-folio">Folio</th>
                                            <th class="ap-th-date">Fecha</th>
                                            <th class="ap-th-concept">Concepto</th>
                                            <th class="ap-th-type">Tipo</th>
                                            <th class="ap-th-origen">Origen</th>
                                            <th class="ap-th-amount text-right">Monto</th>
                                            <th class="ap-th-balance text-right">Saldo</th>
                                            <th class="ap-th-progress text-center">Progreso</th>
                                            <th class="ap-th-status text-center">Estado</th>
                                            <th class="ap-th-actions text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ap-sub-tbody-${emp.user_id}">
                                        ${renderSubRows(emp.records, emp.user_id)}
                                    </tbody>
                                </table>
                            </div>

                            <div class="ap-sub-footer">
                                <div class="ap-sub-summary">
                                    <div class="ap-summary-item">
                                        <span class="ap-summary-label">Total Histórico</span>
                                        <span class="ap-summary-value">${fmt(emp.total_historico)}</span>
                                    </div>
                                    <div class="ap-summary-item">
                                        <span class="ap-summary-label">Saldo Actual</span>
                                        <span class="ap-summary-value ${debtClass}">${fmt(emp.saldo_actual)}</span>
                                    </div>
                                    <div class="ap-summary-item">
                                        <span class="ap-summary-label">Trámites Activos</span>
                                        <span class="ap-summary-value ap-pill-count" style="background:var(--primary-dark);">${emp.tramites_activos}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>`;

                list.innerHTML += html;
            });

            renderPagination(totalPages);
            animateRows('#accounts-payable-data-table');
        }

        function renderSubRows(movements, userId) {
            const searchInput = document.getElementById(`ap-sub-search-${userId}`);
            const typeSelect = document.getElementById(`ap-sub-type-filter-${userId}`);

            if (searchInput) subSearchQuery = searchInput.value;
            if (typeSelect) subTypeFilter = typeSelect.value;

            let filtered = filterMovements(movements);

            if (filtered.length === 0) {
                return `
                <tr>
                    <td colspan="10" class="text-center" style="padding:2rem; color:var(--secondary-lighter);">
                        <i class="bx bx-search-alt" style="font-size:2rem; display:block; margin-bottom:0.5rem;"></i>
                        No se encontraron registros con los filtros aplicados.
                    </td>
                </tr>`;
            }

            const getTypeIcon = (type) => {
                if (type === 'Viaticos') return 'bx bx-plane';
                if (type === 'Operativos') return 'bx bx-hard-hat';
                if (type === 'Caja Chica') return 'bx bx-wallet';
                if (type === 'Reembolso') return 'bx bx-receipt';
                if (type === 'Comprobacion Electronica') return 'bx bx-file';
                if (type === 'Comprobacion Directa') return 'bx bx-file-blank';
                return 'bx bx-briefcase';
            };

            return filtered.map(rec => {
                const statusConfig = getStatusConfig(rec.status);
                const subDebtClass = rec.saldo > 0 ? 'ap-text-danger' : 'ap-text-success';
                const progressPercent = rec.monto > 0 ? Math.round((rec.saldo / rec.monto) * 100) : 0;
                const typeIcon = getTypeIcon(rec.tipo);
                const formattedDate = formatDate(rec.fecha);
                const origenLabel = rec.origen === 'anticipo' ? 'Anticipo' : 'Comprobación';

                let historyBtn = '';
                if (rec.origen === 'anticipo') {
                    historyBtn = `
                    <button class="ap-btn-icon ap-btn-icon-history" onclick="event.stopPropagation(); showAdvanceHistory(${rec.id}, '${rec.folio}')" title="Ver Historial del Anticipo">
                        <i class="bx bx-history"></i>
                    </button>`;
                }

                return `
                <tr class="ap-sub-row">
                    <td class="ap-sub-folio-cell">
                        <span class="ap-sub-folio">${rec.folio}</span>
                    </td>
                    <td class="ap-sub-date-cell">
                        <span class="ap-sub-date">${formattedDate}</span>
                    </td>
                    <td class="ap-sub-concept-cell">
                        <div class="ap-concept-wrapper">
                            <span class="ap-concept-desc">${rec.descripcion || 'Sin descripción'}</span>
                        </div>
                    </td>
                    <td class="ap-sub-type-cell">
                        <span class="ap-type-badge">
                            <i class="${typeIcon}"></i> ${rec.tipo}
                        </span>
                    </td>
                    <td class="ap-sub-origen-cell">
                        <span class="ap-origen-badge ${rec.origen === 'anticipo' ? 'ap-origen-anticipo' : 'ap-origen-comprobacion'}">
                            ${origenLabel}
                        </span>
                    </td>
                    <td class="ap-sub-amount-cell text-right">
                        <span class="ap-amount-value">${fmt(rec.monto)}</span>
                    </td>
                    <td class="ap-sub-balance-cell text-right">
                        <span class="ap-balance-value ${subDebtClass}">${fmt(rec.saldo)}</span>
                        <span class="ap-balance-status">${rec.saldo > 0 ? '' : 'Liquidado'}</span>
                    </td>
                    <td class="ap-sub-progress-cell text-center">
                        <div class="ap-mini-progress">
                            <div class="ap-mini-bar">
                                <div class="ap-mini-fill" style="width: ${100 - progressPercent}%; background: ${rec.saldo > 0 ? '#f59e0b' : '#22c55e'};"></div>
                            </div>
                            <span class="ap-mini-label">${rec.saldo > 0 ? `${progressPercent}%` : '✓'}</span>
                        </div>
                    </td>
                    <td class="ap-sub-status-cell text-center">
                        <span class="ap-status-badge ${statusConfig.class}">
                            <i class="${statusConfig.icon}"></i> ${statusConfig.label}
                        </span>
                    </td>
                    <td class="ap-sub-actions-cell text-center">
                        ${historyBtn}
                    </td>
                </tr>`;
            }).join('');
        }

        window.subFilterChange = function(userId) {
            const tbody = document.getElementById(`ap-sub-tbody-${userId}`);
            const emp = employeesData.find(e => e.user_id === userId);
            if (emp && tbody) {
                const searchInput = document.getElementById(`ap-sub-search-${userId}`);
                const typeSelect = document.getElementById(`ap-sub-type-filter-${userId}`);

                subSearchQuery = searchInput ? searchInput.value : '';
                subTypeFilter = typeSelect ? typeSelect.value : 'all';

                tbody.innerHTML = renderSubRows(emp.records, userId);
            }
        };

        function renderPagination(totalPages) {
            const container = document.getElementById('ap-pagination-controls');
            container.innerHTML = '';
            if (totalPages <= 1) return;

            const btnPrev = document.createElement('button');
            btnPrev.className = 'ap-page-btn';
            btnPrev.innerHTML = '<i class="bx bx-chevron-left"></i>';
            btnPrev.disabled = currentPage === 1;
            btnPrev.onclick = () => { currentPage--; renderTable(); };
            container.appendChild(btnPrev);

            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.className = `ap-page-btn ${i === currentPage ? 'ap-page-btn-active' : ''}`;
                btn.textContent = i;
                btn.onclick = () => { currentPage = i; renderTable(); };
                container.appendChild(btn);
            }

            const btnNext = document.createElement('button');
            btnNext.className = 'ap-page-btn';
            btnNext.innerHTML = '<i class="bx bx-chevron-right"></i>';
            btnNext.disabled = currentPage === totalPages;
            btnNext.onclick = () => { currentPage++; renderTable(); };
            container.appendChild(btnNext);
        }

        document.addEventListener('DOMContentLoaded', () => {
            groupDataByEmployee();
            renderTable();
        });
    </script>
@endpush
