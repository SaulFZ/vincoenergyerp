@extends('modulos.recursoshumanos.sistemas.loadchart.index')

@section('content')
    <style>
        /* ===== Variables y Reset ===== */
        :root {
            --primary-dark-blue: #2c3e50;
            --primary-darker-blue: #34495e;
            --primary-blue: #334c95;
            --primary-orange: #d67e29;
            --dark-gray: #2d3748;
            --medium-gray: #4a5568;
            --light-gray: #e2e8f0;
            --lighter-gray: #f7fafc;
            --white: #ffffff;
            --border-radius-sm: 4px;
            --border-radius-md: 8px;
            --box-shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.1);
            --transition-fast: all 0.15s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            color: var(--dark-gray);
            background-color: var(--lighter-gray);
            line-height: 1.5;
        }

        /* ===== Panel Superior Mejorado ===== */
        .control-panel {
            background-color: var(--white);
            border-radius: var(--border-radius-md);
            box-shadow: var(--box-shadow-sm);
            margin-bottom: 25px;
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: flex-end;
        }

        .panel-header {
            flex: 1 1 100%;
            margin-bottom: 10px;
        }

        .panel-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-dark-blue);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-title i {
            font-size: 1.2em;
            color: var(--primary-orange);
        }

        .panel-subtitle {
            font-size: 0.85rem;
            color: var(--medium-gray);
            margin-top: 4px;
        }

        .filter-group {
            flex: 1;
            min-width: 180px;
        }

        .filter-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--medium-gray);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-input,
        .filter-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius-sm);
            font-size: 0.9rem;
            background-color: var(--white);
            transition: var(--transition-fast);
        }

        .filter-input:focus,
        .filter-select:focus {
            border-color: var(--primary-blue);
            outline: none;
            box-shadow: 0 0 0 2px rgba(51, 76, 149, 0.2);
        }

        .action-group {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .refresh-btn {
            background-color: var(--white);
            color: var(--primary-dark-blue);
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius-sm);
            padding: 10px 16px;
            font-size: 0.85rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition-fast);
            font-weight: 500;
            height: 40px;
        }

        .refresh-btn:hover {
            background-color: var(--lighter-gray);
            transform: translateY(-1px);
        }

        .filter-btn {
            background-color: var(--primary-orange);
            color: var(--white);
            border: none;
            border-radius: var(--border-radius-sm);
            padding: 10px 20px;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition-fast);
            font-weight: 500;
            height: 40px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-btn:hover {
            background-color: #c57124;
            transform: translateY(-1px);
        }

        /* ===== Tabla Mejorada ===== */
        .table-container {
            background-color: var(--white);
            border-radius: var(--border-radius-md);
            box-shadow: var(--box-shadow-sm);
            overflow: hidden;
            margin-bottom: 20px;
            overflow-x: auto;
        }

        .assignment-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        .assignment-table thead {
            background-color: var(--primary-darker-blue);
        }

        .assignment-table th {
            padding: 14px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--white);
            border-bottom: 2px solid var(--primary-dark-blue);
            cursor: pointer;
            /* Añadido para indicar que la columna es clickeable */
            position: relative;
        }

        .assignment-table th i {
            margin-left: 5px;
            transition: transform 0.2s;
        }

        .assignment-table th.sorted-asc i {
            transform: rotate(180deg);
        }

        .assignment-table td {
            padding: 12px 16px;
            vertical-align: middle;
            border-bottom: 1px solid var(--light-gray);
        }

        .assignment-table tr {
            transition: var(--transition-fast);
        }

        .assignment-table tr:hover {
            background-color: rgba(44, 62, 80, 0.05);
        }

        .employee-id {
            font-weight: 600;
            color: var(--primary-dark-blue);
            font-size: 0.9rem;
        }

        /* ===== Selects Mejorados ===== */
        .assignment-select {
            min-width: 200px;
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius-sm);
            font-size: 0.9rem;
            background-color: var(--white);
            transition: var(--transition-fast);
        }

        .assignment-select:focus {
            border-color: var(--primary-blue);
            outline: none;
            box-shadow: 0 0 0 2px rgba(51, 76, 149, 0.2);
        }

        /* ===== Botones de Acción Mejorados ===== */
        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: var(--border-radius-sm);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-fast);
            cursor: pointer;
            border: none;
        }

        .save-btn {
            background-color: var(--primary-dark-blue);
            color: var(--white);
        }

        .save-btn:hover {
            background-color: var(--primary-darker-blue);
            transform: translateY(-1px);
        }

        /* ===== Paginación Mejorada ===== */
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }

        .pagination {
            display: flex;
            list-style: none;
            gap: 6px;
        }

        .page-item {
            margin: 0;
        }

        .page-link {
            display: block;
            padding: 8px 14px;
            font-size: 0.85rem;
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            color: var(--primary-dark-blue);
            background-color: var(--white);
            border: 1px solid var(--light-gray);
            transition: var(--transition-fast);
            font-weight: 500;
        }

        .page-link:hover {
            background-color: var(--lighter-gray);
        }

        .page-item.active .page-link {
            background-color: var(--primary-dark-blue);
            color: var(--white);
            border-color: var(--primary-dark-blue);
        }

        .page-item.disabled .page-link {
            color: var(--medium-gray);
            pointer-events: none;
            background-color: var(--lighter-gray);
        }

        /* ===== Responsive ===== */
        @media (max-width: 768px) {
            .control-panel {
                flex-direction: column;
                align-items: stretch;
                gap: 15px;
            }

            .filter-group {
                width: 100%;
            }

            .action-group {
                width: 100%;
                justify-content: space-between;
            }

            .assignment-table th,
            .assignment-table td {
                padding: 10px 12px;
            }

            .assignment-select {
                min-width: 160px;
            }
        }

        /* ===== Mensajes de la tabla ===== */
        .loading-message,
        .no-results-message {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
            font-size: 1rem;
            font-weight: 500;
            color: var(--medium-gray);
            text-align: center;
            gap: 10px;
        }

        .loading-message i {
            font-size: 1.5rem;
            color: var(--primary-dark-blue);
        }

        .no-results-message i {
            font-size: 1.5rem;
            color: var(--primary-orange);
        }
    </style>

