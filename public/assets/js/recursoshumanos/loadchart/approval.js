document.addEventListener('DOMContentLoaded', function() {


 const quincenaModal = document.getElementById('quincena-modal');
    const openBtn = document.getElementById('days-quincena');
    const closeBtn = quincenaModal.querySelector('.quincena-close-btn');
    const cancelBtn = quincenaModal.querySelector('.quincena-cancel-btn');
    const prevMonthBtn = quincenaModal.querySelector('.quincena-prev-month');
    const nextMonthBtn = quincenaModal.querySelector('.quincena-next-month');
    const monthTitle = quincenaModal.querySelector('.quincena-month-title');
    const calendarGrid = quincenaModal.querySelector('.quincena-calendar-grid');

    let currentDate = new Date();

    // Abrir modal
    if(openBtn) {
        openBtn.addEventListener('click', function() {
            quincenaModal.style.display = 'flex';
            renderQuincenaCalendar(currentDate);
        });
    }

    // Cerrar modal
    function closeQuincenaModal() {
        quincenaModal.style.display = 'none';
    }

    if(closeBtn) closeBtn.addEventListener('click', closeQuincenaModal);
    if(cancelBtn) cancelBtn.addEventListener('click', closeQuincenaModal);

    // Navegación de meses
    if(prevMonthBtn) {
        prevMonthBtn.addEventListener('click', function() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderQuincenaCalendar(currentDate);
        });
    }

    if(nextMonthBtn) {
        nextMonthBtn.addEventListener('click', function() {
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
        const dayCells = calendarGrid.querySelectorAll('.quincena-day-cell');
        dayCells.forEach(cell => cell.remove());

        // Días del mes anterior (celdas vacías)
        for(let i = 0; i < startDay; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'quincena-day-cell disabled';
            calendarGrid.appendChild(emptyCell);
        }

        // Días del mes actual
        for(let day = 1; day <= lastDay.getDate(); day++) {
            const dayCell = document.createElement('div');
            dayCell.className = 'quincena-day-cell';
            dayCell.textContent = day;

            // Marcar días de fin de quincena
            if(day === 15 || day === lastDay.getDate()) {
                dayCell.classList.add('quincena-end');
            }

            calendarGrid.appendChild(dayCell);
        }
    }

    function getMonthName(monthIndex) {
        const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                       'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        return months[monthIndex];
    }


    // Back to calendar button - navegación Laravel real
    const backToCalendarBtn = document.getElementById('back-to-calendar');
    if (backToCalendarBtn) {
        backToCalendarBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = '/recursoshumanos/loadchart/calendar';
        });
    }

    // Period navigation
    document.getElementById('quincena1').addEventListener('click', () => {
        showQuincena(1);
        setActiveButton('quincena1');
    });

    document.getElementById('quincena2').addEventListener('click', () => {
        showQuincena(2);
        setActiveButton('quincena2');
    });

    document.getElementById('full-month').addEventListener('click', () => {
        showFullMonth();
        setActiveButton('full-month');
    });

    document.getElementById('prev-period').addEventListener('click', () => {
        alert('Navegando al período anterior');
    });

    document.getElementById('next-period').addEventListener('click', () => {
        alert('Navegando al próximo período');
    });

    // Botones de aprobación
    document.querySelectorAll('.btn-approve').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('.employee-row');
            approveEmployee(row);
        });
    });

    // Botones de revisión
    document.querySelectorAll('.btn-review').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('.employee-row');
            markAsReviewed(row);
        });
    });

    // Botón guardar cambios
    const saveBtnElement = document.querySelector('.save-btn');
    if (saveBtnElement) {
        saveBtnElement.addEventListener('click', guardarDatos);
    }

    // Mostrar por defecto la quincena 1
    showQuincena(1);

    function setActiveButton(buttonId) {
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        const targetBtn = document.getElementById(buttonId);
        if (targetBtn) {
            targetBtn.classList.add('active');
        }
    }

    function showQuincena(quincena) {
        // Obtener todas las columnas de días
        const dayHeaders = document.querySelectorAll('.approval-table .day-header');
        const allRows = document.querySelectorAll('.approval-table tbody tr');
        const daysColumnsHeader = document.getElementById('days-columns');

        if (!dayHeaders.length || !daysColumnsHeader) return;

        let firstDay = quincena === 1 ? 1 : 16;
        let lastDay = quincena === 1 ? 15 : 31;
        let visibleDays = lastDay - firstDay + 1;

        // Actualizar el colspan del encabezado de días
        daysColumnsHeader.colSpan = visibleDays;

        // Para cada header de día
        dayHeaders.forEach((header, index) => {
            const dayNumber = parseInt(header.textContent.split('\n')[0]);
            const shouldShow = (dayNumber >= firstDay && dayNumber <= lastDay);

            // Mostrar/ocultar header
            header.style.display = shouldShow ? '' : 'none';
        });

        // Mostrar/ocultar las celdas de datos correspondientes en todas las filas
        allRows.forEach(row => {
            const dataCells = row.querySelectorAll('.data-cell');
            dayHeaders.forEach((header, index) => {
                const dayNumber = parseInt(header.textContent.split('\n')[0]);
                const shouldShow = (dayNumber >= firstDay && dayNumber <= lastDay);

                if (dataCells[index]) {
                    dataCells[index].style.display = shouldShow ? '' : 'none';
                }
            });
        });

        // Ajustar el ancho de las columnas fijas (Nombre y KPI)
        const fixedColumns = document.querySelectorAll('.employee-info-cell, .activity-label-cell');
        fixedColumns.forEach(col => {
            col.style.width = 'auto';
            col.style.minWidth = '120px';
        });
    }

    function showFullMonth() {
        // Mostrar todos los headers de días
        const dayHeaders = document.querySelectorAll('.approval-table .day-header');
        const allRows = document.querySelectorAll('.approval-table tbody tr');
        const daysColumnsHeader = document.getElementById('days-columns');

        if (!dayHeaders.length || !daysColumnsHeader) return;

        // Restaurar el colspan original
        daysColumnsHeader.colSpan = 31;

        dayHeaders.forEach((header, index) => {
            header.style.display = '';

            // Mostrar todas las celdas de datos
            allRows.forEach(row => {
                const dataCells = row.querySelectorAll('.data-cell');
                if (dataCells[index]) {
                    dataCells[index].style.display = '';
                }
            });
        });

        // Restaurar el ancho de las columnas fijas
        const fixedColumns = document.querySelectorAll('.employee-info-cell, .activity-label-cell');
        fixedColumns.forEach(col => {
            col.style.width = '';
            col.style.minWidth = '';
        });
    }

    function approveEmployee(row) {
        // Cambiar el estado a aprobado
        const statusIndicators = row.querySelectorAll('.status-indicator');
        statusIndicators.forEach(indicator => {
            if (indicator.textContent === 'N') {
                indicator.textContent = 'A';
                indicator.classList.remove('status-n');
                indicator.classList.add('status-a');
            }
        });

        // Actualizar el botón
        const approveBtn = row.querySelector('.btn-approve');
        if (approveBtn) {
            approveBtn.textContent = 'Aprobado';
            approveBtn.classList.add('approved');
            approveBtn.disabled = true;
        }
    }

    function markAsReviewed(row) {
        // Cambiar el estado a revisado
        const statusIndicators = row.querySelectorAll('.status-indicator');
        statusIndicators.forEach(indicator => {
            if (indicator.textContent === 'N') {
                indicator.textContent = 'R';
                indicator.classList.remove('status-n');
                indicator.classList.add('status-r');
            }
        });

        // Actualizar el botón
        const reviewBtn = row.querySelector('.btn-review');
        if (reviewBtn) {
            reviewBtn.textContent = 'Revisado';
            reviewBtn.classList.add('reviewed');
            reviewBtn.disabled = true;
        }
    }

    function guardarDatos() {
        // Simular envío de datos al servidor
        const saveBtn = document.querySelector('.save-btn');
        if (!saveBtn) return;

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        // Simular retraso de red
        setTimeout(() => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';

            // Mostrar notificación de éxito
            const notification = document.createElement('div');
            notification.className = 'notification success';
            notification.innerHTML = '<i class="fas fa-check-circle"></i> Cambios guardados correctamente';
            document.body.appendChild(notification);

            // Ocultar notificación después de 3 segundos
            setTimeout(() => {
                notification.classList.add('fade-out');
                setTimeout(() => notification.remove(), 500);
            }, 3000);
        }, 1500);
    }

    // Función para alternar la visibilidad de las filas de actividad
    document.querySelectorAll('.employee-info-cell').forEach(cell => {
        cell.addEventListener('click', function() {
            const employeeRow = this.closest('.employee-row');
            const activityRows = employeeRow.nextElementSibling;

            if (activityRows && activityRows.classList.contains('activity-row')) {
                activityRows.classList.toggle('hidden');
            }
        });
    });
});


