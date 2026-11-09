(() => {
    if (window.sgcFormatsSelectorsBound) return;
    window.sgcFormatsSelectorsBound = true;

    function initFormats() {
        const root = document.querySelector('[data-maintenance-formats]');

        if (!root || root.dataset.ready === '1') return;

        const config = JSON.parse(
            root.querySelector('[data-mf-config]').textContent
        );

        const form = root.querySelector('[data-mf-form]');
        const category = root.querySelector('[data-mf-category]');
        const equipment = root.querySelector('[data-mf-equipment]');
        const format = root.querySelector('[data-mf-format]');
        const card = root.querySelector('[data-mf-card]');
        const notice = root.querySelector('[data-mf-notice]');
        const feedback = root.querySelector('[data-mf-feedback]');
        const rows = root.querySelector('[data-mf-rows]');
        const addButton = root.querySelector('[data-mf-add]');

        const actions = root.querySelectorAll(
            '[data-mf-save], [data-mf-print], [data-mf-clear]'
        );

        // Conserva la captura al cambiar de formato o de tipo de equipo.
        const memory = new Map();
        let activeKey = null;

        const selectedCategory = () =>
            config.categories[category.value];

        const selectedFormat = () =>
            selectedCategory()?.formats?.[format.value];

        const storageKey = () => [
            'vescore.sgc.maintenance.format-draft.v1',
            config.owner,
            category.value,
            format.value,
            equipment.value,
        ].map(encodeURIComponent).join(':');

        function resizeAreas() {
            root.querySelectorAll('textarea').forEach((field) => {
                field.style.height = 'auto';
                field.style.height = `${field.scrollHeight}px`;
            });
        }

        function snapshot() {
            const fields = Object.create(null);

            Array.from(form.elements).forEach((field) => {
                if (field.name && !field.disabled) {
                    fields[field.name] = field.type === 'checkbox'
                        ? field.checked
                        : field.value;
                }
            });

            return {
                fields,
                rowCount: rows.children.length,
            };
        }

        function remember() {
            if (activeKey) {
                memory.set(activeKey, snapshot());
            }
        }

        function readDraft() {
            if (memory.has(activeKey)) {
                return memory.get(activeKey);
            }

            try {
                const draft = JSON.parse(
                    localStorage.getItem(activeKey) || 'null'
                );

                if (
                    draft?.version === 1 &&
                    draft.category === category.value &&
                    draft.format === format.value &&
                    draft.equipment === equipment.value &&
                    draft.fields &&
                    typeof draft.fields === 'object'
                ) {
                    return draft;
                }
            } catch {
                feedback.textContent =
                    'No fue posible leer el borrador de este navegador.';
            }

            return null;
        }

        function fillSelect(select, options, emptyLabel) {
            select.replaceChildren();

            options.forEach(([value, label]) => {
                select.add(new Option(label, value));
            });

            if (!options.length) {
                select.add(new Option(emptyLabel, ''));
            }

            select.disabled = !options.length;
        }

        function appendRow(activity = '', fixed = false) {
            const index = rows.children.length;
            const tr = document.createElement('tr');

            const activityCell = document.createElement('td');

            const activityField = document.createElement(
                fixed ? 'input' : 'textarea'
            );

            activityField.name = `checklist[${index}][activity]`;
            activityField.value = activity;

            if (fixed) {
                activityField.type = 'hidden';
                activityField.dataset.fixedActivity = '1';

                const label = document.createElement('span');
                label.textContent = `${index + 1}. ${activity}`;

                activityCell.append(label);
            } else {
                activityField.rows = 2;
                activityField.className = 'mf-cell-input';

                activityField.placeholder =
                    `${index + 1}. Actividad / punto de control`;

                activityField.setAttribute(
                    'aria-label',
                    `Actividad ${index + 1}`
                );
            }

            activityCell.append(activityField);
            tr.append(activityCell);

            [
                ['ok', 'OK'],
                ['na', 'No aplica'],
            ].forEach(([key, label]) => {
                const td = document.createElement('td');
                td.className = 'mf-check';

                const input = document.createElement('input');

                input.type = 'checkbox';
                input.name = `checklist[${index}][${key}]`;
                input.value = '1';
                input.dataset.mfCheck = '1';

                input.setAttribute(
                    'aria-label',
                    `${label}: actividad ${index + 1}`
                );

                td.append(input);
                tr.append(td);
            });

            const observationCell = document.createElement('td');
            const observation = document.createElement('textarea');

            observation.rows = 2;
            observation.className = 'mf-cell-input';
            observation.name = `checklist[${index}][observation]`;

            observation.setAttribute(
                'aria-label',
                `Observación: actividad ${index + 1}`
            );

            observationCell.append(observation);
            tr.append(observationCell);

            rows.append(tr);
        }

        function restore(state) {
            if (!state?.fields) return;

            Array.from(form.elements).forEach((field) => {
                if (
                    !field.name ||
                    field.matches(
                        '[data-mf-category], [data-mf-equipment], [data-mf-format]'
                    ) ||
                    field.dataset.fixedActivity === '1' ||
                    !Object.prototype.hasOwnProperty.call(
                        state.fields,
                        field.name
                    )
                ) {
                    return;
                }

                const value = state.fields[field.name];

                if (field.type === 'checkbox') {
                    field.checked = value === true;
                } else if (
                    typeof value === 'string' &&
                    (
                        field.tagName !== 'SELECT' ||
                        Array.from(field.options).some(
                            option => option.value === value
                        )
                    )
                ) {
                    field.value = value;
                }
            });

            Array.from(rows.children).forEach((row) => {
                const [ok, na] = row.querySelectorAll('[data-mf-check]');

                if (ok.checked && na.checked) {
                    na.checked = false;
                }
            });
        }

        function render(loadSaved = true) {
            const meta = selectedFormat();
            const available = Boolean(meta);

            card.hidden = !available;
            notice.hidden = available;

            actions.forEach((button) => {
                button.disabled = !available;
            });

            feedback.textContent = '';
            activeKey = available ? storageKey() : null;

            if (!available) {
                notice.textContent =
                    `No hay formatos definidos para ${
                        selectedCategory()?.label || 'esta categoría'
                    }.`;

                return;
            }

            const state = loadSaved ? readDraft() : null;

            form.querySelectorAll('[data-mf-field]').forEach((field) => {
                if (field.tagName === 'SELECT') {
                    field.selectedIndex = 0;
                } else {
                    field.value = field.name === 'date'
                        ? config.today
                        : '';
                }
            });

            // Actualiza el documento según Formato vacío.
            root.querySelector('[data-mf-title]').textContent =
                meta.title;

            root.querySelector('[data-mf-code-label]').textContent =
                format.value;

            root.querySelector('[data-mf-category-label]').textContent =
                category.value;

            rows.replaceChildren();

            if (meta.checklist.length) {
                meta.checklist.forEach((activity) => {
                    appendRow(activity, true);
                });
            } else {
                const count = Number.isInteger(state?.rowCount)
                    ? Math.max(1, Math.min(state.rowCount, 1000))
                    : 1;

                for (let i = 0; i < count; i++) {
                    appendRow();
                }
            }

            restore(state);

            // Actualiza el documento según Tipo de equipo / unidad.
            root.querySelector('[data-mf-type-label]').textContent =
                equipment.value;

            form.elements.namedItem('equipment_type_display').value =
                equipment.value;

            addButton.hidden = meta.checklist.length > 0;

            root.querySelector('[data-mf-checklist-help]').hidden =
                meta.checklist.length > 0;

            resizeAreas();
        }

        // Categoría cambia los otros dos selectores.
        category.addEventListener('change', () => {
            remember();

            const current = selectedCategory();

            fillSelect(
                equipment,
                (current.types || []).map(type => [type, type]),
                'Sin tipos definidos'
            );

            fillSelect(
                format,
                Object.entries(current.formats || {}).map(
                    ([code, meta]) => [
                        code,
                        `${code} - ${meta.title}`,
                    ]
                ),
                'Sin formatos definidos'
            );

            render();
        });

        equipment.addEventListener('change', () => {
            remember();
            render();
        });

        format.addEventListener('change', () => {
            remember();
            render();
        });

        // Un punto no puede estar marcado como OK y N/A a la vez.
        rows.addEventListener('change', (event) => {
            if (
                !event.target.matches('[data-mf-check]') ||
                !event.target.checked
            ) {
                return;
            }

            event.target.closest('tr')
                .querySelectorAll('[data-mf-check]')
                .forEach((input) => {
                    if (input !== event.target) {
                        input.checked = false;
                    }
                });
        });

        root.addEventListener('input', (event) => {
            if (event.target.tagName === 'TEXTAREA') {
                resizeAreas();
            }
        });

        addButton.addEventListener('click', () => {
            appendRow();
            resizeAreas();
        });

        root.querySelector('[data-mf-print]')
            .addEventListener('click', () => {
                if (selectedFormat()) {
                    resizeAreas();
                    window.print();
                }
            });

        root.querySelector('[data-mf-clear]')
            .addEventListener('click', () => {
                if (!activeKey) return;

                memory.delete(activeKey);

                let removed = true;

                try {
                    localStorage.removeItem(activeKey);
                } catch {
                    removed = false;
                }

                render(false);

                feedback.textContent = removed
                    ? 'Campos y borrador limpiados.'
                    : 'Campos limpiados; no se pudo eliminar el borrador del navegador.';
            });

        form.addEventListener('submit', (event) => {
            event.preventDefault();

            if (!activeKey) return;

            const state = snapshot();

            memory.set(activeKey, state);

            try {
                localStorage.setItem(
                    activeKey,
                    JSON.stringify({
                        version: 1,
                        category: category.value,
                        format: format.value,
                        equipment: equipment.value,
                        ...state,
                    })
                );

                feedback.textContent =
                    'Borrador guardado en este navegador.';
            } catch {
                feedback.textContent =
                    'No fue posible guardar el borrador en este navegador.';
            }
        });

        root.dataset.ready = '1';

        render();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFormats);
    } else {
        initFormats();
    }

    document.addEventListener('sgcTabLoaded', initFormats);
})();
