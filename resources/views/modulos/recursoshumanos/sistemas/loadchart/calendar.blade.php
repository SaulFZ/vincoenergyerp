@extends('modulos.recursoshumanos.sistemas.loadchart.index')

@section('content')
<div id="normalView">
    <div class="content-layout">
        <div class="employee-details" id="employeeDetails">
            <div class="employee-header">
                <div class="employee-photo-container">
                    <img src="{{ $employee_photo }}" alt="Foto de perfil" class="employee-photo">
                </div>
                <h2>Mis Datos</h2>
            </div>
            <div class="employee-info">
                <div class="info-group">
                    <h3>Nombre</h3>
                    <p>{{ $employee->full_name ?? 'N/A' }}</p>
                </div>
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
                <div class="info-group">
                    <h3>Vacaciones</h3>
                    <p class="vacation-days">12 días</p>
                </div>
                <div class="info-group">
                    <h3>Días de Descanso</h3>
                    <p>6 días</p>
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
                    <tr>
                        @foreach ($calendarDays as $day)
                            @php
                                $isCurrentMonth = $day['current_month'];
                                $isToday = $day['date'] == date('Y-m-d');
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
                            <td class="{{ !$isCurrentMonth ? 'other-month' : '' }} {{ $isToday ? 'current-d' : '' }}">
                                <span class="day-number">{{ $day['day'] }}</span>
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
                    <div>Home Office</div>
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
                    <i class="fa-regular fa-hourglass-half legend-pending"></i>
                    <div>Pendiente</div>
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
                    <i class="fas fa-exclamation-triangle legend-not-approved"></i>
                    <div>No aprobado</div>
                </div>
                <div class="legend-item">
                    <i class="fas fa-flag legend-payroll-start-1"></i>
                    <div>Inicio Q1</div>
                </div>
                <div class="legend-item">
                    <i class="fas fa-flag payroll-end"></i>
                    <div>Fin Quincena</div>
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
                <button class="tab-btn" data-tab="service">Servicio</button>
            </div>

            <div class="tab-content active" id="activity-tab">
                <div class="form-group">
                    <label for="activity-date">Fecha</label>
                    <div class="input-with-icon">
                        <i class="far fa-calendar-alt"></i>
                        <input type="text" id="activity-date" class="input-custom" value="" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label for="activity-type">Tipo de Actividad</label>

                    <div class="custom-select" id="activity-type-select">
                        <div class="select-header" id="activity-type-header">
                            <span class="placeholder">Seleccionar actividad...</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>

                        <div class="select-options" id="activity-type-options">
                            <div class="activity-option" data-value="B">
                                <div class="option-content">
                                    <div class="color-indicator" style="background-color: var(--work-base);"></div>
                                    <div class="activity-label">Trabajo en Base</div>
                                </div>
                                <div class="activity-code" style="background-color: var(--work-base); color: #fff;">B
                                </div>
                            </div>

                            <div class="activity-option" data-value="P">
                                <div class="option-content">
                                    <div class="color-indicator" style="background-color: var(--work-well);"></div>
                                    <div class="activity-label">Trabajo en Pozo</div>
                                </div>
                                <div class="activity-code" style="background-color: var(--work-well); color: #fff;">P
                                </div>
                            </div>

                            <div class="activity-option" data-value="C">
                                <div class="option-content">
                                    <div class="color-indicator" style="background-color: var(--commissioned);"></div>
                                    <div class="activity-label">Comisionado</div>
                                </div>
                                <div class="activity-code"
                                    style="background-color: var(--commissioned); color: #fff;">C</div>
                            </div>

                            <div class="activity-option" data-value="H">
                                <div class="option-content">
                                    <div class="color-indicator" style="background-color: var(--home-office);"></div>
                                    <div class="activity-label">Home Office</div>
                                </div>
                                <div class="activity-code" style="background-color: var(--home-office); color: #fff;">
                                    H</div>
                            </div>

                            <div class="activity-option" data-value="V">
                                <div class="option-content">
                                    <div class="color-indicator" style="background-color: var(--traveling);"></div>
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
                                <div class="activity-code" style="background-color: var(--rest); color: #fff;">D</div>
                            </div>

                            <div class="activity-option" data-value="VAC">
                                <div class="option-content">
                                    <div class="color-indicator" style="background-color: var(--vacation);"></div>
                                    <div class="activity-label">Vacaciones</div>
                                </div>
                                <div class="activity-code" style="background-color: var(--vacation); color: #fff;">VAC
                                </div>
                            </div>

                            <div class="activity-option" data-value="E">
                                <div class="option-content">
                                    <div class="color-indicator" style="background-color: var(--training);"></div>
                                    <div class="activity-label">Entrenamiento</div>
                                </div>
                                <div class="activity-code" style="background-color: var(--training); color: #fff;">E
                                </div>
                            </div>

                            <div class="activity-option" data-value="M">
                                <div class="option-content">
                                    <div class="color-indicator" style="background-color: var(--medical);"></div>
                                    <div class="activity-label">Médico</div>
                                </div>
                                <div class="activity-code" style="background-color: var(--medical); color: #fff;">M
                                </div>
                            </div>
                        </div>
                    </div>

                    <select id="activity-type" name="activity_type" style="display:none;">
                        <option value="">Seleccionar actividad...</option>
                        <option value="B">B - Trabajo en Base</option>
                        <option value="P">P - Trabajo en Pozo</option>
                        <option value="C">C - Comisionado</option>
                        <option value="H">H - Home Office</option>
                        <option value="V">V - Viaje</option>
                        <option value="D">D - Descanso</option>
                        <option value="VAC">VAC - Vacaciones</option>
                        <option value="E">E - Entrenamiento</option>
                        <option value="M">M - Médico</option>
                    </select>

                    <div class="error-message" id="activity-type-error">Debes seleccionar un tipo de actividad</div>
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
                </div>
            </div>

            <script id="service-data" type="application/json">
                @json($services->toArray())
            </script>

            <div class="tab-content" id="service-tab">
                <div class="form-group">
                    <label for="work-type">Tipo de Trabajo</label>
                    <div class="work-type-options">
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
                    <div class="error-message" id="service-performed-error">Debes seleccionar un servicio realizado
                    </div>
                </div>

                <div class="form-group">
                    <label for="service">Servicio</label>
                    <select id="service" class="select-custom" name="service_name" disabled>+
                        <option value="">Seleccionar servicio...</option>
                    </select>
                    <div class="error-message" id="service-error">Debes seleccionar un servicio</div>
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
    <script src="{{ asset('assets/js/recursoshumanos/loadchart/calendar.js') }}"></script>
@endsection
