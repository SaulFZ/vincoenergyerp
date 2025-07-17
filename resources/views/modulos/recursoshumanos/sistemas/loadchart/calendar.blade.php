@extends('modulos.recursoshumanos.sistemas.loadchart.index')

@section('content')
    <div id="normalView">
        <div class="content-layout">
            <!-- Employee Details -->
            <div class="employee-details" id="employeeDetails">
                <div class="employee-header">
                    <div class="employee-photo-container">
                        <img src="{{ asset('assets/img/perfil.png') }}" alt="" class="employee-photo">
                    </div>
                    <h2>Mis Datos</h2>
                </div>
                <div class="employee-info">
                    <div class="info-group">
                        <h3>Nombre</h3>
                        <p>Saul Falcon</p>
                    </div>
                    <div class="info-group">
                        <h3>Número de empleado</h3>
                        <p>V10056</p>
                    </div>
                    <div class="info-group">
                        <h3>Departamento</h3>
                        <p>Administración</p>
                    </div>
                    <div class="info-group">
                        <h3>Puesto</h3>
                        <p>Desarrollador</p>
                    </div>
                    <div class="info-group">
                        <h3>Fecha de Ingreso</h3>
                        <p>15-Mar-2020</p>
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

            <!-- Load Chart Section -->
            <div class="load-chart-container" id="loadChart">
                <div class="chart-header">
                    <h2><i class="fas fa-chart-bar"></i> Load Chart - Junio 2025</h2>
                    <div class="chart-actions">
                        <div class="month-navigation">
                            <button id="prev-month"><i class="fas fa-chevron-left"></i></button>
                            <span>Junio 2025</span>
                            <button id="next-month"><i class="fas fa-chevron-right"></i></button>
                        </div>
                        <button class="btn btn-orange" id="approve-loadchart" data-route="/approval">
                            <i class="fas fa-check-circle"></i> Aprobar Loadchart
                        </button>
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
                            <td class="other-month"><span class="day-number">26</span>
                                <span class="activity-tag work-base">Base</span>
                                <i class="fas fa-exclamation-triangle status-icon"></i>
                            </td>
                            <td class="other-month"><span class="day-number">27</span>
                                <span class="activity-tag work-base">Base</span>
                                <i class="fas fa-exclamation-triangle status-icon"></i>
                            </td>
                            <td class="other-month"><span class="day-number">28</span>
                                <span class="activity-tag work-base">Base</span>
                                <i class="fas fa-exclamation-triangle status-icon"></i>
                            </td>
                            <td class="other-month"><span class="day-number">29</span>
                                <span class="activity-tag work-base">Base</span>
                                <i class="fas fa-exclamation-triangle status-icon"></i>
                            </td>
                            <td class="other-month"><span class="day-number">30</span>
                                <span class="activity-tag work-base">Base</span>
                                <i class="fas fa-exclamation-triangle status-icon"></i>
                            </td>
                            <td class="other-month"><span class="day-number">31</span>
                                <span class="activity-tag rest">Descanso</span>
                                <i class="fas fa-exclamation-triangle status-icon"></i>
                            </td>
                            <td><span class="day-number">1</span>
                                <span class="activity-tag rest">Descanso</span>
                                <i class="fas fa-lock status-icon"></i>
                                <i class="fas fa-flag payroll-icon payroll-start-1" title="Inicio Quincena 1"></i>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="day-number">2</span>
                                <span class="activity-tag work-well">Pozo</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">3</span>
                                <span class="activity-tag work-well">Pozo</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">4</span>
                                <span class="activity-tag work-well">Pozo</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">5</span>
                                <span class="activity-tag work-well">Pozo</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">6</span>
                                <span class="activity-tag work-well">Pozo</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">7</span>
                                <span class="activity-tag rest">Descanso</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">8</span>
                                <span class="activity-tag rest">Descanso</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="day-number">9</span>
                                <span class="activity-tag work-base">Base</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">10</span>
                                <span class="activity-tag work-base">Base</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">11</span>
                                <span class="activity-tag work-base">Base</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">12</span>
                                <span class="activity-tag work-base">Base</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">13</span>
                                <span class="activity-tag work-base">Base</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">14</span>
                                <span class="activity-tag rest">Descanso</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">15</span>
                                <span class="activity-tag rest">Descanso</span>
                                <i class="fas fa-lock status-icon"></i>
                                <i class="fas fa-flag payroll-icon payroll-end" title="Fin Quincena"></i>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="day-number">16</span>
                                <span class="activity-tag work-base">Base</span>
                                <i class="fas fa-lock status-icon"></i>
                                <i class="fas fa-flag payroll-icon payroll-start-2" title="Inicio Quincena 2"></i>
                            </td>
                            <td><span class="day-number">17</span>
                                <span class="activity-tag work-base">Base</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">18</span>
                                <span class="activity-tag training">Entrenamiento</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td class="current-d"><span class="day-number">19</span>
                                <span class="activity-tag training">Entrenamiento</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">20</span>
                                <span class="activity-tag training">Entrenamiento</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">21</span>
                                <span class="activity-tag rest">Descanso</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">22</span>
                                <span class="activity-tag rest">Descanso</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="day-number">23</span>
                                <span class="activity-tag training">Entrenamiento</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">24</span>
                                <span class="activity-tag work-base">Base</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">25</span>
                                <span class="activity-tag medical">Médico</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">26</span>
                                <span class="activity-tag work-base">Base</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">27</span>
                                <span class="activity-tag work-base">Base</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">28</span>
                                <span class="activity-tag rest">Descanso</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                            <td><span class="day-number">29</span>
                                <span class="activity-tag rest">Descanso</span>
                                <i class="fas fa-lock status-icon"></i>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="day-number">30</span>
                                <span class="activity-tag work-base">Base</span>
                                <i class="fas fa-lock status-icon"></i>
                                <i class="fas fa-flag payroll-icon payroll-end" title="Fin Quincena"></i>
                            </td>
                            <td class="other-month"><span class="day-number">1</span>
                                <span class="activity-tag vacation">Vacaciones</span>
                                <i class="fas fa-exclamation-triangle status-icon"></i>
                            </td>
                            <td class="other-month"><span class="day-number">2</span>
                                <span class="activity-tag vacation">Vacaciones</span>
                                <i class="fas fa-exclamation-triangle status-icon"></i>
                            </td>
                            <td class="other-month"><span class="day-number">3</span>
                                <span class="activity-tag vacation">Vacaciones</span>
                                <i class="fas fa-exclamation-triangle status-icon"></i>
                            </td>
                            <td class="other-month"><span class="day-number">4</span>
                                <span class="activity-tag vacation">Vacaciones</span>
                                <i class="fas fa-exclamation-triangle status-icon"></i>
                            </td>
                            <td class="other-month"><span class="day-number">5</span>
                                <span class="activity-tag vacation">Vacaciones</span>
                                <i class="fas fa-exclamation-triangle status-icon"></i>
                            </td>
                            <td class="other-month"><span class="day-number">6</span>
                                <span class="activity-tag vacation">Vacaciones</span>
                                <i class="fas fa-exclamation-triangle status-icon"></i>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Activity Legend -->
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
                        <i class="fas fa-flag legend-payroll-start-2"></i>
                        <div>Inicio Q2</div>
                    </div>
                    <div class="legend-item">
                        <i class="fas fa-flag legend-payroll-end"></i>
                        <div>Fin Quincena</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- Activity Form Modal -->
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

                <!-- Actividad Tab -->
                <div class="tab-content active" id="activity-tab">
                    <div class="form-group">
                        <label for="activity-date">Fecha</label>
                        <div class="input-with-icon">
                            <i class="far fa-calendar-alt"></i>
                            <input type="text" id="activity-date" class="input-custom" value="19 de Junio, 2025"
                                readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="activity-type">Tipo de Actividad</label>
                        <div class="custom-select">
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
                                    <div class="activity-code">B</div>
                                </div>
                                <div class="activity-option" data-value="P">
                                    <div class="option-content">
                                        <div class="color-indicator" style="background-color: var(--work-well);">
                                        </div>
                                        <div class="activity-label">Trabajo en Pozo</div>
                                    </div>
                                    <div class="activity-code">P</div>
                                </div>
                                <div class="activity-option" data-value="D">
                                    <div class="option-content">
                                        <div class="color-indicator" style="background-color: var(--rest);"></div>
                                        <div class="activity-label">Descanso</div>
                                    </div>
                                    <div class="activity-code">D</div>
                                </div>
                                <div class="activity-option" data-value="V">
                                    <div class="option-content">
                                        <div class="color-indicator" style="background-color: var(--vacation);"></div>
                                        <div class="activity-label">Vacaciones</div>
                                    </div>
                                    <div class="activity-code">V</div>
                                </div>
                                <div class="activity-option" data-value="E">
                                    <div class="option-content">
                                        <div class="color-indicator" style="background-color: var(--training);"></div>
                                        <div class="activity-label">Entrenamiento</div>
                                    </div>
                                    <div class="activity-code">E</div>
                                </div>
                                <div class="activity-option" data-value="M">
                                    <div class="option-content">
                                        <div class="color-indicator" style="background-color: var(--medical);"></div>
                                        <div class="activity-label">Médico</div>
                                    </div>
                                    <div class="activity-code">M</div>
                                </div>
                            </div>
                        </div>
                        <select id="activity-type" style="display: none;">
                            <option value="">Seleccionar actividad...</option>
                            <option value="B">B - Trabajo en Base</option>
                            <option value="P">P - Trabajo en Pozo</option>
                            <option value="D">D - Descanso</option>
                            <option value="V">V - Vacaciones</option>
                            <option value="E">E - Entrenamiento</option>
                            <option value="M">M - Médico</option>
                        </select>
                        <div class="error-message" id="activity-type-error">Debes seleccionar un tipo de actividad
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="food-type">Numero de Comidas</label>
                        <select id="food-type" class="select-custom">
                            <option value="">Seleccionar numero de comidas...</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="bonus-type">Tipo de Bono</label>
                        <select id="bonus-type" class="select-custom">
                            <option value="">Seleccionar bono...</option>
                            <option value="performance">Bono por desempeño</option>
                            <option value="attendance">Bono por asistencia</option>
                            <option value="special">Bono especial</option>
                        </select>
                    </div>
                </div>

                <!-- Servicio Tab -->
                <div class="tab-content" id="service-tab">
                    <div class="form-group">
                        <label for="service-category">Categoría de Servicio</label>
                        <select id="service-category" class="select-custom">
                            <option value="">Seleccionar categoría...</option>
                            <option value="electrical">Eléctrico</option>
                            <option value="mechanical">Mecánico</option>
                            <option value="maintenance">Mantenimiento</option>
                            <option value="inspection">Inspección</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="service-type">Tipo de Servicio</label>
                        <select id="service-type" class="select-custom" disabled>
                            <option value="">Primero seleccione una categoría</option>
                        </select>
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
