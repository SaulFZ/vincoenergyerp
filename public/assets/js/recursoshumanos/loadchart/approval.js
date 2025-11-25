document.addEventListener('DOMContentLoaded', function () {
    // Inicialización del script
    initializeApprovalTable();
    setupModalEventListeners();
});

// Asegurarse de limpiar el intervalo cuando la página se cierre
window.addEventListener('beforeunload', function () {
    stopAutoRefresh();
});

// =========================================================================================
// 🌎 GLOBAL VARIABLES
// =========================================================================================
let currentModalData = {
    employeeId: null,
    date: null,
    dailyActivities: null
};

let autoRefreshInterval = null;
let lastUpdateTime = new Date();
let monthlyDays = [];
let employees = [];
// 🆕 Nuevas variables globales (CORREGIDO: Sin redeclaración "let" si ya están en Blade)
allEmployeeRows = [];
allSquadRows = [];
let isFiltersOpen = false;

// Detener el sistema de actualización automática
function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
    document.removeEventListener('visibilitychange', handleVisibilityChange);
}

// Manejar cambios de visibilidad de la pestaña
function handleVisibilityChange() {
    if (!document.hidden) {
        // La pestaña está visible, verificar actualizaciones
        checkForUpdates();
    }
}

function initializeApprovalTable() {
    setupEventListeners();
    initializeEmployeeNameClickListeners();
    initializeEmployeeModalListeners();

    loadMonthData(false).then(() => {
        showQuincena(1);
        setActiveButton("quincena1");
        updatePeriodInfo();
        // 🆕 Inicializar la lista de filas para el filtrado
        allEmployeeRows = Array.from(document.querySelectorAll('.employee-row'));
        allSquadRows = Array.from(document.querySelectorAll('.squad-group-row'));
    });

    // 🆕 Inicializar Event Listeners de Filtro
    if (canSeeFilters) {
        document.getElementById('toggle-filters-btn').addEventListener('click', toggleFilters);
        document.getElementById('department-filter').addEventListener('change', applyFilters);
        document.getElementById('employee-search').addEventListener('input', applyFilters);
    }
}
//INICIAMOS LOGICA DE NUEVO MODAL
// Variables globales para el modal de empleado
let currentEmployeeModal = null;

/**
 * Inicializa los event listeners para los nombres de empleados
 */
function initializeEmployeeNameClickListeners() {
    document.getElementById('approval-table-body').addEventListener('click', function (event) {
        const employeeNameCell = event.target.closest('.employee-info-cell');
        if (employeeNameCell) {
            event.preventDefault();
            const employeeRow = employeeNameCell.closest('.employee-row');
            const employeeId = employeeRow.getAttribute('data-employee-id');
            const employeeName = employeeNameCell.textContent.trim();

            openEmployeeDetailModal(employeeId, employeeName);
        }
    });
}

/**
 * Abre el modal de detalles del empleado
 */
function openEmployeeDetailModal(employeeId, employeeName, month = null, year = null) {
    const modal = document.getElementById('employee-detail-modal');
    const subtitle = document.getElementById('employee-modal-subtitle');
    const content = document.getElementById('employee-detail-content');

    // Mostrar loading
    modal.style.display = 'block';
    subtitle.textContent = `Cargando información de ${employeeName}`;
    content.innerHTML = `
        <div class="loading-spinner-container">
            <div class="loading-spinner-lg"></div>
            <div class="loading-message">Cargando calendario...</div>
        </div>
    `;

    // Construir URL - usa la misma ruta pero con parámetros
    let url = `/recursoshumanos/loadchart/calendar?employee_id=${employeeId}`;
    if (month && year) {
        url += `&month=${month}&year=${year}`;
    }

    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = data.html;
                subtitle.textContent = employeeName;

                // ⚠️ CORRECCIÓN CLAVE:
                // Llamar a la función de inicialización global de calendar.js
                if (typeof initializeModalCalendarScripts === 'function') {
                    initializeModalCalendarScripts(employeeId);
                } else {
                    console.error("Error: La función initializeModalCalendarScripts no está definida. Asegúrate de que calendar.js esté cargado y modificado correctamente.");
                }

            } else {
                throw new Error(data.message || 'Error al cargar la información');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = `
                <div class="error-container">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Error al cargar la información</h3>
                    <p>${error.message}</p>
                    <button class="btn btn-primary" onclick="openEmployeeDetailModal(${employeeId}, '${employeeName}')">
                        <i class="fas fa-redo"></i> Reintentar
                    </button>
                </div>
            `;
        });
}

/**
 * Cierra el modal de detalles del empleado
 */
function closeEmployeeDetailModal() {
    const modal = document.getElementById('employee-detail-modal');
    modal.style.display = 'none';
    currentEmployeeModal = null;
}

/**
 * Inicializa los event listeners del modal
 */
function initializeEmployeeModalListeners() {
    // Cerrar modal con el botón X
    document.querySelector('.employee-detail-close-btn').addEventListener('click', closeEmployeeDetailModal);

    // Cerrar modal haciendo clic fuera del contenido
    document.getElementById('employee-detail-modal').addEventListener('click', function (event) {
        if (event.target === this) {
            closeEmployeeDetailModal();
        }
    });

    // Cerrar modal con la tecla Escape
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeEmployeeDetailModal();
        }
    });
}
//TERMINA LA LOGICA DEL NUEVO MODAL

// 🆕 LÓGICA DE FILTROS

/**
 * Muestra/Oculta el contenedor de filtros
 */
function toggleFilters() {
    const filtersContainer = document.getElementById('filters-container');
    const toggleButton = document.getElementById('toggle-filters-btn');

    if (isFiltersOpen) {
        filtersContainer.style.display = 'none';
        toggleButton.innerHTML = '<i class="fas fa-filter"></i> Abrir Filtros';
    } else {
        filtersContainer.style.display = 'flex'; // O 'block', dependiendo de tu CSS. Usamos flex para el diseño de los filtros.
        toggleButton.innerHTML = '<i class="fas fa-times"></i> Cerrar Filtros';
    }
    isFiltersOpen = !isFiltersOpen;
}


/**
 * Aplica los filtros de departamento y búsqueda.
 */
function applyFilters() {
    // Solo aplica los filtros si el usuario tiene el permiso
    if (!canSeeFilters) {
        return;
    }

    const departmentFilter = document.getElementById('department-filter').value;
    const searchFilter = document.getElementById('employee-search').value.toLowerCase().trim();

    allEmployeeRows.forEach(row => {
        const department = row.getAttribute('data-department');
        const employeeName = row.querySelector('.employee-info-cell').textContent.trim().toLowerCase();

        // Buscar info del empleado en la lista global para obtener el número
        const employeeId = row.getAttribute('data-employee-id');
        const employeeData = employees.find(e => e.id.toString() === employeeId);
        const employeeNumber = employeeData ? employeeData.employee_number.toString().toLowerCase() : '';


        // 1. Filtrar por Departamento (DEBE coincidir con el valor EXACTO o ser vacío "Todos")
        const matchesDepartment = !departmentFilter || department === departmentFilter;

        // 2. Filtrar por Búsqueda (Nombre o Número de Empleado)
        const matchesSearch = !searchFilter || employeeName.includes(searchFilter) || employeeNumber.includes(searchFilter);

        // 3. Aplicar visibilidad a las filas principales (employee-row)
        const isVisible = matchesDepartment && matchesSearch;
        row.style.display = isVisible ? '' : 'none';

        // 4. Ocultar filas de detalle asociadas al empleado si la fila principal no es visible
        let nextRow = row.nextElementSibling;
        while (nextRow && nextRow.classList.contains('activity-row') && nextRow.getAttribute('data-employee-id') === row.getAttribute('data-employee-id')) {
            // Si la fila de empleado principal se oculta, también se ocultan sus detalles
            if (!isVisible) {
                nextRow.style.display = 'none';
            } else {
                // Si la fila de empleado principal es visible, restaurar/mantener la visibilidad
                // definida por el renderizador (si tiene ítems o está colapsada/expandida).
                // Mantenemos el estado de la clase 'hidden' y la visibilidad de ítems del render.
                const isHiddenByToggle = nextRow.classList.contains('hidden');
                const isHiddenByRender = nextRow.getAttribute('data-has-items') === 'false'; // Suponiendo que el renderizador añade este atributo para filas sin ítems

                // Mantenemos la visibilidad por defecto del renderizador
                if (isHiddenByToggle || isHiddenByRender) {
                    nextRow.style.display = 'none';
                } else {
                    nextRow.style.display = '';
                }
            }
            nextRow = nextRow.nextElementSibling;
        }
    });

    // 5. Recalcular la visibilidad de las filas de cuadrilla (squad-group-row)
    allSquadRows.forEach(squadRow => {
        const nextRow = squadRow.nextElementSibling;
        let showSquadRow = false;
        let currentRow = nextRow;

        // Iterar sobre las filas de empleados hasta encontrar la siguiente fila de cuadrilla
        while (currentRow && !currentRow.classList.contains('squad-group-row')) {
            // Verificar si la fila es de empleado y es visible por los filtros
            if (currentRow.classList.contains('employee-row') && currentRow.style.display !== 'none') {
                showSquadRow = true;
                break;
            }
            // Saltar las filas de actividad (detalle)
            if (currentRow.classList.contains('activity-row')) {
                currentRow = currentRow.nextElementSibling;
                continue;
            }
            currentRow = currentRow.nextElementSibling;
        }

        squadRow.style.display = showSquadRow ? '' : 'none';
    });

    // Re-aplicar el filtro de quincena para asegurar que las celdas de días se muestren correctamente
    if (currentView === 'quincena1') {
        showQuincena(1);
    } else if (currentView === 'quincena2') {
        showQuincena(2);
    } else {
        showFullMonth();
    }

    // Volver a calcular totales para actualizar el resumen (aunque esto es opcional si solo se filtra visualmente)
    calculateAndRenderTotals();
}
// TERMINA LÓGICA DE FILTROS


/**
 * Inicializar el sistema de actualización automática
 */
function initializeAutoRefresh() {
    // Verificar cambios cada 10 segundos
    autoRefreshInterval = setInterval(checkForUpdates, 10000);

    // También verificar cuando la pestaña gana foco
    document.addEventListener('visibilitychange', handleVisibilityChange);

    // Guardar el tiempo inicial
    lastUpdateTime = new Date();
}

/**
 * Verifica si hay actualizaciones en el servidor
 */
async function checkForUpdates() {
    try {
        const response = await fetch('/recursoshumanos/loadchart/check-updates', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                last_update: lastUpdateTime.toISOString(),
                month: currentMonth,
                year: currentYear
            })
        });

        const data = await response.json();

        if (data.success && data.has_updates) {
            // Hay actualizaciones, refrescar los datos de forma silenciosa.
            await refreshData();
        }
    } catch (error) {
        console.error('Error verificando actualizaciones:', error);
    }
}

