/* ======================================================= */
/* INVENTARIO TI — datos falsos + render (dashboard/inventario) */
/* Todo vive aquí: cuando haya BD, reemplaza EQUIPOS y         */
/* MANTENIMIENTOS por un fetch() a tu API.                     */
/* ======================================================= */
(function () {
    'use strict';

    // =====================================================
    // 1. CATÁLOGOS
    // =====================================================
    const TIPOS = {
        laptop:         { label: 'Laptops',        singular: 'Laptop',           icon: 'fa-laptop',               color: 'blue'   },
        desktop:        { label: 'Desktops',       singular: 'Desktop',          icon: 'fa-desktop',              color: 'indigo' },
        monitor:        { label: 'Monitores',      singular: 'Monitor',          icon: 'fa-display',              color: 'teal'   },
        celular:        { label: 'Celulares',      singular: 'Celular',          icon: 'fa-mobile-screen',        color: 'green'  },
        tablet:         { label: 'Tablets',        singular: 'Tablet',           icon: 'fa-tablet-screen-button', color: 'violet' },
        impresora:      { label: 'Impresoras',     singular: 'Impresora',        icon: 'fa-print',                color: 'amber'  },
        almacenamiento: { label: 'Almacenamiento', singular: 'Disp. almacenaje', icon: 'fa-hard-drive',           color: 'slate'  },
        red:            { label: 'Redes',          singular: 'Equipo de red',    icon: 'fa-network-wired',        color: 'cyan'   },
        proyector:      { label: 'Proyectores',    singular: 'Proyector',        icon: 'fa-video',                color: 'rose'   },
        accesorio:      { label: 'Accesorios',     singular: 'Accesorio',        icon: 'fa-plug',                 color: 'orange' },
    };

    const ESTADOS = {
        asignado:      { label: 'Asignado',         badge: 'badge-assigned' },
        disponible:    { label: 'Disponible',       badge: 'badge-free'     },
        mantenimiento: { label: 'En mantenimiento', badge: 'badge-maint'    },
        falla:         { label: 'Con falla',        badge: 'badge-fail'     },
        baja:          { label: 'Baja',             badge: 'badge-retired'  },
    };

    // =====================================================
    // 2. DATOS FALSOS
    // =====================================================
    const p = (nombre, noEmp, puesto, area, desde) => ({ nombre, noEmp, puesto, area, desde });

    const e = (codigo, tipo, marca, modelo, serie, estado, extra = {}) => ({
        id: codigo,
        codigo, tipo, marca, modelo, serie, estado,
        asignado: null,
        ubicacion: 'Almacén IT',
        proveedor: 'CompuSur de Tabasco',
        fecha_compra: '2025-06-15',
        fecha_alta: '2025-06-20',
        garantia_hasta: '2028-06-15',
        specs: {},
        accesorios: [],
        observaciones: '',
        ...extra,
    });

    const pc = (disco, cpu, ram, so = 'Windows 11 Pro', sw = 'Office 365, OneDrive') => ({
        'Disco duro': disco,
        'Procesador': cpu,
        'Memoria': ram,
        'Sistema operativo': so,
        'Software': sw,
    });

    const EQUIPOS = [
        // ── Laptops ──
        e('VEC-LAP-001', 'laptop', 'Lenovo', 'ThinkPad E16 Gen 3', 'PF6GFE6R', 'asignado', {
            asignado: p('Emmanuel Cortés Javier', 'V10215', 'Ingeniero en Mtto e Instrumentación', 'Well Testing', '2026-10-05'),
            ubicacion: 'Base Villahermosa',
            fecha_compra: '2026-09-18', fecha_alta: '2026-10-05', garantia_hasta: '2029-09-18',
            specs: pc('477 GB', 'Core i5-210H 2.20 GHz', '16 GB', 'Windows 11 Pro', 'Office 365, OneDrive, Nitro'),
            accesorios: ['Cargador 65W/20A/3.25 (S/N 8SGX21J75552C1TJ68A0E9R)'],
            observaciones: 'Equipo nuevo.',
        }),
        e('VEC-LAP-002', 'laptop', 'HP', 'ProBook 440 G10', '5CD3341KQ2', 'disponible', {
            fecha_compra: '2026-08-25', fecha_alta: '2026-09-02', garantia_hasta: '2029-08-25',
            specs: pc('512 GB SSD', 'Core i5-1335U', '16 GB'),
            accesorios: ['Cargador 65W', 'Mouse inalámbrico'],
            observaciones: 'Listo para asignar.',
        }),
        e('VEC-LAP-003', 'laptop', 'Dell', 'Latitude 5440', 'DL5440-7XK29', 'asignado', {
            asignado: p('Mariana López Ruiz', 'V10188', 'Analista de Finanzas', 'Finanzas', '2025-11-12'),
            ubicacion: 'Oficinas corporativas',
            fecha_compra: '2025-11-03', fecha_alta: '2025-11-10', garantia_hasta: '2028-11-03',
            specs: pc('512 GB SSD', 'Core i7-1355U', '16 GB', 'Windows 11 Pro', 'Office 365, OneDrive, Power BI'),
            accesorios: ['Cargador 65W', 'Mouse', 'Candado Kensington'],
        }),
        e('VEC-LAP-004', 'laptop', 'Lenovo', 'ThinkPad T14 Gen 4', 'PF4ZQ81C', 'mantenimiento', {
            asignado: p('Jorge Ramírez Pérez', 'V10102', 'Supervisor de Operaciones', 'Operaciones', '2024-05-20'),
            ubicacion: 'Taller IT',
            fecha_compra: '2024-05-10', fecha_alta: '2024-05-18', garantia_hasta: '2026-11-10',
            specs: pc('1 TB SSD', 'Core i7-1365U', '32 GB'),
            accesorios: ['Cargador 65W', 'Dock USB-C'],
            observaciones: 'Batería inflada, en reemplazo.',
        }),
        e('VEC-LAP-005', 'laptop', 'HP', 'EliteBook 840 G9', '5CG2281HZP', 'asignado', {
            asignado: p('Andrea Morales Díaz', 'V10143', 'Coordinadora de RH', 'Recursos Humanos', '2024-02-05'),
            ubicacion: 'Oficinas corporativas',
            fecha_compra: '2024-01-22', fecha_alta: '2024-02-01', garantia_hasta: '2026-12-22',
            specs: pc('512 GB SSD', 'Core i5-1245U', '16 GB'),
            accesorios: ['Cargador 65W', 'Mouse', 'Teclado externo'],
        }),
        e('VEC-LAP-006', 'laptop', 'Dell', 'Latitude 3520', 'DL3520-4PQ11', 'baja', {
            fecha_compra: '2021-03-01', fecha_alta: '2021-03-14', garantia_hasta: '2024-03-01',
            specs: pc('256 GB SSD', 'Core i5-1135G7', '8 GB', 'Windows 10 Pro'),
            observaciones: 'Placa madre dañada. Baja autorizada por IT.',
        }),

        // ── Desktops ──
        e('VEC-DSK-001', 'desktop', 'Dell', 'OptiPlex 7010', 'DSK7010-AA01', 'asignado', {
            asignado: p('Ricardo Salazar Gómez', 'V10077', 'Comprador', 'Compras', '2023-07-17'),
            ubicacion: 'Oficinas corporativas',
            fecha_compra: '2023-07-03', fecha_alta: '2023-07-14', garantia_hasta: '2026-07-03',
            specs: pc('512 GB SSD', 'Core i5-13500', '16 GB'),
            accesorios: ['Mouse', 'Teclado', 'Cable de poder', 'Regulador'],
        }),
        e('VEC-DSK-002', 'desktop', 'HP', 'ProDesk 400 G9', 'MXL2390PQ7', 'disponible', {
            fecha_compra: '2025-02-01', fecha_alta: '2025-02-10', garantia_hasta: '2028-02-01',
            specs: pc('256 GB SSD', 'Core i3-12100', '8 GB'),
            accesorios: ['Mouse', 'Teclado', 'Cable de poder'],
        }),

        // ── Monitores ──
        e('VEC-MON-001', 'monitor', 'Dell', 'P2422H 24"', 'CN-0W1MJ9', 'asignado', {
            asignado: p('Mariana López Ruiz', 'V10188', 'Analista de Finanzas', 'Finanzas', '2025-11-12'),
            ubicacion: 'Oficinas corporativas',
            specs: { 'Tamaño': '24 pulgadas', 'Resolución': '1920 x 1080 (FHD)', 'Panel': 'IPS', 'Conexiones': 'HDMI, DisplayPort, VGA' },
            accesorios: ['Cable HDMI', 'Cable de poder'],
        }),
        e('VEC-MON-002', 'monitor', 'LG', '27UL500 27" 4K', 'LG27-908KJ', 'disponible', {
            specs: { 'Tamaño': '27 pulgadas', 'Resolución': '3840 x 2160 (4K)', 'Panel': 'IPS', 'Conexiones': 'HDMI x2, DisplayPort' },
            accesorios: ['Cable HDMI', 'Cable de poder'],
        }),
        e('VEC-MON-003', 'monitor', 'HP', 'E24 G5 24"', '3CQ2410XYZ', 'asignado', {
            asignado: p('Andrea Morales Díaz', 'V10143', 'Coordinadora de RH', 'Recursos Humanos', '2024-02-05'),
            ubicacion: 'Oficinas corporativas',
            specs: { 'Tamaño': '24 pulgadas', 'Resolución': '1920 x 1080 (FHD)', 'Panel': 'IPS', 'Conexiones': 'HDMI, DisplayPort, VGA' },
            accesorios: ['Cable DisplayPort', 'Cable de poder'],
        }),
        e('VEC-MON-004', 'monitor', 'Samsung', 'S24C310', 'ZZ9HK40012', 'falla', {
            asignado: p('Ricardo Salazar Gómez', 'V10077', 'Comprador', 'Compras', '2023-07-17'),
            ubicacion: 'Oficinas corporativas',
            garantia_hasta: '2026-05-01',
            specs: { 'Tamaño': '24 pulgadas', 'Resolución': '1920 x 1080 (FHD)', 'Panel': 'VA', 'Conexiones': 'HDMI, VGA' },
            observaciones: 'Líneas verticales verdes. Falla reportada por el usuario.',
        }),

        // ── Celulares ──
        e('VEC-CEL-001', 'celular', 'Samsung', 'Galaxy A54 5G', 'R5CW31ABCD', 'asignado', {
            asignado: p('Jorge Ramírez Pérez', 'V10102', 'Supervisor de Operaciones', 'Operaciones', '2024-06-03'),
            ubicacion: 'Campo / Operaciones',
            specs: { 'IMEI': '356938 11 482910 4', 'Almacenamiento': '128 GB', 'Memoria': '8 GB', 'Sistema operativo': 'Android 14', 'Línea': '993 555 0142 (Telcel, plan empresarial)' },
            accesorios: ['Cargador 25W', 'Funda protectora', 'Mica'],
        }),
        e('VEC-CEL-002', 'celular', 'Apple', 'iPhone 13', 'F2LXK7ABCD', 'asignado', {
            asignado: p('Laura Sánchez Vidal', 'V10031', 'Gerente Comercial', 'Comercial', '2023-01-16'),
            ubicacion: 'Oficinas corporativas',
            garantia_hasta: '2025-01-10',
            specs: { 'IMEI': '352094 77 190321 8', 'Almacenamiento': '128 GB', 'Sistema operativo': 'iOS 18', 'Línea': '993 555 0177 (Telcel, plan empresarial)' },
            accesorios: ['Cable Lightning', 'Funda'],
        }),
        e('VEC-CEL-003', 'celular', 'Motorola', 'Moto G84 5G', 'ZY32MG84XQ', 'disponible', {
            fecha_alta: '2026-07-20', garantia_hasta: '2027-07-20',
            specs: { 'IMEI': '351120 66 740011 2', 'Almacenamiento': '256 GB', 'Memoria': '12 GB', 'Sistema operativo': 'Android 14', 'Línea': 'Sin línea asignada' },
            accesorios: ['Cargador 33W'],
        }),

        // ── Tablets ──
        e('VEC-TAB-001', 'tablet', 'Samsung', 'Galaxy Tab S9 FE', 'R52W70TAB1', 'asignado', {
            asignado: p('Héctor Mendoza Cruz', 'V10120', 'Técnico de Campo', 'Well Testing', '2025-03-10'),
            ubicacion: 'Campo / Operaciones',
            specs: { 'Almacenamiento': '128 GB', 'Memoria': '6 GB', 'Pantalla': '10.9 pulgadas', 'Sistema operativo': 'Android 14' },
            accesorios: ['Funda rugerizada', 'Cargador 15W', 'S Pen'],
            observaciones: 'Se usa para llenado de reportes en campo.',
        }),
        e('VEC-TAB-002', 'tablet', 'Apple', 'iPad 10.ª generación', 'DMPXK9IPAD', 'disponible', {
            garantia_hasta: '2027-02-14',
            specs: { 'Almacenamiento': '64 GB', 'Pantalla': '10.9 pulgadas', 'Sistema operativo': 'iPadOS 18' },
            accesorios: ['Cable USB-C', 'Funda Smart Folio'],
        }),

        // ── Impresoras ──
        e('VEC-IMP-001', 'impresora', 'HP', 'LaserJet Pro M404dn', 'PHBKN22345', 'asignado', {
            asignado: p('Área de Administración', '—', 'Equipo compartido', 'Administración', '2023-04-05'),
            ubicacion: 'Oficinas corporativas · Planta alta',
            specs: { 'Tecnología': 'Láser monocromática', 'Conexión': 'Ethernet / USB', 'Dirección IP': '192.168.10.45', 'Tóner': 'CF258A' },
            accesorios: ['Cable de poder', 'Cable USB'],
        }),
        e('VEC-IMP-002', 'impresora', 'Epson', 'EcoTank L3250', 'X4KR290871', 'disponible', {
            specs: { 'Tecnología': 'Inyección de tinta (tanque)', 'Conexión': 'Wi-Fi / USB', 'Tinta': 'Botellas 664' },
            accesorios: ['Cable de poder', 'Botellas de tinta'],
        }),

        // ── Almacenamiento ──
        e('VEC-ALM-001', 'almacenamiento', 'Seagate', 'Expansion 2 TB', 'NAAA1B2C3D', 'asignado', {
            asignado: p('Emmanuel Cortés Javier', 'V10215', 'Ingeniero en Mtto e Instrumentación', 'Well Testing', '2026-10-05'),
            ubicacion: 'Base Villahermosa',
            specs: { 'Capacidad': '2 TB', 'Interfaz': 'USB 3.0', 'Tipo': 'HDD externo' },
            accesorios: ['Cable USB 3.0'],
        }),
        e('VEC-ALM-002', 'almacenamiento', 'Western Digital', 'My Passport 1 TB', 'WX42D1234567', 'disponible', {
            specs: { 'Capacidad': '1 TB', 'Interfaz': 'USB 3.0', 'Tipo': 'HDD externo' },
            accesorios: ['Cable USB 3.0'],
        }),

        // ── Redes ──
        e('VEC-RED-001', 'red', 'Cisco', 'Catalyst 2960-X 24 puertos', 'FOC2145X0AB', 'asignado', {
            asignado: p('Infraestructura IT', '—', 'Equipo de infraestructura', 'Sistemas', '2022-09-01'),
            ubicacion: 'Site / Rack principal',
            garantia_hasta: '2027-09-01',
            specs: { 'Puertos': '24 x GbE + 2 SFP', 'Dirección IP': '192.168.10.2', 'Firmware': 'IOS 15.2(7)E' },
        }),
        e('VEC-RED-002', 'red', 'Ubiquiti', 'UniFi U6 Pro (AP)', 'F4E2C6A1B3D5', 'asignado', {
            asignado: p('Infraestructura IT', '—', 'Equipo de infraestructura', 'Sistemas', '2024-08-12'),
            ubicacion: 'Oficinas · Sala de juntas',
            specs: { 'Estándar': 'Wi-Fi 6', 'Dirección IP': '192.168.10.31', 'Alimentación': 'PoE 802.3af' },
        }),

        // ── Proyector ──
        e('VEC-PRY-001', 'proyector', 'Epson', 'PowerLite X49', 'VBXK4400912', 'disponible', {
            ubicacion: 'Sala de juntas',
            specs: { 'Brillo': '3600 lúmenes', 'Resolución': '1024 x 768 (XGA)', 'Conexiones': 'HDMI, VGA' },
            accesorios: ['Cable HDMI', 'Control remoto', 'Maletín'],
        }),

        // ── Accesorios ──
        e('VEC-ACC-001', 'accesorio', 'Lenovo', 'ThinkPad Universal USB-C Dock', 'DK2199W7701', 'asignado', {
            asignado: p('Jorge Ramírez Pérez', 'V10102', 'Supervisor de Operaciones', 'Operaciones', '2024-05-20'),
            ubicacion: 'Oficinas corporativas',
            specs: { 'Tipo': 'Docking station', 'Conexión': 'USB-C', 'Puertos': 'HDMI x2, DP, RJ45, USB-A x3' },
        }),
        e('VEC-ACC-002', 'accesorio', 'APC', 'Back-UPS BX750M', '3B2204X09981', 'falla', {
            ubicacion: 'Oficinas · Recepción',
            garantia_hasta: '2026-11-20',
            specs: { 'Tipo': 'No-break / UPS', 'Capacidad': '750 VA / 410 W' },
            observaciones: 'La batería ya no sostiene carga. Pendiente de reemplazo.',
        }),
    ];

    const MANTENIMIENTOS = [
        { equipo: 'Lenovo ThinkPad T14 Gen 4',  tipo: 'correctivo', tecnico: 'Luis Hernández',  fecha: '2026-10-02', detalle: 'Cambio de batería y limpieza interna' },
        { equipo: 'Samsung S24C310',            tipo: 'correctivo', tecnico: 'Karla Domínguez', fecha: '2026-09-29', detalle: 'Panel con líneas verticales, en diagnóstico' },
        { equipo: 'HP EliteBook 840 G9',        tipo: 'preventivo', tecnico: 'Luis Hernández',  fecha: '2026-09-24', detalle: 'Limpieza, pasta térmica y actualización de BIOS' },
        { equipo: 'Dell OptiPlex 7010',         tipo: 'preventivo', tecnico: 'Karla Domínguez', fecha: '2026-09-18', detalle: 'Limpieza y revisión de disco (SMART)' },
        { equipo: 'HP LaserJet Pro M404dn',     tipo: 'correctivo', tecnico: 'Luis Hernández',  fecha: '2026-09-10', detalle: 'Reemplazo de rodillo de arrastre' },
        { equipo: 'Cisco Catalyst 2960-X',      tipo: 'preventivo', tecnico: 'Karla Domínguez', fecha: '2026-08-30', detalle: 'Respaldo de configuración y firmware' },
        { equipo: 'Dell Latitude 5440',         tipo: 'preventivo', tecnico: 'Luis Hernández',  fecha: '2026-08-21', detalle: 'Limpieza y actualización de drivers' },
        { equipo: 'Apple iPhone 13',            tipo: 'correctivo', tecnico: 'Karla Domínguez', fecha: '2026-08-12', detalle: 'Cambio de pantalla' },
    ];

    // =====================================================
    // 3. HELPERS
    // =====================================================
    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

    const esc = (v) =>
        String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const fmtFecha = (iso) => {
        if (!iso) return '—';
        const [y, m, d] = iso.split('-');
        return `${d}/${m}/${y}`;
    };

    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    function garantiaEstado(iso) {
        const g = new Date(iso + 'T00:00:00');
        if (g < hoy) return 'vencida';
        const dias = (g - hoy) / 86400000;
        return dias <= 90 ? 'por_vencer' : 'vigente';
    }
    EQUIPOS.forEach((x) => (x.garantia_estado = garantiaEstado(x.garantia_hasta)));

    const porId = Object.fromEntries(EQUIPOS.map((x) => [x.id, x]));

    let toastTimer;
    function toast(msg) {
        const el = $('#toast');
        el.textContent = msg;
        el.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => (el.hidden = true), 3200);
    }

    const iconoTipo = (slug, size = '') =>
        `<span class="tipo-icon ${size} ${TIPOS[slug].color}"><i class="fas ${TIPOS[slug].icon}"></i></span>`;

    const badgeEstado = (slug) => `<span class="badge ${ESTADOS[slug].badge}">${ESTADOS[slug].label}</span>`;

    const emptyRow = (cols, icon, texto) =>
        `<tr><td colspan="${cols}" class="empty-state"><i class="fas ${icon}"></i><br>${texto}</td></tr>`;

    // =====================================================
    // 4. DASHBOARD
    // =====================================================
    function renderStats() {
        const activos = EQUIPOS.filter((x) => x.estado !== 'baja');
        $('#statTotal').textContent = activos.length;
        $('#statAsignados').textContent = EQUIPOS.filter((x) => x.estado === 'asignado').length;
        $('#statMant').textContent = EQUIPOS.filter((x) => x.estado === 'mantenimiento').length;
        $('#statFallas').textContent = EQUIPOS.filter((x) => x.estado === 'falla').length;
    }

    function renderRecientes() {
        const q = $('#dashSearch').value.trim().toLowerCase();
        const lista = [...EQUIPOS]
            .sort((a, b) => b.fecha_alta.localeCompare(a.fecha_alta))
            .filter((x) => !q || `${x.marca} ${x.modelo} ${x.serie} ${x.codigo}`.toLowerCase().includes(q))
            .slice(0, 6);

        $('#dashRecientesBody').innerHTML = lista.length
            ? lista.map((x) => `
                <tr class="equipo-row" data-id="${esc(x.id)}">
                    <td>
                        <div class="equipo-cell">
                            ${iconoTipo(x.tipo)}
                            <div>
                                <strong>${esc(x.marca)} ${esc(x.modelo)}</strong><br>
                                <span class="text-xs">S/N: ${esc(x.serie)}</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge badge-type">${TIPOS[x.tipo].singular}</span></td>
                    <td>${badgeEstado(x.estado)}</td>
                </tr>`).join('')
            : emptyRow(3, 'fa-inbox', 'No hay registros recientes para mostrar.');
    }

    function renderMantenimientos() {
        const f = $('#dashMantFilter').value;
        const lista = [...MANTENIMIENTOS]
            .sort((a, b) => b.fecha.localeCompare(a.fecha))
            .filter((m) => !f || m.tipo === f)
            .slice(0, 6);

        $('#dashMantBody').innerHTML = lista.length
            ? lista.map((m) => `
                <tr>
                    <td><strong>${esc(m.equipo)}</strong><br><span class="text-xs">${esc(m.detalle)}</span></td>
                    <td><span class="badge ${m.tipo === 'preventivo' ? 'badge-free' : 'badge-maint'}">${m.tipo === 'preventivo' ? 'Preventivo' : 'Correctivo'}</span></td>
                    <td>${esc(m.tecnico)}</td>
                    <td class="nowrap">${fmtFecha(m.fecha)}</td>
                </tr>`).join('')
            : emptyRow(4, 'fa-clipboard-list', 'No hay mantenimientos recientes.');
    }

    function renderPorTipo() {
        const conteo = {};
        EQUIPOS.filter((x) => x.estado !== 'baja').forEach((x) => (conteo[x.tipo] = (conteo[x.tipo] || 0) + 1));
        const orden = Object.entries(conteo).sort((a, b) => b[1] - a[1]);
        const max = Math.max(...orden.map(([, n]) => n), 1);

        $('#tipoBars').innerHTML = orden.map(([slug, n]) => `
            <button type="button" class="tipo-bar" data-goto="inventario" data-tipo="${slug}">
                ${iconoTipo(slug, 'sm')}
                <span class="tipo-bar-label">${TIPOS[slug].label}</span>
                <span class="tipo-bar-track"><span class="tipo-bar-fill ${TIPOS[slug].color}" style="width:${Math.round((n / max) * 100)}%"></span></span>
                <span class="tipo-bar-count">${n}</span>
            </button>`).join('');
    }

    function renderGarantias() {
        const lista = EQUIPOS
            .filter((x) => x.estado !== 'baja' && x.garantia_estado !== 'vigente')
            .sort((a, b) => a.garantia_hasta.localeCompare(b.garantia_hasta));

        $('#garantiasBody').innerHTML = lista.length
            ? lista.map((x) => `
                <tr class="equipo-row" data-id="${esc(x.id)}">
                    <td><strong>${esc(x.marca)} ${esc(x.modelo)}</strong><br><span class="text-xs">${esc(x.codigo)}</span></td>
                    <td class="nowrap">${fmtFecha(x.garantia_hasta)}</td>
                    <td>${x.garantia_estado === 'vencida'
                        ? '<span class="badge badge-fail">Vencida</span>'
                        : '<span class="badge badge-maint">Por vencer</span>'}</td>
                </tr>`).join('')
            : emptyRow(3, 'fa-shield-halved', 'Todas las garantías están vigentes.');
    }

    // =====================================================
    // 5. INVENTARIO (tabla + filtros)
    // =====================================================
    let tipoActivo = '';

    function renderTabs() {
        const conteo = {};
        EQUIPOS.forEach((x) => (conteo[x.tipo] = (conteo[x.tipo] || 0) + 1));

        const tab = (slug, icon, label, n) => `
            <button type="button" class="type-tab ${slug === tipoActivo ? 'active' : ''}" data-tipo="${slug}" role="tab" aria-selected="${slug === tipoActivo}">
                <i class="fas ${icon}"></i> ${label} <span class="type-count">${n}</span>
            </button>`;

        $('#typeTabs').innerHTML =
            tab('', 'fa-layer-group', 'Todos', EQUIPOS.length) +
            Object.keys(TIPOS)
                .filter((s) => conteo[s])
                .map((s) => tab(s, TIPOS[s].icon, TIPOS[s].label, conteo[s]))
                .join('');
    }

    function equiposFiltrados() {
        const q = $('#searchInput').value.trim().toLowerCase();
        const estado = $('#estadoFilter').value;
        const garantia = $('#garantiaFilter').value;

        return EQUIPOS
            .filter((x) => {
                const hay = [x.codigo, x.marca, x.modelo, x.serie, x.asignado?.nombre, x.asignado?.noEmp, x.ubicacion]
                    .join(' ').toLowerCase();
                return (!tipoActivo || x.tipo === tipoActivo) &&
                    (!estado || x.estado === estado) &&
                    (!garantia || x.garantia_estado === garantia) &&
                    (!q || hay.includes(q));
            })
            .sort((a, b) => a.codigo.localeCompare(b.codigo));
    }

    function renderTabla() {
        const lista = equiposFiltrados();
        $('#resultCount').textContent = `${lista.length} ${lista.length === 1 ? 'resultado' : 'resultados'}`;

        $('#equiposBody').innerHTML = lista.length
            ? lista.map((x) => `
                <tr class="equipo-row" data-id="${esc(x.id)}">
                    <td>
                        <div class="equipo-cell">
                            ${iconoTipo(x.tipo)}
                            <div>
                                <strong>${esc(x.marca)} ${esc(x.modelo)}</strong><br>
                                <span class="text-xs">S/N: ${esc(x.serie)}</span>
                            </div>
                        </div>
                    </td>
                    <td class="nowrap"><code class="codigo">${esc(x.codigo)}</code></td>
                    <td><span class="badge badge-type">${TIPOS[x.tipo].singular}</span></td>
                    <td>${x.asignado
                        ? `<strong>${esc(x.asignado.nombre)}</strong><br><span class="text-xs">${esc(x.asignado.area)}</span>`
                        : '<span class="text-muted">Sin asignar</span>'}</td>
                    <td>${esc(x.ubicacion)}</td>
                    <td>${badgeEstado(x.estado)}</td>
                    <td class="nowrap"><span class="garantia ${x.garantia_estado}"><i class="fas fa-circle"></i> ${fmtFecha(x.garantia_hasta)}</span></td>
                    <td class="td-actions">
                        <button type="button" class="icon-btn" title="Ver detalle" aria-label="Ver detalle de ${esc(x.codigo)}"><i class="fas fa-eye"></i></button>
                    </td>
                </tr>`).join('')
            : emptyRow(8, 'fa-magnifying-glass', 'No hay equipos con esos filtros. Prueba quitando alguno.');
    }

    function activarTipo(slug) {
        tipoActivo = slug;
        renderTabs();
        renderTabla();
    }

    // =====================================================
    // 6. SWITCH DE VISTAS
    // =====================================================
    function setView(view, tipo) {
        const esInv = view === 'inventario';
        $('#viewDashboard').hidden = esInv;
        $('#viewInventario').hidden = !esInv;
        $$('.view-btn').forEach((b) => {
            const on = b.dataset.view === view;
            b.classList.toggle('active', on);
            b.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        if (esInv) activarTipo(tipo || '');
        history.replaceState(null, '', esInv ? '#inventario' : location.pathname + location.search);
    }

    // =====================================================
    // 7. MODALES
    // =====================================================
    let modalAbierto = null;
    let equipoActual = null;

    function abrirModal(el) {
        el.hidden = false;
        modalAbierto = el;
        document.body.style.overflow = 'hidden';
        const foco = $('button, input, select', el);
        if (foco) foco.focus();
    }

    function cerrarModal() {
        if (!modalAbierto) return;
        modalAbierto.hidden = true;
        modalAbierto = null;
        document.body.style.overflow = '';
    }

    const dl = (pares) => pares.map(([k, v]) => `<div><dt>${esc(k)}</dt><dd>${esc(v || '—')}</dd></div>`).join('');

    function abrirDetalle(id) {
        const x = porId[id];
        if (!x) return;
        equipoActual = x;
        const t = TIPOS[x.tipo];

        $('#dIcono').className = `tipo-icon lg ${t.color}`;
        $('#dIcono').innerHTML = `<i class="fas ${t.icon}"></i>`;
        $('#detalleTitulo').textContent = `${x.marca} ${x.modelo}`;
        $('#dCodigo').textContent = x.codigo;
        $('#dEstado').className = `badge ${ESTADOS[x.estado].badge}`;
        $('#dEstado').textContent = ESTADOS[x.estado].label;

        $('#dGeneral').innerHTML = dl([
            ['Tipo', t.singular], ['Marca', x.marca], ['Modelo', x.modelo], ['Número de serie', x.serie],
            ['Ubicación', x.ubicacion], ['Proveedor', x.proveedor],
            ['Fecha de compra', fmtFecha(x.fecha_compra)], ['Alta en inventario', fmtFecha(x.fecha_alta)],
            ['Garantía hasta', fmtFecha(x.garantia_hasta)],
        ]);

        const specs = Object.entries(x.specs);
        $('#dSpecs').innerHTML = specs.length ? dl(specs) : '<div><dd class="text-muted">Sin características registradas.</dd></div>';

        $('#dAsignacion').innerHTML = x.asignado
            ? dl([
                ['Responsable', x.asignado.nombre], ['No. de empleado', x.asignado.noEmp],
                ['Puesto', x.asignado.puesto], ['Área', x.asignado.area],
                ['Asignado desde', fmtFecha(x.asignado.desde)],
            ])
            : '<div><dd class="text-muted">Este equipo no está asignado.</dd></div>';

        $('#dAccesorios').innerHTML = x.accesorios.length
            ? x.accesorios.map((a) => `<span class="chip">${esc(a)}</span>`).join('')
            : '<span class="text-muted">Sin accesorios registrados.</span>';

        $('#dObs').textContent = x.observaciones || 'Sin observaciones.';
        abrirModal($('#detalleModal'));
    }

    // =====================================================
    // 8. EXPORTAR CSV
    // =====================================================
    function exportarCSV() {
        // En el dashboard exporta todo; en inventario respeta los filtros
        const enInventario = !$('#viewInventario').hidden;
        const lista = enInventario ? equiposFiltrados() : [...EQUIPOS].sort((a, b) => a.codigo.localeCompare(b.codigo));

        if (!lista.length) {
            toast('No hay equipos para exportar con esos filtros.');
            return;
        }

        const cab = ['Código', 'Tipo', 'Marca', 'Modelo', 'Serie', 'Estado', 'Responsable', 'No. Emp', 'Área', 'Ubicación', 'Garantía hasta'];
        const celda = (v) => `"${String(v ?? '').replace(/"/g, '""')}"`;
        const filas = lista.map((x) => [
            x.codigo, TIPOS[x.tipo].singular, x.marca, x.modelo, x.serie, ESTADOS[x.estado].label,
            x.asignado?.nombre, x.asignado?.noEmp, x.asignado?.area, x.ubicacion, fmtFecha(x.garantia_hasta),
        ]);

        const csv = [cab, ...filas].map((f) => f.map(celda).join(',')).join('\r\n');
        const a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' }));
        a.download = `inventario-ti-${new Date().toISOString().slice(0, 10)}.csv`;
        a.click();
        URL.revokeObjectURL(a.href);
    }

    // =====================================================
    // 9. RESPONSIVA IMPRIMIBLE
    // =====================================================
    function imprimirResponsiva() {
        const x = equipoActual;
        if (!x) return;

        if (!x.asignado || x.asignado.noEmp === '—') {
            toast('La responsiva solo aplica a equipos asignados a una persona.');
            return;
        }

        const sp = x.specs;
        const t = TIPOS[x.tipo];
        const esComputo = ['laptop', 'desktop', 'tablet'].includes(x.tipo);

        const caracteristicas = esComputo
            ? `
            <tr><th>Marca</th><td>${esc(x.marca)}</td><th>Modelo</th><td>${esc(x.modelo)}</td></tr>
            <tr><th>Serie</th><td>${esc(x.serie)}</td><th>Disco duro</th><td>${esc(sp['Disco duro'] || sp['Almacenamiento'] || '—')}</td></tr>
            <tr><th>Procesador</th><td>${esc(sp['Procesador'] || '—')}</td><th>Memoria</th><td>${esc(sp['Memoria'] || '—')}</td></tr>
            <tr><th>Sistema operativo</th><td>${esc(sp['Sistema operativo'] || '—')}</td><th>Software</th><td>${esc(sp['Software'] || '—')}</td></tr>`
            : `
            <tr><th>Marca</th><td>${esc(x.marca)}</td><th>Modelo</th><td>${esc(x.modelo)}</td></tr>
            <tr><th>Serie</th><td colspan="3">${esc(x.serie)}</td></tr>
            ${Object.entries(sp).map(([k, v]) => `<tr><th>${esc(k)}</th><td colspan="3">${esc(v)}</td></tr>`).join('')}`;

        $('#responsivaPrint').innerHTML = `
            <h1>RESPONSIVA POR EQUIPO DE CÓMPUTO</h1>
            <table>
                <tr><th>Nombre</th><td>${esc(x.asignado.nombre)}</td><th>No. Emp</th><td>${esc(x.asignado.noEmp)}</td></tr>
                <tr><th>Categoría de trabajo</th><td>${esc(x.asignado.puesto)}</td><th>Área</th><td>${esc(x.asignado.area)}</td></tr>
                <tr><th>Fecha</th><td colspan="3">${fmtFecha(x.asignado.desde)}</td></tr>
            </table>

            <h2>Características del equipo (${esc(t.singular)} · ${esc(x.codigo)})</h2>
            <table>${caracteristicas}</table>

            <h2>Observaciones IT</h2>
            <table><tr><td>${esc(x.observaciones || ' ')}</td></tr></table>

            <h2>Accesorios del equipo</h2>
            <table><tr><td>${esc(x.accesorios.join(', ') || 'Ninguno')}</td></tr></table>

            <h2>Lineamiento QHSE Seguridad de la Información y Equipo de Cómputo</h2>
            <p class="legal">
                Como empleado de Vinco Energy Services estoy consciente del riesgo potencial de pérdida de información,
                así como de los activos informáticos que están en posesión del personal. Me comprometo a respetar y hacer
                cumplir el lineamiento L-6.1-VINCO-01, así como la política de confidencialidad de la empresa.
                Si por cualquier motivo dejo la organización o se me pide entregar el equipo, debo regresarlo al área de IT
                con todos los accesorios entregados y solicitar la cancelación de este formato de responsabilidad.
                Todo caso de incumplimiento será acreedor a medidas disciplinarias.
            </p>

            <div class="firma">
                <strong>ACEPTO DE CONFORMIDAD</strong><br><br><br>
                <div>${esc(x.asignado.nombre.toUpperCase())}<br>Nombre y firma empleado Vinco Energy Services</div>
            </div>`;

        window.print();
    }

    // =====================================================
    // 10. INIT
    // =====================================================
    function init() {
        if (!$('#inventoryApp')) return;

        // Selects que dependen de los catálogos
        $('#estadoFilter').innerHTML =
            '<option value="">Todos los estados</option>' +
            Object.entries(ESTADOS).map(([k, v]) => `<option value="${k}">${v.label}</option>`).join('');
        $('#altaTipo').innerHTML = Object.entries(TIPOS)
            .map(([k, v]) => `<option value="${k}">${v.singular}</option>`).join('');

        // Dashboard
        renderStats();
        renderRecientes();
        renderMantenimientos();
        renderPorTipo();
        renderGarantias();

        $('#dashSearch').addEventListener('input', renderRecientes);
        $('#dashMantFilter').addEventListener('change', renderMantenimientos);

        // Inventario
        $('#searchInput').addEventListener('input', renderTabla);
        $('#estadoFilter').addEventListener('change', renderTabla);
        $('#garantiaFilter').addEventListener('change', renderTabla);
        $('#typeTabs').addEventListener('click', (ev) => {
            const tab = ev.target.closest('.type-tab');
            if (tab) activarTipo(tab.dataset.tipo);
        });

        // Navegación entre vistas
        $$('.view-btn').forEach((b) => b.addEventListener('click', () => setView(b.dataset.view)));
        document.addEventListener('click', (ev) => {
            const go = ev.target.closest('[data-goto]');
            if (go) setView(go.dataset.goto, go.dataset.tipo);
        });

        // Abrir detalle desde cualquier tabla (delegado)
        document.addEventListener('click', (ev) => {
            const tr = ev.target.closest('tr.equipo-row');
            if (tr) abrirDetalle(tr.dataset.id);
        });

        // Modales
        $$('.modal-backdrop').forEach((bd) =>
            bd.addEventListener('click', (ev) => {
                if (ev.target === bd || ev.target.closest('[data-close]')) cerrarModal();
            })
        );
        document.addEventListener('keydown', (ev) => {
            if (ev.key === 'Escape') cerrarModal();
        });

        $('#btnAlta').addEventListener('click', () => abrirModal($('#altaModal')));
        $('#altaForm').addEventListener('submit', (ev) => {
            ev.preventDefault();
            cerrarModal();
            ev.target.reset();
            toast('Maqueta: el equipo todavía no se guarda (falta conectar la BD).');
        });

        $('#btnExport').addEventListener('click', exportarCSV);
        $('#btnResponsiva').addEventListener('click', imprimirResponsiva);

        // Entrar directo al inventario con #inventario
        if (location.hash === '#inventario') setView('inventario');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();