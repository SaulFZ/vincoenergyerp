document.addEventListener('DOMContentLoaded', function () {
    // Obtener los datos de servicios desde la base de datos
    const serviceData = JSON.parse(document.getElementById('service-data').textContent);

    // Verificar la estructura de datos
    if (!serviceData || typeof serviceData !== 'object') {
        console.error('Datos de servicio no válidos:', serviceData);
        return;
    }

    // 1. Configuración inicial del modal
    const modal = document.getElementById('activityModal');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelBtn = document.getElementById('cancel-activity');
    const saveBtn = document.getElementById('save-activity');
    const loadingSpinner = saveBtn.querySelector('.loading-spinner');
    let currentSelectedDate = null;
    let monthlyActivities = {}; // Almacenar actividades del mes actual
    let currentActivity = null; // Para rastrear la actividad actual

    // Función para obtener color desde variables CSS
    function getCSSVariable(varName) {
        return getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
    }

    // Función para obtener el color del tag según el tipo de actividad usando variables CSS
    function getActivityTagColor(activityType) {
        const colorVars = {
            'B': '--work-base',
            'P': '--work-well',
            'H': '--home-office',
            'V': '--traveling',
            'D': '--rest',
            'VAC': '--vacation',
            'E': '--training',
            'M': '--medical',
            'C': '--commissioned'
        };

        const cssVar = colorVars[activityType];
        return cssVar ? getCSSVariable(cssVar) : getCSSVariable('--training'); // color por defecto
    }

    // Función mejorada para obtener el ícono de estado basado en day_status
    function getStatusIcon(dayStatus) {
        switch (dayStatus) {
            case 'pending':
                return `<i class="fa-regular fa-hourglass-half status-icon" style="color: ${getCSSVariable('--pending')};" title="Pendiente"></i>`;
            case 'approved':
                return `<i class="fas fa-lock status-icon" style="color: ${getCSSVariable('--approved')};" title="Aprobado"></i>`;
            case 'reviewed':
                return `<i class="fas fa-lock-open status-icon" style="color: ${getCSSVariable('--reviewed')};" title="Revisado"></i>`;
            case 'rejected':
                return `<i class="fas fa-exclamation-triangle status-icon" style="color: ${getCSSVariable('--not-approved')};" title="Rechazado"></i>`;
            default:
                return `<i class="fa-regular fa-hourglass-half status-icon" style="color: ${getCSSVariable('--pending')};" title="Pendiente"></i>`;
        }
    }

    // Función para crear el tag de actividad
    function createActivityHTML(activity) {
        const color = getActivityTagColor(activity.activity_type);
        const statusIcon = getStatusIcon(activity.day_status || 'pending');

        return `
            <div class="day-header-info">
                <div class="activity-tag" style="background-color: ${color};">
                    ${activity.activity_description}
                </div>
                ${statusIcon}
            </div>
        `;
    }

    // Función para mostrar mensajes de rechazo
    function showRejectionMessages(activity) {
        // Limpiar mensajes previos
        clearRejectionMessages();

        // Mostrar razón de rechazo general de la actividad
        if (activity.rejection_reason) {
            showRejectionMessage('activity-type-select', activity.rejection_reason);
        }

        // Verificar y mostrar rechazos en servicios
        if (activity.services_list && activity.services_list.length > 0) {
            activity.services_list.forEach(service => {
                if (service.status === 'Rejected' && service.rejection_reason) {
                    showRejectionMessage('service', service.rejection_reason);
                }
            });
        }

        // Verificar y mostrar rechazos en bonos de comida
        if (activity.food_bonuses && activity.food_bonuses.length > 0) {
            activity.food_bonuses.forEach(bonus => {
                if (bonus.status === 'Rejected' && bonus.rejection_reason) {
                    showRejectionMessage('food-bonus', bonus.rejection_reason);
                }
            });
        }

        // Verificar y mostrar rechazos en bonos de campo
        if (activity.field_bonuses && activity.field_bonuses.length > 0) {
            activity.field_bonuses.forEach(bonus => {
                if (bonus.status === 'Rejected' && bonus.rejection_reason) {
                    showRejectionMessage('field-bonus', bonus.rejection_reason);
                }
            });
        }
    }

    // Función para mostrar mensaje de rechazo específico
    function showRejectionMessage(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (field) {
            const parentGroup = field.closest('.form-group');
            if (parentGroup) {
                // Crear elemento de mensaje de rechazo
                const rejectionDiv = document.createElement('div');
                rejectionDiv.className = 'rejection-message';
                rejectionDiv.innerHTML = `
                    <div class="rejection-content">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Motivo de rechazo: ${message}</span>
                    </div>
                `;
                field.parentNode.insertBefore(rejectionDiv, field.nextSibling);
            }
        }
    }

    // Función para limpiar mensajes de rechazo
    function clearRejectionMessages() {
        document.querySelectorAll('.rejection-message').forEach(el => el.remove());
    }

    // Función para verificar si un elemento está bloqueado
    function isFieldLocked(activity, fieldType, itemIndex = 0) {
        if (!activity) return false;

        // Si el día completo está aprobado, todo está bloqueado
        if (activity.day_status === 'approved') return true;

        // Verificar bloqueos específicos por tipo
        switch (fieldType) {
            case 'activity':
                return activity.activity_status === 'Approved' || activity.activity_status === 'Reviewed';

            case 'service':
                if (activity.services_list && activity.services_list[itemIndex]) {
                    return activity.services_list[itemIndex].status === 'Approved' || activity.services_list[itemIndex].status === 'Reviewed';
                }
                return false;

            case 'food_bonus':
                if (activity.food_bonuses && activity.food_bonuses[itemIndex]) {
                    return activity.food_bonuses[itemIndex].status === 'Approved' || activity.food_bonuses[itemIndex].status === 'Reviewed';
                }
                return false;

            case 'field_bonus':
                if (activity.field_bonuses && activity.field_bonuses[itemIndex]) {
                    return activity.field_bonuses[itemIndex].status === 'Approved' || activity.field_bonuses[itemIndex].status === 'Reviewed';
                }
                return false;

            default:
                return false;
        }
    }

    // Función para bloquear/desbloquear campos
    function toggleFieldLock(element, isLocked, status) {
        if (!element) return;

        // Si el elemento es un `custom-select` o `work-type-option` se maneja de forma especial
        const isCustomSelect = element.classList.contains('custom-select');
        const isWorkTypeOption = element.classList.contains('work-type-option');
        const targetElement = isCustomSelect ? element.querySelector('.select-header') : element;

        if (isLocked) {
            if (isCustomSelect) {
                element.classList.add('locked');
            } else if (isWorkTypeOption) {
                element.style.pointerEvents = 'none';
                element.style.opacity = '0.6';
            } else {
                targetElement.disabled = true;
                targetElement.style.backgroundColor = '#f5f5f5';
                targetElement.style.color = '#999';
                targetElement.style.cursor = 'not-allowed';
            }

            // Agrega el indicador de estado si no existe
            const parentGroup = element.closest('.form-group');
            if (parentGroup && !parentGroup.querySelector('.lock-indicator')) {
                const lockIndicator = document.createElement('div');
                lockIndicator.className = 'lock-indicator';
                let message = '';
                let icon = '';
                if (status === 'Approved' || status === 'approved') {
                    message = 'Aprobado';
                    icon = 'fas fa-lock';
                } else if (status === 'Reviewed' || status === 'reviewed') {
                    message = 'Revisado';
                    icon = 'fas fa-lock-open';
                }
                lockIndicator.innerHTML = `<i class="${icon}"></i> ${message}`;
                parentGroup.appendChild(lockIndicator);
            }
        } else {
            // Desbloquea y remueve el indicador
            if (isCustomSelect) {
                element.classList.remove('locked');
            } else if (isWorkTypeOption) {
                element.style.pointerEvents = '';
                element.style.opacity = '';
            } else {
                targetElement.disabled = false;
                targetElement.style.backgroundColor = '';
                targetElement.style.color = '';
                targetElement.style.cursor = '';
            }

            const parentGroup = element.closest('.form-group');
            if (parentGroup) {
                const lockIndicator = parentGroup.querySelector('.lock-indicator');
                if (lockIndicator) lockIndicator.remove();
            }
        }
    }

    // Función para aplicar estados de bloqueo a todos los campos
    function applyFieldLocks(activity) {
        if (!activity) return;

        // Bloquear campos de actividad principal
        const activityStatus = activity.activity_status || activity.day_status;
        const isActivityLocked = isFieldLocked(activity, 'activity');
        const activityTypeOptions = document.querySelectorAll('.activity-option');
        const commissionedSelect = document.getElementById('commissioned-select');
        const activityTypeSelectContainer = document.getElementById('activity-type-select');

        // Bloquear el select personalizado
        toggleFieldLock(activityTypeSelectContainer, isActivityLocked, activityStatus);

        activityTypeOptions.forEach(option => {
            if (isActivityLocked) {
                option.style.pointerEvents = 'none';
                option.style.opacity = '0.6';
            } else {
                option.style.pointerEvents = '';
                option.style.opacity = '';
            }
        });

        toggleFieldLock(commissionedSelect, isActivityLocked, activityStatus);

        // Bloquear campos de servicio
        const service = activity.services_list && activity.services_list[0];
        const serviceStatus = service ? service.status : null;
        const isServiceLocked = isFieldLocked(activity, 'service');
        const workTypeOptions = document.querySelectorAll('.work-type-option');
        const serviceTypeSelect = document.getElementById('service-type');
        const servicePerformedSelect = document.getElementById('service-performed');
        const serviceSelect = document.getElementById('service');

        workTypeOptions.forEach(option => {
            toggleFieldLock(option, isServiceLocked, serviceStatus);
        });

        toggleFieldLock(serviceTypeSelect, isServiceLocked, serviceStatus);
        toggleFieldLock(servicePerformedSelect, isServiceLocked, serviceStatus);
        toggleFieldLock(serviceSelect, isServiceLocked, serviceStatus);

        // Bloquear campos de bonos
        const foodBonus = activity.food_bonuses && activity.food_bonuses[0];
        const foodBonusStatus = foodBonus ? foodBonus.status : null;
        const isFoodBonusLocked = isFieldLocked(activity, 'food_bonus');
        const fieldBonus = activity.field_bonuses && activity.field_bonuses[0];
        const fieldBonusStatus = fieldBonus ? fieldBonus.status : null;
        const isFieldBonusLocked = isFieldLocked(activity, 'field_bonus');

        toggleFieldLock(document.getElementById('food-bonus'), isFoodBonusLocked, foodBonusStatus);
        toggleFieldLock(document.getElementById('field-bonus'), isFieldBonusLocked, fieldBonusStatus);
    }

    /**
     * Rellena el modal con los datos de una actividad existente.
     * @param {object} activity - El objeto de actividad con sus detalles.
     */
    function populateModalWithActivity(activity) {
        currentActivity = activity;
        resetActivityOptions();
        resetServiceForm();
        resetAdditionalForms();
        resetBonusFields();
        clearRejectionMessages();

        const activityTabBtn = document.querySelector('.tab-btn[data-tab="activity"]');
        const serviceTabBtn = document.querySelector('.tab-btn[data-tab="service"]');

        // Determinar qué pestaña activar y rellenar la actividad
        if (['B', 'P', 'C'].includes(activity.activity_type)) {
            serviceTabBtn.click();
            activityTabBtn.classList.remove('active');
        } else {
            activityTabBtn.click();
            serviceTabBtn.classList.remove('active');
        }

        const activityTypeOption = document.querySelector(`.activity-option[data-value="${activity.activity_type}"]`);
        if (activityTypeOption) {
            activityTypeOption.classList.add('selected');
            activityTypeSelect.value = activity.activity_type;
            placeholder.textContent = activityTypeOption.querySelector('.activity-label').textContent;
        }

        if (activity.activity_type === 'C' && activity.commissioned_to) {
            commissionedField.style.display = 'block';
            commissionedSelect.value = activity.commissioned_to;
        }

        if (activity.services_list && activity.services_list.length > 0) {
            const service = activity.services_list[0];
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
                let workTypeValue = operationType;
                if (operationType === 'Tierra') {
                    workTypeValue = 'Tierra';
                } else if (operationType === 'Marina' || operationType === 'Marino') {
                    workTypeValue = 'Marina';
                }

                const workTypeOption = document.querySelector(`.work-type-option[data-value="${workTypeValue}"]`);
                if (workTypeOption) {
                    workTypeOption.click();
                }

                setTimeout(() => {
                    const serviceTypeSelect = document.getElementById('service-type');
                    const servicePerformedSelect = document.getElementById('service-performed');
                    const serviceSelect = document.getElementById('service');

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
                            }
                        }, 150);
                    }, 150);
                }, 150);
            }
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
            } else if (fieldBonus.bonus_type) {
                Array.from(fieldBonusSelect.options).forEach(option => {
                    if (option.text.includes(fieldBonus.bonus_type) || option.value === fieldBonus.bonus_type) {
                        fieldBonusSelect.value = option.value;
                    }
                });
            }
        }

        setTimeout(() => {
            applyFieldLocks(activity);
            showRejectionMessages(activity);
        }, 200);
    }

    function openModal(processedDate, displayDate) {
        if (processedDate && displayDate) {
            currentSelectedDate = processedDate;
            document.getElementById('activity-date').value = displayDate;

            const existingActivity = monthlyActivities[processedDate];
            if (existingActivity) {
                populateModalWithActivity(existingActivity);
            } else {
                currentActivity = null;
                resetActivityOptions();
                resetServiceForm();
                resetAdditionalForms();
                resetBonusFields();
                clearRejectionMessages();
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
        clearRejectionMessages();
        currentSelectedDate = null;
        currentActivity = null;
        document.querySelectorAll('.lock-indicator').forEach(el => el.remove());
    }

    // Función para cargar actividades del mes
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

    // Función para actualizar el calendario con las actividades (ACTUALIZADA)
    function updateCalendarWithActivities() {
        document.querySelectorAll('.calendar td').forEach(cell => {
            const dayNumberEl = cell.querySelector('.day-number');
            if (!dayNumberEl || !dayNumberEl.textContent.trim()) return;

            const existingHeaderInfo = cell.querySelector('.day-header-info');
            if (existingHeaderInfo) {
                existingHeaderInfo.remove();
            }

            const dayNum = dayNumberEl.textContent.trim();
            const monthSpan = document.querySelector('.month-navigation span');
            let month = parseInt(monthSpan.getAttribute('data-month'));
            let year = parseInt(monthSpan.getAttribute('data-year'));

            if (cell.classList.contains('other-month')) {
                if (dayNum > 15) {
                    month = (month === 1) ? 12 : month - 1;
                    if (month === 12) year--;
                } else {
                    month = (month === 12) ? 1 : month + 1;
                    if (month === 1) year++;
                }
            }

            const cellDate = `${year}-${String(month).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;

            if (monthlyActivities[cellDate]) {
                const activity = monthlyActivities[cellDate];
                const activityHTML = createActivityHTML(activity);
                dayNumberEl.insertAdjacentHTML('afterend', activityHTML);
            }
        });
    }

    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);

    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    // 2. Configuración de tipo de actividad
    const activityOptions = document.querySelectorAll('.activity-option');
    const activityTypeSelect = document.getElementById('activity-type');
    const activityTypeHeader = document.getElementById('activity-type-header');
    const activityTypeOptions = document.getElementById('activity-type-options');
    const placeholder = activityTypeHeader.querySelector('.placeholder');
    const commissionedField = document.getElementById('commissioned-field');
    const commissionedSelect = document.getElementById('commissioned-select');

    activityTypeHeader.addEventListener('click', function () {
        if (currentActivity && isFieldLocked(currentActivity, 'activity')) {
            return;
        }

        this.classList.toggle('open');
        activityTypeOptions.classList.toggle('open');
    });

    document.addEventListener('click', function (e) {
        if (!activityTypeHeader.contains(e.target) && !activityTypeOptions.contains(e.target)) {
            activityTypeHeader.classList.remove('open');
            activityTypeOptions.classList.remove('open');
        }
    });

    activityOptions.forEach(option => {
        option.addEventListener('click', function () {
            if (currentActivity && isFieldLocked(currentActivity, 'activity')) {
                return;
            }

            const value = this.getAttribute('data-value');
            activityOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            activityTypeSelect.value = value;
            placeholder.textContent = this.querySelector('.activity-label').textContent;
            activityTypeHeader.classList.remove('open');
            activityTypeOptions.classList.remove('open');

            if (value === 'C') {
                commissionedField.style.display = 'block';
            } else {
                commissionedField.style.display = 'none';
                commissionedSelect.selectedIndex = 0;
            }
        });
    });

    function resetActivityOptions() {
        activityOptions.forEach(opt => opt.classList.remove('selected'));
        activityTypeSelect.value = '';
        placeholder.textContent = 'Seleccionar actividad...';
    }

    function resetAdditionalForms() {
        commissionedField.style.display = 'none';
        commissionedSelect.selectedIndex = 0;
    }

    function resetBonusFields() {
        document.getElementById('food-bonus').selectedIndex = 0;
        document.getElementById('field-bonus').selectedIndex = 0;
    }

    // 3. Configuración de los selectores anidados
    const workTypeOptions = document.querySelectorAll('.work-type-option');

    workTypeOptions.forEach(option => {
        option.addEventListener('click', function () {
            if (currentActivity && isFieldLocked(currentActivity, 'service')) {
                return;
            }

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
    }

    document.getElementById('service-type').addEventListener('change', function () {
        if (currentActivity && isFieldLocked(currentActivity, 'service')) {
            return;
        }

        const workType = document.querySelector('.work-type-option.selected')?.getAttribute('data-value');
        const serviceTypeId = this.value;

        if (workType && serviceTypeId) {
            const servicePerformedSelect = document.getElementById('service-performed');
            servicePerformedSelect.disabled = false;
            servicePerformedSelect.innerHTML = '<option value="">Seleccionar servicio realizado...</option>';

            const uniquePerformed = [...new Set(serviceData[workType]
                .filter(item => item.service_type.toLowerCase().replace(/\s+/g, '_') === serviceTypeId)
                .map(item => item.service_performed))];

            uniquePerformed.forEach(performed => {
                const option = document.createElement('option');
                option.value = performed.toLowerCase().replace(/\s+/g, '_');
                option.textContent = performed;
                servicePerformedSelect.appendChild(option);
            });

            document.getElementById('service').disabled = true;
        }
    });

    document.getElementById('service-performed').addEventListener('change', function () {
        if (currentActivity && isFieldLocked(currentActivity, 'service')) {
            return;
        }

        const workType = document.querySelector('.work-type-option.selected')?.getAttribute('data-value');
        const serviceTypeId = document.getElementById('service-type').value;
        const performedId = this.value;

        if (workType && serviceTypeId && performedId) {
            const serviceSelect = document.getElementById('service');
            serviceSelect.disabled = false;
            serviceSelect.innerHTML = '<option value="">Seleccionar servicio...</option>';

            const services = serviceData[workType]
                .filter(item =>
                    item.service_type.toLowerCase().replace(/\s+/g, '_') === serviceTypeId &&
                    item.service_performed.toLowerCase().replace(/\s+/g, '_') === performedId
                )
                .map(item => ({
                    id: item.identifier,
                    name: item.service_description,
                    amount: item.amount,
                    performed: item.service_performed
                }));

            services.forEach(service => {
                const option = document.createElement('option');
                option.value = service.id;
                option.textContent = service.name;
                option.setAttribute('data-amount', service.amount);
                option.setAttribute('data-performed', service.performed);
                serviceSelect.appendChild(option);
            });
        }
    });

    function resetServiceForm() {
        workTypeOptions.forEach(opt => opt.classList.remove('selected'));
        document.querySelectorAll('input[name="work-type"]').forEach(radio => {
            radio.checked = false;
        });

        document.getElementById('service-type').disabled = true;
        document.getElementById('service-type').selectedIndex = 0;
        document.getElementById('service-performed').disabled = true;
        document.getElementById('service-performed').selectedIndex = 0;
        document.getElementById('service').disabled = true;
        document.getElementById('service').selectedIndex = 0;

        document.querySelectorAll('.error-message').forEach(el => {
            el.style.display = 'none';
        });
    }

    // 4. Configuración de tabs
    document.querySelectorAll('.tab-btn').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelector('.form-tabs .tab-btn.active').classList.remove('active');
            this.classList.add('active');

            const tabId = this.getAttribute('data-tab');
            document.querySelector('.tab-content.active').classList.remove('active');
            document.getElementById(`${tabId}-tab`).classList.add('active');
        });
    });

    // 5. Configuración del botón guardar con validaciones de bloqueo
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

        const isActivityTabActive = document.getElementById('activity-tab').classList.contains('active');
        const isServiceTabActive = document.getElementById('service-tab').classList.contains('active');
        const activityType = activityTypeSelect.value;
        const workTypeSelected = document.querySelector('.work-type-option.selected');
        const serviceSelect = document.getElementById('service');
        const serviceValue = serviceSelect.value;

        // Validation logic
        if (isActivityTabActive) {
            // No validar si el campo está bloqueado
            if (!isFieldLocked(currentActivity, 'activity')) {
                if (!activityType) {
                    document.getElementById('activity-type-error').style.display = 'block';
                    isValid = false;
                }
                if (activityType === 'C' && !commissionedSelect.value) {
                    document.getElementById('commissioned-error').style.display = 'block';
                    isValid = false;
                }
            }
        } else if (isServiceTabActive) {
            // No validar si el campo está bloqueado
            if (!isFieldLocked(currentActivity, 'service')) {
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
        }

        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Campos incompletos',
                text: 'Por favor, rellene todos los campos requeridos antes de guardar.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        // Check if there are changes to approved/reviewed fields
        if (currentActivity) {
            let hasApprovedChanges = false;
            let approvedFieldsMessage = [];

            // Check day status
            if (currentActivity.day_status === 'approved') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'No se pueden modificar actividades que ya han sido aprobadas.',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            // Check activity
            if (currentActivity.activity_status === 'Approved' || currentActivity.activity_status === 'Reviewed') {
                if (activityType !== currentActivity.activity_type) {
                    hasApprovedChanges = true;
                    approvedFieldsMessage.push('Tipo de actividad');
                }
                if (activityType === 'C' && commissionedSelect.value !== currentActivity.commissioned_to) {
                    hasApprovedChanges = true;
                    approvedFieldsMessage.push('Área Comisionada');
                }
            }

            // Check service
            const existingService = currentActivity.services_list?.[0];
            if (existingService && (existingService.status === 'Approved' || existingService.status === 'Reviewed')) {
                if (serviceValue !== existingService.service_identifier) {
                    hasApprovedChanges = true;
                    approvedFieldsMessage.push('Servicio');
                }
            }

            // Check food bonus
            const existingFoodBonus = currentActivity.food_bonuses?.[0];
            const currentFoodBonus = document.getElementById('food-bonus').value;
            if (existingFoodBonus && (existingFoodBonus.status === 'Approved' || existingFoodBonus.status === 'Reviewed')) {
                if (currentFoodBonus !== existingFoodBonus.num_daily?.toString()) {
                    hasApprovedChanges = true;
                    approvedFieldsMessage.push('Bono de comida');
                }
            }

            // Check field bonus
            const existingFieldBonus = currentActivity.field_bonuses?.[0];
            const currentFieldBonus = document.getElementById('field-bonus').value;
            if (existingFieldBonus && (existingFieldBonus.status === 'Approved' || existingFieldBonus.status === 'Reviewed')) {
                if (currentFieldBonus !== existingFieldBonus.bonus_identifier) {
                    hasApprovedChanges = true;
                    approvedFieldsMessage.push('Bono de campo');
                }
            }

            if (hasApprovedChanges) {
                Swal.fire({
                    icon: 'error',
                    title: 'No se puede guardar',
                    text: `No se pueden modificar los siguientes campos porque ya están aprobados o revisados: ${approvedFieldsMessage.join(', ')}`,
                    confirmButtonText: 'Entendido'
                });
                return;
            }
        }

        const monthSpan = document.querySelector('.month-navigation span');
        const formData = {
            date: currentSelectedDate,
            displayed_month: monthSpan.getAttribute('data-month'),
            displayed_year: monthSpan.getAttribute('data-year'),
            activity_type: activityType || null,
            commissioned_to: commissionedSelect.value || null,
            food_bonus_number: document.getElementById('food-bonus').value || null,
            field_bonus_identifier: document.getElementById('field-bonus').value || null
        };

        if (isServiceTabActive && serviceValue) {
            const service = serviceData[workTypeSelected.getAttribute('data-value')]?.find(s => s.identifier === serviceValue);
            formData.service_identifier = serviceValue;
            formData.service_performed = service.service_performed;
            formData.amount = service.amount;
            formData.currency = service.currency;
        }


        if (!formData.activity_type && !formData.service_identifier && !formData.food_bonus_number && !formData.field_bonus_identifier) {
            Swal.fire({
                icon: 'warning',
                title: 'Selección requerida',
                text: 'Por favor, selecciona una actividad o un servicio antes de guardar.',
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
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ?.getAttribute('content') || ''
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Guardado!',
                        text: 'El registro se ha guardado exitosamente.',
                        showConfirmButton: false,
                        timer: 1500,
                    });
                    closeModal();
                    const monthSpan = document.querySelector('.month-navigation span');
                    const currentMonth = parseInt(monthSpan.getAttribute('data-month'));
                    const currentYear = parseInt(monthSpan.getAttribute('data-year'));
                    loadMonthlyActivities(currentMonth, currentYear);
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
                    title: 'Error de conexión',
                    text: 'Error al comunicarse con el servidor. Por favor, verifique su conexión.',
                    confirmButtonText: 'Aceptar'
                });
            })
            .finally(() => {
                saveBtn.disabled = false;
                loadingSpinner.style.display = 'none';
            });
    });

    // 6. Eventos del calendario para abrir el modal
    function attachDayClickEvents() {
        document.querySelectorAll('.calendar td').forEach(day => {
            const dayNumberEl = day.querySelector('.day-number');
            if (dayNumberEl && dayNumberEl.textContent.trim() !== '') {
                day.addEventListener('click', function () {
                    const dayNum = dayNumberEl.textContent.trim();
                    const monthSpan = document.querySelector('.month-navigation span');
                    let month = parseInt(monthSpan.getAttribute('data-month'));
                    let year = parseInt(monthSpan.getAttribute('data-year'));
                    const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

                    if (day.classList.contains('other-month')) {
                        if (dayNum > 15) {
                            month = (month === 1) ? 12 : month - 1;
                            if (month === 12) year--;
                        } else {
                            month = (month === 12) ? 1 : month + 1;
                            if (month === 1) year++;
                        }
                    }

                    const formattedDate = `${year}-${String(month).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;
                    const displayDate = `${dayNum} de ${monthNames[month - 1]} ${year}`;

                    openModal(formattedDate, displayDate);
                });
            }
        });
    }

    attachDayClickEvents();

    // 7. Navegación por meses del calendario (Carga AJAX) - CON LÍMITES
    const monthSpan = document.querySelector('.month-navigation span');
    const prevMonthBtn = document.getElementById('prev-month');
    const nextMonthBtn = document.getElementById('next-month');
    const calendarTableBody = document.querySelector('.calendar tbody');
    const chartHeader = document.querySelector('.chart-header h2');

    // DEFINIR LÍMITES DE NAVEGACIÓN
    const MIN_YEAR = 2025;
    const MIN_MONTH = 9; // Septiembre de 2025

    // Función para verificar si una fecha está dentro del rango permitido
    function isDateWithinLimits(month, year) {
        if (year < MIN_YEAR) {
            return false;
        }
        if (year === MIN_YEAR && month < MIN_MONTH) {
            return false;
        }
        return true;
    }

    // Función para actualizar el estado de los botones de navegación
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
            Swal.fire({
                icon: 'warning',
                title: 'Límite de navegación',
                text: `No se puede navegar antes de septiembre de ${MIN_YEAR}. El sistema de quincenas inició en esa fecha.`,
                confirmButtonText: 'Entendido'
            });
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

            let newTableHTML = '';
            let currentRow = '<tr>';
            data.calendarDays.forEach((day, index) => {
                const isCurrentMonth = day.current_month;
                const isToday = day.is_today;
                const cellClass = `${!isCurrentMonth ? 'other-month' : ''} ${isToday ? 'current-d' : ''}`;
                const payrollIcons = `
                    ${day.is_payroll_start_1 ? '<i class="fas fa-flag payroll-icon payroll-start-1" title="Inicio Quincena 1"></i>' : ''}
                    ${day.is_payroll_end_1 ? '<i class="fas fa-flag payroll-icon payroll-end-1" title="Fin Quincena 1"></i>' : ''}
                    ${day.is_payroll_start_2 ? '<i class="fas fa-flag payroll-icon payroll-start-2" title="Inicio Quincena 2"></i>' : ''}
                    ${day.is_payroll_end_2 ? '<i class="fas fa-flag payroll-icon payroll-end-2" title="Fin Quincena 2"></i>' : ''}
                `;
                currentRow += `<td class="${cellClass}">
                    <span class="day-number">${day.day}</span>
                    ${payrollIcons}
                </td>`;
                if ((index + 1) % 7 === 0) {
                    newTableHTML += currentRow + '</tr>';
                    currentRow = '<tr>';
                }
            });
            newTableHTML += currentRow.slice(0, -4);
            calendarTableBody.innerHTML = newTableHTML;
            attachDayClickEvents();

            updateNavigationButtons();

            await loadMonthlyActivities(month, year);

        } catch (error) {
            console.error('Error al cargar el calendario:', error);
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

    document.addEventListener('DOMContentLoaded', function () {
        updateNavigationButtons();
    });

    // 8. Botón de aprobación
    const approveBtn = document.getElementById('approve-loadchart');
    if (approveBtn) {
        approveBtn.addEventListener('click', function (e) {
            e.preventDefault();
            window.location.href = '/recursoshumanos/loadchart/approval';
        });
    }

    // Cargar actividades del mes inicial
    const initialMonth = parseInt(monthSpan.getAttribute('data-month'));
    const initialYear = parseInt(monthSpan.getAttribute('data-year'));
    loadMonthlyActivities(initialMonth, initialYear);
});
// Modificar el evento DOMContentLoaded para conectar las actualizaciones
document.addEventListener('DOMContentLoaded', function () {
    initializeApprovalTable();
    setupModalEventListeners();

    // Conectar al sistema de actualizaciones en tiempo real (simulado)
    connectToRealTimeUpdates();
});

