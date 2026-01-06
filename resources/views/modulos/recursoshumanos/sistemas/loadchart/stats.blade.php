@extends('modulos.recursoshumanos.sistemas.loadchart.index')

@section('content')
<style>
    /* --- Variables Globales --- */
    :root {
        --primary-color: #34495e;
        --secondary-color: #2c3e50;
        --accent-color: #3498db;
        --text-dark: #2d3748;
        --text-medium: #4a5568;
        --bg-light: #f8fafc;
        --white: #ffffff;
        --border-color: #e2e8f0;
        --success: #27ae60;
        --danger: #e74c3c;
        --warning: #f39c12;
    }

    body {
        font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        background-color: var(--bg-light);
        color: var(--text-dark);
        font-size: 0.9rem;
    }

    .dashboard-container {
        max-width: 100% !important;
        padding: 20px !important;
    }

    /* --- Tabs --- */
    .data-tabs {
        display: flex;
        gap: 5px;
        margin-bottom: 20px;
        border-bottom: 2px solid var(--border-color);
    }

    .tab-btn {
        padding: 12px 25px;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        font-weight: 600;
        color: var(--text-medium);
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
        margin-bottom: -2px;
    }

    .tab-btn:hover { color: var(--primary-color); background: rgba(0,0,0,0.02); }
    .tab-btn.active { color: var(--primary-color); border-bottom-color: var(--primary-color); }

    /* --- Sections & Cards --- */
    .table-section { display: none; animation: fadeIn 0.3s ease-out; }
    .table-section.active { display: block; }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

    .table-card {
        background: var(--white);
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        border: 1px solid var(--border-color);
        padding: 20px;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--border-color);
    }

    .table-title { font-size: 1.25rem; font-weight: 700; color: var(--secondary-color); margin: 0; }

    /* --- Unified Filters Area --- */
    .table-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        padding: 20px;
        background-color: #f1f5f9;
        border-radius: 8px;
        margin-bottom: 25px;
        align-items: flex-end;
        border: 1px solid var(--border-color);
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 140px;
    }

    .filter-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-medium);
        margin-bottom: 5px;
        letter-spacing: 0.5px;
    }

    .select-field, .input-field {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background-color: var(--white);
        font-size: 13px;
        color: var(--text-dark);
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .select-field:focus, .input-field:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(52, 73, 94, 0.1);
    }

    .select-field:not(:focus):hover, .input-field:not(:focus):hover {
        border-color: #94a3b8;
    }

    /* --- The Divider Line --- */
    .filter-divider {
        width: 1px;
        background-color: #cbd5e1;
        height: 50px;
        margin: 0 15px;
        align-self: center;
    }

    /* --- Buttons --- */
    .btn {
        padding: 9px 18px;
        border-radius: 6px;
        border: none;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: transform 0.1s, opacity 0.2s;
        height: 37px;
    }

    .btn:active { transform: translateY(1px); }
    .btn-primary { background-color: var(--primary-color); color: var(--white); }
    .btn-secondary { background-color: #e2e8f0; color: var(--text-medium); border: 1px solid #cbd5e1; }
    .btn:hover { opacity: 0.9; }

    /* --- Table Styling --- */
    .table-container {
        overflow-x: auto;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        max-height: 500px;
        overflow-y: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1000px;
        background: var(--white);
        font-size: 0.85rem;
    }

    .data-table th {
        background-color: var(--primary-color);
        color: var(--white);
        font-weight: 600;
        padding: 12px 15px;
        text-align: center;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 10;
        border-bottom: 2px solid var(--primary-color);
    }

    .data-table td {
        padding: 10px 15px;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
        color: var(--text-dark);
    }

    .data-table tr:hover { background-color: #f8fafc; }
    .data-table tr:nth-child(even) { background-color: #fcfcfc; }

    /* Utility Classes */
    .text-center { text-align: center !important; }
    .text-right { text-align: right !important; }
    .text-left { text-align: left !important; }

    .currency { font-family: 'Roboto Mono', monospace; font-weight: 500; }
    .currency-mxn { color: var(--secondary-color); font-weight: 600; }
    .currency-usd { color: var(--accent-color); font-size: 0.85em; font-weight: 500; }

    .dual-value {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        line-height: 1.3;
        gap: 2px;
    }
    .total-row { background-color: #edf2f7 !important; font-weight: 700; }
    .total-row td { border-top: 2px solid var(--border-color); }
    .positive { color: var(--success); font-weight: 600; }
    .negative { color: var(--danger); font-weight: 600; }
    .warning { color: var(--warning); }

    /* Status indicators */
    .status-indicator {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 6px;
    }
    .status-active { background-color: var(--success); }
    .status-inactive { background-color: var(--danger); }
    .status-pending { background-color: var(--warning); }

    /* Loading animation */
    .loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid var(--primary-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .filter-divider { display: none; width: 100%; height: 1px; margin: 10px 0; }
        .table-filters { flex-direction: column; align-items: stretch; }
        .filter-group { width: 100%; }
    }

    @media (max-width: 768px) {
        .table-header { flex-direction: column; align-items: flex-start; gap: 15px; }
        .table-title { font-size: 1.1rem; }
        .btn { width: 100%; justify-content: center; }
        .data-table { min-width: 800px; }
    }

    /* Tooltip */
    .tooltip {
        position: relative;
        display: inline-block;
    }

    .tooltip .tooltiptext {
        visibility: hidden;
        width: 200px;
        background-color: var(--secondary-color);
        color: var(--white);
        text-align: center;
        border-radius: 4px;
        padding: 5px;
        position: absolute;
        z-index: 1000;
        bottom: 125%;
        left: 50%;
        transform: translateX(-50%);
        opacity: 0;
        transition: opacity 0.3s;
        font-size: 0.8rem;
    }

    .tooltip:hover .tooltiptext {
        visibility: visible;
        opacity: 1;
    }
</style>

<main class="dashboard-container">

    <div class="data-tabs">
        <button class="tab-btn active" data-tab="bonos-periodicos">Bonos por Periodo</button>
        <button class="tab-btn" data-tab="record-bonos">Record de Bonos</button>
        <button class="tab-btn" data-tab="resumen-general">Resumen General</button>
    </div>

    <!-- Sección 1: Bonos por Periodo -->
    <section id="bonos-periodicos" class="table-section active">
        <div class="table-card">
            <div class="table-header">
                <h2 class="table-title">Bonos por Periodo <span id="current-year-1">2025</span></h2>
                <button class="btn btn-primary" onclick="exportTable('periodos-table', 'Bonos_Periodo')">
                    <i class="fas fa-download"></i> Exportar
                </button>
            </div>

            <div class="table-filters">
                <div class="filter-group">
                    <label class="filter-label">Año</label>
                    <select id="p1-year" class="select-field">
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Tipo de Periodo</label>
                    <select id="p1-type" class="select-field">
                        <option value="quincena">Quincena</option>
                        <option value="mes">Mes</option>
                        <option value="trimestre">Trimestre</option>
                        <option value="anual">Anual</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Periodo Específico</label>
                    <select id="p1-spec" class="select-field">
                        <option value="todos">Todos</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Departamento</label>
                    <select id="p1-dept" class="select-field">
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
                    <label class="filter-label">Filtrar por Mes</label>
                    <select id="p1-mes-filter" class="select-field">
                        <option value="todos">Todos los Meses</option>
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

                <div class="filter-group">
                    <label class="filter-label">Ordenar por</label>
                    <select id="p1-sort" class="select-field">
                        <option value="periodo">Periodo (Ascendente)</option>
                        <option value="periodo-desc">Periodo (Descendente)</option>
                        <option value="bonos">Total Bonos (Mayor a menor)</option>
                        <option value="bonos-asc">Total Bonos (Menor a mayor)</option>
                    </select>
                </div>

                <div class="filter-group" style="flex: 0 0 auto; min-width: auto;">
                    <label class="filter-label">&nbsp;</label>
                    <button class="btn btn-secondary" id="p1-reset" title="Restablecer filtros">
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
                            <th class="text-right">Total en USD</th>
                            <th class="text-right">Promedio x HC</th>
                            <th class="text-center">% del Total</th>
                            <th class="text-center">Tipo Cambio</th>
                        </tr>
                    </thead>
                    <tbody id="periodos-tbody"></tbody>
                    <tfoot id="periodos-tfoot"></tfoot>
                </table>
            </div>
        </div>
    </section>

    <!-- Sección 2: Record de Bonos -->
    <section id="record-bonos" class="table-section">
        <div class="table-card">
            <div class="table-header">
                <h2 class="table-title">Record de Bonos por Empleado <span id="current-year-2">2025</span></h2>
                <button class="btn btn-primary" onclick="exportTable('record-table', 'Record_Bonos')">
                    <i class="fas fa-download"></i> Exportar
                </button>
            </div>

            <div class="table-filters">
                <div class="filter-group">
                    <label class="filter-label">Año</label>
                    <select id="p2-year" class="select-field">
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Departamento</label>
                    <select id="p2-dept" class="select-field">
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
                    <label class="filter-label">Bono Mínimo (MXN)</label>
                    <input type="number" id="p2-min" class="input-field" placeholder="Mínimo" min="0" step="1000">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Bono Máximo (MXN)</label>
                    <input type="number" id="p2-max" class="input-field" placeholder="Máximo" min="0" step="1000">
                </div>

                <div class="filter-group">
                    <label class="filter-label">Ordenar por</label>
                    <select id="p2-sort" class="select-field">
                        <option value="bono-desc">Bono (Mayor a menor)</option>
                        <option value="bono-asc">Bono (Menor a mayor)</option>
                        <option value="nombre">Nombre (A-Z)</option>
                        <option value="departamento">Departamento</option>
                    </select>
                </div>

                <div class="filter-group" style="flex: 0 0 auto; min-width: auto;">
                    <label class="filter-label">&nbsp;</label>
                    <button class="btn btn-secondary" id="p2-reset" title="Restablecer filtros">
                        <i class="fas fa-redo"></i> Limpiar
                    </button>
                </div>
            </div>

            <div class="table-container">
                <table id="record-table" class="data-table">
                    <thead>
                        <tr>
                            <th class="text-left">ID</th>
                            <th class="text-left">Nombre</th>
                            <th class="text-center">Departamento</th>
                            <th class="text-right">Bono (MXN)</th>
                            <th class="text-right">Bono (USD)</th>
                            <th class="text-center">% del Total</th>
                            <th class="text-right">Promedio Depto</th>
                            <th class="text-right">Diferencia</th>
                        </tr>
                    </thead>
                    <tbody id="record-tbody"></tbody>
                    <tfoot id="record-tfoot"></tfoot>
                </table>
            </div>
        </div>
    </section>

    <!-- Sección 3: Resumen General -->
    <section id="resumen-general" class="table-section">
        <div class="table-card">
            <div class="table-header">
                <h2 class="table-title">Resumen General <span id="current-year-3">2025</span></h2>
                <button class="btn btn-primary" onclick="exportTable('resumen-table', 'Resumen_General')">
                    <i class="fas fa-download"></i> Exportar
                </button>
            </div>

            <div class="table-filters">
                <div class="filter-group">
                    <label class="filter-label">Año</label>
                    <select id="p3-year" class="select-field">
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Departamento</label>
                    <select id="p3-dept" class="select-field">
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
                    <label class="filter-label">Vista</label>
                    <select id="p3-view" class="select-field">
                        <option value="anual">Anual</option>
                        <option value="trimestral">Trimestral</option>
                        <option value="mensual">Mensual</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Moneda Principal</label>
                    <select id="p3-currency" class="select-field">
                        <option value="mxn">MXN (Pesos)</option>
                        <option value="usd">USD (Dólares)</option>
                    </select>
                </div>

                <div class="filter-group" style="flex: 0 0 auto; min-width: auto;">
                    <label class="filter-label">&nbsp;</label>
                    <button class="btn btn-secondary" id="p3-reset" title="Restablecer filtros">
                        <i class="fas fa-redo"></i> Limpiar
                    </button>
                </div>
            </div>

            <div class="table-container">
                <table id="resumen-table" class="data-table">
                    <thead>
                        <tr>
                            <th class="text-left">Periodo</th>
                            <th class="text-right">Total Bonos (MXN)</th>
                            <th class="text-right">Total en USD</th>
                            <th class="text-center">HC Promedio</th>
                            <th class="text-right">Bonos x HC (MXN)</th>
                            <th class="text-center">Tipo Cambio</th>
                        </tr>
                    </thead>
                    <tbody id="resumen-tbody"></tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Configuración y Datos ---
        const tasaCambio = 18.50; // 1 USD = 18.50 MXN

        // Datos en Pesos MXN (más completos)
        const datosQuincena = [
            { quincena: 1, mes: 'Enero', bonos: 4475092.64, hc: 104, tipo: 'HREFI' },
            { quincena: 2, mes: 'Febrero', bonos: 9006180.36, hc: 104, tipo: 'HREFI' },
            { quincena: 3, mes: 'Marzo', bonos: 7443761.75, hc: 103, tipo: 'HREFI' },
            { quincena: 4, mes: 'Abril', bonos: 4769956.75, hc: 102, tipo: 'HREFI' },
            { quincena: 5, mes: 'Mayo', bonos: 7719199.00, hc: 101, tipo: 'HREFI' },
            { quincena: 6, mes: 'Junio', bonos: 7284478.43, hc: 101, tipo: 'HREFI' },
            { quincena: 7, mes: 'Julio', bonos: 5536865.00, hc: 101, tipo: 'HREFI' },
            { quincena: 8, mes: 'Agosto', bonos: 5638100.70, hc: 86, tipo: 'HREFI' },
            { quincena: 9, mes: 'Septiembre', bonos: 4736000.00, hc: 86, tipo: 'HREFI' },
            { quincena: 10, mes: 'Octubre', bonos: 5061738.75, hc: 86, tipo: 'HREFI' },
            { quincena: 11, mes: 'Noviembre', bonos: 5699480.00, hc: 86, tipo: 'HREFI' },
            { quincena: 12, mes: 'Diciembre', bonos: 5219662.52, hc: 86, tipo: 'HREFI' },
            { quincena: 13, mes: 'Enero', bonos: 3684802.25, hc: 85, tipo: 'HREFI' },
            { quincena: 14, mes: 'Febrero', bonos: 7056283.32, hc: 85, tipo: 'HREFI' },
            { quincena: 15, mes: 'Marzo', bonos: 6972477.95, hc: 85, tipo: 'HREFI' },
            { quincena: 16, mes: 'Abril', bonos: 7515599.10, hc: 85, tipo: 'HREFI' },
            { quincena: 17, mes: 'Mayo', bonos: 7458902.15, hc: 86, tipo: 'HREFI' },
            { quincena: 18, mes: 'Junio', bonos: 6767374.00, hc: 86, tipo: 'HREFI' },
            { quincena: 19, mes: 'Julio', bonos: 5843739.30, hc: 86, tipo: 'HREFI' },
            { quincena: 20, mes: 'Agosto', bonos: 8306037.50, hc: 86, tipo: 'HREFI' },
            { quincena: 21, mes: 'Septiembre', bonos: 4286000.00, hc: 86, tipo: 'HREFI' },
            { quincena: 22, mes: 'Octubre', bonos: 4891738.75, hc: 86, tipo: 'HREFI' },
            { quincena: 23, mes: 'Noviembre', bonos: 5329480.00, hc: 86, tipo: 'HREFI' },
            { quincena: 24, mes: 'Diciembre', bonos: 4869662.52, hc: 86, tipo: 'HREFI' }
        ];

        const datosRecord = [
            { id: 'V10012', nombre: 'MARCOS FERNANDO RUIZ GUERRERO', departamento: 'ADMINISTRACIÓN', bono: 231250.00 },
            { id: 'V10024', nombre: 'PEDRO ANTONIO TAXILAGA LOPEZ', departamento: 'SUMINISTROS', bono: 333000.00 },
            { id: 'V10026', nombre: 'JESUS AURELIO MORALES COLLADO', departamento: 'OPERACIONES', bono: 178710.00 },
            { id: 'V10029', nombre: 'FRANCISCO JAVIER LARA HIDALGO', departamento: 'SUMINISTROS', bono: 51800.00 },
            { id: 'V10030', nombre: 'RAUL TRONCO ALVAREZ', departamento: 'OPERACIONES', bono: 138750.00 },
            { id: 'V10031', nombre: 'MARVIN DEL CARMEN DE LA CRUZ IZQUIERDO', departamento: 'SUMINISTROS', bono: 199800.00 },
            { id: 'V10041', nombre: 'ANGEL MARIO LOPEZ GOMEZ', departamento: 'OPERACIONES', bono: 256780.00 },
            { id: 'V10046', nombre: 'RAMON BASTAR MEJIA', departamento: 'OPERACIONES', bono: 213120.00 },
            { id: 'V10049', nombre: 'JUAN CARLOS DIAZ RODRIGUEZ', departamento: 'OPERACIONES', bono: 51060.00 },
            { id: 'V10050', nombre: 'DIANA LAURA ARZAT ALEJANDRO', departamento: 'GEOCIENCIAS', bono: 120120.50 },
            { id: 'V10057', nombre: 'LUIS ALBERTO MENDIZ GONZALEZ', departamento: 'GEOCIENCIAS', bono: 133052.00 },
            { id: 'V10069', nombre: 'OCTAVO CESAR HERNANDEZ RAMON', departamento: 'SUMINISTROS', bono: 185000.00 },
            { id: 'V10074', nombre: 'ANTONIO RAVANALES ESCALANTE', departamento: 'OPERACIONES', bono: 92870.00 },
            { id: 'V10075', nombre: 'SALVADOR GERARDO VELASCO LOPEZ', departamento: 'OPERACIONES', bono: 311170.00 },
            { id: 'V10077', nombre: 'ERNESTINA PEREZ ACOSTA', departamento: 'ADMINISTRACIÓN', bono: 27750.00 },
            { id: 'V10084', nombre: 'CHRISTIAN NERI JIMENEZ SALAS', departamento: 'OPERACIONES', bono: 345765.00 },
            { id: 'V10087', nombre: 'EDVINI RAFAEL COLINA LANDA', departamento: 'OPERACIONES', bono: 185740.00 },
            { id: 'V10102', nombre: 'JORGE ARMANDO GARCIA NOCHEBUENA', departamento: 'OPERACIONES', bono: 219410.00 },
            { id: 'V10105', nombre: 'FABIAN HERNANDEZ REYES', departamento: 'OPERACIONES', bono: 25160.00 },
            { id: 'V10106', nombre: 'CARLOS OCTAVO BAUTISTA GONZALEZ', departamento: 'OPERACIONES', bono: 146150.00 },
            { id: 'V10109', nombre: 'RICARDO PURECO ALEJANDRO', departamento: 'OPERACIONES', bono: 213120.00 },
            { id: 'V10110', nombre: 'JOSE ANTONIO ACOSTA CAMPOS', departamento: 'OPERACIONES', bono: 297110.00 },
            { id: 'V10112', nombre: 'CECILIO RAMIREZ MONTERO', departamento: 'OHSE', bono: 23957.50 },
            { id: 'V10114', nombre: 'MIGUEL HERNANDEZ GARCIA', departamento: 'OHSE', bono: 19980.00 },
            { id: 'V10116', nombre: 'MABEL AZUCENA RODRIGUEZ CANTU', departamento: 'OHSE', bono: 7030.00 },
            { id: 'V10120', nombre: 'FELIPE DANIEL LOM TIBURCIO', departamento: 'OPERACIONES', bono: 251600.00 }
        ];

        // --- Helpers ---
        function formatCurrency(amount, currency = 'MXN') {
            if (amount === null || amount === undefined) return '$0.00';
            const loc = currency === 'USD' ? 'en-US' : 'es-MX';
            const formatted = new Intl.NumberFormat(loc, {
                style: 'currency',
                currency: currency,
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount);
            return formatted;
        }

        function convertMxnToUsd(mxn) {
            return mxn / tasaCambio;
        }

        function delay(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        // --- Lógica de Filtros Dinámicos Automáticos ---
        function populateSpecificPeriod(typeSelectId, specSelectId) {
            const typeSel = document.getElementById(typeSelectId);
            const specSel = document.getElementById(specSelectId);

            function updateOptions() {
                const val = typeSel.value;
                specSel.innerHTML = '<option value="todos">Todos</option>';

                if(val === 'quincena') {
                    for(let i=1; i<=24; i++) {
                        specSel.innerHTML += `<option value="${i}">Quincena ${i}</option>`;
                    }
                } else if(val === 'mes') {
                    const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                                  'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                    meses.forEach(m => {
                        specSel.innerHTML += `<option value="${m.toLowerCase()}">${m}</option>`;
                    });
                } else if(val === 'trimestre') {
                    for(let i=1; i<=4; i++) {
                        specSel.innerHTML += `<option value="${i}">Trimestre ${i} (Q${i})</option>`;
                    }
                } else if(val === 'anual') {
                    specSel.innerHTML = '<option value="todos">Año Completo</option>';
                }
            }

            typeSel.addEventListener('change', updateOptions);
            updateOptions();
        }

        // Inicializar dropdowns
        populateSpecificPeriod('p1-type', 'p1-spec');

        // --- Funciones de Renderizado con Filtros Automáticos ---

        // 1. Tabla Periodos (con filtros automáticos)
        async function updatePeriodosTable() {
            const tbody = document.querySelector('#periodos-table tbody');
            const tfoot = document.querySelector('#periodos-table tfoot');
            const yearElement = document.getElementById('current-year-1');

            // Mostrar loading
            tbody.innerHTML = '<tr><td colspan="8" class="text-center"><div class="loading-spinner"></div> Cargando datos...</td></tr>';
            tfoot.innerHTML = '';

            await delay(300); // Simular carga

            const year = document.getElementById('p1-year').value;
            const type = document.getElementById('p1-type').value;
            const spec = document.getElementById('p1-spec').value;
            const dept = document.getElementById('p1-dept').value;
            const mesFilter = document.getElementById('p1-mes-filter').value;
            const sortBy = document.getElementById('p1-sort').value;

            // Actualizar título
            yearElement.textContent = year;

            let data = [...datosQuincena];
            let totalMxn = 0;
            let totalHc = 0;
            let count = 0;

            // Aplicar filtros
            if(type === 'quincena' && spec !== 'todos') {
                data = data.filter(d => d.quincena == spec);
            }
            if(mesFilter !== 'todos') {
                data = data.filter(d => d.mes.toLowerCase() === mesFilter);
            }

            // Ordenar
            if(sortBy === 'periodo-desc') {
                data.sort((a, b) => b.quincena - a.quincena);
            } else if(sortBy === 'bonos') {
                data.sort((a, b) => b.bonos - a.bonos);
            } else if(sortBy === 'bonos-asc') {
                data.sort((a, b) => a.bonos - b.bonos);
            } else {
                data.sort((a, b) => a.quincena - b.quincena);
            }

            // Calcular total para porcentajes
            const totalBonos = data.reduce((acc, item) => acc + item.bonos, 0);

            tbody.innerHTML = '';
            data.forEach(d => {
                totalMxn += d.bonos;
                totalHc += d.hc;
                count++;

                const usd = convertMxnToUsd(d.bonos);
                const promedioXhc = d.bonos / d.hc;
                const porcentajeTotal = totalBonos > 0 ? (d.bonos / totalBonos * 100) : 0;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-center">Q${d.quincena}</td>
                    <td class="text-center">${d.mes}</td>
                    <td class="text-center">${d.hc}</td>
                    <td class="text-right">
                        <div class="dual-value">
                            <span class="currency-mxn">${formatCurrency(d.bonos)}</span>
                            <span class="currency-usd">≈ ${formatCurrency(usd, 'USD')}</span>
                        </div>
                    </td>
                    <td class="text-right currency-usd">${formatCurrency(usd, 'USD')}</td>
                    <td class="text-right currency-mxn">${formatCurrency(promedioXhc)}</td>
                    <td class="text-center">${porcentajeTotal.toFixed(2)}%</td>
                    <td class="text-center">${tasaCambio.toFixed(2)}</td>
                `;
                tbody.appendChild(tr);
            });

            // Fila Total
            if(data.length > 0) {
                const promedioHc = Math.round(totalHc / count);
                const promedioMxn = totalMxn / count;
                const promedioUsd = convertMxnToUsd(promedioMxn);
                const totalUsd = convertMxnToUsd(totalMxn);

                tfoot.innerHTML = `
                    <tr class="total-row">
                        <td colspan="2" class="text-center"><strong>TOTAL (${count} periodos)</strong></td>
                        <td class="text-center"><strong>${promedioHc}</strong></td>
                        <td class="text-right">
                            <div class="dual-value">
                                <span class="currency-mxn"><strong>${formatCurrency(totalMxn)}</strong></span>
                                <span class="currency-usd"><strong>≈ ${formatCurrency(totalUsd, 'USD')}</strong></span>
                            </div>
                        </td>
                        <td class="text-right currency-usd"><strong>${formatCurrency(totalUsd, 'USD')}</strong></td>
                        <td class="text-right currency-mxn"><strong>${formatCurrency(promedioMxn)}</strong></td>
                        <td class="text-center"><strong>100%</strong></td>
                        <td class="text-center"><strong>${tasaCambio.toFixed(2)}</strong></td>
                    </tr>
                `;
            }
        }

        // 2. Tabla Record (con filtros automáticos)
        async function updateRecordTable() {
            const tbody = document.querySelector('#record-table tbody');
            const tfoot = document.querySelector('#record-table tfoot');
            const yearElement = document.getElementById('current-year-2');

            // Mostrar loading
            tbody.innerHTML = '<tr><td colspan="8" class="text-center"><div class="loading-spinner"></div> Cargando datos...</td></tr>';
            tfoot.innerHTML = '';

            await delay(300);

            const year = document.getElementById('p2-year').value;
            const dept = document.getElementById('p2-dept').value;
            const min = parseFloat(document.getElementById('p2-min').value) || 0;
            const max = parseFloat(document.getElementById('p2-max').value) || Infinity;
            const sortBy = document.getElementById('p2-sort').value;

            // Actualizar título
            yearElement.textContent = year;

            let data = datosRecord.filter(item => {
                const matchDepto = dept === 'todos' ||
                    item.departamento.toLowerCase().includes(dept.toLowerCase());
                const matchVal = item.bono >= min && item.bono <= max;
                return matchDepto && matchVal;
            });

            // Ordenar
            if(sortBy === 'bono-desc') {
                data.sort((a, b) => b.bono - a.bono);
            } else if(sortBy === 'bono-asc') {
                data.sort((a, b) => a.bono - b.bono);
            } else if(sortBy === 'nombre') {
                data.sort((a, b) => a.nombre.localeCompare(b.nombre));
            } else if(sortBy === 'departamento') {
                data.sort((a, b) => a.departamento.localeCompare(b.departamento));
            }

            const totalMxn = data.reduce((acc, i) => acc + i.bono, 0);

            // Calcular promedio por departamento
            const deptoSums = {};
            const deptoCounts = {};

            data.forEach(i => {
                if(!deptoSums[i.departamento]) {
                    deptoSums[i.departamento] = 0;
                    deptoCounts[i.departamento] = 0;
                }
                deptoSums[i.departamento] += i.bono;
                deptoCounts[i.departamento]++;
            });

            tbody.innerHTML = '';
            data.forEach(item => {
                const usd = convertMxnToUsd(item.bono);
                const avg = deptoSums[item.departamento] / deptoCounts[item.departamento];
                const diff = item.bono - avg;
                const pct = totalMxn > 0 ? (item.bono / totalMxn * 100) : 0;
                const diffClass = diff >= 0 ? 'positive' : 'negative';
                const diffSymbol = diff >= 0 ? '+' : '';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-left">${item.id}</td>
                    <td class="text-left">${item.nombre}</td>
                    <td class="text-center">${item.departamento}</td>
                    <td class="text-right currency-mxn">${formatCurrency(item.bono)}</td>
                    <td class="text-right currency-usd">${formatCurrency(usd, 'USD')}</td>
                    <td class="text-center">${pct.toFixed(2)}%</td>
                    <td class="text-right currency-mxn">${formatCurrency(avg)}</td>
                    <td class="text-right ${diffClass}">${diffSymbol}${formatCurrency(diff)}</td>
                `;
                tbody.appendChild(tr);
            });

            // Fila Total
            if(data.length > 0) {
                const totalUsd = convertMxnToUsd(totalMxn);
                const promedioGeneral = totalMxn / data.length;

                tfoot.innerHTML = `
                    <tr class="total-row">
                        <td colspan="3" class="text-center"><strong>TOTAL (${data.length} empleados)</strong></td>
                        <td class="text-right currency-mxn"><strong>${formatCurrency(totalMxn)}</strong></td>
                        <td class="text-right currency-usd"><strong>${formatCurrency(totalUsd, 'USD')}</strong></td>
                        <td class="text-center"><strong>100%</strong></td>
                        <td class="text-right currency-mxn"><strong>${formatCurrency(promedioGeneral)}</strong></td>
                        <td class="text-right"><strong>-</strong></td>
                    </tr>
                `;
            }
        }

        // 3. Tabla Resumen (con filtros automáticos)
        async function updateResumenTable() {
            const tbody = document.querySelector('#resumen-table tbody');
            const yearElement = document.getElementById('current-year-3');

            // Mostrar loading
            tbody.innerHTML = '<tr><td colspan="6" class="text-center"><div class="loading-spinner"></div> Cargando datos...</td></tr>';

            await delay(300);

            const year = document.getElementById('p3-year').value;
            const dept = document.getElementById('p3-dept').value;
            const view = document.getElementById('p3-view').value;
            const currency = document.getElementById('p3-currency').value;

            // Actualizar título
            yearElement.textContent = year;

            tbody.innerHTML = '';

            // Datos según vista seleccionada
            if(view === 'anual') {
                const totalMxn = datosQuincena.reduce((acc, item) => acc + item.bonos, 0);
                const totalUsd = convertMxnToUsd(totalMxn);
                const hcPromedio = 85;
                const bonosXhc = totalMxn / hcPromedio;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-left"><strong>${year} (Anual)</strong></td>
                    <td class="text-right currency-mxn">${formatCurrency(totalMxn)}</td>
                    <td class="text-right currency-usd">${formatCurrency(totalUsd, 'USD')}</td>
                    <td class="text-center">${hcPromedio}</td>
                    <td class="text-right currency-mxn">${formatCurrency(bonosXhc)}</td>
                    <td class="text-center">${tasaCambio.toFixed(2)}</td>
                `;
                tbody.appendChild(tr);

            } else if(view === 'trimestral') {
                const trimestres = [
                    { nombre: 'Q1 (Ene-Mar)', meses: ['Enero', 'Febrero', 'Marzo'] },
                    { nombre: 'Q2 (Abr-Jun)', meses: ['Abril', 'Mayo', 'Junio'] },
                    { nombre: 'Q3 (Jul-Sep)', meses: ['Julio', 'Agosto', 'Septiembre'] },
                    { nombre: 'Q4 (Oct-Dic)', meses: ['Octubre', 'Noviembre', 'Diciembre'] }
                ];

                trimestres.forEach(trimestre => {
                    let bonosTrimestreMxn = 0;
                    datosQuincena.forEach(item => {
                        if(trimestre.meses.includes(item.mes)) {
                            bonosTrimestreMxn += item.bonos;
                        }
                    });

                    const bonosTrimestreUsd = convertMxnToUsd(bonosTrimestreMxn);
                    const hcPromedio = 85;
                    const bonosXhc = bonosTrimestreMxn / hcPromedio;

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="text-left"><strong>${trimestre.nombre} ${year}</strong></td>
                        <td class="text-right currency-mxn">${formatCurrency(bonosTrimestreMxn)}</td>
                        <td class="text-right currency-usd">${formatCurrency(bonosTrimestreUsd, 'USD')}</td>
                        <td class="text-center">${hcPromedio}</td>
                        <td class="text-right currency-mxn">${formatCurrency(bonosXhc)}</td>
                        <td class="text-center">${tasaCambio.toFixed(2)}</td>
                    `;
                    tbody.appendChild(tr);
                });

            } else if(view === 'mensual') {
                const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                             'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

                meses.forEach(mes => {
                    let bonosMesMxn = 0;
                    datosQuincena.forEach(item => {
                        if(item.mes === mes) {
                            bonosMesMxn += item.bonos;
                        }
                    });

                    if(bonosMesMxn > 0) {
                        const bonosMesUsd = convertMxnToUsd(bonosMesMxn);
                        const hcPromedio = 85;
                        const bonosXhc = bonosMesMxn / hcPromedio;

                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td class="text-left"><strong>${mes} ${year}</strong></td>
                            <td class="text-right currency-mxn">${formatCurrency(bonosMesMxn)}</td>
                            <td class="text-right currency-usd">${formatCurrency(bonosMesUsd, 'USD')}</td>
                            <td class="text-center">${hcPromedio}</td>
                            <td class="text-right currency-mxn">${formatCurrency(bonosXhc)}</td>
                            <td class="text-center">${tasaCambio.toFixed(2)}</td>
                        `;
                        tbody.appendChild(tr);
                    }
                });
            }
        }

        // --- Event Listeners para Filtros Automáticos ---

        // Tab 1: Bonos por Periodo
        const tab1Filters = ['p1-year', 'p1-type', 'p1-spec', 'p1-dept', 'p1-mes-filter', 'p1-sort'];
        tab1Filters.forEach(id => {
            document.getElementById(id).addEventListener('change', updatePeriodosTable);
        });

        // Tab 2: Record de Bonos
        const tab2Filters = ['p2-year', 'p2-dept', 'p2-sort'];
        tab2Filters.forEach(id => {
            document.getElementById(id).addEventListener('change', updateRecordTable);
        });

        // Inputs con debounce para mejor performance
        let recordTimeout;
        document.getElementById('p2-min').addEventListener('input', function() {
            clearTimeout(recordTimeout);
            recordTimeout = setTimeout(updateRecordTable, 500);
        });

        document.getElementById('p2-max').addEventListener('input', function() {
            clearTimeout(recordTimeout);
            recordTimeout = setTimeout(updateRecordTable, 500);
        });

        // Tab 3: Resumen General
        const tab3Filters = ['p3-year', 'p3-dept', 'p3-view', 'p3-currency'];
        tab3Filters.forEach(id => {
            document.getElementById(id).addEventListener('change', updateResumenTable);
        });

        // --- Listeners de Tabs ---
        const tabs = document.querySelectorAll('.tab-btn');
        const sections = document.querySelectorAll('.table-section');

        tabs.forEach(btn => {
            btn.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                sections.forEach(s => s.classList.remove('active'));

                btn.classList.add('active');
                const target = document.getElementById(btn.dataset.tab);
                target.classList.add('active');

                // Actualizar la tabla correspondiente al cambiar de tab
                if(btn.dataset.tab === 'bonos-periodicos') updatePeriodosTable();
                if(btn.dataset.tab === 'record-bonos') updateRecordTable();
                if(btn.dataset.tab === 'resumen-general') updateResumenTable();
            });
        });

        // --- Botones de Reset ---
        document.getElementById('p1-reset').addEventListener('click', () => {
            document.getElementById('p1-year').value = '2025';
            document.getElementById('p1-type').value = 'quincena';
            document.getElementById('p1-spec').value = 'todos';
            document.getElementById('p1-dept').value = 'todos';
            document.getElementById('p1-mes-filter').value = 'todos';
            document.getElementById('p1-sort').value = 'periodo';
            updatePeriodosTable();
        });

        document.getElementById('p2-reset').addEventListener('click', () => {
            document.getElementById('p2-year').value = '2025';
            document.getElementById('p2-dept').value = 'todos';
            document.getElementById('p2-min').value = '';
            document.getElementById('p2-max').value = '';
            document.getElementById('p2-sort').value = 'bono-desc';
            updateRecordTable();
        });

        document.getElementById('p3-reset').addEventListener('click', () => {
            document.getElementById('p3-year').value = '2025';
            document.getElementById('p3-dept').value = 'todos';
            document.getElementById('p3-view').value = 'anual';
            document.getElementById('p3-currency').value = 'mxn';
            updateResumenTable();
        });

        // --- Función de Exportación ---
        window.exportTable = function(tableId, filename) {
            const table = document.getElementById(tableId);
            let csv = [];

            // Encabezados
            const headers = [];
            table.querySelectorAll('th').forEach(th => {
                headers.push(th.textContent.trim());
            });
            csv.push(headers.join(','));

            // Filas del cuerpo
            table.querySelectorAll('tbody tr').forEach(row => {
                const rowData = [];
                row.querySelectorAll('td').forEach(cell => {
                    let text = cell.textContent.trim();
                    text = text.replace(/[$,]/g, '');
                    text = text.replace(/\s+/g, ' ');
                    rowData.push(`"${text}"`);
                });
                csv.push(rowData.join(','));
            });

            // Fila de total si existe
            table.querySelectorAll('tfoot tr').forEach(row => {
                const rowData = [];
                row.querySelectorAll('td').forEach(cell => {
                    let text = cell.textContent.trim();
                    text = text.replace(/[$,]/g, '');
                    text = text.replace(/\s+/g, ' ');
                    rowData.push(`"${text}"`);
                });
                csv.push(rowData.join(','));
            });

            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');

            if(navigator.msSaveBlob) {
                navigator.msSaveBlob(blob, filename + '.csv');
            } else {
                link.href = URL.createObjectURL(blob);
                link.download = filename + '.csv';
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        };

        // --- Inicialización ---
        updatePeriodosTable();
    });
</script>
@endsection
