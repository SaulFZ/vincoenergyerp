document.addEventListener('DOMContentLoaded', function() {
            // Modal functionality
            const modal = document.getElementById('activityModal');
            const closeModalBtn = document.querySelector('.close-modal');
            const cancelBtn = document.getElementById('cancel-activity');
            const saveBtn = document.getElementById('save-activity');
            const loadingSpinner = saveBtn.querySelector('.loading-spinner');

            // Datos de ejemplo para servicios
            const serviceData = {
                land: {
                    types: [
                        { id: 'exploration', name: 'Exploración' },
                        { id: 'drilling', name: 'Perforación' },
                        { id: 'completion', name: 'Terminación' },
                        { id: 'production', name: 'Producción' }
                    ],
                    performed: {
                        exploration: [
                            { id: 'seismic', name: 'Estudios Sísmicos' },
                            { id: 'geological', name: 'Estudios Geológicos' }
                        ],
                        drilling: [
                            { id: 'rotary', name: 'Perforación Rotatoria' },
                            { id: 'directional', name: 'Perforación Direccional' }
                        ],
                        completion: [
                            { id: 'casing', name: 'Instalación de Tuberías' },
                            { id: 'cementing', name: 'Cementación' }
                        ],
                        production: [
                            { id: 'pumping', name: 'Extracción por Bombeo' },
                            { id: 'stimulation', name: 'Estimulación de Pozos' }
                        ]
                    },
                    services: {
                        seismic: [
                            { id: '2d', name: 'Sísmica 2D' },
                            { id: '3d', name: 'Sísmica 3D' }
                        ],
                        geological: [
                            { id: 'core', name: 'Análisis de Núcleos' },
                            { id: 'sample', name: 'Muestreo Geológico' }
                        ],
                        rotary: [
                            { id: 'rotary_standard', name: 'Perforación Estándar' },
                            { id: 'rotary_advanced', name: 'Perforación Avanzada' }
                        ],
                        directional: [
                            { id: 'directional_standard', name: 'Direccional Estándar' },
                            { id: 'directional_horizontal', name: 'Perforación Horizontal' }
                        ],
                        casing: [
                            { id: 'casing_standard', name: 'Tubería Estándar' },
                            { id: 'casing_lined', name: 'Tubería Revestida' }
                        ],
                        cementing: [
                            { id: 'primary', name: 'Cementación Primaria' },
                            { id: 'secondary', name: 'Cementación Secundaria' }
                        ],
                        pumping: [
                            { id: 'beam', name: 'Bombeo Mecánico' },
                            { id: 'submersible', name: 'Bombeo Sumergible' }
                        ],
                        stimulation: [
                            { id: 'acidizing', name: 'Acidificación' },
                            { id: 'fracking', name: 'Fracturamiento Hidráulico' }
                        ]
                    }
                },
                marine: {
                    types: [
                        { id: 'offshore', name: 'Operaciones Offshore' },
                        { id: 'subsea', name: 'Operaciones Submarinas' },
                        { id: 'logistics', name: 'Logística Marina' }
                    ],
                    performed: {
                        offshore: [
                            { id: 'drilling_offshore', name: 'Perforación Offshore' },
                            { id: 'production_offshore', name: 'Producción Offshore' }
                        ],
                        subsea: [
                            { id: 'installations', name: 'Instalaciones Submarinas' },
                            { id: 'maintenance', name: 'Mantenimiento Submarino' }
                        ],
                        logistics: [
                            { id: 'transport', name: 'Transporte Marítimo' },
                            { id: 'support', name: 'Soporte Operativo' }
                        ]
                    },
                    services: {
                        drilling_offshore: [
                            { id: 'platform', name: 'Plataforma' },
                            { id: 'drillship', name: 'Buque Perforador' }
                        ],
                        production_offshore: [
                            { id: 'fpso', name: 'FPSO' },
                            { id: 'spar', name: 'Plataforma Spar' }
                        ],
                        installations: [
                            { id: 'pipelines', name: 'Instalación de Tuberías' },
                            { id: 'manifolds', name: 'Instalación de Colectores' }
                        ],
                        maintenance: [
                            { id: 'rov', name: 'Mantenimiento con ROV' },
                            { id: 'divers', name: 'Mantenimiento con Buzos' }
                        ],
                        transport: [
                            { id: 'supply', name: 'Buques de Abastecimiento' },
                            { id: 'cargo', name: 'Buques de Carga' }
                        ],
                        support: [
                            { id: 'crew', name: 'Transporte de Personal' },
                            { id: 'rescue', name: 'Rescate Marítimo' }
                        ]
                    }
                }
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

            // Activity type selection
            const activityOptions = document.querySelectorAll('.activity-option');
            const activityTypeSelect = document.getElementById('activity-type');
            const activityTypeHeader = document.getElementById('activity-type-header');
            const activityTypeOptions = document.getElementById('activity-type-options');
            const placeholder = activityTypeHeader.querySelector('.placeholder');

            activityTypeHeader.addEventListener('click', function() {
                this.classList.toggle('open');
                activityTypeOptions.classList.toggle('open');
            });

            // Cerrar dropdown al hacer clic fuera
            document.addEventListener('click', function(e) {
                if (!activityTypeHeader.contains(e.target)) {
                    activityTypeHeader.classList.remove('open');
                    activityTypeOptions.classList.remove('open');
                }
            });

            // Selección de opción de actividad
            activityOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');

                    // Actualizar selección visual
                    activityOptions.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');

                    // Actualizar valor
                    activityTypeSelect.value = value;
                    placeholder.textContent = this.querySelector('.activity-label').textContent;

                    // Cerrar dropdown
                    activityTypeHeader.classList.remove('open');
                    activityTypeOptions.classList.remove('open');
                });
            });

            function resetActivityOptions() {
                activityOptions.forEach(opt => opt.classList.remove('selected'));
                activityTypeSelect.value = '';
                placeholder.textContent = 'Seleccionar actividad...';
            }

            // Work type selection
            const workTypeOptions = document.querySelectorAll('.work-type-option');

            workTypeOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    const radio = this.querySelector('input[type="radio"]');

                    // Actualizar selección visual
                    workTypeOptions.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');

                    // Marcar el radio button
                    radio.checked = true;

                    // Habilitar el selector de tipo de servicio
                    document.getElementById('service-type').disabled = false;

                    // Limpiar y actualizar tipos de servicio
                    updateServiceTypes(value);
                });
            });

            // Actualizar tipos de servicio
            function updateServiceTypes(workType) {
                const serviceTypeSelect = document.getElementById('service-type');
                serviceTypeSelect.innerHTML = '<option value="">Seleccionar tipo de servicio...</option>';

                // Obtener tipos de servicio según el tipo de trabajo
                const serviceTypes = serviceData[workType].types;

                // Agregar opciones
                serviceTypes.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.id;
                    option.textContent = type.name;
                    serviceTypeSelect.appendChild(option);
                });

                // Deshabilitar selectores dependientes
                document.getElementById('service-performed').disabled = true;
                document.getElementById('service').disabled = true;
            }

            // Manejo de cambios en tipo de servicio
            document.getElementById('service-type').addEventListener('change', function() {
                const workType = document.querySelector('.work-type-option.selected')?.getAttribute('data-value');
                const serviceType = this.value;

                if (workType && serviceType) {
                    // Habilitar selector de servicio realizado
                    const servicePerformedSelect = document.getElementById('service-performed');
                    servicePerformedSelect.disabled = false;
                    servicePerformedSelect.innerHTML = '<option value="">Seleccionar servicio realizado...</option>';

                    // Obtener servicios realizados
                    const servicesPerformed = serviceData[workType].performed[serviceType];

                    // Agregar opciones
                    servicesPerformed.forEach(service => {
                        const option = document.createElement('option');
                        option.value = service.id;
                        option.textContent = service.name;
                        servicePerformedSelect.appendChild(option);
                    });

                    // Deshabilitar selector de servicio
                    document.getElementById('service').disabled = true;
                }
            });

            // Manejo de cambios en servicio realizado
            document.getElementById('service-performed').addEventListener('change', function() {
                const workType = document.querySelector('.work-type-option.selected')?.getAttribute('data-value');
                const serviceType = document.getElementById('service-type').value;
                const servicePerformed = this.value;

                if (workType && serviceType && servicePerformed) {
                    // Habilitar selector de servicio
                    const serviceSelect = document.getElementById('service');
                    serviceSelect.disabled = false;
                    serviceSelect.innerHTML = '<option value="">Seleccionar servicio...</option>';

                    // Obtener servicios
                    const services = serviceData[workType].services[servicePerformed];

                    // Agregar opciones
                    services.forEach(service => {
                        const option = document.createElement('option');
                        option.value = service.id;
                        option.textContent = service.name;
                        serviceSelect.appendChild(option);
                    });
                }
            });

            function resetServiceForm() {
                // Reset work type
                workTypeOptions.forEach(opt => opt.classList.remove('selected'));
                document.querySelectorAll('input[name="work-type"]').forEach(radio => {
                    radio.checked = false;
                });

                // Reset selects
                document.getElementById('service-type').disabled = true;
                document.getElementById('service-type').selectedIndex = 0;
                document.getElementById('service-performed').disabled = true;
                document.getElementById('service-performed').selectedIndex = 0;
                document.getElementById('service').disabled = true;
                document.getElementById('service').selectedIndex = 0;

                // Hide error messages
                document.querySelectorAll('.error-message').forEach(el => {
                    el.style.display = 'none';
                });
            }

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
                let isValid = true;

                // Hide all error messages
                document.querySelectorAll('.error-message').forEach(el => {
                    el.style.display = 'none';
                });

                // Validate form based on active tab
                if (document.getElementById('activity-tab').classList.contains('active')) {
                    // Activity tab validation
                    if (!activityTypeSelect.value) {
                        document.getElementById('activity-type-error').style.display = 'block';
                        isValid = false;
                    }
                } else {
                    // Service tab validation
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
        });