// Asegurarse de limpiar el intervalo cuando la página se cierre
window.addEventListener('beforeunload', function() {
    stopAutoRefresh();
});


// Variables globales para el modal y la vista
let currentModalData = {
    employeeId: null,
    date: null,
    dailyActivities: null

};

let autoRefreshInterval = null;
let lastUpdateTime = null;



// Inicializar el sistema de actualización automática
function initializeAutoRefresh() {
    // Verificar cambios cada 5 segundos
    autoRefreshInterval = setInterval(checkForUpdates, 5000);

    // También verificar cuando la pestaña gana foco
    document.addEventListener('visibilitychange', handleVisibilityChange);

    // Guardar el tiempo inicial
    lastUpdateTime = new Date();
}

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
    renderEmployeeWorkLog();
    showQuincena(1);
    setActiveButton("quincena1");
    updatePeriodInfo();

    // Iniciar el sistema de actualización automática
    initializeAutoRefresh();
}

// Verificar si hay actualizaciones en el servidor
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
            // Hay actualizaciones, refrescar los datos
            await refreshData();
            // Notificación de actualización automática eliminada para una experiencia más silenciosa.
        }
    } catch (error) {
        console.error('Error verificando actualizaciones:', error);
    }
}

// Refrescar los datos desde el servidor
async function refreshData() {
    try {
        showLoadingState();

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

        // Actualizar la visualización
        renderEmployeeWorkLog();

        // Actualizar el tiempo de la última actualización
        lastUpdateTime = new Date();

        hideLoadingState();
    } catch (error) {
        console.error('Error refreshing data:', error);
        hideLoadingState();
    }
}

