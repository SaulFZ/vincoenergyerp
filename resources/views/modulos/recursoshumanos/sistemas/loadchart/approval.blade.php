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
                    <button type="button" class="quincena-btn quincena-edit-btn"><i class="fas fa-edit"></i> Editar</button>
                    <button type="button" class="quincena-btn quincena-cancel-btn" style="display: none;"><i class="fas fa-times"></i> Cancelar</button>
                    <button type="button" class="quincena-btn quincena-save-btn" disabled><i class="fas fa-save"></i> Guardar Cambios</button>
                </div>
            </div>
            <div class="quincena-calendar-section">
                <div class="quincena-month-nav">
                    <button type="button" class="quincena-nav-btn quincena-prev-month"><i class="fas fa-chevron-left"></i></button>
                    <h5 class="quincena-month-title"></h5>
                    <button type="button" class="quincena-nav-btn quincena-next-month"><i class="fas fa-chevron-right"></i></button>
                </div>
                <div class="quincena-calendar-grid"></div>
            </div>
        </div>
    </div>
</div>34


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


<script>
        // Definición de rutas
        window.appRoutes = {
            getOperadores: '{{ route('squads.get_operadores') }}',
            getSquads: '{{ route('squads.get_squads') }}',
            storeSquad: '{{ route('squads.store') }}',
            destroySquad: '{{ route('squads.destroy', ['squadNumber' => '__SQUAD_NUMBER__']) }}'.replace('__SQUAD_NUMBER__', ''),
            showSquad: '{{ route('squads.show', ['squadNumber' => '__SQUAD_NUMBER__']) }}'.replace('__SQUAD_NUMBER__','')
        };

    </script>

    <script src="{{ asset('assets/js/recursoshumanos/loadchart/approval.js') }}"></script>
@endsection
