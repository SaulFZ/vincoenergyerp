// calendar.js

/**
 * ⚠️ FUNCIÓN CLAVE: Inicializa todos los scripts y eventos del calendario
 * después de que el HTML parcial ha sido cargado en el modal.
 * @param {string | null} employeeId - El ID del empleado actual (null si es vista principal).
 */
function initializeModalCalendarScripts(employeeId) {

    // --- MANEJO DE LA CARGA DE DATOS SEGURO (CORRECCIÓN) ---
    // Aseguramos que el elemento exista antes de intentar leer su textContent.
    const serviceDataScript = document.getElementById('service-data');
    if (!serviceDataScript) {
        console.error('Error: Elemento #service-data no encontrado. La carga de datos de servicio falló.');
        return; // Salir si el elemento crítico no está presente.
    }

    const serviceData = JSON.parse(serviceDataScript.textContent);

    if (!serviceData || typeof serviceData !== 'object') {
        console.error('Datos de servicio no válidos:', serviceData);
        return;
    }
    // --------------------------------------------------------

    // --- DEFINICIÓN DE CONSTANTES (COMIENZO) ---
    // Nota: Si alguno de estos selectores falla (e.g., calendarLoadingOverlay),
    // la variable será 'null', lo que es manejable más adelante.

    const modal = document.getElementById('activityModal');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelBtn = document.getElementById('cancel-activity');
    const saveBtn = document.getElementById('save-activity');
    const loadingSpinner = saveBtn?.querySelector('.loading-spinner'); // Uso de Optional Chaining
    const serviceTabBtn = document.getElementById('service-tab-btn');
    const vacationDaysElement = document.querySelector('p[data-balance-type="vacation"]');
    const restDaysElement = document.querySelector('p[data-balance-type="rest"]');
    const conditionalFields = document.getElementById('conditional-fields');

    // --- NUEVOS ELEMENTOS PARA VIAJE ---
    const travelDestinationField = document.getElementById('travel-destination-field');
    const travelDestinationInput = document.getElementById('travel-destination');
    const travelReasonField = document.getElementById('travel-reason-field');
    const travelReasonInput = document.getElementById('travel-reason');
    // -----------------------------------


    let currentSelectedDate = null;
    let monthlyActivities = {};
    let currentActivity = null;
    let currentPayrollDates = {};
    let vacationDaysAvailable = parseInt(vacationDaysElement?.textContent) || 0;
    let restDaysAvailable = parseInt(restDaysElement?.textContent) || 0;

    let currentEmployeeId = employeeId; // 👈 Guarda el ID aquí

    // Estados que bloquean la edición (Aprobado o Revisado)
    const statusesToBlockField = ['approved', 'reviewed'];
    // Estados que bloquean TODO (Aprobado o Revisado)
    const statusesToBlockAll = ['approved', 'reviewed'];


    function getCSSVariable(varName) {
        return getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
    }

    function getActivityTagColor(activityType) {
        const colorVars = {
            'B': '--work-base',
            'P': '--work-well',
            'TC': '--home-office', // CAMBIO: H -> TC
            'V': '--traveling',
            'D': '--rest',
            'VAC': '--vacation',
            'E': '--training',
            'M': '--medical',
            'C': '--commissioned',
            'A': '--absence',
            'PE': '--permission'
        };
        const cssVar = colorVars[activityType];
        return cssVar ? getCSSVariable(cssVar) : getCSSVariable('--training');
    }

    function getStatusIcon(dayStatus) {
        switch (dayStatus) {
            case 'under_review':
                return `<i class="fa-regular fa-hourglass-half status-icon" style="color: ${getCSSVariable('--under-review')};" title="Bajo Revisión"></i>`;
            case 'approved':
                return `<i class="fas fa-lock status-icon" style="color: ${getCSSVariable('--approved')};" title="Aprobado"></i>`;
            case 'reviewed':
                return `<i class="fas fa-lock-open status-icon" style="color: ${getCSSVariable('--reviewed')};" title="Revisado"></i>`;
            case 'rejected':
                return `<i class="fas fa-exclamation-triangle status-icon" style="color: ${getCSSVariable('--not-approved')};" title="Rechazado"></i>`;
            default:
                return `<i class="fa-regular fa-hourglass-half status-icon" style="color: ${getCSSVariable('--under-review')};" title="Bajo Revisión"></i>`;
        }
    }

    function createActivityHTML(activity) {
        const color = getActivityTagColor(activity.activity_type);
        const statusIcon = getStatusIcon(activity.day_status || 'under_review');
        return `<div class="day-header-info"><div class="activity-tag" style="background-color: ${color};">${activity.activity_description}</div>${statusIcon}</div>`;
    }

    /**
     * Actualiza la UI con los nuevos saldos.
     */
    async function updateBalanceUI(vacationDays, restDays) {
        if (vacationDaysElement) {
            vacationDaysAvailable = parseInt(vacationDays);
            vacationDaysElement.textContent = `${vacationDaysAvailable} días`;
        }
        if (restDaysElement) {
            restDaysAvailable = parseInt(restDays);
            restDaysElement.textContent = `${restDaysAvailable} días`;
        }
    }

    /**
     * Llama al backend para obtener y actualizar los saldos.
     */
async function fetchBalances() {
    try {
        // ⚠️ CORRECCIÓN: Agregar employee_id a la URL
        const employeeParam = currentEmployeeId ? `?employee_id=${currentEmployeeId}` : '';
        const response = await fetch(`/recursoshumanos/loadchart/balances-data${employeeParam}`);
        const data = await response.json();
        if (data.success) {
            updateBalanceUI(data.vacationDays, data.restDays);
        }
    } catch (error) {
        console.error('Error fetching balances:', error);
    }
}

    function handleActivityTypeChange(activityType) {
        const wellNameField = document.getElementById('well-name-field');
        const wellNameInput = document.getElementById('well-name');
        const commissionedField = document.getElementById('commissioned-field');
        const commissionedSelect = document.getElementById('commissioned-select');
        const vacationError = document.getElementById('vacation-balance-error');
        const hasServiceBonusSelect = document.querySelector('input[name="has_service_bonus"]:checked');

        // Mostrar/Ocultar error de Vacaciones
        if (activityType === 'VAC' && vacationDaysAvailable <= 0) {
            vacationError.style.display = 'block';
        } else {
            vacationError.style.display = 'none';
        }

        // Resetear campos de viaje antes de re-evaluar
        travelDestinationField.style.display = 'none';
        travelDestinationInput.value = '';
        travelReasonField.style.display = 'none';
        travelReasonInput.value = '';

        // --- Lógica de campos condicionales por Tipo de Actividad ---
        if (activityType === 'P') { // Trabajo en Pozo
            wellNameField.style.display = 'block';
            commissionedField.style.display = 'none';
            commissionedSelect.selectedIndex = 0;
            conditionalFields.style.display = 'block'; // Mostrar bonos/servicio
            handleServiceBonusChange(hasServiceBonusSelect?.value); // Re-evaluar pestaña de servicio
        } else if (activityType === 'C') { // Comisionado
            wellNameField.style.display = 'none';
            wellNameInput.value = '';
            commissionedField.style.display = 'block';
            conditionalFields.style.display = 'none'; // Ocultar bonos/servicio
            handleServiceBonusChange('no'); // Ocultar pestaña de servicio
        } else if (activityType === 'TC') { // Trabajo en Casa (Anteriormente H)
            wellNameField.style.display = 'none';
            wellNameInput.value = '';
            commissionedField.style.display = 'none';
            commissionedSelect.selectedIndex = 0;
            conditionalFields.style.display = 'none'; // Ocultar bonos/servicio
            handleServiceBonusChange('no'); // Ocultar pestaña de servicio
        } else if (activityType === 'V') { // Viaje (NUEVO)
            wellNameField.style.display = 'none';
            wellNameInput.value = '';
            commissionedField.style.display = 'none';
            commissionedSelect.selectedIndex = 0;
            conditionalFields.style.display = 'none'; // Ocultar bonos/servicio
            handleServiceBonusChange('no'); // Ocultar pestaña de servicio
            travelDestinationField.style.display = 'block'; // MOSTRAR DESTINO
            travelReasonField.style.display = 'block'; // MOSTRAR MOTIVO
        } else { // Otros (B, D, VAC, E, M, A, PE, N)
            wellNameField.style.display = 'none';
            wellNameInput.value = '';
            commissionedField.style.display = 'none';
            commissionedSelect.selectedIndex = 0;
            conditionalFields.style.display = 'none'; // Ocultar bonos/servicio
            handleServiceBonusChange('no'); // Ocultar pestaña de servicio
        }
        // --- FIN Lógica de campos condicionales ---
    }


    function handleServiceBonusChange(hasServiceBonus) {
        const serviceTab = document.getElementById('service-tab');
        const activityTabBtn = document.querySelector('.tab-btn[data-tab="activity"]');
        const isActivityP = document.getElementById('activity-type').value === 'P';


        if (hasServiceBonus === 'si' && isActivityP) {
            serviceTabBtn.style.display = 'block';
        } else {
            serviceTabBtn.style.display = 'none';
            if (serviceTab.classList.contains('active')) {
                activityTabBtn.click();
            }
            // Asegúrate de resetear el estado interno del radio si se oculta
            if (hasServiceBonus === 'no' || !isActivityP) {
                document.getElementById('service-bonus-no').checked = true;
                document.querySelector('.service-bonus-option[data-value="no"]').classList.add('selected');
                document.querySelector('.service-bonus-option[data-value="si"]').classList.remove('selected');
            }
            resetServiceForm();
        }
    }

    function populatePayrollPeriodOptions() {
        const payrollPeriodSelect = document.getElementById('payroll-period');
        const monthSpan = document.querySelector('.month-navigation span');
        const currentMonth = parseInt(monthSpan.getAttribute('data-month'));
        const currentYear = parseInt(monthSpan.getAttribute('data-year'));
        const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        const prevMonth = currentMonth === 1 ? 12 : currentMonth - 1;
        const prevYear = currentMonth === 1 ? currentYear - 1 : currentYear;
        const prevMonthName = monthNames[prevMonth - 1];

        payrollPeriodSelect.innerHTML = '';
        const defaultOption = document.createElement('option');
        defaultOption.value = 'current';
        defaultOption.textContent = 'Quincena Actual';
        payrollPeriodSelect.appendChild(defaultOption);

        let selectedDate = new Date(currentSelectedDate + 'T00:00:00');
        const q2Start = currentPayrollDates.q2_start ? new Date(currentPayrollDates.q2_start + 'T00:00:00') : null;
        const q2End = currentPayrollDates.q2_end ? new Date(currentPayrollDates.q2_end + 'T23:59:59') : null;

        // Si la fecha seleccionada está en la segunda quincena del mes actual
        if (q2Start && q2End && selectedDate >= q2Start && selectedDate <= q2End) {
            const currentQ1Option = document.createElement('option');
            currentQ1Option.value = 'current_q1';
            currentQ1Option.textContent = `Primera Quincena - ${monthNames[currentMonth - 1]} ${currentYear}`;
            payrollPeriodSelect.appendChild(currentQ1Option);
        }

        // Si la fecha seleccionada está en la primera o segunda quincena del mes actual (para incluir el Q2 anterior)
        // Se asume que solo se puede seleccionar el Q2 anterior si estamos en el Q1 del mes actual o después.
        if (currentMonth !== 1) { // Lógica simple para evitar ir al año anterior en el primer mes
            // No necesitamos la lógica compleja de Q1Start/Q1End aquí, solo si la fecha actual está en el mes actual y es Q1 o Q2.
            // Simplificamos: si el mes actual tiene una configuración de nómina (asumimos que sí) y NO es enero,
            // o si es enero y el año es posterior a MIN_YEAR, incluimos la opción del Q2 anterior.
            const prevQ2Option = document.createElement('option');
            prevQ2Option.value = 'previous_q2';
            prevQ2Option.textContent = `Segunda Quincena - ${prevMonthName} ${prevYear}`;
            payrollPeriodSelect.appendChild(prevQ2Option);
        }

        if (currentActivity && currentActivity.services_list && currentActivity.services_list.length > 0) {
            const service = currentActivity.services_list[0];
            if (service.payroll_period_override) {
                payrollPeriodSelect.value = service.payroll_period_override;
            }
        }
    }
    function showRejectionMessages(activity) {
        clearRejectionMessages();
        if (!activity) return;

        // Mensaje de rechazo general de actividad
        if (activity.rejection_reason && activity.activity_status && activity.activity_status.toLowerCase() === 'rejected') {
            showRejectionMessage('activity-type-select', activity.rejection_reason);
        }

        // Mensaje de rechazo de servicio
        if (activity.services_list && activity.services_list.length > 0) {
            activity.services_list.forEach(service => {
                if (service.status && service.status.toLowerCase() === 'rejected' && service.rejection_reason) {
                    showRejectionMessage('work-type-options', service.rejection_reason, true);
                }
            });
        }

        // Mensaje de rechazo de bono de comida
        if (activity.food_bonuses && activity.food_bonuses.length > 0) {
            activity.food_bonuses.forEach(bonus => {
                if (bonus.status && bonus.status.toLowerCase() === 'rejected' && bonus.rejection_reason) {
                    showRejectionMessage('food-bonus', bonus.rejection_reason);
                }
            });
        }

        // Mensaje de rechazo de bono de campo
        if (activity.field_bonuses && activity.field_bonuses.length > 0) {
            activity.field_bonuses.forEach(bonus => {
                if (bonus.status && bonus.status.toLowerCase() === 'rejected' && bonus.rejection_reason) {
                    showRejectionMessage('field-bonus', bonus.rejection_reason);
                }
            });
        }
    }

    function showRejectionMessage(fieldId, message, isServiceTab = false) {
        const field = document.getElementById(fieldId);
        if (field) {
            const parentGroup = field.closest('.form-group');
            if (parentGroup) {
                // Previene duplicados
                if (parentGroup.querySelector('.rejection-message')) return;

                const rejectionDiv = document.createElement('div');
                rejectionDiv.className = 'rejection-message';
                rejectionDiv.innerHTML = `<div class="rejection-content"><i class="fas fa-exclamation-triangle"></i><span>Motivo de rechazo: ${message}</span></div>`;

                // Si es un campo de la pestaña de servicio, lo inyectamos al final del form-group
                if (isServiceTab) {
                    parentGroup.appendChild(rejectionDiv);
                } else {
                    // Para otros campos, inyectamos después del elemento para mayor visibilidad
                    field.parentNode.insertBefore(rejectionDiv, field.nextSibling);
                }
            }
        }
    }

    function clearRejectionMessages() {
        document.querySelectorAll('.rejection-message').forEach(el => el.remove());
    }

    function clearAllBlocksAndMessages() {
        clearRejectionMessages();
        document.querySelectorAll('.lock-indicator').forEach(el => el.remove());
        unlockAllFields();
    }

    function unlockAllFields() {
        // Elementos en pestaña Actividad
        const activityElements = [
            document.getElementById('activity-type-select'), ...document.querySelectorAll('.activity-option'),
            document.getElementById('commissioned-select'), document.getElementById('well-name'),
            // --- NUEVOS CAMPOS DE VIAJE ---
            document.getElementById('travel-destination'), document.getElementById('travel-reason'),
            // -----------------------------
            ...document.querySelectorAll('.service-bonus-option'),
            document.getElementById('food-bonus'), document.getElementById('field-bonus')
        ];
        // Elementos en pestaña Servicio
        const serviceElements = [
            ...document.querySelectorAll('.work-type-option'),
            document.getElementById('service-type'), document.getElementById('service-performed'),
            document.getElementById('service'), document.getElementById('payroll-period'),
            document.getElementById('service-amount')
        ];

        [...activityElements, ...serviceElements].forEach(element => {
            if (element) {
                if (element.classList.contains('custom-select')) {
                    element.classList.remove('locked');
                } else if (element.classList.contains('work-type-option') || element.classList.contains('activity-option') || element.classList.contains('service-bonus-option')) {
                    element.style.pointerEvents = '';
                    element.style.opacity = '';
                } else if (element.tagName === 'SELECT' || element.tagName === 'INPUT') {
                    element.disabled = false;
                    element.style.backgroundColor = '';
                    element.style.color = '';
                    element.style.cursor = '';
                }
                const parentGroup = element.closest('.form-group');
                if (parentGroup) {
                    const lockIndicator = parentGroup.querySelector('.lock-indicator');
                    if (lockIndicator) lockIndicator.remove();
                }
            }
        });
        saveBtn.disabled = false;
        loadingSpinner.style.display = 'none';
    }

    /**
     * @param {Object} activity - El objeto de actividad para el día.
     * @param {string} fieldType - El tipo de campo ('activity', 'service', 'food_bonus', 'field_bonus').
     * @param {number} itemIndex - Índice del item (generalmente 0 para bonos/servicios).
     * @returns {string} - El estado del campo ('approved', 'reviewed', 'rejected', 'under_review').
     */
    function getFieldStatus(activity, fieldType, itemIndex = 0) {
        if (!activity) return 'under_review';
        let status = 'under_review'; // Valor por defecto

        switch (fieldType) {
            case 'activity':
                status = activity.activity_status || 'under_review';
                break;
            case 'service':
                if (activity.services_list && activity.services_list[itemIndex]) {
                    status = activity.services_list[itemIndex].status || 'under_review';
                }
                break;
            case 'food_bonus':
                if (activity.food_bonuses && activity.food_bonuses[itemIndex]) {
                    status = activity.food_bonuses[itemIndex].status || 'under_review';
                }
                break;
            case 'field_bonus':
                if (activity.field_bonuses && activity.field_bonuses[itemIndex]) {
                    status = activity.field_bonuses[itemIndex].status || 'under_review';
                }
                break;
        }
        // CAMBIO: Nos aseguramos de que SIEMPRE devuelva el estado en minúsculas.
        return status.toLowerCase();
    }

    function toggleFieldLock(element, isLocked, status) {
        if (!element) return;
        const parentGroup = element.closest('.form-group');
        if (!parentGroup) return;

        const isCustomSelect = element.classList.contains('custom-select');
        const isOption = element.classList.contains('work-type-option') || element.classList.contains('activity-option') || element.classList.contains('service-bonus-option');
        const lowerCaseStatus = status ? status.toLowerCase() : '';

        // --- MANEJO DEL INDICADOR DE ESTADO (LA ETIQUETA) ---
        // Primero, limpiamos cualquier etiqueta que ya exista para evitar duplicados.
        const existingIndicator = parentGroup.querySelector('.lock-indicator');
        if (existingIndicator) existingIndicator.remove();

        // Solo creamos la etiqueta para los estados finales: aprobado, revisado o rechazado.
        if (['approved', 'reviewed', 'rejected'].includes(lowerCaseStatus)) {
            const lockIndicator = document.createElement('div');
            lockIndicator.className = 'lock-indicator';

            let message = '', icon = '', bgColor = '';

            switch (lowerCaseStatus) {
                case 'approved':
                    message = 'Aprobado';
                    icon = 'fas fa-check-circle'; // Icono mejorado
                    bgColor = getCSSVariable('--approved');
                    break;
                case 'reviewed':
                    message = 'Revisado';
                    icon = 'fas fa-user-check'; // Icono mejorado
                    bgColor = getCSSVariable('--reviewed');
                    break;
                case 'rejected':
                    message = 'Rechazado';
                    icon = 'fas fa-regular fa-triangle-exclamation'; // Icono para rechazado
                    bgColor = getCSSVariable('--not-approved');
                    break;
            }

            lockIndicator.innerHTML = `<i class="${icon}"></i> ${message}`;
            lockIndicator.style.backgroundColor = bgColor;
            parentGroup.appendChild(lockIndicator);
        }

        // --- MANEJO DEL BLOQUEO DEL CAMPO (Esta lógica no cambia) ---
        if (isLocked) {
            // Aplica estilos de bloqueo
            if (isCustomSelect) {
                element.classList.add('locked');
            } else if (isOption) {
                element.style.pointerEvents = 'none';
                element.style.opacity = '0.6';
            } else {
                element.disabled = true;
                element.style.backgroundColor = '#f5f5f5';
                element.style.color = '#999';
                element.style.cursor = 'not-allowed';
            }
        } else {
            // Elimina estilos de bloqueo
            if (isCustomSelect) {
                element.classList.remove('locked');
            } else if (isOption) {
                element.style.pointerEvents = '';
                element.style.opacity = '';
            } else {
                element.disabled = false;
                element.style.backgroundColor = '';
                element.style.color = '';
                element.style.cursor = '';
            }
        }
    }

    function applyFieldLocks(activity) {
        clearAllBlocksAndMessages();
        saveBtn.disabled = false;

        if (!activity) return;

        const dayStatus = activity.day_status ? activity.day_status.toLowerCase() : '';

        // Determinar si el día completo está bloqueado (Aprobado o Revisado)
        const isDayCompletelyBlocked = statusesToBlockAll.includes(dayStatus);

        // Si el día está completamente bloqueado, aplicamos bloqueo a todo y salimos.
        if (isDayCompletelyBlocked) {
            const allFormElements = document.querySelectorAll('#activity-tab select, #activity-tab .custom-select, #activity-tab input:not([type="radio"]), #service-tab select, #service-tab input');
            document.querySelectorAll('.work-type-option, .activity-option, .service-bonus-option').forEach(el => toggleFieldLock(el, true, dayStatus));
            allFormElements.forEach(el => {
                if (el.id !== 'activity-date' && el.id !== 'service-amount') {
                    toggleFieldLock(el, true, dayStatus);
                }
            });
            saveBtn.disabled = true;
            showRejectionMessages(activity);
            return;
        }

        // --- Pestaña Actividad ---
        const activityStatus = getFieldStatus(activity, 'activity');
        const isActivityLocked = statusesToBlockField.includes(activityStatus);

        const activityTypeSelectContainer = document.getElementById('activity-type-select');
        const commissionedSelect = document.getElementById('commissioned-select');
        const wellNameInput = document.getElementById('well-name');
        // --- NUEVOS CAMPOS DE VIAJE ---
        const travelDestinationInput = document.getElementById('travel-destination');
        const travelReasonInput = document.getElementById('travel-reason');
        // -----------------------------
        const activityTypeOptions = document.querySelectorAll('.activity-option');

        toggleFieldLock(activityTypeSelectContainer, isActivityLocked, activityStatus);
        activityTypeOptions.forEach(option => toggleFieldLock(option, isActivityLocked, activityStatus));
        toggleFieldLock(commissionedSelect, isActivityLocked, activityStatus);
        toggleFieldLock(wellNameInput, isActivityLocked, activityStatus);
        // --- Bloqueo de nuevos campos de Viaje ---
        toggleFieldLock(travelDestinationInput, isActivityLocked, activityStatus);
        toggleFieldLock(travelReasonInput, isActivityLocked, activityStatus);
        // -----------------------------------------


        // ----------------------------------------------------
        // ✅ LÓGICA CLAVE: ESTADO DEL BOTÓN "¿BONO DE SERVICIO?"
        // ----------------------------------------------------
        const serviceBonusOptions = document.querySelectorAll('.service-bonus-option');
        const serviceStatus = getFieldStatus(activity, 'service');

        // 1. Determinar el estado base (actividad principal o servicio).
        let statusForServiceBonus = activityStatus; // Por defecto: estado de la actividad

        if (serviceStatus === 'approved' || serviceStatus === 'reviewed') {
            // Si el servicio tiene un estado de bloqueo, el botón se bloquea con el estado del servicio.
            statusForServiceBonus = serviceStatus;
        } else if (serviceStatus === 'rejected') {
            // Si el servicio fue rechazado, el botón se etiqueta como rechazado y no se bloquea la edición si la actividad no estaba bloqueada.
            statusForServiceBonus = 'rejected';
        }

        // 2. Determinar si se bloquea
        // Si el estado de la actividad (que incluye 'rejected') o el estado del servicio es de bloqueo.
        const isServiceBonusLocked = statusesToBlockField.includes(statusForServiceBonus);

        // Aplicamos el estado y bloqueo correctos a los botones.
        serviceBonusOptions.forEach(option => toggleFieldLock(option, isServiceBonusLocked, statusForServiceBonus));
        // ----------------------------------------------------
        // ✅ FIN LÓGICA CLAVE
        // ----------------------------------------------------


        // --- Bonos ---
        const foodBonusStatus = getFieldStatus(activity, 'food_bonus');
        const isFoodBonusLocked = statusesToBlockField.includes(foodBonusStatus);
        const foodBonusSelect = document.getElementById('food-bonus');
        toggleFieldLock(foodBonusSelect, isFoodBonusLocked, foodBonusStatus);

        const fieldBonusStatus = getFieldStatus(activity, 'field_bonus');
        const isFieldBonusLocked = statusesToBlockField.includes(fieldBonusStatus);
        const fieldBonusSelect = document.getElementById('field-bonus');
        toggleFieldLock(fieldBonusSelect, isFieldBonusLocked, fieldBonusStatus);

        // --- Pestaña Servicio ---
        const isServiceLocked = statusesToBlockField.includes(serviceStatus);
        const workTypeOptions = document.querySelectorAll('.work-type-option');
        const serviceTypeSelect = document.getElementById('service-type');
        const servicePerformedSelect = document.getElementById('service-performed');
        const serviceSelect = document.getElementById('service');
        const payrollPeriodSelect = document.getElementById('payroll-period');
        const serviceAmountInput = document.getElementById('service-amount');

        workTypeOptions.forEach(option => toggleFieldLock(option, isServiceLocked, serviceStatus));
        toggleFieldLock(serviceTypeSelect, isServiceLocked, serviceStatus);
        toggleFieldLock(servicePerformedSelect, isServiceLocked, serviceStatus);
        toggleFieldLock(serviceSelect, isServiceLocked, serviceStatus);
        toggleFieldLock(payrollPeriodSelect, isServiceLocked, serviceStatus);
        toggleFieldLock(serviceAmountInput, isServiceLocked, serviceStatus);

        showRejectionMessages(activity);
    }

    function populateModalWithActivity(activity) {
        currentActivity = activity;
        resetActivityOptions();
        resetServiceForm();
        resetAdditionalForms();
        resetBonusFields();
        clearAllBlocksAndMessages();

        const activityTabBtn = document.querySelector('.tab-btn[data-tab="activity"]');
        const serviceTabButton = document.querySelector('.tab-btn[data-tab="service"]');
        const vacationError = document.getElementById('vacation-balance-error');
        const isActivityP = activity.activity_type === 'P';

        // Mostrar/Ocultar campos condicionales
        conditionalFields.style.display = isActivityP ? 'block' : 'none';

        // Mostrar/Ocultar error de Vacaciones si aplica
        if (activity.activity_type === 'VAC' && vacationDaysAvailable <= 0) {
            vacationError.style.display = 'block';
        } else {
            vacationError.style.display = 'none';
        }


        const hasService = activity.services_list && activity.services_list.length > 0;
        if (hasService && isActivityP) {
            serviceTabBtn.style.display = 'block';
            document.getElementById('service-bonus-yes').checked = true;
            document.querySelector('.service-bonus-option[data-value="si"]').classList.add('selected');
            document.querySelector('.service-bonus-option[data-value="no"]').classList.remove('selected');
            if (!document.querySelector('.tab-btn.active')) {
                serviceTabButton.click();
            }
        } else {
            serviceTabBtn.style.display = 'none';
            if (document.getElementById('service-tab').classList.contains('active')) {
                activityTabBtn.click();
            }
            document.getElementById('service-bonus-no').checked = true;
            document.querySelector('.service-bonus-option[data-value="no"]').classList.add('selected');
            document.querySelector('.service-bonus-option[data-value="si"]').classList.remove('selected');
        }

        const activityTypeOption = document.querySelector(`.activity-option[data-value="${activity.activity_type}"]`);
        if (activityTypeOption) {
            activityTypeOption.classList.add('selected');
            document.getElementById('activity-type').value = activity.activity_type;
            document.querySelector('#activity-type-header .placeholder').textContent = activityTypeOption.querySelector('.activity-label').textContent;

            // Llama a la lógica de campos condicionales
            handleActivityTypeChange(activity.activity_type);
        } else {
            document.getElementById('activity-type').value = activity.activity_type || 'N';
            document.querySelector('#activity-type-header .placeholder').textContent = activity.activity_type === 'N' ? 'Ninguna' : 'Seleccionar actividad...';
            handleActivityTypeChange(activity.activity_type || 'N');
        }

        if (activity.activity_type === 'C' && activity.commissioned_to) {
            document.getElementById('commissioned-field').style.display = 'block';
            document.getElementById('commissioned-select').value = activity.commissioned_to;
        }
        if (activity.well_name) {
            document.getElementById('well-name').value = activity.well_name;
        }

        // --- RELLENAR NUEVOS CAMPOS DE VIAJE ---
        if (activity.activity_type === 'V') {
            travelDestinationField.style.display = 'block';
            travelReasonField.style.display = 'block';
            document.getElementById('travel-destination').value = activity.travel_destination || '';
            document.getElementById('travel-reason').value = activity.travel_reason || '';
        }
        // ---------------------------------------

        if (hasService) {
            // Llama a la versión actualizada de la función
            populateServiceData(activity.services_list[0]);
        }

        if (activity.food_bonuses && activity.food_bonuses.length > 0) {
            const foodBonus = activity.food_bonuses[0];
            if (foodBonus.num_daily) {
                document.getElementById('food-bonus').value = foodBonus.num_daily;
            }
        }

        if (activity.field_bonuses && activity.field_bonuses.length > 0) {
            const fieldBonus = activity.field_bonuses[0];
            const fieldBonusSelect = document.getElementById('field-bonus');
            if (fieldBonus.bonus_identifier) {
                fieldBonusSelect.value = fieldBonus.bonus_identifier;
            }
        }

        setTimeout(() => {
            applyFieldLocks(activity);
        }, 200);
    }

    // =========================================================================
    // ✅ FUNCIONES AUXILIARES REQUERIDAS PARA EL REPOBLADO EN CASCADA
    // Se extrae la lógica de repoblado de 'change' y 'populateServiceData' en
    // funciones dedicadas sin efectos secundarios (como disparar eventos)
    // =========================================================================

    /**
     * Rellena las opciones del selector de 'Servicio Realizado' (Nivel 2)
     * @param {string} workType - 'Tierra' o 'Marina'
     * @param {string} serviceTypeFormatted - El valor (value) del tipo de servicio.
     * @param {string} [selectedPerformedId=null] - El ID a seleccionar.
     */
    function updateServicePerformedOptions(workType, serviceTypeFormatted, selectedPerformedId = null) {
        const servicePerformedSelect = document.getElementById('service-performed');
        const serviceSelect = document.getElementById('service');

        // Limpiar niveles inferiores
        servicePerformedSelect.innerHTML = '<option value="">Seleccionar servicio realizado...</option>';
        serviceSelect.innerHTML = '<option value="">Seleccionar servicio...</option>';
        document.getElementById('service').disabled = true;
        document.getElementById('service-amount-group').style.display = 'none';

        if (workType && serviceTypeFormatted) {
            // Habilitar temporalmente para poder añadir las options
            servicePerformedSelect.disabled = false;

            const uniquePerformed = [...new Set(serviceData[workType]
                .filter(item => item.service_type.toLowerCase().replace(/\s+/g, '_') === serviceTypeFormatted)
                .map(item => item.service_performed))];

            uniquePerformed.forEach(performed => {
                const option = document.createElement('option');
                option.value = performed.toLowerCase().replace(/\s+/g, '_');
                option.textContent = performed;
                servicePerformedSelect.appendChild(option);
            });

            if (selectedPerformedId) {
                servicePerformedSelect.value = selectedPerformedId;
                // Si hay un ID seleccionado, pasa al siguiente nivel
                updateServiceOptions(workType, serviceTypeFormatted, selectedPerformedId);
            }
        }
    }

    /**
     * Rellena las opciones del selector de 'Servicio' (Nivel 3)
     * @param {string} workType - 'Tierra' o 'Marina'
     * @param {string} serviceTypeFormatted - El valor (value) del tipo de servicio.
     * @param {string} servicePerformedFormatted - El valor (value) del servicio realizado.
     * @param {string} [selectedServiceId=null] - El ID del servicio a seleccionar.
     */
    function updateServiceOptions(workType, serviceTypeFormatted, servicePerformedFormatted, selectedServiceId = null) {
        const serviceSelect = document.getElementById('service');
        serviceSelect.innerHTML = '<option value="">Seleccionar servicio...</option>';
        const serviceAmountGroup = document.getElementById('service-amount-group');
        const serviceAmountInput = document.getElementById('service-amount');
        serviceAmountGroup.style.display = 'none';
        serviceAmountInput.value = '';


        if (workType && serviceTypeFormatted && servicePerformedFormatted) {
            // Habilitar temporalmente para poder añadir las options
            serviceSelect.disabled = false;

            const services = serviceData[workType].filter(
                item => item.service_type.toLowerCase().replace(/\s+/g, '_') === serviceTypeFormatted &&
                    item.service_performed.toLowerCase().replace(/\s+/g, '_') === servicePerformedFormatted
            );

            services.forEach(item => {
                const option = document.createElement('option');
                option.value = item.identifier;
                option.textContent = item.service_description;
                option.setAttribute('data-amount', item.amount);
                option.setAttribute('data-currency', item.currency);
                option.setAttribute('data-performed', item.service_performed);
                serviceSelect.appendChild(option);
            });

            if (selectedServiceId) {
                serviceSelect.value = selectedServiceId;
                // Al seleccionar el servicio, actualiza el monto.
                const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const amount = selectedOption.getAttribute('data-amount');
                    const currency = selectedOption.getAttribute('data-currency');
                    if (amount && currency) {
                        serviceAmountInput.value = `${currency} ${parseFloat(amount).toFixed(2)}`;
                        serviceAmountGroup.style.display = 'block';
                    }
                }
            }
        }
    }


    // =========================================================================
    // ✅ FUNCIÓN CORREGIDA: populateServiceData (Usa las nuevas auxiliares)
    // =========================================================================
    function populateServiceData(service) {
        let operationType = null;
        let fullServiceData = null;
        const serviceTypeSelect = document.getElementById('service-type');
        const servicePerformedSelect = document.getElementById('service-performed');
        const serviceSelect = document.getElementById('service');
        const serviceAmountGroup = document.getElementById('service-amount-group');

        // Limpiar el monto antes de cualquier procesamiento
        document.getElementById('service-amount').value = '';
        serviceAmountGroup.style.display = 'none';

        // Buscar la data completa del servicio
        for (const [opType, services] of Object.entries(serviceData)) {
            if (Array.isArray(services)) {
                fullServiceData = services.find(s => s.identifier === service.service_identifier);
                if (fullServiceData) {
                    // CAMBIO: La clave de 'operation_type' puede venir como 'Tierra' o 'Marino'
                    // Pero los data-value de los radios son 'Tierra' y 'Marina'. Usamos la clave de los radios.
                    operationType = opType === 'Marino' ? 'Marina' : opType;
                    break;
                }
            }
        }

        if (operationType && fullServiceData) {
            // 1. Tipo de Trabajo (Radio Buttons)
            let workTypeValue = operationType;
            const workTypeOption = document.querySelector(`.work-type-option[data-value="${workTypeValue}"]`);
            if (workTypeOption) {
                workTypeOption.querySelector('input[type="radio"]').checked = true;
                workTypeOption.classList.add('selected');
                document.querySelectorAll(`.work-type-option:not([data-value="${workTypeValue}"])`).forEach(opt => opt.classList.remove('selected'));
            }

            const serviceTypeFormatted = fullServiceData.service_type.toLowerCase().replace(/\s+/g, '_');
            const servicePerformedFormatted = fullServiceData.service_performed.toLowerCase().replace(/\s+/g, '_');
            const serviceIdentifier = service.service_identifier;

            // 2. Tipo de Servicio (Select) - Llama a la función existente y luego selecciona el valor.
            serviceTypeSelect.disabled = false; // Habilitar antes de poblar
            updateServiceTypes(workTypeValue); // Llama a la función que popula el tipo de servicio (Nivel 1)
            serviceTypeSelect.value = serviceTypeFormatted;

            // 3. Servicio Realizado (Select) - Llama a la nueva función auxiliar y luego selecciona el valor.
            servicePerformedSelect.disabled = false;
            // Llama a la función para poblar el Nivel 2. La función se encarga de seleccionar el valor y llamar a Nivel 3.
            updateServicePerformedOptions(workTypeValue, serviceTypeFormatted, servicePerformedFormatted);

            // 4. Servicio (Select) - Llama a la nueva función auxiliar y luego selecciona el valor, lo que también actualiza el monto.
            serviceSelect.disabled = false;
            // Se puede llamar a Nivel 3 directamente o dejar que la llamada anterior lo haga.
            // Para asegurar la cascada completa y la selección final:
            updateServiceOptions(workTypeValue, serviceTypeFormatted, servicePerformedFormatted, serviceIdentifier);

            // 5. Período de Nómina
            if (service.payroll_period_override) {
                document.getElementById('payroll-period').value = service.payroll_period_override;
            }

            // NO TOCAR LOS SELECTS AQUÍ. La función `applyFieldLocks` se encargará de
            // deshabilitar los campos si el estado del servicio lo requiere.
        }
    }


    function openModal(processedDate, displayDate) {
        if (processedDate && displayDate) {
            currentSelectedDate = processedDate;
            document.getElementById('activity-date').value = displayDate;
            const existingActivity = monthlyActivities[processedDate];
            populatePayrollPeriodOptions();

            if (existingActivity) {
                populateModalWithActivity(existingActivity);
            } else {
                currentActivity = null;
                resetActivityOptions();
                resetServiceForm();
                resetAdditionalForms();
                resetBonusFields();
                clearAllBlocksAndMessages();
                document.getElementById('vacation-balance-error').style.display = 'none';
                document.querySelector('.tab-btn[data-tab="activity"]').click();
                conditionalFields.style.display = 'none';
                serviceTabBtn.style.display = 'none';
                document.getElementById('service-bonus-no').checked = true;
                document.querySelector('.service-bonus-option[data-value="no"]').classList.add('selected');
                document.querySelector('.service-bonus-option[data-value="si"]').classList.remove('selected');
            }
        }
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        resetActivityOptions();
        resetServiceForm();
        resetAdditionalForms();
        resetBonusFields();
        clearAllBlocksAndMessages();
        currentSelectedDate = null;
        currentActivity = null;
        conditionalFields.style.display = 'none';
    }

    function resetActivityOptions() {
        document.querySelectorAll('.activity-option').forEach(opt => opt.classList.remove('selected'));
        document.getElementById('activity-type').value = '';
        document.querySelector('#activity-type-header .placeholder').textContent = 'Seleccionar actividad...';
    }

    function resetAdditionalForms() {
        document.getElementById('commissioned-field').style.display = 'none';
        document.getElementById('commissioned-select').selectedIndex = 0;
        document.getElementById('well-name-field').style.display = 'none';
        document.getElementById('well-name').value = '';
        conditionalFields.style.display = 'none';

        // --- NUEVO RESET DE CAMPOS DE VIAJE ---
        travelDestinationField.style.display = 'none';
        travelDestinationInput.value = '';
        travelReasonField.style.display = 'none';
        travelReasonInput.value = '';
        // --------------------------------------
    }

    function resetBonusFields() {
        document.getElementById('food-bonus').selectedIndex = 0;
        document.getElementById('field-bonus').selectedIndex = 0;
    }

    function resetServiceForm() {
        document.querySelectorAll('.work-type-option').forEach(opt => opt.classList.remove('selected'));
        document.querySelectorAll('input[name="work-type"]').forEach(radio => {
            radio.checked = false;
        });
        document.getElementById('service-type').disabled = true;
        document.getElementById('service-type').innerHTML = '<option value="">Seleccionar tipo de servicio...</option>';
        document.getElementById('service-performed').disabled = true;
        document.getElementById('service-performed').innerHTML = '<option value="">Seleccionar servicio realizado...</option>';
        document.getElementById('service').disabled = true;
        document.getElementById('service').innerHTML = '<option value="">Seleccionar servicio...</option>';
        document.getElementById('payroll-period').selectedIndex = 0;
        document.querySelectorAll('.error-message').forEach(el => {
            el.style.display = 'none';
        });
        document.getElementById('service-amount').value = '';
        document.getElementById('service-amount-group').style.display = 'none';
    }

    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    const activityOptions = document.querySelectorAll('.activity-option');
    const activityTypeHeader = document.getElementById('activity-type-header');
    const activityTypeOptionsEl = document.getElementById('activity-type-options');
    const activityTypeSelect = document.getElementById('activity-type');

    activityTypeHeader.addEventListener('click', function () {
        if (currentActivity && statusesToBlockAll.includes(currentActivity.day_status)) return;
        if (currentActivity && statusesToBlockField.includes(getFieldStatus(currentActivity, 'activity'))) return;

        this.classList.toggle('open');
        activityTypeOptionsEl.classList.toggle('open');
    });
    document.addEventListener('click', function (e) {
        if (!activityTypeHeader.contains(e.target) && !activityTypeOptionsEl.contains(e.target)) {
            activityTypeHeader.classList.remove('open');
            activityTypeOptionsEl.classList.remove('open');
        }
    });

    activityOptions.forEach(option => {
        option.addEventListener('click', function () {
            if (currentActivity && statusesToBlockAll.includes(currentActivity.day_status)) return;
            if (currentActivity && statusesToBlockField.includes(getFieldStatus(currentActivity, 'activity'))) return;

            const value = this.getAttribute('data-value');
            activityOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            activityTypeSelect.value = value;
            document.querySelector('#activity-type-header .placeholder').textContent = this.querySelector('.activity-label').textContent;
            activityTypeHeader.classList.remove('open');
            activityTypeOptionsEl.classList.remove('open');

            handleActivityTypeChange(value);
        });
    });

    const serviceBonusOptions = document.querySelectorAll('.service-bonus-option');
    serviceBonusOptions.forEach(option => {
        option.addEventListener('click', function () {
            // Se debe usar isServiceBonusLocked aquí, no statusToBlockAll/Field directamente en el listener
            if (currentActivity) {
                const serviceStatus = getFieldStatus(currentActivity, 'service');
                const activityStatus = getFieldStatus(currentActivity, 'activity');
                let statusForCheck = activityStatus;

                if (serviceStatus === 'approved' || serviceStatus === 'reviewed') {
                    statusForCheck = serviceStatus;
                }

                if (statusesToBlockAll.includes(statusForCheck) || statusesToBlockField.includes(statusForCheck)) return;
            }

            if (document.getElementById('activity-type').value !== 'P') {
                return;
            }
            const value = this.getAttribute('data-value');
            const radio = this.querySelector('input[type="radio"]');
            serviceBonusOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            radio.checked = true;
            handleServiceBonusChange(value);
        });
    });

    const workTypeOptions = document.querySelectorAll('.work-type-option');
    workTypeOptions.forEach(option => {
        option.addEventListener('click', function () {
            if (currentActivity && statusesToBlockAll.includes(currentActivity.day_status)) return;
            if (currentActivity && statusesToBlockField.includes(getFieldStatus(currentActivity, 'service'))) return;

            const value = this.getAttribute('data-value');
            const radio = this.querySelector('input[type="radio"]');
            workTypeOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            radio.checked = true;
            document.getElementById('service-type').disabled = false;
            updateServiceTypes(value);
        });
    });

    function updateServiceTypes(workType) {
        const serviceTypeSelect = document.getElementById('service-type');
        serviceTypeSelect.innerHTML = '<option value="">Seleccionar tipo de servicio...</option>';
        if (serviceData[workType] && Array.isArray(serviceData[workType])) {
            const uniqueTypes = [...new Set(serviceData[workType].map(item => item.service_type))];
            uniqueTypes.forEach(type => {
                const option = document.createElement('option');
                option.value = type.toLowerCase().replace(/\s+/g, '_');
                option.textContent = type;
                serviceTypeSelect.appendChild(option);
            });
        }
        document.getElementById('service-performed').disabled = true;
        document.getElementById('service-performed').innerHTML = '<option value="">Seleccionar servicio realizado...</option>';
        document.getElementById('service').disabled = true;
        document.getElementById('service').innerHTML = '<option value="">Seleccionar servicio...</option>';
        document.getElementById('service-amount').value = '';
        document.getElementById('service-amount-group').style.display = 'none';
    }

    document.getElementById('service-type').addEventListener('change', function () {
        if (currentActivity && statusesToBlockAll.includes(currentActivity.day_status)) return;
        if (currentActivity && statusesToBlockField.includes(getFieldStatus(currentActivity, 'service'))) return;

        const workType = document.querySelector('.work-type-option.selected')?.getAttribute('data-value');
        const serviceTypeId = this.value;

        // Uso de la nueva función auxiliar para poblar Nivel 2 (Servicio Realizado)
        if (workType && serviceTypeId) {
            updateServicePerformedOptions(workType, serviceTypeId);
        } else {
            // Reseteo si la selección es vacía
            document.getElementById('service-performed').disabled = true;
            document.getElementById('service-performed').innerHTML = '<option value="">Seleccionar servicio realizado...</option>';
            document.getElementById('service').disabled = true;
            document.getElementById('service').innerHTML = '<option value="">Seleccionar servicio...</option>';
            document.getElementById('service-amount-group').style.display = 'none';
        }
    });

    document.getElementById('service-performed').addEventListener('change', function () {
        if (currentActivity && statusesToBlockAll.includes(currentActivity.day_status)) return;
        if (currentActivity && statusesToBlockField.includes(getFieldStatus(currentActivity, 'service'))) return;

        const workType = document.querySelector('.work-type-option.selected')?.getAttribute('data-value');
        const serviceTypeId = document.getElementById('service-type').value;
        const performedId = this.value;

        // Uso de la nueva función auxiliar para poblar Nivel 3 (Servicio)
        if (workType && serviceTypeId && performedId) {
            updateServiceOptions(workType, serviceTypeId, performedId);
        } else {
            // Reseteo si la selección es vacía
            document.getElementById('service').disabled = true;
            document.getElementById('service').innerHTML = '<option value="">Seleccionar servicio...</option>';
            document.getElementById('service-amount-group').style.display = 'none';
        }
    });

    document.getElementById('service').addEventListener('change', function () {
        if (currentActivity && statusesToBlockAll.includes(currentActivity.day_status)) return;
        if (currentActivity && statusesToBlockField.includes(getFieldStatus(currentActivity, 'service'))) return;

        const selectedOption = this.options[this.selectedIndex];
        const serviceAmountGroup = document.getElementById('service-amount-group');
        const serviceAmountInput = document.getElementById('service-amount');

        if (selectedOption.value) {
            const amount = selectedOption.getAttribute('data-amount');
            const currency = selectedOption.getAttribute('data-currency');
            if (amount && currency) {
                serviceAmountInput.value = `${currency} ${parseFloat(amount).toFixed(2)}`;
                serviceAmountGroup.style.display = 'block';
            } else {
                serviceAmountInput.value = '';
                serviceAmountGroup.style.display = 'none';
            }
        } else {
            serviceAmountInput.value = '';
            serviceAmountGroup.style.display = 'none';
        }
    });

    document.querySelectorAll('.tab-btn').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelector('.form-tabs .tab-btn.active').classList.remove('active');
            this.classList.add('active');
            const tabId = this.getAttribute('data-tab');
            document.querySelector('.tab-content.active').classList.remove('active');
            document.getElementById(`${tabId}-tab`).classList.add('active');
        });
    });

    async function loadMonthlyActivities(month, year) {
        try {
            // ⚠️ CORRECCIÓN: Agregar employee_id a la URL
            const employeeParam = currentEmployeeId ? `&employee_id=${currentEmployeeId}` : '';
            const response = await fetch(`/recursoshumanos/loadchart/monthly-activities?month=${month}&year=${year}${employeeParam}`);
            const data = await response.json();
            if (data.success) {
                monthlyActivities = {};
                if (data.activities && Array.isArray(data.activities)) {
                    data.activities.forEach(activity => {
                        monthlyActivities[activity.date] = activity;
                    });
                }
                updateCalendarWithActivities();
            }
        } catch (error) {
            console.error('Error al cargar actividades:', error);
        }
    }

    function updateCalendarWithActivities() {
        document.querySelectorAll('.calendar td').forEach(cell => {
            const dayNumberEl = cell.querySelector('.day-number');
            const cellDate = cell.getAttribute('data-date');

            if (!dayNumberEl || !dayNumberEl.textContent.trim() || !cellDate) return;

            const existingHeaderInfo = cell.querySelector('.day-header-info');
            if (existingHeaderInfo) {
                existingHeaderInfo.remove();
            }

            if (monthlyActivities[cellDate]) {
                const activity = monthlyActivities[cellDate];
                const activityHTML = createActivityHTML(activity);
                dayNumberEl.insertAdjacentHTML('afterend', activityHTML);
            }
        });
    }

    saveBtn.addEventListener('click', function () {
        // La validación inicial de 'isValid' y 'error-message' se mantiene igual...
        let isValid = true;
        document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');

        // --- INICIO DE LA NUEVA LÓGICA DE GUARDADO ---

        const formData = {
            date: currentSelectedDate,
            displayed_month: document.querySelector('.month-navigation span').getAttribute('data-month'),
            displayed_year: document.querySelector('.month-navigation span').getAttribute('data-year'),
        };

        // 1. Procesar la Pestaña de Actividad
        const activityStatus = getFieldStatus(currentActivity, 'activity');
        if (!statusesToBlockField.includes(activityStatus)) {
            const activityType = document.getElementById('activity-type').value;
            formData.activity_type = activityType || 'N';
            formData.commissioned_to = activityType === 'C' ? document.getElementById('commissioned-select').value : null;
            formData.well_name = activityType === 'P' ? document.getElementById('well-name').value : null;

            // --- NUEVOS CAMPOS DE VIAJE ---
            formData.travel_destination = activityType === 'V' ? document.getElementById('travel-destination').value.trim() : null;
            formData.travel_reason = activityType === 'V' ? document.getElementById('travel-reason').value.trim() : null;
            // -----------------------------

            const isActivityP = activityType === 'P';
            const hasServiceBonus = document.querySelector('input[name="has_service_bonus"]:checked')?.value;
            formData.has_service_bonus = isActivityP ? hasServiceBonus : 'no';

            // Validaciones de campos obligatorios para la actividad
            if (!activityType) {
                document.getElementById('activity-type-error').style.display = 'block';
                isValid = false;
            }
            if (activityType === 'C' && !formData.commissioned_to) {
                document.getElementById('commissioned-error').style.display = 'block';
                isValid = false;
            }
            if (activityType === 'P' && !formData.well_name.trim()) {
                document.getElementById('well-name-error').style.display = 'block';
                isValid = false;
            }

            // --- NUEVA VALIDACIÓN PARA VIAJE ---
            if (activityType === 'V' && !formData.travel_destination) {
                document.getElementById('travel-destination-error').style.display = 'block';
                isValid = false;
            }
            if (activityType === 'V' && !formData.travel_reason) {
                document.getElementById('travel-reason-error').style.display = 'block';
                isValid = false;
            }
            // -----------------------------------
        }

        // 2. Procesar Bonos (solo si la actividad es 'P' o 'N' con bonos)
        const currentActivityType = document.getElementById('activity-type').value;

        // Bonos se procesan si estamos en Trabajo en Pozo (P), o si la actividad es 'Ninguna (N)' o está vacía,
        // ya que el backend espera que se envíen si el front los permite ver (solo si el día lo permite).
        if (currentActivityType === 'P' || currentActivityType === 'N' || !currentActivityType) {

            // Bono de Comida
            const foodBonusStatus = getFieldStatus(currentActivity, 'food_bonus');
            if (!statusesToBlockField.includes(foodBonusStatus)) {
                formData.food_bonus_number = document.getElementById('food-bonus').value || null;
            }

            // Bono de Campo
            const fieldBonusStatus = getFieldStatus(currentActivity, 'field_bonus');
            if (!statusesToBlockField.includes(fieldBonusStatus)) {
                formData.field_bonus_identifier = document.getElementById('field-bonus').value || null;
            }
        }


        // 3. Procesar la Pestaña de Servicio
        const serviceStatus = getFieldStatus(currentActivity, 'service');
        const wantsService = document.querySelector('input[name="has_service_bonus"]:checked')?.value === 'si';
        const isActivityP = document.getElementById('activity-type').value === 'P';


        if (wantsService && isActivityP && !statusesToBlockField.includes(serviceStatus)) {
            const workTypeSelected = document.querySelector('.work-type-option.selected');
            const serviceValue = document.getElementById('service').value;

            formData.service_identifier = serviceValue || null;
            formData.payroll_period_override = document.getElementById('payroll-period').value;

            // Validaciones para la sección de servicio
            if (!workTypeSelected) {
                document.getElementById('work-type-error').style.display = 'block';
                isValid = false;
                document.querySelector('.tab-btn[data-tab="service"]').click();
            }
            if (!document.getElementById('service-type').value) {
                document.getElementById('service-type-error').style.display = 'block';
                isValid = false;
                document.querySelector('.tab-btn[data-tab="service"]').click();
            }
            if (!document.getElementById('service-performed').value) {
                document.getElementById('service-performed-error').style.display = 'block';
                isValid = false;
                document.querySelector('.tab-btn[data-tab="service"]').click();
            }
            if (!serviceValue) {
                document.getElementById('service-error').style.display = 'block';
                isValid = false;
                document.querySelector('.tab-btn[data-tab="service"]').click();
            }
        } else if (wantsService && isActivityP && statusesToBlockField.includes(serviceStatus)) {
            // Si el usuario quiere un servicio pero está bloqueado, no se envían los campos de servicio para evitar el error.
            // Los valores se mantendrán en el backend.
            formData.service_identifier = currentActivity?.services_list[0]?.service_identifier;
            formData.payroll_period_override = currentActivity?.services_list[0]?.payroll_period_override;
        } else {
            // Si no se quiere servicio o no es actividad 'P' y la sección no está bloqueada
            if (!statusesToBlockField.includes(serviceStatus)) {
                formData.service_identifier = null;
                formData.payroll_period_override = null;
            }
        }


        // Si después de todas las validaciones algo es inválido, detenemos.
        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Campos incompletos',
                text: 'Por favor, rellene todos los campos requeridos en las secciones que no están bloqueadas.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        // --- FIN DE LA NUEVA LÓGICA DE GUARDADO ---

        saveBtn.disabled = true;
        loadingSpinner.style.display = 'block';

        fetch('/recursoshumanos/loadchart/save-activity', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(formData)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Guardado!',
                        text: data.message || 'El registro se ha guardado exitosamente.',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    closeModal();
                    const monthSpan = document.querySelector('.month-navigation span');
                    const currentMonth = parseInt(monthSpan.getAttribute('data-month'));
                    const currentYear = parseInt(monthSpan.getAttribute('data-year'));
                    updateCalendar(currentMonth, currentYear);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al guardar',
                        text: data.message || 'Error desconocido.',
                        confirmButtonText: 'Aceptar'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'No se pudo comunicar con el servidor.',
                    confirmButtonText: 'Aceptar'
                });
            })
            .finally(() => {
                saveBtn.disabled = false;
                loadingSpinner.style.display = 'none';
            });
    });

    function isDayInPayrollPeriod(dateStr) {
        if (!currentPayrollDates.q1_start && !currentPayrollDates.q2_start) {
            return false;
        }

        const date = new Date(dateStr + 'T00:00:00');

        let inQ1 = false;
        if (currentPayrollDates.q1_start && currentPayrollDates.q1_end) {
            const q1Start = new Date(currentPayrollDates.q1_start + 'T00:00:00');
            const q1End = new Date(currentPayrollDates.q1_end + 'T23:59:59');
            inQ1 = date >= q1Start && date <= q1End;
        }
        let inQ2 = false;
        if (currentPayrollDates.q2_start && currentPayrollDates.q2_end) {
            const q2Start = new Date(currentPayrollDates.q2_start + 'T00:00:00');
            const q2End = new Date(currentPayrollDates.q2_end + 'T23:59:59');
            inQ2 = date >= q2Start && date <= q2End;
        }

        // Revisar si la fecha está en la quincena anterior si aplica
        // Para simplificar esta función, solo revisamos el mes actual, ya que el backend solo envía los períodos del mes visible.

        return inQ1 || inQ2;
    }

    function attachDayClickEvents() {
        document.querySelectorAll('.calendar td').forEach(day => {
            const dayNumberEl = day.querySelector('.day-number');
            const dateAttribute = day.getAttribute('data-date');

            if (dayNumberEl && dayNumberEl.textContent.trim() !== '' && dateAttribute) {
                // Solo adjuntar evento a los días que no son de 'other-month'
                if (!day.classList.contains('other-month')) {
                    // Clonar y reemplazar para eliminar listeners anteriores
                    const newDay = day.cloneNode(true);
                    day.parentNode.replaceChild(newDay, day);

                    newDay.addEventListener('click', function () {
                        const dayNum = dayNumberEl.textContent.trim();
                        const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

                        const dateObj = new Date(dateAttribute + 'T00:00:00');
                        const targetMonth = dateObj.getMonth() + 1;
                        const targetYear = dateObj.getFullYear();

                        const formattedDate = dateAttribute;
                        const displayDate = `${dayNum} de ${monthNames[targetMonth - 1]} de ${targetYear}`;

                        if (isDayInPayrollPeriod(formattedDate)) {
                            openModal(formattedDate, displayDate);
                        } else {
                            Swal.fire({
                                icon: 'info',
                                title: 'Día no editable',
                                text: 'Solo se pueden registrar actividades en los días dentro de los periodos de nómina del mes actual.',
                                confirmButtonText: 'Entendido'
                            });
                        }
                    });
                }
            }
        });
    }

    // El llamado inicial ahora solo adjuntará eventos a los días con número
    // La lógica de calendario estándar ahora se maneja en updateCalendar
    // La función updateCalendar es la que crea las celdas y luego llama a attachDayClickEvents
    // attachDayClickEvents(); // Comentado para evitar duplicidad si el backend ya trae data inicial.

    const monthSpan = document.querySelector('.month-navigation span');
    const prevMonthBtn = document.getElementById('prev-month');
    const nextMonthBtn = document.getElementById('next-month');
    const calendarTableBody = document.querySelector('.calendar tbody');
    const chartHeader = document.querySelector('.chart-header h2');
    const MIN_YEAR = 2025;
    const MIN_MONTH = 9;

    function isDateWithinLimits(month, year) {
        if (year < MIN_YEAR) {
            return false;
        }
        if (year === MIN_YEAR && month < MIN_MONTH) {
            return false;
        }
        return true;
    }

    function updateNavigationButtons() {
        const currentMonth = parseInt(monthSpan.getAttribute('data-month'));
        const currentYear = parseInt(monthSpan.getAttribute('data-year'));
        let prevMonth = currentMonth === 1 ? 12 : currentMonth - 1;
        let prevYear = currentMonth === 1 ? currentYear - 1 : currentYear;
        if (!isDateWithinLimits(prevMonth, prevYear)) {
            prevMonthBtn.disabled = true;
            prevMonthBtn.style.opacity = '0.5';
            prevMonthBtn.style.cursor = 'not-allowed';
            prevMonthBtn.title = 'No se puede navegar antes de septiembre 2025';
        } else {
            prevMonthBtn.disabled = false;
            prevMonthBtn.style.opacity = '1';
            prevMonthBtn.style.cursor = 'pointer';
            prevMonthBtn.title = 'Mes anterior';
        }
    }

    // Variable global para el overlay
    const calendarLoadingOverlay = document.getElementById('calendarLoadingOverlay');

    /**
     * Muestra u oculta la capa de superposición de carga.
     * @param {boolean} isLoading - true para mostrar, false para ocultar.
     */
    function toggleLoadingOverlay(isLoading) {
        // CORRECCIÓN: Usar Optional Chaining para evitar error si el elemento no existe (en el modal de empleado, por ejemplo)
        if (!calendarLoadingOverlay) return;

        if (isLoading) {
            // Usar setTimeout para garantizar que el overlay se muestre de inmediato
            // Esto previene que el código bloquee el renderizado si la carga es muy rápida
            calendarLoadingOverlay.style.display = 'flex';
            // Un pequeño retraso para asegurar que 'display: flex' se ha aplicado antes de la transición de opacidad
            setTimeout(() => {
                calendarLoadingOverlay.classList.add('active');
            }, 10);
        } else {
            calendarLoadingOverlay.classList.remove('active');
            // Un retraso que coincida con la duración de la transición CSS (0.3s)
            setTimeout(() => {
                calendarLoadingOverlay.style.display = 'none';
            }, 300);
        }
    }


    async function updateCalendar(month, year) {
        if (!isDateWithinLimits(month, year)) {
            return;
        }

        // 1. Mostrar la capa de carga sobre el calendario existente
        toggleLoadingOverlay(true);

        try {
            const response = await fetch(`/recursoshumanos/loadchart/calendar-data?month=${month}&year=${year}`);
            if (!response.ok) {
                throw new Error('No se pudo cargar los datos del calendario.');
            }
            const data = await response.json();

            // 2. Actualizar el contenido de la cabecera del calendario
            chartHeader.innerHTML = `<i class="fas fa-chart-bar"></i> Load Chart - ${data.monthName} ${data.currentYear}`;
            monthSpan.textContent = `${data.monthName} ${data.currentYear}`;
            monthSpan.setAttribute('data-month', data.currentMonth);
            monthSpan.setAttribute('data-year', data.currentYear);
            currentPayrollDates = data.payrollDates;

            let newTableHTML = '';
            let currentRow = '<tr>';
            // Se elimina la función `assetPath` ya que no se usa en JS

            // LÓGICA DE CALENDARIO ESTÁNDAR (Días del mes actual y relleno del mes siguiente)

            let firstDayOfMonthIndex = new Date(data.currentYear, data.currentMonth - 1, 1).getDay(); // 0 (Dom) a 6 (Sáb)
            firstDayOfMonthIndex = firstDayOfMonthIndex === 0 ? 6 : firstDayOfMonthIndex - 1; // Ajustar a Lunes (0) a Domingo (6)

            // Celdas vacías iniciales para alinear el día 1 con el día de la semana correcto
            for (let i = 0; i < firstDayOfMonthIndex; i++) {
                currentRow += `<td class="other-month" data-date=""></td>`;
            }

            // Días del mes actual y relleno del siguiente mes (solo los necesarios para completar la semana)
            data.calendarDays.forEach((day, index) => {
                const isCurrentMonth = day.current_month;
                const isToday = day.is_today;
                const inPayrollPeriod = isDayInPayrollPeriod(day.date);
                const isHoliday = day.is_holiday;
                const holidayName = day.holiday_name;
                const holidayIconType = day.holiday_icon_type;

                const cellClass = `${!isCurrentMonth ? 'other-month' : ''} ${isToday ? 'current-d' : ''} ${inPayrollPeriod ? 'in-payroll-period' : ''} ${isHoliday ? 'holiday' : ''}`;
                let holidayIconHTML = '';
                if (isHoliday) {
                    if (holidayIconType === 'christmas_tree') {
                        holidayIconHTML = `<img src="https://img.icons8.com/external-victoruler-flat-victoruler/64/external-christmas-tree-christmas-victoruler-flat-victoruler-1.png" alt="Árbol de Navidad" class="holiday-icon" title="${holidayName}">`;
                    } else {
                        holidayIconHTML = `<img src="https://img.icons8.com/skeuomorphism/32/event.png" alt="Día Festivo" class="holiday-icon" title="${holidayName}">`;
                    }
                }

                const payrollIcons = `${day.is_payroll_start_1 ? '<i class="fas fa-flag payroll-icon payroll-start-1" title="Inicio Quincena 1"></i>' : ''} ${day.is_payroll_end_1 ? '<i class="fas fa-flag payroll-icon payroll-end" title="Fin Quincena 1"></i>' : ''} ${day.is_payroll_start_2 ? '<i class="fas fa-flag payroll-icon payroll-start-2" title="Inicio Quincena 2"></i>' : ''} ${day.is_payroll_end_2 ? '<i class="fas fa-flag payroll-icon payroll-end" title="Fin Quincena 2"></i>' : ''} `;

                // Solo renderizar la celda si tiene día (días del mes actual y días del mes siguiente)
                if (day.day !== '') {
                    currentRow += `<td class="${cellClass}" data-date="${day.date}"><span class="day-number">${day.day}</span>${holidayIconHTML}${payrollIcons}</td>`;
                } else {
                    // Celdas vacías, si el backend las envió, pero no son necesarias con la nueva lógica
                    // Se asume que el backend solo envía los días del mes actual, por lo que day.day siempre tiene valor aquí.
                }

                if ((index + firstDayOfMonthIndex + 1) % 7 === 0) {
                    newTableHTML += currentRow + '</tr>';
                    currentRow = '<tr>';
                }
            });

            // Relleno final de celdas vacías, si el último día del mes no terminó en domingo
            let cellCount = data.calendarDays.length + firstDayOfMonthIndex;
            let daysToAdd = 7 - (cellCount % 7);
            if (daysToAdd !== 7) {
                for (let i = 0; i < daysToAdd; i++) {
                    currentRow += `<td class="other-month" data-date=""></td>`;
                }
                newTableHTML += currentRow + '</tr>';
            }


            // 3. Actualizar el contenido y reasignar eventos
            calendarTableBody.innerHTML = newTableHTML;
            attachDayClickEvents();
            updateNavigationButtons();
            await loadMonthlyActivities(month, year);
            await fetchBalances();

            // 4. Ocultar la capa de carga al finalizar
            toggleLoadingOverlay(false);

        } catch (error) {
            console.error('Error al cargar el calendario:', error);
            calendarTableBody.innerHTML = '<tr><td colspan="7" class="loading-error">Error al cargar el calendario.</td></tr>';

            // 5. Ocultar la capa de carga y mostrar el error con SweetAlert
            toggleLoadingOverlay(false);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo cargar el calendario. Por favor, intente de nuevo.',
                confirmButtonText: 'Aceptar'
            });
        }
    }


    prevMonthBtn.addEventListener('click', function () {
        if (this.disabled) return;
        let currentMonth = parseInt(monthSpan.getAttribute('data-month'));
        let currentYear = parseInt(monthSpan.getAttribute('data-year'));
        if (currentMonth === 1) {
            currentMonth = 12;
            currentYear--;
        } else {
            currentMonth--;
        }
        updateCalendar(currentMonth, currentYear);
    });

    nextMonthBtn.addEventListener('click', function () {
        let currentMonth = parseInt(monthSpan.getAttribute('data-month'));
        let currentYear = parseInt(monthSpan.getAttribute('data-year'));
        if (currentMonth === 12) {
            currentMonth = 1;
            currentYear++;
        } else {
            currentMonth++;
        }
        updateCalendar(currentMonth, currentYear);
    });

    updateNavigationButtons();

    const approveBtn = document.getElementById('approve-loadchart');
    if (approveBtn) {
        approveBtn.addEventListener('click', function (e) {
            e.preventDefault();
            window.location.href = '/recursoshumanos/loadchart/approval';
        });
    }

    const initialMonth = parseInt(monthSpan.getAttribute('data-month'));
    const initialYear = parseInt(monthSpan.getAttribute('data-year'));

    // Llama a updateCalendar para cargar la data inicial al abrir el modal
    updateCalendar(initialMonth, initialYear);

    // Fin del contenido que estaba originalmente dentro de document.addEventListener('DOMContentLoaded', function () {
}

