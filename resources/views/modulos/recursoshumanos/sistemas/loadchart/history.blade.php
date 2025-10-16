@extends('modulos.recursoshumanos.sistemas.loadchart.index')
@push('styles')
    <link href="{{ asset('assets/css/recursoshumanos/loadchart/approval.css') }}" rel="stylesheet">
    <style>
        /* Estilos específicos de la tabla de historial */
        .history-container {
            padding: 20px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .history-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            font-size: 0.9rem;
        }
        .history-table th, .history-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        .history-table th {
            background-color: #34495e;
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        .history-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        .badge-approved { background-color: var(--approved-green); color: white; }
        .badge-reviewed { background-color: var(--reviewed-yellow); color: #333; }
        .badge-rejected { background-color: var(--rejected-red); color: white; }
        .badge-activity { background-color: #49c0c9; color: white; }
        .badge-service { background-color: #3498db; color: white; }
        .badge-bonus { background-color: #f1c40f; color: #333; }

        /* Estilos del filtro */
        .history-filter-bar {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #eee;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .filter-group label {
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .filter-group input, .filter-group button {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
        .filter-group button {
            background-color: #3498db;
            color: white;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 5px; /* Ajuste para alinear con inputs */
        }
        .filter-group button:hover {
            background-color: #2980b9;
        }
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 50;
            border-radius: 12px;
        }
    </style>
@endpush

@section('content')
    <div class="history-container position-relative">
        <h2 class="approval-header h2" style="margin-bottom: 15px; color: #2c3e50;">
            <i class="fas fa-history" style="font-size: 1.2em;"></i> Historial de Aprobaciones de Actividades
        </h2>
        <p style="color: #7f8c8d; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 20px;">
            Consulta todas las acciones de revisión y aprobación realizadas sobre los registros de actividades.
        </p>

        <div class="history-filter-bar">
            <div class="filter-group" style="flex-grow: 0.5;">
                <label for="start_date">Fecha de Inicio:</label>
                <input type="date" id="start_date">
            </div>
            <div class="filter-group" style="flex-grow: 0.5;">
                <label for="end_date">Fecha de Fin:</label>
                <input type="date" id="end_date">
            </div>
            <div class="filter-group" style="flex-grow: 0.2;">
                <button id="apply-filter-btn">
                    <i class="fas fa-filter"></i> Aplicar Filtro
                </button>
            </div>
        </div>

        <div class="table-container">
            <table class="history-table">
                <thead>
                    <tr>
                        <th width="120px">Fecha Actividad</th>
                        <th id="th-empleado" width="150px">Empleado Afectado</th>
                        <th width="150px">Tipo de Ítem</th>
                        <th width="100px">Acción</th>
                        <th>Detalle del Ítem</th>
                        <th width="150px">Realizado por</th>
                        <th>Comentario</th>
                        <th width="120px">Fecha Acción</th>
                    </tr>
                </thead>
                <tbody id="history-table-body">
                    <tr><td colspan="8" style="text-align: center;">Cargando historial...</td></tr>
                </tbody>
            </table>
        </div>

        <div id="pagination-links" class="d-flex justify-content-center mt-3">
            </div>

        <div id="loading-overlay" class="loading-overlay" style="display: none;">
            <i class="fas fa-spinner fa-spin fa-2x" style="color: #3498db;"></i>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const historyTableBody = document.getElementById('history-table-body');
            const applyFilterBtn = document.getElementById('apply-filter-btn');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            const paginationLinks = document.getElementById('pagination-links');
            const loadingOverlay = document.getElementById('loading-overlay');
            const employeeHeader = document.getElementById('th-empleado');

            // Inicialización de Flatpickr para un mejor selector de fecha (opcional)
            flatpickr(startDateInput, { dateFormat: "Y-m-d" });
            flatpickr(endDateInput, { dateFormat: "Y-m-d" });

            // Función para obtener los datos del historial
            async function fetchHistoryData(page = 1) {
                const startDate = startDateInput.value;
                const endDate = endDateInput.value;

                loadingOverlay.style.display = 'flex';
                historyTableBody.innerHTML = '<tr><td colspan="8" style="text-align: center;"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>';

                try {
                    const response = await fetch(`/recursoshumanos/loadchart/history/data?page=${page}&start_date=${startDate}&end_date=${endDate}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Error al obtener los datos del historial.');
                    }

                    const data = await response.json();
                    renderHistoryTable(data.history.data, data.isAssigned);
                    renderPagination(data.history);

                } catch (error) {
                    historyTableBody.innerHTML = `<tr><td colspan="8" style="text-align: center; color: #e74c3c;">
                        <i class="fas fa-exclamation-triangle"></i> Error al cargar el historial: ${error.message}
                    </td></tr>`;
                } finally {
                    loadingOverlay.style.display = 'none';
                }
            }

            // Función para renderizar la tabla con los datos
            function renderHistoryTable(logs, isAssigned) {
                historyTableBody.innerHTML = '';

                // Ajustar el encabezado de la columna Empleado
                employeeHeader.textContent = isAssigned ? 'Empleado Afectado' : 'Tu Registro';

                if (logs.length === 0) {
                    historyTableBody.innerHTML = '<tr><td colspan="8" style="text-align: center;">No se encontraron registros de historial en el período seleccionado.</td></tr>';
                    return;
                }

                logs.forEach(log => {
                    const tr = document.createElement('tr');

                    // Colorear el badge de la acción
                    const actionBadge = `<span class="badge badge-${log.action.split('_').pop()}">${log.action.replace(/_/g, ' ')}</span>`;

                    // Colorear el badge del tipo de ítem
                    const typeBadge = `<span class="badge badge-${log.log_type.replace(/_/, '-')}">${log.log_type.replace(/_/, ' ')}</span>`;

                    // Detalle del ítem
                    let itemDetails = '';
                    if (log.item_details) {
                         const details = log.item_details;
                         if (details.type) {
                             itemDetails += `<strong>Tipo:</strong> ${details.type}<br>`;
                         }
                         if (details.identifier) {
                             itemDetails += `<strong>ID:</strong> ${details.identifier}`;
                         }
                    }

                    // Determinar el nombre del empleado
                    const employeeName = log.employee ? log.employee.full_name : 'N/A';

                    tr.innerHTML = `
                        <td>${log.activity_date}</td>
                        <td>${employeeName}</td>
                        <td>${typeBadge}</td>
                        <td>${actionBadge}</td>
                        <td>${itemDetails || '-'}</td>
                        <td>${log.user ? log.user.name : 'Sistema'}</td>
                        <td>${log.comment || '-'}</td>
                        <td>${new Date(log.created_at).toLocaleString('es-ES')}</td>
                    `;
                    historyTableBody.appendChild(tr);
                });
            }

            // Función para renderizar los enlaces de paginación
            function renderPagination(pagination) {
                paginationLinks.innerHTML = '';

                if (pagination.last_page > 1) {
                    const ul = document.createElement('ul');
                    ul.classList.add('pagination', 'm-0');

                    // Función para crear un botón de página
                    const createButton = (label, page, isActive = false, isDisabled = false) => {
                        const li = document.createElement('li');
                        li.classList.add('page-item', isActive ? 'active' : '', isDisabled ? 'disabled' : '');

                        const a = document.createElement('a');
                        a.classList.add('page-link');
                        a.innerHTML = label;
                        a.href = '#';

                        if (!isDisabled && !isActive) {
                             a.addEventListener('click', (e) => {
                                 e.preventDefault();
                                 fetchHistoryData(page);
                             });
                        }

                        li.appendChild(a);
                        return li;
                    };

                    // Botón Anterior
                    ul.appendChild(createButton('<i class="fas fa-chevron-left"></i>', pagination.current_page - 1, false, pagination.current_page === 1));

                    // Páginas
                    pagination.links.forEach(link => {
                        if (link.url && link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;') {
                            const pageNumber = new URL(link.url).searchParams.get('page');
                            ul.appendChild(createButton(link.label, pageNumber, link.active));
                        }
                    });

                    // Botón Siguiente
                    ul.appendChild(createButton('<i class="fas fa-chevron-right"></i>', pagination.current_page + 1, false, pagination.current_page === pagination.last_page));

                    paginationLinks.appendChild(ul);
                }
            }


            // Listener para el botón de filtro
            applyFilterBtn.addEventListener('click', function () {
                fetchHistoryData(1);
            });

            // Reemplazar el handler del index.js para que cargue esta vista
            document.querySelector('a[data-route="history"]').addEventListener('click', function(e) {
                e.preventDefault();
                // Simula la navegación de Laravel al cambiar la URL.
                // Esto permite que el index.js del _layout principal maneje la carga,
                // pero si no existe, la vista se carga directamente.
                window.location.href = this.getAttribute('data-route');
            });


            // Carga inicial
            fetchHistoryData(1);
        });
    </script>
@endpush
