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

<style>
    /* Animaciones y estilos para la gestión de cuadrillas */

/* Skeleton loading */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 4px;
    color: transparent !important;
    position: relative;
    overflow: hidden;
}

.skeleton-badge {
    width: 100px;
    height: 28px;
    display: inline-block;
}

.skeleton-text {
    height: 12px;
    margin: 4px 0;
    display: block;
}

.skeleton-btn {
    width: 30px;
    height: 30px;
    display: inline-block;
    margin: 0 5px;
    border-radius: 50%;
}

@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Loading form */
.loading-form .form-body {
    position: relative;
}

.loading-form .form-body::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.7);
    z-index: 10;
}

.loading-form .form-body::before {
    content: 'Cargando...';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 11;
    font-size: 1.2rem;
    color: #333;
}

/* Success animation */
.success-animation .form-modal-content {
    animation: pulseSuccess 0.5s 2;
    position: relative;
}

.success-animation .form-modal-content::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 4rem;
    color: #28a745;
    z-index: 10;
}

@keyframes pulseSuccess {
    0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4); }
    70% { box-shadow: 0 0 0 15px rgba(40, 167, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
}

/* Transiciones suaves */
.squad-row {
    transition: all 0.3s ease;
}

.squad-modal-card {
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.is-visible .squad-modal-card {
    animation: modalEnter 0.4s ease forwards;
}

@keyframes modalEnter {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Operadores deshabilitados */
select option:disabled {
    color: #999;
    font-style: italic;
    background-color: #f8f9fa;
}

/* Error state */
.error {
    color: #dc3545;
    text-align: center;
    padding: 2rem;
}

.error i {
    font-size: 2rem;
    margin-bottom: 1rem;
    display: block;
}

.retry-btn {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 1rem;
    transition: background 0.3s;
}

.retry-btn:hover {
    background: #c82333;
}

/* Estilos para cuadrillas existentes deshabilitadas */
select option[disabled] {
    color: #6c757d;
}

/* Tooltip para opciones deshabilitadas */
select option[disabled]:hover {
    cursor: not-allowed;
}
</style>
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
@endsection
