(() => {
    'use strict';

    const root = document.getElementById('assignmentApp');

    if (!root) return;

    const $ = (selector) => root.querySelector(selector);

    const esc = (value) =>
        String(value ?? '').replace(/[&<>"']/g, (character) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        })[character]);

    const norm = (value) =>
        String(value ?? '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');

    const iso = (date) =>
        `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;

    const today = () => iso(new Date());

    const ago = (days) => {
        const date = new Date();
        date.setDate(date.getDate() - days);
        return iso(date);
    };

    const fmt = (date) =>
        date ? date.split('-').reverse().join('/') : '—';

    const validDate = (date) =>
        /^\d{4}-\d{2}-\d{2}$/.test(date) &&
        iso(new Date(`${date}T12:00:00`)) === date;

    const nameOf = (item) => `${item.marca} ${item.modelo}`;

    const serialOf = (item) =>
        item.serie_teclado || item.serie_mouse
            ? [
                item.serie_teclado && `Teclado: ${item.serie_teclado}`,
                item.serie_mouse && `Mouse: ${item.serie_mouse}`,
                item.serie_receptor && `Receptor: ${item.serie_receptor}`,
            ].filter(Boolean).join(' · ')
            : item.serie || 'Sin número de serie';

    /* ===================================================
       DATOS DE EJEMPLO

       Son independientes del módulo de equipos.
       No se realizan solicitudes a una API.
       =================================================== */

    const people = [
        {
            id: 1,
            numero: 'EMP-001',
            nombre: 'Ana Martínez',
            area: 'Recursos Humanos',
            puesto: 'Coordinadora',
        },
        {
            id: 2,
            numero: 'EMP-002',
            nombre: 'Carlos López',
            area: 'Operaciones',
            puesto: 'Supervisor',
        },
        {
            id: 3,
            numero: 'EMP-003',
            nombre: 'María Hernández',
            area: 'Contabilidad',
            puesto: 'Analista',
        },
        {
            id: 4,
            numero: 'EMP-004',
            nombre: 'Luis Ramírez',
            area: 'TI',
            puesto: 'Soporte técnico',
        },
        {
            id: 5,
            numero: 'EMP-005',
            nombre: 'Diana Torres',
            area: 'Ventas',
            puesto: 'Ejecutiva',
        },
        {
            id: 6,
            numero: 'EMP-006',
            nombre: 'Jorge Sánchez',
            area: 'Ingeniería',
            puesto: 'Ingeniero',
        },
    ];

    const included = (tipo, marca, modelo, serie = '') => ({
        tipo,
        marca,
        modelo,
        serie,
    });

    const asset = (
        id,
        folio,
        categoria,
        tipo,
        marca,
        modelo,
        serie,
        area,
        condicion,
        incluidos = []
    ) => ({
        id,
        folio,
        categoria,
        tipo,
        marca,
        modelo,
        serie,
        area,
        condicion,
        incluidos,
        estado: 'disponible',
    });

    const assets = [
        asset(
            1,
            'VES-LAP-001',
            'Laptops',
            'Convencional',
            'Lenovo',
            'ThinkPad E16',
            'PF6GFE6R',
            'Recursos Humanos',
            'Seminuevo',
            [
                included('Cargador', 'Lenovo', '65 W USB-C', 'CHG-001'),
                included('Mouse', 'Logitech', 'M220', 'MS-001'),
                included('Mochila', 'Lenovo', '16 pulgadas'),
            ]
        ),
        asset(
            2,
            'VES-LAP-002',
            'Laptops',
            'Convencional',
            'ASUS',
            'ExpertBook B1',
            'ASB1400K22',
            'TI',
            'Nuevo',
            [
                included('Cargador', 'ASUS', '65 W', 'CHG-002'),
            ]
        ),
        asset(
            3,
            'VES-DES-001',
            'Computadoras de escritorio',
            'Formato pequeño (SFF)',
            'Dell',
            'OptiPlex 7010',
            'DO7010S55',
            'Recursos Humanos',
            'Seminuevo',
            [
                {
                    tipo: 'Combo teclado y mouse',
                    marca: 'Logitech',
                    modelo: 'MK270',
                    serie_teclado: 'KB-270',
                    serie_mouse: 'MS-270',
                    serie_receptor: 'RCV-270',
                },
            ]
        ),
        asset(
            4,
            'VES-MON-001',
            'Monitores',
            'De escritorio',
            'Dell',
            'P2422H',
            'CN0P2422H1',
            'TI',
            'Usado',
            [
                included('Cable HDMI', 'Genérico', '1.8 m'),
            ]
        ),
        asset(
            5,
            'VES-CEL-001',
            'Telefonía móvil',
            'Teléfono',
            'Samsung',
            'Galaxy A54',
            'RF8T30ABCDE',
            'Ventas',
            'Nuevo',
            [
                included('Cargador', 'Samsung', '25 W', 'CHG-005'),
            ]
        ),
        asset(
            6,
            'VES-PRY-001',
            'Proyección',
            'Proyector',
            'Epson',
            'PowerLite E20',
            'X4RK7300112',
            'Operaciones',
            'Usado',
            [
                included('Control remoto', 'Epson', 'Original'),
                included('Cable HDMI', 'Genérico', '1.8 m'),
            ]
        ),
        {
            ...asset(
                7,
                'VES-ACC-001',
                'Accesorios',
                'Combo teclado y mouse',
                'Logitech',
                'MK295',
                '',
                'TI',
                'Nuevo'
            ),
            serie_teclado: 'KB-295',
            serie_mouse: 'MS-295',
            serie_receptor: 'RCV-295',
        },
        asset(
            8,
            'VES-IMP-001',
            'Impresoras',
            'De documentos',
            'HP',
            'LaserJet Pro',
            'PHBVN12345',
            'Contabilidad',
            'Usado',
            [
                included('Cable de poder', 'Genérico', 'AC'),
                included('Cable de red', 'Genérico', 'Cat6'),
            ]
        ),
        asset(
            9,
            'VES-ACC-002',
            'Accesorios',
            'Cargador',
            'Lenovo',
            '65 W USB-C',
            'CHG-INDEPENDIENTE',
            'TI',
            'Nuevo'
        ),
        {
            ...asset(
                10,
                'VES-LAP-003',
                'Laptops',
                'Convencional',
                'HP',
                'ProBook 450',
                'HP450G10A7',
                'TI',
                'Usado'
            ),
            estado: 'en_mantenimiento',
        },
    ];

    const personById = (id) =>
        people.find((person) => person.id === Number(id));

    const assetById = (id) =>
        assets.find((item) => item.id === Number(id));

    const snapshot = (item) => ({
        ...item,
        incluidos: item.incluidos.map((part) => ({ ...part })),
    });

    const assignments = [
        {
            id: 1,
            folio: 'VES-ASG-0001',
            personaId: 1,
            tipo: 'asignacion',
            fechaEntrega: ago(20),
            fechaPrevista: '',
            estado: 'activa',
            equipos: [
                snapshot(assetById(1)),
                snapshot(assetById(3)),
            ],
            notasEntrega: 'Entrega de equipos para actividades del área.',
            fechaDevolucion: '',
            notasDevolucion: '',
            destinos: {},
        },
        {
            id: 2,
            folio: 'VES-ASG-0002',
            personaId: 2,
            tipo: 'prestamo',
            fechaEntrega: ago(7),
            fechaPrevista: ago(2),
            estado: 'activa',
            equipos: [
                snapshot(assetById(6)),
            ],
            notasEntrega: 'Préstamo para capacitación.',
            fechaDevolucion: '',
            notasDevolucion: '',
            destinos: {},
        },
        {
            id: 3,
            folio: 'VES-ASG-0003',
            personaId: 3,
            tipo: 'asignacion',
            fechaEntrega: ago(30),
            fechaPrevista: '',
            estado: 'devuelta',
            equipos: [
                snapshot(assetById(4)),
            ],
            notasEntrega: 'Monitor de apoyo.',
            fechaDevolucion: ago(15),
            notasDevolucion: 'Recibido con su cable HDMI.',
            destinos: {
                4: 'disponible',
            },
        },
    ];

    assignments
        .filter((record) => record.estado === 'activa')
        .forEach((record) => {
            record.equipos.forEach((item) => {
                assetById(item.id).estado =
                    record.tipo === 'prestamo'
                        ? 'en_prestamo'
                        : 'asignado';
            });
        });

    const recordById = (id) =>
        assignments.find((record) => record.id === Number(id));

    const overdue = (record) =>
        record.estado === 'activa' &&
        record.tipo === 'prestamo' &&
        record.fechaPrevista < today();

    const nextId = () =>
        Math.max(0, ...assignments.map((record) => record.id)) + 1;

    const folioOf = (id) =>
        `VES-ASG-${String(id).padStart(4, '0')}`;

    /* ===================================================
       REGISTRO Y DEVOLUCIÓN EN MEMORIA
       =================================================== */

    function createAssignment(data) {
        if (!personById(data.personaId)) {
            throw new Error('Selecciona un colaborador válido.');
        }

        if (!['asignacion', 'prestamo'].includes(data.tipo)) {
            throw new Error('Selecciona un tipo de entrega válido.');
        }

        if (
            !validDate(data.fechaEntrega) ||
            data.fechaEntrega > today()
        ) {
            throw new Error(
                'Revisa la fecha de entrega; no puede ser futura.'
            );
        }

        if (
            data.tipo === 'prestamo' &&
            (
                !validDate(data.fechaPrevista) ||
                data.fechaPrevista < data.fechaEntrega
            )
        ) {
            throw new Error(
                'La devolución prevista debe ser igual o posterior a la entrega.'
            );
        }

        if (
            !data.equipos.length ||
            new Set(data.equipos).size !== data.equipos.length
        ) {
            throw new Error('Selecciona equipos sin repetir.');
        }

        const selected = data.equipos.map(assetById);

        if (
            selected.some(
                (item) => !item || item.estado !== 'disponible'
            )
        ) {
            throw new Error(
                'Uno de los equipos ya no está disponible. Revisa la selección.'
            );
        }

        const id = nextId();

        const record = {
            id,
            folio: folioOf(id),
            personaId: Number(data.personaId),
            tipo: data.tipo,
            fechaEntrega: data.fechaEntrega,
            fechaPrevista:
                data.tipo === 'prestamo'
                    ? data.fechaPrevista
                    : '',
            estado: 'activa',
            equipos: selected.map(snapshot),
            notasEntrega: data.notasEntrega.trim(),
            fechaDevolucion: '',
            notasDevolucion: '',
            destinos: {},
        };

        selected.forEach((item) => {
            item.estado =
                data.tipo === 'prestamo'
                    ? 'en_prestamo'
                    : 'asignado';
        });

        assignments.push(record);

        return record;
    }

    function returnAssignment(id, date, destinations, notes) {
        const record = recordById(id);

        if (!record || record.estado !== 'activa') {
            throw new Error('La asignación ya no está activa.');
        }

        if (
            !validDate(date) ||
            date < record.fechaEntrega ||
            date > today()
        ) {
            throw new Error(
                'La devolución debe estar entre la fecha de entrega y hoy.'
            );
        }

        if (
            record.equipos.some(
                (item) =>
                    !['disponible', 'en_mantenimiento'].includes(
                        destinations[item.id]
                    )
            )
        ) {
            throw new Error('Indica cómo se recibe cada equipo.');
        }

        record.equipos.forEach((item) => {
            assetById(item.id).estado = destinations[item.id];
        });

        record.estado = 'devuelta';
        record.fechaDevolucion = date;
        record.notasDevolucion = notes.trim();
        record.destinos = { ...destinations };
    }

    /* ===================================================
       ESTADO DE LA VISTA
       =================================================== */

    let page = 1;
    const pageSize = 10;

    let selectedPerson = null;
    let selectedAssets = new Set();
    let returningId = null;
    let toastTimer;

    const showError = (selector, message = '') => {
        const element = $(selector);
        element.textContent = message;
        element.hidden = !message;
    };

    const showDialog = (id) => $(id).showModal();

    function toast(message) {
        $('#iaToast').textContent = message;
        $('#iaToast').hidden = false;

        clearTimeout(toastTimer);

        toastTimer = setTimeout(() => {
            $('#iaToast').hidden = true;
        }, 4000);
    }

    const statusHTML = (record) =>
        record.estado === 'devuelta'
            ? '<span class="ia-badge">Devuelta</span>'
            : overdue(record)
                ? '<span class="ia-badge overdue">Préstamo vencido</span>'
                : '<span class="ia-badge active">Activa</span>';

    const typeHTML = (record) =>
        record.tipo === 'prestamo'
            ? '<span class="ia-badge loan">Préstamo temporal</span>'
            : '<span class="ia-badge">Asignación</span>';

    /* ===================================================
       INDICADORES Y TABLA
       =================================================== */

    function render() {
        const active = assignments.filter(
            (record) => record.estado === 'activa'
        );

        $('#iaStatActive').textContent = active.length;

        $('#iaStatEquipment').textContent = active.reduce(
            (total, record) => total + record.equipos.length,
            0
        );

        $('#iaStatAvailable').textContent = assets.filter(
            (item) => item.estado === 'disponible'
        ).length;

        $('#iaStatOverdue').textContent =
            assignments.filter(overdue).length;

        renderTable();
    }

    function renderTable() {
        const query = norm($('#iaQuery').value.trim());
        const status = $('#iaStatusFilter').value;

        const filtered = assignments.filter((record) => {
            if (
                status === 'vencida'
                    ? !overdue(record)
                    : status && record.estado !== status
            ) {
                return false;
            }

            const person = personById(record.personaId);

            return norm([
                record.folio,
                person.nombre,
                person.numero,
                person.area,
                ...record.equipos.flatMap((item) => [
                    item.folio,
                    nameOf(item),
                    serialOf(item),
                ]),
            ].join(' ')).includes(query);
        }).sort((a, b) => b.id - a.id);

        const pages = Math.max(
            1,
            Math.ceil(filtered.length / pageSize)
        );

        page = Math.min(Math.max(1, page), pages);

        const start = (page - 1) * pageSize;

        $('#iaResultCount').textContent =
            `${filtered.length} registros`;

        $('#iaPageInfo').textContent =
            `Mostrando ${filtered.length ? start + 1 : 0}–` +
            `${Math.min(start + pageSize, filtered.length)} ` +
            `de ${filtered.length} · Página ${page} de ${pages}`;

        $('#iaPrev').disabled = page === 1;
        $('#iaNext').disabled = page === pages;

        $('#iaRows').innerHTML = filtered
            .slice(start, start + pageSize)
            .map((record) => {
                const person = personById(record.personaId);

                return `
                    <tr>
                        <td>
                            <code class="ia-code">
                                ${esc(record.folio)}
                            </code>
                        </td>

                        <td>
                            <strong>${esc(person.nombre)}</strong>
                            <small>
                                ${esc(person.numero)} · ${esc(person.area)}
                            </small>
                        </td>

                        <td>
                            <span class="ia-count">
                                ${record.equipos.length}
                            </span>
                        </td>

                        <td>${typeHTML(record)}</td>

                        <td>${fmt(record.fechaEntrega)}</td>

                        <td>
                            ${fmt(record.fechaPrevista)}
                            ${
                                overdue(record)
                                    ? '<small>Devolución pendiente</small>'
                                    : ''
                            }
                        </td>

                        <td>${statusHTML(record)}</td>

                        <td class="ia-actions">
                            <button
                                type="button"
                                class="ia-icon-btn"
                                data-detail="${record.id}"
                                aria-label="Ver ${esc(record.folio)}"
                            >
                                <i
                                    class="fas fa-eye"
                                    aria-hidden="true"
                                ></i>
                            </button>

                            ${
                                record.estado === 'activa'
                                    ? `
                                        <button
                                            type="button"
                                            class="ia-icon-btn"
                                            data-return="${record.id}"
                                            aria-label="Devolver equipos de ${esc(record.folio)}"
                                        >
                                            <i
                                                class="fas fa-rotate-left"
                                                aria-hidden="true"
                                            ></i>
                                        </button>
                                    `
                                    : ''
                            }
                        </td>
                    </tr>
                `;
            })
            .join('') ||
            `
                <tr>
                    <td colspan="8">
                        <div class="ia-empty">
                            No se encontraron asignaciones con esos filtros.
                        </div>
                    </td>
                </tr>
            `;
    }

    /* ===================================================
       ACCESORIOS INCLUIDOS
       =================================================== */

    const includedHTML = (item) =>
        item.incluidos.length
            ? `
                <ul class="ia-included">
                    ${item.incluidos.map((part) => `
                        <li>
                            ${esc(part.tipo)} ·
                            ${esc(
                                [part.marca, part.modelo]
                                    .filter(Boolean)
                                    .join(' ')
                            )} ·
                            ${esc(serialOf(part))}
                        </li>
                    `).join('')}
                </ul>
            `
            : '<p class="ia-help">Sin accesorios incluidos.</p>';

    /* ===================================================
       BÚSQUEDA DE COLABORADORES
       =================================================== */

    function renderPeople() {
        const query = norm(
            $('#iaPersonSearch').value.trim()
        );

        const results = people.filter(
            (person) =>
                norm(
                    `${person.nombre} ${person.numero} ${person.area}`
                ).includes(query)
        ).slice(0, 6);

        $('#iaPersonResults').hidden = false;

        $('#iaPersonResults').innerHTML =
            results.map((person) => `
                <button
                    type="button"
                    class="ia-person-option"
                    data-person="${person.id}"
                >
                    <strong>${esc(person.nombre)}</strong>
                    <small>
                        ${esc(person.numero)} ·
                        ${esc(person.area)} ·
                        ${esc(person.puesto)}
                    </small>
                </button>
            `).join('') ||
            '<p class="ia-empty">No se encontraron colaboradores.</p>';
    }

    /* ===================================================
       SELECCIÓN DE EQUIPOS DISPONIBLES
       =================================================== */

    function renderPicker() {
        const query = norm(
            $('#iaAssetSearch').value.trim()
        );

        const results = assets.filter(
            (item) =>
                item.estado === 'disponible' &&
                norm(
                    `${item.folio} ${nameOf(item)} ` +
                    `${serialOf(item)} ${item.categoria} ${item.tipo}`
                ).includes(query)
        );

        $('#iaAssetResults').innerHTML =
            results.map((item) => `
                <label class="ia-picker-row">
                    <input
                        type="checkbox"
                        data-asset="${item.id}"
                        ${selectedAssets.has(item.id) ? 'checked' : ''}
                    >

                    <span>
                        <strong>${esc(nameOf(item))}</strong>

                        <small>
                            ${esc(item.folio)} ·
                            ${esc(item.tipo)} ·
                            ${esc(serialOf(item))}
                        </small>

                        <small>
                            ${esc(item.condicion)} ·
                            Área de pertenencia: ${esc(item.area)} ·
                            ${item.incluidos.length} incluidos
                        </small>
                    </span>

                    <span class="ia-badge active">Disponible</span>
                </label>
            `).join('') ||
            `
                <div class="ia-empty">
                    No hay equipos disponibles para esta búsqueda.
                </div>
            `;
    }

    function renderSelected() {
        $('#iaSelectionCount').textContent =
            selectedAssets.size;

        $('#iaSelectionError').hidden = true;

        $('#iaSelected').innerHTML =
            [...selectedAssets].map((id) => {
                const item = assetById(id);

                return `
                    <article class="ia-selected-card">
                        <strong>
                            ${esc(item.folio)} · ${esc(nameOf(item))}
                        </strong>

                        <button
                            type="button"
                            class="ia-icon-btn"
                            data-remove="${id}"
                            aria-label="Quitar ${esc(item.folio)}"
                        >
                            <i
                                class="fas fa-xmark"
                                aria-hidden="true"
                            ></i>
                        </button>

                        <details>
                            <summary>
                                ${item.incluidos.length} accesorios incluidos
                            </summary>
                            ${includedHTML(item)}
                        </details>
                    </article>
                `;
            }).join('');
    }

    function deliveryTypeChanged() {
        const loan =
            $('#iaDeliveryType').value === 'prestamo';

        $('#iaExpectedWrap').hidden = !loan;
        $('#iaExpectedDate').required = loan;

        $('#iaExpectedDate').min =
            $('#iaDeliveryDate').value;

        if (!loan) {
            $('#iaExpectedDate').value = '';
        }
    }

    function openCreate() {
        $('#iaCreateForm').reset();

        selectedPerson = null;
        selectedAssets = new Set();

        $('#iaPersonSearch').setCustomValidity('');
        $('#iaPersonResults').hidden = true;
        $('#iaPersonCard').hidden = true;

        $('#iaFolio').value = folioOf(nextId());

        $('#iaDeliveryDate').value = today();
        $('#iaDeliveryDate').max = today();

        showError('#iaCreateError');

        deliveryTypeChanged();
        renderPicker();
        renderSelected();

        showDialog('#iaCreateDialog');

        $('#iaPersonSearch').focus();
    }

    /* ===================================================
       DETALLE
       =================================================== */

    function openDetail(id) {
        const record = recordById(id);

        if (!record) return;

        const person = personById(record.personaId);

        const row = (label, value) => `
            <div>
                <dt>${esc(label)}</dt>
                <dd>${value}</dd>
            </div>
        `;

        $('#iaDetailSubtitle').textContent =
            record.folio;

        $('#iaDetailData').innerHTML = [
            row('Responsable', esc(person.nombre)),
            row('Número de empleado', esc(person.numero)),
            row('Área del responsable', esc(person.area)),
            row('Puesto', esc(person.puesto)),
            row('Tipo de entrega', typeHTML(record)),
            row('Estado', statusHTML(record)),
            row('Fecha de entrega', fmt(record.fechaEntrega)),
            row('Devolución prevista', fmt(record.fechaPrevista)),
        ].join('');

        $('#iaDetailItems').innerHTML =
            record.equipos.map((item) => `
                <article class="ia-item">
                    <code class="ia-code">${esc(item.folio)}</code>

                    <h4>${esc(nameOf(item))}</h4>

                    <p>
                        ${esc(item.categoria)} ·
                        ${esc(item.tipo)} ·
                        ${esc(serialOf(item))}
                    </p>

                    <p>
                        Estado al entregar: ${esc(item.condicion)} ·
                        Área de pertenencia: ${esc(item.area)}
                    </p>

                    ${includedHTML(item)}

                    ${
                        record.estado === 'devuelta'
                            ? `
                                <p>
                                    Recepción:
                                    ${
                                        record.destinos[item.id] === 'en_mantenimiento'
                                            ? 'En mantenimiento'
                                            : 'Disponible'
                                    }
                                </p>
                            `
                            : ''
                    }
                </article>
            `).join('');

        $('#iaDetailNotes').textContent =
            record.notasEntrega || 'Sin observaciones.';

        $('#iaReturnInfo').hidden =
            record.estado !== 'devuelta';

        $('#iaReturnNotes').textContent =
            `Fecha de devolución: ${fmt(record.fechaDevolucion)}\n` +
            `${record.notasDevolucion || 'Sin observaciones.'}`;

        $('#iaDetailReturn').hidden =
            record.estado !== 'activa';

        $('#iaDetailReturn').dataset.return =
            record.id;

        showDialog('#iaDetailDialog');
    }

    /* ===================================================
       DEVOLUCIÓN
       =================================================== */

    function openReturn(id) {
        const record = recordById(id);

        if (!record || record.estado !== 'activa') return;

        if ($('#iaDetailDialog').open) {
            $('#iaDetailDialog').close();
        }

        returningId = record.id;

        $('#iaReturnForm').reset();

        $('#iaReturnSubtitle').textContent =
            `${record.folio} · ${personById(record.personaId).nombre}`;

        $('#iaReturnedDate').value = today();
        $('#iaReturnedDate').min = record.fechaEntrega;
        $('#iaReturnedDate').max = today();

        $('#iaReturnItems').innerHTML =
            record.equipos.map((item) => `
                <div class="ia-return-row">
                    <div>
                        <strong>${esc(nameOf(item))}</strong>
                        <small>
                            ${esc(item.folio)} ·
                            ${item.incluidos.length} incluidos
                        </small>
                    </div>

                    <label class="ia-field">
                        <span>Estado operativo al recibir</span>

                        <select
                            data-destination="${item.id}"
                            required
                        >
                            <option value="disponible">
                                Disponible
                            </option>
                            <option value="en_mantenimiento">
                                En mantenimiento
                            </option>
                        </select>
                    </label>
                </div>
            `).join('');

        showError('#iaReturnError');

        showDialog('#iaReturnDialog');
    }

    /* ===================================================
       EVENTOS
       =================================================== */

    function init() {
        render();

        $('#iaNew').addEventListener(
            'click',
            openCreate
        );

        $('#iaQuery').addEventListener('input', () => {
            page = 1;
            renderTable();
        });

        $('#iaStatusFilter').addEventListener('change', () => {
            page = 1;
            renderTable();
        });

        $('#iaPrev').addEventListener('click', () => {
            page--;
            renderTable();
        });

        $('#iaNext').addEventListener('click', () => {
            page++;
            renderTable();
        });

        $('#iaPersonSearch').addEventListener('input', () => {
            selectedPerson = null;

            $('#iaPersonCard').hidden = true;
            $('#iaPersonSearch').setCustomValidity('');

            renderPeople();
        });

        $('#iaPersonSearch').addEventListener('focus', () => {
            if (!selectedPerson) {
                renderPeople();
            }
        });

        $('#iaPersonResults').addEventListener('click', (event) => {
            const button =
                event.target.closest('[data-person]');

            if (!button) return;

            selectedPerson =
                personById(button.dataset.person);

            $('#iaPersonSearch').value =
                `${selectedPerson.nombre} · ${selectedPerson.numero}`;

            $('#iaPersonSearch').setCustomValidity('');

            $('#iaPersonResults').hidden = true;
            $('#iaPersonCard').hidden = false;

            $('#iaPersonCard').innerHTML = `
                <strong>${esc(selectedPerson.nombre)}</strong>
                <span>
                    ${esc(selectedPerson.numero)} ·
                    ${esc(selectedPerson.area)} ·
                    ${esc(selectedPerson.puesto)}
                </span>
            `;

            $('#iaDeliveryDate').focus();
        });

        $('#iaAssetSearch').addEventListener(
            'input',
            renderPicker
        );

        $('#iaAssetResults').addEventListener('change', (event) => {
            const input =
                event.target.closest('[data-asset]');

            if (!input) return;

            const id = Number(input.dataset.asset);

            if (input.checked) {
                selectedAssets.add(id);
            } else {
                selectedAssets.delete(id);
            }

            renderSelected();
        });

        $('#iaSelected').addEventListener('click', (event) => {
            const button =
                event.target.closest('[data-remove]');

            if (!button) return;

            selectedAssets.delete(
                Number(button.dataset.remove)
            );

            renderPicker();
            renderSelected();

            $('#iaAssetSearch').focus();
        });

        $('#iaDeliveryType').addEventListener(
            'change',
            deliveryTypeChanged
        );

        $('#iaDeliveryDate').addEventListener(
            'change',
            deliveryTypeChanged
        );

        $('#iaCreateForm').addEventListener('submit', (event) => {
            event.preventDefault();

            showError('#iaCreateError');

            $('#iaPersonSearch').setCustomValidity(
                selectedPerson
                    ? ''
                    : 'Selecciona un colaborador de los resultados.'
            );

            if (!event.currentTarget.reportValidity()) {
                return;
            }

            if (!selectedAssets.size) {
                $('#iaSelectionError').hidden = false;
                $('#iaAssetSearch').focus();
                return;
            }

            try {
                const record = createAssignment({
                    personaId: selectedPerson.id,
                    tipo: $('#iaDeliveryType').value,
                    fechaEntrega: $('#iaDeliveryDate').value,
                    fechaPrevista: $('#iaExpectedDate').value,
                    notasEntrega: $('#iaDeliveryNotes').value,
                    equipos: [...selectedAssets],
                });

                $('#iaCreateDialog').close();

                $('#iaQuery').value = '';
                $('#iaStatusFilter').value = 'activa';

                page = 1;

                render();

                toast(
                    `Asignación ${record.folio} registrada.`
                );
            } catch (error) {
                showError(
                    '#iaCreateError',
                    error.message
                );
            }
        });

        $('#iaReturnForm').addEventListener('submit', (event) => {
            event.preventDefault();

            showError('#iaReturnError');

            if (!event.currentTarget.reportValidity()) {
                return;
            }

            const destinations = Object.fromEntries(
                [
                    ...$('#iaReturnItems').querySelectorAll(
                        '[data-destination]'
                    ),
                ].map((select) => [
                    select.dataset.destination,
                    select.value,
                ])
            );

            try {
                returnAssignment(
                    returningId,
                    $('#iaReturnedDate').value,
                    destinations,
                    $('#iaReturnedNotes').value
                );

                $('#iaReturnDialog').close();

                $('#iaStatusFilter').value = '';

                page = 1;

                render();

                toast(
                    'Devolución registrada. Se actualizaron los equipos.'
                );
            } catch (error) {
                showError(
                    '#iaReturnError',
                    error.message
                );
            }
        });

        root.addEventListener('click', (event) => {
            const close =
                event.target.closest('[data-close]');

            if (close) {
                close.closest('dialog').close();
                return;
            }

            const detail =
                event.target.closest('[data-detail]');

            if (detail) {
                openDetail(detail.dataset.detail);
                return;
            }

            const returned =
                event.target.closest('[data-return]');

            if (returned) {
                openReturn(returned.dataset.return);
            }
        });

        root.querySelectorAll('dialog').forEach((dialog) => {
            dialog.addEventListener('click', (event) => {
                if (event.target !== dialog) return;

                const rect =
                    dialog.getBoundingClientRect();

                if (
                    event.clientX < rect.left ||
                    event.clientX > rect.right ||
                    event.clientY < rect.top ||
                    event.clientY > rect.bottom
                ) {
                    dialog.close();
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            init
        );
    } else {
        init();
    }
})();
