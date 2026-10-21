(() => {
    'use strict';

    /* ════════════════════════════════════════════════════════════════
       CONFIGURACIÓN Y ESTADO
       ════════════════════════════════════════════════════════════════ */
    const CFG          = window.AP_CONFIG || {};
    let EMPLOYEES      = CFG.employees || [];
    const URL_AP       = CFG.urlAP || '';
    const URL_ADJUST   = CFG.urlAdjust || '';
    const EVIDENCE_URL = CFG.evidenceUrl || '';
    const CSRF         = CFG.csrf || '';

    const MOVEMENTS = {
        Abono_Retencion: { label: 'Retención de sobrante', icon: 'bx bx-slider-alt', sign: 1 }
    };

    const STATE = {
        page: 1,
        size: '5',
        q: '',
        filter: 'all',
        expandedEmpIds: new Set(),
        subFilters: {},
        // Kardex
        kxUid: null,
        kxGroups: [],
        kxQ: '',
        kxPage: 1,
        kxSize: '5',
        kxLimit: 5,
        kxExpanded: new Set(),
        // Reembolsos
        reembUid: null,
        reembQ: '',
        reembFilter: 'all',
        reembPage: 1,
        reembSize: '10',
        // Desglose Anticipo
        advModal: { uid: null, advId: null }
    };

    /* ════════════════════════════════════════════════════════════════
       WRAPPER SWEETALERT2
       ════════════════════════════════════════════════════════════════ */
    const SWAL_CLASSES = {
        popup: 'ap-swal-popup',
        title: 'ap-swal-title',
        confirmButton: 'ap-swal-confirm',
        cancelButton: 'ap-swal-cancel'
    };

    async function ensureSwal() {
        if (window.Swal) return window.Swal;
        return new Promise((resolve, reject) => {
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
            s.onload = () => resolve(window.Swal);
            s.onerror = () => reject(new Error('No se pudo cargar SweetAlert2'));
            document.head.appendChild(s);
        });
    }

    async function swalFire(opts) {
        const Swal = await ensureSwal();
        return Swal.fire({
            buttonsStyling: false,
            customClass: SWAL_CLASSES,
            ...opts
        });
    }

    async function swalLoading(title = 'Procesando...') {
        const Swal = await ensureSwal();
        Swal.fire({
            title,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            customClass: SWAL_CLASSES,
            didOpen: () => Swal.showLoading()
        });
    }

    async function swalToast(title, icon = 'success') {
        const Swal = await ensureSwal();
        return Swal.fire({
            toast: true,
            position: 'top-end',
            icon,
            title,
            showConfirmButton: false,
            timer: 2600,
            timerProgressBar: true
        });
    }

    /* ════════════════════════════════════════════════════════════════
       UTILIDADES
       ════════════════════════════════════════════════════════════════ */
    const fmt      = n => new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(Number(n) || 0);
    const num      = v => Number(v) || 0;
    const norm     = v => String(v ?? '').trim();
    const esc      = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
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
        const v = m.advance_id;
        return v == null || v === '' ? null : Number(v);
    };

    /* ════════════════════════════════════════════════════════════════
       SINCRONIZACIÓN EN TIEMPO REAL SIN RECARGAR PÁGINA
       ════════════════════════════════════════════════════════════════ */
    async function refreshAppData() {
        try {
            const res = await fetch(URL_AP, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) throw new Error('Error al sincronizar datos');
            const j = await res.json();
            if (j.success && Array.isArray(j.employees)) {
                EMPLOYEES = j.employees.map(enrichEmployee);
                initMetrics();
                renderTable();

                if (document.getElementById('md-kardex')?.classList.contains('active') && STATE.kxUid) {
                    const emp = findEmp(STATE.kxUid);
                    if (emp) {
                        STATE.kxGroups = buildGroups(emp);
                        renderKardex();
                    }
                }
                if (document.getElementById('md-reembolsos')?.classList.contains('active') && STATE.reembUid) {
                    renderReembolsosModal(STATE.reembUid);
                }
                if (document.getElementById('md-advance')?.classList.contains('active') && STATE.advModal.advId) {
                    renderAdvanceModal(STATE.advModal.uid, STATE.advModal.advId);
                }
            }
        } catch (err) {
            console.error(err);
        }
    }

    /* ════════════════════════════════════════════════════════════════
       ESTADOS
       ════════════════════════════════════════════════════════════════ */
    const FINAL_STATES = ['Aprobado', 'Rechazado'];

    function effStatusOf(status, approval) {
        const canonical = v => {
            const s = norm(v).toLowerCase();
            return s ? s.charAt(0).toUpperCase() + s.slice(1) : '';
        };
        const s = canonical(status), a = canonical(approval);
        if (s === 'Rechazado' || a === 'Rechazado') return 'Rechazado';
        if (s === 'Validado' && FINAL_STATES.includes(a)) return a;
        if (FINAL_STATES.includes(a)) return a;
        if (FINAL_STATES.includes(s)) return s;
        return s || a || 'Pendiente';
    }
    const effStatus  = m => effStatusOf(m.status, m.status_approval);
    const isApproved = m => effStatus(m) === 'Aprobado';
    const isRejected = m => effStatus(m) === 'Rechazado';
    const isPending  = m => ['Pendiente', 'Validado'].includes(effStatus(m));
    const isPaid = m => {
        if (m.origen === 'comprobacion' || m.origen === 'anticipo' || m.origen === 'ajuste_balance') return false;
        return norm(m.status_payment).toLowerCase() === 'pagado'
            || norm(m.status_balance).toLowerCase() === 'pagado';
    };
    const isSettled  = a => norm(a.settlement_status).toLowerCase() === 'comprobado';

    const isPayableReemb = m =>
        m.origen === 'reembolso' &&
        norm(m.status).toLowerCase() === 'validado' &&
        isApproved(m) &&
        norm(m.status_payment).toLowerCase() !== 'pagado' &&
        m.puede_pagar !== false;

    const isPayableExcess = m =>
        m.origen === 'excedente' &&
        isApproved(m) &&
        norm(m.status_balance).toLowerCase() !== 'pagado';

    const direction = m => m.signo === 'abono' ? 'abono' : 'cargo';

    const STATUS_MAP = {
        pendiente:            { c: 'st-wait',       i: 'bx bx-hourglass',     l: 'Pendiente' },
        validado:             { c: 'st-rev',        i: 'bx bx-list-check',    l: 'Validado' },
        aprobado:             { c: 'st-ok',         i: 'bx bx-check-shield',  l: 'Aprobado' },
        rechazado:            { c: 'st-fail',       i: 'bx bx-x-circle',      l: 'Rechazado' },
        en_espera:            { c: 'st-wait',       i: 'bx bx-time-five',     l: 'En espera' },
        en_revision:          { c: 'st-rev',        i: 'bx bx-search',        l: 'En revisión' },
        borrador:             { c: 'st-draft',      i: 'bx bx-edit-alt',      l: 'Borrador' },
        consolidado:          { c: 'st-retencion',  i: 'bx bx-archive',       l: 'Consolidado' },
        comprobado:           { c: 'st-comprobado', i: 'bx bx-check-double',  l: 'Comprobado' },
        no_comprobado:        { c: 'st-mute',       i: 'bx bx-minus-circle',  l: 'Sin comprobar' },
        comprobando:          { c: 'st-proc',       i: 'bx bx-refresh',       l: 'Comprobando' },
        retenido:             { c: 'st-retencion',  i: 'bx bx-lock-alt',      l: 'Retenido' },
        pagado:               { c: 'st-paid',       i: 'bx bx-check-double',  l: 'Pagado' },
        no_pagado:            { c: 'st-wait',       i: 'bx bx-time',          l: 'No pagado' },
        por_definir:          { c: 'st-mute',       i: 'bx bx-time',          l: 'Por definir' },
        devolucion_pendiente: { c: 'st-dev',        i: 'bx bx-upload',        l: 'Devolución pendiente' },
        por_verificar:        { c: 'st-ver',        i: 'bx bx-search-alt',    l: 'Por verificar' },
        devuelto:             { c: 'st-devuelto',   i: 'bx bx-revision',      l: 'Devuelto' },
        pago_pendiente:       { c: 'st-pay',        i: 'bx bx-wallet',        l: 'Pago pendiente' },
        no_aplica:            { c: 'st-mute',       i: 'bx bx-minus',         l: 'N/A' },
        n_a:                  { c: 'st-mute',       i: 'bx bx-minus',         l: 'N/A' }
    };

    function statusBadge(s) {
        const key = norm(s).toLowerCase().replace(/\s+/g, '_');
        const c = STATUS_MAP[key] || { c: 'st-mute', i: 'bx bx-minus', l: norm(s) || '—' };
        return `<span class="st-badge ${c.c}"><i class="${c.i}"></i>${esc(c.l)}</span>`;
    }

    /* ════════════════════════════════════════════════════════════════
       OPERACIÓN / TIPO / LIQUIDACIÓN / CONCEPTO / FOLIO
       ════════════════════════════════════════════════════════════════ */
    function opInfo(m) {
        if (m.origen === 'anticipo')  return { cls: 'op-anticipo',  icon: 'bx bx-up-arrow-circle', label: 'Anticipo' };
        if (m.origen === 'reembolso') return { cls: 'op-reembolso', icon: 'bx bx-money-withdraw',  label: 'Reembolso' };
        if (m.origen === 'excedente') return { cls: 'op-excedente', icon: 'bx bx-trending-up',     label: 'Excedente de anticipo' };
        if (m.origen === 'sobrante')  return { cls: 'op-sobrante',  icon: 'bx bx-undo',            label: 'Devolución de sobrante' };
        if (m.origen === 'comprobacion') return m.subtipo === 'electronica'
            ? { cls: 'op-electronica', icon: 'bx bx-credit-card', label: 'Comprobación electrónica' }
            : { cls: 'op-directa',     icon: 'bx bx-transfer',    label: 'Comprobación directa' };
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
        if (m.origen === 'anticipo')       return m.tipo || '—';
        if (m.origen === 'comprobacion')   return m.metodo || (m.subtipo === 'electronica' ? 'Tarjeta Toka' : 'Transferencia');
        if (m.origen === 'reembolso')      return m.payment_method || m.metodo || 'Transferencia';
        if (m.origen === 'sobrante')       return m.leftover_method || m.metodo || 'Transferencia';
        if (m.origen === 'excedente')      return m.excess_method   || m.metodo || 'Transferencia';
        if (m.origen === 'ajuste_balance') return '—';
        return '—';
    }

    function payBadge(m) {
        if (m.origen === 'reembolso')      return statusBadge(m.status_payment || 'no_pagado');
        if (m.origen === 'anticipo')       return statusBadge(m.settlement_status || 'no_comprobado');
        if (m.origen === 'comprobacion')   return '<span class="c-mute">—</span>';
        if (m.origen === 'sobrante')       return statusBadge(m.status_balance || (m.has_proof ? 'por_verificar' : 'devolucion_pendiente'));
        if (m.origen === 'excedente')      return statusBadge(m.status_balance || 'pago_pendiente');
        if (m.origen === 'ajuste_balance') return statusBadge('retenido');
        return '<span class="c-mute">—</span>';
    }

    function folioCell(f, kind = '') {
        let type = kind;
        if (!type) {
            const s = String(f || '').toUpperCase();
            if (s.startsWith('ANT-')) type = 'anticipo';
            else if (s.startsWith('BAL-')) type = 'ajuste_balance';
            else type = 'comprobacion';
        }
        return `<span class="folio folio-${esc(type)}">${esc(f)}</span>`;
    }

    const concept = text => {
        const t = text || 'Sin descripción';
        return `<span class="cc" data-full="${esc(t)}" tabindex="0">${esc(t)}</span>`;
    };

    function sobranteOf(k) {
        if (k.origen !== 'comprobacion') return null;
        const lo = k.leftover || {};
        const amount = num(lo.amount ?? k.leftover_amount);
        if (amount <= 0) return null;
        const proofPath = lo.proof_path ?? k.leftover_proof_path ?? null;
        const stBal = lo.status_balance || k.status_balance || (proofPath ? 'por_verificar' : 'devolucion_pendiente');
        return {
            origen: 'sobrante',
            id: `s${k.id}`,
            claim_id: k.id,
            folio: k.folio,
            fecha: k.fecha,
            fecha_raw: k.fecha_raw,
            tipo: 'Sobrante',
            metodo: lo.method || k.leftover_method || 'Transferencia',
            leftover_method: lo.method || k.leftover_method || 'Transferencia',
            leftover_proof_path: proofPath,
            descripcion: lo.notes || k.leftover_notes || `Devolución de sobrante (${k.folio})`,
            monto: amount,
            status: k.status,
            status_approval: k.status_approval,
            status_balance: stBal,
            has_proof: !!(lo.has_proof || proofPath),
            vinculo: k.vinculo,
            advance_id: k.advance_id,
            signo: 'abono'
        };
    }

    function excedenteOf(k) {
        if (k.origen !== 'comprobacion') return null;
        const ex = k.excess || {};
        const amount = num(ex.amount ?? k.excess_amount);
        if (amount <= 0) return null;
        const proofPath = ex.proof_path ?? k.excess_proof_path ?? null;
        const stBal = ex.status_balance || k.status_balance || 'pago_pendiente';
        return {
            origen: 'excedente',
            id: `x${k.id}`,
            claim_id: k.id,
            folio: k.folio,
            fecha: k.fecha,
            fecha_raw: k.fecha_raw,
            tipo: 'Excedente de anticipo',
            metodo: ex.method || k.excess_method || 'Transferencia',
            excess_method: ex.method || k.excess_method || 'Transferencia',
            excess_notes: ex.notes || k.excess_notes || '',
            excess_proof_path: proofPath,
            descripcion: ex.notes || k.excess_notes || `Excedente a favor del colaborador (${k.folio})`,
            monto: amount,
            status: k.status,
            status_approval: k.status_approval,
            status_balance: stBal,
            status_payment: stBal === 'pagado' ? 'pagado' : 'no_pagado',
            has_proof: !!(ex.has_proof || proofPath),
            vinculo: k.vinculo,
            advance_id: k.advance_id,
            signo: 'cargo'
        };
    }

    /* ════════════════════════════════════════════════════════════════
       CÁLCULOS EN CENTAVOS
       ════════════════════════════════════════════════════════════════ */
    function advanceSummary(emp, adv) {
        const kids = emp.movimientos
            .filter(x => x.origen !== 'anticipo' && linkId(x) === Number(adv.id))
            .sort(byTime);

        const sumCents = rows => rows.reduce((s, x) => s + Math.round(num(x.monto) * 100), 0);
        const approved = kids.filter(x => x.origen === 'comprobacion' && isApproved(x));

        const comprobado = sumCents(approved) / 100;

        const devuelto = approved.reduce((s, x) => {
            const loAmt = num(x.leftover ? x.leftover.amount : x.leftover_amount);
            const stBal = norm(x.status_balance).toLowerCase();
            if (loAmt > 0 && (stBal === 'devuelto' || stBal === 'por_verificar' || x.leftover_proof_path)) {
                return s + Math.round(loAmt * 100);
            }
            return s;
        }, 0) / 100;

        const retenido = sumCents(
            kids.filter(x => x.origen === 'ajuste_balance' && ['Abono_Retencion', 'Liquidacion_Caja'].includes(x.tipo))
        ) / 100;

        const saldo = (
            Math.round(num(adv.monto) * 100) -
            Math.round(comprobado * 100) -
            Math.round(devuelto * 100) -
            Math.round(retenido * 100)
        ) / 100;

        const pendiente = Math.max(0, saldo);
        const excedente = Math.max(0, -saldo);

        const totalConciliado = (
            Math.round(comprobado * 100) +
            Math.round(pendiente * 100) +
            Math.round(devuelto * 100) +
            Math.round(retenido * 100)
        ) / 100;

        return { kids, comprobado, devuelto, retenido, saldo, pendiente, excedente, totalConciliado };
    }

    const activeAdvances = emp =>
        emp.movimientos.filter(m => m.origen === 'anticipo' && !['Rechazado', 'Pendiente', 'Borrador'].includes(effStatus(m)) && !isSettled(m));

    function enrichEmployee(e) {
        const movs = e.movimientos || [];
        const advs = activeAdvances(e);

        const payablesReemb  = movs.filter(isPayableReemb);
        const payablesExcess = movs.map(excedenteOf).filter(Boolean).filter(isPayableExcess);

        const totalReembPagar = payablesReemb.reduce((s, m) => s + num(m.monto), 0)
                              + payablesExcess.reduce((s, x) => s + num(x.monto), 0);

        e._pend        = movs.filter(m => m.origen !== 'ajuste_balance' && isPending(m)).length;
        e._advBal      = advs.reduce((s, a) => s + advanceSummary(e, a).pendiente, 0);
        e._advN        = advs.filter(a => advanceSummary(e, a).pendiente > 0).length;
        e._reembPagar  = totalReembPagar;
        e._reembPagarN = payablesReemb.length + payablesExcess.length;
        e._nAnt        = movs.filter(m => m.origen === 'anticipo' && !isRejected(m)).length;
        e._nComp       = movs.filter(m => m.origen === 'comprobacion' && !isRejected(m)).length;
        e._nReemb      = movs.filter(m => m.origen === 'reembolso' && !isRejected(m)).length + movs.map(excedenteOf).filter(Boolean).length;
        return e;
    }

    /* ════════════════════════════════════════════════════════════════
       GESTIÓN DE MODALES SECUNDARIOS
       ════════════════════════════════════════════════════════════════ */
    const modalStack = [];
    const modalFocus = new Map();

    function openModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        if (!modalStack.includes(id)) {
            modalStack.push(id);
            modalFocus.set(id, document.activeElement);
        }
        modal.style.zIndex = 9990 + modalStack.indexOf(id);
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        (modal.querySelector('button, input, select, textarea') || modal).focus();
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('active');
        const index = modalStack.indexOf(id);
        if (index >= 0) modalStack.splice(index, 1);
        if (!modalStack.length) document.body.style.overflow = '';
        modalFocus.get(id)?.focus();
        modalFocus.delete(id);
    }

    function setTitle(id, html) {
        const el = document.querySelector(`#${id} .md-title`);
        if (el) el.innerHTML = html;
    }

    /* ════════════════════════════════════════════════════════════════
       MÉTRICAS Y TABLA PRINCIPAL
       ════════════════════════════════════════════════════════════════ */
    function initMetrics() {
        let pend = 0, adv = 0, reemb = 0, net = 0;
        EMPLOYEES.forEach(e => {
            pend  += e._pend;
            adv   += e._advBal;
            reemb += e._reembPagar;
            net   += num(e.saldo_corriente);
        });
        document.getElementById('m-pending').textContent = pend;
        document.getElementById('m-adv').textContent = fmt(adv);
        document.getElementById('m-reemb').textContent = fmt(reemb);
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
        if (!container) return;
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
            const bal        = num(e.saldo_corriente);
            const balTxt     = bal > 0 ? '+ ' + fmt(bal) : (bal < 0 ? fmt(bal) : '$0.00');
            const balCls     = bal > 0 ? 'c-abono' : (bal < 0 ? 'c-cargo' : 'c-mute');
            const isExpanded = STATE.expandedEmpIds.has(Number(e.user_id));
            const subFilter  = STATE.subFilters[e.user_id] || { q: '', t: 'all' };

            return `
            <tr id="row-${e.user_id}" class="emp-row ${isExpanded ? 'expanded' : ''}" data-uid="${e.user_id}">
                <td class="m-td"><div class="ap-toggle"><i class="bx bx-chevron-right ${isExpanded ? 'rot' : ''}"></i></div></td>
                <td class="m-td">
                    <div class="ap-user">
                        <div class="ap-avatar">${esc(initials(e.nombre))}</div>
                        <div>
                            <span class="ap-name">${esc(e.nombre)}</span>
                            ${e.ultimo_movimiento ? `<span class="ap-last">Último mov.: ${esc(e.ultimo_movimiento)}</span>` : ''}
                        </div>
                    </div>
                </td>
                <td class="m-td c-center"><span class="ap-depto">${esc(e.depto)}</span></td>
                <td class="m-td t-center">
                    <div class="ap-tram">
                        <span class="ap-pill ${e._pend ? '' : 'zero'}" title="Trámites pendientes o validados">${e._pend}</span>
                        <div class="ap-chips">
                            <span class="ap-chip" title="Anticipos"><i class="bx bx-up-arrow-circle"></i>${e._nAnt}</span>
                            <span class="ap-chip" title="Comprobaciones"><i class="bx bx-check-double"></i>${e._nComp}</span>
                            <span class="ap-chip" title="Reembolsos y Excedentes"><i class="bx bx-money"></i>${e._nReemb}</span>
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
                        <button type="button" class="ap-btn ap-btn-reemb ${e._nReemb ? '' : 'off'}" data-action="reembolsos" data-uid="${e.user_id}" title="${e._nReemb ? 'Revisar o pagar reembolsos y excedentes' : 'Sin reembolsos'}">
                            <i class="bx bx-money"></i> Reembolsos
                            ${e._reembPagarN ? `<span class="ap-btn-badge">${e._reembPagarN}</span>` : ''}
                        </button>
                    </div>
                </td>
            </tr>
            <tr id="det-${e.user_id}" class="det-row ${isExpanded ? '' : 'hidden'}">
                <td colspan="8">
                    <div class="det-box">
                        <div class="det-head">
                            <h4><i class="bx bx-layer"></i> Anticipos, comprobaciones, reembolsos y ajustes</h4>
                            <span class="det-count">${e.movimientos.length} registro(s)</span>
                        </div>
                        <div class="det-filters">
                            <div class="det-field grow">
                                <i class="bx bx-search"></i>
                                <input type="text" id="sub-q-${e.user_id}" data-sub="${e.user_id}" value="${esc(subFilter.q)}" placeholder="Buscar por folio, concepto o vinculación...">
                            </div>
                            <div class="det-field fixed">
                                <i class="bx bx-filter-alt"></i>
                                <select id="sub-t-${e.user_id}" data-sub="${e.user_id}">
                                    <option value="all" ${subFilter.t === 'all' ? 'selected' : ''}>Todos los movimientos</option>
                                    <option value="anticipo" ${subFilter.t === 'anticipo' ? 'selected' : ''}>Anticipos</option>
                                    <option value="comprobacion_directa" ${subFilter.t === 'comprobacion_directa' ? 'selected' : ''}>Comprobaciones directas</option>
                                    <option value="comprobacion_electronica" ${subFilter.t === 'comprobacion_electronica' ? 'selected' : ''}>Comprobaciones electrónicas</option>
                                    <option value="reembolso" ${subFilter.t === 'reembolso' ? 'selected' : ''}>Reembolsos</option>
                                    <option value="sobrante" ${subFilter.t === 'sobrante' ? 'selected' : ''}>Devoluciones de sobrante</option>
                                    <option value="excedente" ${subFilter.t === 'excedente' ? 'selected' : ''}>Excedentes de anticipo</option>
                                    <option value="ajuste_balance" ${subFilter.t === 'ajuste_balance' ? 'selected' : ''}>Retenciones de sobrante</option>
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
                                        <th>Liquidación</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="sub-body-${e.user_id}">${renderSubRows(e, subFilter.q, subFilter.t)}</tbody>
                            </table>
                        </div>
                    </div>
                </td>
            </tr>`;
        }).join('');

        buildPager(pager, totalPages, STATE.page, p => { STATE.page = p; renderTable(); });
    }

    function toggleAcc(uid) {
        const numUid = Number(uid);
        const det = document.getElementById(`det-${numUid}`);
        const row = document.getElementById(`row-${numUid}`);
        if (!det || !row) return;

        document.querySelectorAll('.det-row:not(.hidden)').forEach(r => {
            const otherId = Number(r.id.replace('det-', ''));
            if (otherId === numUid) return;
            r.classList.add('hidden');
            STATE.expandedEmpIds.delete(otherId);
            const otherRow = document.getElementById(`row-${otherId}`);
            otherRow?.classList.remove('expanded');
            otherRow?.querySelector('.ap-toggle i')?.classList.remove('rot');
        });

        const open = det.classList.contains('hidden');
        det.classList.toggle('hidden', !open);
        row.classList.toggle('expanded', open);
        row.querySelector('.ap-toggle i')?.classList.toggle('rot', open);

        if (open) STATE.expandedEmpIds.add(numUid);
        else STATE.expandedEmpIds.delete(numUid);
    }

    /* ════════════════════════════════════════════════════════════════
       ACORDEÓN: MOVIMIENTOS DEL COLABORADOR
       ════════════════════════════════════════════════════════════════ */
    const subKey = m => m.origen === 'comprobacion' ? `comprobacion_${m.subtipo}` : m.origen;

    function buildChildActionBtn(m) {
        if (m.origen === 'sobrante' && isApproved(m) && norm(m.status_balance).toLowerCase() === 'por_verificar') {
            const proof = m.leftover_proof_path || '';
            return `<button type="button" class="btn-pay btn-verify" data-action="verify-leftover" data-id="${m.claim_id}" data-folio="${esc(m.folio)}" data-monto="${num(m.monto)}" data-proof="${esc(proof)}" title="Confirmar ingreso de devolución en Tesorería"><i class="bx bx-check-shield"></i> Verificar</button>`;
        }
        if (m.origen === 'excedente' && isApproved(m) && norm(m.status_balance).toLowerCase() === 'pago_pendiente') {
            return `<button type="button" class="btn-pay" data-action="pay-excess" data-id="${m.claim_id}" data-folio="${esc(m.folio)}" data-monto="${num(m.monto)}" title="Liquidar excedente al colaborador"><i class="bx bx-dollar-circle"></i> Pagar</button>`;
        }
        if (m.origen === 'excedente' && norm(m.status_balance).toLowerCase() === 'pagado') {
            return `<button type="button" class="btn-pay btn-view" data-action="view-excess" data-folio="${esc(m.folio)}" data-monto="${num(m.monto)}" data-method="${esc(m.excess_method || '')}" data-notes="${esc(m.excess_notes || '')}" data-proof="${esc(m.excess_proof_path || '')}" title="Ver detalle del pago"><i class="bx bx-show"></i> Ver</button>`;
        }
        if (m.origen === 'reembolso' && isPayableReemb(m)) {
            return `<button type="button" class="btn-pay" data-action="pay" data-id="${m.id}" data-folio="${esc(m.folio)}" data-monto="${num(m.monto)}" title="Registrar pago de reembolso"><i class="bx bx-dollar-circle"></i> Pagar</button>`;
        }
        if (m.origen === 'reembolso' && isPaid(m)) {
            return `<button type="button" class="btn-pay btn-view" data-action="view-reemb" data-folio="${esc(m.folio)}" data-monto="${num(m.monto)}" data-method="${esc(m.payment_method || m.metodo || '')}" data-notes="${esc(m.payment_notes || '')}" data-proof="${esc(m.reimbursement_proof_path || '')}" title="Ver detalle del pago"><i class="bx bx-show"></i> Ver</button>`;
        }
        return '<span class="c-mute">—</span>';
    }

    function subRow(emp, m) {
        const dir  = direction(m);
        const sign = dir === 'cargo' ? '+' : '−';

        let action = '<span class="c-mute">—</span>';
        if (m.origen === 'anticipo') {
            action = `<button type="button" class="ic-btn" data-action="advance" data-uid="${emp.user_id}" data-id="${m.id}" title="Ver desglose"><i class="bx bx-list-ul"></i></button>`;
        } else if (m.origen === 'sobrante' || m.origen === 'excedente' || m.origen === 'reembolso') {
            action = buildChildActionBtn(m);
        }

        const vinculo = m.origen === 'anticipo'
            ? '<span class="c-mute">—</span>'
            : (m.vinculo && !['General', 'Sin anticipo'].includes(m.vinculo)
                ? folioCell(m.vinculo, 'link')
                : `<span class="c-mute">${esc(m.vinculo || '—')}</span>`);

        return `
        <tr>
            <td class="c-center">${folioCell(m.folio, m.origen)}</td>
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
            .flatMap(m => {
                const items = [m];
                const l = sobranteOf(m);
                const x = excedenteOf(m);
                if (l) items.push(l);
                if (x) items.push(x);
                return items;
            })
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
        STATE.subFilters[uid] = { q, t };
        body.innerHTML = renderSubRows(emp, q, t);
    }

    /* ════════════════════════════════════════════════════════════════
       KARDEX CONTABLE INTEGRAL (BUSCADOR + ACORDEÓN + PAGINACIÓN)
       ════════════════════════════════════════════════════════════════ */
    function pushWithBalanceItems(rows, k) {
        rows.push({ type: 'child', m: k });
        const sob = sobranteOf(k);
        if (sob) rows.push({ type: 'child', m: sob });
        const exc = excedenteOf(k);
        if (exc) rows.push({ type: 'child', m: exc });
    }

    function buildGroups(emp) {
        const advances = emp.movimientos
            .filter(m => m.origen === 'anticipo')
            .sort((a, b) => byTime(b, a));

        const advIds = new Set(advances.map(a => Number(a.id)));

        const groups = advances.map(adv => {
            const s         = advanceSummary(emp, adv);
            const kids      = s.kids.slice().sort(byTime);
            const childRows = [];
            kids.forEach(k => pushWithBalanceItems(childRows, k));

            const searchParts = [
                adv.folio, adv.descripcion, adv.tipo, adv.fecha, effStatus(adv), adv.settlement_status,
                ...childRows.flatMap(cr => [cr.m.folio, cr.m.descripcion, cr.m.tipo, cr.m.fecha])
            ];

            const rows = [
                { type: 'parent', m: adv, s, childCount: childRows.length },
                ...childRows,
                { type: 'summary', m: adv, s }
            ];

            return {
                groupId: `adv-${adv.id}`,
                searchText: searchParts.filter(Boolean).join(' ').toLowerCase(),
                rows
            };
        });

        const orphans = emp.movimientos
            .filter(m => (m.origen === 'comprobacion' || m.origen === 'ajuste_balance') && !advIds.has(linkId(m)))
            .sort(byTime);

        if (orphans.length) {
            const rows = [];
            orphans.forEach(k => pushWithBalanceItems(rows, k));
            const searchParts = rows.flatMap(cr => [cr.m.folio, cr.m.descripcion, cr.m.tipo, cr.m.fecha]);
            groups.push({
                groupId: 'orphans',
                label: 'Sin anticipo vinculado',
                searchText: searchParts.filter(Boolean).join(' ').toLowerCase(),
                rows
            });
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

    function kxParent(m, s, uid, groupId, isOpen, childCount) {
        const eligible = !['Pendiente', 'Borrador', 'Rechazado'].includes(effStatus(m)) && !isSettled(m);
        const acts = [
            `<button type="button" class="ic-btn" data-action="advance" data-uid="${uid}" data-id="${m.id}" title="Ver desglose"><i class="bx bx-list-ul"></i></button>`
        ];
        if (eligible && s.pendiente === 0) {
            acts.push(`<button type="button" class="ic-btn ok" data-action="settle" data-id="${m.id}" title="Marcar como comprobado"><i class="bx bx-check-double"></i></button>`);
        }
        if (eligible && s.pendiente > 0) {
            acts.push(`<button type="button" class="ic-btn warn" data-action="ajuste" data-uid="${uid}" data-advid="${m.id}" title="Retener sobrante"><i class="bx bx-slider-alt"></i></button>`);
        }

        const saldoCls = s.pendiente > 0 ? 'pend' : 'abono';

        return `
        <tr class="kx-parent ${isOpen ? 'is-open' : ''}" data-kx-toggle="${esc(groupId)}" title="Haz clic para desplegar u ocultar comprobaciones y resumen">
            <td class="c-date">
                <div class="kx-parent-date">
                    <i class="bx bx-chevron-right kx-chevron"></i>
                    <span>${esc(formatDate(m.fecha))}</span>
                    <span class="kx-kids-pill" title="${childCount} movimiento(s) vinculado(s)">${childCount}</span>
                </div>
            </td>
            <td class="c-center">${folioCell(m.folio, m.origen)}</td>
            <td class="c-center">${opBadge(m)}</td>
            <td class="c-center"><span class="tipo-txt">${esc(tipoText(m))}</span></td>
            <td>${concept(m.descripcion)}</td>
            <td class="num cargo">${fmt(m.monto)}</td>
            <td class="num"></td>
            <td class="num ${saldoCls}">${fmt(s.pendiente)}</td>
            <td class="c-center">${statusBadge(effStatus(m))}</td>
            <td class="c-center">${payBadge(m)}</td>
            <td class="c-center"><div class="acc">${acts.join('')}</div></td>
        </tr>`;
    }

    function kxSummary(m, s, groupId, isOpen) {
        const pendCls = s.pendiente > 0 ? 'pend-txt' : 'ok-txt';

        const dynamicItems = [];
        if (s.devuelto > 0) {
            dynamicItems.push(`<div class="kx-sum-item"><span class="lbl">Devuelto</span><strong class="sob-txt">${fmt(s.devuelto)}</strong></div>`);
        }
        if (s.retenido > 0) {
            dynamicItems.push(`<div class="kx-sum-item"><span class="lbl">Retenido</span><strong class="ok-txt">${fmt(s.retenido)}</strong></div>`);
        }
        if (s.excedente > 0) {
            dynamicItems.push(`<div class="kx-sum-item"><span class="lbl">Excedente</span><strong class="exc-txt">${fmt(s.excedente)}</strong></div>`);
        }

        return `
        <tr class="kx-summary ${isOpen ? '' : 'hidden'}" data-kx-group="${esc(groupId)}">
            <td colspan="5">
                <span class="sum-title"><i class="bx bx-calculator"></i> Resumen · ${esc(m.folio)}</span>
            </td>
            <td class="num"></td>
            <td class="num">
                <span class="lbl">Comprobado</span>
                <strong class="abono-txt">${fmt(s.comprobado)}</strong>
            </td>
            <td class="num">
                <span class="lbl">Por Comprobar</span>
                <strong class="${pendCls}">${fmt(s.pendiente)}</strong>
            </td>
            <td colspan="3">
                <div class="kx-sum-equation">
                    ${dynamicItems.join('')}
                    <span class="kx-sum-eq-sign">=</span>
                    <div class="kx-sum-total">
                        <span class="lbl">Total</span>
                        <strong>${fmt(s.totalConciliado)}</strong>
                    </div>
                </div>
            </td>
        </tr>`;
    }

    function kxChild(m, groupId, isOpen) {
        const isRemb  = m.origen === 'reembolso';
        const isSob   = m.origen === 'sobrante';
        const isExc   = m.origen === 'excedente';
        const isCargo = isRemb || isExc;
        const cls     = [
            'kx-child',
            isRemb ? 'is-remb' : '',
            isSob ? 'is-sob' : '',
            isExc ? 'is-exc' : '',
            isOpen ? '' : 'hidden'
        ].filter(Boolean).join(' ');

        let evidence = '';
        if (isSob && m.leftover_proof_path && EVIDENCE_URL) {
            evidence = `<a class="evidence-link" href="${EVIDENCE_URL}?path=${encodeURIComponent(m.leftover_proof_path)}" target="_blank" rel="noopener"><i class="bx bxs-file-pdf"></i> Comprobante</a>`;
        } else if (isExc && m.excess_proof_path && EVIDENCE_URL) {
            evidence = `<a class="evidence-link" href="${EVIDENCE_URL}?path=${encodeURIComponent(m.excess_proof_path)}" target="_blank" rel="noopener"><i class="bx bxs-file-pdf"></i> Comprobante</a>`;
        }

        const actionBtn = buildChildActionBtn(m);

        return `
        <tr class="${cls}" data-kx-group="${esc(groupId)}">
            <td class="c-date"><i class="bx bx-subdirectory-right kx-branch"></i>${esc(formatDate(m.fecha))}</td>
            <td class="c-center">${folioCell(m.folio, m.origen)}</td>
            <td class="c-center">${opBadge(m)}</td>
            <td class="c-center"><span class="tipo-txt">${esc(tipoText(m))}</span></td>
            <td>${concept(m.descripcion)}${evidence}</td>
            <td class="num cargo">${isCargo ? fmt(m.monto) : ''}</td>
            <td class="num abono">${!isCargo ? fmt(m.monto) : ''}</td>
            <td class="num"></td>
            <td class="c-center">${statusBadge(effStatus(m))}</td>
            <td class="c-center">${payBadge(m)}</td>
            <td class="c-center">${actionBtn !== '<span class="c-mute">—</span>' ? actionBtn : ''}</td>
        </tr>`;
    }

    function kxRow(r, uid, groupId, isOpen) {
        if (r.type === 'parent')  return kxParent(r.m, r.s, uid, groupId, isOpen, r.childCount || 0);
        if (r.type === 'summary') return kxSummary(r.m, r.s, groupId, isOpen);
        return kxChild(r.m, groupId, isOpen);
    }

    function toggleKxGroup(groupId) {
        if (!groupId) return;
        const open = !STATE.kxExpanded.has(groupId);
        if (open) STATE.kxExpanded.add(groupId);
        else STATE.kxExpanded.delete(groupId);

        const body = document.getElementById('md-kardex-body');
        if (!body) return;
        const parentRow = body.querySelector(`tr.kx-parent[data-kx-toggle="${groupId}"]`);
        if (parentRow) parentRow.classList.toggle('is-open', open);

        body.querySelectorAll(`tr[data-kx-group="${groupId}"]`).forEach(row => {
            row.classList.toggle('hidden', !open);
        });
    }

    function renderKardexTableSection() {
        const emp = findEmp(STATE.kxUid);
        if (!emp) return;

        const q = STATE.kxQ.toLowerCase().trim();
        const filteredGroups = q
            ? STATE.kxGroups.filter(g => g.searchText.includes(q))
            : STATE.kxGroups;

        const baseSize   = STATE.kxSize === 'all' ? Math.max(filteredGroups.length, 1) : parseInt(STATE.kxSize, 10);
        const totalPages = Math.max(1, Math.ceil(filteredGroups.length / baseSize));
        if (STATE.kxPage > totalPages) STATE.kxPage = totalPages;

        const start      = (STATE.kxPage - 1) * baseSize;
        const visibleCnt = STATE.kxSize === 'all' ? filteredGroups.length : Math.max(baseSize, STATE.kxLimit);
        const pageGroups = filteredGroups.slice(start, start + visibleCnt);
        const hasMore    = (start + pageGroups.length) < filteredGroups.length;

        let rows = '';
        pageGroups.forEach(g => {
            const isOpen = g.groupId === 'orphans' ? true : STATE.kxExpanded.has(g.groupId);
            if (g.label) rows += `<tr class="kx-group"><td colspan="11">${esc(g.label)}</td></tr>`;
            g.rows.forEach(r => { rows += kxRow(r, emp.user_id, g.groupId, isOpen); });
        });
        if (!pageGroups.length) {
            rows = `<tr><td colspan="11" class="kx-empty">${q ? 'No se encontraron anticipos que coincidan con la búsqueda.' : 'Sin asientos contables registrados.'}</td></tr>`;
        }

        const shownFrom = filteredGroups.length ? start + 1 : 0;
        const shownTo   = start + pageGroups.length;

        const tbody = document.getElementById('kx-tbody');
        if (tbody) tbody.innerHTML = rows;

        const metaEl = document.getElementById('kx-toolbar-meta');
        if (metaEl) metaEl.textContent = `${filteredGroups.length} anticipo(s) encontrado(s)`;

        const infoEl = document.getElementById('kx-foot-info');
        if (infoEl) infoEl.textContent = `Mostrando ${shownFrom}–${shownTo} de ${filteredGroups.length} grupo(s) · Página ${STATE.kxPage} de ${totalPages}`;

        const loadMoreWrap = document.getElementById('kx-load-more-wrap');
        if (loadMoreWrap) {
            loadMoreWrap.innerHTML = hasMore
                ? `<button type="button" class="btn-load-more" data-action="kx-load-more"><i class="bx bx-plus-circle"></i> Mostrar más</button>`
                : '';
        }

        const clearBtn = document.getElementById('kx-search-clear');
        if (clearBtn) clearBtn.classList.toggle('hidden', !q);

        buildPager(document.getElementById('kx-pager'), totalPages, STATE.kxPage, p => {
            STATE.kxPage  = p;
            STATE.kxLimit = STATE.kxSize === 'all' ? filteredGroups.length : parseInt(STATE.kxSize, 10);
            renderKardexTableSection();
        });
    }

    function renderKardex() {
        const emp = findEmp(STATE.kxUid);
        if (!emp) return;

        const body   = document.getElementById('md-kardex-body');
        const t      = kardexTotals(emp);
        const saldo  = num(emp.saldo_corriente);
        const balCls = saldo > 0 ? 'ok' : (saldo < 0 ? 'danger' : '');

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
                <div class="kx-stat pend"><span class="lbl">Por Comprobar</span><span class="val">${fmt(t.pendiente)}</span></div>
                <div class="kx-stat ${balCls}"><span class="lbl">Saldo en libro mayor</span><span class="val">${fmt(saldo)}</span></div>
            </div>
        </div>

        <div class="kx-toolbar">
            <div class="kx-search-box">
                <i class="bx bx-search"></i>
                <input type="text" id="kx-search-input" value="${esc(STATE.kxQ)}" placeholder="Buscar anticipo por folio, concepto, fecha o comprobación vinculada...">
                <button type="button" id="kx-search-clear" class="kx-search-clear ${STATE.kxQ ? '' : 'hidden'}" title="Limpiar búsqueda"><i class="bx bx-x"></i></button>
            </div>
            <span class="kx-toolbar-meta" id="kx-toolbar-meta"></span>
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
                        <th>Cargo</th>
                        <th>Abono</th>
                        <th>Por Comprobar</th>
                        <th>Estado</th>
                        <th>Liquidación</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="kx-tbody"></tbody>
            </table>
        </div>

        <div class="kx-foot">
            <span class="info" id="kx-foot-info"></span>
            <div class="kx-foot-center">
                <div class="kx-pager" id="kx-pager"></div>
                <span id="kx-load-more-wrap"></span>
            </div>
            <div class="kx-size">
                <span>Mostrar:</span>
                <select id="kx-size">
                    ${['5', '10', '20'].map(n => `<option value="${n}" ${STATE.kxSize === n ? 'selected' : ''}>${n}</option>`).join('')}
                    <option value="all" ${STATE.kxSize === 'all' ? 'selected' : ''}>Todos</option>
                </select>
            </div>
        </div>
        <p class="kx-note">Haz clic sobre cualquier anticipo para desplegar u ocultar sus comprobaciones y el resumen contable.</p>`;

        renderKardexTableSection();
    }

    function openKardex(uid) {
        const emp = findEmp(uid);
        if (!emp) return;
        STATE.kxUid      = emp.user_id;
        STATE.kxGroups   = buildGroups(emp);
        STATE.kxQ        = '';
        STATE.kxPage     = 1;
        STATE.kxSize     = '5';
        STATE.kxLimit    = 5;
        STATE.kxExpanded = new Set();
        renderKardex();
        openModal('md-kardex');
    }

    /* ════════════════════════════════════════════════════════════════
       DESGLOSE DEL ANTICIPO
       ════════════════════════════════════════════════════════════════ */
    function renderAdvanceModal(uid, advId) {
        const emp = uid
            ? findEmp(uid)
            : EMPLOYEES.find(e => e.movimientos.some(m => m.origen === 'anticipo' && Number(m.id) === Number(advId)));
        const adv = emp && emp.movimientos.find(m => m.origen === 'anticipo' && Number(m.id) === Number(advId));
        if (!adv) return false;

        STATE.advModal = { uid: emp.user_id, advId: adv.id };

        const s    = advanceSummary(emp, adv);
        const kids = s.kids.slice().sort(byTime).flatMap(k => {
            const items = [k];
            const sob = sobranteOf(k);
            const exc = excedenteOf(k);
            if (sob) items.push(sob);
            if (exc) items.push(exc);
            return items;
        });

        setTitle('md-advance', `<i class="bx bx-list-ul"></i> Desglose del ${folioCell(adv.folio, 'anticipo')}`);

        const rows = kids.length ? kids.map(k => {
            const dir  = direction(k);
            const sign = dir === 'cargo' ? '+' : '−';
            return `
            <tr>
                <td class="c-center">${folioCell(k.folio, k.origen)}</td>
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
            <div class="kx-stat pend"><span class="lbl">Por Comprobar</span><span class="val">${fmt(s.pendiente)}</span></div>
            <div class="kx-stat"><span class="lbl">Estado</span>${statusBadge(effStatus(adv))}</div>
            <div class="kx-stat"><span class="lbl">Liquidación</span>${statusBadge(adv.settlement_status || 'no_comprobado')}</div>
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
                        <th>Liquidación</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>`;

        return true;
    }

    function openAdvance(uid, advId) {
        if (renderAdvanceModal(uid, advId)) {
            openModal('md-advance');
        }
    }

    /* ════════════════════════════════════════════════════════════════
       REEMBOLSOS Y EXCEDENTES (BUSCADOR + PAGINADOR DE 10 POR PÁGINA)
       ════════════════════════════════════════════════════════════════ */
    function getReembolsosData(emp) {
        const reembs   = emp.movimientos.filter(m => m.origen === 'reembolso');
        const excesses = emp.movimientos.map(excedenteOf).filter(Boolean);
        return [...reembs, ...excesses].sort((a, b) => byTime(b, a));
    }

    function renderReembolsosTableSection() {
        const emp = findEmp(STATE.reembUid);
        if (!emp) return;

        const allData = getReembolsosData(emp);
        const q       = STATE.reembQ.toLowerCase().trim();
        const flt     = STATE.reembFilter;

        const filtered = allData.filter(r => {
            const matchQ = !q || [r.folio, r.descripcion, r.tipo, r.fecha, tipoText(r)].some(v => String(v || '').toLowerCase().includes(q));
            if (!matchQ) return false;
            if (flt === 'por_pagar') return isPayableReemb(r) || isPayableExcess(r);
            if (flt === 'pagado')    return isPaid(r);
            if (flt === 'reembolso') return r.origen === 'reembolso';
            if (flt === 'excedente') return r.origen === 'excedente';
            return true;
        });

        const perPage    = STATE.reembSize === 'all' ? Math.max(filtered.length, 1) : parseInt(STATE.reembSize, 10);
        const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
        if (STATE.reembPage > totalPages) STATE.reembPage = totalPages;

        const start     = (STATE.reembPage - 1) * perPage;
        const pageItems = filtered.slice(start, start + perPage);

        const rows = pageItems.length ? pageItems.map(r => {
            let payBtn = '<span class="c-mute">—</span>';
            if (isPayableReemb(r)) {
                payBtn = `<button type="button" class="btn-pay" data-action="pay" data-id="${r.id}" data-folio="${esc(r.folio)}" data-monto="${num(r.monto)}"><i class="bx bx-dollar-circle"></i> Pagar</button>`;
            } else if (isPayableExcess(r)) {
                payBtn = `<button type="button" class="btn-pay" data-action="pay-excess" data-id="${r.claim_id}" data-folio="${esc(r.folio)}" data-monto="${num(r.monto)}"><i class="bx bx-dollar-circle"></i> Pagar excedente</button>`;
            } else if (r.origen === 'excedente' && isPaid(r)) {
                payBtn = `<button type="button" class="btn-pay btn-view" data-action="view-excess" data-folio="${esc(r.folio)}" data-monto="${num(r.monto)}" data-method="${esc(r.excess_method || '')}" data-notes="${esc(r.excess_notes || '')}" data-proof="${esc(r.excess_proof_path || '')}"><i class="bx bx-show"></i> Ver</button>`;
            } else if (r.origen === 'reembolso' && isPaid(r)) {
                payBtn = `<button type="button" class="btn-pay btn-view" data-action="view-reemb" data-folio="${esc(r.folio)}" data-monto="${num(r.monto)}" data-method="${esc(r.payment_method || r.metodo || '')}" data-notes="${esc(r.payment_notes || '')}" data-proof="${esc(r.reimbursement_proof_path || '')}"><i class="bx bx-show"></i> Ver</button>`;
            }

            const proofUrl = r.origen === 'reembolso' ? r.reimbursement_proof_path : r.excess_proof_path;
            const evidence = (proofUrl && EVIDENCE_URL)
                ? `<a class="evidence-link" href="${EVIDENCE_URL}?path=${encodeURIComponent(proofUrl)}" target="_blank" rel="noopener"><i class="bx bxs-file-pdf"></i> Comprobante</a>`
                : '';

            return `
            <tr>
                <td class="c-center">${folioCell(r.folio, r.origen)}</td>
                <td class="c-center date-cell">${esc(formatDate(r.fecha))}</td>
                <td>${concept(r.descripcion)}${evidence}</td>
                <td class="c-center">${opBadge(r)}</td>
                <td class="c-center"><span class="tipo-txt">${esc(tipoText(r))}</span></td>
                <td class="t-right"><span class="rb-amount">${fmt(r.monto)}</span></td>
                <td class="c-center">${statusBadge(effStatus(r))}</td>
                <td class="c-center">${payBadge(r)}</td>
                <td class="c-center">${payBtn}</td>
            </tr>`;
        }).join('') : `<tr><td colspan="9" class="kx-empty">${q || flt !== 'all' ? 'No hay reembolsos que coincidan con el filtro.' : 'Este colaborador no tiene reembolsos ni excedentes registrados.'}</td></tr>`;

        const tbody = document.getElementById('reemb-tbody');
        if (tbody) tbody.innerHTML = rows;

        const shownFrom = filtered.length ? start + 1 : 0;
        const shownTo   = start + pageItems.length;

        const infoEl = document.getElementById('reemb-foot-info');
        if (infoEl) infoEl.textContent = `Mostrando ${shownFrom}–${shownTo} de ${filtered.length} registro(s) · Página ${STATE.reembPage} de ${totalPages}`;

        const clearBtn = document.getElementById('reemb-search-clear');
        if (clearBtn) clearBtn.classList.toggle('hidden', !q);

        buildPager(document.getElementById('reemb-pager'), totalPages, STATE.reembPage, p => {
            STATE.reembPage = p;
            renderReembolsosTableSection();
        });
    }

    function renderReembolsosModal(uid) {
        const emp = findEmp(uid);
        if (!emp) return false;

        STATE.reembUid = emp.user_id;

        const data     = getReembolsosData(emp);
        const active   = data.filter(r => !isRejected(r));
        const total    = active.reduce((s, r) => s + num(r.monto), 0);
        const porPagar = data.filter(r => isPayableReemb(r) || isPayableExcess(r)).reduce((s, r) => s + num(r.monto), 0);
        const pagado   = data.filter(r => isPaid(r) && !isRejected(r)).reduce((s, r) => s + num(r.monto), 0);

        document.getElementById('md-reembolsos-body').innerHTML = `
        <div class="kx-head">
            <div class="kx-user">
                <div class="kx-avatar">${esc(initials(emp.nombre))}</div>
                <div>
                    <div class="kx-user-name">${esc(emp.nombre)}</div>
                    <div class="kx-user-depto">${esc(emp.depto)} · Reembolsos y excedentes</div>
                </div>
            </div>
            <div class="kx-stats">
                <div class="kx-stat accent"><span class="lbl">Total solicitado</span><span class="val">${fmt(total)}</span></div>
                <div class="kx-stat pend"><span class="lbl">Por pagar</span><span class="val">${fmt(porPagar)}</span></div>
                <div class="kx-stat ok"><span class="lbl">Pagado</span><span class="val">${fmt(pagado)}</span></div>
            </div>
        </div>

        <div class="kx-toolbar">
            <div class="kx-search-box">
                <i class="bx bx-search"></i>
                <input type="text" id="reemb-search-input" value="${esc(STATE.reembQ)}" placeholder="Buscar por folio, motivo, método o fecha...">
                <button type="button" id="reemb-search-clear" class="kx-search-clear ${STATE.reembQ ? '' : 'hidden'}" title="Limpiar búsqueda"><i class="bx bx-x"></i></button>
            </div>
            <select id="reemb-filter-select" class="kx-filter-select">
                <option value="all" ${STATE.reembFilter === 'all' ? 'selected' : ''}>Todos los registros</option>
                <option value="por_pagar" ${STATE.reembFilter === 'por_pagar' ? 'selected' : ''}>Pendientes de pago</option>
                <option value="pagado" ${STATE.reembFilter === 'pagado' ? 'selected' : ''}>Pagados</option>
                <option value="reembolso" ${STATE.reembFilter === 'reembolso' ? 'selected' : ''}>Solo reembolsos</option>
                <option value="excedente" ${STATE.reembFilter === 'excedente' ? 'selected' : ''}>Solo excedentes</option>
            </select>
        </div>

        <div class="kx-table-wrap">
            <table class="kx-table">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th>Operación</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Liquidación</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="reemb-tbody"></tbody>
            </table>
        </div>

        <div class="kx-foot">
            <span class="info" id="reemb-foot-info"></span>
            <div class="kx-foot-center">
                <div class="kx-pager" id="reemb-pager"></div>
            </div>
            <div class="kx-size">
                <span>Por página:</span>
                <select id="reemb-size">
                    ${['10', '20', '50'].map(n => `<option value="${n}" ${STATE.reembSize === n ? 'selected' : ''}>${n}</option>`).join('')}
                    <option value="all" ${STATE.reembSize === 'all' ? 'selected' : ''}>Todos</option>
                </select>
            </div>
        </div>`;

        renderReembolsosTableSection();
        return true;
    }

    function openReembolsos(uid) {
        STATE.reembQ      = '';
        STATE.reembFilter = 'all';
        STATE.reembPage   = 1;
        STATE.reembSize   = '10';
        if (renderReembolsosModal(uid)) {
            openModal('md-reembolsos');
        }
    }

    /* ════════════════════════════════════════════════════════════════
       ACCIONES CON SWEETALERT2 Y COMPROBANTE PDF EN REEMBOLSO
       ════════════════════════════════════════════════════════════════ */
    async function payReembolso(id, folio, monto) {
        const { isConfirmed, value: formData } = await swalFire({
            title: 'Registrar pago de reembolso',
            html: `
                <div class="payment-form">
                    <div class="sum">${folioCell(folio, 'reembolso')}<br>Monto a pagar: <strong>${fmt(monto)}</strong></div>
                    <label for="payment-method">Método de pago</label>
                    <select id="payment-method">
                        <option value="Transferencia">Transferencia</option>
                        <option value="Tarjeta Toka">Tarjeta Toka</option>
                        <option value="Efectivo">Efectivo</option>
                    </select>
                    <label for="payment-ref">Referencia (opcional)</label>
                    <input id="payment-ref" type="text" maxlength="120" placeholder="Clave de rastreo, folio de transferencia...">
                    <label for="payment-file">Comprobante PDF de pago (opcional, máx. 10MB)</label>
                    <input id="payment-file" type="file" accept=".pdf,application/pdf">
                </div>`,
            showCancelButton: true,
            confirmButtonText: 'Confirmar pago',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const fd = new FormData();
                fd.append('_token', CSRF);
                fd.append('payment_method', document.getElementById('payment-method').value);
                fd.append('reference', document.getElementById('payment-ref').value.trim());
                const fileInput = document.getElementById('payment-file');
                if (fileInput && fileInput.files[0]) {
                    fd.append('reimbursement_proof', fileInput.files[0]);
                }
                return fd;
            }
        });
        if (!isConfirmed || !formData) return;

        try {
            await swalLoading('Registrando pago...');
            const res = await fetch(`${URL_AP}/claims/${id}/mark-paid`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CSRF
                },
                body: formData
            });
            const j = await res.json();
            if (j.success) {
                await refreshAppData();
                await swalToast(j.message || 'Reembolso marcado como pagado.');
            } else {
                await swalFire({ icon: 'error', title: 'No se pudo registrar', text: j.message || 'Revisa los datos e intenta de nuevo.' });
            }
        } catch (e) {
            await swalFire({ icon: 'error', title: 'Falla de red', text: 'No se pudo contactar al servidor.' });
        }
    }

    async function viewReembolsoPayment(folio, monto, method, notes, proofPath) {
        const proofLink = proofPath && EVIDENCE_URL
            ? `${EVIDENCE_URL}?path=${encodeURIComponent(proofPath)}`
            : null;

        await swalFire({
            title: 'Detalle del pago de reembolso',
            html: `
                <div class="payment-form">
                    <div class="sum">${folioCell(folio, 'reembolso')}<br>Monto pagado: <strong>${fmt(monto)}</strong></div>
                    <div class="excess-detail">
                        <div class="row"><span class="k">Método de pago</span><span class="v">${esc(method || 'Transferencia')}</span></div>
                        <div class="row"><span class="k">Referencia</span><span class="v">${esc(notes || '—')}</span></div>
                    </div>
                    <div style="margin-top:.9rem; text-align:left;">
                        ${proofLink
                            ? `<a class="evidence-link" href="${proofLink}" target="_blank" rel="noopener"><i class="bx bxs-file-pdf"></i> Ver comprobante PDF de pago</a>`
                            : `<span class="c-mute" style="display:inline-flex; align-items:center; gap:.35rem; font-size:.78rem;"><i class="bx bx-info-circle"></i> Sin comprobante PDF adjunto.</span>`}
                    </div>
                </div>`,
            confirmButtonText: 'Cerrar'
        });
    }

    async function verifyLeftover(claimId, folio, monto, proofPath) {
        if (!proofPath) {
            await swalFire({
                icon: 'warning',
                title: 'No se puede verificar la devolución',
                html: `<div class="payment-form">
                    <div class="sum">${folioCell(folio, 'sobrante')}<br>Sobrante: <strong>${fmt(monto)}</strong></div>
                    <p class="dialog-note" style="margin-top:.85rem;">El colaborador <strong>no ha cargado el comprobante de transferencia</strong> del sobrante. No es posible confirmar el ingreso en Tesorería sin dicho comprobante.</p>
                </div>`,
                confirmButtonText: 'Entendido'
            });
            return;
        }

        const proofLink = `${EVIDENCE_URL}?path=${encodeURIComponent(proofPath)}`;
        const { isConfirmed } = await swalFire({
            icon: 'question',
            title: 'Verificar devolución de sobrante',
            html: `<div class="payment-form">
                <div class="sum">${folioCell(folio, 'sobrante')}<br>Sobrante devuelto por el colaborador: <strong>${fmt(monto)}</strong></div>
                <p class="dialog-note" style="margin-top:.85rem;">¿Confirmas que el depósito o transferencia del sobrante ya fue verificado en la cuenta bancaria de la empresa?</p>
                <div style="margin-top:.85rem;">
                    <a class="evidence-link" href="${proofLink}" target="_blank" rel="noopener"><i class="bx bxs-file-pdf"></i> Ver comprobante de transferencia</a>
                </div>
            </div>`,
            showCancelButton: true,
            confirmButtonText: 'Sí, confirmar ingreso',
            cancelButtonText: 'Cancelar'
        });
        if (!isConfirmed) return;

        try {
            await swalLoading('Verificando sobrante...');
            const res = await fetch(`${URL_AP}/claims/${claimId}/verify-leftover`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF }
            });
            const j = await res.json();
            if (j.success) {
                await refreshAppData();
                await swalToast(j.message || 'Devolución de sobrante verificada.');
            } else {
                await swalFire({ icon: 'error', title: 'No se pudo verificar', text: j.message || 'Intenta nuevamente.' });
            }
        } catch (e) {
            await swalFire({ icon: 'error', title: 'Falla de red', text: 'No se pudo contactar al servidor.' });
        }
    }

    async function payExcess(claimId, folio, monto) {
        const { isConfirmed, value: formData } = await swalFire({
            title: 'Liquidar excedente al colaborador',
            html: `
                <div class="payment-form">
                    <div class="sum">${folioCell(folio, 'excedente')}<br>Excedente a reembolsar: <strong>${fmt(monto)}</strong></div>
                    <label for="exc-pay-method">Método de pago</label>
                    <select id="exc-pay-method">
                        <option value="Transferencia">Transferencia</option>
                        <option value="Tarjeta Toka">Tarjeta Toka</option>
                        <option value="Efectivo">Efectivo</option>
                    </select>
                    <label for="exc-pay-notes">Referencia (opcional)</label>
                    <input id="exc-pay-notes" type="text" maxlength="250" placeholder="Clave de rastreo, folio de transferencia...">
                    <label for="exc-pay-file">Comprobante PDF de pago (opcional, máx. 10MB)</label>
                    <input id="exc-pay-file" type="file" accept=".pdf,application/pdf">
                </div>`,
            showCancelButton: true,
            confirmButtonText: 'Registrar pago de excedente',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const fd = new FormData();
                fd.append('_token', CSRF);
                fd.append('excess_method', document.getElementById('exc-pay-method').value);
                fd.append('excess_notes', document.getElementById('exc-pay-notes').value.trim());
                const fileInput = document.getElementById('exc-pay-file');
                if (fileInput && fileInput.files[0]) {
                    fd.append('excess_proof', fileInput.files[0]);
                }
                return fd;
            }
        });
        if (!isConfirmed || !formData) return;

        try {
            await swalLoading('Registrando pago de excedente...');
            const res = await fetch(`${URL_AP}/claims/${claimId}/pay-excess`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const j = await res.json();
            if (j.success) {
                await refreshAppData();
                await swalToast(j.message || 'Excedente pagado correctamente.');
            } else {
                await swalFire({ icon: 'error', title: 'No se pudo registrar', text: j.message || 'Verifica los datos e intenta nuevamente.' });
            }
        } catch (e) {
            await swalFire({ icon: 'error', title: 'Falla de red', text: 'No se pudo contactar al servidor.' });
        }
    }

    async function viewExcessPayment(folio, monto, method, notes, proofPath) {
        const proofLink = proofPath && EVIDENCE_URL
            ? `${EVIDENCE_URL}?path=${encodeURIComponent(proofPath)}`
            : null;

        await swalFire({
            title: 'Detalle del pago de excedente',
            html: `
                <div class="payment-form">
                    <div class="sum">${folioCell(folio, 'excedente')}<br>Excedente: <strong>${fmt(monto)}</strong></div>
                    <div class="excess-detail">
                        <div class="row"><span class="k">Método de pago</span><span class="v">${esc(method || '—')}</span></div>
                        <div class="row"><span class="k">Referencia / Comentarios</span><span class="v">${esc(notes || '—')}</span></div>
                    </div>
                    <div style="margin-top:.9rem; text-align:left;">
                        ${proofLink
                            ? `<a class="evidence-link" href="${proofLink}" target="_blank" rel="noopener"><i class="bx bxs-file-pdf"></i> Ver comprobante de transferencia</a>`
                            : `<span class="c-mute" style="display:inline-flex; align-items:center; gap:.35rem; font-size:.78rem;"><i class="bx bx-info-circle"></i> Sin comprobante adjunto.</span>`}
                    </div>
                </div>`,
            confirmButtonText: 'Cerrar'
        });
    }

    async function settleAdvance(id) {
        const { isConfirmed } = await swalFire({
            icon: 'question',
            title: '¿Cerrar anticipo?',
            html: '<p class="dialog-note">Esto marcará el anticipo como <strong>comprobado/saldado</strong>. Tesorería ya no esperará comprobantes ni devoluciones para este folio.</p>',
            showCancelButton: true,
            confirmButtonText: 'Sí, marcar como comprobado',
            cancelButtonText: 'Cancelar'
        });
        if (!isConfirmed) return;

        try {
            await swalLoading('Cerrando anticipo...');
            const res = await fetch(`${URL_AP}/advances/${id}/settle`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF }
            });
            const j = await res.json();
            if (j.success) {
                await refreshAppData();
                await swalToast(j.message || 'Anticipo cerrado en tesorería.');
            } else {
                await swalFire({ icon: 'error', title: 'Error', text: j.message || 'No se pudo cerrar el anticipo.' });
            }
        } catch (e) {
            await swalFire({ icon: 'error', title: 'Error de red', text: 'No se pudo contactar con el servidor.' });
        }
    }

    function openAjuste(uid, advId) {
        const emp = findEmp(uid);
        if (!emp) return;

        const adv      = emp.movimientos.find(m => m.origen === 'anticipo' && Number(m.id) === Number(advId));
        const restante = adv ? Math.max(0, advanceSummary(emp, adv).pendiente) : 0;

        document.getElementById('adj-user-id').value = uid;
        document.getElementById('adj-adv-id').value = adv ? adv.id : '';
        document.getElementById('adj-adv-saldo').value = restante;
        document.getElementById('adj-user-name').textContent = emp.nombre;

        const refWrap = document.getElementById('adj-ref-wrap');
        refWrap.classList.toggle('hidden', !adv);
        if (adv) document.getElementById('adj-ref-folio').textContent = adv.folio;

        const useTotalBtn = document.getElementById('adj-use-total');
        if (useTotalBtn) useTotalBtn.classList.toggle('hidden', !adv || restante === 0);

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
        const type     = document.getElementById('adj-type').value;
        const amountEl = document.getElementById('adj-amount');
        const max      = num(document.getElementById('adj-adv-saldo').value);
        const amount   = num(amountEl.value);
        const desc     = document.getElementById('adj-desc').value.trim();
        const uid      = document.getElementById('adj-user-id').value;
        const advId    = document.getElementById('adj-adv-id').value;

        if (amount <= 0) return swalFire({ icon: 'warning', title: 'Atención', text: 'El monto a retener debe ser mayor a cero.' });
        if (advId && amount > max) return swalFire({ icon: 'warning', title: 'Atención', text: `El monto no puede superar el saldo pendiente (${fmt(max)}).` });
        if (!desc) return swalFire({ icon: 'warning', title: 'Atención', text: 'Es obligatorio detallar el motivo para auditoría.' });

        try {
            await swalLoading('Registrando retención...');
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
                await refreshAppData();
                await swalToast(`${j.message} (${j.folio})`);
            } else {
                await swalFire({ icon: 'error', title: 'Error de asiento', text: j.message || 'No se pudo registrar la retención.' });
            }
        } catch (e) {
            await swalFire({ icon: 'error', title: 'Falla de red', text: 'No se pudo contactar al servidor.' });
        }
    }

    /* ════════════════════════════════════════════════════════════════
       TOOLTIP FLOTANTE PARA CONCEPTOS
       ════════════════════════════════════════════════════════════════ */
    const tip = document.getElementById('cc-tip');
    let tipPinned = false;

    function showTip(el) {
        if (!tip) return;
        tip.textContent = el.dataset.full || '';
        tip.classList.add('show');
        const r  = el.getBoundingClientRect();
        const tw = tip.offsetWidth;
        const th = tip.offsetHeight;
        const left = Math.min(Math.max(12, r.left), window.innerWidth - tw - 12);
        let top  = r.bottom + 8;
        if (top + th > window.innerHeight - 12) top = r.top - th - 8;
        tip.style.left = left + 'px';
        tip.style.top  = top + 'px';
    }

    function hideTip() {
        if (!tip) return;
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
        document.querySelectorAll('[data-close]').forEach(b =>
            b.addEventListener('click', () => closeModal(b.dataset.close))
        );

        document.addEventListener('keydown', e => {
            if (window.Swal && window.Swal.isVisible()) return;

            if (e.key === 'Escape') {
                hideTip();
                const topModal = modalStack[modalStack.length - 1];
                if (topModal) closeModal(topModal);
                return;
            }

            const id = modalStack[modalStack.length - 1];
            if (!id) return;

            if (e.key === 'Tab') {
                const focusable = Array.from(
                    document.getElementById(id).querySelectorAll('button, a[href], input, select, textarea, [tabindex="0"]')
                ).filter(x => !x.disabled && x.getClientRects().length);
                const first = focusable[0], last = focusable[focusable.length - 1];
                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last?.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first?.focus();
                }
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
                if (d.action === 'kardex')          return openKardex(d.uid);
                if (d.action === 'reembolsos')      return openReembolsos(d.uid);
                if (d.action === 'advance')         return openAdvance(d.uid, d.id);
                if (d.action === 'pay')             return payReembolso(d.id, d.folio, num(d.monto));
                if (d.action === 'view-reemb')      return viewReembolsoPayment(d.folio, num(d.monto), d.method, d.notes, d.proof);
                if (d.action === 'verify-leftover') return verifyLeftover(d.id, d.folio, num(d.monto), d.proof || '');
                if (d.action === 'pay-excess')      return payExcess(d.id, d.folio, num(d.monto));
                if (d.action === 'view-excess')     return viewExcessPayment(d.folio, num(d.monto), d.method, d.notes, d.proof);
            }
            const row = e.target.closest('tr.emp-row');
            if (row) toggleAcc(row.dataset.uid);
        });
        tbody.addEventListener('input', e => { if (e.target.dataset.sub) applySubFilter(e.target.dataset.sub); });
        tbody.addEventListener('change', e => { if (e.target.dataset.sub) applySubFilter(e.target.dataset.sub); });

        // Eventos del Kardex
        const kx = document.getElementById('md-kardex-body');
        kx.addEventListener('click', e => {
            if (e.target.closest('#kx-search-clear')) {
                STATE.kxQ = '';
                STATE.kxPage = 1;
                const inp = document.getElementById('kx-search-input');
                if (inp) { inp.value = ''; inp.focus(); }
                renderKardexTableSection();
                return;
            }

            const btn = e.target.closest('[data-action]');
            if (btn) {
                e.stopPropagation();
                const d = btn.dataset;
                if (d.action === 'advance')         return openAdvance(d.uid, d.id);
                if (d.action === 'settle')          return settleAdvance(d.id);
                if (d.action === 'ajuste')          return openAjuste(d.uid, d.advid);
                if (d.action === 'verify-leftover') return verifyLeftover(d.id, d.folio, num(d.monto), d.proof || '');
                if (d.action === 'pay-excess')      return payExcess(d.id, d.folio, num(d.monto));
                if (d.action === 'view-excess')     return viewExcessPayment(d.folio, num(d.monto), d.method, d.notes, d.proof);
                if (d.action === 'kx-load-more') {
                    const step = STATE.kxSize === 'all' ? STATE.kxGroups.length : parseInt(STATE.kxSize, 10);
                    STATE.kxLimit += step;
                    renderKardexTableSection();
                    return;
                }
            }

            if (e.target.closest('a, button, input, select, .cc')) return;

            const parentRow = e.target.closest('tr.kx-parent[data-kx-toggle]');
            if (parentRow) {
                toggleKxGroup(parentRow.dataset.kxToggle);
            }
        });

        kx.addEventListener('input', e => {
            if (e.target.id === 'kx-search-input') {
                STATE.kxQ    = e.target.value;
                STATE.kxPage = 1;
                renderKardexTableSection();
            }
        });

        kx.addEventListener('change', e => {
            if (e.target.id === 'kx-size') {
                STATE.kxSize  = e.target.value;
                STATE.kxPage  = 1;
                STATE.kxLimit = STATE.kxSize === 'all' ? STATE.kxGroups.length : parseInt(STATE.kxSize, 10);
                renderKardexTableSection();
            }
        });

        // Eventos del modal de Reembolsos
        const rb = document.getElementById('md-reembolsos-body');
        rb.addEventListener('click', e => {
            if (e.target.closest('#reemb-search-clear')) {
                STATE.reembQ    = '';
                STATE.reembPage = 1;
                const inp = document.getElementById('reemb-search-input');
                if (inp) { inp.value = ''; inp.focus(); }
                renderReembolsosTableSection();
                return;
            }

            const btn = e.target.closest('[data-action]');
            if (!btn) return;
            const d = btn.dataset;
            if (d.action === 'pay')         return payReembolso(d.id, d.folio, num(d.monto));
            if (d.action === 'view-reemb')  return viewReembolsoPayment(d.folio, num(d.monto), d.method, d.notes, d.proof);
            if (d.action === 'pay-excess')  return payExcess(d.id, d.folio, num(d.monto));
            if (d.action === 'view-excess') return viewExcessPayment(d.folio, num(d.monto), d.method, d.notes, d.proof);
        });

        rb.addEventListener('input', e => {
            if (e.target.id === 'reemb-search-input') {
                STATE.reembQ    = e.target.value;
                STATE.reembPage = 1;
                renderReembolsosTableSection();
            }
        });

        rb.addEventListener('change', e => {
            if (e.target.id === 'reemb-filter-select') {
                STATE.reembFilter = e.target.value;
                STATE.reembPage   = 1;
                renderReembolsosTableSection();
            }
            if (e.target.id === 'reemb-size') {
                STATE.reembSize = e.target.value;
                STATE.reembPage = 1;
                renderReembolsosTableSection();
            }
        });

        document.getElementById('adj-type').addEventListener('change', updateAdjHint);
        document.getElementById('adj-amount').addEventListener('input', updateAdjHint);
        document.getElementById('adj-use-total')?.addEventListener('click', () => {
            const s = num(document.getElementById('adj-adv-saldo').value);
            if (s > 0) {
                document.getElementById('adj-amount').value = s.toFixed(2);
                updateAdjHint();
            }
        });
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