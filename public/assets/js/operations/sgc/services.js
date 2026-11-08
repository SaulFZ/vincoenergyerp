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
        const catalogs = JSON.parse(form.dataset.catalogs);
        const catalogMessages = new Set();
        if (!catalogs.serviceTypes.length) catalogMessages.add('No hay tipos de servicio activos. Registra o activa uno en el catálogo.');
        if (!catalogs.families.length) catalogMessages.add('No hay familias de herramientas activas. Regístralas antes de asignar herramientas.');
        if (!catalogs.tools.length) catalogMessages.add('No hay herramientas registradas. Regístralas en el catálogo antes de asignarlas.');
        form.querySelectorAll('select[data-catalog]').forEach(field => {
            if ([...field.options].some(option => option.value !== '')) return;
            if (field.dataset.emptyMessage) catalogMessages.add(field.dataset.emptyMessage);
            else if (field.name === 'well_id') catalogMessages.add('No hay pozos activos. Registra un pozo o activa uno existente.');
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
                    field.checked = (Array.isArray(value) ? value : [value]).map(String).includes(field.value);
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
        initializeServiceOperations(form, mode, service.operations || [], catalogs);
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
        const cableSelect = form.elements.namedItem('cable_id');
        const syncCable = () => {
            const cable = catalogs.cables.find(item => String(item.id) === cableSelect.value);
            form.elements.namedItem('tambor').value = cable?.drum_code || '';
        };
        const syncUre = (preload = false) => {
            const selected = ure.selectedOptions[0];
            const hasUnit = Boolean(ure.value);
            form.querySelector('#svc-ure_selected').value = hasUnit ? selected.textContent.trim() : '';
            content.querySelector('#ure-linked-summary').hidden = !hasUnit;
            const linked = hasUnit ? catalogs.cables.filter(item => String(item.current_unit_id) === ure.value) : [];
            content.querySelector('[data-ure-status]').textContent = selected?.dataset.status || 'Sin dato';
            content.querySelector('[data-ure-cable]').textContent = linked.length ? linked.map(item => item.code).join(', ') : 'Sin cable ligado';
            content.querySelector('[data-ure-drum]').textContent = linked.length ? linked.map(item => item.drum_code || 'Sin tambor').join(', ') : 'Sin tambor';
            if (preload && linked.length === 1) { cableSelect.value = String(linked[0].id); syncCable(); }
        };
        ure.addEventListener('change', () => syncUre(true));
        cableSelect.addEventListener('change', syncCable);
        syncUre(mode === 'create');
        if (cableSelect.value) syncCable();
        let trainingRequest;
        form.querySelectorAll('[data-training-for]').forEach(button => {
            button.addEventListener('click', async () => {
                const panel = form.querySelector('#employee-training-panel');
                const employeeId = form.elements.namedItem(button.dataset.trainingFor).value;
                panel.hidden = false;
                trainingRequest?.abort();
                if (!employeeId) { panel.textContent = 'Selecciona primero un empleado para consultar sus cursos.'; return; }
                const request = new AbortController(); trainingRequest = request;
                const url = new URL(form.dataset.trainingUrl, window.location.origin);
                url.searchParams.set('employee_id', employeeId);
                panel.textContent = 'Consultando cursos…';
                try {
                    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: request.signal });
                    if (!response.ok) throw new Error('No se pudieron consultar los cursos.');
                    const data = await response.json();
                    if (request.signal.aborted) return;
                    panel.replaceChildren();
                    const heading = document.createElement('h3'); heading.className = 'form-card-title';
                    heading.textContent = 'Cursos de ' + data.employee; panel.append(heading);
                    if (!data.records.length) {
                        const message = document.createElement('p');
                        message.textContent = 'Este empleado no tiene cursos asignados. Registra sus asignaciones antes de utilizar esta consulta.';
                        panel.append(message);
                    } else {
                        const list = document.createElement('ul');
                        data.records.forEach(record => {
                            const item = document.createElement('li');
                            item.textContent = [record.name, record.category, record.status, 'Emisión: ' + (record.issue_date || 'Sin fecha'), 'Vencimiento: ' + (record.expiration_date || 'Sin fecha')].filter(Boolean).join(' · ');
                            list.append(item);
                        });
                        panel.append(list);
                    }
                } catch (error) { if (error.name !== 'AbortError') panel.textContent = 'No se pudieron consultar los cursos. Inténtalo de nuevo.'; }
            });
        });
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
function initializeServiceOperations(form, mode, savedOperations = [], catalogs) {
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
    const serviceOptions = catalogs.serviceTypes;
    const updateEmpty = () => { empty.hidden = [...container.children].some(block => !block.hidden); };
    function createOperation(data = {}, plannedName = null) {
        const prefix = `operations[${operationIndex++}]`;
        const block = create('section', 'operation-card');
        const header = create('div', 'operation-heading');
        const serviceName = create('select', 'form-select');
        serviceName.name = `${prefix}[service_type_id]`; serviceName.disabled = readOnly;
        serviceName.add(new Option('Seleccionar servicio', ''));
        serviceOptions.forEach(type => serviceName.add(new Option(type.name, String(type.id))));
        serviceName.value = data.service_type_id == null ? '' : String(data.service_type_id);
        header.append(label('Servicio', serviceName), action('Quitar servicio', () => {
            if (plannedName) {
                const checkbox = [...form.querySelectorAll('[name="service_type_ids[]"]')].find(control => control.value === plannedName);
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
                const familySelect = create('select', 'form-select');
                familySelect.name = toolPrefix + '[tool_family_id]'; familySelect.disabled = readOnly;
                familySelect.add(new Option('Seleccionar familia', ''));
                catalogs.families.forEach(family => familySelect.add(new Option(family.name, String(family.id))));
                const toolSelect = create('select', 'form-select');
                toolSelect.name = toolPrefix + '[tool_id]'; toolSelect.disabled = readOnly;
                const selectedTool = catalogs.tools.find(tool => String(tool.id) === String(data.tool_id));
                familySelect.value = String(data.tool_family_id ?? selectedTool?.tool_family_id ?? '');
                const serial = input(toolPrefix + '[serial]', data.serial ?? selectedTool?.serial_number ?? '');
                serial.readOnly = true;
                const populateTools = (selectedId = '') => {
                    toolSelect.replaceChildren(new Option('Seleccionar herramienta', ''));
                    catalogs.tools.filter(tool => !familySelect.value || String(tool.tool_family_id) === familySelect.value)
                        .forEach(tool => toolSelect.add(new Option([tool.code, tool.name, tool.serial_number].filter(Boolean).join(' · '), String(tool.id))));
                    toolSelect.value = String(selectedId);
                };
                populateTools(data.tool_id ?? '');
                familySelect.addEventListener('change', () => { populateTools(); serial.value = ''; });
                toolSelect.addEventListener('change', () => {
                    const tool = catalogs.tools.find(item => String(item.id) === toolSelect.value);
                    if (tool?.tool_family_id) familySelect.value = String(tool.tool_family_id);
                    serial.value = tool?.serial_number || '';
                });
                fields.forEach(([key, title, type]) => {
                    const cell = create('td');
                    const control = key === 'family' ? familySelect : key === 'tool_name' ? toolSelect : key === 'serial' ? serial : input(`${toolPrefix}[${key}]`, data[key], type || 'text');
                    control.setAttribute('aria-label', title);
                    if (key.endsWith('_percent')) control.max = '100';
                    if (key === 'max_temperature_c') control.removeAttribute('min');
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
    const checkboxFor = name => [...form.querySelectorAll('[name="service_type_ids[]"]')].find(control => control.value === name);
    savedOperations.forEach(data => {
        const checkbox = checkboxFor(String(data.service_type_id));
        if (checkbox) checkbox.checked = true;
        createOperation(data, checkbox && !plannedBlocks.has(String(data.service_type_id)) ? String(data.service_type_id) : null);
    });
    function syncPlanned() {
        const selected = [...form.querySelectorAll('[name="service_type_ids[]"]:checked')].map(control => control.value);
        selected.forEach(name => { if (!plannedBlocks.has(name)) createOperation({service_type_id: name}, name); });
        plannedBlocks.forEach((block, name) => {
            block.hidden = !selected.includes(name);
            block.querySelectorAll('input, select, button').forEach(control => { control.disabled = readOnly || block.hidden; });
        });
        updateEmpty();
    }
    form.querySelectorAll('[name="service_type_ids[]"]').forEach(checkbox => checkbox.addEventListener('change', syncPlanned));
    addOperation.hidden = readOnly; addOperation.disabled = readOnly;
    addOperation.addEventListener('click', () => createOperation());
    syncPlanned();
}
