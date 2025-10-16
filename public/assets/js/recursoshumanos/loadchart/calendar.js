document.addEventListener('DOMContentLoaded', function () {
    const serviceData = JSON.parse(document.getElementById('service-data').textContent);

    if (!serviceData || typeof serviceData !== 'object') {
        console.error('Datos de servicio no válidos:', serviceData);
        return;
    }

    const modal = document.getElementById('activityModal');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelBtn = document.getElementById('cancel-activity');
    const saveBtn = document.getElementById('save-activity');
    const loadingSpinner = saveBtn.querySelector('.loading-spinner');
    const serviceTabBtn = document.getElementById('service-tab-btn');
    const vacationDaysElement = document.querySelector('p[data-balance-type="vacation"]');
    const restDaysElement = document.querySelector('p[data-balance-type="rest"]');
    const conditionalFields = document.getElementById('conditional-fields');

    let currentSelectedDate = null;
    let monthlyActivities = {};
    let currentActivity = null;
    let currentPayrollDates = {};
    let vacationDaysAvailable = parseInt(vacationDaysElement?.textContent) || 0;
    let restDaysAvailable = parseInt(restDaysElement?.textContent) || 0;

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
            const response = await fetch('/recursoshumanos/loadchart/balances-data');
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
        } else { // Otros (B, V, D, VAC, E, M, A, PE, N)
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
        const q1Start = currentPayrollDates.q1_start ? new Date(currentPayrollDates.q1_start + 'T00:00:00') : null;
        const q1End = currentPayrollDates.q1_end ? new Date(currentPayrollDates.q1_end + 'T23:59:59') : null;
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
        if (q1Start && q1End && selectedDate >= q1Start && selectedDate <= q1End) {
             // Lógica para añadir el Q2 anterior, buscando si existe configuración de quincena para el mes anterior
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
        if (activity.rejection_reason && activity.activity_status === 'rejected') {
            showRejectionMessage('activity-type-select', activity.rejection_reason);
        }

        // Mensaje de rechazo de servicio
        if (activity.services_list && activity.services_list.length > 0) {
            activity.services_list.forEach(service => {
                if (service.status === 'rejected' && service.rejection_reason) {
                    // Usamos work-type-options para apuntar al primer campo de la pestaña de servicio
                    showRejectionMessage('work-type-options', service.rejection_reason, true);
                }
            });
        }
        // Mensaje de rechazo de bono de comida
        if (activity.food_bonuses && activity.food_bonuses.length > 0) {
            activity.food_bonuses.forEach(bonus => {
                if (bonus.status === 'rejected' && bonus.rejection_reason) {
                    showRejectionMessage('food-bonus', bonus.rejection_reason);
                }
            });
        }
        // Mensaje de rechazo de bono de campo
        if (activity.field_bonuses && activity.field_bonuses.length > 0) {
            activity.field_bonuses.forEach(bonus => {
                if (bonus.status === 'rejected' && bonus.rejection_reason) {
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
                if(isServiceTab){
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

        switch (fieldType) {
            case 'activity':
                return activity.activity_status || 'under_review';
            case 'service':
                if (activity.services_list && activity.services_list[itemIndex]) {
                    return activity.services_list[itemIndex].status || 'under_review';
                }
                return 'under_review';
            case 'food_bonus':
                if (activity.food_bonuses && activity.food_bonuses[itemIndex]) {
                    return activity.food_bonuses[itemIndex].status || 'under_review';
                }
                return 'under_review';
            case 'field_bonus':
                if (activity.field_bonuses && activity.field_bonuses[itemIndex]) {
                    return activity.field_bonuses[itemIndex].status || 'under_review';
                }
                return 'under_review';
            default:
                return 'under_review';
        }
    }

    function toggleFieldLock(element, isLocked, status) {
        if (!element) return;
        const parentGroup = element.closest('.form-group');
        if (!parentGroup) return;
        const isCustomSelect = element.classList.contains('custom-select');
        const isOption = element.classList.contains('work-type-option') || element.classList.contains('activity-option') || element.classList.contains('service-bonus-option');

        if (isLocked) {
            // Aplicar estilos de bloqueo
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

            // Crear indicador de bloqueo/revisión
            let lockIndicator = parentGroup.querySelector('.lock-indicator');
            if (!lockIndicator) {
                lockIndicator = document.createElement('div');
                lockIndicator.className = 'lock-indicator';
                parentGroup.appendChild(lockIndicator);
            }

            let message = '';
            let icon = '';
            let bgColor = '';

            if (status === 'approved') {
                message = 'Aprobado';
                icon = 'fas fa-lock';
                bgColor = getCSSVariable('--approved');
            } else if (status === 'reviewed') {
                message = 'Revisado';
                icon = 'fas fa-lock-open';
                bgColor = getCSSVariable('--reviewed');
            } else {
                lockIndicator.remove(); // No debería suceder si isLocked es true
                return;
            }

            lockIndicator.innerHTML = `<i class="${icon}"></i> ${message}`;
            lockIndicator.style.backgroundColor = bgColor;

        } else {
            // Eliminar estilos y elementos de bloqueo
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
            const lockIndicator = parentGroup.querySelector('.lock-indicator');
            if (lockIndicator) lockIndicator.remove();
        }
    }

    function applyFieldLocks(activity) {
        // 1. Limpiar estados anteriores
        clearAllBlocksAndMessages();
        saveBtn.disabled = false;

        if (!activity) return;

        // 2. Bloqueo TOTAL si el día está APROBADO o REVISADO
        const dayStatus = activity.day_status;
        if (statusesToBlockAll.includes(dayStatus)) {
            // Deshabilitar todos los campos del formulario
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

        // 3. Bloqueo GRANULAR (Si day_status es 'rejected' o 'under_review')

        // --- Pestaña Actividad ---
        const activityStatus = getFieldStatus(activity, 'activity');
        const isActivityLocked = statusesToBlockField.includes(activityStatus);

        const activityTypeSelectContainer = document.getElementById('activity-type-select');
        const commissionedSelect = document.getElementById('commissioned-select');
        const wellNameInput = document.getElementById('well-name');
        const activityTypeOptions = document.querySelectorAll('.activity-option');
        const serviceBonusOptions = document.querySelectorAll('.service-bonus-option');

        toggleFieldLock(activityTypeSelectContainer, isActivityLocked, activityStatus);
        activityTypeOptions.forEach(option => toggleFieldLock(option, isActivityLocked, activityStatus));

        toggleFieldLock(commissionedSelect, isActivityLocked, activityStatus);
        toggleFieldLock(wellNameInput, isActivityLocked, activityStatus);
        serviceBonusOptions.forEach(option => toggleFieldLock(option, isActivityLocked, activityStatus));


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
        const serviceStatus = getFieldStatus(activity, 'service');
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

        // 4. Mostrar mensajes de rechazo (si aplica)
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

            if(activity.activity_type === 'P' || activity.activity_type === 'C' || activity.activity_type === 'TC') { // CAMBIO: Añadido TC
                handleActivityTypeChange(activity.activity_type);
            }
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

        if (hasService) {
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

    function populateServiceData(service) {
        let operationType = null;
        let fullServiceData = null;
        for (const [opType, services] of Object.entries(serviceData)) {
            if (Array.isArray(services)) {
                fullServiceData = services.find(s => s.identifier === service.service_identifier);
                if (fullServiceData) {
                    operationType = opType;
                    break;
                }
            }
        }
        if (operationType && fullServiceData) {
            let workTypeValue = operationType === 'Tierra' ? 'Tierra' : 'Marina';
            const workTypeOption = document.querySelector(`.work-type-option[data-value="${workTypeValue}"]`);
            if (workTypeOption) {
                workTypeOption.querySelector('input[type="radio"]').checked = true;
                workTypeOption.classList.add('selected');
                document.querySelectorAll(`.work-type-option:not([data-value="${workTypeValue}"])`).forEach(opt => opt.classList.remove('selected'));
                updateServiceTypes(workTypeValue);
            }

            setTimeout(() => {
                const serviceTypeSelect = document.getElementById('service-type');
                const servicePerformedSelect = document.getElementById('service-performed');
                const serviceSelect = document.getElementById('service');
                const serviceAmountInput = document.getElementById('service-amount');
                const serviceAmountGroup = document.getElementById('service-amount-group');

                const serviceTypeFormatted = fullServiceData.service_type.toLowerCase().replace(/\s+/g, '_');
                const serviceTypeOption = Array.from(serviceTypeSelect.options).find(opt => opt.value === serviceTypeFormatted);
                if (serviceTypeOption) {
                    serviceTypeSelect.value = serviceTypeFormatted;
                    serviceTypeSelect.dispatchEvent(new Event('change'));
                }

                setTimeout(() => {
                    const servicePerformedFormatted = fullServiceData.service_performed.toLowerCase().replace(/\s+/g, '_');
                    const servicePerformedOption = Array.from(servicePerformedSelect.options).find(opt => opt.value === servicePerformedFormatted);
                    if (servicePerformedOption) {
                        servicePerformedSelect.value = servicePerformedFormatted;
                        servicePerformedSelect.dispatchEvent(new Event('change'));
                    }

                    setTimeout(() => {
                        const serviceOption = Array.from(serviceSelect.options).find(opt => opt.value === service.service_identifier);
                        if (serviceOption) {
                            serviceSelect.value = service.service_identifier;
                            const amount = serviceOption.getAttribute('data-amount');
                            const currency = serviceOption.getAttribute('data-currency');
                            if (amount && currency) {
                                serviceAmountInput.value = `${currency} ${parseFloat(amount).toFixed(2)}`;
                                serviceAmountGroup.style.display = 'block';
                            }
                        }
                        if (service.payroll_period_override) {
                            document.getElementById('payroll-period').value = service.payroll_period_override;
                        }
                    }, 150);
                }, 150);
            }, 150);
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
            if (currentActivity && statusesToBlockAll.includes(currentActivity.day_status)) return;
            if (currentActivity && statusesToBlockField.includes(getFieldStatus(currentActivity, 'activity'))) return;

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
        if (workType && serviceTypeId) {
            const servicePerformedSelect = document.getElementById('service-performed');
            servicePerformedSelect.disabled = false;
            servicePerformedSelect.innerHTML = '<option value="">Seleccionar servicio realizado...</option>';
            const uniquePerformed = [...new Set(serviceData[workType].filter(item => item.service_type.toLowerCase().replace(/\s+/g, '_') === serviceTypeId).map(item => item.service_performed))];
            uniquePerformed.forEach(performed => {
                const option = document.createElement('option');
                option.value = performed.toLowerCase().replace(/\s+/g, '_');
                option.textContent = performed;
                servicePerformedSelect.appendChild(option);
            });
            document.getElementById('service').disabled = true;
            document.getElementById('service-amount-group').style.display = 'none';
        }
    });

    document.getElementById('service-performed').addEventListener('change', function () {
        if (currentActivity && statusesToBlockAll.includes(currentActivity.day_status)) return;
        if (currentActivity && statusesToBlockField.includes(getFieldStatus(currentActivity, 'service'))) return;

        const workType = document.querySelector('.work-type-option.selected')?.getAttribute('data-value');
        const serviceTypeId = document.getElementById('service-type').value;
        const performedId = this.value;
        if (workType && serviceTypeId && performedId) {
            const serviceSelect = document.getElementById('service');
            serviceSelect.disabled = false;
            serviceSelect.innerHTML = '<option value="">Seleccionar servicio...</option>';
            const services = serviceData[workType].filter(item => item.service_type.toLowerCase().replace(/\s+/g, '_') === serviceTypeId && item.service_performed.toLowerCase().replace(/\s+/g, '_') === performedId);
            services.forEach(service => {
                const option = document.createElement('option');
                option.value = service.identifier;
                option.textContent = service.service_description;
                option.setAttribute('data-amount', service.amount);
                option.setAttribute('data-currency', service.currency);
                option.setAttribute('data-performed', service.service_performed);
                serviceSelect.appendChild(option);
            });
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
            const response = await fetch(`/recursoshumanos/loadchart/monthly-activities?month=${month}&year=${year}`);
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
        let isValid = true;
        document.querySelectorAll('.error-message').forEach(el => {
            el.style.display = 'none';
        });

        if (!currentSelectedDate) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Fecha no válida. Por favor, intente de nuevo.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        const activityType = document.getElementById('activity-type').value;
        const workTypeSelected = document.querySelector('.work-type-option.selected');
        const serviceSelect = document.getElementById('service');
        const serviceValue = serviceSelect.value;
        const hasServiceBonus = document.querySelector('input[name="has_service_bonus"]:checked')?.value;
        const isActivityP = activityType === 'P';

        // --- INICIO: VERIFICACIÓN DE BLOQUEO DE CAMPOS (Doble Check) ---
        if (currentActivity && statusesToBlockAll.includes(currentActivity.day_status)) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: `No se pueden modificar actividades que ya han sido ${currentActivity.day_status === 'approved' ? 'aprobadas' : 'revisadas'}.`,
                confirmButtonText: 'Entendido'
            });
            return;
        }

        let hasChangesToLockedFields = false;
        let lockedFieldsMessage = [];


        if (currentActivity) {
            // Verificar cambios en Actividad Principal
            const activityStatus = getFieldStatus(currentActivity, 'activity');
            const isActLocked = statusesToBlockField.includes(activityStatus);

            if (isActLocked && activityType !== currentActivity.activity_type) {
                hasChangesToLockedFields = true;
                lockedFieldsMessage.push('Tipo de actividad');
            }
            if (isActLocked && activityType === 'C' && document.getElementById('commissioned-select').value !== (currentActivity.commissioned_to || '')) {
                hasChangesToLockedFields = true;
                lockedFieldsMessage.push('Área Comisionada');
            }
            if (isActLocked && activityType === 'P' && document.getElementById('well-name').value !== (currentActivity.well_name || '')) {
                hasChangesToLockedFields = true;
                lockedFieldsMessage.push('Nombre del Pozo');
            }
            if (isActLocked && isActivityP && hasServiceBonus !== (currentActivity.has_service_bonus || 'no')) {
                hasChangesToLockedFields = true;
                lockedFieldsMessage.push('¿Bono de servicio?');
            }

            // Verificar cambios en Bonos/Servicios (si aplica)
            if (isActivityP) {
                // Food Bonus
                const existingFoodBonus = currentActivity.food_bonuses?.[0];
                const currentFoodBonus = document.getElementById('food-bonus').value;
                const isFoodBonusLocked = statusesToBlockField.includes(getFieldStatus(currentActivity, 'food_bonus'));
                if (isFoodBonusLocked && (currentFoodBonus !== (existingFoodBonus?.num_daily?.toString() || '') || (!existingFoodBonus && currentFoodBonus !== ''))) {
                    hasChangesToLockedFields = true;
                    lockedFieldsMessage.push('Bono de comida');
                }

                // Field Bonus
                const existingFieldBonus = currentActivity.field_bonuses?.[0];
                const currentFieldBonus = document.getElementById('field-bonus').value;
                const isFieldBonusLocked = statusesToBlockField.includes(getFieldStatus(currentActivity, 'field_bonus'));
                if (isFieldBonusLocked && (currentFieldBonus !== (existingFieldBonus?.bonus_identifier || '') || (!existingFieldBonus && currentFieldBonus !== ''))) {
                    hasChangesToLockedFields = true;
                    lockedFieldsMessage.push('Bono de campo');
                }

                // Service
                if (hasServiceBonus === 'si') {
                    const existingService = currentActivity.services_list?.[0];
                    const isServiceLocked = statusesToBlockField.includes(getFieldStatus(currentActivity, 'service'));
                    const payrollPeriodValue = document.getElementById('payroll-period').value;

                    if (isServiceLocked && serviceValue !== (existingService?.service_identifier || '')) {
                        hasChangesToLockedFields = true;
                        lockedFieldsMessage.push('Servicio');
                    }
                    if (isServiceLocked) {
                        const oldPayroll = existingService?.payroll_period_override || 'current';
                        if (payrollPeriodValue !== oldPayroll) {
                            hasChangesToLockedFields = true;
                            lockedFieldsMessage.push('Período de Quincena del Servicio');
                        }
                    }
                }
            }

            if (hasChangesToLockedFields) {
                Swal.fire({
                    icon: 'error',
                    title: 'No se puede guardar',
                    text: `No se pueden modificar los siguientes campos porque ya están revisados o aprobados: ${lockedFieldsMessage.join(', ')}. Solo los campos en estado 'Rechazado' o 'Bajo Revisión' son editables.`,
                    confirmButtonText: 'Entendido'
                });
                return;
            }
        }
        // --- FIN: VERIFICACIÓN DE BLOQUEO DE CAMPOS ---

        // Validar saldo de vacaciones
        if (activityType === 'VAC' && vacationDaysAvailable <= 0 && (!currentActivity || currentActivity.activity_type !== 'VAC')) {
            document.getElementById('vacation-balance-error').style.display = 'block';
            Swal.fire({
                icon: 'warning',
                title: 'Saldo Insuficiente',
                text: 'No tiene días de vacaciones disponibles.',
                confirmButtonText: 'Aceptar'
            });
            return;
        } else {
            document.getElementById('vacation-balance-error').style.display = 'none';
        }

        // Validación de campos obligatorios

        // 1. Pestaña Actividad
        const isActivityTypeFieldLocked = statusesToBlockField.includes(getFieldStatus(currentActivity, 'activity'));
        if (!isActivityTypeFieldLocked) {
             if (!activityType) {
                document.getElementById('activity-type-error').style.display = 'block';
                isValid = false;
            }
            if (activityType === 'C' && !document.getElementById('commissioned-select').value) {
                document.getElementById('commissioned-error').style.display = 'block';
                isValid = false;
            }
            if (activityType === 'P' && !document.getElementById('well-name').value.trim()) {
                document.getElementById('well-name-error').style.display = 'block';
                isValid = false;
            }
        }


        // 2. Pestaña Servicio (sólo si se marcó "Sí" y la actividad es Trabajo en Pozo)
        const isServiceSectionNeeded = isActivityP && hasServiceBonus === 'si';
        const isServiceFieldLocked = statusesToBlockField.includes(getFieldStatus(currentActivity, 'service'));

        if (isServiceSectionNeeded && !isServiceFieldLocked) {
            const serviceTypeValue = document.getElementById('service-type').value;
            const servicePerformedValue = document.getElementById('service-performed').value;

            if (!workTypeSelected) {
                document.getElementById('work-type-error').style.display = 'block';
                isValid = false;
            }
            if (!serviceTypeValue) {
                document.getElementById('service-type-error').style.display = 'block';
                isValid = false;
            }
            if (!servicePerformedValue) {
                document.getElementById('service-performed-error').style.display = 'block';
                isValid = false;
            }
            if (!serviceValue) {
                document.getElementById('service-error').style.display = 'block';
                isValid = false;
            }
        }

        // 3. Chequeo de que al menos algo se está registrando
        const hasFoodBonus = document.getElementById('food-bonus').value !== '';
        const hasFieldBonus = document.getElementById('field-bonus').value !== '';

        if (!activityType && (!isActivityP || (isActivityP && hasServiceBonus === 'no' && !hasFoodBonus && !hasFieldBonus))) {
             // Esto es el caso de intentar guardar un día vacío, lo que debe ser equivalente a borrar la actividad.
             // Sin embargo, si existen actividades anteriores (oldActivity) que no eran 'N', deben ser eliminadas.
             // La validación en el backend se encarga de no crear un log si no hay actividad,
             // y de eliminar el log si se envía 'N' sin bonos/servicios y existía un log.
             // Por el lado del front, permitiremos que se envíe 'N' si no se seleccionó otra cosa,
             // para que el backend maneje la eliminación.

             if (currentActivity && currentActivity.activity_type !== 'N') {
                // Si había una actividad y el usuario seleccionó 'Ninguna' o deseleccionó todo, lo dejaremos pasar para eliminación
             } else if (!currentActivity && !activityType && !isServiceSectionNeeded && !hasFoodBonus && !hasFieldBonus) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selección requerida',
                    text: 'Por favor, selecciona al menos una actividad, bono de comida, bono de campo o un servicio antes de guardar.',
                    confirmButtonText: 'Aceptar'
                });
                return;
             }
        }

        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Campos incompletos',
                text: 'Por favor, rellene todos los campos requeridos y no bloqueados antes de guardar.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        // --- PREPARACIÓN DEL FORM DATA ---
        const monthSpan = document.querySelector('.month-navigation span');
        const formData = {
            date: currentSelectedDate,
            displayed_month: monthSpan.getAttribute('data-month'),
            displayed_year: monthSpan.getAttribute('data-year'),
            activity_type: activityType || 'N',
            commissioned_to: activityType === 'C' ? document.getElementById('commissioned-select').value : null,
            well_name: activityType === 'P' ? document.getElementById('well-name').value : null,
            has_service_bonus: isActivityP ? hasServiceBonus : 'no',
            food_bonus_number: isActivityP && hasFoodBonus ? document.getElementById('food-bonus').value : null,
            field_bonus_identifier: isActivityP && hasFieldBonus ? document.getElementById('field-bonus').value : null
        };

        let payrollPeriodValue = null;
        const selectedPayrollPeriod = document.getElementById('payroll-period').value;
        const currentMonth = parseInt(monthSpan.getAttribute('data-month'));
        const currentYear = parseInt(monthSpan.getAttribute('data-year'));
        const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

        if (selectedPayrollPeriod === 'current_q1') {
            payrollPeriodValue = `Primera quincena de ${monthNames[currentMonth - 1]} ${currentYear}`;
        } else if (selectedPayrollPeriod === 'previous_q2') {
            const prevMonth = currentMonth === 1 ? 12 : currentMonth - 1;
            const prevYear = currentMonth === 1 ? currentYear - 1 : currentYear;
            payrollPeriodValue = `Segunda quincena de ${monthNames[prevMonth - 1]} ${prevYear}`;
        } else if (selectedPayrollPeriod === 'current') {
            payrollPeriodValue = null;
        } else {
            payrollPeriodValue = selectedPayrollPeriod;
        }

        if (isActivityP && hasServiceBonus === 'si' && serviceValue) {
            const workTypeAttr = workTypeSelected?.getAttribute('data-value');
            if (workTypeAttr) {
                const service = serviceData[workTypeAttr]?.find(s => s.identifier === serviceValue);
                if (service) {
                    formData.service_identifier = serviceValue;
                    formData.service_performed = service.service_performed;
                    formData.amount = service.amount;
                    formData.currency = service.currency;
                    formData.payroll_period_override = payrollPeriodValue;
                }
            }
        } else {
            formData.service_identifier = null;
            formData.service_performed = null;
            formData.amount = null;
            formData.currency = null;
            formData.payroll_period_override = null;
        }

        saveBtn.disabled = true;
        loadingSpinner.style.display = 'block';

        // --- LLAMADA FETCH ---
        fetch('/recursoshumanos/loadchart/save-activity', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(formData)
            })
            .then(response => {
                if (response.status === 403) {
                     return response.json().then(data => {
                        throw new Error(data.message || 'No autorizado a modificar actividades aprobadas.');
                     });
                }
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Error desconocido al guardar.');
                    });
                }
                return response.json();
            })
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
                        text: data.message || 'Error desconocido. Intente de nuevo más tarde.',
                        confirmButtonText: 'Aceptar'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error al guardar',
                    text: error.message || 'Error al comunicarse con el servidor. Por favor, verifique su conexión.',
                    confirmButtonText: 'Aceptar'
                });
            })
            .finally(() => {
                if (currentActivity && statusesToBlockAll.includes(currentActivity.day_status)) {
                    saveBtn.disabled = true;
                    loadingSpinner.style.display = 'none';
                } else {
                    saveBtn.disabled = false;
                    loadingSpinner.style.display = 'none';
                }
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

        return inQ1 || inQ2;
    }

    function attachDayClickEvents() {
        document.querySelectorAll('.calendar td').forEach(day => {
            const dayNumberEl = day.querySelector('.day-number');
            const dateAttribute = day.getAttribute('data-date');

            if (dayNumberEl && dayNumberEl.textContent.trim() !== '' && dateAttribute) {
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
        });
    }

    attachDayClickEvents();

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

    async function updateCalendar(month, year) {
        if (!isDateWithinLimits(month, year)) {
            return;
        }
        try {
            calendarTableBody.innerHTML = '<tr><td colspan="7" class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>';
            const response = await fetch(`/recursoshumanos/loadchart/calendar-data?month=${month}&year=${year}`);
            if (!response.ok) {
                throw new Error('No se pudo cargar los datos del calendario.');
            }
            const data = await response.json();
            chartHeader.innerHTML = `<i class="fas fa-chart-bar"></i> Load Chart - ${data.monthName} ${data.currentYear}`;
            monthSpan.textContent = `${data.monthName} ${data.currentYear}`;
            monthSpan.setAttribute('data-month', data.currentMonth);
            monthSpan.setAttribute('data-year', data.currentYear);
            currentPayrollDates = data.payrollDates;
            let newTableHTML = '';
            let currentRow = '<tr>';
            const assetPath = (path) => `{{ asset('${path}') }}`;

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

                currentRow += `<td class="${cellClass}" data-date="${day.date}"><span class="day-number">${day.day}</span>${holidayIconHTML}${payrollIcons}</td>`;
                if ((index + 1) % 7 === 0) {
                    newTableHTML += currentRow + '</tr>';
                    currentRow = '<tr>';
                }
            });
            if (currentRow !== '<tr>') {
                newTableHTML += currentRow.slice(0, -4) + '</tr>';
            }

            calendarTableBody.innerHTML = newTableHTML;
            attachDayClickEvents();
            updateNavigationButtons();
            await loadMonthlyActivities(month, year);
            await fetchBalances();
        } catch (error) {
            console.error('Error al cargar el calendario:', error);
            calendarTableBody.innerHTML = '<tr><td colspan="7" class="loading-error">Error al cargar el calendario.</td></tr>';
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

    updateCalendar(initialMonth, initialYear);
});
