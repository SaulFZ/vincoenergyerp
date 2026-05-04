@extends('modules.rh.loadchart.index')

@section('header_metadata')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <div class="container">
        <div class="content-layout">
            {{-- Sección del Formulario Original - OCULTA PERMANENTEMENTE --}}
            <div class="form-section" style="display: none;"></div>

            <div class="list-section" style="width: 100%; max-width: 100%; min-width: 0;">
                <div class="card">
                    <div class="card-header">
                        <div class="header-section" id="listHeader">
                            <h1><i class="fas fa-list"></i> Balances de Vacaciones Registrados</h1>
                            <p>Administra los días de vacaciones y descanso disponibles para cada empleado</p>
                        </div>
                        <div class="header-actions">
                            {{-- BOTÓN PARA ABRIR EL MODAL EN MODO CREAR/AGREGAR --}}
                            <button type="button" class="btn btn-primary" id="openAddFormBtn"
                                style="display: none !important;">
                                <i class="fas fa-plus-circle"></i> Agregar Balance
                            </button>

                            {{-- FILTROS PARA LA TABLA --}}
                            <div class="table-filters" id="mainTableFilters">
                                <select id="filterDepartment" class="select-custom">
                                    <option value="">Todas las Áreas</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept }}">{{ $dept }}</option>
                                    @endforeach
                                </select>

                                <select id="filterRestMode" class="select-custom">
                                    <option value="">Todas las Modalidades</option>
                                    <option value="5x2">5x2</option>
                                    <option value="6x1">6x1</option>
                                    <option value="10X20">10X20</option>
                                    <option value="21x7">21x7</option>
                                    <option value="24x6">24x6</option>
                                    <option value="UNASSIGNED">No Asignado</option>
                                </select>
                            </div>

                            {{-- COMIENZO DEL BUSCADOR --}}
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" id="searchVacation" placeholder="Buscar por empleado...">
                            </div>

                            {{-- BOTÓN PARA ALTERNAR VISTA --}}
                            <button type="button" class="btn btn-primary" id="toggleVacationView">
                                <i class="fas fa-calendar-check"></i> Ver Vacaciones Tomadas
                            </button>

                            {{-- BOTÓN PARA ABRIR MODAL DE REPORTES --}}
                            <button type="button" class="btn btn-info" id="openReportModalBtn" title="Generar Reporte PDF"
                                style="display: none;">
                                <i class="fas fa-file-pdf"></i> Reporte
                            </button>
                        </div>
                    </div>

                    <div class="card-body" style="width: 100%; overflow: hidden;">
                        {{-- Contenedor para la vista de Balances Disponibles (Vista de Tabla Normal) --}}
                        <div id="balanceViewContainer">
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Empleado</th>
                                            <th>Área</th>
                                            <th>Fecha de Ingreso</th>
                                            <th>Años de Servicio</th>
                                            <th>Modalidad Descanso</th>
                                            <th>Días Disponibles</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="vacationTableBody"></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Contenedor para la vista de Vacaciones Tomadas (Estilo Calendario Gantt Continuo) --}}
                        <div id="takenViewContainer" style="display: none; width: 100%;">
                        </div>

                        {{-- PAGINACIÓN (Se oculta dinámicamente en la vista Gantt) --}}
                        <div class="pagination-container" id="mainPaginationContainer">
                            <div class="per-page-selector">
                                <span>Mostrar:</span>
                                <select id="perPageSelector" class="select-custom">
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="all">Todos</option>
                                </select>
                            </div>
                            <div class="pagination-links-container">
                                <div class="pagination-links" id="pagination-links"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL FLOTANTE PARA AGREGAR/EDITAR BALANCE --}}
    <div class="modal" id="formModal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3 id="formTitleModal"><i class="fas fa-plus-circle"></i> Agregar Balance</h3>
                <button class="close-modal" id="closeFormModalBtn">&times;</button>
            </div>
            <div class="modal-body">
                <form id="vacationForm">
                    @csrf
                    <input type="hidden" id="balance_id" name="id">
                    <input type="hidden" id="employee_id_hidden" name="employee_id">

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="employee_id">Empleado</label>
                            <select id="employee_id" class="select-custom" required disabled>
                                <option value="">Seleccionar empleado...</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="vacation_days_available">Días de Vacaciones Disponibles</label>
                            <input type="number" id="vacation_days_available" name="vacation_days_available"
                                class="input-custom" min="0" placeholder="0" required>
                        </div>

                        <div class="form-group">
                            <label for="rest_days_available">Días de Descanso Disponibles</label>
                            <input type="number" id="rest_days_available" name="rest_days_available"
                                class="input-custom" min="0" placeholder="0" required>
                        </div>

                        <div class="form-group">
                            <label for="years_of_service">Años de Servicio</label>
                            <input type="number" id="years_of_service" name="years_of_service" class="input-custom"
                                min="0" placeholder="0" required readonly>
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="rest_mode">Modalidad de Descanso</label>
                            <select id="rest_mode" name="rest_mode" class="select-custom" required>
                                <option value="5x2" selected>5 días trabajo x 2 días descanso</option>
                                <option value="6x1">6 días trabajo x 1 día descanso</option>
                                <option value="10X20">20 dias trabajo x 10 dias descanso</option>
                                <option value="21x7">21 días trabajo x 7 días descanso</option>
                                <option value="24x6">24 días trabajo x 6 días descanso</option>
                                <option value="UNASSIGNED">No Asignado</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions" style="border-top: none; padding-top: 0;">
                        <button type="button" id="cancelEditModal" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" id="submitBtnModal" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Balance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL DE CONFIRMACIÓN PARA ELIMINAR --}}
    <div class="modal" id="confirmModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle"><i class="fas fa-exclamation-triangle"></i> Confirmar Acción</h3>
                <button class="close-modal" id="closeConfirmModalBtn">&times;</button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage">¿Estás seguro de que deseas realizar esta acción?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelConfirm">Cancelar</button>
                <button class="btn btn-danger" id="confirmAction">Confirmar</button>
            </div>
        </div>
    </div>

    {{-- MODAL PARA VER HISTORIAL COMPLETO DE VACACIONES --}}
    <div class="modal" id="historyModal">
        <div class="modal-content" style="max-width: 900px;">
            <div class="modal-header">
                <h3><i class="fas fa-history"></i> Historial Completo de Vacaciones</h3>
                <button class="close-modal" id="closeHistoryModalBtn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="employee-header"
                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e2e8f0;">
                    <div>
                        <h4 id="historyEmployeeName" style="margin: 0 0 5px 0; color: #2d3748;"></h4>
                        <div style="display: flex; gap: 15px; font-size: 0.9rem; color: #4a5568;">
                            <span><strong>No. Empleado:</strong> <span id="historyEmployeeNumber"></span></span>
                            <span><strong>Área:</strong> <span id="historyEmployeeArea"></span></span>
                            <span><strong>Ingreso:</strong> <span id="historyHireDate"></span></span>
                        </div>
                    </div>
                    <div class="search-box" style="width: 250px;">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchHistory" placeholder="Buscar por fecha o mes...">
                    </div>
                </div>

                <div class="history-summary"
                    style="margin-bottom: 20px; padding: 15px; background: #e8f4fd; border-radius: 8px;">
                    <h5 style="margin: 0 0 10px 0; color: #2d3748;">Resumen</h5>
                    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; text-align: center;">
                        <div>
                            <div style="font-size: 24px; font-weight: bold; color: var(--under-review);"
                                id="totalUnderReview">0</div>
                            <div style="font-size: 11px; color: #4a5568; font-weight: 600;">Bajo Revisión</div>
                        </div>
                        <div>
                            <div style="font-size: 24px; font-weight: bold; color: var(--reviewed-detail);"
                                id="totalReviewed">0</div>
                            <div style="font-size: 11px; color: #4a5568; font-weight: 600;">Revisados</div>
                        </div>
                        <div>
                            <div style="font-size: 24px; font-weight: bold; color: var(--approved-detail);"
                                id="totalApproved">0</div>
                            <div style="font-size: 11px; color: #4a5568; font-weight: 600;">Aprobados</div>
                        </div>
                        <div>
                            <div style="font-size: 24px; font-weight: bold; color: var(--rejected-detail);"
                                id="totalRejected">0</div>
                            <div style="font-size: 11px; color: #4a5568; font-weight: 600;">Rechazados</div>
                        </div>
                        <div style="border-left: 2px solid #cbd5e0;">
                            <div style="font-size: 24px; font-weight: bold; color: #4299e1;" id="totalDays">0</div>
                            <div style="font-size: 11px; color: #4a5568; font-weight: 600;">Total Días</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Mes</th>
                                <th>Año</th>
                                <th>Estatus</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="closeHistoryModal">Cerrar</button>
            </div>
        </div>
    </div>

    {{-- MODAL FLOTANTE PARA GENERACIÓN DE REPORTES --}}
    <div class="modal" id="reportModal">
        <div class="modal-content" style="max-width: 750px;">
            <div class="modal-header"
                style="background-color: var(--primary-blue); color: var(--white); border-top-left-radius: 10px; border-top-right-radius: 10px;">
                <h3 style="color: var(--white); margin: 0;"><i class="fas fa-file-pdf"></i> Generar Reporte de Vacaciones
                </h3>
                <button class="close-modal" id="closeReportModalBtn"
                    style="color: var(--white); opacity: 0.8;">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert-info"
                    style="margin-bottom: 20px; padding: 10px; border-radius: 6px; background: #e8f4fd; border: 1px solid #99c2e0;">
                    <i class="fas fa-info-circle" style="color: #4299e1;"></i> Los filtros aplicados aquí solo afectan la
                    generación del reporte PDF, no la tabla principal.
                </div>

                <form id="reportForm">
                    @csrf
                    <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 20px;">

                        {{-- TIPO DE REPORTE --}}
                        <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 10px;">
                            <label for="report_type"><i class="fas fa-chart-line"></i> Tipo de Reporte</label>
                            <select id="report_type" name="report_type" class="select-custom" required>
                                <option value="AVAILABLE">Días Disponibles (Balance Actual)</option>
                                <option value="TAKEN">Días Tomados (Detallado)</option>
                            </select>
                        </div>

                        {{-- RANGO DE FECHAS --}}
                        <div id="date_range_group"
                            style="display: none; grid-column: 1 / -1; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; border: 1px dashed #cbd5e0; padding: 15px; border-radius: 6px;">
                            <div class="form-group">
                                <label for="date_from"><i class="fas fa-calendar-alt"></i> Fecha Desde</label>
                                <input type="date" id="date_from" name="date_from" class="input-custom">
                                <small id="date_from_error" class="text-danger" style="color: var(--danger);"></small>
                            </div>
                            <div class="form-group">
                                <label for="date_to"><i class="fas fa-calendar-alt"></i> Fecha Hasta</label>
                                <input type="date" id="date_to" name="date_to" class="input-custom">
                                <small id="date_to_error" class="text-danger" style="color: var(--danger);"></small>
                            </div>
                        </div>

                        {{-- FILTRO DE DEPARTAMENTO/ÁREA --}}
                        <div class="form-group">
                            <label for="report_department"><i class="fas fa-sitemap"></i> Área</label>
                            <div class="multi-select-container">
                                <div class="select-all-container">
                                    <label class="checkbox-label" style="font-weight: bold; color: var(--primary-blue);">
                                        <input type="checkbox" id="select_all_departments"> Seleccionar Todo
                                    </label>
                                </div>
                                <div class="checkbox-group" id="department_checkbox_group" style="max-height: 150px;">
                                    @foreach ($departments as $department)
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="departments[]" value="{{ $department }}"
                                                checked> {{ $department }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- FILTRO DE EMPLEADO --}}
                        <div class="form-group">
                            <label for="report_employee_id"><i class="fas fa-user-friends"></i> Personal</label>
                            <div class="multi-select-container">
                                <div class="select-all-container">
                                    <label class="checkbox-label" style="font-weight: bold; color: var(--primary-blue);">
                                        <input type="checkbox" id="select_all_employees" checked> Seleccionar Todo
                                    </label>
                                    <button type="button" id="clear_employees" class="btn-clear">Limpiar</button>
                                </div>
                                <div class="checkbox-group" id="employee_checkbox_group" style="max-height: 150px;">
                                    @foreach ($employees as $employee)
                                        <label class="checkbox-label employee-option"
                                            data-department="{{ $employee->area ? $employee->area->name : '' }}"
                                            style="display: flex;">
                                            <input type="checkbox" name="employees[]" value="{{ $employee->id }}"
                                                checked>
                                            {{ $employee->full_name }} ({{ $employee->employee_number ?? 'N/A' }})
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- FILTRO DE ESTATUS --}}
                        <div class="form-group" id="status_filter_group"
                            style="grid-column: 1 / -1; display: none; border: 1px dashed #cbd5e0; padding: 15px; border-radius: 6px;">
                            <label><i class="fas fa-info-circle"></i> Estatus de Días Tomados</label>
                            <div class="checkbox-group"
                                style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; max-height: 150px; overflow-y: auto;">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="status_filter[]" value="Approved" checked> Aprobado
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="status_filter[]" value="Reviewed"> Revisado
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="status_filter[]" value="Under_Review"> Bajo Revisión
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="status_filter[]" value="Rejected"> Rechazado
                                </label>
                            </div>
                        </div>

                    </div>
                </form>

                {{-- Resumen/Preview --}}
                <div class="history-summary"
                    style="margin-top: 30px; padding: 15px; background: #fffbe6; border-radius: 8px; border: 1px solid #ffe8b1;">
                    <h5 style="margin: 0 0 10px 0; color: #744210;"><i class="fas fa-check-circle"></i> Resumen del
                        Reporte a Generar</h5>
                    <div id="report-summary-text"
                        style="font-size: 0.9rem; color: #744210; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div><strong>Tipo:</strong> <span id="summary-type">Días Disponibles</span></div>
                        <div><strong>Empleados:</strong> <span id="summary-employees">Todos</span></div>
                        <div style="grid-column: 1 / -1;"><strong>Áreas:</strong> <span
                                id="summary-departments">Todas</span></div>
                        <div id="summary-dates" style="display: none;"><strong>Período:</strong> <span
                                id="summary-dates-text"></span></div>
                        <div id="summary-status" style="display: none;"><strong>Estatus:</strong> <span
                                id="summary-status-text"></span></div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" id="cancelReportModal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="generateReportBtn">
                    <i class="fas fa-download"></i> Generar Reporte PDF
                </button>
            </div>
        </div>
    </div>

    <style>
        :root {
            --dark-gray: #2d3748;
            --medium-gray: #4a5568;
            --light-gray: #e2e8f0;
            --background-gray: #f7fafc;
            --primary-blue: #283848;
            --secondary-blue: #34495e;
            --white: #ffffff;
            --success: #48bb78;
            --warning: #f6ad55;
            --danger: #e53e3e;
            --dark-red: #9B2C2C;

            --under-review: #ffd900;
            --reviewed-detail: #da8544;
            --approved-detail: #64946f;
            --rejected-detail: #f35900;
        }

        .container {
            padding: 20px;
        }

        .content-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .list-section {
            width: 100%;
        }

        .card {
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
            border: 1px solid var(--light-gray);
        }

        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--background-gray);
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            flex-wrap: wrap; /* Asegura responsividad en móviles */
            gap: 15px;
        }

        .header-section {
            flex: 1; /* Permite que el título ocupe el espacio principal */
            min-width: 250px;
        }

        .card-header h1 {
            color: var(--primary-blue);
            margin: 0;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .card-header p {
            margin: 0;
            color: var(--medium-gray);
            font-size: 0.9rem;
        }

        /* ---------------------------------------------------
           CORRECCIÓN PARA MANTENER LOS BOTONES EN UNA SOLA FILA
           --------------------------------------------------- */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: nowrap; /* Mantiene todo estrictamente en una línea */
            overflow-x: auto; /* Permite scroll horizontal en pantallas muy pequeñas sin desbordar */
            padding-bottom: 4px; /* Espacio para la barra de scroll si aparece */
        }

        /* Ocultar barra de scroll en navegadores Webkit para un look más limpio */
        .header-actions::-webkit-scrollbar {
            height: 6px;
        }
        .header-actions::-webkit-scrollbar-thumb {
            background-color: var(--light-gray);
            border-radius: 4px;
        }

        .table-filters {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: nowrap; /* Asegura que los selects también se queden en línea */
        }

        .search-box {
            position: relative;
            width: 250px;
            flex-shrink: 0; /* Evita que el buscador se encoja demasiado */
        }

        .search-box i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--medium-gray);
        }

        .search-box input {
            padding: 10px 12px 10px 35px;
            width: 100%;
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            box-sizing: border-box;
        }

        .card-body {
            padding: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--dark-gray);
            font-size: 0.95rem;
            display: block;
        }

        .input-custom,
        .select-custom {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            font-size: 1rem;
            background-color: var(--white);
            box-sizing: border-box;
        }

        .input-custom[readonly] {
            background-color: #f0f4f8;
            cursor: not-allowed;
            color: var(--medium-gray);
        }

        .input-custom:focus,
        .select-custom:focus {
            border-color: var(--secondary-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 73, 94, 0.2);
        }

        .form-actions {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            grid-column: 1 / -1;
            padding-top: 10px;
            border-top: 1px solid var(--light-gray);
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: 1px solid transparent;
            white-space: nowrap; /* Evita que el texto del botón salte de línea */
        }

        .btn-primary {
            background-color: var(--primary-blue);
            color: var(--white);
            border-color: var(--primary-blue);
        }

        .btn-primary:hover {
            background-color: var(--secondary-blue);
            border-color: var(--secondary-blue);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--medium-gray);
            border-color: var(--light-gray);
        }

        .btn-outline:hover {
            background-color: var(--light-gray);
            color: var(--dark-gray);
        }

        .btn-info {
            background-color: #4299e1;
            color: var(--white);
            border-color: #4299e1;
        }

        .btn-info:hover {
            background-color: #3182ce;
            border-color: #3182ce;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }

        .data-table th {
            background-color: var(--primary-blue);
            color: var(--white);
            font-weight: 600;
        }

        .data-table tbody tr:hover {
            background-color: #f0f4f8;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            background-color: #ebf4ff;
            color: var(--secondary-blue);
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn-icon {
            background: none;
            border: 1px solid transparent;
            padding: 8px;
            border-radius: 8px;
            cursor: pointer;
            color: var(--medium-gray);
            transition: all 0.2s ease;
        }

        .btn-icon:hover {
            background: var(--light-gray);
            color: var(--dark-gray);
        }

        .btn-edit:hover {
            color: var(--approved-detail);
            border-color: var(--approved-detail);
            background: rgba(100, 148, 111, 0.1);
        }

        .btn-delete:hover {
            color: var(--rejected-detail);
            border-color: var(--rejected-detail);
            background: rgba(243, 89, 0, 0.1);
        }

        .status-badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 11px;
            text-transform: uppercase;
        }

        .status-badge.disponible { background-color: var(--success); color: var(--white); }
        .status-badge.por_vencer { background-color: var(--warning); color: #744210; }
        .status-badge.agotado { background-color: var(--dark-red); color: var(--white); }
        .status-badge.under_review, .status-badge.bajo_revision { background-color: var(--under-review); color: #4b3e00; }
        .status-badge.reviewed, .status-badge.revisado { background-color: var(--reviewed-detail); color: var(--white); }
        .status-badge.approved, .status-badge.aprobado { background-color: var(--approved-detail); color: var(--white); }
        .status-badge.rejected, .status-badge.rechazado { background-color: var(--rejected-detail); color: var(--white); }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: var(--white);
            margin: auto;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close-modal {
            color: var(--medium-gray);
            font-size: 28px;
            font-weight: bold;
            border: none;
            background: none;
            cursor: pointer;
            padding: 0;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid var(--light-gray);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-danger {
            background-color: var(--danger);
            color: var(--white);
            border-color: var(--danger);
        }

        .btn-danger:hover {
            background-color: #c53030;
            border-color: #c53030;
        }

        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid var(--light-gray);
        }

        .pagination-links {
            display: flex;
            gap: 5px;
        }

        .pagination-item {
            padding: 8px 12px;
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            text-decoration: none;
            color: var(--primary-blue);
            font-size: 0.9rem;
        }

        .pagination-item:hover:not(.active):not(.disabled) {
            background-color: var(--light-gray);
        }

        .pagination-item.active {
            background-color: var(--primary-blue);
            color: var(--white);
        }

        .pagination-item.disabled {
            color: var(--medium-gray);
            cursor: not-allowed;
            opacity: 0.6;
            background-color: #f7fafc;
        }

        .multi-select-container {
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            padding: 10px;
            max-height: 200px;
            overflow-y: auto;
            background-color: var(--white);
        }

        .select-all-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
            margin-bottom: 10px;
            border-bottom: 1px solid var(--light-gray);
        }

        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
        }

        .checkbox-label:hover {
            background-color: var(--background-gray);
        }

        .btn-clear {
            background: none;
            border: 1px solid var(--light-gray);
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 0.8rem;
            cursor: pointer;
        }

        /* =========================================================================
           ESTILOS: GANTT SPLIT-PANE CONTINUO CON SOLUCIÓN FLEXBOX Y DRAG TO SCROLL
           ========================================================================= */
        .gantt-container {
            display: flex;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            background: var(--white);
            height: 65vh;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            margin-top: 15px;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }

        .gantt-left-panel {
            width: 320px;
            min-width: 320px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            border-right: 2px solid #e2e8f0;
            background: var(--white);
            z-index: 10;
            box-shadow: 3px 0 5px rgba(0, 0, 0, 0.03);
        }

        .gantt-right-panel {
            flex-grow: 1;
            flex-shrink: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            background: #fafbfc;
            cursor: grab;
        }

        .gantt-right-panel.is-dragging { cursor: grabbing; }
        .gantt-right-panel.is-dragging .gantt-right-body * { user-select: none; pointer-events: none; }

        .gantt-header {
            height: 66px;
            flex-shrink: 0;
            background: #fafbfc;
            border-bottom: 1px solid var(--light-gray);
            box-sizing: border-box;
        }

        .left-header {
            display: flex;
            align-items: center;
            padding: 0 15px;
        }

        .right-header {
            overflow: hidden;
            background: #fafbfc;
        }

        .gantt-left-body {
            flex-grow: 1;
            overflow: hidden;
            background: var(--white);
        }

        .gantt-right-body {
            flex-grow: 1;
            overflow-x: auto;
            overflow-y: auto;
            position: relative;
        }

        .gantt-months-row {
            display: flex;
            height: 30px;
            border-bottom: 1px solid var(--light-gray);
            box-sizing: border-box;
        }

        .gantt-month-cell {
            border-right: 1px solid var(--light-gray);
            box-sizing: border-box;
            background: #f0f4f8;
            position: relative;
            overflow: hidden;
        }

        .gantt-month-name-sticky {
            position: sticky;
            left: 0;
            display: inline-block;
            padding: 0 15px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--primary-blue);
            text-transform: uppercase;
            line-height: 30px;
            white-space: nowrap;
        }

        .gantt-days-row {
            display: flex;
            height: 35px;
        }

        .gantt-day-cell {
            width: 45px;
            min-width: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--medium-gray);
            border-right: 1px dashed #edf2f7;
            box-sizing: border-box;
        }

        .gantt-day-cell.weekend {
            background: #e2e8f0;
            color: #718096;
        }

        .gantt-row {
            height: 60px;
            border-bottom: 1px solid var(--light-gray);
            box-sizing: border-box;
        }

        .left-row {
            display: flex;
            padding: 0 15px;
            align-items: center;
            justify-content: space-between;
        }

        .employee-info-cell {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
        }

        .emp-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .emp-details {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            flex-grow: 1;
        }

        .emp-name {
            font-weight: 600;
            color: var(--dark-gray);
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .emp-area {
            font-size: 0.75rem;
            color: var(--medium-gray);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .btn-history-mini {
            background: none;
            border: none;
            color: var(--medium-gray);
            cursor: pointer;
            padding: 5px;
            transition: color 0.2s;
            font-size: 1.2rem;
        }

        .btn-history-mini:hover {
            color: #4299e1;
        }

        .right-row {
            display: flex;
            position: relative;
        }

        .gantt-grid-cell {
            width: 45px;
            min-width: 45px;
            border-right: 1px dashed #edf2f7;
            box-sizing: border-box;
        }

        .gantt-grid-cell.weekend {
            background: rgba(240, 244, 248, 0.5);
        }

        .timeline-event-wrapper {
            position: absolute;
            top: 10px;
            height: 40px;
            padding: 0 3px;
            box-sizing: border-box;
            z-index: 5;
        }

        .timeline-event {
            border-radius: 8px;
            padding: 0;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.12);
            height: 100%;
            cursor: pointer;
            transition: transform 0.2s, filter 0.2s;
            overflow: hidden;
            white-space: nowrap;
            width: 100%;
            background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.15) 0%, rgba(0, 0, 0, 0) 100%);
        }

        .timeline-event:hover {
            transform: scale(1.02);
            filter: brightness(1.1);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .event-icon-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 0 5px;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .event-icon { font-size: 0.8rem; }

        .event-duration-container {
            flex-grow: 1;
            text-align: center;
            padding: 0 3px;
        }

        .event-duration {
            padding: 2px 4px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .timeline-event.single-day {
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2px 0;
        }

        .timeline-event.single-day .event-icon-container {
            height: auto;
            padding: 0;
            border-right: none !important;
            background-color: transparent !important;
            margin-bottom: 2px;
        }

        .timeline-event.single-day .event-icon { font-size: 0.85rem; }

        .timeline-event.single-day .event-duration-container {
            padding: 0;
            line-height: 1;
        }

        .timeline-event.single-day .event-duration {
            background-color: transparent !important;
            font-size: 0.7rem;
            padding: 0;
        }

        .gantt-right-body::-webkit-scrollbar { width: 12px; height: 12px; }
        .gantt-right-body::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 6px; border: 2px solid white; }
        .gantt-right-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 6px; border: 2px solid white; }
        .gantt-right-body::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        [title] { position: relative; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ─── Estado de la aplicación ───────────────────────────────────────────
            let vacationBalancesData = @json($vacationBalances);
            let vacationDaysTakenData = @json($vacationDaysTaken);
            const employeesData = @json($employees);

            let currentPage = 1;
            let isBalanceView = true;
            let currentBalanceId = null;
            let currentAction = '';
            let currentEmployeeHistory = null;

            let lastScrollLeft = null;
            let lastScrollTop = null;

            // ─── Referencias DOM ───────────────────────────────────────────────────
            const perPageSelector = document.getElementById('perPageSelector');
            let itemsPerPage = parseInt(perPageSelector.value);
            const searchInput = document.getElementById('searchVacation');
            const listHeader = document.getElementById('listHeader');
            const toggleVacationViewBtn = document.getElementById('toggleVacationView');
            const balanceViewContainer = document.getElementById('balanceViewContainer');
            const takenViewContainer = document.getElementById('takenViewContainer');
            const paginationContainer = document.getElementById('mainPaginationContainer');
            const paginationLinksContainer = document.getElementById('pagination-links');
            const filterDepartment = document.getElementById('filterDepartment');
            const filterRestMode = document.getElementById('filterRestMode');
            const openReportModalBtn = document.getElementById('openReportModalBtn');
            const openAddFormBtn = document.getElementById('openAddFormBtn');

            const formModal = document.getElementById('formModal');
            const formTitleModal = document.getElementById('formTitleModal');
            const vacationForm = document.getElementById('vacationForm');
            const submitBtnModal = document.getElementById('submitBtnModal');
            const cancelEditModal = document.getElementById('cancelEditModal');
            const closeFormModalBtn = document.getElementById('closeFormModalBtn');
            const employeeIdSelect = document.getElementById('employee_id');
            const employeeIdHidden = document.getElementById('employee_id_hidden');

            const confirmModal = document.getElementById('confirmModal');
            const confirmActionBtn = document.getElementById('confirmAction');

            const historyModal = document.getElementById('historyModal');
            const closeHistoryModalBtn = document.getElementById('closeHistoryModalBtn');
            const closeHistoryModal = document.getElementById('closeHistoryModal');
            const searchHistoryInput = document.getElementById('searchHistory');
            const historyTableBody = document.getElementById('historyTableBody');

            const reportModal = document.getElementById('reportModal');
            const closeReportModalBtn = document.getElementById('closeReportModalBtn');
            const cancelReportModal = document.getElementById('cancelReportModal');
            const generateReportBtn = document.getElementById('generateReportBtn');
            const reportForm = document.getElementById('reportForm');
            const reportTypeSelect = document.getElementById('report_type');
            const dateRangeGroup = document.getElementById('date_range_group');
            const statusFilterGroup = document.getElementById('status_filter_group');
            const dateFromInput = document.getElementById('date_from');
            const dateToInput = document.getElementById('date_to');

            const selectAllDepartments = document.getElementById('select_all_departments');
            const departmentCheckboxGroup = document.getElementById('department_checkbox_group');
            const selectAllEmployees = document.getElementById('select_all_employees');
            const clearEmployeesBtn = document.getElementById('clear_employees');
            const employeeCheckboxGroup = document.getElementById('employee_checkbox_group');

            // ─── 🔄 REFRESH SIN RECARGAR PÁGINA ───────────────────────────────────
            async function refreshData() {
                try {
                    const response = await fetch('/rh/loadchart/employee_vacation_balance/data', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                        }
                    });

                    if (!response.ok) throw new Error('Error al obtener datos');
                    const json = await response.json();

                    vacationBalancesData = json.vacationBalances;
                    vacationDaysTakenData = json.vacationDaysTaken;

                    renderTableAndPagination();

                } catch (error) {
                    console.error('refreshData error:', error);
                }
            }

            // ─── Funciones auxiliares ──────────────────────────────────────────────
            function formatHireDate(dateString) {
                if (!dateString || dateString === 'N/A') return 'N/A';
                const parts = dateString.split('-');
                if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
                return dateString;
            }

            function getMonthName(monthIndex) {
                const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
                    'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                ];
                return months[monthIndex] || 'Mes';
            }

            function getInitials(name) {
                let parts = name.trim().split(' ');
                if (parts.length >= 2) {
                    return (parts[0][0] + parts[1][0]).toUpperCase();
                }
                return name.substring(0, 2).toUpperCase();
            }

            function translateAndStyleStatus(statusEnglish) {
                const normalizedStatus = statusEnglish.toLowerCase().replace(/\s/g, '_');
                const map = {
                    under_review: {
                        display: 'Bajo Revisión',
                        class: 'bajo_revision',
                        color: 'var(--warning)',
                        icon: 'fa-clock',
                        text: '#4b3e00',
                        bgIcon: 'rgba(0,0,0,0.1)',
                        bgDuration: 'rgba(0,0,0,0.15)'
                    },
                    reviewed: {
                        display: 'Revisado',
                        class: 'revisado',
                        color: 'var(--reviewed-detail)',
                        icon: 'fa-clipboard-check',
                        text: 'white',
                        bgIcon: 'rgba(255,255,255,0.15)',
                        bgDuration: 'rgba(255,255,255,0.25)'
                    },
                    approved: {
                        display: 'Aprobado',
                        class: 'aprobado',
                        color: 'var(--approved-detail)',
                        icon: 'fa-umbrella-beach',
                        text: 'white',
                        bgIcon: 'rgba(255,255,255,0.15)',
                        bgDuration: 'rgba(255,255,255,0.25)'
                    },
                    rejected: {
                        display: 'Rechazado',
                        class: 'rechazado',
                        color: 'var(--rejected-detail)',
                        icon: 'fa-times-circle',
                        text: 'white',
                        bgIcon: 'rgba(255,255,255,0.15)',
                        bgDuration: 'rgba(255,255,255,0.25)'
                    },
                };
                return map[normalizedStatus] ?? {
                    display: statusEnglish,
                    class: normalizedStatus,
                    color: '#a0aec0',
                    icon: 'fa-calendar-day',
                    text: 'white',
                    bgIcon: 'rgba(255,255,255,0.15)',
                    bgDuration: 'rgba(255,255,255,0.25)'
                };
            }

            function captureScrollPosition() {
                const rightBody = document.getElementById('ganttRightBody');
                if (rightBody) {
                    lastScrollLeft = rightBody.scrollLeft;
                    lastScrollTop = rightBody.scrollTop;
                }
            }

            // ─── Renderizado Principal ────────────────────────────────────────────
            function initialize() {
                renderTableAndPagination();
            }

            function renderTableAndPagination() {

                if (!isBalanceView) {
                    captureScrollPosition();
                }

                const searchTerm = searchInput.value.toLowerCase();
                const deptFilter = filterDepartment.value;
                const modeFilter = filterRestMode.value;

                const dataToUse = isBalanceView ? vacationBalancesData : vacationBalancesData;

                const filteredData = dataToUse.filter(item => {
                    const fullName = item.employee ? item.employee.full_name : '';
                    const departmentName = item.employee?.area?.name ?? '';
                    const restMode = item.rest_mode || '5x2';

                    const matchesSearch = fullName.toLowerCase().includes(searchTerm);
                    const matchesDept = deptFilter === '' || departmentName === deptFilter;
                    const matchesMode = !isBalanceView || modeFilter === '' || restMode === modeFilter;

                    return matchesSearch && matchesDept && matchesMode;
                });

                balanceViewContainer.style.display = isBalanceView ? 'block' : 'none';
                takenViewContainer.style.display = isBalanceView ? 'none' : 'block';

                if (isBalanceView) {
                    paginationContainer.style.display = 'flex';

                    const isAll = itemsPerPage === 'all';
                    const totalPages = isAll ? 1 : Math.ceil(filteredData.length / itemsPerPage);

                    if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
                    else if (totalPages === 0) currentPage = 1;

                    const startIndex = isAll ? 0 : (currentPage - 1) * itemsPerPage;
                    const endIndex = isAll ? filteredData.length : startIndex + itemsPerPage;
                    const itemsToDisplay = filteredData.slice(startIndex, endIndex);

                    renderBalanceViewHeader();
                    renderBalanceTableRows(itemsToDisplay);
                    openReportModalBtn.style.display = 'none';
                    renderPagination(totalPages);
                } else {
                    paginationContainer.style.display = 'none';

                    renderTakenViewHeader();
                    renderTimelineCalendar(filteredData);
                    openReportModalBtn.style.display = 'inline-flex';
                }
            }

            function renderBalanceViewHeader() {
                listHeader.innerHTML =
                    `<h1><i class="fas fa-list"></i> Balances de Vacaciones Registrados</h1><p>Administra los días de vacaciones y descanso disponibles para cada empleado</p>`;
                toggleVacationViewBtn.innerHTML = '<i class="fas fa-calendar-check"></i> Ver Calendario';
                toggleVacationViewBtn.classList.replace('btn-outline', 'btn-primary');
                filterRestMode.style.display = 'inline-block';
                openAddFormBtn.style.setProperty('display', 'inline-flex', 'important'); // Mostrar botón agregar
            }

            function renderTakenViewHeader() {
                listHeader.innerHTML =
                    `<h1><i class="fas fa-calendar-alt"></i> Calendario de Vacaciones</h1><p>Desplázate horizontalmente (Arrastrando) para ver la línea de tiempo del personal</p>`;
                toggleVacationViewBtn.innerHTML = '<i class="fas fa-balance-scale"></i> Ver Balances Disponibles';
                toggleVacationViewBtn.classList.replace('btn-primary', 'btn-outline');
                filterRestMode.style.display = 'none';
                openAddFormBtn.style.setProperty('display', 'none', 'important'); // Ocultar botón agregar
            }

            function renderBalanceTableRows(items) {
                const tbody = document.querySelector('#balanceViewContainer #vacationTableBody');
                tbody.innerHTML = '';
                if (!items.length) {
                    renderEmptyState(searchInput.value, true, tbody);
                    return;
                }

                items.forEach(balance => {
                    const row = document.createElement('tr');
                    const employeeName = balance.employee?.full_name ?? 'Empleado no encontrado';
                    const departmentName = balance.employee?.area?.name ?? 'N/A';
                    const hireDate = balance.employee ? formatHireDate(balance.employee.hire_date) : 'N/A';

                    row.innerHTML = `
                <td>${employeeName}</td>
                <td>${departmentName}</td>
                <td>${hireDate}</td>
                <td>${balance.years_of_service} años</td>
                <td><span class="badge">${balance.rest_mode || '5x2'}</span></td>
                <td><span class="badge" style="background:var(--primary-blue); color:white;">${balance.vacation_days_available} días</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-icon btn-edit" title="Editar" data-balance-id="${balance.id}"><i class="fas fa-edit"></i></button>
                        <button class="btn-icon btn-delete" title="Eliminar" data-balance-id="${balance.id}"><i class="fas fa-trash"></i></button>
                    </div>
                </td>`;
                    tbody.appendChild(row);
                });
            }

            // =================================================================================
            // ✅ CALENDARIO GANTT CONTINUO
            // =================================================================================
            function renderTimelineCalendar(items) {
                const container = document.getElementById('takenViewContainer');
                container.innerHTML = '';

                if (!items.length) {
                    container.innerHTML = `<table style="width:100%"><tbody id="emptyTbody"></tbody></table>`;
                    renderEmptyState(searchInput.value, false, document.getElementById('emptyTbody'));
                    return;
                }

                let minDate = new Date('2099-01-01');
                let maxDate = new Date('1900-01-01');
                let hasData = false;

                vacationDaysTakenData.forEach(empTaken => {
                    if (empTaken.vacation_days_details && empTaken.vacation_days_details.length > 0) {
                        empTaken.vacation_days_details.forEach(d => {
                            let dt = new Date(d.date + 'T00:00:00');
                            if (dt < minDate) minDate = dt;
                            if (dt > maxDate) maxDate = dt;
                            hasData = true;
                        });
                    }
                });

                const today = new Date();
                if (!hasData) {
                    minDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    maxDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                } else {
                    minDate = new Date(minDate.getFullYear(), minDate.getMonth(), 1);
                    maxDate = new Date(maxDate.getFullYear(), maxDate.getMonth() + 2, 0);
                }

                if (today < minDate) minDate = new Date(today.getFullYear(), today.getMonth(), 1);
                if (today > maxDate) maxDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);

                let dateColumns = [];
                let currDate = new Date(minDate);
                while (currDate <= maxDate) {
                    dateColumns.push(new Date(currDate));
                    currDate.setDate(currDate.getDate() + 1);
                }

                const dayWidth = 45;
                const totalGridWidth = dateColumns.length * dayWidth;

                let monthsHtml = '';
                let currentMonthStr = `${dateColumns[0].getMonth()}-${dateColumns[0].getFullYear()}`;
                let currentMonthName = getMonthName(dateColumns[0].getMonth());
                let currentYear = dateColumns[0].getFullYear();
                let daysInMonthCount = 0;

                dateColumns.forEach((dt, i) => {
                    let dtStr = `${dt.getMonth()}-${dt.getFullYear()}`;
                    if (dtStr !== currentMonthStr) {
                        monthsHtml += `
                        <div class="gantt-month-cell" style="width: ${daysInMonthCount * dayWidth}px;">
                            <span class="gantt-month-name-sticky">${currentMonthName} ${currentYear}</span>
                        </div>`;

                        currentMonthStr = dtStr;
                        currentMonthName = getMonthName(dt.getMonth());
                        currentYear = dt.getFullYear();
                        daysInMonthCount = 1;
                    } else {
                        daysInMonthCount++;
                    }
                });
                if (daysInMonthCount > 0) {
                    monthsHtml += `
                    <div class="gantt-month-cell" style="width: ${daysInMonthCount * dayWidth}px;">
                        <span class="gantt-month-name-sticky">${currentMonthName} ${currentYear}</span>
                    </div>`;
                }

                const daysMap = ['D', 'L', 'M', 'M', 'J', 'V', 'S'];
                let daysHtml = '';
                dateColumns.forEach(dt => {
                    const isWeekend = (dt.getDay() === 0 || dt.getDay() === 6);
                    daysHtml +=
                        `<div class="gantt-day-cell ${isWeekend ? 'weekend' : ''}">${daysMap[dt.getDay()]} ${dt.getDate()}</div>`;
                });

                let leftBodyHtml = '';
                let rightBodyHtml = '';

                items.forEach(balanceEmp => {
                    const empName = balanceEmp.employee ? balanceEmp.employee.full_name : 'N/A';
                    const empArea = balanceEmp.employee?.area?.name ?? 'Sin área asignada';
                    const avatarColor = stringToColor(empName);

                    leftBodyHtml += `
                        <div class="gantt-row left-row">
                            <div class="employee-info-cell">
                                <div class="emp-avatar" style="background-color: ${avatarColor}">${getInitials(empName)}</div>
                                <div class="emp-details" title="${empName}">
                                    <span class="emp-name">${empName}</span>
                                    <span class="emp-area">${empArea}</span>
                                </div>
                                <button class="btn-history-mini btn-history" title="Ver bitácora" data-employee-id="${empName}">
                                    <i class="fas fa-user-clock"></i>
                                </button>
                            </div>
                        </div>`;

                    let rowRight = `<div class="gantt-row right-row" style="width: ${totalGridWidth}px;">`;

                    dateColumns.forEach(dt => {
                        const isWeekend = (dt.getDay() === 0 || dt.getDay() === 6);
                        rowRight +=
                            `<div class="gantt-grid-cell ${isWeekend ? 'weekend' : ''}"></div>`;
                    });

                    let takenEmp = vacationDaysTakenData.find(t => t.full_name === empName);
                    let details = takenEmp ? (takenEmp.vacation_days_details || []).slice().sort((a, b) =>
                        new Date(a.date) - new Date(b.date)) : [];

                    let blocks = [];
                    let currentBlock = null;

                    details.forEach(d => {
                        let dDate = new Date(d.date + 'T00:00:00');
                        if (!currentBlock) {
                            currentBlock = {
                                start: dDate,
                                end: new Date(dDate),
                                status: d.status,
                                count: 1
                            };
                        } else {
                            let expectedNext = new Date(currentBlock.end);
                            expectedNext.setDate(expectedNext.getDate() + 1);

                            if (dDate.getTime() === expectedNext.getTime() && currentBlock
                                .status === d.status) {
                                currentBlock.end = new Date(dDate);
                                currentBlock.count++;
                            } else {
                                blocks.push({
                                    ...currentBlock
                                });
                                currentBlock = {
                                    start: dDate,
                                    end: new Date(dDate),
                                    status: d.status,
                                    count: 1
                                };
                            }
                        }
                    });
                    if (currentBlock) blocks.push(currentBlock);

                    // Posicionar Píldoras con estilos dinámicos
                    blocks.forEach(block => {
                        let startIndex = dateColumns.findIndex(d => d.getTime() === block.start
                            .getTime());
                        if (startIndex !== -1) {
                            let leftPx = startIndex * dayWidth;
                            let widthPx = block.count * dayWidth;
                            let statusStyle = translateAndStyleStatus(block.status);
                            let tooltipDate = block.count === 1 ? block.start.toLocaleDateString() :
                                `${block.start.toLocaleDateString()} al ${block.end.toLocaleDateString()}`;

                            let singleDayClass = block.count === 1 ? 'single-day' : '';

                            rowRight += `
                            <div class="timeline-event-wrapper" style="left: ${leftPx}px; width: ${widthPx}px;">
                                <div class="timeline-event ${singleDayClass}" style="background-color: ${statusStyle.color}; color: ${statusStyle.text};" title="${statusStyle.display} | ${tooltipDate}">
                                    <div class="event-icon-container" style="background-color: ${block.count === 1 ? 'transparent' : statusStyle.bgIcon}; border-right: ${block.count === 1 ? 'none' : '1px solid rgba(255,255,255,0.1)'};">
                                        <span class="event-icon"><i class="fas ${statusStyle.icon}"></i></span>
                                    </div>
                                    <div class="event-duration-container">
                                        <span class="event-duration" style="background-color: ${block.count === 1 ? 'transparent' : statusStyle.bgDuration};">${block.count}d</span>
                                    </div>
                                </div>
                            </div>`;
                        }
                    });

                    rowRight += `</div>`;
                    rightBodyHtml += rowRight;
                });

                const splitPaneHtml = `
                <div class="gantt-container" id="ganttMainContainer">
                    <div class="gantt-left-panel">
                        <div class="gantt-header left-header">
                            <span style="font-weight: 700; color: var(--primary-blue); font-size: 0.95rem;">Personal / Área</span>
                        </div>
                        <div class="gantt-left-body" id="ganttLeftBody">
                            ${leftBodyHtml}
                        </div>
                    </div>

                    <div class="gantt-right-panel" id="ganttRightPanel">
                        <div class="gantt-header right-header" id="ganttRightHeader">
                            <div style="width: ${totalGridWidth}px;">
                                <div class="gantt-months-row">${monthsHtml}</div>
                                <div class="gantt-days-row">${daysHtml}</div>
                            </div>
                        </div>
                        <div class="gantt-right-body" id="ganttRightBody">
                            ${rightBodyHtml}
                        </div>
                    </div>
                </div>`;

                container.innerHTML = splitPaneHtml;

                setTimeout(() => {
                    const rightPanel = document.getElementById('ganttRightPanel');
                    const rightBody = document.getElementById('ganttRightBody');
                    const leftBody = document.getElementById('ganttLeftBody');
                    const rightHeader = document.getElementById('ganttRightHeader');

                    if (rightBody && leftBody && rightHeader) {

                        if (lastScrollLeft !== null) {
                            rightBody.style.scrollBehavior = 'auto';
                            rightBody.scrollLeft = lastScrollLeft;
                            rightBody.scrollTop = lastScrollTop;
                            setTimeout(() => {
                                rightBody.style.scrollBehavior = 'smooth';
                            }, 50);
                        } else {
                            let todayIndex = dateColumns.findIndex(d =>
                                d.getFullYear() === today.getFullYear() &&
                                d.getMonth() === today.getMonth() &&
                                d.getDate() === today.getDate()
                            );

                            if (todayIndex === -1) {
                                todayIndex = dateColumns.findIndex(d =>
                                    d.getFullYear() === today.getFullYear() &&
                                    d.getMonth() === today.getMonth()
                                );
                            }

                            if (todayIndex !== -1) {
                                let scrollToPx = (todayIndex * dayWidth) - (rightBody.clientWidth / 2) + (
                                    dayWidth / 2);
                                rightBody.scrollLeft = Math.max(0, scrollToPx);
                            }
                        }

                        rightBody.addEventListener('scroll', () => {
                            leftBody.scrollTop = rightBody.scrollTop;
                            rightHeader.scrollLeft = rightBody.scrollLeft;
                        });

                        leftBody.addEventListener('wheel', (e) => {
                            rightBody.scrollTop += e.deltaY;
                            e.preventDefault();
                        }, {
                            passive: false
                        });

                        let isDown = false;
                        let startX;
                        let scrollLeft;
                        let startY;
                        let scrollTop;

                        rightPanel.addEventListener('mousedown', (e) => {
                            isDown = true;
                            rightPanel.classList.add('is-dragging');
                            rightBody.style.scrollBehavior = 'auto';

                            startX = e.pageX - rightBody.offsetLeft;
                            scrollLeft = rightBody.scrollLeft;

                            startY = e.pageY - rightBody.offsetTop;
                            scrollTop = rightBody.scrollTop;
                        });

                        rightPanel.addEventListener('mouseleave', () => {
                            isDown = false;
                            rightPanel.classList.remove('is-dragging');
                            rightBody.style.scrollBehavior = 'smooth';
                        });

                        rightPanel.addEventListener('mouseup', () => {
                            isDown = false;
                            rightPanel.classList.remove('is-dragging');
                            rightBody.style.scrollBehavior = 'smooth';
                        });

                        rightPanel.addEventListener('mousemove', (e) => {
                            if (!isDown) return;
                            e.preventDefault();

                            const x = e.pageX - rightBody.offsetLeft;
                            const walkX = (x - startX) * 1.5;
                            rightBody.scrollLeft = scrollLeft - walkX;

                            const y = e.pageY - rightBody.offsetTop;
                            const walkY = (y - startY) * 1.5;
                            rightBody.scrollTop = scrollTop - walkY;
                        });
                    }
                }, 50);
            }

            function stringToColor(str) {
                let hash = 0;
                for (let i = 0; i < str.length; i++) {
                    hash = str.charCodeAt(i) + ((hash << 5) - hash);
                }
                let color = '#';
                for (let i = 0; i < 3; i++) {
                    let value = (hash >> (i * 8)) & 0xFF;
                    value = Math.max(50, Math.min(150, value));
                    color += ('00' + value.toString(16)).substr(-2);
                }
                return color;
            }

            function renderEmptyState(searchTerm, isBalance, targetBody) {
                const message = searchTerm ? 'No hay registros que coincidan con la búsqueda.' :
                    (isBalance ? 'No hay balances de vacaciones registrados.' :
                        'No hay historial de vacaciones tomadas en la base de datos.');

                targetBody.innerHTML = `
            <tr><td colspan="100%">
                <div class="empty-state" style="text-align:center;padding:50px;">
                    <i class="fas fa-calendar-times" style="font-size:3em;color:var(--light-gray);margin-bottom:15px;"></i>
                    <h4 style="color:var(--dark-gray);">${message}</h4>
                </div>
            </td></tr>`;
            }

            function renderPagination(totalPages) {
                paginationLinksContainer.innerHTML = '';
                if (totalPages <= 1) return;

                const prev = document.createElement('a');
                prev.href = '#';
                prev.className = 'pagination-item';
                if (currentPage === 1) prev.classList.add('disabled');
                prev.innerHTML = '&laquo; Anterior';
                prev.addEventListener('click', e => {
                    e.preventDefault();
                    if (currentPage > 1) {
                        currentPage--;
                        renderTableAndPagination();
                    }
                });
                paginationLinksContainer.appendChild(prev);

                for (let i = 1; i <= totalPages; i++) {
                    const link = document.createElement('a');
                    link.href = '#';
                    link.className = 'pagination-item';
                    if (i === currentPage) link.classList.add('active');
                    link.textContent = i;
                    link.addEventListener('click', e => {
                        e.preventDefault();
                        currentPage = i;
                        renderTableAndPagination();
                    });
                    paginationLinksContainer.appendChild(link);
                }

                const next = document.createElement('a');
                next.href = '#';
                next.className = 'pagination-item';
                if (currentPage === totalPages) next.classList.add('disabled');
                next.innerHTML = 'Siguiente &raquo;';
                next.addEventListener('click', e => {
                    e.preventDefault();
                    if (currentPage < totalPages) {
                        currentPage++;
                        renderTableAndPagination();
                    }
                });
                paginationLinksContainer.appendChild(next);
            }

            // ─── LÓGICA DE CRUD DE BALANCES ───────────────
            async function saveBalance() {
                const formData = new FormData(vacationForm);
                const balanceId = formData.get('id');
                const url = balanceId ? `/rh/loadchart/employee_vacation_balance/${balanceId}` :
                    '/rh/loadchart/employee_vacation_balance';

                if (balanceId) {
                    formData.append('_method', 'PUT');
                } else {
                    employeeIdHidden.value = employeeIdSelect.value;
                }
                formData.delete('years_of_service');

                submitBtnModal.disabled = true;
                submitBtnModal.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                    const data = await response.json();

                    if (response.ok) {
                        closeFormModal();
                        Swal?.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        await refreshData();
                    } else if (response.status === 422 && data.errors) {
                        const errorMessages = Object.values(data.errors).map(e => `<li>${e[0]}</li>`).join('');
                        Swal?.fire({
                            icon: 'warning',
                            title: '¡Revisa los Datos!',
                            html: `<ul>${errorMessages}</ul>`,
                            confirmButtonText: 'Corregir'
                        });
                    } else {
                        Swal?.fire({
                            icon: 'error',
                            title: 'Error Inesperado',
                            text: data.message || 'Inténtalo de nuevo.',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                } catch (err) {
                    Swal?.fire({
                        icon: 'error',
                        title: 'Error de Conexión',
                        text: 'Verifica tu red.',
                        confirmButtonText: 'Aceptar'
                    });
                } finally {
                    submitBtnModal.disabled = false;
                    submitBtnModal.innerHTML = formData.get('id') ?
                        '<i class="fas fa-save"></i> Actualizar Balance' :
                        '<i class="fas fa-save"></i> Guardar Balance';
                }
            }

            async function editBalance(balanceId) {
                try {
                    const response = await fetch(`/rh/loadchart/employee_vacation_balance/${balanceId}/edit`);
                    const balance = await response.json();

                    document.getElementById('balance_id').value = balance.id;
                    employeeIdSelect.value = balance.employee_id;
                    employeeIdHidden.value = balance.employee_id;
                    document.getElementById('vacation_days_available').value = balance.vacation_days_available;
                    document.getElementById('rest_days_available').value = balance.rest_days_available;
                    document.getElementById('years_of_service').value = balance.years_of_service;
                    document.getElementById('rest_mode').value = balance.rest_mode || '5x2';

                    formTitleModal.innerHTML = '<i class="fas fa-edit"></i> Editar Balance de Vacaciones';
                    submitBtnModal.innerHTML = '<i class="fas fa-save"></i> Actualizar Balance';
                    currentBalanceId = balance.id;
                    formModal.style.display = 'flex';
                } catch (err) {
                    Swal?.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo cargar el balance para editar.'
                    });
                }
            }

            function confirmDelete(balanceId) {
                currentBalanceId = balanceId;
                currentAction = 'delete';
                confirmModal.style.display = 'flex';
            }

            async function deleteBalance(balanceId) {
                try {
                    const response = await fetch(`/rh/loadchart/employee_vacation_balance/${balanceId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json',
                        },
                    });
                    const data = await response.json();

                    if (response.ok) {
                        confirmModal.style.display = 'none';
                        Swal?.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        await refreshData();
                    } else {
                        Swal?.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                } catch (err) {
                    Swal?.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Verifica tu red.'
                    });
                }
            }

            function resetForm() {
                vacationForm.reset();
                document.getElementById('balance_id').value = '';
                formTitleModal.innerHTML = '<i class="fas fa-plus-circle"></i> Agregar Balance';
            }

            function closeFormModal() {
                formModal.style.display = 'none';
                resetForm();
            }

            // ─── Modal Historial (ACTUALIZADO A 4 ESTATUS) ─────────────────────────
            function openHistoryModal(employeeData) {
                currentEmployeeHistory = employeeData;
                document.getElementById('historyEmployeeName').textContent = employeeData.full_name;
                document.getElementById('historyEmployeeNumber').textContent = employeeData.employee_number ??
                'N/A';
                document.getElementById('historyEmployeeArea').textContent = employeeData.area ?? 'N/A';
                document.getElementById('historyHireDate').textContent = formatHireDate(employeeData.hire_date);
                renderHistoryTable(employeeData.vacation_days_details);
                historyModal.style.display = 'flex';
            }

            function renderHistoryTable(vacationDetails) {
                historyTableBody.innerHTML = '';

                // Reseteamos los contadores
                let underReviewCount = 0;
                let reviewedCount = 0;
                let approvedCount = 0;
                let rejectedCount = 0;

                if (!vacationDetails?.length) {
                    updateHistorySummary(0, 0, 0, 0, 0);
                    return;
                }

                const sorted = [...vacationDetails].sort((a, b) => new Date(b.date) - new Date(a.date));
                sorted.forEach(detail => {
                    const d = new Date(detail.date + 'T00:00:00');
                    const s = translateAndStyleStatus(detail.status);

                    // Sumamos para el resumen de 4 Estatus
                    if (s.class === 'bajo_revision') underReviewCount++;
                    else if (s.class === 'revisado') reviewedCount++;
                    else if (s.class === 'aprobado') approvedCount++;
                    else if (s.class === 'rechazado') rejectedCount++;

                    const row = document.createElement('tr');
                    row.innerHTML = `
                <td>${d.getDate().toString().padStart(2,'0')}/${(d.getMonth()+1).toString().padStart(2,'0')}/${d.getFullYear()}</td>
                <td>${getMonthName(d.getMonth())}</td>
                <td>${d.getFullYear()}</td>
                <td><span class="status-badge ${s.class}" style="background-color:${s.color}; color:${s.text};">${s.display}</span></td>
                <td>Vacaciones</td>`;
                    historyTableBody.appendChild(row);
                });

                updateHistorySummary(underReviewCount, reviewedCount, approvedCount, rejectedCount, sorted.length);
            }

            function updateHistorySummary(underReview, reviewed, approved, rejected, total) {
                document.getElementById('totalUnderReview').textContent = underReview;
                document.getElementById('totalReviewed').textContent = reviewed;
                document.getElementById('totalApproved').textContent = approved;
                document.getElementById('totalRejected').textContent = rejected;
                document.getElementById('totalDays').textContent = total;
            }

            function closeHistoryModalFunc() {
                historyModal.style.display = 'none';
            }

 // ─── LÓGICA DE REPORTES PDF ──────────────────────────────────
            function toggleReportFilters() {
                const isTaken = reportTypeSelect.value === 'TAKEN';
                dateRangeGroup.style.display = isTaken ? 'grid' : 'none';
                statusFilterGroup.style.display = isTaken ? 'block' : 'none';
            }

            async function generateReport() {
                generateReportBtn.disabled = true;
                generateReportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';

                // ✅ CORRECCIÓN: Se usa "new URLSearchParams" correctamente
                const formData = new FormData(reportForm);
                const params = new URLSearchParams(formData);

                try {
                    const response = await fetch('/rh/loadchart/employee_vacation_balance/generate-report', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            // ✅ Agregamos explícitamente el token CSRF por seguridad
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: params,
                    });

                    // Verificamos si la respuesta es exitosa y NO es JSON (lo que significa que es el PDF)
                    if (response.ok && !response.headers.get('content-type')?.includes('application/json')) {
                        const blob = await response.blob();
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;

                        // Nombre dinámico para el archivo
                        const typeStr = reportTypeSelect.value === 'AVAILABLE' ? 'disponibles' : 'tomadas';
                        a.download = `reporte_vacaciones_${typeStr}.pdf`;

                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url); // Limpiamos la memoria

                        reportModal.style.display = 'none';
                    } else {
                        // Si el backend devolvió un JSON (error de validación o error interno)
                        const data = await response.json();

                        if (response.status === 422) {
                            // Error de validación (ej. falta seleccionar algo)
                            const errorMessages = Object.values(data.errors).map(e => `<li>${e[0]}</li>`).join('');
                            Swal?.fire({
                                icon: 'warning',
                                title: 'Faltan datos',
                                html: `<ul style="text-align: left; margin: 0;">${errorMessages}</ul>`,
                                confirmButtonText: 'Entendido'
                            });
                        } else {
                            // Error 500 u otro
                            Swal?.fire({
                                icon: 'error',
                                title: 'Error al generar',
                                text: data.message || 'Ocurrió un problema en el servidor al crear el PDF.',
                                confirmButtonText: 'Cerrar'
                            });
                        }
                    }
                } catch (err) {
                    console.error("Error en la petición del reporte:", err);
                    Swal?.fire({
                        icon: 'error',
                        title: 'Error de Red',
                        text: 'No se pudo conectar con el servidor. Verifica tu conexión.',
                        confirmButtonText: 'Aceptar'
                    });
                } finally {
                    // Restauramos el botón a su estado original
                    generateReportBtn.disabled = false;
                    generateReportBtn.innerHTML = '<i class="fas fa-download"></i> Generar Reporte PDF';
                }
            }

            // ─── Listeners ────────────────────────────────────────────────────────
            toggleVacationViewBtn.addEventListener('click', () => {
                isBalanceView = !isBalanceView;

                if (isBalanceView) {
                    lastScrollLeft = null;
                    lastScrollTop = null;
                }

                currentPage = 1;
                renderTableAndPagination();
            });

            // ✅ RECUPERAMOS LOS LISTENERS DEL FORMULARIO
            openAddFormBtn.addEventListener('click', () => {
                if (isBalanceView) {
                    resetForm();
                    formModal.style.display = 'flex';
                }
            });

            vacationForm.addEventListener('submit', e => {
                e.preventDefault();
                saveBalance();
            });

            cancelEditModal.addEventListener('click', closeFormModal);
            closeFormModalBtn.addEventListener('click', closeFormModal);


            // Delegación de clics para tabla de balances e historial
            document.addEventListener('click', function(e) {
                if (isBalanceView) {
                    const editBtn = e.target.closest('.btn-edit');
                    const deleteBtn = e.target.closest('.btn-delete');
                    if (editBtn) editBalance(editBtn.dataset.balanceId);
                    if (deleteBtn) confirmDelete(deleteBtn.dataset.balanceId);
                } else {
                    const historyBtn = e.target.closest('.btn-history');
                    if (historyBtn) {
                        const empName = historyBtn.dataset.employeeId;
                        const takenEmp = vacationDaysTakenData.find(i => i.full_name === empName);

                        if (takenEmp && takenEmp.vacation_days_details && takenEmp.vacation_days_details
                            .length > 0) {
                            openHistoryModal(takenEmp);
                        } else {
                            const mockEmp = {
                                full_name: empName,
                                area: historyBtn.closest('.employee-info-cell').querySelector(
                                    '.emp-area').textContent,
                                employee_number: 'N/A',
                                hire_date: 'N/A',
                                vacation_days_details: []
                            };
                            openHistoryModal(mockEmp);
                        }
                    }
                }
            });

            openReportModalBtn.addEventListener('click', () => {
                reportModal.style.display = 'flex';
                toggleReportFilters();
            });
            closeReportModalBtn.addEventListener('click', () => reportModal.style.display = 'none');
            cancelReportModal.addEventListener('click', () => reportModal.style.display = 'none');
            generateReportBtn.addEventListener('click', generateReport);
            reportTypeSelect.addEventListener('change', toggleReportFilters);
            closeHistoryModalBtn.addEventListener('click', closeHistoryModalFunc);
            closeHistoryModal.addEventListener('click', closeHistoryModalFunc);

            searchInput.addEventListener('input', () => {
                currentPage = 1;
                renderTableAndPagination();
            });
            filterDepartment.addEventListener('change', () => {
                currentPage = 1;
                renderTableAndPagination();
            });

            initialize();
        });
    </script>
@endsection
