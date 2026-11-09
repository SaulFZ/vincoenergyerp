(() => {
    if (window.sgcOperationsSubmitBound) return;
    window.sgcOperationsSubmitBound = true;

    const filterNames = [
        'anio',
        'mes',
        'cliente',
        'pozo',
        'estado',
        'tipo',
    ];

    let sourcePromise = null;

    function readOptions(doc, name) {
        const select = doc.querySelector(
            `select[name="${name}"]`
        );

        if (!select) return [];

        return Array.from(select.options).map((option) => ({
            value: option.value,
            label: option.textContent.trim(),
            selected: option.hasAttribute('selected'),
        }));
    }

    function getSource(action) {
        if (sourcePromise) return sourcePromise;

        const url = new URL(action, window.location.origin);

        url.search = new URLSearchParams({
            view: 'data',
        }).toString();

        sourcePromise = fetch(url, {
            credentials: 'same-origin',

            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(
                        `Catálogo: HTTP ${response.status}`
                    );
                }

                return response.text();
            })
            .then((html) => {
                const doc = new DOMParser().parseFromString(
                    html,
                    'text/html'
                );

                const options = readOptions(doc, 'serviceId');

                const services = options
                    .map((option) => {
                        const parts = option.label.split(' · ');

                        if (!option.value || parts.length < 3) {
                            return null;
                        }

                        return {
                            id: option.value,
                            os: parts[0].trim(),
                            cliente: parts[1].trim(),
                            pozo: parts.slice(2).join(' · ').trim(),
                        };
                    })
                    .filter(Boolean);

                const stages = Array.from(
                    doc.querySelectorAll('h2')
                )
                    .map((heading) => ({
                        title: heading.textContent.trim(),

                        activities: Array.from(
                            heading.parentElement.querySelectorAll(
                                'p.text-lg'
                            )
                        ).map(item => item.textContent.trim()),
                    }))
                    .filter(stage => stage.activities.length);

                if (!services.length || !stages.length) {
                    throw new Error(
                        'La vista data debe contener el HTML original de operaciones.'
                    );
                }

                return {
                    services,
                    stages,

                    defaultId:
                        options.find(option => option.selected)?.value ||
                        services[0].id,

                    catalogs: Object.fromEntries(
                        filterNames.map(name => [
                            name,
                            readOptions(doc, name).filter(
                                option => option.value
                            ),
                        ])
                    ),
                };
            })
            .catch((error) => {
                sourcePromise = null;
                throw error;
            });

        return sourcePromise;
    }

    function makeUrl(path, params) {
        const url = new URL(path, window.location.origin);

        url.search = new URLSearchParams(params).toString();

        return url.href;
    }

    function renderFlow(root, source, service) {
        const flow = root.querySelector('[data-op-flow]');
        const selected = root.querySelector('[data-op-selected]');
        const empty = root.querySelector('[data-op-empty]');
        const edit = root.querySelector('[data-op-edit]');

        flow.replaceChildren();

        flow.hidden = !service;
        selected.hidden = !service;
        edit.hidden = !service;
        empty.hidden = Boolean(service);

        if (!service) return;

        root.querySelector('[data-op-os]').textContent =
            service.os;

        root.querySelector('[data-op-client]').textContent =
            service.cliente;

        root.querySelector('[data-op-well]').textContent =
            service.pozo;

        const editUrl = makeUrl('/operations/sgc/services', {
            view: 'new_service',
            mode: 'edit',
            id: service.id,
        });

        edit.href = editUrl;

        source.stages.forEach((stage) => {
            const phase = root
                .querySelector('[data-op-phase-template]')
                .content.firstElementChild.cloneNode(true);

            phase.querySelector('[data-op-phase-name]').textContent =
                stage.title;

            phase.querySelector('[data-op-phase-count]').textContent =
                `${stage.activities.length} actividades`;

            const body = phase.querySelector(
                '[data-op-phase-body]'
            );

            stage.activities.forEach((activity, index) => {
                const card = root
                    .querySelector('[data-op-activity-template]')
                    .content.firstElementChild.cloneNode(true);

                const params = {
                    serviceId: service.id,
                    stage: `${stage.title} - ${activity}`,
                };

                card.querySelector('[data-op-number]').textContent =
                    index + 1;

                card.querySelector('[data-op-activity-title]').textContent =
                    activity;

                card.querySelector('[data-op-action="edit"]').href =
                    editUrl;

                card.querySelector('[data-op-action="evidence"]').href =
                    makeUrl('/evidencias', params);

                card.querySelector('[data-op-action="upload"]').href =
                    makeUrl('/evidencias/nueva', params);

                card.querySelector('[data-op-action="catalog"]').href =
                    makeUrl('/evidencias', {
                        ...params,
                        catalogo: 'procedimientos-checklists-formatos',
                    });

                body.append(card);
            });

            flow.append(phase);
        });
    }

    async function hydrate(root) {
        const form = root.querySelector(
            '[data-operations-filters]'
        );

        const fields = root.querySelector('[data-op-fields]');
        const submit = root.querySelector('[data-op-submit]');
        const loading = root.querySelector('[data-op-loading]');
        const error = root.querySelector('[data-op-error]');
        const retry = root.querySelector('[data-op-retry]');

        fields.disabled = true;
        submit.disabled = true;
        retry.disabled = true;

        loading.hidden = false;
        error.hidden = true;

        try {
            const source = await getSource(form.action);

            if (!root.isConnected) return;

            const params = new URL(
                window.location.href
            ).searchParams;

            form.elements.namedItem('q').value =
                params.get('q') || '';

            filterNames.forEach((name) => {
                const select = form.elements.namedItem(name);
                const placeholder = select.options[0].textContent;

                select.replaceChildren(
                    new Option(placeholder, '')
                );

                source.catalogs[name].forEach((option) => {
                    select.add(
                        new Option(option.label, option.value)
                    );
                });

                const requested = params.get(name) || '';

                select.value = source.catalogs[name].some(
                    option => option.value === requested
                )
                    ? requested
                    : '';
            });

            const normalize = value => value
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase();

            const query = normalize(
                form.elements.namedItem('q').value.trim()
            );

            const client =
                form.elements.namedItem('cliente').value;

            const well =
                form.elements.namedItem('pozo').value;

            const filtered = source.services.filter((service) => {
                const matchesClient =
                    !client || service.cliente === client;

                const matchesWell =
                    !well || service.pozo === well;

                const text = normalize(
                    `${service.id} ${service.os} ${service.cliente} ${service.pozo}`
                );

                return matchesClient &&
                    matchesWell &&
                    (!query || text.includes(query));
            });

            const picker = form.elements.namedItem('serviceId');

            picker.replaceChildren();

            filtered.forEach((service) => {
                picker.add(
                    new Option(
                        `${service.os} · ${service.cliente} · ${service.pozo}`,
                        service.id
                    )
                );
            });

            if (!filtered.length) {
                picker.add(
                    new Option(
                        'No hay servicios con estos filtros',
                        ''
                    )
                );
            }

            picker.disabled = !filtered.length;

            const requestedId =
                params.get('serviceId') || source.defaultId;

            const selected = filtered.find(
                service => service.id === requestedId
            ) || filtered[0];

            picker.value = selected?.id || '';

            root.querySelector('[data-op-filtered]').textContent =
                filtered.length;

            root.querySelector('[data-op-total]').textContent =
                source.services.length;

            renderFlow(root, source, selected);

            const canonical = new URL(window.location.href);

            if (selected) {
                canonical.searchParams.set(
                    'serviceId',
                    selected.id
                );
            } else {
                canonical.searchParams.delete('serviceId');
            }

            window.history.replaceState(
                window.history.state,
                '',
                canonical.href
            );

            fields.disabled = false;
            submit.disabled = false;
        } catch (failure) {
            if (root.isConnected) {
                error.hidden = false;
            }

            console.error('Operaciones:', failure);
        } finally {
            if (root.isConnected) {
                loading.hidden = true;
                retry.disabled = false;
            }
        }
    }

    function initOperations() {
        const root = document.querySelector(
            '[data-operations-root]'
        );

        if (!root || root.dataset.initialized === '1') {
            return;
        }

        root.dataset.initialized = '1';

        root.querySelector('[data-op-retry]')
            .addEventListener('click', () => hydrate(root));

        hydrate(root);
    }

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (
            event.defaultPrevented ||
            !(form instanceof HTMLFormElement) ||
            !form.matches('#filter-form[data-operations-filters]') ||
            typeof window.fetchTabContent !== 'function'
        ) {
            return;
        }

        event.preventDefault();

        const url = new URL(
            form.action,
            window.location.origin
        );

        const params = new URLSearchParams(
            new FormData(form)
        );

        params.set('view', 'list');

        url.search = params.toString();

        window.fetchTabContent(url.href);
    });

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initOperations
        );
    } else {
        initOperations();
    }

    document.addEventListener(
        'sgcTabLoaded',
        initOperations
    );
})();
