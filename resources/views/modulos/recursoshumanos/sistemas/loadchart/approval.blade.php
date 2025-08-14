@extends('modulos.recursoshumanos.sistemas.loadchart.index')

@section('content')
    <div id="approvalView">
        <!-- Approval View (Hidden by default) -->
        <div id="approvalView">
            <div class="approval-container">
                <div class="approval-header">
                    <h2><i class="fas fa-check-circle"></i>Load Chart - Junio 2025</h2>
                    <div class="period-navigation-container">
                        <div class="period-navigation">
                            <button class="period-btn" id="prev-period"><i class="fas fa-chevron-left"></i></button>
                            <button class="period-btn active" id="quincena1">Quincena 1</button>
                            <button class="period-btn" id="quincena2">Quincena 2</button>
                            <button class="period-btn" id="full-month">Mes Completo</button>
                            <button class="period-btn" id="next-period"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                    <div class="approval-actions">

                        <button class="squad-btn" id="squad-control">
                            <i class="fas fa-users-cog"></i> Control de Cuadrillas
                        </button>

                        <button class="back-btn" id="days-quincena">
                            <i class="fas fa-calendar-day"></i> Días de Quincena
                        </button>
                        <button class="back-btn" id="back-to-calendar" data-route="/calendar">
                            <i class="fas fa-arrow-left"></i> Volver al Calendario
                        </button>
                    </div>
                </div>

                <table class="approval-table">
                    <thead>
                        <!-- En el thead -->
                        <tr class="header-row">
                            <th rowspan="2">Nombre</th>
                            <th rowspan="2">KPI</th>
                            <th colspan="15" id="days-columns">Días del Mes</th>
                            <!-- Cambiado a colspan dinámico -->
                            <th rowspan="2">Total</th>
                            <th rowspan="2" class="vacations-header" title="Vacaciones">Vac.</th>
                            <th rowspan="2" class="breaks-header" title="Descansos">Desc.</th>
                            <th rowspan="2" class="utilization-header" title="Utilización">Utiliz.</th>
                            <th rowspan="2" class="utilization-header" title="Utilización">Aprob.</th>
                        </tr>

                        <tr class="header-row">
                            <!-- Días 1-31 -->
                            <th class="day-header weekend">1<br>Dom</th>
                            <th class="day-header">2<br>Lun</th>
                            <th class="day-header">3<br>Mar</th>
                            <th class="day-header">4<br>Mié</th>
                            <th class="day-header">5<br>Jue</th>
                            <th class="day-header">6<br>Vie</th>
                            <th class="day-header weekend">7<br>Sáb</th>
                            <th class="day-header weekend">8<br>Dom</th>
                            <th class="day-header">9<br>Lun</th>
                            <th class="day-header">10<br>Mar</th>
                            <th class="day-header">11<br>Mié</th>
                            <th class="day-header">12<br>Jue</th>
                            <th class="day-header">13<br>Vie</th>
                            <th class="day-header weekend">14<br>Sáb</th>
                            <th class="day-header weekend">15<br>Dom</th>
                            <th class="day-header">16<br>Lun</th>
                            <th class="day-header">17<br>Mar</th>
                            <th class="day-header">18<br>Mié</th>
                            <th class="day-header">19<br>Jue</th>
                            <th class="day-header">20<br>Vie</th>
                            <th class="day-header weekend">21<br>Sáb</th>
                            <th class="day-header weekend">22<br>Dom</th>
                            <th class="day-header">23<br>Lun</th>
                            <th class="day-header">24<br>Mar</th>
                            <th class="day-header">25<br>Mié</th>
                            <th class="day-header">26<br>Jue</th>
                            <th class="day-header">27<br>Vie</th>
                            <th class="day-header weekend">28<br>Sáb</th>
                            <th class="day-header weekend">29<br>Dom</th>
                            <th class="day-header">30<br>Lun</th>
                            <th class="day-header">31<br>Mar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Empleado 1 -->
                        <tr class="employee-row">
                            <td rowspan="4" class="employee-info-cell">Saul Falcon Perez</td>
                            <td class="activity-label-cell">Actividad</td>
                            <!-- Indicadores de actividad para cada día -->
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-a">A</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <td class="data-cell">
                                <div class="status-indicator status-n">N</div>
                            </td>
                            <!-- Total (celda normal) -->
                            <td class="data-cell">
                                <div class="status-indicator">1</div>
                            </td>
                            <!-- Vacaciones (celda única) -->
                            <td rowspan="4" class="vacations-cell">
                                <div class="vacations-container">
                                    <div class="vacations-value">12</div>
                                </div>
                            </td>
                            <!-- Descansos (celda única) -->
                            <td rowspan="4" class="breaks-cell">
                                <div class="breaks-container">
                                    <div class="breaks-value">6</div>
                                </div>
                            </td>
                            <!-- Utilización (celda única) -->
                            <td rowspan="4" class="utilization-cell">
                                <div class="utilization-container">
                                    <div class="utilization-value">80%</div>
                                </div>
                            </td>
                            <td rowspan="4" class="utilization-cell">
                                <div class="utilization-container">
                                    <button class="btn-review">Revisado</button>
                                    <button class="btn-approve">Aprobado</button>
                                </div>
                            </td>
                        </tr>

                        <tr class="activity-row">
                            <td class="activity-label-cell">Comida</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">8</td>
                            <td class="data-cell">8</td>
                            <td class="data-cell">8</td>
                            <td class="data-cell">8</td>
                            <td class="data-cell">8</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">8</td>
                            <td class="data-cell">8</td>
                            <td class="data-cell">8</td>
                            <td class="data-cell">8</td>
                            <td class="data-cell">8</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">8</td>
                            <td class="data-cell">8</td>
                            <td class="data-cell">4</td>
                            <td class="data-cell">4</td>
                            <td class="data-cell">4</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">4</td>
                            <td class="data-cell">8</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">8</td>
                            <td class="data-cell">8</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">8</td>
                            <td class="data-cell">8</td>
                            <!-- Total (celda normal) -->
                            <td class="data-cell">128</td>
                        </tr>
                        <tr class="activity-row">
                            <td class="activity-label-cell">Bono</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">12,000</td>
                            <td class="data-cell">12,000</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">12,000</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">12,000</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">1</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <!-- Total (celda normal) -->
                            <td class="data-cell">1</td>
                        </tr>
                        <tr class="activity-row">
                            <td class="activity-label-cell">Servicio</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">20000</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <td class="data-cell">0</td>
                            <!-- Total (celda normal) -->
                            <td class="data-cell">0</td>
                        </tr>
                    </tbody>
                </table>
                <button class="save-btn" onclick="guardarDatos()">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>

    <div id="quincena-modal" class="quincena-modal-container" style="display: none;">
        <div class="quincena-modal-card">
            <div class="quincena-modal-header">
                <h3 class="quincena-modal-title">Configurar Quincenas</h3>
                <button class="quincena-close-btn">&times;</button>
            </div>

            <div class="quincena-modal-content">
                <!-- Sección de quincenas (izquierda) -->
                <div class="quincena-selection-section">
                    <div class="quincena-selection-group">
                        <div class="quincena-select-header">
                            <span class="quincena-badge">1</span>
                            <h4 class="quincena-select-title">Primera Quincena</h4>
                        </div>
                        <div class="quincena-date-range">
                            <div class="quincena-date-field">
                                <label>Fecha Inicio</label>
                                <input type="date" id="q1-start" class="quincena-date-input">
                            </div>
                            <div class="quincena-date-field">
                                <label>Fecha Fin</label>
                                <input type="date" id="q1-end" class="quincena-date-input">
                            </div>
                        </div>
                    </div>

                    <div class="quincena-selection-group">
                        <div class="quincena-select-header">
                            <span class="quincena-badge">2</span>
                            <h4 class="quincena-select-title">Segunda Quincena</h4>
                        </div>
                        <div class="quincena-date-range">
                            <div class="quincena-date-field">
                                <label>Fecha Inicio</label>
                                <input type="date" id="q2-start" class="quincena-date-input">
                            </div>
                            <div class="quincena-date-field">
                                <label>Fecha Fin</label>
                                <input type="date" id="q2-end" class="quincena-date-input">
                            </div>
                        </div>
                    </div>

                    <div class="quincena-actions">
                        <button class="quincena-btn quincena-cancel-btn">Cancelar</button>
                        <button class="quincena-btn quincena-save-btn">Guardar Cambios</button>
                    </div>
                </div>

                <!-- Sección de calendario (derecha) -->
                <div class="quincena-calendar-section">
                    <div class="quincena-month-nav">
                        <button class="quincena-nav-btn quincena-prev-month">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <h4 class="quincena-month-title">Junio 2025</h4>
                        <button class="quincena-nav-btn quincena-next-month">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>

                    <div class="quincena-calendar-grid">
                        <div class="quincena-day-label">Dom</div>
                        <div class="quincena-day-label">Lun</div>
                        <div class="quincena-day-label">Mar</div>
                        <div class="quincena-day-label">Mié</div>
                        <div class="quincena-day-label">Jue</div>
                        <div class="quincena-day-label">Vie</div>
                        <div class="quincena-day-label">Sáb</div>

                        <!-- Días se generarán dinámicamente -->
                    </div>
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
                            <tr class="squad-row">
                                <td class="centered-cell">
                                    <span class="squad-badge">Cuadrilla-01</span>
                                </td>
                                <td>
                                    <div class="operators-grid">
                                        <div class="operator-card">
                                            <div class="operator-avatar"><i class="fas fa-user"></i></div>
                                            <div class="operator-info">
                                                <span class="operator-name">Saul Falcon Perez</span>
                                                <span class="operator-id">ID: OP-001</span>
                                            </div>
                                        </div>
                                        <div class="operator-card">
                                            <div class="operator-avatar"><i class="fas fa-user"></i></div>
                                            <div class="operator-info">
                                                <span class="operator-name">Juan Pérez López</span>
                                                <span class="operator-id">ID: OP-002</span>
                                            </div>
                                        </div>
                                        <div class="operator-card">
                                            <div class="operator-avatar"><i class="fas fa-user"></i></div>
                                            <div class="operator-info">
                                                <span class="operator-name">María García Ruiz</span>
                                                <span class="operator-id">ID: OP-003</span>
                                            </div>
                                        </div>
                                        <div class="operator-card">
                                            <div class="operator-avatar"><i class="fas fa-user"></i></div>
                                            <div class="operator-info">
                                                <span class="operator-name">Carlos Martínez Soto</span>
                                                <span class="operator-id">ID: OP-004</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn edit" title="Editar"><i
                                                class="fas fa-pencil-alt"></i></button>
                                        <button class="action-btn delete" title="Eliminar"><i
                                                class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="squad-row incomplete-row">
                                <td class="centered-cell">
                                    <span class="squad-badge">Cuadrilla-02</span>
                                </td>
                                <td>
                                    <div class="operators-grid">
                                        <div class="operator-card">
                                            <div class="operator-avatar"><i class="fas fa-user"></i></div>
                                            <div class="operator-info">
                                                <span class="operator-name">Luis Hernández</span>
                                                <span class="operator-id">ID: OP-005</span>
                                            </div>
                                        </div>
                                        <div class="operator-card vacant">
                                            <div class="operator-avatar"><i class="fas fa-user-plus"></i></div>
                                            <div class="operator-info"><span class="operator-name">Vacío</span></div>
                                        </div>
                                        <div class="operator-card vacant">
                                            <div class="operator-avatar"><i class="fas fa-user-plus"></i></div>
                                            <div class="operator-info"><span class="operator-name">Vacío</span></div>
                                        </div>
                                        <div class="operator-card vacant">
                                            <div class="operator-avatar"><i class="fas fa-user-plus"></i></div>
                                            <div class="operator-info"><span class="operator-name">Vacío</span></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn edit" title="Editar"><i
                                                class="fas fa-pencil-alt"></i></button>
                                        <button class="action-btn delete" title="Eliminar"><i
                                                class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
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
                    <label>Seleccionar 4 Operadores</label>
                    <div class="operator-selection">
                        <div class="operator-selector">
                            <label>Operador 1</label>
                            <select class="form-select">
                                <option value="">Seleccionar...</option>
                                <option value="OP-001">Saul Falcon Perez</option>
                                <option value="OP-002">Juan Pérez López</option>
                                <option value="OP-003">María García Ruiz</option>
                                <option value="OP-004">Carlos Martínez Soto</option>
                                <option value="OP-005">Luis Hernández</option>
                            </select>
                        </div>
                        <div class="operator-selector">
                            <label>Operador 2</label>
                            <select class="form-select">
                                <option value="">Seleccionar...</option>
                                <option value="OP-001">Saul Falcon Perez</option>
                                <option value="OP-002">Juan Pérez López</option>
                                <option value="OP-003">María García Ruiz</option>
                                <option value="OP-004">Carlos Martínez Soto</option>
                                <option value="OP-005">Luis Hernández</option>
                            </select>
                        </div>
                        <div class="operator-selector">
                            <label>Operador 3</label>
                            <select class="form-select">
                                <option value="">Seleccionar...</option>
                                <option value="OP-001">Saul Falcon Perez</option>
                                <option value="OP-002">Juan Pérez López</option>
                                <option value="OP-003">María García Ruiz</option>
                                <option value="OP-004">Carlos Martínez Soto</option>
                                <option value="OP-005">Luis Hernández</option>
                            </select>
                        </div>
                        <div class="operator-selector">
                            <label>Operador 4</label>
                            <select class="form-select">
                                <option value="">Seleccionar...</option>
                                <option value="OP-001">Saul Falcon Perez</option>
                                <option value="OP-002">Juan Pérez López</option>
                                <option value="OP-003">María García Ruiz</option>
                                <option value="OP-004">Carlos Martínez Soto</option>
                                <option value="OP-005">Luis Hernández</option>
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

    <script src="{{ asset('assets/js/recursoshumanos/loadchart/approval.js') }}"></script>
@endsection
