@extends('modules.rh.loadchart.index')

@section('content')
    <div class="container">


        <div class="content-layout">
            <div class="form-section">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-plus-circle"></i> {{ isset($editingBonus) ? 'Editar' : 'Agregar' }} Bono de
                            Campo</h3>
                    </div>
                    <div class="card-body">
                        <form id="bonusForm">
                            @csrf
                            <input type="hidden" id="bonus_id" name="id">

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="employee_category">Puesto de Empleado *</label>
                                    <select id="employee_category" name="employee_category" class="select-custom" required>
                                        <option value="">Seleccionar puesto...</option>
                                        @foreach ($jobTitles as $jobTitle)
                                            <option value="{{ $jobTitle }}">{{ $jobTitle }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="bonus_type">Tipo de Bono *</label>
                                    <input type="text" id="bonus_type" name="bonus_type" class="input-custom"
                                        placeholder="Ej: Bono de Riesgo, Bono de Productividad..." required>
                                </div>

                                <div class="form-group">
                                    <label for="bonus_identifier">Identificador *</label>
                                    <input type="text" id="bonus_identifier" name="bonus_identifier" class="input-custom"
                                        placeholder="Ej: BR-OP-001" required>
                                    <small class="form-help">Identificador único para el bono</small>
                                </div>

                                <div class="form-group">
                                    <label for="amount">Monto *</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-dollar-sign"></i>
                                        <input type="number" id="amount" name="amount" class="input-custom"
                                            step="0.01" min="0" placeholder="0.00" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="currency">Moneda *</label>
                                    <select id="currency" name="currency" class="select-custom" required>
                                        <option value="">Seleccionar moneda...</option>
                                        @foreach ($currencies as $key => $currency)
                                            <option value="{{ $key }}">{{ $currency }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="button" id="cancelEdit" class="btn btn-outline" style="display: none;">
                                    <i class="fas fa-times"></i> Cancelar
                                </button>
                                <button type="submit" id="submitBtn" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Bono
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="list-section">
                <div class="card">
                    <div class="card-header">
                        <div class="card-section">
                            <h1><i class="fas fa-money-bill-wave"></i> Gestión de Bonos de Campo</h1>
                            <p>Administra los bonos de campo disponibles para los empleados</p>
                        </div>
                        <div class="header-actions">
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" id="searchBonus" placeholder="Buscar bonos...">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Puesto</th>
                                        <th>Tipo de Bono</th>
                                        <th>Identificador</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="bonusesTableBody">
                                    {{-- Los bonos se renderizarán con JS --}}
                                </tbody>
                            </table>
                        </div>

                        {{-- Paginación y selector de elementos por página --}}
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
                    </div>
                </div>
            </div>
        </div>
    </div>

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
        /* Agregando Variables de color si no están definidas */
        :root {
            --dark-gray: #2d3748;
            --medium-gray: #4a5568;
            --light-gray: #e2e8f0;
            --primary-blue: #283848;
            --secondary-blue: #34495e;
            --white: #ffffff;
            --danger-red: #e53e3e;
        }

        /* Estructura del contenido */
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

        /* Tarjetas (cards) */
        .card {
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            padding: 10px 15px;
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
            padding: 20px;
        }

        /* Formulario */
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

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--medium-gray);
            font-size: 1.1rem;
        }

        .input-with-icon input {
            padding-left: 35px;
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

        /* --- INICIO: SECCIÓN DE BÚSQUEDA Y ACCIONES --- */

        .header-actions {
            display: flex;
            gap: 10px;
            /* Mantener el espacio entre elementos */
            align-items: center;
            /* ** IMPORTANTE: Centra verticalmente el input y otros elementos ** */
        }

        .search-box {
            position: relative;
            width: 250px; /* Ancho fijo para el buscador */
        }

        .search-box i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--medium-gray);
            font-size: 1.1rem; /* Icono un poco más grande */
        }

        .search-box input {
            /* Ajustar el padding izquierdo para el icono */
            padding: 10px 12px 10px 35px;
            width: 100%;
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            background-color: var(--white);
        }

        /* --- FIN: SECCIÓN DE BÚSQUEDA Y ACCIONES --- */

        /* Tabla de Bonos */
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

        .identifier-badge {
            background: var(--secondary-blue);
            color: var(--white);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .amount {
            font-weight: 600;
        }

        .usd-amount {
            color: #28a745;
        }

        .mxn-amount {
            color: #ff9900; /* Asumiendo un color naranja para MXN si no tienes var(--primary-orange) */
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-badge.active {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .status-badge i {
            font-size: 8px;
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
            color: #28a745;
        }

        .btn-toggle:hover {
            color: var(--secondary-blue);
        }

        .btn-delete:hover {
            color: var(--danger-red);
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

        /* Estilos de Modal */
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

        /* ENCABEZADO: Fondo oscuro, Texto y Botón de cerrar en blanco */
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

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 15px 20px;
            border-top: 1px solid var(--light-gray);
        }

        /* BOTÓN DE PELIGRO (Confirmar): Rojo con texto blanco */
        .btn-danger {
            background-color: #ee1818;
            color: var(--white);
            border: 1px solid #ee1818;
        }

        .btn-danger:hover {
            background-color: #c53030;
            border-color: #c53030;
        }

        /* Animación de apertura */
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

        /* Estilos de Paginación Mejorada y Centrada */
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

        /* Contenedor para centrar la paginación */
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
            // Definir el estado global de la aplicación
            let currentPage = 1;
            let itemsPerPage = 10;
            let currentSearchTerm = '';
            let isEditing = false;
            let currentBonusId = null;

            // Obtener referencias a los elementos del DOM
            const bonusForm = document.getElementById('bonusForm');
            const submitBtn = document.getElementById('submitBtn');
            const cancelEditBtn = document.getElementById('cancelEdit');
            const searchInput = document.getElementById('searchBonus');
            const bonusesTableBody = document.getElementById('bonusesTableBody');
            const confirmModal = document.getElementById('confirmModal');
            const perPageSelector = document.getElementById('perPageSelector');
            const paginationLinksContainer = document.getElementById('pagination-links');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            console.log('CSRF Token:', csrfToken);

            // Función principal para obtener y renderizar los bonos
            async function fetchAndRenderBonuses() {
                try {
                    const url = new URL('/rh/loadchart/field-bonuses-data', window.location
                        .origin);
                    url.searchParams.append('per_page', itemsPerPage);
                    url.searchParams.append('page', currentPage);
                    url.searchParams.append('search', currentSearchTerm);

                    const response = await fetch(url);
                    if (!response.ok) throw new Error('Network response was not ok');

                    const data = await response.json();
                    renderTable(data.data);
                    renderPagination(data);

                } catch (error) {
                    console.error('Error fetching bonuses:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de carga',
                        text: 'No se pudo cargar la lista de bonos. Por favor, intente de nuevo.'
                    });
                }
            }

            // Renderizar las filas de la tabla
            function renderTable(bonuses) {
                bonusesTableBody.innerHTML = '';

                if (bonuses.length > 0) {
                    bonuses.forEach(bonus => {
                        const row = document.createElement('tr');
                        row.dataset.bonusId = bonus.id;

                        const statusClass = bonus.is_active ? 'active' : 'inactive';
                        const statusText = bonus.is_active ? 'Activo' : 'Inactivo';
                        const toggleTitle = bonus.is_active ? 'Desactivar' : 'Activar';
                        const toggleIcon = bonus.is_active ? 'fa-toggle-on' : 'fa-toggle-off';
                        const amountClass = bonus.currency === 'USD' ? 'usd-amount' : 'mxn-amount';

                        row.innerHTML = `
                    <td>${bonus.employee_category}</td>
                    <td>${bonus.bonus_type}</td>
                    <td><span class="badge identifier-badge">${bonus.bonus_identifier}</span></td>
                    <td><span class="amount ${amountClass}">${bonus.currency} ${parseFloat(bonus.amount).toFixed(2)}</span></td>
                    <td>
                        <span class="status-badge ${statusClass}">
                            <i class="fas fa-circle"></i> ${statusText}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon btn-toggle" title="${toggleTitle}" data-bonus-id="${bonus.id}">
                                <i class="fas ${toggleIcon}"></i>
                            </button>
                            <button class="btn-icon btn-edit" title="Editar" data-bonus-id="${bonus.id}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon btn-delete" title="Eliminar" data-bonus-id="${bonus.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                        bonusesTableBody.appendChild(row);
                    });
                } else {
                    bonusesTableBody.innerHTML = `
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fas fa-money-bill-wave"></i>
                            <h4>No hay bonos registrados que coincidan.</h4>
                            <p>Intenta con otro término de búsqueda.</p>
                        </div>
                    </td>
                </tr>
            `;
                }
            }

            // Renderizar los enlaces de paginación
            function renderPagination(data) {
                paginationLinksContainer.innerHTML = '';
                const totalPages = data.last_page;
                if (totalPages <= 1) return;

                // Botón "Anterior"
                const prevButton = document.createElement('a');
                prevButton.href = '#';
                prevButton.classList.add('pagination-item');
                if (data.current_page === 1) prevButton.classList.add('disabled');
                prevButton.innerHTML = '&laquo; Anterior';
                prevButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (data.current_page > 1) {
                        currentPage = data.current_page - 1;
                        fetchAndRenderBonuses();
                    }
                });
                paginationLinksContainer.appendChild(prevButton);

                // Números de página
                for (let i = 1; i <= totalPages; i++) {
                    const pageLink = document.createElement('a');
                    pageLink.href = '#';
                    pageLink.classList.add('pagination-item');
                    if (i === data.current_page) pageLink.classList.add('active');
                    pageLink.textContent = i;
                    pageLink.addEventListener('click', (e) => {
                        e.preventDefault();
                        currentPage = i;
                        fetchAndRenderBonuses();
                    });
                    paginationLinksContainer.appendChild(pageLink);
                }

                // Botón "Siguiente"
                const nextButton = document.createElement('a');
                nextButton.href = '#';
                nextButton.classList.add('pagination-item');
                if (data.current_page === totalPages) nextButton.classList.add('disabled');
                nextButton.innerHTML = 'Siguiente &raquo;';
                nextButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (data.current_page < totalPages) {
                        currentPage = data.current_page + 1;
                        fetchAndRenderBonuses();
                    }
                });
                paginationLinksContainer.appendChild(nextButton);
            }

            // Función mejorada para guardar o actualizar un bono
            async function saveBonus() {
                // Obtener y limpiar valores del formulario
                const employeeCategory = document.getElementById('employee_category').value.trim();
                const bonusType = document.getElementById('bonus_type').value.trim();
                const bonusIdentifier = document.getElementById('bonus_identifier').value.trim();
                const amount = document.getElementById('amount').value;
                const currency = document.getElementById('currency').value;

                // Validación básica en el cliente
                if (!employeeCategory || !bonusType || !bonusIdentifier || !amount || !currency) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campos requeridos',
                        text: 'Por favor, complete todos los campos obligatorios.'
                    });
                    return;
                }

                // Validar que el monto sea un número válido
                const numericAmount = parseFloat(amount);
                if (isNaN(numericAmount) || numericAmount < 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Monto inválido',
                        text: 'Por favor, ingrese un monto válido mayor o igual a 0.'
                    });
                    return;
                }

                // Crear el payload
                const requestData = {
                    employee_category: employeeCategory,
                    bonus_type: bonusType,
                    bonus_identifier: bonusIdentifier,
                    amount: numericAmount,
                    currency: currency
                };

                // Configurar URL y método
                let url = '/rh/loadchart/field-bonuses';
                let method = 'POST'; // Default method for new records

                if (isEditing) {
                    url += `/${currentBonusId}`;
                    method = 'PUT'; // Change method to PUT for updates
                }

                // Configurar headers
                const headers = {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                };

                try {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

                    const response = await fetch(url, {
                        method: method, // Use the dynamically set method
                        headers: headers,
                        body: JSON.stringify(requestData)
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        resetForm();
                        fetchAndRenderBonuses();
                    } else {
                        let errorMessage = data.message || 'Error desconocido';

                        if (data.errors) {
                            const errorList = Object.values(data.errors).flat();
                            errorMessage = errorList.join('\n');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error de validación',
                            text: errorMessage,
                            customClass: {
                                content: 'text-left'
                            }
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor. Verifique su conexión e inténtelo de nuevo.'
                    });
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = isEditing ?
                        '<i class="fas fa-save"></i> Actualizar Bono' :
                        '<i class="fas fa-save"></i> Guardar Bono';
                }
            }

            // Función para cargar un bono en el formulario para su edición
            async function editBonus(bonusId) {
                try {
                    const response = await fetch(`/rh/loadchart/field-bonuses/${bonusId}/edit`);
                    if (!response.ok) throw new Error('Error al obtener los datos');

                    const bonus = await response.json();

                    document.getElementById('bonus_id').value = bonus.id;
                    document.getElementById('employee_category').value = bonus.employee_category;
                    document.getElementById('bonus_type').value = bonus.bonus_type;
                    document.getElementById('bonus_identifier').value = bonus.bonus_identifier;
                    document.getElementById('amount').value = bonus.amount;
                    document.getElementById('currency').value = bonus.currency;

                    isEditing = true;
                    currentBonusId = bonusId;
                    cancelEditBtn.style.display = 'inline-block';
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Actualizar Bono';

                    document.querySelector('.form-section').scrollIntoView({
                        behavior: 'smooth'
                    });
                } catch (error) {
                    console.error('Error editing bonus:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo cargar el bono para editar.'
                    });
                }
            }

            // Función para cambiar el estado activo/inactivo de un bono
            async function toggleBonusStatus(bonusId) {
                try {
                    const response = await fetch(
                        `/rh/loadchart/field-bonuses/${bonusId}/toggle-status`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        });

                    const data = await response.json();

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        fetchAndRenderBonuses();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                } catch (error) {
                    console.error('Error toggling status:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error inesperado al cambiar el estado.'
                    });
                }
            }

            // Mostrar el modal de confirmación para eliminar
            function confirmDelete(bonusId) {
                currentBonusId = bonusId;
                document.getElementById('confirmMessage').textContent =
                    '¿Estás seguro de que deseas eliminar este bono de campo? Esta acción es irreversible.';
                confirmModal.style.display = 'flex';
            }

            // Eliminar el bono cuando se confirma en el modal
            async function deleteBonus() {
                try {
                    const response = await fetch(`/rh/loadchart/field-bonuses/${currentBonusId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
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
                        fetchAndRenderBonuses();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                } catch (error) {
                    console.error('Error deleting bonus:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error inesperado al eliminar el bono.'
                    });
                    confirmModal.style.display = 'none';
                }
            }

            // Restablecer el formulario a su estado inicial
            function resetForm() {
                bonusForm.reset();
                isEditing = false;
                currentBonusId = null;
                cancelEditBtn.style.display = 'none';
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Bono';
                document.getElementById('bonus_id').value = '';

                console.log('Form reset - isEditing:', isEditing, 'currentBonusId:', currentBonusId);
            }

            // Event listeners
            bonusForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Form submitted');
                saveBonus();
            });

            cancelEditBtn.addEventListener('click', function() {
                console.log('Edit cancelled');
                resetForm();
            });

            searchInput.addEventListener('input', () => {
                currentSearchTerm = searchInput.value;
                currentPage = 1;
                fetchAndRenderBonuses();
            });

            perPageSelector.addEventListener('change', function() {
                itemsPerPage = this.value;
                currentPage = 1;
                fetchAndRenderBonuses();
            });

            // Delegación de eventos para los botones de la tabla
            bonusesTableBody.addEventListener('click', function(e) {
                const target = e.target.closest('button');
                if (!target) return;

                const bonusId = target.dataset.bonusId;
                console.log('Button clicked:', target.className, 'Bonus ID:', bonusId);

                if (target.classList.contains('btn-edit')) {
                    editBonus(bonusId);
                } else if (target.classList.contains('btn-toggle')) {
                    toggleBonusStatus(bonusId);
                } else if (target.classList.contains('btn-delete')) {
                    confirmDelete(bonusId);
                }
            });

            // Event listeners del modal de confirmación
            document.getElementById('confirmAction').addEventListener('click', deleteBonus);

            document.getElementById('cancelConfirm').addEventListener('click', function() {
                confirmModal.style.display = 'none';
            });

            document.querySelector('#confirmModal .close-modal').addEventListener('click', function() {
                confirmModal.style.display = 'none';
            });

            // Carga inicial de datos
            console.log('Initializing application...');
            fetchAndRenderBonuses();
        });
    </script>
@endsection
