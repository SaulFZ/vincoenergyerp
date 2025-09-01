document.addEventListener('DOMContentLoaded', function() {

    // =================================================================
    // === LÓGICA PARA SERVICIOS Y BONOS ===============================
    // =================================================================

    const servicesInfoBtn = document.getElementById('services-info');
    const servicesModal = document.getElementById('services-modal');
    const servicesCloseBtn = document.querySelector('.services-close-btn');
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    if (servicesInfoBtn) {
        servicesInfoBtn.addEventListener('click', function() {
            servicesModal.style.display = 'flex';
            loadServicesAndBonuses();
        });
    }

    if (servicesCloseBtn) {
        servicesCloseBtn.addEventListener('click', function() {
            servicesModal.style.display = 'none';
        });
    }

    // Cierra el modal si el usuario hace clic fuera de él
    window.addEventListener('click', function(event) {
        if (event.target === servicesModal) {
            servicesModal.style.display = 'none';
        }
    });

    // Funcionalidad de pestañas
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            const targetTab = this.getAttribute('data-tab');
            document.getElementById(targetTab).classList.add('active');
            this.classList.add('active');
        });
    });

    /**
     * Carga servicios y bonos del servidor y los muestra en el modal.
     */
    async function loadServicesAndBonuses() {
        const servicesPlaceholder = document.getElementById('services-placeholder');
        const bonusesPlaceholder = document.getElementById('bonuses-placeholder');

        servicesPlaceholder.innerHTML = '<p>Cargando servicios...</p>';
        bonusesPlaceholder.innerHTML = '<p>Cargando bonos...</p>';

        try {
            const response = await fetch('/recursoshumanos/loadchart/info-services');
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();

            renderServices(servicesPlaceholder, data.services);
            renderBonuses(bonusesPlaceholder, data.bonuses);

        } catch (error) {
            console.error('Error fetching services and bonuses:', error);
            servicesPlaceholder.innerHTML = '<p style="color: red;">Error al cargar los servicios.</p>';
            bonusesPlaceholder.innerHTML = '<p style="color: red;">Error al cargar los bonos.</p>';
        }
    }

    /**
     * Renderiza los datos de servicios en un formato de tabla.
     * @param {HTMLElement} placeholder - El elemento DOM donde renderizar.
     * @param {Object} servicesData - Los datos de servicios agrupados.
     */
    function renderServices(placeholder, servicesData) {
        if (Object.keys(servicesData).length === 0) {
            placeholder.innerHTML = '<p>No hay servicios disponibles.</p>';
            return;
        }

        const servicesTable = document.createElement('div');
        servicesTable.className = 'services-table';
        servicesTable.innerHTML = `
            <div class="table-header">
                <div class="col-1">Tipo de Operación</div>
                <div class="col-2">Tipo de Servicio</div>
                <div class="col-3">Servicio Realizado</div>
                <div class="col-4">ID</div>
                <div class="col-5">Descripción</div>
            </div>
        `;

        for (const operationType in servicesData) {
            const operationGroup = servicesData[operationType];
            for (const serviceType in operationGroup) {
                const serviceTypeGroup = operationGroup[serviceType];
                for (const servicePerformed in serviceTypeGroup) {
                    const servicesArray = serviceTypeGroup[servicePerformed];
                    servicesArray.forEach(service => {
                        const row = document.createElement('div');
                        row.className = 'table-row';
                        row.innerHTML = `
                            <div class="col-1">${service.operation_type}</div>
                            <div class="col-2">${service.service_type}</div>
                            <div class="col-3">${service.service_performed}</div>
                            <div class="col-4">${service.identifier}</div>
                            <div class="col-5">${service.service_description}</div>
                        `;
                        servicesTable.appendChild(row);
                    });
                }
            }
        }
        placeholder.innerHTML = '';
        placeholder.appendChild(servicesTable);
    }

    /**
     * Renderiza los datos de bonos en un formato de tabla.
     * @param {HTMLElement} placeholder - El elemento DOM donde renderizar.
     * @param {Array} bonusesData - El array de objetos de bonos.
     */
    function renderBonuses(placeholder, bonusesData) {
        if (bonusesData.length === 0) {
            placeholder.innerHTML = '<p>No hay bonos disponibles.</p>';
            return;
        }

        const bonusesTable = document.createElement('div');
        bonusesTable.className = 'bonuses-table';
        bonusesTable.innerHTML = `
            <div class="table-header">
                <div class="col-1">Categoría de Empleado</div>
                <div class="col-2">Tipo de Bono</div>
                <div class="col-3">Identificador</div>
            </div>
        `;

        bonusesData.forEach(bonus => {
            const row = document.createElement('div');
            row.className = 'table-row';
            row.innerHTML = `
                <div class="col-1">${bonus.employee_category}</div>
                <div class="col-2">${bonus.bonus_type}</div>
                <div class="col-3">${bonus.bonus_identifier}</div>
            `;
            bonusesTable.appendChild(row);
        });

        placeholder.innerHTML = '';
        placeholder.appendChild(bonusesTable);
    }

    // =================================================================
    // === LÓGICA PARA CALENDARIO Y QUINCENAS ==========================
    // =================================================================

    const quincenaModal = document.getElementById("quincena-modal");
    const openBtn = document.getElementById("days-quincena");
    const quincenaCloseBtn = quincenaModal.querySelector(".quincena-close-btn");
    const cancelBtn = quincenaModal.querySelector(".quincena-cancel-btn");
    const saveBtn = quincenaModal.querySelector(".quincena-save-btn");
    const editBtn = quincenaModal.querySelector(".quincena-edit-btn");
    const prevMonthBtn = quincenaModal.querySelector(".quincena-prev-month");
    const nextMonthBtn = quincenaModal.querySelector(".quincena-next-month");
    const monthTitle = quincenaModal.querySelector(".quincena-month-title");
    const calendarGrid = quincenaModal.querySelector(".quincena-calendar-grid");

    // Inputs de fechas
    const q1StartInput = document.getElementById("q1-start");
    const q1EndInput = document.getElementById("q1-end");
    const q2StartInput = document.getElementById("q2-start");
    const q2EndInput = document.getElementById("q2-end");

    let currentDate = new Date();
    let currentQuincenaConfig = null;
    let originalQuincenaConfig = null;

    if (openBtn) {
        openBtn.addEventListener("click", function () {
            quincenaModal.style.display = "flex";
            loadCurrentMonthConfig();
        });
    }

    function closeQuincenaModal() {
        quincenaModal.style.display = "none";
        resetForm();
    }

    if (quincenaCloseBtn) quincenaCloseBtn.addEventListener("click", closeQuincenaModal);

    if (cancelBtn) {
        cancelBtn.addEventListener("click", function() {
            if (originalQuincenaConfig) {
                populateFormWithConfig(originalQuincenaConfig);
            }
            disableEditMode();
        });
    }
    if (editBtn) editBtn.addEventListener("click", enableEditMode);

    if (prevMonthBtn) {
        prevMonthBtn.addEventListener("click", function () {
            currentDate.setMonth(currentDate.getMonth() - 1);
            loadCurrentMonthConfig();
        });
    }
    if (nextMonthBtn) {
        nextMonthBtn.addEventListener("click", function () {
            currentDate.setMonth(currentDate.getMonth() + 1);
            loadCurrentMonthConfig();
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener("click", saveQuincenaConfig);
    }

    document.querySelectorAll('.quincena-date-input').forEach(input => {
        input.addEventListener("change", updateCalendarFromInputs);
    });

    function enableEditMode() {
        saveBtn.disabled = false;
        editBtn.style.display = 'none';
        cancelBtn.style.display = 'inline-flex';
        document.querySelectorAll('.quincena-date-input').forEach(input => {
            input.removeAttribute('readonly');
        });
        showNotification('Modo de edición activado. Puedes modificar las fechas.', 'info');
        renderQuincenaCalendar(currentDate);
    }

    function disableEditMode() {
        saveBtn.disabled = true;
        editBtn.style.display = 'inline-flex';
        cancelBtn.style.display = 'none';
        document.querySelectorAll('.quincena-date-input').forEach(input => {
            input.setAttribute('readonly', true);
        });
        renderQuincenaCalendar(currentDate);
    }

    async function loadCurrentMonthConfig() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth() + 1;
        try {
            const response = await fetch(`/recursoshumanos/loadchart/fortnightly-config/${year}/${month}`);
            if (response.ok) {
                const config = await response.json();
                if (config) {
                    currentQuincenaConfig = config;
                } else {
                    currentQuincenaConfig = await generateDefaultConfig(year, month);
                }
            } else {
                currentQuincenaConfig = await generateDefaultConfig(year, month);
            }
            originalQuincenaConfig = { ...currentQuincenaConfig };
            populateFormWithConfig(currentQuincenaConfig);
            disableEditMode();
        } catch (error) {
            console.error('Error loading config:', error);
            currentQuincenaConfig = await generateDefaultConfig(year, month);
            originalQuincenaConfig = { ...currentQuincenaConfig };
            populateFormWithConfig(currentQuincenaConfig);
            disableEditMode();
        }
    }

    async function generateDefaultConfig(year, month) {
        try {
            const response = await fetch('/recursoshumanos/loadchart/fortnightly-config/generate-default', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ year, month })
            });
            const result = await response.json();
            if (response.ok) {
                return result.data;
            }
        } catch (error) {
            console.error('Error generating default config:', error);
        }

        const firstDay = new Date(year, month - 1, 1);
        const lastDay = new Date(year, month, 0);
        const fifteenthDay = new Date(year, month - 1, 15);
        const sixteenthDay = new Date(year, month - 1, 16);
        if (sixteenthDay.getMonth() + 1 !== month) {
            sixteenthDay.setDate(lastDay.getDate());
        }
        return {
            year: year,
            month: month,
            q1_start: formatDateForInput(firstDay),
            q1_end: formatDateForInput(fifteenthDay),
            q2_start: formatDateForInput(sixteenthDay),
            q2_end: formatDateForInput(lastDay)
        };
    }

    function populateFormWithConfig(config) {
        q1StartInput.value = config.q1_start ? new Date(config.q1_start).toISOString().split('T')[0] : '';
        q1EndInput.value = config.q1_end ? new Date(config.q1_end).toISOString().split('T')[0] : '';
        q2StartInput.value = config.q2_start ? new Date(config.q2_start).toISOString().split('T')[0] : '';
        q2EndInput.value = config.q2_end ? new Date(config.q2_end).toISOString().split('T')[0] : '';
        renderQuincenaCalendar(currentDate);
    }

    function formatDateForInput(date) {
        const d = new Date(date);
        let month = '' + (d.getMonth() + 1);
        let day = '' + d.getDate();
        const year = d.getFullYear();
        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;
        return [year, month, day].join('-');
    }

    function updateCalendarFromInputs() {
        renderQuincenaCalendar(currentDate);
    }

    function isDateInQuincena(date) {
        const q1Start = q1StartInput.value ? new Date(q1StartInput.value + 'T00:00:00') : null;
        const q1End = q1EndInput.value ? new Date(q1EndInput.value + 'T00:00:00') : null;
        const q2Start = q2StartInput.value ? new Date(q2StartInput.value + 'T00:00:00') : null;
        const q2End = q2EndInput.value ? new Date(q2EndInput.value + 'T00:00:00') : null;

        const testDate = new Date(date);
        testDate.setHours(0, 0, 0, 0);

        const isQ1 = q1Start && q1End && testDate >= q1Start && testDate <= q1End;
        const isQ2 = q2Start && q2End && testDate >= q2Start && testDate <= q2End;

        return isQ1 || isQ2;
    }

    function isBoundaryDay(date) {
        const q1Start = q1StartInput.value ? new Date(q1StartInput.value + 'T00:00:00') : null;
        const q1End = q1EndInput.value ? new Date(q1EndInput.value + 'T00:00:00') : null;
        const q2Start = q2StartInput.value ? new Date(q2StartInput.value + 'T00:00:00') : null;
        const q2End = q2EndInput.value ? new Date(q2EndInput.value + 'T00:00:00') : null;

        const testDate = new Date(date);
        testDate.setHours(0, 0, 0, 0);

        let isStart = false;
        let isEnd = false;

        if (q1Start && testDate.getTime() === q1Start.getTime()) isStart = true;
        if (q1End && testDate.getTime() === q1End.getTime()) isEnd = true;
        if (q2Start && testDate.getTime() === q2Start.getTime()) isStart = true;
        if (q2End && testDate.getTime() === q2End.getTime()) isEnd = true;

        return { isStart, isEnd };
    }

    function renderQuincenaCalendar(date) {
        const year = date.getFullYear();
        const month = date.getMonth();
        monthTitle.textContent = `${getMonthName(month)} ${year}`;

        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const firstDayOfWeek = firstDay.getDay();

        while (calendarGrid.firstChild) {
            calendarGrid.removeChild(calendarGrid.firstChild);
        }

        const dayLabels = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];
        dayLabels.forEach(label => {
            const labelCell = document.createElement("div");
            labelCell.className = "quincena-day-label";
            labelCell.textContent = label;
            calendarGrid.appendChild(labelCell);
        });

        const prevMonthLastDay = new Date(year, month, 0);
        const prevMonthDaysCount = 5;
        const offset = (firstDayOfWeek - prevMonthDaysCount + 7) % 7;

        for (let i = 0; i < offset; i++) {
            const emptyCell = document.createElement("div");
            emptyCell.className = "quincena-day-cell empty-cell";
            calendarGrid.appendChild(emptyCell);
        }

        for (let i = prevMonthDaysCount - 1; i >= 0; i--) {
            const day = prevMonthLastDay.getDate() - i;
            const prevMonthDate = new Date(prevMonthLastDay.getFullYear(), prevMonthLastDay.getMonth(), day);

            const dayCell = document.createElement("div");
            dayCell.className = "quincena-day-cell other-month-day";
            dayCell.textContent = day;

            if (isDateInQuincena(prevMonthDate)) {
                dayCell.classList.add("quincena-selected");
            }

            const boundary = isBoundaryDay(prevMonthDate);
            if (boundary.isStart) {
                dayCell.classList.add("quincena-start");
            }
            if (boundary.isEnd) {
                dayCell.classList.add("quincena-end");
            }

            dayCell.setAttribute('data-tooltip', `${day} de ${getMonthName(prevMonthDate.getMonth())} ${prevMonthDate.getFullYear()}`);
            dayCell.classList.add('has-tooltip');
            calendarGrid.appendChild(dayCell);
        }

        for (let day = 1; day <= lastDay.getDate(); day++) {
            const dayCell = document.createElement("div");
            dayCell.className = "quincena-day-cell";
            dayCell.textContent = day;
            const cellDate = new Date(year, month, day);

            if (isDateInQuincena(cellDate)) {
                dayCell.classList.add("quincena-selected");
            }

            const boundary = isBoundaryDay(cellDate);
            if (boundary.isStart) {
                dayCell.classList.add("quincena-start");
            }
            if (boundary.isEnd) {
                dayCell.classList.add("quincena-end");
            }

            dayCell.setAttribute('data-tooltip', `${day} de ${getMonthName(month)} ${year}`);
            dayCell.classList.add('has-tooltip');
            calendarGrid.appendChild(dayCell);
        }

        const cellsUsed = offset + prevMonthDaysCount + lastDay.getDate();
        const totalCells = Math.ceil(cellsUsed / 7) * 7;
        const remainingCells = totalCells - cellsUsed;

        for (let i = 0; i < remainingCells; i++) {
            const emptyCell = document.createElement("div");
            emptyCell.className = "quincena-day-cell empty-cell";
            calendarGrid.appendChild(emptyCell);
        }
    }

    async function saveQuincenaConfig() {
        if (!q1StartInput.value || !q1EndInput.value || !q2StartInput.value || !q2EndInput.value) {
            showNotification('Por favor, complete todas las fechas', 'error');
            return;
        }
        const q1Start = new Date(q1StartInput.value + 'T00:00:00');
        const q1End = new Date(q1EndInput.value + 'T00:00:00');
        const q2Start = new Date(q2StartInput.value + 'T00:00:00');
        const q2End = new Date(q2EndInput.value + 'T00:00:00');

        if (q1Start > q1End) {
            showNotification('La fecha de inicio de la primera quincena debe ser anterior a la de fin.', 'error');
            return;
        }
        if (q1End.getTime() >= q2Start.getTime()) {
            showNotification('La primera quincena debe terminar antes de que inicie la segunda.', 'error');
            return;
        }
        if (q2Start > q2End) {
            showNotification('La fecha de inicio de la segunda quincena debe ser anterior a la de fin.', 'error');
            return;
        }

        const configData = {
            year: currentDate.getFullYear(),
            month: currentDate.getMonth() + 1,
            q1_start: q1StartInput.value,
            q1_end: q1EndInput.value,
            q2_start: q2StartInput.value,
            q2_end: q2EndInput.value,
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        try {
            const response = await fetch('/recursoshumanos/loadchart/fortnightly-config', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': configData._token
                },
                body: JSON.stringify(configData)
            });
            const result = await response.json();

            if (response.ok) {
                showNotification('Configuración guardada exitosamente', 'success');
                currentQuincenaConfig = result.data;
                originalQuincenaConfig = { ...currentQuincenaConfig };
                populateFormWithConfig(currentQuincenaConfig);
                disableEditMode();
            } else {
                const errorMessage = result.errors ? Object.values(result.errors).flat().join('<br>') : result.message;
                showNotification(errorMessage || 'Error al guardar la configuración', 'error');
            }
        } catch (error) {
            console.error('Error saving config:', error);
            showNotification('Error al guardar la configuración', 'error');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
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

    function resetForm() {
        currentQuincenaConfig = null;
        originalQuincenaConfig = null;
        q1StartInput.value = '';
        q1EndInput.value = '';
        q2StartInput.value = '';
        q2EndInput.value = '';
        disableEditMode();
        renderQuincenaCalendar(currentDate);
    }

    function getMonthName(monthIndex) {
        const months = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        return months[monthIndex];
    }

    // =================================================================
    // === LÓGICA PARA TABLA DE EMPLEADOS ==============================
    // =================================================================

    const backToCalendarBtn = document.getElementById("back-to-calendar");
    if (backToCalendarBtn) {
        backToCalendarBtn.addEventListener("click", function (e) {
            e.preventDefault();
            window.location.href = "/recursoshumanos/loadchart/calendar";
        });
    }

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
    document.getElementById("prev-period").addEventListener("click", () => {
        alert("Navegando al período anterior");
    });
    document.getElementById("next-period").addEventListener("click", () => {
        alert("Navegando al próximo período");
    });

    document.querySelectorAll(".btn-approve").forEach((btn) => {
        btn.addEventListener("click", function () {
            const row = this.closest(".employee-row");
            approveEmployee(row);
        });
    });

    document.querySelectorAll(".btn-review").forEach((btn) => {
        btn.addEventListener("click", function () {
            const row = this.closest(".employee-row");
            markAsReviewed(row);
        });
    });

    const saveBtnElement = document.querySelector(".save-btn");
    if (saveBtnElement) {
        saveBtnElement.addEventListener("click", guardarDatos);
    }

    showQuincena(1);

    function setActiveButton(buttonId) {
        document.querySelectorAll(".period-btn").forEach((btn) => {
            btn.classList.remove("active");
        });
        const targetBtn = document.getElementById(buttonId);
        if (targetBtn) {
            targetBtn.classList.add("active");
        }
    }

    function showQuincena(quincena) {
        const dayHeaders = document.querySelectorAll(".approval-table .day-header");
        const allRows = document.querySelectorAll(".approval-table tbody tr");
        const daysColumnsHeader = document.getElementById("days-columns");
        if (!dayHeaders.length || !daysColumnsHeader) return;

        let firstDay = quincena === 1 ? 1 : 16;
        let lastDay = quincena === 1 ? 15 : 31;
        let visibleDays = lastDay - firstDay + 1;

        daysColumnsHeader.colSpan = visibleDays;

        dayHeaders.forEach((header, index) => {
            const dayNumber = parseInt(header.textContent.split("\n")[0]);
            const shouldShow = dayNumber >= firstDay && dayNumber <= lastDay;
            header.style.display = shouldShow ? "" : "none";
        });

        allRows.forEach((row) => {
            const dataCells = row.querySelectorAll(".data-cell");
            dayHeaders.forEach((header, index) => {
                const dayNumber = parseInt(header.textContent.split("\n")[0]);
                const shouldShow = dayNumber >= firstDay && dayNumber <= lastDay;
                if (dataCells[index]) {
                    dataCells[index].style.display = shouldShow ? "" : "none";
                }
            });
        });

        const fixedColumns = document.querySelectorAll(".employee-info-cell, .activity-label-cell");
        fixedColumns.forEach((col) => {
            col.style.width = "auto";
            col.style.minWidth = "120px";
        });
    }

    function showFullMonth() {
        const dayHeaders = document.querySelectorAll(".approval-table .day-header");
        const allRows = document.querySelectorAll(".approval-table tbody tr");
        const daysColumnsHeader = document.getElementById("days-columns");
        if (!dayHeaders.length || !daysColumnsHeader) return;

        daysColumnsHeader.colSpan = 31;

        dayHeaders.forEach((header, index) => {
            header.style.display = "";
            allRows.forEach((row) => {
                const dataCells = row.querySelectorAll(".data-cell");
                if (dataCells[index]) {
                    dataCells[index].style.display = "";
                }
            });
        });

        const fixedColumns = document.querySelectorAll(".employee-info-cell, .activity-label-cell");
        fixedColumns.forEach((col) => {
            col.style.width = "";
            col.style.minWidth = "";
        });
    }

    function approveEmployee(row) {
        const statusIndicators = row.querySelectorAll(".status-indicator");
        statusIndicators.forEach((indicator) => {
            if (indicator.textContent === "N") {
                indicator.textContent = "A";
                indicator.classList.remove("status-n");
                indicator.classList.add("status-a");
            }
        });
        const approveBtn = row.querySelector(".btn-approve");
        if (approveBtn) {
            approveBtn.textContent = "Aprobado";
            approveBtn.classList.add("approved");
            approveBtn.disabled = true;
        }
    }

    function markAsReviewed(row) {
        const statusIndicators = row.querySelectorAll(".status-indicator");
        statusIndicators.forEach((indicator) => {
            if (indicator.textContent === "N") {
                indicator.textContent = "R";
                indicator.classList.remove("status-n");
                indicator.classList.add("status-r");
            }
        });
        const reviewBtn = row.querySelector(".btn-review");
        if (reviewBtn) {
            reviewBtn.textContent = "Revisado";
            reviewBtn.classList.add("reviewed");
            reviewBtn.disabled = true;
        }
    }

    function guardarDatos() {
        const saveBtn = document.querySelector(".save-btn");
        if (!saveBtn) return;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        setTimeout(() => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
            const notification = document.createElement("div");
            notification.className = "notification success";
            notification.innerHTML = '<i class="fas fa-check-circle"></i> Cambios guardados correctamente';
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.classList.add("fade-out");
                setTimeout(() => notification.remove(), 500);
            }, 3000);
        }, 1500);
    }

    function toggleActivityRows(employeeRow) {
        const activityRows = employeeRow.nextElementSibling;
        if (activityRows && activityRows.classList.contains("activity-row")) {
            activityRows.classList.toggle("hidden");
        }
    }

    document.querySelectorAll(".employee-info-cell").forEach((cell) => {
        cell.addEventListener("click", function () {
            const employeeRow = this.closest(".employee-row");
            toggleActivityRows(employeeRow);
        });
    });





    /* ================================================================= */
    /* === LÓGICA PARA GESTIÓN DE CUADRILLAS (SQUAD CONTROL) ========= */
    /* ================================================================= */

    // Elementos del DOM
    const squadModal = document.getElementById("squad-control-modal");
    const squadFormModal = document.getElementById("squad-form-modal");
    const closeSquadModalBtn = squadModal.querySelector(".squad-close-btn");
    const closeFormModalBtns = squadFormModal.querySelectorAll(
        ".form-close-btn, .cancel-sq-btn"
    );
    const addNewSquadBtn = document.getElementById("add-new-squad");
    const saveFormBtn = document.querySelector(".save-sq-btn");
    const formTitle = document.getElementById("form-title");
    const squadSelect = document.getElementById("squad-select");
    const operatorSelects = document.querySelectorAll(".operator-select");
    const squadTableBody = document.querySelector(".squad-table tbody");
    const searchInput = document.querySelector(".search-input");
    const searchClearBtn = document.querySelector(".search-clear");

    // Variables de estado
    let operadoresData = [];
    let squadsData = [];
    let isEditMode = false;
    let editingSquadNumber = null;

    // Mostrar skeleton de carga
    function showLoadingSkeleton() {
        squadTableBody.innerHTML = "";
        for (let i = 0; i < 5; i++) {
            const row = document.createElement("tr");
            row.className = "squad-row loading";
            row.innerHTML = `
                <td class="centered-cell">
                    <div class="skeleton skeleton-badge"></div>
                </td>
                <td>
                    <div class="operators-grid">
                        ${Array(4)
                            .fill()
                            .map(
                                () => `
                            <div class="operator-card skeleton">
                                <div class="operator-avatar skeleton"></div>
                                <div class="operator-info">
                                    <span class="operator-name skeleton skeleton-text"></span>
                                    <span class="operator-id skeleton skeleton-text"></span>
                                </div>
                            </div>
                        `
                            )
                            .join("")}
                    </div>
                </td>
                <td>
                    <div class="action-buttons">
                        <div class="skeleton skeleton-btn"></div>
                        <div class="skeleton skeleton-btn"></div>
                    </div>
                </td>
            `;
            squadTableBody.appendChild(row);
        }
    }

    // Generar opciones de cuadrillas disponibles
    // Generar opciones de cuadrillas disponibles
