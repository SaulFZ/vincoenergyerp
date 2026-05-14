{{-- Lee la variable $isGuardia que viene del CalendarController --}}
<input type="hidden" id="is_guardia_user" value="{{ isset($isGuardia) && $isGuardia ? '1' : '0' }}">
{{-- Lee la variable $isSuministro que viene del CalendarController --}}
<input type="hidden" id="is_suministro_user" value="{{ isset($isSuministro) && $isSuministro ? '1' : '0' }}">
{{-- Variable para requerir descripcion de base --}}
<input type="hidden" id="requires_base_description"
    value="{{ isset($requiresBaseDescription) && $requiresBaseDescription ? '1' : '0' }}">

<div id="normalView">
    <div class="content-layout">
        <div class="employee-details" id="employeeDetails">
            <div class="employee-header">
                <div class="employee-photo-container">
                    {{-- ✅ La URL viene formateada perfectamente desde el Controller --}}
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

                {{-- NUEVO: ÁREA Y DEPARTAMENTO --}}
                <div class="info-group">
                    <h3>Área</h3>
                    <p>{{ $employee->area->name ?? 'N/A' }}</p>
                </div>

                {{-- Mostramos el departamento SOLO si existe y tiene un nombre válido (ej. evitamos ID 5 que viene en blanco) --}}
                @php $deptoObj = $employee->department()->first(); @endphp
                @if($deptoObj && !empty(trim($deptoObj->name)))
                    <div class="info-group">
                        <h3>Departamento</h3>
                        <p>{{ $deptoObj->name }}</p>
                    </div>
                @endif
                {{-- FIN NUEVO --}}

                <div class="info-group">
                    <h3>Puesto</h3>
                    <p>{{ $employee->job_title ?? 'N/A' }}</p>
                </div>
                <div class="info-group">
                    <h3>Fecha de Ingreso</h3>
                    <p>{{ $hire_date }}</p>
                </div>
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

        <div class="load-chart-container" id="loadChart">
            <div class="chart-header">
                <h2><i class="fas fa-chart-bar"></i> Load Chart - {{ $monthName }} {{ $currentYear }}</h2>
                <div class="chart-actions">
                    <div class="month-navigation">
                        <button id="prev-month"><i class="fas fa-chevron-left"></i></button>
                        <span data-month="{{ $currentMonth }}" data-year="{{ $currentYear }}">{{ $monthName }}
                            {{ $currentYear }}</span>
                        <button id="next-month"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    @if (!isset($isForModal) || !$isForModal)
                        @if (\App\Helpers\PermissionHelper::hasDirectPermission('ver_loadchart'))
                            <button class="btn btn-orange" id="approve-loadchart" data-route="/approval">
                                <i class="fas fa-check-circle"></i> Aprobar Loadchart
                            </button>
                        @endif
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
                    <tr>
                        @foreach ($calendarDays as $day)
                            @php
                                $isCurrentMonth = $day['current_month'];
                                $isToday = $day['date'] == date('Y-m-d');

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

                                @if (isset($day['is_holiday']) && $day['is_holiday'])
                                    @if ($day['holiday_icon_type'] == 'christmas_tree')
                                        <img src="https://img.icons8.com/external-victoruler-flat-victoruler/64/external-christmas-tree-christmas-victoruler-flat-victoruler-1.png"
                                            alt="Árbol de Navidad" class="holiday-icon"
                                            title="{{ $day['holiday_name'] }}">
                                    @else
                                        <img src="https://img.icons8.com/skeuomorphism/32/event.png" alt="Día Festivo"
                                            class="holiday-icon" title="{{ $day['holiday_name'] }}">
                                    @endif
                                @endif

                                @if ($isPayrollStart1)
                                    <i class="fas fa-flag payroll-icon payroll-start-1" title="Inicio Quincena 1"></i>
                                @endif
                                @if ($isPayrollEnd1)
                                    <i class="fas fa-flag payroll-icon payroll-end" title="Fin Quincena 1"></i>
                                @endif
                                @if ($isPayrollStart2)
                                    <i class="fas fa-flag payroll-icon payroll-start-2" title="Inicio Quincena 2"></i>
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
            <div class="loading-overlay" id="calendarLoadingOverlay">
                <div class="loading-spinner-lg"></div>
                <div class="loading-message">Cargando datos del calendario...</div>
            </div>
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
                    <div>Trabajo en Casa</div>
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
                    <i class="fas fa-flag payroll-start-1"></i>
                    <div>Inicio Q1</div>
                </div>
                <div class="legend-item">
                    <i class="fas fa-flag payroll-start-2"></i>
                    <div>Inicio Q2</div>
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
                                <div class="activity-code" style="background-color: var(--work-base); color: #fff;">B
                                </div>
                            </div>

                            <div class="activity-option" data-value="P">
                                <div class="option-content">
                                    <div class="color-indicator" style="background-color: var(--work-well);">
                                    </div>
                                    <div class="activity-label">Trabajo en Pozo</div>
                                </div>
                                <div class="activity-code" style="background-color: var(--work-well); color: #fff;">P
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

                            <div class="activity-option" data-value="TC">
                                <div class="option-content">
                                    <div class="color-indicator" style="background-color: var(--home-office);">
                                    </div>
                                    <div class="activity-label">Trabajo en Casa</div>
                                </div>
                                <div class="activity-code" style="background-color: var(--home-office); color: #fff;">
                                    TC</div>
                            </div>

                            <div class="activity-option" data-value="V">
                                <div class="option-content">
                                    <div class="color-indicator" style="background-color: var(--traveling);">
                                    </div>
                                    <div class="activity-label">Viaje</div>
                                </div>
                                <div class="activity-code" style="background-color: var(--traveling); color: #fff;">V
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
                                    <div class="color-indicator" style="background-color: var(--vacation);">
                                    </div>
                                    <div class="activity-label">Vacaciones</div>
                                </div>
                                <div class="activity-code" style="background-color: var(--vacation); color: #fff;">VAC
                                </div>
                            </div>

                            <div class="activity-option" data-value="E">
                                <div class="option-content">
                                    <div class="color-indicator" style="background-color: var(--training);">
                                    </div>
                                    <div class="activity-label">Entrenamiento</div>
                                </div>
                                <div class="activity-code" style="background-color: var(--training); color: #fff;">E
                                </div>
                            </div>

                            <div class="activity-option" data-value="M">
                                <div class="option-content">
                                    <div class="color-indicator" style="background-color: var(--medical);">
                                    </div>
                                    <div class="activity-label">Médico</div>
                                </div>
                                <div class="activity-code" style="background-color: var(--medical); color: #fff;">
                                    M
                                </div>
                            </div>

                            <div class="activity-option" data-value="A">
                                <div class="option-content">
                                    <div class="color-indicator" style="background-color: var(--absence);">
                                    </div>
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
                                <div class="activity-code" style="background-color: var(--permission); color: #fff;">
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

                    <select id="activity-type" name="activity_type" style="display:none;">
                        <option value="">Seleccionar actividad...</option>
                        <option value="B">B - Trabajo en Base</option>
                        <option value="P">P - Trabajo en Pozo</option>
                        <option value="C">C - Comisionado</option>
                        <option value="TC">TC - Trabajo en Casa</option>
                        <option value="V">V - Viaje</option>
                        <option value="D">D - Descanso</option>
                        <option value="VAC">VAC - Vacaciones</option>
                        <option value="E">E - Entrenamiento</option>
                        <option value="M">M - Médico</option>
                        <option value="A">A - Ausencia</option>
                        <option value="PE">PE - Permiso</option>
                        <option value="N">N - Ninguna</option>
                    </select>

                    <div class="error-message" id="activity-type-error">Debes seleccionar un tipo de actividad
                    </div>
                    <div class="error-message" id="vacation-balance-error" style="display:none;">No tienes
                        días de vacaciones disponibles.</div>

                </div>

                <div class="form-group" id="base-activity-description-group" style="display: none;">
                    <label for="base-activity-description">Descripción de Actividad en Base</label>
                    <select id="base-activity-description" name="base_activity_description" class="select-custom">
                        <option value="Actividad en base">Actividad en base</option>
                        <option value="">Seleccionar actividad específica...</option>
                        <option value="Paso de cable">Paso de cable</option>
                        <option value="Pruebas de presion para los ECP">Pruebas de presión para los ECP</option>
                        <option value="Pintura y soldaduras en area de taller">Pintura y soldaduras en área de taller
                        </option>
                        <option value="Movimiento o eventos con gerencias">Movimiento o eventos con gerencias</option>
                        <option value="Mantenimiento a polvorin Vinco">Mantenimiento a polvorín Vinco</option>
                    </select>
                    <div class="error-message" id="base-activity-description-error">Debes seleccionar una descripción
                        de la actividad</div>
                </div>

                <div id="guardia-activity-groups" style="display: none;">
                    <div class="form-group" id="activity-matutina-group">
                        <label>Actividad Matutina(12HR)</label>
                        <div class="custom-select" id="activity-matutina-select">
                            <div class="select-header" id="activity-matutina-header">
                                <span class="placeholder">Seleccionar...</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="select-options" id="activity-matutina-options">
                                <div class="activity-option" data-value="B">
                                    <div class="option-content">
                                        <div class="color-indicator" style="background-color: var(--work-base);">
                                        </div>
                                        <div class="activity-label">Trabajo en Base</div>
                                    </div>
                                    <div class="activity-code"
                                        style="background-color: var(--work-base); color: #fff;">B</div>
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
                                        style="background-color: var(--vacation); color: #fff;">VAC</div>
                                </div>
                                <div class="activity-option" data-value="M">
                                    <div class="option-content">
                                        <div class="color-indicator" style="background-color: var(--medical);"></div>
                                        <div class="activity-label">Médico</div>
                                    </div>
                                    <div class="activity-code" style="background-color: var(--medical); color: #fff;">
                                        M</div>
                                </div>
                                <div class="activity-option" data-value="A">
                                    <div class="option-content">
                                        <div class="color-indicator" style="background-color: var(--absence);"></div>
                                        <div class="activity-label">Ausencia</div>
                                    </div>
                                    <div class="activity-code" style="background-color: var(--absence); color: #fff;">
                                        A</div>
                                </div>
                                <div class="activity-option" data-value="PE">
                                    <div class="option-content">
                                        <div class="color-indicator" style="background-color: var(--permission);">
                                        </div>
                                        <div class="activity-label">Permiso</div>
                                    </div>
                                    <div class="activity-code"
                                        style="background-color: var(--permission); color: #fff;">PE</div>
                                </div>
                                <div class="activity-option" data-value="N">
                                    <div class="option-content">
                                        <div class="color-indicator" style="background-color: #ccc;"></div>
                                        <div class="activity-label">Ninguna</div>
                                    </div>
                                    <div class="activity-code" style="background-color: #ccc; color: #333;">N</div>
                                </div>
                            </div>
                        </div>
                        <select id="activity-matutina" style="display:none;">
                            <option value="">Seleccionar...</option>
                            <option value="B">B</option>
                            <option value="D">D</option>
                            <option value="VAC">VAC</option>
                            <option value="M">M</option>
                            <option value="A">A</option>
                            <option value="PE">PE</option>
                            <option value="N">N</option>
                        </select>
                        <div class="error-message" id="activity-matutina-error" style="display:none;">Debes
                            seleccionar una actividad matutina</div>
                    </div>

                    <div class="form-group" id="activity-vespertina-group">
                        <label>Actividad Vespertina(12HR)</label>
                        <div class="custom-select" id="activity-vespertina-select">
                            <div class="select-header" id="activity-vespertina-header">
                                <span class="placeholder">Seleccionar...</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="select-options" id="activity-vespertina-options">
                                <div class="activity-option" data-value="B">
                                    <div class="option-content">
                                        <div class="color-indicator" style="background-color: var(--work-base);">
                                        </div>
                                        <div class="activity-label">Trabajo en Base</div>
                                    </div>
                                    <div class="activity-code"
                                        style="background-color: var(--work-base); color: #fff;">B</div>
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
                                        style="background-color: var(--vacation); color: #fff;">VAC</div>
                                </div>
                                <div class="activity-option" data-value="M">
                                    <div class="option-content">
                                        <div class="color-indicator" style="background-color: var(--medical);"></div>
                                        <div class="activity-label">Médico</div>
                                    </div>
                                    <div class="activity-code" style="background-color: var(--medical); color: #fff;">
                                        M</div>
                                </div>
                                <div class="activity-option" data-value="A">
                                    <div class="option-content">
                                        <div class="color-indicator" style="background-color: var(--absence);"></div>
                                        <div class="activity-label">Ausencia</div>
                                    </div>
                                    <div class="activity-code" style="background-color: var(--absence); color: #fff;">
                                        A</div>
                                </div>
                                <div class="activity-option" data-value="PE">
                                    <div class="option-content">
                                        <div class="color-indicator" style="background-color: var(--permission);">
                                        </div>
                                        <div class="activity-label">Permiso</div>
                                    </div>
                                    <div class="activity-code"
                                        style="background-color: var(--permission); color: #fff;">PE</div>
                                </div>
                                <div class="activity-option" data-value="N">
                                    <div class="option-content">
                                        <div class="color-indicator" style="background-color: #ccc;"></div>
                                        <div class="activity-label">Ninguna</div>
                                    </div>
                                    <div class="activity-code" style="background-color: #ccc; color: #333;">N</div>
                                </div>
                            </div>
                        </div>
                        <select id="activity-vespertina" style="display:none;">
                            <option value="">Seleccionar...</option>
                            <option value="B">B</option>
                            <option value="D">D</option>
                            <option value="VAC">VAC</option>
                            <option value="M">M</option>
                            <option value="A">A</option>
                            <option value="PE">PE</option>
                            <option value="N">N</option>
                        </select>
                        <div class="error-message" id="activity-vespertina-error" style="display:none;">Debes
                            seleccionar una actividad vespertina</div>
                    </div>

                    <div class="form-group" id="guardia-bonus-group" style="display: none;"> <label>Bono (Solo en
                            Base)</label>
                        <div class="custom-select" id="guardia-bonus-custom-select">
                            <div class="select-header" id="guardia-bonus-header">
                                <span class="placeholder">Seleccionar bono...</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="select-options" id="guardia-bonus-options">
                                @if (isset($guardiaBonuses))
                                    @foreach ($guardiaBonuses as $bonus)
                                        <div class="activity-option bonus-option"
                                            data-value="{{ $bonus->bonus_identifier }}"
                                            data-amount="{{ $bonus->amount }}"
                                            data-currency="{{ $bonus->currency }}">
                                            <div class="option-content">
                                                <div class="activity-label">{{ $bonus->bonus_type }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                                <div class="activity-option bonus-option" data-value="" data-amount="0"
                                    data-currency="MXN">
                                    <div class="option-content">
                                        <div class="activity-label" style="color: #999;">Ninguno</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="guardia-field-bonus" value="">

                        <div id="guardia-bonus-quantity-container"
                            style="display: none; margin-top: 15px; padding-left: 5px;">
                            <label for="guardia-bonus-quantity"
                                style="font-size: 0.9em; font-weight: bold; color: #555;">Cantidad Realizada (Unidades,
                                Metros)</label>
                            <input type="number" id="guardia-bonus-quantity" class="input-custom" value="1"
                                min="1" step="1" style="width: 100%;">
                        </div>
                    </div>
                </div>

                <div class="form-group" id="well-name-field" style="display: none; position: relative;">
                    <label for="well-name">Buscar el Nombre del Pozo</label>
                    <div class="input-with-icon">
                        <i class="fas fa-oil-well"></i>
                        <input type="text" id="well-name" class="input-custom"
                            placeholder="Ingrese nombre del pozo" autocomplete="off">
                    </div>

                    <ul id="well-search-results" class="autocomplete-list" style="display: none;"></ul>

                    <div class="error-message" id="well-name-error">Debes ingresar el nombre del pozo</div>
                </div>

                <div class="form-group" id="commissioned-field" style="display: none;">
                    <label for="commissioned-select">Área Comisionada</label>
                    <select id="commissioned-select" class="select-custom">
                        <option value="">Seleccionar área...</option>
                        @if (isset($areasList))
                            @foreach ($areasList as $area)
                                {{-- FILTRO: NO MOSTRAR EL ÁREA DEL EMPLEADO ACTUAL --}}
                                @if ($area != ($employee->area->name ?? ''))
                                    <option value="{{ $area }}">{{ $area }}</option>
                                @endif
                            @endforeach
                        @endif
                    </select>
                    <div class="error-message" id="commissioned-error">Debes seleccionar un área comisionada
                    </div>
                </div>

                {{-- NUEVO SELECTOR DE TIPO DE ACTIVIDAD EN COMISIONADO --}}
                <div class="form-group" id="commissioned-activity-type-field" style="display: none;">
                    <label for="commissioned-activity-type">Tipo de Actividad en Comisionado</label>
                    <select id="commissioned-activity-type" class="select-custom">
                        <option value="">Seleccionar tipo de actividad...</option>
                        <option value="Entrega de Documentación">Entrega de Documentación</option>
                        <option value="Traslado de personal">Traslado de personal</option>
                        <option value="Movimiento de gerencia">Movimiento de gerencia</option>
                    </select>
                    <div class="error-message" id="commissioned-activity-type-error">Debes seleccionar el tipo de
                        actividad en comisionado</div>
                </div>

                <div class="form-group" id="travel-destination-field" style="display: none;">
                    <label for="travel-destination">Destino del Viaje</label>
                    <div class="input-with-icon">
                        <i class="fas fa-map-marker-alt"></i>
                        <input type="text" id="travel-destination" class="input-custom"
                            placeholder="Ingrese el destino">
                    </div>
                    <div class="error-message" id="travel-destination-error">Debes ingresar el destino del
                        viaje
                    </div>
                </div>

                <div class="form-group" id="travel-reason-field" style="display: none;">
                    <label for="travel-reason">Motivo del Viaje</label>
                    <div class="input-with-icon">
                        <i class="fas fa-route"></i>
                        <input type="text" id="travel-reason" class="input-custom"
                            placeholder="Ingrese el motivo">
                    </div>
                    <div class="error-message" id="travel-reason-error">Debes ingresar el motivo del viaje
                    </div>
                </div>

                {{-- NUEVOS CAMPOS DE CONTINUACIÓN Y SUMINISTRO --}}
                <div class="form-group" id="continuation-field"
                    style="display: none; margin-bottom: 15px; background: #eef2f5; padding: 10px; border-radius: 6px;">
                    <label class="checkbox-inline"
                        style="margin: 0; font-weight: bold; color: #333; cursor: pointer;">
                        <input type="checkbox" id="is-continuation" style="margin-right: 8px;">
                        <i class="fas fa-link"></i> Es continuación de viaje del día anterior
                    </label>
                </div>

                {{-- 🛠️ AHORA ES UN SELECTOR DINÁMICO --}}
                <div class="form-group" id="contract-number-field" style="display: none;">
                    <label for="contract-number">Número de Contrato (Suministro)</label>
                    <div class="input-with-icon">
                        <i class="fas fa-file-contract"
                            style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #888;"></i>
                        <select id="contract-number" class="select-custom" style="padding-left: 35px;">
                            <option value="">Seleccionar contrato...</option>
                            @if (isset($supplyContracts))
                                @foreach ($supplyContracts as $contract)
                                    <option value="{{ $contract->number }}">
                                        {{ $contract->number }} @if ($contract->short_name)
                                        @endif
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="error-message" id="contract-number-error">Debes seleccionar el número de contrato
                    </div>
                </div>

                <div class="form-group" id="travel-service-type-field" style="display: none;">
                    <label for="travel-service-type">Tipo de Servicio (Suministro)</label>
                    <select id="travel-service-type" class="select-custom">
                        <option value="">Seleccionar tipo de servicio...</option>
                        <option value="Entrega">Entrega</option>
                        <option value="Documentación">Documentación</option>
                        <option value="Soporte Técnico">Soporte Técnico</option>
                        <option value="Traslado (Solo Viaje)">Traslado (Solo Viaje)</option>
                    </select>
                    <div class="error-message" id="travel-service-type-error">Debes seleccionar el tipo de servicio
                    </div>
                </div>
                {{-- FIN NUEVOS CAMPOS --}}

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
                        <div class="error-message" id="field-bonus-error">
                            Debes seleccionar un bono de campo.
                        </div>
                    </div>
                    @if (isset($showServiceBonusOption) && $showServiceBonusOption)
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
                    @endif
                </div>
            </div>
            <script id="service-data" type="application/json">
        @json($services->toArray())
    </script>

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
                    <div class="error-message" id="service-type-error">Debes seleccionar un tipo de servicio
                    </div>
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

                <div class="form-group" id="service-real-date-group">
                    <label for="service-real-date">Fecha de Realización del Servicio</label>
                    <div class="input-with-icon">
                        <i class="far fa-calendar-alt"></i>
                        <input type="text" id="service-real-date" class="input-custom date-selector-flat"
                            placeholder="Seleccionar fecha del servicio" readonly>
                    </div>
                    <small class="form-help">Fecha real en la que el servicio se llevó a cabo. Por defecto se
                        carga el día seleccionado en el calendario. Cámbiala solo si el servicio fue en otra
                        fecha anterior.
                    </small>
                    <div class="error-message" id="service-real-date-error" style="display:none;">Debes
                        seleccionar la fecha real del servicio.
                    </div>
                </div>

                <div class="form-group" style="display: none;">
                    <label for="payroll-period">Periodo de Quincena del Servicio</label>
                    <select id="payroll-period" class="select-custom">
                        <option value="current">Quincena Actual</option>
                    </select>
                    <small class="form-help">Este campo está obsoleto.</small>
                </div>

            </div>
        </div>

        <div class="form-actions" style="@if (isset($isForModal) &&
                $isForModal &&
                !\App\Helpers\PermissionHelper::hasDirectPermission('editar_loadchart_empleado')) display: none; @endif">
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

