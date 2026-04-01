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
// 🆕 Nuevas variables globales
allEmployeeRows = [];
allSquadRows = [];
let isFiltersOpen = false;
let auxiliarPalState = 'inactive';

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

        allEmployeeRows = Array.from(document.querySelectorAll('.employee-row'));
        allSquadRows = Array.from(document.querySelectorAll('.squad-group-row'));

        if (typeof canSeeFilters !== 'undefined' && canSeeFilters) {
            const assignmentFilter = document.getElementById('assignment-filter');
            if (assignmentFilter) {
                assignmentFilter.value = 'assigned';
            }
            populatePositionFilter();
        }

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

    // ✅ Inicializar Event Listeners de Filtro usando 'area-filter'
    if (typeof canSeeFilters !== 'undefined' && canSeeFilters) {
        document.getElementById('toggle-filters-btn').addEventListener('click', toggleFilters);
        document.getElementById('assignment-filter').addEventListener('change', applyFilters);

        document.getElementById('area-filter').addEventListener('change', function () {
            populatePositionFilter();
            applyFilters();
        });

        document.getElementById('position-filter').addEventListener('change', applyFilters);
        document.getElementById('employee-search').addEventListener('input', applyFilters);
    }
}

// Variables globales para el modal de empleado
let currentEmployeeModal = null;

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