function generateSquadOptions() {
    squadSelect.innerHTML =
        '<option value="">Seleccionar Cuadrilla...</option>';

    // Obtener números de cuadrillas existentes
    const existingSquads = squadsData.map((squad) => squad.squad_number);

    for (let i = 1; i <= 20; i++) {
        const option = document.createElement("option");
        const squadNumber = i.toString().padStart(2, "0");
        option.value = i;
        option.textContent = `Cuadrilla-${squadNumber}`;

        // LÓGICA MEJORADA
        // Deshabilitar la opción si ya existe en otra cuadrilla
        const isExisting = existingSquads.includes(i);
        const isEditingThisSquad = (isEditMode && parseInt(editingSquadNumber) === i);

        if (isExisting && !isEditingThisSquad) {
            option.disabled = true;
            option.textContent += " (Existente)";
        }

        // Fin de la lógica mejorada
        squadSelect.appendChild(option);
    }
}

    // Cargar operadores con caché local
    async function fetchAndPopulateOperators() {
        try {
            // Mostrar loading en selects
            operatorSelects.forEach((select) => {
                select.innerHTML =
                    '<option value="">Cargando operadores...</option>';
                select.disabled = true;
            });

            // Verificar si ya tenemos los datos en caché
            if (operadoresData.length > 0) {
                populateOperatorSelects();
                return;
            }

            const response = await fetch(window.appRoutes.getOperadores);
            if (!response.ok) throw new Error("Error al obtener operadores");

            operadoresData = await response.json();
            populateOperatorSelects();
        } catch (error) {
            console.error("Error:", error);
            operatorSelects.forEach((select) => {
                select.innerHTML = '<option value="">Error al cargar</option>';
            });
            showError("Error al cargar los operadores");
        }
    }

    function populateOperatorSelects() {
        operatorSelects.forEach((select) => {
            select.innerHTML =
                '<option value="">Seleccionar Operador...</option>';
            operadoresData.forEach((operador) => {
                const option = document.createElement("option");
                option.value = operador.employee_number;
                option.textContent = operador.full_name;

                // Deshabilitar operadores ya asignados (excepto en edición)
                if (!isEditMode) {
                    const isAssigned = squadsData.some((squad) =>
                        squad.employees.some(
                            (emp) =>
                                emp.employee_number === operador.employee_number
                        )
                    );
                    option.disabled = isAssigned;
                    if (isAssigned) {
                        option.textContent += " (Asignado)";
                    }
                }

                select.appendChild(option);
            });
            select.disabled = false;
        });
    }

    // Cargar cuadrillas con skeleton
    async function loadSquads() {
        showLoadingSkeleton();

        try {
            const response = await fetch(window.appRoutes.getSquads);
            if (!response.ok) throw new Error("Error al cargar cuadrillas");

            squadsData = await response.json();
            renderSquadsTable();
        } catch (error) {
            console.error("Error:", error);
            squadTableBody.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Error al cargar las cuadrillas</p>
                        <button class="retry-btn" id="retry-load-squads">Reintentar</button>
                    </td>
                </tr>
            `;
            document
                .getElementById("retry-load-squads")
                ?.addEventListener("click", loadSquads);
        }
    }

    // Renderizar tabla de cuadrillas
    function renderSquadsTable() {
        squadTableBody.innerHTML = "";

        if (squadsData.length === 0) {
            squadTableBody.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center" style="padding: 2rem;">
                        <i class="fas fa-users" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <p style="color: #666;">No hay cuadrillas registradas</p>
                    </td>
                </tr>
            `;
            return;
        }

        squadsData.forEach((squad) => {
            const row = document.createElement("tr");
            row.className = "squad-row";
            row.dataset.squadNumber = squad.squad_number;

            // Crear celdas de operadores
            let operatorsHtml = "";
            squad.employees.forEach((employee) => {
                operatorsHtml += `
                    <div class="operator-card">
                        <div class="operator-avatar"><i class="fas fa-user"></i></div>
                        <div class="operator-info">
                            <span class="operator-name">${employee.full_name}</span>
                            <span class="operator-id">ID: ${employee.employee_number}</span>
                        </div>
                    </div>
                `;
            });

            // Agregar espacios vacíos si hay menos de 4 operadores
            const remainingSlots = 4 - squad.employees.length;
            for (let i = 0; i < remainingSlots; i++) {
                operatorsHtml += `
                    <div class="operator-card vacant">
                        <div class="operator-avatar"><i class="fas fa-user-plus"></i></div>
                        <div class="operator-info"><span class="operator-name">Vacío</span></div>
                    </div>
                `;
            }

            row.innerHTML = `
                <td class="centered-cell">
                    <span class="squad-badge">${squad.squad_name}</span>
                </td>
                <td>
                    <div class="operators-grid">
                        ${operatorsHtml}
                    </div>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="action-btn edit" title="Editar" data-squad="${squad.squad_number}">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button class="action-btn delete" title="Eliminar" data-squad="${squad.squad_number}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            `;

            squadTableBody.appendChild(row);
        });
    }

    // Editar cuadrilla con animación
    function editSquad(squadNumber) {
        // Busca la cuadrilla en los datos que ya tenemos
        const squadData = squadsData.find(
            (squad) => parseInt(squad.squad_number) === parseInt(squadNumber)
        );

        // Valida que la cuadrilla exista
        if (!squadData) {
            showError("Cuadrilla no encontrada en los datos locales.");
            return;
        }

        // Configura el formulario para el modo de edición
        isEditMode = true;
        editingSquadNumber = squadNumber;
        formTitle.textContent = `Editar ${squadData.squad_name}`;

        // Regenera las opciones del select de cuadrillas y selecciona la actual
        generateSquadOptions();
        squadSelect.value = squadNumber;

        // Limpia todos los selects primero
        operatorSelects.forEach((select) => {
            select.innerHTML =
                '<option value="">Seleccionar Operador...</option>';

            // Llenar con todos los operadores
            operadoresData.forEach((operador) => {
                const option = document.createElement("option");
                option.value = operador.employee_number;
                option.textContent = operador.full_name;

                // Deshabilitar operadores ya asignados a OTRAS cuadrillas
                const isAssignedToOtherSquad = squadsData.some(
                    (squad) =>
                        squad.squad_number != squadNumber &&
                        squad.employees.some(
                            (emp) =>
                                emp.employee_number === operador.employee_number
                        )
                );

                option.disabled = isAssignedToOtherSquad;
                if (isAssignedToOtherSquad) {
                    option.textContent += " (Asignado a otra cuadrilla)";
                }

                select.appendChild(option);
            });
        });

        // Rellena los selects de operadores con los datos de la cuadrilla
        squadData.employees.forEach((employee, index) => {
            if (index < operatorSelects.length) {
                operatorSelects[index].value = employee.employee_number;
            }
        });

        // Muestra el modal del formulario
        showModal(squadFormModal);
    }

    // Eliminar cuadrilla
    async function deleteSquad(squadNumber) {
        const result = await Swal.fire({
            title: "¿Estás seguro?",
            text: "Esta acción eliminará la cuadrilla. ¡No podrás revertirlo!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(
                    window.appRoutes.destroySquad + squadNumber,
                    {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content,
                        },
                    }
                );

                const data = await response.json();

                if (response.ok) {
                    await loadSquads();
                    showSuccess(data.message);
                } else {
                    showError(data.message);
                }
            } catch (error) {
                console.error("Error:", error);
                showError("Error al eliminar la cuadrilla");
            }
        }
    }

    // Guardar cuadrilla con validación mejorada
    async function saveSquad() {
        const squadNumber = squadSelect.value;
        const selectedOperators = Array.from(operatorSelects)
            .map((select) => select.value)
            .filter((op) => op);

        // Validaciones
        if (!squadNumber) {
            showError("Selecciona un número de cuadrilla");
            return false;
        }

        if (selectedOperators.length === 0) {
            showError("Selecciona al menos un operador");
            return false;
        }

        if (selectedOperators.length > 4) {
            showError("Máximo 4 operadores por cuadrilla");
            return false;
        }

        if (new Set(selectedOperators).size !== selectedOperators.length) {
            showError("No puede haber operadores duplicados");
            return false;
        }

        try {
            saveFormBtn.innerHTML =
                '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            saveFormBtn.disabled = true;

            const response = await fetch(window.appRoutes.storeSquad, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                },
                body: JSON.stringify({
                    squad_number: squadNumber,
                    employee_ids: selectedOperators,
                    is_edit: isEditMode,
                }),
            });

            const data = await response.json();

            if (response.ok) {
                // Actualizar los datos locales con la respuesta del servidor
                if (isEditMode) {
                    // Actualizar cuadrilla existente
                    const squadIndex = squadsData.findIndex(
                        (s) => s.squad_number == squadNumber
                    );
                    if (squadIndex !== -1) {
                        squadsData[squadIndex] = data.squad;
                    }
                } else {
                    // Agregar nueva cuadrilla
                    squadsData.push(data.squad);
                }

                // Ordenar cuadrillas por número
                squadsData.sort((a, b) => a.squad_number - b.squad_number);

                // Animación de éxito
                squadFormModal.classList.add("success-animation");
                setTimeout(() => {
                    hideModal(squadFormModal);
                    squadFormModal.classList.remove("success-animation");

                    // Renderizar tabla con datos actualizados
                    renderSquadsTable();
                    showSuccess(data.message);
                    resetForm();
                }, 1000);
            } else {
                showError(data.message || "Error al guardar");
            }
        } catch (error) {
            console.error("Error:", error);
            showError("Error al guardar la cuadrilla");
        } finally {
            saveFormBtn.innerHTML =
                '<i class="fas fa-save"></i> Guardar Cuadrilla';
            saveFormBtn.disabled = false;
        }
    }

    // Reiniciar formulario
    function resetForm() {
        squadSelect.value = "";
        operatorSelects.forEach((select) => (select.value = ""));
        isEditMode = false;
        editingSquadNumber = null;
        formTitle.textContent = "Nueva Cuadrilla";
    }

    // Búsqueda
    function handleSearch() {
        const searchTerm = searchInput.value.toLowerCase();
        const squadRows = document.querySelectorAll(".squad-row:not(.loading)");

        squadRows.forEach((row) => {
            const squadName = row
                .querySelector(".squad-badge")
                .textContent.toLowerCase();
            const operatorNames = Array.from(
                row.querySelectorAll(".operator-name")
            )
                .map((el) => el.textContent.toLowerCase())
                .join(" ");

            if (
                squadName.includes(searchTerm) ||
                operatorNames.includes(searchTerm)
            ) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    // Mostrar/ocultar modales
    function showModal(modalElement) {
        modalElement.classList.add("is-visible");
        document.body.style.overflow = "hidden";
    }

    function hideModal(modalElement) {
        modalElement.classList.remove("is-visible");
        if (!document.querySelector(".is-visible")) {
            document.body.style.overflow = "auto";
        }
    }

    function showError(message) {
        Swal.fire("Error", message, "error");
    }

    function showSuccess(message) {
        Swal.fire("¡Éxito!", message, "success");
    }

    // Event Listeners
    document.getElementById("squad-control")?.addEventListener("click", () => {
        showModal(squadModal);
        loadSquads();
    });

    closeSquadModalBtn.addEventListener("click", () => hideModal(squadModal));

    closeFormModalBtns.forEach((btn) =>
        btn.addEventListener("click", () => hideModal(squadFormModal))
    );

    addNewSquadBtn.addEventListener("click", () => {
        resetForm();
        generateSquadOptions();
        populateOperatorSelects();
        showModal(squadFormModal);
    });

    squadTableBody.addEventListener("click", function (event) {
        const editBtn = event.target.closest(".action-btn.edit");
        const deleteBtn = event.target.closest(".action-btn.delete");

        if (editBtn) {
            editSquad(editBtn.dataset.squad);
        }

        if (deleteBtn) {
            deleteSquad(deleteBtn.dataset.squad);
        }
    });

    saveFormBtn.addEventListener("click", saveSquad);
    searchInput.addEventListener("input", handleSearch);
    searchClearBtn.addEventListener("click", () => {
        searchInput.value = "";
        handleSearch();
    });

    // Inicialización mejorada
    async function initialize() {
        try {
            // Mostrar skeleton loading
            showLoadingSkeleton();

            // Cargar operadores y cuadrillas en paralelo para mayor velocidad
            const [operadoresResponse, squadsResponse] = await Promise.all([
                fetch(window.appRoutes.getOperadores),
                fetch(window.appRoutes.getSquads),
            ]);

            if (!operadoresResponse.ok || !squadsResponse.ok) {
                throw new Error("Error al cargar datos iniciales");
            }

            // Asignar datos
            operadoresData = await operadoresResponse.json();
            squadsData = await squadsResponse.json();

            // Renderizar tabla
            renderSquadsTable();

            // Generar opciones de cuadrillas
            generateSquadOptions();
        } catch (error) {
            console.error("Error en inicialización:", error);
            showError("Error al cargar los datos iniciales");

            // Mostrar opción de reintento
            squadTableBody.innerHTML = `
            <tr>
                <td colspan="3" class="text-center error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Error al cargar los datos</p>
                    <button class="retry-btn" id="retry-load-data">Reintentar</button>
                </td>
            </tr>
        `;
            document
                .getElementById("retry-load-data")
                ?.addEventListener("click", initialize);
        }
    }

    initialize();
});
