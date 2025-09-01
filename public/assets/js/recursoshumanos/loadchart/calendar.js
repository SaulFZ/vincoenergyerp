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

    function openModal(date) {
        if (date) {
            document.getElementById('activity-date').value = date;
        }
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        resetActivityOptions();
        resetServiceForm();
        resetAdditionalForms(); // New function to reset the new form fields
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
            const value = this.getAttribute('data-value');
            activityOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            activityTypeSelect.value = value;
            placeholder.textContent = this.querySelector('.activity-label').textContent;
            activityTypeHeader.classList.remove('open');
            activityTypeOptions.classList.remove('open');

            // NEW LOGIC: Show/hide the "Comisionada" dropdown based on activity
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

    // New function to reset additional forms like "Comisionada"
    function resetAdditionalForms() {
        commissionedField.style.display = 'none';
        commissionedSelect.selectedIndex = 0;
    }

    // 3. Configuración de los selectores anidados
    const workTypeOptions = document.querySelectorAll('.work-type-option');
    workTypeOptions.forEach(option => {
        option.addEventListener('click', function () {
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
        if (serviceData[workType]) {
            const uniqueTypes = [...new Set(serviceData[workType].map(item => item.service_type))];
            uniqueTypes.forEach(type => {
                const option = document.createElement('option');
                option.value = type.toLowerCase().replace(/\s+/g, '_');
                option.textContent = type;
                serviceTypeSelect.appendChild(option);
            });
        }
        document.getElementById('service-performed').disabled = true;
        document.getElementById('service').disabled = true;
    }

    document.getElementById('service-type').addEventListener('change', function () {
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
        }
    });

    document.getElementById('service-performed').addEventListener('change', function () {
        const workType = document.querySelector('.work-type-option.selected')?.getAttribute('data-value');
        const serviceTypeId = document.getElementById('service-type').value;
        const performedId = this.value;
        if (workType && serviceTypeId && performedId) {
            const serviceSelect = document.getElementById('service');
            serviceSelect.disabled = false;
            serviceSelect.innerHTML = '<option value="">Seleccionar servicio...</option>';
            const services = serviceData[workType].filter(item => item.service_type.toLowerCase().replace(/\s+/g, '_') === serviceTypeId && item.service_performed.toLowerCase().replace(/\s+/g, '_') === performedId).map(item => ({
                id: item.identifier,
                name: item.service_description
            }));
            services.forEach(service => {
                const option = document.createElement('option');
                option.value = service.id;
                option.textContent = service.name;
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

    // 5. Configuración del botón guardar
    saveBtn.addEventListener('click', function () {
        let isValid = true;
        document.querySelectorAll('.error-message').forEach(el => {
            el.style.display = 'none';
        });

        if (document.getElementById('activity-tab').classList.contains('active')) {
            if (!activityTypeSelect.value) {
                document.getElementById('activity-type-error').style.display = 'block';
                isValid = false;
            }
            // NEW VALIDATION: Check the "Comisionada" dropdown if "Comisionado" is selected
            if (activityTypeSelect.value === 'C' && !commissionedSelect.value) {
                document.getElementById('commissioned-error').style.display = 'block';
                isValid = false;
            }

        } else {
            if (!document.querySelector('.work-type-option.selected')) {
                document.getElementById('work-type-error').style.display = 'block';
                isValid = false;
            }
            const serviceType = document.getElementById('service-type');
            if (!serviceType.value || serviceType.disabled) {
                document.getElementById('service-type-error').style.display = 'block';
                isValid = false;
            }
            const servicePerformed = document.getElementById('service-performed');
            if (!servicePerformed.value || servicePerformed.disabled) {
                document.getElementById('service-performed-error').style.display = 'block';
                isValid = false;
            }
            const service = document.getElementById('service');
            if (!service.value || service.disabled) {
                document.getElementById('service-error').style.display = 'block';
                isValid = false;
            }
        }

        if (!isValid) return;

        saveBtn.disabled = true;
        loadingSpinner.style.display = 'block';

        // Simular envío al servidor
        setTimeout(() => {
            saveBtn.disabled = false;
            loadingSpinner.style.display = 'none';
            alert('Registro guardado exitosamente!');
            closeModal();
        }, 1000);
    });

    // 6. Eventos del calendario para abrir el modal (AJAX - Se re-adjuntan después de cada carga)
    function attachDayClickEvents() {
        document.querySelectorAll('.calendar td').forEach(day => {
            day.addEventListener('click', function () {
                const dayNumber = this.querySelector('.day-number').textContent;
                const monthYearText = document.querySelector('.chart-header .month-navigation span').textContent.replace(/\s+/g, ' ').trim();
                const fullDate = `${dayNumber} de ${monthYearText}`;
                openModal(fullDate);
            });
        });
    }

    // Llamada inicial para adjuntar eventos al calendario de carga inicial
    attachDayClickEvents();

    // 7. Navegación por meses del calendario (Carga AJAX)
    const monthSpan = document.querySelector('.month-navigation span');
    const prevMonthBtn = document.getElementById('prev-month');
    const nextMonthBtn = document.getElementById('next-month');
    const calendarTableBody = document.querySelector('.calendar tbody');
    const chartHeader = document.querySelector('.chart-header h2');

    async function updateCalendar(month, year) {
        try {
            // Muestra un indicador de carga
            calendarTableBody.innerHTML = '<tr><td colspan="7" class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>';

            const response = await fetch(`/recursoshumanos/loadchart/calendar-data?month=${month}&year=${year}`);
            if (!response.ok) {
                throw new Error('No se pudo cargar los datos del calendario.');
            }

            const data = await response.json();

            // Actualiza el encabezado del calendario
            chartHeader.innerHTML = `<i class="fas fa-chart-bar"></i> Load Chart - ${data.monthName} ${data.currentYear}`;
            monthSpan.textContent = `${data.monthName} ${data.currentYear}`;
            monthSpan.setAttribute('data-month', data.currentMonth);
            monthSpan.setAttribute('data-year', data.currentYear);

            // Genera y actualiza la tabla del calendario
            let newTableHTML = '';
            let currentRow = '<tr>';
            data.calendarDays.forEach((day, index) => {
                const isCurrentMonth = day.current_month;
                const isToday = day.is_today;
                const isPayrollStart1 = day.is_payroll_start_1;
                const isPayrollEnd1 = day.is_payroll_end_1;
                const isPayrollStart2 = day.is_payroll_start_2;
                const isPayrollEnd2 = day.is_payroll_end_2;

                const cellClass = `${!isCurrentMonth ? 'other-month' : ''} ${isToday ? 'current-d' : ''}`;
                const payrollIcons = `
                    ${isPayrollStart1 ? '<i class="fas fa-flag payroll-icon payroll-start-1" title="Inicio Quincena 1"></i>' : ''}
                    ${isPayrollEnd1 ? '<i class="fas fa-flag payroll-icon payroll-end-1" title="Fin Quincena 1"></i>' : ''}
                    ${isPayrollStart2 ? '<i class="fas fa-flag payroll-icon payroll-start-2" title="Inicio Quincena 2"></i>' : ''}
                    ${isPayrollEnd2 ? '<i class="fas fa-flag payroll-icon payroll-end-2" title="Fin Quincena 2"></i>' : ''}
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
            newTableHTML += currentRow.slice(0, -4); // Elimina el '<tr>' final si no hay 7 días
            calendarTableBody.innerHTML = newTableHTML;

            // Re-adjunta los event listeners a los nuevos días del calendario
            attachDayClickEvents();

        } catch (error) {
            console.error('Error al cargar el calendario:', error);
            alert('No se pudo cargar el calendario. Por favor, intente de nuevo.');
        }
    }

    prevMonthBtn.addEventListener('click', function () {
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

    // 8. Botón de aprobación
    const approveBtn = document.getElementById('approve-loadchart');
    if (approveBtn) {
        approveBtn.addEventListener('click', function (e) {
            e.preventDefault();
            window.location.href = '/recursoshumanos/loadchart/approval';
        });
    }
});