// =========================================================================
// INICIALIZACIÓN AUTOMÁTICA (MANTIENE LA FUNCIÓN EN EL MISMO ARCHIVO)
// =========================================================================
// calendar.js (FINAL DEL ARCHIVO)

// ... (Aquí termina la función initializeModalCalendarScripts) ...
// }

// =========================================================================
// INICIALIZACIÓN AUTOMÁTICA (MANTIENE LA FUNCIÓN EN EL MISMO ARCHIVO)
// =========================================================================
document.addEventListener('DOMContentLoaded', function () {
    // ⚠️ NUEVO ENFOQUE: Solo iniciar si encontramos el contenedor principal del calendario.
    const calendarContainer = document.getElementById('loadChart');
    const serviceDataElement = document.getElementById('service-data');

    // Si NO estamos en el modal (que se inicializa manualmente por AJAX)
    // Y SÍ estamos en la vista principal (donde el contenido ya está en el DOM),
    // debemos iniciar la función.

    // Usamos el elemento #loadChart y #service-data como indicadores seguros
    if (calendarContainer && serviceDataElement) {
        if (typeof initializeModalCalendarScripts === 'function') {
            // Llamamos a la función para la vista estática, pasamos null.
            initializeModalCalendarScripts(null);
        } else {
            console.error("Error: La función initializeModalCalendarScripts no se encontró para inicializar la vista principal.");
        }
    }
    // Nota: Si es el modal, esta sección no se ejecuta, ya que la función se llama
    // manualmente en el then(data => ...) de tu archivo 'approval.js'.
});
