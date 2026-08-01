{{-- ════════════════════════════════════════════════════════════════════════════
     VISTA BLADE: PANEL DE ANTICIPOS (advances.blade.php)
     Vinco ERP - Gestión y Solicitud de Fondos Operativos
     ════════════════════════════════════════════════════════════════════════════ --}}
@extends('modules.administration.expense-claims.index')

@section('styles')
    {{-- Vinculación de la hoja de estilos externa limpia --}}
    <link rel="stylesheet" href="{{ asset('css/modules/administration/expense-claims/advances.css') }}">
@endsection

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
                    <i class="bx bx-history"></i> Historial de Anticipos Operativos
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

                    {{-- BOTÓN NUEVO ANTICIPO --}}
                    <button class="btn btn-primary adv-btn-new" onclick="openAdvanceModalForCreate()" aria-label="Solicitar Anticipo">
                        <i class="bx bx-plus-circle" style="font-size: 1.1rem;"></i> Solicitar Anticipo
                    </button>
                </div>
            </div>

            <div class="adv-table-scroll">
                <table class="adv-data-table" id="advances-data-table">
                    <thead>
                        <tr>
                            <th>Folio Sistema</th>
                            <th>Solicitante</th>
                            <th>Departamento</th>
                            <th class="text-center">Fecha Requerida</th>
                            <th>Tipo de Anticipo</th>
                            <th>Motivo / Justificación</th>
                            <th class="text-right">Monto (MXN)</th>
                            <th class="text-right">Saldo a Comprobar</th>
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
                            <option value="10" selected>10</option>
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
         MODAL: SOLICITAR / VER ANTICIPO DE GASTO OPERATIVO
         ════════════════════════════════════════════════════════════════════════ --}}
    <div id="advance-modal" class="modal-bg hidden" aria-hidden="true" role="dialog">
        <div class="modal-box adv-modal-box">
            <div class="adv-modal-header">
                <h2 class="adv-modal-title" id="adv-modal-title">
                    <i class="bx bx-money-withdraw"></i> Solicitud de <strong>Anticipo</strong>
                </h2>
                <div class="modal-header-actions">
                    <button class="adv-btn-close" onclick="closeAdvanceModal()" aria-label="Cerrar ventana"><i class="bx bx-x"></i></button>
                </div>
            </div>

            <div class="adv-modal-body">
                <div class="adv-info-strip">
                    <div>
                        <span class="adv-info-label">Folio del Anticipo</span>
                        <strong id="adv-modal-folio" class="adv-info-val-primary">Nuevo Trámite</strong>
                    </div>
                    <div class="text-right">
                        <span class="adv-info-label">Estado Actual</span>
                        <strong id="adv-modal-status" class="adv-info-val-secondary">Generando...</strong>
                    </div>
                </div>

                <div class="adv-grid-2">
                    <div>
                        <label class="adv-input-label">Nombre del Solicitante</label>
                        <div class="adv-input-wrap">
                            <i class="bx bx-user adv-input-icon"></i>
                            <input type="text" id="adv-user-name" class="adv-input" readonly value="{{ Auth::user()->name }}">
                        </div>
                    </div>
                    <div>
                        <label class="adv-input-label">Fecha de Requerimiento</label>
                        <div class="adv-input-wrap">
                            <i class="bx bx-calendar adv-input-icon"></i>
                            <input type="text" id="adv-date-text" class="adv-input adv-focusable" placeholder="DD/MM/AAAA">
                        </div>
                    </div>
                </div>

                <div class="adv-grid-2">
                    <div>
                        <label class="adv-input-label">Tipo de Anticipo</label>
                        <div class="adv-input-wrap">
                            <i class="bx bx-briefcase adv-input-icon"></i>
                            <select id="adv-type" class="adv-input adv-focusable">
                                <option value="Viaticos" selected>Viáticos y Hospedaje</option>
                                <option value="Operativos">Gastos Operativos (Campo)</option>
                                <option value="Caja Chica">Fondo de Caja Chica</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="adv-input-label">Monto Solicitado (MXN)</label>
                        <div class="adv-input-wrap">
                            <i class="bx bx-dollar adv-input-icon"></i>
                            <input type="number" id="adv-amount" class="adv-input adv-focusable" placeholder="Ej. 5000.00" min="1" step="0.01">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="adv-input-label">Descripción / Justificación Operativa</label>
                    <textarea id="adv-desc" class="adv-input adv-focusable" placeholder="Explique para qué se destinarán los fondos solicitados..."></textarea>
                </div>
            </div>

            <div class="adv-modal-footer">
                <span id="adv-modal-note" class="adv-footer-note">
                    <i class="bx bx-info-circle"></i> Los anticipos requieren validación de la gerencia.
                </span>
                <div id="adv-footer-create" class="adv-footer-actions">
                    <button type="button" class="adv-btn-cancel" onclick="closeAdvanceModal()">Cancelar</button>
                    <button type="button" class="btn btn-primary adv-btn-submit" onclick="submitAdvance()">
                        <i class="bx bx-send"></i> Emitir Solicitud
                    </button>
                </div>
                <div id="adv-footer-view" class="adv-footer-actions hidden">
                    <button type="button" class="btn btn-secondary adv-btn-close-view" onclick="closeAdvanceModal()">Cerrar Vista</button>
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
        const advancesData = {!! json_encode($advances) !!};
        let currentPage = 1;
        let itemsPerPage = 10;
        let searchQuery = '';
        let activeFilter = 'all';

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
                const debtClass = adv.saldo > 0 ? 'adv-text-danger' : 'adv-text-success';
                let shortMotive = adv.motivo.length > 30 ? adv.motivo.substring(0, 30) + '...' : adv.motivo;

                let html = `
                <tr class="adv-row-enter" style="animation-delay: ${index * 0.04}s;">
                    <td><strong class="adv-text-primary adv-font-mono">${adv.folio}</strong></td>
                    <td><strong class="adv-fw-700">${adv.nombre}</strong></td>
                    <td>${adv.depto}</td>
                    <td class="text-center">${adv.fecha}</td>
                    <td><i class="${getTypeIcon(adv.tipo)} adv-icon-muted"></i> ${adv.tipo}</td>
                    <td title="${adv.motivo}">${shortMotive}</td>
                    <td class="text-right adv-font-mono adv-fw-700">${fmt(adv.monto)}</td>
                    <td class="text-right adv-font-mono adv-fw-700 ${debtClass}">${fmt(adv.saldo)}</td>
                    <td class="text-center">
                        <span class="adv-status-badge ${st.class}"><i class="${st.icon}"></i> ${st.label}</span>
                    </td>
                    <td class="text-center">
                        <button class="adv-btn-icon-view" onclick="verDetalles(${adv.id})" title="Inspeccionar Solicitud"><i class="bx bx-search-alt"></i></button>
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

        /* ── LÓGICA DEL MODAL ── */
        function openAdvanceModalForCreate() {
            document.getElementById('adv-modal-title').innerHTML = '<i class="bx bx-money-withdraw"></i> Solicitud de <strong>Anticipo</strong>';
            document.getElementById('adv-modal-folio').textContent = 'Asignación Automática';
            document.getElementById('adv-modal-status').textContent = 'Borrador / Pendiente';

            flatpickr("#adv-date-text", { locale: "es", dateFormat: "d/m/Y", disableMobile: "true" });
            document.getElementById('adv-date-text').value = '';
            document.getElementById('adv-amount').value = '';
            document.getElementById('adv-desc').value = '';

            document.querySelectorAll('.adv-focusable').forEach(el => el.removeAttribute('disabled'));
            document.getElementById('adv-footer-create').classList.remove('hidden');
            document.getElementById('adv-footer-view').classList.add('hidden');

            document.getElementById('advance-modal').classList.remove('hidden');
        }

        async function verDetalles(id) {
            Swal.fire({ title: 'Cargando Detalles...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const response = await fetch(`{{ url('administration/expense-claims/advances') }}/${id}`);
                const res = await response.json();

                if (res.success) {
                    const adv = res.data;
                    document.getElementById('adv-modal-title').innerHTML = `<i class="bx bx-search-alt"></i> Inspección de <strong>Anticipo</strong>`;
                    document.getElementById('adv-modal-folio').textContent = adv.folio_system;
                    document.getElementById('adv-modal-status').textContent = adv.status;

                    document.getElementById('adv-user-name').value = adv.user.name;

                    const dateParts = adv.advance_date.split('-');
                    document.getElementById('adv-date-text').value = `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;

                    document.getElementById('adv-type').value = adv.advance_type;
                    document.getElementById('adv-amount').value = adv.amount;
                    document.getElementById('adv-desc').value = adv.description;

                    document.querySelectorAll('.adv-focusable').forEach(el => el.setAttribute('disabled', 'true'));
                    document.getElementById('adv-footer-create').classList.add('hidden');
                    document.getElementById('adv-footer-view').classList.remove('hidden');

                    Swal.close();
                    document.getElementById('advance-modal').classList.remove('hidden');
                }
            } catch (error) {
                Swal.fire('Error', 'No se pudo cargar la información.', 'error');
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

            if (!fecha || !monto || !desc) {
                Swal.fire('Atención', 'Todos los campos son obligatorios.', 'warning');
                return;
            }

            let formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('advance_type', tipo);
            formData.append('advance_date', fecha);
            formData.append('amount', monto);
            formData.append('description', desc);

            try {
                Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                const response = await fetch('{{ route('expense-claims.advances.store') }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();

                if (data.success) {
                    Swal.fire({ title: '¡Anticipo Solicitado!', text: `Folio: ${data.folio}`, icon: 'success' }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Problema de conexión con el servidor.', 'error');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            initDashboard();
            renderTable();
        });
    </script>
@endpush
