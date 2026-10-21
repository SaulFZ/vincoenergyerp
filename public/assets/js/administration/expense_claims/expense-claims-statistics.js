(() => {
    'use strict';

    // DEMOSTRACIÓN: toda la información se genera aquí. No hay llamadas al servidor.
    // Importes en centavos para mantener consistentes tarjetas, gráficas y detalle.
    const CUTOFF = '2026-10-16';
    const PEOPLE = [
        { id: 1, name: 'Andrea Molina', area: 'Operaciones' },
        { id: 2, name: 'Diego Rivera', area: 'Comercial' },
        { id: 3, name: 'Lucía Herrera', area: 'Administración' },
        { id: 4, name: 'Mateo Campos', area: 'Tecnología' },
        { id: 5, name: 'Valeria Santos', area: 'Operaciones' },
        { id: 6, name: 'Emiliano Ríos', area: 'Comercial' }
    ];
    const CATEGORIES = ['Hospedaje', 'Transporte', 'Alimentación', 'Materiales', 'Otros'];
    const COLORS = ['#20406d', '#6689b0', '#739d96', '#9988ad', '#b6a78b'];
    const MONTHS = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    const TYPES = {
        anticipo: { label: 'Anticipo', color: 'blue' },
        comprobacion: { label: 'Comprobación', color: 'green' },
        reembolso: { label: 'Reembolso', color: 'purple' },
        retencion: { label: 'Retención', color: 'sand' }
    };
    const state = { period: 'quarter', area: 'all', person: 'all', query: '', type: 'all', page: 1, size: 8 };
    const $ = id => document.getElementById(id);
    const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const money = cents => new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN', maximumFractionDigits: 2 }).format(cents / 100);
    const compact = cents => new Intl.NumberFormat('es-MX', { notation: 'compact', maximumFractionDigits: 1 }).format(cents / 100);
    const percent = n => new Intl.NumberFormat('es-MX', { maximumFractionDigits: 1 }).format(n) + '%';
    const sum = (rows, key = 'amount') => rows.reduce((total, row) => total + row[key], 0);
    const searchKey = value => String(value).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    const dateLabel = value => { const [y, m, d] = value.split('-'); return `${d} ${MONTHS[Number(m) - 1].toLowerCase()} ${y}`; };
    const initials = name => name.split(' ').slice(0, 2).map(word => word[0]).join('');

    function createDemoData() {
        const rows = [];
        let id = 1;
        const date = (month, day) => `2026-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const add = row => {
            const person = PEOPLE.find(p => p.id === row.personId);
            const prefix = { anticipo: 'ANT', comprobacion: 'COM', reembolso: 'REE', retencion: 'BAL' }[row.operation];
            const record = { id, folio: `${prefix}-26${row.date.slice(5, 7)}-${String(id).padStart(3, '0')}`, person, approval: '', payment: '', advanceId: null, leftover: 0, category: 'Otros', ...row };
            id++;
            rows.push(record);
            return record;
        };
        for (let month = 1; month <= 10; month++) {
            PEOPLE.forEach((person, i) => {
                const amount = (6500 + ((month * 1709 + i * 2351) % 15000)) * 100;
                const pending = month === 10 && i === 5;
                const advance = add({ personId: person.id, operation: 'anticipo', date: date(month, 1 + i), amount,
                    status: pending ? 'Pendiente' : 'Entregado', subtype: i % 2 ? 'Tarjeta Toka' : 'Transferencia',
                    concept: `Anticipo para ${['visita a sucursal', 'reunión con clientes', 'gestión administrativa', 'soporte en sitio', 'supervisión de obra', 'visita comercial'][i]} — ${MONTHS[month - 1]} 2026.` });
                if (pending) return;
                const approved = month < 10 || i % 3 === 0;
                const claimAmount = Math.round(amount * (month < 8 ? 0.9 : 0.62));
                const leftover = approved && month < 8 ? amount - claimAmount : 0;
                add({ personId: person.id, operation: 'comprobacion', date: date(month, 8 + i), amount: claimAmount,
                    advanceId: advance.id, status: approved ? 'Validado' : (i === 1 ? 'Pendiente' : 'Validado'),
                    approval: approved ? 'Aprobado' : (i === 4 ? 'Rechazado' : 'En revisión'),
                    subtype: i % 2 ? 'Electrónica · Toka' : 'Directa · Transferencia', category: CATEGORIES[(month + i) % 5],
                    leftover, concept: `Comprobación de ${CATEGORIES[(month + i) % 5].toLowerCase()} correspondiente al anticipo ${advance.folio}. Incluye los gastos del servicio y los documentos de respaldo de la visita.` });
                if (month >= 8 && approved && i === 0) {
                    add({ personId: person.id, operation: 'retencion', date: date(month, 15), amount: amount - claimAmount,
                        advanceId: advance.id, status: 'Consolidado', subtype: 'Sobrante retenido',
                        concept: `Sobrante de ${advance.folio} conservado para una próxima comisión. Suma al libro mayor y liquida el pendiente de este anticipo.` });
                }
                if ((month + i) % 2 === 0) {
                    const approvedRefund = month < 10 || i === 0;
                    add({ personId: person.id, operation: 'reembolso', date: date(month, 9 + i), amount: (650 + ((month * 237 + i * 431) % 3500)) * 100,
                        status: approvedRefund ? 'Validado' : 'Pendiente', approval: approvedRefund ? 'Aprobado' : 'En espera',
                        payment: month < 9 ? 'Pagado' : 'No pagado', subtype: 'Recursos propios', category: CATEGORIES[(i + 2) % 5],
                        concept: `Reembolso de gastos cubiertos con recursos propios para actividades de ${person.area.toLowerCase()}.` });
                }
            });
        }
        return rows;
    }

    const DATA = createDemoData();
    function effectiveStatus(row) {
        if (row.status === 'Rechazado') return 'Rechazado';
        if (row.status === 'Validado' && ['Aprobado', 'Rechazado'].includes(row.approval)) return row.approval;
        return row.status;
    }
    const approved = row => row.status === 'Validado' && row.approval === 'Aprobado';
    function advanceBalance(advance) {
        if (advance.status !== 'Entregado') return 0;
        const related = DATA.filter(row => row.advanceId === advance.id && row.date <= CUTOFF);
        const used = related.reduce((total, row) => total + (row.operation === 'comprobacion' && approved(row) ? row.amount + row.leftover : row.operation === 'retencion' ? row.amount : 0), 0);
        return Math.max(0, advance.amount - used);
    }
    function treasury(row) {
        if (row.operation === 'anticipo') return row.status === 'Entregado' ? (advanceBalance(row) === 0 ? 'Comprobado' : 'No comprobado') : 'No aplica';
        if (row.operation === 'reembolso') return effectiveStatus(row) === 'Rechazado' ? 'No aplica' : row.payment;
        if (row.operation === 'retencion') return 'Aplicada';
        return 'No aplica';
    }
    function scopedRows() {
        const start = state.period === 'month' ? '2026-10-01' : state.period === 'quarter' ? '2026-08-01' : '2026-01-01';
        return DATA.filter(row => row.date >= start && row.date <= CUTOFF && (state.area === 'all' || row.person.area === state.area) && (state.person === 'all' || String(row.person.id) === state.person));
    }
    function tableRows() {
        return scopedRows().filter(row => (state.type === 'all' || row.operation === state.type) && (!state.query || searchKey([row.folio, row.person.name, row.person.area, row.concept].join(' ')).includes(searchKey(state.query))))
            .sort((a, b) => b.date.localeCompare(a.date) || b.id - a.id);
    }
    function metrics(rows) {
        const advances = rows.filter(row => row.operation === 'anticipo' && row.status === 'Entregado');
        const claims = rows.filter(row => row.operation === 'comprobacion' && approved(row));
        const refunds = rows.filter(row => row.operation === 'reembolso' && approved(row) && row.payment !== 'Pagado');
        const balance = advances.reduce((total, row) => total + advanceBalance(row), 0);
        const requested = rows.filter(row => ['comprobacion', 'reembolso'].includes(row.operation));
        const awaiting = requested.filter(row => ['Pendiente', 'Validado'].includes(effectiveStatus(row)));
        const resolved = advances.filter(row => advanceBalance(row) === 0).length;
        return { advances, claims, refunds, balance, requested, awaiting, resolved, rate: advances.length ? resolved / advances.length * 100 : 0 };
    }
    function badge(label, color) {
        const colors = { Aprobado: 'green', Comprobado: 'green', Pagado: 'green', Aplicada: 'green', Consolidado: 'green', Entregado: 'blue', Validado: 'blue', Pendiente: 'sand', 'No pagado': 'sand', Rechazado: 'red', 'No comprobado': 'gray' };
        return `<span class="ecs-badge ecs-badge-${color || colors[label] || 'gray'}">${esc(label)}</span>`;
    }
    function renderKpis(rows) {
        const m = metrics(rows);
        const cards = [
            ['Anticipos entregados', money(sum(m.advances)), `${m.advances.length} anticipos del periodo`, 'bx-wallet', true],
            ['Gasto comprobado', money(sum(m.claims)), `${m.claims.length} comprobaciones aprobadas`, 'bx-check-shield'],
            ['Saldo por comprobar', money(m.balance), 'De los anticipos seleccionados, al corte', 'bx-time-five'],
            ['Reembolsos por pagar', money(sum(m.refunds)), `${m.refunds.length} aprobados pendientes de pago`, 'bx-transfer-alt'],
            ['Pendientes de dictamen', String(m.awaiting.length), 'En revisión o esperando aprobación', 'bx-list-check'],
            ['Anticipos conciliados', percent(m.rate), `${m.resolved} de ${m.advances.length} sin saldo pendiente`, 'bx-pie-chart-alt-2']
        ];
        $('ecs-kpis').innerHTML = cards.map(([label, value, note, icon, featured], i) => `<article class="ecs-kpi ${featured ? 'ecs-kpi-featured' : ''}"><div class="ecs-kpi-top"><span class="ecs-kpi-label">${label}</span><span class="ecs-kpi-icon"><i class="bx ${icon}" aria-hidden="true"></i></span></div><div class="ecs-kpi-value">${value}</div><div class="ecs-kpi-foot">${note}</div>${i === 5 ? `<div class="ecs-mini-progress"><span style="width:${m.rate}%"></span></div>` : ''}</article>`).join('');
    }
    function renderTrend(rows) {
        const months = state.period === 'month' ? [10] : state.period === 'quarter' ? [8, 9, 10] : [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        const points = months.map(month => {
            const selected = rows.filter(row => Number(row.date.slice(5, 7)) === month);
            return { month, advances: sum(selected.filter(r => r.operation === 'anticipo' && r.status === 'Entregado')), claims: sum(selected.filter(r => r.operation === 'comprobacion' && approved(r))) };
        });
        const max = Math.max(10000, ...points.flatMap(p => [p.advances, p.claims]));
        const top = Math.ceil(max / 1000000) * 1000000;
        const left = 64, base = 224, height = 172, plot = 636, group = plot / months.length, bar = Math.min(27, group * 0.25);
        let content = '';
        for (let i = 0; i <= 4; i++) {
            const value = top * i / 4, y = base - height * i / 4;
            content += `<line x1="${left}" y1="${y}" x2="710" y2="${y}" stroke="#e7edf4" ${i ? 'stroke-dasharray="3 5"' : ''}/><text x="54" y="${y + 4}" text-anchor="end">$${compact(value)}</text>`;
        }
        points.forEach((point, i) => {
            const x = left + group * (i + 0.5);
            [point.advances, point.claims].forEach((value, series) => {
                const h = value / top * height;
                content += `<rect x="${x + (series ? 3 : -bar - 3)}" y="${base - h}" width="${bar}" height="${h}" rx="4" fill="${series ? '#739d96' : '#20406d'}"><title>${MONTHS[point.month - 1]} · ${series ? 'Comprobaciones aprobadas' : 'Anticipos entregados'}: ${money(value)}</title></rect>`;
            });
            content += `<text x="${x}" y="250" text-anchor="middle">${MONTHS[point.month - 1]}</text>`;
        });
        $('ecs-trend').innerHTML = `<svg viewBox="0 0 740 270" role="img" aria-labelledby="ecs-trend-title ecs-trend-desc"><title id="ecs-trend-title">Anticipos entregados y comprobaciones aprobadas por mes</title><desc id="ecs-trend-desc">${points.map(p => `${MONTHS[p.month - 1]}: anticipos ${money(p.advances)}, comprobado ${money(p.claims)}`).join('. ')}</desc>${content}</svg>`;
    }
    function renderCategories(rows) {
        const claims = rows.filter(r => r.operation === 'comprobacion' && approved(r)), total = sum(claims);
        if (!total) { $('ecs-categories').innerHTML = '<p class="ecs-empty">No hay comprobaciones aprobadas en esta selección.</p>'; return; }
        let offset = 0;
        const values = CATEGORIES.map(category => sum(claims.filter(r => r.category === category)));
        const circles = values.map((value, i) => {
            const share = value / total * 100;
            const circle = `<circle cx="100" cy="100" r="77" fill="none" stroke="${COLORS[i]}" stroke-width="22" pathLength="100" stroke-dasharray="${share} ${100 - share}" stroke-dashoffset="${-offset}"><title>${CATEGORIES[i]}: ${money(value)}</title></circle>`;
            offset += share; return circle;
        }).join('');
        $('ecs-categories').innerHTML = `<div class="ecs-donut-wrap"><div class="ecs-donut"><svg viewBox="0 0 200 200" role="img" aria-label="Distribución de comprobaciones aprobadas por categoría">${circles}</svg><div class="ecs-donut-label"><strong>$${compact(total)}</strong><span>Gasto aprobado</span></div></div><div class="ecs-category-list">${values.map((value, i) => `<div class="ecs-category"><span class="ecs-dot" style="background:${COLORS[i]}"></span><div>${CATEGORIES[i]}<small>${money(value)}</small></div><strong>${percent(value / total * 100)}</strong></div>`).join('')}</div></div>`;
    }
    function renderAreas(rows) {
        const advances = metrics(rows).advances;
        const areas = [...new Set(PEOPLE.map(p => p.area))].filter(area => state.area === 'all' || state.area === area);
        const values = areas.map(area => ({ area, amount: advances.filter(a => a.person.area === area).reduce((n, a) => n + advanceBalance(a), 0) })).sort((a, b) => b.amount - a.amount);
        const max = Math.max(1, ...values.map(a => a.amount));
        $('ecs-areas').innerHTML = values.map(({ area, amount }) => `<div class="ecs-area"><div class="ecs-area-top"><span>${esc(area)}</span><strong>${money(amount)}</strong></div><div class="ecs-bar"><span style="width:${amount / max * 100}%"></span></div></div>`).join('');
    }
    function renderStatuses(rows) {
        const { requested } = metrics(rows);
        const statuses = ['Pendiente', 'Validado', 'Aprobado', 'Rechazado'];
        $('ecs-statuses').innerHTML = statuses.map((status, i) => {
            const count = requested.filter(r => effectiveStatus(r) === status).length;
            return `<div class="ecs-status">${badge(status)}<div class="ecs-bar"><span style="width:${requested.length ? count / requested.length * 100 : 0}%;background:${['#b6a78b', '#6689b0', '#739d96', '#b88991'][i]}"></span></div><strong>${count}</strong></div>`;
        }).join('') + `<p class="ecs-small-note">${requested.length} solicitudes · Validado significa pendiente de aprobación final.</p>`;
    }
    function renderPriorities(rows) {
        const m = metrics(rows);
        const oldest = m.advances.filter(a => advanceBalance(a) > 0 && (Date.parse(CUTOFF + 'T12:00:00Z') - Date.parse(a.date + 'T12:00:00Z')) / 86400000 > 30);
        const rejected = m.requested.filter(r => effectiveStatus(r) === 'Rechazado');
        const priorities = [
            ['bx-calendar-exclamation', `${oldest.length} anticipos con más de 30 días`, `${money(oldest.reduce((n, a) => n + advanceBalance(a), 0))} por comprobar. Antigüedad informativa, no fecha de vencimiento.`],
            ['bx-wallet', `${m.refunds.length} reembolsos listos para pago`, `${money(sum(m.refunds))} con validación y aprobación completas.`],
            ['bx-revision', `${rejected.length} solicitudes rechazadas`, 'Requieren revisión del motivo y corrección por el solicitante.']
        ];
        $('ecs-priorities').innerHTML = priorities.map(([icon, title, text]) => `<div class="ecs-priority"><span class="ecs-priority-icon"><i class="bx ${icon}" aria-hidden="true"></i></span><div><strong>${title}</strong><p>${text}</p></div></div>`).join('');
    }
    function renderTable() {
        const rows = tableRows(), pages = Math.max(1, Math.ceil(rows.length / state.size));
        state.page = Math.min(state.page, pages);
        const start = (state.page - 1) * state.size;
        $('ecs-count').textContent = rows.length;
        $('ecs-table-body').innerHTML = rows.slice(start, start + state.size).map(row => `<tr>
            <td><span class="ecs-folio">${esc(row.folio)}</span><span class="ecs-sub">${dateLabel(row.date)}</span></td>
            <td><div class="ecs-person"><span class="ecs-avatar">${initials(row.person.name)}</span><div><strong>${esc(row.person.name)}</strong><span class="ecs-sub">${esc(row.person.area)}</span></div></div></td>
            <td>${badge(TYPES[row.operation].label, TYPES[row.operation].color)}</td><td>${esc(row.subtype)}</td>
            <td><span class="ecs-concept" tabindex="0" title="${esc(row.concept)}">${esc(row.concept)}</span></td><td class="ecs-amount">${money(row.amount)}</td>
            <td>${badge(effectiveStatus(row))}</td><td>${badge(treasury(row))}</td><td><button type="button" class="ecs-button ecs-button-light ecs-detail-button" data-detail="${row.id}" aria-label="Ver detalle de ${esc(row.folio)}"><i class="bx bx-show" aria-hidden="true"></i></button></td>
            </tr>`).join('') || '<tr><td colspan="9" class="ecs-empty">Sin movimientos para los filtros seleccionados.</td></tr>';
        $('ecs-page-label').textContent = rows.length ? `Mostrando ${start + 1}–${Math.min(start + state.size, rows.length)} de ${rows.length} movimientos` : '0 movimientos';
        $('ecs-page').textContent = `${state.page} / ${pages}`;
        $('ecs-prev').disabled = state.page <= 1;
        $('ecs-next').disabled = state.page >= pages;
        $('ecs-export').disabled = rows.length === 0;
    }
    function renderAll() {
        const rows = scopedRows();
        const period = state.period === 'month' ? 'Octubre 2026' : state.period === 'quarter' ? 'Agosto — octubre 2026' : 'Enero — octubre 2026';
        $('ecs-scope').textContent = `${period} · ${state.area === 'all' ? 'Todas las áreas' : state.area}`;
        renderKpis(rows); renderTrend(rows); renderCategories(rows); renderAreas(rows); renderStatuses(rows); renderPriorities(rows); renderTable();
    }
    function updatePeople() {
        const people = PEOPLE.filter(p => state.area === 'all' || p.area === state.area);
        if (!people.some(p => String(p.id) === state.person)) state.person = 'all';
        $('ecs-person').innerHTML = '<option value="all">Todos los colaboradores</option>' + people.map(p => `<option value="${p.id}">${esc(p.name)}</option>`).join('');
        $('ecs-person').value = state.person;
    }
    function showDetail(id) {
        const row = DATA.find(r => r.id === Number(id));
        if (!row) return;
        const advance = DATA.find(r => r.id === row.advanceId);
        const items = [ ['Folio', `<span class="ecs-folio">${esc(row.folio)}</span>`], ['Fecha', dateLabel(row.date)],
            ['Colaborador', esc(row.person.name)], ['Departamento', esc(row.person.area)],
            ['Operación', badge(TYPES[row.operation].label, TYPES[row.operation].color)], ['Tipo', esc(row.subtype)],
            ['Monto', money(row.amount)], ['Estado', badge(effectiveStatus(row))], ['Tesorería', badge(treasury(row))],
            ['Anticipo relacionado', advance ? esc(advance.folio) : 'No aplica'] ];
        if (row.operation === 'anticipo') items.push(['Saldo por comprobar', money(advanceBalance(row))]);
        if (row.leftover) items.push(['Devolución de sobrante incluida', money(row.leftover)]);
        $('ecs-detail-title').textContent = row.folio;
        $('ecs-detail-body').innerHTML = `<dl class="ecs-detail-grid">${items.map(([label, value]) => `<div><dt>${label}</dt><dd>${value}</dd></div>`).join('')}<div class="ecs-detail-wide"><dt>Concepto completo</dt><dd>${esc(row.concept)}</dd></div><div class="ecs-detail-wide"><dt>Origen</dt><dd>Datos ficticios generados en JavaScript. Este detalle no modifica registros.</dd></div></dl>`;
        $('ecs-detail').showModal();
    }
    function exportCsv() {
        const cell = value => '"' + String(value).replace(/^[=+@\-]/, match => "'" + match).replace(/"/g, '""') + '"';
        const records = [['Folio', 'Fecha', 'Colaborador', 'Departamento', 'Operación', 'Tipo', 'Concepto', 'Monto MXN', 'Estado', 'Tesorería'],
            ...tableRows().map(r => [r.folio, r.date, r.person.name, r.person.area, TYPES[r.operation].label, r.subtype, r.concept, (r.amount / 100).toFixed(2), effectiveStatus(r), treasury(r)])];
        const blob = new Blob(['\uFEFF' + records.map(row => row.map(cell).join(',')).join('\r\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob), link = document.createElement('a');
        link.href = url; link.download = `estadisticas-gastos-demo-${state.period}-${CUTOFF}.csv`;
        document.body.appendChild(link); link.click(); link.remove();
        setTimeout(() => URL.revokeObjectURL(url), 1000);
    }
    function init() {
        if (!$('ecs-dashboard')) return;
        $('ecs-area').innerHTML += [...new Set(PEOPLE.map(p => p.area))].sort().map(area => `<option value="${esc(area)}">${esc(area)}</option>`).join('');
        updatePeople();
        ['period', 'area', 'person'].forEach(key => $('ecs-' + key).addEventListener('change', event => {
            state[key] = event.target.value; state.page = 1;
            if (key === 'area') updatePeople();
            renderAll();
        }));
        $('ecs-reset').addEventListener('click', () => {
            Object.assign(state, { period: 'quarter', area: 'all', person: 'all', query: '', type: 'all', page: 1, size: 8 });
            ['period', 'area', 'type', 'size'].forEach(key => { $('ecs-' + key).value = state[key]; });
            $('ecs-search').value = ''; updatePeople(); renderAll();
        });
        $('ecs-search').addEventListener('input', event => { state.query = event.target.value.trim(); state.page = 1; renderTable(); });
        $('ecs-type').addEventListener('change', event => { state.type = event.target.value; state.page = 1; renderTable(); });
        $('ecs-size').addEventListener('change', event => { state.size = Number(event.target.value); state.page = 1; renderTable(); });
        $('ecs-prev').addEventListener('click', () => { state.page = Math.max(1, state.page - 1); renderTable(); });
        $('ecs-next').addEventListener('click', () => { state.page++; renderTable(); });
        $('ecs-table-body').addEventListener('click', event => { const button = event.target.closest('[data-detail]'); if (button) showDetail(button.dataset.detail); });
        $('ecs-close').addEventListener('click', () => $('ecs-detail').close());
        $('ecs-detail').addEventListener('click', event => { if (event.target === $('ecs-detail')) { const b = $('ecs-detail').getBoundingClientRect(); if (event.clientX < b.left || event.clientX > b.right || event.clientY < b.top || event.clientY > b.bottom) $('ecs-detail').close(); } });
        $('ecs-export').addEventListener('click', exportCsv);
        renderAll();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