// Función para actualizar una celda específica con nuevos datos
function updateSpecificCell(employeeId, date, newData) {
    const employeeRow = document.querySelector(`.employee-row[data-employee-id="${employeeId}"]`);
    if (!employeeRow) return;

    // Encontrar la celda específica para esta fecha
    const allDayCells = Array.from(employeeRow.querySelectorAll('.data-cell'));
    const targetCell = allDayCells.find(cell => cell.getAttribute('data-date') === date);

    if (!targetCell) return;

    // Actualizar el indicador de estado
    const statusIndicator = targetCell.querySelector('.status-indicator');
    if (statusIndicator && newData.activity_type) {
        statusIndicator.textContent = newData.activity_type.toUpperCase();
        statusIndicator.className = `status-indicator status-${newData.activity_type.toLowerCase()}`;
        statusIndicator.title = newData.activity_description || '';

        // Actualizar el borde según el estado del día
        const dayStatus = newData.day_status || 'pending';
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

    // También actualizar las filas relacionadas (comida, bono, servicio)
    updateRelatedDataRows(employeeRow, date, newData);

    // Recalcular totales
    calculateAndRenderTotals();
}

// Función para simular la recepción de actualizaciones desde otras vistas
// Esta función sería llamada por el sistema de notificaciones o WebSockets
function receiveUpdateFromOtherView(updateData) {
    // updateData debería contener: employeeId, date, newActivityData
    const { employeeId, date, newActivityData } = updateData;

    // Actualizar los datos locales
    const employeeIndex = workLogsData.findIndex(log => log.employee_id.toString() === employeeId);
    if (employeeIndex !== -1) {
        const dailyIndex = workLogsData[employeeIndex].daily_activities.findIndex(act => act.date === date);
        if (dailyIndex !== -1) {
            // Actualizar la actividad existente
            workLogsData[employeeIndex].daily_activities[dailyIndex] = {
                ...workLogsData[employeeIndex].daily_activities[dailyIndex],
                ...newActivityData
            };
        } else {
            // Agregar nueva actividad
            workLogsData[employeeIndex].daily_activities.push({
                date: date,
                ...newActivityData
            });
        }

        // Actualizar la visualización
        updateSpecificCell(employeeId, date, newActivityData);
        showNotification('Datos actualizados desde otra vista', 'info');
    }
}

// Ejemplo de cómo se conectaría a un sistema de notificaciones en tiempo real
function connectToRealTimeUpdates() {
    // Aquí se conectaría a WebSockets, EventSource, o otro sistema de notificaciones
    // Este es un ejemplo simulado usando setInterval
    setInterval(() => {
        // Simular recepción de actualizaciones aleatorias
        if (Math.random() > 0.7) { // 30% de probabilidad de recibir una actualización
            const randomEmployee = workLogsData[Math.floor(Math.random() * workLogsData.length)];
            if (randomEmployee && randomEmployee.daily_activities && randomEmployee.daily_activities.length > 0) {
                const randomActivity = randomEmployee.daily_activities[
                    Math.floor(Math.random() * randomEmployee.daily_activities.length)
                ];

                // Simular un cambio
                const updatedData = {
                    employeeId: randomEmployee.employee_id,
                    date: randomActivity.date,
                    newActivityData: {
                        ...randomActivity,
                        activity_type: ['B', 'P', 'H', 'T', 'D', 'V', 'E', 'M', 'C'][Math.floor(Math.random() * 9)],
                        day_status: ['pending', 'reviewed', 'approved', 'rejected'][Math.floor(Math.random() * 4)]
                    }
                };

                receiveUpdateFromOtherView(updatedData);
            }
        }
    }, 10000); // Verificar cada 10 segundos
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

    // Asignar listeners a los botones de aprobación masiva
    document.getElementById('approval-table-body').addEventListener('click', function(event) {
        const btn = event.target.closest('.btn-approve');
        if (btn) {
            handleApprove.call(btn);
        }
    });

    document.getElementById('approval-table-body').addEventListener('click', function(event) {
        const btn = event.target.closest('.btn-review');
        if (btn) {
            handleReview.call(btn);
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

        if (select && !select.disabled) {
            const originalItem = getItemFromActivity(currentModalData.dailyActivities, itemType, itemIndex);
            if (originalItem && originalItem.status.toLowerCase() !== select.value.toLowerCase()) {
                 changes.push({
                    date: currentModalData.date,
                    item_type: itemType,
                    item_index: itemIndex,
                    status: select.value,
                    rejection_reason: comment
                });
            }
        }
    });

    if (changes.length > 0) {
        await updateDailyItemsStatus(currentModalData.employeeId, changes);
    } else {
        showNotification('No hay cambios para guardar.', 'info');
    }
    modal.style.display = 'none';
}

function getItemFromActivity(dailyActivity, itemType, itemIndex) {
    if (itemType === 'activity') {
        return { status: dailyActivity.activity_status, rejection_reason: dailyActivity.rejection_reason };
    }
    if (itemType === 'food_bonuses' && dailyActivity.food_bonuses && dailyActivity.food_bonuses[itemIndex]) {
        return dailyActivity.food_bonuses[itemIndex];
    }
    if (itemType === 'field_bonuses' && dailyActivity.field_bonuses && dailyActivity.field_bonuses[itemIndex]) {
        return dailyActivity.field_bonuses[itemIndex];
    }
    if (itemType === 'services_list' && dailyActivity.services_list && dailyActivity.services_list[itemIndex]) {
        return dailyActivity.services_list[itemIndex];
    }
    return null;
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
    currentModalData.dailyActivities = dailyActivity; // Almacenar la actividad del día en los datos globales

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
    const approvalSelector = getModalApprovalSelectorHtml(statusPillClass, isReviewer, isApprover, itemType, itemIndex);

    // Obtener el estado real para deshabilitar si es necesario
    const isLocked = (status === 'Approved' && !isApprover) || (status === 'Reviewed' && !isApprover && !isReviewer) || (status === 'Rejected');

    row.innerHTML = `
        <td>${concept}</td>
        <td>${details}</td>
        <td>${identifier}</td>
        <td class="amount-cell">${amount !== null ? `${amount}` : ''}</td>
        <td>
            ${approvalSelector}
        </td>
        <td>
            <textarea class="form-control modal-comment" placeholder="Motivo de rechazo..." ${isLocked ? 'disabled' : ''}>${rejectionReason || ''}</textarea>
        </td>
    `;
    tableBody.appendChild(row);
}

function getModalApprovalSelectorHtml(currentStatus, isReviewer, isApprover, itemType, itemIndex) {
    let options = [];

    const canChangeToPending = false;
    const isItemApproved = currentStatus === 'approved';
    const isItemRejected = currentStatus === 'rejected';

    if (isReviewer && !isApprover) { // Es revisor
        if (isItemApproved || isItemRejected) {
            options = [{ value: currentStatus, text: currentStatus, disabled: true }];
        } else {
            options = [
                { value: 'pending', text: 'Pendiente', disabled: !canChangeToPending },
                { value: 'reviewed', text: 'Revisado', disabled: false },
                { value: 'rejected', text: 'Rechazado', disabled: false }
            ];
        }
    } else if (isApprover) { // Es aprobador (puede ser revisor también)
        options = [
            { value: 'pending', text: 'Pendiente', disabled: true },
            { value: 'reviewed', text: 'Revisado', disabled: true },
            { value: 'approved', text: 'Aprobado', disabled: isItemApproved },
            { value: 'rejected', text: 'Rechazado', disabled: false }
        ];
    } else { // No tiene permisos
        return `<span class="badge badge-${currentStatus === 'approved' ? 'success' : currentStatus === 'reviewed' ? 'primary' : currentStatus === 'rejected' ? 'danger' : 'info'}">${currentStatus}</span>`;
    }

    let html = `<select class="form-control item-approval-selector">`;
    const uniqueOptions = [...new Map(options.map(item => [item.value, item])).values()];

    uniqueOptions.forEach(option => {
        let isSelected = option.value === currentStatus;
        let isDisabled = option.disabled;

        html += `<option value="${option.value}" ${isSelected ? 'selected' : ''} ${isDisabled ? 'disabled' : ''}>${option.text}</option>`;
    });

    html += `</select>`;

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
    const fortnight = currentView;

    // Verificar permisos antes de proceder
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

    // Verificar si hay actividades que necesitan aprobación
    const activitiesToApprove = periodActivities.filter(act => act.day_status !== 'approved' && act.day_status !== 'rejected');
    const rejectedActivitiesExist = periodActivities.some(act => act.day_status === 'rejected');

    if (activitiesToApprove.length > 0) {
        Swal.fire({
            title: '¿Está seguro?',
            text: `Se aprobarán todas las actividades (${activitiesToApprove.length} días) en estado Pendiente o Revisado para este período. Esta acción no se puede deshacer.`,
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
            text: 'Solo quedan actividades en estado "Rechazado" en este período. El empleado debe corregirlas antes de poder aprobar.',
            icon: 'error',
            confirmButtonText: 'Entendido'
        });
    } else {
        showNotification('No hay actividades pendientes o revisadas para aprobar en este período.', 'info');
    }
}

async function handleReview() {
    const row = this.closest(".employee-row");
    const employeeId = row.getAttribute('data-employee-id');
    const fortnight = currentView;

    // Verificar permisos antes de proceder
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

    // Verificar si hay actividades que necesitan revisión
    const activitiesToReview = periodActivities.filter(act => act.day_status === 'pending');
    const rejectedActivitiesExist = periodActivities.some(act => act.day_status === 'rejected');

    if (activitiesToReview.length > 0) {
        Swal.fire({
            title: '¿Está seguro?',
            text: `Se revisarán todas las actividades (${activitiesToReview.length} días) en estado Pendiente para este período.`,
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
            text: 'Solo quedan actividades en estado "Rechazado" en este período. El empleado debe corregirlas antes de que puedan ser revisadas.',
            icon: 'error',
            confirmButtonText: 'Entendido'
        });
    } else {
        showNotification('No hay actividades pendientes para revisar en este período.', 'info');
    }
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

        // Mantener la vista actual después de cargar los datos
        if (currentView === 'quincena1') {
            showQuincena(1);
        } else if (currentView === 'quincena2') {
            showQuincena(2);
        } else {
            showFullMonth();
        }

        hideLoadingState();
        // Notificación de carga de datos eliminada.
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

        const mainRow = createEmployeeRow(employee, monthlyDays, 'Actividad', true);
        tableBody.appendChild(mainRow);

        const foodRow = createEmployeeRow(employee, monthlyDays, 'Comida', false);
        tableBody.appendChild(foodRow);

        const fieldBonusRow = createEmployeeRow(employee, monthlyDays, 'Bono', false);
        tableBody.appendChild(fieldBonusRow);

        const serviceRow = createEmployeeRow(employee, monthlyDays, 'Servicio', false);
        tableBody.appendChild(serviceRow);
    });
    renderEmployeeWorkLog();
}