/**
 * Refresca los datos desde el servidor.
 */
async function refreshData() {
    try {
        stopAutoRefresh(); // Detener durante la carga

        const response = await fetch(`/recursoshumanos/loadchart/approval-data/${currentYear}/${currentMonth}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error('Error al cargar los datos actualizados');
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Error al cargar los datos actualizados');
        }

        // Actualizar variables globales
        workLogsData = data.workLogsData;
        fortnightlyConfig = data.fortnightlyConfig;
        loadChartAssignments = data.loadChartAssignments;
        canSeeAmounts = data.canSeeAmounts; // 👈🏼 CRÍTICO: canSeeAmounts se actualiza aquí
        userPermissions = data.userPermissions;
        monthlyDays = data.monthlyDays; // 👈🏼 CRÍTICO: Actualizar la variable monthlyDays
        employees = data.employees; // 👈🏼 CRÍTICO: Actualizar la variable employees

        // LLAMAR A loadMonthData con isRefresh = true para que maneje la actualización de la tabla y la vista.
        await loadMonthData(true);

        // El resto de la lógica de reinicio de autoRefresh y lastUpdateTime se mueve a loadMonthData
    } catch (error) {
        console.error('Error refreshing data:', error);
        initializeAutoRefresh(); // Reiniciar el refresco incluso con error
    }
}


function setupEventListeners() {
    // Navegación de períodos
    document.getElementById("prev-period").addEventListener("click", () => navigateToPreviousMonth());
    document.getElementById("next-period").addEventListener("click", () => navigateToNextMonth());

    // Navegación de quincenas
    document.getElementById("quincena1").addEventListener("click", () => {
        showQuincena(1);
        setActiveButton("quincena1");
    });

    document.getElementById("quincena2").addEventListener("click", () => {
        showQuincena(2);
        setActiveButton("quincena2");
    });

    document.getElementById("full-month").addEventListener("click", () => {
        showFullMonth();
        setActiveButton("full-month");
    });

    // Botón volver al calendario
    const backToCalendarBtn = document.getElementById("back-to-calendar");
    if (backToCalendarBtn) {
        backToCalendarBtn.addEventListener("click", function (e) {
            e.preventDefault();
            window.location.href = "/recursoshumanos/loadchart/calendar";
        });
    }

    // Agregar manejador de eventos para el modal
    document.getElementById('approval-table-body').addEventListener('click', handleTableClick);

    // Asignar listeners a los botones de aprobación masiva (delegación)
    document.getElementById('approval-table-body').addEventListener('click', function (event) {
        const btn = event.target.closest('.btn-approve');
        if (btn) {
            handleApprove.call(btn, event);
        }
        const btnReview = event.target.closest('.btn-review');
        if (btnReview) {
            handleReview.call(btnReview, event);
        }
    });
}

function setupModalEventListeners() {
    const modal = document.getElementById('approvalModal');
    const closeButtons = modal.querySelectorAll('.modal-approval-close, .modal-approval-close-btn');
    closeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    });

    document.getElementById('modal-save-btn').addEventListener('click', saveModalChanges);
}

function handleTableClick(event) {
    const statusIndicator = event.target.closest('.status-indicator');
    if (statusIndicator) {
        const cell = statusIndicator.closest('.data-cell');
        const row = cell.closest('.employee-row');
        const employeeId = row.getAttribute('data-employee-id');
        const date = cell.getAttribute('data-date');

        // 🆕 Bloquear el modal si el usuario no es Revisor ni Aprobador del empleado
        const employeeAssignment = loadChartAssignments.find(a => a.employee_id.toString() === employeeId);
        const isReviewerForEmployee = employeeAssignment && employeeAssignment.reviewer_id === currentUserId;
        const isApproverForEmployee = employeeAssignment && employeeAssignment.approver_id === currentUserId;

        if (!isReviewerForEmployee && !isApproverForEmployee) {
            // Solo mostrar el mensaje si hay actividad, si no, se muestra el mensaje 'No hay actividades'
            const employeeData = workLogsData.find(log => log.employee_id.toString() === employeeId);
            const dailyActivity = employeeData?.daily_activities.find(act => act.date === date);

            if (dailyActivity) {
                showSwalNotification('Acceso Denegado', 'Solo puede abrir el detalle de actividad para los empleados que tiene asignados como Revisor o Aprobador.', 'error');
            } else {
                showSwalNotification('Información', 'No hay actividades registradas para este día.', 'info');
            }
            return;
        }


        const employeeData = workLogsData.find(log => log.employee_id.toString() === employeeId);
        const dailyActivity = employeeData?.daily_activities.find(act => act.date === date);

        if (dailyActivity) {
            openApprovalModal(employeeData, dailyActivity);
        } else {
            showSwalNotification('Información', 'No hay actividades registradas para este día.', 'info');
        }
    }
}

async function saveModalChanges() {
    const modal = document.getElementById('approvalModal');
    const tableBody = modal.querySelector('#modal-table-body');
    const rows = tableBody.querySelectorAll('tr');
    const changes = [];

    rows.forEach(row => {
        const itemType = row.getAttribute('data-item-type');
        // itemIndex puede ser 'null' si es actividad principal
        const itemIndex = row.getAttribute('data-item-index') !== 'null' ? parseInt(row.getAttribute('data-item-index')) : null;
        const select = row.querySelector('.item-approval-selector');
        const comment = row.querySelector('.modal-comment').value;

        if (select && !select.disabled) {
            const originalItem = getItemFromActivity(currentModalData.dailyActivities, itemType, itemIndex);
            const newStatus = select.value.toLowerCase();
            const originalStatus = originalItem.status ? originalItem.status.toLowerCase() : 'under_review';

            // Solo registrar el cambio si el estado es diferente al original
            if (originalStatus !== newStatus) {
                changes.push({
                    date: currentModalData.date,
                    item_type: itemType,
                    item_index: itemIndex,
                    status: newStatus,
                    rejection_reason: comment
                });
            }
        }
    });

    if (changes.length > 0) {
        await updateDailyItemsStatus(currentModalData.employeeId, changes);
        // El modal se cierra en updateDailyItemsStatus si es exitoso
    } else {
        showSwalNotification('Información', 'No hay cambios para guardar.', 'info');
        // No cerrar el modal
    }
}

function getItemFromActivity(dailyActivity, itemType, itemIndex) {
    if (itemType === 'activity') {
        return { status: dailyActivity.activity_status, rejection_reason: dailyActivity.rejection_reason };
    }
    const listMap = {
        'food_bonuses': dailyActivity.food_bonuses,
        'field_bonuses': dailyActivity.field_bonuses,
        'services_list': dailyActivity.services_list
    };

    if (listMap[itemType] && listMap[itemType][itemIndex]) {
        return listMap[itemType][itemIndex];
    }
    return null;
}

function openApprovalModal(employeeData, dailyActivity) {
    const modal = document.getElementById('approvalModal');
    const subtitle = modal.querySelector('.modal-approval-subtitle');
    const tableBody = modal.querySelector('#modal-table-body');
    // Búsqueda del nombre del empleado en la tabla
    const employeeRow = document.querySelector(`.employee-row[data-employee-id="${employeeData.employee_id}"]`);
    // CRITICAL: El nombre está en la 3ra columna (index 2) de la fila.
    // Ojo: Si la fila no tiene <td>[rowspan] por estar agrupada, busca el elemento que contenga el nombre.
    let employeeName = 'Empleado Desconocido';
    if (employeeRow) {
        // Busca la celda con la clase employee-info-cell (la que contiene el nombre).
        const nameCell = employeeRow.querySelector('.employee-info-cell');
        if (nameCell) {
            employeeName = nameCell.textContent.trim();
        }
    }


    const dateWithTime = dailyActivity.date + 'T12:00:00'; // Añadir T12:00:00 para evitar problemas de zona horaria

    const formattedDate = new Date(dateWithTime).toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
    currentModalData.employeeId = employeeData.employee_id;
    currentModalData.date = dailyActivity.date;
    currentModalData.dailyActivities = dailyActivity;

    const assignment = loadChartAssignments.find(assgn => assgn.employee_id === employeeData.employee_id);
    const isReviewer = assignment && assignment.reviewer_id === currentUserId;
    const isApprover = assignment && assignment.approver_id === currentUserId;

    subtitle.textContent = `${employeeName} - ${formattedDate}`;
    tableBody.innerHTML = '';

    // 1. Actividad principal
    if (dailyActivity.activity_type) {
        const activityStatus = dailyActivity.activity_status ?? 'under_review';

        let additionalDetails = '';
        if (dailyActivity.activity_type === 'P' && dailyActivity.well_name) {
            additionalDetails = `<strong>Pozo:</strong> ${dailyActivity.well_name}`;
        } else if (dailyActivity.activity_type === 'C' && dailyActivity.commissioned_to) {
            additionalDetails = `<strong>Área Comisionada:</strong> ${dailyActivity.commissioned_to}`;
        }
        // 👇 INICIO DE MODIFICACIÓN: Mostrar Destino y Motivo para Viaje (V)
        else if (dailyActivity.activity_type === 'V') {
            const destination = dailyActivity.travel_destination || 'N/A';
            const reason = dailyActivity.travel_reason || 'N/A';
            additionalDetails = `
                <div><strong>Destino:</strong> ${destination}</div>
                <div><strong>Motivo:</strong> ${reason}</div>
            `;
        }
        // 👆 FIN DE MODIFICACIÓN
        else if (dailyActivity.activity_description) {
            additionalDetails = `Descripción: ${dailyActivity.activity_description}`;
        }


        addRowToModalTable(
            'Actividad',
            dailyActivity.activity_description || '',
            dailyActivity.activity_type || 'N',
            additionalDetails,
            'activity',
            null,
            activityStatus,
            dailyActivity.rejection_reason,
            null,
            isReviewer,
            isApprover
        );
    }

    // 2. Bonos de Comida
    // ... (Lógica de Bonos y Servicios sigue aquí, sin cambios)
    if (dailyActivity.food_bonuses && dailyActivity.food_bonuses.length > 0) {
        dailyActivity.food_bonuses.forEach((bonus, index) => {
            const amount = canSeeAmounts ? `\$${Number(bonus.daily_amount).toFixed(2)} MXN` : null;
            const details = `<strong>${bonus.bonus_type}</strong>`;
            addRowToModalTable(
                'Bono',
                details,
                'Comida',
                `Cantidad: ${bonus.num_daily}`,
                'food_bonuses',
                index,
                bonus.status,
                bonus.rejection_reason,
                amount,
                isReviewer,
                isApprover
            );
        });
    }

    // 3. Bonos de Campo
    if (dailyActivity.field_bonuses && dailyActivity.field_bonuses.length > 0) {
        dailyActivity.field_bonuses.forEach((bonus, index) => {
            let amount = null;
            if (canSeeAmounts) {
                if (bonus.currency === 'USD') {
                    amount = `\$${Number(bonus.daily_amount).toFixed(2)} USD`;
                } else {
                    amount = `\$${Number(bonus.daily_amount).toFixed(2)} MXN`;
                }
            }
            addRowToModalTable(
                'Bono',
                `<strong>${bonus.bonus_type}</strong>`,
                bonus.bonus_identifier,
                `Moneda: ${bonus.currency}`,
                'field_bonuses',
                index,
                bonus.status,
                bonus.rejection_reason,
                amount,
                isReviewer,
                isApprover
            );
        });
    }

    // 4. Servicios
    if (dailyActivity.services_list && dailyActivity.services_list.length > 0) {
        dailyActivity.services_list.forEach((service, index) => {
            const amount = canSeeAmounts ? `\$${Number(service.amount).toFixed(2)} MXN` : null;

            let payrollDetail = service.payroll_period_override ?
                `<strong>Período:</strong> ${service.payroll_period_override}` :
                `Período: Quincena Actual`;

            addRowToModalTable(
                'Servicio',
                service.service_name,
                service.service_identifier,
                payrollDetail,
                'services_list',
                index,
                service.status,
                service.rejection_reason,
                amount,
                isReviewer,
                isApprover
            );
        });
    }

    // Ocultar/mostrar columna de montos
    const amountHeader = modal.querySelector('.amount-header');
    if (amountHeader) {
        amountHeader.style.display = canSeeAmounts ? '' : 'none';
        modal.querySelectorAll('.amount-cell').forEach(cell => {
            cell.style.display = canSeeAmounts ? '' : 'none';
        });
    }

    modal.style.display = 'block';
}


function addRowToModalTable(concept, details, identifier, additionalDetails, itemType, itemIndex, status, rejectionReason, amount, isReviewer, isApprover) {
    const tableBody = document.getElementById('modal-table-body');
    const row = document.createElement('tr');
    row.setAttribute('data-item-type', itemType);
    row.setAttribute('data-item-index', itemIndex === null ? 'null' : itemIndex);

    const safeStatus = status || 'under_review';
    const currentStatusLower = safeStatus.toLowerCase();
    const approvalSelector = getModalApprovalSelectorHtml(currentStatusLower, isReviewer, isApprover, itemType, itemIndex);

    // VERIFICACIÓN CLAVE PARA HABILITAR/DESHABILITAR EL TEXTAREA:
    const isSelectorDisabled = approvalSelector.startsWith('<span') || approvalSelector.includes('disabled');

    let isLocked = false;

    // -------------------------------------------------------------
    // ⭐ LÓGICA DE BLOQUEO PARA EL COMENTARIO (MEJORADA Y SIMPLIFICADA)
    // -------------------------------------------------------------
    if (isSelectorDisabled || (!isReviewer && !isApprover)) {
        isLocked = true;
    }
    // Si el estado es Aprobado, solo el Aprobador puede cambiarlo, si no lo es, se bloquea.
    if (currentStatusLower === 'approved' && !isApprover) {
        isLocked = true;
    }
    // Si es Revisor y el ítem está Rechazado, no debe poder cambiarlo (espera corrección del empleado), por lo tanto, se bloquea.
    if (currentStatusLower === 'rejected' && isReviewer && !isApprover) {
        isLocked = true;
    }
    // Si el selector no está rígidamente bloqueado, el textarea debe estar disponible
    // para que el usuario con permisos pueda escribir un motivo de rechazo (o borrarlo).
    if (isReviewer || isApprover) {
        isLocked = false;
    }
    // Sobreescribir: Si el selector es el <span> (bloqueado rígidamente por la lógica de rol), bloquear
    if (approvalSelector.startsWith('<span')) {
        isLocked = true;
    }
    // -------------------------------------------------------------


    const commentValue = rejectionReason || '';

    // CORRECCIÓN: Si no se pueden ver montos, la celda de monto estará vacía.
    const amountCellContent = amount !== null ? `${amount}` : (canSeeAmounts ? 'N/A' : '');

    // Usamos innerHTML para permitir el formato (ej. <strong>)
    row.innerHTML = `
        <td>${concept}</td>
        <td>${details}</td>
        <td>${identifier}</td>
        <td>${additionalDetails}</td>
        <td class="amount-cell">${amountCellContent}</td>
        <td>
            ${approvalSelector}
        </td>
        <td>
            <textarea class="form-control modal-comment" placeholder="Motivo de rechazo..." ${isLocked ? 'disabled' : ''}>${commentValue}</textarea>
        </td>
    `;
    tableBody.appendChild(row);

    // ⭐ Lógica de Evento Dinámico para habilitar/deshabilitar el comentario
    const selectElement = row.querySelector('.item-approval-selector');
    const textareaElement = row.querySelector('.modal-comment');

    if (selectElement && textareaElement) {
        // Bloquear el comentario si no tiene permisos O si el select está disabled
        if (isLocked) {
            textareaElement.disabled = true;
        } else {
            // Inicialmente, si el estado no es 'rejected', deshabilitar el campo de rechazo
            if (selectElement.value !== 'rejected') {
                textareaElement.disabled = true;
            }

            // Agregar listener para controlar dinámicamente
            selectElement.addEventListener('change', function () {
                if (this.value === 'rejected') {
                    textareaElement.disabled = false;
                    textareaElement.focus();
                } else {
                    textareaElement.disabled = true;
                    // Opcional: limpiar el comentario si se cambia a un estado de aprobación/revisión
                    // textareaElement.value = '';
                }
            });
        }
    }
}

function getModalApprovalSelectorHtml(currentStatus, isReviewer, isApprover, itemType, itemIndex) {
    let options = [];

    const isItemApproved = currentStatus === 'approved';
    const isItemRejected = currentStatus === 'rejected';
    const isItemReviewed = currentStatus === 'reviewed';
    const isItemUnderReview = currentStatus === 'under_review';

    // Opciones base con traducción
    const baseOptions = [
        { value: 'under_review', text: 'Bajo Revisión', disabled: false },
        { value: 'reviewed', text: 'Revisado', disabled: false },
        { value: 'approved', text: 'Aprobado', disabled: false },
        { value: 'rejected', text: 'Rechazado', disabled: false }
    ];

    if (isApprover) { // Es Aprobador (incluye el caso de ser Aprobador y Revisor)
        // El aprobador puede cambiar a cualquier estado, PERO no puede degradar de Aprobado.
        options = baseOptions.map(option => ({ ...option, disabled: false }));

        if (isItemApproved) {
            // Regla 1: Un Aprobado solo puede ir a Rechazado o quedarse en Aprobado.
            options.find(o => o.value === 'reviewed').disabled = true;
            options.find(o => o.value === 'under_review').disabled = true;
            // Rechazado sigue disponible para des-aprobar si es necesario
            options.find(o => o.value === 'rejected').disabled = false;

        } else if (isItemRejected) {
            // Si está rechazado, el aprobador puede moverlo a cualquier otro estado.
            options.find(o => o.value === 'rejected').disabled = false;
        } else {
            // Bajo Revisión y Revisado son opciones válidas si no está aprobado/rechazado
            options.find(o => o.value === 'under_review').disabled = (isItemReviewed); // Si está Revisado, no debe volver a Bajo Revisión
        }

    } else if (isReviewer) { // Es solo revisor
        if (isItemApproved) {
            // El revisor NO puede cambiar un aprobado (bloqueado, solo el aprobador puede desaprobar/rechazar).
            const approvedOption = baseOptions.find(o => o.value === 'approved');
            return `<span class="badge badge-success">${approvedOption.text} (Bloqueado)</span>`;
        } else if (isItemRejected) {
            // El revisor NO puede cambiar un rechazado, el empleado debe corregir.
            const rejectedOption = baseOptions.find(o => o.value === 'rejected');
            return `<span class="badge badge-danger">${rejectedOption.text} (Esperando Corrección)</span>`;
        } else {
            // Puede cambiar entre bajo revisión, revisado y rechazo.
            options = [
                { value: 'under_review', text: 'Bajo Revisión', disabled: false },
                { value: 'reviewed', text: 'Revisado', disabled: false },
                { value: 'rejected', text: 'Rechazado', disabled: false }
            ];
            // Quitar 'approved' y bloquear 'under_review' si ya está 'reviewed'
            options = options.filter(opt => opt.value !== 'approved');
            if (isItemReviewed) {
                options.find(o => o.value === 'under_review').disabled = true;
            }
        }
    } else { // No tiene permisos
        const statusClassMap = { 'approved': 'success', 'reviewed': 'primary', 'rejected': 'danger', 'under_review': 'info' };
        const statusText = baseOptions.find(o => o.value === currentStatus)?.text || 'Desconocido';
        const statusClass = statusClassMap[currentStatus] || 'info';
        return `<span class="badge badge-${statusClass}">${statusText}</span>`;
    }

    let html = `<select class="form-control item-approval-selector">`;

    // Filtramos las opciones finales basándonos en si están deshabilitadas o si son el estado actual.
    // Usamos las opciones base para asegurar el orden.
    const finalOptions = baseOptions.map(baseOpt => {
        const currentOpt = options.find(o => o.value === baseOpt.value) || baseOpt;

        let isDisabled = currentOpt.disabled;
        let isSelected = baseOpt.value === currentStatus;

        // Si el ítem ya está "Aprobado" o "Rechazado", las otras opciones se fuerzan a deshabilitadas para no-aprobadores
        if (!isApprover && (isItemApproved || isItemRejected) && baseOpt.value !== currentStatus) {
            isDisabled = true;
        }

        // Bloquear 'under_review' si ya tiene un estado de revisión o superior para forzar la toma de decisión
        if ((isItemReviewed || isItemApproved) && baseOpt.value === 'under_review') {
            isDisabled = true;
        }

        return {
            value: baseOpt.value,
            text: baseOpt.text,
            disabled: isDisabled,
            selected: isSelected
        };
    }).filter(opt => isApprover || isReviewer ? opt.value !== 'approved' || isApprover : true); // Ocultar Aprobado si es solo Revisor

    // Si el rol es Aprobador, mantenemos todas las 4 opciones pero aplicamos el disabled
    if (isApprover) {
        finalOptions.forEach(option => {
            let isDisabled = option.disabled;

            // Regla de NO downgrade de APROBADO
            if (isItemApproved && (option.value === 'reviewed' || option.value === 'under_review')) {
                isDisabled = true;
            }

            html += `<option value="${option.value}" ${option.selected ? 'selected' : ''} ${isDisabled ? 'disabled' : ''}>${option.text}</option>`;
        });
    } else if (isReviewer) {
        // Para el revisor, solo mostramos las opciones que puede usar: Bajo Revisión, Revisado, Rechazado
        baseOptions.filter(opt => opt.value !== 'approved').forEach(option => {
            const currentOpt = finalOptions.find(f => f.value === option.value);
            if (currentOpt) {
                let isDisabled = currentOpt.disabled;

                // Si está Revisado, no permitimos volver a Bajo Revisión
                if (isItemReviewed && option.value === 'under_review') {
                    isDisabled = true;
                }

                // Si está Rechazado, no permitimos cambiarlo (espera corrección)
                if (isItemRejected && option.value !== 'rejected') {
                    isDisabled = true;
                }

                html += `<option value="${option.value}" ${currentOpt.selected ? 'selected' : ''} ${isDisabled ? 'disabled' : ''}>${option.text}</option>`;
            }
        });
    }


    html += `</select>`;

    return html;
}

/**
 * Actualiza el estado de los ítems individuales a través del modal.
 */
async function updateDailyItemsStatus(employeeId, changes) {
    try {
        // Mostrar estado de carga principal
        showLoadingState();

        // Determinar si hay algún rechazo en los cambios para el mensaje de carga
        const hasRejections = changes.some(change => change.status === 'rejected');
        const savingMessage = hasRejections
            ? 'Guardando cambios y notificando al empleado...'
            : 'Guardando cambios...';

        showModalSavingState(savingMessage);

        const response = await fetch('/recursoshumanos/loadchart/update-multiple-statuses', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                employee_id: employeeId,
                changes: changes,
                month: currentMonth,
                year: currentYear
            })
        });

        const data = await response.json();

        // Ocultar estados de carga (principal y modal)
        hideLoadingState();
        hideModalSavingState();

        if (data.success) {
            let message = data.message;

            // Mensaje de éxito condicional
            if (data.rejections_sent) {
                message = "Estados actualizados correctamente. Se ha notificado el rechazo al empleado vía correo.";
            } else {
                message = "Estados actualizados correctamente.";
            }

            showSwalNotification('Éxito', message, 'success');
            document.getElementById('approvalModal').style.display = 'none'; // Cerrar modal al éxito
            await loadMonthData(true); // Recargar datos para actualizar la tabla
        } else {
            showSwalNotification('Error', data.message, 'error');
            // No cerrar el modal
        }
    } catch (error) {
        console.error('Error al actualizar el estado de los ítems:', error);
        hideLoadingState();
        hideModalSavingState();
        showSwalNotification('Error Crítico', 'Error al guardar los cambios: ' + error.message, 'error');
        // No cerrar el modal
    }
}

/**
 * Muestra el estado de guardado/notificación en el modal.
 */
function showModalSavingState(message) {
    const modalContent = document.querySelector('#approvalModal .modal-approval-content');
    if (modalContent) {
        modalContent.style.position = 'relative';

        // Crear overlay/spinner
        let overlay = document.getElementById('modal-save-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'modal-save-overlay';
            overlay.className = 'modal-save-overlay';
            overlay.innerHTML = `
                <div class="loading-spinner-lg modal-spinner"></div>
                <div class="loading-message modal-saving-message">${message}</div>
            `;
            modalContent.appendChild(overlay);
        } else {
            overlay.querySelector('.modal-saving-message').textContent = message;
        }
        overlay.style.display = 'flex';

        // Deshabilitar botones de guardado
        document.getElementById('modal-save-btn').disabled = true;
        document.querySelectorAll('.modal-approval-close, .modal-approval-close-btn').forEach(btn => btn.disabled = true);
    }
}

/**
 * Oculta el estado de guardado/notificación en el modal.
 */
function hideModalSavingState() {
    const overlay = document.getElementById('modal-save-overlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
    // Re-habilitar botones
    document.getElementById('modal-save-btn').disabled = false;
    document.querySelectorAll('.modal-approval-close, .modal-approval-close-btn').forEach(btn => btn.disabled = false);
}

function setupEmployeeRowListeners() {
    // Los event listeners están delegados en setupEventListeners.
}

async function handleApprove() {
    const row = this.closest(".employee-row");
    const employeeId = row.getAttribute('data-employee-id');
    const fortnight = currentView;

    const employeeAssignment = loadChartAssignments.find(a => a.employee_id.toString() === employeeId);
    const isApproverForEmployee = employeeAssignment && employeeAssignment.approver_id === currentUserId;

    if (!isApproverForEmployee) {
        showNotification('No tiene permisos para aprobar este registro.', 'error');
        return;
    }

    const employeeLog = workLogsData.find(log => log.employee_id === parseInt(employeeId));

    if (!employeeLog || !employeeLog.daily_activities || employeeLog.daily_activities.length === 0) {
        showNotification('No hay actividades registradas para aprobar.', 'info');
        return;
    }

    let periodActivities = [];
    if (fortnight === 'quincena1' && fortnightlyConfig.q1_start && fortnightlyConfig.q1_end) {
        periodActivities = employeeLog.daily_activities.filter(act => {
            const actDate = new Date(act.date);
            const q1Start = new Date(fortnightlyConfig.q1_start);
            const q1End = new Date(fortnightlyConfig.q1_end);
            return actDate >= q1Start && actDate <= q1End;
        });
    } else if (fortnight === 'quincena2' && fortnightlyConfig.q2_start && fortnightlyConfig.q2_end) {
        periodActivities = employeeLog.daily_activities.filter(act => {
            const actDate = new Date(act.date);
            const q2Start = new Date(fortnightlyConfig.q2_start);
            const q2End = new Date(fortnightlyConfig.q2_end);
            return actDate >= q2Start && actDate <= q2End;
        });
    } else if (fortnight === 'full-month') {
        periodActivities = employeeLog.daily_activities;
    }

    const activitiesToApprove = periodActivities.filter(act => act.day_status !== 'approved' && act.day_status !== 'rejected');
    const rejectedActivitiesExist = periodActivities.some(act => act.day_status === 'rejected');
    const underReviewActivitiesExist = periodActivities.some(act => act.day_status === 'under_review');
    const reviewedActivitiesExist = periodActivities.some(act => act.day_status === 'reviewed');

    if (activitiesToApprove.length > 0) {
        let message = `Se aprobarán todas las actividades (${activitiesToApprove.length} días) en estado `;
        const statesToApprove = [];
        if (underReviewActivitiesExist) statesToApprove.push('Bajo Revisión');
        if (reviewedActivitiesExist) statesToApprove.push('Revisado');
        message += `${statesToApprove.join(' y ')} para este período. Esta acción es irreversible.`;

        Swal.fire({
            title: '¿Está seguro de aprobar?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, aprobar',
            cancelButtonText: 'Cancelar'
        }).then(async (result) => {
            if (result.isConfirmed) {
                await saveApprovalStatus(employeeId, 'approved', fortnight);
            }
        });
    } else if (rejectedActivitiesExist) {
        Swal.fire({
            title: 'No se puede aprobar',
            text: 'Existen actividades en estado "Rechazado" en este período. El empleado debe corregirlas antes de poder aprobar.',
            icon: 'error',
            confirmButtonText: 'Entendido'
        });
    } else {
        showNotification('No hay actividades bajo revisión o revisadas para aprobar en este período.', 'info');
    }
}

async function handleReview() {
    const row = this.closest(".employee-row");
    const employeeId = row.getAttribute('data-employee-id');
    const fortnight = currentView;

    const employeeAssignment = loadChartAssignments.find(a => a.employee_id.toString() === employeeId);
    const isReviewerForEmployee = employeeAssignment && employeeAssignment.reviewer_id === currentUserId;

    if (!isReviewerForEmployee) {
        showNotification('No tiene permisos para revisar este registro.', 'error');
        return;
    }

    const employeeLog = workLogsData.find(log => log.employee_id === parseInt(employeeId));

    if (!employeeLog || !employeeLog.daily_activities || employeeLog.daily_activities.length === 0) {
        showNotification('No hay actividades registradas para revisar.', 'info');
        return;
    }

    let periodActivities = [];
    if (fortnight === 'quincena1' && fortnightlyConfig.q1_start && fortnightlyConfig.q1_end) {
        periodActivities = employeeLog.daily_activities.filter(act => {
            const actDate = new Date(act.date);
            const q1Start = new Date(fortnightlyConfig.q1_start);
            const q1End = new Date(fortnightlyConfig.q1_end);
            return actDate >= q1Start && actDate <= q1End;
        });
    } else if (fortnight === 'quincena2' && fortnightlyConfig.q2_start && fortnightlyConfig.q2_end) {
        periodActivities = employeeLog.daily_activities.filter(act => {
            const actDate = new Date(act.date);
            const q2Start = new Date(fortnightlyConfig.q2_start);
            const q2End = new Date(fortnightlyConfig.q2_end);
            return actDate >= q2Start && actDate <= q2End;
        });
    } else if (fortnight === 'full-month') {
        periodActivities = employeeLog.daily_activities;
    }

    const activitiesToReview = periodActivities.filter(act => act.day_status === 'under_review');
    const rejectedActivitiesExist = periodActivities.some(act => act.day_status === 'rejected');

    if (activitiesToReview.length > 0) {
        Swal.fire({
            title: '¿Está seguro de revisar?',
            text: `Se revisarán todas las actividades (${activitiesToReview.length} días) en estado Bajo Revisión para este período.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, revisar',
            cancelButtonText: 'Cancelar'
        }).then(async (result) => {
            if (result.isConfirmed) {
                await saveApprovalStatus(employeeId, 'reviewed', fortnight);
            }
        });
    } else if (rejectedActivitiesExist) {
        Swal.fire({
            title: 'No se puede revisar',
            text: 'Existen actividades en estado "Rechazado" en este período. El empleado debe corregirlas antes de que puedan ser revisadas.',
            icon: 'error',
            confirmButtonText: 'Entendido'
        });
    } else {
        showNotification('No hay actividades bajo revisión para revisar en este período.', 'info');
    }
}


