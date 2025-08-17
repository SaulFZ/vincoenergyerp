document.addEventListener('DOMContentLoaded', function() {
    // Obtener los datos de servicios desde la base de datos
    const serviceData = JSON.parse(document.getElementById('service-data').textContent);

    // Verificar estructura de datos
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

    // Funciones para abrir/cerrar modal
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
    }

    // Event listeners para cerrar modal
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

    activityTypeHeader.addEventListener('click', function() {
        this.classList.toggle('open');
        activityTypeOptions.classList.toggle('open');
    });

    document.addEventListener('click', function(e) {
        if (!activityTypeHeader.contains(e.target)) {
            activityTypeHeader.classList.remove('open');
            activityTypeOptions.classList.remove('open');
        }
    });

    activityOptions.forEach(option => {
        option.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            activityOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            activityTypeSelect.value = value;
            placeholder.textContent = this.querySelector('.activity-label').textContent;
            activityTypeHeader.classList.remove('open');
            activityTypeOptions.classList.remove('open');
        });
    });

    function resetActivityOptions() {
        activityOptions.forEach(opt => opt.classList.remove('selected'));
        activityTypeSelect.value = '';
        placeholder.textContent = 'Seleccionar actividad...';
    }

    // 3. Configuración de los selectores anidados
    const workTypeOptions = document.querySelectorAll('.work-type-option');

    workTypeOptions.forEach(option => {
        option.addEventListener('click', function() {
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
            const uniqueTypes = [...new Set(
                serviceData[workType].map(item => item.service_type)
            )];

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

    document.getElementById('service-type').addEventListener('change', function() {
        const workType = document.querySelector('.work-type-option.selected')?.getAttribute('data-value');
        const serviceTypeId = this.value;

        if (workType && serviceTypeId) {
            const servicePerformedSelect = document.getElementById('service-performed');
            servicePerformedSelect.disabled = false;
            servicePerformedSelect.innerHTML = '<option value="">Seleccionar servicio realizado...</option>';

            const uniquePerformed = [...new Set(
                serviceData[workType]
                    .filter(item =>
                        item.service_type.toLowerCase().replace(/\s+/g, '_') === serviceTypeId
                    )
                    .map(item => item.service_performed)
            )];

            uniquePerformed.forEach(performed => {
                const option = document.createElement('option');
                option.value = performed.toLowerCase().replace(/\s+/g, '_');
                option.textContent = performed;
                servicePerformedSelect.appendChild(option);
            });

            document.getElementById('service').disabled = true;
        }
    });

    document.getElementById('service-performed').addEventListener('change', function() {
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
        tab.addEventListener('click', function() {
            document.querySelector('.form-tabs .tab-btn.active').classList.remove('active');
            this.classList.add('active');

            const tabId = this.getAttribute('data-tab');
            document.querySelector('.tab-content.active').classList.remove('active');
            document.getElementById(`${tabId}-tab`).classList.add('active');
        });
    });

    // 5. Configuración del botón guardar
    saveBtn.addEventListener('click', function() {
        let isValid = true;

        document.querySelectorAll('.error-message').forEach(el => {
            el.style.display = 'none';
        });

        if (document.getElementById('activity-tab').classList.contains('active')) {
            if (!activityTypeSelect.value) {
                document.getElementById('activity-type-error').style.display = 'block';
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

    // 6. Eventos del calendario
    document.querySelectorAll('.calendar td:not(.other-month)').forEach(day => {
        day.addEventListener('click', function() {
            const dayNumber = this.querySelector('.day-number').textContent;
            openModal(`${dayNumber} de Junio, 2025`);
        });
    });

    document.getElementById('prev-month').addEventListener('click', () => {
        alert('Navegando al mes anterior');
    });

    document.getElementById('next-month').addEventListener('click', () => {
        alert('Navegando al próximo mes');
    });

    // 7. Botón de aprobación
    const approveBtn = document.getElementById('approve-loadchart');
    if (approveBtn) {
        approveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = '/recursoshumanos/loadchart/approval';
        });
    }

});
