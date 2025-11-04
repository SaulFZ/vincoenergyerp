@extends('modulos.recursoshumanos.sistemas.loadchart.index')

@section('content')

<!--
    <div>
        <div id="normalView">
            <div class="content-layout">
                {{-- DETALLES DEL EMPLEADO --}}
                <div class="employee-details" id="employeeDetails">
                    <div class="employee-header">
                        <div class="employee-photo-container">
                            <img src="{{ $employee_photo }}" alt="Foto de perfil" class="employee-photo">
                        </div>
                        <div>
                            <h2>Nombre</h2>
                            <p class="employee-name-header">{{ $employee->full_name ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="employee-info">
                        <div class="info-group">
                            <h3>Número de empleado</h3>
                            <p>{{ $employee->employee_number ?? 'N/A' }}</p>
                        </div>
                        <div class="info-group">
                            <h3>Departamento</h3>
                            <p>{{ $employee->department ?? 'N/A' }}</p>
                        </div>
                        <div class="info-group">
                            <h3>Puesto</h3>
                            <p>{{ $employee->job_title ?? 'N/A' }}</p>
                        </div>
                        <div class="info-group">
                            <h3>Fecha de Ingreso</h3>
                            <p>{{ $hire_date }}</p>
                        </div>
                        {{-- Saldos (MODIFICADO: Añadido data-balance-type para JS) --}}
                        <div class="info-group">
                            <h3>Vacaciones</h3>
                            <p class="vacation-days" data-balance-type="vacation">{{ $vacationDays }} días</p>
                        </div>
                        <div class="info-group">
                            <h3>Días de Descanso</h3>
                            <p data-balance-type="rest">{{ $restDays }} días</p>
                        </div>
                    </div>
                </div>

                {{-- CONTENEDOR PRINCIPAL DEL CALENDARIO (LOAD CHART) --}}
                <div class="load-chart-container" id="loadChart">
                    <div class="chart-header">
                        <h2><i class="fas fa-chart-bar"></i> Load Chart - {{ $monthName }} {{ $currentYear }}</h2>
                        <div class="chart-actions">
                            <div class="month-navigation">
                                <button id="prev-month"><i class="fas fa-chevron-left"></i></button>
                                <span data-month="{{ $currentMonth }}"
                                    data-year="{{ $currentYear }}">{{ $monthName }}
                                    {{ $currentYear }}</span>
                                <button id="next-month"><i class="fas fa-chevron-right"></i></button>
                            </div>
                            @if (\App\Helpers\PermissionHelper::hasDirectPermission('ver_loadchart'))
                                <button class="btn btn-orange" id="approve-loadchart" data-route="/approval">
                                    <i class="fas fa-check-circle"></i> Aprobar Loadchart
                                </button>
                            @endif
                        </div>
                    </div>
                    <table class="calendar">
                        <thead>
                            <tr>
                                <th>Lunes</th>
                                <th>Martes</th>
                                <th>Miércoles</th>
                                <th>Jueves</th>
                                <th>Viernes</th>
                                <th>Sábado</th>
                                <th>Domingo</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- La tabla se renderiza inicialmente con PHP y luego se actualiza con JS/AJAX --}}
                            <tr>
                                @foreach ($calendarDays as $day)
                                    @php
                                        $isCurrentMonth = $day['current_month'];
                                        $isToday = $day['date'] == date('Y-m-d');

                                        // Lógica para determinar si el día está en un período de nómina
                                        $dateString = $day['date'] ?? null;
                                        $dateObj = $dateString ? new \DateTime($dateString) : null;
                                        $inPayrollPeriod = false;

                                        if ($dateObj) {
                                            if ($payrollDates['q1_start'] && $payrollDates['q1_end']) {
                                                $q1Start = new \DateTime($payrollDates['q1_start']);
                                                $q1End = new \DateTime($payrollDates['q1_end'] . ' 23:59:59');
                                                if ($dateObj >= $q1Start && $dateObj <= $q1End) {
                                                    $inPayrollPeriod = true;
                                                }
                                            }
                                            if ($payrollDates['q2_start'] && $payrollDates['q2_end']) {
                                                $q2Start = new \DateTime($payrollDates['q2_start']);
                                                $q2End = new \DateTime($payrollDates['q2_end'] . ' 23:59:59');
                                                if ($dateObj >= $q2Start && $dateObj <= $q2End) {
                                                    $inPayrollPeriod = true;
                                                }
                                            }
                                        }

                                        $isPayrollStart1 =
                                            $payrollDates['q1_start'] &&
                                            date('Y-m-d', strtotime($payrollDates['q1_start'])) == $day['date'];
                                        $isPayrollEnd1 =
                                            $payrollDates['q1_end'] &&
                                            date('Y-m-d', strtotime($payrollDates['q1_end'])) == $day['date'];
                                        $isPayrollStart2 =
                                            $payrollDates['q2_start'] &&
                                            date('Y-m-d', strtotime($payrollDates['q2_start'])) == $day['date'];
                                        $isPayrollEnd2 =
                                            $payrollDates['q2_end'] &&
                                            date('Y-m-d', strtotime($payrollDates['q2_end'])) == $day['date'];
                                    @endphp

                                    <td class="{{ !$isCurrentMonth ? 'other-month' : '' }} {{ $isToday ? 'current-d' : '' }} {{ $inPayrollPeriod ? 'in-payroll-period' : '' }}"
                                        data-date="{{ $day['date'] }}">
                                        <span class="day-number">{{ $day['day'] }}</span>

                                        {{-- Íconos de Día Festivo --}}
                                        @if (isset($day['is_holiday']) && $day['is_holiday'])
                                            @if ($day['holiday_icon_type'] == 'christmas_tree')
                                                <img src="https://img.icons8.com/external-victoruler-flat-victoruler/64/external-christmas-tree-christmas-victoruler-flat-victoruler-1.png"
                                                    alt="Árbol de Navidad" class="holiday-icon"
                                                    title="{{ $day['holiday_name'] }}">
                                            @else
                                                {{-- Ícono por defecto para otros días festivos --}}
                                                <img src="https://img.icons8.com/skeuomorphism/32/event.png"
                                                    alt="Día Festivo" class="holiday-icon"
                                                    title="{{ $day['holiday_name'] }}">
                                            @endif
                                        @endif

                                        {{-- Íconos de Período de Nómina --}}
                                        @if ($isPayrollStart1)
                                            <i class="fas fa-flag payroll-icon payroll-start-1"
                                                title="Inicio Quincena 1"></i>
                                        @endif
                                        @if ($isPayrollEnd1)
                                            <i class="fas fa-flag payroll-icon payroll-end" title="Fin Quincena 1"></i>
                                        @endif
                                        @if ($isPayrollStart2)
                                            <i class="fas fa-flag payroll-icon payroll-start-2"
                                                title="Inicio Quincena 2"></i>
                                        @endif
                                        @if ($isPayrollEnd2)
                                            <i class="fas fa-flag payroll-icon payroll-end" title="Fin Quincena 2"></i>
                                        @endif

                                    </td>
                                    @if ($loop->iteration % 7 == 0)
                            </tr>
                            <tr>
                                @endif
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                    {{-- NUEVO OVERLAY DE CARGA --}}
                    <div class="loading-overlay" id="calendarLoadingOverlay">
                        <div class="loading-spinner-lg"></div>
                        <div class="loading-message">Cargando datos del calendario...</div>
                    </div>
                    {{-- LEYENDA DEL CALENDARIO --}}
                    <div class="activity-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: var(--work-base);"></div>
                            <div>Trabajo en Base</div>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: var(--work-well);"></div>
                            <div>Trabajo en Pozo</div>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: var(--home-office);"></div>
                            <div>Trabajo en Casa</div> {{-- CAMBIO: Home Office -> Trabajo en Casa --}}
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: var(--traveling);"></div>
                            <div>Viaje</div>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: var(--rest);"></div>
                            <div>Descanso</div>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: var(--vacation);"></div>
                            <div>Vacaciones</div>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: var(--training);"></div>
                            <div>Entrenamiento</div>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: var(--medical);"></div>
                            <div>Médico</div>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: var(--commissioned);"></div>
                            <div>Comisionado</div>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: var(--absence);"></div>
                            <div>Ausencia</div>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: var(--permission);"></div>
                            <div>Permiso</div>
                        </div>
                        <div class="legend-item">
                            <i class="fa-regular fa-hourglass-half legend-under-review"></i>
                            <div>Bajo Revisión</div>
                        </div>
                        <div class="legend-item">
                            <i class="fas fa-lock-open legend-reviewed"></i>
                            <div>Revisado</div>
                        </div>
                        <div class="legend-item">
                            <i class="fas fa-lock legend-approved"></i>
                            <div>Aprobado</div>
                        </div>
                        <div class="legend-item">
                            <i class="fas fa-exclamation-triangle legend-rejected"></i>
                            <div>Rechazado</div>
                        </div>

                        <div class="legend-item">
                            <i class="fas fa-flag  payroll-start-1"></i>
                            <div>Inicio Q1</div>
                        </div>
                        <div class="legend-item">
                            <i class="fas fa-flag payroll-end"></i>
                            <div>Fin Quincena</div>
                        </div>
                        <div class="legend-item">
                            <img src="https://img.icons8.com/skeuomorphism/32/event.png" alt="Día Festivo"
                                class="legend-holiday-icon">
                            <div>Dia Festivo</div>
                        </div>

                    </div>
                </div>
            </div>
        </div>


        {{-- MODAL DE REGISTRO DE ACTIVIDAD --}}
        <div class="modal" id="activityModal">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="header-icon">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <h3>Registrar Actividad</h3>
                    <button class="close-modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-tabs">
                        <button class="tab-btn active" data-tab="activity">Actividad</button>
                        <button class="tab-btn" data-tab="service" id="service-tab-btn"
                            style="display: none;">Servicio</button>
                    </div>

                    <div class="tab-content active" id="activity-tab">
                        <div class="form-group">
                            <label for="activity-date">Fecha</label>
                            <div class="input-with-icon">
                                <i class="far fa-calendar-alt"></i>
                                <input type="text" id="activity-date" class="input-custom" value="" readonly>
                            </div>
                        </div>

                        <div class="form-group" id="activity-type-group">
                            <label for="activity-type">Tipo de Actividad</label>

                            <div class="custom-select" id="activity-type-select">
                                <div class="select-header" id="activity-type-header">
                                    <span class="placeholder">Seleccionar actividad...</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>

                                <div class="select-options" id="activity-type-options">
                                    <div class="activity-option" data-value="B">
                                        <div class="option-content">
                                            <div class="color-indicator" style="background-color: var(--work-base);">
                                            </div>
                                            <div class="activity-label">Trabajo en Base</div>
                                        </div>
                                        <div class="activity-code"
                                            style="background-color: var(--work-base); color: #fff;">B
                                        </div>
                                    </div>

                                    <div class="activity-option" data-value="P">
                                        <div class="option-content">
                                            <div class="color-indicator" style="background-color: var(--work-well);">
                                            </div>
                                            <div class="activity-label">Trabajo en Pozo</div>
                                        </div>
                                        <div class="activity-code"
                                            style="background-color: var(--work-well); color: #fff;">P
                                        </div>
                                    </div>

                                    <div class="activity-option" data-value="C">
                                        <div class="option-content">
                                            <div class="color-indicator" style="background-color: var(--commissioned);">
                                            </div>
                                            <div class="activity-label">Comisionado</div>
                                        </div>
                                        <div class="activity-code"
                                            style="background-color: var(--commissioned); color: #fff;">C</div>
                                    </div>

                                    {{-- CAMBIO: Home Office (H) a Trabajo en Casa (TC) --}}
                                    <div class="activity-option" data-value="TC">
                                        <div class="option-content">
                                            <div class="color-indicator" style="background-color: var(--home-office);">
                                            </div>
                                            <div class="activity-label">Trabajo en Casa</div>
                                        </div>
                                        <div class="activity-code"
                                            style="background-color: var(--home-office); color: #fff;">
                                            TC</div>
                                    </div>

                                    <div class="activity-option" data-value="V">
                                        <div class="option-content">
                                            <div class="color-indicator" style="background-color: var(--traveling);">
                                            </div>
                                            <div class="activity-label">Viaje</div>
                                        </div>
                                        <div class="activity-code"
                                            style="background-color: var(--traveling); color: #fff;">V
                                        </div>
                                    </div>

                                    <div class="activity-option" data-value="D">
                                        <div class="option-content">
                                            <div class="color-indicator" style="background-color: var(--rest);"></div>
                                            <div class="activity-label">Descanso</div>
                                        </div>
                                        <div class="activity-code" style="background-color: var(--rest); color: #fff;">D
                                        </div>
                                    </div>

                                    <div class="activity-option" data-value="VAC">
                                        <div class="option-content">
                                            <div class="color-indicator" style="background-color: var(--vacation);"></div>
                                            <div class="activity-label">Vacaciones</div>
                                        </div>
                                        <div class="activity-code"
                                            style="background-color: var(--vacation); color: #fff;">VAC
                                        </div>
                                    </div>

                                    <div class="activity-option" data-value="E">
                                        <div class="option-content">
                                            <div class="color-indicator" style="background-color: var(--training);"></div>
                                            <div class="activity-label">Entrenamiento</div>
                                        </div>
                                        <div class="activity-code"
                                            style="background-color: var(--training); color: #fff;">E
                                        </div>
                                    </div>

                                    <div class="activity-option" data-value="M">
                                        <div class="option-content">
                                            <div class="color-indicator" style="background-color: var(--medical);"></div>
                                            <div class="activity-label">Médico</div>
                                        </div>
                                        <div class="activity-code" style="background-color: var(--medical); color: #fff;">
                                            M
                                        </div>
                                    </div>

                                    <div class="activity-option" data-value="A">
                                        <div class="option-content">
                                            <div class="color-indicator" style="background-color: var(--absence);"></div>
                                            <div class="activity-label">Ausencia</div>
                                        </div>
                                        <div class="activity-code" style="background-color: var(--absence); color: #fff;">
                                            A
                                        </div>
                                    </div>

                                    <div class="activity-option" data-value="PE">
                                        <div class="option-content">
                                            <div class="color-indicator" style="background-color: var(--permission);">
                                            </div>
                                            <div class="activity-label">Permiso</div>
                                        </div>
                                        <div class="activity-code"
                                            style="background-color: var(--permission); color: #fff;">
                                            PE
                                        </div>
                                    </div>

                                    <div class="activity-option" data-value="N">
                                        <div class="option-content">
                                            <div class="color-indicator" style="background-color: #ccc;"></div>
                                            <div class="activity-label">Ninguna / Solo Bonos/Servicios</div>
                                        </div>
                                        <div class="activity-code" style="background-color: #ccc; color: #333;">
                                            N
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Hidden original select (for form data, required for old validation layers) --}}
                            <select id="activity-type" name="activity_type" style="display:none;">
                                <option value="">Seleccionar actividad...</option>
                                <option value="B">B - Trabajo en Base</option>
                                <option value="P">P - Trabajo en Pozo</option>
                                <option value="C">C - Comisionado</option>
                                <option value="TC">TC - Trabajo en Casa</option> {{-- CAMBIO: H -> TC --}}
                                <option value="V">V - Viaje</option>
                                <option value="D">D - Descanso</option>
                                <option value="VAC">VAC - Vacaciones</option>
                                <option value="E">E - Entrenamiento</option>
                                <option value="M">M - Médico</option>
                                <option value="A">A - Ausencia</option>
                                <option value="PE">PE - Permiso</option>
                                <option value="N">N - Ninguna</option>
                            </select>

                            {{-- Error Messages --}}
                            <div class="error-message" id="activity-type-error">Debes seleccionar un tipo de actividad
                            </div>
                            <div class="error-message" id="vacation-balance-error" style="display:none;">No tienes días
                                de
                                vacaciones disponibles.</div>

                        </div>

                        <div class="form-group" id="well-name-field" style="display: none;">
                            <label for="well-name">Nombre del Pozo</label>
                            <div class="input-with-icon">
                                <i class="fas fa-oil-well"></i>
                                <input type="text" id="well-name" class="input-custom"
                                    placeholder="Ingrese nombre del pozo">
                            </div>
                            <div class="error-message" id="well-name-error">Debes ingresar el nombre del pozo</div>
                        </div>

                        <div class="form-group" id="commissioned-field" style="display: none;">
                            <label for="commissioned-select">Área Comisionada</label>
                            <select id="commissioned-select" class="select-custom">
                                <option value="">Seleccionar área...</option>
                                <option value="Recursos Humanos">Recursos Humanos</option>
                                <option value="QHSE">QHSE</option>
                                <option value="Ventas">Ventas</option>
                                <option value="Administracion">Administración</option>
                                <option value="Operaciones">Operaciones</option>
                                <option value="Suministros">Suministros</option>
                                <option value="Geociencias">Geociencias</option>
                            </select>
                            <div class="error-message" id="commissioned-error">Debes seleccionar un área comisionada</div>
                        </div>

                        {{-- INICIO: NUEVOS CAMPOS PARA VIAJE (V) --}}
                        <div class="form-group" id="travel-destination-field" style="display: none;">
                            <label for="travel-destination">Destino del Viaje</label>
                            <div class="input-with-icon">
                                <i class="fas fa-map-marker-alt"></i>
                                <input type="text" id="travel-destination" class="input-custom"
                                    placeholder="Ingrese el destino">
                            </div>
                            <div class="error-message" id="travel-destination-error">Debes ingresar el destino del viaje
                            </div>
                        </div>

                        <div class="form-group" id="travel-reason-field" style="display: none;">
                            <label for="travel-reason">Motivo del Viaje</label>
                            <div class="input-with-icon">
                                <i class="fas fa-route"></i>
                                <input type="text" id="travel-reason" class="input-custom"
                                    placeholder="Ingrese el motivo">
                            </div>
                            <div class="error-message" id="travel-reason-error">Debes ingresar el motivo del viaje</div>
                        </div>
                        {{-- FIN: NUEVOS CAMPOS PARA VIAJE (V) --}}


                        {{-- INICIO: CAMPOS CONDICIONALES A TRABAJO EN POZO (P) --}}
                        <div id="conditional-fields" style="display: none;">

                            <div class="form-group">
                                <label for="food-bonus">Bono de Comida</label>
                                <select id="food-bonus" class="select-custom">
                                    <option value="">Seleccionar bono de comida...</option>
                                    @foreach ($foodOptions as $meal)
                                        <option value="{{ $meal->meal_number }}">
                                            {{ $meal->meal_number }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="error-message" id="food-bonus-error">Debes seleccionar un bono de comida.
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="field-bonus">Bono de Campo</label>
                                <select id="field-bonus" class="select-custom">
                                    <option value="">Seleccionar bono de campo...</option>
                                    @foreach ($fieldBonuses as $bonus)
                                        <option value="{{ $bonus->bonus_identifier }}">
                                            {{ $bonus->bonus_type }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="error-message" id="field-bonus-error">Debes seleccionar un bono de campo.
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="has-service-bonus">¿Bono de servicio?</label>
                                <div class="service-bonus-options">
                                    <div class="service-bonus-option selected" data-value="no">
                                        <input type="radio" name="has_service_bonus" id="service-bonus-no"
                                            value="no" checked>
                                        <span class="service-bonus-label">
                                            <i class="fas fa-times-circle"></i> No
                                        </span>
                                    </div>
                                    <div class="service-bonus-option" data-value="si">
                                        <input type="radio" name="has_service_bonus" id="service-bonus-yes"
                                            value="si">
                                        <span class="service-bonus-label">
                                            <i class="fas fa-check-circle"></i> Sí
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- FIN: CAMPOS CONDICIONALES A TRABAJO EN POZO (P) --}}
                    </div>

                    {{-- JSON DATA PARA JAVASCRIPT --}}
                    <script id="service-data" type="application/json">
                    @json($services->toArray())
                </script>

                    {{-- PESTAÑA DE SERVICIO --}}
                    <div class="tab-content" id="service-tab">
                        <div class="form-group">
                            <label for="work-type">Tipo de Trabajo</label>
                            <div class="work-type-options" id="work-type-options">
                                <div class="work-type-option" data-value="Tierra">
                                    <input type="radio" name="work-type" id="work-land" value="Tierra">
                                    <span class="work-type-label">
                                        <i class="fas fa-mountain"></i> Tierra
                                    </span>
                                </div>
                                <div class="work-type-option" data-value="Marina">
                                    <input type="radio" name="work-type" id="work-marine" value="Marina">
                                    <span class="work-type-label">
                                        <i class="fas fa-water"></i> Marina
                                    </span>
                                </div>
                            </div>
                            <div class="error-message" id="work-type-error">Debes seleccionar un tipo de trabajo</div>
                        </div>

                        <div class="form-group">
                            <label for="service-type">Tipo de Servicio</label>
                            <select id="service-type" class="select-custom" disabled>
                                <option value="">Seleccionar tipo de servicio...</option>
                            </select>
                            <div class="error-message" id="service-type-error">Debes seleccionar un tipo de servicio</div>
                        </div>

                        <div class="form-group">
                            <label for="service-performed">Servicio Realizado</label>
                            <select id="service-performed" class="select-custom" disabled>
                                <option value="">Seleccionar servicio realizado...</option>
                            </select>
                            <div class="error-message" id="service-performed-error">Debes seleccionar un servicio
                                realizado
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="service">Servicio</label>
                            <select id="service" class="select-custom" name="service_name" disabled>
                                <option value="">Seleccionar servicio...</option>
                            </select>
                            <div class="error-message" id="service-error">Debes seleccionar un servicio</div>
                        </div>
                        <div class="form-group" id="service-amount-group" style="display: none;">
                            <label for="service-amount">Monto del Servicio</label>
                            <div class="input-with-icon">
                                <i class="fas fa-dollar-sign"></i>
                                <input type="text" id="service-amount" class="input-custom" value="" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="payroll-period">Periodo de Quincena del Servicio</label>
                            <select id="payroll-period" class="select-custom">
                                {{-- Opciones cargadas por JS --}}
                            </select>
                            <small class="form-help">Selecciona en caso el servicio se realizó en la quincena
                                anterior</small>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-outline" id="cancel-activity">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button class="btn btn-primary" id="save-activity">
                            <i class="fas fa-save"></i> Guardar
                            <div class="loading-spinner"></div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- INCLUSIÓN DEL SCRIPT DE JAVASCRIPT --}}
        <script src="{{ asset('assets/js/recursoshumanos/loadchart/calendar.js') }}"></script>

    </div>
 -->


    @include('modulos.recursoshumanos.sistemas.loadchart.calendar_partial')
@endsection