function openEmployeeDetailModal(employeeId, employeeName, month = null, year = null) {
    const modal = document.getElementById('employee-detail-modal');
    const subtitle = document.getElementById('employee-modal-subtitle');
    const content = document.getElementById('employee-detail-content');

    modal.style.display = 'block';
    subtitle.textContent = `Cargando información de ${employeeName}`;
    content.innerHTML = `
        <div class="loading-spinner-container">
            <div class="loading-spinner-lg"></div>
            <div class="loading-message">Cargando calendario...</div>
        </div>
    `;

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

                if (typeof initializeModalCalendarScripts === 'function') {
                    initializeModalCalendarScripts(employeeId);
                } else {
                    console.error("Error: La función initializeModalCalendarScripts no está definida.");
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

function closeEmployeeDetailModal() {
    const modal = document.getElementById('employee-detail-modal');
    modal.style.display = 'none';
    currentEmployeeModal = null;
}

function initializeEmployeeModalListeners() {
    document.querySelector('.employee-detail-close-btn').addEventListener('click', closeEmployeeDetailModal);

    document.getElementById('employee-detail-modal').addEventListener('click', function (event) {
        if (event.target === this) {
            closeEmployeeDetailModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeEmployeeDetailModal();
        }
    });
}

// 🆕 LÓGICA DE FILTROS

function toggleFilters() {
    const filtersContainer = document.getElementById('filters-container');
    const toggleButton = document.getElementById('toggle-filters-btn');

    if (isFiltersOpen) {
        filtersContainer.style.display = 'none';
        toggleButton.innerHTML = '<i class="fas fa-filter"></i> Abrir Filtros';
    } else {
        filtersContainer.style.display = 'flex';
        toggleButton.innerHTML = '<i class="fas fa-times"></i> Cerrar Filtros';
    }
    isFiltersOpen = !isFiltersOpen;
}


/**
 * ✅ NUEVA FUNCIÓN: Llena el select de cargos basándose en el área
 */
function populatePositionFilter() {
    const areaFilter = document.getElementById('area-filter');
    const positionFilter = document.getElementById('position-filter');

    if (!areaFilter || !positionFilter) return;

    const currentPosition = positionFilter.value;
    const selectedArea = areaFilter.value;

    const uniquePositions = new Set();

    employees.forEach(employee => {
        // Leemos desde el array de objetos traídos por AJAX/Blade
        const empArea = employee.area ? employee.area.name : '';

        if (!selectedArea || empArea === selectedArea) {
            if (employee.position) {
                uniquePositions.add(employee.position);
            }
        }
    });

    const sortedPositions = Array.from(uniquePositions).sort();
    positionFilter.innerHTML = '<option value="">Todos los Cargos</option>';

    sortedPositions.forEach(position => {
        const option = document.createElement('option');
        option.value = position;
        option.textContent = position;
        positionFilter.appendChild(option);
    });

    if (currentPosition && uniquePositions.has(currentPosition)) {
        positionFilter.value = currentPosition;
    } else {
        positionFilter.value = "";
    }
}

/**
 * ✅ Aplica los filtros usando ÁREA, CARGO, asignación y búsqueda.
 */
function applyFilters() {
    const assignmentFilterElem = document.getElementById('assignment-filter');
    const areaFilterElem = document.getElementById('area-filter');
    const positionFilterElem = document.getElementById('position-filter');
    const searchFilterElem = document.getElementById('employee-search');

    const assignmentFilter = assignmentFilterElem ? assignmentFilterElem.value : 'all';
    const areaFilter = areaFilterElem ? areaFilterElem.value : '';
    const positionFilter = positionFilterElem ? positionFilterElem.value : '';
    const searchFilter = searchFilterElem ? searchFilterElem.value.toLowerCase().trim() : '';

    const isUsingManualFilters = (
        areaFilter !== '' ||
        positionFilter !== '' ||
        searchFilter !== '' ||
        (assignmentFilterElem !== null && assignmentFilter === 'all')
    );

    allEmployeeRows.forEach(row => {
        const area = row.getAttribute('data-area'); // Usamos data-area
        const employeeId = row.getAttribute('data-employee-id');

        const employeeData = employees.find(e => e.id.toString() === employeeId);

        const employeeName = employeeData ? employeeData.full_name.toLowerCase().trim() : '';
        const employeeNumber = employeeData ? employeeData.employee_number.toString().toLowerCase() : '';
        const employeePosition = employeeData ? employeeData.position : '';

        const employeeJobTitle = employeeData && employeeData.job_title ? employeeData.job_title.trim().toUpperCase() : '';
        const isAuxiliarPal = employeeJobTitle.includes('AUXILIAR PAL');

        const employeeAssignment = loadChartAssignments.find(a => a.employee_id.toString() === employeeId);
        const isReviewerForEmployee = employeeAssignment && employeeAssignment.reviewer_id === currentUserId;
        const isApproverForEmployee = employeeAssignment && employeeAssignment.approver_id === currentUserId;
        const isAssigned = isReviewerForEmployee || isApproverForEmployee;

        // --- APLICACIÓN DE REGLAS ---
        const matchesArea = !areaFilter || area === areaFilter;
        const matchesPosition = !positionFilter || employeePosition === positionFilter;
        const matchesSearch = !searchFilter || employeeName.includes(searchFilter) || employeeNumber.includes(searchFilter);

        const matchesAssignment = !assignmentFilterElem ? true : (assignmentFilter === 'all' || (assignmentFilter === 'assigned' && isAssigned));

        let matchesAuxiliar = true;

        if (auxiliarPalState === 'active') {
            matchesAuxiliar = isAuxiliarPal;
        } else {
            if (isAuxiliarPal) {
                if (!isUsingManualFilters) {
                    matchesAuxiliar = false;
                }
            }
        }

        // --- RESULTADO FINAL ---
        const isVisible = matchesArea && matchesPosition && matchesSearch && matchesAssignment && matchesAuxiliar;

        row.style.display = isVisible ? '' : 'none';

        let nextRow = row.nextElementSibling;
        while (nextRow && nextRow.classList.contains('activity-row') && nextRow.getAttribute('data-employee-id') === row.getAttribute('data-employee-id')) {
            if (!isVisible) {
                nextRow.style.display = 'none';
            } else {
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

    if (typeof currentView !== 'undefined') {
        if (currentView === 'quincena1') showQuincena(1);
        else if (currentView === 'quincena2') showQuincena(2);
        else showFullMonth();
    }

    calculateAndRenderTotals();
}

function initializeAutoRefresh() {
    autoRefreshInterval = setInterval(checkForUpdates, 10000);
    document.addEventListener('visibilitychange', handleVisibilityChange);
    lastUpdateTime = new Date();
}

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
            await refreshData();
        }
    } catch (error) {
        console.error('Error verificando actualizaciones:', error);
    }
}

async function refreshData() {
    try {
        stopAutoRefresh();

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

        workLogsData = data.workLogsData;
        fortnightlyConfig = data.fortnightlyConfig;
        loadChartAssignments = data.loadChartAssignments;
        canSeeAmounts = data.canSeeAmounts;
        userPermissions = data.userPermissions;
        monthlyDays = data.monthlyDays;
        employees = data.employees;

        await loadMonthData(true);

    } catch (error) {
        console.error('Error refreshing data:', error);
        initializeAutoRefresh();
    }
}

function setupEventListeners() {
    document.getElementById("prev-period").addEventListener("click", () => navigateToPreviousMonth());
    document.getElementById("next-period").addEventListener("click", () => navigateToNextMonth());

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

    const backToCalendarBtn = document.getElementById("back-to-calendar");
    if (backToCalendarBtn) {
        backToCalendarBtn.addEventListener("click", function (e) {
            e.preventDefault();
            window.location.href = "/rh/loadchart/calendar";
        });
    }

    document.getElementById('approval-table-body').addEventListener('click', handleTableClick);

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
        const row = cell.closest('.employee-row') || cell.closest('.activity-row');

        if (!row) return;

        const employeeId = row.getAttribute('data-employee-id');
        const date = cell.getAttribute('data-date');

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

    if (dailyActivity.activity_type) {
        const actStatus = (dailyActivity.activity_status ?? 'under_review').toLowerCase();
        let extraDetails = '';

        if (dailyActivity.activity_type === 'P' && dailyActivity.well_name) {
            extraDetails = `<span class="chip-label">Pozo</span> <strong style="color:#1a2035;">${dailyActivity.well_name}</strong>`;
        } else if (dailyActivity.activity_type === 'C' && dailyActivity.commissioned_to) {
            extraDetails = `<span class="chip-label">Área comisionada</span> <strong style="color:#1a2035;">${dailyActivity.commissioned_to}</strong>`;
        } else if (dailyActivity.activity_type === 'V') {
            extraDetails = `Destino: <strong style="color:#1a2035;">${dailyActivity.travel_destination || 'N/A'}</strong> &nbsp;·&nbsp; Motivo: <strong style="color:#1a2035;">${dailyActivity.travel_reason || 'N/A'}</strong>`;

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

    if (dailyActivity.activity_type_vespertina && dailyActivity.activity_type_vespertina !== 'N') {
        const vStatus = (dailyActivity.activity_status_vespertina || 'under_review').toLowerCase();
        let vDetails = '';

        if (dailyActivity.activity_type_vespertina === 'B' && dailyActivity.base_activity_description) {
            vDetails = `Actividad: <strong style="color:#1a2035;">${dailyActivity.base_activity_description}</strong>`;
        } else if (dailyActivity.activity_type_vespertina === 'P' && dailyActivity.well_name) {
            vDetails = `Pozo: <strong style="color:#1a2035;">${dailyActivity.well_name}</strong>`;
        } else if (dailyActivity.activity_type_vespertina === 'V') {
            vDetails = `Destino: <strong style="color:#1a2035;">${dailyActivity.travel_destination || 'N/A'}</strong> &nbsp;·&nbsp; Motivo: <strong style="color:#1a2035;">${dailyActivity.travel_reason || 'N/A'}</strong>`;

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

    const isSelectorDisabled = approvalSelector.startsWith('<span') || approvalSelector.includes('disabled');
    let isLocked = false;

    if (isSelectorDisabled || (!isReviewer && !isApprover)) {
        isLocked = true;
    }
    if (currentStatusLower === 'approved' && !isApprover) {
        isLocked = true;
    }
    if (currentStatusLower === 'rejected' && isReviewer && !isApprover) {
        isLocked = true;
    }
    if (isReviewer || isApprover) {
        isLocked = false;
    }
    if (approvalSelector.startsWith('<span')) {
        isLocked = true;
    }

    const commentValue = rejectionReason || '';
    const amountCellContent = amount !== null ? `${amount}` : (canSeeAmounts ? 'N/A' : '');

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

    const selectElement = row.querySelector('.item-approval-selector');
    const textareaElement = row.querySelector('.modal-comment');

    if (selectElement && textareaElement) {
        if (isLocked) {
            textareaElement.disabled = true;
        } else {
            if (selectElement.value !== 'rejected') {
                textareaElement.disabled = true;
            }
            selectElement.addEventListener('change', function () {
                if (this.value === 'rejected') {
                    textareaElement.disabled = false;
                    textareaElement.focus();
                } else {
                    textareaElement.disabled = true;
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

    const baseOptions = [
        { value: 'under_review', text: 'Bajo Revisión', disabled: false },
        { value: 'reviewed', text: 'Revisado', disabled: false },
        { value: 'approved', text: 'Aprobado', disabled: false },
        { value: 'rejected', text: 'Rechazado', disabled: false }
    ];

    if (isApprover) {
        options = baseOptions.map(option => ({ ...option, disabled: false }));

        if (isItemApproved) {
            options.find(o => o.value === 'reviewed').disabled = true;
            options.find(o => o.value === 'under_review').disabled = true;
            options.find(o => o.value === 'rejected').disabled = false;
        } else if (isItemRejected) {
            options.find(o => o.value === 'rejected').disabled = false;
        } else {
            options.find(o => o.value === 'under_review').disabled = (isItemReviewed);
        }
    } else if (isReviewer) {
        if (isItemApproved) {
            const approvedOption = baseOptions.find(o => o.value === 'approved');
            return `<span class="badge badge-success">${approvedOption.text} (Bloqueado)</span>`;
        } else if (isItemRejected) {
            const rejectedOption = baseOptions.find(o => o.value === 'rejected');
            return `<span class="badge badge-danger">${rejectedOption.text} (Esperando Corrección)</span>`;
        } else {
            options = [
                { value: 'under_review', text: 'Bajo Revisión', disabled: false },
                { value: 'reviewed', text: 'Revisado', disabled: false },
                { value: 'rejected', text: 'Rechazado', disabled: false }
            ];
            options = options.filter(opt => opt.value !== 'approved');
            if (isItemReviewed) {
                options.find(o => o.value === 'under_review').disabled = true;
            }
        }
    } else {
        const statusClassMap = { 'approved': 'success', 'reviewed': 'primary', 'rejected': 'danger', 'under_review': 'info' };
        const statusText = baseOptions.find(o => o.value === currentStatus)?.text || 'Desconocido';
        const statusClass = statusClassMap[currentStatus] || 'info';
        return `<span class="badge badge-${statusClass}">${statusText}</span>`;
    }

    let html = `<select class="form-control item-approval-selector">`;

    const finalOptions = baseOptions.map(baseOpt => {
        const currentOpt = options.find(o => o.value === baseOpt.value) || baseOpt;

        let isDisabled = currentOpt.disabled;
        let isSelected = baseOpt.value === currentStatus;

        if (!isApprover && (isItemApproved || isItemRejected) && baseOpt.value !== currentStatus) {
            isDisabled = true;
        }

        if ((isItemReviewed || isItemApproved) && baseOpt.value === 'under_review') {
            isDisabled = true;
        }

        return {
            value: baseOpt.value,
            text: baseOpt.text,
            disabled: isDisabled,
            selected: isSelected
        };
    }).filter(opt => isApprover || isReviewer ? opt.value !== 'approved' || isApprover : true);

    if (isApprover) {
        finalOptions.forEach(option => {
            let isDisabled = option.disabled;
            if (isItemApproved && (option.value === 'reviewed' || option.value === 'under_review')) {
                isDisabled = true;
            }
            html += `<option value="${option.value}" ${option.selected ? 'selected' : ''} ${isDisabled ? 'disabled' : ''}>${option.text}</option>`;
        });
    } else if (isReviewer) {
        baseOptions.filter(opt => opt.value !== 'approved').forEach(option => {
            const currentOpt = finalOptions.find(f => f.value === option.value);
            if (currentOpt) {
                let isDisabled = currentOpt.disabled;
                if (isItemReviewed && option.value === 'under_review') {
                    isDisabled = true;
                }
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

async function updateDailyItemsStatus(employeeId, changes) {
    try {
        showLoadingState();

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

        hideLoadingState();
        hideModalSavingState();

        if (data.success) {
            let message = data.message;
            if (data.rejections_sent) {
                message = "Estados actualizados correctamente. Se ha notificado el rechazo al empleado vía correo.";
            } else {
                message = "Estados actualizados correctamente.";
            }

            showSwalNotification('Éxito', message, 'success');
            document.getElementById('approvalModal').style.display = 'none';
            await loadMonthData(true);
        } else {
            showSwalNotification('Error', data.message, 'error');
        }
    } catch (error) {
        console.error('Error al actualizar el estado de los ítems:', error);
        hideLoadingState();
        hideModalSavingState();
        showSwalNotification('Error Crítico', 'Error al guardar los cambios: ' + error.message, 'error');
    }
}

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
    document.querySelectorAll('.modal-approval-close, .modal-approval-close-btn').forEach(btn => btn.disabled = false);
}

function setupEmployeeRowListeners() {
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
            const q1EndPlusDay = new Date(q1End);
            q1EndPlusDay.setDate(q1EndPlusDay.getDate() + 1);
            return actDate >= q1Start && actDate < q1EndPlusDay;
        } else if (fortnight === 'quincena2' && q2Start && q2End) {
            const q2EndPlusDay = new Date(q2End);
            q2EndPlusDay.setDate(q2EndPlusDay.getDate() + 1);
            return actDate >= q2Start && actDate < q2EndPlusDay;
        }
        return false;
    });
}

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
            await loadMonthData(true);
        } else {
            showSwalNotification('Error', `Error al actualizar: ${data.message}`, 'error');
        }
    } catch (error) {
        console.error('Error saving approval status:', error);
        hideLoadingState();
        showSwalNotification('Error Crítico', 'Error al guardar el estado de aprobación', 'error');
    }
}

function showSwalNotification(title, message, icon) {
    Swal.fire({
        title: title,
        text: message,
        icon: icon,
        confirmButtonText: 'Aceptar'
    });
}

function showNotification(message, type = 'info') {
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
    if (e.target.closest('.employee-info-cell')) {
        const employeeRow = e.target.closest('.employee-row');
        if (employeeRow) {
            // toggleActivityRows(employeeRow);
        }
    }
});

function toggleActivityRows(employeeRow) {
    const allRows = Array.from(employeeRow.parentElement.children);
    const startIndex = allRows.indexOf(employeeRow);
    let activityRowCount = 0;

    for (let i = startIndex + 1; i < allRows.length; i++) {
        if (allRows[i].classList.contains('activity-row') && allRows[i].getAttribute('data-employee-id') === employeeRow.getAttribute('data-employee-id')) {
            if (allRows[i].style.display !== 'none') {
                allRows[i].classList.toggle('hidden');
                activityRowCount++;
            }
        } else if (allRows[i].classList.contains('employee-row') || allRows[i].classList.contains('squad-group-row')) {
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

        workLogsData = data.workLogsData;
        fortnightlyConfig = data.fortnightlyConfig;
        loadChartAssignments = data.loadChartAssignments;
        canSeeAmounts = data.canSeeAmounts;
        userPermissions = data.userPermissions;
        monthlyDays = data.monthlyDays;
        employees = data.employees;

        updateTableStructure(data.monthlyDays, data.employees);
        updatePeriodInfo();

        if (currentView === 'quincena1') {
            showQuincena(1);
        } else if (currentView === 'quincena2') {
            showQuincena(2);
        } else {
            showFullMonth();
        }

        allEmployeeRows = Array.from(document.querySelectorAll('.employee-row'));
        allSquadRows = Array.from(document.querySelectorAll('.squad-group-row'));

        // ✅ RECONSTRUIMOS EL SELECT DE CARGOS AL CARGAR DATOS
        if (typeof canSeeFilters !== 'undefined' && canSeeFilters) {
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

function updateTableStructure(monthlyDays, employees) {
    updateDaysHeader(monthlyDays);
    updateTableBody(employees, monthlyDays);
    renderEmployeeWorkLog();
}

function updateDaysHeader(monthlyDays) {
    const daysColumnsHeader = document.getElementById('days-columns');
    const daysHeaderRow = document.getElementById('days-header-row');

    if (daysColumnsHeader) {
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

            if (currentView === 'quincena1' && !dayInfo.is_quincena_1) th.style.display = 'none';
            if (currentView === 'quincena2' && !dayInfo.is_quincena_2) th.style.display = 'none';

            daysHeaderRow.appendChild(th);
        });
    }
}

/**
 * ✅ CONSTRUCCIÓN DE LA TABLA DINÁMICA CON data-area
 */
function updateTableBody(employeesData, monthlyDaysData) {
    const tbody = document.getElementById('approval-table-body');
    if (!tbody) return;

    tbody.innerHTML = '';

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

            // ✅ DEFINIR EL ÁREA AQUÍ PARA LOS DATA-ATTRIBUTES
            const empArea = employee.area ? employee.area.name : '';

            let mainRow = document.createElement('tr');
            mainRow.className = 'employee-row';
            mainRow.setAttribute('data-employee-id', employee.id);
            mainRow.setAttribute('data-area', empArea); // ✅ PONEMOS data-area

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

            const rowTypes = ['Vespertina', 'Comida', 'Bono', 'Servicio'];
            rowTypes.forEach(rowType => {
                let detailRow = document.createElement('tr');
                detailRow.className = 'activity-row';
                detailRow.setAttribute('data-employee-id', employee.id);
                detailRow.setAttribute('data-area', empArea); // ✅ PONEMOS data-area

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

function showQuincena(quincena) {
    const dayHeaders = document.querySelectorAll(".approval-table .day-header");
    const allRows = document.querySelectorAll(".approval-table tbody tr");
    const daysColumnsHeader = document.getElementById("days-columns");

    if (!dayHeaders.length || !daysColumnsHeader) return;

    let visibleDays = 0;
    const targetAttr = quincena === 1 ? 'data-quincena-1' : 'data-quincena-2';

    dayHeaders.forEach((header, index) => {
        const shouldShow = header.getAttribute(targetAttr) === 'true';

        header.style.display = shouldShow ? "" : "none";
        if (shouldShow) visibleDays++;

        allRows.forEach((row) => {
            const dataCells = row.querySelectorAll(".data-cell");
            const dayIndexInRow = index + 2;

            if (row.classList.contains('activity-row') || row.classList.contains('employee-row')) {
                const dayCell = Array.from(row.children).find(cell => cell.getAttribute('data-day') === header.getAttribute('data-day') && cell.getAttribute('data-date') === header.getAttribute('data-date'));

                if (dayCell) {
                    if (row.style.display !== 'none') {
                        dayCell.style.display = shouldShow ? "" : "none";
                    }
                }
            }
        });
    });

    daysColumnsHeader.colSpan = visibleDays;
    daysColumnsHeader.textContent = `Días del Período (${visibleDays} días)`;

    calculateAndRenderTotals();
}

function showFullMonth() {
    const dayHeaders = document.querySelectorAll(".approval-table .day-header");
    const allRows = document.querySelectorAll(".approval-table tbody tr");
    const daysColumnsHeader = document.getElementById("days-columns");

    if (!dayHeaders.length || !daysColumnsHeader) return;

    dayHeaders.forEach((header) => {
        header.style.display = "";

        allRows.forEach((row) => {
            if (row.classList.contains('activity-row') || row.classList.contains('employee-row')) {
                const dayCell = Array.from(row.children).find(cell => cell.getAttribute('data-day') === header.getAttribute('data-day') && cell.getAttribute('data-date') === header.getAttribute('data-date'));

                if (dayCell) {
                    if (row.style.display !== 'none') {
                        dayCell.style.display = "";
                    }
                }
            }
        });
    });

    daysColumnsHeader.colSpan = dayHeaders.length;
    daysColumnsHeader.textContent = `Días del Período (${dayHeaders.length} días)`;

    calculateAndRenderTotals();
}

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

        visibleDates.forEach(dateAttr => {
            const dailyActivity = dailyActivitiesMap.get(dateAttr);
            if (dailyActivity && dailyActivity.activity_type_vespertina && dailyActivity.activity_type_vespertina !== 'N') {
                isDoubleShiftEmployee = true;
            }
        });

        visibleDates.forEach(dateAttr => {
            const dailyActivity = dailyActivitiesMap.get(dateAttr);

            if (dailyActivity) {
                const activityType = dailyActivity.activity_type ? dailyActivity.activity_type.toUpperCase() : 'N';
                const vType = dailyActivity.activity_type_vespertina ? dailyActivity.activity_type_vespertina.toUpperCase() : 'N';

                if (workingActivityTypes.includes(activityType)) totalDays++;

                if (activityType === 'VAC' || vType === 'VAC') {
                    totalVacations++;
                }

                if (workingActivityTypes.includes(vType)) totalVespertina++;

                if (isDoubleShiftEmployee) {
                    if (activityType === 'D') halfShiftBreaksCount++;
                    if (vType === 'D') halfShiftBreaksCount++;
                } else {
                    if (activityType === 'D') totalBreaks++;
                }

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

                if (vespertinaCells[index] && dailyActivity.activity_type_vespertina) {
                    const vInd = vespertinaCells[index].querySelector('.status-indicator');
                    if (vInd && dailyActivity.activity_type_vespertina !== 'N') {
                        const vType = dailyActivity.activity_type_vespertina.toUpperCase();
                        vInd.textContent = vType;
                        vInd.className = `status-indicator status-${vType.toLowerCase()}`;
                        vInd.title = dailyActivity.activity_description_vespertina || '';

                        const vespStatus = (dailyActivity.activity_status_vespertina || 'under_review').toLowerCase();

                        if (vespStatus === 'approved') vInd.classList.add('approved-border');
                        else if (vespStatus === 'reviewed') vInd.classList.add('reviewed-border');
                        else if (vespStatus === 'rejected') vInd.classList.add('rejected-border');
                        else vInd.classList.add('under-review-border');
                    }
                }

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

function enableDragToScroll(containerSelector) {
    const container = document.querySelector(containerSelector);
    if (!container) return;

    let isDown = false;
    let startX, startY, scrollLeft, scrollTop;

    container.addEventListener('mousedown', (e) => {
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
        const walkX = (x - startX) * 1.2;
        const walkY = (y - startY) * 1.2;
        container.scrollLeft = scrollLeft - walkX;
        container.scrollTop = scrollTop - walkY;
    });

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

enableDragToScroll('.table-container');
