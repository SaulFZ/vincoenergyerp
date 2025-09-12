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
