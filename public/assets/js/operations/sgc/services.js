// modal para crear, ver o editar un servicio
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('service-modal');
    if (!modal) return;
    const content = document.getElementById('service-modal-content');
    let opener;
    let service = {};
    let pending;

    function rowData(row) {
        if (!row) return service;
        const cells = [...row.cells].map(cell => cell.textContent.trim());
        return {
            ...JSON.parse(row.dataset.service || '{}'),
            os: cells[0], fecha: parseDate(cells[1]), estado: cells[15],
            cliente: cells[4], pozo: cells[5], grua: cells[7] === '-' ? '' : cells[7],
            cable: cells[14] === '-' ? '' : cells[14], horas_npt: parseFloat(cells[3]) || 0,
        };
    }

    function parseDate(value) {
        const parts = value.split('/');
        return parts.length === 3 ? `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}` : value;
    }

    function initialize(mode) {
        const form = content.querySelector('#wizard-form');
        form.querySelectorAll('[name]').forEach(field => {
            if (field.name === '_token' || field.name === 'service_id') return;
            const key = field.name.replace(/\[\]$/, '');
            const value = key === 'tiene_npt' ? (Number(service.horas_npt) > 0 ? 'SI' : 'NO') : service[key];
            if (mode !== 'create' && value != null) {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = (Array.isArray(value) ? value : [value]).includes(field.value);
                } else {
                    if (field.tagName === 'SELECT') {
                        const option = [...field.options].find(option => option.value.toLowerCase() === String(value).toLowerCase());
                        if (!option && value !== '') field.add(new Option(value, value));
                        field.value = option ? option.value : value;
                    } else field.value = value;
                }
            }
            if (mode === 'view') field.disabled = true;
        });
        let step = 1;
        const showStep = next => {
            step = Math.min(4, Math.max(1, next));
            content.querySelectorAll('.step-content').forEach((panel, index) => panel.classList.toggle('active', index === step - 1));
            content.querySelectorAll('.step-nav').forEach((button, index) => {
                button.querySelector('.step-bubble').classList.toggle('is-active', index === step - 1);
                button.querySelector('.step-bubble').classList.toggle('is-complete', index < step - 1);
                button.querySelector('.step-label').classList.toggle('is-active', index === step - 1);
                button.setAttribute('aria-current', index === step - 1 ? 'step' : 'false');
            });
            content.querySelectorAll('.step-line').forEach((line, index) => line.classList.toggle('is-complete', index < step - 1));
            content.querySelector('#step-counter').textContent = `Paso ${step} de 4`;
            content.querySelector('#step-percent').textContent = `${step * 25}%`;
            content.querySelector('#progress-bar').style.width = `${step * 25}%`;
            content.querySelector('#btn-prev')?.classList.toggle('hidden', step === 1);
            content.querySelector('#btn-next')?.classList.toggle('hidden', step === 4);
            content.querySelector('#btn-submit')?.classList.toggle('hidden', step !== 4);
        };
        content.querySelector('#btn-prev')?.addEventListener('click', () => showStep(step - 1));
        content.querySelector('#btn-next')?.addEventListener('click', () => showStep(step + 1));
        content.querySelectorAll('.step-nav').forEach(button => button.addEventListener('click', () => showStep(Number(button.dataset.step))));
        // SGC todavía no dispone de un endpoint para persistir este formulario.
        form.addEventListener('submit', event => {
            event.preventDefault();
            window.Swal.fire({ icon: 'info', title: 'Guardado no disponible', text: 'El registro de servicios aún no está conectado al guardado.' });
        });
        showStep(1);
    }

    document.addEventListener('click', async event => {
        if (event.target.closest('[data-service-close], .service-modal-close')) {
            event.preventDefault();
            modal.close();
            return;
        }
        const trigger = event.target.closest('.service-modal-trigger');
        if (!trigger) return;
        event.preventDefault();
        const url = new URL(trigger.href);
        const mode = url.searchParams.get('mode') || 'create';
        service = mode === 'create' ? {} : rowData(trigger.closest('tr'));
        if (!modal.open) {
            opener = trigger;
            modal.showModal();
            document.body.classList.add('service-modal-open');
        }
        pending?.abort();
        const request = new AbortController();
        pending = request;
        content.textContent = 'Cargando servicio…';
        content.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: request.signal });
            if (!response.ok) throw new Error('No se pudo cargar el servicio.');
            const html = await response.text();
            if (request.signal.aborted) return;
            content.innerHTML = html;
            initialize(mode);
        } catch (error) {
            if (error.name !== 'AbortError') content.textContent = 'No se pudo cargar el servicio. Cierra el modal e inténtalo de nuevo.';
        } finally {
            if (pending === request) content.removeAttribute('aria-busy');
        }
    });
    modal.addEventListener('close', () => {
        pending?.abort();
        document.body.classList.remove('service-modal-open');
        content.replaceChildren();
        opener?.focus();
    });
});