// Función corregida para crear la fila del empleado con botones condicionais
function createEmployeeRow(employee, monthlyDays, rowType, isMainRow) {
    const tr = document.createElement('tr');
    tr.className = isMainRow ? 'employee-row' : 'activity-row';
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
            dayCell.textContent = '0';
        }

        tr.appendChild(dayCell);
    });

    const totalCell = document.createElement('td');
    totalCell.className = `data-cell ${rowType === 'Actividad' ? 'total-activity' : (rowType === 'Comida' ? 'total-food' : (rowType === 'Bono' ? 'total-field-bonus' : 'total-service'))}`;
    totalCell.innerHTML = rowType === 'Actividad' ? '<div class="status-indicator">0</div>' : '0';
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

        let buttonsHtml = '';
        if (isReviewerForEmployee) {
            buttonsHtml += '<button class="btn-review">Revisar</button>';
        }
        if (isApproverForEmployee) {
            buttonsHtml += '<button class="btn-approve">Aprobar</button>';
        }

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

                totalService += dailyActivity.services_list ? dailyActivity.services_list.reduce((sum, service) => sum + Number(service.amount), 0) : 0;
            }
        });

        const activityRow = employeeRow;
        const foodRow = activityRow.nextElementSibling;
        const fieldBonusRow = foodRow.nextElementSibling;
        const serviceRow = fieldBonusRow.nextElementSibling;

        const activityTotalCell = activityRow.querySelector('.total-activity');
        const foodTotalCell = foodRow.querySelector('.total-food');
        const fieldBonusTotalCell = fieldBonusRow.querySelector('.total-field-bonus');
        const serviceTotalCell = serviceRow.querySelector('.total-service');

        if (activityTotalCell) activityTotalCell.querySelector('.status-indicator').textContent = totalDays;

        if (canSeeAmounts) {
            if (foodTotalCell) foodTotalCell.textContent = totalFood.toFixed(2);
            if (fieldBonusTotalCell) fieldBonusTotalCell.textContent = totalFieldBonus.toFixed(2);
            if (serviceTotalCell) serviceTotalCell.textContent = totalService.toFixed(2);
        } else {
            if (foodTotalCell) foodTotalCell.textContent = '0';
            if (fieldBonusTotalCell) fieldBonusTotalCell.textContent = '0';
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
                utilCell.style.color = '#4e6952ff';
                utilCell.style.fontWeight = 'bold';
            } else if (utilizationRate >= 85 && utilizationRate <= 90) {
                utilCell.style.color = '#FF4500';
            } else if (utilizationRate >= 91) {
                utilCell.style.color = '#B22222';
            }
        }
    });
}

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
        const servicioRow = fieldBonusRow.nextElementSibling;

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

            if (dailyActivity) {
                const statusIndicator = cell.querySelector('.status-indicator');
                if (statusIndicator) {
                    const activityType = dailyActivity.activity_type || 'N';
                    statusIndicator.textContent = activityType.toUpperCase();
                    statusIndicator.className = `status-indicator status-${activityType.toLowerCase()}`;
                    statusIndicator.title = dailyActivity.activity_description || '';

                    const dayStatus = getDailyStatusIndicator(dailyActivity);

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
                if (servicioCells[index]) {
                    servicioCells[index].textContent = '0';
                    servicioCells[index].title = 'No hay servicios registrados';
                }
            }
        });

        // Habilitar/deshabilitar botones de revisión/aprobación masiva
        const employeeLog = workLogsData.find(log => log.employee_id === employeeData.employee_id);
        const employeeAssignment = loadChartAssignments.find(a => a.employee_id === employeeData.employee_id);
        const isReviewerForEmployee = employeeAssignment && employeeAssignment.reviewer_id === currentUserId;
        const isApproverForEmployee = employeeAssignment && employeeAssignment.approver_id === currentUserId;

        const reviewBtn = employeeRow.querySelector('.btn-review');
        const approveBtn = employeeRow.querySelector('.btn-approve');

        if (reviewBtn) {
             const allDaysReviewed = employeeLog.daily_activities.every(act => act.day_status === 'reviewed' || act.day_status === 'approved' || act.day_status === 'rejected');
             reviewBtn.disabled = !isReviewerForEmployee || allDaysReviewed;
             reviewBtn.classList.toggle('reviewed', employeeLog.reviewed_at !== null);
        }

        if (approveBtn) {
            const allDaysApproved = employeeLog.daily_activities.every(act => act.day_status === 'approved');
            approveBtn.disabled = !isApproverForEmployee || allDaysApproved;
            approveBtn.classList.toggle('approved', employeeLog.approved_at !== null);
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
    const itemTypes = ['food_bonuses', 'field_bonuses', 'services_list'];
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
    // 1. If any element is REJECTED → day_status = 'rejected'
    if (hasRejected) {
        return 'rejected';
    }

    // 2. If there are PENDING elements → day_status = 'pending'
    if (hasPending) {
        return 'pending';
    }

    // 3. If ALL elements are APPROVED → day_status = 'approved'
    if (approvedItems === totalItems) {
        return 'approved';
    }

    // 4. If there are REVIEWED elements (and possibly APPROVED, without pending or rejected) → day_status = 'reviewed'
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
