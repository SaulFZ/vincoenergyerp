/**
 * ⚠️ FUNCIÓN CLAVE: Inicializa todos los scripts y eventos del calendario
 * después de que el HTML parcial ha sido cargado en el modal.
 * @param {string | null} employeeId - El ID del empleado actual (null si es vista principal).
 */
function initializeModalCalendarScripts(employeeId) {

    // --- MANEJO DE LA CARGA DE DATOS SEGURO ---
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

    // --- DEFINICIÓN DE CONSTANTES ---
    const modal = document.getElementById('activityModal');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelBtn = document.getElementById('cancel-activity');
    const saveBtn = document.getElementById('save-activity');
    const loadingSpinner = saveBtn?.querySelector('.loading-spinner');
    const serviceTabBtn = document.getElementById('service-tab-btn');
    const vacationDaysElement = document.querySelector('p[data-balance-type="vacation"]');
    const restDaysElement = document.querySelector('p[data-balance-type="rest"]');
    const conditionalFields = document.getElementById('conditional-fields');

    const travelDestinationField = document.getElementById('travel-destination-field');
    const travelDestinationInput = document.getElementById('travel-destination');
    const travelReasonField = document.getElementById('travel-reason-field');
    const travelReasonInput = document.getElementById('travel-reason');
    const serviceRealDateInput = document.getElementById('service-real-date');

    const requiresBaseDesc = document.getElementById('requires_base_description')?.value === '1';
    const baseDescGroup = document.getElementById('base-activity-description-group');
    const baseDescSelect = document.getElementById('base-activity-description');

    // 🚔 NUEVO: Elementos de Guardia
    const isGuardia = document.getElementById('is_guardia_user')?.value === '1';
    const normalActivityGroup = document.getElementById('activity-type-group');
    const guardiaActivityGroups = document.getElementById('guardia-activity-groups');
    const matutinaSelect = document.getElementById('activity-matutina');
    const vespertinaSelect = document.getElementById('activity-vespertina');

    const guardiaBonusGroup = document.getElementById('guardia-bonus-group');
    const guardiaBonusInput = document.getElementById('guardia-field-bonus');
    const guardiaBonusHeader = document.getElementById('guardia-bonus-header');
    const guardiaBonusOptionsEl = document.getElementById('guardia-bonus-options');

    const guardiaBonusQuantityContainer = document.getElementById('guardia-bonus-quantity-container');
    const guardiaBonusQuantityInput = document.getElementById('guardia-bonus-quantity');
    let currentGuardiaBonusAmount = 0;
    let currentGuardiaBonusCurrency = 'MXN';

    // 📦 NUEVO: Suministro y Continuación
    const isSuministro = document.getElementById('is_suministro_user')?.value === '1';
    const continuationField = document.getElementById('continuation-field');
    const isContinuationCheckbox = document.getElementById('is-continuation');
    const contractNumberField = document.getElementById('contract-number-field');
    const contractNumberInput = document.getElementById('contract-number');
    const travelServiceTypeField = document.getElementById('travel-service-type-field');
    const travelServiceTypeInput = document.getElementById('travel-service-type');

    // 🆕 NUEVO: Elementos de Comisionado
    const commissionedActivityTypeField = document.getElementById('commissioned-activity-type-field');
    const commissionedActivityTypeSelect = document.getElementById('commissioned-activity-type');

    if (isGuardia) {
        if (normalActivityGroup) normalActivityGroup.style.display = 'none';
        if (guardiaActivityGroups) guardiaActivityGroups.style.display = 'block';
        if (serviceTabBtn) serviceTabBtn.style.display = 'none';
    }

    let currentSelectedDate = null;
    let monthlyActivities = {};
    let currentActivity = null;
    let currentPayrollDates = {};
    let vacationDaysAvailable = parseInt(vacationDaysElement?.textContent) || 0;
    let currentEmployeeId = employeeId;

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

    // --- LÓGICA DE ACTUALIZACIÓN DINÁMICA DEL SELECT DE SERVICIOS (VIAJE) ---
    function updateTravelServiceOptions(isContinuation) {
        if (!travelServiceTypeInput) return;

        // Guardamos el valor actual por si podemos conservarlo
        const currentValue = travelServiceTypeInput.value;
        travelServiceTypeInput.innerHTML = '<option value="">Seleccionar tipo de servicio...</option>';

        const baseOptions = ['Entrega', 'Documentación', 'Soporte Técnico', 'Traslado (Solo Viaje)'];

        baseOptions.forEach(opt => {
            // Opción base normal
            const optionEl = document.createElement('option');
            optionEl.value = opt;
            optionEl.textContent = opt;
            travelServiceTypeInput.appendChild(optionEl);

            // Si es continuación, agregamos también las opciones "Continuación de..."
            if (isContinuation) {
                const contOptionEl = document.createElement('option');
                contOptionEl.value = `Continuación de ${opt}`;
                contOptionEl.textContent = `Continuación de ${opt}`;
                travelServiceTypeInput.appendChild(contOptionEl);
            }
        });

        // Intentamos restaurar el valor si aún existe en las nuevas opciones
        if (currentValue) {
            travelServiceTypeInput.value = currentValue;
        }
    }

    // EVENTO DEL CHECKBOX DE CONTINUACIÓN DE VIAJE
    if (isContinuationCheckbox) {
        isContinuationCheckbox.addEventListener('change', function() {
            updateTravelServiceOptions(this.checked);

            if (this.checked) {
                travelDestinationInput.value = this.dataset.prevDest || '';
                travelReasonInput.value = this.dataset.prevReason || '';
                // MODIFICACIÓN: Ya no se bloquean para permitir edición/adición de información
                travelDestinationInput.readOnly = false;
                travelReasonInput.readOnly = false;
                travelDestinationInput.style.backgroundColor = '';
                travelReasonInput.style.backgroundColor = '';

                if (isSuministro) {
                    contractNumberInput.value = this.dataset.prevContract || '';
                    contractNumberInput.disabled = true; // <-- Select bloqueado
                    contractNumberInput.style.backgroundColor = '#eef2f5';

                    // Aseguramos que el selector de servicio ESTÉ HABILITADO para que puedan cambiarlo
                    travelServiceTypeInput.disabled = false;

                    // Preseleccionamos "Continuación de..." por defecto
                    let prevService = this.dataset.prevService || '';
                    if (prevService) {
                        // Si ya tiene el prefijo, no lo duplicamos
                        if (!prevService.startsWith('Continuación de')) {
                            travelServiceTypeInput.value = `Continuación de ${prevService}`;
                        } else {
                            travelServiceTypeInput.value = prevService;
                        }
                    }
                }
            } else {
                travelDestinationInput.readOnly = false;
                travelReasonInput.readOnly = false;
                travelDestinationInput.style.backgroundColor = '';
                travelReasonInput.style.backgroundColor = '';

                if (isSuministro) {
                    contractNumberInput.disabled = false; // <-- Select desbloqueado
                    contractNumberInput.style.backgroundColor = '';
                    travelServiceTypeInput.disabled = false;
                    travelServiceTypeInput.value = '';
                }

                if (!currentActivity || currentActivity.activity_type !== 'V') {
                     travelDestinationInput.value = '';
                     travelReasonInput.value = '';
                     if (isSuministro) {
                         contractNumberInput.value = '';
                     }
                }
            }
        });
    }

  // --- LÓGICA DE AUTOCOMPLETADO DE POZOS ---
    const wellNameInput = document.getElementById('well-name');
    const wellSearchResults = document.getElementById('well-search-results');
    let wellSearchTimeout;

    if (wellNameInput && wellSearchResults) {
        wellNameInput.addEventListener('input', function() {
            this.dataset.valid = "false";

            clearTimeout(wellSearchTimeout);
            const term = this.value.trim();

            if (term.length < 2) {
                wellSearchResults.style.display = 'none';
                return;
            }

            wellSearchTimeout = setTimeout(async () => {
                wellSearchResults.innerHTML = `
                    <li class="searching-indicator">
                        <i class="fas fa-spinner fa-spin"></i> Buscando...
                    </li>`;
                wellSearchResults.style.display = 'block';

                try {
                    const response = await fetch(`/rh/loadchart/search-wells?q=${encodeURIComponent(term)}`);
                    const wells = await response.json();

                    wellSearchResults.innerHTML = '';

                    if (wells.length > 0) {
                        wells.forEach(well => {
                            const li = document.createElement('li');

                            // --- NUEVO RESALTADO INTELIGENTE (MULTI-PALABRAS) ---
                            let highlightedName = well.name;

                            // Cambiamos guiones por espacios y separamos cada palabra tecleada
                            const keywords = term.replace(/-/g, ' ').split(/\s+/)
                                .filter(k => k.trim() !== '')
                                .map(kw => kw.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')); // Escapar caracteres especiales

                            if (keywords.length > 0) {
                                // Buscamos cualquiera de las palabras clave de un solo golpe
                                const regex = new RegExp(`(${keywords.join('|')})`, "gi");
                                highlightedName = well.name.replace(regex, "<strong>$1</strong>");
                            }
                            // --------------------------------------------------

                            li.innerHTML = `<i class="fas fa-oil-well"></i> <span>${highlightedName}</span>`;

                            li.addEventListener('click', function() {
                                wellNameInput.value = well.name;
                                wellNameInput.dataset.valid = "true";
                                wellSearchResults.style.display = 'none';
                            });
                            wellSearchResults.appendChild(li);
                        });
                        wellSearchResults.style.display = 'block';
                    } else {
                        wellSearchResults.innerHTML = '<li class="no-results"><i class="fas fa-search-minus"></i> No se encontraron pozos activos</li>';
                        wellSearchResults.style.display = 'block';
                    }
                } catch (error) {
                    console.error('Error buscando pozos:', error);
                    wellSearchResults.innerHTML = '<li class="no-results" style="color: red;"><i class="fas fa-exclamation-triangle"></i> Error de conexión</li>';
                }
            }, 300);
        });

        document.addEventListener('click', function(e) {
            if (!wellNameInput.contains(e.target) && !wellSearchResults.contains(e.target)) {
                wellSearchResults.style.display = 'none';
            }
        });

        wellNameInput.addEventListener('focus', function() {
            if (this.value.trim().length >= 2 && wellSearchResults.innerHTML !== '') {
                wellSearchResults.style.display = 'block';
            }
        });
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
        let html = `<div class="day-header-info">`;

        html += `<div class="tags-wrapper">`;

        html += `<div class="activity-tag" style="background-color: ${color};">${activity.activity_description || activity.activity_type}</div>`;

        if (activity.activity_type_vespertina && activity.activity_type_vespertina !== 'N') {
            const colorVesp = getActivityTagColor(activity.activity_type_vespertina);
            html += `<div class="activity-tag" style="background-color: ${colorVesp};">${activity.activity_description_vespertina || activity.activity_type_vespertina}</div>`;
        }

        html += `</div>`;
        html += `${statusIcon}</div>`;
        return html;
    }

    async function updateBalanceUI(vacationDays, totalRestDaysInMonth) {
        if (vacationDaysElement) {
            vacationDaysAvailable = parseInt(vacationDays);
            vacationDaysElement.textContent = `${vacationDaysAvailable} día(s)`;
        }
        if (restDaysElement) {
            restDaysElement.textContent = `${totalRestDaysInMonth} día(s)`;
        }
    }

    async function fetchBalances() {
        try {
            const monthSpan = document.querySelector('.month-navigation span');
            const currentMonth = monthSpan.getAttribute('data-month');
            const currentYear = monthSpan.getAttribute('data-year');

            const employeeParam = currentEmployeeId ? `?employee_id=${currentEmployeeId}&month=${currentMonth}&year=${currentYear}` : `?month=${currentMonth}&year=${currentYear}`;

            const response = await fetch(`/rh/loadchart/balances-data${employeeParam}`);
            const data = await response.json();
            if (data.success) {
                updateBalanceUI(data.vacationDays, data.totalRestDaysInMonth);
            }
        } catch (error) {
            console.error('Error fetching balances:', error);
        }
    }

    function handleBaseDescriptionChange() {
        if (!requiresBaseDesc || document.getElementById('activity-type').value !== 'B') return;

        const val = baseDescSelect.value;
        const foodBonusContainer = document.getElementById('food-bonus') ? document.getElementById('food-bonus').closest('.form-group') : null;
        const fieldBonusContainer = document.getElementById('field-bonus') ? document.getElementById('field-bonus').closest('.form-group') : null;
        const foodBonusSelect = document.getElementById('food-bonus');

        if (['Movimiento o eventos con gerencias', 'Mantenimiento a polvorin Vinco'].includes(val)) {
            if (foodBonusContainer) foodBonusContainer.style.display = 'block';
            if (fieldBonusContainer) fieldBonusContainer.style.display = 'block';
        } else if (['Paso de cable', 'Pruebas de presion para los ECP', 'Pintura y soldaduras en area de taller'].includes(val)) {
            if (foodBonusContainer) foodBonusContainer.style.display = 'none';
            if (fieldBonusContainer) fieldBonusContainer.style.display = 'block';
            if (foodBonusSelect && !statusesToBlockField.includes(getFieldStatus(currentActivity, 'food_bonus'))) {
                foodBonusSelect.selectedIndex = 0;
            }
        } else {
            if (foodBonusContainer) foodBonusContainer.style.display = 'none';
            if (fieldBonusContainer) fieldBonusContainer.style.display = 'none';
            if (foodBonusSelect && !statusesToBlockField.includes(getFieldStatus(currentActivity, 'food_bonus'))) {
                foodBonusSelect.selectedIndex = 0;
            }
            const fieldBonusSelect = document.getElementById('field-bonus');
            if (fieldBonusSelect && !statusesToBlockField.includes(getFieldStatus(currentActivity, 'field_bonus'))) {
                fieldBonusSelect.selectedIndex = 0;
            }
        }
    }

    if (baseDescSelect) {
        baseDescSelect.addEventListener('change', handleBaseDescriptionChange);
    }

    function handleGuardiaActivityChange() {
        if (!isGuardia) return;

        const matVal = matutinaSelect.value || 'N';
        const vespVal = vespertinaSelect.value || 'N';
        const vacationError = document.getElementById('vacation-balance-error');

        if ((matVal === 'VAC' || vespVal === 'VAC') && vacationDaysAvailable <= 0) {
            if (vacationError) vacationError.style.display = 'block';
        } else {
            if (vacationError) vacationError.style.display = 'none';
        }

        if (matVal === 'B' || vespVal === 'B') {
            if (guardiaBonusGroup) guardiaBonusGroup.style.display = 'block';
        } else {
            if (guardiaBonusGroup) guardiaBonusGroup.style.display = 'none';

            if (guardiaBonusInput) {
                guardiaBonusInput.value = '';
                const headerPlaceholder = document.querySelector('#guardia-bonus-header .placeholder');
                if (headerPlaceholder) headerPlaceholder.textContent = 'Seleccionar bono (Solo si aplica)';
                document.querySelectorAll('#guardia-bonus-options .bonus-option').forEach(opt => opt.classList.remove('selected'));

                if (guardiaBonusQuantityContainer) {
                    guardiaBonusQuantityContainer.style.display = 'none';
                    if (guardiaBonusQuantityInput) guardiaBonusQuantityInput.value = 1;
                    currentGuardiaBonusAmount = 0;
                }
            }
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
        const foodBonusContainer = foodBonusSelect ? foodBonusSelect.closest('.form-group') : null;
        const fieldBonusContainer = fieldBonusSelect ? fieldBonusSelect.closest('.form-group') : null;

        // 🚔 LÓGICA EXCLUSIVA PARA GUARDIAS
        if (isGuardia) {
            conditionalFields.style.display = 'none';
            travelDestinationField.style.display = 'none';
            travelReasonField.style.display = 'none';
            wellNameField.style.display = 'none';
            commissionedField.style.display = 'none';
            if (continuationField) continuationField.style.display = 'none';
            if (contractNumberField) contractNumberField.style.display = 'none';
            if (travelServiceTypeField) travelServiceTypeField.style.display = 'none';
            if (commissionedActivityTypeField) commissionedActivityTypeField.style.display = 'none';

            if (baseDescGroup) baseDescGroup.style.display = 'none';
            vacationError.style.display = 'none';

            const radioNo = document.getElementById('service-bonus-no');
            if (radioNo) radioNo.checked = true;

            handleGuardiaActivityChange();
            return;
        }

        if (activityType === 'VAC' && vacationDaysAvailable <= 0) {
            vacationError.style.display = 'block';
        } else {
            vacationError.style.display = 'none';
        }

        travelDestinationField.style.display = 'none';
        travelReasonField.style.display = 'none';
        wellNameField.style.display = 'none';
        commissionedField.style.display = 'none';
        if (continuationField) continuationField.style.display = 'none';
        if (contractNumberField) contractNumberField.style.display = 'none';
        if (travelServiceTypeField) travelServiceTypeField.style.display = 'none';
        if (isContinuationCheckbox) isContinuationCheckbox.checked = false;
        if (commissionedActivityTypeField) commissionedActivityTypeField.style.display = 'none';

        if (activityType === 'B' && requiresBaseDesc) {
            baseDescGroup.style.display = 'block';
            conditionalFields.style.display = 'block';
            handleBaseDescriptionChange();
            resetServiceForm();
            const radioNo = document.getElementById('service-bonus-no');
            const optionNo = document.querySelector('.service-bonus-option[data-value="no"]');
            const optionSi = document.querySelector('.service-bonus-option[data-value="si"]');

            if (radioNo && optionNo && optionSi) {
                radioNo.checked = true;
                optionNo.classList.add('selected');
                optionSi.classList.remove('selected');
                handleServiceBonusChange('no');
            }
        } else if (activityType === 'P') {
            if (baseDescGroup) baseDescGroup.style.display = 'none';
            wellNameField.style.display = 'block';
            conditionalFields.style.display = 'block';
            if (foodBonusContainer) foodBonusContainer.style.display = 'block';
            if (fieldBonusContainer) fieldBonusContainer.style.display = 'block';
            handleServiceBonusChange(hasServiceBonusSelect?.value);

        } else if (activityType === 'C') {
            if (baseDescGroup) baseDescGroup.style.display = 'none';
            commissionedField.style.display = 'block';
            commissionedActivityTypeField.style.display = 'block'; // 🆕 Mostrar el nuevo campo
            conditionalFields.style.display = 'block';
            if (foodBonusContainer) foodBonusContainer.style.display = 'none';
            if (fieldBonusContainer) fieldBonusContainer.style.display = 'block';
            if (foodBonusSelect) foodBonusSelect.selectedIndex = 0;
            wellNameInput.value = '';

            const radioNo = document.getElementById('service-bonus-no');
            const optionNo = document.querySelector('.service-bonus-option[data-value="no"]');
            const optionSi = document.querySelector('.service-bonus-option[data-value="si"]');

            if (radioNo && optionNo && optionSi) {
                radioNo.checked = true;
                optionNo.classList.add('selected');
                optionSi.classList.remove('selected');
                handleServiceBonusChange('no');
            }
            const serviceStatus = getFieldStatus(currentActivity, 'service');
            if (!statusesToBlockField.includes(serviceStatus)) {
                resetServiceForm();
            }

        } else if (activityType === 'V') {
            if (baseDescGroup) baseDescGroup.style.display = 'none';
            travelDestinationField.style.display = 'block';
            travelReasonField.style.display = 'block';

            if (isSuministro) {
                contractNumberField.style.display = 'block';
                travelServiceTypeField.style.display = 'block';
                updateTravelServiceOptions(false); // Opciones por defecto sin continuación
            }

            // Descubrir si el día de ayer fue viaje
            if (currentSelectedDate) {
                let yesterdayDate = new Date(currentSelectedDate + 'T00:00:00');
                yesterdayDate.setDate(yesterdayDate.getDate() - 1);
                let yesterdayStr = yesterdayDate.toISOString().split('T')[0];
                let prevActivity = monthlyActivities[yesterdayStr];

                if (prevActivity && prevActivity.activity_type === 'V' && continuationField) {
                    continuationField.style.display = 'block';
                    isContinuationCheckbox.dataset.prevDest = prevActivity.travel_destination || '';
                    isContinuationCheckbox.dataset.prevReason = prevActivity.travel_reason || '';
                    isContinuationCheckbox.dataset.prevContract = prevActivity.contract_number || '';
                    isContinuationCheckbox.dataset.prevService = prevActivity.travel_service_type || '';
                } else if (continuationField) {
                    continuationField.style.display = 'none';
                    if (isContinuationCheckbox) isContinuationCheckbox.checked = false;
                }
            }

            conditionalFields.style.display = 'block';
            if (foodBonusContainer) foodBonusContainer.style.display = 'none';
            if (fieldBonusContainer) fieldBonusContainer.style.display = 'block';
            if (foodBonusSelect) foodBonusSelect.selectedIndex = 0;
            wellNameInput.value = '';
            commissionedSelect.selectedIndex = 0;

            const radioNo = document.getElementById('service-bonus-no');
            const optionNo = document.querySelector('.service-bonus-option[data-value="no"]');
            const optionSi = document.querySelector('.service-bonus-option[data-value="si"]');

            if (radioNo && optionNo && optionSi) {
                radioNo.checked = true;
                optionNo.classList.add('selected');
                optionSi.classList.remove('selected');
                handleServiceBonusChange('no');
            }
            const serviceStatus = getFieldStatus(currentActivity, 'service');
            if (!statusesToBlockField.includes(serviceStatus)) {
                resetServiceForm();
            }

        } else {
            if (baseDescGroup) baseDescGroup.style.display = 'none';
            if (baseDescSelect) baseDescSelect.value = '';
            wellNameInput.value = '';
            travelDestinationInput.value = '';
            travelReasonInput.value = '';
            commissionedSelect.selectedIndex = 0;
            if (contractNumberInput) contractNumberInput.value = '';
            if (commissionedActivityTypeSelect) commissionedActivityTypeSelect.selectedIndex = 0;

            const foodBonusStatus = getFieldStatus(currentActivity, 'food_bonus');
            const fieldBonusStatus = getFieldStatus(currentActivity, 'field_bonus');

            if (!statusesToBlockField.includes(foodBonusStatus)) {
                if (foodBonusSelect) foodBonusSelect.selectedIndex = 0;
            }
            if (!statusesToBlockField.includes(fieldBonusStatus)) {
                if (fieldBonusSelect) fieldBonusSelect.selectedIndex = 0;
            }

            conditionalFields.style.display = 'none';
            if (foodBonusContainer) foodBonusContainer.style.display = 'block';
            if (fieldBonusContainer) fieldBonusContainer.style.display = 'block';

            const serviceStatus = getFieldStatus(currentActivity, 'service');
            if (!statusesToBlockField.includes(serviceStatus)) {
                const radioNo = document.getElementById('service-bonus-no');
                const optionNo = document.querySelector('.service-bonus-option[data-value="no"]');
                const optionSi = document.querySelector('.service-bonus-option[data-value="si"]');

                if (radioNo && optionNo && optionSi) {
                    radioNo.checked = true;
                    optionNo.classList.add('selected');
                    optionSi.classList.remove('selected');
                    handleServiceBonusChange('no');
                }
                resetServiceForm();
            }
        }
    }

    function handleServiceBonusChange(hasServiceBonus) {
        if (isGuardia) return;

        const serviceTab = document.getElementById('service-tab');
        const activityTabBtn = document.querySelector('.tab-btn[data-tab="activity"]');
        const isActivityP = document.getElementById('activity-type').value === 'P';

        if (hasServiceBonus === 'si' && isActivityP) {
            serviceTabBtn.style.display = 'block';
            if (serviceRealDateInput.value === '' && currentSelectedDate) {
                serviceRealDateInput.value = currentSelectedDate;
            }
        } else {
            serviceTabBtn.style.display = 'none';
            if (serviceTab.classList.contains('active')) {
                activityTabBtn.click();
            }
            if (hasServiceBonus === 'no' || !isActivityP) {
                const radioNo = document.getElementById('service-bonus-no');
                if (radioNo) radioNo.checked = true;
                const optionNo = document.querySelector('.service-bonus-option[data-value="no"]');
                if (optionNo) optionNo.classList.add('selected');
                const optionSi = document.querySelector('.service-bonus-option[data-value="si"]');
                if (optionSi) optionSi.classList.remove('selected');
            }
            resetServiceForm();
        }
    }

    function showRejectionMessages(activity) {
        clearRejectionMessages();
        if (!activity) return;

        if (activity.rejection_reason && activity.activity_status && activity.activity_status.toLowerCase() === 'rejected') {
            if (isGuardia) {
                showRejectionMessage('activity-matutina-select', activity.rejection_reason);
            } else {
                showRejectionMessage('activity-type-select', activity.rejection_reason);
            }
        }

        if (activity.services_list && activity.services_list.length > 0) {
            activity.services_list.forEach(service => {
                if (service.status && service.status.toLowerCase() === 'rejected' && service.rejection_reason) {
                    showRejectionMessage('work-type-options', service.rejection_reason, true);
                }
            });
        }

        if (activity.food_bonuses && activity.food_bonuses.length > 0) {
            activity.food_bonuses.forEach(bonus => {
                if (bonus.status && bonus.status.toLowerCase() === 'rejected' && bonus.rejection_reason) {
                    showRejectionMessage('food-bonus', bonus.rejection_reason);
                }
            });
        }

        if (activity.field_bonuses && activity.field_bonuses.length > 0) {
            activity.field_bonuses.forEach(bonus => {
                if (bonus.status && bonus.status.toLowerCase() === 'rejected' && bonus.rejection_reason) {
                    if (isGuardia) {
                        showRejectionMessage('guardia-bonus-custom-select', bonus.rejection_reason);
                    } else {
                        showRejectionMessage('field-bonus', bonus.rejection_reason);
                    }
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
        const activityElements = [
            document.getElementById('activity-type-select'), ...document.querySelectorAll('.activity-option'),
            document.getElementById('commissioned-select'), document.getElementById('well-name'),
            document.getElementById('travel-destination'), document.getElementById('travel-reason'),
            ...document.querySelectorAll('.service-bonus-option'),
            document.getElementById('food-bonus'), document.getElementById('field-bonus'),
            document.getElementById('base-activity-description'),
            document.getElementById('activity-matutina-select'), document.getElementById('activity-vespertina-select'),
            document.getElementById('guardia-bonus-custom-select'), ...document.querySelectorAll('.bonus-option'),
            document.getElementById('guardia-bonus-quantity'),
            // Nuevos
            document.getElementById('contract-number'), document.getElementById('travel-service-type'),
            document.getElementById('is-continuation'),
            document.getElementById('commissioned-activity-type') // 🆕 Nuevo campo
        ];
        const serviceElements = [
            ...document.querySelectorAll('.work-type-option'),
            document.getElementById('service-type'), document.getElementById('service-performed'),
            document.getElementById('service'), document.getElementById('payroll-period'),
            document.getElementById('service-amount'),
            serviceRealDateInput
        ];

        [...activityElements, ...serviceElements].forEach(element => {
            if (element) {
                if (element.classList.contains('custom-select')) {
                    element.classList.remove('locked');
                } else if (element.classList.contains('work-type-option') || element.classList.contains('activity-option') || element.classList.contains('service-bonus-option') || element.classList.contains('bonus-option')) {
                    element.style.pointerEvents = '';
                    element.style.opacity = '';
                } else if (element.tagName === 'SELECT' || element.tagName === 'INPUT') {
                    if (element.id !== 'is-continuation') { // Excepción para checkbox
                        element.disabled = false;
                        element.readOnly = false;
                        element.style.backgroundColor = '';
                        element.style.color = '';
                        element.style.cursor = '';
                    } else {
                        element.disabled = false;
                    }
                }
                const parentGroup = element.closest('.form-group');
                if (parentGroup) {
                    const lockIndicator = parentGroup.querySelector('.lock-indicator');
                    if (lockIndicator) lockIndicator.remove();
                }
            }
        });

        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar';
        saveBtn.style.opacity = '1';
        saveBtn.style.cursor = 'pointer';
        if (loadingSpinner) loadingSpinner.style.display = 'none';
    }

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
        const isOption = element.classList.contains('work-type-option') || element.classList.contains('activity-option') || element.classList.contains('service-bonus-option') || element.classList.contains('bonus-option');
        const lowerCaseStatus = status ? status.toLowerCase() : '';

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

            if (element.id !== 'guardia-bonus-quantity' && element.id !== 'is-continuation') {
                parentGroup.appendChild(lockIndicator);
            }
        }

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
        const isDayCompletelyBlocked = statusesToBlockAll.includes(dayStatus);

        if (isDayCompletelyBlocked) {
            const allFormElements = document.querySelectorAll('#activity-tab select, #activity-tab .custom-select, #activity-tab input:not([type="radio"]), #service-tab select, #service-tab input');
            document.querySelectorAll('.work-type-option, .activity-option, .service-bonus-option, .bonus-option').forEach(el => toggleFieldLock(el, true, dayStatus));
            allFormElements.forEach(el => {
                if (el.id !== 'activity-date' && el.id !== 'service-amount') {
                    toggleFieldLock(el, true, dayStatus);
                }
            });
            saveBtn.disabled = true;
            showRejectionMessages(activity);
            return;
        }

        const activityStatus = getFieldStatus(activity, 'activity');
        const isActivityLocked = statusesToBlockField.includes(activityStatus);

        if (isGuardia) {
            const matContainer = document.getElementById('activity-matutina-select');
            const vespContainer = document.getElementById('activity-vespertina-select');
            const matOptions = document.querySelectorAll('#activity-matutina-options .activity-option');
            const vespOptions = document.querySelectorAll('#activity-vespertina-options .activity-option');

            toggleFieldLock(matContainer, isActivityLocked, activityStatus);
            matOptions.forEach(opt => toggleFieldLock(opt, isActivityLocked, activityStatus));

            toggleFieldLock(vespContainer, isActivityLocked, activityStatus);
            vespOptions.forEach(opt => toggleFieldLock(opt, isActivityLocked, activityStatus));

            const fieldBonusStatus = getFieldStatus(activity, 'field_bonus');
            const isFieldBonusLocked = statusesToBlockField.includes(fieldBonusStatus);
            const guardiaBonusCustomSelect = document.getElementById('guardia-bonus-custom-select');
            const guardiaBonusOpts = document.querySelectorAll('#guardia-bonus-options .bonus-option');

            toggleFieldLock(guardiaBonusCustomSelect, isFieldBonusLocked, fieldBonusStatus);
            guardiaBonusOpts.forEach(opt => toggleFieldLock(opt, isFieldBonusLocked, fieldBonusStatus));
            if (guardiaBonusQuantityInput) toggleFieldLock(guardiaBonusQuantityInput, isFieldBonusLocked, fieldBonusStatus);

        } else {
            const activityTypeSelectContainer = document.getElementById('activity-type-select');
            const commissionedSelect = document.getElementById('commissioned-select');
            const wellNameInput = document.getElementById('well-name');
            const travelDestinationInput = document.getElementById('travel-destination');
            const travelReasonInput = document.getElementById('travel-reason');
            const activityTypeOptions = document.querySelectorAll('.activity-option');
            const commissionedActivityTypeSelectEl = document.getElementById('commissioned-activity-type'); // 🆕

            toggleFieldLock(activityTypeSelectContainer, isActivityLocked, activityStatus);
            activityTypeOptions.forEach(option => toggleFieldLock(option, isActivityLocked, activityStatus));
            toggleFieldLock(commissionedSelect, isActivityLocked, activityStatus);
            toggleFieldLock(wellNameInput, isActivityLocked, activityStatus);
            toggleFieldLock(travelDestinationInput, isActivityLocked, activityStatus);
            toggleFieldLock(travelReasonInput, isActivityLocked, activityStatus);
            if (commissionedActivityTypeSelectEl) {
                toggleFieldLock(commissionedActivityTypeSelectEl, isActivityLocked, activityStatus);
            }

            if (isSuministro) {
                toggleFieldLock(document.getElementById('contract-number'), isActivityLocked, activityStatus);
                toggleFieldLock(document.getElementById('travel-service-type'), isActivityLocked, activityStatus);
                toggleFieldLock(document.getElementById('is-continuation'), isActivityLocked, activityStatus);
            }

            if (baseDescSelect) {
                toggleFieldLock(baseDescSelect, isActivityLocked, activityStatus);
            }
        }

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

        const foodBonusStatus = getFieldStatus(activity, 'food_bonus');
        const isFoodBonusLocked = statusesToBlockField.includes(foodBonusStatus);
        const foodBonusSelect = document.getElementById('food-bonus');
        toggleFieldLock(foodBonusSelect, isFoodBonusLocked, foodBonusStatus);

        const fieldBonusStatus = getFieldStatus(activity, 'field_bonus');
        const isFieldBonusLocked = statusesToBlockField.includes(fieldBonusStatus);
        const fieldBonusSelect = document.getElementById('field-bonus');
        toggleFieldLock(fieldBonusSelect, isFieldBonusLocked, fieldBonusStatus);

        const isServiceLocked = statusesToBlockField.includes(serviceStatus);
        const workTypeOptions = document.querySelectorAll('.work-type-option');
        const serviceTypeSelect = document.getElementById('service-type');
        const servicePerformedSelect = document.getElementById('service-performed');
        const serviceSelect = document.getElementById('service');
        const payrollPeriodSelect = document.getElementById('payroll-period');
        const serviceAmountInput = document.getElementById('service-amount');

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
        resetServiceForm();
        resetAdditionalForms();
        resetBonusFields();
        clearAllBlocksAndMessages();

        const activityTabBtn = document.querySelector('.tab-btn[data-tab="activity"]');
        const serviceTabButton = document.querySelector('.tab-btn[data-tab="service"]');
        const vacationError = document.getElementById('vacation-balance-error');

        // 🚔 POBLAMIENTO EXCLUSIVO PARA GUARDIAS
        if (isGuardia) {
            if (matutinaSelect) matutinaSelect.value = activity.activity_type || 'N';
            if (vespertinaSelect) vespertinaSelect.value = activity.activity_type_vespertina || 'N';

            const matOpt = document.querySelector(`#activity-matutina-options .activity-option[data-value="${activity.activity_type || 'N'}"]`);
            if (matOpt) {
                matOpt.classList.add('selected');
                document.querySelector('#activity-matutina-header .placeholder').textContent = matOpt.querySelector('.activity-label').textContent;
            }

            const vespOpt = document.querySelector(`#activity-vespertina-options .activity-option[data-value="${activity.activity_type_vespertina || 'N'}"]`);
            if (vespOpt) {
                vespOpt.classList.add('selected');
                document.querySelector('#activity-vespertina-header .placeholder').textContent = vespOpt.querySelector('.activity-label').textContent;
            }

            conditionalFields.style.display = 'none';
            handleGuardiaActivityChange();

            if (activity.field_bonuses && activity.field_bonuses.length > 0) {
                const fieldBonus = activity.field_bonuses[0];
                if (fieldBonus.bonus_identifier && guardiaBonusInput) {
                    guardiaBonusInput.value = fieldBonus.bonus_identifier;
                    const optBonus = document.querySelector(`#guardia-bonus-options .bonus-option[data-value="${fieldBonus.bonus_identifier}"]`);
                    if (optBonus) {
                        optBonus.classList.add('selected');
                        document.querySelector('#guardia-bonus-header .placeholder').textContent = optBonus.querySelector('.activity-label').textContent;

                        currentGuardiaBonusAmount = parseFloat(optBonus.getAttribute('data-amount')) || parseFloat(fieldBonus.base_amount) || 0;
                        currentGuardiaBonusCurrency = optBonus.getAttribute('data-currency') || fieldBonus.currency || 'MXN';

                        if (guardiaBonusQuantityContainer && guardiaBonusQuantityInput) {
                            guardiaBonusQuantityInput.value = fieldBonus.quantity || 1;
                            guardiaBonusQuantityContainer.style.display = 'block';
                        }
                    }
                }
            }

            setTimeout(() => {
                applyFieldLocks(activity);
            }, 200);

            return;
        }

        const isActivityP = activity.activity_type === 'P';

        if (activity.activity_type === 'VAC' && vacationDaysAvailable <= 0) {
            vacationError.style.display = 'block';
        } else {
            vacationError.style.display = 'none';
        }

        const hasService = activity.services_list && activity.services_list.length > 0;

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

        if (activity.activity_type === 'B' && requiresBaseDesc && activity.base_activity_description) {
            if (baseDescSelect) baseDescSelect.value = activity.base_activity_description;
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

        // 🆕 POBLAR TIPO DE ACTIVIDAD EN COMISIONADO
        if (activity.activity_type === 'C' && activity.commissioned_activity_type) {
            commissionedActivityTypeSelect.value = activity.commissioned_activity_type;
        }

        if (activity.activity_type === 'C' && activity.commissioned_to) {
            document.getElementById('commissioned-field').style.display = 'block';
            document.getElementById('commissioned-select').value = activity.commissioned_to;
        }

        if (activity.well_name) {
            document.getElementById('well-name').value = activity.well_name;
        }

        if (activity.activity_type === 'V') {
            travelDestinationField.style.display = 'block';
            travelReasonField.style.display = 'block';
            document.getElementById('travel-destination').value = activity.travel_destination || '';
            document.getElementById('travel-reason').value = activity.travel_reason || '';

            let isCont = activity.is_continuation || false;

            if (isContinuationCheckbox && activity.is_continuation !== undefined) {
                 isContinuationCheckbox.checked = isCont;
                 if(isCont && continuationField) continuationField.style.display = 'block';
            }

            if (isSuministro) {
                contractNumberField.style.display = 'block';
                travelServiceTypeField.style.display = 'block';

                updateTravelServiceOptions(isCont); // Opciones normales o de continuación

                contractNumberInput.value = activity.contract_number || '';
                travelServiceTypeInput.value = activity.travel_service_type || '';
            }

            // Aplicar estilos si es continuación (MODIFICADO: Ya no se bloquean los inputs de viaje)
            if (isCont) {
                travelDestinationInput.readOnly = false; // Ya no se bloquea
                travelReasonInput.readOnly = false;      // Ya no se bloquea
                travelDestinationInput.style.backgroundColor = '';
                travelReasonInput.style.backgroundColor = '';
                if (isSuministro) {
                    contractNumberInput.disabled = true; // <-- Select bloqueado (se mantiene por ser de Suministro/Contrato)
                    contractNumberInput.style.backgroundColor = '#eef2f5';
                    // Mantenemos el select de servicio desbloqueado para poder cambiar de actividad
                    travelServiceTypeInput.disabled = false;
                }
            } else {
                travelDestinationInput.readOnly = false;
                travelReasonInput.readOnly = false;
                travelDestinationInput.style.backgroundColor = '';
                travelReasonInput.style.backgroundColor = '';
                if (isSuministro) {
                    contractNumberInput.disabled = false; // <-- Select desbloqueado
                    contractNumberInput.style.backgroundColor = '';
                    travelServiceTypeInput.disabled = false;
                }
            }
        }

        if (hasService) {
            populateServiceData(activity.services_list[0]);
        }
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

    function updateServicePerformedOptions(workType, serviceTypeFormatted, selectedPerformedId = null) {
        const servicePerformedSelect = document.getElementById('service-performed');
        const serviceSelect = document.getElementById('service');

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

    function populateServiceData(service) {
        let operationType = null;
        let fullServiceData = null;
        const serviceTypeSelect = document.getElementById('service-type');
        const servicePerformedSelect = document.getElementById('service-performed');
        const serviceSelect = document.getElementById('service');
        const serviceAmountGroup = document.getElementById('service-amount-group');

        document.getElementById('service-amount').value = '';
        serviceAmountGroup.style.display = 'none';

        serviceRealDateInput.value = service.service_real_date || currentSelectedDate;

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

            serviceTypeSelect.disabled = false;
            updateServiceTypes(workTypeValue);
            serviceTypeSelect.value = serviceTypeFormatted;

            servicePerformedSelect.disabled = false;
            updateServicePerformedOptions(workTypeValue, serviceTypeFormatted, servicePerformedFormatted, servicePerformedFormatted);

            serviceSelect.disabled = false;
            updateServiceOptions(workTypeValue, serviceTypeFormatted, servicePerformedFormatted, serviceIdentifier);
        }
    }


    function openModal(processedDate, displayDate) {
        if (processedDate && displayDate) {
            currentSelectedDate = processedDate;
            document.getElementById('activity-date').value = displayDate;
            const existingActivity = monthlyActivities[processedDate];

            currentActivity = null;
            resetActivityOptions();
            resetServiceForm();
            resetAdditionalForms();
            resetBonusFields();
            clearAllBlocksAndMessages();
            document.getElementById('vacation-balance-error').style.display = 'none';
            document.querySelector('.tab-btn[data-tab="activity"]').click();

            if (!isGuardia) {
                conditionalFields.style.display = 'none';
                serviceTabBtn.style.display = 'none';
            } else {
                conditionalFields.style.display = 'none';
                handleGuardiaActivityChange();
            }

            serviceRealDateInput.value = processedDate;

            if (existingActivity) {
                populateModalWithActivity(existingActivity);
            } else {
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
        resetServiceForm();
        resetAdditionalForms();
        resetBonusFields();
        clearAllBlocksAndMessages();
        currentSelectedDate = null;
        currentActivity = null;

        if (!isGuardia) {
            conditionalFields.style.display = 'none';
        }

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
        if (isGuardia) {
            if (matutinaSelect) matutinaSelect.value = '';
            if (vespertinaSelect) vespertinaSelect.value = '';

            document.querySelectorAll('#activity-matutina-options .activity-option').forEach(opt => opt.classList.remove('selected'));
            document.querySelectorAll('#activity-vespertina-options .activity-option').forEach(opt => opt.classList.remove('selected'));

            const matHeader = document.querySelector('#activity-matutina-header .placeholder');
            const vespHeader = document.querySelector('#activity-vespertina-header .placeholder');

            if (matHeader) matHeader.textContent = 'Seleccionar...';
            if (vespHeader) vespHeader.textContent = 'Seleccionar...';

            document.getElementById('activity-matutina-error').style.display = 'none';
            document.getElementById('activity-vespertina-error').style.display = 'none';
        } else {
            document.querySelectorAll('#activity-type-options .activity-option').forEach(opt => opt.classList.remove('selected'));
            document.getElementById('activity-type').value = '';
            document.querySelector('#activity-type-header .placeholder').textContent = 'Seleccionar actividad...';
        }
    }

function resetAdditionalForms() {
        document.getElementById('commissioned-field').style.display = 'none';
        document.getElementById('commissioned-select').selectedIndex = 0;
        if (commissionedActivityTypeSelect) {
            commissionedActivityTypeSelect.selectedIndex = 0;
            commissionedActivityTypeField.style.display = 'none';
        }
        document.getElementById('well-name-field').style.display = 'none';
        document.getElementById('well-name').value = '';

        if (!isGuardia) {
            conditionalFields.style.display = 'none';
        }

        travelDestinationField.style.display = 'none';
        travelDestinationInput.value = '';
        travelDestinationInput.readOnly = false;
        travelDestinationInput.style.backgroundColor = '';

        travelReasonField.style.display = 'none';
        travelReasonInput.value = '';
        travelReasonInput.readOnly = false;
        travelReasonInput.style.backgroundColor = '';

        if (continuationField) {
            continuationField.style.display = 'none';
            isContinuationCheckbox.checked = false;
        }

        if (contractNumberField) {
            contractNumberField.style.display = 'none';
            contractNumberInput.value = '';
            contractNumberInput.disabled = false; // <-- Restauramos el select para que no quede bloqueado en otros días
            contractNumberInput.style.backgroundColor = '';
        }

        if (travelServiceTypeField) {
            travelServiceTypeField.style.display = 'none';
            travelServiceTypeInput.value = '';
            travelServiceTypeInput.disabled = false;
        }

        if (baseDescGroup) {
            baseDescGroup.style.display = 'none';
            if (baseDescSelect) baseDescSelect.selectedIndex = 0;
        }
    }
    function resetBonusFields() {
        if (document.getElementById('food-bonus')) document.getElementById('food-bonus').selectedIndex = 0;
        if (document.getElementById('field-bonus')) document.getElementById('field-bonus').selectedIndex = 0;

        if (guardiaBonusInput) {
            guardiaBonusInput.value = '';
            const headerPlaceholder = document.querySelector('#guardia-bonus-header .placeholder');
            if (headerPlaceholder) headerPlaceholder.textContent = 'Seleccionar bono especial...';
            document.querySelectorAll('#guardia-bonus-options .bonus-option').forEach(opt => opt.classList.remove('selected'));

            if (guardiaBonusQuantityContainer) {
                guardiaBonusQuantityContainer.style.display = 'none';
                if (guardiaBonusQuantityInput) guardiaBonusQuantityInput.value = 1;
                currentGuardiaBonusAmount = 0;
            }
        }
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

    document.addEventListener('click', function (e) {
        const selectsToClose = [
            { header: 'activity-type-header', options: 'activity-type-options' },
            { header: 'activity-matutina-header', options: 'activity-matutina-options' },
            { header: 'activity-vespertina-header', options: 'activity-vespertina-options' },
            { header: 'guardia-bonus-header', options: 'guardia-bonus-options' }
        ];

        selectsToClose.forEach(item => {
            const h = document.getElementById(item.header);
            const o = document.getElementById(item.options);
            if (h && o && !h.contains(e.target) && !o.contains(e.target)) {
                h.classList.remove('open');
                o.classList.remove('open');
            }
        });
    });

    if (!isGuardia) {
        const activityOptions = document.querySelectorAll('#activity-type-options .activity-option');
        const activityTypeHeader = document.getElementById('activity-type-header');
        const activityTypeOptionsEl = document.getElementById('activity-type-options');
        const activityTypeSelect = document.getElementById('activity-type');

        activityTypeHeader.addEventListener('click', function (e) {
            e.stopPropagation();
            if (currentActivity && statusesToBlockAll.includes(currentActivity.day_status)) return;
            if (currentActivity && statusesToBlockField.includes(getFieldStatus(currentActivity, 'activity'))) return;

            this.classList.toggle('open');
            activityTypeOptionsEl.classList.toggle('open');
        });

        activityOptions.forEach(option => {
            option.addEventListener('click', function (e) {
                e.stopPropagation();
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

                if (document.getElementById('activity-type').value !== 'P') return;

                const value = this.getAttribute('data-value');
                const radio = this.querySelector('input[type="radio"]');
                serviceBonusOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                radio.checked = true;
                handleServiceBonusChange(value);
            });
        });
    }

    function setupGuardiaCustomSelect(prefix) {
        const header = document.getElementById(`activity-${prefix}-header`);
        const optionsEl = document.getElementById(`activity-${prefix}-options`);
        const realSelect = document.getElementById(`activity-${prefix}`);
        if (!header || !optionsEl) return;

        const options = optionsEl.querySelectorAll('.activity-option');

        header.addEventListener('click', function (e) {
            e.stopPropagation();
            if (currentActivity && statusesToBlockAll.includes(currentActivity.day_status)) return;
            if (currentActivity && statusesToBlockField.includes(getFieldStatus(currentActivity, 'activity'))) return;

            this.classList.toggle('open');
            optionsEl.classList.toggle('open');
        });

        options.forEach(opt => {
            opt.addEventListener('click', function (e) {
                e.stopPropagation();
                if (currentActivity && statusesToBlockAll.includes(currentActivity.day_status)) return;
                if (currentActivity && statusesToBlockField.includes(getFieldStatus(currentActivity, 'activity'))) return;

                options.forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                const val = this.getAttribute('data-value');
                realSelect.value = val;
                header.querySelector('.placeholder').textContent = this.querySelector('.activity-label').textContent;

                header.classList.remove('open');
                optionsEl.classList.remove('open');

                handleGuardiaActivityChange();
            });
        });
    }

    if (isGuardia) {
        setupGuardiaCustomSelect('matutina');
        setupGuardiaCustomSelect('vespertina');

        if (guardiaBonusHeader && guardiaBonusOptionsEl) {
            const bonusOptions = guardiaBonusOptionsEl.querySelectorAll('.bonus-option');

            guardiaBonusHeader.addEventListener('click', function (e) {
                e.stopPropagation();
                if (currentActivity && statusesToBlockAll.includes(currentActivity.day_status)) return;
                const bonusStatus = getFieldStatus(currentActivity, 'field_bonus');
                if (currentActivity && statusesToBlockField.includes(bonusStatus)) return;

                this.classList.toggle('open');
                guardiaBonusOptionsEl.classList.toggle('open');
            });

            bonusOptions.forEach(opt => {
                opt.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (currentActivity && statusesToBlockAll.includes(currentActivity.day_status)) return;
                    const bonusStatus = getFieldStatus(currentActivity, 'field_bonus');
                    if (currentActivity && statusesToBlockField.includes(bonusStatus)) return;

                    bonusOptions.forEach(o => o.classList.remove('selected'));
                    this.classList.add('selected');

                    const val = this.getAttribute('data-value');
                    guardiaBonusInput.value = val;

                    currentGuardiaBonusAmount = parseFloat(this.getAttribute('data-amount')) || 0;
                    currentGuardiaBonusCurrency = this.getAttribute('data-currency') || 'MXN';

                    const labelText = this.querySelector('.activity-label').textContent;
                    guardiaBonusHeader.querySelector('.placeholder').textContent = val ? labelText : 'Seleccionar bono especial...';

                    if (val && guardiaBonusQuantityContainer) {
                        guardiaBonusQuantityContainer.style.display = 'block';
                    } else if (!val && guardiaBonusQuantityContainer) {
                        guardiaBonusQuantityContainer.style.display = 'none';
                        guardiaBonusQuantityInput.value = 1;
                    }

                    guardiaBonusHeader.classList.remove('open');
                    guardiaBonusOptionsEl.classList.remove('open');
                });
            });
        }
    }

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
            const response = await fetch(`/rh/loadchart/monthly-activities?month=${month}&year=${year}${employeeParam}`);
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
            employee_id: currentEmployeeId,
            date: currentSelectedDate,
            displayed_month: document.querySelector('.month-navigation span').getAttribute('data-month'),
            displayed_year: document.querySelector('.month-navigation span').getAttribute('data-year'),
        };

        const activityStatus = getFieldStatus(currentActivity, 'activity');

        if (isGuardia) {
            if (!statusesToBlockField.includes(activityStatus)) {
                formData.activity_type = matutinaSelect.value || 'N';
                formData.activity_type_vespertina = vespertinaSelect.value || 'N';

                formData.field_bonus_identifier = guardiaBonusInput ? guardiaBonusInput.value || null : null;
                formData.guardia_bonus_quantity = guardiaBonusQuantityInput ? (parseInt(guardiaBonusQuantityInput.value) || 1) : 1;
                formData.has_service_bonus = 'no';

                if (formData.activity_type === 'VAC' || formData.activity_type_vespertina === 'VAC') {
                    if (vacationDaysAvailable <= 0) {
                        document.getElementById('vacation-balance-error').style.display = 'block';
                        isValid = false;
                        Swal.fire({
                            icon: 'warning',
                            title: 'Límite alcanzado',
                            text: `El límite de tus vacaciones ha sido alcanzado.`,
                            confirmButtonText: 'Aceptar'
                        });
                        return;
                    }
                }
            }
        } else {
            if (!statusesToBlockField.includes(activityStatus)) {
                const activityType = document.getElementById('activity-type').value;
                formData.activity_type = activityType || 'N';
                formData.commissioned_to = activityType === 'C' ? document.getElementById('commissioned-select').value : null;
                formData.commissioned_activity_type = activityType === 'C' ? commissionedActivityTypeSelect.value : null; // 🆕 Nuevo campo
                formData.well_name = activityType === 'P' ? document.getElementById('well-name').value : null;

                // ---> SECCIÓN DE VIAJE ACTUALIZADA
                formData.travel_destination = activityType === 'V' ? document.getElementById('travel-destination').value.trim() : null;
                formData.travel_reason = activityType === 'V' ? document.getElementById('travel-reason').value.trim() : null;
                formData.is_continuation = activityType === 'V' ? isContinuationCheckbox.checked : false;

                if (isSuministro && activityType === 'V') {
                    formData.contract_number = contractNumberInput.value.trim();
                    formData.travel_service_type = travelServiceTypeInput.value;

                    if (!formData.contract_number) {
                        document.getElementById('contract-number-error').style.display = 'block';
                        isValid = false;
                    }
                    if (!formData.travel_service_type) {
                        document.getElementById('travel-service-type-error').style.display = 'block';
                        isValid = false;
                    }
                } else {
                    formData.contract_number = null;
                    formData.travel_service_type = null;
                }
                // <--- FIN SECCIÓN ACTUALIZADA

                if (activityType === 'B' && requiresBaseDesc) {
                    formData.base_activity_description = baseDescSelect.value;
                    if (!formData.base_activity_description) {
                        document.getElementById('base-activity-description-error').style.display = 'block';
                        isValid = false;
                    }
                }

                const isActivityP = activityType === 'P';
                const hasServiceBonus = document.querySelector('input[name="has_service_bonus"]:checked')?.value;
                formData.has_service_bonus = isActivityP ? hasServiceBonus : 'no';

                if (activityType === 'VAC') {
                    let currentVacationDaysInMonth = 0;
                    document.querySelectorAll('.calendar td[data-date]').forEach(cell => {
                        const activity = monthlyActivities[cell.getAttribute('data-date')];
                        if (activity && activity.activity_type === 'VAC') {
                            currentVacationDaysInMonth++;
                        }
                    });

                    const isNewVacationDay = !(currentActivity && currentActivity.activity_type === 'VAC' && currentActivity.date === currentSelectedDate);

                    if (isNewVacationDay || (currentActivity && currentActivity.activity_type !== 'VAC')) {
                        if (!currentActivity || currentActivity.activity_type !== 'VAC') {
                            currentVacationDaysInMonth++;
                        }
                    }

                    const daysAvailable = vacationDaysAvailable;

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

                if (!activityType) {
                    document.getElementById('activity-type-error').style.display = 'block';
                    isValid = false;
                }
                if (activityType === 'C' && !formData.commissioned_to) {
                    document.getElementById('commissioned-error').style.display = 'block';
                    isValid = false;
                }
                // 🆕 Validación para tipo de actividad en comisionado
                if (activityType === 'C' && !formData.commissioned_activity_type) {
                    document.getElementById('commissioned-activity-type-error').style.display = 'block';
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

            const currentActivityType = document.getElementById('activity-type').value;

            if (['P', 'N', 'B'].includes(currentActivityType) || !currentActivityType) {
                const foodBonusStatus = getFieldStatus(currentActivity, 'food_bonus');
                if (!statusesToBlockField.includes(foodBonusStatus)) {
                    formData.food_bonus_number = document.getElementById('food-bonus').value || null;
                }
            }

            if (['P', 'N', 'C', 'V', 'B'].includes(currentActivityType) || !currentActivityType) {
                const fieldBonusStatus = getFieldStatus(currentActivity, 'field_bonus');
                if (!statusesToBlockField.includes(fieldBonusStatus)) {
                    formData.field_bonus_identifier = document.getElementById('field-bonus').value || null;
                }
            }

            const serviceStatus = getFieldStatus(currentActivity, 'service');
            const wantsService = document.querySelector('input[name="has_service_bonus"]:checked')?.value === 'si';
            const isActivityP = document.getElementById('activity-type').value === 'P';

            const realDate = serviceRealDateInput.value;

            if (wantsService && isActivityP && !statusesToBlockField.includes(serviceStatus)) {
                const workTypeSelected = document.querySelector('.work-type-option.selected');
                const serviceValue = document.getElementById('service').value;

                formData.service_identifier = serviceValue || null;
                formData.service_real_date = realDate;

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
                if (!formData.service_real_date) {
                    document.getElementById('service-real-date-error').style.display = 'block';
                    isValid = false;
                    document.querySelector('.tab-btn[data-tab="service"]').click();
                }
            } else if (wantsService && isActivityP && statusesToBlockField.includes(serviceStatus)) {
                formData.service_identifier = currentActivity?.services_list[0]?.service_identifier;
                formData.service_real_date = currentActivity?.services_list[0]?.service_real_date;
            } else {
                if (!statusesToBlockField.includes(serviceStatus)) {
                    formData.service_identifier = null;
                    formData.service_real_date = null;
                }
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

        const originalBtnHTML = '<i class="fas fa-save"></i> Guardar';
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        saveBtn.style.opacity = '0.7';
        saveBtn.style.cursor = 'wait';
        if (loadingSpinner) loadingSpinner.style.display = 'none';

        fetch('/rh/loadchart/save-activity', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
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
                    title: 'Error de Servidor',
                    text: 'Se produjo un error al procesar la solicitud. Revisa la consola o los logs.',
                    confirmButtonText: 'Aceptar'
                });
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalBtnHTML;
                saveBtn.style.opacity = '1';
                saveBtn.style.cursor = 'pointer';
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
            const response = await fetch(`/rh/loadchart/calendar-data?month=${month}&year=${year}`);
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
            let currentRow = ' <tbody><tr>';

            let firstDayOfMonthIndex = new Date(data.currentYear, data.currentMonth - 1, 1).getDay();
            firstDayOfMonthIndex = firstDayOfMonthIndex === 0 ? 6 : firstDayOfMonthIndex - 1;

            for (let i = 0; i < firstDayOfMonthIndex; i++) {
                currentRow += `<td class="other-month" data-date=""> </td>`;
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
                    currentRow += `<td class="other-month" data-date=""> </td>`;
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
            window.location.href = '/rh/loadchart/approval';
        });
    }

    const initialMonth = parseInt(monthSpan.getAttribute('data-month'));
    const initialYear = parseInt(monthSpan.getAttribute('data-year'));

    updateCalendar(initialMonth, initialYear);

    if (typeof flatpickr !== 'undefined' && serviceRealDateInput) {
        flatpickr(serviceRealDateInput, {
            dateFormat: "Y-m-d",
            disableMobile: true
        });
    } else {
        console.error("Flatpickr no está disponible o el elemento serviceRealDateInput no existe.");
    }
}

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
