@extends('modulos.recursoshumanos.sistemas.loadchart.index')
@push('styles')
    {{-- Asegúrate de que este CSS incluya los estilos del punto 3 (si es necesario) --}}
    <link href="{{ asset('assets/css/recursoshumanos/loadchart/approval.css') }}" rel="stylesheet">
@endpush


@section('content')
    <div id="approvalView">
        <div class="approval-container">
            <div class="approval-header">
                <div class="period-navigation-container">
                    <div class="period-navigation">
                        <button class="period-btn active" id="quincena1">Quincena 1</button>
                        <button class="period-btn" id="quincena2">Quincena 2</button>
                        <button class="period-btn" id="full-month">Mes Completo</button>
                        <button class="period-btn" id="prev-period"><i class="fas fa-chevron-left"></i></button>
                        <span class="current-period-info" id="current-period">{{ now()->locale('es')->monthName }}
                            {{ now()->year }}</span>
                        <button class="period-btn" id="next-period"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>

                <div class="approval-actions">
                    {{-- Control de Cuadrillas --}}
                    @if (\App\Helpers\PermissionHelper::hasDirectPermission('control_cuadrillas'))
                        <button class="squad-btn" id="squad-control">
                            <i class="fas fa-users-cog"></i> Control de Cuadrillas
                        </button>
                    @endif

                    {{-- Info de Servicios (Sin permiso explícito en tu solicitud, se mantiene visible) --}}
                    <button class="services-info-btn" id="services-info">
                        <i class="fas fa-tasks"></i> Info de Servicios
                    </button>

                    {{-- Días de Quincena --}}
                    @if (\App\Helpers\PermissionHelper::hasDirectPermission('gestionar_quincenas'))
                        <button class="back-btn" id="days-quincena">
                            <i class="fas fa-calendar-day"></i> Días de Quincena
                        </button>
                    @endif

                    {{-- Volver al Calendario (Se asume visible para todos) --}}
                    <button class="back-btn" id="back-to-calendar" data-route="/calendar">
                        <i class="fas fa-arrow-left"></i> Volver al Calendario
                    </button>
                </div>
            </div>

            <div class="activity-legend">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: var(--work-base);">B</div>
                    <span>Base</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: var(--work-well);">P</div>
                    <span>Pozo</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: var(--home-office);">TC</div>
                    <span>Trabajo en Casa</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: var(--commissioned);">C</div>
                    <span>Comisionado</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: var(--traveling);">V</div>
                    <span>Viaje</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: var(--training);">E</div>
                    <span>Entrenamiento</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: var(--rest);">D</div>
                    <span>Descanso</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: var(--vacation);">VAC</div>
                    <span>Vacaciones</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: var(--medical);">M</div>
                    <span>Médico</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: var(--permission);">PE</div>
                    <span>Permiso</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: var(--absence);">A</div>
                    <span>Ausencia</span>
                </div>
                <div class="legend-divider"></div>
                {{-- ➡️ Indicadores de Aprobación --}}
                <div class="legend-item">
                    <div class="legend-color under-review-border"></div>
                    <span>Bajo Revisión</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color reviewed-border"></div>
                    <span>Revisado</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color approved-border"></div>
                    <span>Aprobado</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color rejected-border"></div>
                    <span>Rechazado</span>
                </div>
            </div>

            <div class="table-container">
                <table class="approval-table" id="approval-table">
                    <thead>
                        <tr class="header-row">
                            <th rowspan="2">Nombre</th>
                            <th rowspan="2">KPI</th>
                            <th colspan="{{ count($monthlyDays) }}" id="days-columns">Días del Período</th>
                            <th rowspan="2">Total</th>
                            <th rowspan="2" class="vacations-header" title="Vacaciones">Vac.</th>
                            <th rowspan="2" class="breaks-header" title="Descansos/Otros">Desc.</th>
                            <th rowspan="2" class="utilization-header" title="Utilización">Utiliz.</th>
                            <th rowspan="2" class="utilization-header" title="Aprobación">Aprob.</th>
                        </tr>
                        <tr class="header-row" id="days-header-row">
                            @foreach ($monthlyDays as $dayInfo)
                                <th class="day-header {{ $dayInfo['is_quincena_1'] ? 'quincena-1' : '' }} {{ $dayInfo['is_quincena_2'] ? 'quincena-2' : '' }} {{ !$dayInfo['is_working_day'] ? 'non-working' : '' }} {{ !$dayInfo['is_current_month'] ? 'other-month' : '' }}"
                                    data-day="{{ $dayInfo['day'] }}" data-date="{{ $dayInfo['date'] }}"
                                    data-quincena-1="{{ $dayInfo['is_quincena_1'] ? 'true' : 'false' }}"
                                    data-quincena-2="{{ $dayInfo['is_quincena_2'] ? 'true' : 'false' }}"
                                    data-current-month="{{ $dayInfo['is_current_month'] ? 'true' : 'false' }}"
                                    data-month="{{ $dayInfo['month'] }}"
                                    title="{{ \Carbon\Carbon::parse($dayInfo['date'])->format('d/m/Y') }}{{ !$dayInfo['is_current_month'] ? ' (Mes anterior)' : '' }}">
                                    {{ $dayInfo['day'] }}<br>{{ $dayInfo['day_name'] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="approval-table-body">
                        @php
                            // 1. Obtener empleados con su número de cuadrilla (si tienen asignación)
                            $employeesWithSquad = $employees->map(function ($employee) {
                                // Asume que un empleado puede tener múltiples entradas en la tabla Squad,
                                // pero para propósitos de la cuadrilla principal, tomaremos la primera o la más relevante.
                                // Si no hay squad, se asigna un número grande para que vaya al final.
                                $squad = $employee->squads->first();
                                $employee->squad_number = $squad ? $squad->squad_number : 999;
                                return $employee;
                            });

                            // 2. Ordenar y agrupar por número de cuadrilla
                            $groupedEmployees = $employeesWithSquad
                                ->sortBy('squad_number')
                                ->groupBy('squad_number');

                            $showSquadGrouping = \App\Helpers\PermissionHelper::hasDirectPermission('control_cuadrillas');
                            $totalDayColumns = count($monthlyDays) + 4; // Nombre, KPI, Total, Vac, Desc, Utiliz, Aprob (7 columnas de rowspan)
                            $colspanValue = $totalDayColumns + count($monthlyDays) - 1; // Calculando el colspan correcto (columna Nombre + días + Totales - 1)
                        @endphp

                        @foreach ($groupedEmployees as $squadNumber => $squadEmployees)

                            {{-- INICIO: Fila de Encabezado de Cuadrilla (Solo si tiene permiso) --}}
                            @if ($showSquadGrouping)
                                @php
                                    $squadLabel = $squadNumber !== 999
                                        ? 'Cuadrilla-' . str_pad($squadNumber, 2, '0', STR_PAD_LEFT)
                                        : 'Sin Cuadrilla Asignada';
                                    $squadClass = $squadNumber !== 999
                                        ? 'squad-group-row-active'
                                        : 'squad-group-row-none';
                                @endphp
                                <tr class="squad-group-row {{ $squadClass }}">
                                    <td colspan="{{ $colspanValue }}" class="squad-group-label">
                                        {{ $squadLabel }}
                                    </td>
                                </tr>
                            @endif
                            {{-- FIN: Fila de Encabezado de Cuadrilla --}}

                            @foreach ($squadEmployees as $employee)
                                {{-- Fila Principal (Actividad) --}}
                                <tr class="employee-row" data-employee-id="{{ $employee->id }}">
                                    <td rowspan="4" class="employee-info-cell" data-icon="bx bx-calendar"
                                        data-text="ver calendario">
                                        {{ $employee->full_name }}
                                    </td>
                                    <td class="activity-label-cell">Actividad</td>
                                    @foreach ($monthlyDays as $dayInfo)
                                        <td class="data-cell {{ $dayInfo['is_quincena_1'] ? 'quincena-1' : '' }} {{ $dayInfo['is_quincena_2'] ? 'quincena-2' : '' }} {{ !$dayInfo['is_working_day'] ? 'non-working' : '' }} {{ !$dayInfo['is_current_month'] ? 'other-month' : '' }}"
                                            data-day="{{ $dayInfo['day'] }}" data-date="{{ $dayInfo['date'] }}">
                                            <div class="status-indicator status-n">N</div>
                                        </td>
                                    @endforeach
                                    <td class="data-cell total-activity">
                                        <div class="status-indicator">0</div>
                                    </td>
                                    <td rowspan="4" class="vacations-cell">
                                        <div class="vacations-container">
                                            <div class="vacations-value">0</div>
                                        </div>
                                    </td>
                                    <td rowspan="4" class="breaks-cell">
                                        <div class="breaks-container">
                                            <div class="breaks-value">0</div>
                                        </div>
                                    </td>
                                    <td rowspan="4" class="utilization-cell">
                                        <div class="utilization-container">
                                            <div class="utilization-value">0%</div>
                                        </div>
                                    </td>
                                    <td rowspan="4" class="actions-cell">
                                        <div class="actions-container">
                                            @php
                                                $employeeAssignment = $loadChartAssignments->firstWhere(
                                                    'employee_id',
                                                    $employee->id,
                                                );
                                                $isReviewerForEmployee =
                                                    $employeeAssignment &&
                                                    $employeeAssignment->reviewer_id === auth()->id();
                                                $isApproverForEmployee =
                                                    $employeeAssignment &&
                                                    $employeeAssignment->approver_id === auth()->id();
                                            @endphp

                                            @if ($isReviewerForEmployee)
                                                <button class="btn-review">Revisar</button>
                                            @endif

                                            @if ($isApproverForEmployee)
                                                <button class="btn-approve">Aprobar</button>
                                            @endif

                                            @if (!$isReviewerForEmployee && !$isApproverForEmployee)
                                                <span class="no-permissions">Sin permisos</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                {{-- Filas de Detalle --}}
                                @php $rowTypes = ['Comida', 'Bono', 'Servicio']; @endphp
                                @foreach ($rowTypes as $rowType)
                                    <tr class="activity-row hidden" data-employee-id="{{ $employee->id }}">
                                        <td class="activity-label-cell">{{ $rowType }}</td>
                                        @foreach ($monthlyDays as $dayInfo)
                                            <td class="data-cell {{ $dayInfo['is_quincena_1'] ? 'quincena-1' : '' }} {{ $dayInfo['is_quincena_2'] ? 'quincena-2' : '' }} {{ !$dayInfo['is_working_day'] ? 'non-working' : '' }} {{ !$dayInfo['is_current_month'] ? 'other-month' : '' }}"
                                                data-day="{{ $dayInfo['day'] }}" data-date="{{ $dayInfo['date'] }}">0</td>
                                        @endforeach
                                        <td class="data-cell total-{{ strtolower(str_replace(' ', '-', $rowType)) }}">0</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button class="save-btn" onclick="guardarDatos()">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
        </div>

        <script>
            // Variables globales
            let currentMonth = {{ $currentMonth }};
            let currentYear = {{ $currentYear }};
            let currentView = 'quincena1';
            let workLogsData = @json($workLogsData ?? []);
            let fortnightlyConfig = @json($fortnightlyConfig);
            let canSeeAmounts = @json($canSeeAmounts ?? false);
            let userPermissions = @json($userPermissions ?? []);
            let loadChartAssignments = @json($loadChartAssignments ?? []);
            let currentUserId = {{ auth()->id() }};
        </script>
    </div>
    <div class="modal-approval-custom" id="approvalModal">
        <div class="modal-approval-content">
            <div class="modal-approval-header">
                <div>
                    <div class="modal-approval-title">Registro de Actividad</div>
                    <div class="modal-approval-subtitle">Juan Pérez - 15 de Enero, 2024</div>
                </div>
                <button class="modal-approval-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-approval-body">
                <table class="approval-table-custom">
                    <thead>
                        <tr>
                            <th style="width: 8%">Concepto</th>
                            <th style="width: 20%">Detalles</th>
                            <th style="width: 10%">ID/Tipo</th>
                            <th style="width: 25%">Detalles Adicionales</th>
                            <th class="amount-header" style="width: 10%">Monto</th>
                            <th style="width: 12%">Estado Actual</th>
                            <th style="width: 15%">Comentarios</th>
                        </tr>
                    </thead>
                    <tbody id="modal-table-body">
                    </tbody>
                </table>
            </div>
            <div class="modal-approval-footer">
                <button class="btn btn-secondary modal-approval-close-btn">
                    <i class="fas fa-times"></i> Cerrar
                </button>
                <button class="btn btn-success" id="modal-save-btn">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>

    {{-- Aquí el resto de tus modales (services-modal, quincena-modal, squad-control-modal) --}}

    <div id="services-modal" class="services-modal">
        <div class="services-modal-content">
            <span class="services-close-btn">&times;</span>
            <h2 class="services-title">Catálogo de Servicios y Bonos</h2>

            <div class="modal-body">
                <div class="form-tabs">
                    <button class="tab-btn active" data-tab="services-tab">Servicios</button>
                    <button class="tab-btn" data-tab="bonuses-tab">Bonos</button>
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
        // Definición de rutas
        window.appRoutes = {
            getOperadores: '{{ route('squads.get_operadores') }}',
            getSquads: '{{ route('squads.get_squads') }}',
            storeSquad: '{{ route('squads.store') }}',
            destroySquad: '{{ route('squads.destroy', ['squadNumber' => '__SQUAD_NUMBER__']) }}'.replace(
                '__SQUAD_NUMBER__', ''),
            showSquad: '{{ route('squads.show', ['squadNumber' => '__SQUAD_NUMBER__']) }}'.replace('__SQUAD_NUMBER__',
                '')
        };
    </script>

    <script src="{{ asset('assets/js/recursoshumanos/loadchart/approval.js') }}"></script>
    <script src="{{ asset('assets/js/recursoshumanos/loadchart/approvalModals.js') }}"></script>
@endsection










