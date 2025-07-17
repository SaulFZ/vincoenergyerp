document.addEventListener('DOMContentLoaded', function() {
    // Modal functionality
    const modal = document.getElementById('activityModal');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelBtn = document.getElementById('cancel-activity');
    const saveBtn = document.getElementById('save-activity');
    const loadingSpinner = document.createElement('div');
    loadingSpinner.className = 'loading-spinner';
    saveBtn.appendChild(loadingSpinner);

    // Datos de ejemplo para tipos de servicio
    const serviceTypesData = {
        electrical: [
            { value: "elec1", label: "Reparación de cableado" },
            { value: "elec2", label: "Instalación eléctrica" },
            { value: "elec3", label: "Mantenimiento de paneles" }
        ],
        mechanical: [
            { value: "mech1", label: "Cambio de aceite" },
            { value: "mech2", label: "Reparación de motor" },
            { value: "mech3", label: "Alineación y balanceo" }
        ],
        maintenance: [
            { value: "maint1", label: "Mantenimiento preventivo" },
            { value: "maint2", label: "Mantenimiento correctivo" },
            { value: "maint3", label: "Limpieza general" }
        ],
        inspection: [
            { value: "insp1", label: "Inspección de seguridad" },
            { value: "insp2", label: "Inspección de calidad" },
            { value: "insp3", label: "Inspección rutinaria" }
        ]
    };

    // Función para abrir el modal
    function openModal(date) {
        if (date) {
            document.getElementById('activity-date').value = date;
        }
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    // Función para cerrar el modal
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        resetActivityOptions();
    }

    // Event listeners para cerrar modal
    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Activity type selection
    const activityOptions = document.querySelectorAll('.activity-option');
    const activityTypeSelect = document.getElementById('activity-type');
    const activityTypeHeader = document.getElementById('activity-type-header');
    const activityTypeOptions = document.getElementById('activity-type-options');

    activityTypeHeader.addEventListener('click', function() {
        this.classList.toggle('open');
        activityTypeOptions.classList.toggle('open');
    });

    activityOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove selection from all options
            activityOptions.forEach(opt => opt.classList.remove('selected'));

            // Add selection to clicked option
            this.classList.add('selected');

            // Update the hidden select value and header text
            const value = this.getAttribute('data-value');
            const label = this.querySelector('.activity-label').textContent;
            activityTypeSelect.value = value;
            activityTypeHeader.querySelector('.placeholder').textContent = label;

            // Close dropdown
            activityTypeHeader.classList.remove('open');
            activityTypeOptions.classList.remove('open');
        });
    });

    // Cerrar dropdown al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!activityTypeHeader.contains(e.target)) {
            activityTypeHeader.classList.remove('open');
            activityTypeOptions.classList.remove('open');
        }
    });

    function resetActivityOptions() {
        activityOptions.forEach(opt => opt.classList.remove('selected'));
        activityTypeSelect.value = '';
        activityTypeHeader.querySelector('.placeholder').textContent = 'Seleccionar actividad...';
    }

    // Manejo de tipos de servicio
    const serviceCategory = document.getElementById('service-category');
    const serviceType = document.getElementById('service-type');

    serviceCategory.addEventListener('change', function() {
        if (this.value) {
            serviceType.disabled = false;
            serviceType.innerHTML = '<option value="">Seleccionar tipo...</option>';

            // Agregar opciones basadas en la categoría seleccionada
            serviceTypesData[this.value].forEach(option => {
                const optElement = document.createElement('option');
                optElement.value = option.value;
                optElement.textContent = option.label;
                serviceType.appendChild(optElement);
            });
        } else {
            serviceType.disabled = true;
            serviceType.innerHTML = '<option value="">Primero seleccione una categoría</option>';
        }
    });

    // Tabs functionality
    document.querySelectorAll('.tab-btn').forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active tabs
            document.querySelector('.form-tabs .tab-btn.active').classList.remove('active');
            this.classList.add('active');

            // Update active content
            const tabId = this.getAttribute('data-tab');
            document.querySelector('.tab-content.active').classList.remove('active');
            document.getElementById(`${tabId}-tab`).classList.add('active');
        });
    });

    // Save activity functionality
    saveBtn.addEventListener('click', function() {
        // Validate form based on active tab
        if (document.getElementById('activity-tab').classList.contains('active')) {
            if (!activityTypeSelect.value) {
                alert('Por favor selecciona un tipo de actividad');
                return;
            }
        } else {
            if (!serviceCategory.value || !serviceType.value || serviceType.disabled) {
                alert('Por favor completa todos los campos del servicio');
                return;
            }
        }

        // Show loading state
        saveBtn.disabled = true;
        loadingSpinner.style.display = 'block';

        // Simulate server request
        setTimeout(() => {
            // Hide loading
            saveBtn.disabled = false;
            loadingSpinner.style.display = 'none';

            // Show success message
            alert('Registro guardado exitosamente!');

            // Close modal
            closeModal();
        }, 1000);
    });

    // Calendar day click - opens modal with selected date
    document.querySelectorAll('.calendar td:not(.other-month)').forEach(day => {
        day.addEventListener('click', function() {
            const dayNumber = this.querySelector('.day-number').textContent;
            document.getElementById('activity-date').value = `${dayNumber} de Junio, 2025`;
            openModal(`${dayNumber} de Junio, 2025`);
        });
    });

    // Month navigation
    document.getElementById('prev-month').addEventListener('click', () => {
        alert('Navegando al mes anterior');
    });

    document.getElementById('next-month').addEventListener('click', () => {
        alert('Navegando al próximo mes');
    });
    // Botón de aprobar loadchart
    const approveBtn = document.getElementById('approve-loadchart');
    if (approveBtn) {
        approveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = '/recursoshumanos/loadchart/approval';
        });
    }


    // Hacer la función openModal accesible globalmente
    window.openActivityModal = openModal;
});