@section('content')
    <div class="assignment-container">
        <div class="control-panel">
            <div class="panel-header">
                <h1 class="panel-title">
                    <i class="fas fa-user-shield"></i>
                    Asignación de Responsables
                </h1>
                <p class="panel-subtitle">Gestión de revisores y aprobadores por empleado</p>
            </div>

            <div class="filter-group">
                <label for="search-filter" class="filter-label">Buscar</label>
                <input type="text" id="search-filter" class="filter-input" placeholder="Buscar por nombre, ID, cargo...">
            </div>

            <div class="filter-group">
                <label for="department-filter" class="filter-label">Departamento</label>
                <select id="department-filter" class="filter-select">
                    <option value="all">Todos los departamentos</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department }}">{{ $department }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="per-page-filter" class="filter-label">Mostrar Empleados</label>
                <select id="per-page-filter" class="filter-select">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="all">Todos</option>
                </select>
            </div>
        </div>

        <div class="table-container">
            <table class="assignment-table">
                <thead>
                    <tr>
                        <th width="100" data-sort="employee_number">ID<i class="fas fa-sort"></i></th>
                        <th data-sort="full_name">Empleado<i class="fas fa-sort"></i></th>
                        <th data-sort="department">Departamento<i class="fas fa-sort"></i></th>
                        <th data-sort="position">Cargo<i class="fas fa-sort"></i></th>
                        <th data-sort="job_title">Puesto<i class="fas fa-sort"></i></th>
                        <th>Revisor</th>
                        <th>Aprobador</th>
                        <th width="100">Acciones</th>
                    </tr>
                </thead>
                <tbody id="employees-table-body">
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            <ul class="pagination" id="pagination-list">
            </ul>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Variables del DOM
            const departmentFilter = document.getElementById('department-filter');
            const perPageFilter = document.getElementById('per-page-filter');
            const searchFilter = document.getElementById('search-filter');
            const employeesTableBody = document.getElementById('employees-table-body');
            const paginationList = document.getElementById('pagination-list');
            const tableHeaders = document.querySelectorAll('.assignment-table th[data-sort]');

            let currentPage = 1;
            let currentSortColumn = 'employee_number';
            let currentSortDirection = 'asc';
            const urlGetEmployees = '{{ route('loadchart.getEmployees') }}';

            // Variables de datos de la vista pasadas desde Laravel
            const reviewers = @json($reviewers);
            const approvers = @json($approvers);

            // Función para generar las opciones del selector de revisores
            function createReviewerOptions() {
                let options = '<option value="">Seleccionar...</option>';
                reviewers.forEach(reviewer => {
                    options += `<option value="${reviewer.id}">${reviewer.name}</option>`;
                });
                return options;
            }

            // Función para generar las opciones del selector de aprobadores
            function createApproverOptions() {
                let options = '<option value="">Seleccionar...</option>';
                approvers.forEach(approver => {
                    options += `<option value="${approver.id}">${approver.name}</option>`;
                });
                return options;
            }

            // Función para cargar empleados
            function loadEmployees(page = 1) {
                const department = departmentFilter.value;
                const perPage = perPageFilter.value;
                const searchQuery = searchFilter.value;

                // Mostrar loading
                employeesTableBody.innerHTML = `
                    <tr>
                        <td colspan="8">
                            <div class="loading-message">
                                <i class="fas fa-spinner fa-spin"></i>
                                <span>Cargando empleados...</span>
                            </div>
                        </td>
                    </tr>
                `;

                // Configurar parámetros de la solicitud
                const params = new URLSearchParams({
                    department: department,
                    per_page: perPage,
                    page: page,
                    search: searchQuery,
                    sort_by: currentSortColumn,
                    sort_direction: currentSortDirection
                });

                // Hacer la solicitud fetch
                fetch(`${urlGetEmployees}?${params.toString()}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        employeesTableBody.innerHTML = '';
                        if (data.employees.length === 0) {
                            employeesTableBody.innerHTML = `
                            <tr>
                                <td colspan="8">
                                    <div class="no-results-message">
                                        <i class="fas fa-info-circle"></i>
                                        <span>No se encontraron empleados con los filtros aplicados.</span>
                                    </div>
                                </td>
                            </tr>
                        `;
                            paginationList.innerHTML = '';
                            return;
                        }

                        // Generar opciones de revisor y aprobador fuera del bucle para mayor eficiencia
                        const reviewerOptions = createReviewerOptions();
                        const approverOptions = createApproverOptions();

                        // Llenar la tabla con los empleados
                        data.employees.forEach(employee => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                            <td class="employee-id">${employee.employee_number || 'N/A'}</td>
                            <td>${employee.full_name || 'N/A'}</td>
                            <td>${employee.department || 'N/A'}</td>
                            <td>${employee.position || 'N/A'}</td>
                            <td>${employee.job_title || 'N/A'}</td>
                            <td>
                                <select class="assignment-select reviewer-select">
                                    ${reviewerOptions}
                                </select>
                            </td>
                            <td>
                                <select class="assignment-select approver-select">
                                    ${approverOptions}
                                </select>
                            </td>
                            <td>
                                <button class="action-btn save-btn" data-employee-id="${employee.id}">
                                    <i class="fas fa-save"></i>
                                </button>
                            </td>
                        `;
                            employeesTableBody.appendChild(row);
                        });

                        // Actualizar la paginación
                        updatePagination(data);
                    })
                    .catch(error => {
                        console.error('Error al cargar empleados:', error);
                        employeesTableBody.innerHTML =
                            '<tr><td colspan="8" class="text-center text-danger">Error al cargar los empleados</td></tr>';
                    });
            }

            // ... (El resto de las funciones de paginación y event listeners se mantienen igual) ...
            function updatePagination(data) {
                paginationList.innerHTML = '';

                // Botón Anterior
                const prevLi = document.createElement('li');
                prevLi.className = `page-item ${data.current_page === 1 ? 'disabled' : ''}`;
                prevLi.innerHTML =
                    `<a href="#" class="page-link" data-page="${data.current_page - 1}">Anterior</a>`;
                paginationList.appendChild(prevLi);

                // Números de página
                for (let i = 1; i <= data.last_page; i++) {
                    const pageLi = document.createElement('li');
                    pageLi.className = `page-item ${i === data.current_page ? 'active' : ''}`;
                    pageLi.innerHTML = `<a href="#" class="page-link" data-page="${i}">${i}</a>`;
                    paginationList.appendChild(pageLi);
                }

                // Botón Siguiente
                const nextLi = document.createElement('li');
                nextLi.className = `page-item ${data.current_page === data.last_page ? 'disabled' : ''}`;
                nextLi.innerHTML =
                    `<a href="#" class="page-link" data-page="${data.current_page + 1}">Siguiente</a>`;
                paginationList.appendChild(nextLi);

                // Agregar event listeners a los botones de paginación
                document.querySelectorAll('.page-link').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const page = parseInt(this.getAttribute('data-page'));
                        if (!isNaN(page) && page !== currentPage) {
                            currentPage = page;
                            loadEmployees(currentPage);
                        }
                    });
                });
            }

            // Event listeners para los filtros y búsqueda (automáticos)
            departmentFilter.addEventListener('change', function() {
                currentPage = 1;
                loadEmployees(currentPage);
            });

            perPageFilter.addEventListener('change', function() {
                currentPage = 1;
                loadEmployees(currentPage);
            });

            searchFilter.addEventListener('keyup', function() {
                currentPage = 1;
                loadEmployees(currentPage);
            });

            // Event listeners para ordenar la tabla
            tableHeaders.forEach(header => {
                header.addEventListener('click', function() {
                    const sortColumn = this.getAttribute('data-sort');

                    if (currentSortColumn === sortColumn) {
                        currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        currentSortColumn = sortColumn;
                        currentSortDirection = 'asc';
                    }

                    tableHeaders.forEach(th => th.classList.remove('sorted-asc', 'sorted-desc'));
                    this.classList.add(currentSortDirection === 'asc' ? 'sorted-asc' :
                        'sorted-desc');

                    currentPage = 1;
                    loadEmployees(currentPage);
                });
            });

            // Cargar empleados al iniciar
            loadEmployees(currentPage);
        });
    </script>
@endsection
