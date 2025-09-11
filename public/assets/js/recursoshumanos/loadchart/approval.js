// Inicializar la tabla cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function () {
    initializeApprovalTable();
    setupModalEventListeners();
});

// Variables globales para el modal y la vista
let currentModalData = {
    employeeId: null,
    date: null,
    dailyActivities: null
};



function initializeApprovalTable() {
    setupEventListeners();
    renderEmployeeWorkLog();
    // Se asegura de que la tabla se renderice primero y luego se muestre la quincena 1
    showQuincena(1);
    setActiveButton("quincena1");
    updatePeriodInfo();
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

    // Manejadores para los botones de aprobación de la tabla principal (se asignarán dinámicamente)
    setupEmployeeRowListeners();
}

function setupModalEventListeners() {
    const modal = document.getElementById('approvalModal');
    const closeButtons = modal.querySelectorAll('.modal-approval-close, .modal-approval-close-btn');
    closeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    });

    // Manejador del botón guardar en el modal
    document.getElementById('modal-save-btn').addEventListener('click', saveModalChanges);
}

function handleTableClick(event) {
    const statusIndicator = event.target.closest('.status-indicator');
    if (statusIndicator) {
        const cell = statusIndicator.closest('.data-cell');
        const row = cell.closest('.employee-row');
        const employeeId = row.getAttribute('data-employee-id');
        const date = cell.getAttribute('data-date');

        const employeeData = workLogsData.find(log => log.employee_id.toString() === employeeId);
        const dailyActivity = employeeData?.daily_activities.find(act => act.date === date);

        if (dailyActivity) {
            openApprovalModal(employeeData, dailyActivity);
        } else {
            showNotification('No hay actividades registradas para este día.', 'info');
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
        const itemIndex = row.getAttribute('data-item-index') !== 'null' ? row.getAttribute('data-item-index') : null;
        const select = row.querySelector('.item-approval-selector');
        const comment = row.querySelector('.modal-comment').value;

        if (select && select.value !== 'pending' && !select.disabled) {
            changes.push({
                date: currentModalData.date,
                item_type: itemType,
                item_index: itemIndex,
                status: select.value,
                rejection_reason: comment
            });
        }
    });

    if (changes.length > 0) {
        await updateDailyItemsStatus(currentModalData.employeeId, changes);
    } else {
        showNotification('No hay cambios para guardar.', 'info');
    }
    modal.style.display = 'none';
}
function openApprovalModal(employeeData, dailyActivity) {
    const modal = document.getElementById('approvalModal');
    const subtitle = modal.querySelector('.modal-approval-subtitle');
    const tableBody = modal.querySelector('#modal-table-body');
    const employeeName = document.querySelector(`.employee-row[data-employee-id="${employeeData.employee_id}"] .employee-info-cell`).textContent.trim();
    const dateWithTime = dailyActivity.date + 'T12:00:00';

    const formattedDate = new Date(dateWithTime).toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
    currentModalData.employeeId = employeeData.employee_id;
    currentModalData.date = dailyActivity.date;

    const assignment = loadChartAssignments.find(assgn => assgn.employee_id === employeeData.employee_id);
    const isReviewer = assignment && assignment.reviewer_id === currentUserId;
    const isApprover = assignment && assignment.approver_id === currentUserId;

    subtitle.textContent = `${employeeName} - ${formattedDate}`;
    tableBody.innerHTML = '';

    // Llenar el modal con los datos del día
    // Actividad principal
    if (dailyActivity.activity_type) {
        const activityStatus = dailyActivity.activity_status ?? 'pending';
        addRowToModalTable('Actividad', dailyActivity.activity_description || '', '', 'activity', null, activityStatus, dailyActivity.rejection_reason, null, isReviewer, isApprover);
    }

    // Bonos de Comida
    if (dailyActivity.food_bonuses && dailyActivity.food_bonuses.length > 0) {
        dailyActivity.food_bonuses.forEach((bonus, index) => {
            const amount = canSeeAmounts ? bonus.daily_amount : null;
            addRowToModalTable('Bono de Comida', bonus.bonus_type, `x${bonus.num_daily}`, 'food_bonuses', index, bonus.status, bonus.rejection_reason, amount, isReviewer, isApprover);
        });
    }

    // Bonos de Campo
    if (dailyActivity.field_bonuses && dailyActivity.field_bonuses.length > 0) {
        dailyActivity.field_bonuses.forEach((bonus, index) => {
            let amount = null;
            if (canSeeAmounts) {
                if (bonus.currency === 'USD') {
                    amount = `${Number(bonus.daily_amount).toFixed(2)} USD (${(Number(bonus.daily_amount) * Number(bonus.usd_to_mxn_rate)).toFixed(2)} MXN)`;
                } else {
                    amount = `${Number(bonus.daily_amount).toFixed(2)} MXN`;
                }
            }
            addRowToModalTable('Bono de Campo', bonus.bonus_type, bonus.bonus_identifier, 'field_bonuses', index, bonus.status, bonus.rejection_reason, amount, isReviewer, isApprover);
        });
    }

    // Bonos de Nómina
    if (dailyActivity.payroll_bonuses && dailyActivity.payroll_bonuses.length > 0) {
        dailyActivity.payroll_bonuses.forEach((bonus, index) => {
            const amount = canSeeAmounts ? `${Number(bonus.total_amount).toFixed(2)} MXN` : null;
            addRowToModalTable('Bono de Nómina', bonus.bonus_name, `${bonus.days} días`, 'payroll_bonuses', index, bonus.status, bonus.rejection_reason, amount, isReviewer, isApprover);
        });
    }

    // Servicios
    if (dailyActivity.services_list && dailyActivity.services_list.length > 0) {
        dailyActivity.services_list.forEach((service, index) => {
            const amount = canSeeAmounts ? `${Number(service.amount).toFixed(2)} MXN` : null;
            addRowToModalTable('Servicio', service.service_name, service.service_identifier, 'services_list', index, service.status, service.rejection_reason, amount, isReviewer, isApprover);
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

function addRowToModalTable(concept, details, identifier, itemType, itemIndex, status, rejectionReason, amount, isReviewer, isApprover) {
    const tableBody = document.getElementById('modal-table-body');
    const row = document.createElement('tr');
    row.setAttribute('data-item-type', itemType);
    row.setAttribute('data-item-index', itemIndex);

    const safeStatus = status || 'pending';
    const statusPillClass = safeStatus.toLowerCase();
    const approvalSelector = getModalApprovalSelectorHtml(statusPillClass, isReviewer, isApprover);

    row.innerHTML = `
        <td>${concept}</td>
        <td>${details}</td>
        <td>${identifier}</td>
        <td class="amount-cell">${amount !== null ? `${amount}` : ''}</td>
        <td>
            ${approvalSelector}
        </td>
        <td>
            <textarea class="form-control modal-comment" placeholder="Motivo de rechazo...">${rejectionReason || ''}</textarea>
        </td>
    `;
    tableBody.appendChild(row);
}

function getModalApprovalSelectorHtml(currentStatus, isReviewer, isApprover) {
    const options = [
        { value: 'reviewed', text: 'Revisado' },
        { value: 'approved', text: 'Aprobado' },
        { value: 'rejected', text: 'Rechazado' }
    ];

    if (currentStatus === 'pending') {
        options.unshift({ value: 'pending', text: 'Pendiente' });
    }

    let html = `<select class="form-control item-approval-selector">`;
    options.forEach(option => {
        let isSelected = option.value === currentStatus;
        let isDisabled = false;

        if (currentStatus === 'pending') {
            if (option.value === 'pending') {
                isDisabled = true;
            }
        }

        if (isReviewer && !isApprover && option.value === 'approved') {
            return;
        }

        if (!isReviewer && !isApprover) {
            isDisabled = true;
        }

        if (currentStatus === 'approved') {
            if (!isApprover) {
                isDisabled = true;
            } else if (option.value === 'pending' || option.value === 'reviewed') {
                isDisabled = true;
            }
        }

        if (currentStatus === 'reviewed' && option.value === 'pending') {
            isDisabled = true;
        }

        if (currentStatus === 'rejected') {
            isDisabled = true;
        }

        html += `<option value="${option.value}" ${isSelected ? 'selected' : ''} ${isDisabled ? 'disabled' : ''}>${option.text}</option>`;
    });
    html += `</select>`;

    if (currentStatus === 'approved' && !isApprover) {
        return `<span class="badge badge-success">Aprobado</span>`;
    }

    if (currentStatus === 'pending') {
        return html;
    }

    return html;
}

async function updateDailyItemsStatus(employeeId, changes) {
    try {
        showLoadingState();
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
        hideLoadingState();
        if (data.success) {
            showNotification(data.message, 'success');
            loadMonthData();
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error al actualizar el estado de los ítems:', error);
        hideLoadingState();
        showNotification('Error al guardar los cambios: ' + error.message, 'error');
    }
}

// === Funciones de la tabla principal (refactorizadas) ===

function setupEmployeeRowListeners() {
    document.querySelectorAll(".btn-approve").forEach((btn) => {
        btn.removeEventListener("click", handleApprove);
        btn.addEventListener("click", handleApprove);
    });

    document.querySelectorAll(".btn-review").forEach((btn) => {
        btn.removeEventListener("click", handleReview);
        btn.addEventListener("click", handleReview);
    });
}

// Función corregida para manejar las acciones de los botones con validación de permisos
async function handleApprove() {
    const row = this.closest(".employee-row");
    const employeeId = row.getAttribute('data-employee-id');

    // Verificar permisos antes de proceder
    const employeeAssignment = loadChartAssignments.find(a => a.employee_id.toString() === employeeId);
    const isApproverForEmployee = employeeAssignment && employeeAssignment.approver_id === currentUserId;

    if (!isApproverForEmployee) {
        showNotification('No tiene permisos para aprobar este registro.', 'error');
        return;
    }

    const fortnight = currentView;
    await saveApprovalStatus(employeeId, 'approved', fortnight);
}

async function handleReview() {
    const row = this.closest(".employee-row");
    const employeeId = row.getAttribute('data-employee-id');

    // Verificar permisos antes de proceder
    const employeeAssignment = loadChartAssignments.find(a => a.employee_id.toString() === employeeId);
    const isReviewerForEmployee = employeeAssignment && employeeAssignment.reviewer_id === currentUserId;

    if (!isReviewerForEmployee) {
        showNotification('No tiene permisos para revisar este registro.', 'error');
        return;
    }

    const fortnight = currentView;
    await saveApprovalStatus(employeeId, 'reviewed', fortnight);
}


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
            showNotification(data.message, 'success');
            await loadMonthData(); // Recargar todos los datos para reflejar los cambios
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error saving approval status:', error);
        hideLoadingState();
        showNotification('Error al guardar el estado de aprobación', 'error');
    }
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

function toggleActivityRows(employeeRow) {
    const allRows = Array.from(employeeRow.parentElement.children);
    const startIndex = allRows.indexOf(employeeRow);
    let activityRowCount = 0;
    for (let i = startIndex + 1; i < allRows.length; i++) {
        if (allRows[i].classList.contains('activity-row')) {
            activityRowCount++;
        } else {
            break;
        }
    }

    for (let i = 1; i <= activityRowCount; i++) {
        const row = allRows[startIndex + i];
        if (row) {
            row.classList.toggle('hidden');
        }
    }
}

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('employee-info-cell')) {
        const employeeRow = e.target.closest('.employee-row');
        if (employeeRow) {
            toggleActivityRows(employeeRow);
        }
    }
});

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

async function loadMonthData() {
    try {
        showLoadingState();

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

        // Actualizar variables globales
        workLogsData = data.workLogsData;
        fortnightlyConfig = data.fortnightlyConfig;
        loadChartAssignments = data.loadChartAssignments;
        canSeeAmounts = data.canSeeAmounts;
        userPermissions = data.userPermissions;

        // Regenerar la tabla
        updateTableStructure(data.monthlyDays, data.employees);

        // Actualizar información del período
        updatePeriodInfo();

        // Renderizar datos de trabajo
        renderEmployeeWorkLog();

        // Mantener la vista actual después de cargar los datos
        if (currentView === 'quincena1') {
            showQuincena(1);
        } else if (currentView === 'quincena2') {
            showQuincena(2);
        } else {
            showFullMonth();
        }

        hideLoadingState();
        showNotification(data.message || 'Datos cargados correctamente', 'success');
    } catch (error) {
        console.error('Error loading month data:', error);
        showNotification('Error al cargar los datos del mes: ' + error.message, 'error');
        hideLoadingState();
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
    updateTableBody(monthlyDays, employees);
    setupEmployeeRowListeners();
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

function updateTableBody(monthlyDays, employees) {
    const tableBody = document.getElementById('approval-table-body');
    if (!tableBody) return;

    tableBody.innerHTML = '';

    employees.forEach(employee => {
        const workLog = workLogsData.find(log => log.employee_id === employee.id);
        const hasPayrollBonuses = workLog ? workLog.has_payroll_bonuses : false;

        const mainRow = createEmployeeRow(employee, monthlyDays, 'Actividad', true, hasPayrollBonuses);
        tableBody.appendChild(mainRow);

        const foodRow = createEmployeeRow(employee, monthlyDays, 'Comida', false, hasPayrollBonuses);
        tableBody.appendChild(foodRow);

        const fieldBonusRow = createEmployeeRow(employee, monthlyDays, 'Bono', false, hasPayrollBonuses);
        tableBody.appendChild(fieldBonusRow);

        if (hasPayrollBonuses) {
            const payrollBonusRow = createEmployeeRow(employee, monthlyDays, 'BonoN', false, hasPayrollBonuses);
            tableBody.appendChild(payrollBonusRow);
        }

        const serviceRow = createEmployeeRow(employee, monthlyDays, 'Servicio', false, hasPayrollBonuses);
        tableBody.appendChild(serviceRow);
    });
}

// Función corregida para crear la fila del empleado con botones condicionais
function createEmployeeRow(employee, monthlyDays, rowType, isMainRow, hasPayrollBonuses) {
    const tr = document.createElement('tr');
    tr.className = isMainRow ? 'employee-row' : 'activity-row';
    tr.setAttribute('data-employee-id', employee.id);

    if (isMainRow) {
        const nameCell = document.createElement('td');
        nameCell.className = 'employee-info-cell';
        nameCell.rowSpan = hasPayrollBonuses ? 5 : 4;
        nameCell.textContent = employee.full_name;
        tr.appendChild(nameCell);
    }

    const labelCell = document.createElement('td');
    labelCell.className = 'activity-label-cell';
    labelCell.textContent = rowType;
    tr.appendChild(labelCell);

    monthlyDays.forEach(dayInfo => {
        const dayCell = document.createElement('td');
        dayCell.className = `data-cell ${dayInfo.is_quincena_1 ? 'quincena-1' : ''} ${dayInfo.is_quincena_2 ? 'quincena-2' : ''} ${!dayInfo.is_working_day ? 'non-working' : ''} ${!dayInfo.is_current_month ? 'other-month' : ''}`;
        dayCell.setAttribute('data-day', dayInfo.day);
        dayCell.setAttribute('data-date', dayInfo.date);
        dayCell.setAttribute('data-quincena-1', dayInfo.is_quincena_1 ? 'true' : 'false');
        dayCell.setAttribute('data-quincena-2', dayInfo.is_quincena_2 ? 'true' : 'false');
        dayCell.setAttribute('data-current-month', dayInfo.is_current_month ? 'true' : 'false');

        if (rowType === 'Actividad') {
            const statusDiv = document.createElement('div');
            statusDiv.className = 'status-indicator status-n';
            statusDiv.textContent = 'N';
            dayCell.appendChild(statusDiv);
        } else {
            dayCell.textContent = '0';
        }

        tr.appendChild(dayCell);
    });

    const totalCell = document.createElement('td');
    totalCell.className = `data-cell ${rowType === 'Actividad' ? 'total-activity' : (rowType === 'Comida' ? 'total-food' : (rowType === 'Bono' ? 'total-field-bonus' : (rowType === 'BonoN' ? 'total-payroll-bonus' : 'total-service')))}`;
    totalCell.innerHTML = rowType === 'Actividad' ? '<div class="status-indicator">0</div>' : '0';
    tr.appendChild(totalCell);

    if (isMainRow) {
        const vacCell = document.createElement('td');
        vacCell.className = 'vacations-cell';
        vacCell.rowSpan = hasPayrollBonuses ? 5 : 4;
        vacCell.innerHTML = '<div class="vacations-container"><div class="vacations-value">0</div></div>';
        tr.appendChild(vacCell);

        const breaksCell = document.createElement('td');
        breaksCell.className = 'breaks-cell';
        breaksCell.rowSpan = hasPayrollBonuses ? 5 : 4;
        breaksCell.innerHTML = '<div class="breaks-container"><div class="breaks-value">0</div></div>';
        tr.appendChild(breaksCell);

        const utilCell = document.createElement('td');
        utilCell.className = 'utilization-cell';
        utilCell.rowSpan = hasPayrollBonuses ? 5 : 4;
        utilCell.innerHTML = '<div class="utilization-container"><div class="utilization-value">0%</div></div>';
        tr.appendChild(utilCell);

        const approvalCell = document.createElement('td');
        approvalCell.className = 'actions-cell';
        approvalCell.rowSpan = hasPayrollBonuses ? 5 : 4;

        // CORRECCIÓN: Verificar permisos específicos para este empleado
        const employeeAssignment = loadChartAssignments.find(a => a.employee_id === employee.id);
        const isReviewerForEmployee = employeeAssignment && employeeAssignment.reviewer_id === currentUserId;
        const isApproverForEmployee = employeeAssignment && employeeAssignment.approver_id === currentUserId;

        let buttonsHtml = '';

        // Solo mostrar el botón de revisar si es revisor para este empleado
        if (isReviewerForEmployee) {
            buttonsHtml += '<button class="btn-review">Revisado</button>';
        }

        // Solo mostrar el botón de aprobar si es aprobador para este empleado
        if (isApproverForEmployee) {
            buttonsHtml += '<button class="btn-approve">Aprobado</button>';
        }

        // Si no tiene permisos, no mostrar botones
        if (!isReviewerForEmployee && !isApproverForEmployee) {
            buttonsHtml = '<span class="no-permissions">Sin permisos</span>';
        }

        approvalCell.innerHTML = `<div class="actions-container">${buttonsHtml}</div>`;
        tr.appendChild(approvalCell);
    }

    return tr;
}

// === FUNCIONES DE VISUALIZACIÓN DE QUINCENAS ===
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

    dayHeaders.forEach((header, index) => {
        const isQuincena1 = header.getAttribute('data-quincena-1') === 'true';
        const isQuincena2 = header.getAttribute('data-quincena-2') === 'true';
        const shouldShow = (quincena === 1 && isQuincena1) || (quincena === 2 && isQuincena2);

        header.style.display = shouldShow ? "" : "none";

        if (shouldShow) visibleDays++;

        allRows.forEach((row) => {
            const dataCells = row.querySelectorAll(".data-cell");
            if (dataCells[index]) {
                dataCells[index].style.display = shouldShow ? "" : "none";
            }
        });
    });

    daysColumnsHeader.colSpan = visibleDays;
    calculateAndRenderTotals();
}

function showFullMonth() {
    const dayHeaders = document.querySelectorAll(".approval-table .day-header");
    const allRows = document.querySelectorAll(".approval-table tbody tr");
    const daysColumnsHeader = document.getElementById("days-columns");

    if (!dayHeaders.length || !daysColumnsHeader) return;

    dayHeaders.forEach((header, index) => {
        header.style.display = "";

        allRows.forEach((row) => {
            const dataCells = row.querySelectorAll(".data-cell");
            if (dataCells[index]) {
                dataCells[index].style.display = "";
            }
        });
    });

    daysColumnsHeader.colSpan = dayHeaders.length;
    calculateAndRenderTotals();
}

// === FUNCIONES DE CÁLCULO DE TOTALES ===
function calculateAndRenderTotals() {
    const tableBody = document.getElementById('approval-table-body');
    if (!tableBody || !workLogsData || workLogsData.length === 0) {
        return;
    }

    const employeeRows = document.querySelectorAll('.employee-row');

    employeeRows.forEach(employeeRow => {
        const employeeId = employeeRow.getAttribute('data-employee-id');
        const employeeData = workLogsData.find(log => log.employee_id.toString() === employeeId);

        if (!employeeData || !employeeData.daily_activities) {
            return;
        }

        let totalDays = 0;
        let totalFood = 0;
        let totalFieldBonus = 0;
        let totalPayrollBonus = 0;
        let totalService = 0;
        let totalBreaks = 0;
        let totalVacations = 0;

        const dailyActivitiesMap = new Map(employeeData.daily_activities.map(activity => [activity.date, activity]));

        const allDayCells = Array.from(employeeRow.querySelectorAll('.data-cell')).filter(cell => cell.getAttribute('data-date'));
        const visibleDayCells = allDayCells.filter(cell => cell.style.display !== 'none');

        // Contar días laborables del MES COMPLETO (ambas quincenas)
        const allWorkingDays = Array.from(document.querySelectorAll('.day-header'))
            .filter(header => header.getAttribute('data-quincena-1') === 'true' ||
                header.getAttribute('data-quincena-2') === 'true')
            .length;

        visibleDayCells.forEach(cell => {
            const dateAttr = cell.getAttribute('data-date');
            const dailyActivity = dailyActivitiesMap.get(dateAttr);

            if (dailyActivity) {
                // Solo contar actividades B, P, H, V, E, C para la utilización
                if (dailyActivity.activity_type &&
                    ['B', 'P', 'H', 'V', 'E', 'C'].includes(dailyActivity.activity_type.toUpperCase())) {
                    totalDays++;
                }

                if (dailyActivity.activity_type && dailyActivity.activity_type.toLowerCase() === 'vac') {
                    totalVacations++;
                }
                if (dailyActivity.activity_type && dailyActivity.activity_type.toLowerCase() === 'd') {
                    totalBreaks++;
                }

                totalFood += dailyActivity.food_bonuses ? dailyActivity.food_bonuses.reduce((sum, bonus) => sum + Number(bonus.daily_amount), 0) : 0;

                const dailyFieldBonus = dailyActivity.field_bonuses ? dailyActivity.field_bonuses.reduce((sum, bonus) => {
                    if (bonus.currency === 'USD') {
                        return sum + (Number(bonus.daily_amount) * Number(bonus.usd_to_mxn_rate));
                    }
                    return sum + Number(bonus.daily_amount);
                }, 0) : 0;
                totalFieldBonus += dailyFieldBonus;

                totalPayrollBonus += dailyActivity.payroll_bonuses ? dailyActivity.payroll_bonuses.reduce((sum, bonus) => sum + Number(bonus.total_amount), 0) : 0;
                totalService += dailyActivity.services_list ? dailyActivity.services_list.reduce((sum, service) => sum + Number(service.amount), 0) : 0;
            }
        });

        const activityRow = employeeRow;
        const foodRow = activityRow.nextElementSibling;
        const fieldBonusRow = foodRow.nextElementSibling;
        const payrollBonusRow = employeeData.has_payroll_bonuses ? fieldBonusRow.nextElementSibling : null;
        const serviceRow = payrollBonusRow ? payrollBonusRow.nextElementSibling : fieldBonusRow.nextElementSibling;

        const activityTotalCell = activityRow.querySelector('.total-activity');
        const foodTotalCell = foodRow.querySelector('.total-food');
        const fieldBonusTotalCell = fieldBonusRow.querySelector('.total-field-bonus');
        const payrollBonusTotalCell = payrollBonusRow ? payrollBonusRow.querySelector('.total-payroll-bonus') : null;
        const serviceTotalCell = serviceRow.querySelector('.total-service');

        if (activityTotalCell) activityTotalCell.querySelector('.status-indicator').textContent = totalDays;

        if (canSeeAmounts) {
            if (foodTotalCell) foodTotalCell.textContent = totalFood.toFixed(2);
            if (fieldBonusTotalCell) fieldBonusTotalCell.textContent = totalFieldBonus.toFixed(2);
            if (payrollBonusTotalCell) {
                payrollBonusTotalCell.textContent = totalPayrollBonus.toFixed(2);
            }
            if (serviceTotalCell) serviceTotalCell.textContent = totalService.toFixed(2);
        } else {
            if (foodTotalCell) foodTotalCell.textContent = '0';
            if (fieldBonusTotalCell) fieldBonusTotalCell.textContent = '0';
            if (payrollBonusTotalCell) payrollBonusTotalCell.textContent = '0';
            if (serviceTotalCell) serviceTotalCell.textContent = '0';
        }

        const vacCell = employeeRow.querySelector('.vacations-value');
        if (vacCell) vacCell.textContent = totalVacations;

        const breaksCell = employeeRow.querySelector('.breaks-value');
        if (breaksCell) breaksCell.textContent = totalBreaks;

        const utilCell = employeeRow.querySelector('.utilization-value');
        if (utilCell) {
            const utilizationRate = allWorkingDays > 0 ? (totalDays / allWorkingDays) * 100 : 0;
            utilCell.textContent = `${utilizationRate.toFixed(2)}%`;

            if (utilizationRate < 85) {
                // Opción 1: Verde claro
                utilCell.style.color = '#4e6952ff'; // O 'lightgreen'
                utilCell.style.fontWeight = 'bold';
            } else if (utilizationRate >= 85 && utilizationRate <= 90) {
                // Opción 2: Naranja brillante
                utilCell.style.color = '#FF4500'; // O 'darkorange'
            } else if (utilizationRate >= 91) {
                // Opción 3: Rojo oscuro
                utilCell.style.color = '#B22222'; // O 'firebrick'
            }
        }
    });
}

// Función corregida para actualizar el estado de los botones después de cargar datos
// Función corregida para actualizar el estado de los botones después de cargar datos
function renderEmployeeWorkLog() {
    if (!workLogsData || workLogsData.length === 0) {
        return;
    }

    workLogsData.forEach(employeeData => {
        const employeeRow = document.querySelector(`.employee-row[data-employee-id="${employeeData.employee_id}"]`);
        if (!employeeRow) {
            return;
        }

        const activityRow = employeeRow;
        const comidaRow = employeeRow.nextElementSibling;
        const fieldBonusRow = comidaRow.nextElementSibling;
        const payrollBonusRow = employeeData.has_payroll_bonuses ? fieldBonusRow.nextElementSibling : null;
        const servicioRow = employeeData.has_payroll_bonuses ? payrollBonusRow.nextElementSibling : fieldBonusRow.nextElementSibling;

        const activityCells = Array.from(activityRow.querySelectorAll('.data-cell'));
        const comidaCells = Array.from(comidaRow.querySelectorAll('.data-cell'));
        const fieldBonusCells = Array.from(fieldBonusRow.querySelectorAll('.data-cell'));
        const payrollBonusCells = payrollBonusRow ? Array.from(payrollBonusRow.querySelectorAll('.data-cell')) : [];
        const servicioCells = Array.from(servicioRow.querySelectorAll('.data-cell'));

        const dailyActivitiesMap = new Map();
        if (employeeData.daily_activities) {
            employeeData.daily_activities.forEach(activity => {
                dailyActivitiesMap.set(activity.date, activity);
            });
        }

        activityCells.forEach((cell, index) => {
            const dateAttr = cell.getAttribute('data-date');
            if (!dateAttr) {
                return;
            }

            const dailyActivity = dailyActivitiesMap.get(dateAttr);

            if (dailyActivity) {
                const statusIndicator = cell.querySelector('.status-indicator');
                if (statusIndicator) {
                    const activityType = dailyActivity.activity_type || 'N';
                    statusIndicator.textContent = activityType.toUpperCase();
                    statusIndicator.className = `status-indicator status-${activityType.toLowerCase()}`;
                    statusIndicator.title = dailyActivity.activity_description || '';

                    // Usar la nueva función para determinar el estado del día
                    const dayStatus = getDailyStatusIndicator(dailyActivity);

                    // Añadir la clase de borde adecuada basada en day_status
                    statusIndicator.classList.remove('approved-border', 'reviewed-border', 'rejected-border', 'pending-border');
                    if (dayStatus === 'approved') {
                        statusIndicator.classList.add('approved-border');
                    } else if (dayStatus === 'reviewed') {
                        statusIndicator.classList.add('reviewed-border');
                    } else if (dayStatus === 'rejected') {
                        statusIndicator.classList.add('rejected-border');
                    } else {
                        statusIndicator.classList.add('pending-border');
                    }
                }

                // Resto del código para mostrar bonos y servicios (sin cambios)
                if (canSeeAmounts) {
                    if (comidaCells[index]) {
                        const totalFoodBonus = dailyActivity.food_bonuses ? dailyActivity.food_bonuses.reduce((sum, bonus) => sum + Number(bonus.daily_amount), 0) : 0;
                        comidaCells[index].textContent = totalFoodBonus > 0 ? totalFoodBonus.toFixed(2) : '0';
                        comidaCells[index].title = dailyActivity.food_bonuses && dailyActivity.food_bonuses.length > 0 ? dailyActivity.food_bonuses.map(b => `${b.num_daily} comidas`).join(', ') : '';
                    }

                    if (fieldBonusCells[index]) {
                        const totalFieldBonus = dailyActivity.field_bonuses ? dailyActivity.field_bonuses.reduce((sum, bonus) => {
                            if (bonus.currency === 'USD') {
                                return sum + (Number(bonus.daily_amount) * Number(bonus.usd_to_mxn_rate));
                            }
                            return sum + Number(bonus.daily_amount);
                        }, 0) : 0;
                        fieldBonusCells[index].textContent = totalFieldBonus > 0 ? totalFieldBonus.toFixed(2) : '0';
                        fieldBonusCells[index].title = dailyActivity.field_bonuses && dailyActivity.field_bonuses.length > 0 ? dailyActivity.field_bonuses.map(b => `${b.bonus_type} (${b.daily_amount} ${b.currency})`).join(', ') : '';
                    }

                    if (payrollBonusCells[index]) {
                        const totalPayrollBonus = dailyActivity.payroll_bonuses ? dailyActivity.payroll_bonuses.reduce((sum, bonus) => sum + Number(bonus.total_amount), 0) : 0;
                        payrollBonusCells[index].textContent = totalPayrollBonus > 0 ? totalPayrollBonus.toFixed(2) : '0';
                        payrollBonusCells[index].title = dailyActivity.payroll_bonuses && dailyActivity.payroll_bonuses.length > 0 ? dailyActivity.payroll_bonuses.map(b => `${b.bonus_name} (${b.days} días)`).join(', ') : '';
                    }

                    if (servicioCells[index]) {
                        const totalServiceAmount = dailyActivity.services_list ? dailyActivity.services_list.reduce((sum, service) => sum + Number(service.amount), 0) : 0;
                        servicioCells[index].textContent = totalServiceAmount > 0 ? totalServiceAmount.toFixed(2) : '0';
                        servicioCells[index].title = dailyActivity.services_list && dailyActivity.services_list.length > 0 ? dailyActivity.services_list.map(s => s.service_name).join(', ') : '';
                    }
                } else {
                    if (comidaCells[index]) {
                        const foodCount = dailyActivity.food_bonuses ? dailyActivity.food_bonuses.length : 0;
                        comidaCells[index].textContent = foodCount > 0 ? dailyActivity.food_bonuses.map(b => b.num_daily).join(', ') : '0';
                        comidaCells[index].title = dailyActivity.food_bonuses && dailyActivity.food_bonuses.length > 0 ? dailyActivity.food_bonuses.map(b => `${b.bonus_type} x${b.num_daily}`).join(', ') : '';
                    }

                    if (fieldBonusCells[index]) {
                        const bonusCount = dailyActivity.field_bonuses ? dailyActivity.field_bonuses.length : 0;
                        fieldBonusCells[index].textContent = bonusCount > 0 ? dailyActivity.field_bonuses.map(b => b.bonus_identifier).join(', ') : '0';
                        fieldBonusCells[index].title = dailyActivity.field_bonuses && dailyActivity.field_bonuses.length > 0 ? dailyActivity.field_bonuses.map(b => b.bonus_type).join(', ') : '';
                    }

                    if (payrollBonusCells[index]) {
                        const payrollCount = dailyActivity.payroll_bonuses ? dailyActivity.payroll_bonuses.length : 0;
                        payrollBonusCells[index].textContent = payrollCount > 0 ? dailyActivity.payroll_bonuses.map(b => b.days).join(', ') : '0';
                        payrollBonusCells[index].title = dailyActivity.payroll_bonuses && dailyActivity.payroll_bonuses.length > 0 ? dailyActivity.payroll_bonuses.map(b => b.bonus_name).join(', ') : '';
                    }

                    if (servicioCells[index]) {
                        const serviceCount = dailyActivity.services_list ? dailyActivity.services_list.length : 0;
                        servicioCells[index].textContent = serviceCount > 0 ? dailyActivity.services_list.map(s => s.service_identifier).join(', ') : '0';
                        servicioCells[index].title = dailyActivity.services_list && dailyActivity.services_list.length > 0 ? dailyActivity.services_list.map(s => s.service_name).join(', ') : '';
                    }
                }
            } else {
                const statusIndicator = cell.querySelector('.status-indicator');
                if (statusIndicator) {
                    statusIndicator.textContent = 'N';
                    statusIndicator.className = 'status-indicator status-n pending-border';
                    statusIndicator.title = 'No hay actividad registrada';
                }
                if (comidaCells[index]) {
                    comidaCells[index].textContent = '0';
                    comidaCells[index].title = 'No hay bonos de comida registrados';
                }
                if (fieldBonusCells[index]) {
                    fieldBonusCells[index].textContent = '0';
                    fieldBonusCells[index].title = 'No hay bonos de campo registrados';
                }
                if (payrollBonusCells[index]) {
                    payrollBonusCells[index].textContent = '0';
                    payrollBonusCells[index].title = 'No hay bonos de nómina registrados';
                }
                if (servicioCells[index]) {
                    servicioCells[index].textContent = '0';
                    servicioCells[index].title = 'No hay servicios registrados';
                }
            }
        });

        // CORRECCIÓN: Verificar permisos antes de actualizar el estado de los botones
        const employeeAssignment = loadChartAssignments.find(a => a.employee_id === employeeData.employee_id);
        const isReviewerForEmployee = employeeAssignment && employeeAssignment.reviewer_id === currentUserId;
        const isApproverForEmployee = employeeAssignment && employeeAssignment.approver_id === currentUserId;

        const reviewBtn = employeeRow.querySelector('.btn-review');
        const approveBtn = employeeRow.querySelector('.btn-approve');
        const employeeLog = workLogsData.find(log => log.employee_id === employeeData.employee_id);

        // Solo actualizar el botón de revisar si existe y el usuario es revisor
        if (reviewBtn && isReviewerForEmployee) {
            if (employeeLog && employeeLog.reviewed_at) {
                reviewBtn.textContent = 'Revisado';
                reviewBtn.disabled = true;
                reviewBtn.classList.add('reviewed');
            } else {
                reviewBtn.textContent = 'Revisado';
                reviewBtn.disabled = false;
                reviewBtn.classList.remove('reviewed');
            }
        }

        // Solo actualizar el botón de aprobar si existe y el usuario es aprobador
        if (approveBtn && isApproverForEmployee) {
            if (employeeLog && employeeLog.approved_at) {
                approveBtn.textContent = 'Aprobado';
                approveBtn.disabled = true;
                approveBtn.classList.add('approved');
            } else {
                approveBtn.textContent = 'Aprobado';
                approveBtn.disabled = false;
                approveBtn.classList.remove('approved');
            }
        }
    });

    calculateAndRenderTotals();
}

/**
 * Determines the overall status for a single day based on all its components.
 * Rules:
 * 1. If any element is REJECTED → day_status = 'rejected'
 * 2. If there are PENDING elements → day_status = 'pending'
 * 3. If ALL elements are APPROVED → day_status = 'approved'
 * 4. If there are REVIEWED elements (and possibly APPROVED, without pending or rejected) → day_status = 'reviewed'
 *
 * @param {object} dailyActivity - The object containing all activities for a specific day.
 * @returns {string} - 'approved', 'reviewed', 'rejected', or 'pending'.
 */
function getDailyStatusIndicator(dailyActivity) {
    let hasRejected = false;
    let hasPending = false;
    let hasReviewed = false;
    let hasApproved = false;
    let totalItems = 0;
    let approvedItems = 0;
    let reviewedItems = 0;

    // Check main activity status
    const activityStatus = dailyActivity.activity_status ? dailyActivity.activity_status.toLowerCase() : 'pending';

    // Solo contar si hay actividad registrada (no 'N')
    if (dailyActivity.activity_type && dailyActivity.activity_type !== 'N' && dailyActivity.activity_type !== 'n') {
        totalItems++;
        switch (activityStatus) {
            case 'rejected':
                hasRejected = true;
                break;
            case 'pending':
                hasPending = true;
                break;
            case 'reviewed':
                hasReviewed = true;
                reviewedItems++;
                break;
            case 'approved':
                hasApproved = true;
                approvedItems++;
                break;
        }
    }

    // Check all sub-item statuses
    const itemTypes = ['food_bonuses', 'field_bonuses', 'payroll_bonuses', 'services_list'];
    itemTypes.forEach(type => {
        if (dailyActivity[type] && dailyActivity[type].length > 0) {
            dailyActivity[type].forEach(item => {
                totalItems++;
                const itemStatus = item.status ? item.status.toLowerCase() : 'pending';
                switch (itemStatus) {
                    case 'rejected':
                        hasRejected = true;
                        break;
                    case 'pending':
                        hasPending = true;
                        break;
                    case 'reviewed':
                        hasReviewed = true;
                        reviewedItems++;
                        break;
                    case 'approved':
                        hasApproved = true;
                        approvedItems++;
                        break;
                }
            });
        }
    });

    // Si no hay ítems registrados, el estado es 'pending' por defecto
    if (totalItems === 0) {
        return 'pending';
    }

    // Aplicar la lógica de prioridad según las reglas
    // 1. Si cualquier elemento está RECHAZADO → day_status = 'rejected'
    if (hasRejected) {
        return 'rejected';
    }

    // 2. Si hay elementos PENDIENTES → day_status = 'pending'
    if (hasPending) {
        return 'pending';
    }

    // 3. Si TODOS los elementos están APROBADOS → day_status = 'approved'
    if (approvedItems === totalItems) {
        return 'approved';
    }

    // 4. Si hay elementos REVISADOS (y posiblemente APROBADOS, sin pendientes ni rechazados) → day_status = 'reviewed'
    if (hasReviewed || (reviewedItems + approvedItems === totalItems && reviewedItems > 0)) {
        return 'reviewed';
    }

    // Por defecto, si no encaja en ninguna categoría anterior
    return 'pending';
}

/**
 * Actualiza el day_status para cada actividad diaria después de modificaciones
 * @param {Array} dailyActivities - Array de actividades diarias
 * @returns {Array} - Array actualizado con day_status
 */
function updateDayStatusForActivities(dailyActivities) {
    return dailyActivities.map(dailyActivity => {
        dailyActivity.day_status = getDailyStatusIndicator(dailyActivity);
        return dailyActivity;
    });
}
