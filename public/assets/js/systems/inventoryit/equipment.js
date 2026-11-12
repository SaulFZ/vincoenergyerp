/* =======================================================
   INVENTARIO TI — EQUIPOS (VesCore)
   Conectado a base de datos MySQL en Laravel 11.
   Catálogo por categoría, folios automáticos VES-XXX-###,
   tipo de equipo en datos generales y registro de accesorios
   o componentes incluidos con su marca, modelo y N/S.

   - "Estado del equipo" = Nuevo, Seminuevo, Usado, Reacondicionado
   - "Condición" = Excelente, Bueno, Regular, Con detalles, Dañado
   - "Estatus" = Disponible, Asignado, En préstamo, Reservado,
                 En mantenimiento, En garantía, Deshabilitado,
                 Extraviado, Robado, Baja
   ======================================================= */

(() => {
    'use strict';

    const root = document.getElementById('inventoryApp');

    if (!root) return;

    const $ = (sel, ctx = root) => ctx.querySelector(sel);

    const CONFIG = window.VES_INVENTORY_CONFIG || {
        items: [],
        storeUrl: '',
        csrfToken: '',
    };

    /* ---------------------------------------------------
       Utilidades
    --------------------------------------------------- */

    const esc = (v) =>
        String(v ?? '').replace(/[&<>"']/g, (c) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        })[c]);

    const fmtFecha = (iso) => {
        if (!iso) return '—';

        const [y, m, d] = String(iso).split('T')[0].split('-');

        return `${d}/${m}/${y}`;
    };

    const hoyISO = () => {
        const t = new Date();
        const p = (n) => String(n).padStart(2, '0');

        return `${t.getFullYear()}-${p(t.getMonth() + 1)}-${p(t.getDate())}`;
    };

    const norm = (s) =>
        String(s ?? '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');

    // Une listas sin repetir opciones
    const uni = (...listas) => [...new Set(listas.flat())];

    /* ---------------------------------------------------
       Listas de opciones reutilizables (sin barras ni paréntesis)
    --------------------------------------------------- */

    const OPT_RAM = [
        '2 GB',
        '4 GB',
        '6 GB',
        '8 GB',
        '12 GB',
        '16 GB',
        '24 GB',
        '32 GB',
        '48 GB',
        '64 GB',
        '96 GB',
        '128 GB',
        '256 GB',
    ];

    const OPT_RAM_MOVIL = [
        '1 GB',
        '2 GB',
        '3 GB',
        '4 GB',
        '6 GB',
        '8 GB',
        '12 GB',
        '16 GB',
    ];

    const OPT_TIPO_RAM = [
        'DDR3',
        'DDR3L',
        'DDR4',
        'DDR5',
        'LPDDR4',
        'LPDDR4X',
        'LPDDR5',
        'LPDDR5X',
        'Memoria unificada Apple',
    ];

    const OPT_ALMACENAMIENTO = [
        '64 GB',
        '128 GB',
        '256 GB',
        '512 GB',
        '1 TB',
        '2 TB',
        '4 TB',
        '6 TB',
        '8 TB',
        '12 TB',
        '16 TB',
    ];

    const OPT_ALMACENAMIENTO_MOVIL = [
        '16 GB',
        '32 GB',
        '64 GB',
        '128 GB',
        '256 GB',
        '512 GB',
        '1 TB',
    ];

    const OPT_TIPO_ALMACENAMIENTO = [
        'SSD NVMe M.2',
        'SSD SATA 2.5 pulgadas',
        'SSD mSATA',
        'Disco duro SATA 2.5 pulgadas',
        'Disco duro SATA 3.5 pulgadas',
        'Disco duro SAS',
        'Doble unidad SSD y disco duro',
        'Memoria eMMC',
    ];

    const OPT_SO_PC = [
        'Windows 11 Pro',
        'Windows 11 Home',
        'Windows 11 Enterprise',
        'Windows 10 Pro',
        'Windows 10 Home',
        'Windows 10 Enterprise',
        'Windows Server 2016',
        'Windows Server 2019',
        'Windows Server 2022',
        'Windows Server 2025',
        'Ubuntu Linux',
        'Debian Linux',
        'Fedora Linux',
        'Rocky Linux',
        'Red Hat Enterprise Linux',
        'macOS',
        'ChromeOS',
        'Sin sistema operativo',
        'Otro',
    ];

    const OPT_SO_CELULAR = [
        'Android 11',
        'Android 12',
        'Android 13',
        'Android 14',
        'Android 15',
        'iOS 15',
        'iOS 16',
        'iOS 17',
        'iOS 18',
        'Sistema propietario',
        'Otro',
    ];

    const OPT_SO_TABLET = [
        'Android 11',
        'Android 12',
        'Android 13',
        'Android 14',
        'Android 15',
        'iPadOS 15',
        'iPadOS 16',
        'iPadOS 17',
        'iPadOS 18',
        'Windows 11 Pro',
        'Windows 11 Home',
        'Windows 10 Pro',
        'Otro',
    ];

    const OPT_RESOLUCION = [
        'HD 1366 x 768',
        'HD+ 1600 x 900',
        'Full HD 1920 x 1080',
        'WUXGA 1920 x 1200',
        '2K QHD 2560 x 1440',
        'WQXGA 2560 x 1600',
        'UltraWide 2560 x 1080',
        'UltraWide QHD 3440 x 1440',
        '4K UHD 3840 x 2160',
        '5K 5120 x 2880',
        'Super UltraWide 5120 x 1440',
    ];

    const OPT_PANTALLA_LAPTOP = [
        '11.6"',
        '12.5"',
        '13.3"',
        '13.6"',
        '14"',
        '15.6"',
        '16"',
        '17.3"',
    ];

    const OPT_PANTALLA_TABLET = [
        '7"',
        '8"',
        '8.3"',
        '9"',
        '10.1"',
        '10.5"',
        '10.9"',
        '11"',
        '12.4"',
        '12.9"',
        '13"',
    ];

    const OPT_TAMANO_MONITOR = [
        '18.5"',
        '19"',
        '20"',
        '21.5"',
        '22"',
        '23.8"',
        '24"',
        '27"',
        '29"',
        '32"',
        '34"',
        '43"',
        '49"',
        '55"',
        '65"',
        '75"',
        '85"',
    ];

    const OPT_VELOCIDAD_RED = [
        '10 Mbps',
        '100 Mbps Fast Ethernet',
        '1 Gbps Gigabit Ethernet',
        '2.5 Gbps Ethernet',
        '5 Gbps Ethernet',
        '10 Gbps Ethernet',
        '10 Gbps Fibra SFP+',
        '25 Gbps Fibra SFP28',
        '40 Gbps Fibra QSFP+',
        '100 Gbps Fibra',
        'Inalámbrico Wi-Fi 5',
        'Inalámbrico Wi-Fi 6',
        'Inalámbrico Wi-Fi 6E',
        'Inalámbrico Wi-Fi 7',
    ];

    // Cada cable completamente separado
    const GRP_CABLES = [
        'Cable de poder',
        'Cable de poder C13',
        'Cable de poder C14',
        'Cable de red Cat5e',
        'Cable de red Cat6',
        'Cable de red Cat6A',
        'Cable de fibra óptica',
        'Cable de consola serial',
        'Cable HDMI',
        'Cable DisplayPort',
        'Cable Mini DisplayPort',
        'Cable VGA',
        'Cable DVI',
        'Cable USB-A a USB-A',
        'Cable USB-A a USB-B para impresora',
        'Cable USB-A a USB-C',
        'Cable USB-C a USB-C',
        'Cable USB-A a Micro USB',
        'Cable USB-A a Lightning',
        'Cable USB-C a Lightning',
        'Cable Thunderbolt',
        'Cable de audio 3.5 mm',
        'Cable de audio RCA',
        'Cable de audio XLR',
        'Cable telefónico RJ11',
    ];

    /* ---------------------------------------------------
       Catálogo de categorías y tipos de equipo
    --------------------------------------------------- */

    const TIPOS = {
        laptop: {
            label: 'Laptops',
            singular: 'Laptop',
            prefix: 'VES-LAP',
            icon: 'fa-laptop',
            color: 'blue',
            placeholderMarca: 'Lenovo',
            placeholderModelo: 'ThinkPad E16 Gen 3',
            subtipos: [
                'Laptop estándar',
                'Laptop ultradelgada',
                'Laptop 2 en 1 convertible',
                'Laptop estación de trabajo',
                'Laptop de alto rendimiento',
                'Laptop de uso rudo',
                'MacBook Air',
                'MacBook Pro',
                'Chromebook',
            ],
            specs: [
                { k: 'procesador', l: 'Procesador', ph: 'Intel Core i7-1355U' },
                { k: 'ram', l: 'Memoria RAM', opts: OPT_RAM },
                { k: 'tipo_ram', l: 'Tipo de memoria RAM', opts: OPT_TIPO_RAM },
                { k: 'almacenamiento', l: 'Capacidad de almacenamiento', opts: OPT_ALMACENAMIENTO },
                { k: 'tipo_almacenamiento', l: 'Tipo de almacenamiento', opts: OPT_TIPO_ALMACENAMIENTO },
                { k: 'grafica', l: 'Tarjeta gráfica', ph: 'Intel Iris Xe' },
                { k: 'pantalla', l: 'Tamaño de pantalla', opts: OPT_PANTALLA_LAPTOP },
                { k: 'resolucion', l: 'Resolución', opts: OPT_RESOLUCION },
                { k: 'so', l: 'Sistema operativo', opts: OPT_SO_PC },
            ],
        },

        desktop: {
            label: 'Desktops',
            singular: 'Desktop',
            prefix: 'VES-DES',
            icon: 'fa-desktop',
            color: 'indigo',
            placeholderMarca: 'Dell',
            placeholderModelo: 'OptiPlex 7010',
            subtipos: [
                'All in One',
                'iMac',
                'Gabinete compacto SFF',
                'Mini PC',
                'Mac Mini',
                'Mac Studio',
                'Torre',
                'Media torre',
                'Estación de trabajo',
                'Cliente ligero',
            ],
            specs: [
                { k: 'procesador', l: 'Procesador', ph: 'Intel Core i7-13700' },
                { k: 'ram', l: 'Memoria RAM', opts: OPT_RAM },
                { k: 'tipo_ram', l: 'Tipo de memoria RAM', opts: OPT_TIPO_RAM },
                { k: 'almacenamiento', l: 'Capacidad de almacenamiento', opts: OPT_ALMACENAMIENTO },
                { k: 'tipo_almacenamiento', l: 'Tipo de almacenamiento', opts: OPT_TIPO_ALMACENAMIENTO },
                { k: 'grafica', l: 'Tarjeta gráfica', ph: 'Intel UHD 770' },
                { k: 'pantalla', l: 'Pantalla integrada en All in One', opts: ['No aplica', '21.5"', '23.8"', '24"', '27"', '32"'] },
                { k: 'so', l: 'Sistema operativo', opts: OPT_SO_PC },
            ],
        },

        monitor: {
            label: 'Monitores',
            singular: 'Monitor',
            prefix: 'VES-MON',
            icon: 'fa-display',
            color: 'teal',
            placeholderMarca: 'Dell',
            placeholderModelo: 'P2422H',
            subtipos: [
                'Monitor estándar',
                'Monitor profesional para diseño',
                'Monitor ultra panorámico',
                'Monitor curvo',
                'Monitor táctil',
                'Monitor portátil',
                'Monitor de alta tasa de refresco',
            ],
            specs: [
                { k: 'tamano', l: 'Tamaño en pulgadas', opts: OPT_TAMANO_MONITOR },
                { k: 'resolucion', l: 'Resolución nativa', opts: OPT_RESOLUCION },
                { k: 'panel', l: 'Tipo de panel', opts: ['IPS', 'VA', 'TN', 'OLED', 'QLED', 'Mini LED'] },
                { k: 'tasa_refresco', l: 'Tasa de refresco', opts: ['60 Hz', '75 Hz', '100 Hz', '120 Hz', '144 Hz', '165 Hz', '240 Hz'] },
                { k: 'puertos', l: 'Puertos de video', ph: 'HDMI, DisplayPort, VGA' },
                { k: 'vesa', l: 'Soporte para montaje VESA', opts: ['Sí 75 x 75 mm', 'Sí 100 x 100 mm', 'Sí 200 x 200 mm', 'No compatible'] },
            ],
        },

        celular: {
            label: 'Celulares',
            singular: 'Celular',
            prefix: 'VES-CEL',
            icon: 'fa-mobile-screen',
            color: 'green',
            placeholderMarca: 'Samsung',
            placeholderModelo: 'Galaxy A54 5G',
            subtipos: [
                'Teléfono inteligente',
                'Teléfono de uso rudo',
                'Teléfono plegable',
                'Teléfono satelital',
                'Teléfono básico',
                'Radio comunicador móvil',
            ],
            specs: [
                { k: 'imei', l: 'IMEI principal *', ph: '356938035643809', req: true },
                { k: 'imei2', l: 'IMEI secundario', ph: 'Solo para equipos con doble SIM' },
                { k: 'linea', l: 'Número de línea asignado', ph: '993 123 4567' },
                {
                    k: 'operador',
                    l: 'Operador telefónico',
                    opts: ['Telcel', 'AT&T', 'Movistar', 'Bait', 'Altan Redes', 'Unefon', 'Virgin Mobile', 'Sin tarjeta SIM'],
                },
                { k: 'tipo_sim', l: 'Tipo de SIM', opts: ['Nano SIM', 'eSIM', 'Doble Nano SIM', 'Nano SIM y eSIM', 'Sin SIM'] },
                { k: 'almacenamiento', l: 'Almacenamiento interno', opts: OPT_ALMACENAMIENTO_MOVIL },
                { k: 'ram', l: 'Memoria RAM', opts: OPT_RAM_MOVIL },
                { k: 'so', l: 'Sistema operativo', opts: OPT_SO_CELULAR },
            ],
        },

        tablet: {
            label: 'Tablets',
            singular: 'Tablet',
            prefix: 'VES-TAB',
            icon: 'fa-tablet-screen-button',
            color: 'violet',
            placeholderMarca: 'Apple',
            placeholderModelo: 'iPad 10.ª Gen',
            subtipos: [
                'Tablet estándar',
                'iPad',
                'iPad Air',
                'iPad Pro',
                'iPad Mini',
                'Tablet de uso rudo',
                'Tablet con teclado desmontable',
                'Terminal colectora de datos',
                'Lector de libros electrónicos',
            ],
            specs: [
                { k: 'pantalla', l: 'Tamaño de pantalla', opts: OPT_PANTALLA_TABLET },
                { k: 'almacenamiento', l: 'Almacenamiento interno', opts: OPT_ALMACENAMIENTO_MOVIL },
                { k: 'ram', l: 'Memoria RAM', opts: OPT_RAM_MOVIL },
                { k: 'conectividad', l: 'Conectividad', opts: ['Solo Wi-Fi', 'Wi-Fi y red celular 4G', 'Wi-Fi y red celular 5G'] },
                { k: 'imei', l: 'IMEI en caso de tener red celular', ph: 'Opcional' },
                { k: 'so', l: 'Sistema operativo', opts: OPT_SO_TABLET },
            ],
        },

        impresora: {
            label: 'Impresoras',
            singular: 'Impresora',
            prefix: 'VES-IMP',
            icon: 'fa-print',
            color: 'amber',
            placeholderMarca: 'HP',
            placeholderModelo: 'LaserJet Pro M404dn',
            subtipos: [
                'Multifuncional láser monocromática',
                'Multifuncional láser a color',
                'Impresora láser monocromática',
                'Impresora láser a color',
                'Multifuncional de tinta continua',
                'Impresora de tinta continua',
                'Impresora de inyección de tinta',
                'Impresora térmica de etiquetas',
                'Impresora térmica de recibos',
                'Impresora de credenciales PVC',
                'Impresora de matriz de puntos',
                'Plotter de gran formato',
                'Escáner de documentos',
            ],
            specs: [
                { k: 'funcion', l: 'Funciones del equipo', opts: ['Solo impresión', 'Impresión, escaneo y copias', 'Impresión, escaneo, copias y fax', 'Solo escaneo'] },
                { k: 'conectividad', l: 'Conectividad', ph: 'Ethernet, Wi-Fi, USB' },
                { k: 'ip', l: 'Dirección IP asignada', ph: '192.168.1.50' },
                { k: 'formato_papel', l: 'Tamaño máximo de papel', opts: ['Carta', 'Oficio', 'A4', 'A3', 'Tabloide', 'Rollo de etiquetas', 'Rollo térmico de recibos', 'Rollo de gran formato', 'Tarjeta PVC'] },
                { k: 'duplex', l: 'Impresión a doble cara', opts: ['Automática', 'Manual', 'No disponible'] },
                { k: 'consumible', l: 'Modelo de tóner o tinta', ph: 'Tóner CF258A' },
            ],
        },

        redes: {
            label: 'Redes',
            singular: 'Equipo de red',
            prefix: 'VES-RED',
            icon: 'fa-network-wired',
            color: 'cyan',
            placeholderMarca: 'Synology',
            placeholderModelo: 'RS822RP+',
            subtipos: [
                'NAS de rack',
                'NAS de escritorio',
                'Unidad de expansión SAN',
                'Servidor de rack',
                'Servidor de torre',
                'Switch administrable',
                'Switch no administrable',
                'Switch PoE',
                'Router',
                'Balanceador de carga',
                'Firewall',
                'Access Point',
                'Repetidor inalámbrico',
                'Antena inalámbrica punto a punto',
                'Antena sectorial',
                'Controlador de red',
                'Grabador NVR',
                'Grabador DVR',
                'Cámara IP',
                'Teléfono IP',
                'Módem',
                'Terminal satelital Starlink',
                'Convertidor de medios de fibra',
                'Panel de parcheo',
            ],
            specs: [
                { k: 'administracion', l: 'Tipo de administración', opts: ['Administrable', 'No administrable', 'Controlado en la nube', 'No aplica'] },
                { k: 'ip', l: 'Dirección IP', ph: '192.168.1.20' },
                { k: 'mac', l: 'Dirección MAC', ph: '78:45:58:A1:B2:C3' },
                { k: 'puertos', l: 'Cantidad y tipo de puertos', ph: '24 puertos Gigabit, 2 puertos SFP' },
                { k: 'velocidad', l: 'Velocidad de red', opts: OPT_VELOCIDAD_RED },
                { k: 'poe', l: 'Tecnología PoE', opts: ['Sin PoE', 'PoE estándar', 'PoE+', 'PoE++', 'PoE pasivo', 'Alimentado por PoE'] },
                { k: 'bahias_capacidad', l: 'Bahías y capacidad en caso de NAS o servidor', ph: '4 bahías con 16 TB en RAID 5' },
                { k: 'ubicacion', l: 'Rack o ubicación física', ph: 'Site Principal Rack A' },
            ],
        },

        almacenamiento: {
            label: 'Almacenamiento',
            singular: 'Almacenamiento',
            prefix: 'VES-ALM',
            icon: 'fa-hard-drive',
            color: 'slate',
            placeholderMarca: 'Samsung',
            placeholderModelo: 'T7 Shield 2TB',
            subtipos: [
                'SSD externo portátil',
                'Disco duro externo portátil',
                'Disco duro externo de escritorio',
                'SSD interno NVMe M.2',
                'SSD interno SATA',
                'Disco duro interno para PC',
                'Disco duro interno para NAS',
                'Disco duro interno para servidor',
                'Disco duro interno para videovigilancia',
                'Memoria USB',
                'Tarjeta SD',
                'Tarjeta MicroSD',
                'Carcasa para disco duro',
                'Base acopladora de discos duros',
                'Unidad óptica externa DVD',
                'Cartucho de cinta de respaldo',
            ],
            specs: [
                {
                    k: 'capacidad',
                    l: 'Capacidad de almacenamiento',
                    opts: ['16 GB', '32 GB', '64 GB', '128 GB', '256 GB', '512 GB', '1 TB', '2 TB', '4 TB', '6 TB', '8 TB', '10 TB', '12 TB', '16 TB', '18 TB', '20 TB o más'],
                },
                {
                    k: 'interfaz',
                    l: 'Interfaz de conexión',
                    opts: ['USB 2.0', 'USB 3.0', 'USB 3.2', 'USB-C', 'Thunderbolt', 'SATA III', 'NVMe PCIe', 'SAS', 'Ranura SD', 'Ranura MicroSD'],
                },
                { k: 'formato', l: 'Tamaño o formato físico', opts: ['Portátil de bolsillo', '2.5 pulgadas', '3.5 pulgadas', 'M.2 2280', 'M.2 2230', 'Memoria USB', 'Tarjeta de memoria'] },
                { k: 'uso', l: 'Uso asignado', ph: 'Respaldo de información, expansión de equipo, disco para NAS' },
            ],
        },

        energia: {
            label: 'Energía y Respaldo',
            singular: 'Equipo de energía',
            prefix: 'VES-ENE',
            icon: 'fa-car-battery',
            color: 'amber',
            placeholderMarca: 'APC',
            placeholderModelo: 'Smart-UPS 1500VA',
            subtipos: [
                'No-Break interactivo',
                'No-Break online de doble conversión',
                'No-Break para montaje en rack',
                'Regulador de voltaje',
                'Barra de distribución PDU para rack',
                'Banco de baterías externo',
                'Estación de energía portátil',
            ],
            specs: [
                { k: 'capacidad_va', l: 'Capacidad en VA y Watts', ph: '1500 VA, 1000 W' },
                { k: 'topologia', l: 'Tipo de protección', opts: ['Interactivo', 'Online doble conversión', 'Respaldo básico', 'Solo regulación de voltaje', 'Distribución de energía PDU'] },
                { k: 'tomas', l: 'Cantidad de contactos eléctricos', ph: '8 contactos NEMA 5-15R' },
                { k: 'voltaje', l: 'Voltaje de operación', opts: ['120 V', '208 V', '220 V', 'Bivoltaje 120 V y 220 V'] },
                { k: 'formato_montaje', l: 'Formato de instalación', opts: ['Torre de piso', 'Compacto de escritorio', 'Rack de 1U', 'Rack de 2U', 'Rack vertical'] },
                { k: 'bateria_reemplazo', l: 'Modelo de batería interna', ph: 'APCRBC124' },
            ],
        },

        audiovideo: {
            label: 'Audio y Video',
            singular: 'Audio y Video',
            prefix: 'VES-AV',
            icon: 'fa-video',
            color: 'rose',
            placeholderMarca: 'Epson',
            placeholderModelo: 'PowerLite E20',
            subtipos: [
                'Proyector estándar',
                'Proyector de tiro corto',
                'Proyector de tiro ultracorto',
                'Proyector portátil',
                'Televisor Smart TV',
                'Pantalla interactiva táctil',
                'Barra de videoconferencia',
                'Sistema de videoconferencia para sala',
                'Cámara PTZ para sala de juntas',
                'Altavoz omnidireccional para conferencias',
                'Micrófono inalámbrico',
                'Sistema de sonido o bocina de sala',
                'Pantalla de proyección con tripié',
                'Pantalla de proyección fija',
                'Pantalla de proyección eléctrica',
                'Multiplicador de video HDMI',
            ],
            specs: [
                { k: 'resolucion', l: 'Resolución de imagen', opts: ['SVGA', 'XGA', 'WXGA', 'Full HD 1080p', 'WUXGA', '4K UHD', 'No aplica'] },
                { k: 'brillo_potencia', l: 'Brillo en lúmenes o pulgadas', ph: '3400 lúmenes o 65 pulgadas' },
                { k: 'tecnologia', l: 'Tecnología', opts: ['Lámpara', 'Láser', 'LED', 'Pantalla OLED', 'Pantalla QLED', 'Audio digital', 'No aplica'] },
                { k: 'conectividad', l: 'Entradas y conexiones', ph: 'HDMI, VGA, USB, Red' },
                { k: 'montaje', l: 'Ubicación de montaje', opts: ['Sobre mesa', 'Fijo en techo', 'Fijo en pared', 'Pedestal con ruedas', 'Portátil'] },
                { k: 'ubicacion', l: 'Sala o área instalada', ph: 'Sala de juntas principal' },
            ],
        },

        accesorio: {
            label: 'Accesorios',
            singular: 'Accesorio',
            prefix: 'VES-ACC',
            icon: 'fa-keyboard',
            color: 'orange',
            placeholderMarca: 'Logitech',
            placeholderModelo: 'MK295',
            subtipos: uni(
                [
                    'Combo teclado y mouse',
                    'Kit de varias piezas',
                    'Mouse inalámbrico',
                    'Mouse alámbrico',
                    'Mouse ergonómico',
                    'Teclado inalámbrico',
                    'Teclado alámbrico',
                    'Teclado numérico',
                    'Estación de acoplamiento',
                    'Concentrador USB',
                    'Cámara web',
                    'Diadema con micrófono',
                    'Audífonos',
                    'Bocinas de escritorio',
                    'Micrófono de escritorio',
                    'Lector de código de barras',
                    'Lector de huella digital',
                    'Lector de tarjetas SD',
                    'Lector de tarjetas inteligentes',
                    'Tableta gráfica de dibujo',
                    'Lápiz óptico',
                    'Apple Pencil',
                    'Cargador de laptop',
                    'Cargador de celular o tablet',
                    'Cargador inalámbrico',
                    'Batería portátil',
                    'Batería de repuesto',
                    'Switch KVM',
                    'Adaptador USB-C a HDMI',
                    'Adaptador USB-C a VGA',
                    'Adaptador HDMI a VGA',
                    'Adaptador DisplayPort a HDMI',
                    'Adaptador DisplayPort a VGA',
                    'Adaptador USB a Ethernet RJ45',
                    'Adaptador USB-C a Ethernet RJ45',
                    'Adaptador USB-C a USB-A',
                    'Módulo transceptor SFP de fibra',
                    'Inyector PoE',
                    'Receptor inalámbrico USB',
                    'Brazo sencillo para monitor',
                    'Brazo doble para monitor',
                    'Soporte de escritorio para monitor',
                    'Soporte de pared para pantalla',
                    'Soporte para laptop',
                    'Base enfriadora para laptop',
                    'Apuntador láser para presentaciones',
                    'Control remoto',
                    'Candado de seguridad para equipo',
                    'Mochila para laptop',
                    'Maletín para laptop',
                    'Funda protectora',
                    'Funda con teclado',
                    'Estuche rígido',
                    'Protector de pantalla',
                    'Tapete para mouse',
                    'Multicontacto con supresor de picos',
                    'Extensión eléctrica de uso rudo',
                ],
                GRP_CABLES,
                ['Otro accesorio']
            ),
            specs: [
                {
                    k: 'conexion',
                    l: 'Tipo de conexión',
                    opts: [
                        'Inalámbrico con receptor USB',
                        'Bluetooth',
                        'Inalámbrico dual Bluetooth y receptor USB',
                        'Cable USB-A',
                        'Cable USB-C',
                        'HDMI',
                        'DisplayPort',
                        'VGA',
                        'Audio 3.5 mm',
                        'Red RJ45',
                        'Fibra SFP',
                        'Corriente eléctrica',
                        'Sin conexión electrónica',
                    ],
                },
                { k: 'compatibilidad', l: 'Potencia o detalle técnico', ph: '65W, 100x100 VESA, Gigabit' },
                { k: 'contenido', l: 'Piezas incluidas', ph: 'Teclado, mouse y receptor USB' },
            ],
        },
    };

    const SUBTIPOS_KIT_SERIE = new Set([
        'Combo teclado y mouse',
        'Kit de varias piezas',
    ]);

    const SUBTIPOS_SERIE_OPCIONAL = new Set([
        'Brazo sencillo para monitor',
        'Brazo doble para monitor',
        'Soporte de escritorio para monitor',
        'Soporte de pared para pantalla',
        'Soporte para laptop',
        'Base enfriadora para laptop',
        'Multicontacto con supresor de picos',
        'Extensión eléctrica de uso rudo',
        'Apuntador láser para presentaciones',
        'Control remoto',
        'Candado de seguridad para equipo',
        'Mochila para laptop',
        'Maletín para laptop',
        'Funda protectora',
        'Estuche rígido',
        'Protector de pantalla',
        'Tapete para mouse',
        'Adaptador USB-C a HDMI',
        'Adaptador USB-C a VGA',
        'Adaptador HDMI a VGA',
        'Adaptador DisplayPort a HDMI',
        'Adaptador DisplayPort a VGA',
        'Adaptador USB a Ethernet RJ45',
        'Adaptador USB-C a Ethernet RJ45',
        'Adaptador USB-C a USB-A',
        ...GRP_CABLES,
        'Otro accesorio',
    ]);

    /* ---------------------------------------------------
       Catálogo de accesorios y componentes incluidos
       en la ficha de un equipo (sin barras ni paréntesis).
    --------------------------------------------------- */

    const S = (icon, reqMarca, reqSerie, phMarca, phModelo, phSerie) => ({
        modo: 'serie',
        reqMarca,
        reqSerie,
        icon,
        phMarca,
        phModelo,
        phSerie,
    });

    const P = (icon, phMarca, phModelo) => ({
        modo: 'simple',
        icon,
        phMarca,
        phModelo,
    });

    const CATALOGO_COMPONENTES = {
        // Componentes con número de serie
        'Monitor externo': S('fa-display', true, true, 'Dell', 'P2422H 24 pulgadas', 'Número de serie del monitor'),
        'Mouse': S('fa-computer-mouse', true, true, 'Logitech', 'M220 Silent', 'Número de serie del mouse'),
        'Teclado': S('fa-keyboard', true, true, 'Logitech', 'K295', 'Número de serie del teclado'),
        'Teclado numérico': S('fa-keyboard', false, false, 'Logitech', 'Numérico USB', 'Número de serie del teclado'),
        'Combo teclado y mouse': {
            modo: 'combo',
            reqMarca: true,
            reqSerie: true,
            icon: 'fa-keyboard',
            phMarca: 'Logitech',
            phModelo: 'MK295',
        },
        'Cargador': S('fa-plug', false, false, 'Lenovo', '65W USB-C', 'Número de serie del cargador'),
        'Cargador inalámbrico': S('fa-bolt', false, false, 'Samsung', 'Carga rápida 15W', 'Número de serie del cargador'),
        'Batería adicional': S('fa-battery-full', true, false, 'Lenovo', 'Batería de 6 celdas', 'Número de serie de la batería'),
        'Batería portátil': S('fa-battery-half', false, false, 'Anker', '10000 mAh', 'Número de serie de la batería'),
        'No-Break': S('fa-car-battery', true, true, 'APC', 'Back-UPS 600VA', 'Número de serie del No-Break'),
        'Regulador de voltaje': S('fa-bolt', false, false, 'Koblenz', '1000 VA', 'Número de serie del regulador'),
        'Estación de acoplamiento': S('fa-ethernet', true, true, 'Dell', 'WD19S', 'Número de serie de la estación'),
        'Concentrador USB': S('fa-ethernet', false, false, 'Ugreen', 'Concentrador de 4 puertos', 'Número de serie del concentrador'),
        'Cámara web': S('fa-camera', true, true, 'Logitech', 'C920 HD', 'Número de serie de la cámara'),
        'Diadema con micrófono': S('fa-headset', true, false, 'Jabra', 'Evolve 20', 'Número de serie de la diadema'),
        'Audífonos': S('fa-headphones', false, false, 'Logitech', 'Audífonos alámbricos', 'Número de serie de los audífonos'),
        'Bocinas': S('fa-volume-high', false, false, 'Logitech', 'Z200', 'Número de serie de las bocinas'),
        'Micrófono': S('fa-microphone', false, false, 'Jabra', 'Micrófono USB', 'Número de serie del micrófono'),
        'Altavoz para conferencias': S('fa-volume-high', true, false, 'Jabra', 'Speak 510', 'Número de serie del altavoz'),
        'Lector de código de barras': S('fa-barcode', true, true, 'Zebra', 'DS2208', 'Número de serie del lector'),
        'Lector de huella digital': S('fa-fingerprint', true, true, 'ZKTeco', 'Lector USB', 'Número de serie del lector'),
        'Escáner de documentos': S('fa-print', true, true, 'Epson', 'WorkForce DS-530', 'Número de serie del escáner'),
        'Adaptador USB-C a HDMI': S('fa-link', false, false, 'Dell', 'Adaptador de video', 'Número de serie opcional'),
        'Adaptador USB-C a VGA': S('fa-link', false, false, 'Dell', 'Adaptador de video', 'Número de serie opcional'),
        'Adaptador HDMI a VGA': S('fa-link', false, false, 'Genérico', 'Convertidor de video', 'Número de serie opcional'),
        'Adaptador DisplayPort a HDMI': S('fa-link', false, false, 'Dell', 'Adaptador de video', 'Número de serie opcional'),
        'Adaptador DisplayPort a VGA': S('fa-link', false, false, 'Dell', 'Adaptador de video', 'Número de serie opcional'),
        'Adaptador USB a Ethernet RJ45': S('fa-ethernet', false, false, 'TP-Link', 'UE300 Gigabit', 'Número de serie opcional'),
        'Adaptador USB-C a Ethernet RJ45': S('fa-ethernet', false, false, 'Ugreen', 'Gigabit Ethernet', 'Número de serie opcional'),
        'Adaptador USB-C a USB-A': S('fa-link', false, false, 'Anker', 'Adaptador OTG', 'Número de serie opcional'),
        'Lector de tarjetas SD': S('fa-sd-card', false, false, 'Kingston', 'Lector USB 3.0', 'Número de serie opcional'),
        'Disco duro externo': S('fa-hard-drive', true, true, 'Western Digital', 'Elements 1 TB', 'Número de serie del disco'),
        'SSD externo': S('fa-hard-drive', true, true, 'Samsung', 'T7 1 TB', 'Número de serie del SSD'),
        'Disco duro interno instalado': S('fa-hard-drive', true, true, 'Seagate', 'IronWolf 4 TB SATA', 'Número de serie del disco'),
        'SSD interno instalado': S('fa-hard-drive', true, true, 'Kingston', 'NVMe 1 TB', 'Número de serie del SSD'),
        'Memoria USB': S('fa-memory', false, false, 'Kingston', '64 GB USB 3.2', 'Número de serie opcional'),
        'Tarjeta SD': S('fa-sd-card', false, false, 'SanDisk', '128 GB', 'Número de serie opcional'),
        'Tarjeta MicroSD': S('fa-sd-card', false, false, 'SanDisk', '128 GB', 'Número de serie opcional'),
        'Tarjeta SIM': S('fa-sim-card', false, false, 'Telcel', 'Nano SIM', 'Código ICCID de la SIM'),
        'Módulo transceptor SFP de fibra': S('fa-ethernet', true, true, 'Ubiquiti', 'SFP+ 10G', 'Número de serie del módulo'),
        'Inyector PoE': S('fa-plug', false, false, 'Ubiquiti', 'PoE 48V', 'Número de serie del inyector'),
        'Lápiz óptico': S('fa-pen-nib', true, false, 'Apple', 'Apple Pencil', 'Número de serie del lápiz'),
        'Funda con teclado': S('fa-keyboard', true, false, 'Logitech', 'Combo Touch', 'Número de serie del teclado'),
        'Receptor inalámbrico USB': S('fa-plug', false, false, 'Logitech', 'Receptor Unifying', 'Número de serie del receptor'),

        // Componentes simples sin número de serie obligatorio
        'Control remoto': P('fa-gamepad', 'Epson', 'Control original'),
        'Apuntador láser para presentaciones': P('fa-bullseye', 'Logitech', 'R400'),
        'Mochila para laptop': P('fa-briefcase', 'Lenovo', 'Mochila de 15.6 pulgadas'),
        'Maletín para laptop': P('fa-briefcase', 'Targus', 'Maletín ejecutivo'),
        'Funda protectora': P('fa-shield', 'Genérica', 'Funda de uso rudo'),
        'Estuche rígido': P('fa-shield', 'Genérico', 'Estuche protector'),
        'Protector de pantalla': P('fa-shield', 'Genérico', 'Cristal templado'),
        'Candado de seguridad para equipo': P('fa-lock', 'Kensington', 'Candado con llave'),
        'Base enfriadora para laptop': P('fa-fan', 'Cooler Master', 'Base con ventiladores'),
        'Soporte para laptop': P('fa-laptop', 'Genérico', 'Soporte plegable de aluminio'),
        'Brazo sencillo para monitor': P('fa-display', 'Mountup', 'Brazo articulado de 1 monitor'),
        'Brazo doble para monitor': P('fa-display', 'Mountup', 'Brazo articulado de 2 monitores'),
        'Soporte de escritorio para monitor': P('fa-display', 'Genérico', 'Base de escritorio'),
        'Soporte de pared para pantalla': P('fa-box', 'Genérico', 'Soporte VESA de pared'),
        'Soporte de techo para proyector': P('fa-video', 'Genérico', 'Soporte ajustable de techo'),
        'Rieles de montaje para rack': P('fa-server', 'Synology', 'Juego de rieles para rack'),
        'Antena adicional': P('fa-tower-broadcast', 'Ubiquiti', 'Antena externa'),
        'Multicontacto con supresor de picos': P('fa-plug', 'Steren', 'Barra de 6 contactos'),
        'Extensión eléctrica de uso rudo': P('fa-plug', 'Truper', 'Extensión de 5 metros'),
        'Tapete para mouse': P('fa-computer-mouse', 'Genérico', 'Tapete de tela'),
        'Switch KVM': P('fa-ethernet', 'TP-Link', 'KVM de 2 puertos'),
        'Tableta gráfica de dibujo': P('fa-pen-nib', 'Wacom', 'Intuos'),
        'Tóner de repuesto': P('fa-box', 'HP', 'Tóner original en caja'),
        'Bote de tinta de repuesto': P('fa-box', 'Epson', 'Tinta original'),
        'Charola de papel adicional': P('fa-box', 'HP', 'Bandeja de 550 hojas'),
        'Pantalla de proyección': P('fa-video', 'Genérica', 'Pantalla de 100 pulgadas'),
        'Lámpara de repuesto para proyector': P('fa-box', 'Epson', 'Lámpara original'),

        // Cables individuales
        'Cable de poder': P('fa-plug', 'Genérico', 'Cable de corriente de 1.8 m'),
        'Cable de poder C13': P('fa-plug', 'Genérico', 'Cable de alimentación para servidor o monitor'),
        'Cable de poder C14': P('fa-plug', 'APC', 'Cable de interconexión para No-Break o PDU'),
        'Cable de red Cat5e': P('fa-network-wired', 'Genérico', 'Patch cord UTP Cat5e'),
        'Cable de red Cat6': P('fa-network-wired', 'Panduit', 'Patch cord UTP Cat6'),
        'Cable de red Cat6A': P('fa-network-wired', 'Panduit', 'Patch cord UTP Cat6A'),
        'Cable de fibra óptica': P('fa-network-wired', 'LinkedPro', 'Jumper de fibra óptica LC'),
        'Cable de consola serial': P('fa-terminal', 'Cisco', 'Cable de configuración RJ45 a USB'),
        'Cable HDMI': P('fa-link', 'Ugreen', 'Cable HDMI de 1.8 m'),
        'Cable DisplayPort': P('fa-link', 'Dell', 'Cable DisplayPort de 1.8 m'),
        'Cable Mini DisplayPort': P('fa-link', 'Genérico', 'Cable Mini DisplayPort de 1.8 m'),
        'Cable VGA': P('fa-link', 'Genérico', 'Cable VGA de 1.8 m'),
        'Cable DVI': P('fa-link', 'Genérico', 'Cable DVI de 1.8 m'),
        'Cable USB-A a USB-A': P('fa-link', 'Genérico', 'Cable USB 3.0'),
        'Cable USB-A a USB-B para impresora': P('fa-link', 'Genérico', 'Cable USB para impresora o escáner'),
        'Cable USB-A a USB-C': P('fa-link', 'Genérico', 'Cable de carga y datos de 1 m'),
        'Cable USB-C a USB-C': P('fa-link', 'Samsung', 'Cable USB-C de carga rápida'),
        'Cable USB-A a Micro USB': P('fa-link', 'Genérico', 'Cable Micro USB de 1 m'),
        'Cable USB-A a Lightning': P('fa-link', 'Apple', 'Cable Lightning de 1 m'),
        'Cable USB-C a Lightning': P('fa-link', 'Apple', 'Cable de carga rápida Lightning'),
        'Cable Thunderbolt': P('fa-link', 'Apple', 'Cable Thunderbolt de alta velocidad'),
        'Cable de audio 3.5 mm': P('fa-link', 'Genérico', 'Cable auxiliar de audio'),
        'Cable de audio RCA': P('fa-link', 'Genérico', 'Cable de audio estéreo RCA'),
        'Cable de audio XLR': P('fa-link', 'Genérico', 'Cable para micrófono profesional'),
        'Cable telefónico RJ11': P('fa-link', 'Genérico', 'Cable de línea telefónica'),
    };

    const GRP_PROTECCION = [
        'Mochila para laptop',
        'Maletín para laptop',
        'Funda protectora',
        'Estuche rígido',
        'Protector de pantalla',
        'Candado de seguridad para equipo',
    ];

    const GRP_ENERGIA_COMP = [
        'Cargador',
        'Cargador inalámbrico',
        'Batería adicional',
        'Batería portátil',
        'No-Break',
        'Regulador de voltaje',
        'Multicontacto con supresor de picos',
        'Extensión eléctrica de uso rudo',
    ];

    const GRP_PERIFERICOS = [
        'Mouse',
        'Teclado',
        'Teclado numérico',
        'Combo teclado y mouse',
        'Receptor inalámbrico USB',
        'Cámara web',
        'Diadema con micrófono',
        'Audífonos',
        'Bocinas',
        'Micrófono',
        'Altavoz para conferencias',
        'Lector de código de barras',
        'Lector de huella digital',
        'Escáner de documentos',
        'Apuntador láser para presentaciones',
        'Tapete para mouse',
        'Tableta gráfica de dibujo',
    ];

    const GRP_ADAPTADORES = [
        'Estación de acoplamiento',
        'Concentrador USB',
        'Adaptador USB-C a HDMI',
        'Adaptador USB-C a VGA',
        'Adaptador HDMI a VGA',
        'Adaptador DisplayPort a HDMI',
        'Adaptador DisplayPort a VGA',
        'Adaptador USB a Ethernet RJ45',
        'Adaptador USB-C a Ethernet RJ45',
        'Adaptador USB-C a USB-A',
        'Lector de tarjetas SD',
        'Switch KVM',
    ];

    const GRP_MEMORIAS = [
        'Disco duro externo',
        'SSD externo',
        'Memoria USB',
        'Tarjeta SD',
        'Tarjeta MicroSD',
    ];

    const COMPLEMENTOS_POR_TIPO = {
        laptop: uni(
            ['Cargador', 'Monitor externo'],
            GRP_PERIFERICOS,
            GRP_ADAPTADORES,
            GRP_PROTECCION,
            GRP_ENERGIA_COMP,
            GRP_MEMORIAS,
            [
                'Base enfriadora para laptop',
                'Soporte para laptop',
                'Brazo sencillo para monitor',
                'Brazo doble para monitor',
            ],
            GRP_CABLES
        ),

        desktop: uni(
            ['Monitor externo', 'Combo teclado y mouse', 'Teclado', 'Mouse'],
            ['No-Break', 'Regulador de voltaje', 'Cargador', 'Multicontacto con supresor de picos'],
            GRP_PERIFERICOS,
            GRP_ADAPTADORES,
            [
                'Brazo sencillo para monitor',
                'Brazo doble para monitor',
                'Candado de seguridad para equipo',
            ],
            GRP_MEMORIAS,
            GRP_CABLES
        ),

        monitor: uni(
            ['Cargador', 'Cable de poder', 'Cable de poder C13'],
            ['Cable HDMI', 'Cable DisplayPort', 'Cable Mini DisplayPort', 'Cable USB-C a USB-C', 'Cable VGA', 'Cable DVI'],
            [
                'Adaptador USB-C a HDMI',
                'Adaptador HDMI a VGA',
                'Adaptador DisplayPort a HDMI',
                'Adaptador DisplayPort a VGA',
            ],
            [
                'Brazo sencillo para monitor',
                'Brazo doble para monitor',
                'Soporte de escritorio para monitor',
                'Soporte de pared para pantalla',
            ],
            ['Cámara web', 'Bocinas', 'Candado de seguridad para equipo']
        ),

        celular: uni(
            ['Cargador', 'Cargador inalámbrico', 'Batería portátil'],
            ['Cable USB-C a USB-C', 'Cable USB-A a USB-C', 'Cable USB-A a Lightning', 'Cable USB-C a Lightning', 'Cable USB-A a Micro USB'],
            ['Tarjeta SIM', 'Tarjeta MicroSD'],
            ['Funda protectora', 'Protector de pantalla'],
            ['Audífonos', 'Diadema con micrófono', 'Lápiz óptico', 'Adaptador USB-C a USB-A']
        ),

        tablet: uni(
            ['Cargador', 'Batería portátil'],
            ['Lápiz óptico', 'Funda con teclado', 'Teclado', 'Mouse'],
            ['Cable USB-C a USB-C', 'Cable USB-A a USB-C', 'Cable USB-A a Lightning', 'Cable USB-C a Lightning'],
            ['Tarjeta SIM', 'Tarjeta MicroSD'],
            ['Funda protectora', 'Estuche rígido', 'Protector de pantalla'],
            ['Concentrador USB', 'Adaptador USB-C a HDMI', 'Audífonos']
        ),

        impresora: uni(
            ['Cable de poder', 'Cable USB-A a USB-B para impresora', 'Cable de red Cat6', 'Cable telefónico RJ11'],
            ['Cargador', 'No-Break', 'Regulador de voltaje'],
            ['Tóner de repuesto', 'Bote de tinta de repuesto', 'Charola de papel adicional']
        ),

        redes: uni(
            ['Disco duro interno instalado', 'SSD interno instalado'],
            ['Módulo transceptor SFP de fibra', 'Inyector PoE'],
            ['Rieles de montaje para rack', 'Soporte de pared para pantalla', 'Antena adicional'],
            ['Cable de poder', 'Cable de poder C13', 'Cable de poder C14', 'Cargador', 'No-Break'],
            ['Cable de red Cat5e', 'Cable de red Cat6', 'Cable de red Cat6A', 'Cable de fibra óptica', 'Cable de consola serial'],
            ['Disco duro externo', 'SSD externo']
        ),

        almacenamiento: uni(
            ['Cable USB-C a USB-C', 'Cable USB-A a USB-C', 'Cable USB-A a USB-A', 'Cable Thunderbolt', 'Cargador', 'Cable de poder'],
            ['Estuche rígido', 'Funda protectora', 'Adaptador USB-C a USB-A', 'Lector de tarjetas SD']
        ),

        energia: uni(
            ['Batería adicional', 'Rieles de montaje para rack'],
            ['Cable de poder', 'Cable de poder C13', 'Cable de poder C14', 'Cable USB-A a USB-B para impresora', 'Cable de red Cat6'],
            ['Multicontacto con supresor de picos', 'Extensión eléctrica de uso rudo']
        ),

        audiovideo: uni(
            ['Control remoto', 'Apuntador láser para presentaciones'],
            ['Altavoz para conferencias', 'Micrófono', 'Cámara web', 'Bocinas'],
            ['Pantalla de proyección', 'Soporte de techo para proyector', 'Soporte de pared para pantalla'],
            ['Maletín para laptop', 'Mochila para laptop', 'Estuche rígido', 'Candado de seguridad para equipo'],
            ['Adaptador USB-C a HDMI', 'Adaptador HDMI a VGA', 'Concentrador USB'],
            [
                'Cable de poder',
                'Cable HDMI',
                'Cable DisplayPort',
                'Cable VGA',
                'Cable USB-C a USB-C',
                'Cable de red Cat6',
                'Cable de audio 3.5 mm',
                'Cable de audio RCA',
                'Cable de audio XLR',
            ]
        ),

        accesorio: uni(
            ['Receptor inalámbrico USB', 'Cargador', 'Batería adicional'],
            ['Funda protectora', 'Estuche rígido'],
            ['Cable de poder', 'Cable USB-C a USB-C', 'Cable USB-A a USB-C', 'Cable HDMI', 'Cable DisplayPort']
        ),
    };

    function normalizarComponente(raw) {
        if (!raw) return null;
        if (typeof raw === 'string') {
            return {
                tipo: raw,
                marca: '',
                modelo: '',
                serie: '',
                serie_teclado: '',
                serie_mouse: '',
                serie_receptor: '',
            };
        }
        return {
            tipo: raw.tipo || 'Accesorio',
            marca: raw.marca || '',
            modelo: raw.modelo || '',
            serie: raw.serie || '',
            serie_teclado: raw.serie_teclado || '',
            serie_mouse: raw.serie_mouse || '',
            serie_receptor: raw.serie_receptor || '',
        };
    }

    function iconoComponente(tipoNombre) {
        const cfg = CATALOGO_COMPONENTES[tipoNombre];
        if (cfg?.icon) return cfg.icon;

        const texto = norm(tipoNombre);
        if (/monitor|pantalla|brazo/.test(texto)) return 'fa-display';
        if (/mouse|raton|tapete/.test(texto)) return 'fa-computer-mouse';
        if (/teclado|combo/.test(texto)) return 'fa-keyboard';
        if (/mochila|maletin|estuche|funda/.test(texto)) return 'fa-briefcase';
        if (/cargador|corriente|poder|poe|break|regulador|multicontacto|extension/.test(texto)) return 'fa-plug';
        if (/disco|ssd|memoria/.test(texto)) return 'fa-hard-drive';
        if (/cable|hdmi|adaptador|usb|fibra/.test(texto)) return 'fa-link';
        if (/candado/.test(texto)) return 'fa-lock';
        if (/control/.test(texto)) return 'fa-gamepad';

        return 'fa-box';
    }

    function resumenSerieComponente(c) {
        if (c.serie_teclado || c.serie_mouse) {
            const p = [];
            if (c.serie_teclado) p.push(`KB: ${c.serie_teclado}`);
            if (c.serie_mouse) p.push(`MS: ${c.serie_mouse}`);
            if (c.serie_receptor) p.push(`RCV: ${c.serie_receptor}`);
            return p.join(' · ');
        }
        return c.serie || '';
    }

    /* ---------------------------------------------------
       1. Estado del equipo (Nuevo, Seminuevo, Usado, Reacondicionado)
    --------------------------------------------------- */

    const ESTADOS = {
        nuevo: {
            label: 'Nuevo',
            badge: 'badge-new',
        },
        seminuevo: {
            label: 'Seminuevo',
            badge: 'badge-semi',
        },
        usado: {
            label: 'Usado',
            badge: 'badge-used',
        },
        reacondicionado: {
            label: 'Reacondicionado',
            badge: 'badge-refurb',
        },
    };

    /* ---------------------------------------------------
       2. Condición (Excelente, Bueno, Regular, Con detalles, Dañado)
    --------------------------------------------------- */

    const CONDICIONES = {
        excelente: {
            label: 'Excelente',
            badge: 'badge-cond-excelente',
        },
        bueno: {
            label: 'Bueno',
            badge: 'badge-cond-bueno',
        },
        regular: {
            label: 'Regular',
            badge: 'badge-cond-regular',
        },
        con_detalles: {
            label: 'Con detalles',
            badge: 'badge-cond-detalles',
        },
        danado: {
            label: 'Dañado',
            badge: 'badge-cond-danado',
        },
    };

    /* ---------------------------------------------------
       3. Estatus
    --------------------------------------------------- */

    const ESTATUS_DEFAULT = 'disponible';

    const ESTATUS = {
        disponible: {
            label: 'Disponible',
            badge: 'badge-free',
        },
        asignado: {
            label: 'Asignado',
            badge: 'badge-assigned',
        },
        en_prestamo: {
            label: 'En préstamo',
            badge: 'badge-loan',
        },
        reservado: {
            label: 'Reservado',
            badge: 'badge-reserved',
        },
        en_mantenimiento: {
            label: 'En mantenimiento',
            badge: 'badge-maint',
        },
        en_garantia: {
            label: 'En garantía',
            badge: 'badge-warranty',
        },
        deshabilitado: {
            label: 'Deshabilitado',
            badge: 'badge-disabled',
        },
        extraviado: {
            label: 'Extraviado',
            badge: 'badge-lost',
        },
        robado: {
            label: 'Robado',
            badge: 'badge-stolen',
        },
        baja: {
            label: 'Baja',
            badge: 'badge-retired',
        },
    };

    const GARANTIAS = {
        vigente: 'Vigente',
        por_vencer: 'Por vencer',
        vencida: 'Vencida',
    };

    /* ---------------------------------------------------
       Estado en memoria cargado desde BD (sin datos falsos)
    --------------------------------------------------- */

    let items = [];
    let guardando = false;

    const state = {
        tipo: 'todos',
        q: '',
        estado: '',
        condicion: '',
        estatus: '',
        garantia: '',
        pagina: 1,
        porPagina: 10,
    };

    const porId = (id) => items.find((e) => Number(e.id) === Number(id));
    const porCodigo = (c) => items.find((e) => e.codigo === c);

    function siguienteCodigoPreview(tipo) {
        const prefix = TIPOS[tipo]?.prefix || 'VES-EQP';

        let max = 0;
        items.forEach((e) => {
            if (e.tipo === tipo && e.codigo) {
                const m = String(e.codigo).match(/-(\d+)$/);
                if (m) {
                    const num = Number(m[1]);
                    if (num > max) max = num;
                }
            }
        });

        let n = max + 1;
        let codigo;

        do {
            codigo = `${prefix}-${String(n).padStart(3, '0')}`;
            n++;
        } while (porCodigo(codigo));

        return codigo;
    }

    function obtenerSubtipo(e) {
        return e.subtipo || e.specs?.subtipo || '';
    }

    function todasLasSeriesDe(e) {
        const series = [
            e.serie,
            e.serie_teclado,
            e.serie_mouse,
            e.serie_receptor,
        ];

        (e.extras || []).forEach((raw) => {
            const c = normalizarComponente(raw);
            if (!c) return;
            series.push(c.serie, c.serie_teclado, c.serie_mouse, c.serie_receptor);
        });

        return series.map((s) => String(s ?? '').trim()).filter(Boolean);
    }

    function cargarDatosIniciales() {
        const iniciales = Array.isArray(CONFIG.items) ? CONFIG.items : [];

        items = iniciales.map((raw) => ({
            ...raw,
            condicion: raw.condicion || 'excelente',
            subtipo: raw.subtipo || raw.specs?.subtipo || '',
            extras: (raw.extras || []).map(normalizarComponente).filter(Boolean),
        }));
    }

    function garantiaInfo(e) {
        if (!e.garantia_hasta) {
            return { key: '', label: 'Sin garantía' };
        }

        const limpia = String(e.garantia_hasta).split('T')[0];
        const [y, m, d] = limpia.split('-').map(Number);
        const fin = new Date(y, m - 1, d);
        const hoy = new Date();

        hoy.setHours(0, 0, 0, 0);

        const dias = Math.round((fin - hoy) / 86400000);

        if (dias < 0) return { key: 'vencida', label: GARANTIAS.vencida };
        if (dias <= 90) return { key: 'por_vencer', label: GARANTIAS.por_vencer };

        return { key: 'vigente', label: GARANTIAS.vigente };
    }

    const estadoDe = (e) => ESTADOS[e.estado] || ESTADOS.nuevo;
    const condicionDe = (e) => CONDICIONES[e.condicion] || CONDICIONES.excelente;
    const estatusDe = (e) => ESTATUS[e.estatus] || ESTATUS.disponible;

    const opcionesHTML = (mapa, primera) =>
        `<option value="">${esc(primera)}</option>` +
        Object.entries(mapa)
            .map(([k, v]) => `<option value="${esc(k)}">${esc(v.label)}</option>`)
            .join('');

    /* ---------------------------------------------------
       Render: selector de categorías
    --------------------------------------------------- */

    let menuAbierto = false;
    let menuIdx = 0;

    function opcionesCategoria() {
        return [
            {
                key: 'todos',
                label: 'Todos',
                icon: 'fa-layer-group',
                color: 'slate',
                n: items.length,
            },
            ...Object.entries(TIPOS).map(([key, t]) => ({
                key,
                label: t.label,
                icon: t.icon,
                color: t.color,
                n: items.filter((e) => e.tipo === key).length,
            })),
        ];
    }

    function renderTabs() {
        const ops = opcionesCategoria();
        const act = ops.find((o) => o.key === state.tipo) || ops[0];

        $('#typeTabs').innerHTML = `
            <div class="type-select ${menuAbierto ? 'open' : ''}">
                <button
                    type="button"
                    class="type-select-btn"
                    id="typeSelectBtn"
                    aria-haspopup="listbox"
                    aria-expanded="${menuAbierto}"
                    aria-controls="typeSelectList"
                    ${menuAbierto ? `aria-activedescendant="typeOpt-${ops[menuIdx].key}"` : ''}
                >
                    <span class="tipo-icon sm ${act.color}" aria-hidden="true">
                        <i class="fas ${act.icon}"></i>
                    </span>

                    <span class="type-select-text">
                        <small>Categoría</small>
                        <strong>${esc(act.label)}</strong>
                    </span>

                    <span class="type-count">${act.n}</span>

                    <i class="fas fa-chevron-down type-select-chevron" aria-hidden="true"></i>
                </button>

                <ul
                    class="type-select-menu"
                    id="typeSelectList"
                    role="listbox"
                    aria-label="Categoría de equipo"
                    ${menuAbierto ? '' : 'hidden'}
                >
                    ${ops.map((o, i) => `
                        <li
                            role="option"
                            id="typeOpt-${o.key}"
                            data-tipo="${o.key}"
                            class="type-option
                                ${o.key === state.tipo ? 'selected' : ''}
                                ${menuAbierto && i === menuIdx ? 'focused' : ''}"
                            aria-selected="${o.key === state.tipo}"
                        >
                            <span class="tipo-icon sm ${o.color}" aria-hidden="true">
                                <i class="fas ${o.icon}"></i>
                            </span>
                            <span class="type-option-label">${esc(o.label)}</span>
                            <span class="type-count">${o.n}</span>
                            <i class="fas fa-check" aria-hidden="true"></i>
                        </li>
                    `).join('')}
                </ul>
            </div>
        `;

        if (menuAbierto) {
            const f = $('.type-option.focused');
            if (f) f.scrollIntoView({ block: 'nearest' });
        }
    }

    function alternarMenu(abrir) {
        menuAbierto = abrir;

        if (abrir) {
            menuIdx = Math.max(
                0,
                opcionesCategoria().findIndex((o) => o.key === state.tipo)
            );
        }

        renderTabs();
        $('#typeSelectBtn').focus();
    }

    function elegirCategoria(key) {
        state.tipo = key;
        state.pagina = 1;
        menuAbierto = false;

        render();
        $('#typeSelectBtn').focus();
    }

    function moverFocoMenu(idx) {
        const ops = opcionesCategoria();
        menuIdx = (idx + ops.length) % ops.length;

        root.querySelectorAll('.type-option').forEach((li, i) => {
            li.classList.toggle('focused', i === menuIdx);
        });

        $('#typeSelectBtn').setAttribute(
            'aria-activedescendant',
            `typeOpt-${ops[menuIdx].key}`
        );

        const f = $('.type-option.focused');
        if (f) f.scrollIntoView({ block: 'nearest' });
    }

    /* ---------------------------------------------------
       Render: tabla
    --------------------------------------------------- */

    function filtrar() {
        const q = norm(state.q.trim());

        return items.filter((e) => {
            if (state.tipo !== 'todos' && e.tipo !== state.tipo) return false;
            if (state.estado && e.estado !== state.estado) return false;
            if (state.condicion && e.condicion !== state.condicion) return false;
            if (state.estatus && e.estatus !== state.estatus) return false;
            if (state.garantia && garantiaInfo(e).key !== state.garantia) return false;

            if (q) {
                const textosExtras = (e.extras || []).flatMap((raw) => {
                    const c = normalizarComponente(raw);
                    return [
                        c.tipo,
                        c.marca,
                        c.modelo,
                        c.serie,
                        c.serie_teclado,
                        c.serie_mouse,
                        c.serie_receptor,
                    ];
                });

                const pool = norm([
                    e.codigo,
                    e.serie,
                    e.serie_teclado,
                    e.serie_mouse,
                    e.serie_receptor,
                    e.marca,
                    e.modelo,
                    e.subtipo,
                    e.area,
                    estadoDe(e).label,
                    condicionDe(e).label,
                    estatusDe(e).label,
                    ...Object.values(e.specs || {}),
                    ...textosExtras,
                ].join(' '));

                if (!pool.includes(q)) return false;
            }

            return true;
        });
    }

    function renderSerieCelda(e) {
        if (e.serie_teclado || e.serie_mouse) {
            const partes = [];
            if (e.serie_teclado) partes.push(`KB: ${esc(e.serie_teclado)}`);
            if (e.serie_mouse) partes.push(`MS: ${esc(e.serie_mouse)}`);
            return `<div class="text-xs">S/N ${partes.join(' · ')}</div>`;
        }

        return `
            <div class="text-xs">
                ${e.serie ? 'S/N ' + esc(e.serie) : 'Sin número de serie'}
            </div>
        `;
    }

    function celdaAccesorios(e) {
        const n = e.extras?.length || 0;

        if (!n) {
            return '<span class="text-muted">Sin accesorios</span>';
        }

        return `
            <span class="acc-count">
                <i class="fas fa-boxes-stacked" aria-hidden="true"></i>
                ${n} ${n === 1 ? 'accesorio' : 'accesorios'}
            </span>
        `;
    }

    function renderPaginacion(total) {
        const paginas = Math.max(1, Math.ceil(total / state.porPagina));
        state.pagina = Math.max(1, Math.min(state.pagina, paginas));

        const inicio = (state.pagina - 1) * state.porPagina;

        $('#pageSize').value = String(state.porPagina);
        $('#pageInfo').textContent = `Mostrando ${total ? inicio + 1 : 0}–${Math.min(
            inicio + state.porPagina,
            total
        )} de ${total} registros`;

        const boton = (pagina, texto, label, disabled = false, actual = false) => `
            <button
                type="button"
                class="page-btn"
                data-pagina="${pagina}"
                aria-label="${label}"
                ${disabled ? 'disabled' : ''}
                ${actual ? 'aria-current="page"' : ''}
            >
                ${texto}
            </button>
        `;

        const visibles = [
            ...new Set([1, paginas, state.pagina - 1, state.pagina, state.pagina + 1]),
        ]
            .filter((n) => n >= 1 && n <= paginas)
            .sort((a, b) => a - b);

        let anterior = 0;

        const numeros = visibles
            .map((n) => {
                const puntos =
                    anterior && n - anterior > 1
                        ? '<span class="page-dots" aria-hidden="true">…</span>'
                        : '';
                anterior = n;
                return puntos + boton(n, n, `Página ${n}`, false, n === state.pagina);
            })
            .join('');

        $('#paginacion').innerHTML =
            boton(
                state.pagina - 1,
                '<i class="fas fa-chevron-left" aria-hidden="true"></i>',
                'Página anterior',
                state.pagina === 1
            ) +
            numeros +
            boton(
                state.pagina + 1,
                '<i class="fas fa-chevron-right" aria-hidden="true"></i>',
                'Página siguiente',
                state.pagina === paginas
            );

        return inicio;
    }

    function renderTabla() {
        const lista = filtrar();
        const inicio = renderPaginacion(lista.length);
        const body = $('#equiposBody');

        $('#resultCount').textContent = `(${lista.length})`;

        if (!lista.length) {
            body.innerHTML = `
                <tr>
                    <td colspan="11">
                        <div class="empty-state">
                            <i class="fas fa-box-open" aria-hidden="true"></i>
                            ${
                                items.length === 0
                                    ? 'Aún no hay equipos registrados en la base de datos.'
                                    : 'No se encontraron equipos con esos filtros.'
                            }
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        body.innerHTML = lista
            .slice(inicio, inicio + state.porPagina)
            .map((e) => {
                const t = TIPOS[e.tipo] || TIPOS.laptop;
                const est = estadoDe(e);
                const cond = condicionDe(e);
                const sta = estatusDe(e);
                const g = garantiaInfo(e);
                const sub = obtenerSubtipo(e);

                return `
                    <tr class="equipo-row" data-id="${e.id}" tabindex="0">
                        <td>
                            <div class="equipo-cell">
                                <span class="tipo-icon ${t.color}" aria-hidden="true">
                                    <i class="fas ${t.icon}"></i>
                                </span>
                                <div>
                                    <strong>${esc(e.marca)} ${esc(e.modelo)}</strong>
                                    ${renderSerieCelda(e)}
                                </div>
                            </div>
                        </td>

                        <td>
                            <code class="codigo">${esc(e.codigo)}</code>
                        </td>

                        <td>
                            <span class="badge badge-type">${esc(t.singular)}</span>
                        </td>

                        <td>${esc(sub) || '<span class="text-muted">—</span>'}</td>

                        <td>${celdaAccesorios(e)}</td>

                        <td>${esc(e.area) || '<span class="text-muted">—</span>'}</td>

                        <td>
                            <span class="badge ${est.badge}">${esc(est.label)}</span>
                        </td>

                        <td>
                            <span class="badge ${cond.badge}">${esc(cond.label)}</span>
                        </td>

                        <td>
                            <span class="badge ${sta.badge}">${esc(sta.label)}</span>
                        </td>

                        <td>
                            ${
                                g.key
                                    ? `
                                        <span class="garantia ${g.key}">
                                            <i class="fas fa-circle" aria-hidden="true"></i>
                                            ${esc(g.label)}
                                        </span>
                                        <div class="text-xs">${fmtFecha(e.garantia_hasta)}</div>
                                    `
                                    : '<span class="text-muted">Sin garantía</span>'
                            }
                        </td>

                        <td class="td-actions">
                            <button
                                type="button"
                                class="icon-btn"
                                data-ver="${e.id}"
                                aria-label="Ver detalle de ${esc(e.codigo)}"
                            >
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </td>
                    </tr>
                `;
            })
            .join('');
    }

    function renderStats() {
        const total = items.length;
        const disponibles = items.filter((e) => e.estatus === 'disponible').length;
        const fuera = items.filter(
            (e) => e.estatus === 'en_mantenimiento' || e.estatus === 'en_garantia'
        ).length;
        const porVencer = items.filter(
            (e) => garantiaInfo(e).key === 'por_vencer'
        ).length;

        $('#statTotal').textContent = total;
        $('#statDisponibles').textContent = disponibles;
        $('#statMantenimiento').textContent = fuera;
        $('#statGarantia').textContent = porVencer;
    }

    function render() {
        renderStats();
        renderTabs();
        renderTabla();
    }

    /* ---------------------------------------------------
       Modales: apertura, cierre y foco
    --------------------------------------------------- */

    let modalActivo = null;
    let focoPrevio = null;
    let overflowPrevio = '';

    function activarSeccion(modal, clave, enfocar = false) {
        const tabs = [...modal.querySelectorAll('.modal-tab')];
        const tab = tabs.find((el) => el.dataset.section === clave) || tabs[0];

        if (!tab) return;

        modal.dataset.seccion = tab.dataset.section;

        tabs.forEach((el) => {
            const activo = el === tab;
            el.setAttribute('aria-selected', String(activo));
            el.tabIndex = activo ? 0 : -1;
            document.getElementById(el.getAttribute('aria-controls')).hidden = !activo;
        });

        $('.modal-body', modal).scrollTop = 0;

        if (modal.id === 'altaModal') {
            const indice = tabs.indexOf(tab);
            $('#altaPaso').textContent = `Sección ${indice + 1} de ${tabs.length}`;
            $('#altaAnterior').hidden = indice === 0;
            $('#altaSiguiente').hidden = indice === tabs.length - 1;
            $('#altaGuardar').hidden = indice !== tabs.length - 1;
        }

        if (enfocar) {
            tab.scrollIntoView({ block: 'nearest', inline: 'nearest' });
            tab.focus({ preventScroll: true });
        }
    }

    function abrirModal(el) {
        if (!modalActivo) {
            focoPrevio = document.activeElement;
            overflowPrevio = document.body.style.overflow;
        } else if (modalActivo !== el) {
            modalActivo.hidden = true;
        }

        el.hidden = false;
        modalActivo = el;
        document.body.style.overflow = 'hidden';

        const dlg = $('.modal', el);
        dlg.scrollTop = 0;

        const body = $('.modal-body', el);
        if (body) body.scrollTop = 0;

        dlg.focus();
    }

    function cerrarModal(el, restaurarFoco = true) {
        el.hidden = true;

        if (modalActivo === el) {
            modalActivo = null;
        }

        if (!modalActivo) {
            document.body.style.overflow = overflowPrevio;

            if (restaurarFoco && focoPrevio && document.contains(focoPrevio)) {
                focoPrevio.focus();
            }
        }
    }

    function atraparTab(ev) {
        if (ev.key !== 'Tab' || !modalActivo) return;

        const foco = [
            ...modalActivo.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            ),
        ].filter(
            (n) =>
                !n.disabled &&
                n.tabIndex >= 0 &&
                n.getClientRects().length &&
                !n.closest('[hidden]')
        );

        if (!foco.length) return;

        const primero = foco[0];
        const ultimo = foco[foco.length - 1];

        if (
            ev.shiftKey &&
            (document.activeElement === primero || !foco.includes(document.activeElement))
        ) {
            ev.preventDefault();
            ultimo.focus();
        } else if (
            !ev.shiftKey &&
            (document.activeElement === ultimo || !foco.includes(document.activeElement))
        ) {
            ev.preventDefault();
            primero.focus();
        }
    }

    /* ---------------------------------------------------
       Toast
    --------------------------------------------------- */

    let toastTimer = null;

    function toast(msg) {
        const el = $('#toast');
        el.textContent = msg;
        el.hidden = false;

        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            el.hidden = true;
        }, 3500);
    }

    /* ---------------------------------------------------
       Modal: detalle
    --------------------------------------------------- */

    const fila = (label, valor) => `<div><dt>${esc(label)}</dt><dd>${valor}</dd></div>`;
    const vacio = '<span class="text-muted">—</span>';

    function abrirDetalle(id) {
        const e = porId(id);
        if (!e) return;

        const t = TIPOS[e.tipo] || TIPOS.laptop;
        const est = estadoDe(e);
        const cond = condicionDe(e);
        const sta = estatusDe(e);
        const g = garantiaInfo(e);
        const sub = obtenerSubtipo(e);

        const ic = $('#dIcono');
        ic.className = `tipo-icon lg ${t.color}`;
        ic.innerHTML = `<i class="fas ${t.icon}"></i>`;

        $('#detalleTitulo').textContent = `${e.marca} ${e.modelo}`;
        $('#dCodigo').textContent = e.codigo;

        const badge = $('#dEstado');
        badge.className = `badge ${est.badge}`;
        badge.textContent = est.label;

        const badgeCondicion = $('#dCondicion');
        if (badgeCondicion) {
            badgeCondicion.className = `badge ${cond.badge}`;
            badgeCondicion.textContent = cond.label;
        }

        const badgeEstatus = $('#dEstatus');
        badgeEstatus.className = `badge ${sta.badge}`;
        badgeEstatus.textContent = sta.label;

        const filasGenerales = [
            fila('Fecha de alta', fmtFecha(e.fecha_alta)),
            fila('Folio del equipo', `<code class="codigo">${esc(e.codigo)}</code>`),
            fila('Categoría de equipo', esc(t.singular)),
            fila('Tipo de equipo', esc(sub) || vacio),
            fila('Estado del equipo', `<span class="badge ${est.badge}">${esc(est.label)}</span>`),
            fila('Condición', `<span class="badge ${cond.badge}">${esc(cond.label)}</span>`),
            fila('Estatus', `<span class="badge ${sta.badge}">${esc(sta.label)}</span>`),
            fila('Marca', esc(e.marca)),
            fila('Modelo', esc(e.modelo)),
        ];

        if (e.serie_teclado || e.serie_mouse) {
            filasGenerales.push(
                fila(
                    'N/S Teclado',
                    e.serie_teclado ? `<code class="codigo">${esc(e.serie_teclado)}</code>` : vacio
                ),
                fila(
                    'N/S Mouse',
                    e.serie_mouse ? `<code class="codigo">${esc(e.serie_mouse)}</code>` : vacio
                )
            );
            if (e.serie_receptor) {
                filasGenerales.push(
                    fila('N/S Receptor', `<code class="codigo">${esc(e.serie_receptor)}</code>`)
                );
            }
        } else {
            filasGenerales.push(
                fila(
                    'Número de serie',
                    e.serie ? `<code class="codigo">${esc(e.serie)}</code>` : vacio
                )
            );
        }

        filasGenerales.push(
            fila(
                'Garantía hasta',
                e.garantia_hasta
                    ? `
                        ${fmtFecha(e.garantia_hasta)}
                        <span class="garantia ${g.key}">
                            <i class="fas fa-circle" aria-hidden="true"></i>
                            ${esc(g.label)}
                        </span>
                    `
                    : 'Sin garantía'
            ),
            fila('Área a la que pertenece', esc(e.area) || vacio)
        );

        $('#dGeneral').innerHTML = filasGenerales.join('');

        // Características técnicas
        $('#dSpecs').innerHTML = t.specs
            .map((f) => fila(f.l.replace(/\s*\*$/, ''), esc(e.specs?.[f.k]) || vacio))
            .join('');

        // Accesorios y componentes incluidos con su marca, modelo y serie
        $('#dAccesorios').innerHTML = e.extras?.length
            ? e.extras
                  .map((raw) => {
                      const c = normalizarComponente(raw);
                      const marcaModelo = [c.marca, c.modelo].filter(Boolean).join(' · ');
                      const serieTxt = resumenSerieComponente(c);

                      return `
                          <div class="comp-detail-item">
                              <span class="tipo-icon sm orange" aria-hidden="true">
                                  <i class="fas ${iconoComponente(c.tipo)}"></i>
                              </span>
                              <div class="comp-detail-body">
                                  <strong>${esc(c.tipo)}</strong>
                                  <span>${marcaModelo ? esc(marcaModelo) : 'Sin marca ni modelo especificado'}</span>
                              </div>
                              <div class="comp-detail-sn">
                                  ${
                                      serieTxt
                                          ? `<code class="codigo">S/N: ${esc(serieTxt)}</code>`
                                          : '<span class="text-muted">Sin S/N</span>'
                                  }
                              </div>
                          </div>
                      `;
                  })
                  .join('')
            : '<span class="text-muted">Sin accesorios ni componentes incluidos.</span>';

        $('#dObs').textContent = e.observaciones || 'Sin observaciones.';

        activarSeccion($('#detalleModal'), 'general');
        abrirModal($('#detalleModal'));
    }

    /* ---------------------------------------------------
       Modal: alta (Datos generales y captura de componentes)
    --------------------------------------------------- */

    function campoSpecHTML(f) {
        const name = `spec_${f.k}`;

        if (f.opts) {
            return `
                <label class="field">
                    <span>${esc(f.l)}</span>
                    <select name="${name}" ${f.req ? 'required' : ''}>
                        <option value="">Seleccionar</option>
                        ${f.opts.map((o) => `<option value="${esc(o)}">${esc(o)}</option>`).join('')}
                    </select>
                </label>
            `;
        }

        return `
            <label class="field">
                <span>${esc(f.l)}</span>
                <input
                    type="text"
                    name="${name}"
                    placeholder="${esc(f.ph || '')}"
                    ${f.req ? 'required' : ''}
                >
            </label>
        `;
    }

    let altaTipoActual = null;
    let altaSpecsBorrador = {};
    let altaExtrasBorrador = {};
    let extrasAgregados = [];

    function esModoKitSerie(tipo, subtipo) {
        return tipo === 'accesorio' && SUBTIPOS_KIT_SERIE.has(subtipo);
    }

    function actualizarModoSerieAlta() {
        const tipo = $('#altaTipo').value;
        const subtipo = $('#altaSubtipo').value;
        const modoKit = esModoKitSerie(tipo, subtipo);

        const simpleWrap = $('#altaSerieSimpleWrap');
        const kitWrap = $('#altaSerieKitWrap');
        const inputSerie = $('#altaSerie');
        const inputKb = $('#altaSerieTeclado');
        const inputMs = $('#altaSerieMouse');

        simpleWrap.hidden = modoKit;
        kitWrap.hidden = !modoKit;

        if (modoKit) {
            inputSerie.required = false;
            inputSerie.setCustomValidity('');

            const esComboExacto = subtipo === 'Combo teclado y mouse';
            inputKb.required = esComboExacto;
            inputMs.required = esComboExacto;

            $('#altaSerieTecladoLabel').textContent = esComboExacto
                ? 'N/S Teclado *'
                : 'N/S Pieza 1 *';
            $('#altaSerieMouseLabel').textContent = esComboExacto
                ? 'N/S Mouse *'
                : 'N/S Pieza 2';
        } else {
            inputKb.required = false;
            inputMs.required = false;
            inputKb.setCustomValidity('');
            inputMs.setCustomValidity('');

            const opcional = tipo === 'accesorio' && SUBTIPOS_SERIE_OPCIONAL.has(subtipo);
            inputSerie.required = !opcional;
            inputSerie.placeholder = opcional ? 'Opcional para este tipo' : 'Ej. PF6GFE6R';
            $('#altaSerieLabel').textContent = `Número de serie${opcional ? ' (opcional)' : ' *'}`;
        }
    }

    /* --- Constructor de accesorios y componentes incluidos --- */

    function cerrarCompBuilder() {
        $('#compBuilder').hidden = true;
        $('#compBuilderError').hidden = true;
        $('#compBuilderError').textContent = '';
        $('#altaExtras').value = '';
    }

    function abrirCompBuilder(nombreComp) {
        const cfg = CATALOGO_COMPONENTES[nombreComp] || {
            modo: 'serie',
            reqMarca: false,
            reqSerie: false,
            icon: 'fa-box',
            phMarca: 'Marca',
            phModelo: 'Modelo o descripción',
            phSerie: 'Número de serie',
        };

        $('#compBuilder').hidden = false;
        $('#compBuilderError').hidden = true;
        $('#compBuilderError').textContent = '';

        $('#compBuilderTitle').innerHTML = `
            <i class="fas ${cfg.icon}" aria-hidden="true"></i>
            Configurar: ${esc(nombreComp)}
        `;

        $('#compBuilderHint').textContent =
            cfg.modo === 'combo'
                ? 'Registra la marca y los números de serie del teclado y mouse'
                : cfg.modo === 'serie'
                  ? cfg.reqSerie
                      ? 'Marca y número de serie obligatorios'
                      : 'Marca y número de serie opcional si no cuenta con etiqueta'
                  : 'Pieza complementaria sin número de serie obligatorio';

        $('#compMarcaLabel').textContent = cfg.reqMarca ? 'Marca *' : 'Marca';
        $('#compMarca').placeholder = `Ej. ${cfg.phMarca || 'Logitech'}`;
        $('#compMarca').value = '';

        $('#compModeloLabel').textContent = 'Modelo o detalle';
        $('#compModelo').placeholder = `Ej. ${cfg.phModelo || 'Modelo'}`;
        $('#compModelo').value = '';

        const simpleWrap = $('#compSerieSimpleWrap');
        const comboWrap = $('#compSerieComboWrap');

        if (cfg.modo === 'combo') {
            simpleWrap.hidden = true;
            comboWrap.hidden = false;
            $('#compSerieTeclado').value = '';
            $('#compSerieMouse').value = '';
            $('#compSerieReceptor').value = '';
        } else if (cfg.modo === 'serie') {
            simpleWrap.hidden = false;
            comboWrap.hidden = true;
            $('#compSerieLabel').textContent = cfg.reqSerie
                ? 'Número de serie *'
                : 'Número de serie';
            $('#compSerie').placeholder = cfg.phSerie || 'Ej. SN-8839201';
            $('#compSerie').value = '';
        } else {
            simpleWrap.hidden = true;
            comboWrap.hidden = true;
            $('#compSerie').value = '';
        }

        $('#compMarca').focus();
    }

    function mostrarErrorBuilder(msg, inputFocus) {
        const err = $('#compBuilderError');
        err.textContent = msg;
        err.hidden = false;
        if (inputFocus) inputFocus.focus();
    }

    function confirmarCompBuilder() {
        const tipoComp = $('#altaExtras').value;
        if (!tipoComp) return;

        const cfg = CATALOGO_COMPONENTES[tipoComp] || { modo: 'serie' };
        const marca = $('#compMarca').value.trim();
        const modelo = $('#compModelo').value.trim();

        let serie = '';
        let serie_teclado = '';
        let serie_mouse = '';
        let serie_receptor = '';

        if (cfg.reqMarca && !marca) {
            mostrarErrorBuilder(`Indica la marca para "${tipoComp}".`, $('#compMarca'));
            return;
        }

        const seriesOcupadas = new Set([
            ...items.flatMap((e) => todasLasSeriesDe(e).map(norm)),
            ...extrasAgregados
                .flatMap((c) => [c.serie, c.serie_teclado, c.serie_mouse, c.serie_receptor])
                .filter(Boolean)
                .map(norm),
        ]);

        const serieEquipoActual = $('#altaSerie').value.trim();
        if (serieEquipoActual) seriesOcupadas.add(norm(serieEquipoActual));

        if (cfg.modo === 'combo') {
            serie_teclado = $('#compSerieTeclado').value.trim();
            serie_mouse = $('#compSerieMouse').value.trim();
            serie_receptor = $('#compSerieReceptor').value.trim();

            if (!serie_teclado) {
                mostrarErrorBuilder('Ingresa el número de serie del teclado.', $('#compSerieTeclado'));
                return;
            }
            if (!serie_mouse) {
                mostrarErrorBuilder('Ingresa el número de serie del mouse.', $('#compSerieMouse'));
                return;
            }
            if (norm(serie_teclado) === norm(serie_mouse)) {
                mostrarErrorBuilder('El N/S del mouse no puede ser igual al del teclado.', $('#compSerieMouse'));
                return;
            }
            if (seriesOcupadas.has(norm(serie_teclado))) {
                mostrarErrorBuilder('Ese N/S de teclado ya está registrado.', $('#compSerieTeclado'));
                return;
            }
            if (seriesOcupadas.has(norm(serie_mouse))) {
                mostrarErrorBuilder('Ese N/S de mouse ya está registrado.', $('#compSerieMouse'));
                return;
            }

            const partes = [`KB: ${serie_teclado}`, `MS: ${serie_mouse}`];
            if (serie_receptor) partes.push(`RCV: ${serie_receptor}`);
            serie = partes.join(' · ');
        } else if (cfg.modo === 'serie') {
            serie = $('#compSerie').value.trim();

            if (cfg.reqSerie && !serie) {
                mostrarErrorBuilder(`Ingresa el número de serie para "${tipoComp}".`, $('#compSerie'));
                return;
            }
            if (serie && seriesOcupadas.has(norm(serie))) {
                mostrarErrorBuilder('Ese número de serie ya existe en el inventario o en este equipo.', $('#compSerie'));
                return;
            }
        }

        extrasAgregados.push({
            tipo: tipoComp,
            marca,
            modelo,
            serie,
            serie_teclado,
            serie_mouse,
            serie_receptor,
        });

        cerrarCompBuilder();
        renderComplementosAlta();

        $('#altaExtrasEstado').textContent = `${tipoComp} agregado. ${extrasAgregados.length} componentes incluidos.`;
        $('#altaExtras').focus();
    }

    function renderComplementosAlta() {
        const tipo = $('#altaTipo').value;
        const opciones = COMPLEMENTOS_POR_TIPO[tipo] || [];
        const selector = $('#altaExtras');

        selector.innerHTML =
            '<option value="">Seleccionar accesorio o componente para agregar…</option>' +
            opciones.map((nombre) => `<option value="${esc(nombre)}">${esc(nombre)}</option>`).join('');

        $('#altaExtrasSeleccionados').innerHTML = extrasAgregados
            .map((c, idx) => {
                const marcaModelo = [c.marca, c.modelo].filter(Boolean).join(' · ');
                const serieTxt = resumenSerieComponente(c);

                return `
                    <div class="comp-added-card" role="listitem">
                        <span class="tipo-icon sm orange" aria-hidden="true">
                            <i class="fas ${iconoComponente(c.tipo)}"></i>
                        </span>

                        <div class="comp-added-info">
                            <div class="comp-added-top">
                                <strong>${esc(c.tipo)}</strong>
                                ${marcaModelo ? `<span>· ${esc(marcaModelo)}</span>` : ''}
                            </div>
                            <div class="comp-added-bottom">
                                ${
                                    serieTxt
                                        ? `<code class="codigo">S/N: ${esc(serieTxt)}</code>`
                                        : '<span class="text-xs">Sin número de serie</span>'
                                }
                            </div>
                        </div>

                        <button
                            type="button"
                            class="icon-btn"
                            data-quitar-extra="${idx}"
                            aria-label="Quitar ${esc(c.tipo)}"
                        >
                            <i class="fas fa-trash-can" aria-hidden="true"></i>
                        </button>
                    </div>
                `;
            })
            .join('');

        $('#altaExtrasVacio').hidden = extrasAgregados.length > 0;
    }

    function renderCamposAlta() {
        const form = $('#altaForm');

        if (altaTipoActual && TIPOS[altaTipoActual]) {
            altaSpecsBorrador[altaTipoActual] = Object.fromEntries(
                TIPOS[altaTipoActual].specs.map((f) => [
                    f.k,
                    form.elements.namedItem(`spec_${f.k}`)?.value || '',
                ])
            );
            altaExtrasBorrador[altaTipoActual] = [...extrasAgregados];
        }

        const tipo = $('#altaTipo').value;
        const t = TIPOS[tipo];
        if (!t) return;

        altaTipoActual = tipo;

        const headerIcon = $('#altaHeaderIcon');
        headerIcon.className = `tipo-icon lg ${t.color}`;
        headerIcon.innerHTML = `<i class="fas ${t.icon}"></i>`;

        $('#altaFolio').value = siguienteCodigoPreview(tipo);
        $('#altaTipoLabel').textContent = `· ${t.singular}`;

        $('#altaMarca').placeholder = `Ej. ${t.placeholderMarca || 'Lenovo'}`;
        $('#altaModelo').placeholder = `Ej. ${t.placeholderModelo || 'Modelo'}`;

        const subSelect = $('#altaSubtipo');

        subSelect.required = true;
        subSelect.innerHTML =
            '<option value="">Seleccionar tipo de equipo…</option>' +
            t.subtipos.map((s) => `<option value="${esc(s)}">${esc(s)}</option>`).join('');

        actualizarModoSerieAlta();

        $('#altaSpecs').innerHTML = t.specs.map(campoSpecHTML).join('');

        t.specs.forEach((f) => {
            const input = form.elements.namedItem(`spec_${f.k}`);
            if (input) {
                input.value = altaSpecsBorrador[tipo]?.[f.k] || '';
            }
        });

        extrasAgregados = [...(altaExtrasBorrador[tipo] || [])];
        cerrarCompBuilder();
        renderComplementosAlta();
    }

    function mostrarErrorAlta(campo, mensaje = '') {
        if (mensaje) {
            campo.setCustomValidity(mensaje);
        }

        const panel = campo.closest('[role="tabpanel"]');
        const tab = panel && root.querySelector(`[aria-controls="${panel.id}"]`);

        if (tab) {
            activarSeccion($('#altaModal'), tab.dataset.section);
        }

        campo.focus();
        campo.reportValidity();

        return false;
    }

    function validarAlta(contenedor = $('#altaForm')) {
        const campos = [...contenedor.querySelectorAll('input, select, textarea')].filter(
            (el) => el.willValidate && !el.closest('[hidden]') && !el.closest('#compBuilder')
        );

        campos.forEach((campo) => {
            campo.setCustomValidity('');

            if (campo.required && campo.type === 'text' && !campo.value.trim()) {
                campo.setCustomValidity('Completa este campo.');
            }
        });

        const invalido = campos.find((campo) => !campo.checkValidity());
        if (invalido) return mostrarErrorAlta(invalido);

        if (
            (contenedor === $('#altaForm') || contenedor.id === 'altaAccesorios') &&
            !$('#compBuilder').hidden
        ) {
            activarSeccion($('#altaModal'), 'accesorios');
            mostrarErrorBuilder(
                'Haz clic en "Agregar al equipo" para guardar este componente o en "Cancelar" antes de continuar.',
                $('#btnConfirmarComp')
            );
            return false;
        }

        const form = $('#altaForm');
        if (contenedor === form || contenedor.id === 'altaGeneral') {
            const tipo = $('#altaTipo').value;
            const subtipo = $('#altaSubtipo').value;
            const seriesExistentes = new Set(
                items.flatMap((e) => todasLasSeriesDe(e).map(norm))
            );

            if (esModoKitSerie(tipo, subtipo)) {
                const kb = $('#altaSerieTeclado').value.trim();
                const ms = $('#altaSerieMouse').value.trim();
                const rcv = $('#altaSerieReceptor').value.trim();

                if (!kb && !ms) {
                    return mostrarErrorAlta(
                        $('#altaSerieTeclado'),
                        'Registra al menos el número de serie del teclado o del mouse.'
                    );
                }

                if (kb && ms && norm(kb) === norm(ms)) {
                    return mostrarErrorAlta(
                        $('#altaSerieMouse'),
                        'El número de serie del mouse no puede ser idéntico al del teclado.'
                    );
                }

                if (kb && seriesExistentes.has(norm(kb))) {
                    return mostrarErrorAlta(
                        $('#altaSerieTeclado'),
                        'Ya existe un registro en el inventario con este N/S de teclado.'
                    );
                }

                if (ms && seriesExistentes.has(norm(ms))) {
                    return mostrarErrorAlta(
                        $('#altaSerieMouse'),
                        'Ya existe un registro en el inventario con este N/S de mouse.'
                    );
                }

                if (rcv && seriesExistentes.has(norm(rcv))) {
                    return mostrarErrorAlta(
                        $('#altaSerieReceptor'),
                        'Ya existe un registro con ese N/S de receptor.'
                    );
                }
            } else {
                const serie = $('#altaSerie').value.trim();
                if (serie && seriesExistentes.has(norm(serie))) {
                    return mostrarErrorAlta(
                        $('#altaSerie'),
                        'Ya existe un equipo o accesorio con ese número de serie.'
                    );
                }
            }

            const alta = form.elements.fecha_alta.value;
            const garantia = form.elements.garantia_hasta.value;

            if (alta && garantia && garantia < alta) {
                return mostrarErrorAlta(
                    form.elements.garantia_hasta,
                    'La fecha de garantía no puede ser anterior a la fecha de alta.'
                );
            }
        }

        return true;
    }

    function moverSeccionAlta(paso) {
        const modal = $('#altaModal');
        const tabs = [...modal.querySelectorAll('.modal-tab')];
        const indice = tabs.findIndex(
            (tab) => tab.getAttribute('aria-selected') === 'true'
        );
        const panel = document.getElementById(tabs[indice].getAttribute('aria-controls'));

        if (paso > 0 && !validarAlta(panel)) return;

        const siguiente = tabs[indice + paso];
        if (siguiente) {
            activarSeccion(modal, siguiente.dataset.section, true);
        }
    }

    function abrirAlta() {
        const form = $('#altaForm');
        form.reset();

        altaTipoActual = null;
        altaSpecsBorrador = {};
        altaExtrasBorrador = {};
        extrasAgregados = [];

        form.querySelectorAll('input, select, textarea').forEach((el) => {
            el.setCustomValidity('');
        });

        $('#altaExtrasEstado').textContent = '';

        $('#altaTipo').innerHTML = Object.entries(TIPOS)
            .map(([k, t]) => `<option value="${k}">${esc(t.singular)}</option>`)
            .join('');

        if (state.tipo !== 'todos') {
            $('#altaTipo').value = state.tipo;
        }

        // 1. Estado del equipo
        $('#altaEstado').innerHTML = Object.entries(ESTADOS)
            .map(([k, v]) => `<option value="${k}">${esc(v.label)}</option>`)
            .join('');
        form.elements.estado.value = 'nuevo';

        // 2. Condición
        const selectCondicion = $('#altaCondicion');
        if (selectCondicion) {
            selectCondicion.innerHTML = Object.entries(CONDICIONES)
                .map(([k, v]) => `<option value="${k}">${esc(v.label)}</option>`)
                .join('');
            selectCondicion.value = 'excelente';
        }

        // 3. Estatus
        $('#altaEstatus').innerHTML = Object.entries(ESTATUS)
            .map(([k, v]) => `<option value="${k}">${esc(v.label)}</option>`)
            .join('');
        $('#altaEstatus').value = ESTATUS_DEFAULT;

        form.elements.fecha_alta.value = hoyISO();

        renderCamposAlta();
        activarSeccion($('#altaModal'), 'general');
        abrirModal($('#altaModal'));

        form.elements.fecha_alta.focus();
    }

    async function guardar(ev) {
        ev.preventDefault();

        if (guardando) return;

        const form = ev.currentTarget;

        if ($('#altaModal').dataset.seccion !== 'observaciones') {
            moverSeccionAlta(1);
            return;
        }

        if (!validarAlta(form)) return;

        const fd = new FormData(form);
        const get = (k) => String(fd.get(k) ?? '').trim();

        const tipo = get('tipo');
        const t = TIPOS[tipo];
        const subtipo = get('subtipo');

        let serie = get('serie');
        let serie_teclado = '';
        let serie_mouse = '';
        let serie_receptor = '';

        if (esModoKitSerie(tipo, subtipo)) {
            serie_teclado = get('serie_teclado');
            serie_mouse = get('serie_mouse');
            serie_receptor = get('serie_receptor');

            const partes = [];
            if (serie_teclado) partes.push(`KB: ${serie_teclado}`);
            if (serie_mouse) partes.push(`MS: ${serie_mouse}`);
            if (serie_receptor) partes.push(`RCV: ${serie_receptor}`);
            serie = partes.join(' · ');
        }

        const specs = {};
        t.specs.forEach((f) => {
            specs[f.k] = get(`spec_${f.k}`);
        });

        const payload = {
            tipo,
            subtipo,
            marca: get('marca'),
            modelo: get('modelo'),
            serie: serie || null,
            serie_teclado: serie_teclado || null,
            serie_mouse: serie_mouse || null,
            serie_receptor: serie_receptor || null,
            estado: ESTADOS[get('estado')] ? get('estado') : 'nuevo',
            condicion: CONDICIONES[get('condicion')] ? get('condicion') : 'excelente',
            estatus: ESTATUS[get('estatus')] ? get('estatus') : ESTATUS_DEFAULT,
            area_id: get('area_id') ? Number(get('area_id')) : null,
            fecha_alta: get('fecha_alta') || hoyISO(),
            garantia_hasta: get('garantia_hasta') || null,
            specs,
            extras: extrasAgregados,
            observaciones: get('observaciones') || null,
        };

        const btnGuardar = $('#altaGuardar');
        const textoOriginal = btnGuardar.innerHTML;

        try {
            guardando = true;
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Guardando...';

            const response = await fetch(CONFIG.storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CONFIG.csrfToken || get('_token'),
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (!response.ok) {
                const msgError =
                    data?.message ||
                    Object.values(data?.errors || {})[0]?.[0] ||
                    'No se pudo guardar el equipo. Verifica los datos.';
                toast(msgError);
                return;
            }

            if (data.equipment) {
                items.unshift({
                    ...data.equipment,
                    extras: (data.equipment.extras || []).map(normalizarComponente).filter(Boolean),
                });
            }

            cerrarModal($('#altaModal'));

            state.tipo = tipo;
            state.pagina = 1;

            render();
            toast(data.message || `Equipo ${data.equipment?.codigo} guardado.`);
        } catch (err) {
            toast('Error de conexión al intentar guardar el equipo.');
        } finally {
            guardando = false;
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = textoOriginal;
        }
    }

    /* ---------------------------------------------------
       Eventos
    --------------------------------------------------- */

    function init() {
        cargarDatosIniciales();

        // Filtros superiores: Estado, Condición y Estatus
        $('#estadoFilter').innerHTML = opcionesHTML(ESTADOS, 'Todos los estados');
        if ($('#condicionFilter')) {
            $('#condicionFilter').innerHTML = opcionesHTML(CONDICIONES, 'Cualquier condición');
        }
        $('#estatusFilter').innerHTML = opcionesHTML(ESTATUS, 'Todos los estatus');

        render();

        // Selector de categorías
        const selector = $('#typeTabs');

        selector.addEventListener('click', (ev) => {
            const opt = ev.target.closest('.type-option');
            if (opt) {
                elegirCategoria(opt.dataset.tipo);
                return;
            }
            if (ev.target.closest('#typeSelectBtn')) {
                alternarMenu(!menuAbierto);
            }
        });

        selector.addEventListener('mousemove', (ev) => {
            const opt = ev.target.closest('.type-option');
            if (!opt) return;

            const idx = [...root.querySelectorAll('.type-option')].indexOf(opt);
            if (idx !== menuIdx) moverFocoMenu(idx);
        });

        selector.addEventListener('keydown', (ev) => {
            if (!ev.target.closest('#typeSelectBtn')) return;
            const ops = opcionesCategoria();

            if (!menuAbierto) {
                if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(ev.key)) {
                    ev.preventDefault();
                    alternarMenu(true);
                }
                return;
            }

            switch (ev.key) {
                case 'ArrowDown':
                    ev.preventDefault();
                    moverFocoMenu(menuIdx + 1);
                    break;
                case 'ArrowUp':
                    ev.preventDefault();
                    moverFocoMenu(menuIdx - 1);
                    break;
                case 'Home':
                    ev.preventDefault();
                    moverFocoMenu(0);
                    break;
                case 'End':
                    ev.preventDefault();
                    moverFocoMenu(ops.length - 1);
                    break;
                case 'Enter':
                case ' ':
                    ev.preventDefault();
                    elegirCategoria(ops[menuIdx].key);
                    break;
                case 'Escape':
                    ev.preventDefault();
                    alternarMenu(false);
                    break;
                case 'Tab':
                    menuAbierto = false;
                    renderTabs();
                    break;
            }
        });

        document.addEventListener('click', (ev) => {
            if (menuAbierto && !ev.target.closest('.type-select')) {
                menuAbierto = false;
                renderTabs();
            }
        });

        // Filtros de tabla
        $('#searchInput').addEventListener('input', (ev) => {
            state.q = ev.target.value;
            state.pagina = 1;
            renderTabla();
        });

        $('#estadoFilter').addEventListener('change', (ev) => {
            state.estado = ev.target.value;
            state.pagina = 1;
            renderTabla();
        });

        if ($('#condicionFilter')) {
            $('#condicionFilter').addEventListener('change', (ev) => {
                state.condicion = ev.target.value;
                state.pagina = 1;
                renderTabla();
            });
        }

        $('#estatusFilter').addEventListener('change', (ev) => {
            state.estatus = ev.target.value;
            state.pagina = 1;
            renderTabla();
        });

        $('#garantiaFilter').addEventListener('change', (ev) => {
            state.garantia = ev.target.value;
            state.pagina = 1;
            renderTabla();
        });

        $('#pageSize').addEventListener('change', (ev) => {
            const cantidad = Number(ev.target.value);
            state.porPagina = [10, 25, 50].includes(cantidad) ? cantidad : 10;
            state.pagina = 1;
            renderTabla();
        });

        $('#paginacion').addEventListener('click', (ev) => {
            const boton = ev.target.closest('[data-pagina]');
            if (!boton || boton.disabled) return;

            const label = boton.getAttribute('aria-label');
            state.pagina = Number(boton.dataset.pagina);
            renderTabla();

            const foco =
                [...$('#paginacion').querySelectorAll('button')].find(
                    (el) => el.getAttribute('aria-label') === label && !el.disabled
                ) || $('#paginacion [aria-current="page"]');

            foco?.focus({ preventScroll: true });
        });

        // Tabla: abrir detalle
        const body = $('#equiposBody');

        body.addEventListener('click', (ev) => {
            const row = ev.target.closest('.equipo-row');
            if (row) abrirDetalle(row.dataset.id);
        });

        body.addEventListener('keydown', (ev) => {
            if (ev.key !== 'Enter' && ev.key !== ' ') return;
            const row = ev.target.closest('.equipo-row');
            if (!row || ev.target !== row) return;

            ev.preventDefault();
            abrirDetalle(row.dataset.id);
        });

        // Alta: controles principales
        $('#btnAlta').addEventListener('click', abrirAlta);
        $('#altaTipo').addEventListener('change', renderCamposAlta);
        $('#altaSubtipo').addEventListener('change', actualizarModoSerieAlta);

        // Si el usuario selecciona estado "Nuevo", colocar condición "Excelente"
        $('#altaEstado').addEventListener('change', (ev) => {
            if (ev.target.value === 'nuevo' && $('#altaCondicion')) {
                $('#altaCondicion').value = 'excelente';
            }
        });

        // Selector de accesorio o componente
        $('#altaExtras').addEventListener('change', (ev) => {
            const nombre = ev.target.value;
            if (!nombre) {
                cerrarCompBuilder();
                return;
            }
            abrirCompBuilder(nombre);
        });

        $('#btnConfirmarComp').addEventListener('click', confirmarCompBuilder);
        $('#btnCancelarComp').addEventListener('click', () => {
            cerrarCompBuilder();
            $('#altaExtras').focus();
        });

        $('#compBuilder').addEventListener('keydown', (ev) => {
            if (ev.key === 'Enter' && ev.target.tagName === 'INPUT') {
                ev.preventDefault();
                confirmarCompBuilder();
            }
        });

        // Quitar accesorio o componente de la lista
        $('#altaExtrasSeleccionados').addEventListener('click', (ev) => {
            const boton = ev.target.closest('[data-quitar-extra]');
            if (!boton) return;

            const idx = Number(boton.dataset.quitarExtra);
            const eliminado = extrasAgregados[idx];
            extrasAgregados.splice(idx, 1);

            renderComplementosAlta();

            if (eliminado) {
                $('#altaExtrasEstado').textContent = `${eliminado.tipo} eliminado. ${extrasAgregados.length} componentes incluidos.`;
            }
            $('#altaExtras').focus();
        });

        // Navegación entre pasos del modal de alta
        $('#altaAnterior').addEventListener('click', () => moverSeccionAlta(-1));
        $('#altaSiguiente').addEventListener('click', () => moverSeccionAlta(1));

        $('#altaForm').addEventListener('input', (ev) => {
            if (typeof ev.target.setCustomValidity === 'function') {
                ev.target.setCustomValidity('');
            }
            if (ev.target.closest('#compBuilder')) {
                $('#compBuilderError').hidden = true;
            }
        });

        $('#altaForm').addEventListener('submit', guardar);

        // Pestañas de los modales
        root.addEventListener('click', (ev) => {
            const tab = ev.target.closest('.modal-tab');
            if (!tab) return;

            const modal = tab.closest('.modal-backdrop');
            if (modal.id === 'altaModal') {
                const tabs = [...modal.querySelectorAll('.modal-tab')];
                const actualIdx = tabs.findIndex(
                    (el) => el.getAttribute('aria-selected') === 'true'
                );
                const destinoIdx = tabs.indexOf(tab);

                if (destinoIdx > actualIdx) {
                    const panelActual = document.getElementById(
                        tabs[actualIdx].getAttribute('aria-controls')
                    );
                    if (!validarAlta(panelActual)) return;
                }
            }

            activarSeccion(modal, tab.dataset.section);
        });

        // Cerrar modales
        root.addEventListener('click', (ev) => {
            const backdrop = ev.target.closest('.modal-backdrop');
            if (!backdrop) return;

            if (ev.target.closest('[data-close]') || ev.target === backdrop) {
                cerrarModal(backdrop);
            }
        });

        document.addEventListener('keydown', (ev) => {
            if (ev.key === 'Escape' && modalActivo) {
                cerrarModal(modalActivo);
            }
            atraparTab(ev);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