/**
 * Filtra las actividades diarias por el período seleccionado (quincena o mes completo).
 */
function filterActivitiesByPeriod(dailyActivities, fortnight) {
    if (fortnight === 'full-month') {
        return dailyActivities;
    }

    const q1Start = fortnightlyConfig.q1_start ? new Date(fortnightlyConfig.q1_start + 'T00:00:00') : null;
    const q1End = fortnightlyConfig.q1_end ? new Date(fortnightlyConfig.q1_end + 'T00:00:00') : null;
    const q2Start = fortnightlyConfig.q2_start ? new Date(fortnightlyConfig.q2_start + 'T00:00:00') : null;
    const q2End = fortnightlyConfig.q2_end ? new Date(fortnightlyConfig.q2_end + 'T00:00:00') : null;

    return dailyActivities.filter(act => {
        const actDate = new Date(act.date + 'T00:00:00');
        if (fortnight === 'quincena1' && q1Start && q1End) {
            // Se ajusta para incluir el final del día
            const q1EndPlusDay = new Date(q1End);
            q1EndPlusDay.setDate(q1EndPlusDay.getDate() + 1);
            return actDate >= q1Start && actDate < q1EndPlusDay;
        } else if (fortnight === 'quincena2' && q2Start && q2End) {
            // Se ajusta para incluir el final del día
            const q2EndPlusDay = new Date(q2End);
            q2EndPlusDay.setDate(q2EndPlusDay.getDate() + 1);
            return actDate >= q2Start && actDate < q2EndPlusDay;
        }
        return false;
    });
}


