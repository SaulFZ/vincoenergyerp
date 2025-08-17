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
