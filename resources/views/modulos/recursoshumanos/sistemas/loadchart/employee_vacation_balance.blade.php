@extends('modulos.recursoshumanos.sistemas.loadchart.index')

@section('content')
    <div class="container">

        <div class="content-layout">
            <div class="form-section">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-plus-circle"></i> Agregar Vacaciones</h3>
                    </div>
                    <div class="card-body">
                        <form id="vacationForm">
                            @csrf
                            <input type="hidden" id="balance_id" name="id">

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="employee_id">Empleado</label>
                                    <select id="employee_id" name="employee_id" class="select-custom" required>
                                        <option value="">Seleccionar empleado...</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="vacation_days_available">Vacaciones Disponibles</label>
                                    <input type="number" id="vacation_days_available" name="vacation_days_available"
                                        class="input-custom" min="0" placeholder="0" required>
                                </div>

                                <div class="form-group">
                                    <label for="rest_days_available">Descanso Disponibles</label>
                                    <input type="number" id="rest_days_available" name="rest_days_available"
                                        class="input-custom" min="0" placeholder="0" required>
                                </div>

                                <div class="form-group">
                                    <label for="years_of_service">Años de Servicio</label>
                                    {{-- ** CAMBIO: Campo Años de Servicio ahora es READONLY ** --}}
                                    <input type="number" id="years_of_service" name="years_of_service" class="input-custom"
                                        min="0" placeholder="0" required readonly>
                                </div>

                                <div class="form-group">
                                    <label for="rest_mode">Modalidad de Descanso</label>
                                    <select id="rest_mode" name="rest_mode" class="select-custom" required>
                                        <option value="5x2" selected>5 días trabajo x 2 días descanso</option>
                                        <option value="6x1">6 días trabajo x 1 día descanso</option>
                                        <option value="28x7">28 días trabajo x 7 días descanso (Rotativo)</option>
                                        <option value="12x24hr">12 horas trabajo x 24 horas descanso (Turno)</option>
                                        <option value="UNASSIGNED">No Asignado</option>
                                    </select>
                                </div>

                            </div>

                            <div class="form-actions">
                                <button type="button" id="cancelEdit" class="btn btn-outline" style="display: none;">
                                    <i class="fas fa-times"></i> Cancelar
                                </button>
                                <button type="submit" id="submitBtn" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Balance
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="list-section">
                <div class="card">
                    <div class="card-header">
                        <div class="header-section">
                            <h1><i class="fas fa-list"></i> Balances de Vacaciones Registrados</h1>
                            <p>Administra los días de vacaciones y descanso disponibles para cada empleado</p>
                        </div>
                        <div class="header-actions">

                            {{-- COMIENZO DEL BUSCADOR --}}
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" id="searchVacation" placeholder="Buscar por empleado...">
                            </div>
                            {{-- FIN DEL BUSCADOR --}}

                        </div>
                    </div>

                    <div class="card-body">
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
                                <tbody id="vacationTableBody">
                                    {{-- Los datos se renderizan con JS --}}
                                </tbody>
                            </table>
                        </div>

                        {{-- PAGINACIÓN Y SELECTOR (Se mantiene) --}}
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
                                <div class="pagination-links" id="pagination-links">
                                    {{-- Los enlaces de paginación se renderizan con JS --}}
                                </div>
                            </div>
                        </div>
                        {{-- FIN: PAGINACIÓN Y SELECTOR --}}

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de confirmación (sin cambios) --}}
    <div class="modal" id="confirmModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Confirmar acción</h3>
                <button class="close-modal">&times;</button>
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
    <style>
        /* ... CSS Styles ... */
        :root {
            --dark-gray: #2d3748;
            --medium-gray: #4a5568;
            --light-gray: #e2e8f0;
            --background-gray: #f7fafc;
            --primary-blue: #283848;
            --secondary-blue: #34495e;
            --white: #ffffff;
        }

        .content-layout {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 10px;
        }

        @media (max-width: 1200px) {
            .content-layout {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
        }

        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            color: var(--primary-blue);
            margin: 0;
            font-size: 1.5rem;
        }

        .card-body {
            padding: 15px;
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

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--dark-gray);
        }

        .input-custom,
        .select-custom {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            background-color: var(--white);
        }

        /* Estilo para campo de solo lectura para diferenciarlo */
        .input-custom[readonly] {
            background-color: var(--background-gray); /* Color más tenue */
            cursor: default;
        }

        .input-custom:focus,
        .select-custom:focus {
            border-color: var(--secondary-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 73, 94, 0.25);
        }

        .form-help {
            font-size: 0.875rem;
            color: var(--medium-gray);
            margin-top: 5px;
        }

        .form-actions {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            grid-column: 1 / -1;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary-blue);
            color: var(--white);
            border: 1px solid var(--primary-blue);
        }

        .btn-primary:hover {
            background-color: #1a232c;
            border-color: #1a232c;
        }

        .btn-outline {
            background-color: transparent;
            color: var(--medium-gray);
            border: 1px solid var(--light-gray);
        }

        .btn-outline:hover {
            background-color: var(--light-gray);
        }

        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
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
            font-size: 1rem;
            transition: border-color 0.3s ease;
            background-color: var(--white);
        }

        .search-box input:focus {
            border-color: var(--secondary-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 73, 94, 0.25);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }

        .data-table th {
            background-color: var(--primary-blue);
            color: var(--white);
            font-weight: 600;
        }

        .data-table tr:hover {
            background-color: #f1f5f8;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            background-color: var(--light-gray);
            color: var(--medium-gray);
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn-icon {
            background: none;
            border: none;
            padding: 6px;
            border-radius: 4px;
            cursor: pointer;
            color: var(--medium-gray);
            transition: all 0.3s ease;
        }

        .btn-icon:hover {
            background: var(--light-gray);
        }

        .btn-edit:hover {
            color: #38a169;
        }

        .btn-delete:hover {
            color: #e53e3e;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--medium-gray);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: var(--light-gray);
        }

        .empty-state h4 {
            margin-bottom: 10px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: var(--white);
            padding: 0;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 450px;
            animation: fadeIn 0.3s ease-out;
        }

        .modal-header {
            background-color: var(--primary-blue);
            color: var(--white);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            border-bottom: 1px solid var(--secondary-blue);
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            color: var(--white);
        }

        .modal-body {
            padding: 20px;
        }

        .modal-body p {
            color: var(--medium-gray);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 15px 20px;
            border-top: 1px solid var(--light-gray);
        }

        .close-modal {
            color: var(--white);
            font-size: 1.5rem;
            font-weight: bold;
            border: none;
            background: none;
            cursor: pointer;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .close-modal:hover {
            opacity: 1;
        }

        .btn-danger {
            background-color: #e53e3e;
            color: var(--white);
            border: 1px solid #e53e3e;
        }

        .btn-danger:hover {
            background-color: #c53030;
            border-color: #c53030;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pagination-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
        }

        .per-page-selector {
            display: flex;
            align-items: center;
        }

        .per-page-selector span {
            color: var(--medium-gray);
            margin-right: 5px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .per-page-selector select {
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            background-color: white;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .per-page-selector select:hover {
            border-color: var(--primary-blue);
        }

        .pagination-links-container {
            flex-grow: 1;
            display: flex;
            justify-content: center;
        }

        .pagination-links {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .pagination-item {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 8px;
            font-size: 0.875rem;
            color: var(--primary-blue);
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.2s ease-in-out;
        }

        .pagination-item:hover {
            background-color: var(--primary-blue);
            color: white;
            border-color: var(--primary-blue);
        }

        .pagination-item.active {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            color: white;
            font-weight: 600;
            cursor: default;
        }

        .pagination-item.disabled {
            color: #ccc;
            cursor: not-allowed;
            background-color: #f9f9f9;
            border-color: #eee;
        }
    </style>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Datos de los balances de vacaciones desde el servidor (JSON codificado)
            const vacationBalancesData = @json($vacationBalances);
            let currentPage = 1;

            // Obtener referencias a elementos del DOM
            const perPageSelector = document.getElementById('perPageSelector');
            let itemsPerPage = parseInt(perPageSelector.value);
            let filteredBalances = [];

            const vacationForm = document.getElementById('vacationForm');
            const submitBtn = document.getElementById('submitBtn');
            const cancelEditBtn = document.getElementById('cancelEdit');
            const searchInput = document.getElementById('searchVacation');
            const vacationTableBody = document.getElementById('vacationTableBody');
            const confirmModal = document.getElementById('confirmModal');
            const paginationLinksContainer = document.getElementById('pagination-links');
            let currentBalanceId = null;

            // Función auxiliar para dar formato a la fecha (YYYY-MM-DD a DD/MM/YYYY)
            function formatHireDate(dateString) {
                if (!dateString || dateString === 'N/A') return 'N/A';
                const parts = dateString.split('-'); // Asumiendo formato YYYY-MM-DD
                if (parts.length === 3) {
                    return `${parts[2]}/${parts[1]}/${parts[0]}`; // DD/MM/YYYY
                }
                return dateString;
            }

            // -------------------------------------------------------------------------
            // INICIALIZACIÓN Y RENDERIZADO (Paginación y Filtro)
            // -------------------------------------------------------------------------

            function initialize() {
                filteredBalances = vacationBalancesData;
                renderTableAndPagination();
            }

            function renderTableAndPagination() {
                const searchTerm = searchInput.value.toLowerCase();

                // 1. Filtrar los datos por término de búsqueda
                const tempFiltered = vacationBalancesData.filter(balance =>
                    (balance.employee ? balance.employee.full_name : 'Empleado no encontrado').toLowerCase()
                    .includes(searchTerm)
                );
                filteredBalances = tempFiltered;

                const isAll = itemsPerPage === 'all';
                const totalPages = isAll ? 1 : Math.ceil(filteredBalances.length / itemsPerPage);

                if (currentPage > totalPages && totalPages > 0) {
                    currentPage = totalPages;
                } else if (totalPages === 0) {
                    currentPage = 1;
                }

                const startIndex = isAll ? 0 : (currentPage - 1) * itemsPerPage;
                const endIndex = isAll ? filteredBalances.length : startIndex + itemsPerPage;
                const balancesToDisplay = filteredBalances.slice(startIndex, endIndex);

                vacationTableBody.innerHTML = '';

                // 2. Renderizar filas de la tabla
                if (balancesToDisplay.length > 0) {
                    balancesToDisplay.forEach(balance => {
                        const row = document.createElement('tr');
                        row.dataset.balanceId = balance.id;

                        const employeeName = balance.employee ? balance.employee.full_name :
                            'Empleado no encontrado';
                        // Mostrar la fecha de ingreso formateada
                        const hireDate = balance.employee ? formatHireDate(balance.employee.hire_date) :
                            'N/A';

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
                    const message = searchTerm ? 'No hay balances de vacaciones registrados que coincidan.' :
                        'No hay balances de vacaciones registrados.';
                    const detail = searchTerm ? 'Intenta con otro término de búsqueda.' :
                        'Comienza agregando un balance usando el formulario.';

                    vacationTableBody.innerHTML = `
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-umbrella-beach"></i>
                                <h4>${message}</h4>
                                <p>${detail}</p>
                            </div>
                        </td>
                    </tr>
                    `;
                }

                // 3. Renderizar enlaces de paginación
                renderPagination(totalPages);
            }

            // ... (renderPagination se mantiene igual)

            function renderPagination(totalPages) {
                paginationLinksContainer.innerHTML = '';
                if (totalPages <= 1) return;

                // Botón "Previous"
                const prevButton = document.createElement('a');
                prevButton.href = '#';
                prevButton.classList.add('pagination-item');
                if (currentPage === 1) prevButton.classList.add('disabled');
                prevButton.innerHTML = '&laquo; Previous';
                prevButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (currentPage > 1) {
                        currentPage--;
                        renderTableAndPagination();
                    }
                });
                paginationLinksContainer.appendChild(prevButton);

                // Números de página
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

                // Botón "Next"
                const nextButton = document.createElement('a');
                nextButton.href = '#';
                nextButton.classList.add('pagination-item');
                if (currentPage === totalPages) nextButton.classList.add('disabled');
                nextButton.innerHTML = 'Next &raquo;';
                nextButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (currentPage < totalPages) {
                        currentPage++;
                        renderTableAndPagination();
                    }
                });
                paginationLinksContainer.appendChild(nextButton);
            }

            // -------------------------------------------------------------------------
            // FUNCIONES CRUD (AJAX con SweetAlert2)
            // -------------------------------------------------------------------------

            /**
             * Función para guardar (POST) o actualizar (PUT) el balance de vacaciones.
             */
            async function saveBalance() {
                const formData = new FormData(vacationForm);
                const balanceId = formData.get('id');

                // Define la URL de destino
                const url = balanceId ?
                    `/recursoshumanos/loadchart/employee_vacation_balance/${balanceId}` :
                    '/recursoshumanos/loadchart/employee_vacation_balance';

                // Si estamos actualizando, añade el campo oculto _method=PUT para Laravel
                if (balanceId) {
                    formData.append('_method', 'PUT');
                }

                // ** CAMBIO JS: Eliminamos el campo years_of_service del FormData para que Laravel no lo use **
                // Ya no es necesario porque Laravel lo recalcula con hire_date.
                if (!balanceId) {
                    // Para la creación, solo necesitamos employee_id y rest_mode para el cálculo inicial.
                    // El valor del input 'years_of_service' es ignorado por el controlador ahora.
                    formData.delete('years_of_service');
                } else {
                    // Para la actualización, lo recalcularemos en el backend.
                    formData.delete('years_of_service');
                }


                try {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

                    const response = await fetch(url, {
                        method: 'POST', // Usamos POST para simular PUT/DELETE en formularios
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (response.ok) {
                        // Éxito (Código 2xx)
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        resetForm();
                        location.reload(); // Recarga para ver el cambio
                    } else if (response.status === 422 && data.errors) {
                        // Error de Validación (Código 422)
                        let errorMessages = '';
                        for (const field in data.errors) {
                            errorMessages += `<li>${data.errors[field][0]}</li>`;
                        }

                        Swal.fire({
                            icon: 'warning',
                            title: '¡Revisa los Datos!',
                            html: `<ul>${errorMessages}</ul>`,
                            confirmButtonText: 'Corregir'
                        });

                    } else {
                        // Otros Errores del Servidor (Código 500, 404, etc.)
                        Swal.fire({
                            icon: 'error',
                            title: 'Error Inesperado',
                            text: data.message ||
                                'Ocurrió un error al procesar la solicitud. Inténtalo de nuevo.',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                } catch (error) {
                    // Error de Conexión o Fetch
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Conexión',
                        text: 'No se pudo conectar con el servidor. Verifica tu red.',
                        confirmButtonText: 'Aceptar'
                    });
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = balanceId ?
                        '<i class="fas fa-save"></i> Actualizar Balance' :
                        '<i class="fas fa-save"></i> Guardar Balance';
                }
            }

            async function editBalance(balanceId) {
                try {
                    const response = await fetch(
                        `/recursoshumanos/loadchart/employee_vacation_balance/${balanceId}/edit`);
                    const balance = await response.json();

                    document.getElementById('balance_id').value = balance.id;
                    document.getElementById('employee_id').value = balance.employee_id;
                    document.getElementById('vacation_days_available').value = balance.vacation_days_available;
                    document.getElementById('rest_days_available').value = balance.rest_days_available;

                    // ** CAMBIO JS: Actualizamos el campo readonly con el valor sincronizado **
                    document.getElementById('years_of_service').value = balance.years_of_service;

                    // Cargar el valor de rest_mode
                    document.getElementById('rest_mode').value = balance.rest_mode || '5x2';

                    currentBalanceId = balanceId;
                    cancelEditBtn.style.display = 'inline-block';
                    document.querySelector('.card-header h3').innerHTML =
                        '<i class="fas fa-edit"></i> Editar Balance de Vacaciones';
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Actualizar Balance';

                    document.querySelector('.form-section').scrollIntoView({
                        behavior: 'smooth'
                    });
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo cargar el balance para editar'
                    });
                }
            }

            function confirmDelete(balanceId) {
                currentBalanceId = balanceId;
                document.getElementById('confirmMessage').textContent =
                    '¿Estás seguro de que deseas eliminar este balance de vacaciones? Esta acción no se puede deshacer.';
                confirmModal.style.display = 'flex';
            }

            function resetForm() {
                vacationForm.reset();
                currentBalanceId = null;
                cancelEditBtn.style.display = 'none';
                document.querySelector('.card-header h3').innerHTML =
                    '<i class="fas fa-plus-circle"></i> Agregar Vacaciones';
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Balance';
                document.getElementById('balance_id').value = '';
                // Restablecer el select al valor por defecto
                document.getElementById('rest_mode').value = '5x2';
                // Asegurar que el campo readonly se limpie
                document.getElementById('years_of_service').value = '';
            }

            // -------------------------------------------------------------------------
            // MANEJADORES DE EVENTOS (Botón de actualización forzada eliminado)
            // -------------------------------------------------------------------------

            perPageSelector.addEventListener('change', function() {
                itemsPerPage = this.value === 'all' ? 'all' : parseInt(this.value);
                currentPage = 1;
                renderTableAndPagination();
            });

            searchInput.addEventListener('input', () => {
                currentPage = 1;
                renderTableAndPagination();
            });

            vacationForm.addEventListener('submit', function(e) {
                e.preventDefault();
                saveBalance();
            });

            cancelEditBtn.addEventListener('click', function() {
                resetForm();
            });

            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-edit')) {
                    const balanceId = e.target.closest('.btn-edit').dataset.balanceId;
                    editBalance(balanceId);
                }

                if (e.target.closest('.btn-delete')) {
                    const balanceId = e.target.closest('.btn-delete').dataset.balanceId;
                    confirmDelete(balanceId);
                }
            });

            document.getElementById('confirmAction').addEventListener('click', async function() {
                if (currentBalanceId) {
                    try {
                        const url =
                            `/recursoshumanos/loadchart/employee_vacation_balance/${currentBalanceId}`;
                        const response = await fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                        });

                        const data = await response.json();

                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            confirmModal.style.display = 'none';
                            location.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message
                            });
                        }
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message
                        });
                        confirmModal.style.display = 'none';
                    }
                }
            });

            document.getElementById('cancelConfirm').addEventListener('click', function() {
                confirmModal.style.display = 'none';
            });

            document.querySelector('.close-modal').addEventListener('click', function() {
                confirmModal.style.display = 'none';
            });

            // Iniciar la aplicación
            initialize();
        });
    </script>
@endsection
