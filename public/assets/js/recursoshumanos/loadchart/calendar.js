/**
 * ⚠️ FUNCIÓN CLAVE: Inicializa todos los scripts y eventos del calendario
 * después de que el HTML parcial ha sido cargado en el modal.
 * @param {string | null} employeeId - El ID del empleado actual (null si es vista principal).
 */
function initializeModalCalendarScripts(employeeId) {

    // --- MANEJO DE LA CARGA DE DATOS SEGURO (CORRECCIÓN) ---
    const serviceDataScript = document.getElementById('service-data');
    if (!serviceDataScript) {
        console.error('Error: Elemento #service-data no encontrado. La carga de datos de servicio falló.');
        return;
    }

    const serviceData = JSON.parse(serviceDataScript.textContent);

    if (!serviceData || typeof serviceData !== 'object') {
        console.error('Datos de servicio no válidos:', serviceData);
        return;
    }
    // --------------------------------------------------------

    // --- DEFINICIÓN DE CONSTANTES (COMIENZO) ---
    const modal = document.getElementById('activityModal');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelBtn = document.getElementById('cancel-activity');
    const saveBtn = document.getElementById('save-activity');
    const loadingSpinner = saveBtn?.querySelector('.loading-spinner');
    const serviceTabBtn = document.getElementById('service-tab-btn');
    const vacationDaysElement = document.querySelector('p[data-balance-type="vacation"]');
    const restDaysElement = document.querySelector('p[data-balance-type="rest"]');
    const conditionalFields = document.getElementById('conditional-fields');

    // --- ELEMENTOS DE VIAJE ---
    const travelDestinationField = document.getElementById('travel-destination-field');
    const travelDestinationInput = document.getElementById('travel-destination');
    const travelReasonField = document.getElementById('travel-reason-field');
    const travelReasonInput = document.getElementById('travel-reason');

    // 🥇 NUEVO ELEMENTO: Fecha Real de Servicio
    const serviceRealDateInput = document.getElementById('service-real-date');
    // -----------------------------------


    let currentSelectedDate = null;
    let monthlyActivities = {};
    let currentActivity = null;
    let currentPayrollDates = {};
    let vacationDaysAvailable = parseInt(vacationDaysElement?.textContent) || 0;
    let currentEmployeeId = employeeId;

    // Estados que bloquean la edición
    const statusesToBlockField = ['approved', 'reviewed'];
    const statusesToBlockAll = ['approved', 'reviewed'];


    function getCSSVariable(varName) {
        return getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
    }

    function getActivityTagColor(activityType) {
        const colorVars = {
            'B': '--work-base',
            'P': '--work-well',
            'TC': '--home-office',
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
    async function updateBalanceUI(vacationDays, totalRestDaysInMonth) {
        if (vacationDaysElement) {
            vacationDaysAvailable = parseInt(vacationDays);
            vacationDaysElement.textContent = `${vacationDaysAvailable} día(s)`;
        }
        if (restDaysElement) {
            restDaysElement.textContent = `${totalRestDaysInMonth} día(s)`;
        }
    }

    /**
     * Llama al backend para obtener y actualizar los saldos.
     */
    async function fetchBalances() {
        try {
            const monthSpan = document.querySelector('.month-navigation span');
            const currentMonth = monthSpan.getAttribute('data-month');
            const currentYear = monthSpan.getAttribute('data-year');

            const employeeParam = currentEmployeeId ? `?employee_id=${currentEmployeeId}&month=${currentMonth}&year=${currentYear}` : `?month=${currentMonth}&year=${currentYear}`;

            const response = await fetch(`/recursoshumanos/loadchart/balances-data${employeeParam}`);
            const data = await response.json();
            if (data.success) {
                updateBalanceUI(data.vacationDays, data.totalRestDaysInMonth);
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
        const foodBonusSelect = document.getElementById('food-bonus');
        const fieldBonusSelect = document.getElementById('field-bonus');

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

        // Resetear campos específicos de Pozo y Comisionado
        wellNameField.style.display = 'none';
        commissionedField.style.display = 'none';


        // --- Lógica de campos condicionales por Tipo de Actividad ---
        if (activityType === 'P') { // Trabajo en Pozo
            wellNameField.style.display = 'block';
            conditionalFields.style.display = 'block'; // Mostrar bonos/servicio
            handleServiceBonusChange(hasServiceBonusSelect?.value); // Re-evaluar pestaña de servicio

        } else if (activityType === 'C') { // Comisionado
            commissionedField.style.display = 'block';
            conditionalFields.style.display = 'none'; // Ocultar bonos/servicio para Comisionado
            // Limpiar campos específicos de Pozo
            wellNameInput.value = '';
            // Forzar que no tenga bono de servicio (solo si no está bloqueado)
            const serviceStatus = getFieldStatus(currentActivity, 'service');
            if (!statusesToBlockField.includes(serviceStatus)) {
                const radioNo = document.getElementById('service-bonus-no');
                const optionNo = document.querySelector('.service-bonus-option[data-value="no"]');
                const optionSi = document.querySelector('.service-bonus-option[data-value="si"]');

                if (radioNo && optionNo && optionSi) {
                    radioNo.checked = true;
                    optionNo.classList.add('selected');
                    optionSi.classList.remove('selected');
                    handleServiceBonusChange('no'); // Ocultar pestaña de servicio
                }
                resetServiceForm();
            }

        } else {
            // ✅ PARA TODAS LAS OTRAS ACTIVIDADES (B, TC, V, D, VAC, E, M, A, PE, N)
            // Limpiar campos específicos de Pozo
            wellNameInput.value = '';

            // Limpiar campos de Comisionado
            commissionedSelect.selectedIndex = 0;

            // ✅ SOLO LIMPIAR BONOS si NO están BLOQUEADOS (aprobados/revisados)
            const foodBonusStatus = getFieldStatus(currentActivity, 'food_bonus');
            const fieldBonusStatus = getFieldStatus(currentActivity, 'field_bonus');

            // Si el bono de comida NO está bloqueado (puede ser rejected o under_review), limpiarlo
            if (!statusesToBlockField.includes(foodBonusStatus)) {
                if (foodBonusSelect) foodBonusSelect.selectedIndex = 0;
            }

            // Si el bono de campo NO está bloqueado (puede ser rejected o under_review), limpiarlo
            if (!statusesToBlockField.includes(fieldBonusStatus)) {
                if (fieldBonusSelect) fieldBonusSelect.selectedIndex = 0;
            }

            // Ocultar sección de bonos (pero mantener los valores si están bloqueados)
            conditionalFields.style.display = 'none';

            // Forzar que no tenga bono de servicio (solo si no está bloqueado)
            const serviceStatus = getFieldStatus(currentActivity, 'service');
            if (!statusesToBlockField.includes(serviceStatus)) {

                const radioNo = document.getElementById('service-bonus-no');
                const optionNo = document.querySelector('.service-bonus-option[data-value="no"]');
                const optionSi = document.querySelector('.service-bonus-option[data-value="si"]');

                if (radioNo && optionNo && optionSi) {
                    radioNo.checked = true;
                    optionNo.classList.add('selected');
                    optionSi.classList.remove('selected');
                    handleServiceBonusChange('no'); // Ocultar pestaña de servicio
                }
                // ✅ Limpiar servicios solo si no están bloqueados
                resetServiceForm();
            }
        }

        // Manejo específico para Viaje
        if (activityType === 'V') {
            travelDestinationField.style.display = 'block';
            travelReasonField.style.display = 'block';
        }
    }

    function handleServiceBonusChange(hasServiceBonus) {
        const serviceTab = document.getElementById('service-tab');
        const activityTabBtn = document.querySelector('.tab-btn[data-tab="activity"]');
        const isActivityP = document.getElementById('activity-type').value === 'P';


        if (hasServiceBonus === 'si' && isActivityP) {
            serviceTabBtn.style.display = 'block';
            // Si el usuario acaba de seleccionar "Sí" al servicio, inicializamos la fecha con la del día seleccionado
            // 🥇 LÓGICA DE INICIALIZACIÓN/ACTUALIZACIÓN
            if (serviceRealDateInput.value === '' && currentSelectedDate) {
                 serviceRealDateInput.value = currentSelectedDate;
            }
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
            // NOTA: resetServiceForm ya no limpia serviceRealDateInput
            resetServiceForm();
        }
    }

    // ❌ ELIMINADA: function populatePayrollPeriodOptions()

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
                if (parentGroup.querySelector('.rejection-message')) return;

                const rejectionDiv = document.createElement('div');
                rejectionDiv.className = 'rejection-message';
                rejectionDiv.innerHTML = `<div class="rejection-content"><i class="fas fa-exclamation-triangle"></i><span>Motivo de rechazo: ${message}</span></div>`;

                if (isServiceTab) {
                    parentGroup.appendChild(rejectionDiv);
                } else {
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
            // --- CAMPOS DE VIAJE ---
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
            document.getElementById('service-amount'),
            // 🥇 NUEVO CAMPO DE FECHA REAL
            serviceRealDateInput
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
        let status = 'under_review';

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
        const existingIndicator = parentGroup.querySelector('.lock-indicator');
        if (existingIndicator) existingIndicator.remove();

        if (['approved', 'reviewed', 'rejected'].includes(lowerCaseStatus)) {
            const lockIndicator = document.createElement('div');
            lockIndicator.className = 'lock-indicator';

            let message = '', icon = '', bgColor = '';

            switch (lowerCaseStatus) {
                case 'approved':
                    message = 'Aprobado';
                    icon = 'fas fa-check-circle';
                    bgColor = getCSSVariable('--approved');
                    break;
                case 'reviewed':
                    message = 'Revisado';
                    icon = 'fas fa-user-check';
                    bgColor = getCSSVariable('--reviewed');
                    break;
                case 'rejected':
                    message = 'Rechazado';
                    icon = 'fas fa-regular fa-triangle-exclamation';
                    bgColor = getCSSVariable('--not-approved');
                    break;
            }

            lockIndicator.innerHTML = `<i class="${icon}"></i> ${message}`;
            lockIndicator.style.backgroundColor = bgColor;
            parentGroup.appendChild(lockIndicator);
        }

        // --- MANEJO DEL BLOQUEO DEL CAMPO ---
        if (isLocked) {
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
        const travelDestinationInput = document.getElementById('travel-destination');
        const travelReasonInput = document.getElementById('travel-reason');
        const activityTypeOptions = document.querySelectorAll('.activity-option');

        toggleFieldLock(activityTypeSelectContainer, isActivityLocked, activityStatus);
        activityTypeOptions.forEach(option => toggleFieldLock(option, isActivityLocked, activityStatus));
        toggleFieldLock(commissionedSelect, isActivityLocked, activityStatus);
        toggleFieldLock(wellNameInput, isActivityLocked, activityStatus);
        toggleFieldLock(travelDestinationInput, isActivityLocked, activityStatus);
        toggleFieldLock(travelReasonInput, isActivityLocked, activityStatus);

        // ----------------------------------------------------
        // LÓGICA CLAVE: ESTADO DEL BOTÓN "¿BONO DE SERVICIO?"
        // ----------------------------------------------------
        const serviceBonusOptions = document.querySelectorAll('.service-bonus-option');
        const serviceStatus = getFieldStatus(activity, 'service');

        let statusForServiceBonus = activityStatus;

        if (serviceStatus === 'approved' || serviceStatus === 'reviewed') {
            statusForServiceBonus = serviceStatus;
        } else if (serviceStatus === 'rejected') {
            statusForServiceBonus = 'rejected';
        }

        const isServiceBonusLocked = statusesToBlockField.includes(statusForServiceBonus);

        serviceBonusOptions.forEach(option => toggleFieldLock(option, isServiceBonusLocked, statusForServiceBonus));
        // ----------------------------------------------------
        // FIN LÓGICA CLAVE
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

        // 🥇 BLOQUEAR NUEVO CAMPO DE FECHA REAL
        toggleFieldLock(serviceRealDateInput, isServiceLocked, serviceStatus);

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
        // NOTA: resetServiceForm se mantiene aquí, pero ahora NO borra serviceRealDateInput
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

        // Lógica para el botón de Servicio
        const radioYes = document.getElementById('service-bonus-yes');
        const radioNo = document.getElementById('service-bonus-no');
        const optionNo = document.querySelector('.service-bonus-option[data-value="no"]');
        const optionSi = document.querySelector('.service-bonus-option[data-value="si"]');

        if (hasService && isActivityP) {
            serviceTabBtn.style.display = 'block';
            if (radioYes && optionNo && optionSi) {
                radioYes.checked = true;
                optionSi.classList.add('selected');
                optionNo.classList.remove('selected');
            }
            if (!document.querySelector('.tab-btn.active')) {
                serviceTabButton.click();
            }
        } else {
            serviceTabBtn.style.display = 'none';
            if (document.getElementById('service-tab').classList.contains('active')) {
                activityTabBtn.click();
            }
            if (radioNo && optionNo && optionSi) {
                radioNo.checked = true;
                optionNo.classList.add('selected');
                optionSi.classList.remove('selected');
            }
        }

        const activityTypeOption = document.querySelector(`.activity-option[data-value="${activity.activity_type}"]`);
        if (activityTypeOption) {
            activityTypeOption.classList.add('selected');
            document.getElementById('activity-type').value = activity.activity_type;
            document.querySelector('#activity-type-header .placeholder').textContent = activityTypeOption.querySelector('.activity-label').textContent;

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

        // --- RELLENAR CAMPOS DE VIAJE ---
        if (activity.activity_type === 'V') {
            travelDestinationField.style.display = 'block';
            travelReasonField.style.display = 'block';
            document.getElementById('travel-destination').value = activity.travel_destination || '';
            document.getElementById('travel-reason').value = activity.travel_reason || '';
        }
        // ---------------------------------------

        if (hasService) {
            // Si tiene servicio, populateServiceData lo carga (incluida la fecha real guardada).
            populateServiceData(activity.services_list[0]);
        }
        // 🥇 CASO CLAVE: Si ya tiene una actividad pero no servicio (o la actividad es de pozo)
        // La fecha real DEBE SER LA FECHA SELECCIONADA DEL CALENDARIO.
        // Si no hay servicio guardado, forzamos la fecha seleccionada.
        else {
            serviceRealDateInput.value = currentSelectedDate;
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
    // FUNCIONES AUXILIARES REQUERIDAS PARA EL REPOBLADO EN CASCADA
    // =========================================================================

    /**
     * Rellena las opciones del selector de 'Servicio Realizado' (Nivel 2)
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
                updateServiceOptions(workType, serviceTypeFormatted, selectedPerformedId);
            }
        }
    }

    /**
     * Rellena las opciones del selector de 'Servicio' (Nivel 3)
     */
    function updateServiceOptions(workType, serviceTypeFormatted, servicePerformedFormatted, selectedServiceId = null) {
        const serviceSelect = document.getElementById('service');
        serviceSelect.innerHTML = '<option value="">Seleccionar servicio...</option>';
        const serviceAmountGroup = document.getElementById('service-amount-group');
        const serviceAmountInput = document.getElementById('service-amount');
        serviceAmountGroup.style.display = 'none';
        serviceAmountInput.value = '';


        if (workType && serviceTypeFormatted && servicePerformedFormatted) {
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
    // FUNCIÓN CORREGIDA: populateServiceData (Ahora maneja service_real_date)
    // =========================================================================
    function populateServiceData(service) {
        let operationType = null;
        let fullServiceData = null;
        const serviceTypeSelect = document.getElementById('service-type');
        const servicePerformedSelect = document.getElementById('service-performed');
        const serviceSelect = document.getElementById('service');
        const serviceAmountGroup = document.getElementById('service-amount-group');

        document.getElementById('service-amount').value = '';
        serviceAmountGroup.style.display = 'none';

        // 🥇 CORRECCIÓN CLAVE: Mantiene la fecha guardada del servicio o usa la seleccionada si no hay guardada
        serviceRealDateInput.value = service.service_real_date || currentSelectedDate;

        // Buscar la data completa del servicio
        for (const [opType, services] of Object.entries(serviceData)) {
            if (Array.isArray(services)) {
                fullServiceData = services.find(s => s.identifier === service.service_identifier);
                if (fullServiceData) {
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

            // 2. Tipo de Servicio (Select)
            serviceTypeSelect.disabled = false;
            updateServiceTypes(workTypeValue);
            serviceTypeSelect.value = serviceTypeFormatted;

            // 3. Servicio Realizado (Select)
            servicePerformedSelect.disabled = false;
            updateServicePerformedOptions(workTypeValue, serviceTypeFormatted, servicePerformedFormatted, servicePerformedFormatted);

            // 4. Servicio (Select)
            serviceSelect.disabled = false;
            updateServiceOptions(workTypeValue, serviceTypeFormatted, servicePerformedFormatted, serviceIdentifier);

            // ❌ ELIMINADA: Lógica de Período de Nómina.
        }
    }


    function openModal(processedDate, displayDate) {
        if (processedDate && displayDate) {
            currentSelectedDate = processedDate;
            document.getElementById('activity-date').value = displayDate;
            const existingActivity = monthlyActivities[processedDate];

            // 1. Resetear formularios
            currentActivity = null;
            resetActivityOptions();
            resetServiceForm(); // 👈 Ya no borra serviceRealDateInput
            resetAdditionalForms();
            resetBonusFields();
            clearAllBlocksAndMessages();
            document.getElementById('vacation-balance-error').style.display = 'none';
            document.querySelector('.tab-btn[data-tab="activity"]').click();
            conditionalFields.style.display = 'none';
            serviceTabBtn.style.display = 'none';

            // 2. 🥇 CARGAR FECHA SELECCIONADA DEL CALENDARIO SIEMPRE
            // Esta es la fecha por defecto que el usuario seleccionó.
            serviceRealDateInput.value = processedDate;


            if (existingActivity) {
                // 3. Si hay actividad existente, el resto del modal se carga desde la actividad.
                // Esta llamada internamente actualizará serviceRealDateInput si el servicio existe.
                populateModalWithActivity(existingActivity);
            } else {
                // 4. Si no hay actividad existente, se queda con la fecha cargada en el paso 2.
                const radioNo = document.getElementById('service-bonus-no');
                const optionNo = document.querySelector('.service-bonus-option[data-value="no"]');
                const optionSi = document.querySelector('.service-bonus-option[data-value="si"]');

                if (radioNo && optionNo && optionSi) {
                    radioNo.checked = true;
                    optionNo.classList.add('selected');
                    optionSi.classList.remove('selected');
                }
            }
        }
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        resetActivityOptions();
        // NOTA: resetServiceForm se mantiene aquí, pero ahora NO borra serviceRealDateInput
        resetServiceForm();
        resetAdditionalForms();
        resetBonusFields();
        clearAllBlocksAndMessages();
        currentSelectedDate = null;
        currentActivity = null;
        conditionalFields.style.display = 'none';

        const radioNo = document.getElementById('service-bonus-no');
        const optionNo = document.querySelector('.service-bonus-option[data-value="no"]');
        const optionSi = document.querySelector('.service-bonus-option[data-value="si"]');

        if (radioNo && optionNo && optionSi) {
            radioNo.checked = true;
            optionNo.classList.add('selected');
            optionSi.classList.remove('selected');
        }
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

        travelDestinationField.style.display = 'none';
        travelDestinationInput.value = '';
        travelReasonField.style.display = 'none';
        travelReasonInput.value = '';
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
        document.getElementById('payroll-period').selectedIndex = 0; // Se mantiene solo para resetear el select oculto
        document.querySelectorAll('.error-message').forEach(el => {
            el.style.display = 'none';
        });
        document.getElementById('service-amount').value = '';
        document.getElementById('service-amount-group').style.display = 'none';
        // ❌ CORRECCIÓN CLAVE: ELIMINAR EL RESET DEL CAMPO DE FECHA REAL
        // serviceRealDateInput.value = '';
    }

    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });
    // ... (Eventos de actividad y servicio se mantienen igual) ...

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

        if (workType && serviceTypeId) {
            updateServicePerformedOptions(workType, serviceTypeId);
        } else {
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

        if (workType && serviceTypeId && performedId) {
            updateServiceOptions(workType, serviceTypeId, performedId);
        } else {
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
        let isValid = true;
        document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');

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

            formData.travel_destination = activityType === 'V' ? document.getElementById('travel-destination').value.trim() : null;
            formData.travel_reason = activityType === 'V' ? document.getElementById('travel-reason').value.trim() : null;

            const isActivityP = activityType === 'P';
            const hasServiceBonus = document.querySelector('input[name="has_service_bonus"]:checked')?.value;
            formData.has_service_bonus = isActivityP ? hasServiceBonus : 'no';

            // *** NUEVA VALIDACIÓN DE VACACIONES EN EL CLIENTE ***
            if (activityType === 'VAC') {
                let currentVacationDaysInMonth = 0;
                document.querySelectorAll('.calendar td[data-date]').forEach(cell => {
                    const activity = monthlyActivities[cell.getAttribute('data-date')];
                    if (activity && activity.activity_type === 'VAC') {
                        currentVacationDaysInMonth++;
                    }
                });

                // Si el día que estamos guardando NO es el mismo día que ya estaba registrado como VAC, sumamos 1.
                const isNewVacationDay = !(currentActivity && currentActivity.activity_type === 'VAC' && currentActivity.date === currentSelectedDate);

                if (isNewVacationDay || (currentActivity && currentActivity.activity_type !== 'VAC')) {
                    // Si cambiamos de N, B, V, etc. a VAC, o si es un día nuevo, sumamos 1.
                    // Si cambiamos de VAC a VAC, el conteo no aumenta. El servidor lo recalcula correctamente.
                    if (!currentActivity || currentActivity.activity_type !== 'VAC') {
                        currentVacationDaysInMonth++;
                    }
                }

                const daysAvailable = vacationDaysAvailable;

                // Si los días solicitados exceden el saldo
                if (currentVacationDaysInMonth > daysAvailable) {
                    document.getElementById('vacation-balance-error').style.display = 'block';
                    isValid = false;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Límite alcanzado',
                        text: `El límite de tus vacaciones ha sido alcanzado. Saldo disponible: ${daysAvailable} día(s).`,
                        confirmButtonText: 'Aceptar'
                    });
                    return;
                }
            }
            // **********************************************


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

            if (activityType === 'V' && !formData.travel_destination) {
                document.getElementById('travel-destination-error').style.display = 'block';
                isValid = false;
            }
            if (activityType === 'V' && !formData.travel_reason) {
                document.getElementById('travel-reason-error').style.display = 'block';
                isValid = false;
            }
        }

        // 2. Procesar Bonos (solo si la actividad es 'P' o 'N' con bonos)
        const currentActivityType = document.getElementById('activity-type').value;

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

        // 🥇 CAMBIO CLAVE: Capturar la fecha real
        const realDate = serviceRealDateInput.value;

        if (wantsService && isActivityP && !statusesToBlockField.includes(serviceStatus)) {
            const workTypeSelected = document.querySelector('.work-type-option.selected');
            const serviceValue = document.getElementById('service').value;

            formData.service_identifier = serviceValue || null;
            // 🥇 NUEVO: Usar la fecha real para la imputación
            formData.service_real_date = realDate;

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
            // 🥇 NUEVA VALIDACIÓN PARA LA FECHA REAL
            if (!formData.service_real_date) {
                document.getElementById('service-real-date-error').style.display = 'block';
                isValid = false;
                document.querySelector('.tab-btn[data-tab="service"]').click();
            }
        } else if (wantsService && isActivityP && statusesToBlockField.includes(serviceStatus)) {
            // Si el servicio está bloqueado, mantenemos los valores guardados
            formData.service_identifier = currentActivity?.services_list[0]?.service_identifier;
            // 🥇 MANTENER LA FECHA REAL BLOQUEADA
            formData.service_real_date = currentActivity?.services_list[0]?.service_real_date;
        } else {
            // Si no se quiere servicio o no es actividad 'P' y la sección no está bloqueada
            if (!statusesToBlockField.includes(serviceStatus)) {
                formData.service_identifier = null;
                // 🥇 LIMPIAR CAMPO DE FECHA REAL (solo si no se necesita)
                formData.service_real_date = null;
            }
        }


        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Campos incompletos',
                text: 'Por favor, rellene todos los campos requeridos en las secciones que no están bloqueadas.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

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
                        // Muestra el mensaje de validación del servidor (incluyendo el de vacaciones)
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

    // ... (isDayInPayrollPeriod, attachDayClickEvents, updateCalendar, etc., se mantienen igual) ...

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
        // ... (se mantiene igual) ...
        document.querySelectorAll('.calendar td').forEach(day => {
            const dayNumberEl = day.querySelector('.day-number');
            const dateAttribute = day.getAttribute('data-date');

            if (dayNumberEl && dayNumberEl.textContent.trim() !== '' && dateAttribute) {
                if (!day.classList.contains('other-month')) {
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

    const calendarLoadingOverlay = document.getElementById('calendarLoadingOverlay');

    function toggleLoadingOverlay(isLoading) {
        if (!calendarLoadingOverlay) return;

        if (isLoading) {
            calendarLoadingOverlay.style.display = 'flex';
            setTimeout(() => {
                calendarLoadingOverlay.classList.add('active');
            }, 10);
        } else {
            calendarLoadingOverlay.classList.remove('active');
            setTimeout(() => {
                calendarLoadingOverlay.style.display = 'none';
            }, 300);
        }
    }


    async function updateCalendar(month, year) {
        if (!isDateWithinLimits(month, year)) {
            return;
        }

        toggleLoadingOverlay(true);

        try {
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

            let firstDayOfMonthIndex = new Date(data.currentYear, data.currentMonth - 1, 1).getDay();
            firstDayOfMonthIndex = firstDayOfMonthIndex === 0 ? 6 : firstDayOfMonthIndex - 1;

            for (let i = 0; i < firstDayOfMonthIndex; i++) {
                currentRow += `<td class="other-month" data-date=""></td>`;
            }

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

                if (day.day !== '') {
                    currentRow += `<td class="${cellClass}" data-date="${day.date}"><span class="day-number">${day.day}</span>${holidayIconHTML}${payrollIcons}</td>`;
                } else {
                }

                if ((index + firstDayOfMonthIndex + 1) % 7 === 0) {
                    newTableHTML += currentRow + '</tr>';
                    currentRow = '<tr>';
                }
            });

            let cellCount = data.calendarDays.length + firstDayOfMonthIndex;
            let daysToAdd = 7 - (cellCount % 7);
            if (daysToAdd !== 7) {
                for (let i = 0; i < daysToAdd; i++) {
                    currentRow += `<td class="other-month" data-date=""></td>`;
                }
                newTableHTML += currentRow + '</tr>';
            }


            calendarTableBody.innerHTML = newTableHTML;
            attachDayClickEvents();
            updateNavigationButtons();
            await loadMonthlyActivities(month, year);
            await fetchBalances();

            toggleLoadingOverlay(false);

        } catch (error) {
            console.error('Error al cargar el calendario:', error);
            calendarTableBody.innerHTML = '<tr><td colspan="7" class="loading-error">Error al cargar el calendario.</td></tr>';

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

    updateCalendar(initialMonth, initialYear);

    // 🥇 NUEVA INICIALIZACIÓN DE FLATPCIKR
    if (typeof flatpickr !== 'undefined' && serviceRealDateInput) {
        flatpickr(serviceRealDateInput, {
            dateFormat: "Y-m-d",
            // Permite seleccionar cualquier fecha pasada, sin restricción de hoy.
            disableMobile: true
        });
    } else {
        console.error("Flatpickr no está disponible o el elemento serviceRealDateInput no existe.");
    }

}


// =========================================================================
// INICIALIZACIÓN AUTOMÁTICA
// =========================================================================
document.addEventListener('DOMContentLoaded', function () {
    const calendarContainer = document.getElementById('loadChart');
    const serviceDataElement = document.getElementById('service-data');

    if (calendarContainer && serviceDataElement) {
        if (typeof initializeModalCalendarScripts === 'function') {
            initializeModalCalendarScripts(null);
        } else {
            console.error("Error: La función initializeModalCalendarScripts no se encontró para inicializar la vista principal.");
        }
    }
});
