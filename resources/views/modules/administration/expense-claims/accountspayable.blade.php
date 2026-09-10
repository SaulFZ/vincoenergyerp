{{-- ════════════════════════════════════════════════════════════════════════════
     VISTA BLADE: PANEL DE CUENTAS POR PAGAR (ESTADO DE CUENTA Y KARDEX)
     ════════════════════════════════════════════════════════════════════════════ --}}
@extends('modules.administration.expense-claims.index')

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
                    <i class="bx bx-group"></i> Cuentas por Pagar (Estado de Cuenta)
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
                            <th class="text-right">Anticipos (Vivo)</th>
                            <th class="text-right">Balance Corriente</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="ap-advances-list">
                        {{-- Inyección JS --}}
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

    {{-- ════════════════════════════════════════════════════════════════════════════
         MODALES NATIVOS (HTML/CSS) - KARDEX, HISTORIAL Y AJUSTE
         ════════════════════════════════════════════════════════════════════════════ --}}

    {{-- 1. Modal Kardex Premium --}}
    <div class="kardex-modal-overlay" id="kardex-modal-overlay">
        <div class="kardex-modal-wrapper">
            <div class="kardex-modal-header">
                <div class="kardex-modal-title-wrap">
                    <i class="bx bx-receipt"></i>
                    <span>Kardex Contable Integral</span>
                </div>
                <button class="kardex-modal-close" onclick="closeKardexModal()" title="Cerrar"><i class="bx bx-x"></i></button>
            </div>
            <div class="kardex-modal-body" id="kardex-modal-body">
                {{-- Contenido dinámico --}}
            </div>
        </div>
    </div>

    {{-- 2. Modal Historial de Anticipo --}}
    <div class="kardex-modal-overlay" id="advance-history-modal-overlay">
        <div class="kardex-modal-wrapper" style="max-width: 700px;">
            <div class="kardex-modal-header">
                <div class="kardex-modal-title-wrap">
                    <i class="bx bx-history"></i>
                    <span>Desglose del Anticipo</span>
                </div>
                <button class="kardex-modal-close" onclick="closeAdvanceHistoryModal()" title="Cerrar"><i class="bx bx-x"></i></button>
            </div>
            <div class="kardex-modal-body" id="advance-history-modal-body">
                {{-- Contenido dinámico --}}
            </div>
        </div>
    </div>

    {{-- 3. Modal de Ajuste Manual en Libro Mayor --}}
    <div class="kardex-modal-overlay" id="ajuste-modal-overlay">
        <div class="kardex-modal-wrapper" style="max-width: 500px;">
            <div class="kardex-modal-header">
                <div class="kardex-modal-title-wrap">
                    <i class="bx bx-slider-alt"></i>
                    <span>Ajuste en Libro Mayor</span>
                </div>
                <button class="kardex-modal-close" onclick="closeAjusteModal()" title="Cerrar"><i class="bx bx-x"></i></button>
            </div>
            <div class="kardex-modal-body">
                <div class="adj-user-banner">
                    <span style="font-size:0.7rem; color:var(--secondary-lighter); text-transform:uppercase; font-weight:700;">Colaborador Titular</span>
                    <div id="adj-user-name" style="font-size:1.1rem; font-weight:800; color:var(--secondary-dark);"></div>
                    <div id="adj-ref-wrap" style="margin-top:0.3rem; display:none;">
                        <span style="font-size:0.7rem; color:var(--secondary-lighter); text-transform:uppercase; font-weight:700;">Folio Vinculante</span>
                        <div id="adj-ref-folio" style="font-family:monospace; font-weight:700; color:var(--primary-dark); font-size:0.9rem;"></div>
                    </div>
                </div>

                <input type="hidden" id="adj-user-id">
                <input type="hidden" id="adj-adv-id">
                <input type="hidden" id="adj-claim-id">

                <div class="adj-form-group">
                    <label class="adj-form-label">Naturaleza del Movimiento</label>
                    <select id="adj-movement-type" class="adj-form-select">
                        <option value="Abono_Retencion">Retención de Sobrante (Abono vía nómina)</option>
                        <option value="Liquidacion_Caja">Liquidación en Efectivo (Devolución a caja)</option>
                        <option value="Cargo_Excedente">Reconocimiento de Excedente (Gasto extra)</option>
                    </select>
                </div>

                <div class="adj-form-group">
                    <label class="adj-form-label">Monto a Afectar (MXN)</label>
                    <input type="number" id="adj-amount" class="adj-form-input" step="0.01" min="0.01" placeholder="Ej. 1500.50">
                </div>

                <div class="adj-form-group">
                    <label class="adj-form-label">Justificación de Auditoría</label>
                    <textarea id="adj-desc" class="adj-form-textarea" placeholder="Redacte el motivo exacto que ampara este ajuste contable (Obligatorio)..."></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:0.8rem; margin-top: 1.5rem;">
                    <button class="btn-secondary" onclick="closeAjusteModal()">Cancelar</button>
                    <button class="btn-primary" onclick="submitAjusteForm()">Registrar Asiento</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ── VARIABLES GLOBALES DIRECTAS DESDE EL CONTROLADOR ──
        const employeesData = {!! json_encode($advancesData) !!};
        let currentPage = 1;
        let itemsPerPage = 5;
        let searchQuery = '';
        let activeFilter = 'all';
        let subSearchQuery = '';
        let subTypeFilter = 'all';

        // ── VARIABLES DE PAGINACIÓN DEL KARDEX ──
        let kardexCurrentPage = 1;
        let kardexItemsPerPage = 7;
        let currentKardexMovs = [];
        let currentKardexEmp = null;

        const fmt = n => new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN'
        }).format(n);

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
                return date.toLocaleDateString('es-MX', {
                    day: '2-digit', month: '2-digit', year: 'numeric'
                });
            } catch {
                return dateStr;
            }
        };

        function getStatusConfig(status) {
            const map = {
                'Pendiente': { class: 'badge-wait', icon: 'bx bx-hourglass', label: 'Pendiente' },
                'Aprobado': { class: 'badge-ok', icon: 'bx bx-check-shield', label: 'Aprobado' },
                'Rechazado': { class: 'badge-fail', icon: 'bx bx-x-circle', label: 'Rechazado' },
                'Entregado': { class: 'badge-process', icon: 'bx bx-wallet', label: 'Entregado' },
                'Comprobado': { class: 'badge-paid', icon: 'bx bx-check-double', label: 'Comprobado' },
                'Borrador': { class: 'badge-draft', icon: 'bx bx-edit-alt', label: 'Borrador' },
                'Validado': { class: 'badge-review', icon: 'bx bx-list-check', label: 'Validado' },
                'Consolidado': { class: 'badge-ok', icon: 'bx bx-archive', label: 'Consolidado' }
            };
            return map[status] || { class: 'badge-disabled', icon: 'bx bx-minus', label: status };
        }

        function initDashboard() {
            let totalTransit = 0, cPend = 0, cApp = 0;

            employeesData.forEach(emp => {
                totalTransit += emp.total_saldo;
                emp.movimientos.forEach(mov => {
                    if (mov.status === 'Pendiente') cPend++;
                    if (mov.status === 'Aprobado' || mov.status === 'Validado') cApp++;
                });
            });

            document.getElementById('ap-metric-pending-val').textContent = cPend;
            document.getElementById('ap-metric-approved-val').textContent = cApp;
            document.getElementById('ap-metric-transit-val').textContent = fmt(totalTransit);
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
                filtered = filtered.filter(mov => mov.origen === subTypeFilter || mov.tipo === subTypeFilter);
            }
            return filtered;
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
                    emp.movimientos.some(mov => mov.status === activeFilter);

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
            if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
            const startIdx = (currentPage - 1) * itemsPerPage;
            const paginatedData = filtered.slice(startIdx, startIdx + itemsPerPage);

            paginatedData.forEach((emp, index) => {
                let initials = getInitials(emp.nombre);
                let delay = index * 0.04;

                let balanceText = '';
                let balanceColorClass = 'ap-text-muted';

                if (emp.saldo_corriente > 0) {
                    balanceText = `+ ${fmt(emp.saldo_corriente)}`;
                    balanceColorClass = 'ap-text-success';
                } else if (emp.saldo_corriente < 0) {
                    balanceText = `${fmt(emp.saldo_corriente)}`;
                    balanceColorClass = 'ap-text-danger';
                } else {
                    balanceText = '$0.00';
                }

                let debtClass = emp.total_saldo > 0 ? 'ap-text-danger' : 'ap-text-success';

                let html = `
                <tr id="ap-row-${emp.user_id}" class="ap-emp-row" onclick="toggleAccordion(${emp.user_id})" style="animation-delay: ${delay}s;">
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
                            <span class="ap-row-amount ${debtClass}">${fmt(emp.total_saldo)}</span>
                        </div>
                    </td>
                    <td class="text-right">
                        <div class="ap-row-amount-wrap">
                            <span class="ap-row-amount ${balanceColorClass}">${balanceText}</span>
                        </div>
                    </td>
                    <td class="cell-actions text-center">
                        <div class="ap-actions-wrap" style="justify-content:center;">
                            <button class="ap-btn-icon ap-btn-icon-view" onclick="event.stopPropagation(); showKardex(${emp.user_id})" title="Ver Kardex del Colaborador">
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
                                <h4 class="ap-sub-title"><i class="bx bx-layer"></i> Desglose Cronológico de Movimientos</h4>
                                <span class="ap-sub-count">${emp.movimientos.length} registros</span>
                            </div>

                            <div class="ap-sub-filters">
                                <div class="ap-sub-search-wrap">
                                    <i class="bx bx-search ap-sub-search-icon"></i>
                                    <input type="text" id="ap-sub-search-${emp.user_id}" class="ap-sub-search-input" placeholder="Buscar por folio o concepto..." oninput="window.subFilterChange(${emp.user_id})">
                                </div>
                                <div class="ap-sub-filter-wrap">
                                    <i class="bx bx-filter-alt ap-sub-filter-icon"></i>
                                    <select id="ap-sub-type-filter-${emp.user_id}" class="ap-sub-filter-select" onchange="window.subFilterChange(${emp.user_id})">
                                        <option value="all">Todos los movimientos</option>
                                        <option value="anticipo">Solo Anticipos</option>
                                        <option value="reembolso">Solo Reembolsos</option>
                                        <option value="comprobacion">Solo Comprobaciones</option>
                                        <option value="ajuste_balance">Solo Ajustes</option>
                                    </select>
                                </div>
                            </div>

                            <div class="ap-sub-table-wrap">
                                <table class="ap-sub-table" id="ap-sub-table-${emp.user_id}">
                                    <thead>
                                        <tr>
                                            <th class="ap-th-folio">Folio</th>
                                            <th class="ap-th-date">Fecha Op.</th>
                                            <th class="ap-th-concept">Concepto / Motivo</th>
                                            <th class="ap-th-type">Estructura</th>
                                            <th class="ap-th-origen text-center">Naturaleza</th>
                                            <th class="ap-th-amount text-right">Monto MXN</th>
                                            <th class="ap-th-balance text-right">Saldo Deuda</th>
                                            <th class="ap-th-progress text-center">Progreso / Pago</th>
                                            <th class="ap-th-status text-center">Estado</th>
                                            <th class="ap-th-actions text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ap-sub-tbody-${emp.user_id}">
                                        ${renderSubRows(emp.movimientos, emp.user_id, emp.nombre)}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>`;

                list.innerHTML += html;
            });

            renderPagination(totalPages);
            animateRows('#accounts-payable-data-table');
        }

        function renderSubRows(movements, userId, userName) {
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
                        No se encontraron registros.
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
                if (type === 'Abono_Retencion') return 'bx bx-minus-circle';
                if (type === 'Liquidacion_Caja') return 'bx bx-check-circle';
                if (type === 'Cargo_Excedente') return 'bx bx-plus-circle';
                return 'bx bx-briefcase';
            };

            return filtered.map(rec => {
                const statusConfig = getStatusConfig(rec.status);
                const typeIcon = getTypeIcon(rec.tipo);

                let origenBadge = '';
                let saldoHtml = '';
                let subDebtClass = '';
                let actionsHtml = '';
                let progressHtml = '';

                // LÓGICA POR TIPO DE ORIGEN
                if (rec.origen === 'anticipo') {
                    subDebtClass = rec.saldo > 0 ? 'ap-text-danger' : 'ap-text-success';
                    origenBadge = `<span class="ap-origen-badge ap-origen-anticipo"><i class="bx bx-up-arrow-circle"></i> Anticipo</span>`;
                    saldoHtml = `<span class="ap-balance-value ${subDebtClass}">${fmt(rec.saldo)}</span>`;

                    const perc = rec.monto > 0 ? Math.round(((rec.monto - rec.saldo) / rec.monto) * 100) : 0;
                    const progressColor = rec.saldo > 0 ? '#f59e0b' : '#22c55e';
                    progressHtml = `
                        <div class="ap-mini-progress">
                            <div class="ap-mini-bar">
                                <div class="ap-mini-fill" style="width: ${perc}%; background: ${progressColor};"></div>
                            </div>
                            <span class="ap-mini-label">${rec.saldo > 0 ? `${perc}%` : '✓ 100%'}</span>
                        </div>
                    `;

                    actionsHtml = `
                        <div style="display:flex; gap:0.3rem; justify-content:center;">
                            <button class="ap-btn-icon" style="background:var(--surface-muted); color:var(--secondary-dark); border-color:#cbd5e1; width:2rem; height:2rem;"
                                    onclick="event.stopPropagation(); showAdvanceHistory(${rec.id}, '${rec.folio}')"
                                    title="Ver Comprobaciones Asociadas">
                                <i class="bx bx-list-ul"></i>
                            </button>
                    `;

                    if (rec.saldo > 0) {
                        actionsHtml += `
                            <button class="ap-btn-icon ap-btn-ajuste" style="width:2rem; height:2rem;"
                                    onclick="event.stopPropagation(); openBalanceAdjustmentModal(${userId}, '${userName}', ${rec.id}, null, '${rec.folio}')"
                                    title="Ajustar Sobrante de este Anticipo">
                                <i class="bx bx-slider-alt"></i>
                            </button>
                        `;
                    }
                    actionsHtml += `</div>`;

                } else if (rec.origen === 'reembolso' || rec.origen === 'comprobacion') {
                    if (rec.origen === 'reembolso') {
                        origenBadge = `<span class="ap-origen-badge ap-origen-reembolso"><i class="bx bx-down-arrow-circle"></i> Reembolso</span>`;
                        saldoHtml = `<span class="ap-balance-value" style="color:var(--secondary-lighter);">N/A</span>`;
                    } else {
                        origenBadge = `<span class="ap-origen-badge ap-origen-comprobacion" title="Ampara el anticipo: ${rec.vinculo}"><i class="bx bx-link"></i> ${rec.vinculo || 'Comprobación'}</span>`;
                        saldoHtml = `<span class="ap-balance-value" style="color:var(--secondary-lighter);">Abono</span>`;
                    }

                    let payStatus = 'POR PAGAR';
                    let payColor = '#ca8a04';
                    let payBg = '#fef08a';

                    if (['Pagado', 'Comprobado', 'Entregado', 'Consolidado'].includes(rec.status)) {
                        payStatus = 'LIQUIDADO';
                        payColor = '#16a34a';
                        payBg = '#dcfce7';
                    } else if (['Borrador', 'Rechazado'].includes(rec.status)) {
                        payStatus = 'NO PROCEDE';
                        payColor = '#dc2626';
                        payBg = '#fee2e2';
                    }

                    progressHtml = `
                        <span class="ap-payment-status" style="color:${payColor}; background:${payBg};">
                            ${payStatus}
                        </span>
                    `;
                    actionsHtml = `<span style="color:var(--secondary-lighter);">—</span>`;

                } else if (rec.origen === 'ajuste_balance') {
                    const isRetencion = rec.tipo === 'Abono_Retencion' || rec.tipo === 'Liquidacion_Caja';
                    const colorStyle = isRetencion ?
                        'background:#fee2e2; color:#991b1b; border:1px solid #f87171;' :
                        'background:#dcfce7; color:#166534; border:1px solid #4ade80;';

                    const tipoFormateado = rec.tipo.replace(/_/g, ' ');

                    origenBadge = `<span class="ap-origen-badge" style="${colorStyle}"><i class="bx bx-slider-alt"></i> ${tipoFormateado}</span>`;
                    saldoHtml = `<span class="ap-balance-value" style="font-weight:700;">${fmt(rec.saldo)}</span>`;

                    progressHtml = `<span class="ap-payment-status" style="color:#16a34a; background:#dcfce7;">ASENTADO</span>`;
                    actionsHtml = `<span style="color:var(--secondary-lighter);">—</span>`;
                }

                return `
                <tr class="ap-sub-row">
                    <td class="ap-sub-folio-cell">
                        <span class="ap-sub-folio">${rec.folio}</span>
                    </td>
                    <td class="ap-sub-date-cell">
                        <span class="ap-sub-date">${rec.fecha}</span>
                    </td>
                    <td class="ap-sub-concept-cell">
                        <div class="ap-concept-wrapper">
                            <span class="ap-concept-desc">${rec.descripcion || 'Sin descripción'}</span>
                        </div>
                    </td>
                    <td class="ap-sub-type-cell">
                        <span class="ap-type-badge">
                            <i class="${typeIcon}"></i> ${rec.tipo.replace(/_/g, ' ')}
                        </span>
                    </td>
                    <td class="ap-sub-origen-cell text-center">
                        ${origenBadge}
                    </td>
                    <td class="ap-sub-amount-cell text-right">
                        <span class="ap-amount-value">${fmt(rec.monto)}</span>
                    </td>
                    <td class="ap-sub-balance-cell text-right">
                        ${saldoHtml}
                    </td>
                    <td class="ap-sub-progress-cell text-center">
                        ${progressHtml}
                    </td>
                    <td class="ap-sub-status-cell text-center">
                        <span class="ap-status-badge ${statusConfig.class}">
                            <i class="${statusConfig.icon}"></i> ${statusConfig.label}
                        </span>
                    </td>
                    <td class="ap-sub-actions-cell text-center">
                        ${actionsHtml}
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

                tbody.innerHTML = renderSubRows(emp.movimientos, userId, emp.nombre);
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
            btnPrev.onclick = () => {
                currentPage--;
                renderTable();
            };
            container.appendChild(btnPrev);

            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.className = `ap-page-btn ${i === currentPage ? 'ap-page-btn-active' : ''}`;
                btn.textContent = i;
                btn.onclick = () => {
                    currentPage = i;
                    renderTable();
                };
                container.appendChild(btn);
            }

            const btnNext = document.createElement('button');
            btnNext.className = 'ap-page-btn';
            btnNext.innerHTML = '<i class="bx bx-chevron-right"></i>';
            btnNext.disabled = currentPage === totalPages;
            btnNext.onclick = () => {
                currentPage++;
                renderTable();
            };
            container.appendChild(btnNext);
        }

        // ── KARDEX GLOBAL DEL COLABORADOR (PAGINACIÓN + UI PREMIUM) ──
        function showKardex(userId) {
            const emp = employeesData.find(e => e.user_id === userId);
            if (!emp) return;

            currentKardexEmp = emp;
            currentKardexMovs = [...emp.movimientos].sort((a, b) => a.fecha_raw - b.fecha_raw);
            kardexCurrentPage = 1;

            renderKardex();

            const overlay = document.getElementById('kardex-modal-overlay');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function renderKardex() {
            const totalPages = Math.ceil(currentKardexMovs.length / kardexItemsPerPage);
            if (kardexCurrentPage > totalPages && totalPages > 0) kardexCurrentPage = totalPages;

            const startIdx = (kardexCurrentPage - 1) * kardexItemsPerPage;
            const paginatedMovs = currentKardexMovs.slice(startIdx, startIdx + kardexItemsPerPage);

            let saldoStatus = currentKardexEmp.saldo_corriente > 0 ? 'k-stat-positive' : (currentKardexEmp.saldo_corriente < 0 ? 'k-stat-negative' : '');

            let html = `
            <div class="kardex-content">
                <div class="kardex-premium-header">
                    <div class="k-user-card">
                        <div class="k-avatar-lg">${getInitials(currentKardexEmp.nombre)}</div>
                        <div class="k-user-details">
                            <span class="k-user-name">${currentKardexEmp.nombre}</span>
                            <span class="k-user-depto"><i class="bx bx-buildings"></i> ${currentKardexEmp.depto}</span>
                        </div>
                    </div>
                    <div class="k-financial-cards">
                        <div class="k-stat-card ${saldoStatus}" style="min-width: 200px;">
                            <span class="k-stat-label">Saldo en Libro Mayor</span>
                            <span class="k-stat-value">${fmt(currentKardexEmp.saldo_corriente)}</span>
                        </div>
                    </div>
                </div>

                <div class="kardex-table-wrapper">
                    <table class="kardex-table">
                        <thead>
                            <tr>
                                <th style="width: 12%;">Fecha</th>
                                <th style="width: 15%;">Folio</th>
                                <th style="width: 32%;">Naturaleza Operativa</th>
                                <th style="width: 12%;">Cargo (+)</th>
                                <th style="width: 12%;">Abono (-)</th>
                                <th style="width: 17%;">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            if (paginatedMovs.length === 0) {
                html += `<tr><td colspan="6" class="kardex-empty-row">No existen asientos contables registrados.</td></tr>`;
            } else {
                paginatedMovs.forEach(m => {
                    let cargo = '';
                    let abono = '';

                    if (m.origen === 'anticipo') {
                        cargo = fmt(m.monto);
                    } else if (m.origen === 'comprobacion' || m.origen === 'reembolso') {
                        abono = fmt(m.monto);
                    } else if (m.origen === 'ajuste_balance') {
                        if (m.tipo === 'Abono_Retencion' || m.tipo === 'Liquidacion_Caja') {
                            abono = fmt(m.monto);
                        } else {
                            cargo = fmt(m.monto);
                        }
                    }

                    html += `
                        <tr>
                            <td class="kardex-date-cell">${m.fecha.split(' ')[0]}</td>
                            <td class="kardex-folio-cell"><span class="kardex-folio">${m.folio}</span></td>
                            <td class="kardex-concept-cell" title="${m.descripcion}">${m.descripcion}</td>
                            <td class="kardex-cargo-cell">${cargo}</td>
                            <td class="kardex-abono-cell">${abono}</td>
                            <td class="kardex-status-cell">
                                <span class="ap-status-badge ${getStatusConfig(m.status).class}">${m.status}</span>
                            </td>
                        </tr>
                    `;
                });
            }

            html += `
                        </tbody>
                    </table>
                </div>

                <div class="kardex-footer-controls">
                    <span class="k-page-info">Total de registros: ${currentKardexMovs.length}</span>
                    <div class="k-pagination" id="k-pagination-container">
                        <!-- Paginación JS -->
                    </div>
                    <div>
                        <select id="k-size-select" class="k-size-select" onchange="changeKardexPageSize()">
                            <option value="7" ${kardexItemsPerPage === 7 ? 'selected' : ''}>Mostrar 7</option>
                            <option value="10" ${kardexItemsPerPage === 10 ? 'selected' : ''}>Mostrar 10</option>
                            <option value="20" ${kardexItemsPerPage === 20 ? 'selected' : ''}>Mostrar 20</option>
                            <option value="30" ${kardexItemsPerPage === 30 ? 'selected' : ''}>Mostrar 30</option>
                            <option value="all" ${kardexItemsPerPage > 1000 ? 'selected' : ''}>Todos</option>
                        </select>
                    </div>
                </div>
            </div>`;

            document.getElementById('kardex-modal-body').innerHTML = html;
            renderKardexPagination(totalPages);
        }

        function changeKardexPageSize() {
            const val = document.getElementById('k-size-select').value;
            kardexItemsPerPage = val === 'all' ? 999999 : parseInt(val);
            kardexCurrentPage = 1;
            renderKardex();
        }

        function renderKardexPagination(totalPages) {
            const container = document.getElementById('k-pagination-container');
            if (!container || totalPages <= 1) return;

            const btnPrev = document.createElement('button');
            btnPrev.className = 'k-page-btn';
            btnPrev.innerHTML = '<i class="bx bx-chevron-left"></i>';
            btnPrev.disabled = kardexCurrentPage === 1;
            btnPrev.onclick = () => { kardexCurrentPage--; renderKardex(); };
            container.appendChild(btnPrev);

            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.className = `k-page-btn ${i === kardexCurrentPage ? 'active' : ''}`;
                btn.textContent = i;
                btn.onclick = () => { kardexCurrentPage = i; renderKardex(); };
                container.appendChild(btn);
            }

            const btnNext = document.createElement('button');
            btnNext.className = 'k-page-btn';
            btnNext.innerHTML = '<i class="bx bx-chevron-right"></i>';
            btnNext.disabled = kardexCurrentPage === totalPages;
            btnNext.onclick = () => { kardexCurrentPage++; renderKardex(); };
            container.appendChild(btnNext);
        }

        // ── HISTORIAL DE ANTICIPO ──
        async function showAdvanceHistory(advanceId, advanceFolio) {
            try {
                const modalBody = document.getElementById('advance-history-modal-body');
                const overlay = document.getElementById('advance-history-modal-overlay');
                modalBody.innerHTML = `
                    <div style="text-align:center; padding:2rem;">
                        <i class="bx bx-loader-alt bx-spin" style="font-size:2.5rem; color:var(--primary-dark);"></i>
                        <p style="color:var(--secondary-lighter); margin-top:1rem;">Cargando información...</p>
                    </div>
                `;
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';

                const response = await fetch(`{{ url('administration/expense-claims/advances') }}/${advanceId}`);
                const res = await response.json();

                if (!res.success) {
                    modalBody.innerHTML = `<div style="text-align:center; padding:2rem; color:var(--secondary-lighter);">No se pudo cargar el historial.</div>`;
                    return;
                }

                const advanceData = res.data;
                modalBody.innerHTML = buildAdvanceHistoryHTML(advanceData, advanceFolio);

            } catch (error) {
                const modalBody = document.getElementById('advance-history-modal-body');
                modalBody.innerHTML = `<div style="text-align:center; padding:2rem; color:#dc2626;">Error de conexión. Intente nuevamente.</div>`;
            }
        }

        function buildAdvanceHistoryHTML(advanceData, advanceFolio) {
            const statusConfig = getStatusConfig(advanceData.status);
            let html = `
            <div class="kardex-content">
                <div class="advance-summary-cards" style="display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.5rem;">
                    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:0.75rem; padding:1rem; display:flex; flex-direction:column;">
                        <span style="font-size:0.68rem; font-weight:600; text-transform:uppercase; color:var(--secondary-lighter);">Monto Original</span>
                        <span style="font-size:1.2rem; font-weight:800; color:var(--secondary-dark);">${fmt(advanceData.amount)}</span>
                    </div>
                    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:0.75rem; padding:1rem; display:flex; flex-direction:column;">
                        <span style="font-size:0.68rem; font-weight:600; text-transform:uppercase; color:var(--secondary-lighter);">Saldo a Comprobar</span>
                        <span style="font-size:1.2rem; font-weight:800; color:${advanceData.balance > 0 ? '#dc2626' : '#16a34a'};">${fmt(advanceData.balance)}</span>
                    </div>
                    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:0.75rem; padding:1rem; display:flex; flex-direction:column; justify-content:center; align-items:flex-start;">
                        <span class="ap-status-badge ${statusConfig.class}">
                            <i class="${statusConfig.icon}"></i> ${advanceData.status}
                        </span>
                    </div>
                </div>

                <div style="font-size:0.9rem; font-weight:700; color:var(--primary-dark); margin-bottom:0.75rem; text-transform:uppercase;">
                    <i class="bx bx-receipt"></i> Comprobaciones (${advanceData.expense_claims ? advanceData.expense_claims.length : 0})
                </div>

                <div class="kardex-table-wrapper">
                    <table class="kardex-table">
                        <thead>
                            <tr>
                                <th style="width: 25%;">Folio</th>
                                <th style="width: 15%;">Fecha</th>
                                <th style="width: 30%;">Tipo</th>
                                <th style="width: 15%;">Monto</th>
                                <th style="width: 15%;">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            if (advanceData.expense_claims && advanceData.expense_claims.length > 0) {
                advanceData.expense_claims.forEach(claim => {
                    let claimStatus = getStatusConfig(claim.status_review);
                    html += `
                        <tr>
                            <td class="kardex-folio-cell"><span class="kardex-folio">${claim.folio_system}</span></td>
                            <td class="kardex-date-cell">${formatDate(claim.claim_date)}</td>
                            <td class="kardex-concept-cell">${claim.request_type}</td>
                            <td class="kardex-abono-cell">- ${fmt(claim.total_amount)}</td>
                            <td class="kardex-status-cell">
                                <span class="ap-status-badge ${claimStatus.class}">${claim.status_review}</span>
                            </td>
                        </tr>
                    `;
                });
            } else {
                html += `<tr><td colspan="5" class="kardex-empty-row">No hay comprobaciones registradas.</td></tr>`;
            }

            html += `
                        </tbody>
                    </table>
                </div>
            </div>`;
            return html;
        }

        // ── MODAL NATIVO DE AJUSTE MANUAL EN LIBRO MAYOR ──
        function openBalanceAdjustmentModal(userId, userName, advanceId = '', claimId = '', refFolio = '') {
            document.getElementById('adj-user-id').value = userId;
            document.getElementById('adj-adv-id').value = advanceId;
            document.getElementById('adj-claim-id').value = claimId;

            document.getElementById('adj-user-name').textContent = userName;

            const refWrap = document.getElementById('adj-ref-wrap');
            if(refFolio) {
                document.getElementById('adj-ref-folio').textContent = refFolio;
                refWrap.style.display = 'block';
            } else {
                refWrap.style.display = 'none';
            }

            // Reset form
            document.getElementById('adj-movement-type').value = 'Abono_Retencion';
            document.getElementById('adj-amount').value = '';
            document.getElementById('adj-desc').value = '';

            const overlay = document.getElementById('ajuste-modal-overlay');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeAjusteModal() {
            const overlay = document.getElementById('ajuste-modal-overlay');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Cierre general de modales
        function closeKardexModal() { document.getElementById('kardex-modal-overlay').classList.remove('active'); document.body.style.overflow = ''; }
        function closeAdvanceHistoryModal() { document.getElementById('advance-history-modal-overlay').classList.remove('active'); document.body.style.overflow = ''; }

        document.querySelectorAll('.kardex-modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeKardexModal();
                closeAdvanceHistoryModal();
                closeAjusteModal();
            }
        });

        async function submitAjusteForm() {
            const movement_type = document.getElementById('adj-movement-type').value;
            const amount = parseFloat(document.getElementById('adj-amount').value);
            const description = document.getElementById('adj-desc').value.trim();
            const user_id = document.getElementById('adj-user-id').value;
            const advance_id = document.getElementById('adj-adv-id').value;
            const claim_id = document.getElementById('adj-claim-id').value;

            if (!amount || amount <= 0) {
                Swal.fire('Atención', 'El monto debe ser mayor a cero.', 'warning');
                return;
            }
            if (!description) {
                Swal.fire('Atención', 'Es obligatorio detallar el concepto contable para auditoría.', 'warning');
                return;
            }

            const payload = {
                _token: '{{ csrf_token() }}',
                user_id: user_id,
                movement_type: movement_type,
                amount: amount,
                description: description,
                expense_advance_id: advance_id || null,
                expense_claim_id: claim_id || null
            };

            try {
                Swal.fire({ title: 'Asentando en Libro Mayor...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                const response = await fetch('{{ route('expense-claims.accounts-payable.adjust-balance') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(payload)
                });
                const res = await response.json();

                if (res.success) {
                    closeAjusteModal();
                    Swal.fire({
                        icon: 'success',
                        title: '¡Libro Mayor Actualizado!',
                        text: `${res.message} (Folio Auditoría: ${res.folio})`,
                        confirmButtonColor: 'var(--primary-dark)'
                    }).then(() => { window.location.reload(); });
                } else {
                    Swal.fire('Error de Asiento', res.message, 'error');
                }
            } catch (error) {
                Swal.fire('Falla de Red', 'No se pudo contactar al servidor para registrar el movimiento.', 'error');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            initDashboard();
            renderTable();
        });
    </script>
@endpush
