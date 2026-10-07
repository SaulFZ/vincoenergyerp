@extends('modules.rh.loadchart.index')

@section('content')
    <div id="approvalView">
        <div class="approval-container">
            <div class="approval-header">
                <div class="period-navigation-container">
                    <div class="period-navigation">
                        <button class="period-btn active" id="quincena1">Quincena 1</button>
                        <button class="period-btn" id="quincena2">Quincena 2</button>
                        <button class="period-btn" id="full-month">Mes</button>
                        <button class="period-btn" id="prev-period"><i class="fas fa-chevron-left"></i></button>
                        <span class="current-period-info" id="current-period">{{ now()->locale('es')->monthName }}
                            {{ now()->year }}</span>
                        <button class="period-btn" id="next-period"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>

                <div class="approval-actions">
                    @if (\App\Helpers\PermissionHelper::hasDirectPermission('ver_filtros'))
                        <button class="back-btn" id="toggle-filters-btn" aria-expanded="false" aria-controls="filters-container">
                            <i class="fas fa-filter"></i> Abrir Filtros
                        </button>
                    @endif

                    @if (\App\Helpers\PermissionHelper::hasDirectPermission('ver_guardias'))
                        <button class="back-btn" id="toggle-auxiliar-pal-btn" style="background-color: #4a5568; color: white;">
                            <i class="fas fa-user-shield"></i> Ver Auxiliar PAL
                        </button>
                    @endif

                    @if (\App\Helpers\PermissionHelper::hasDirectPermission('control_cuadrillas'))
                        <button class="squad-btn" id="squad-control">
                            <i class="fas fa-users-cog"></i> Control de Cuadrillas
                        </button>
                    @endif

                    <button class="services-info-btn" id="services-info">
                        <i class="fas fa-tasks"></i> Info de Servicios
                    </button>

                    @if (\App\Helpers\PermissionHelper::hasDirectPermission('gestionar_quincenas'))
                        <button class="back-btn" id="days-quincena">
                            <i class="fas fa-calendar-day"></i> Días de Quincena
                        </button>
                    @endif

                    <button class="back-btn" id="back-to-calendar" data-route="/calendar">
                        <i class="fas fa-arrow-left"></i> Volver al Calendario
                    </button>
                </div>
            </div>

            @if (\App\Helpers\PermissionHelper::hasDirectPermission('ver_filtros'))
                <div class="filters-container" id="filters-container" style="display: none;">
                    <div class="filter-group">
                        <label for="assignment-filter">Asignación</label>
                        <select id="assignment-filter" class="filter-select">
                            <option value="all">Todos los Empleados</option>
                            <option value="assigned" selected>Ver Solo Personal Asignados</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="area-filter">Área</label>
                        <select id="area-filter" class="filter-select">
                            <option value="">Todas las Áreas</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area }}">{{ $area }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="position-filter">Cargo</label>
                        <select id="position-filter" class="filter-select">
                            <option value="">Todos los Cargos</option>
                        </select>
                    </div>
                    <div class="filter-group filter-search-group">
                        <label for="employee-search">Buscar Empleado</label>
                        <input type="text" id="employee-search" class="filter-input" placeholder="Nombre, # Empleado...">
                    </div>
                </div>
            @endif

            <div class="activity-legend">
                <div class="legend-item"><div class="legend-color" style="background-color: var(--work-base);">B</div><span>Base</span></div>
                <div class="legend-item"><div class="legend-color" style="background-color: var(--work-well);">P</div><span>Pozo</span></div>
                <div class="legend-item"><div class="legend-color" style="background-color: var(--home-office);">TC</div><span>Trabajo en Casa</span></div>
                <div class="legend-item"><div class="legend-color" style="background-color: var(--commissioned);">C</div><span>Comisionado</span></div>
                <div class="legend-item"><div class="legend-color" style="background-color: var(--traveling);">V</div><span>Viaje</span></div>
                <div class="legend-item"><div class="legend-color" style="background-color: var(--training);">E</div><span>Entrenamiento</span></div>
                <div class="legend-item"><div class="legend-color" style="background-color: var(--rest);">D</div><span>Descanso</span></div>
                <div class="legend-item"><div class="legend-color" style="background-color: var(--vacation);">VAC</div><span>Vacaciones</span></div>
                <div class="legend-item"><div class="legend-color" style="background-color: var(--medical);">M</div><span>Médico</span></div>
                <div class="legend-item"><div class="legend-color" style="background-color: var(--permission);">PE</div><span>Permiso</span></div>
                <div class="legend-item"><div class="legend-color" style="background-color: var(--absence);">A</div><span>Ausencia</span></div>
                <div class="legend-item"><div class="legend-color" style="background-color: var(--gray);">N</div><span>Sin Act.</span></div>
                <div class="legend-divider"></div>
                <div class="legend-item"><div class="legend-color under-review-border"></div><span>Bajo Revisión</span></div>
                <div class="legend-item"><div class="legend-color reviewed-border"></div><span>Revisado</span></div>
                <div class="legend-item"><div class="legend-color approved-border"></div><span>Aprobado</span></div>
                <div class="legend-item"><div class="legend-color rejected-border"></div><span>Rechazado</span></div>
            </div>

            <div class="table-master-wrapper">
                <div id="table-loading-overlay" class="table-loading-overlay">
                    <div class="table-loader-spinner"></div>
                    <div class="table-loader-text">Procesando Datos</div>
                </div>

                <div class="table-container">
                    <table class="approval-table" id="approval-table">
                        <thead>
                            <tr class="header-row">
                                <th rowspan="2">Nombre</th>
                                <th rowspan="2">KPI</th>
                                @php
                                    $q1DaysCount = collect($monthlyDays)->where('is_quincena_1', true)->count();
                                @endphp
                                <th colspan="{{ $q1DaysCount }}" id="days-columns">Días del Período ({{ $q1DaysCount }} días)</th>
                                <th rowspan="2">Total</th>
                                <th rowspan="2" class="vacations-header" title="Vacaciones">Vac.</th>
                                <th rowspan="2" class="breaks-header" title="Descansos/Otros">Desc.</th>
                                <th rowspan="2" class="utilization-header" title="Utilización">Utiliz.</th>
                                <th rowspan="2" class="utilization-header" title="Aprobación">Acciones</th>
                            </tr>
                            <tr class="header-row" id="days-header-row">
                                {{-- Renderizado por JS --}}
                            </tr>
                        </thead>
                        <tbody id="approval-table-body">
                            {{-- EL JS RENDERIZARÁ LA TABLA AQUÍ DE FORMA DINÁMICA Y RÁPIDA --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
            // ✅ Variables globales inyectadas de forma segura y completa
            let currentMonth = {{ $currentMonth }};
            let currentYear = {{ $currentYear }};
            let currentView = 'quincena1';
            
            // Los datos esenciales se cargan directo desde el servidor
            let workLogsData = @json($workLogsData ?? []);
            let fortnightlyConfig = @json($fortnightlyConfig);
            let monthlyDays = @json($monthlyDays ?? []);
            let employees = @json($employees ?? []);
            let loadChartAssignments = @json($loadChartAssignments ?? []);
            let userPermissions = @json($userPermissions ?? []);
            let canSeeAmounts = @json($canSeeAmounts ?? false);
            let canSeeFilters = @json(\App\Helpers\PermissionHelper::hasDirectPermission('ver_filtros'));
            let currentUserId = {{ auth()->id() }};
            
            let allEmployeeRows = [];
            let allSquadRows = [];
        </script>
    </div>

    {{-- Modal de Aprobación --}}
    <div class="modal-approval-custom" id="approvalModal">
        <div class="modal-approval-content">
            <div class="modal-approval-header">
                <div class="modal-header-left">
                    <div class="modal-header-icon"><i class="fas fa-clipboard-check"></i></div>
                    <div>
                        <div class="modal-approval-title">Registro de Actividad</div>
                        <div class="modal-approval-subtitle">—</div>
                    </div>
                </div>
                <button class="modal-approval-close" title="Cerrar"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-approval-body" id="modal-items-list"></div>
            <div class="modal-approval-footer">
                <button class="btn btn-secondary modal-approval-close-btn"><i class="fas fa-times"></i> Cerrar</button>
                <button class="btn btn-success" id="modal-save-btn"><i class="fas fa-save"></i> Guardar Cambios</button>
            </div>
        </div>
    </div>

 
    {{-- Modal de Servicios --}}
    <div id="services-modal" class="services-modal">
        <div class="services-modal-content">
            <span class="services-close-btn">&times;</span>
            <h2 class="services-title">Catálogo de Servicios y Bonos</h2>

            <div class="modal-body">
                <div class="form-tabs">
                    <button class="tab-btn active tab-services" data-tab="services-tab">Servicios</button>
                    <button class="tab-btn tab-bonuses" data-tab="bonuses-tab">Bonos</button>
                    <div class="search-container">
                        <input type="text" id="service-search"
                            placeholder="Buscar por ID, Operación, o Descripción..." class="search-input">
                    </div>
                </div>

                <div class="tab-content active" id="services-tab">
                    <div id="services-placeholder" class="services-section">
                        <p>Cargando servicios...</p>
                    </div>
                </div>

                <div class="tab-content" id="bonuses-tab">
                    <div id="bonuses-placeholder" class="bonuses-section">
                        <p>Cargando bonos...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Quincenas --}}
    <div id="quincena-modal" class="quincena-modal-container" style="display: none;">
        <div class="quincena-modal-card">
            <div class="quincena-modal-header">
                <h5 class="quincena-modal-title">Gestión de Quincenas</h5>
                <button type="button" class="quincena-close-btn">&times;</button>
            </div>
            <div class="quincena-modal-content">
                <div class="quincena-selection-section">
                    <div class="quincena-selection-group">
                        <div class="quincena-select-header">
                            <span class="quincena-badge">1</span>
                            <h6 class="quincena-select-title">Primera Quincena</h6>
                        </div>
                        <div class="quincena-date-range">
                            <div class="quincena-date-field">
                                <label for="q1-start">Inicio:</label>
                                <input type="date" id="q1-start" class="quincena-date-input" readonly>
                            </div>
                            <div class="quincena-date-field">
                                <label for="q1-end">Fin:</label>
                                <input type="date" id="q1-end" class="quincena-date-input" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="quincena-selection-group">
                        <div class="quincena-select-header">
                            <span class="quincena-badge">2</span>
                            <h6 class="quincena-select-title">Segunda Quincena</h6>
                        </div>
                        <div class="quincena-date-range">
                            <div class="quincena-date-field">
                                <label for="q2-start">Inicio:</label>
                                <input type="date" id="q2-start" class="quincena-date-input" readonly>
                            </div>
                            <div class="quincena-date-field">
                                <label for="q2-end">Fin:</label>
                                <input type="date" id="q2-end" class="quincena-date-input" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="quincena-actions">
                        <button type="button" class="quincena-btn quincena-edit-btn"><i class="fas fa-edit"></i>
                            Editar</button>
                        <button type="button" class="quincena-btn quincena-cancel-btn" style="display: none;"><i
                                class="fas fa-times"></i> Cancelar</button>
                        <button type="button" class="quincena-btn quincena-save-btn" disabled><i
                                class="fas fa-save"></i> Guardar Cambios</button>
                    </div>
                </div>
                <div class="quincena-calendar-section">
                    <div class="quincena-month-nav">
                        <button type="button" class="quincena-nav-btn quincena-prev-month"><i
                                class="fas fa-chevron-left"></i></button>
                        <h5 class="quincena-month-title"></h5>
                        <button type="button" class="quincena-nav-btn quincena-next-month"><i
                                class="fas fa-chevron-right"></i></button>
                    </div>
                    <div class="quincena-calendar-grid"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Gestión de Cuadrillas --}}
    <div id="squad-control-modal" class="squad-modal-container">
        <div class="squad-modal-card">
            <div class="squad-modal-header">
                <div class="header-content">
                    <i class="fas fa-users-cog header-icon"></i>
                    <div>
                        <h3>Gestión de Cuadrillas</h3>
                        <p class="subtitle">Administra los grupos de operadores</p>
                    </div>
                </div>
                <button class="squad-close-btn">&times;</button>
            </div>

            <div class="squad-modal-content">
                <div class="toolbar">
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Buscar cuadrilla u operador...">
                        <button class="search-clear"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="toolbar-buttons">
                        <button class="toolbar-btn primary" id="add-new-squad">
                            <i class="fas fa-plus"></i> Nueva Cuadrilla
                        </button>
                    </div>
                </div>

                <div class="responsive-table-container">
                    <table class="squad-table">
                        <thead>
                            <tr>
                                <th width="120px" class="centered-cell">Núm Cuadrilla</th>
                                <th>Operadores</th>
                                <th width="120px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Formulario de Cuadrilla --}}
    <div id="squad-form-modal" class="form-modal">
        <div class="form-modal-content">
            <div class="form-header">
                <h4><i class="fas fa-users"></i> <span id="form-title">Nueva Cuadrilla</span></h4>
                <button class="form-close-btn">&times;</button>
            </div>
            <div class="form-body">
                <div class="form-group">
                    <label for="squad-select">Número de Cuadrilla</label>
                    <select class="form-select" id="squad-select">
                    </select>
                </div>
                <div class="form-group">
                    <label>Seleccionar Operadores (Máximo 4)</label>
                    <div class="operator-selection">
                        <div class="operator-selector">
                            <label>Operador 1</label>
                            <select class="form-select operator-select">
                                <option value="">Seleccionar...</option>
                            </select>
                        </div>
                        <div class="operator-selector">
                            <label>Operador 2</label>
                            <select class="form-select operator-select">
                                <option value="">Seleccionar...</option>
                            </select>
                        </div>
                        <div class="operator-selector">
                            <label>Operador 3</label>
                            <select class="form-select operator-select">
                                <option value="">Seleccionar...</option>
                            </select>
                        </div>
                        <div class="operator-selector">
                            <label>Operador 4</label>
                            <select class="form-select operator-select">
                                <option value="">Seleccionar...</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-footer">
                <button class="form-btn cancel-sq-btn">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button class="form-btn save-sq-btn">
                    <i class="fas fa-save"></i> Guardar Cuadrilla
                </button>
            </div>
        </div>
    </div>

    {{-- NUEVO Modal para ver detalles del empleado con el CALENDAR_PARTIAL --}}
    <div id="employee-detail-modal" class="employee-detail-modal">
        <div class="employee-detail-modal-content">
            <div class="employee-detail-modal-header">
                <div class="header-content">
                    <i class="fas fa-user header-icon"></i>
                    <div>
                        <h3>Detalles del Empleado</h3>
                        <p class="subtitle" id="employee-modal-subtitle">Cargando información...</p>
                    </div>
                </div>
                <button class="employee-detail-close-btn">&times;</button>
            </div>

            <div class="employee-detail-modal-body" id="employee-detail-content">
                <div class="loading-spinner-container">
                    <div class="loading-spinner-lg"></div>
                    <div class="loading-message">Cargando información del empleado...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.appRoutes = {
            getOperadores: '{{ route('squads.get_operadores') }}',
            getSquads: '{{ route('squads.get_squads') }}',
            storeSquad: '{{ route('squads.store') }}',
            destroySquad: '{{ route('squads.destroy', ['squadNumber' => '__SQUAD_NUMBER__']) }}'.replace('__SQUAD_NUMBER__', ''),
            showSquad: '{{ route('squads.show', ['squadNumber' => '__SQUAD_NUMBER__']) }}'.replace('__SQUAD_NUMBER__', '')
        };
    </script>

    <script src="{{ asset('assets/js/rh/loadchart/approval.js') }}"></script>
    <script src="{{ asset('assets/js/rh/loadchart/approvalModals.js') }}"></script>
@endsection