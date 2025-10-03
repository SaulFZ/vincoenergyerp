document.addEventListener('DOMContentLoaded', function() {
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
    let currentSelectedDate = null;
    let monthlyActivities = {};
    let currentActivity = null;
    let currentPayrollDates = {};

    function getCSSVariable(varName) {
        return getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
    }

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
        return `
            <div class="day-header-info">
                <div class="activity-tag" style="background-color: ${color};">
                    ${activity.activity_description}
                </div>
                ${statusIcon}
            </div>
            `;
    }

    function handleActivityTypeChange(activityType) {
        const wellNameField = document.getElementById('well-name-field');
        const wellNameInput = document.getElementById('well-name');
        const commissionedField = document.getElementById('commissioned-field');
        const commissionedSelect = document.getElementById('commissioned-select');

        if (activityType === 'P') {
            wellNameField.style.display = 'block';
            commissionedField.style.display = 'none';
            commissionedSelect.selectedIndex = 0;
        } else if (activityType === 'C') {
            wellNameField.style.display = 'none';
            wellNameInput.value = '';
            commissionedField.style.display = 'block';
        } else {
            wellNameField.style.display = 'none';
            wellNameInput.value = '';
            commissionedField.style.display = 'none';
            commissionedSelect.selectedIndex = 0;
        }
    }

    function handleServiceBonusChange(hasServiceBonus) {
        const serviceTab = document.getElementById('service-tab');
        const activityTabBtn = document.querySelector('.tab-btn[data-tab="activity"]');

        if (hasServiceBonus === 'si') {
            serviceTabBtn.style.display = 'block';
            if (activityTabBtn.classList.contains('active') && currentActivity && currentActivity.services_list && currentActivity.services_list.length > 0) {
                serviceTabBtn.click();
            }
        } else {
            serviceTabBtn.style.display = 'none';
            if (serviceTab.classList.contains('active')) {
                activityTabBtn.click();
            }
            resetServiceForm();
        }
    }

    function populatePayrollPeriodOptions() {
        const payrollPeriodSelect = document.getElementById('payroll-period');
        const monthSpan = document.querySelector('.month-navigation span');
        const currentMonth = parseInt(monthSpan.getAttribute('data-month'));
        const currentYear = parseInt(monthSpan.getAttribute('data-year'));

        const monthNames = [
            "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
            "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
        ];

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

        if (q1Start && q1End && selectedDate >= q1Start && selectedDate <= q1End) {
            const prevQ2Option = document.createElement('option');
            prevQ2Option.value = 'previous_q2';
            prevQ2Option.textContent = `Segunda Quincena - ${prevMonthName} ${prevYear}`;
            payrollPeriodSelect.appendChild(prevQ2Option);
        } else if (q2Start && q2End && selectedDate >= q2Start && selectedDate <= q2End) {
            const currentQ1Option = document.createElement('option');
            currentQ1Option.value = 'current_q1';
            currentQ1Option.textContent = `Primera Quincena - ${monthNames[currentMonth - 1]} ${currentYear}`;
            payrollPeriodSelect.appendChild(currentQ1Option);
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

        if (activity.rejection_reason && activity.day_status === 'rejected') {
            showRejectionMessage('activity-type-select', activity.rejection_reason);
        }

        if (activity.services_list && activity.services_list.length > 0) {
            activity.services_list.forEach(service => {
                if (service.status === 'rejected' && service.rejection_reason) {
                    showRejectionMessage('service', service.rejection_reason);
                }
            });
        }

        if (activity.food_bonuses && activity.food_bonuses.length > 0) {
            activity.food_bonuses.forEach(bonus => {
                if (bonus.status === 'rejected' && bonus.rejection_reason) {
                    showRejectionMessage('food-bonus', bonus.rejection_reason);
                }
            });
        }

        if (activity.field_bonuses && activity.field_bonuses.length > 0) {
            activity.field_bonuses.forEach(bonus => {
                if (bonus.status === 'rejected' && bonus.rejection_reason) {
                    showRejectionMessage('field-bonus', bonus.rejection_reason);
                }
            });
        }
    }

    function showRejectionMessage(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (field) {
            const parentGroup = field.closest('.form-group');
            if (parentGroup) {
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

    function clearRejectionMessages() {
        document.querySelectorAll('.rejection-message').forEach(el => el.remove());
    }

    function clearAllBlocksAndMessages() {
        clearRejectionMessages();
        document.querySelectorAll('.lock-indicator').forEach(el => el.remove());
        unlockAllFields();
    }

    function unlockAllFields() {
        const elementsToUnlock = [
            document.getElementById('activity-type-select'),
            ...document.querySelectorAll('.activity-option'),
            document.getElementById('commissioned-select'),
            document.getElementById('well-name'),
            ...document.querySelectorAll('.service-bonus-option'),
            ...document.querySelectorAll('.work-type-option'),
            document.getElementById('service-type'),
            document.getElementById('service-performed'),
            document.getElementById('service'),
            document.getElementById('payroll-period'),
            document.getElementById('food-bonus'),
            document.getElementById('field-bonus')
        ];

        elementsToUnlock.forEach(element => {
            if (element) {
                if (element.classList.contains('custom-select')) {
                    element.classList.remove('locked');
                } else if (element.classList.contains('work-type-option') || element.classList.contains('activity-option') || element.classList.contains('service-bonus-option')) {
                    element.style.pointerEvents = '';
                    element.style.opacity = '';
                } else {
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
    }

    function isFieldLocked(activity, fieldType, itemIndex = 0) {
        if (!activity) return false;

        if (activity.day_status === 'approved') {
            return true;
        }

        switch (fieldType) {
            case 'activity':
                return activity.activity_status === 'approved' || activity.activity_status === 'reviewed';
            case 'service':
                if (activity.services_list && activity.services_list[itemIndex]) {
                    return activity.services_list[itemIndex].status === 'approved' || activity.services_list[itemIndex].status === 'reviewed';
                }
                return false;
            case 'food_bonus':
                if (activity.food_bonuses && activity.food_bonuses[itemIndex]) {
                    return activity.food_bonuses[itemIndex].status === 'approved' || activity.food_bonuses[itemIndex].status === 'reviewed';
                }
                return false;
            case 'field_bonus':
                if (activity.field_bonuses && activity.field_bonuses[itemIndex]) {
                    return activity.field_bonuses[itemIndex].status === 'approved' || activity.field_bonuses[itemIndex].status === 'reviewed';
                }
                return false;
            default:
                return false;
        }
    }

    function toggleFieldLock(element, isLocked, status) {
        if (!element) return;
        const parentGroup = element.closest('.form-group');
        if (!parentGroup) return;

        const isCustomSelect = element.classList.contains('custom-select');
        const isOption = element.classList.contains('work-type-option') || element.classList.contains('activity-option') || element.classList.contains('service-bonus-option');

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

            if (!parentGroup.querySelector('.lock-indicator')) {
                const lockIndicator = document.createElement('div');
                lockIndicator.className = 'lock-indicator';
                let message = '';
                let icon = '';
                if (status === 'approved') {
                    message = 'Aprobado';
                    icon = 'fas fa-lock';
                } else if (status === 'reviewed') {
                    message = 'Revisado';
                    icon = 'fas fa-lock-open';
                }
                lockIndicator.innerHTML = `<i class="${icon}"></i> ${message}`;
                parentGroup.appendChild(lockIndicator);
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

            const lockIndicator = parentGroup.querySelector('.lock-indicator');
            if (lockIndicator) lockIndicator.remove();
        }
    }

    function applyFieldLocks(activity) {
        if (!activity) return;

        if (activity.day_status === 'approved') {
            const allFormElements = document.querySelectorAll('#activity-tab select, #activity-tab .custom-select, #activity-tab input, #service-tab select, #service-tab .work-type-option');
            allFormElements.forEach(el => toggleFieldLock(el, true, 'approved'));
            return;
        }

        const isActivityLocked = isFieldLocked(activity, 'activity');
        const activityTypeSelectContainer = document.getElementById('activity-type-select');
        const commissionedSelect = document.getElementById('commissioned-select');
        const wellNameInput = document.getElementById('well-name');
        const activityTypeOptions = document.querySelectorAll('.activity-option');
        const serviceBonusOptions = document.querySelectorAll('.service-bonus-option');

        toggleFieldLock(activityTypeSelectContainer, isActivityLocked, activity.activity_status);
        activityTypeOptions.forEach(option => toggleFieldLock(option, isActivityLocked, activity.activity_status));
        serviceBonusOptions.forEach(option => toggleFieldLock(option, isActivityLocked, activity.activity_status));
        toggleFieldLock(commissionedSelect, isActivityLocked, activity.activity_status);
        toggleFieldLock(wellNameInput, isActivityLocked, activity.activity_status);

        const service = activity.services_list?.[0];
        const isServiceLocked = isFieldLocked(activity, 'service');
        const workTypeOptions = document.querySelectorAll('.work-type-option');
        const serviceTypeSelect = document.getElementById('service-type');
        const servicePerformedSelect = document.getElementById('service-performed');
        const serviceSelect = document.getElementById('service');
        const payrollPeriodSelect = document.getElementById('payroll-period');
        const serviceAmountInput = document.getElementById('service-amount');

        workTypeOptions.forEach(option => toggleFieldLock(option, isServiceLocked, service?.status));
        toggleFieldLock(serviceTypeSelect, isServiceLocked, service?.status);
        toggleFieldLock(servicePerformedSelect, isServiceLocked, service?.status);
        toggleFieldLock(serviceSelect, isServiceLocked, service?.status);
        toggleFieldLock(payrollPeriodSelect, isServiceLocked, service?.status);
        toggleFieldLock(serviceAmountInput, isServiceLocked, service?.status);

        const foodBonus = activity.food_bonuses?.[0];
        const isFoodBonusLocked = isFieldLocked(activity, 'food_bonus');
        toggleFieldLock(document.getElementById('food-bonus'), isFoodBonusLocked, foodBonus?.status);

        const fieldBonus = activity.field_bonuses?.[0];
        const isFieldBonusLocked = isFieldLocked(activity, 'field_bonus');
        toggleFieldLock(document.getElementById('field-bonus'), isFieldBonusLocked, fieldBonus?.status);

        const rejectedFields = getRejectedFields(activity);
        rejectedFields.forEach(field => {
            const element = document.getElementById(field.id);
            if (element) {
                toggleFieldLock(element, false, 'rejected');
            }
        });
        showRejectionMessages(activity);
    }

    function getRejectedFields(activity) {
        const rejected = [];
        if (!activity) return rejected;

        if (activity.day_status === 'rejected') {
            rejected.push({ id: 'activity-type-select' });
        }
        if (activity.services_list && activity.services_list.some(s => s.status === 'rejected')) {
            rejected.push({ id: 'service' });
        }
        if (activity.food_bonuses && activity.food_bonuses.some(b => b.status === 'rejected')) {
            rejected.push({ id: 'food-bonus' });
        }
        if (activity.field_bonuses && activity.field_bonuses.some(b => b.status === 'rejected')) {
            rejected.push({ id: 'field-bonus' });
        }
        return rejected;
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

        const hasService = activity.services_list && activity.services_list.length > 0;
        if (hasService) {
            serviceTabBtn.style.display = 'block';
            document.getElementById('service-bonus-yes').checked = true;
            document.querySelector('.service-bonus-option[data-value="si"]').classList.add('selected');
            document.querySelector('.service-bonus-option[data-value="no"]').classList.remove('selected');
            serviceTabButton.click();
        } else {
            serviceTabBtn.style.display = 'none';
            document.getElementById('service-bonus-no').checked = true;
            document.querySelector('.service-bonus-option[data-value="no"]').classList.add('selected');
            document.querySelector('.service-bonus-option[data-value="si"]').classList.remove('selected');
            activityTabBtn.click();
        }

        const activityTypeOption = document.querySelector(`.activity-option[data-value="${activity.activity_type}"]`);
        if (activityTypeOption) {
            activityTypeOption.classList.add('selected');
            document.getElementById('activity-type').value = activity.activity_type;
            document.querySelector('#activity-type-header .placeholder').textContent = activityTypeOption.querySelector('.activity-label').textContent;

            handleActivityTypeChange(activity.activity_type);
        }

        if (activity.activity_type === 'C' && activity.commissioned_to) {
            document.getElementById('commissioned-field').style.display = 'block';
            document.getElementById('commissioned-select').value = activity.commissioned_to;
        }

        if (activity.well_name) {
            document.getElementById('well-name').value = activity.well_name;
        }

        if (hasService) {
            const service = activity.services_list[0];
            populateServiceData(service);
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
                workTypeOption.click();
            }

            setTimeout(() => {
                const serviceTypeSelect = document.getElementById('service-type');
                const servicePerformedSelect = document.getElementById('service-performed');
                const serviceSelect = document.getElementById('service');
                const payrollPeriodSelect = document.getElementById('payroll-period');
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
                            payrollPeriodSelect.value = service.payroll_period_override;
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

                document.querySelector('.tab-btn[data-tab="activity"]').click();
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
        document.getElementById('service-type').selectedIndex = 0;
        document.getElementById('service-performed').disabled = true;
        document.getElementById('service-performed').innerHTML = '<option value="">Seleccionar servicio realizado...</option>';
        document.getElementById('service').disabled = true;
        document.getElementById('service').innerHTML = '<option value="">Seleccionar servicio...</option>';
        document.getElementById('payroll-period').selectedIndex = 0;
        document.querySelectorAll('.error-message').forEach(el => {
            el.style.display = 'none';
        });

        // CORRECCIÓN: Resetear el campo del monto del servicio
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
    const activityTypeOptions = document.getElementById('activity-type-options');
    const activityTypeSelect = document.getElementById('activity-type');

    activityTypeHeader.addEventListener('click', function() {
        if (currentActivity && isFieldLocked(currentActivity, 'activity')) {
            return;
        }
        this.classList.toggle('open');
        activityTypeOptions.classList.toggle('open');
    });

    document.addEventListener('click', function(e) {
        if (!activityTypeHeader.contains(e.target) && !activityTypeOptions.contains(e.target)) {
            activityTypeHeader.classList.remove('open');
            activityTypeOptions.classList.remove('open');
        }
    });

    activityOptions.forEach(option => {
        option.addEventListener('click', function() {
            if (currentActivity && isFieldLocked(currentActivity, 'activity')) {
                return;
            }
            const value = this.getAttribute('data-value');
            activityOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            activityTypeSelect.value = value;
            document.querySelector('#activity-type-header .placeholder').textContent = this.querySelector('.activity-label').textContent;
            activityTypeHeader.classList.remove('open');
            activityTypeOptions.classList.remove('open');

            handleActivityTypeChange(value);

            if (value === 'C') {
                document.getElementById('commissioned-field').style.display = 'block';
            } else {
                document.getElementById('commissioned-field').style.display = 'none';
                document.getElementById('commissioned-select').selectedIndex = 0;
            }
        });
    });

    const serviceBonusOptions = document.querySelectorAll('.service-bonus-option');
    serviceBonusOptions.forEach(option => {
        option.addEventListener('click', function() {
            if (currentActivity && isFieldLocked(currentActivity, 'activity')) {
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
        option.addEventListener('click', function() {
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

        // CORRECCIÓN: Resetear el monto del servicio al cambiar el tipo de trabajo
        document.getElementById('service-amount').value = '';
        document.getElementById('service-amount-group').style.display = 'none';
    }

    document.getElementById('service-type').addEventListener('change', function() {
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
            document.getElementById('service-amount-group').style.display = 'none';
        }
    });

    document.getElementById('service-performed').addEventListener('change', function() {
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
                );
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

    document.getElementById('service').addEventListener('change', function() {
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
        tab.addEventListener('click', function() {
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
                const dayInCell = parseInt(dayNum);
                const isPreviousMonth = dayInCell > 15;
                if (isPreviousMonth) {
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

    saveBtn.addEventListener('click', function() {
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
        const activityType = document.getElementById('activity-type').value;
        const workTypeSelected = document.querySelector('.work-type-option.selected');
        const serviceSelect = document.getElementById('service');
        const serviceValue = serviceSelect.value;
        const hasServiceBonus = document.querySelector('input[name="has_service_bonus"]:checked')?.value;

        if (currentActivity) {
            let hasChangesToLockedFields = false;
            let lockedFieldsMessage = [];

            if (currentActivity.day_status === 'approved') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'No se pueden modificar actividades que ya han sido aprobadas.',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            const isActivityLocked = isFieldLocked(currentActivity, 'activity');
            if (isActivityLocked && activityType !== currentActivity.activity_type) {
                hasChangesToLockedFields = true;
                lockedFieldsMessage.push('Tipo de actividad');
            }

            if (isActivityLocked && activityType === 'C' && document.getElementById('commissioned-select').value !== currentActivity.commissioned_to) {
                hasChangesToLockedFields = true;
                lockedFieldsMessage.push('Área Comisionada');
            }

            if (isActivityLocked && activityType === 'P' && document.getElementById('well-name').value !== (currentActivity.well_name || '')) {
                hasChangesToLockedFields = true;
                lockedFieldsMessage.push('Nombre del Pozo');
            }

            const existingService = currentActivity.services_list?.[0];
            const isServiceLocked = isFieldLocked(currentActivity, 'service');
            if (isServiceLocked && serviceValue !== existingService?.service_identifier) {
                hasChangesToLockedFields = true;
                lockedFieldsMessage.push('Servicio');
            }

            const existingFoodBonus = currentActivity.food_bonuses?.[0];
            const currentFoodBonus = document.getElementById('food-bonus').value;
            const isFoodBonusLocked = isFieldLocked(currentActivity, 'food_bonus');
            if (isFoodBonusLocked && currentFoodBonus !== existingFoodBonus?.num_daily?.toString()) {
                hasChangesToLockedFields = true;
                lockedFieldsMessage.push('Bono de comida');
            }

            const existingFieldBonus = currentActivity.field_bonuses?.[0];
            const currentFieldBonus = document.getElementById('field-bonus').value;
            const isFieldBonusLocked = isFieldLocked(currentActivity, 'field_bonus');
            if (isFieldBonusLocked && currentFieldBonus !== existingFieldBonus?.bonus_identifier) {
                hasChangesToLockedFields = true;
                lockedFieldsMessage.push('Bono de campo');
            }

            if (hasChangesToLockedFields) {
                Swal.fire({
                    icon: 'error',
                    title: 'No se puede guardar',
                    text: `No se pueden modificar los siguientes campos porque ya están aprobados o revisados: ${lockedFieldsMessage.join(', ')}`,
                    confirmButtonText: 'Entendido'
                });
                return;
            }
        }

        if (isActivityTabActive && !isFieldLocked(currentActivity, 'activity')) {
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
        } else if (isServiceTabActive && hasServiceBonus === 'si' && !isFieldLocked(currentActivity, 'service')) {
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

        const hasFoodBonus = document.getElementById('food-bonus').value !== '';
        const hasFieldBonus = document.getElementById('field-bonus').value !== '';

        if (!activityType && hasServiceBonus === 'no' && !hasFoodBonus && !hasFieldBonus) {
            Swal.fire({
                icon: 'warning',
                title: 'Selección requerida',
                text: 'Por favor, selecciona una actividad antes de guardar.',
                confirmButtonText: 'Aceptar'
            });
            return;
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

        const monthSpan = document.querySelector('.month-navigation span');
        const formData = {
            date: currentSelectedDate,
            displayed_month: monthSpan.getAttribute('data-month'),
            displayed_year: monthSpan.getAttribute('data-year'),
            activity_type: activityType || null,
            commissioned_to: document.getElementById('commissioned-select').value || null,
            well_name: document.getElementById('well-name').value || null,
            has_service_bonus: hasServiceBonus,
            food_bonus_number: hasFoodBonus ? document.getElementById('food-bonus').value : null,
            field_bonus_identifier: hasFieldBonus ? document.getElementById('field-bonus').value : null
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

        if (hasServiceBonus === 'si' && serviceValue) {
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
                        text: 'El registro se ha guardado exitosamente.',
                        showConfirmButton: false,
                        timer: 1500,
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
            if (dayNumberEl && dayNumberEl.textContent.trim() !== '') {
                day.addEventListener('click', function() {
                    const dayNum = dayNumberEl.textContent.trim();
                    const monthSpan = document.querySelector('.month-navigation span');
                    let month = parseInt(monthSpan.getAttribute('data-month'));
                    let year = parseInt(monthSpan.getAttribute('data-year'));
                    const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

                    let targetMonth = month;
                    let targetYear = year;

                    if (day.classList.contains('other-month')) {
                        const dayInCell = parseInt(dayNum);
                        const isPreviousMonth = dayInCell > 15;
                        if (isPreviousMonth) {
                            targetMonth = (month === 1) ? 12 : month - 1;
                            if (month === 12) year--;
                        } else {
                            targetMonth = (month === 12) ? 1 : month + 1;
                            if (month === 1) year++;
                        }
                    }

                    const formattedDate = `${targetYear}-${String(targetMonth).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;

                    if (isDayInPayrollPeriod(formattedDate)) {
                        const displayDate = `${dayNum} de ${monthNames[targetMonth - 1]} de ${targetYear}`;
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
            currentPayrollDates = data.payrollDates;
            let newTableHTML = '';
            let currentRow = '<tr>';
            data.calendarDays.forEach((day, index) => {
                const isCurrentMonth = day.current_month;
                const isToday = day.is_today;
                const inPayrollPeriod = isDayInPayrollPeriod(day.date);
                const isHoliday = day.is_holiday;
                const holidayName = day.holiday_name;
                const holidayIconType = day.holiday_icon_type;

                const cellClass = `${!isCurrentMonth ? 'other-month' : ''} ${isToday ? 'current-d' : ''} ${inPayrollPeriod ? 'in-payroll-period' : ''}`;

                let holidayIconHTML = '';
                if (isHoliday) {
                    if (holidayIconType === 'christmas_tree') {
                        holidayIconHTML = `<img src="https://img.icons8.com/external-victoruler-flat-victoruler/64/external-christmas-tree-christmas-victoruler-flat-victoruler-1.png" alt="Árbol de Navidad" class="holiday-icon" title="${holidayName}">`;
                    } else {
                        holidayIconHTML = `<img src="https://img.icons8.com/skeuomorphism/32/event.png" alt="Día Festivo" class="holiday-icon" title="${holidayName}">`;
                    }
                }

                const payrollIcons = `
                    ${day.is_payroll_start_1 ? '<i class="fas fa-flag payroll-icon payroll-start-1" title="Inicio Quincena 1"></i>' : ''}
                    ${day.is_payroll_end_1 ? '<i class="fas fa-flag payroll-icon payroll-end" title="Fin Quincena 1"></i>' : ''}
                    ${day.is_payroll_start_2 ? '<i class="fas fa-flag payroll-icon payroll-start-2" title="Inicio Quincena 2"></i>' : ''}
                    ${day.is_payroll_end_2 ? '<i class="fas fa-flag payroll-icon payroll-end" title="Fin Quincena 2"></i>' : ''}
                `;

                currentRow += `<td class="${cellClass}" data-date="${day.date}">
                <span class="day-number">${day.day}</span>
                ${holidayIconHTML}
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

    prevMonthBtn.addEventListener('click', function() {
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

    nextMonthBtn.addEventListener('click', function() {
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

    document.addEventListener('DOMContentLoaded', function() {
        updateNavigationButtons();
    });

    const approveBtn = document.getElementById('approve-loadchart');
    if (approveBtn) {
        approveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = '/recursoshumanos/loadchart/approval';
        });
    }

    const initialMonth = parseInt(monthSpan.getAttribute('data-month'));
    const initialYear = parseInt(monthSpan.getAttribute('data-year'));
    updateCalendar(initialMonth, initialYear);
});

// ==== Código para la tabla de aprobación (no del calendario) ====
// El resto del código que proporcionaste para la tabla de aprobación

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
        const dayStatus = newData.day_status || 'under_review';
        statusIndicator.classList.remove('approved-border', 'reviewed-border', 'rejected-border', 'under-review-border');
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
                        day_status: ['under_review', 'reviewed', 'approved', 'rejected'][Math.floor(Math.random() * 4)]
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
        const activityStatus = dailyActivity.activity_status ?? 'under_review';
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

    const safeStatus = status || 'under_review';
    const statusPillClass = safeStatus.toLowerCase();
    const approvalSelector = getModalApprovalSelectorHtml(statusPillClass, isReviewer, isApprover, itemType, itemIndex);

    const isLocked = (status === 'approved' && !isApprover) || (status === 'reviewed' && !isApprover && !isReviewer) || (status === 'rejected');

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

    const canChangeToUnderReview = false;
    const isItemApproved = currentStatus === 'approved';
    const isItemRejected = currentStatus === 'rejected';
    const isItemReviewed = currentStatus === 'reviewed';

    if (isReviewer && !isApprover) { // Es revisor
        if (isItemApproved || isItemRejected) {
            options = [{ value: currentStatus, text: currentStatus, disabled: true }];
        } else {
            options = [
                { value: 'under_review', text: 'Bajo Revisión', disabled: true },
                { value: 'reviewed', text: 'Revisado', disabled: false },
                { value: 'rejected', text: 'Rechazado', disabled: false }
            ];
        }
    } else if (isApprover) { // Es aprobador (puede ser revisor también)
        options = [
            { value: 'under_review', text: 'Bajo Revisión', disabled: false },
            { value: 'reviewed', text: 'Revisado', disabled: false },
            { value: 'approved', text: 'Aprobado', disabled: false },
            { value: 'rejected', text: 'Rechazado', disabled: false }
        ];
        if (isItemApproved) {
            options[2].disabled = true;
            options[3].disabled = false; // El aprobador siempre puede rechazar un aprobado
        }
        if(isItemRejected){
            options[3].disabled = true;
        }
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
        message += `${statesToApprove.join(' y ')} para este período. Esta acción no se puede deshacer.`;

        Swal.fire({
            title: '¿Está seguro?',
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
            text: 'Solo quedan actividades en estado "Rechazado" en este período. El empleado debe corregirlas antes de poder aprobar.',
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
            title: '¿Está seguro?',
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
            text: 'Solo quedan actividades en estado "Rechazado" en este período. El empleado debe corregirlas antes de que puedan ser revisadas.',
            icon: 'error',
            confirmButtonText: 'Entendido'
        });
    } else {
        showNotification('No hay actividades bajo revisión para revisar en este período.', 'info');
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
            await loadMonthData();
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

        workLogsData = data.workLogsData;
        fortnightlyConfig = data.fortnightlyConfig;
        loadChartAssignments = data.loadChartAssignments;
        canSeeAmounts = data.canSeeAmounts;
        userPermissions = data.userPermissions;

        updateTableStructure(data.monthlyDays, data.employees);

        updatePeriodInfo();

        if (currentView === 'quincena1') {
            showQuincena(1);
        } else if (currentView === 'quincena2') {
            showQuincena(2);
        } else {
            showFullMonth();
        }

        hideLoadingState();
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

// CORRECCIÓN: Actualización de la función para calcular y renderizar totales
function calculateAndRenderTotals() {
    const tableBody = document.getElementById('approval-table-body');
    if (!tableBody || !workLogsData || workLogsData.length === 0) {
        return;
    }

    const employeeRows = document.querySelectorAll('.employee-row');
    const nonWorkingActivityTypes = ['N', 'D', 'VAC', 'PE', 'A', 'M'];

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
        let totalUnaccountedDays = 0; // Para PE, A, M

        const dailyActivitiesMap = new Map(employeeData.daily_activities.map(activity => [activity.date, activity]));

        const allDayCells = Array.from(employeeRow.querySelectorAll('.data-cell')).filter(cell => cell.getAttribute('data-date'));
        const visibleDayCells = allDayCells.filter(cell => cell.style.display !== 'none');

        const allWorkingDays = Array.from(document.querySelectorAll('.day-header'))
            .filter(header => header.getAttribute('data-quincena-1') === 'true' ||
                header.getAttribute('data-quincena-2') === 'true')
            .length;

        visibleDayCells.forEach(cell => {
            const dateAttr = cell.getAttribute('data-date');
            const dailyActivity = dailyActivitiesMap.get(dateAttr);

            if (dailyActivity) {
                const activityType = dailyActivity.activity_type ? dailyActivity.activity_type.toUpperCase() : 'N';

                // Contar días laborables (no son los no laborables)
                if (!nonWorkingActivityTypes.includes(activityType)) {
                    totalDays++;
                }

                if (activityType === 'VAC') {
                    totalVacations++;
                }
                if (activityType === 'D') {
                    totalBreaks++;
                }
                // Contar las nuevas actividades que no cuentan para la utilización
                if (['PE', 'A', 'M'].includes(activityType)) {
                     totalUnaccountedDays++;
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
        if (breaksCell) breaksCell.textContent = totalBreaks + totalUnaccountedDays; // Sumar los nuevos días no laborables

        const utilCell = employeeRow.querySelector('.utilization-value');
        if (utilCell) {
            const utilizationRate = allWorkingDays > 0 ? (totalDays / allWorkingDays) * 100 : 0;
            // CORRECCIÓN: Redondear a un número entero
            utilCell.textContent = `${utilizationRate.toFixed(0)}%`;

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
// Fin de la corrección
// ... (el resto del código sigue igual) ...
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

                    statusIndicator.classList.remove('approved-border', 'reviewed-border', 'rejected-border', 'under-review-border', 'no-activity-border');

                    if (activityType.toUpperCase() === 'N' || activityType.toUpperCase() === 'D' || activityType.toUpperCase() === 'VAC') {
                        statusIndicator.classList.add('no-activity-border');
                    } else {
                        const dayStatus = getDailyStatusIndicator(dailyActivity);
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
                    statusIndicator.className = 'status-indicator status-n no-activity-border';
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

function getDailyStatusIndicator(dailyActivity) {
    let hasRejected = false;
    let hasUnderReview = false;
    let hasReviewed = false;
    let hasApproved = false;
    let totalItems = 0;
    let approvedItems = 0;
    let reviewedItems = 0;

    const activityStatus = dailyActivity.activity_status ? dailyActivity.activity_status.toLowerCase() : 'under_review';

    if (dailyActivity.activity_type && dailyActivity.activity_type !== 'N' && dailyActivity.activity_type !== 'n') {
        totalItems++;
        switch (activityStatus) {
            case 'rejected':
                hasRejected = true;
                break;
            case 'under_review':
                hasUnderReview = true;
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

    const itemTypes = ['food_bonuses', 'field_bonuses', 'services_list'];
    itemTypes.forEach(type => {
        if (dailyActivity[type] && dailyActivity[type].length > 0) {
            dailyActivity[type].forEach(item => {
                totalItems++;
                const itemStatus = item.status ? item.status.toLowerCase() : 'under_review';
                switch (itemStatus) {
                    case 'rejected':
                        hasRejected = true;
                        break;
                    case 'under_review':
                        hasUnderReview = true;
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

    if (hasReviewed || (reviewedItems + approvedItems === totalItems && reviewedItems > 0)) {
        return 'reviewed';
    }

    return 'under_review';
}

function updateDayStatusForActivities(dailyActivities) {
    return dailyActivities.map(dailyActivity => {
        dailyActivity.day_status = getDailyStatusIndicator(dailyActivity);
        return dailyActivity;
    });
}
