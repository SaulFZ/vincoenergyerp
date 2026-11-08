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
        const catalogMessages = new Set();
        form.querySelectorAll('select[data-catalog]').forEach(field => {
            if ([...field.options].some(option => option.value !== '')) return;
            if (field.name === 'well_id') catalogMessages.add('No hay pozos activos. Registra un pozo o activa uno existente.');
            else if (['engineer_id', 'trainee_id'].includes(field.name)) catalogMessages.add('No hay empleados activos. Registra o activa al personal antes de asignarlo.');
            else catalogMessages.add('No hay unidades registradas. Registra las unidades antes de asignarlas como URE o recursos.');
        });
        form.querySelectorAll('[name]').forEach(field => {
            if (field.name === '_token' || field.name === 'service_id' || field.hasAttribute('data-display-only')) return;
            const key = field.name.replace(/\[\]$/, '');
            const value = key === 'tiene_npt' ? (Number(service.horas_npt) > 0 ? 'SI' : 'NO') : service[key];
            if (mode !== 'create' && value != null) {
                if (field.hasAttribute('data-catalog')) {
                    // Los catálogos aceptan únicamente IDs existentes; nunca crear opciones con nombres.
                    const ids = (Array.isArray(value) ? value : [value]).map(String);
                    const availableIds = new Set([...field.options].map(option => option.value));
                    if (ids.some(id => id !== '' && !availableIds.has(id))) {
                        const label = form.querySelector('label[for="' + field.id + '"]')?.textContent.trim() || field.name;
                        catalogMessages.add(label + ': el registro asignado no está disponible. Revisa el catálogo y selecciona un registro válido.');
                    }
                    [...field.options].forEach(option => { option.selected = ids.includes(option.value); });
                    if (!field.multiple && ![...field.options].some(option => option.selected)) field.value = '';
                } else if (field.type === 'checkbox' || field.type === 'radio') {
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
        const alert = content.querySelector('#service-catalog-alert');
        const alertItems = content.querySelector('#service-catalog-alert-items');
        alertItems.replaceChildren();
        catalogMessages.forEach(message => {
            const item = document.createElement('li');
            item.textContent = message;
            alertItems.append(item);
        });
        alert.hidden = catalogMessages.size === 0;
        initializeServiceOperations(form, mode, service.operations || []);
        const syncNpt = () => {
            const hasNpt = form.elements.namedItem('tiene_npt').value === 'SI';
            const hours = form.elements.namedItem('horas_npt');
            const eventType = form.elements.namedItem('tipo_evento_npt');
            if (!hasNpt) { hours.value = '0'; eventType.value = 'Sin NPT'; }
            else if (eventType.value === 'Sin NPT') eventType.value = 'Otro';
            ['horas_npt', 'tipo_evento_npt', 'responsable_npt', 'descripcion_npt', 'accion_correctiva_npt'].forEach(name => {
                form.elements.namedItem(name).disabled = mode === 'view' || !hasNpt;
            });
        };
        form.elements.namedItem('tiene_npt').addEventListener('change', syncNpt);
        syncNpt();
        const ure = form.elements.namedItem('ure_unit_id');
        const syncUre = () => {
            const selected = ure.selectedOptions[0];
            const hasUnit = Boolean(ure.value);
            form.querySelector('#svc-ure_selected').value = hasUnit ? selected.textContent.trim() : '';
            content.querySelector('#ure-linked-summary').hidden = !hasUnit;
            content.querySelector('[data-ure-status]').textContent = selected?.dataset.status || 'Sin dato';
            content.querySelector('[data-ure-cable]').textContent = selected?.dataset.cable || 'Sin cable ligado';
            content.querySelector('[data-ure-drum]').textContent = selected?.dataset.drum || 'Sin tambor';
            // Se precarga únicamente cuando exista una relación real de cable/tambor.
            if (selected?.dataset.cable) form.elements.namedItem('cable').value = selected.dataset.cable;
            if (selected?.dataset.drum) form.elements.namedItem('tambor').value = selected.dataset.drum;
        };
        ure.addEventListener('change', syncUre);
        syncUre();
        const totalSteps = content.querySelectorAll('.step-content').length;
        let step = 1;
        const showStep = next => {
            step = Math.min(totalSteps, Math.max(1, next));
            content.querySelectorAll('.step-content').forEach((panel, index) => panel.classList.toggle('active', index === step - 1));
            content.querySelectorAll('.step-nav').forEach((button, index) => {
                button.querySelector('.step-bubble').classList.toggle('is-active', index === step - 1);
                button.querySelector('.step-bubble').classList.toggle('is-complete', index < step - 1);
                button.querySelector('.step-label').classList.toggle('is-active', index === step - 1);
                button.setAttribute('aria-current', index === step - 1 ? 'step' : 'false');
            });
            content.querySelectorAll('.step-line').forEach((line, index) => line.classList.toggle('is-complete', index < step - 1));
            content.querySelector('#step-counter').textContent = `Paso ${step} de ${totalSteps}`;
            content.querySelector('#step-percent').textContent = `${Math.round(step / totalSteps * 100)}%`;
            content.querySelector('#progress-bar').style.width = `${Math.round(step / totalSteps * 100)}%`;
            content.querySelector('#btn-prev')?.classList.toggle('hidden', step === 1);
            content.querySelector('#btn-next')?.classList.toggle('hidden', step === totalSteps);
            content.querySelector('#btn-submit')?.classList.toggle('hidden', step !== totalSteps);
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

// Jerarquía TRIH: servicio, corridas y filas de herramientas, como el alta de SGC beta.
function initializeServiceOperations(form, mode, savedOperations = []) {
    const container = form.querySelector('#service-operations');
    const empty = form.querySelector('#operations-empty');
    const addOperation = form.querySelector('#add-trih-operation');
    const readOnly = mode === 'view';
    const plannedBlocks = new Map();
    let operationIndex = 0;
    const create = (tag, className = '', text = '') => {
        const node = document.createElement(tag);
        node.className = className;
        node.textContent = text;
        return node;
    };
    const action = (label, callback) => {
        const button = create('button', 'btn btn-outline btn-sm no-print', label);
        button.type = 'button'; button.hidden = readOnly; button.disabled = readOnly;
        button.addEventListener('click', callback);
        return button;
    };
    const input = (name, value = '', type = 'text') => {
        const control = create('input', 'form-input');
        control.name = name; control.type = type; control.disabled = readOnly;
        if (type === 'checkbox') control.checked = value === true || value === 1;
        else control.value = value ?? '';
        if (type === 'number') { control.step = 'any'; control.min = '0'; }
        return control;
    };
    const label = (title, control) => {
        const wrapper = create('label', 'form-field');
        wrapper.append(create('span', 'form-label', title), control);
        return wrapper;
    };
    const serviceOptions = () => [...form.querySelectorAll('[name="servicios[]"]')].map(checkbox => checkbox.value);
    const updateEmpty = () => { empty.hidden = [...container.children].some(block => !block.hidden); };
    function createOperation(data = {}, plannedName = null) {
        const prefix = `operations[${operationIndex++}]`;
        const block = create('section', 'operation-card');
        const header = create('div', 'operation-heading');
        const serviceName = create('select', 'form-select');
        serviceName.name = `${prefix}[service_name]`; serviceName.disabled = readOnly;
        serviceName.add(new Option('Seleccionar servicio', ''));
        [...new Set([...serviceOptions(), data.service_name].filter(Boolean))].forEach(name => serviceName.add(new Option(name, name)));
        serviceName.value = data.service_name || '';
        header.append(label('Servicio', serviceName), action('Quitar servicio', () => {
            if (plannedName) {
                const checkbox = [...form.querySelectorAll('[name="servicios[]"]')].find(control => control.value === plannedName);
                if (checkbox) checkbox.checked = false;
                plannedBlocks.delete(plannedName);
            }
            block.remove(); updateEmpty();
        }));
        block.append(header, label('Notas del servicio', input(`${prefix}[notes]`, data.notes)));
        const runs = create('div', 'operations-stack'); block.append(runs);
        let runIndex = 0;
        const renumber = () => [...runs.children].forEach((run, index) => {
            run.querySelector('h4').textContent = `Corrida ${index + 1}`;
            run.querySelector('[data-run-number]').value = index + 1;
        });
        function createRun(data = {}) {
            const runPrefix = `${prefix}[runs][${runIndex++}]`;
            const run = create('section', 'operation-run');
            const heading = create('div', 'operation-heading');
            const number = input(`${runPrefix}[run_number]`, '', 'hidden'); number.dataset.runNumber = '';
            const tools = create('tbody');
            let toolIndex = 0;
            function createTool(data = {}) {
                const toolPrefix = `${runPrefix}[tools][${toolIndex++}]`;
                const row = create('tr');
                const fields = [
                    ['family', 'Familia'], ['tool_name', 'Herramienta'], ['serial', 'Serial'], ['wl', 'WL/SL'],
                    ['hours_in_hole', 'Hrs pozo', 'number'], ['lost_time_hours', 'Lost time', 'number'],
                    ['max_temperature_c', 'Temp máx °C', 'number'], ['max_pressure_psi', 'Presión máx psi', 'number'],
                    ['max_depth_m', 'Prof. máx m', 'number'], ['h2s_percent', 'H2S %', 'number'], ['co2_percent', 'CO2 %', 'number'],
                    ['fluid', 'Fluido'], ['exposure_time', 'Exposición'], ['failed', 'Falla', 'checkbox'],
                    ['fishing', 'Pesca', 'checkbox'], ['notes', 'Notas'],
                ];
                fields.forEach(([key, title, type]) => {
                    const cell = create('td');
                    const control = input(`${toolPrefix}[${key}]`, data[key], type || 'text');
                    control.setAttribute('aria-label', title);
                    if (key.endsWith('_percent')) control.max = '100';
                    if (key === 'max_temperature_c') control.removeAttribute('min');
                    // Familia/herramienta siguen siendo captura libre hasta contar con catálogo.
                    cell.append(control); row.append(cell);
                });
                const actions = create('td');
                actions.append(action('Quitar', () => {
                    row.remove();
                    if (!tools.children.length) createTool();
                }));
                row.append(actions); tools.append(row);
            }
            heading.append(create('h4', 'form-card-title'), action('+ Herramienta', () => createTool()), action('Quitar corrida', () => { run.remove(); renumber(); }));
            run.append(heading, number, label('Notas de corrida', input(`${runPrefix}[notes]`, data.notes)));
            const wrap = create('div', 'trih-table-wrap');
            const table = create('table', 'trih-tools-table');
            const thead = create('thead'); const tr = create('tr');
            ['Familia','Herramienta','Serial','WL/SL','Hrs pozo','Lost time','Temp máx °C','Presión máx psi','Prof. máx m','H2S %','CO2 %','Fluido','Exposición','Falla','Pesca','Notas','Acción'].forEach(title => { const th = create('th', '', title); th.scope = 'col'; tr.append(th); });
            thead.append(tr); table.append(thead, tools); wrap.append(table); run.append(wrap);
            (data.tools?.length ? data.tools : [{}]).forEach(createTool);
            runs.append(run); renumber();
        }
        block.append(action('+ Agregar corrida', () => createRun()));
        (data.runs?.length ? data.runs : [{}]).forEach(createRun);
        container.append(block);
        if (plannedName) plannedBlocks.set(plannedName, block);
        updateEmpty();
        return block;
    }
    const checkboxFor = name => [...form.querySelectorAll('[name="servicios[]"]')].find(control => control.value === name);
    savedOperations.forEach(data => {
        const checkbox = checkboxFor(data.service_name);
        if (checkbox) checkbox.checked = true;
        createOperation(data, checkbox && !plannedBlocks.has(data.service_name) ? data.service_name : null);
    });
    function syncPlanned() {
        const selected = [...form.querySelectorAll('[name="servicios[]"]:checked')].map(control => control.value);
        selected.forEach(name => { if (!plannedBlocks.has(name)) createOperation({service_name: name}, name); });
        plannedBlocks.forEach((block, name) => {
            block.hidden = !selected.includes(name);
            block.querySelectorAll('input, select, button').forEach(control => { control.disabled = readOnly || block.hidden; });
        });
        updateEmpty();
    }
    form.querySelectorAll('[name="servicios[]"]').forEach(checkbox => checkbox.addEventListener('change', syncPlanned));
    addOperation.hidden = readOnly; addOperation.disabled = readOnly;
    addOperation.addEventListener('click', () => createOperation());
    syncPlanned();
}
