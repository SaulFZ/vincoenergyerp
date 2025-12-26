@extends('modulos.recursoshumanos.sistemas.loadchart.index')

@section('content')
<style>
    /* --- Variables Globales --- */
    :root {
        --dark-gray: #2d3748;
        --medium-gray: #4a5568;
        --light-gray: #e2e8f0;
        --background-gray: #f7fafc;
        --secondary-blue: #34495e;
        --white: #ffffff;
        --primary-color: var(--secondary-blue);
        --text-dark: var(--dark-gray);
        --text-medium: var(--medium-gray);
        --border-color: var(--light-gray);
        --success-color: #38a169;
        --danger-color: #e53e3e;
        --warning-color: #d69e2e;
        --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    body {
        font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
        background-color: var(--background-gray);
        color: var(--text-dark);
        line-height: 1.6;
    }

    .dashboard-container {
        max-width: 100% !important;
        margin: 0 auto !important;
        padding: 20px 15px !important;
    }

    /* --- Tabs de Navegación --- */
    .data-tabs {
        display: flex;
        gap: 5px;
        margin-bottom: 20px;
        border-bottom: 2px solid var(--light-gray);
    }

    .tab-btn {
        padding: 12px 24px;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        font-weight: 600;
        color: var(--medium-gray);
        cursor: pointer;
        transition: all 0.3s;
        margin-bottom: -2px;
    }

    .tab-btn.active {
        color: var(--primary-color);
        border-bottom: 3px solid var(--primary-color);
    }

    .tab-btn:hover:not(.active) {
        background-color: rgba(0,0,0,0.02);
    }

    /* --- Secciones y Tarjetas --- */
    .table-section {
        display: none;
        animation: fadeIn 0.4s ease-out;
    }

    .table-section.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .table-card {
        background: var(--white);
        border-radius: 8px;
        box-shadow: var(--card-shadow);
        padding: 25px;
        border: 1px solid var(--light-gray);
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--border-color);
    }

    .table-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--dark-gray);
        margin: 0;
    }

    /* --- Área de Filtros Unificada --- */
    .table-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 25px;
        padding: 20px;
        background-color: #f8fafc;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        align-items: flex-end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        min-width: 140px;
        flex: 1;
    }

    /* LÍNEA DIVISORIA SOLICITADA */
    .filter-divider {
        width: 1px;
        height: 50px;
        background-color: #cbd5e1;
        margin: 0 15px;
        align-self: flex-end;
        margin-bottom: 2px;
    }

    .filter-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--medium-gray);
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .select-field, .input-field {
        padding: 9px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 13px;
        background-color: var(--white);
        color: var(--dark-gray);
        width: 100%;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .select-field:focus, .input-field:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(52, 73, 94, 0.1);
    }

    /* Botones */
    .btn {
        padding: 9px 18px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 38px;
        border: none;
        transition: transform 0.1s;
    }

    .btn:active { transform: translateY(1px); }

    .btn-primary { background-color: var(--primary-color); color: var(--white); }
    .btn-primary:hover { background-color: var(--dark-gray); }

    .btn-secondary { background-color: #e2e8f0; color: var(--medium-gray); border: 1px solid #cbd5e1; }
    .btn-secondary:hover { background-color: #cbd5e1; color: var(--dark-gray); }

    /* --- Tablas --- */
    .table-container {
        overflow-x: auto;
        border: 1px solid var(--border-color);
        border-radius: 6px;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1000px;
        background-color: var(--white);
    }

    .data-table th {
        background-color: var(--primary-color);
        color: var(--white);
        padding: 12px 15px;
        font-size: 0.85rem;
        text-align: center;
        white-space: nowrap;
        position: sticky;
        top: 0;
    }

    .data-table td {
        padding: 10px 15px;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.9rem;
        text-align: center;
        vertical-align: middle;
        color: var(--text-dark);
    }

    .data-table tr:hover { background-color: #f1f5f9; }
    .data-table tr:nth-child(even) { background-color: #fcfcfc; }

    /* Estilos de Moneda */
    .currency-cell {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 2px;
    }
    .currency-mxn { font-family: 'Roboto Mono', monospace; font-weight: 700; color: var(--dark-gray); }
    .currency-usd { font-family: 'Roboto Mono', monospace; font-size: 0.85em; color: var(--success-color); }

    .text-right { text-align: right !important; }
    .text-left { text-align: left !important; }
    .text-center { text-align: center !important; }

    .total-row { background-color: #edf2f7 !important; font-weight: bold; }
    .total-row td { border-top: 2px solid #cbd5e1; }

    .positive { color: var(--success-color); font-weight: 700; }
    .negative { color: var(--danger-color); font-weight: 700; }

    /* Responsive */
    @media (max-width: 1200px) {
        .filter-divider { display: none; }
        .table-filters { flex-direction: column; align-items: stretch; }
        .filter-group { width: 100%; }
        .btn { width: 100%; }
    }
</style>

<main class="dashboard-container">
    <div class="data-tabs">
        <button class="tab-btn active" data-tab="bonos-periodicos">Bonos por Periodo</button>
        <button class="tab-btn" data-tab="record-bonos">Record de Bonos</button>
        <button class="tab-btn" data-tab="resumen-general">Resumen General</button>
    </div>

    <section id="bonos-periodicos" class="table-section active">
        <div class="table-card">
            <div class="table-header">
                <h2 class="table-title">Bonos por Periodo (MXN Principal)</h2>
                <button class="btn btn-primary" onclick="exportTable('periodos-table', 'Bonos_Periodo_2025')">
                    <i class="fas fa-download"></i> Exportar
                </button>
            </div>

            <div class="table-filters" id="filters-periodos">
                <div class="filter-group">
                    <label class="filter-label">Año</label>
                    <select class="select-field year-selector">
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Tipo de Periodo</label>
                    <select class="select-field type-selector">
                        <option value="quincena">Quincena</option>
                        <option value="mes">Mes</option>
                        <option value="trimestre">Trimestre</option>
                        <option value="anual">Anual</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Periodo Específico</label>
                    <select class="select-field spec-selector">
                        <option value="todos">Todas las Quincenas</option>
                        </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Departamento</label>
                    <select class="select-field dept-selector">
                        <option value="todos">Todos los Departamentos</option>
                        <option value="operaciones">Operaciones</option>
                        <option value="administracion">Administración</option>
                        <option value="suministros">Suministros</option>
                        <option value="geociencias">Geociencias</option>
                        <option value="ventas">Ventas</option>
                        <option value="mantenimiento">Mantenimiento</option>
                        <option value="ohse">OHSE</option>
                    </select>
                </div>

                <div class="filter-divider"></div>

                <div class="filter-group">
                    <label class="filter-label">Filtrar por Mes (Visual)</label>
                    <select id="mes-periodo-filter" class="select-field">
                        <option value="todos">Todos</option>
                        <option value="enero">Enero</option>
                        <option value="febrero">Febrero</option>
                        <option value="marzo">Marzo</option>
                        <option value="abril">Abril</option>
                        <option value="mayo">Mayo</option>
                        <option value="junio">Junio</option>
                        <option value="julio">Julio</option>
                        <option value="agosto">Agosto</option>
                        <option value="septiembre">Septiembre</option>
                        <option value="octubre">Octubre</option>
                        <option value="noviembre">Noviembre</option>
                        <option value="diciembre">Diciembre</option>
                    </select>
                </div>

                <div class="filter-group" style="flex-direction: row; gap: 10px;">
                    <button class="btn btn-primary apply-filters-btn">
                        <i class="fas fa-filter"></i> Aplicar
                    </button>
                    <button class="btn btn-secondary reset-filters-btn">
                        <i class="fas fa-redo"></i> Limpiar
                    </button>
                </div>
            </div>

            <div class="table-container">
                <table id="periodos-table" class="data-table">
                    <thead>
                        <tr>
                            <th class="text-center">Periodo</th>
                            <th class="text-center">Mes</th>
                            <th class="text-center">HC</th>
                            <th class="text-right">Total Bonos (MXN)</th>
                            <th class="text-right">Total (USD)</th>
                            <th class="text-right">Promedio x HC (MXN)</th>
                            <th class="text-center">% Total</th>
                            <th class="text-center">T.C.</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>

    <section id="record-bonos" class="table-section">
        <div class="table-card">
            <div class="table-header">
                <h2 class="table-title">Record de Bonos por Empleado</h2>
                <button class="btn btn-primary" onclick="exportTable('record-table', 'Record_Bonos')">
                    <i class="fas fa-download"></i> Exportar
                </button>
            </div>

            <div class="table-filters" id="filters-record">
                <div class="filter-group">
                    <label class="filter-label">Año</label>
                    <select class="select-field year-selector">
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Departamento</label>
                    <select class="select-field dept-selector" id="departamento-record-filter">
                        <option value="todos">Todos</option>
                        <option value="operaciones">Operaciones</option>
                        <option value="administracion">Administración</option>
                        <option value="suministros">Suministros</option>
                        <option value="geociencias">Geociencias</option>
                        <option value="ohse">OHSE</option>
                    </select>
                </div>

                <div class="filter-divider"></div>

                <div class="filter-group">
                    <label class="filter-label">Mínimo (MXN)</label>
                    <input type="number" id="bono-minimo-filter" class="input-field" placeholder="0">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Máximo (MXN)</label>
                    <input type="number" id="bono-maximo-filter" class="input-field" placeholder="500000">
                </div>

                <div class="filter-group" style="flex-direction: row; gap: 10px;">
                    <button class="btn btn-primary apply-filters-btn">Aplicar</button>
                    <button class="btn btn-secondary reset-filters-btn">Limpiar</button>
                </div>
            </div>

            <div class="table-container">
                <table id="record-table" class="data-table">
                    <thead>
                        <tr>
                            <th class="text-left">ID</th>
                            <th class="text-left">Nombre</th>
                            <th class="text-center">Depto</th>
                            <th class="text-right">Bono (MXN)</th>
                            <th class="text-right">Bono (USD)</th>
                            <th class="text-right">% Total</th>
                            <th class="text-right">Promedio Depto</th>
                            <th class="text-right">Diferencia</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>

    <section id="resumen-general" class="table-section">
        <div class="table-card">
            <div class="table-header">
                <h2 class="table-title">Resumen General</h2>
                <button class="btn btn-primary" onclick="exportTable('resumen-table', 'Resumen_General')">
                    <i class="fas fa-download"></i> Exportar
                </button>
            </div>

            <div class="table-filters" id="filters-resumen">
                <div class="filter-group">
                    <label class="filter-label">Año</label>
                    <select class="select-field year-selector">
                        <option value="2025">2025</option>
                    </select>
                </div>

                <div class="filter-divider"></div>

                <div class="filter-group">
                    <label class="filter-label">Agrupación</label>
                    <select id="resumen-periodo-filter" class="select-field">
                        <option value="anual">Anual</option>
                        <option value="trimestral">Trimestral</option>
                        <option value="mensual">Mensual</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Moneda Base</label>
                    <select id="resumen-moneda-filter" class="select-field">
                        <option value="mxn">MXN</option>
                        <option value="usd">USD</option>
                    </select>
                </div>

                <div class="filter-group" style="flex-direction: row; gap: 10px;">
                    <button class="btn btn-primary apply-filters-btn">Actualizar</button>
                </div>
            </div>

            <div class="table-container">
                <table id="resumen-table" class="data-table">
                    <thead>
                        <tr>
                            <th class="text-left">Periodo</th>
                            <th class="text-right">Total (MXN)</th>
                            <th class="text-right">Total (USD)</th>
                            <th class="text-right">HC Promedio</th>
                            <th class="text-right">Bonos x HC (MXN)</th>
                            <th class="text-right">Tipo Cambio</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Configuración Global ---
    const tasaCambio = 18.50; // Tasa fija para conversiones

    // Datos de ejemplo (Fuente: MXN)
    const datosQuincena = [
        { quincena: 1, mes: 'Enero', bonos: 4475092.64, hc: 104 },
        { quincena: 2, mes: 'Febrero', bonos: 9006180.36, hc: 104 },
        { quincena: 3, mes: 'Marzo', bonos: 7443761.75, hc: 103 },
        { quincena: 4, mes: 'Abril', bonos: 4769956.75, hc: 102 },
        { quincena: 5, mes: 'Mayo', bonos: 7719199.00, hc: 101 },
        { quincena: 6, mes: 'Junio', bonos: 7284478.43, hc: 101 },
        { quincena: 7, mes: 'Julio', bonos: 5536865.00, hc: 101 },
        { quincena: 8, mes: 'Agosto', bonos: 5638100.70, hc: 86 }
    ];

    const datosRecord = [
        { id: 'V10012', nombre: 'MARCOS RUIZ', departamento: 'ADMINISTRACIÓN', bono: 231250.00 },
        { id: 'V10024', nombre: 'PEDRO TAXILAGA', departamento: 'SUMINISTROS', bono: 333000.00 },
        { id: 'V10026', nombre: 'JESUS MORALES', departamento: 'OPERACIONES', bono: 178710.00 },
        { id: 'V10050', nombre: 'DIANA ARZAT', departamento: 'GEOCIENCIAS', bono: 120120.50 }
    ];

    // --- Lógica de Navegación (Tabs) ---
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tableSections = document.querySelectorAll('.table-section');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tableSections.forEach(section => section.classList.remove('active'));

            button.classList.add('active');
            const tabId = button.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');

            // Actualizar datos al cambiar tab
            updateTableData(tabId);
        });
    });

    // --- Funciones de Utilidad ---
    function formatCurrency(amount, currency = 'MXN') {
        return new Intl.NumberFormat(currency === 'USD' ? 'en-US' : 'es-MX', {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 2
        }).format(amount);
    }

    function convertirMxnAUsd(mxn) {
        return mxn / tasaCambio;
    }

    // --- Lógica de Filtros Dinámicos (Llenado de Selects) ---
    function initFilters(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const typeSel = container.querySelector('.type-selector');
        const specSel = container.querySelector('.spec-selector');
        const applyBtn = container.querySelector('.apply-filters-btn');
        const resetBtn = container.querySelector('.reset-filters-btn');

        // Llenar "Periodo Específico" basado en "Tipo"
        if (typeSel && specSel) {
            typeSel.addEventListener('change', function() {
                const tipo = this.value;
                specSel.innerHTML = '<option value="todos">Todos</option>';

                if (tipo === 'quincena') {
                    for(let i=1; i<=24; i++) {
                        specSel.innerHTML += `<option value="${i}">Quincena ${i}</option>`;
                    }
                } else if (tipo === 'mes') {
                    ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'].forEach(m => {
                        specSel.innerHTML += `<option value="${m.toLowerCase()}">${m}</option>`;
                    });
                } else if (tipo === 'trimestre') {
                    for(let i=1; i<=4; i++) {
                        specSel.innerHTML += `<option value="${i}">Trimestre ${i}</option>`;
                    }
                }
            });
            // Inicializar
            typeSel.dispatchEvent(new Event('change'));
        }

        // Botones
        if (applyBtn) {
            applyBtn.addEventListener('click', () => {
                const sectionId = container.closest('section').id;
                updateTableData(sectionId);
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                container.querySelectorAll('select, input').forEach(el => {
                    if (el.tagName === 'SELECT') el.selectedIndex = 0;
                    else el.value = '';
                });
                if (typeSel) typeSel.dispatchEvent(new Event('change'));
                const sectionId = container.closest('section').id;
                updateTableData(sectionId);
            });
        }
    }

    // Inicializar filtros para cada sección
    ['filters-periodos', 'filters-record', 'filters-resumen'].forEach(initFilters);

    // --- Actualización de Tablas ---
    function updateTableData(sectionId) {
        if (sectionId === 'bonos-periodicos') renderPeriodosTable();
        else if (sectionId === 'record-bonos') renderRecordTable();
        else if (sectionId === 'resumen-general') renderResumenTable();
    }

    function renderPeriodosTable() {
        const tbody = document.querySelector('#periodos-table tbody');
        tbody.innerHTML = '';

        // Obtener valores de filtros del bloque "filters-periodos"
        const container = document.getElementById('filters-periodos');
        const type = container.querySelector('.type-selector').value;
        const spec = container.querySelector('.spec-selector').value;
        const mesFilter = document.getElementById('mes-periodo-filter').value;

        // Filtrar datos (Simplificado para el ejemplo)
        let data = [...datosQuincena];

        if (type === 'quincena' && spec !== 'todos') {
            data = data.filter(d => d.quincena == spec);
        } else if (type === 'mes' && spec !== 'todos') {
            data = data.filter(d => d.mes.toLowerCase() === spec);
        }

        if (mesFilter !== 'todos') {
            data = data.filter(d => d.mes.toLowerCase() === mesFilter);
        }

        let totalMxn = 0, totalUsd = 0;

        data.forEach(item => {
            const usd = convertirMxnAUsd(item.bonos);
            totalMxn += item.bonos;
            totalUsd += usd;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${type === 'quincena' ? 'Q'+item.quincena : item.mes}</td>
                <td>${item.mes}</td>
                <td>${item.hc}</td>
                <td class="text-right">
                    <div class="currency-cell">
                        <span class="currency-mxn">${formatCurrency(item.bonos, 'MXN')}</span>
                    </div>
                </td>
                <td class="text-right">
                    <span class="currency-usd">${formatCurrency(usd, 'USD')}</span>
                </td>
                <td class="text-right currency-mxn">${formatCurrency(item.bonos/item.hc, 'MXN')}</td>
                <td>-</td>
                <td>${tasaCambio.toFixed(2)}</td>
            `;
            tbody.appendChild(tr);
        });

        // Fila Total
        if (data.length > 0) {
            const trTotal = document.createElement('tr');
            trTotal.className = 'total-row';
            trTotal.innerHTML = `
                <td colspan="3">TOTAL</td>
                <td class="text-right currency-mxn">${formatCurrency(totalMxn, 'MXN')}</td>
                <td class="text-right currency-usd">${formatCurrency(totalUsd, 'USD')}</td>
                <td colspan="3"></td>
            `;
            tbody.appendChild(trTotal);
        }
    }

    function renderRecordTable() {
        const tbody = document.querySelector('#record-table tbody');
        tbody.innerHTML = '';

        const container = document.getElementById('filters-record');
        const depto = container.querySelector('.dept-selector').value;
        const min = parseFloat(document.getElementById('bono-minimo-filter').value) || 0;
        const max = parseFloat(document.getElementById('bono-maximo-filter').value) || Infinity;

        let filtered = datosRecord.filter(item => {
            const matchDepto = depto === 'todos' || item.departamento.toLowerCase() === depto.toLowerCase();
            const matchVal = item.bono >= min && item.bono <= max;
            return matchDepto && matchVal;
        });

        const totalMxn = filtered.reduce((sum, i) => sum + i.bono, 0);

        filtered.forEach(item => {
            const usd = convertirMxnAUsd(item.bono);
            const pct = totalMxn > 0 ? (item.bono / totalMxn * 100) : 0;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-left">${item.id}</td>
                <td class="text-left">${item.nombre}</td>
                <td>${item.departamento}</td>
                <td class="text-right currency-mxn">${formatCurrency(item.bono, 'MXN')}</td>
                <td class="text-right currency-usd">${formatCurrency(usd, 'USD')}</td>
                <td class="text-right">${pct.toFixed(2)}%</td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderResumenTable() {
        const tbody = document.querySelector('#resumen-table tbody');
        tbody.innerHTML = '';
        // Lógica simple de resumen
        let totalMxn = datosQuincena.reduce((sum, i) => sum + i.bonos, 0);
        let totalUsd = convertirMxnAUsd(totalMxn);

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="text-left">2025 Anual</td>
            <td class="text-right currency-mxn">${formatCurrency(totalMxn, 'MXN')}</td>
            <td class="text-right currency-usd">${formatCurrency(totalUsd, 'USD')}</td>
            <td class="text-right">98</td>
            <td class="text-right currency-mxn">${formatCurrency(totalMxn/98, 'MXN')}</td>
            <td class="text-right">${tasaCambio.toFixed(2)}</td>
        `;
        tbody.appendChild(tr);
    }

    // Exportar (Placeholder)
    window.exportTable = function(tableId, filename) {
        alert("Exportando " + filename + " a CSV...");
    };

    // Inicializar primera tabla
    renderPeriodosTable();
});
</script>
@endsection