/**
 * Envía la solicitud masiva de revisión/aprobación al servidor.
 */
async function saveApprovalStatus(employeeId, status, fortnight) {
    try {
        showLoadingState();
        const response = await fetch('/recursoshumanos/loadchart/update-approval-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                employee_id: employeeId,
                month: currentMonth,
                year: currentYear,
                status: status,
                fortnight: fortnight
            })
        });

        const data = await response.json();
        hideLoadingState();

        if (data.success) {
            const statusText = status.charAt(0).toUpperCase() + status.slice(1);
            const fortnightText = fortnight === 'full-month' ? 'mes completo' : fortnight === 'quincena1' ? 'primera quincena' : 'segunda quincena';
            showSwalNotification('Éxito', `Estado del ${fortnightText} actualizado a '${statusText}' correctamente.`, 'success');
            await loadMonthData(true); // Recargar datos para actualizar la tabla
        } else {
            showSwalNotification('Error', `Error al actualizar: ${data.message}`, 'error');
        }
    } catch (error) {
        console.error('Error saving approval status:', error);
        hideLoadingState();
        showSwalNotification('Error Crítico', 'Error al guardar el estado de aprobación', 'error');
    }
}

/**
 * Muestra notificaciones usando SweetAlert2 para mensajes importantes.
 */
