document.addEventListener("DOMContentLoaded", function () {
    /* ================================================================= */
    /* === LÓGICA PARA GESTIÓN DE CALENDARIO Y EMPLEADOS =============== */
    /* ================================================================= */
    const quincenaModal = document.getElementById("quincena-modal");
    const openBtn = document.getElementById("days-quincena");
    const closeBtn = quincenaModal.querySelector(".quincena-close-btn");
    const cancelBtn = quincenaModal.querySelector(".quincena-cancel-btn");
    const prevMonthBtn = quincenaModal.querySelector(".quincena-prev-month");
    const nextMonthBtn = quincenaModal.querySelector(".quincena-next-month");
    const monthTitle = quincenaModal.querySelector(".quincena-month-title");
    const calendarGrid = quincenaModal.querySelector(".quincena-calendar-grid");

    let currentDate = new Date();

    // Abrir modal
    if (openBtn) {
        openBtn.addEventListener("click", function () {
            quincenaModal.style.display = "flex";
            renderQuincenaCalendar(currentDate);
        });
    }

    // Cerrar modal
    function closeQuincenaModal() {
        quincenaModal.style.display = "none";
    }

    if (closeBtn) closeBtn.addEventListener("click", closeQuincenaModal);
    if (cancelBtn) cancelBtn.addEventListener("click", closeQuincenaModal);

    // Navegación de meses
    if (prevMonthBtn) {
        prevMonthBtn.addEventListener("click", function () {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderQuincenaCalendar(currentDate);
        });
    }

    if (nextMonthBtn) {
        nextMonthBtn.addEventListener("click", function () {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderQuincenaCalendar(currentDate);
        });
    }

    // Renderizar calendario
    function renderQuincenaCalendar(date) {
        const year = date.getFullYear();
        const month = date.getMonth();

        monthTitle.textContent = `${getMonthName(month)} ${year}`;

        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const startDay = firstDay.getDay();

        // Limpiar celdas de días (excepto headers)
        const dayCells = calendarGrid.querySelectorAll(".quincena-day-cell");
        dayCells.forEach((cell) => cell.remove());

        // Días del mes anterior (celdas vacías)
        for (let i = 0; i < startDay; i++) {
            const emptyCell = document.createElement("div");
            emptyCell.className = "quincena-day-cell disabled";
            calendarGrid.appendChild(emptyCell);
        }

        // Días del mes actual
        for (let day = 1; day <= lastDay.getDate(); day++) {
            const dayCell = document.createElement("div");
            dayCell.className = "quincena-day-cell";
            dayCell.textContent = day;

            // Marcar días de fin de quincena
            if (day === 15 || day === lastDay.getDate()) {
                dayCell.classList.add("quincena-end");
            }

            calendarGrid.appendChild(dayCell);
        }
    }

    function getMonthName(monthIndex) {
        const months = [
            "Enero",
            "Febrero",
            "Marzo",
            "Abril",
            "Mayo",
            "Junio",
            "Julio",
            "Agosto",
            "Septiembre",
            "Octubre",
            "Noviembre",
            "Diciembre",
        ];
        return months[monthIndex];
    }

    // Back to calendar button - navegación Laravel real
    const backToCalendarBtn = document.getElementById("back-to-calendar");
    if (backToCalendarBtn) {
        backToCalendarBtn.addEventListener("click", function (e) {
            e.preventDefault();
            window.location.href = "/recursoshumanos/loadchart/calendar";
        });
    }

    // Period navigation
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

    // Botones de aprobación
    document.querySelectorAll(".btn-approve").forEach((btn) => {
        btn.addEventListener("click", function () {
            const row = this.closest(".employee-row");
            approveEmployee(row);
        });
    });

    // Botones de revisión
    document.querySelectorAll(".btn-review").forEach((btn) => {
        btn.addEventListener("click", function () {
            const row = this.closest(".employee-row");
            markAsReviewed(row);
        });
    });

    // Botón guardar cambios
    const saveBtnElement = document.querySelector(".save-btn");
    if (saveBtnElement) {
        saveBtnElement.addEventListener("click", guardarDatos);
    }

    // Mostrar por defecto la quincena 1
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
        // Obtener todas las columnas de días
        const dayHeaders = document.querySelectorAll(
            ".approval-table .day-header"
        );
        const allRows = document.querySelectorAll(".approval-table tbody tr");
        const daysColumnsHeader = document.getElementById("days-columns");

        if (!dayHeaders.length || !daysColumnsHeader) return;

        let firstDay = quincena === 1 ? 1 : 16;
        let lastDay = quincena === 1 ? 15 : 31;
        let visibleDays = lastDay - firstDay + 1;

        // Actualizar el colspan del encabezado de días
        daysColumnsHeader.colSpan = visibleDays;

        // Para cada header de día
        dayHeaders.forEach((header, index) => {
            const dayNumber = parseInt(header.textContent.split("\n")[0]);
            const shouldShow = dayNumber >= firstDay && dayNumber <= lastDay;

            // Mostrar/ocultar header
            header.style.display = shouldShow ? "" : "none";
        });

        // Mostrar/ocultar las celdas de datos correspondientes en todas las filas
        allRows.forEach((row) => {
            const dataCells = row.querySelectorAll(".data-cell");
            dayHeaders.forEach((header, index) => {
                const dayNumber = parseInt(header.textContent.split("\n")[0]);
                const shouldShow =
                    dayNumber >= firstDay && dayNumber <= lastDay;

                if (dataCells[index]) {
                    dataCells[index].style.display = shouldShow ? "" : "none";
                }
            });
        });

        // Ajustar el ancho de las columnas fijas (Nombre y KPI)
        const fixedColumns = document.querySelectorAll(
            ".employee-info-cell, .activity-label-cell"
        );
        fixedColumns.forEach((col) => {
            col.style.width = "auto";
            col.style.minWidth = "120px";
        });
    }

    function showFullMonth() {
        // Mostrar todos los headers de días
        const dayHeaders = document.querySelectorAll(
            ".approval-table .day-header"
        );
        const allRows = document.querySelectorAll(".approval-table tbody tr");
        const daysColumnsHeader = document.getElementById("days-columns");

        if (!dayHeaders.length || !daysColumnsHeader) return;

        // Restaurar el colspan original
        daysColumnsHeader.colSpan = 31;

        dayHeaders.forEach((header, index) => {
            header.style.display = "";

            // Mostrar todas las celdas de datos
            allRows.forEach((row) => {
                const dataCells = row.querySelectorAll(".data-cell");
                if (dataCells[index]) {
                    dataCells[index].style.display = "";
                }
            });
        });

        // Restaurar el ancho de las columnas fijas
        const fixedColumns = document.querySelectorAll(
            ".employee-info-cell, .activity-label-cell"
        );
        fixedColumns.forEach((col) => {
            col.style.width = "";
            col.style.minWidth = "";
        });
    }

    function approveEmployee(row) {
        // Cambiar el estado a aprobado
        const statusIndicators = row.querySelectorAll(".status-indicator");
        statusIndicators.forEach((indicator) => {
            if (indicator.textContent === "N") {
                indicator.textContent = "A";
                indicator.classList.remove("status-n");
                indicator.classList.add("status-a");
            }
        });

        // Actualizar el botón
        const approveBtn = row.querySelector(".btn-approve");
        if (approveBtn) {
            approveBtn.textContent = "Aprobado";
            approveBtn.classList.add("approved");
            approveBtn.disabled = true;
        }
    }

    function markAsReviewed(row) {
        // Cambiar el estado a revisado
        const statusIndicators = row.querySelectorAll(".status-indicator");
        statusIndicators.forEach((indicator) => {
            if (indicator.textContent === "N") {
                indicator.textContent = "R";
                indicator.classList.remove("status-n");
                indicator.classList.add("status-r");
            }
        });

        // Actualizar el botón
        const reviewBtn = row.querySelector(".btn-review");
        if (reviewBtn) {
            reviewBtn.textContent = "Revisado";
            reviewBtn.classList.add("reviewed");
            reviewBtn.disabled = true;
        }
    }

    function guardarDatos() {
        // Simular envío de datos al servidor
        const saveBtn = document.querySelector(".save-btn");
        if (!saveBtn) return;

        saveBtn.disabled = true;
        saveBtn.innerHTML =
            '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        // Simular retraso de red
        setTimeout(() => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';

            // Mostrar notificación de éxito
            const notification = document.createElement("div");
            notification.className = "notification success";
            notification.innerHTML =
                '<i class="fas fa-check-circle"></i> Cambios guardados correctamente';
            document.body.appendChild(notification);

            // Ocultar notificación después de 3 segundos
            setTimeout(() => {
                notification.classList.add("fade-out");
                setTimeout(() => notification.remove(), 500);
            }, 3000);
        }, 1500);
    }

    // Función para alternar la visibilidad de las filas de actividad
    document.querySelectorAll(".employee-info-cell").forEach((cell) => {
        cell.addEventListener("click", function () {
            const employeeRow = this.closest(".employee-row");
            const activityRows = employeeRow.nextElementSibling;

            if (
                activityRows &&
                activityRows.classList.contains("activity-row")
            ) {
                activityRows.classList.toggle("hidden");
            }
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
