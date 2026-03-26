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
let auxiliarPalState = 'inactive'; // Cambiado de 'hidden' a 'inactive'
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





// Variable global para el estado

function initializeApprovalTable() {
    setupEventListeners();
    initializeEmployeeNameClickListeners();
    initializeEmployeeModalListeners();

    loadMonthData(false).then(() => {
        showQuincena(1);
        setActiveButton("quincena1");
        updatePeriodInfo();

        allEmployeeRows = Array.from(document.querySelectorAll('.employee-row'));
        allSquadRows = Array.from(document.querySelectorAll('.squad-group-row'));

        if (typeof canSeeFilters !== 'undefined' && canSeeFilters) {
            const assignmentFilter = document.getElementById('assignment-filter');
            if (assignmentFilter) {
                assignmentFilter.value = 'assigned';
            }
            populatePositionFilter();
        }

        // ⭐ CRÍTICO: Llamamos applyFilters() SIEMPRE, incluso si no tiene permiso ver_filtros.
        // Esto asegura que la regla de ocultar a los Auxiliares PAL se aplique desde el inicio.
        applyFilters();
    });

    const toggleAuxBtn = document.getElementById('toggle-auxiliar-pal-btn');
    if (toggleAuxBtn) {
        toggleAuxBtn.addEventListener('click', function () {
            if (auxiliarPalState === 'inactive') {
                auxiliarPalState = 'active';
                this.innerHTML = '<i class="fas fa-times-circle"></i> Regresar';
                this.style.backgroundColor = '#e53e3e';
            } else {
                auxiliarPalState = 'inactive';
                this.innerHTML = '<i class="fas fa-user-shield"></i> Ver Auxiliar PAL';
                this.style.backgroundColor = '#4a5568';
            }
            applyFilters();
        });
    }

    // Inicializar Event Listeners de Filtro Normales
    if (typeof canSeeFilters !== 'undefined' && canSeeFilters) {
        document.getElementById('toggle-filters-btn').addEventListener('click', toggleFilters);
        document.getElementById('assignment-filter').addEventListener('change', applyFilters);

        document.getElementById('department-filter').addEventListener('change', function () {
            populatePositionFilter();
            applyFilters();
        });

        document.getElementById('position-filter').addEventListener('change', applyFilters);
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
    let url = `/rh/loadchart/calendar?employee_id=${employeeId}`;
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
 * ⭐ NUEVA FUNCIÓN: Llena el select de cargos dinámicamente.
 * Filtra los cargos disponibles basándose en el departamento seleccionado.
 */
function populatePositionFilter() {
    const departmentFilter = document.getElementById('department-filter');
    const positionFilter = document.getElementById('position-filter');

    if (!departmentFilter || !positionFilter) return;

    // 1. Guardar el valor actual del cargo (si existe) para intentar mantenerlo
    // si sigue siendo válido después del cambio de departamento.
    const currentPosition = positionFilter.value;
    const selectedDepartment = departmentFilter.value;

    // 2. Obtener cargos únicos basados en el departamento seleccionado
    // Usamos la variable global 'employees' que ya tienes cargada
    const uniquePositions = new Set();

    employees.forEach(employee => {
        // Si no hay departamento seleccionado (Ver todos) O el departamento coincide
        if (!selectedDepartment || employee.department === selectedDepartment) {
            if (employee.position) {
                uniquePositions.add(employee.position);
            }
        }
    });

    // 3. Convertir a array y ordenar alfabéticamente
    const sortedPositions = Array.from(uniquePositions).sort();

    // 4. Limpiar y reconstruir el select
    positionFilter.innerHTML = '<option value="">Todos los Cargos</option>';

    sortedPositions.forEach(position => {
        const option = document.createElement('option');
        option.value = position;
        option.textContent = position;
        positionFilter.appendChild(option);
    });

    // 5. Intentar restaurar la selección anterior si aún existe en la nueva lista,
    // de lo contrario, se queda en "Todos los Cargos" (valor "")
    if (currentPosition && uniquePositions.has(currentPosition)) {
        positionFilter.value = currentPosition;
    } else {
        positionFilter.value = "";
    }
}
/**
 * Aplica los filtros de departamento, CARGO, asignación y búsqueda.
 */
function applyFilters() {
    // ⭐ ELIMINAMOS el if(!canSeeFilters) return; porque necesitamos que evalúe a los guardias sí o sí.

    // Obtener valores de los filtros (De forma segura por si Blade no los renderizó)
    const assignmentFilterElem = document.getElementById('assignment-filter');
    const departmentFilterElem = document.getElementById('department-filter');
    const positionFilterElem = document.getElementById('position-filter');
    const searchFilterElem = document.getElementById('employee-search');

    const assignmentFilter = assignmentFilterElem ? assignmentFilterElem.value : 'all'; // Default fallback
    const departmentFilter = departmentFilterElem ? departmentFilterElem.value : '';
    const positionFilter = positionFilterElem ? positionFilterElem.value : '';
    const searchFilter = searchFilterElem ? searchFilterElem.value.toLowerCase().trim() : '';

    // ⭐ Detecta si el usuario está usando filtros intencionalmente.
    // Solo validamos assignmentFilter === 'all' si el filtro realmente existe en el HTML.
    const isUsingManualFilters = (
        departmentFilter !== '' ||
        positionFilter !== '' ||
        searchFilter !== '' ||
        (assignmentFilterElem !== null && assignmentFilter === 'all')
    );

    allEmployeeRows.forEach(row => {
        const department = row.getAttribute('data-department');
        const employeeId = row.getAttribute('data-employee-id');

        // Buscar info del empleado en la lista global
        const employeeData = employees.find(e => e.id.toString() === employeeId);

        // Datos para filtrado
        const employeeName = employeeData ? employeeData.full_name.toLowerCase().trim() : '';
        const employeeNumber = employeeData ? employeeData.employee_number.toString().toLowerCase() : '';
        const employeePosition = employeeData ? employeeData.position : '';

        // Obtenemos el job_title
        const employeeJobTitle = employeeData && employeeData.job_title ? employeeData.job_title.trim().toUpperCase() : '';
        const isAuxiliarPal = employeeJobTitle.includes('AUXILIAR PAL');

        // Lógica de Asignación
        const employeeAssignment = loadChartAssignments.find(a => a.employee_id.toString() === employeeId);
        const isReviewerForEmployee = employeeAssignment && employeeAssignment.reviewer_id === currentUserId;
        const isApproverForEmployee = employeeAssignment && employeeAssignment.approver_id === currentUserId;
        const isAssigned = isReviewerForEmployee || isApproverForEmployee;

        // --- APLICACIÓN DE REGLAS ---
        const matchesDepartment = !departmentFilter || department === departmentFilter;
        const matchesPosition = !positionFilter || employeePosition === positionFilter;
        const matchesSearch = !searchFilter || employeeName.includes(searchFilter) || employeeNumber.includes(searchFilter);

        // Si el filtro de asignación no existe en HTML, asumimos true para no ocultar nada por error
        const matchesAssignment = !assignmentFilterElem ? true : (assignmentFilter === 'all' || (assignmentFilter === 'assigned' && isAssigned));

        // ⭐ REGLA ESTRICTA PARA OCULTAR/MOSTRAR AUXILIAR PAL
        let matchesAuxiliar = true;

        if (auxiliarPalState === 'active') {
            // 1. Si el botón especial está encendido, SOLO mostrar a los Auxiliares PAL
            matchesAuxiliar = isAuxiliarPal;
        } else {
            // 2. Si el botón está apagado...
            if (isAuxiliarPal) {
                // Si el empleado ES Auxiliar PAL, lo ocultamos por defecto...
                // a menos que el usuario esté buscando o filtrando activamente.
                if (!isUsingManualFilters) {
                    matchesAuxiliar = false;
                }
            }
        }

        // --- RESULTADO FINAL ---
        const isVisible = matchesDepartment && matchesPosition && matchesSearch && matchesAssignment && matchesAuxiliar;

        // Aplicar visibilidad a la fila principal
        row.style.display = isVisible ? '' : 'none';

        // Ocultar/Mostrar filas de detalle asociadas
        let nextRow = row.nextElementSibling;
        while (nextRow && nextRow.classList.contains('activity-row') && nextRow.getAttribute('data-employee-id') === row.getAttribute('data-employee-id')) {
            if (!isVisible) {
                nextRow.style.display = 'none';
            } else {
                // Restaurar visibilidad basada en si tiene ítems
                const isHiddenByToggle = nextRow.classList.contains('hidden');
                const isHiddenByRender = nextRow.getAttribute('data-has-items') === 'false';

                if (isHiddenByToggle || isHiddenByRender) {
                    nextRow.style.display = 'none';
                } else {
                    nextRow.style.display = '';
                }
            }
            nextRow = nextRow.nextElementSibling;
        }
    });

    // Recalcular visibilidad de las filas de cuadrilla
    if (typeof allSquadRows !== 'undefined') {
        allSquadRows.forEach(squadRow => {
            const nextRow = squadRow.nextElementSibling;
            let showSquadRow = false;
            let currentRow = nextRow;

            while (currentRow && !currentRow.classList.contains('squad-group-row')) {
                if (currentRow.classList.contains('employee-row') && currentRow.style.display !== 'none') {
                    showSquadRow = true;
                    break;
                }
                if (currentRow.classList.contains('activity-row')) {
                    currentRow = currentRow.nextElementSibling;
                    continue;
                }
                currentRow = currentRow.nextElementSibling;
            }
            squadRow.style.display = showSquadRow ? '' : 'none';
        });
    }

    // Re-aplicar vista de quincena/mes
    if (typeof currentView !== 'undefined') {
        if (currentView === 'quincena1') showQuincena(1);
        else if (currentView === 'quincena2') showQuincena(2);
        else showFullMonth();
    }

    // Recalcular totales
    calculateAndRenderTotals();
}
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
        const response = await fetch('/rh/loadchart/check-updates', {
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

        const response = await fetch(`/rh/loadchart/approval-data/${currentYear}/${currentMonth}`, {
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
            window.location.href = "/rh/loadchart/calendar";
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

    modal.querySelectorAll('.modal-approval-close, .modal-approval-close-btn').forEach(btn => {
        btn.addEventListener('click', () => { modal.style.display = 'none'; });
    });

    document.getElementById('modal-save-btn').addEventListener('click', saveModalChanges);
}

function handleTableClick(event) {
    const statusIndicator = event.target.closest('.status-indicator');
    if (statusIndicator) {
        const cell = statusIndicator.closest('.data-cell');

        // ⭐ CORRECCIÓN CLAVE: Buscar la fila principal o la fila de actividad
        const row = cell.closest('.employee-row') || cell.closest('.activity-row');

        if (!row) return; // Prevención de errores si el DOM no coincide

        const employeeId = row.getAttribute('data-employee-id');
        const date = cell.getAttribute('data-date');

        // Bloquear el modal si el usuario no es Revisor ni Aprobador del empleado
        const employeeAssignment = loadChartAssignments.find(a => a.employee_id.toString() === employeeId);
        const isReviewerForEmployee = employeeAssignment && employeeAssignment.reviewer_id === currentUserId;
        const isApproverForEmployee = employeeAssignment && employeeAssignment.approver_id === currentUserId;

        const employeeData = workLogsData.find(log => log.employee_id.toString() === employeeId);
        const dailyActivity = employeeData?.daily_activities.find(act => act.date === date);

        if (!isReviewerForEmployee && !isApproverForEmployee) {
            if (dailyActivity) {
                showSwalNotification('Acceso Denegado', 'Solo puede abrir el detalle de actividad para los empleados que tiene asignados como Revisor o Aprobador.', 'error');
            } else {
                showSwalNotification('Información', 'No hay actividades registradas para este día.', 'info');
            }
            return;
        }

        if (dailyActivity) {
            openApprovalModal(employeeData, dailyActivity);
        } else {
            showSwalNotification('Información', 'No hay actividades registradas para este día.', 'info');
        }
    }
}
// =========================================================================
// saveModalChanges — COMPLETA Y CORREGIDA
// =========================================================================

async function saveModalChanges() {
    const itemsList = document.getElementById('modal-items-list');
    const cards = itemsList.querySelectorAll('.approval-item-card');
    const changes = [];

    let activityStatus = null;
    let vespertinaStatus = null;
    let isVac = false;

    cards.forEach(card => {
        const itemType = card.getAttribute('data-item-type');
        const itemIndexAttr = card.getAttribute('data-item-index');
        const itemIndex = itemIndexAttr !== 'null' ? parseInt(itemIndexAttr) : null;
        const newStatus = card.getAttribute('data-selected-status');
        const originalStatus = card.getAttribute('data-original-status');
        const originalComment = card.getAttribute('data-original-comment');
        const effectivelyLocked = card.getAttribute('data-effectively-locked') === 'true';

        const textarea = card.querySelector('.rejection-textarea');
        const comment = textarea ? textarea.value.trim() : '';

        if (itemType === 'activity') {
            activityStatus = newStatus;
            if (currentModalData.dailyActivities.activity_type === 'VAC') isVac = true;
        }
        if (itemType === 'activity_vespertina') {
            vespertinaStatus = newStatus;
            if (currentModalData.dailyActivities.activity_type_vespertina === 'VAC') isVac = true;
        }

        if (!effectivelyLocked && (newStatus !== originalStatus || comment !== originalComment)) {
            changes.push({
                date: currentModalData.date,
                item_type: itemType,
                item_index: itemIndex,
                status: newStatus,
                rejection_reason: comment
            });
        }
    });

    // Validación: Vacaciones matutina y vespertina deben coincidir
    if (isVac && activityStatus && vespertinaStatus && activityStatus !== vespertinaStatus) {
        showSwalNotification(
            'Acción Inválida',
            'Para Vacaciones, el turno Matutino y Vespertino deben coincidir (ambos Aprobados o ambos Rechazados).',
            'warning'
        );
        return;
    }

    if (changes.length > 0) {
        await updateDailyItemsStatus(currentModalData.employeeId, changes);
    } else {
        showSwalNotification('Información', 'No hay cambios para guardar.', 'info');
    }
}


// =========================================================================
// getItemFromActivity — COMPLETA (sin cambios, se mantiene igual)
// =========================================================================

function getItemFromActivity(dailyActivity, itemType, itemIndex) {
    if (itemType === 'activity') {
        return {
            status: dailyActivity.activity_status,
            rejection_reason: dailyActivity.rejection_reason
        };
    }
    if (itemType === 'activity_vespertina') {
        return {
            status: dailyActivity.activity_status_vespertina || 'under_review',
            rejection_reason: dailyActivity.rejection_reason_vespertina || ''
        };
    }
    const listMap = {
        food_bonuses: dailyActivity.food_bonuses,
        field_bonuses: dailyActivity.field_bonuses,
        services_list: dailyActivity.services_list
    };
    if (listMap[itemType] && listMap[itemType][itemIndex]) {
        return listMap[itemType][itemIndex];
    }
    return null;
}

// =========================================================================
// HELPERS DEL MODAL
// =========================================================================

function getCardTypeClass(itemType) {
    return {
        activity: 'activity',
        activity_vespertina: 'vespertina',
        food_bonuses: 'food',
        field_bonuses: 'field',
        services_list: 'service'
    }[itemType] || 'activity';
}

function getStatusLabel(status) {
    return {
        under_review: 'Bajo Revisión',
        reviewed: 'Revisado',
        approved: 'Aprobado',
        rejected: 'Rechazado'
    }[status] || 'Bajo Revisión';
}

function getStatusIconClass(status) {
    return {
        under_review: 'fa-regular fa-hourglass-half',
        reviewed: 'fas fa-lock-open',
        approved: 'fas fa-lock',
        rejected: 'fas fa-exclamation-triangle'
    }[status] || 'fa-regular fa-hourglass-half';
}

function buildStatusBadge(status) {
    const s = (status || 'under_review').toLowerCase();
    return `<span class="item-status-badge status-badge-${s}">
                <i class="${getStatusIconClass(s)}" style="font-size:10px;"></i> ${getStatusLabel(s)}
            </span>`;
}

// =========================================================================
// addItemCard — COMPLETA Y CORREGIDA
// =========================================================================

function addItemCard(concept, details, identifier, additionalDetails, itemType, itemIndex, status, rejectionReason, amount, isReviewer, isApprover) {
    const itemsList = document.getElementById('modal-items-list');
    const safeStatus = (status || 'under_review').toLowerCase();
    const typeClass = getCardTypeClass(itemType);

    const isLockedNoRole = !isReviewer && !isApprover;
    const isLockedByRole = (isReviewer && !isApprover) && (safeStatus === 'approved' || safeStatus === 'rejected');
    const effectivelyLocked = isLockedNoRole || isLockedByRole;

    const card = document.createElement('div');
    card.className = `approval-item-card type-${typeClass}${effectivelyLocked ? ' is-locked' : ''}`;
    card.setAttribute('data-item-type', itemType);
    card.setAttribute('data-item-index', itemIndex === null ? 'null' : itemIndex);
    card.setAttribute('data-selected-status', safeStatus);
    card.setAttribute('data-original-status', safeStatus);
    card.setAttribute('data-original-comment', rejectionReason || '');
    card.setAttribute('data-effectively-locked', effectivelyLocked ? 'true' : 'false');

    // Colores de actividad para el pill
    const activityColors = {
        B: 'var(--work-base)', P: 'var(--work-well)', TC: 'var(--home-office)',
        V: 'var(--traveling)', D: 'var(--rest)', VAC: 'var(--vacation)',
        E: 'var(--training)', M: 'var(--medical)', C: 'var(--commissioned)',
        A: 'var(--absence)', PE: 'var(--permission)', N: '#ccc'
    };

    const showPill = ['activity', 'activity_vespertina'].includes(itemType);
    const pillColor = activityColors[identifier] || '#a5a5a5';
    const pillHTML = showPill
        ? `<span class="item-activity-pill" style="background-color:${pillColor};">${identifier}</span>`
        : '';

    // ✅ Detalles adicionales + monto con Monto: resaltado
    let detailsRow = '';
    const detailParts = [];

    if (additionalDetails) {
        detailParts.push(`<span class="detail-chip">${additionalDetails}</span>`);
    }

    if (amount && canSeeAmounts) {
        detailParts.push(`
        <span class="amount-chip">
            <span style="font-size:10px; font-weight:600; opacity:0.8; margin-right:3px;">Monto:</span>
            <strong style="font-size:13px; letter-spacing:0.01em;">$${amount}</strong>
        </span>
    `);
    }

    if (detailParts.length) {
        detailsRow = `<div class="item-card-details">${detailParts.join('')}</div>`;
    }

    // Controles según rol y estado
    let controlsHTML = '';

    if (effectivelyLocked) {
        let lockMsg = '';
        if (isLockedNoRole) {
            lockMsg = `<i class="fas fa-lock" style="font-size:11px;"></i> ${getStatusLabel(safeStatus)}`;
        } else if (safeStatus === 'approved') {
            lockMsg = `<i class="fas fa-lock" style="font-size:11px; color:#64946f;"></i> Aprobado — solo el Aprobador puede modificar`;
        } else {
            lockMsg = `<i class="fas fa-exclamation-triangle" style="font-size:11px; color:#f35900;"></i> Rechazado — esperando corrección del empleado`;
        }

        controlsHTML = `<div class="item-card-controls"><span class="locked-status-display">${lockMsg}</span>`;

        if (rejectionReason) {
            controlsHTML += `
                <div class="item-card-details">
                    <span class="rejection-reason-chip">
                        <i class="fas fa-comment" style="font-size:10px;"></i> ${rejectionReason}
                    </span>
                </div>`;
        }
        controlsHTML += '</div>';

    } else {
        const isApproved = safeStatus === 'approved';
        const isReviewed = safeStatus === 'reviewed';
        const isRejected = safeStatus === 'rejected';

        const btnDefs = [
            {
                status: 'under_review',
                label: 'Bajo Revisión',
                icon: 'fa-regular fa-hourglass-half',
                show: true,
                disabled: isReviewed || isApproved
            },
            {
                status: 'reviewed',
                label: 'Revisado',
                icon: 'fas fa-lock-open',
                show: true,
                disabled: false
            },
            {
                status: 'approved',
                label: 'Aprobado',
                icon: 'fas fa-lock',
                show: isApprover,
                disabled: false
            },
            {
                status: 'rejected',
                label: 'Rechazado',
                icon: 'fas fa-exclamation-triangle',
                show: true,
                disabled: false
            },
        ];

        // Si ya está aprobado, solo puede ir a Rechazado
        if (isApprover && isApproved) {
            btnDefs.find(b => b.status === 'under_review').disabled = true;
            btnDefs.find(b => b.status === 'reviewed').disabled = true;
        }

        let btns = '';
        btnDefs.forEach(btn => {
            if (!btn.show) return;
            const isActive = btn.status === safeStatus;
            btns += `
                <button class="approval-btn btn-${btn.status}${isActive ? ' is-active' : ''}"
                        data-status="${btn.status}"
                        ${btn.disabled ? 'disabled' : ''}>
                    <i class="${btn.icon}" style="font-size:10px;"></i> ${btn.label}
                </button>`;
        });

        controlsHTML = `
            <div class="item-card-controls">
                <div class="approval-btn-group">${btns}</div>
                <div class="rejection-textarea-wrapper${isRejected ? ' is-visible' : ''}">
                    <textarea class="rejection-textarea" placeholder="Escribe el motivo de rechazo...">${rejectionReason || ''}</textarea>
                </div>
            </div>`;
    }

    card.innerHTML = `
        <div class="item-card-header">
            <span class="item-concept-badge">${concept}</span>
            ${pillHTML}
            <span class="item-description">${details}</span>
<span style="display:inline-flex; align-items:center; gap:5px; flex-shrink:0;">
    <span style="font-size:10px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.06em;">Estado:</span>
    ${buildStatusBadge(safeStatus)}
</span>        </div>
        ${detailsRow}
        ${controlsHTML}
    `;

    // Event listeners en botones
    if (!effectivelyLocked) {
        const btnGroup = card.querySelector('.approval-btn-group');
        const textareaWrapper = card.querySelector('.rejection-textarea-wrapper');
        const statusBadge = card.querySelector('.item-status-badge');

        if (btnGroup) {
            btnGroup.querySelectorAll('.approval-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    if (this.disabled) return;
                    const newStatus = this.getAttribute('data-status');

                    btnGroup.querySelectorAll('.approval-btn').forEach(b => b.classList.remove('is-active'));
                    this.classList.add('is-active');

                    card.setAttribute('data-selected-status', newStatus);

                    if (statusBadge) {
                        statusBadge.className = `item-status-badge status-badge-${newStatus}`;
                        statusBadge.innerHTML = `<i class="${getStatusIconClass(newStatus)}" style="font-size:10px;"></i> ${getStatusLabel(newStatus)}`;
                    }

                    if (textareaWrapper) {
                        if (newStatus === 'rejected') {
                            textareaWrapper.classList.add('is-visible');
                            textareaWrapper.querySelector('textarea').focus();
                        } else {
                            textareaWrapper.classList.remove('is-visible');
                        }
                    }
                });
            });
        }
    }

    itemsList.appendChild(card);
}

// =========================================================================
// openApprovalModal — COMPLETA Y CORREGIDA
// =========================================================================

// =========================================================================
// openApprovalModal — ACTUALIZADO CON CAMPOS DE SUMINISTRO
// =========================================================================

function openApprovalModal(employeeData, dailyActivity) {
    const modal = document.getElementById('approvalModal');
    const subtitle = modal.querySelector('.modal-approval-subtitle');
    const itemsList = document.getElementById('modal-items-list');

    let employeeName = 'Empleado Desconocido';
    let employeeJobTitle = '';

    const employeeInfo = employees.find(e => e.id.toString() === employeeData.employee_id.toString());
    if (employeeInfo) {
        employeeName = employeeInfo.full_name;
        employeeJobTitle = employeeInfo.job_title ? employeeInfo.job_title.toUpperCase() : '';
    } else {
        const employeeRow = document.querySelector(`.employee-row[data-employee-id="${employeeData.employee_id}"]`);
        if (employeeRow) {
            const nameCell = employeeRow.querySelector('.employee-info-cell');
            if (nameCell) employeeName = nameCell.textContent.trim();
        }
    }

    const isGuardia = employeeJobTitle.includes('AUXILIAR PAL');

    const dateWithTime = dailyActivity.date + 'T12:00:00';
    const formattedDate = new Date(dateWithTime).toLocaleDateString('es-ES', {
        day: 'numeric', month: 'long', year: 'numeric'
    });

    currentModalData.employeeId = employeeData.employee_id;
    currentModalData.date = dailyActivity.date;
    currentModalData.dailyActivities = dailyActivity;

    const assignment = loadChartAssignments.find(a => a.employee_id === employeeData.employee_id);
    const isReviewer = assignment && assignment.reviewer_id === currentUserId;
    const isApprover = assignment && assignment.approver_id === currentUserId;

    subtitle.textContent = `${employeeName} — ${formattedDate}`;
    itemsList.innerHTML = '';

    // ── 1. Actividad principal (Matutina) ──
    if (dailyActivity.activity_type) {
        const actStatus = (dailyActivity.activity_status ?? 'under_review').toLowerCase();
        let extraDetails = '';

        if (dailyActivity.activity_type === 'P' && dailyActivity.well_name) {
            extraDetails = `<span class="chip-label">Pozo</span> <strong style="color:#1a2035;">${dailyActivity.well_name}</strong>`;
        } else if (dailyActivity.activity_type === 'C' && dailyActivity.commissioned_to) {
            extraDetails = `<span class="chip-label">Área comisionada</span> <strong style="color:#1a2035;">${dailyActivity.commissioned_to}</strong>`;
        } else if (dailyActivity.activity_type === 'V') {
            extraDetails = `Destino: <strong style="color:#1a2035;">${dailyActivity.travel_destination || 'N/A'}</strong> &nbsp;·&nbsp; Motivo: <strong style="color:#1a2035;">${dailyActivity.travel_reason || 'N/A'}</strong>`;

            // ⭐ AQUÍ SE AGREGAN LOS DATOS DE SUMINISTRO SI EXISTEN
            if (dailyActivity.contract_number) {
                extraDetails += `<br><span style="margin-top: 5px; display: inline-block; font-size: 0.95em;">
                                    <i class="fas fa-file-contract" style="color:#6b7280;"></i> Contrato: <strong style="color:#1a2035;">${dailyActivity.contract_number}</strong>
                                    &nbsp;·&nbsp;
                                    <i class="fas fa-cogs" style="color:#6b7280;"></i> Servicio: <strong style="color:#1a2035;">${dailyActivity.travel_service_type || 'N/A'}</strong>
                                 </span>`;
            }
        } else if (dailyActivity.activity_type === 'B' && dailyActivity.base_activity_description) {
            extraDetails = `Actividad: <strong style="color:#1a2035;">${dailyActivity.base_activity_description}</strong>`;
        }

        const conceptTitle = (dailyActivity.activity_type_vespertina && dailyActivity.activity_type_vespertina !== 'N')
            ? 'Matutina' : 'Actividad';

        addItemCard(
            conceptTitle,
            dailyActivity.activity_description || '',
            dailyActivity.activity_type || 'N',
            extraDetails,
            'activity', null, actStatus,
            dailyActivity.rejection_reason,
            null, isReviewer, isApprover
        );
    }

    // ── 1.5 Actividad Vespertina ──
    if (dailyActivity.activity_type_vespertina && dailyActivity.activity_type_vespertina !== 'N') {
        const vStatus = (dailyActivity.activity_status_vespertina || 'under_review').toLowerCase();
        let vDetails = '';

        if (dailyActivity.activity_type_vespertina === 'B' && dailyActivity.base_activity_description) {
            vDetails = `Actividad: <strong style="color:#1a2035;">${dailyActivity.base_activity_description}</strong>`;
        } else if (dailyActivity.activity_type_vespertina === 'P' && dailyActivity.well_name) {
            vDetails = `Pozo: <strong style="color:#1a2035;">${dailyActivity.well_name}</strong>`;
        } else if (dailyActivity.activity_type_vespertina === 'V') {
            vDetails = `Destino: <strong style="color:#1a2035;">${dailyActivity.travel_destination || 'N/A'}</strong> &nbsp;·&nbsp; Motivo: <strong style="color:#1a2035;">${dailyActivity.travel_reason || 'N/A'}</strong>`;

            // ⭐ AGREGADO TAMBIÉN PARA VESPERTINA POR SI ACASO
            if (dailyActivity.contract_number) {
                vDetails += `<br><span style="margin-top: 5px; display: inline-block; font-size: 0.95em;">
                                <i class="fas fa-file-contract" style="color:#6b7280;"></i> Contrato: <strong style="color:#1a2035;">${dailyActivity.contract_number}</strong>
                                &nbsp;·&nbsp;
                                <i class="fas fa-cogs" style="color:#6b7280;"></i> Servicio: <strong style="color:#1a2035;">${dailyActivity.travel_service_type || 'N/A'}</strong>
                             </span>`;
            }
        } else if (dailyActivity.activity_description_vespertina) {
            vDetails = dailyActivity.activity_description_vespertina;
        }

        addItemCard(
            'Vespertina',
            dailyActivity.activity_description_vespertina || '',
            dailyActivity.activity_type_vespertina || 'N',
            vDetails,
            'activity_vespertina', null, vStatus,
            dailyActivity.rejection_reason_vespertina || '',
            null, isReviewer, isApprover
        );
    }

    // ── 2. Bonos de Comida ──
    if (dailyActivity.food_bonuses && dailyActivity.food_bonuses.length > 0) {
        dailyActivity.food_bonuses.forEach((bonus, index) => {
            const amount = canSeeAmounts ? `${Number(bonus.daily_amount).toFixed(2)} MXN` : null;
            const detalle = `Raciones: <strong style="font-size:13px; color:#1a2035;">${bonus.num_daily}</strong>`;

            addItemCard(
                'Bono', bonus.bonus_type, 'Comida', detalle, 'food_bonuses', index,
                bonus.status, bonus.rejection_reason, amount, isReviewer, isApprover
            );
        });
    }

    // ── 3. Bonos de Campo / Especial Guardia ──
    if (dailyActivity.field_bonuses && dailyActivity.field_bonuses.length > 0) {
        dailyActivity.field_bonuses.forEach((bonus, index) => {
            let detailParts = [`Moneda: <strong style="color:#1a2035;">${bonus.currency}</strong>`];

            if (bonus.quantity && bonus.quantity > 1) {
                detailParts.push(`Cant. realizada: <strong style="font-size:13px; color:#1a2035;">${bonus.quantity}</strong>`);
            }

            if (canSeeAmounts && bonus.base_amount && bonus.quantity > 1) {
                detailParts.push(`Precio base: <strong style="color:#166534;">$${Number(bonus.base_amount).toFixed(2)}</strong>`);
            }

            const amount = canSeeAmounts ? `${Number(bonus.daily_amount).toFixed(2)} ${bonus.currency}` : null;
            const conceptName = isGuardia ? 'Bono Especial' : 'Bono';

            addItemCard(
                conceptName, bonus.bonus_type, bonus.bonus_identifier, detailParts.join(' &nbsp;·&nbsp; '),
                'field_bonuses', index, bonus.status, bonus.rejection_reason, amount, isReviewer, isApprover
            );
        });
    }

    // ── 4. Servicios ──
    if (dailyActivity.services_list && dailyActivity.services_list.length > 0) {
        dailyActivity.services_list.forEach((service, index) => {
            const amount = canSeeAmounts ? `${Number(service.amount).toFixed(2)} MXN` : null;
            const payrollDetail = service.service_real_date
                ? `Fecha realización: <strong style="color:#1a2035;">${service.service_real_date}</strong>`
                : `<span style="color:#9ca3af;">Sin fecha especificada</span>`;

            addItemCard(
                'Servicio', service.service_name, service.service_identifier, payrollDetail,
                'services_list', index, service.status, service.rejection_reason, amount, isReviewer, isApprover
            );
        });
    }

    modal.style.display = 'flex';
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

        const response = await fetch('/rh/loadchart/update-multiple-statuses', {
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
    if (!modalContent) return;

    let overlay = document.getElementById('modal-save-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'modal-save-overlay';
        overlay.className = 'modal-save-overlay';
        overlay.innerHTML = `
            <div class="modal-spinner"></div>
            <div class="modal-saving-message">${message}</div>
        `;
        modalContent.appendChild(overlay);
    } else {
        overlay.querySelector('.modal-saving-message').textContent = message;
    }

    overlay.style.display = 'flex';
    document.getElementById('modal-save-btn').disabled = true;
    document.querySelectorAll('.modal-approval-close, .modal-approval-close-btn')
        .forEach(btn => btn.disabled = true);
}

function hideModalSavingState() {
    const overlay = document.getElementById('modal-save-overlay');
    if (overlay) overlay.style.display = 'none';

    document.getElementById('modal-save-btn').disabled = false;
    document.querySelectorAll('.modal-approval-close, .modal-approval-close-btn')
        .forEach(btn => btn.disabled = false);
}

function hideModalSavingState() {
    const overlay = document.getElementById('modal-save-overlay');
    if (overlay) overlay.style.display = 'none';

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
        const response = await fetch('/rh/loadchart/update-approval-status', {
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

        const response = await fetch(`/rh/loadchart/approval-data/${currentYear}/${currentMonth}`, {
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

        // Actualizar variables globales
        workLogsData = data.workLogsData;
        fortnightlyConfig = data.fortnightlyConfig;
        loadChartAssignments = data.loadChartAssignments;
        canSeeAmounts = data.canSeeAmounts;
        userPermissions = data.userPermissions;
        monthlyDays = data.monthlyDays;
        employees = data.employees; // Actualización crítica de empleados

        // 1. Reconstruir tabla
        updateTableStructure(data.monthlyDays, data.employees);
        updatePeriodInfo();

        // 2. Restaurar vista (Quincena 1, 2 o Mes)
        if (currentView === 'quincena1') {
            showQuincena(1);
        } else if (currentView === 'quincena2') {
            showQuincena(2);
        } else {
            showFullMonth();
        }

        // 3. ⭐ CRÍTICO: Actualizar filas para filtro y REPOBLAR CARGOS con los nuevos datos
        allEmployeeRows = Array.from(document.querySelectorAll('.employee-row'));
        allSquadRows = Array.from(document.querySelectorAll('.squad-group-row'));

        if (typeof canSeeFilters !== 'undefined' && canSeeFilters) {
            // Llenamos de nuevo el filtro porque los empleados cambiaron
            populatePositionFilter();
            applyFilters();
        }

        setupEmployeeRowListeners();

        if (!isRefresh) {
            hideLoadingState();
        }

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
        // ⭐ Aseguramos que el ColSpan cargue ya con los días ocultos descontados
        let visibleCount = monthlyDays.length;
        if (currentView === 'quincena1') visibleCount = monthlyDays.filter(d => d.is_quincena_1).length;
        if (currentView === 'quincena2') visibleCount = monthlyDays.filter(d => d.is_quincena_2).length;

        daysColumnsHeader.colSpan = visibleCount;
        daysColumnsHeader.textContent = `Días del Período (${visibleCount} días)`;
    }

    if (daysHeaderRow) {
        daysHeaderRow.innerHTML = '';

        monthlyDays.forEach(dayInfo => {
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

            // ⭐ APLICAR DISPLAY NONE INMEDIATAMENTE SI NO PERTENECE A LA VISTA ACTUAL
            if (currentView === 'quincena1' && !dayInfo.is_quincena_1) th.style.display = 'none';
            if (currentView === 'quincena2' && !dayInfo.is_quincena_2) th.style.display = 'none';

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

    const groupedEmployees = {};
    employeesData.forEach(employee => {
        const squadNumber = employee.squads && employee.squads.length > 0
            ? employee.squads[0].squad_number
            : 999;

        if (!groupedEmployees[squadNumber]) {
            groupedEmployees[squadNumber] = [];
        }
        groupedEmployees[squadNumber].push(employee);
    });

    const sortedSquadNumbers = Object.keys(groupedEmployees).sort((a, b) => parseInt(a) - parseInt(b));
    const showSquadGrouping = document.getElementById('squad-control') !== null;
    const totalColumnsForSquadRow = 6 + monthlyDaysData.length;

    sortedSquadNumbers.forEach(squadNumber => {
        const squadEmployees = groupedEmployees[squadNumber];

        if (showSquadGrouping) {
            const squadLabel = squadNumber !== '999'
                ? `Cuadrilla-${squadNumber.padStart(2, '0')}`
                : 'Sin Cuadrilla Asignada';
            const squadClass = squadNumber !== '999' ? 'squad-group-row-active' : 'squad-group-row-none';

            let squadRow = document.createElement('tr');
            squadRow.className = `squad-group-row ${squadClass}`;
            squadRow.setAttribute('data-squad-number', squadNumber);
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

            let mainRow = document.createElement('tr');
            mainRow.className = 'employee-row';
            mainRow.setAttribute('data-employee-id', employee.id);
            mainRow.setAttribute('data-department', employee.department);

            // Rowspan ajustado a 5 (Nombre + Vespertina + Comida + Bono + Servicio)
            mainRow.innerHTML = `
                <td rowspan="5" class="employee-info-cell" data-icon="bx bx-calendar" data-text="ver calendario">
                    ${employee.full_name}
                </td>
                <td class="activity-label-cell activity-main-label">Actividad</td>
                ${monthlyDaysData.map(dayInfo => {
                let displayStyle = '';
                if (currentView === 'quincena1' && !dayInfo.is_quincena_1) displayStyle = 'display: none;';
                if (currentView === 'quincena2' && !dayInfo.is_quincena_2) displayStyle = 'display: none;';

                return `
                    <td class="data-cell ${dayInfo.is_quincena_1 ? 'quincena-1' : ''} ${dayInfo.is_quincena_2 ? 'quincena-2' : ''} ${!dayInfo.is_working_day ? 'non-working' : ''} ${!dayInfo.is_current_month ? 'other-month' : ''}"
                        data-day="${dayInfo.day}" data-date="${dayInfo.date}" style="${displayStyle}">
                        <div class="status-indicator status-n">N</div>
                    </td>
                `}).join('')}
                <td class="data-cell total-activity">
                    <div class="status-indicator">0</div>
                </td>
                <td rowspan="5" class="vacations-cell">
                    <div class="vacations-container">
                        <div class="vacations-value">0</div>
                    </div>
                </td>
                <td rowspan="5" class="breaks-cell">
                    <div class="breaks-container">
                        <div class="breaks-value">0</div>
                    </div>
                </td>
                <td rowspan="5" class="utilization-cell">
                    <div class="utilization-container">
                        <div class="utilization-value">0%</div>
                    </div>
                </td>
                <td rowspan="5" class="actions-cell">
                    <div class="actions-container">
                        ${isReviewerForEmployee ? '<button class="btn-review">Revisar</button>' : ''}
                        ${isApproverForEmployee ? '<button class="btn-approve">Aprobar</button>' : ''}
                        ${!isReviewerForEmployee && !isApproverForEmployee ? '<span class="no-permissions">Sin permisos</span>' : ''}
                    </div>
                </td>
            `;
            tbody.appendChild(mainRow);

            // AÑADIDO: 'Vespertina' a los tipos de fila
            const rowTypes = ['Vespertina', 'Comida', 'Bono', 'Servicio'];
            rowTypes.forEach(rowType => {
                let detailRow = document.createElement('tr');
                detailRow.className = 'activity-row';
                detailRow.setAttribute('data-employee-id', employee.id);
                detailRow.setAttribute('data-department', employee.department);

                // Lógica especial para generar los indicadores de la fila Vespertina
                let cellsHtml = '';
                if (rowType === 'Vespertina') {
                    cellsHtml = monthlyDaysData.map(dayInfo => {
                        let displayStyle = '';
                        if (currentView === 'quincena1' && !dayInfo.is_quincena_1) displayStyle = 'display: none;';
                        if (currentView === 'quincena2' && !dayInfo.is_quincena_2) displayStyle = 'display: none;';
                        return `
                        <td class="data-cell ${dayInfo.is_quincena_1 ? 'quincena-1' : ''} ${dayInfo.is_quincena_2 ? 'quincena-2' : ''} ${!dayInfo.is_working_day ? 'non-working' : ''} ${!dayInfo.is_current_month ? 'other-month' : ''}"
                            data-day="${dayInfo.day}" data-date="${dayInfo.date}" style="${displayStyle}">
                            <div class="status-indicator status-n">N</div>
                        </td>
                    `}).join('');
                } else {
                    cellsHtml = monthlyDaysData.map(dayInfo => {
                        let displayStyle = '';
                        if (currentView === 'quincena1' && !dayInfo.is_quincena_1) displayStyle = 'display: none;';
                        if (currentView === 'quincena2' && !dayInfo.is_quincena_2) displayStyle = 'display: none;';
                        return `
                        <td class="data-cell ${dayInfo.is_quincena_1 ? 'quincena-1' : ''} ${dayInfo.is_quincena_2 ? 'quincena-2' : ''} ${!dayInfo.is_working_day ? 'non-working' : ''} ${!dayInfo.is_current_month ? 'other-month' : ''}"
                            data-day="${dayInfo.day}" data-date="${dayInfo.date}" style="${displayStyle}">0</td>
                    `}).join('');
                }
                // Celda totalizadora respectiva
                let totalCellHtml = '';
                if (rowType === 'Vespertina') {
                    totalCellHtml = '<td class="data-cell total-vespertina"><div class="status-indicator">0</div></td>';
                } else {
                    totalCellHtml = `<td class="data-cell total-${rowType.toLowerCase()}">0</td>`;
                }

                detailRow.innerHTML = `
                    <td class="activity-label-cell">${rowType}</td>
                    ${cellsHtml}
                    ${totalCellHtml}
                `;
                tbody.appendChild(detailRow);
            });
        });
    });
}

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

/**
 * Calcula y renderiza los totales (Suma Vespertina y ajusta Descansos por turnos)
 */
function calculateAndRenderTotals() {
    const employeeRows = Array.from(document.querySelectorAll('.employee-row')).filter(row => row.style.display !== 'none');
    const workingActivityTypes = ['B', 'P', 'TC', 'V', 'E', 'C'];
    const allDayHeaders = Array.from(document.querySelectorAll('.day-header'));
    const visibleDayHeaders = allDayHeaders.filter(header => header.style.display !== 'none');
    const visibleDates = visibleDayHeaders.map(header => header.getAttribute('data-date'));
    const totalWorkingDaysInFullMonth = allDayHeaders.filter(header =>
        header.getAttribute('data-quincena-1') === 'true' ||
        header.getAttribute('data-quincena-2') === 'true'
    ).length;

    employeeRows.forEach(employeeRow => {
        const employeeId = employeeRow.getAttribute('data-employee-id');
        const employeeData = workLogsData.find(log => log.employee_id.toString() === employeeId);

        if (!employeeData || !employeeData.daily_activities) return;

        let totalDays = 0;
        let totalVespertina = 0;
        let totalFood = 0;
        let totalFieldBonus = 0;
        let totalService = 0;
        let totalBreaks = 0;
        let totalVacations = 0;

        let foodCount = 0;
        let fieldBonusCount = 0;
        let serviceCount = 0;

        let isDoubleShiftEmployee = false;
        let halfShiftBreaksCount = 0;

        const dailyActivitiesMap = new Map(employeeData.daily_activities.map(activity => [activity.date, activity]));

        // 1. Detectar si el empleado maneja turnos dobles en este periodo
        visibleDates.forEach(dateAttr => {
            const dailyActivity = dailyActivitiesMap.get(dateAttr);
            if (dailyActivity && dailyActivity.activity_type_vespertina && dailyActivity.activity_type_vespertina !== 'N') {
                isDoubleShiftEmployee = true;
            }
        });

        // 2. Calcular acumulados
        visibleDates.forEach(dateAttr => {
            const dailyActivity = dailyActivitiesMap.get(dateAttr);

            if (dailyActivity) {
                const activityType = dailyActivity.activity_type ? dailyActivity.activity_type.toUpperCase() : 'N';
                const vType = dailyActivity.activity_type_vespertina ? dailyActivity.activity_type_vespertina.toUpperCase() : 'N';

                if (workingActivityTypes.includes(activityType)) totalDays++;

                // ⭐ CORRECCIÓN DE VACACIONES VISUALES:
                // Se cuentan por DÍA. Si CUALQUIERA de los dos turnos es VAC, suma solo 1 vez para el día completo.
                if (activityType === 'VAC' || vType === 'VAC') {
                    totalVacations++;
                }

                // Conteo Vespertina laborada
                if (workingActivityTypes.includes(vType)) totalVespertina++;

                // Lógica de Descansos ('D')
                if (isDoubleShiftEmployee) {
                    // Si es guardia, sumamos cada 'D' como medio turno
                    if (activityType === 'D') halfShiftBreaksCount++;
                    if (vType === 'D') halfShiftBreaksCount++;
                } else {
                    // Si es empleado normal, cada 'D' es un día completo
                    if (activityType === 'D') totalBreaks++;
                }

                // Cálculos de Bonos y Servicios
                const dailyFoodBonuses = dailyActivity.food_bonuses || [];
                const dailyFieldBonuses = dailyActivity.field_bonuses || [];
                const dailyServices = dailyActivity.services_list || [];

                foodCount += dailyFoodBonuses.length;
                fieldBonusCount += dailyFieldBonuses.length;
                serviceCount += dailyServices.length;

                if (canSeeAmounts) {
                    totalFood += dailyFoodBonuses.reduce((sum, bonus) => sum + Number(bonus.daily_amount || 0), 0);
                    totalFieldBonus += dailyFieldBonuses.reduce((sum, bonus) => {
                        const amount = Number(bonus.daily_amount || 0);
                        if (bonus.currency === 'USD' && bonus.usd_to_mxn_rate) return sum + amount;
                        return sum + amount;
                    }, 0);
                    totalService += dailyServices.reduce((sum, service) => sum + Number(service.amount || 0), 0);
                }
            }
        });

        // Cálculo Final de Descansos para Guardias
        if (isDoubleShiftEmployee) {
            totalBreaks = halfShiftBreaksCount / 2;
        }

        let currentRow = employeeRow.nextElementSibling;
        let vespertinaRow = null;
        let foodRow = null;
        let fieldBonusRow = null;
        let serviceRow = null;

        while (currentRow && currentRow.getAttribute('data-employee-id') === employeeId.toString() && currentRow.classList.contains('activity-row')) {
            const label = currentRow.querySelector('.activity-label-cell').textContent;
            if (label === 'Vespertina') vespertinaRow = currentRow;
            else if (label === 'Comida') foodRow = currentRow;
            else if (label === 'Bono') fieldBonusRow = currentRow;
            else if (label === 'Servicio') serviceRow = currentRow;
            currentRow = currentRow.nextElementSibling;
        }

        const activityTotalCell = employeeRow.querySelector('.total-activity .status-indicator');
        const vespertinaTotalCell = vespertinaRow ? vespertinaRow.querySelector('.total-vespertina .status-indicator') : null;
        const foodTotalCell = foodRow ? foodRow.querySelector('.total-comida') : null;
        const fieldBonusTotalCell = fieldBonusRow ? fieldBonusRow.querySelector('.total-bono') : null;
        const serviceTotalCell = serviceRow ? serviceRow.querySelector('.total-servicio') : null;

        if (activityTotalCell) activityTotalCell.textContent = totalDays;
        if (vespertinaTotalCell) vespertinaTotalCell.textContent = totalVespertina;

        if (foodTotalCell) foodTotalCell.textContent = canSeeAmounts ? totalFood.toFixed(2) : foodCount.toString();
        if (fieldBonusTotalCell) fieldBonusTotalCell.textContent = canSeeAmounts ? totalFieldBonus.toFixed(2) : fieldBonusCount.toString();
        if (serviceTotalCell) serviceTotalCell.textContent = canSeeAmounts ? totalService.toFixed(2) : serviceCount.toString();

        const vacCell = employeeRow.querySelector('.vacations-value');
        if (vacCell) vacCell.textContent = totalVacations;

        const breaksCell = employeeRow.querySelector('.breaks-value');
        if (breaksCell) breaksCell.textContent = totalBreaks;

        const utilCell = employeeRow.querySelector('.utilization-value');
        if (utilCell) {
            const utilizationRate = totalWorkingDaysInFullMonth > 0 ? (totalDays / totalWorkingDaysInFullMonth) * 100 : 0;
            const percentage = Math.round(utilizationRate);
            utilCell.textContent = `${percentage}%`;

            if (percentage < 85) {
                utilCell.style.color = '#4e6952ff';
                utilCell.style.fontWeight = 'bold';
            } else if (percentage >= 85 && percentage <= 90) {
                utilCell.style.color = '#FF4500';
            } else if (percentage > 90) {
                utilCell.style.color = '#B22222';
            }
        }
    });
}



/**
 * Renderiza los datos de workLog en la tabla (Controla colores separados para Matutina y Vespertina)
 */
function renderEmployeeWorkLog() {
    if (!workLogsData || workLogsData.length === 0) {
        calculateAndRenderTotals();
        return;
    }

    workLogsData.forEach(employeeData => {
        const employeeRow = document.querySelector(`.employee-row[data-employee-id="${employeeData.employee_id}"]`);
        if (!employeeRow) return;

        const activityRow = employeeRow;
        let currentRow = employeeRow.nextElementSibling;

        let vespertinaRow = null;
        let comidaRow = null;
        let fieldBonusRow = null;
        let servicioRow = null;

        while (currentRow && currentRow.getAttribute('data-employee-id') === employeeData.employee_id.toString() && currentRow.classList.contains('activity-row')) {
            const label = currentRow.querySelector('.activity-label-cell').textContent;
            if (label === 'Vespertina') vespertinaRow = currentRow;
            else if (label === 'Comida') comidaRow = currentRow;
            else if (label === 'Bono') fieldBonusRow = currentRow;
            else if (label === 'Servicio') servicioRow = currentRow;
            currentRow = currentRow.nextElementSibling;
        }

        const activityCells = Array.from(activityRow.querySelectorAll('.data-cell')).filter(cell => cell.getAttribute('data-date'));
        const vespertinaCells = vespertinaRow ? Array.from(vespertinaRow.querySelectorAll('.data-cell')).filter(cell => cell.getAttribute('data-date')) : [];
        const comidaCells = comidaRow ? Array.from(comidaRow.querySelectorAll('.data-cell')).filter(cell => cell.getAttribute('data-date')) : [];
        const fieldBonusCells = fieldBonusRow ? Array.from(fieldBonusRow.querySelectorAll('.data-cell')).filter(cell => cell.getAttribute('data-date')) : [];
        const servicioCells = servicioRow ? Array.from(servicioRow.querySelectorAll('.data-cell')).filter(cell => cell.getAttribute('data-date')) : [];

        const dailyActivitiesMap = new Map();
        if (employeeData.daily_activities) {
            employeeData.daily_activities.forEach(activity => {
                dailyActivitiesMap.set(activity.date, activity);
            });
        }

        let hasVespertinaItemsInPeriod = false;
        let hasFoodItemsInPeriod = false;
        let hasFieldBonusItemsInPeriod = false;
        let hasServiceItemsInPeriod = false;

        const visibleDayHeaders = Array.from(document.querySelectorAll('.day-header')).filter(header => header.style.display !== 'none');
        const visibleDates = visibleDayHeaders.map(header => header.getAttribute('data-date'));

        visibleDates.forEach(dateAttr => {
            const dailyActivity = dailyActivitiesMap.get(dateAttr);
            if (dailyActivity) {
                if (dailyActivity.activity_type_vespertina && dailyActivity.activity_type_vespertina !== 'N') hasVespertinaItemsInPeriod = true;
                if (dailyActivity.food_bonuses && dailyActivity.food_bonuses.length > 0) hasFoodItemsInPeriod = true;
                if (dailyActivity.field_bonuses && dailyActivity.field_bonuses.length > 0) hasFieldBonusItemsInPeriod = true;
                if (dailyActivity.services_list && dailyActivity.services_list.length > 0) hasServiceItemsInPeriod = true;
            }
        });

        if (vespertinaRow) vespertinaRow.style.display = hasVespertinaItemsInPeriod ? '' : 'none';
        if (comidaRow) comidaRow.style.display = hasFoodItemsInPeriod ? '' : 'none';
        if (fieldBonusRow) fieldBonusRow.style.display = hasFieldBonusItemsInPeriod ? '' : 'none';
        if (servicioRow) servicioRow.style.display = hasServiceItemsInPeriod ? '' : 'none';

        if (vespertinaRow) vespertinaRow.setAttribute('data-has-items', hasVespertinaItemsInPeriod ? 'true' : 'false');
        if (comidaRow) comidaRow.setAttribute('data-has-items', hasFoodItemsInPeriod ? 'true' : 'false');
        if (fieldBonusRow) fieldBonusRow.setAttribute('data-has-items', hasFieldBonusItemsInPeriod ? 'true' : 'false');
        if (servicioRow) servicioRow.setAttribute('data-has-items', hasServiceItemsInPeriod ? 'true' : 'false');

        let visibleDetailRows = 1;
        if (hasVespertinaItemsInPeriod) visibleDetailRows++;
        if (hasFoodItemsInPeriod) visibleDetailRows++;
        if (hasFieldBonusItemsInPeriod) visibleDetailRows++;
        if (hasServiceItemsInPeriod) visibleDetailRows++;

        const mainLabelCell = employeeRow.querySelector('.activity-main-label');
        if (mainLabelCell) {
            mainLabelCell.textContent = hasVespertinaItemsInPeriod ? 'Matutina' : 'Actividad';
        }

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

        activityCells.forEach((cell, index) => {
            const dateAttr = cell.getAttribute('data-date');
            if (!dateAttr) return;

            const dailyActivity = dailyActivitiesMap.get(dateAttr);
            const statusIndicator = cell.querySelector('.status-indicator');

            if (statusIndicator) {
                statusIndicator.textContent = 'N';
                statusIndicator.className = 'status-indicator status-n';
                statusIndicator.title = 'No hay actividad registrada';
                statusIndicator.classList.remove('approved-border', 'reviewed-border', 'rejected-border', 'under-review-border');
            }
            if (vespertinaCells[index]) {
                const vInd = vespertinaCells[index].querySelector('.status-indicator');
                if (vInd) {
                    vInd.textContent = 'N';
                    vInd.className = 'status-indicator status-n';
                    vInd.title = 'No hay actividad registrada';
                    vInd.classList.remove('approved-border', 'reviewed-border', 'rejected-border', 'under-review-border');
                }
            }
            if (comidaCells[index]) comidaCells[index].textContent = '0';
            if (fieldBonusCells[index]) fieldBonusCells[index].textContent = '0';
            if (servicioCells[index]) servicioCells[index].textContent = '0';

            if (dailyActivity) {

                // Matutina
                if (statusIndicator && dailyActivity.activity_type) {
                    const activityType = dailyActivity.activity_type.toUpperCase();
                    statusIndicator.textContent = activityType;
                    statusIndicator.className = `status-indicator status-${activityType.toLowerCase()}`;
                    statusIndicator.title = dailyActivity.activity_description || '';

                    const matStatus = (dailyActivity.activity_status || 'under_review').toLowerCase();

                    if (matStatus === 'approved') statusIndicator.classList.add('approved-border');
                    else if (matStatus === 'reviewed') statusIndicator.classList.add('reviewed-border');
                    else if (matStatus === 'rejected') statusIndicator.classList.add('rejected-border');
                    else statusIndicator.classList.add('under-review-border');
                }

                // Vespertina
                if (vespertinaCells[index] && dailyActivity.activity_type_vespertina) {
                    const vInd = vespertinaCells[index].querySelector('.status-indicator');
                    if (vInd && dailyActivity.activity_type_vespertina !== 'N') {
                        const vType = dailyActivity.activity_type_vespertina.toUpperCase();
                        vInd.textContent = vType;
                        vInd.className = `status-indicator status-${vType.toLowerCase()}`;
                        vInd.title = dailyActivity.activity_description_vespertina || '';

                        // ⭐ CORRECCIÓN: Evaluado estrictamente con activity_status_vespertina
                        const vespStatus = (dailyActivity.activity_status_vespertina || 'under_review').toLowerCase();

                        if (vespStatus === 'approved') vInd.classList.add('approved-border');
                        else if (vespStatus === 'reviewed') vInd.classList.add('reviewed-border');
                        else if (vespStatus === 'rejected') vInd.classList.add('rejected-border');
                        else vInd.classList.add('under-review-border');
                    }
                }

                // Bonos y Servicios (se mantiene idéntico)
                if (canSeeAmounts) {
                    if (comidaCells[index]) {
                        const totalFoodBonus = dailyActivity.food_bonuses ? dailyActivity.food_bonuses.reduce((sum, bonus) => sum + Number(bonus.daily_amount || 0), 0) : 0;
                        comidaCells[index].textContent = totalFoodBonus > 0 ? totalFoodBonus.toFixed(2) : '0';
                        comidaCells[index].title = dailyActivity.food_bonuses && dailyActivity.food_bonuses.length > 0 ? dailyActivity.food_bonuses.map(b => `${b.num_daily} comidas`).join(', ') : '';
                    }

                    if (fieldBonusCells[index]) {
                        const totalFieldBonus = dailyActivity.field_bonuses ? dailyActivity.field_bonuses.reduce((sum, bonus) => {
                            const amount = Number(bonus.daily_amount || 0);
                            if (bonus.currency === 'USD' && bonus.usd_to_mxn_rate) return sum + amount;
                            return sum + amount;
                        }, 0) : 0;
                        fieldBonusCells[index].textContent = totalFieldBonus > 0 ? totalFieldBonus.toFixed(2) : '0';
                        fieldBonusCells[index].title = dailyActivity.field_bonuses && dailyActivity.field_bonuses.length > 0 ? dailyActivity.field_bonuses.map(b => `${b.bonus_type} (${b.daily_amount} ${b.currency})`).join(', ') : '';
                    }

                    if (servicioCells[index]) {
                        const totalServiceAmount = dailyActivity.services_list ? dailyActivity.services_list.reduce((sum, service) => sum + Number(service.amount || 0), 0) : 0;
                        servicioCells[index].textContent = totalServiceAmount > 0 ? totalServiceAmount.toFixed(2) : '0';
                        servicioCells[index].title = dailyActivity.services_list && dailyActivity.services_list.length > 0 ? dailyActivity.services_list.map(s => s.service_name).join(', ') : '';
                    }
                } else {
                    if (comidaCells[index]) {
                        const foodBonuses = dailyActivity.food_bonuses || [];
                        if (foodBonuses.length > 0) {
                            const totalNumDaily = foodBonuses.reduce((sum, bonus) => sum + Number(bonus.num_daily || 0), 0);
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
                            servicioCells[index].textContent = services.length > 1 ? services.length : (services[0].service_identifier || 'Servicio');
                            servicioCells[index].title = services.map(s => `${s.service_name} (${s.service_identifier})`).join(', ');
                        } else {
                            servicioCells[index].textContent = '0';
                            servicioCells[index].title = '';
                        }
                    }
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



/**
 * DRAG TO SCROLL — Scroll con arrastre tipo "mano"
 * Aplicar a cualquier contenedor con scroll
 */
function enableDragToScroll(containerSelector) {
    const container = document.querySelector(containerSelector);
    if (!container) return;

    let isDown = false;
    let startX, startY, scrollLeft, scrollTop;

    container.addEventListener('mousedown', (e) => {
        // No activar si el click es sobre un botón, input, select, etc.
        if (e.target.closest('button, input, select, a, .status-indicator')) return;

        isDown = true;
        container.classList.add('is-grabbing');
        startX = e.pageX - container.offsetLeft;
        startY = e.pageY - container.offsetTop;
        scrollLeft = container.scrollLeft;
        scrollTop = container.scrollTop;
        e.preventDefault();
    });

    container.addEventListener('mouseleave', () => {
        isDown = false;
        container.classList.remove('is-grabbing');
    });

    container.addEventListener('mouseup', () => {
        isDown = false;
        container.classList.remove('is-grabbing');
    });

    container.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - container.offsetLeft;
        const y = e.pageY - container.offsetTop;
        const walkX = (x - startX) * 1.2; // velocidad horizontal
        const walkY = (y - startY) * 1.2; // velocidad vertical
        container.scrollLeft = scrollLeft - walkX;
        container.scrollTop = scrollTop - walkY;
    });

    // Touch support (móvil)
    let touchStartX, touchStartY, touchScrollLeft, touchScrollTop;

    container.addEventListener('touchstart', (e) => {
        touchStartX = e.touches[0].pageX - container.offsetLeft;
        touchStartY = e.touches[0].pageY - container.offsetTop;
        touchScrollLeft = container.scrollLeft;
        touchScrollTop = container.scrollTop;
    }, { passive: true });

    container.addEventListener('touchmove', (e) => {
        const x = e.touches[0].pageX - container.offsetLeft;
        const y = e.touches[0].pageY - container.offsetTop;
        container.scrollLeft = touchScrollLeft - (x - touchStartX);
        container.scrollTop = touchScrollTop - (y - touchStartY);
    }, { passive: true });
}

// Inicializar en tu tabla
enableDragToScroll('.table-container');
























