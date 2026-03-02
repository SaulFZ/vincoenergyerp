@extends('modulos.recursoshumanos.loadchart.index')

@section('header_metadata')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <div class="container">
        <div class="content-layout">
            {{-- Sección del Formulario Original - OCULTA PERMANENTEMENTE --}}
            <div class="form-section" style="display: none;"></div>

            <div class="list-section" style="width: 100%;">
                <div class="card">
                    <div class="card-header">
                        <div class="header-section" id="listHeader">
                            <h1><i class="fas fa-list"></i> Balances de Vacaciones Registrados</h1>
                            <p>Administra los días de vacaciones y descanso disponibles para cada empleado</p>
                        </div>
                        <div class="header-actions">
                            {{-- BOTÓN PARA ABRIR EL MODAL EN MODO CREAR/AGREGAR --}}
                            <button type="button" class="btn btn-primary" id="openAddFormBtn" style="display: none !important;">
                                <i class="fas fa-plus-circle"></i> Agregar Balance
                            </button>

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
                            <button type="button" class="btn btn-info" id="openReportModalBtn" title="Generar Reporte PDF" style="display: none;">
                                <i class="fas fa-file-pdf"></i> Reporte
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Contenedor para la vista de Balances Disponibles --}}
                        <div id="balanceViewContainer">
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Empleado</th>
                                            <th>Fecha de Ingreso</th>
                                            <th>Años de Servicio</th>
                                            <th>Modalidad Descanso</th>
                                            <th>Días de Vacaciones</th>
                                            <th>Días de Descanso</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="vacationTableBody"></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Contenedor para la vista de Vacaciones Tomadas --}}
                        <div id="takenViewContainer" style="display: none;">
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>No. Empleado</th>
                                            <th>Nombre del Empleado</th>
                                            <th>Fecha de Ingreso</th>
                                            <th>Área</th>
                                            <th>Días Disponibles</th>
                                            <th>Últimos Días Tomados</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="takenTableBody"></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- PAGINACIÓN --}}
                        <div class="pagination-container">
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
                            <input type="number" id="vacation_days_available" name="vacation_days_available" class="input-custom" min="0" placeholder="0" required>
                        </div>

                        <div class="form-group">
                            <label for="rest_days_available">Días de Descanso Disponibles</label>
                            <input type="number" id="rest_days_available" name="rest_days_available" class="input-custom" min="0" placeholder="0" required>
                        </div>

                        <div class="form-group">
                            <label for="years_of_service">Años de Servicio</label>
                            <input type="number" id="years_of_service" name="years_of_service" class="input-custom" min="0" placeholder="0" required readonly>
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="rest_mode">Modalidad de Descanso</label>
                            <select id="rest_mode" name="rest_mode" class="select-custom" required>
                                <option value="5x2" selected>5 días trabajo x 2 días descanso</option>
                                <option value="6x1">6 días trabajo x 1 día descanso</option>
                                <option value="24x6">24 días trabajo x 6 días descanso (Rotativo)</option>
                                <option value="12x24hr">12 horas trabajo x 24 horas descanso (Turno)</option>
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
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3><i class="fas fa-history"></i> Historial Completo de Vacaciones</h3>
                <button class="close-modal" id="closeHistoryModalBtn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="employee-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e2e8f0;">
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

                <div class="history-summary" style="margin-bottom: 20px; padding: 15px; background: #e8f4fd; border-radius: 8px;">
                    <h5 style="margin: 0 0 10px 0; color: #2d3748;">Resumen</h5>
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; text-align: center;">
                        <div>
                            <div style="font-size: 24px; font-weight: bold; color: var(--success);" id="totalApproved">0</div>
                            <div style="font-size: 12px; color: #4a5568;">Aprobados</div>
                        </div>
                        <div>
                            <div style="font-size: 24px; font-weight: bold; color: var(--warning);" id="totalPending">0</div>
                            <div style="font-size: 12px; color: #4a5568;">Pendientes</div>
                        </div>
                        <div>
                            <div style="font-size: 24px; font-weight: bold; color: var(--danger);" id="totalRejected">0</div>
                            <div style="font-size: 12px; color: #4a5568;">Rechazados</div>
                        </div>
                        <div>
                            <div style="font-size: 24px; font-weight: bold; color: #4299e1;" id="totalDays">0</div>
                            <div style="font-size: 12px; color: #4a5568;">Total Días</div>
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

    {{-- MODAL FLOTANTE PARA GENERACIÓN DE REPORTES - MEJORADO --}}
    <div class="modal" id="reportModal">
        <div class="modal-content" style="max-width: 750px;">
            <div class="modal-header" style="background-color: var(--primary-blue); color: var(--white); border-top-left-radius: 10px; border-top-right-radius: 10px;">
                <h3 style="color: var(--white); margin: 0;"><i class="fas fa-file-pdf"></i> Generar Reporte de Vacaciones</h3>
                <button class="close-modal" id="closeReportModalBtn" style="color: var(--white); opacity: 0.8;">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert-info" style="margin-bottom: 20px; padding: 10px; border-radius: 6px; background: #e8f4fd; border: 1px solid #99c2e0;">
                    <i class="fas fa-info-circle" style="color: #4299e1;"></i> Los filtros aplicados aquí solo afectan la generación del reporte PDF, no la tabla principal.
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

                        {{-- RANGO DE FECHAS (Visible solo para Días Tomados) --}}
                        <div id="date_range_group" style="display: none; grid-column: 1 / -1; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; border: 1px dashed #cbd5e0; padding: 15px; border-radius: 6px;">
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
                            <label for="report_department"><i class="fas fa-sitemap"></i> Departamento / Área</label>
                            <div class="multi-select-container">
                                <div class="select-all-container">
                                    <label class="checkbox-label" style="font-weight: bold; color: var(--primary-blue);">
                                        <input type="checkbox" id="select_all_departments"> Seleccionar Todo
                                    </label>
                                </div>
                                <div class="checkbox-group" id="department_checkbox_group" style="max-height: 150px;">
                                    @foreach ($departments as $department)
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="departments[]" value="{{ $department }}" checked> {{ $department }}
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
                                        <label class="checkbox-label employee-option" data-department="{{ $employee->department }}" style="display: flex;">
                                            <input type="checkbox" name="employees[]" value="{{ $employee->id }}" checked>
                                            {{ $employee->full_name }} ({{ $employee->employee_number ?? 'N/A' }})
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- FILTRO DE ESTATUS (Opcional, solo para Días Tomados) --}}
                        <div class="form-group" id="status_filter_group" style="grid-column: 1 / -1; display: none; border: 1px dashed #cbd5e0; padding: 15px; border-radius: 6px;">
                            <label><i class="fas fa-info-circle"></i> Estatus de Días Tomados</label>
                            <div class="checkbox-group" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; max-height: 150px; overflow-y: auto;">
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

                {{-- Resumen/Preview - Mejorado --}}
                <div class="history-summary" style="margin-top: 30px; padding: 15px; background: #fffbe6; border-radius: 8px; border: 1px solid #ffe8b1;">
                    <h5 style="margin: 0 0 10px 0; color: #744210;"><i class="fas fa-check-circle"></i> Resumen del Reporte a Generar</h5>
                    <div id="report-summary-text" style="font-size: 0.9rem; color: #744210; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div><strong>Tipo:</strong> <span id="summary-type">Días Disponibles</span></div>
                        <div><strong>Empleados:</strong> <span id="summary-employees">Todos</span></div>
                        <div style="grid-column: 1 / -1;"><strong>Departamentos:</strong> <span id="summary-departments">Todos</span></div>
                        <div id="summary-dates" style="display: none;"><strong>Período:</strong> <span id="summary-dates-text"></span></div>
                        <div id="summary-status" style="display: none;"><strong>Estatus:</strong> <span id="summary-status-text"></span></div>
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
        /* Estilos CSS (Sin cambios en las variables base, solo añado para el modal de reportes) */
        :root {
            --dark-gray: #2d3748;
            --medium-gray: #4a5568;
            --light-gray: #e2e8f0;
            --background-gray: #f7fafc;
            --primary-blue: #283848;
            --secondary-blue: #34495e;
            --white: #ffffff;
            /* Colores Generales (Disponible, Por Vencer, Agotado) */
            --success: #48bb78;
            --warning: #f6ad55;
            --danger: #e53e3e;
            --dark-red: #9B2C2C;

            /* Colores de Estado de Detalle */
            --under-review: #ffd900;
            --reviewed-detail: #da8544;
            --approved-detail: #64946f;
            --rejected-detail: #f35900;
        }

        .container {
            padding: 20px;
        }

        /* HACEMOS QUE EL LAYOUT SEA 1 COLUMNA, ya que la sección del formulario se oculta */
        .content-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .list-section {
            width: 100%;
            /* Asegura que la lista ocupe todo el ancho */
        }

        /* Fin de ajustes de layout */

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
        }

        .card-header h3 {
            color: var(--primary-blue);
            margin: 0;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }

        .card-header h3 i {
            margin-right: 10px;
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

        .form-group label i {
            margin-right: 5px;
            color: #4299e1;
        }

        .input-custom,
        .select-custom {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background-color: var(--white);
            box-sizing: border-box; /* Asegura que padding no aumente el ancho */
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
        }

        .btn-primary {
            background-color: var(--primary-blue);
            color: var(--white);
            border: 1px solid var(--primary-blue);
        }

        .btn-primary:hover {
            background-color: var(--secondary-blue);
            border-color: var(--secondary-blue);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--medium-gray);
            border: 1px solid var(--light-gray);
        }

        .btn-outline:hover {
            background-color: var(--light-gray);
            color: var(--dark-gray);
        }

        .btn-info {
            background-color: #4299e1;
            color: var(--white);
            border: 1px solid #4299e1;
        }

        .btn-info:hover {
            background-color: #3182ce;
            border-color: #3182ce;
        }

        .header-section h1 {
            font-size: 1.5rem;
            color: var(--primary-blue);
            margin-bottom: 5px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            /* Permite que los botones se envuelvan en pantallas pequeñas */
        }

        .search-box {
            position: relative;
            width: 250px;
        }

        .search-box i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--medium-gray);
            font-size: 1.1rem;
        }

        .search-box input {
            padding: 10px 12px 10px 35px;
            width: 100%;
            border: 1px solid var(--light-gray);
            border-radius: 6px;
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
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
            text-transform: capitalize;
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

        /* --- MEJORAS DE BOTONES DE ACCIÓN EN TABLA --- */
        .btn-icon {
            background: none;
            border: 1px solid transparent;
            /* Base transparente */
            padding: 8px;
            /* Aumentar el padding para mejor área de clic */
            border-radius: 8px;
            /* Bordes más suaves */
            cursor: pointer;
            color: var(--medium-gray);
            transition: all 0.2s ease;
        }

        .btn-icon:hover {
            background: var(--light-gray);
            /* Fondo gris claro al pasar el mouse */
            color: var(--dark-gray);
        }

        .btn-edit:hover {
            color: var(--approved-detail);
            /* Color verde para editar */
            border-color: var(--approved-detail);
            background: rgba(100, 148, 111, 0.1);
            /* Fondo muy suave de color de éxito */
        }

        .btn-delete:hover {
            color: var(--rejected-detail);
            /* Color naranja/rojo para eliminar */
            border-color: var(--rejected-detail);
            background: rgba(243, 89, 0, 0.1);
            /* Fondo muy suave de color de peligro */
        }

        .btn-history:hover {
            color: #4299e1;
            /* Color azul para historial */
            border-color: #4299e1;
            background: rgba(66, 153, 225, 0.1);
        }

        /* --- FIN DE MEJORAS DE BOTONES DE ACCIÓN --- */

        /* ESTILOS DE ESTATUS GENERAL (Disponible, Por Vencer, Agotado) */
        .status-badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 11px;
            text-transform: uppercase;
        }

        .status-badge.disponible {
            background-color: var(--success);
            color: var(--white);
        }

        .status-badge.por_vencer {
            background-color: var(--warning);
            color: #744210;
        }

        .status-badge.agotado {
            background-color: var(--dark-red);
            color: var(--white);
        }

        /* ESTILOS DE ESTATUS DE DETALLE (Bajo Revision, Aprobado, Rechazado, Revisado) */
        .status-badge.under_review,
        .status-badge.bajo_revision {
            background-color: var(--under-review);
            color: #4b3e00;
            text-transform: capitalize;
        }

        .status-badge.reviewed,
        .status-badge.revisado {
            background-color: var(--reviewed-detail);
            color: var(--white);
            text-transform: capitalize;
        }

        .status-badge.approved,
        .status-badge.aprobado {
            background-color: var(--approved-detail);
            color: var(--white);
            text-transform: capitalize;
        }

        .status-badge.rejected,
        .status-badge.rechazado {
            background-color: var(--rejected-detail);
            color: var(--white);
            text-transform: capitalize;
        }

        /* Estilos del Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: var(--white);
            margin: auto;
            padding: 0;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.3s;
        }

        #formModal .modal-content {
            max-width: 600px;
            /* Ancho para el formulario */
        }

        #historyModal .modal-content {
            max-width: 800px;
            /* Ancho para el historial */
        }

        #reportModal .modal-content {
            max-width: 750px;
            /* Ancho para el reporte */
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

        #confirmModal .modal-body {
            text-align: center;
            /* Se mantiene para el modal de confirmación */
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
            border: 1px solid var(--danger);
        }

        .btn-danger:hover {
            background-color: #c53030;
            border-color: #c53030;
        }

        /* --- ESTILOS DE PAGINACIÓN --- */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid var(--light-gray);
        }

        .per-page-selector span {
            color: var(--dark-gray);
            margin-right: 5px;
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
            transition: background-color 0.3s, color 0.3s;
            font-size: 0.9rem;
        }

        .pagination-item:hover:not(.active):not(.disabled) {
            background-color: var(--light-gray);
        }

        .pagination-item.active {
            background-color: var(--primary-blue);
            color: var(--white);
            border-color: var(--primary-blue);
        }

        .pagination-item.disabled {
            color: var(--medium-gray);
            cursor: not-allowed;
            opacity: 0.6;
            background-color: #f7fafc;
        }

        /* Estilos para días disponibles con estatus integrado */
        .days-with-status {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .days-count {
            font-weight: 600;
            color: var(--dark-gray);
        }

        /* Estilos para los nuevos elementos del modal de reportes */
        .multi-select-container {
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            padding: 10px;
            max-height: 200px;
            overflow-y: auto;
            background-color: var(--white);
            box-sizing: border-box;
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
            transition: background-color 0.2s;
        }

        .checkbox-label:hover {
            background-color: var(--background-gray);
        }

        .checkbox-label input[type="checkbox"] {
            margin: 0;
            width: 16px;
            height: 16px;
            accent-color: var(--primary-blue);
        }

        .btn-clear {
            background: none;
            border: 1px solid var(--light-gray);
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 0.8rem;
            cursor: pointer;
            color: var(--medium-gray);
        }

        .btn-clear:hover {
            background-color: var(--light-gray);
        }

        /* Estilos para el resumen del reporte */
        #report-summary-text > div {
            margin-bottom: 5px;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Variables y referencias existentes...
            const vacationBalancesData = @json($vacationBalances);
            const vacationDaysTakenData = @json($vacationDaysTaken);
            const employeesData = @json($employees);

            let currentPage = 1;
            const perPageSelector = document.getElementById('perPageSelector');
            let itemsPerPage = parseInt(perPageSelector.value);
            const searchInput = document.getElementById('searchVacation');
            const listHeader = document.getElementById('listHeader');
            const toggleVacationViewBtn = document.getElementById('toggleVacationView');
            const balanceViewContainer = document.getElementById('balanceViewContainer');
            const takenViewContainer = document.getElementById('takenViewContainer');
            const takenTableBody = document.getElementById('takenTableBody');
            const paginationLinksContainer = document.getElementById('pagination-links');

            // 🥇 Referencia al botón de reporte
            const openReportModalBtn = document.getElementById('openReportModalBtn');

            // Elementos del FORMULARIO MODAL
            const formModal = document.getElementById('formModal');
            const formTitleModal = document.getElementById('formTitleModal');
            const vacationForm = document.getElementById('vacationForm');
            const submitBtnModal = document.getElementById('submitBtnModal');
            const cancelEditModal = document.getElementById('cancelEditModal');
            const closeFormModalBtn = document.getElementById('closeFormModalBtn');
            const employeeIdSelect = document.getElementById('employee_id');
            const employeeIdHidden = document.getElementById('employee_id_hidden');

            // Elementos del Modal de CONFIRMACIÓN
            const confirmModal = document.getElementById('confirmModal');
            const confirmActionBtn = document.getElementById('confirmAction');

            // Elementos del Modal de HISTORIAL
            const historyModal = document.getElementById('historyModal');
            const closeHistoryModalBtn = document.getElementById('closeHistoryModalBtn');
            const closeHistoryModal = document.getElementById('closeHistoryModal');
            const searchHistoryInput = document.getElementById('searchHistory');
            const historyTableBody = document.getElementById('historyTableBody');

            // 🥇 Elementos del Modal de REPORTES - MEJORADOS
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
            const dateFromError = document.getElementById('date_from_error');
            const dateToError = document.getElementById('date_to_error');

            // Nuevos elementos para los filtros mejorados
            const selectAllDepartments = document.getElementById('select_all_departments');
            const departmentCheckboxGroup = document.getElementById('department_checkbox_group');
            const selectAllEmployees = document.getElementById('select_all_employees');
            const clearEmployeesBtn = document.getElementById('clear_employees');
            const employeeCheckboxGroup = document.getElementById('employee_checkbox_group');
            const employeeOptions = document.querySelectorAll('.employee-option');

            // Elementos del resumen
            const summaryType = document.getElementById('summary-type');
            const summaryDepartments = document.getElementById('summary-departments');
            const summaryEmployees = document.getElementById('summary-employees');
            const summaryDates = document.getElementById('summary-dates');
            const summaryDatesText = document.getElementById('summary-dates-text');
            const summaryStatus = document.getElementById('summary-status');
            const summaryStatusText = document.getElementById('summary-status-text');

            let isBalanceView = true;
            let currentEmployeeHistory = null;
            let currentBalanceId = null;
            let currentAction = '';

            // Funciones auxiliares existentes...
            function formatHireDate(dateString) {
                if (!dateString || dateString === 'N/A') return 'N/A';
                const parts = dateString.split('-');
                if (parts.length === 3) {
                    return `${parts[2]}/${parts[1]}/${parts[0]}`;
                }
                return dateString;
            }

            function getMonthName(monthNumber) {
                const months = [
                    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                ];
                return months[monthNumber - 1] || 'Mes desconocido';
            }

            function getAvailableDaysForTakenView(employeeName) {
                const normalizedName = employeeName.trim().toLowerCase();
                const balanceEntry = vacationBalancesData.find(item => {
                    const fullName = item.employee ? item.employee.full_name : 'Empleado no encontrado';
                    return fullName.toLowerCase() === normalizedName;
                });
                return balanceEntry ? parseInt(balanceEntry.vacation_days_available) : 0;
            }

            function translateAndStyleStatus(statusEnglish) {
                const normalizedStatus = statusEnglish.toLowerCase().replace(/\s/g, '_');
                let displayStatus;
                let cssClass;

                switch (normalizedStatus) {
                    case 'under_review':
                        displayStatus = 'Bajo Revisión';
                        cssClass = 'bajo_revision';
                        break;
                    case 'reviewed':
                        displayStatus = 'Revisado';
                        cssClass = 'revisado';
                        break;
                    case 'approved':
                        displayStatus = 'Aprobado';
                        cssClass = 'aprobado';
                        break;
                    case 'rejected':
                        displayStatus = 'Rechazado';
                        cssClass = 'rechazado';
                        break;
                    default:
                        displayStatus = statusEnglish;
                        cssClass = normalizedStatus;
                }
                return {
                    display: displayStatus,
                    class: cssClass
                };
            }

            function isAnniversaryApproaching(hireDateString) {
                if (!hireDateString || hireDateString === 'N/A') return false;
                const hireDateParts = hireDateString.split('-');
                if (hireDateParts.length !== 3) return false;

                const today = new Date();
                const hireDay = parseInt(hireDateParts[2]);
                const hireMonth = parseInt(hireDateParts[1]) - 1;

                let anniversary = new Date(today.getFullYear(), hireMonth, hireDay);
                if (anniversary < today) {
                    anniversary.setFullYear(today.getFullYear() + 1);
                }

                const oneDay = 24 * 60 * 60 * 1000;
                const diffDays = Math.ceil((anniversary.getTime() - today.getTime()) / oneDay);
                return diffDays > 0 && diffDays <= 30;
            }

            function getGeneralStatus(availableDays, hireDate) {
                if (availableDays <= 0) {
                    return {
                        text: 'AGOTADO',
                        class: 'agotado'
                    };
                }
                if (isAnniversaryApproaching(hireDate)) {
                    return {
                        text: 'POR VENCER',
                        class: 'por_vencer'
                    };
                }
                return {
                    text: 'DISPONIBLE',
                    class: 'disponible'
                };
            }

            // Funciones de renderizado existentes...
            function initialize() {
                renderTableAndPagination();
            }

            function renderTableAndPagination() {
                const searchTerm = searchInput.value.toLowerCase();
                let dataToUse = isBalanceView ? vacationBalancesData : vacationDaysTakenData;

                const tempFiltered = dataToUse.filter(item => {
                    const fullName = isBalanceView ?
                        (item.employee ? item.employee.full_name : 'Empleado no encontrado') :
                        item.full_name;
                    return fullName.toLowerCase().includes(searchTerm);
                });
                let filteredData = tempFiltered;

                const isAll = itemsPerPage === 'all';
                const totalPages = isAll ? 1 : Math.ceil(filteredData.length / itemsPerPage);

                if (currentPage > totalPages && totalPages > 0) {
                    currentPage = totalPages;
                } else if (totalPages === 0) {
                    currentPage = 1;
                }

                const startIndex = isAll ? 0 : (currentPage - 1) * itemsPerPage;
                const endIndex = isAll ? filteredData.length : startIndex + itemsPerPage;
                const itemsToDisplay = filteredData.slice(startIndex, endIndex);

                balanceViewContainer.style.display = isBalanceView ? 'block' : 'none';
                takenViewContainer.style.display = isBalanceView ? 'none' : 'block';

                if (isBalanceView) {
                    renderBalanceViewHeader();
                    renderBalanceTableRows(itemsToDisplay);
                    // 🌟 Ocultar botón de reporte en vista de Balance
                    openReportModalBtn.style.display = 'none';
                } else {
                    renderTakenViewHeader();
                    renderTakenTableRows(itemsToDisplay);
                    // 🌟 Mostrar botón de reporte en vista de Tomadas
                    openReportModalBtn.style.display = 'inline-flex';
                }

                renderPagination(totalPages);
            }

            function renderBalanceViewHeader() {
                listHeader.innerHTML = `
                    <h1><i class="fas fa-list"></i> Balances de Vacaciones Registrados</h1>
                    <p>Administra los días de vacaciones y descanso disponibles para cada empleado</p>
                `;
                toggleVacationViewBtn.innerHTML = '<i class="fas fa-calendar-check"></i> Ver Vacaciones Tomadas';
                toggleVacationViewBtn.classList.remove('btn-outline');
                toggleVacationViewBtn.classList.add('btn-primary');
            }

            function renderTakenViewHeader() {
                listHeader.innerHTML = `
                    <h1><i class="fas fa-calendar-alt"></i> Historial de Vacaciones Tomadas</h1>
                    <p>Detalle de los días de vacaciones que han sido consumidos</p>
                `;
                toggleVacationViewBtn.innerHTML = '<i class="fas fa-balance-scale"></i> Ver Balances Disponibles';
                toggleVacationViewBtn.classList.remove('btn-primary');
                toggleVacationViewBtn.classList.add('btn-outline');
            }

            function renderBalanceTableRows(balancesToDisplay) {
                const vacationTableBody = document.querySelector('#balanceViewContainer #vacationTableBody');
                vacationTableBody.innerHTML = '';
                if (balancesToDisplay.length > 0) {
                    balancesToDisplay.forEach(balance => {
                        const row = document.createElement('tr');
                        row.dataset.balanceId = balance.id;

                        const employeeName = balance.employee ? balance.employee.full_name : 'Empleado no encontrado';
                        const hireDate = balance.employee ? formatHireDate(balance.employee.hire_date) : 'N/A';

                        row.innerHTML = `
                            <td>${employeeName}</td>
                            <td>${hireDate}</td>
                            <td>${balance.years_of_service} años</td>
                            <td><span class="badge">${balance.rest_mode || '5x2'}</span></td>
                            <td><span class="badge">${balance.vacation_days_available} días</span></td>
                            <td><span class="badge">${balance.rest_days_available} días</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon btn-edit" title="Editar" data-balance-id="${balance.id}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon btn-delete" title="Eliminar" data-balance-id="${balance.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        `;
                        vacationTableBody.appendChild(row);
                    });
                } else {
                    renderEmptyState(searchInput.value, true, vacationTableBody);
                }
            }

            function renderTakenTableRows(itemsToDisplay) {
                takenTableBody.innerHTML = '';
                if (itemsToDisplay.length > 0) {
                    itemsToDisplay.forEach(item => {
                        const row = document.createElement('tr');
                        const availableDays = getAvailableDaysForTakenView(item.full_name);
                        const generalStatus = getGeneralStatus(availableDays, item.hire_date);

                        const recentDetails = item.vacation_days_details ? [...item.vacation_days_details]
                            .filter(d => d.date)
                            .sort((a, b) => new Date(b.date) - new Date(a.date))
                            .slice(0, 1) : [];

                        const detailHtml = recentDetails.map(detail => {
                            const statusInfo = translateAndStyleStatus(detail.status);
                            const dateFormatted = formatHireDate(detail.date);
                            return `
                                <li style="margin-bottom: 2px;">
                                    ${dateFormatted}:
                                    <span class="status-badge ${statusInfo.class}" title="Estatus: ${statusInfo.display}">
                                        ${statusInfo.display}
                                    </span>
                                </li>
                            `;
                        }).join('');

                        const daysAvailableWithStatus = `
                            <div class="days-with-status">
                                <span class="days-count">${availableDays} días</span>
                                <span class="status-badge ${generalStatus.class}">${generalStatus.text}</span>
                            </div>
                        `;

                        const totalDaysTaken = item.vacation_days_details ? item.vacation_days_details.length : 0;
                        const overflowMessage = totalDaysTaken > 1 ?
                            `<li style="color: var(--primary-blue); font-style: italic; font-weight: 600;">
                                +${totalDaysTaken - 1} más...
                            </li>` : '';

                        row.innerHTML = `
                            <td>${item.employee_number || 'N/A'}</td>
                            <td>${item.full_name}</td>
                            <td>${formatHireDate(item.hire_date)}</td>
                            <td>${item.area || 'N/A'}</td>
                            <td>${daysAvailableWithStatus}</td>
                            <td>
                                ${totalDaysTaken > 0 ? `
                                    <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.9em;">
                                        ${detailHtml}
                                        ${overflowMessage}
                                    </ul>
                                ` : 'No hay días tomados'}
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon btn-history" title="Ver Historial Completo" data-employee-id="${item.full_name}">
                                        <i class="fas fa-history"></i>
                                    </button>
                                </div>
                            </td>
                        `;
                        takenTableBody.appendChild(row);
                    });
                } else {
                    renderEmptyState(searchInput.value, false, takenTableBody);
                }
            }

            function renderEmptyState(searchTerm, isBalance = true, targetBody) {
                const message = searchTerm ?
                    'No hay registros que coincidan con la búsqueda.' :
                    (isBalance ? 'No hay balances de vacaciones registrados.' :
                        'No hay historial de vacaciones tomadas.');
                const detail = searchTerm ?
                    'Intenta con otro término de búsqueda.' :
                    (isBalance ? 'Comienza agregando un balance usando el formulario.' :
                        'Asegúrate de que los empleados hayan registrado días de vacaciones (VAC) en sus bitácoras.'
                    );
                const colspan = isBalance ? 7 : 7;

                targetBody.innerHTML = `
                    <tr>
                        <td colspan="${colspan}">
                            <div class="empty-state" style="text-align: center; padding: 30px;">
                                <i class="fas fa-umbrella-beach" style="font-size: 3em; color: var(--light-gray); margin-bottom: 10px;"></i>
                                <h4>${message}</h4>
                                <p>${detail}</p>
                            </div>
                        </td>
                    </tr>
                `;
            }

            function renderPagination(totalPages) {
                paginationLinksContainer.innerHTML = '';
                if (totalPages <= 1) return;

                const prevButton = document.createElement('a');
                prevButton.href = '#';
                prevButton.classList.add('pagination-item');
                if (currentPage === 1) prevButton.classList.add('disabled');
                prevButton.innerHTML = '&laquo; Anterior';
                prevButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (currentPage > 1) {
                        currentPage--;
                        renderTableAndPagination();
                    }
                });
                paginationLinksContainer.appendChild(prevButton);

                const maxPagesToShow = 5;
                let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
                let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

                if (endPage - startPage < maxPagesToShow - 1) {
                    startPage = Math.max(1, endPage - maxPagesToShow + 1);
                }

                for (let i = startPage; i <= endPage; i++) {
                    const pageLink = document.createElement('a');
                    pageLink.href = '#';
                    pageLink.classList.add('pagination-item');
                    if (i === currentPage) pageLink.classList.add('active');
                    pageLink.textContent = i;
                    pageLink.addEventListener('click', (e) => {
                        e.preventDefault();
                        currentPage = i;
                        renderTableAndPagination();
                    });
                    paginationLinksContainer.appendChild(pageLink);
                }

                const nextButton = document.createElement('a');
                nextButton.href = '#';
                nextButton.classList.add('pagination-item');
                if (currentPage === totalPages) nextButton.classList.add('disabled');
                nextButton.innerHTML = 'Siguiente &raquo;';
                nextButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (currentPage < totalPages) {
                        currentPage++;
                        renderTableAndPagination();
                    }
                });
                paginationLinksContainer.appendChild(nextButton);
            }

            // Funciones CRUD existentes...
            async function saveBalance() {
                const formData = new FormData(vacationForm);
                const balanceId = formData.get('id');
                const url = balanceId ?
                    `/recursoshumanos/loadchart/employee_vacation_balance/${balanceId}` :
                    '/recursoshumanos/loadchart/employee_vacation_balance';

                if (balanceId) {
                    formData.append('_method', 'PUT');
                } else {
                    employeeIdHidden.value = employeeIdSelect.value;
                }

                formData.delete('years_of_service');

                try {
                    submitBtnModal.disabled = true;
                    submitBtnModal.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (response.ok) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            alert('¡Éxito! ' + data.message);
                        }
                        closeFormModal();
                        location.reload();
                    } else if (response.status === 422 && data.errors) {
                        let errorMessages = '';
                        for (const field in data.errors) {
                            errorMessages += `<li>${data.errors[field][0]}</li>`;
                        }

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: '¡Revisa los Datos!',
                                html: `<ul>${errorMessages}</ul>`,
                                confirmButtonText: 'Corregir'
                            });
                        } else {
                            alert('¡Revisa los Datos!:\n' + errorMessages.replace(/<\/?li>/g, '').replace(/<\/?ul>/g, ''));
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error Inesperado',
                                text: data.message || 'Ocurrió un error al procesar la solicitud. Inténtalo de nuevo.',
                                confirmButtonText: 'Aceptar'
                            });
                        } else {
                            alert('Error: ' + (data.message || 'Error inesperado'));
                        }
                    }
                } catch (error) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Conexión',
                            text: 'No se pudo conectar con el servidor. Verifica tu red.',
                            confirmButtonText: 'Aceptar'
                        });
                    } else {
                        alert('Error de Conexión. Verifica tu red.');
                    }
                } finally {
                    submitBtnModal.disabled = false;
                    submitBtnModal.innerHTML = balanceId ?
                        '<i class="fas fa-save"></i> Actualizar Balance' :
                        '<i class="fas fa-save"></i> Guardar Balance';
                }
            }

            async function editBalance(balanceId) {
                try {
                    const response = await fetch(`/recursoshumanos/loadchart/employee_vacation_balance/${balanceId}/edit`);
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
                } catch (error) {
                    alert('No se pudo cargar el balance para editar');
                }
            }

            function confirmDelete(balanceId) {
                currentBalanceId = balanceId;
                currentAction = 'delete';
                document.getElementById('modalTitle').innerHTML = '<i class="fas fa-trash"></i> Confirmar Eliminación';
                document.getElementById('confirmMessage').textContent = '¿Estás seguro de que deseas eliminar este balance de vacaciones? Esta acción no se puede deshacer.';
                confirmActionBtn.classList.remove('btn-primary');
                confirmActionBtn.classList.add('btn-danger');
                confirmModal.style.display = 'flex';
            }

            async function deleteBalance(balanceId) {
                try {
                    const url = `/recursoshumanos/loadchart/employee_vacation_balance/${balanceId}`;
                    const response = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                    });

                    const data = await response.json();

                    if (response.ok) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            alert('¡Éxito! ' + data.message);
                        }
                        confirmModal.style.display = 'none';
                        location.reload();
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message
                            });
                        } else {
                            alert('Error: ' + data.message);
                        }
                    }
                } catch (error) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Conexión',
                            text: 'No se pudo conectar con el servidor. Verifica tu red.'
                        });
                    } else {
                        alert('Error de Conexión. Verifica tu red.');
                    }
                }
            }

            function resetForm() {
                vacationForm.reset();
                currentBalanceId = null;
                employeeIdSelect.value = '';
                employeeIdHidden.value = '';
                formTitleModal.innerHTML = '<i class="fas fa-plus-circle"></i> Agregar Balance';
                submitBtnModal.innerHTML = '<i class="fas fa-save"></i> Guardar Balance';
                document.getElementById('balance_id').value = '';
                document.getElementById('rest_mode').value = '5x2';
                document.getElementById('years_of_service').value = '';
            }

            function closeFormModal() {
                formModal.style.display = 'none';
                resetForm();
            }

            // Funciones del modal de historial...
            function openHistoryModal(employeeData) {
                currentEmployeeHistory = employeeData;
                document.getElementById('historyEmployeeName').textContent = employeeData.full_name;
                document.getElementById('historyEmployeeNumber').textContent = employeeData.employee_number || 'N/A';
                document.getElementById('historyEmployeeArea').textContent = employeeData.area || 'N/A';
                document.getElementById('historyHireDate').textContent = formatHireDate(employeeData.hire_date);
                renderHistoryTable(employeeData.vacation_days_details);
                historyModal.style.display = 'flex';
            }

            function renderHistoryTable(vacationDetails) {
                historyTableBody.innerHTML = '';

                if (!vacationDetails || vacationDetails.length === 0) {
                    historyTableBody.innerHTML = `
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px;">
                                <i class="fas fa-inbox" style="font-size: 48px; color: #cbd5e0; margin-bottom: 10px;"></i>
                                <p>No hay registros de vacaciones tomadas</p>
                            </td>
                        </tr>
                    `;
                    updateHistorySummary(0, 0, 0, 0);
                    return;
                }

                const sortedDetails = [...vacationDetails].sort((a, b) => {
                    return new Date(b.date) - new Date(a.date);
                });

                let totalApproved = 0;
                let totalPending = 0;
                let totalRejected = 0;
                let totalDays = sortedDetails.length;

                sortedDetails.forEach(detail => {
                    const date = new Date(detail.date);
                    const day = date.getDate().toString().padStart(2, '0');
                    const month = (date.getMonth() + 1).toString().padStart(2, '0');
                    const year = date.getFullYear();
                    const monthName = getMonthName(date.getMonth() + 1);

                    const statusInfo = translateAndStyleStatus(detail.status);

                    switch (detail.status.toLowerCase().replace(/\s/g, '_')) {
                        case 'approved':
                            totalApproved++;
                            break;
                        case 'under_review':
                        case 'reviewed':
                            totalPending++;
                            break;
                        case 'rejected':
                            totalRejected++;
                            break;
                    }

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${day}/${month}/${year}</td>
                        <td>${monthName}</td>
                        <td>${year}</td>
                        <td><span class="status-badge ${statusInfo.class}">${statusInfo.display}</span></td>
                        <td>Vacaciones</td>
                    `;
                    historyTableBody.appendChild(row);
                });

                updateHistorySummary(totalApproved, totalPending, totalRejected, totalDays);
            }

            function updateHistorySummary(approved, pending, rejected, total) {
                document.getElementById('totalApproved').textContent = approved;
                document.getElementById('totalPending').textContent = pending;
                document.getElementById('totalRejected').textContent = rejected;
                document.getElementById('totalDays').textContent = total;
            }

            function filterHistory(searchTerm) {
                if (!currentEmployeeHistory || !currentEmployeeHistory.vacation_days_details) return;

                const searchLower = searchTerm.toLowerCase();

                const filteredDetails = currentEmployeeHistory.vacation_days_details.filter(detail => {
                    const date = new Date(detail.date);
                    const day = date.getDate().toString().padStart(2, '0');
                    const month = (date.getMonth() + 1).toString().padStart(2, '0');
                    const year = date.getFullYear();
                    const monthName = getMonthName(date.getMonth() + 1).toLowerCase();

                    const formattedDate = `${day}/${month}/${year}`;
                    const statusInfo = translateAndStyleStatus(detail.status);

                    return formattedDate.includes(searchLower) ||
                        monthName.includes(searchLower) ||
                        year.toString().includes(searchLower) ||
                        statusInfo.display.toLowerCase().includes(searchLower);
                });

                renderHistoryTable(filteredDetails);
            }

            function closeHistoryModalFunc() {
                historyModal.style.display = 'none';
                currentEmployeeHistory = null;
                searchHistoryInput.value = '';
            }

            // ******************************************************************
            // 🥇 FUNCIONES MEJORADAS PARA EL MODAL DE REPORTES
            // ******************************************************************

            function toggleReportFilters() {
                const reportType = reportTypeSelect.value;
                const isTakenReport = reportType === 'TAKEN';

                dateRangeGroup.style.display = isTakenReport ? 'grid' : 'none';
                statusFilterGroup.style.display = isTakenReport ? 'block' : 'none';

                // Limpiar errores al cambiar de tipo de reporte
                dateFromError.textContent = '';
                dateToError.textContent = '';

                // Si cambiamos a AVAILABLE, forzamos la selección de todos los empleados
                if (!isTakenReport) {
                    toggleAllEmployees(true);
                    selectAllEmployees.checked = true;
                    selectAllEmployees.indeterminate = false;
                }

                updateReportSummary();
            }

            function updateReportSummary() {
                const reportType = reportTypeSelect.value;
                const selectedDepartments = getSelectedDepartments();
                const selectedEmployees = getSelectedEmployees();
                const dateFrom = dateFromInput.value;
                const dateTo = dateToInput.value;
                const selectedStatuses = getSelectedStatuses().map(s => translateAndStyleStatus(s).display); // Usar la traducción

                // Actualizar tipo de reporte
                summaryType.textContent = reportType === 'AVAILABLE' ? 'Días Disponibles' : 'Días Tomados';

                // Actualizar departamentos
                const totalDepartments = document.querySelectorAll('#department_checkbox_group input[type="checkbox"]').length;
                if (selectedDepartments.length === 0) {
                    summaryDepartments.textContent = 'Ninguno';
                } else if (selectedDepartments.length === totalDepartments) {
                    summaryDepartments.textContent = 'Todos';
                } else {
                    summaryDepartments.textContent = selectedDepartments.join(', ');
                }

                // Actualizar empleados
                const visibleEmployeeCheckboxes = document.querySelectorAll('.employee-option[style*="display: flex"] input[type="checkbox"]').length;
                if (selectedEmployees.length === 0) {
                    summaryEmployees.textContent = 'Ninguno';
                } else if (selectedEmployees.length === employeesData.length) {
                    summaryEmployees.textContent = 'Todos';
                } else if (selectedEmployees.length === visibleEmployeeCheckboxes && selectedDepartments.length < totalDepartments) {
                    summaryEmployees.textContent = `Todos en ${selectedDepartments.length} Deptos.`;
                } else {
                    summaryEmployees.textContent = `${selectedEmployees.length} empleado(s) seleccionado(s)`;
                }

                // Actualizar fechas
                if (reportType === 'TAKEN') {
                    summaryDates.style.display = 'block';
                    const fromText = dateFrom ? formatDateForDisplay(dateFrom) : 'Inicio';
                    const toText = dateTo ? formatDateForDisplay(dateTo) : 'Fin';
                    summaryDatesText.textContent = `${fromText} a ${toText}`;
                } else {
                    summaryDates.style.display = 'none';
                }

                // Actualizar estatus
                if (reportType === 'TAKEN') {
                    summaryStatus.style.display = 'block';
                    if (selectedStatuses.length === 0) {
                        summaryStatusText.textContent = 'Ninguno';
                    } else {
                        summaryStatusText.textContent = selectedStatuses.join(', ');
                    }
                } else {
                    summaryStatus.style.display = 'none';
                }
            }

            function formatDateForDisplay(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString + 'T00:00:00'); // Añadir T00:00:00 para evitar problemas de zona horaria
                return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
            }

            function getSelectedDepartments() {
                const selected = [];
                document.querySelectorAll('#department_checkbox_group input[type="checkbox"]:checked').forEach(checkbox => {
                    selected.push(checkbox.value);
                });
                return selected;
            }

            function getSelectedEmployees() {
                const selected = [];
                document.querySelectorAll('#employee_checkbox_group input[type="checkbox"]:checked').forEach(checkbox => {
                    selected.push(checkbox.value);
                });
                return selected;
            }

            function getSelectedStatuses() {
                const selected = [];
                document.querySelectorAll('#status_filter_group input[type="checkbox"]:checked').forEach(checkbox => {
                    selected.push(checkbox.value);
                });
                return selected;
            }

            function filterEmployeesByDepartment() {
                const selectedDepartments = getSelectedDepartments();
                const employeeOptions = document.querySelectorAll('.employee-option');
                const isAllDepartmentsChecked = document.querySelectorAll('#department_checkbox_group input[type="checkbox"]').length === selectedDepartments.length;

                employeeOptions.forEach(option => {
                    const department = option.getAttribute('data-department');
                    const checkbox = option.querySelector('input[type="checkbox"]');

                    if (selectedDepartments.length === 0) {
                        // Si no hay departamentos seleccionados, ocultar todos.
                        option.style.display = 'none';
                        checkbox.checked = false;
                    } else if (selectedDepartments.includes(department)) {
                        option.style.display = 'flex';
                        // Mantener el estado de checked si ya estaba marcado antes de la selección inicial
                        if (isAllDepartmentsChecked) {
                            checkbox.checked = true;
                        }
                    } else {
                        option.style.display = 'none';
                        checkbox.checked = false;
                    }
                });

                updateEmployeeSelectAllState();
                updateReportSummary();
            }

            function updateDepartmentSelectAllState() {
                const checkboxes = document.querySelectorAll('#department_checkbox_group input[type="checkbox"]');
                const checkedCount = document.querySelectorAll('#department_checkbox_group input[type="checkbox"]:checked').length;

                if (checkboxes.length === 0) return;

                if (checkedCount === 0) {
                    selectAllDepartments.checked = false;
                    selectAllDepartments.indeterminate = false;
                } else if (checkedCount === checkboxes.length) {
                    selectAllDepartments.checked = true;
                    selectAllDepartments.indeterminate = false;
                } else {
                    selectAllDepartments.checked = false;
                    selectAllDepartments.indeterminate = true;
                }
            }

            function updateEmployeeSelectAllState() {
                const visibleCheckboxes = document.querySelectorAll('.employee-option[style*="display: flex"] input[type="checkbox"]');
                const checkedCount = document.querySelectorAll('.employee-option[style*="display: flex"] input[type="checkbox"]:checked').length;

                if (visibleCheckboxes.length === 0) {
                    selectAllEmployees.disabled = true;
                    selectAllEmployees.checked = false;
                    selectAllEmployees.indeterminate = false;
                } else {
                    selectAllEmployees.disabled = false;
                    if (checkedCount === 0) {
                        selectAllEmployees.checked = false;
                        selectAllEmployees.indeterminate = false;
                    } else if (checkedCount === visibleCheckboxes.length) {
                        selectAllEmployees.checked = true;
                        selectAllEmployees.indeterminate = false;
                    } else {
                        selectAllEmployees.checked = false;
                        selectAllEmployees.indeterminate = true;
                    }
                }
            }

            function toggleAllDepartments(checked) {
                document.querySelectorAll('#department_checkbox_group input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = checked;
                });
                selectAllDepartments.indeterminate = false;
                filterEmployeesByDepartment();
            }

            function toggleAllEmployees(checked) {
                document.querySelectorAll('.employee-option[style*="display: flex"] input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = checked;
                });
                selectAllEmployees.indeterminate = false;
                updateReportSummary();
            }

            function clearAllEmployees() {
                document.querySelectorAll('#employee_checkbox_group input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = false;
                });
                updateEmployeeSelectAllState();
                updateReportSummary();
            }

            function validateReportForm() {
                let isValid = true;
                const reportType = reportTypeSelect.value;
                const dateFrom = dateFromInput.value;
                const dateTo = dateToInput.value;

                dateFromError.textContent = '';
                dateToError.textContent = '';

                if (reportType === 'TAKEN') {
                    if (dateFrom && dateTo && new Date(dateFrom) > new Date(dateTo)) {
                        dateToError.textContent = 'La fecha hasta debe ser igual o posterior a la fecha desde.';
                        isValid = false;
                    }
                    if (getSelectedStatuses().length === 0) {
                        // Opcional: Mostrar un mensaje de advertencia si no hay estatus seleccionado para TAKEN
                        // Se puede omitir la validación dura y dejar que el backend maneje el caso de "sin datos".
                    }
                }

                if (getSelectedDepartments().length === 0) {
                    // Si no hay departamentos seleccionados, el reporte no tendrá datos.
                    // Podrías agregar una alerta visual, pero no es una validación de envío estricta si el backend acepta arrays vacíos.
                }

                return isValid;
            }

            function getReportFormData() {
                const data = {};
                const formData = new FormData(reportForm);

                for (let [key, value] of formData.entries()) {
                    if (key.endsWith('[]')) {
                        const cleanKey = key.slice(0, -2);
                        if (!data[cleanKey]) {
                            data[cleanKey] = [];
                        }
                        data[cleanKey].push(value);
                    } else if (key !== '_token') {
                        data[key] = value;
                    }
                }

                data.departments = data.departments || [];
                data.employees = data.employees || [];

                if (data.report_type !== 'TAKEN') {
                    data.date_from = null;
                    data.date_to = null;
                    data.status_filter = ['Approved', 'Reviewed', 'Under_Review', 'Rejected']; // Se envía todo, pero el backend lo ignora para AVAILABLE.
                } else {
                    data.status_filter = data.status_filter && data.status_filter.length > 0 ? data.status_filter : ['Approved'];
                }

                return data;
            }

            async function generateReport() {
                if (!validateReportForm()) {
                    return;
                }

                const reportData = getReportFormData();

                generateReportBtn.disabled = true;
                generateReportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';

                const params = new URLSearchParams();
                for (const key in reportData) {
                    if (Array.isArray(reportData[key])) {
                        reportData[key].forEach(val => params.append(key + '[]', val));
                    } else if (reportData[key] !== null) {
                        params.append(key, reportData[key]);
                    }
                }
                params.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                try {
    const response = await fetch('/recursoshumanos/loadchart/employee_vacation_balance/generate-report', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            // 💡 SOLUCIÓN: Agrega este encabezado para que Laravel reconozca los datos POST
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        // 💡 SOLUCIÓN: Convierte 'params' a una cadena de URL
        body: params.toString()
    });
                    if (response.headers.get('content-type') && response.headers.get('content-type').includes('application/json')) {
                        const data = await response.json();
                        let errorMessages = '';
                        if (response.status === 422 && data.errors) {
                            for (const field in data.errors) {
                                errorMessages += `<li>${data.errors[field][0]}</li>`;
                                if (field.includes('date_from')) dateFromError.textContent = data.errors[field][0];
                                if (field.includes('date_to')) dateToError.textContent = data.errors[field][0];
                            }
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'warning',
                                    title: '¡Revisa los Filtros!',
                                    html: `<ul>${errorMessages}</ul>`,
                                    confirmButtonText: 'Corregir'
                                });
                            }
                        } else {
                            const message = data.message || 'Error desconocido al generar el reporte.';
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error Inesperado',
                                    text: message,
                                    confirmButtonText: 'Aceptar'
                                });
                            }
                        }
                    } else if (response.ok) {
                        const blob = await response.blob();
                        const disposition = response.headers.get('Content-Disposition');
                        let filename = 'reporte_vacaciones.pdf';
                        if (disposition && disposition.indexOf('attachment') !== -1) {
                            const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                            const matches = filenameRegex.exec(disposition);
                            if (matches != null && matches[1]) {
                                filename = matches[1].replace(/['"]/g, '');
                            }
                        }

                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = url;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Reporte Generado!',
                                text: `El archivo ${filename} se ha descargado exitosamente.`,
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }

                        closeReportModalFunc();
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error HTTP ' + response.status,
                                text: 'Ocurrió un error en el servidor al generar el reporte.',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    }
                } catch (error) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Conexión',
                            text: 'No se pudo conectar con el servidor para generar el reporte. Verifica tu red.',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                } finally {
                    generateReportBtn.disabled = false;
                    generateReportBtn.innerHTML = '<i class="fas fa-download"></i> Generar Reporte PDF';
                }
            }

            function closeReportModalFunc() {
                reportModal.style.display = 'none';
                dateFromError.textContent = '';
                dateToError.textContent = '';
                // Opcional: Reestablecer a valores por defecto o mantener la última selección.
                // reportTypeSelect.value = 'AVAILABLE';
                // toggleReportFilters();
            }

            // ******************************************************************
            // MANEJADORES DE EVENTOS - MEJORADOS
            // ******************************************************************

            // Eventos existentes...
            document.getElementById('openAddFormBtn').addEventListener('click', function() {
                if (isBalanceView) {
                    resetForm();
                    formModal.style.display = 'flex';
                }
            });

            vacationForm.addEventListener('submit', function(e) {
                e.preventDefault();
                saveBalance();
            });

            cancelEditModal.addEventListener('click', function() {
                closeFormModal();
            });

            closeFormModalBtn.addEventListener('click', function() {
                closeFormModal();
            });

            toggleVacationViewBtn.addEventListener('click', function() {
                isBalanceView = !isBalanceView;
                currentPage = 1;
                searchInput.value = '';

                renderTableAndPagination();
            });

            perPageSelector.addEventListener('change', function() {
                itemsPerPage = this.value === 'all' ? 'all' : parseInt(this.value);
                currentPage = 1;
                renderTableAndPagination();
            });

            searchInput.addEventListener('input', () => {
                currentPage = 1;
                renderTableAndPagination();
            });

            searchHistoryInput.addEventListener('input', function() {
                filterHistory(this.value);
            });

            document.addEventListener('click', function(e) {
                if (isBalanceView) {
                    if (e.target.closest('.btn-edit')) {
                        const balanceId = e.target.closest('.btn-edit').dataset.balanceId;
                        editBalance(balanceId);
                    }
                    if (e.target.closest('.btn-delete')) {
                        const balanceId = e.target.closest('.btn-delete').dataset.balanceId;
                        confirmDelete(balanceId);
                    }
                } else {
                    if (e.target.closest('.btn-history')) {
                        const employeeName = e.target.closest('.btn-history').dataset.employeeId;
                        const employeeData = vacationDaysTakenData.find(item => item.full_name === employeeName);
                        if (employeeData) {
                            openHistoryModal(employeeData);
                        }
                    }
                }
            });

            confirmActionBtn.addEventListener('click', async function() {
                if (currentAction === 'delete' && currentBalanceId) {
                    await deleteBalance(currentBalanceId);
                    currentAction = '';
                    currentBalanceId = null;
                }
            });

            document.getElementById('cancelConfirm').addEventListener('click', function() {
                confirmModal.style.display = 'none';
            });

            document.getElementById('closeConfirmModalBtn').addEventListener('click', function() {
                confirmModal.style.display = 'none';
            });

            closeHistoryModalBtn.addEventListener('click', closeHistoryModalFunc);
            closeHistoryModal.addEventListener('click', closeHistoryModalFunc);

            // 🥇 NUEVOS EVENTOS PARA EL MODAL DE REPORTES MEJORADO
            openReportModalBtn.addEventListener('click', function() {
                reportModal.style.display = 'flex';
                toggleReportFilters();
                updateReportSummary();
            });

            closeReportModalBtn.addEventListener('click', closeReportModalFunc);
            cancelReportModal.addEventListener('click', closeReportModalFunc);

            reportTypeSelect.addEventListener('change', toggleReportFilters);

            dateFromInput.addEventListener('change', function() {
                validateReportForm();
                updateReportSummary();
            });

            dateToInput.addEventListener('change', function() {
                validateReportForm();
                updateReportSummary();
            });

            generateReportBtn.addEventListener('click', generateReport);

            // Eventos para los nuevos filtros mejorados
            selectAllDepartments.addEventListener('change', function() {
                toggleAllDepartments(this.checked);
            });

            departmentCheckboxGroup.addEventListener('change', function() {
                updateDepartmentSelectAllState();
                filterEmployeesByDepartment();
            });

            selectAllEmployees.addEventListener('change', function() {
                toggleAllEmployees(this.checked);
            });

            employeeCheckboxGroup.addEventListener('change', function() {
                updateEmployeeSelectAllState();
                updateReportSummary();
            });

            clearEmployeesBtn.addEventListener('click', clearAllEmployees);

            // Eventos para actualizar el resumen cuando cambian los checkboxes de estatus
            statusFilterGroup.addEventListener('change', updateReportSummary);

            // Cerrar modales haciendo clic fuera
            window.addEventListener('click', function(event) {
                if (event.target === confirmModal) {
                    confirmModal.style.display = 'none';
                }
                if (event.target === formModal) {
                    closeFormModal();
                }
                if (event.target === historyModal) {
                    closeHistoryModalFunc();
                }
                if (event.target === reportModal) {
                    closeReportModalFunc();
                }
            });

            // Inicializar la aplicación
            initialize();

            // Inicializar estados de los selectores múltiples
            updateDepartmentSelectAllState();
            updateEmployeeSelectAllState();
        });
    </script>
@endsection
