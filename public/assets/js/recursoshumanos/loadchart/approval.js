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
// 🌎 GLOBAL VARIABLES (ASSUMING these are set by PHP before this script, but initializing
// for robustness and to ensure `canSeeAmounts` has a fallback value.)
// =========================================================================================
let currentModalData = {
    employeeId: null,
    date: null,
    dailyActivities: null
};

let autoRefreshInterval = null;
let lastUpdateTime = new Date();




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
    // ⚠️ Se remueve la llamada a renderEmployeeWorkLog() aquí para que loadMonthData
    // lo haga después de obtener los datos.
    loadMonthData(false).then(() => {
        showQuincena(1); // Muestra Quincena 1 por defecto, ahora que el DOM está poblado
        setActiveButton("quincena1");
        updatePeriodInfo();
    });
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
        canSeeAmounts = data.canSeeAmounts;
        userPermissions = data.userPermissions;

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
    const employeeRow = document.querySelector(`.employee-row[data-employee-id="${employeeData.employee_id}"]`);
    const employeeName = employeeRow ? employeeRow.querySelector('.employee-info-cell').textContent.trim() : 'Empleado Desconocido';
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
        } else if (dailyActivity.activity_description) {
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
    // El comentario debe estar bloqueado SOLO si:
    // 1. El usuario NO es ni Revisor ni Aprobador.
    // 2. El ítem ya está en estado 'approved' o 'rejected' y el usuario no es el Aprobador (quien puede revertir el rechazo).
    // 3. O, si el selector de estado en sí mismo está disabled por la lógica de permisos (getModalApprovalSelectorHtml).
    const isSelectorDisabled = approvalSelector.startsWith('<span') || approvalSelector.includes('disabled');

    // Si el usuario tiene permisos (Revisor o Aprobador), el campo de comentario debe estar
    // HABILITADO para que puedan introducir el motivo del rechazo *antes de guardar* o si ya fue rechazado.
    // La única excepción para bloquear es si no tiene permisos O si el selector está rígidamente bloqueado.
    let isLocked = isSelectorDisabled && !isApprover;

    // Si ya está aprobado y no es el aprobador, se bloquea (no puede cambiar ni comentar).
    if (currentStatusLower === 'approved' && !isApprover) {
        isLocked = true;
    }

    // Si no tiene permisos, está bloqueado.
    if (!isReviewer && !isApprover) {
        isLocked = true;
    }

    // Si está Rechazado, el campo debe estar editable para que el aprobador pueda limpiar el motivo
    // o el revisor/aprobador pueda ver el motivo, pero solo editable si se rechaza de nuevo.
    // DEJAMOS QUE LA LÓGICA DE EVENTO DINÁMICO HAGA EL BLOQUEO/DESBLOQUEO
    // Inicialmente, solo lo bloqueamos si no hay permisos.

    // -------------------------------------------------------------
    // ⭐ NUEVA LÓGICA SIMPLE: Si el selector está bloqueado con el <span> (no hay control),
    // o si el usuario no tiene ningún permiso, se bloquea el comentario.
    // De lo contrario, se permite escribir el comentario, y la lógica dinámica de JS
    // lo controlará al seleccionar 'Rechazado'.
    if (!isReviewer && !isApprover) {
        isLocked = true;
    }
    // Si el estado es Aprobado, solo el Aprobador puede hacer algo.
    if (currentStatusLower === 'approved' && !isApprover) {
        isLocked = true;
    }
    // Si está Rechazado, debe estar editable para que el Aprobador pueda moverlo a otro estado.
    if (currentStatusLower === 'rejected' && !isApprover) {
        // Un Revisor no debe poder cambiar un Rechazado, se asume que espera la corrección del empleado.
        isLocked = true;
    }
    // Si el usuario es el que tiene los permisos (aprobador o revisor), DEBE estar disponible
    // para escribir el motivo de rechazo.
    if (isReviewer || isApprover) {
        isLocked = false;
    }
    // Sobreescribir: Si el selector es el <span> (bloqueado rígidamente por la lógica de rol), bloquear
    if (approvalSelector.startsWith('<span')) {
        isLocked = true;
    }
    // -------------------------------------------------------------


    const commentValue = rejectionReason || '';

    // Usamos innerHTML para permitir el formato (ej. <strong>)
    row.innerHTML = `
        <td>${concept}</td>
        <td>${details}</td>
        <td>${identifier}</td>
        <td>${additionalDetails}</td>
        <td class="amount-cell">${amount !== null ? `${amount}` : 'N/A'}</td>
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
            showSwalNotification('Éxito', data.message, 'success');
            document.getElementById('approvalModal').style.display = 'none'; // Cerrar modal al éxito
            await loadMonthData(true); // Recargar datos para actualizar la tabla
        } else {
            showSwalNotification('Error', data.message, 'error');
            // No cerrar el modal para que el usuario pueda corregir
        }
    } catch (error) {
        console.error('Error al actualizar el estado de los ítems:', error);
        hideLoadingState();
        showSwalNotification('Error Crítico', 'Error al guardar los cambios: ' + error.message, 'error');
        // No cerrar el modal
    }
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
    if (e.target.classList.contains('employee-info-cell')) {
        const employeeRow = e.target.closest('.employee-row');
        if (employeeRow) {
            toggleActivityRows(employeeRow);
        }
    }
});

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
        canSeeAmounts = data.canSeeAmounts; // 👈🏼 CRITICAL: canSeeAmounts se actualiza aquí
        userPermissions = data.userPermissions;

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

function updateTableStructure(monthlyDays, employees) {
    updateDaysHeader(monthlyDays);
    updateTableBody(monthlyDays, employees);
    // REMOVIDO: Se llama en loadMonthData
    // setupEmployeeRowListeners();
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
        const mainRow = createEmployeeRow(employee, monthlyDays, 'Actividad', true);
        tableBody.appendChild(mainRow);

        const foodRow = createEmployeeRow(employee, monthlyDays, 'Comida', false);
        tableBody.appendChild(foodRow);

        const fieldBonusRow = createEmployeeRow(employee, monthlyDays, 'Bono', false);
        tableBody.appendChild(fieldBonusRow);

        const serviceRow = createEmployeeRow(employee, monthlyDays, 'Servicio', false);
        tableBody.appendChild(serviceRow);
    });
    renderEmployeeWorkLog(); // 👈🏼 CRITICAL: Call after creating rows to populate them
}

function createEmployeeRow(employee, monthlyDays, rowType, isMainRow) {
    const tr = document.createElement('tr');
    tr.className = isMainRow ? 'employee-row' : 'activity-row hidden';
    tr.setAttribute('data-employee-id', employee.id);

    if (isMainRow) {
        const nameCell = document.createElement('td');
        nameCell.className = 'employee-info-cell';
        nameCell.rowSpan = 4;
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
            // Inicialización de las celdas de bonos/servicios con 0.
            dayCell.textContent = '0';
        }

        tr.appendChild(dayCell);
    });

    const totalCell = document.createElement('td');
    // Se corrige la clase de la celda de totales, que no lleva 'data-cell' para distinguirla de las celdas de días
    totalCell.className = rowType === 'Actividad' ? 'total-activity-cell' : (rowType === 'Comida' ? 'total-food-cell' : (rowType === 'Bono' ? 'total-field-bonus-cell' : 'total-service-cell'));
    // 👈🏼 CRITICAL: Se inicializa con '0' o '0.00' si no puede ver montos
    if (rowType === 'Actividad') {
        totalCell.innerHTML = '<div class="status-indicator">0</div>';
    } else {
        totalCell.textContent = canSeeAmounts ? '0.00' : '0';
    }

    tr.appendChild(totalCell);

    if (isMainRow) {
        const vacCell = document.createElement('td');
        vacCell.className = 'vacations-cell';
        vacCell.rowSpan = 4;
        vacCell.innerHTML = '<div class="vacations-container"><div class="vacations-value">0</div></div>';
        tr.appendChild(vacCell);

        const breaksCell = document.createElement('td');
        breaksCell.className = 'breaks-cell';
        breaksCell.rowSpan = 4;
        breaksCell.innerHTML = '<div class="breaks-container"><div class="breaks-value">0</div></div>';
        tr.appendChild(breaksCell);

        const utilCell = document.createElement('td');
        utilCell.className = 'utilization-cell';
        utilCell.rowSpan = 4;
        utilCell.innerHTML = '<div class="utilization-container"><div class="utilization-value">0%</div></div>';
        tr.appendChild(utilCell);

        const approvalCell = document.createElement('td');
        approvalCell.className = 'actions-cell';
        approvalCell.rowSpan = 4;

        const employeeAssignment = loadChartAssignments.find(a => a.employee_id === employee.id);
        const isReviewerForEmployee = employeeAssignment && employeeAssignment.reviewer_id === currentUserId;
        const isApproverForEmployee = employeeAssignment && employeeAssignment.approver_id === currentUserId;

        let buttonsHtml = '<div class="actions-container">';
        if (isReviewerForEmployee) {
            buttonsHtml += '<button class="btn-review">Revisar</button>';
        }
        if (isApproverForEmployee) {
            buttonsHtml += '<button class="btn-approve">Aprobar</button>';
        }

        if (!isReviewerForEmployee && !isApproverForEmployee) {
            buttonsHtml = '<span class="no-permissions">Sin permisos</span>';
        } else {
            buttonsHtml += '</div>';
        }

        approvalCell.innerHTML = buttonsHtml;
        tr.appendChild(approvalCell);
    }

    return tr;
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
            if (dataCells[index]) {
                dataCells[index].style.display = shouldShow ? "" : "none";
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
    dayHeaders.forEach((header, index) => {
        header.style.display = "";

        allRows.forEach((row) => {
            const dataCells = row.querySelectorAll(".data-cell");
            // Asegurarse de que estamos manipulando solo las celdas de días
            if (dataCells[index]) {
                dataCells[index].style.display = "";
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
 * Calcula y renderiza los totales de días y la utilización.
 */
function calculateAndRenderTotals() {
    const employeeRows = document.querySelectorAll('.employee-row');
    const workingActivityTypes = ['B', 'P', 'TC', 'V', 'E', 'C'];

    // 1. OBTENER TODOS LOS ENCABEZADOS DE DÍA PARA EL CÁLCULO MENSUAL
    const allDayHeaders = Array.from(document.querySelectorAll('.day-header'));

    // 2. CALCULAR EL TOTAL DE DÍAS LABORABLES EN EL MES COMPLETO (ESTE ES EL CAMBIO CLAVE)
    // Este valor será nuestro denominador fijo para el cálculo de utilización.
    const totalWorkingDaysInFullMonth = allDayHeaders.filter(header =>
        header.getAttribute('data-quincena-1') === 'true' ||
        header.getAttribute('data-quincena-2') === 'true'
    ).length;

    // 3. Filtrar los encabezados que están visibles actualmente para los cálculos de cada período.
    const visibleDayHeaders = allDayHeaders.filter(header => header.style.display !== 'none');

    employeeRows.forEach(employeeRow => {
        const employeeId = employeeRow.getAttribute('data-employee-id');
        const employeeData = workLogsData.find(log => log.employee_id.toString() === employeeId);

        if (!employeeData || !employeeData.daily_activities) {
            return;
        }

        let totalDays = 0; // Días de actividad contables (Base, Pozo, etc.) EN EL PERÍODO VISIBLE
        let totalFood = 0;
        let totalFieldBonus = 0;
        let totalService = 0;
        let totalBreaksAndOthers = 0; // Días D, PE, A, M
        let totalVacations = 0; // Días VAC

        const dailyActivitiesMap = new Map(employeeData.daily_activities.map(activity => [activity.date, activity]));

        // Filtramos las celdas de actividad principales visibles
        const allActivityCells = Array.from(employeeRow.querySelectorAll('.data-cell')).filter(cell => cell.getAttribute('data-date'));
        const visibleActivityCells = allActivityCells.filter(cell => cell.style.display !== 'none');

        visibleActivityCells.forEach(cell => {
            const dateAttr = cell.getAttribute('data-date');
            const dailyActivity = dailyActivitiesMap.get(dateAttr);

            if (dailyActivity) {
                const activityType = dailyActivity.activity_type ? dailyActivity.activity_type.toUpperCase() : 'N';

                if (workingActivityTypes.includes(activityType)) {
                    totalDays++;
                }

                if (activityType === 'VAC') {
                    totalVacations++;
                }

                if (['D', 'PE', 'A', 'M'].includes(activityType)) {
                    totalBreaksAndOthers++;
                }

                // Sumar bonos/servicios
                const dailyFoodBonus = dailyActivity.food_bonuses ? dailyActivity.food_bonuses.reduce((sum, bonus) => sum + Number(bonus.daily_amount || 0), 0) : 0;
                totalFood += dailyFoodBonus;


                const dailyFieldBonus = dailyActivity.field_bonuses ? dailyActivity.field_bonuses.reduce((sum, bonus) => {
                    const amount = Number(bonus.daily_amount || 0);
                    if (bonus.currency === 'USD' && bonus.usd_to_mxn_rate) {
                        return sum + (amount * Number(bonus.usd_to_mxn_rate));
                    }
                    return sum + amount;
                }, 0) : 0;
                totalFieldBonus += dailyFieldBonus;

                const dailyServiceAmount = dailyActivity.services_list ? dailyActivity.services_list.reduce((sum, service) => sum + Number(service.amount || 0), 0) : 0;
                totalService += dailyServiceAmount;
            }
        });

        // Renderizar Totales en las 4 filas
        const activityRow = employeeRow;
        const foodRow = activityRow.nextElementSibling;
        const fieldBonusRow = foodRow.nextElementSibling;
        const serviceRow = fieldBonusRow.nextElementSibling;

        const activityTotalCell = activityRow.querySelector('.total-activity-cell');
        const foodTotalCell = foodRow.querySelector('.total-food-cell');
        const fieldBonusTotalCell = fieldBonusRow.querySelector('.total-field-bonus-cell');
        const serviceTotalCell = serviceRow.querySelector('.total-service-cell');

        if (activityTotalCell) activityTotalCell.querySelector('.status-indicator').textContent = totalDays;

        // 👈🏼 CRITICAL: Lógica de visualización del total de Comida y Servicio (CORRECCIÓN AQUÍ)
        if (canSeeAmounts) {
            // Si puede ver montos, muestra .toFixed(2), incluso si es 0
            if (foodTotalCell) foodTotalCell.textContent = totalFood.toFixed(2);
            if (fieldBonusTotalCell) fieldBonusTotalCell.textContent = totalFieldBonus.toFixed(2);
            if (serviceTotalCell) serviceTotalCell.textContent = totalService.toFixed(2);
        } else {
            // Si NO puede ver montos, muestra '0' (sin decimales)
            if (foodTotalCell) foodTotalCell.textContent = '0';
            if (fieldBonusTotalCell) fieldBonusTotalCell.textContent = '0';
            if (serviceTotalCell) serviceTotalCell.textContent = '0';
        }

        // Renderizar Saldos y Utilización
        const vacCell = employeeRow.querySelector('.vacations-value');
        if (vacCell) vacCell.textContent = totalVacations;

        const breaksCell = employeeRow.querySelector('.breaks-value');
        if (breaksCell) breaksCell.textContent = totalBreaksAndOthers;

        const utilCell = employeeRow.querySelector('.utilization-value');
        if (utilCell) {
            // 4. MODIFICACIÓN FINAL: Usar el total del mes completo como denominador.
            // La variable `totalDays` ya contiene la suma correcta para el período visible (quincena o mes).
            const utilizationRate = totalWorkingDaysInFullMonth > 0 ? (totalDays / totalWorkingDaysInFullMonth) * 100 : 0;
            const percentage = Math.round(utilizationRate);
            utilCell.textContent = `${percentage}%`;

            // Establecer el color basado en la utilización
            // Esta lógica ahora se aplicará al porcentaje relativo al mes
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
        // Asegurarse de que si no hay datos, al menos se calculen los totales (que serán 0)
        calculateAndRenderTotals();
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
        const servicioRow = fieldBonusRow.nextElementSibling;

        // Se usa querySelectorAll('.data-cell') para asegurar que solo se seleccionan las celdas de días
        const activityCells = Array.from(activityRow.querySelectorAll('.data-cell'));
        const comidaCells = Array.from(comidaRow.querySelectorAll('.data-cell'));
        const fieldBonusCells = Array.from(fieldBonusRow.querySelectorAll('.data-cell'));
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
                if (canSeeAmounts) { // 👈🏼 CRITICAL: Verificar si se pueden ver montos
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
                    // Mostrar conteo si no se pueden ver montos (se mantiene la lógica original de conteo/0)
                    if (comidaCells[index]) {
                        const foodCount = dailyActivity.food_bonuses ? dailyActivity.food_bonuses.length : 0;
                        comidaCells[index].textContent = foodCount > 0 ? foodCount : '0';
                        comidaCells[index].title = dailyActivity.food_bonuses && dailyActivity.food_bonuses.length > 0 ? dailyActivity.food_bonuses.map(b => `${b.bonus_type} x${b.num_daily}`).join(', ') : '';
                    }

                    if (fieldBonusCells[index]) {
                        const bonusCount = dailyActivity.field_bonuses ? dailyActivity.field_bonuses.length : 0;
                        fieldBonusCells[index].textContent = bonusCount > 0 ? bonusCount : '0';
                        fieldBonusCells[index].title = dailyActivity.field_bonuses && dailyActivity.field_bonuses.length > 0 ? dailyActivity.field_bonuses.map(b => b.bonus_type).join(', ') : '';
                    }

                    if (servicioCells[index]) {
                        const serviceCount = dailyActivity.services_list ? dailyActivity.services_list.length : 0;
                        servicioCells[index].textContent = serviceCount > 0 ? serviceCount : '0';
                        servicioCells[index].title = dailyActivity.services_list && dailyActivity.services_list.length > 0 ? dailyActivity.services_list.map(s => s.service_name).join(', ') : '';
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
