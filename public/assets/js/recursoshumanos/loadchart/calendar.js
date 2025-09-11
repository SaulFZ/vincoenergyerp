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
                // Ícono de candado para 'aprobado'
                return `<i class="fas fa-lock status-icon" style="color: ${getCSSVariable('--approved')};" title="Aprobado"></i>`;
            case 'rejected':
                // Ícono de triángulo de advertencia para 'rechazado'
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
            showRejectionMessage('activity-type', activity.rejection_reason);
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

                // Insertar después del campo
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

        // Si la actividad completa está aprobada, todo está bloqueado
        if (activity.day_status === 'approved') return true;

        // Verificar bloqueos específicos por tipo
        switch (fieldType) {
            case 'activity':
                return activity.activity_status === 'Approved';

            case 'service':
                if (activity.services_list && activity.services_list[itemIndex]) {
                    return activity.services_list[itemIndex].status === 'Approved';
                }
                return false;

            case 'food_bonus':
                if (activity.food_bonuses && activity.food_bonuses[itemIndex]) {
                    return activity.food_bonuses[itemIndex].status === 'Approved';
                }
                return false;

            case 'field_bonus':
                if (activity.field_bonuses && activity.field_bonuses[itemIndex]) {
                    return activity.field_bonuses[itemIndex].status === 'Approved';
                }
                return false;

            default:
                return false;
        }
    }

    // Función para bloquear/desbloquear campos
    function toggleFieldLock(element, isLocked, fieldType = '') {
        if (!element) return;

        if (isLocked) {
            element.disabled = true;
            element.style.backgroundColor = '#f5f5f5';
            element.style.color = '#999';
            element.style.cursor = 'not-allowed';

            // Agregar indicador visual de bloqueo
            const parentGroup = element.closest('.form-group');
            if (parentGroup && !parentGroup.querySelector('.lock-indicator')) {
                const lockIndicator = document.createElement('div');
                lockIndicator.className = 'lock-indicator';
                lockIndicator.innerHTML = '<i class="fas fa-lock"></i> Campo bloqueado (aprobado)';
                parentGroup.appendChild(lockIndicator);
            }
        } else {
            element.disabled = false;
            element.style.backgroundColor = '';
            element.style.color = '';
            element.style.cursor = '';

            // Remover indicador de bloqueo
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
        const isActivityLocked = isFieldLocked(activity, 'activity');
        const activityTypeOptions = document.querySelectorAll('.activity-option');
        const commissionedSelect = document.getElementById('commissioned-select');

        activityTypeOptions.forEach(option => {
            if (isActivityLocked) {
                option.style.pointerEvents = 'none';
                option.style.opacity = '0.6';
            } else {
                option.style.pointerEvents = '';
                option.style.opacity = '';
            }
        });

        toggleFieldLock(commissionedSelect, isActivityLocked, 'activity');

        // Bloquear campos de servicio
        const isServiceLocked = isFieldLocked(activity, 'service');
        const workTypeOptions = document.querySelectorAll('.work-type-option');
        const serviceTypeSelect = document.getElementById('service-type');
        const servicePerformedSelect = document.getElementById('service-performed');
        const serviceSelect = document.getElementById('service');

        workTypeOptions.forEach(option => {
            if (isServiceLocked) {
                option.style.pointerEvents = 'none';
                option.style.opacity = '0.6';
            } else {
                option.style.pointerEvents = '';
                option.style.opacity = '';
            }
        });

        toggleFieldLock(serviceTypeSelect, isServiceLocked, 'service');
        toggleFieldLock(servicePerformedSelect, isServiceLocked, 'service');
        toggleFieldLock(serviceSelect, isServiceLocked, 'service');

        // Bloquear campos de bonos
        const isFoodBonusLocked = isFieldLocked(activity, 'food_bonus');
        const isFieldBonusLocked = isFieldLocked(activity, 'field_bonus');

        toggleFieldLock(document.getElementById('food-bonus'), isFoodBonusLocked, 'food_bonus');
        toggleFieldLock(document.getElementById('field-bonus'), isFieldBonusLocked, 'field_bonus');
    }

    /**
     * Rellena el modal con los datos de una actividad existente.
     * @param {object} activity - El objeto de actividad con sus detalles.
     */
    function populateModalWithActivity(activity) {
        console.log('Populando modal con actividad:', activity); // Debug log
        currentActivity = activity; // Guardar referencia a la actividad actual

        // Resetear el formulario antes de rellenarlo
        resetActivityOptions();
        resetServiceForm();
        resetAdditionalForms();
        resetBonusFields();
        clearRejectionMessages();

        // Determinar qué pestaña activar
        const activityTabBtn = document.querySelector('.tab-btn[data-tab="activity"]');
        const serviceTabBtn = document.querySelector('.tab-btn[data-tab="service"]');

        // Si es una actividad de trabajo, activa la pestaña de servicio
        if (['B', 'P', 'C'].includes(activity.activity_type)) {
            serviceTabBtn.click();
            activityTabBtn.classList.remove('active');
        } else {
            activityTabBtn.click();
            serviceTabBtn.classList.remove('active');
        }

        // Rellenar los campos de la actividad
        const activityTypeOption = document.querySelector(`.activity-option[data-value="${activity.activity_type}"]`);
        if (activityTypeOption) {
            activityTypeOption.click();
        }

        // Manejar campo comisionado
        if (activity.activity_type === 'C' && activity.commissioned_to) {
            commissionedField.style.display = 'block';
            commissionedSelect.value = activity.commissioned_to;
        }

        // Rellenar los campos de servicio si existen
        if (activity.services_list && activity.services_list.length > 0) {
            const service = activity.services_list[0];
            console.log('Servicio encontrado:', service); // Debug log
            console.log('Datos de servicio disponibles:', serviceData); // Debug log

            // Determinar el tipo de operación basado en el service_identifier
            let operationType = null;
            let fullServiceData = null;

            // Buscar en todos los tipos de operación disponibles
            for (const [opType, services] of Object.entries(serviceData)) {
                console.log(`Buscando en tipo de operación: ${opType}`); // Debug log

                if (Array.isArray(services)) {
                    fullServiceData = services.find(s => s.identifier === service.service_identifier);
                    if (fullServiceData) {
                        operationType = opType;
                        console.log(`Servicio encontrado en tipo: ${opType}`); // Debug log
                        break;
                    }
                }
            }

            console.log('Tipo de operación determinado:', operationType); // Debug log
            console.log('Datos completos del servicio:', fullServiceData); // Debug log

            if (operationType && fullServiceData) {
                // Mapear el tipo de operación a los valores del HTML
                let workTypeValue = operationType;
                if (operationType === 'Tierra') {
                    workTypeValue = 'Tierra';
                } else if (operationType === 'Marina' || operationType === 'Marino') {
                    workTypeValue = 'Marina';
                }

                console.log('Work type value para HTML:', workTypeValue); // Debug log

                // Seleccionar el tipo de trabajo
                const workTypeOption = document.querySelector(`.work-type-option[data-value="${workTypeValue}"]`);
                if (workTypeOption) {
                    workTypeOption.click();
                    console.log('Tipo de trabajo seleccionado:', workTypeValue); // Debug log
                } else {
                    console.error('No se encontró work-type-option para:', workTypeValue); // Debug log
                }

                // Usar setTimeout para dar tiempo a que los selects se pueblen
                setTimeout(() => {
                    const serviceTypeSelect = document.getElementById('service-type');
                    const servicePerformedSelect = document.getElementById('service-performed');
                    const serviceSelect = document.getElementById('service');

                    // Seleccionar service_type
                    const serviceTypeFormatted = fullServiceData.service_type.toLowerCase().replace(/\s+/g, '_');
                    console.log('Service type formatted:', serviceTypeFormatted); // Debug log

                    // Verificar que la opción existe en el select
                    const serviceTypeOption = Array.from(serviceTypeSelect.options).find(opt => opt.value === serviceTypeFormatted);
                    if (serviceTypeOption) {
                        serviceTypeSelect.value = serviceTypeFormatted;
                        serviceTypeSelect.dispatchEvent(new Event('change'));
                        console.log('Service type seleccionado correctamente'); // Debug log
                    } else {
                        console.error('Service type option no encontrada:', serviceTypeFormatted); // Debug log
                    }

                    setTimeout(() => {
                        // Seleccionar service_performed
                        const servicePerformedFormatted = fullServiceData.service_performed.toLowerCase().replace(/\s+/g, '_');
                        console.log('Service performed formatted:', servicePerformedFormatted); // Debug log

                        const servicePerformedOption = Array.from(servicePerformedSelect.options).find(opt => opt.value === servicePerformedFormatted);
                        if (servicePerformedOption) {
                            servicePerformedSelect.value = servicePerformedFormatted;
                            servicePerformedSelect.dispatchEvent(new Event('change'));
                            console.log('Service performed seleccionado correctamente'); // Debug log
                        } else {
                            console.error('Service performed option no encontrada:', servicePerformedFormatted); // Debug log
                        }

                        setTimeout(() => {
                            // Seleccionar el servicio específico
                            console.log('Seleccionando servicio:', service.service_identifier); // Debug log

                            const serviceOption = Array.from(serviceSelect.options).find(opt => opt.value === service.service_identifier);
                            if (serviceOption) {
                                serviceSelect.value = service.service_identifier;
                                console.log('Servicio seleccionado correctamente'); // Debug log
                            } else {
                                console.error('Service option no encontrada:', service.service_identifier); // Debug log
                            }
                        }, 150);
                    }, 150);
                }, 150);
            } else {
                console.error('No se pudo determinar el tipo de operación para el servicio:', service.service_identifier);
            }
        }

        // Rellenar los campos de bonos si existen
        // Bono de comida
        if (activity.food_bonuses && activity.food_bonuses.length > 0) {
            const foodBonus = activity.food_bonuses[0];
            console.log('Bono de comida encontrado:', foodBonus); // Debug log

            if (foodBonus.num_daily) {
                document.getElementById('food-bonus').value = foodBonus.num_daily;
            }
        }

        // Bono de campo
        if (activity.field_bonuses && activity.field_bonuses.length > 0) {
            const fieldBonus = activity.field_bonuses[0];
            console.log('Bono de campo encontrado:', fieldBonus); // Debug log

            const fieldBonusSelect = document.getElementById('field-bonus');

            if (fieldBonus.bonus_identifier) {
                fieldBonusSelect.value = fieldBonus.bonus_identifier;
            } else if (fieldBonus.bonus_type) {
                // Buscar en las opciones del select el que corresponde al bonus_type
                Array.from(fieldBonusSelect.options).forEach(option => {
                    if (option.text.includes(fieldBonus.bonus_type) || option.value === fieldBonus.bonus_type) {
                        fieldBonusSelect.value = option.value;
                    }
                });
            }
        }

        // Aplicar estados de bloqueo después de rellenar los campos
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
                console.log('Actividad existente encontrada para', processedDate, ':', existingActivity); // Debug log
                populateModalWithActivity(existingActivity);
            } else {
                console.log('No hay actividad existente para', processedDate); // Debug log
                currentActivity = null;
                // Si no hay actividad, resetear el formulario
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

        // Limpiar indicadores de bloqueo
        document.querySelectorAll('.lock-indicator').forEach(el => el.remove());
    }

    // Función para cargar actividades del mes
    async function loadMonthlyActivities(month, year) {
        try {
            const response = await fetch(`/recursoshumanos/loadchart/monthly-activities?month=${month}&year=${year}`);
            const data = await response.json();

            if (data.success) {
                monthlyActivities = {};
                // Convertir el array de actividades en un objeto indexado por fecha
                if (data.activities && Array.isArray(data.activities)) {
                    data.activities.forEach(activity => {
                        monthlyActivities[activity.date] = activity;
                    });
                    console.log('Actividades mensuales cargadas:', monthlyActivities); // Debug log
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

            // Limpiar contenido previo de actividades
            const existingHeaderInfo = cell.querySelector('.day-header-info');
            if (existingHeaderInfo) {
                existingHeaderInfo.remove();
            }

            // Construir la fecha para esta celda
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

            // Verificar si hay actividad para esta fecha
            if (monthlyActivities[cellDate]) {
                const activity = monthlyActivities[cellDate];
                const activityHTML = createActivityHTML(activity);

                // Insertar el nuevo HTML justo después del elemento con la clase day-number
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
        // No permitir abrir si está bloqueado
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
            // No permitir selección si está bloqueado
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
            // No permitir selección si está bloqueado
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
        console.log('Actualizando tipos de servicio para:', workType); // Debug log
        console.log('Service data disponible:', serviceData); // Debug log

        const serviceTypeSelect = document.getElementById('service-type');
        serviceTypeSelect.innerHTML = '<option value="">Seleccionar tipo de servicio...</option>';

        if (serviceData[workType] && Array.isArray(serviceData[workType])) {
            const uniqueTypes = [...new Set(serviceData[workType].map(item => item.service_type))];
            console.log('Tipos únicos encontrados:', uniqueTypes); // Debug log

            uniqueTypes.forEach(type => {
                const option = document.createElement('option');
                option.value = type.toLowerCase().replace(/\s+/g, '_');
                option.textContent = type;
                serviceTypeSelect.appendChild(option);
            });
        } else {
            console.error('No se encontraron datos de servicio para el tipo:', workType); // Debug log
        }

        document.getElementById('service-performed').disabled = true;
        document.getElementById('service-performed').innerHTML = '<option value="">Seleccionar servicio realizado...</option>';
        document.getElementById('service').disabled = true;
        document.getElementById('service').innerHTML = '<option value="">Seleccionar servicio...</option>';
    }

    document.getElementById('service-type').addEventListener('change', function () {
        // No continuar si está bloqueado
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
        // No continuar si está bloqueado
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
                    performed: item.service_performed
                }));

            services.forEach(service => {
                const option = document.createElement('option');
                option.value = service.id;
                option.textContent = service.name;
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
        // Verificar si hay campos bloqueados que impidan guardar
        if (currentActivity) {
            const isActivityBlocked = isFieldLocked(currentActivity, 'activity');
            const isServiceBlocked = isFieldLocked(currentActivity, 'service');
            const isFoodBonusBlocked = isFieldLocked(currentActivity, 'food_bonus');
            const isFieldBonusLocked = isFieldLocked(currentActivity, 'field_bonus');

            // Si está completamente aprobado, no permitir cambios
            if (currentActivity.day_status === 'approved') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'No se pueden modificar actividades que ya han sido aprobadas.',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            // Verificar si hay intentos de modificar campos aprobados específicos
            let hasApprovedChanges = false;
            let approvedFieldsMessage = [];

            if (isActivityBlocked && activityTypeSelect.value !== currentActivity.activity_type) {
                hasApprovedChanges = true;
                approvedFieldsMessage.push('Tipo de actividad');
            }

            if (isServiceBlocked && document.getElementById('service-tab').classList.contains('active')) {
                const currentServiceId = document.getElementById('service').value;
                const existingServiceId = currentActivity.services_list?.[0]?.service_identifier;
                if (currentServiceId !== existingServiceId) {
                    hasApprovedChanges = true;
                    approvedFieldsMessage.push('Servicio');
                }
            }

            if (isFoodBonusBlocked) {
                const currentFoodBonus = document.getElementById('food-bonus').value;
                const existingFoodBonus = currentActivity.food_bonuses?.[0]?.num_daily?.toString();
                if (currentFoodBonus !== existingFoodBonus) {
                    hasApprovedChanges = true;
                    approvedFieldsMessage.push('Bono de comida');
                }
            }

            if (isFieldBonusBlocked) {
                const currentFieldBonus = document.getElementById('field-bonus').value;
                const existingFieldBonus = currentActivity.field_bonuses?.[0]?.bonus_identifier;
                if (currentFieldBonus !== existingFieldBonus) {
                    hasApprovedChanges = true;
                    approvedFieldsMessage.push('Bono de campo');
                }
            }

            if (hasApprovedChanges) {
                Swal.fire({
                    icon: 'error',
                    title: 'No se puede guardar',
                    text: `No se pueden modificar los siguientes campos porque ya están aprobados: ${approvedFieldsMessage.join(', ')}`,
                    confirmButtonText: 'Entendido'
                });
                return;
            }
        }

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

        if (document.getElementById('activity-tab').classList.contains('active')) {
            if (!activityTypeSelect.value) {
                document.getElementById('activity-type-error').style.display = 'block';
                isValid = false;
            }
            if (activityTypeSelect.value === 'C' && !commissionedSelect.value) {
                document.getElementById('commissioned-error').style.display = 'block';
                isValid = false;
            }
        } else { // Service tab
            const workTypeSelected = document.querySelector('.work-type-option.selected');
            const serviceTypeValue = document.getElementById('service-type').value;
            const servicePerformedValue = document.getElementById('service-performed').value;
            const serviceValue = document.getElementById('service').value;

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

        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Campos incompletos',
                text: 'Por favor, rellene todos los campos requeridos antes de guardar.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        // PREPARAR DATOS PARA ENVIAR AL SERVIDOR
        const monthSpan = document.querySelector('.month-navigation span');
        const formData = {
            date: currentSelectedDate,
            displayed_month: monthSpan.getAttribute('data-month'),
            displayed_year: monthSpan.getAttribute('data-year'),
            activity_type: activityTypeSelect.value,
            commissioned_to: commissionedSelect.value || null,
            food_bonus_number: document.getElementById('food-bonus').value || null,
            field_bonus_identifier: document.getElementById('field-bonus').value || null
        };

        if (document.getElementById('service-tab').classList.contains('active')) {
            const serviceSelect = document.getElementById('service');
            const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
            formData.service_identifier = serviceSelect.value;
            formData.service_performed = selectedOption.getAttribute('data-performed');
            formData.amount = 25000.00; // Valor por defecto
            formData.currency = 'MXN';
        }

        // New check: if both activity and service types are empty, don't save
        if (!formData.activity_type && !formData.service_identifier) {
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
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(formData)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: '¡Guardado!',
                        text: 'El registro se ha guardado exitosamente.',
                        showConfirmButton: false,
                        timer: 1500,
                        toast: true
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
                    if (data.errors) {
                        console.error('Errores de validación:', data.errors);
                    }
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

                    // Adjust month and year for "other-month" cells
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

        // Calcular el mes anterior
        let prevMonth = currentMonth === 1 ? 12 : currentMonth - 1;
        let prevYear = currentMonth === 1 ? currentYear - 1 : currentYear;

        // Deshabilitar botón anterior si estamos en el límite mínimo
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
        // Verificar si la fecha solicitada está dentro de los límites
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

            // Actualizar estado de los botones después de cargar el calendario
            updateNavigationButtons();

            // Cargar actividades para el nuevo mes
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
        if (this.disabled) return; // No hacer nada si el botón está deshabilitado

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

    // Inicializar el estado de los botones al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
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