function showSwalNotification(title, message, icon) {
    Swal.fire({
        title: title,
        text: message,
        icon: icon,
        confirmButtonText: 'Aceptar'
    });
}

function showNotification(message, type = 'info') {
    // Implementación de notificación de barra inferior/superior
    const notification = document.createElement("div");
    notification.className = `notification ${type}`;

    const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
    notification.innerHTML = `<i class="fas ${icon}"></i> ${message}`;

    document.body.appendChild(notification);

    setTimeout(() => notification.classList.add('show'), 100);

    setTimeout(() => {
        notification.classList.add("fade-out");
        setTimeout(() => notification.remove(), 500);
    }, 4000);
}

document.addEventListener('click', function (e) {
    // CRITICAL: El click para expandir/colapsar debe ser en la celda de nombre
    if (e.target.closest('.employee-info-cell')) {
        const employeeRow = e.target.closest('.employee-row');
        if (employeeRow) {
            // El click en el nombre ahora abre el modal de calendario, así que se usa una clase
            // para alternar la visibilidad de las filas de detalle.
            // toggleActivityRows(employeeRow);
        }
    }
});

function toggleActivityRows(employeeRow) {
    const allRows = Array.from(employeeRow.parentElement.children);
    const startIndex = allRows.indexOf(employeeRow);
    let activityRowCount = 0;

    // Recorre las siguientes filas para encontrar las filas de actividad asociadas a este empleado.
    for (let i = startIndex + 1; i < allRows.length; i++) {
        // La condición de parada es encontrar la siguiente fila principal (employee-row) o una fila que no sea activity-row.
        if (allRows[i].classList.contains('activity-row') && allRows[i].getAttribute('data-employee-id') === employeeRow.getAttribute('data-employee-id')) {
            // Solo alternar si la fila NO es hidden por la lógica de visibilidad de items
            if (allRows[i].style.display !== 'none') {
                allRows[i].classList.toggle('hidden');
                activityRowCount++;
            }
        } else if (allRows[i].classList.contains('employee-row') || allRows[i].classList.contains('squad-group-row')) { // CRÍTICO: Detener también en la fila de cuadrilla
            // Detener si es la siguiente fila de empleado o de cuadrilla
            break;
        }
    }
}

async function navigateToPreviousMonth() {
    if (currentMonth === 1) {
        currentMonth = 12;
        currentYear--;
    } else {
        currentMonth--;
    }
    await loadMonthData();
}

async function navigateToNextMonth() {
    if (currentMonth === 12) {
        currentMonth = 1;
        currentYear++;
    } else {
        currentMonth++;
    }
    await loadMonthData();
}

/**
 * Carga los datos de un mes específico desde el servidor.
 */
