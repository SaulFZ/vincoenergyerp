(() => {
    'use strict';

    /* ════════════════════════════════════════════════════════════════
       CONFIGURACIÓN
       ════════════════════════════════════════════════════════════════ */
    const CFG        = window.AP_CONFIG || {};
    const EMPLOYEES  = CFG.employees || [];
    const URL_AP     = CFG.urlAP || '';
    const URL_ADJUST = CFG.urlAdjust || '';
    const CSRF       = CFG.csrf || '';

    const MOVEMENTS = {
        Abono_Retencion: { label: 'Retención de sobrante', icon: 'bx bx-slider-alt', sign: 1 }
    };

    const STATE = {
        page: 1, size: '5', q: '', filter: 'all',
        kxUid: null, kxGroups: [], kxPage: 1, kxSize: '3'
    };

    /* ════════════════════════════════════════════════════════════════
       UTILIDADES
       ════════════════════════════════════════════════════════════════ */
    const fmt  = n => new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(Number(n) || 0);
    const num  = v => Number(v) || 0;
    const norm = v => String(v ?? '').trim();
    const esc  = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const humanize = s => String(s || '').replace(/_/g, ' ');
    const findEmp  = id => EMPLOYEES.find(e => Number(e.user_id) === Number(id));

    const initials = name => {
        if (!name) return 'U';
        const w = name.trim().split(/\s+/).filter(Boolean);
        return w.length > 1 ? (w[0][0] + w[1][0]).toUpperCase() : name.substring(0, 2).toUpperCase();
    };

    function formatDate(v) {
        if (!v) return '—';
        const s = String(v);
        const iso = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (iso) return `${iso[3]}/${iso[2]}/${iso[1]}`;
        if (/^\d{2}\/\d{2}\/\d{4}/.test(s)) return s.split(' ')[0];
        return s;
    }

    function toTime(v) {
        if (v == null || v === '') return 0;
        if (typeof v === 'number') return v;
        const s = String(v);
        const dmy = s.match(/^(\d{2})\/(\d{2})\/(\d{4})/);
        if (dmy) return new Date(+dmy[3], +dmy[2] - 1, +dmy[1]).getTime() / 1000;
        const t = Date.parse(s);
        return isNaN(t) ? 0 : t / 1000;
    }
    const timeOf = m => toTime(m.fecha_raw ?? m.fecha);
    const byTime = (a, b) => timeOf(a) - timeOf(b) || Number(a.id) - Number(b.id);

    const linkId = m => {
        const v = m.advance_id ?? m.related_advance_id;
        return v == null || v === '' ? null : Number(v);
    };

    /* ════════════════════════════════════════════════════════════════
       ESTADOS
       status_review (revisión) + status_approval (aprobación).
       Tras "Validado", la aprobación puede ser Aprobado o Rechazado.
       ════════════════════════════════════════════════════════════════ */
    const FINAL_STATES = ['Aprobado', 'Rechazado'];

    function effStatusOf(status, approval) {
        const a = norm(approval), s = norm(status);
        if (FINAL_STATES.includes(a)) return a;
        if (FINAL_STATES.includes(s)) return s;
        return s || a || 'Pendiente';
    }
    const effStatus  = m => effStatusOf(m.status, m.status_approval);
    const isApproved = m => effStatus(m) === 'Aprobado';
    const isRejected = m => effStatus(m) === 'Rechazado';
    const isPending  = m => ['Pendiente', 'Validado'].includes(effStatus(m));
    const isPaid     = m => norm(m.status_payment).toLowerCase() === 'pagado';
    const isSettled  = a => norm(a.settlement_status).toLowerCase() === 'comprobado';
    const isPayable  = m => m.origen === 'reembolso' && isApproved(m) && !isPaid(m);
    const direction  = m => m.signo === 'abono' ? 'abono' : 'cargo';

    const STATUS_MAP = {
        pendiente:     { c: 'st-wait',  i: 'bx bx-hourglass',     l: 'Pendiente' },
        validado:      { c: 'st-rev',   i: 'bx bx-list-check',    l: 'Validado' },
        aprobado:      { c: 'st-ok',    i: 'bx bx-check-shield',  l: 'Aprobado' },
        rechazado:     { c: 'st-fail',  i: 'bx bx-x-circle',      l: 'Rechazado' },
        en_espera:     { c: 'st-wait',  i: 'bx bx-time-five',     l: 'En espera' },
        en_revision:   { c: 'st-rev',   i: 'bx bx-search',        l: 'En revisión' },
        borrador:      { c: 'st-draft', i: 'bx bx-edit-alt',      l: 'Borrador' },
        consolidado:   { c: 'st-ok',    i: 'bx bx-archive',       l: 'Consolidado' },
        comprobado:    { c: 'st-ok',    i: 'bx bx-check-double',  l: 'Comprobado' },
        no_comprobado: { c: 'st-mute',  i: 'bx bx-minus-circle',  l: 'No comprobado' },
        comprobando:   { c: 'st-proc',  i: 'bx bx-loader-circle', l: 'Comprobando' },
        retenido:      { c: 'st-proc',  i: 'bx bx-lock-alt',      l: 'Retenido' },
        pagado:        { c: 'st-ok',    i: 'bx bx-check-circle',  l: 'Pagado' },
        no_pagado:     { c: 'st-wait',  i: 'bx bx-time',          l: 'No pagado' },
        no_aplica:     { c: 'st-mute',  i: 'bx bx-minus',         l: 'N/A' },
        n_a:           { c: 'st-mute',  i: 'bx bx-minus',         l: 'N/A' }
    };

    function statusBadge(s) {
        const key = norm(s).toLowerCase().replace(/\s+/g, '_');
        const c = STATUS_MAP[key] || { c: 'st-mute', i: 'bx bx-minus', l: norm(s) || '—' };
        return `<span class="st-badge ${c.c}"><i class="${c.i}"></i>${esc(c.l)}</span>`;
    }

    /* ════════════════════════════════════════════════════════════════
       OPERACIÓN / TIPO / PAGO-COMPROBACIÓN / CONCEPTO / FOLIO
       ════════════════════════════════════════════════════════════════ */
    function opInfo(m) {
        if (m.origen === 'anticipo')  return { cls: 'op-anticipo',   icon: 'bx bx-up-arrow-circle', label: 'Anticipo' };
        if (m.origen === 'reembolso') return linkId(m) != null
            ? { cls: 'op-excedente', icon: 'bx bx-link',           label: 'Reembolso excedente' }
            : { cls: 'op-reembolso', icon: 'bx bx-money-withdraw', label: 'Reembolso' };
        if (m.origen === 'comprobacion') return m.subtipo === 'electronica'
            ? { cls: 'op-electronica', icon: 'bx bx-credit-card', label: 'Comprobación electrónica' }
            : { cls: 'op-directa',     icon: 'bx bx-transfer',    label: 'Comprobación directa' };
        if (m.origen === 'sobrante')  return { cls: 'op-sobrante', icon: 'bx bx-coin-stack', label: 'Sobrante' };
        return {
            cls: 'op-retencion',
            icon: (MOVEMENTS[m.tipo] || {}).icon || 'bx bx-slider-alt',
            label: (MOVEMENTS[m.tipo] || {}).label || humanize(m.tipo)
        };
    }

    const opBadge = m => {
        const o = opInfo(m);
        return `<span class="op-badge ${o.cls}"><i class="${o.icon}"></i>${esc(o.label)}</span>`;
    };

    function tipoText(m) {
        if (m.origen === 'anticipo')     return m.tipo || '—';
        if (m.origen === 'reembolso')    return linkId(m) != null ? 'Excedente de anticipo' : 'Directo';
        if (m.origen === 'comprobacion') return m.metodo || '—';
        if (m.origen === 'sobrante')     return m.metodo || '—';
        return 'Libro mayor';
    }

    /* Comprobación / pago según el tipo de movimiento */
    function payBadge(m) {
        if (m.origen === 'reembolso')    return statusBadge(m.status_payment || 'no_aplica');
        if (m.origen === 'anticipo')     return statusBadge(m.settlement_status || 'no_comprobado');
        if (m.origen === 'comprobacion') return statusBadge(isApproved(m) ? 'comprobado' : 'no_comprobado');
        if (m.origen === 'sobrante')     return statusBadge(m.has_proof ? 'pagado' : 'no_pagado');
        if (m.origen === 'ajuste_balance') return statusBadge('retenido');
        return '<span class="c-mute">—</span>';
    }

    const folioCell = f => `<span class="folio">${esc(f)}</span>`;

    const concept = text => {
        const t = text || 'Sin descripción';
        return `<span class="cc" data-full="${esc(t)}" tabindex="0">${esc(t)}</span>`;
    };

    /* ════════════════════════════════════════════════════════════════
       CÁLCULOS
       ════════════════════════════════════════════════════════════════ */
    function advanceSummary(emp, adv) {
        const kids = emp.movimientos
            .filter(x => x.origen !== 'anticipo' && linkId(x) === Number(adv.id))
            .sort(byTime);

        const comprobado = kids
            .filter(x => x.origen === 'comprobacion' && isApproved(x))
            .reduce((s, x) => s + num(x.monto), 0);

        const devuelto = kids
            .filter(x => x.origen === 'comprobacion' && isApproved(x) && x.leftover && x.leftover.has_proof)
            .reduce((s, x) => s + num(x.leftover.amount), 0);

        const retenido = kids
            .filter(x => x.origen === 'ajuste_balance')
            .reduce((s, x) => s + num(x.monto), 0);

        const saldo = num(adv.monto) - comprobado - devuelto - retenido;

        return {
            kids, comprobado, devuelto, retenido, saldo,
            pendiente: Math.max(0, saldo),
            excedente: Math.max(0, -saldo)
        };
    }

    const activeAdvances = emp =>
        emp.movimientos.filter(m => m.origen === 'anticipo' && !isRejected(m) && !isSettled(m));

    function enrichEmployee(e) {
        const movs = e.movimientos || [];
        const advs = activeAdvances(e);
        const payables = movs.filter(isPayable);

        e._pend        = movs.filter(m => m.origen !== 'ajuste_balance' && isPending(m)).length;
        e._advBal      = advs.reduce((s, a) => s + advanceSummary(e, a).pendiente, 0);
        e._advN        = advs.filter(a => advanceSummary(e, a).pendiente > 0).length;
        e._reembPagar  = payables.reduce((s, m) => s + num(m.monto), 0);
        e._reembPagarN = payables.length;
        e._nAnt        = movs.filter(m => m.origen === 'anticipo' && !isRejected(m)).length;
        e._nComp       = movs.filter(m => m.origen === 'comprobacion' && !isRejected(m)).length;
        e._nReemb      = movs.filter(m => m.origen === 'reembolso' && !isRejected(m)).length;
        return e;
    }

    /* ════════════════════════════════════════════════════════════════
       MODALES
       ════════════════════════════════════════════════════════════════ */
    function openModal(id) {
        document.getElementById(id).classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
        if (!document.querySelector('.md-overlay.active')) document.body.style.overflow = '';
    }

    function setTitle(id, html) {
        const el = document.querySelector(`#${id} .md-title`);
        if (el) el.innerHTML = html;
    }

    /* ════════════════════════════════════════════════════════════════
       MÉTRICAS Y TABLA PRINCIPAL
       ════════════════════════════════════════════════════════════════ */
    function initMetrics() {
        let pend = 0, adv = 0, advN = 0, reemb = 0, reembN = 0, net = 0;
        EMPLOYEES.forEach(e => {
            pend  += e._pend;
            adv   += e._advBal; if (e._advBal > 0) advN++;
            reemb += e._reembPagar; reembN += e._reembPagarN;
            net   += num(e.saldo_corriente);
        });
        document.getElementById('m-pending').textContent = pend;
        document.getElementById('m-adv').textContent = fmt(adv);
        document.getElementById('m-adv-hint').textContent = `${advN} colaborador(es) con saldo`;
        document.getElementById('m-reemb').textContent = fmt(reemb);
        document.getElementById('m-reemb-hint').textContent = `${reembN} reembolso(s) por pagar`;
        const netEl = document.getElementById('m-net');
        netEl.textContent = fmt(net);
        netEl.classList.toggle('c-abono', net > 0);
        netEl.classList.toggle('c-cargo', net < 0);
        document.getElementById('ap-count').textContent = EMPLOYEES.length;
    }

    const FILTERS = {
        all:        () => true,
        pendiente:  e => e._pend > 0,
        anticipos:  e => e._advBal > 0,
        reembolsos: e => e._reembPagar > 0
    };

    function buildPager(container, totalPages, current, onChange) {
        container.innerHTML = '';
        if (totalPages <= 1) return;

        const add = (html, page, o = {}) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = `pg ${o.active ? 'active' : ''}`;
            b.innerHTML = html;
            b.disabled = !!o.disabled;
            if (!o.disabled && !o.active) b.onclick = () => onChange(page);
            container.appendChild(b);
        };

        add('<i class="bx bx-chevron-left"></i>', current - 1, { disabled: current === 1 });

        const pages = [];
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || Math.abs(i - current) <= 1) pages.push(i);
            else if (pages[pages.length - 1] !== '…') pages.push('…');
        }
        pages.forEach(p => {
            if (p === '…') {
                const s = document.createElement('span');
                s.className = 'pg-gap';
                s.textContent = '…';
                container.appendChild(s);
            } else {
                add(p, p, { active: p === current });
            }
        });

        add('<i class="bx bx-chevron-right"></i>', current + 1, { disabled: current === totalPages });
    }

    function renderTable() {
        const tbody = document.getElementById('ap-tbody');
        const empty = document.getElementById('ap-empty');
        const pager = document.getElementById('ap-pager');
        const pred  = FILTERS[STATE.filter] || FILTERS.all;

        const filtered = EMPLOYEES.filter(e =>
            (!STATE.q || e.nombre.toLowerCase().includes(STATE.q) || (e.depto || '').toLowerCase().includes(STATE.q)) && pred(e)
        );

        document.getElementById('ap-footer-lbl').textContent = `${filtered.length} colaborador(es)`;

        if (!filtered.length) {
            tbody.innerHTML = '';
            pager.innerHTML = '';
            empty.classList.remove('hidden');
            return;
        }
        empty.classList.add('hidden');

        const perPage    = STATE.size === 'all' ? filtered.length : parseInt(STATE.size, 10);
        const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
        if (STATE.page > totalPages) STATE.page = totalPages;
        const start = (STATE.page - 1) * perPage;

        tbody.innerHTML = filtered.slice(start, start + perPage).map(e => {
            const bal    = num(e.saldo_corriente);
            const balTxt = bal > 0 ? '+ ' + fmt(bal) : (bal < 0 ? fmt(bal) : '$0.00');
            const balCls = bal > 0 ? 'c-abono' : (bal < 0 ? 'c-cargo' : 'c-mute');

            return `
            <tr id="row-${e.user_id}" class="emp-row" data-uid="${e.user_id}">
                <td class="m-td"><div class="ap-toggle"><i class="bx bx-chevron-right"></i></div></td>
                <td class="m-td">
                    <div class="ap-user">
                        <div class="ap-avatar">${esc(initials(e.nombre))}</div>
                        <div>
                            <span class="ap-name">${esc(e.nombre)}</span>
                            ${e.ultimo_movimiento ? `<span class="ap-last">Último mov.: ${esc(e.ultimo_movimiento)}</span>` : ''}
                        </div>
                    </div>
                </td>
                <td class="m-td"><span class="ap-depto">${esc(e.depto)}</span></td>
                <td class="m-td t-center">
                    <div class="ap-tram">
                        <span class="ap-pill ${e._pend ? '' : 'zero'}" title="Trámites pendientes o validados">${e._pend}</span>
                        <div class="ap-chips">
                            <span class="ap-chip" title="Anticipos"><i class="bx bx-up-arrow-circle"></i>${e._nAnt}</span>
                            <span class="ap-chip" title="Comprobaciones"><i class="bx bx-check-double"></i>${e._nComp}</span>
                            <span class="ap-chip" title="Reembolsos"><i class="bx bx-money"></i>${e._nReemb}</span>
                        </div>
                    </div>
                </td>
                <td class="m-td t-right"><span class="ap-money ${e._advBal > 0 ? 'c-cargo' : 'c-mute'}">${fmt(e._advBal)}</span></td>
                <td class="m-td t-right"><span class="ap-money ${e._reembPagar > 0 ? 'navy' : 'c-mute'}">${fmt(e._reembPagar)}</span></td>
                <td class="m-td t-right"><span class="ap-money ${balCls}">${balTxt}</span></td>
                <td class="m-td">
                    <div class="ap-actions">
                        <button type="button" class="ap-btn ap-btn-kardex" data-action="kardex" data-uid="${e.user_id}" title="Kardex de anticipos y comprobaciones">
                            <i class="bx bx-receipt"></i> Kardex
                        </button>
                        <button type="button" class="ap-btn ap-btn-reemb ${e._nReemb ? '' : 'off'}" data-action="reembolsos" data-uid="${e.user_id}" title="${e._nReemb ? 'Revisar o pagar reembolsos' : 'Sin reembolsos'}">
                            <i class="bx bx-money"></i> Reembolsos
                            ${e._reembPagarN ? `<span class="ap-btn-badge">${e._reembPagarN}</span>` : ''}
                        </button>
                    </div>
                </td>
            </tr>
            <tr id="det-${e.user_id}" class="det-row hidden">
                <td colspan="8">
                    <div class="det-box">
                        <div class="det-head">
                            <h4><i class="bx bx-layer"></i> Anticipos, comprobaciones, reembolsos y ajustes</h4>
                            <span class="det-count">${e.movimientos.length} registro(s)</span>
                        </div>
                        <div class="det-filters">
                            <div class="det-field grow">
                                <i class="bx bx-search"></i>
                                <input type="text" id="sub-q-${e.user_id}" data-sub="${e.user_id}" placeholder="Buscar por folio, concepto o vinculación...">
                            </div>
                            <div class="det-field fixed">
                                <i class="bx bx-filter-alt"></i>
                                <select id="sub-t-${e.user_id}" data-sub="${e.user_id}">
                                    <option value="all">Todos los movimientos</option>
                                    <option value="anticipo">Anticipos</option>
                                    <option value="comprobacion_directa">Comprobaciones directas</option>
                                    <option value="comprobacion_electronica">Comprobaciones electrónicas</option>
                                    <option value="reembolso">Reembolsos</option>
                                    <option value="ajuste_balance">Retenciones de sobrante</option>
                                </select>
                            </div>
                        </div>
                        <div class="det-table-wrap">
                            <table class="det-table">
                                <thead>
                                    <tr>
                                        <th>Folio</th>
                                        <th>Fecha</th>
                                        <th>Concepto</th>
                                        <th>Operación</th>
                                        <th>Tipo</th>
                                        <th>Vínculo</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Comprobación / Pago</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="sub-body-${e.user_id}">${renderSubRows(e, '', 'all')}</tbody>
                            </table>
                        </div>
                    </div>
                </td>
            </tr>`;
        }).join('');

        buildPager(pager, totalPages, STATE.page, p => { STATE.page = p; renderTable(); });
    }

    function toggleAcc(uid) {
        const det = document.getElementById(`det-${uid}`);
        const row = document.getElementById(`row-${uid}`);
        if (!det || !row) return;

        document.querySelectorAll('.det-row:not(.hidden)').forEach(r => {
            if (r.id === `det-${uid}`) return;
            r.classList.add('hidden');
            document.getElementById(`row-${r.id.replace('det-', '')}`)?.classList.remove('expanded');
        });

        const open = det.classList.contains('hidden');
        det.classList.toggle('hidden', !open);
        row.classList.toggle('expanded', open);
        row.querySelector('.ap-toggle i')?.classList.toggle('rot', open);
    }

    /* ════════════════════════════════════════════════════════════════
       ACORDEÓN: MOVIMIENTOS DEL COLABORADOR
       ════════════════════════════════════════════════════════════════ */
    const subKey = m => m.origen === 'comprobacion' ? `comprobacion_${m.subtipo}` : m.origen;

    function subRow(emp, m) {
        const dir  = direction(m);
        const sign = dir === 'cargo' ? '+' : '−';

        const action = m.origen === 'anticipo'
            ? `<button type="button" class="ic-btn" data-action="advance" data-uid="${emp.user_id}" data-id="${m.id}" title="Ver desglose"><i class="bx bx-list-ul"></i></button>`
            : '<span class="c-mute">—</span>';

        const vinculo = m.origen === 'anticipo'
            ? '<span class="c-mute">—</span>'
            : (m.vinculo && !['General', 'Sin anticipo'].includes(m.vinculo)
                ? folioCell(m.vinculo)
                : `<span class="c-mute">${esc(m.vinculo || '—')}</span>`);

        return `
        <tr>
            <td class="c-center">${folioCell(m.folio)}</td>
            <td class="c-center date-cell">${esc(formatDate(m.fecha))}</td>
            <td>${concept(m.descripcion)}</td>
            <td class="c-center">${opBadge(m)}</td>
            <td class="c-center"><span class="tipo-txt">${esc(tipoText(m))}</span></td>
            <td class="c-center">${vinculo}</td>
            <td class="t-right"><span class="amt ${dir}">${sign} ${fmt(m.monto)}</span></td>
            <td class="c-center">${statusBadge(effStatus(m))}</td>
            <td class="c-center">${payBadge(m)}</td>
            <td class="c-center">${action}</td>
        </tr>`;
    }

    function renderSubRows(emp, q, type) {
        const list = emp.movimientos
            .filter(m => {
                const okType = type === 'all' || subKey(m) === type;
                const okQ = !q || [m.folio, m.descripcion, m.vinculo, m.tipo].some(v => String(v || '').toLowerCase().includes(q));
                return okType && okQ;
            })
            .sort((a, b) => byTime(b, a));

        if (!list.length) {
            return '<tr><td colspan="10" class="det-empty"><i class="bx bx-search-alt"></i>Sin registros</td></tr>';
        }
        return list.map(m => subRow(emp, m)).join('');
    }

    function applySubFilter(uid) {
        const emp  = findEmp(uid);
        const body = document.getElementById(`sub-body-${uid}`);
        if (!emp || !body) return;
        const q = (document.getElementById(`sub-q-${uid}`)?.value || '').toLowerCase().trim();
        const t = document.getElementById(`sub-t-${uid}`)?.value || 'all';
        body.innerHTML = renderSubRows(emp, q, t);
    }

    /* ════════════════════════════════════════════════════════════════
       KARDEX
       Grupos por anticipo (más reciente primero). Dentro de cada grupo:
       comprobaciones (con su sobrante debajo), retenciones,
       reembolsos excedentes y al final la fila de resumen.
       ════════════════════════════════════════════════════════════════ */
    function sobranteOf(k) {
        const lo = k.leftover;
        return {
            origen: 'sobrante',
            id: `s${k.id}`,
            folio: k.folio,
            fecha: k.fecha,
            fecha_raw: k.fecha_raw,
            tipo: 'Sobrante',
            metodo: lo.method || '—',
            descripcion: lo.notes || `Sobrante de ${k.folio}`,
            monto: num(lo.amount),
            has_proof: !!lo.has_proof,
            signo: 'abono'
        };
    }

    function pushWithLeftover(rows, k) {
        rows.push({ type: 'child', m: k });
        if (k.origen === 'comprobacion' && k.leftover) {
            rows.push({ type: 'child', m: sobranteOf(k) });
        }
    }

    function buildGroups(emp) {
        const advances = emp.movimientos
            .filter(m => m.origen === 'anticipo')
            .sort((a, b) => byTime(b, a));

        const advIds = new Set(advances.map(a => Number(a.id)));

        const groups = advances.map(adv => {
            const s    = advanceSummary(emp, adv);
            const kids = s.kids.slice().sort(byTime);
            const rows = [{ type: 'parent', m: adv, s }];

            kids.filter(k => k.origen !== 'reembolso').forEach(k => pushWithLeftover(rows, k));
            kids.filter(k => k.origen === 'reembolso').forEach(k => rows.push({ type: 'child', m: k }));
            rows.push({ type: 'summary', m: adv, s });

            return { rows };
        });

        const orphans = emp.movimientos
            .filter(m => (m.origen === 'comprobacion' || m.origen === 'ajuste_balance') && !advIds.has(linkId(m)))
            .sort(byTime);

        if (orphans.length) {
            const rows = [];
            orphans.forEach(k => pushWithLeftover(rows, k));
            groups.push({ label: 'Sin anticipo vinculado', rows });
        }

        return groups;
    }

    function kardexTotals(emp) {
        let anticipado = 0, comprobado = 0, pendiente = 0;
        emp.movimientos.forEach(m => {
            if (m.origen === 'anticipo' && !isRejected(m)) anticipado += num(m.monto);
            if (m.origen === 'comprobacion' && isApproved(m)) comprobado += num(m.monto);
        });
        activeAdvances(emp).forEach(a => { pendiente += advanceSummary(emp, a).pendiente; });
        return { anticipado, comprobado, pendiente };
    }

    function kxParent(m, s, uid) {
        const acts = [
            `<button type="button" class="ic-btn" data-action="advance" data-uid="${uid}" data-id="${m.id}" title="Ver desglose"><i class="bx bx-list-ul"></i></button>`
        ];
        if (!isSettled(m) && !isRejected(m)) {
            acts.push(`<button type="button" class="ic-btn ok" data-action="settle" data-id="${m.id}" title="Marcar como comprobado"><i class="bx bx-check-double"></i></button>`);
        }
        if (s.pendiente > 0 && !isRejected(m) && !isSettled(m)) {
            acts.push(`<button type="button" class="ic-btn warn" data-action="ajuste" data-uid="${uid}" data-advid="${m.id}" title="Retener sobrante"><i class="bx bx-slider-alt"></i></button>`);
        }

        return `
        <tr class="kx-parent">
            <td class="c-date">${esc(formatDate(m.fecha))}</td>
            <td class="c-center">${folioCell(m.folio)}</td>
            <td class="c-center">${opBadge(m)}</td>
            <td class="c-center"><span class="tipo-txt">${esc(tipoText(m))}</span></td>
            <td>${concept(m.descripcion)}</td>
            <td class="num cargo">${fmt(m.monto)}</td>
            <td class="num"></td>
            <td class="num"></td>
            <td class="c-center">${statusBadge(effStatus(m))}</td>
            <td class="c-center">${payBadge(m)}</td>
            <td class="c-center"><div class="acc">${acts.join('')}</div></td>
        </tr>`;
    }

    function kxSummary(s) {
        const exc = s.excedente > 0;
        const valCls = exc ? 'exc-txt' : (s.pendiente > 0 ? 'pend-txt' : 'ok-txt');
        return `
        <tr class="kx-summary">
            <td colspan="5"><span class="sum-title"><i class="bx bx-calculator"></i> Resumen del anticipo</span></td>
            <td class="num"></td>
            <td class="num">
                <span class="lbl">Comprobado</span>
                <strong class="abono-txt">${fmt(s.comprobado)}</strong>
            </td>
            <td class="num">
                <span class="lbl">${exc ? 'Excedente' : 'Por comprobar'}</span>
                <strong class="${valCls}">${fmt(exc ? s.excedente : s.pendiente)}</strong>
            </td>
            <td colspan="3"></td>
        </tr>`;
    }

    function kxChild(m) {
        const isRemb = m.origen === 'reembolso';
        const isSob  = m.origen === 'sobrante';
        const dim    = (m.origen === 'comprobacion' && !isApproved(m))
                    || (isSob && !m.has_proof)
                    || (isRemb && isRejected(m));
        const cls    = ['kx-child', isRemb ? 'is-remb' : '', isSob ? 'is-sob' : '', dim ? 'kx-dim' : ''].join(' ').trim();
        const estado = isSob ? '<span class="c-mute">—</span>' : statusBadge(effStatus(m));

        return `
        <tr class="${cls}">
            <td class="c-date"><i class="bx bx-subdirectory-right kx-branch"></i>${esc(formatDate(m.fecha))}</td>
            <td class="c-center">${folioCell(m.folio)}</td>
            <td class="c-center">${opBadge(m)}</td>
            <td class="c-center"><span class="tipo-txt">${esc(tipoText(m))}</span></td>
            <td>${concept(m.descripcion)}</td>
            <td class="num cargo">${isRemb ? fmt(m.monto) : ''}</td>
            <td class="num abono">${!isRemb ? fmt(m.monto) : ''}</td>
            <td class="num"></td>
            <td class="c-center">${estado}</td>
            <td class="c-center">${payBadge(m)}</td>
            <td></td>
        </tr>`;
    }

    function kxRow(r, uid) {
        if (r.type === 'parent')  return kxParent(r.m, r.s, uid);
        if (r.type === 'summary') return kxSummary(r.s);
        return kxChild(r.m);
    }

    function renderKardex() {
        const emp = findEmp(STATE.kxUid);
        if (!emp) return;

        const body   = document.getElementById('md-kardex-body');
        const groups = STATE.kxGroups;

        const perPage    = STATE.kxSize === 'all' ? Math.max(groups.length, 1) : parseInt(STATE.kxSize, 10);
        const totalPages = Math.max(1, Math.ceil(groups.length / perPage));
        if (STATE.kxPage > totalPages) STATE.kxPage = totalPages;
        const start      = (STATE.kxPage - 1) * perPage;
        const pageGroups = groups.slice(start, start + perPage);

        const t      = kardexTotals(emp);
        const saldo  = num(emp.saldo_corriente);
        const balCls = saldo > 0 ? 'ok' : (saldo < 0 ? 'danger' : '');

        let rows = '';
        pageGroups.forEach(g => {
            if (g.label) rows += `<tr class="kx-group"><td colspan="11">${esc(g.label)}</td></tr>`;
            g.rows.forEach(r => { rows += kxRow(r, emp.user_id); });
        });
        if (!pageGroups.length) {
            rows = '<tr><td colspan="11" class="kx-empty">Sin asientos contables registrados.</td></tr>';
        }

        body.innerHTML = `
        <div class="kx-head">
            <div class="kx-user">
                <div class="kx-avatar">${esc(initials(emp.nombre))}</div>
                <div>
                    <div class="kx-user-name">${esc(emp.nombre)}</div>
                    <div class="kx-user-depto"><i class="bx bx-buildings"></i> ${esc(emp.depto)}</div>
                </div>
            </div>
            <div class="kx-stats">
                <div class="kx-stat accent"><span class="lbl">Anticipado</span><span class="val">${fmt(t.anticipado)}</span></div>
                <div class="kx-stat ok"><span class="lbl">Comprobado</span><span class="val">${fmt(t.comprobado)}</span></div>
                <div class="kx-stat pend"><span class="lbl">Por comprobar</span><span class="val">${fmt(t.pendiente)}</span></div>
                <div class="kx-stat ${balCls}"><span class="lbl">Saldo en libro mayor</span><span class="val">${fmt(saldo)}</span></div>
            </div>
        </div>

        <div class="kx-table-wrap">
            <table class="kx-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Folio</th>
                        <th>Operación</th>
                        <th>Tipo</th>
                        <th>Concepto</th>
                        <th>Cargo (+)</th>
                        <th>Abono (−)</th>
                        <th>Saldo por comprobar</th>
                        <th>Estado</th>
                        <th>Comprobación / Pago</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>

        <div class="kx-foot">
            <span class="info">${groups.length} grupo(s) · Página ${STATE.kxPage} de ${totalPages}</span>
            <div class="kx-pager" id="kx-pager"></div>
            <div class="kx-size">
                <span>Anticipos por página</span>
                <select id="kx-size">
                    ${['3', '5', '10'].map(n => `<option value="${n}" ${STATE.kxSize === n ? 'selected' : ''}>${n}</option>`).join('')}
                    <option value="all" ${STATE.kxSize === 'all' ? 'selected' : ''}>Todos</option>
                </select>
            </div>
        </div>
        <p class="kx-note">Los reembolsos sin anticipo no aparecen aquí; se consultan y pagan desde el botón Reembolsos. Las filas atenuadas no afectan el saldo.</p>`;

        buildPager(document.getElementById('kx-pager'), totalPages, STATE.kxPage, p => {
            STATE.kxPage = p;
            renderKardex();
        });
    }

    function openKardex(uid) {
        const emp = findEmp(uid);
        if (!emp) return;
        STATE.kxUid    = emp.user_id;
        STATE.kxGroups = buildGroups(emp);
        STATE.kxPage   = 1;
        renderKardex();
        openModal('md-kardex');
    }

    /* ════════════════════════════════════════════════════════════════
       DESGLOSE DEL ANTICIPO
       ════════════════════════════════════════════════════════════════ */
    function openAdvance(uid, advId) {
        const emp = findEmp(uid);
        const adv = emp && emp.movimientos.find(m => m.origen === 'anticipo' && Number(m.id) === Number(advId));
        if (!adv) return;

        const s    = advanceSummary(emp, adv);
        const kids = s.kids.slice().sort(byTime);

        setTitle('md-advance', `<i class="bx bx-list-ul"></i> Desglose del <strong>${esc(adv.folio)}</strong>`);

        const rows = kids.length ? kids.map(k => {
            const dir  = direction(k);
            const sign = dir === 'cargo' ? '+' : '−';
            return `
            <tr>
                <td class="c-center">${folioCell(k.folio)}</td>
                <td class="c-center date-cell">${esc(formatDate(k.fecha))}</td>
                <td class="c-center">${opBadge(k)}</td>
                <td class="c-center"><span class="tipo-txt">${esc(tipoText(k))}</span></td>
                <td>${concept(k.descripcion)}</td>
                <td class="t-right"><span class="amt ${dir}">${sign} ${fmt(k.monto)}</span></td>
                <td class="c-center">${statusBadge(effStatus(k))}</td>
                <td class="c-center">${payBadge(k)}</td>
            </tr>`;
        }).join('') : '<tr><td colspan="8" class="kx-empty">Sin movimientos registrados para este anticipo.</td></tr>';

        document.getElementById('md-advance-body').innerHTML = `
        <div class="kx-stats">
            <div class="kx-stat accent"><span class="lbl">Monto original</span><span class="val">${fmt(adv.monto)}</span></div>
            <div class="kx-stat pend"><span class="lbl">Por comprobar</span><span class="val">${fmt(s.pendiente)}</span></div>
            <div class="kx-stat"><span class="lbl">Estado</span>${statusBadge(effStatus(adv))}</div>
            <div class="kx-stat"><span class="lbl">Comprobación</span>${statusBadge(adv.settlement_status || 'no_comprobado')}</div>
        </div>

        <div class="sec-title"><i class="bx bx-receipt"></i> Movimientos (${kids.length})</div>
        <div class="kx-table-wrap">
            <table class="kx-table">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Operación</th>
                        <th>Tipo</th>
                        <th>Concepto</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Comprobación / Pago</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>`;

        openModal('md-advance');
    }

    /* ════════════════════════════════════════════════════════════════
       REEMBOLSOS DEL COLABORADOR
       ════════════════════════════════════════════════════════════════ */
    function openReembolsos(uid) {
        const emp = findEmp(uid);
        if (!emp) return;

        const data = emp.movimientos
            .filter(m => m.origen === 'reembolso')
            .sort((a, b) => byTime(b, a));

        const active   = data.filter(r => !isRejected(r));
        const total    = active.reduce((s, r) => s + num(r.monto), 0);
        const porPagar = data.filter(isPayable).reduce((s, r) => s + num(r.monto), 0);
        const pagado   = data.filter(r => isPaid(r) && !isRejected(r)).reduce((s, r) => s + num(r.monto), 0);

        const rows = data.length ? data.map(r => `
            <tr>
                <td class="c-center">${folioCell(r.folio)}</td>
                <td class="c-center date-cell">${esc(formatDate(r.fecha))}</td>
                <td>${concept(r.descripcion)}</td>
                <td class="c-center">${opBadge(r)}</td>
                <td class="c-center"><span class="tipo-txt">${esc(tipoText(r))}</span></td>
                <td class="t-right"><span class="rb-amount">${fmt(r.monto)}</span></td>
                <td class="c-center">${statusBadge(effStatus(r))}</td>
                <td class="c-center">${payBadge(r)}</td>
                <td class="c-center">${isPayable(r)
                    ? `<button type="button" class="btn-pay" data-action="pay" data-id="${r.id}" data-folio="${esc(r.folio)}" data-monto="${num(r.monto)}"><i class="bx bx-dollar-circle"></i> Pagar</button>`
                    : '<span class="c-mute">—</span>'}
                </td>
            </tr>`).join('')
            : '<tr><td colspan="9" class="kx-empty">Este colaborador no tiene reembolsos registrados.</td></tr>';

        document.getElementById('md-reembolsos-body').innerHTML = `
        <div class="kx-head">
            <div class="kx-user">
                <div class="kx-avatar">${esc(initials(emp.nombre))}</div>
                <div>
                    <div class="kx-user-name">${esc(emp.nombre)}</div>
                    <div class="kx-user-depto">${esc(emp.depto)} · Gastos pagados con recursos propios</div>
                </div>
            </div>
            <div class="kx-stats">
                <div class="kx-stat accent"><span class="lbl">Total solicitado</span><span class="val">${fmt(total)}</span></div>
                <div class="kx-stat pend"><span class="lbl">Por pagar</span><span class="val">${fmt(porPagar)}</span></div>
                <div class="kx-stat ok"><span class="lbl">Pagado</span><span class="val">${fmt(pagado)}</span></div>
            </div>
        </div>

        <div class="kx-table-wrap">
            <table class="kx-table">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Motivo</th>
                        <th>Operación</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Pago</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>`;

        openModal('md-reembolsos');
    }

    /* ════════════════════════════════════════════════════════════════
       ACCIONES: PAGO, CIERRE DE ANTICIPO, RETENCIÓN
       ════════════════════════════════════════════════════════════════ */
    async function payReembolso(id, folio, monto) {
        const { value: form } = await Swal.fire({
            title: 'Registrar pago de reembolso',
            html: `
                <div class="swal-pay">
                    <div class="sum"><strong>${esc(folio)}</strong><br>Monto a pagar: <strong>${fmt(monto)}</strong></div>
                    <label for="swal-method">Método de pago</label>
                    <select id="swal-method">
                        <option value="Transferencia">Transferencia</option>
                        <option value="Tarjeta Toka">Tarjeta Toka</option>
                        <option value="Efectivo">Efectivo</option>
                    </select>
                    <label for="swal-ref">Referencia (opcional)</label>
                    <input id="swal-ref" type="text" maxlength="120" placeholder="Clave de rastreo, folio de transferencia...">
                </div>`,
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: 'Confirmar pago',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#152845',
            preConfirm: () => ({
                payment_method: document.getElementById('swal-method').value,
                reference: document.getElementById('swal-ref').value.trim()
            })
        });
        if (!form) return;

        try {
            Swal.fire({ title: 'Registrando pago...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            const res = await fetch(`${URL_AP}/claims/${id}/mark-paid`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify(form)
            });
            const j = await res.json();
            if (j.success) {
                Swal.fire({ icon: 'success', title: 'Pago registrado', text: j.message, confirmButtonColor: '#152845' })
                    .then(() => location.reload());
            } else {
                Swal.fire('No se pudo registrar', j.message || 'Revisa los datos e intenta de nuevo.', 'error');
            }
        } catch (e) {
            Swal.fire('Falla de red', 'No se pudo contactar al servidor.', 'error');
        }
    }

    async function settleAdvance(id) {
        const { isConfirmed } = await Swal.fire({
            title: '¿Cerrar anticipo?',
            html: '<span style="font-size:.9rem;color:#64748b;">Esto marcará el anticipo como <strong>comprobado</strong>. Tesorería ya no esperará comprobantes ni devoluciones para este folio.</span>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Sí, marcar como comprobado',
            cancelButtonText: 'Cancelar'
        });
        if (!isConfirmed) return;

        try {
            Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            const res = await fetch(`${URL_AP}/advances/${id}/settle`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF }
            });
            const j = await res.json();
            if (j.success) {
                Swal.fire({ icon: 'success', title: 'Anticipo cerrado', text: j.message, confirmButtonColor: '#152845' })
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', j.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error de red', 'No se pudo contactar con el servidor.', 'error');
        }
    }

    function openAjuste(uid, advId) {
        const emp = findEmp(uid);
        if (!emp) return;

        const adv     = emp.movimientos.find(m => m.origen === 'anticipo' && Number(m.id) === Number(advId));
        const restante = adv ? Math.max(0, advanceSummary(emp, adv).pendiente) : 0;

        document.getElementById('adj-user-id').value = uid;
        document.getElementById('adj-adv-id').value = adv ? adv.id : '';
        document.getElementById('adj-adv-saldo').value = restante;
        document.getElementById('adj-user-name').textContent = emp.nombre;

        const refWrap = document.getElementById('adj-ref-wrap');
        refWrap.classList.toggle('hidden', !adv);
        if (adv) document.getElementById('adj-ref-folio').textContent = adv.folio;

        document.getElementById('adj-type').innerHTML = Object.entries(MOVEMENTS)
            .map(([k, t]) => `<option value="${k}">${esc(t.label)}</option>`).join('');

        const amountEl = document.getElementById('adj-amount');
        amountEl.value = restante.toFixed(2);
        amountEl.max = restante.toFixed(2);
        document.getElementById('adj-desc').value = '';

        updateAdjHint();
        openModal('md-ajuste');
    }

    function updateAdjHint() {
        const uid = document.getElementById('adj-user-id').value;
        const emp = findEmp(uid);
        const t   = MOVEMENTS[document.getElementById('adj-type').value];
        if (!emp || !t) return;

        const amount    = num(document.getElementById('adj-amount').value);
        const current   = num(emp.saldo_corriente);
        const projected = current + t.sign * amount;

        const box = document.getElementById('adj-effect');
        box.className = 'f-effect up f-group';
        box.innerHTML = `
            <i class="bx bx-trending-up"></i>
            <div>
                Esta retención <strong>sumará ${fmt(amount)}</strong> al saldo en libro mayor.
                <div class="eff-row">
                    <span>Saldo actual <b>${fmt(current)}</b></span>
                    <i class="bx bx-right-arrow-alt"></i>
                    <span>Saldo resultante <b>${fmt(projected)}</b></span>
                </div>
            </div>`;
    }

    async function submitAjuste() {
        const type   = document.getElementById('adj-type').value;
        const amountEl = document.getElementById('adj-amount');
        const max    = num(document.getElementById('adj-adv-saldo').value);
        const amount = num(amountEl.value);
        const desc   = document.getElementById('adj-desc').value.trim();
        const uid    = document.getElementById('adj-user-id').value;
        const advId  = document.getElementById('adj-adv-id').value;

        if (amount <= 0) return Swal.fire('Atención', 'El monto a retener debe ser mayor a cero.', 'warning');
        if (advId && amount > max) return Swal.fire('Atención', `El monto no puede superar el saldo pendiente (${fmt(max)}).`, 'warning');
        if (!desc) return Swal.fire('Atención', 'Es obligatorio detallar el motivo para auditoría.', 'warning');

        try {
            Swal.fire({ title: 'Registrando retención...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            const res = await fetch(URL_ADJUST, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({
                    user_id: uid,
                    movement_type: type,
                    amount,
                    description: desc,
                    expense_advance_id: advId || null
                })
            });
            const j = await res.json();
            if (j.success) {
                closeModal('md-ajuste');
                Swal.fire({ icon: 'success', title: 'Libro mayor actualizado', text: `${j.message} (Folio: ${j.folio})`, confirmButtonColor: '#152845' })
                    .then(() => location.reload());
            } else {
                Swal.fire('Error de asiento', j.message || 'No se pudo registrar la retención.', 'error');
            }
        } catch (e) {
            Swal.fire('Falla de red', 'No se pudo contactar al servidor.', 'error');
        }
    }

    /* ════════════════════════════════════════════════════════════════
       TOOLTIP FLOTANTE PARA CONCEPTOS
       ════════════════════════════════════════════════════════════════ */
    const tip = document.getElementById('cc-tip');
    let tipPinned = false;

    function showTip(el) {
        tip.textContent = el.dataset.full || '';
        tip.classList.add('show');
        const r  = el.getBoundingClientRect();
        const tw = tip.offsetWidth;
        const th = tip.offsetHeight;
        let left = Math.min(Math.max(12, r.left), window.innerWidth - tw - 12);
        let top  = r.bottom + 8;
        if (top + th > window.innerHeight - 12) top = r.top - th - 8;
        tip.style.left = left + 'px';
        tip.style.top  = top + 'px';
    }

    function hideTip() {
        tip.classList.remove('show');
        tipPinned = false;
    }

    function bindTooltip() {
        document.addEventListener('mouseover', e => {
            const el = e.target.closest && e.target.closest('.cc[data-full]');
            if (el && !tipPinned) showTip(el);
        });
        document.addEventListener('mouseout', e => {
            const el = e.target.closest && e.target.closest('.cc[data-full]');
            if (el && !tipPinned && !el.contains(e.relatedTarget)) hideTip();
        });
        document.addEventListener('focusin', e => {
            const el = e.target.closest && e.target.closest('.cc[data-full]');
            if (el) showTip(el);
        });
        document.addEventListener('focusout', e => {
            if (e.target.closest && e.target.closest('.cc[data-full]')) hideTip();
        });
        document.addEventListener('click', e => {
            const el = e.target.closest && e.target.closest('.cc[data-full]');
            if (el) { showTip(el); tipPinned = true; }
            else if (tipPinned) hideTip();
        });
        window.addEventListener('scroll', hideTip, true);
        window.addEventListener('resize', hideTip);
    }

    /* ════════════════════════════════════════════════════════════════
       EVENTOS
       ════════════════════════════════════════════════════════════════ */
    function bindEvents() {
        document.querySelectorAll('.md-overlay').forEach(o =>
            o.addEventListener('click', e => { if (e.target === o) closeModal(o.id); })
        );
        document.querySelectorAll('[data-close]').forEach(b =>
            b.addEventListener('click', () => closeModal(b.dataset.close))
        );
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                hideTip();
                document.querySelectorAll('.md-overlay.active').forEach(o => closeModal(o.id));
            }
        });

        document.getElementById('ap-search').addEventListener('input', e => {
            STATE.q = e.target.value.toLowerCase().trim();
            STATE.page = 1;
            renderTable();
        });

        document.getElementById('ap-size').addEventListener('change', e => {
            STATE.size = e.target.value;
            STATE.page = 1;
            renderTable();
        });

        document.querySelectorAll('.ap-tab').forEach(tab => tab.addEventListener('click', function () {
            document.querySelectorAll('.ap-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            STATE.filter = this.dataset.filter;
            STATE.page = 1;
            renderTable();
        }));

        const tbody = document.getElementById('ap-tbody');
        tbody.addEventListener('click', e => {
            const btn = e.target.closest('[data-action]');
            if (btn) {
                e.stopPropagation();
                const d = btn.dataset;
                if (d.action === 'kardex')     return openKardex(d.uid);
                if (d.action === 'reembolsos') return openReembolsos(d.uid);
                if (d.action === 'advance')    return openAdvance(d.uid, d.id);
            }
            const row = e.target.closest('tr.emp-row');
            if (row) toggleAcc(row.dataset.uid);
        });
        tbody.addEventListener('input', e => { if (e.target.dataset.sub) applySubFilter(e.target.dataset.sub); });
        tbody.addEventListener('change', e => { if (e.target.dataset.sub) applySubFilter(e.target.dataset.sub); });

        const kx = document.getElementById('md-kardex-body');
        kx.addEventListener('click', e => {
            const btn = e.target.closest('[data-action]');
            if (!btn) return;
            const d = btn.dataset;
            if (d.action === 'advance') return openAdvance(d.uid, d.id);
            if (d.action === 'settle')  return settleAdvance(d.id);
            if (d.action === 'ajuste')  return openAjuste(d.uid, d.advid);
        });
        kx.addEventListener('change', e => {
            if (e.target.id === 'kx-size') {
                STATE.kxSize = e.target.value;
                STATE.kxPage = 1;
                renderKardex();
            }
        });

        document.getElementById('md-reembolsos-body').addEventListener('click', e => {
            const btn = e.target.closest('[data-action="pay"]');
            if (btn) payReembolso(btn.dataset.id, btn.dataset.folio, num(btn.dataset.monto));
        });

        document.getElementById('adj-type').addEventListener('change', updateAdjHint);
        document.getElementById('adj-amount').addEventListener('input', updateAdjHint);
        document.getElementById('adj-submit').addEventListener('click', submitAjuste);

        bindTooltip();
    }

    /* ════════════════════════════════════════════════════════════════
       INIT
       ════════════════════════════════════════════════════════════════ */
    function init() {
        EMPLOYEES.forEach(enrichEmployee);
        initMetrics();
        bindEvents();
        renderTable();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
