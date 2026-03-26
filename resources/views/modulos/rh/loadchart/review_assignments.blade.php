@extends('modulos.rh.loadchart.index')

@section('content')
    <style>
        /* ===== Variables y Reset ===== */
        :root {
            --primary-dark-blue: #2c3e50;
            --primary-darker-blue: #34495e;
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

        /* ===== Botón de Guardar Mejorado ===== */
        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: var(--border-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-fast);
            cursor: pointer;
            border: none;
            margin: 0 auto;
            /* Centrado horizontal */
        }

        .save-btn {
            background-color: var(--primary-dark-blue);
            color: var(--white);
        }

        .save-btn:hover {
            background-color: var(--primary-darker-blue);
            transform: translateY(-1px);
        }

        /* Estado de éxito */
        .save-btn.success {
            background-color: #28a745;
            /* Verde de éxito */
            animation: pulse 0.5s;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
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

        /* Toast Styles */

        .toast {
            position: fixed;
            /* Cambia la posición a la parte superior */
            top: 100px;
            right: 20px;
            background: white;
            /* Sombra más sutil y moderna */
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            /* Bordes más redondeados */
            padding: 16px 20px;
            /* Aumenta el padding para un mejor espacio */
            display: flex;
            align-items: center;
            gap: 12px;
            /* Usa gap en lugar de margin para el icono */
            z-index: 1000;
            /* Animación para que aparezca desde arriba y se desvanezca */
            transform: translateY(-100px);
            opacity: 0;
            transition: all 0.4s ease-out;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            /* Tamaño del icono ajustado para un mejor visual */
            font-size: 16px;
        }

        /* Toast de éxito */
        .toast.success .toast-icon {
            background: #28a745;
            /* Un verde más vibrante */
        }

        /* Toast de error */
        .toast.error .toast-icon {
            background: #dc3545;
            /* Un rojo más fuerte */
        }

        /* Toast de advertencia (opcional) */
        .toast.warning .toast-icon {
            background: #ffc107;
        }

        /* Estilos del mensaje */
        .toast-message {
            margin: 0;
            font-size: 15px;
            /* Fuente ligeramente más grande */
            color: #333;
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

    <!-- Toast Notification -->
    <div id="toast" class="toast" style="display: none;">
        <div class="toast-icon"></div>
        <div class="toast-content">
            <p class="toast-message"></p>
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
            const toast = document.getElementById('toast');

            let currentPage = 1;
            let currentSortColumn = 'employee_number';
            let currentSortDirection = 'asc';
            const urlGetEmployees = '{{ route('loadchart.getEmployees') }}';
            const urlGetAssignments = '{{ route('loadchart.getExistingAssignments') }}';
            const urlSaveAssignment = '{{ route('loadchart.saveAssignment') }}';

            // Variables de datos de la vista pasadas desde Laravel
            const reviewers = @json($reviewers);
            const approvers = @json($approvers);

            // Función para mostrar notificaciones Toast
            function showToast(type, message) {
                toast.className = `toast ${type}`;
                toast.querySelector('.toast-icon').innerHTML =
                    type === 'success' ? '<i class="fas fa-check"></i>' : '<i class="fas fa-exclamation"></i>';
                toast.querySelector('.toast-message').textContent = message;
                toast.style.display = 'flex';
                toast.classList.add('show');

                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        toast.style.display = 'none';
                    }, 300);
                }, 3000);
            }

            // Función para generar las opciones del selector de revisores
            function createReviewerOptions(selectedId = null) {
                let options = '<option value="">Seleccionar...</option>';
                reviewers.forEach(reviewer => {
                    const selected = selectedId == reviewer.id ? 'selected' : '';
                    options += `<option value="${reviewer.id}" ${selected}>${reviewer.name}</option>`;
                });
                return options;
            }

            // Función para generar las opciones del selector de aprobadores
            function createApproverOptions(selectedId = null) {
                let options = '<option value="">Seleccionar...</option>';
                approvers.forEach(approver => {
                    const selected = selectedId == approver.id ? 'selected' : '';
                    options += `<option value="${approver.id}" ${selected}>${approver.name}</option>`;
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

                // Hacer la solicitud fetch para obtener empleados
                fetch(`${urlGetEmployees}?${params.toString()}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
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

                        // Obtener IDs de empleados para buscar sus asignaciones
                        const employeeIds = data.employees.map(emp => emp.id);

                        // Obtener asignaciones existentes para estos empleados
                        return fetch(urlGetAssignments, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    employee_ids: employeeIds
                                })
                            })
                            .then(response => response.json())
                            .then(assignments => {
                                // Construir la tabla con los empleados y sus asignaciones
                                employeesTableBody.innerHTML = '';

                                data.employees.forEach(employee => {
                                    const assignment = assignments[employee.id] || {};
                                    const row = document.createElement('tr');

                                    row.innerHTML = `
                                    <td class="employee-id">${employee.employee_number || 'N/A'}</td>
                                    <td>${employee.full_name || 'N/A'}</td>
                                    <td>${employee.department || 'N/A'}</td>
                                    <td>${employee.position || 'N/A'}</td>
                                    <td>${employee.job_title || 'N/A'}</td>
                                    <td>
                                        <select class="assignment-select reviewer-select">
                                            ${createReviewerOptions(assignment.reviewer_id)}
                                        </select>
                                    </td>
                                    <td>
                                        <select class="assignment-select approver-select">
                                            ${createApproverOptions(assignment.approver_id)}
                                        </select>
                                    </td>
                                    <td style="text-align: center;">
                                        <button class="action-btn save-btn" data-employee-id="${employee.id}">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </td>
                                `;

                                    employeesTableBody.appendChild(row);
                                });

                                // Actualizar la paginación
                                updatePagination(data);
                            });
                    })
                    .catch(error => {
                        console.error('Error al cargar empleados:', error);
                        employeesTableBody.innerHTML = `
                            <tr>
                                <td colspan="8" class="text-center text-danger">
                                    Error al cargar los empleados. Por favor, intente nuevamente.
                                </td>
                            </tr>
                        `;
                        showToast('error', 'Error al cargar empleados');
                    });
            }

            // Función para actualizar la paginación
            function updatePagination(data) {
                paginationList.innerHTML = '';

                // Botón Anterior
                const prevLi = document.createElement('li');
                prevLi.className = `page-item ${data.current_page === 1 ? 'disabled' : ''}`;
                prevLi.innerHTML =
                    `<a href="#" class="page-link" data-page="${data.current_page - 1}">Anterior</a>`;
                paginationList.appendChild(prevLi);

                // Números de página
                const startPage = Math.max(1, data.current_page - 2);
                const endPage = Math.min(data.last_page, data.current_page + 2);

                if (startPage > 1) {
                    const li = document.createElement('li');
                    li.className = 'page-item';
                    li.innerHTML = `<a href="#" class="page-link" data-page="1">1</a>`;
                    paginationList.appendChild(li);

                    if (startPage > 2) {
                        const dotsLi = document.createElement('li');
                        dotsLi.className = 'page-item disabled';
                        dotsLi.innerHTML = `<span class="page-link">...</span>`;
                        paginationList.appendChild(dotsLi);
                    }
                }

                for (let i = startPage; i <= endPage; i++) {
                    const pageLi = document.createElement('li');
                    pageLi.className = `page-item ${i === data.current_page ? 'active' : ''}`;
                    pageLi.innerHTML = `<a href="#" class="page-link" data-page="${i}">${i}</a>`;
                    paginationList.appendChild(pageLi);
                }

                if (endPage < data.last_page) {
                    if (endPage < data.last_page - 1) {
                        const dotsLi = document.createElement('li');
                        dotsLi.className = 'page-item disabled';
                        dotsLi.innerHTML = `<span class="page-link">...</span>`;
                        paginationList.appendChild(dotsLi);
                    }

                    const li = document.createElement('li');
                    li.className = 'page-item';
                    li.innerHTML =
                        `<a href="#" class="page-link" data-page="${data.last_page}">${data.last_page}</a>`;
                    paginationList.appendChild(li);
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

            // Función para guardar una asignación
            function saveAssignment(employeeId, reviewerId, approverId) {
                const saveBtn = document.querySelector(`.save-btn[data-employee-id="${employeeId}"]`);
                const originalIcon = saveBtn.innerHTML;
                const originalClass = saveBtn.className;

                // Estado de carga
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                saveBtn.disabled = true;
                saveBtn.classList.remove('success');

                const data = {
                    employee_id: employeeId,
                    reviewer_id: reviewerId || null,
                    approver_id: approverId || null,
                    _token: '{{ csrf_token() }}'
                };

                fetch(urlSaveAssignment, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Estado de éxito
                            saveBtn.innerHTML = '<i class="fas fa-check"></i>';
                            saveBtn.classList.add('success');
                            showToast('success', data.message);
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        saveBtn.innerHTML = '<i class="fas fa-times"></i>';
                        showToast('error', error.message);
                    })
                    .finally(() => {
                        setTimeout(() => {
                            // Restaurar estado original
                            saveBtn.innerHTML = originalIcon;
                            saveBtn.className = originalClass;
                            saveBtn.disabled = false;
                        }, 2000);
                    });
            }

            // Event listeners para los filtros y búsqueda
            departmentFilter.addEventListener('change', function() {
                currentPage = 1;
                loadEmployees(currentPage);
            });

            perPageFilter.addEventListener('change', function() {
                currentPage = 1;
                loadEmployees(currentPage);
            });

            // Debounce para la búsqueda
            let searchTimeout;
            searchFilter.addEventListener('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentPage = 1;
                    loadEmployees(currentPage);
                }, 500);
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

                    tableHeaders.forEach(th => {
                        th.classList.remove('sorted-asc', 'sorted-desc');
                        th.querySelector('i').className = 'fas fa-sort';
                    });

                    this.classList.add(`sorted-${currentSortDirection}`);
                    this.querySelector('i').className =
                        currentSortDirection === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';

                    currentPage = 1;
                    loadEmployees(currentPage);
                });
            });

            // Event delegation para los botones de guardar
            document.addEventListener('click', function(e) {
                if (e.target.closest('.save-btn')) {
                    const saveBtn = e.target.closest('.save-btn');
                    const row = saveBtn.closest('tr');
                    const employeeId = saveBtn.getAttribute('data-employee-id');
                    const reviewerId = row.querySelector('.reviewer-select').value;
                    const approverId = row.querySelector('.approver-select').value;

                    saveAssignment(employeeId, reviewerId, approverId);
                }
            });

            // Cargar empleados al iniciar
            loadEmployees(currentPage);
        });
    </script>
@endsection