async function loadMonthData(isRefresh = false) {
    try {
        if (!isRefresh) {
            showLoadingState();
        }
        stopAutoRefresh();

        const response = await fetch(`/recursoshumanos/loadchart/approval-data/${currentYear}/${currentMonth}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (!response.ok) {
            throw new Error('Error al cargar los datos del mes');
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Error al cargar los datos');
        }

        // 👈🏼 CRITICAL: Actualizar variables globales con los datos del backend
        workLogsData = data.workLogsData;
        fortnightlyConfig = data.fortnightlyConfig;
        loadChartAssignments = data.loadChartAssignments;
        canSeeAmounts = data.canSeeAmounts; // 👈🏼 CRÍTICO: canSeeAmounts se actualiza aquí
        userPermissions = data.userPermissions;
        monthlyDays = data.monthlyDays; // 👈🏼 CRÍTICO: Guardar los días para la renderización
        employees = data.employees; // 👈🏼 CRÍTICO: Guardar los empleados para la renderización

        // 1. Si es la primera carga o si la estructura de días cambió, se actualiza toda la tabla
        updateTableStructure(data.monthlyDays, data.employees);


        updatePeriodInfo();

        // 2. Se vuelve a mostrar la vista actual para aplicar los filtros de visibilidad correctos.
        if (currentView === 'quincena1') {
            showQuincena(1);
        } else if (currentView === 'quincena2') {
            showQuincena(2);
        } else {
            showFullMonth();
        }

        // 🆕 Aplicar filtros inmediatamente después de la recarga
        // Esto es CRÍTICO para los usuarios con permiso de filtro
        allEmployeeRows = Array.from(document.querySelectorAll('.employee-row'));
        allSquadRows = Array.from(document.querySelectorAll('.squad-group-row'));

        if (canSeeFilters) {
            applyFilters();
        }

        // 3. Setup listeners *después* de que la vista se ha actualizado
        setupEmployeeRowListeners();

        // Si es una navegación, ocultar el estado de carga
        if (!isRefresh) {
            hideLoadingState();
        }

        // Actualizar el tiempo de la última actualización y reiniciar el refresco
        lastUpdateTime = new Date();
        initializeAutoRefresh();

    } catch (error) {
        console.error('Error loading month data:', error);
        showSwalNotification('Error', 'Error al cargar los datos del mes: ' + error.message, 'error');
        hideLoadingState();
        initializeAutoRefresh();
    }
}

function showLoadingState() {
    const table = document.getElementById('approval-table');
    if (table) {
        table.style.opacity = '0.5';
        table.style.pointerEvents = 'none';
    }

    const prevBtn = document.getElementById('prev-period');
    const nextBtn = document.getElementById('next-period');
    if (prevBtn) prevBtn.disabled = true;
    if (nextBtn) nextBtn.disabled = true;
}

function hideLoadingState() {
    const table = document.getElementById('approval-table');
    if (table) {
        table.style.opacity = '1';
        table.style.pointerEvents = 'auto';
    }

    const prevBtn = document.getElementById('prev-period');
    const nextBtn = document.getElementById('next-period');
    if (prevBtn) prevBtn.disabled = false;
    if (nextBtn) nextBtn.disabled = false;
}

function updatePeriodInfo() {
    const monthNames = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];

    const periodInfo = document.getElementById('current-period');
    if (periodInfo) {
        periodInfo.textContent = `${monthNames[currentMonth - 1]} ${currentYear}`;
    }
}

/**
 * CRÍTICO: Esta función debe reconstruir la tabla (tbody) o llamar a una función
 * que haga una renderización completa desde el JS, ya que la estructura cambia
 * con los días y la agrupación de cuadrillas. Dado que no tenemos el Blade
 * aquí, simularemos una carga de datos completa reconstruyendo el Tbody
 * en JS, o *idealmente*, haciendo una llamada AJAX que devuelva el HTML renderizado.
 *
 * Dado que el Blade es complejo, la solución más simple y robusta es:
 * 1. Actualizar el encabezado (días).
 * 2. Re-generar el body usando una función que simule la lógica de Blade.
 * (Optamos por la opción 3: Renderizar solo los datos si la estructura es similar, o forzar la recarga
 * para meses diferentes si la complejidad de Blade lo impide.)
 *
 * *Mejor solución por la complejidad de Blade:* Forzar la recarga de toda la vista.
 * Pero para una solución AJAX, re-implementaremos el renderizado del cuerpo aquí.
 */
function updateTableStructure(monthlyDays, employees) {
    updateDaysHeader(monthlyDays);
    updateTableBody(employees, monthlyDays); // 👈🏼 CRÍTICO: Reconstruir el body de la tabla
    renderEmployeeWorkLog(); // Llenar los datos

}

function updateDaysHeader(monthlyDays) {
    const daysColumnsHeader = document.getElementById('days-columns');
    const daysHeaderRow = document.getElementById('days-header-row');

    if (daysColumnsHeader) {
        daysColumnsHeader.colSpan = monthlyDays.length;
        daysColumnsHeader.textContent = `Días del Período (${monthlyDays.length} días)`;
    }


    if (daysHeaderRow) {
        daysHeaderRow.innerHTML = '';

        monthlyDays.forEach(dayInfo => {
            // 🚨 CORRECCIÓN: Si es el último día del mes (ej. día 31) y solo aparece en la Quincena 2,
            // la lógica de quincena es correcta en el backend.
            const th = document.createElement('th');
            th.className = `day-header ${dayInfo.is_quincena_1 ? 'quincena-1' : ''} ${dayInfo.is_quincena_2 ? 'quincena-2' : ''} ${!dayInfo.is_working_day ? 'non-working' : ''} ${!dayInfo.is_current_month ? 'other-month' : ''}`;
            th.setAttribute('data-day', dayInfo.day);
            th.setAttribute('data-date', dayInfo.date);
            th.setAttribute('data-quincena-1', dayInfo.is_quincena_1 ? 'true' : 'false');
            th.setAttribute('data-quincena-2', dayInfo.is_quincena_2 ? 'true' : 'false');
            th.setAttribute('data-current-month', dayInfo.is_current_month ? 'true' : 'false');
            th.setAttribute('data-month', dayInfo.month);
            th.innerHTML = `${dayInfo.day}<br>${dayInfo.day_name}`;
            th.title = `${dayInfo.date}${!dayInfo.is_current_month ? ' (Mes anterior)' : ''}`;
            daysHeaderRow.appendChild(th);
        });
    }

}


/**
 * CRÍTICO: Reconstruye el cuerpo de la tabla (tbody) para soportar la carga AJAX
 * de meses con diferente número de días y empleados.
 */
function updateTableBody(employeesData, monthlyDaysData) {
    const tbody = document.getElementById('approval-table-body');
    if (!tbody) return;

    tbody.innerHTML = ''; // Limpiar el cuerpo existente

    // 1. Agrupar empleados por número de cuadrilla (squad_number)
    const groupedEmployees = {};

    employeesData.forEach(employee => {
        // Accedemos a la propiedad `squads` que debe ser devuelta por la API
        const squadNumber = employee.squads && employee.squads.length > 0
            ? employee.squads[0].squad_number
            : 999; // Usar 999 para "Sin Cuadrilla Asignada"

        if (!groupedEmployees[squadNumber]) {
            groupedEmployees[squadNumber] = [];
        }
        groupedEmployees[squadNumber].push(employee);
    });

    // 2. Ordenar las cuadrillas: 1, 2, ..., 999
    const sortedSquadNumbers = Object.keys(groupedEmployees).sort((a, b) => parseInt(a) - parseInt(b));

    // Verificar si el usuario tiene el permiso de ver cuadrillas (simulación del Blade @if)
    const showSquadGrouping = document.getElementById('squad-control') !== null;

    // Calcular el colspan total (Nombre + KPI + Días + Total + Vac + Desc + Utiliz + Aprob)
    // 1 (Nombre) + 1 (KPI) + Días + 1 (Total) + 1 (Vac) + 1 (Desc) + 1 (Utiliz) + 1 (Aprob) = 6 + monthlyDaysData.length
    const totalColumnsForSquadRow = 6 + monthlyDaysData.length;

    // 3. Renderizar filas
    sortedSquadNumbers.forEach(squadNumber => {
        const squadEmployees = groupedEmployees[squadNumber];

        // 3.1. Fila de Encabezado de Cuadrilla (si el permiso está activo)
        if (showSquadGrouping) {
            const squadLabel = squadNumber !== '999'
                ? `Cuadrilla-${squadNumber.padStart(2, '0')}`
                : 'Sin Cuadrilla Asignada';
            const squadClass = squadNumber !== '999' ? 'squad-group-row-active' : 'squad-group-row-none';

            let squadRow = document.createElement('tr');
            squadRow.className = `squad-group-row ${squadClass}`;
            squadRow.setAttribute('data-squad-number', squadNumber);
            // El colspan aquí debe ser el total de columnas, que es 8 + length(dias)
            squadRow.innerHTML = `
                <td colspan="${totalColumnsForSquadRow}" class="squad-group-label">
                    ${squadLabel}
                </td>
            `;
            tbody.appendChild(squadRow);
        }

        squadEmployees.forEach(employee => {
            const assignment = loadChartAssignments.find(a => a.employee_id === employee.id);
            const isReviewerForEmployee = assignment && assignment.reviewer_id === currentUserId;
            const isApproverForEmployee = assignment && assignment.approver_id === currentUserId;

            // 3.2. Fila Principal (Actividad)
            let mainRow = document.createElement('tr');
            mainRow.className = 'employee-row';
            mainRow.setAttribute('data-employee-id', employee.id);
            mainRow.setAttribute('data-department', employee.department); // Añadir departamento para el filtro

            // Inicializamos el HTML de la fila principal. El rowspan es 4 por defecto,
            // pero se ajustará luego en renderEmployeeWorkLog si hay filas ocultas.
            mainRow.innerHTML = `
                <td rowspan="4" class="employee-info-cell" data-icon="bx bx-calendar" data-text="ver calendario">
                    ${employee.full_name}
                </td>
                <td class="activity-label-cell activity-main-label">Actividad</td>
                ${monthlyDaysData.map(dayInfo => `
                    <td class="data-cell ${dayInfo.is_quincena_1 ? 'quincena-1' : ''} ${dayInfo.is_quincena_2 ? 'quincena-2' : ''} ${!dayInfo.is_working_day ? 'non-working' : ''} ${!dayInfo.is_current_month ? 'other-month' : ''}"
                        data-day="${dayInfo.day}" data-date="${dayInfo.date}">
                        <div class="status-indicator status-n">N</div>
                    </td>
                `).join('')}
                <td class="data-cell total-activity">
                    <div class="status-indicator">0</div>
                </td>
                <td rowspan="4" class="vacations-cell">
                    <div class="vacations-container">
                        <div class="vacations-value">0</div>
                    </div>
                </td>
                <td rowspan="4" class="breaks-cell">
                    <div class="breaks-container">
                        <div class="breaks-value">0</div>
                    </div>
                </td>
                <td rowspan="4" class="utilization-cell">
                    <div class="utilization-container">
                        <div class="utilization-value">0%</div>
                    </div>
                </td>
                <td rowspan="4" class="actions-cell">
                    <div class="actions-container">
                        ${isReviewerForEmployee ? '<button class="btn-review">Revisar</button>' : ''}
                        ${isApproverForEmployee ? '<button class="btn-approve">Aprobar</button>' : ''}
                        ${!isReviewerForEmployee && !isApproverForEmployee ? '<span class="no-permissions">Sin permisos</span>' : ''}
                    </div>
                </td>
            `;
            tbody.appendChild(mainRow); // Agregamos la fila principal

            // 3.3. Filas de Detalle (Comida, Bono, Servicio)
            const rowTypes = ['Comida', 'Bono', 'Servicio'];
            rowTypes.forEach(rowType => {
                let detailRow = document.createElement('tr');
                // Se eliminó la clase 'hidden' de forma predeterminada, ya que la visibilidad de ítems la maneja
                // el renderizador en base a si el empleado tiene logs para ese ítem en el periodo visible.
                detailRow.className = 'activity-row';
                detailRow.setAttribute('data-employee-id', employee.id);
                detailRow.setAttribute('data-department', employee.department);

                // Mantenemos las celdas de días y la celda de total. Las celdas de rowspan están en la fila principal.
                detailRow.innerHTML = `
                    <td class="activity-label-cell">${rowType}</td>
                    ${monthlyDaysData.map(dayInfo => `
                        <td class="data-cell ${dayInfo.is_quincena_1 ? 'quincena-1' : ''} ${dayInfo.is_quincena_2 ? 'quincena-2' : ''} ${!dayInfo.is_working_day ? 'non-working' : ''} ${!dayInfo.is_current_month ? 'other-month' : ''}"
                            data-day="${dayInfo.day}" data-date="${dayInfo.date}">0</td>
                    `).join('')}
                    <td class="data-cell total-${rowType.toLowerCase()}">0</td>
                `;
                tbody.appendChild(detailRow); // **CRÍTICO:** Agregar la fila de detalle INMEDIATAMENTE después de la principal.
            });
        });
    });
}
// ... (código que sigue)

function setActiveButton(buttonId) {
    document.querySelectorAll(".period-btn").forEach((btn) => {
        btn.classList.remove("active");
    });

    const targetBtn = document.getElementById(buttonId);
    if (targetBtn) {
        targetBtn.classList.add("active");
    }

    currentView = buttonId;
}

/**
 * Muestra solo los días de una quincena y ajusta el colspan.
 */
function showQuincena(quincena) {
    const dayHeaders = document.querySelectorAll(".approval-table .day-header");
    const allRows = document.querySelectorAll(".approval-table tbody tr");
    const daysColumnsHeader = document.getElementById("days-columns");

    if (!dayHeaders.length || !daysColumnsHeader) return;

    let visibleDays = 0;
    const targetAttr = quincena === 1 ? 'data-quincena-1' : 'data-quincena-2';

    // 1. Ocultar/Mostrar encabezados de día y celdas de datos
    dayHeaders.forEach((header, index) => {
        const shouldShow = header.getAttribute(targetAttr) === 'true';

        // 1.1 Encabezados
        header.style.display = shouldShow ? "" : "none";
        if (shouldShow) visibleDays++;

        // 1.2 Celdas de datos
        allRows.forEach((row) => {
            const dataCells = row.querySelectorAll(".data-cell");
            // Asegurarse de que estamos manipulando solo las celdas de días
            // La posición de los días es a partir del índice 2 (después de Nombre y KPI)
            const dayIndexInRow = index + 2;

            if (row.classList.contains('activity-row') || row.classList.contains('employee-row')) {
                // Hay que ignorar las celdas de resumen al final
                const dayCell = Array.from(row.children).find(cell => cell.getAttribute('data-day') === header.getAttribute('data-day') && cell.getAttribute('data-date') === header.getAttribute('data-date'));

                if (dayCell) {
                    // Solo cambiar la visibilidad de la celda si la fila principal del empleado está visible por el filtro
                    if (row.style.display !== 'none') {
                        dayCell.style.display = shouldShow ? "" : "none";
                    }
                }
            }
        });
    });

    // 2. Ajustar el colspan del encabezado superior
    daysColumnsHeader.colSpan = visibleDays;
    daysColumnsHeader.textContent = `Días del Período (${visibleDays} días)`;

    // 3. Recalcular y Renderizar Totales
    calculateAndRenderTotals();
}

/**
 * Muestra todos los días del mes y ajusta el colspan.
 */
function showFullMonth() {
    const dayHeaders = document.querySelectorAll(".approval-table .day-header");
    const allRows = document.querySelectorAll(".approval-table tbody tr");
    const daysColumnsHeader = document.getElementById("days-columns");

    if (!dayHeaders.length || !daysColumnsHeader) return;

    // 1. Mostrar encabezados de día y celdas de datos
    dayHeaders.forEach((header) => {
        header.style.display = "";

        allRows.forEach((row) => {
            // Asegurarse de que estamos manipulando solo las celdas de días
            if (row.classList.contains('activity-row') || row.classList.contains('employee-row')) {
                const dayCell = Array.from(row.children).find(cell => cell.getAttribute('data-day') === header.getAttribute('data-day') && cell.getAttribute('data-date') === header.getAttribute('data-date'));

                if (dayCell) {
                    // Solo cambiar la visibilidad de la celda si la fila principal del empleado está visible por el filtro
                    if (row.style.display !== 'none') {
                        dayCell.style.display = "";
                    }
                }
            }
        });
    });

    // 2. Ajustar el colspan del encabezado superior
    daysColumnsHeader.colSpan = dayHeaders.length;
    daysColumnsHeader.textContent = `Días del Período (${dayHeaders.length} días)`;

    // 3. Recalcular y Renderizar Totales
    calculateAndRenderTotals();
}

// ... (código anterior)

/**
 * Calcula y renderiza los totales de días y la utilización.
 */
function calculateAndRenderTotals() {
    // Solo calcular y renderizar para las filas VISIBLES
    const employeeRows = Array.from(document.querySelectorAll('.employee-row')).filter(row => row.style.display !== 'none');
    const workingActivityTypes = ['B', 'P', 'TC', 'V', 'E', 'C'];

    // 1. OBTENER TODOS LOS ENCABEZADOS DE DÍA Y FILTRAR SOLO LOS VISIBLES
    const allDayHeaders = Array.from(document.querySelectorAll('.day-header'));
    const visibleDayHeaders = allDayHeaders.filter(header => header.style.display !== 'none');

    // Lista de fechas visibles para el período actual (quincena1, quincena2, full-month)
    const visibleDates = visibleDayHeaders.map(header => header.getAttribute('data-date'));

    // 2. CALCULAR EL TOTAL DE DÍAS LABORABLES EN EL MES COMPLETO
    const totalWorkingDaysInFullMonth = allDayHeaders.filter(header =>
        header.getAttribute('data-quincena-1') === 'true' ||
        header.getAttribute('data-quincena-2') === 'true'
    ).length;

    employeeRows.forEach(employeeRow => {
        const employeeId = employeeRow.getAttribute('data-employee-id');
        const employeeData = workLogsData.find(log => log.employee_id.toString() === employeeId);

        if (!employeeData || !employeeData.daily_activities) {
            // Limpiar celdas si no hay datos
            // ... (Lógica de limpieza omitida para concisión)
            return;
        }

        let totalDays = 0; // Días de actividad contables (Base, Pozo, etc.) EN EL PERÍODO VISIBLE
        let totalFood = 0; // Monto total o conteo total (depende de canSeeAmounts)
        let totalFieldBonus = 0; // Monto total o conteo total
        let totalService = 0; // Monto total o conteo total
        let totalBreaks = 0; // Descansos (D)
        let totalVacations = 0; // Días VAC

        let foodCount = 0; // Conteo real de ítems
        let fieldBonusCount = 0;
        let serviceCount = 0;

        const dailyActivitiesMap = new Map(employeeData.daily_activities.map(activity => [activity.date, activity]));

        // 🚨 COMIENZO DE LA CORRECCIÓN CRÍTICA: Iterar sobre las fechas visibles
        visibleDates.forEach(dateAttr => {
            const dailyActivity = dailyActivitiesMap.get(dateAttr);

            if (dailyActivity) {
                const activityType = dailyActivity.activity_type ? dailyActivity.activity_type.toUpperCase() : 'N';

                if (workingActivityTypes.includes(activityType)) {
                    totalDays++;
                }

                if (activityType === 'VAC') {
                    totalVacations++;
                }

                if (activityType === 'D') {
                    totalBreaks++;
                }

                // Sumar bonos/servicios (Corregido para manejar montos y conteos)
                const dailyFoodBonuses = dailyActivity.food_bonuses || [];
                const dailyFieldBonuses = dailyActivity.field_bonuses || [];
                const dailyServices = dailyActivity.services_list || [];

                foodCount += dailyFoodBonuses.length;
                fieldBonusCount += dailyFieldBonuses.length;
                serviceCount += dailyServices.length;

                if (canSeeAmounts) {
                    const dailyFoodBonus = dailyFoodBonuses.reduce((sum, bonus) => sum + Number(bonus.daily_amount || 0), 0);
                    totalFood += dailyFoodBonus;

                    const dailyFieldBonus = dailyFieldBonuses.reduce((sum, bonus) => {
                        const amount = Number(bonus.daily_amount || 0);
                        if (bonus.currency === 'USD' && bonus.usd_to_mxn_rate) {
                            // Asume que la conversión de USD a MXN ya se realizó o que el monto es el que se quiere sumar.
                            return sum + amount;
                        }
                        return sum + amount;
                    }, 0);
                    totalFieldBonus += dailyFieldBonus;

                    const dailyServiceAmount = dailyServices.reduce((sum, service) => sum + Number(service.amount || 0), 0);
                    totalService += dailyServiceAmount;
                } else {
                    // Si no se ven montos, los "totales" deben ser el CONTEO de ítems
                    // Esta lógica en calculateAndRenderTotals es solo para el renderizado final del total.
                    // Los contadores de ítems ya están sumados arriba.
                }
            }
        });
        // 🚨 FIN DE LA CORRECCIÓN CRÍTICA


        // Obtener las filas de detalle (Comida, Bono, Servicio)
        let currentRow = employeeRow.nextElementSibling;
        let foodRow = null;
        let fieldBonusRow = null;
        let serviceRow = null;

        while (currentRow && currentRow.getAttribute('data-employee-id') === employeeId.toString() && currentRow.classList.contains('activity-row')) {
            const label = currentRow.querySelector('.activity-label-cell').textContent;
            if (label === 'Comida') foodRow = currentRow;
            else if (label === 'Bono') fieldBonusRow = currentRow;
            else if (label === 'Servicio') serviceRow = currentRow;
            currentRow = currentRow.nextElementSibling;
        }

        const activityTotalCell = employeeRow.querySelector('.total-activity .status-indicator'); // Apuntar al indicador dentro de la celda.
        const foodTotalCell = foodRow ? foodRow.querySelector('.total-comida') : null; // Corregir clase a total-comida
        const fieldBonusTotalCell = fieldBonusRow ? fieldBonusRow.querySelector('.total-bono') : null; // Corregir clase a total-bono
        const serviceTotalCell = serviceRow ? serviceRow.querySelector('.total-servicio') : null; // Corregir clase a total-servicio


        // Renderizar los totales de Días, Bonos y Servicios
        if (activityTotalCell) activityTotalCell.textContent = totalDays;

        // **CRÍTICO: Renderizar totales de Bonos y Servicios**
        if (foodTotalCell) {
            // Mostrar monto con 2 decimales O conteo (entero)
            foodTotalCell.textContent = canSeeAmounts ? totalFood.toFixed(2) : foodCount.toString();
        }
        if (fieldBonusTotalCell) {
            fieldBonusTotalCell.textContent = canSeeAmounts ? totalFieldBonus.toFixed(2) : fieldBonusCount.toString();
        }
        if (serviceTotalCell) {
            serviceTotalCell.textContent = canSeeAmounts ? totalService.toFixed(2) : serviceCount.toString();
        }


        // Renderizar Saldos y Utilización
        const vacCell = employeeRow.querySelector('.vacations-value');
        if (vacCell) vacCell.textContent = totalVacations;

        const breaksCell = employeeRow.querySelector('.breaks-value');
        if (breaksCell) breaksCell.textContent = totalBreaks;

        const utilCell = employeeRow.querySelector('.utilization-value');
        if (utilCell) {
            // Usar el total del mes completo como denominador.
            const utilizationRate = totalWorkingDaysInFullMonth > 0 ? (totalDays / totalWorkingDaysInFullMonth) * 100 : 0;
            const percentage = Math.round(utilizationRate);
            utilCell.textContent = `${percentage}%`;

            // ... (Lógica de colores ya es correcta)
            if (percentage < 85) {
                utilCell.style.color = '#4e6952ff'; // Verde suave
                utilCell.style.fontWeight = 'bold';
            } else if (percentage >= 85 && percentage <= 90) {
                utilCell.style.color = '#FF4500'; // Naranja
            } else if (percentage > 90) {
                utilCell.style.color = '#B22222'; // Rojo fuerte
            }
        }
    });
}


/**
 * Renderiza los datos de workLog en la tabla.
 */
function renderEmployeeWorkLog() {
    if (!workLogsData || workLogsData.length === 0) {
        calculateAndRenderTotals();
        return;
    }

    workLogsData.forEach(employeeData => {
        const employeeRow = document.querySelector(`.employee-row[data-employee-id="${employeeData.employee_id}"]`);
        if (!employeeRow) {
            return;
        }

        const activityRow = employeeRow;
        // Obtener filas de detalle basándose en la nueva estructura de Blade
        let currentRow = employeeRow.nextElementSibling;
        let comidaRow = null;
        let fieldBonusRow = null;
        let servicioRow = null;

        while (currentRow && currentRow.getAttribute('data-employee-id') === employeeData.employee_id.toString() && currentRow.classList.contains('activity-row')) {
            const label = currentRow.querySelector('.activity-label-cell').textContent;
            if (label === 'Comida') comidaRow = currentRow;
            else if (label === 'Bono') fieldBonusRow = currentRow;
            else if (label === 'Servicio') servicioRow = currentRow;
            currentRow = currentRow.nextElementSibling;
        }


        // Se usa querySelectorAll('.data-cell') para asegurar que solo se seleccionan las celdas de días
        const activityCells = Array.from(activityRow.querySelectorAll('.data-cell')).filter(cell => cell.getAttribute('data-date'));
        const comidaCells = comidaRow ? Array.from(comidaRow.querySelectorAll('.data-cell')).filter(cell => cell.getAttribute('data-date')) : [];
        const fieldBonusCells = fieldBonusRow ? Array.from(fieldBonusRow.querySelectorAll('.data-cell')).filter(cell => cell.getAttribute('data-date')) : [];
        const servicioCells = servicioRow ? Array.from(servicioRow.querySelectorAll('.data-cell')).filter(cell => cell.getAttribute('data-date')) : [];

        const dailyActivitiesMap = new Map();
        if (employeeData.daily_activities) {
            employeeData.daily_activities.forEach(activity => {
                dailyActivitiesMap.set(activity.date, activity);
            });
        }

        // **INICIO LÓGICA DE VISIBILIDAD DE FILAS DE DETALLE (CORRECCIÓN PREVIA)**
        let hasFoodItemsInPeriod = false;
        let hasFieldBonusItemsInPeriod = false;
        let hasServiceItemsInPeriod = false;

        const visibleDayHeaders = Array.from(document.querySelectorAll('.day-header')).filter(header => header.style.display !== 'none');
        const visibleDates = visibleDayHeaders.map(header => header.getAttribute('data-date'));

        visibleDates.forEach(dateAttr => {
            const dailyActivity = dailyActivitiesMap.get(dateAttr);
            if (dailyActivity) {
                if (dailyActivity.food_bonuses && dailyActivity.food_bonuses.length > 0) hasFoodItemsInPeriod = true;
                if (dailyActivity.field_bonuses && dailyActivity.field_bonuses.length > 0) hasFieldBonusItemsInPeriod = true;
                if (dailyActivity.services_list && dailyActivity.services_list.length > 0) hasServiceItemsInPeriod = true;
            }
        });

        // Ocultar/Mostrar filas completas si no hay items de ese tipo en el periodo visible
        if (comidaRow) {
            comidaRow.style.display = hasFoodItemsInPeriod ? '' : 'none';
        }
        if (fieldBonusRow) {
            fieldBonusRow.style.display = hasFieldBonusItemsInPeriod ? '' : 'none';
        }
        if (servicioRow) {
            servicioRow.style.display = hasServiceItemsInPeriod ? '' : 'none';
        }

        // 🆕 Añadir data-has-items para que el filtro JS sepa si ocultar la fila de detalle al restaurar la visibilidad
        if (comidaRow) comidaRow.setAttribute('data-has-items', hasFoodItemsInPeriod ? 'true' : 'false');
        if (fieldBonusRow) fieldBonusRow.setAttribute('data-has-items', hasFieldBonusItemsInPeriod ? 'true' : 'false');
        if (servicioRow) servicioRow.setAttribute('data-has-items', hasServiceItemsInPeriod ? 'true' : 'false');


        // Lógica de ajuste de Rowspan de la celda de nombre (manual y simple)
        let visibleDetailRows = 1; // La fila de Actividad siempre está visible
        if (hasFoodItemsInPeriod) visibleDetailRows++;
        if (hasFieldBonusItemsInPeriod) visibleDetailRows++;
        if (hasServiceItemsInPeriod) visibleDetailRows++;

        const employeeNameCell = employeeRow.querySelector('.employee-info-cell');
        const vacationsCell = employeeRow.querySelector('.vacations-cell');
        const breaksCell = employeeRow.querySelector('.breaks-cell');
        const utilizationCell = employeeRow.querySelector('.utilization-cell');
        const actionsCell = employeeRow.querySelector('.actions-cell');

        if (employeeNameCell) employeeNameCell.setAttribute('rowspan', visibleDetailRows);
        if (vacationsCell) vacationsCell.setAttribute('rowspan', visibleDetailRows);
        if (breaksCell) breaksCell.setAttribute('rowspan', visibleDetailRows);
        if (utilizationCell) utilizationCell.setAttribute('rowspan', visibleDetailRows);
        if (actionsCell) actionsCell.setAttribute('rowspan', visibleDetailRows);
        // **FIN LÓGICA DE VISIBILIDAD DE FILAS DE DETALLE**


        activityCells.forEach((cell, index) => {
            const dateAttr = cell.getAttribute('data-date');
            if (!dateAttr) {
                return;
            }

            const dailyActivity = dailyActivitiesMap.get(dateAttr);
            const statusIndicator = cell.querySelector('.status-indicator');

            // 1. Resetear el indicador y celdas
            if (statusIndicator) {
                statusIndicator.textContent = 'N';
                statusIndicator.className = 'status-indicator status-n';
                statusIndicator.title = 'No hay actividad registrada';
                statusIndicator.classList.remove('approved-border', 'reviewed-border', 'rejected-border', 'under-review-border', 'no-activity-border');
            }
            if (comidaCells[index]) comidaCells[index].textContent = '0';
            if (fieldBonusCells[index]) fieldBonusCells[index].textContent = '0';
            if (servicioCells[index]) servicioCells[index].textContent = '0';


            if (dailyActivity) {
                const activityType = dailyActivity.activity_type ? dailyActivity.activity_type.toUpperCase() : 'N';
                const dayStatus = dailyActivity.day_status; // Usar el estado consolidado calculado en el backend

                if (statusIndicator) {
                    statusIndicator.textContent = activityType;
                    statusIndicator.className = `status-indicator status-${activityType.toLowerCase()}`;
                    statusIndicator.title = dailyActivity.activity_description || '';

                    // Borde de estado para la actividad principal y los días no laborables/vacaciones
                    if (dayStatus === 'approved') {
                        statusIndicator.classList.add('approved-border');
                    } else if (dayStatus === 'reviewed') {
                        statusIndicator.classList.add('reviewed-border');
                    } else if (dayStatus === 'rejected') {
                        statusIndicator.classList.add('rejected-border');
                    } else {
                        statusIndicator.classList.add('under-review-border');
                    }
                }

                // Renderizar datos de las filas de detalles (Comida, Bono, Servicio)
                if (canSeeAmounts) { // 👈🏼 CRITICAL: Verificar si se pueden ver montos (Mostrar MONTOS)
                    if (comidaCells[index]) {
                        const totalFoodBonus = dailyActivity.food_bonuses ? dailyActivity.food_bonuses.reduce((sum, bonus) => sum + Number(bonus.daily_amount || 0), 0) : 0;
                        // Muestra 0.00 si no hay monto, de lo contrario el monto con 2 decimales
                        comidaCells[index].textContent = totalFoodBonus > 0 ? totalFoodBonus.toFixed(2) : '0';
                        comidaCells[index].title = dailyActivity.food_bonuses && dailyActivity.food_bonuses.length > 0 ? dailyActivity.food_bonuses.map(b => `${b.num_daily} comidas`).join(', ') : '';
                    }

                    if (fieldBonusCells[index]) {
                        const totalFieldBonus = dailyActivity.field_bonuses ? dailyActivity.field_bonuses.reduce((sum, bonus) => {
                            const amount = Number(bonus.daily_amount || 0);
                            if (bonus.currency === 'USD') {
                                return sum + (amount * Number(bonus.usd_to_mxn_rate || 1));
                            }
                            return sum + amount;
                        }, 0) : 0;
                        // Muestra 0.00 si no hay monto, de lo contrario el monto con 2 decimales
                        fieldBonusCells[index].textContent = totalFieldBonus > 0 ? totalFieldBonus.toFixed(2) : '0';
                        fieldBonusCells[index].title = dailyActivity.field_bonuses && dailyActivity.field_bonuses.length > 0 ? dailyActivity.field_bonuses.map(b => `${b.bonus_type} (${b.daily_amount} ${b.currency})`).join(', ') : '';
                    }

                    if (servicioCells[index]) {
                        const totalServiceAmount = dailyActivity.services_list ? dailyActivity.services_list.reduce((sum, service) => sum + Number(service.amount || 0), 0) : 0;
                        // Muestra 0.00 si no hay monto, de lo contrario el monto con 2 decimales
                        servicioCells[index].textContent = totalServiceAmount > 0 ? totalServiceAmount.toFixed(2) : '0';
                        servicioCells[index].title = dailyActivity.services_list && dailyActivity.services_list.length > 0 ? dailyActivity.services_list.map(s => s.service_name).join(', ') : '';
                    }
                } else {
                    // **INICIO CORRECCIÓN para mostrar el IDENTIFICADOR/TIPO si no hay montos**
                    if (comidaCells[index]) {
                        const foodBonuses = dailyActivity.food_bonuses || [];
                        if (foodBonuses.length > 0) {
                            // Si solo hay un tipo de comida, mostrar el count, si hay varios, mostrar el conteo total de items (ej: 2)
                            const totalNumDaily = foodBonuses.reduce((sum, bonus) => sum + Number(bonus.num_daily || 0), 0);

                            // Mostrar la cantidad de ítems si hay más de 1 tipo, o el total de comidas si es solo un tipo
                            // Para 'Comida', el requisito es mostrar 1, 2, 3... comidas, no el identificador.
                            comidaCells[index].textContent = foodBonuses.length > 1 ? `${foodBonuses.length} items` : `${totalNumDaily}`;
                            comidaCells[index].title = foodBonuses.map(b => `${b.bonus_type} x${b.num_daily}`).join(', ');
                        } else {
                            comidaCells[index].textContent = '0';
                            comidaCells[index].title = '';
                        }
                    }

                    if (fieldBonusCells[index]) {
                        const fieldBonuses = dailyActivity.field_bonuses || [];
                        if (fieldBonuses.length > 0) {
                            // Si hay un solo bono, mostrar su identificador; si hay varios, el conteo.
                            // El identificador (bonus_identifier) es el ID/Tipo que solicitas.
                            fieldBonusCells[index].textContent = fieldBonuses.length > 1 ? fieldBonuses.length : (fieldBonuses[0].bonus_identifier || 'Bono');
                            fieldBonusCells[index].title = fieldBonuses.map(b => `${b.bonus_type} (${b.bonus_identifier})`).join(', ');
                        } else {
                            fieldBonusCells[index].textContent = '0';
                            fieldBonusCells[index].title = '';
                        }
                    }

                    if (servicioCells[index]) {
                        const services = dailyActivity.services_list || [];
                        if (services.length > 0) {
                            // Si hay un solo servicio, mostrar su identificador; si hay varios, el conteo.
                            // El identificador (service_identifier) es el ID/Tipo que solicitas.
                            servicioCells[index].textContent = services.length > 1 ? services.length : (services[0].service_identifier || 'Servicio');
                            servicioCells[index].title = services.map(s => `${s.service_name} (${s.service_identifier})`).join(', ');
                        } else {
                            servicioCells[index].textContent = '0';
                            servicioCells[index].title = '';
                        }
                    }
                    // **FIN CORRECCIÓN**
                }
            }
        });
    });


    calculateAndRenderTotals();
}

/**
 * Función auxiliar para obtener el estado del día, duplicada de PHP para el frontend
 * para colorear los bordes (aunque ahora el backend lo proporciona). Se mantiene para
 * compatibilidad y validación en el modal.
 */
function getDailyStatusIndicator(dailyActivity) {
    let hasRejected = false;
    let hasUnderReview = false;
    let hasReviewed = false;
    let hasApproved = false;
    let totalItems = 0;
    let approvedItems = 0;
    let reviewedItems = 0;

    const activityStatus = dailyActivity.activity_status ? dailyActivity.activity_status.toLowerCase() : 'under_review';
    const activityType = dailyActivity.activity_type ? dailyActivity.activity_type.toUpperCase() : 'N';
    const activityIsRelevant = activityType !== 'N';

    if (activityIsRelevant) {
        totalItems++;
        switch (activityStatus) {
            case 'rejected': hasRejected = true; break;
            case 'under_review': hasUnderReview = true; break;
            case 'reviewed': hasReviewed = true; reviewedItems++; break;
            case 'approved': hasApproved = true; approvedItems++; break;
        }
    }

    const itemTypes = ['food_bonuses', 'field_bonuses', 'services_list'];
    itemTypes.forEach(type => {
        if (dailyActivity[type] && dailyActivity[type].length > 0) {
            dailyActivity[type].forEach(item => {
                totalItems++;
                const itemStatus = item.status ? item.status.toLowerCase() : 'under_review';
                switch (itemStatus) {
                    case 'rejected': hasRejected = true; break;
                    case 'under_review': hasUnderReview = true; break;
                    case 'reviewed': hasReviewed = true; reviewedItems++; break;
                    case 'approved': hasApproved = true; approvedItems++; break;
                }
            });
        }
    });

    if (totalItems === 0) {
        return 'under_review';
    }
    if (hasRejected) {
        return 'rejected';
    }
    if (hasUnderReview) {
        return 'under_review';
    }
    if (approvedItems === totalItems) {
        return 'approved';
    }
    if (hasReviewed) {
        return 'reviewed';
    }

    return 'under_review';
}
