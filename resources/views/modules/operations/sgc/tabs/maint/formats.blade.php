@php
    /*
    |--------------------------------------------------------------------------
    | Tipos de equipo ECP
    |--------------------------------------------------------------------------
    */
    $ecpTypes = [
        'ECP completo',
        'BOP / Preventor',
        'Lubricador',
        'Stuffing Box',
        'Grease Injection Head',
        'Packoff',
        'Tool Catcher',
        'Tool Trap',
        'Manifold / válvulas',
        'Mangueras / líneas de presión',
        'Crossovers / adaptadores',
        'Bomba de prueba',
    ];

    /*
    |--------------------------------------------------------------------------
    | Formatos ECP
    |--------------------------------------------------------------------------
    | El adjunto contiene el checklist completo de FM-ECP-001.
    | Los demás permiten capturar sus actividades en blanco.
    */
    $ecpFormats = [
        'FM-ECP-001' => [
            'title' => 'Recepción e inspección inicial de ECP',
            'checklist' => [
                'Equipo recibido físicamente e identificado',
                'Número económico / serial confirmado',
                'Tipo de ECP confirmado contra solicitud',
                'Roscas inspeccionadas',
                'Conexiones de presión inspeccionadas',
                'Sellos / empaques visibles revisados',
                'Válvulas operan libremente',
                'Manómetros presentes y legibles si aplica',
                'Mangueras sin daño visible',
                'Condición inicial documentada con observaciones',
            ],
        ],

        'FM-ECP-002' => [
            'title' => 'Mantenimiento preventivo de ECP / PCE',
            'checklist' => [],
        ],

        'FM-ECP-003' => [
            'title' => 'Prueba de presión de ECP',
            'checklist' => [],
        ],

        'FM-ECP-004' => [
            'title' => 'Liberación de ECP a operación',
            'checklist' => [],
        ],

        'FM-ECP-005' => [
            'title' => 'Mantenimiento correctivo de ECP',
            'checklist' => [],
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | Constructor de formatos con actividades editables
    |--------------------------------------------------------------------------
    */
    $buildMaintenanceFormats = static function (array $titles): array {
        $formats = [];

        foreach ($titles as $code => $title) {
            $formats[$code] = [
                'title' => $title,
                'checklist' => [],
            ];
        }

        return $formats;
    };

    /*
    |--------------------------------------------------------------------------
    | Formatos de Herramientas
    |--------------------------------------------------------------------------
    */
    $toolFormats = $buildMaintenanceFormats([
        'FM-LAB-001' => 'Recepción de herramienta en laboratorio',
        'FM-LAB-002' => 'Diagnóstico y mantenimiento correctivo',
        'FM-LAB-003' => 'Mantenimiento preventivo PM1 / PM2',
        'FM-LAB-004' => 'Prueba electrónica / funcional',
        'FM-LAB-005' => 'Calibración / verificación metrológica',
        'FM-LAB-006' => 'Liberación de herramienta a campo',
        'FM-LAB-007' => 'Control de refacciones y componentes',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Formatos base de Unidades
    |--------------------------------------------------------------------------
    | Identificadores internos, pendientes de sus códigos oficiales.
    */
    $unitFormats = $buildMaintenanceFormats([
        'UNIDAD_GENERAL' => 'Registro general de mantenimiento de unidad',
        'UNIDAD_PREVENTIVO' => 'Mantenimiento preventivo de unidad',
        'UNIDAD_CORRECTIVO' => 'Mantenimiento correctivo de unidad',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Catálogo completo de categorías
    |--------------------------------------------------------------------------
    */
    $categories = [
        'HERRAMIENTA' => [
            'label' => 'Herramientas',

            'types' => [
                'Herramienta de registro',
                'Centralizador',
                'CCL',
                'Sensor',
                'Telemetría',
                'Generador',
                'OVP',
                'EMA',
                'Magneto',
                'Anclas',
                'Tijera',
                'Otra herramienta',
            ],

            'formats' => $toolFormats,
        ],

        'ECP' => [
            'label' => 'ECP / PCE',
            'types' => $ecpTypes,
            'formats' => $ecpFormats,
        ],

        'UNIDAD' => [
            'label' => 'Unidades',

            'types' => [
                'Unidad de registro (URE)',
                'Grúa',
                'Plana',
                'Pickup',
                'Cabina',
                'Otra unidad',
            ],

            'formats' => $unitFormats,
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | Campos base del formulario
    |--------------------------------------------------------------------------
    */
    $fields = [
        'folio' => ['Folio interno', 'text'],

        'date' => ['Fecha', 'date'],

        'responsible' => ['Responsable', 'text'],

        'priority' => [
            'Prioridad',
            'select',
            [
                'Normal',
                'Alta',
                'Urgente',
                'Equipo detenido',
            ],
        ],

        'equipment' => ['Equipo / componente', 'text'],

        'asset_code' => ['Código / número económico', 'text'],

        'serial' => ['Serial', 'text'],

        'equipment_type_display' => ['Tipo seleccionado', 'text'],

        'service_order' => ['OS relacionada', 'text'],

        'client' => ['Cliente', 'text'],

        'well' => ['Pozo', 'text'],

        'test_pressure' => ['Presión de prueba', 'text'],

        'test_duration' => ['Tiempo de prueba', 'text'],

        'instrument' => ['Instrumento / manómetro', 'text'],

        'instrument_certificate' => ['Certificado instrumento', 'text'],

        'next_maintenance' => ['Próximo mantenimiento', 'date'],

        'result' => [
            'Resultado',
            'select',
            [
                'Pendiente',
                'Aprobado',
                'Aprobado con observaciones',
                'Rechazado',
                'Requiere correctivo',
                'Fuera de servicio',
            ],
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | Selección inicial y configuración para JavaScript
    |--------------------------------------------------------------------------
    */
    $initialCategory = 'ECP';
    $initialType = 'BOP / Preventor';
    $initialFormat = 'FM-ECP-001';

    $initialCategoryData = $categories[$initialCategory];
    $initialFormatData = $initialCategoryData['formats'][$initialFormat];

    $catalog = [
        'categories' => $categories,
        'today' => now()->toDateString(),
        'owner' => (string) (auth()->id() ?? 'guest'),
    ];
@endphp

<style>
    .maintenance-formats [hidden] {
        display: none !important;
    }

    .maintenance-formats .mf-title {
        margin: 0 0 6px;
        color: #081735;
        font-size: 18px;
        font-weight: 800;
    }

    .maintenance-formats .mf-description,
    .maintenance-formats .mf-help {
        margin: 0 0 12px;
        color: #64748b;
        font-size: 12px;
    }

    .maintenance-formats .mf-toolbar {
        display: grid;
        grid-template-columns:
            minmax(120px, 1fr)
            minmax(160px, 1.5fr)
            minmax(220px, 2fr);
        gap: 10px;
        align-items: end;
    }

    .maintenance-formats .filter-group,
    .maintenance-formats .form-field {
        min-width: 0;
    }

    .maintenance-formats .mf-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .maintenance-formats .mf-print-button {
        border-color: #ffb000;
        background: #ffb000;
        color: #081735;
    }

    .maintenance-formats .mf-feedback {
        margin-top: 10px;
        color: #334155;
        font-size: 12px;
    }

    .maintenance-formats .mf-feedback:empty {
        display: none;
    }

    .maintenance-formats .mf-notice {
        padding: 14px;
        border: 1px solid #f2cf78;
        border-radius: 10px;
        background: #fff9e8;
        color: #6b4a00;
        font-size: 13px;
    }

    .maintenance-formats .mf-card {
        padding: 20px;
        border: 1px solid #d7deea;
        border-radius: 12px;
        background: #fff;
        color: #081735;
    }

    .maintenance-formats .mf-header {
        display: grid;
        grid-template-columns: 1fr 2fr 1.2fr;
        gap: 14px;
        align-items: center;
        padding-bottom: 12px;
        margin-bottom: 18px;
        border-bottom: 2px solid #081735;
    }

    .maintenance-formats .mf-brand {
        font-size: 26px;
        font-weight: 900;
    }

    .maintenance-formats .mf-heading {
        text-align: center;
    }

    .maintenance-formats .mf-heading h2 {
        margin: 0 0 6px;
        font-size: 17px;
        font-weight: 800;
    }

    .maintenance-formats .mf-meta {
        font-size: 11px;
        text-align: right;
        line-height: 1.7;
    }

    .maintenance-formats .mf-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        min-width: 0;
        margin: 0 0 18px;
        padding: 0;
        border: 0;
    }

    .maintenance-formats .mf-wide {
        grid-column: span 2;
    }

    .maintenance-formats .mf-checklist-title {
        margin: 0 0 10px;
        font-size: 14px;
        font-weight: 800;
    }

    .maintenance-formats .mf-table-wrap {
        overflow-x: auto;
    }

    .maintenance-formats .mf-table {
        width: 100%;
        min-width: 650px;
        border-collapse: collapse;
        font-size: 12px;
    }

    .maintenance-formats .mf-table th,
    .maintenance-formats .mf-table td {
        padding: 8px;
        border: 1px solid #cbd5e1;
        text-align: left;
        vertical-align: top;
    }

    .maintenance-formats .mf-table th {
        background: #f8fafc;
        font-size: 11px;
        font-weight: 800;
    }

    .maintenance-formats .mf-table .mf-check {
        width: 42px;
        text-align: center;
    }

    .maintenance-formats .mf-check input {
        width: 16px;
        height: 16px;
        accent-color: #081735;
    }

    .maintenance-formats .mf-cell-input {
        display: block;
        width: 100%;
        min-height: 36px;
        padding: 4px;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        background: #fff;
        color: #081735;
        font: inherit;
        resize: vertical;
    }

    .maintenance-formats .mf-observations {
        margin-top: 16px;
    }

    .maintenance-formats .mf-signatures {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 24px;
        margin-top: 48px;
    }

    .maintenance-formats .mf-signature {
        padding-top: 8px;
        border-top: 1px solid #081735;
        font-size: 12px;
        font-weight: 800;
        text-align: center;
    }

    .maintenance-formats button:disabled,
    .maintenance-formats select:disabled {
        opacity: 0.55;
        cursor: default;
    }

    @media (max-width: 760px) {
        .maintenance-formats .mf-toolbar,
        .maintenance-formats .mf-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .maintenance-formats .mf-toolbar > :last-child {
            grid-column: 1 / -1;
        }

        .maintenance-formats .mf-header {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .maintenance-formats .mf-meta {
            text-align: center;
        }

        .maintenance-formats .mf-card {
            padding: 12px;
        }

        .maintenance-formats .mf-signatures {
            gap: 10px;
        }

        .maintenance-formats .mf-signature {
            font-size: 10px;
        }
    }

    @media print {
        .header-main,
        .footer-main,
        .no-print {
            display: none !important;
        }

        .main-container,
        .tab-body {
            padding: 0 !important;
        }

        .maintenance-formats .mf-card {
            padding: 10px;
            border: 1px solid #000;
            border-radius: 0;
        }

        .maintenance-formats .mf-header {
            grid-template-columns: 1fr 2fr 1.2fr;
        }

        .maintenance-formats .mf-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
        }

        .maintenance-formats .mf-meta {
            text-align: right;
        }

        .maintenance-formats .mf-heading h2 {
            font-size: 13px;
        }

        .maintenance-formats .form-input,
        .maintenance-formats .form-select,
        .maintenance-formats .form-textarea {
            padding: 4px;
            border: 0;
            border-bottom: 1px solid #000;
            border-radius: 0;
            background: #fff;
            font-size: 10px;
        }

        .maintenance-formats .mf-table-wrap {
            overflow: visible;
        }

        .maintenance-formats .mf-table {
            min-width: 0;
            font-size: 10px;
        }

        .maintenance-formats .mf-table th,
        .maintenance-formats .mf-table td {
            padding: 5px;
            border-color: #000;
        }

        .maintenance-formats .mf-table tr,
        .maintenance-formats .mf-signatures {
            break-inside: avoid;
        }
    }
</style>

<div class="maintenance-formats" data-maintenance-formats>

    {{-- El JS lee este catálogo también después de una carga AJAX. --}}
    <script type="application/json" data-mf-config>@json($catalog)</script>

    <noscript>
        <p class="mf-notice">
            Activa JavaScript para utilizar los selectores y los borradores.
        </p>
    </noscript>

    {{-- No se usa filter-form: este formulario es de captura. --}}
    <form id="maintenance-format-form" data-mf-form>

        {{-- ========================================== --}}
        {{-- 1. SELECTORES Y ACCIONES                    --}}
        {{-- ========================================== --}}

        <section class="filter-section no-print">
            <h1 class="mf-title">Formatos vacíos de mantenimiento</h1>

            <p class="mf-description">
                Selecciona categoría, tipo de equipo/unidad y formato.
            </p>

            <div class="mf-toolbar">

                <div class="filter-group">
                    <label for="mf-category" class="filter-label">
                        Categoría
                    </label>

                    <select
                        id="mf-category"
                        name="category"
                        class="filter-input"
                        data-mf-category
                    >
                        @foreach ($categories as $code => $category)
                            <option
                                value="{{ $code }}"
                                @selected($code === $initialCategory)
                            >
                                {{ $category['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="mf-equipment" class="filter-label">
                        Tipo de equipo / unidad
                    </label>

                    <select
                        id="mf-equipment"
                        name="equipment_type"
                        class="filter-input"
                        data-mf-equipment
                    >
                        @foreach ($initialCategoryData['types'] as $type)
                            <option
                                value="{{ $type }}"
                                @selected($type === $initialType)
                            >
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="mf-format" class="filter-label">
                        Formato vacío
                    </label>

                    <select
                        id="mf-format"
                        name="format_code"
                        class="filter-input"
                        data-mf-format
                    >
                        @foreach ($initialCategoryData['formats'] as $code => $format)
                            <option
                                value="{{ $code }}"
                                @selected($code === $initialFormat)
                            >
                                {{ $code }} - {{ $format['title'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="mf-actions">
                <button
                    type="submit"
                    class="btn btn-sm btn-primary"
                    data-mf-save
                >
                    Guardar borrador
                </button>

                <button
                    type="button"
                    class="btn btn-sm mf-print-button"
                    data-mf-print
                >
                    Imprimir / PDF
                </button>

                <button
                    type="button"
                    class="btn btn-sm btn-outline"
                    data-mf-clear
                >
                    Limpiar
                </button>
            </div>

            <p class="mf-feedback" role="status" data-mf-feedback></p>
        </section>

        <p class="mf-notice no-print" data-mf-notice hidden></p>

        {{-- ========================================== --}}
        {{-- 2. DOCUMENTO DEL FORMATO SELECCIONADO       --}}
        {{-- ========================================== --}}

        <section class="mf-card print-card" data-mf-card>

            <header class="mf-header">
                <div class="mf-brand">VINCO</div>

                <div class="mf-heading">
                    <h2 data-mf-title>
                        {{ $initialFormatData['title'] }}
                    </h2>

                    <strong data-mf-code-label>
                        {{ $initialFormat }}
                    </strong>
                </div>

                <div class="mf-meta">
                    <div>
                        Categoría:
                        <strong data-mf-category-label>
                            {{ $initialCategory }}
                        </strong>
                    </div>

                    <div>
                        Tipo:
                        <strong data-mf-type-label>
                            {{ $initialType }}
                        </strong>
                    </div>

                    <div>Rev. 0</div>
                </div>
            </header>

            {{-- DATOS BASE --}}
            <fieldset class="mf-grid" aria-label="Datos del mantenimiento">
                @foreach ($fields as $name => $definition)
                    <div class="form-field {{ $name === 'equipment' ? 'mf-wide' : '' }}">

                        <label for="mf-field-{{ $name }}" class="form-label">
                            {{ $definition[0] }}
                        </label>

                        @if ($definition[1] === 'select')
                            <select
                                id="mf-field-{{ $name }}"
                                name="{{ $name }}"
                                class="form-select"
                                data-mf-field
                            >
                                @foreach ($definition[2] as $option)
                                    <option value="{{ $option }}">
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <input
                                id="mf-field-{{ $name }}"
                                type="{{ $definition[1] }}"
                                name="{{ $name }}"
                                class="form-input"
                                value="{{ $name === 'date' ? $catalog['today'] : ($name === 'equipment_type_display' ? $initialType : '') }}"
                                @readonly($name === 'equipment_type_display')
                                data-mf-field
                            >
                        @endif

                    </div>
                @endforeach
            </fieldset>

            {{-- CHECKLIST --}}
            <h3 class="mf-checklist-title">Checklist técnico</h3>

            <p class="mf-help no-print" data-mf-checklist-help hidden>
                Captura las actividades de este formato.
            </p>

            <div class="mf-table-wrap">
                <table class="mf-table">
                    <thead>
                        <tr>
                            <th scope="col">
                                Actividad / punto de control
                            </th>

                            <th scope="col" class="mf-check">
                                OK
                            </th>

                            <th scope="col" class="mf-check">
                                N/A
                            </th>

                            <th scope="col">
                                Observación
                            </th>
                        </tr>
                    </thead>

                    {{-- Lo dibuja maintenance-formats.js según la selección. --}}
                    <tbody data-mf-rows></tbody>
                </table>
            </div>

            <div class="mf-actions no-print">
                <button
                    type="button"
                    class="btn btn-sm btn-outline"
                    data-mf-add
                    hidden
                >
                    Agregar actividad
                </button>
            </div>

            {{-- OBSERVACIONES --}}
            <div class="form-field mf-observations">
                <label for="mf-observations" class="form-label">
                    Observaciones generales
                </label>

                <textarea
                    id="mf-observations"
                    name="general_observations"
                    class="form-textarea"
                    rows="4"
                    data-mf-field
                ></textarea>
            </div>

            {{-- FIRMAS --}}
            <div class="mf-signatures">
                <div class="mf-signature">
                    Responsable ejecutor
                </div>

                <div class="mf-signature">
                    Supervisor / responsable
                </div>

                <div class="mf-signature">
                    Calidad / liberación
                </div>
            </div>

        </section>
    </form>
</div>
